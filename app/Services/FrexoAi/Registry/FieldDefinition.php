<?php

namespace App\Services\FrexoAi\Registry;

class FieldDefinition
{
    public const SCOPE_SHIPMENT = 'shipment';
    public const SCOPE_MASTER = 'master';
    public const SCOPE_HOUSE = 'house';
    public const SCOPE_CONTAINER = 'container';
    public const SCOPE_LEG = 'leg';
    public const SCOPE_PARTY = 'party';

    public const POLICY_EXTRACTABLE = 'DOCUMENT_EXTRACTABLE';
    public const POLICY_TRUSTED_CONTEXT = 'TRUSTED_CONTEXT_ONLY';
    public const POLICY_INTERNAL_WORKFLOW = 'INTERNAL_WORKFLOW';
    public const POLICY_CALCULATED = 'CALCULATED';

    public const OVERWRITE_NEVER = 'never';
    public const OVERWRITE_ONLY_IF_EMPTY = 'only_if_empty';
    public const OVERWRITE_USER_CONFIRMED = 'user_confirmed';

    public string $path;
    public string $type; // string, number, integer, boolean, date, array, object
    public string $scope;
    public ?string $unit;
    public string $sourcePolicy;
    public string $overwritePolicy;
    public ?string $enumSource;
    public ?string $normalizer;
    public ?string $validator;
    public int $authorityPriority; // higher number = higher document authority
    public string $description;

    public function __construct(
        string $path,
        string $type,
        string $scope,
        string $sourcePolicy = self::POLICY_EXTRACTABLE,
        string $overwritePolicy = self::OVERWRITE_ONLY_IF_EMPTY,
        ?string $unit = null,
        ?string $enumSource = null,
        ?string $normalizer = null,
        ?string $validator = null,
        int $authorityPriority = 50,
        string $description = ''
    ) {
        $this->path = $path;
        $this->type = $type;
        $this->scope = $scope;
        $this->sourcePolicy = $sourcePolicy;
        $this->overwritePolicy = $overwritePolicy;
        $this->unit = $unit;
        $this->enumSource = $enumSource;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->authorityPriority = $authorityPriority;
        $this->description = $description;
    }

    public function isInternalWorkflow(): bool
    {
        return $this->sourcePolicy === self::POLICY_INTERNAL_WORKFLOW;
    }

    public function isExtractable(): bool
    {
        return $this->sourcePolicy === self::POLICY_EXTRACTABLE;
    }
}
