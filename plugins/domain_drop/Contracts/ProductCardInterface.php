<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Contracts;

use DomainSystem\Plugins\domain_drop\DTO\DropshipProductDto;

/**
 * Contrato base para Renderizadores de Cards de Produtos no DomainDrop (SOLID / OCP / ISP).
 */
interface ProductCardInterface
{
    /**
     * Retorna a entidade DTO do produto associada a este card.
     */
    public function getProduct(): DropshipProductDto;

    /**
     * Retorna as opções contextuais de renderização (baseUrl, rate, categories, etc.).
     * 
     * @return array<string, mixed>
     */
    public function getOptions(): array;

    /**
     * Renderiza o HTML completo e semântico do card do produto.
     */
    public function render(): string;
}
