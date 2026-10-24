<?php

namespace App\Services\FrexoAi\Validation;

class ValidationResult
{
    public const STATUS_PASS = 'pass';

    public const STATUS_WARN = 'warn';

    public const STATUS_FAIL = 'fail';

    public string $status;

    public string $code;

    public string $message;

    public string $fieldPath;

    public mixed $normalizedValue;

    public function __construct(
        string $status,
        string $code,
        string $message,
        string $fieldPath = '',
        mixed $normalizedValue = null
    ) {
        $this->status = $status;
        $this->code = $code;
        $this->message = $message;
        $this->fieldPath = $fieldPath;
        $this->normalizedValue = $normalizedValue;
    }

    public static function pass(string $fieldPath = '', mixed $normalizedValue = null, string $code = 'VALID'): self
    {
        return new self(self::STATUS_PASS, $code, 'Valid', $fieldPath, $normalizedValue);
    }

    public static function warn(string $code, string $message, string $fieldPath = '', mixed $normalizedValue = null): self
    {
        return new self(self::STATUS_WARN, $code, $message, $fieldPath, $normalizedValue);
    }

    public static function fail(string $code, string $message, string $fieldPath = '', mixed $normalizedValue = null): self
    {
        return new self(self::STATUS_FAIL, $code, $message, $fieldPath, $normalizedValue);
    }

    public function isPass(): bool
    {
        return $this->status === self::STATUS_PASS;
    }

    public function isWarn(): bool
    {
        return $this->status === self::STATUS_WARN;
    }

    public function isFail(): bool
    {
        return $this->status === self::STATUS_FAIL;
    }
}
