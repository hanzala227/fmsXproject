<?php

namespace Tests\Unit\FrexoAi;

use PHPUnit\Framework\TestCase;
use App\Services\FrexoAi\Registry\FieldRegistry;
use App\Services\FrexoAi\Registry\FieldDefinition;
use App\Services\FrexoAi\Schema\SchemaBuilder;
use App\Services\FrexoAi\Mapper\FormDataMapper;

class Chunk2SchemasAndRegistryTest extends TestCase
{
    public function test_all_four_modules_are_defined_in_registry(): void
    {
        $modules = FieldRegistry::getAllModules();
        $this->assertCount(4, $modules);
        $this->assertContains(FieldRegistry::MODULE_OCEAN_IMPORT, $modules);
        $this->assertContains(FieldRegistry::MODULE_OCEAN_EXPORT, $modules);
        $this->assertContains(FieldRegistry::MODULE_AIR_IMPORT, $modules);
        $this->assertContains(FieldRegistry::MODULE_AIR_EXPORT, $modules);
    }

    public function test_registry_contains_classified_fields_for_all_modules(): void
    {
        foreach (FieldRegistry::getAllModules() as $module) {
            $fields = FieldRegistry::getFields($module);
            $this->assertNotEmpty($fields, "Module $module should have registered fields");

            foreach ($fields as $path => $def) {
                $this->assertInstanceOf(FieldDefinition::class, $def);
                $this->assertEquals($path, $def->path);
                $this->assertContains($def->scope, [
                    FieldDefinition::SCOPE_SHIPMENT,
                    FieldDefinition::SCOPE_MASTER,
                    FieldDefinition::SCOPE_HOUSE,
                    FieldDefinition::SCOPE_CONTAINER,
                    FieldDefinition::SCOPE_LEG,
                    FieldDefinition::SCOPE_PARTY,
                ]);
                $this->assertContains($def->sourcePolicy, [
                    FieldDefinition::POLICY_EXTRACTABLE,
                    FieldDefinition::POLICY_TRUSTED_CONTEXT,
                    FieldDefinition::POLICY_INTERNAL_WORKFLOW,
                    FieldDefinition::POLICY_CALCULATED,
                ]);
                $this->assertContains($def->overwritePolicy, [
                    FieldDefinition::OVERWRITE_NEVER,
                    FieldDefinition::OVERWRITE_ONLY_IF_EMPTY,
                    FieldDefinition::OVERWRITE_USER_CONFIRMED,
                ]);
            }
        }
    }

    public function test_internal_workflow_fields_are_marked_with_never_overwrite(): void
    {
        foreach (FieldRegistry::getAllModules() as $module) {
            $internalFields = ['mbl.file_no', 'mbl.post_date', 'mbl.op_name', 'mbl.dm_sales_person_name', 'mbl.company_id'];
            foreach ($internalFields as $path) {
                $field = FieldRegistry::getField($module, $path);
                $this->assertNotNull($field, "Field $path must exist in $module");
                $this->assertTrue($field->isInternalWorkflow(), "Field $path must be INTERNAL_WORKFLOW in $module");
                $this->assertEquals(FieldDefinition::OVERWRITE_NEVER, $field->overwritePolicy);
            }
        }
    }

    public function test_schema_builder_excludes_internal_workflow_fields(): void
    {
        foreach (FieldRegistry::getAllModules() as $module) {
            $schema = SchemaBuilder::buildGeminiResponseSchema($module);
            $this->assertEquals('object', $schema['type']);
            $this->assertArrayHasKey('mbl', $schema['properties']);
            $this->assertArrayHasKey('hbls', $schema['properties']);

            $mblProps = $schema['properties']['mbl']['properties'];
            $this->assertArrayNotHasKey('file_no', $mblProps, "Gemini schema must NEVER include file_no");
            $this->assertArrayNotHasKey('post_date', $mblProps, "Gemini schema must NEVER include post_date");
            $this->assertArrayNotHasKey('op_name', $mblProps, "Gemini schema must NEVER include op_name");
            $this->assertArrayNotHasKey('dm_sales_person_name', $mblProps, "Gemini schema must NEVER include dm_sales_person_name");
            $this->assertArrayNotHasKey('company_id', $mblProps, "Gemini schema must NEVER include company_id");
            $this->assertArrayNotHasKey('id', $mblProps, "Gemini schema must NEVER include DB id");
        }
    }

    public function test_schema_builder_field_rules_text_contains_internal_workflow_warning(): void
    {
        foreach (FieldRegistry::getAllModules() as $module) {
            $rulesText = SchemaBuilder::buildFieldRulesText($module);
            $this->assertStringContainsString('CRITICAL: The following fields are strictly INTERNAL_WORKFLOW', $rulesText);
            $this->assertStringContainsString('mbl.file_no', $rulesText);
            $this->assertStringContainsString('mbl.company_id', $rulesText);
        }
    }

    public function test_form_data_mapper_strips_internal_workflow_fields(): void
    {
        $maliciousAiOutput = [
            'mbl' => [
                'file_no' => 'HACKED-FILE-999',
                'company_id' => 9999,
                'op_name' => 'Fake Operator',
                'dm_sales_person_name' => 'Fake Sales',
                'mbl_no' => 'MSCU12345678',
                'carrier_name' => 'MSC',
            ],
            'containers' => [
                [
                    'container_no' => 'MSCU7654321',
                    'seal_no' => 'SL999',
                    'container_type' => '40HC',
                ]
            ],
            'hbls' => [
                [
                    'hbl_no' => 'HBL001',
                    'shipper_name' => 'Acme Corp',
                    'sales_person_name' => 'Malicious Hacker',
                ]
            ]
        ];

        $mapped = FormDataMapper::mapToApplicationStructure('ocean_import', $maliciousAiOutput);

        $this->assertArrayNotHasKey('file_no', $mapped['mbl']);
        $this->assertArrayNotHasKey('company_id', $mapped['mbl']);
        $this->assertArrayNotHasKey('op_name', $mapped['mbl']);
        $this->assertArrayNotHasKey('dm_sales_person_name', $mapped['mbl']);
        $this->assertEquals('MSCU12345678', $mapped['mbl']['mbl_no']);
        $this->assertEquals('MSC', $mapped['mbl']['carrier_name']);

        $this->assertCount(1, $mapped['containers']);
        $this->assertEquals('MSCU7654321', $mapped['containers'][0]['container_no']);

        $this->assertCount(1, $mapped['hbls']);
        $this->assertEquals('HBL001', $mapped['hbls'][0]['hbl_no']);
        $this->assertArrayNotHasKey('sales_person_name', $mapped['hbls'][0]);
    }
}
