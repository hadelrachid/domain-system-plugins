<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Contracts\Validation;

/**
 * Interface para resultados individuais de cada Guard do Pipeline de Validação.
 */
interface ValidationResultInterface
{
    /**
     * Informa se o produto foi aprovado no Guard de validação.
     */
    public function isValid(): bool;

    /**
     * Motivo da rejeição ou mensagem informativa de sucesso.
     */
    public function getReason(): string;

    /**
     * Nome do Guard responsável pela validação.
     */
    public function getGuardName(): string;

    /**
     * Detalhes diagnósticos da validação (códigos de erro, payloads).
     *
     * @return array<string, mixed>
     */
    public function getDetails(): array;
}
