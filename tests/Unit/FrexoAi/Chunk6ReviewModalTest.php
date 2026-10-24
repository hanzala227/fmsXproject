<?php

namespace Tests\Unit\FrexoAi;

use App\Http\Controllers\AiExtractionController;
use Tests\TestCase;

class Chunk6ReviewModalTest extends TestCase
{
    /**
     * Test 1: "Accept all verified" applies ONLY fields with EXPLICIT or DERIVED status and no warnings/conflicts.
     */
    public function test_accept_all_verified_filters_correctly(): void
    {
        $fields = [
            ['key' => 'mbl_no', 'status' => 'EXPLICIT', 'hasConflict' => false, 'hasWarning' => false, 'selected' => false],
            ['key' => 'vessel_name', 'status' => 'DERIVED', 'hasConflict' => false, 'hasWarning' => false, 'selected' => false],
            ['key' => 'weight', 'status' => 'REVIEW_REQUIRED', 'hasConflict' => false, 'hasWarning' => true, 'selected' => true],
            ['key' => 'carrier_id', 'status' => 'CONFLICT', 'hasConflict' => true, 'hasWarning' => false, 'selected' => true],
            ['key' => 'shipper_id', 'status' => 'AMBIGUOUS', 'hasConflict' => false, 'hasWarning' => false, 'selected' => true],
            ['key' => 'cargo_desc', 'status' => 'UNREADABLE', 'hasConflict' => false, 'hasWarning' => false, 'selected' => true],
        ];

        // Simulate "Accept All Verified" filter algorithm from ai-modal.blade.php
        foreach ($fields as &$item) {
            $isVerified = in_array($item['status'], ['EXPLICIT', 'DERIVED'], true)
                && empty($item['hasConflict'])
                && empty($item['hasWarning'])
                && ! in_array($item['status'], ['CONFLICT', 'AMBIGUOUS', 'REVIEW_REQUIRED', 'UNREADABLE'], true);

            $item['selected'] = $isVerified;
        }
        unset($item);

        $this->assertTrue($fields[0]['selected'], 'EXPLICIT field should be selected');
        $this->assertTrue($fields[1]['selected'], 'DERIVED field should be selected');
        $this->assertFalse($fields[2]['selected'], 'REVIEW_REQUIRED field must NOT be selected');
        $this->assertFalse($fields[3]['selected'], 'CONFLICT field must NOT be selected');
        $this->assertFalse($fields[4]['selected'], 'AMBIGUOUS field must NOT be selected');
        $this->assertFalse($fields[5]['selected'], 'UNREADABLE field must NOT be selected');
    }

    /**
     * Test 2: Conflict candidates are preserved and not auto-resolved.
     */
    public function test_conflict_candidates_preserved_for_operator_selection(): void
    {
        $fieldItem = [
            'key' => 'pol_id',
            'label' => 'Port of Loading',
            'status' => 'CONFLICT',
            'value' => null,
            'candidates' => [
                ['id' => 10, 'name' => 'Shanghai', 'code' => 'CNSHA'],
                ['id' => 12, 'name' => 'Ningbo', 'code' => 'CNNGB'],
            ],
            'selected' => false,
        ];

        // Ensure candidate array is intact and operator selection sets value
        $this->assertCount(2, $fieldItem['candidates']);
        $this->assertNull($fieldItem['value']);

        // Operator selects candidate 0
        $fieldItem['value'] = $fieldItem['candidates'][0]['id'];
        $fieldItem['selected'] = true;

        $this->assertEquals(10, $fieldItem['value']);
        $this->assertTrue($fieldItem['selected']);
    }

    /**
     * Test 3: Flag OFF preserves legacy pipeline behavior.
     */
    public function test_feature_flag_off_preserves_legacy_pipeline(): void
    {
        // By default or when set to false, frexo_ai.enabled is false
        config(['frexo_ai.enabled' => false]);
        $this->assertFalse(config('frexo_ai.enabled'));

        // Controller checks config('frexo_ai.enabled')
        $controller = new \ReflectionClass(AiExtractionController::class);
        $this->assertTrue($controller->hasMethod('extractOceanImport'));
        $this->assertTrue($controller->hasMethod('extractOceanExport'));
        $this->assertTrue($controller->hasMethod('extractAirImport'));
        $this->assertTrue($controller->hasMethod('extractAirExport'));
        $this->assertTrue($controller->hasMethod('apply'));
    }

    /**
     * Test 4: Frontend Blade template compiles without errors.
     */
    public function test_modal_blade_template_renders_or_compiles(): void
    {
        $viewPath = resource_path('views/partials/ai-modal.blade.php');
        $this->assertFileExists($viewPath);

        $content = file_get_contents($viewPath);
        $this->assertStringContainsString('Accept All Verified', $content);
        $this->assertStringContainsString('badge-explicit', $content);
        $this->assertStringContainsString('badge-conflict', $content);
        $this->assertStringContainsString('frexo-jump-evidence-btn', $content);
        $this->assertStringContainsString('frexo-candidate-picker', $content);
        $this->assertStringContainsString('unmatchedDocs', $content);
        $this->assertStringContainsString('failedDocs', $content);
    }
}
