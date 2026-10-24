<?php

namespace App\Services\FrexoAi\Schema;

use App\Services\FrexoAi\Registry\FieldDefinition;
use App\Services\FrexoAi\Registry\FieldRegistry;

class SchemaBuilder
{
    /**
     * Build standard JSON schema subset compatible with Gemini structured outputs
     */
    public static function buildGeminiResponseSchema(string $module): array
    {
        $fields = FieldRegistry::getFields($module);

        $mblProperties = [];
        $hblProperties = [];
        $containerProperties = [];
        $routeProperties = [];
        $flightProperties = [];

        foreach ($fields as $field) {
            if ($field->isInternalWorkflow()) {
                // INTERNAL_WORKFLOW fields must never be in the LLM response schema
                continue;
            }

            $propertySchema = self::mapFieldToSchemaProperty($field);

            if (str_starts_with($field->path, 'mbl.')) {
                $propKey = substr($field->path, 4);
                $mblProperties[$propKey] = $propertySchema;
            } elseif (str_starts_with($field->path, 'hbls[].')) {
                $propKey = substr($field->path, 7);
                $hblProperties[$propKey] = $propertySchema;
            } elseif (str_starts_with($field->path, 'containers[].')) {
                $propKey = substr($field->path, 13);
                $containerProperties[$propKey] = $propertySchema;
            } elseif (str_starts_with($field->path, 'routes[].')) {
                $propKey = substr($field->path, 9);
                $routeProperties[$propKey] = $propertySchema;
            } elseif (str_starts_with($field->path, 'flights[].')) {
                $propKey = substr($field->path, 10);
                $flightProperties[$propKey] = $propertySchema;
            }
        }

        $schema = [
            'type' => 'object',
            'properties' => [
                'mbl' => [
                    'type' => 'object',
                    'description' => 'Master BL or Master AWB level fields',
                    'properties' => $mblProperties,
                ],
                'hbls' => [
                    'type' => 'array',
                    'description' => 'List of House BLs or House AWBs',
                    'items' => [
                        'type' => 'object',
                        'properties' => $hblProperties,
                    ],
                ],
            ],
            'required' => ['mbl', 'hbls'],
        ];

        if (! empty($containerProperties)) {
            $schema['properties']['containers'] = [
                'type' => 'array',
                'description' => 'List of shipping containers',
                'items' => [
                    'type' => 'object',
                    'properties' => $containerProperties,
                ],
            ];
        }

        if (! empty($routeProperties)) {
            $schema['properties']['routes'] = [
                'type' => 'array',
                'description' => 'Multi-leg shipping route legs',
                'items' => [
                    'type' => 'object',
                    'properties' => $routeProperties,
                ],
            ];
        }

        if (! empty($flightProperties)) {
            $schema['properties']['flights'] = [
                'type' => 'array',
                'description' => 'Multi-leg flight schedule',
                'items' => [
                    'type' => 'object',
                    'properties' => $flightProperties,
                ],
            ];
        }

        return $schema;
    }

    /**
     * Dynamically generate {{FIELD_RULES}} for the runtime prompt directly from the FieldRegistry.
     * Guarantees zero drift between prompt instructions and validator schemas.
     */
    public static function buildFieldRulesText(string $module): string
    {
        $fields = FieldRegistry::getFields($module);
        $lines = [];

        $lines[] = 'FIELD SPECIFICATIONS FOR MODULE: '.strtoupper($module);
        $lines[] = 'CRITICAL: The following fields are strictly INTERNAL_WORKFLOW and MUST NEVER be extracted from documents or populated: File Number (mbl.file_no), Post Date (mbl.post_date), Operator (mbl.op_name), Sales Person (mbl.dm_sales_person_name), Company ID (mbl.company_id), Database record IDs.';
        $lines[] = '';
        $lines[] = 'EXTRACTABLE FIELDS, UNITS, AND AUTHORITY RULES:';

        foreach ($fields as $field) {
            if ($field->isInternalWorkflow()) {
                continue;
            }

            $ruleDesc = "- {$field->path} ({$field->type})";
            if ($field->unit) {
                $ruleDesc .= " [Unit: {$field->unit}]";
            }
            if ($field->enumSource) {
                $ruleDesc .= " [Allowed values: {$field->enumSource}]";
            }
            if ($field->authorityPriority) {
                $ruleDesc .= " [Authority Priority: {$field->authorityPriority}]";
            }
            if ($field->description) {
                $ruleDesc .= ": {$field->description}";
            }
            $lines[] = $ruleDesc;
        }

        return implode("\n", $lines);
    }

    protected static function mapFieldToSchemaProperty(FieldDefinition $field): array
    {
        $prop = [
            'description' => $field->description ?: $field->path,
            'nullable' => true,
        ];

        switch ($field->type) {
            case 'integer':
                $prop['type'] = 'integer';
                break;
            case 'number':
                $prop['type'] = 'number';
                break;
            case 'boolean':
                $prop['type'] = 'boolean';
                break;
            case 'date':
                $prop['type'] = 'string';
                $prop['description'] .= ' (Format: YYYY-MM-DD)';
                break;
            case 'string':
            default:
                $prop['type'] = 'string';
                if ($field->enumSource) {
                    $prop['enum'] = array_map('trim', explode(',', $field->enumSource));
                }
                break;
        }

        return $prop;
    }
}
