<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentMail;
use App\Services\TenantMailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class DocumentEmailController extends Controller
{
    /**
     * Send email with a client-generated PDF attached (base64 encoded).
     * The PDF is generated in the browser via html2canvas + jsPDF,
     * then sent as base64 to the server.
     */
    public function sendEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|email',
            'cc' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'pdf_base64' => 'required|string',
            'document_name' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Configure SMTP for the tenant
            $configured = TenantMailService::configureForCompany();
            if (!$configured) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Email settings are not configured for your company. Please update them in Settings → Profile.'
                ], 400);
            }

            // Decode PDF from base64
            $pdfBase64 = $request->input('pdf_base64');
            // Remove data URI prefix if present
            if (str_contains($pdfBase64, ',')) {
                $pdfBase64 = explode(',', $pdfBase64, 2)[1];
            }
            $pdfContent = base64_decode($pdfBase64);

            if ($pdfContent === false || strlen($pdfContent) < 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid PDF data received. Please try again.'
                ], 400);
            }

            $filename = $request->input('document_name', 'document') . '.pdf';
            
            // Prepare CC emails
            $to = $request->input('to');
            $ccList = [];
            if ($request->filled('cc')) {
                $ccEmails = preg_split('/[,;]+/', $request->input('cc'));
                foreach ($ccEmails as $cc) {
                    $cc = trim($cc);
                    if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                        $ccList[] = $cc;
                    }
                }
            }

            $mail = new DocumentMail(
                $request->input('subject'),
                $request->input('body'),
                $pdfContent,
                $filename
            );

            $pendingMail = Mail::to($to);
            if (!empty($ccList)) {
                $pendingMail->cc($ccList);
            }

            $pendingMail->send($mail);

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully with PDF attachment!'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send document email: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the email: ' . $e->getMessage()
            ], 500);
        }
    }
}
