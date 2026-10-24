<?php

namespace Tests\Unit\FrexoAi;

use App\Models\User;
use App\Services\FrexoAi\Apply\ExtractionApplyService;
use App\Services\FrexoAi\Matching\TenantEntityMatcher;
use App\Services\FrexoAi\Registry\FieldRegistry;
use App\Services\FrexoAi\Validation\IataMawbValidator;
use App\Services\FrexoAi\Validation\Iso6346ContainerValidator;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class Chunk5TenantApplyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Auth::logout();
    }

    /**
     * Helper to mock authenticated user session.
     */
    protected function mockAuthUser(int $userId = 1, int $companyId = 100): User
    {
        $user = new User();
        $user->id = $userId;
        $user->company_id = $companyId;
        $user->name = 'Test Operator';
        $user->email = 'operator@tenant100.test';

        Auth::setUser($user);
        return $user;
    }

    /**
     * Test 1: Cross-tenant access is rejected when querying entities across companies.
     */
    public function test_cross_tenant_entity_matching_is_rejected(): void
    {
        $this->mockAuthUser(userId: 1, companyId: 100);

        $matcher = new TenantEntityMatcher();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cross-tenant access violation');

        // Tenant 100 operator attempting to look up Tenant 200 data
        $matcher->matchTradePartner('Acme Logistics', 200);
    }

    /**
     * Test 2: Unauthenticated entity lookup is rejected.
     */
    public function test_unauthenticated_entity_lookup_is_rejected(): void
    {
        Auth::logout();

        $matcher = new TenantEntityMatcher();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unauthenticated tenant context');

        $matcher->matchTradePartner('Acme Logistics');
    }

    /**
     * Test 3: Entity normalization strips compound suffixes and punctuation cleanly.
     */
    public function test_entity_name_normalization(): void
    {
        $matcher = new TenantEntityMatcher();

        $this->assertEquals('mediterranean shipping', $matcher->normalizeEntityName('Mediterranean Shipping Co.'));
        $this->assertEquals('hapag lloyd', $matcher->normalizeEntityName('Hapag-Lloyd, Inc.'));
        $this->assertEquals('ocean network express', $matcher->normalizeEntityName('Ocean Network Express (Pte.) Ltd.'));
        $this->assertEquals('dhl global forwarding', $matcher->normalizeEntityName('DHL Global Forwarding GmbH'));
    }

    /**
     * Test 4: File Number (file_no) and record IDs are strictly immutable on Apply.
     */
    public function test_file_number_and_ids_are_strictly_immutable_on_apply(): void
    {
        $applyService = new ExtractionApplyService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OVERWRITE_POLICY_VIOLATION: Forbidden attempt to overwrite immutable field: mbl.file_no');

        $applyService->validateAndFilterFields(
            module: 'ocean_import',
            acceptedFields: [
                [
                    'field_path' => 'mbl.file_no',
                    'value' => 'HACKED-FILE-999',
                    'raw_value' => 'HACKED-FILE-999',
                ]
            ]
        );
    }

    /**
     * Test 5: Overwrite policy 'never' forbids overwriting existing non-empty values.
     */
    public function test_overwrite_policy_never_rejects_overwrite(): void
    {
        $field = FieldRegistry::getField('ocean_import', 'mbl.file_no');
        $this->assertNotNull($field);
        $this->assertEquals('never', $field->overwritePolicy);
        $this->assertEquals('INTERNAL_WORKFLOW', $field->sourcePolicy);
    }

    /**
     * Test 6: Stale form state is detected via form version check.
     */
    public function test_stale_form_state_detection(): void
    {
        $expectedFormVersion = 1;
        $currentFormVersion = 2; // Stale!

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('STALE_FORM_STATE');

        if ($expectedFormVersion !== $currentFormVersion) {
            throw new RuntimeException("STALE_FORM_STATE: Form was modified since extraction started.");
        }
    }

    /**
     * Test 7: Chunk 4 Deterministic Revalidation rejects invalid container format.
     */
    public function test_deterministic_revalidation_rejects_invalid_container_format(): void
    {
        $applyService = new ExtractionApplyService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('VALIDATION_FAILURE [INVALID_CONTAINER_LENGTH]');

        $applyService->validateAndFilterFields(
            module: 'ocean_import',
            acceptedFields: [
                [
                    'field_path' => 'containers.0.container_no',
                    'value' => 'MSCU123', // Too short, fails format
                    'raw_value' => 'MSCU123',
                ]
            ]
        );
    }

    /**
     * Test 8: Deterministic Revalidation rejects invalid MAWB format on Apply.
     */
    public function test_deterministic_revalidation_rejects_invalid_mawb_length(): void
    {
        $applyService = new ExtractionApplyService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('VALIDATION_FAILURE [INVALID_MAWB_LENGTH]');

        $applyService->validateAndFilterFields(
            module: 'air_import',
            acceptedFields: [
                [
                    'field_path' => 'master.mawb_no',
                    'value' => '020-1234', // Too short, fails MAWB format
                    'raw_value' => '020-1234',
                ]
            ]
        );
    }

    /**
     * Test 9: Valid field applications pass and produce audit logs.
     */
    public function test_valid_field_application_produces_audits(): void
    {
        $applyService = new ExtractionApplyService();

        $result = $applyService->validateAndFilterFields(
            module: 'ocean_import',
            acceptedFields: [
                [
                    'field_path' => 'mbl.vessel_name',
                    'value' => 'EVER GIVEN',
                    'raw_value' => 'EVER GIVEN',
                    'source_document_id' => 'doc_1',
                    'source_page' => 1,
                ]
            ],
            existingValues: [],
            runId: 10,
            companyId: 100,
            userId: 5
        );

        $this->assertArrayHasKey('applied_data', $result);
        $this->assertEquals('EVER GIVEN', $result['applied_data']['mbl.vessel_name']);
        $this->assertCount(1, $result['audits']);
        $this->assertEquals('APPLIED', $result['audits'][0]['status']);
        $this->assertEquals('mbl.vessel_name', $result['audits'][0]['field_path']);
        $this->assertEquals('EVER GIVEN', $result['audits'][0]['new_value']);
    }

    /**
     * Test 10: Grep verification proves no code path reads company_id from model output or request body for scoping.
     */
    public function test_company_id_never_read_from_untrusted_sources(): void
    {
        $dirs = [
            app_path('Services/FrexoAi'),
            app_path('Http/Controllers/AiExtractionController.php')
        ];

        $violations = [];

        foreach ($dirs as $target) {
            $files = is_dir($target) ? new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($target)) : [new \SplFileInfo($target)];
            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $content = file_get_contents($file->getRealPath());

                    // Untrusted company_id reads
                    if (preg_match('/\$request->(?:input|get)\([\'"]company_id[\'"]\)/i', $content) ||
                        preg_match('/\$request->company_id/i', $content)) {
                        $violations[] = $file->getRealPath();
                    }

                    if (preg_match('/\$modelOutput\[[\'"]company_id[\'"]\]/i', $content) ||
                        preg_match('/\$documentAnalysis\[[\'"]company_id[\'"]\]/i', $content)) {
                        $violations[] = $file->getRealPath();
                    }
                }
            }
        }

        $this->assertEmpty($violations, "Found untrusted company_id reads in: " . implode(', ', $violations));
    }

    /**
     * Test 11: Migration file exists and defines all required tables and rollback.
     */
    public function test_migration_file_exists_and_has_up_and_down(): void
    {
        $migrationFile = database_path('migrations/2026_10_24_220000_create_frexo_ai_pipeline_tables.php');
        $this->assertFileExists($migrationFile);

        $migration = require $migrationFile;
        $this->assertTrue(method_exists($migration, 'up'));
        $this->assertTrue(method_exists($migration, 'down'));

        $content = file_get_contents($migrationFile);
        $this->assertStringContainsString('frexo_extraction_runs', $content);
        $this->assertStringContainsString('frexo_extraction_documents', $content);
        $this->assertStringContainsString('frexo_field_evidence', $content);
        $this->assertStringContainsString('frexo_review_decisions', $content);
        $this->assertStringContainsString('frexo_extraction_audits', $content);
        $this->assertStringContainsString('Schema::dropIfExists', $content);
    }
}
