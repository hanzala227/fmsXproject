<?php

namespace Tests\Unit\FrexoAi;

use App\Services\FrexoAi\Client\MockGeminiClient;
use App\Services\FrexoAi\DTO\FieldEvidenceDTO;
use App\Services\FrexoAi\Pipeline\StagedExtractionPipeline;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class Chunk3StagedPipelineTest extends TestCase
{
    public function test_end_to_end_pipeline_produces_full_contract_for_all_four_modules(): void
    {
        $modules = ['ocean_import', 'ocean_export', 'air_import', 'air_export'];

        foreach ($modules as $module) {
            $mockClient = new MockGeminiClient;

            // Queue Stage 2 extraction response
            $mockClient->queueResponse(json_encode([
                'document_type' => 'BILL_OF_LADING',
                'mbl' => [
                    'mbl_no' => 'MBL-TEST-123',
                    'booking_no' => 'BK-999',
                    'carrier_name' => 'MAERSK',
                ],
                'hbls' => [
                    [
                        'hbl_no' => 'HBL-001',
                        'shipper_name' => 'Global Logistics Inc',
                        'pieces' => 100,
                    ],
                ],
                'containers' => [
                    [
                        'container_no' => 'MSCU1234567',
                        'seal_no' => 'SL123',
                        'container_type' => '40HQ',
                    ],
                ],
                'routes' => [
                    [
                        'vessel_name' => 'MAERSK MC-KINNEY',
                        'voyage' => '001W',
                    ],
                ],
                'field_evidence' => [
                    [
                        'field_path' => 'mbl.mbl_no',
                        'scope' => 'master',
                        'raw_value' => 'MBL-TEST-123',
                        'normalized_value' => 'MBL-TEST-123',
                        'status' => 'EXPLICIT',
                        'source' => [
                            'page' => 1,
                            'evidence_text' => 'B/L No: MBL-TEST-123',
                        ],
                    ],
                ],
            ]));

            $pipeline = new StagedExtractionPipeline($mockClient);
            $file = UploadedFile::fake()->createWithContent('bill_of_lading.txt', 'SHIPMENT B/L No: MBL-TEST-123 CARRIER MAERSK');

            $result = $pipeline->execute([$file], $module);
            $array = $result->toArray();

            // Verify full contract structure
            $this->assertEquals('1.0', $array['schema_version']);
            $this->assertEquals($module, $array['module']);
            $this->assertNotEmpty($array['document_analysis']);
            $this->assertEquals('SUCCESS', $array['document_analysis'][0]['status']);
            $this->assertNotEmpty($array['shipment_groups']);
            $this->assertArrayHasKey('form_data', $array);
            $this->assertNotEmpty($array['field_evidence']);
            $this->assertArrayHasKey('conflicts', $array);
            $this->assertArrayHasKey('warnings', $array);
            $this->assertArrayHasKey('processing_summary', $array);
        }
    }

    public function test_failed_document_is_marked_as_failed_in_document_analysis(): void
    {
        $mockClient = new MockGeminiClient;

        // Doc 1 succeeds
        $mockClient->queueResponse(json_encode([
            'document_type' => 'COMMERCIAL_INVOICE',
            'mbl' => ['booking_no' => 'INV-001'],
        ]));

        // Doc 2 fails with malformed non-JSON
        $mockClient->queueResponse('MALFORMED_NON_JSON_RESPONSE_FROM_MODEL');

        $pipeline = new StagedExtractionPipeline($mockClient);

        $file1 = UploadedFile::fake()->createWithContent('invoice.txt', 'INVOICE INV-001');
        $file2 = UploadedFile::fake()->createWithContent('corrupted.txt', 'CORRUPTED FILE');

        $result = $pipeline->execute([$file1, $file2], 'ocean_import');
        $analyses = $result->documentAnalysis;

        $this->assertCount(2, $analyses);
        $this->assertEquals('SUCCESS', $analyses[0]->status);
        $this->assertEquals('FAILED', $analyses[1]->status);
        $this->assertNotEmpty($analyses[1]->limitations);
    }

    public function test_anti_hallucination_downgrades_fabricated_evidence_to_review_required(): void
    {
        $mockClient = new MockGeminiClient;

        // Model claims evidence that is NOT present in document text
        $mockClient->queueResponse(json_encode([
            'document_type' => 'BILL_OF_LADING',
            'mbl' => ['mbl_no' => 'FABRICATED-MBL-999'],
            'field_evidence' => [
                [
                    'field_path' => 'mbl.mbl_no',
                    'scope' => 'master',
                    'raw_value' => 'FABRICATED-MBL-999',
                    'normalized_value' => 'FABRICATED-MBL-999',
                    'status' => 'EXPLICIT',
                    'source' => [
                        'page' => 1,
                        'evidence_text' => 'Completely invented text not in document',
                    ],
                ],
            ],
        ]));

        $pipeline = new StagedExtractionPipeline($mockClient);

        $file = UploadedFile::fake()->createWithContent('bl.txt', 'ACTUAL TEXT IN FILE CONTAINS ONLY GENERAL WORDS');

        $result = $pipeline->execute([$file], 'ocean_import');

        $this->assertNotEmpty($result->fieldEvidence);
        $evidence = $result->fieldEvidence[0];

        $this->assertEquals(FieldEvidenceDTO::STATUS_REVIEW_REQUIRED, $evidence->status, 'Fabricated evidence must be downgraded to REVIEW_REQUIRED');
        $this->assertNotEmpty($result->warnings);
        $this->assertEquals('EVIDENCE_NOT_FOUND', $result->warnings[0]['code']);
        $this->assertEquals(1, $result->processingSummary['anti_hallucination']['downgraded_to_review']);
    }

    public function test_feature_flag_defaults_to_false(): void
    {
        $this->assertFalse(config('frexo_ai.enabled', false), 'Feature flag must default to false for safety');
    }
}
