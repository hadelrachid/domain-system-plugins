<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Contracts;

/**
 * Contrato para fornecimento do catálogo curado oficial e matrizes de SKUs (ISP / DIP).
 */
interface CuratedCatalogProviderInterface
{
    /**
     * Retorna a árvore completa do catálogo curado agrupado por categoria com dados de produtos e SKUs.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getCatalog(): array;
}
