<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Contracts\Validation;

/**
 * Interface para Guards individuais do Pipeline de Validação (SRP).
 * 
 * Cada Guard inspeciona uma dimensão contratual única baseada na documentação oficial:
 * - Existência real e status de venda (dropshiper.docx / ae-product-management.docx)
 * - Rota logística e destino Brasil (ae-freight-shipment.docx / aliexpress-direct-logistic.docx)
 * - Reconciliação de precificação e matriz SKU (ae-product-management.docx)
 * - Integridade de títulos, imagens CDN e categorias (ae-image.docx / categoria-e-atributos.docx)
 */
interface ProductValidationGuardInterface
{
    /**
     * Retorna o nome identificador do Guard.
     */
    public function getName(): string;

    /**
     * Executa a inspeção contratual sobre o item bruto minerado.
     *
     * @param string $id Identificador do anúncio
     * @param array<string, mixed> $rawData Carga bruta do anúncio
     */
    public function validate(string $id, array $rawData): ValidationResultInterface;
}
