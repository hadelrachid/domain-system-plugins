<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Contracts;

/**
 * Contrato para Resolução e Identificação Rigorosa do Depósito / Entreposto Logístico (SRP / DIP).
 * 
 * Separa país do vendedor (seller_country), país de destino (ship_to) e país físico do estoque (warehouse_country).
 * Classifica a procedência real em 3 estados contratuais:
 * - 'BR': Estoque Físico Nacional Comprovado (Local Supply / Entreposto Brasil / local_flag).
 * - 'CN': Entreposto Central de Fabricação Cross-Border (China / Remessa Conforme DDP).
 * - 'UNKNOWN': Procedência logística não comprovada documentalmente.
 */
interface WarehouseOriginResolverInterface
{
    public const ORIGIN_BR = 'BR';
    public const ORIGIN_CN = 'CN';
    public const ORIGIN_UNKNOWN = 'UNKNOWN';

    public const MODE_BR = 'BR';
    public const MODE_CN = 'CN';
    public const MODE_ALL = 'ALL';

    /**
     * Resolve a procedência logística do produto com base nos metadados estruturados.
     *
     * @param array<string, mixed> $rawItem Dados brutos retornados pela API ou pelo motor de busca.
     * @param string|null $title Título bruto do produto para análise secundária de pronta entrega.
     * @return string 'BR', 'CN' ou 'UNKNOWN'.
     */
    public function resolve(array $rawItem, ?string $title = null): string;

    /**
     * Retorna se o item possui estoque comprovadamente localizado no Brasil.
     *
     * @param array<string, mixed> $rawItem
     * @param string|null $title
     * @return bool
     */
    public function isShippedFromBrazil(array $rawItem, ?string $title = null): bool;
}
