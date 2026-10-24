<?php

namespace App\Services\FrexoAi\DTO;

class ConflictCandidateDTO
{
    public string $fieldPath;
    public string $scope;
    public mixed $proposedValue;
    public array $candidates; // array of ['value' => ..., 'source' => [...]]
    public string $reason;

    public function __construct(
        string $fieldPath,
        string $scope,
        mixed $proposedValue,
        array $candidates = [],
        string $reason = ''
    ) {
        $this->fieldPath = $fieldPath;
        $this->scope = $scope;
        $this->proposedValue = $proposedValue;
        $this->candidates = $candidates;
        $this->reason = $reason;
    }

    public function toArray(): array
    {
        return [
            'field_path' => $this->fieldPath,
            'scope' => $this->scope,
            'proposed_value' => $this->proposedValue,
            'candidates' => $this->candidates,
            'reason' => $this->reason,
        ];
    }
}
