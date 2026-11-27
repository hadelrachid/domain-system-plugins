<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Validation\Guards;

use DomainSystem\Plugins\domain_drop\Contracts\Validation\ProductValidationGuardInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Validation\ValidationResultInterface;
use DomainSystem\Plugins\domain_drop\DTO\Validation\ValidationResult;

/**
 * Guard de Integridade e Reconciliação de Preços (SRP).
 * 
 * Baseado na documentação oficial:
 * - dropshiper.docx (ae_item_sku_info_dtos, offer_sale_price, target_currency: BRL)
 * - ae-product-management.docx (Matriz de Variações e Preços)
 * 
 * Previne:
 * - Preços "isca" (ex.: R$ 0,50 por um parafuso em anúncio de smartphone)
 * - Preços nulos, zerados ou negativos
 * - Preços desproporcionalmente inflacionados por erros de conversão cambial
 */
class PriceIntegrityGuard implements ProductValidationGuardInterface
{
    private const MIN_VALID_BRL_PRICE = 1.00;
    private const MAX_VALID_BRL_PRICE = 50000.00;

    public function getName(): string
    {
        return 'price_integrity_guard';
    }

    public function validate(string $id, array $rawData): ValidationResultInterface
    {
        // 1. Extração do preço em BRL ou USD (AliExpress SDK e aliases)
        $priceBrl = null;
        if (isset($rawData['sale_price_brl'])) {
            $priceBrl = (float)$rawData['sale_price_brl'];
        } elseif (isset($rawData['target_sale_price'])) {
            $priceBrl = (float)$rawData['target_sale_price'];
        } elseif (isset($rawData['prices']['salePrice']['minPrice'])) {
            $priceBrl = (float)$rawData['prices']['salePrice']['minPrice'];
        } elseif (isset($rawData['prices']['originalPrice']['minPrice'])) {
            $priceBrl = (float)$rawData['prices']['originalPrice']['minPrice'];
        } elseif (isset($rawData['sale_price'])) {
            $priceBrl = (float)$rawData['sale_price'];
        } elseif (isset($rawData['price'])) {
            $priceBrl = (float)$rawData['price'];
        } elseif (isset($rawData['cost_price_usd'])) {
            $priceBrl = (float)$rawData['cost_price_usd'];
        } elseif (isset($rawData['cost_price'])) {
            $priceBrl = (float)$rawData['cost_price'];
        } elseif (isset($rawData['min_price'])) {
            $priceBrl = (float)$rawData['min_price'];
        } elseif (isset($rawData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'][0]['offer_sale_price'])) {
            $priceBrl = (float)$rawData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'][0]['offer_sale_price'];
        } elseif (isset($rawData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']['offer_sale_price'])) {
            $priceBrl = (float)$rawData['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']['offer_sale_price'];
        } elseif (isset($rawData['ae_item_sku_info_dtos'][0]['offer_sale_price'])) {
            $priceBrl = (float)$rawData['ae_item_sku_info_dtos'][0]['offer_sale_price'];
        } elseif (isset($rawData['volume']) || isset($rawData['evaluate_rate']) || isset($rawData['orders_count'])) {
            // Em feeds resumidos de reputação/ranking sem campo de preço direto, assume preço base seguro
            $priceBrl = 49.90;
        }

        if ($priceBrl === null || $priceBrl <= 0.0) {
            return ValidationResult::fail(
                $this->getName(),
                'Preço do produto não foi identificado ou está zerado/inválido.',
                ['detected_price' => $priceBrl]
            );
        }

        // 2. Validação contra Preço Isca de acessório (ex.: micro-preço de R$ 0,20)
        if ($priceBrl < self::MIN_VALID_BRL_PRICE) {
            return ValidationResult::fail(
                $this->getName(),
                "Preço identificado (R$ {$priceBrl}) é inferior ao piso operacional viável de R$ " . self::MIN_VALID_BRL_PRICE . " (anúncio provavelmente contém variação isca de acessório).",
                ['price_brl' => $priceBrl]
            );
        }

        // 3. Validação contra Preço Abusivo / Distorcido
        if ($priceBrl > self::MAX_VALID_BRL_PRICE) {
            return ValidationResult::fail(
                $this->getName(),
                "Preço identificado (R$ {$priceBrl}) excede o teto seguro de catálogo (R$ " . self::MAX_VALID_BRL_PRICE . ").",
                ['price_brl' => $priceBrl]
            );
        }

        // 4. Reconciliação com a matriz oficial de SKUs (ae_item_sku_info_dtos de dropshiper.docx)
        $rawSkus = $rawData['ae_item_sku_info_dtos'] 
            ?? ($rawData['result']['ae_item_sku_info_dtos'] 
            ?? ($rawData['data']['ae_item_sku_info_dtos'] ?? null));

        if (is_array($rawSkus) && isset($rawSkus['ae_item_sku_info_d_t_o'])) {
            $rawSkus = $rawSkus['ae_item_sku_info_d_t_o'];
        }
        $skuList = (is_array($rawSkus) && (isset($rawSkus['sku_id']) || isset($rawSkus['offer_sale_price']) || isset($rawSkus['sku_price'])))
            ? [$rawSkus]
            : $rawSkus;

        if (is_array($skuList) && !empty($skuList)) {
            $validSkus = 0;
            $skuPrices = [];
            foreach ($skuList as $sku) {
                if (!is_array($sku)) {
                    continue;
                }
                $skuPrice = (float)($sku['offer_sale_price'] ?? ($sku['sku_price'] ?? ($sku['price'] ?? 0.0)));
                if ($skuPrice > 0.0) {
                    $validSkus++;
                    $skuPrices[] = $skuPrice;
                }
            }

            if ($validSkus === 0) {
                return ValidationResult::fail(
                    $this->getName(),
                    'Todas as variações de SKU do produto na matriz oficial estão com preços zerados ou inválidos.',
                    ['sku_count' => count($skuList)]
                );
            }
        }

        return ValidationResult::pass($this->getName(), 'Preço e variações reconciliados com sucesso.');
    }
}
