<?php

namespace App\Services;

use App\Models\AiSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Smalot\PdfParser\Parser;

class AiExtractionService
{
    /**
     * Extract structured data from an uploaded file for a specific module.
     *
     * @param UploadedFile $file
     * @param string $module (e.g., 'ocean_import')
     * @return array|null
     * @throws Exception
     */
    public function extractData(UploadedFile $file, string $module): ?array
    {
        $settings = AiSetting::current();
        if (!$settings || !$settings->api_key) {
            throw new Exception("AI is not configured. Please contact the Super Admin to configure AI settings.");
        }

        // 1. Extract text from document
        $text = $this->extractTextFromFile($file);
        if (empty(trim($text))) {
            throw new Exception("Could not extract any text from the provided document.");
        }

        // 2. Get prompt/schema for module
        $schemaPrompt = $this->getSchemaPrompt($module);

        // 3. Call AI
        $jsonResponse = $this->callAi($settings, $text, $schemaPrompt);

        // 4. Parse JSON
        $data = $this->parseJsonFromResponse($jsonResponse);
        if (!$data) {
            throw new Exception("AI returned an invalid response format.");
        }

        return $data;
    }

    private function extractTextFromFile(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $text = '';

        if ($extension === 'pdf') {
            try {
                $parser = new Parser();
                $pdf = $parser->parseFile($file->getPathname());
                $text = $pdf->getText();
            } catch (Exception $e) {
                Log::error("PDF Parsing Error: " . $e->getMessage());
                throw new Exception("Failed to read PDF file.");
            }
        } elseif (in_array($extension, ['csv', 'txt'])) {
            $text = file_get_contents($file->getPathname());
        } else {
            throw new Exception("Unsupported file type. Please upload a PDF or text document.");
        }

        // Truncate text if it's too long to prevent massive token usage (e.g., max 30000 chars)
        return substr($text, 0, 30000);
    }

    private function callAi(AiSetting $settings, string $text, string $schemaPrompt): string
    {
        $baseUrl = rtrim($settings->base_url, '/');
        $apiType = strtolower($settings->api_type ?? 'openai');
        
        // Handle Gemini via OpenAI Compatibility API
        if ($apiType === 'gemini') {
            // Override base_url to Gemini's OpenAI compatible endpoint if it's currently pointing to OpenAI
            if (str_contains($baseUrl, 'api.openai.com')) {
                $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/openai';
            }
        }

        // Ensure endpoint ends with /chat/completions for standard OpenAI compatible APIs
        if (!str_ends_with($baseUrl, '/chat/completions')) {
             if (str_ends_with($baseUrl, '/v1') || str_ends_with($baseUrl, '/v1beta/openai')) {
                 $baseUrl .= '/chat/completions';
             } else {
                 $baseUrl .= '/v1/chat/completions';
             }
        }

        $systemPrompt = "You are a professional freight forwarding AI assistant. Your job is to extract shipping data from the provided document text and return it STRICTLY as a JSON object matching the requested schema. DO NOT include any markdown formatting, explanations, or additional text. Just the raw JSON object.";

        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ],
            [
                'role' => 'user',
                'content' => "DOCUMENT TEXT:\n---\n" . $text . "\n---\n\nREQUIRED SCHEMA:\n" . $schemaPrompt
            ]
        ];

        try {
            // Gemini requires response_format string if using older versions, but gpt-4o uses object.
            // Let's omit response_format for max compatibility, and just prompt strongly for JSON.
            $payload = [
                'model' => $settings->model_name ?? 'gpt-4o',
                'messages' => $messages,
                'temperature' => 0.1,
            ];
            
            // Only add response_format object for non-Gemini to prevent crashes on some Gemini models
            if ($apiType !== 'gemini') {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $settings->api_key,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($baseUrl, $payload);

            if ($response->failed()) {
                Log::error("AI API Error: " . $response->body());
                throw new Exception("AI API request failed: " . $response->status());
            }

            $result = $response->json();
            return $result['choices'][0]['message']['content'] ?? '';

        } catch (Exception $e) {
            Log::error("AI Connection Error: " . $e->getMessage());
            throw new Exception("Failed to connect to AI service: " . $e->getMessage());
        }
    }

    private function parseJsonFromResponse(string $response): ?array
    {
        // Strip markdown code blocks if the AI includes them despite instructions
        $response = preg_replace('/```json/i', '', $response);
        $response = preg_replace('/```/i', '', $response);
        $response = trim($response);

        return json_decode($response, true);
    }

    private function getSchemaPrompt(string $module): string
    {
        if ($module === 'ocean_import') {
            return $this->getOceanImportSchema();
        }
        if ($module === 'ocean_export') {
            return $this->getOceanExportSchema();
        }
        if ($module === 'air_import') {
            return $this->getAirImportSchema();
        }

        throw new Exception("Module '$module' schema not defined.");
    }

    private function getOceanImportSchema(): string
    {
        return json_encode([
            "mbl" => [
                "file_no" => "string (File number if present)",
                "mbl_no" => "string (Master Bill of Lading Number)",
                "booking_no" => "string (Booking Number)",
                "post_date" => "YYYY-MM-DD (Date of posting or issue)",
                "forwarding_agent_name" => "string (Forwarding agent company name)",
                "op_name" => "string (Operator or OP name)",
                "agent_ref_no" => "string (Agent reference number or Customer Ref)",
                "dm_sales_person_name" => "string (Sales person name)",
                "oversea_agent_name" => "string (Oversea agent company name)",
                "co_loader_name" => "string (Co-loader company name)",
                "contract_no" => "string (Contract number)",
                "carrier_name" => "string (Name of the shipping carrier)",
                "sales_type" => "string (Sales type)",
                "bl_type" => "string (Type of B/L, e.g. Original, Express, Seaway)",
                "acct_carrier_name" => "string (Accounting carrier company name)",
                "sub_bl_no" => "string (Sub B/L number)",
                "dm_notify_name" => "string (Notify party name for MBL)",
                "cargo_type" => "string (Type of cargo, e.g. GENERAL CARGO)",
                "vessel_name" => "string (Name of the vessel)",
                "voyage" => "string (Voyage number)",
                "pol_name" => "string (Port of Loading)",
                "pod_name" => "string (Port of Discharge)",
                "del_name" => "string (Place of Delivery)",
                "fdest_name" => "string (Final Destination)",
                "cy_location_name" => "string (CY Location)",
                "cfs_location_name" => "string (CFS Location)",
                "etd" => "YYYY-MM-DD (Estimated Time of Departure)",
                "eta" => "YYYY-MM-DD (Estimated Time of Arrival)",
                "business_referred_by_name" => "string (Business referred by)",
                "description" => "string (Cargo description or remarks)"
            ],
            "hbls" => [
                [
                    "hbl_no" => "string (House Bill of Lading Number)",
                    "shipper_name" => "string (Shipper company name)",
                    "consignee_name" => "string (Consignee company name)",
                    "notify_party_name" => "string (Notify party name)",
                    "customer_name" => "string (Customer company name)",
                    "sales_person_name" => "string (Sales person name)",
                    "forwarding_agent_name" => "string (Forwarding agent name)",
                    "bl_type" => "string (B/L Type, e.g. Original, Express)",
                    "cargo_type" => "string (Cargo Type)",
                    "freight_term" => "string (Freight term, e.g. PREPAID, COLLECT)",
                    "pieces" => "integer (Total pieces/packages)",
                    "weight" => "float (Total gross weight in KG)",
                    "volume" => "float (Total volume in CBM)",
                    "hbl_description" => "string (Specific description for this HBL)",
                    "hbl_mark" => "string (Marks and numbers)"
                ]
            ],
            "containers" => [
                [
                    "container_no" => "string (e.g. TCNU1234567)",
                    "seal_no" => "string",
                    "container_type" => "string (e.g. 20GP, 40HQ)"
                ]
            ]
        ], JSON_PRETTY_PRINT);
    }


    private function getOceanExportSchema(): string
    {
        return json_encode([
            "mbl" => [
                "file_no" => "string (File number if present)",
                "mbl_no" => "string (Master Bill of Lading Number)",
                "booking_no" => "string (Booking Number)",
                "post_date" => "YYYY-MM-DD (Date of posting or issue)",
                "forwarding_agent_name" => "string (Forwarding agent company name)",
                "op_name" => "string (Operator or OP name)",
                "agent_ref_no" => "string (Agent reference number or Customer Ref)",
                "dm_sales_person_name" => "string (Sales person name)",
                "oversea_agent_name" => "string (Oversea agent company name)",
                "co_loader_name" => "string (Co-loader company name)",
                "contract_no" => "string (Contract number)",
                "carrier_name" => "string (Name of the shipping carrier)",
                "sales_type" => "string (Sales type)",
                "bl_type" => "string (Type of B/L, e.g. Original, Express, Seaway)",
                "acct_carrier_name" => "string (Accounting carrier company name)",
                "sub_bl_no" => "string (Sub B/L number)",
                "dm_notify_name" => "string (Notify party name for MBL)",
                "cargo_type" => "string (Type of cargo, e.g. GENERAL CARGO)",
                "vessel_name" => "string (Name of the vessel)",
                "voyage" => "string (Voyage number)",
                "pol_name" => "string (Port of Loading)",
                "pod_name" => "string (Port of Discharge)",
                "del_name" => "string (Place of Delivery)",
                "fdest_name" => "string (Final Destination)",
                "receipt_name" => "string (Place of Receipt)",
                "cy_location_name" => "string (CY Location)",
                "cfs_location_name" => "string (CFS Location)",
                "return_location_name" => "string (Return Location)",
                "empty_pickup_name" => "string (Empty Pickup Location)",
                "delivery_location_name" => "string (Delivery Location)",
                "etd" => "YYYY-MM-DD (Estimated Time of Departure)",
                "eta" => "YYYY-MM-DD (Estimated Time of Arrival)",
                "atd" => "YYYY-MM-DD (Actual Time of Departure)",
                "ata" => "YYYY-MM-DD (Actual Time of Arrival)",
                "receipt_etd" => "YYYY-MM-DD (Place of Receipt ETD)",
                "final_eta" => "YYYY-MM-DD (Final ETA)",
                "etb" => "YYYY-MM-DD (Estimated Time of Berthing)",
                "port_cutoff" => "YYYY-MM-DD (Port Cut-off date)",
                "doc_cutoff" => "YYYY-MM-DD (Document Cut-off date)",
                "vgm_cutoff" => "YYYY-MM-DD (VGM Cut-off date)",
                "rail_cutoff" => "YYYY-MM-DD (Rail Cut-off date)",
                "latest_gate_in" => "YYYY-MM-DD (Latest gate in date)",
                "obl_received_date" => "YYYY-MM-DD (Original B/L received date)",
                "released_date" => "YYYY-MM-DD (Released date)",
                "freight_term" => "string (Freight term, e.g. PREPAID, COLLECT)",
                "obl_type" => "string (OBL type)",
                "ship_mode" => "string (Ship mode, e.g. FCL, LCL)",
                "business_referred_by_name" => "string (Business referred by)",
                "is_express_bl" => "boolean (true if express BL)",
                "description" => "string (Cargo description or remarks)"
            ],
            "hbls" => [
                [
                    "hbl_no" => "string (House Bill of Lading Number)",
                    "shipper_name" => "string (Shipper company name)",
                    "consignee_name" => "string (Consignee company name)",
                    "notify_party_name" => "string (Notify party name)",
                    "customer_name" => "string (Customer company name)",
                    "sales_person_name" => "string (Sales person name)",
                    "forwarding_agent_name" => "string (Forwarding agent name)",
                    "bl_type" => "string (B/L Type, e.g. Original, Express)",
                    "cargo_type" => "string (Cargo Type)",
                    "freight_term" => "string (Freight term, e.g. PREPAID, COLLECT)",
                    "pieces" => "integer (Total pieces/packages)",
                    "weight" => "float (Total gross weight in KG)",
                    "volume" => "float (Total volume in CBM)",
                    "hbl_description" => "string (Specific description for this HBL)",
                    "hbl_mark" => "string (Marks and numbers)"
                ]
            ],
            "containers" => [
                [
                    "container_no" => "string (e.g. TCNU1234567)",
                    "seal_no" => "string",
                    "container_type" => "string (e.g. 20GP, 40HQ)"
                ]
            ]
        ], JSON_PRETTY_PRINT);
    }

    private function getAirImportSchema(): string
    {
        return json_encode([
            "mbl" => [
                "file_no" => "string (File number if present)",
                "mbl_no" => "string (Master Air Waybill Number)",
                "booking_no" => "string",
                "post_date" => "YYYY-MM-DD",
                "carrier_name" => "string (Name of Carrier)",
                "flight_no" => "string (Flight Number)",
                "pol_name" => "string (Port of Loading Name or Departure Airport)",
                "pod_name" => "string (Port of Discharge Name or Destination Airport)",
                "etd" => "YYYY-MM-DD",
                "eta" => "YYYY-MM-DD",
                "atd" => "YYYY-MM-DD",
                "ata" => "YYYY-MM-DD",
                "forwarding_agent_name" => "string",
                "op_name" => "string",
                "dm_sales_person_name" => "string",
                "oversea_agent_name" => "string",
                "co_loader_name" => "string",
                "agent_ref_no" => "string",
                "contract_no" => "string",
                "sales_type" => "string",
                "bl_type" => "string",
                "cargo_type" => "string",
                "description" => "string"
            ],
            "hbls" => [
                [
                    "hbl_no" => "string (House Air Waybill Number)",
                    "shipper_name" => "string",
                    "consignee_name" => "string",
                    "notify_party_name" => "string",
                    "weight" => "number (Gross Weight)",
                    "volume" => "number (Volume Weight/Chargeable)",
                    "pieces" => "number (Quantity of packages/cartons)",
                    "description" => "string (Nature of goods)"
                ]
            ]
        ], JSON_PRETTY_PRINT);
    }
}
