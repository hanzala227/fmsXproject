<?php

namespace App\Services\FrexoAi\DTO;

use InvalidArgumentException;

class FieldEvidenceDTO
{
    public const STATUS_EXPLICIT = 'EXPLICIT';
    public const STATUS_DERIVED = 'DERIVED';
    public const STATUS_AMBIGUOUS = 'AMBIGUOUS';
    public const STATUS_CONFLICT = 'CONFLICT';
    public const STATUS_NOT_FOUND = 'NOT_FOUND';
    public const STATUS_UNREADABLE = 'UNREADABLE';
    public const STATUS_NOT_APPLICABLE = 'NOT_APPLICABLE';
    public const STATUS_REVIEW_REQUIRED = 'REVIEW_REQUIRED';

    public static function allowedStatuses(): array
    {
        return [
            self::STATUS_EXPLICIT,
            self::STATUS_DERIVED,
            self::STATUS_AMBIGUOUS,
            self::STATUS_CONFLICT,
            self::STATUS_NOT_FOUND,
            self::STATUS_UNREADABLE,
            self::STATUS_NOT_APPLICABLE,
            self::STATUS_REVIEW_REQUIRED,
        ];
    }

    public string $fieldPath;
    public string $scope;
    public mixed $rawValue;
    public mixed $normalizedValue;
    public ?string $unit;
    public string $status;
    public ?array $source; // ['document_id' => ..., 'document_type' => ..., 'page' => ..., 'evidence_text' => ..., 'region' => ...]
    public ?array $derivation; // ['rule' => ..., 'inputs' => [...]]

    public function __construct(
        string $fieldPath,
        string $scope,
        mixed $rawValue,
        mixed $normalizedValue,
        ?string $unit = null,
        string $status = self::STATUS_EXPLICIT,
        ?array $source = null,
        ?array $derivation = null
    ) {
        if (!in_array($status, self::allowedStatuses(), true)) {
            throw new InvalidArgumentException("Invalid field evidence status: $status");
        }

        if (isset($source['evidence_text']) && is_string($source['evidence_text'])) {
            if (mb_strlen($source['evidence_text']) > 200) {
                $source['evidence_text'] = mb_substr($source['evidence_text'], 0, 200);
            }
        }

        $this->fieldPath = $fieldPath;
        $this->scope = $scope;
        $this->rawValue = $rawValue;
        $this->normalizedValue = $normalizedValue;
        $this->unit = $unit;
        $this->status = $status;
        $this->source = $source;
        $this->derivation = $derivation;
    }

    public function toArray(): array
    {
        return [
            'field_path' => $this->fieldPath,
            'scope' => $this->scope,
            'raw_value' => $this->rawValue,
            'normalized_value' => $this->normalizedValue,
            'unit' => $this->unit,
            'status' => $this->status,
            'source' => $this->source,
            'derivation' => $this->derivation,
        ];
    }
}
