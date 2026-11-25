<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\DTO\Validation;

use DomainSystem\Plugins\domain_drop\Contracts\Validation\ValidationResultInterface;

/**
 * Value Object imutável com o resultado de uma inspeção de Guard.
 */
class ValidationResult implements ValidationResultInterface
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        private readonly bool $valid,
        private readonly string $reason,
        private readonly string $guardName,
        private readonly array $details = []
    ) {
    }

    public static function pass(string $guardName, string $reason = 'Produto aprovado no guard.', array $details = []): self
    {
        return new self(true, $reason, $guardName, $details);
    }

    public static function fail(string $guardName, string $reason, array $details = []): self
    {
        return new self(false, $reason, $guardName, $details);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getGuardName(): string
    {
        return $this->guardName;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
