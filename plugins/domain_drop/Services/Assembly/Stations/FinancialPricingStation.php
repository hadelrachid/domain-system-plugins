<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Assembly\Stations;

use DomainSystem\Plugins\domain_drop\Contracts\Assembly\AssemblyStationInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Assembly\ProductAssemblyContextInterface;
use DomainSystem\Plugins\domain_drop\Contracts\CurrencyConverterInterface;

/**
 * Estação 5: Motor Financeiro e Precificação Dinâmica
 *
 * Converte moedas e aplica markups, DDP e margem de lucro com total transparência.
 */
class FinancialPricingStation implements AssemblyStationInterface
{
    private ?CurrencyConverterInterface $currencyConverter;
    private ?float $defaultRate;
    private ?float $defaultMarkup;

    public function __construct(
        CurrencyConverterInterface|float|null $currencyConverter = null,
        ?float $rate = null,
        ?float $markup = null
    ) {
        if (is_numeric($currencyConverter)) {
            $this->currencyConverter = null;
            $this->defaultRate = (float)$currencyConverter;
            $this->defaultMarkup = $rate;
        } else {
            $this->currencyConverter = $currencyConverter;
            $this->defaultRate = $rate;
            $this->defaultMarkup = $markup;
        }
    }

    public function getName(): string
    {
        return 'FinancialPricing';
    }

    public function process(ProductAssemblyContextInterface $context): ProductAssemblyContextInterface
    {
        $shared = $context->getSharedOptions();
        $markupMultiplier = (float)($this->defaultMarkup ?? $shared['markup'] ?? 1.4); // 40% margem padrão se não informado
        if ($markupMultiplier <= 0.0) {
            $markupMultiplier = 1.4;
        }

        $rawPrice = (float)$context->get('raw_price', 0.0);
        if ($rawPrice <= 0.0) {
            $rawPrice = (float)$context->get('cost_price_usd', 0.0);
        }
        if ($rawPrice <= 0.0) {
            $rawPrice = (float)$context->get('price', 0.0);
        }

        if ($rawPrice <= 0.0) {
            return $context->abort($this->getName(), 'Preço de custo do produto inválido ou zerado');
        }

        // Câmbio USD -> BRL
        $costBrl = $rawPrice;
        $currency = strtoupper((string)$context->get('currency', 'USD'));

        if ($currency !== 'BRL') {
            if ($this->currencyConverter !== null) {
                $rate = $this->currencyConverter->getExchangeRate($currency, 'BRL');
                if ($rate <= 0.0) {
                    $rate = (float)($this->defaultRate ?? $shared['exchange_rate'] ?? 5.50);
                }
                $costBrl = $rawPrice * $rate;
            } else {
                // Fallback conservador de câmbio
                $exchangeRate = (float)($this->defaultRate ?? $shared['exchange_rate'] ?? 5.50);
                $costBrl = $rawPrice * $exchangeRate;
            }
        }

        // Cálculo de preço de venda e margens
        $costBrl = round($costBrl, 2);
        $salePriceBrl = round($costBrl * $markupMultiplier, 2);
        $profitBrl = round($salePriceBrl - $costBrl, 2);
        $marginPercent = ($salePriceBrl > 0.0) ? round(($profitBrl / $salePriceBrl) * 100, 1) : 0.0;

        $context->set('cost_brl', $costBrl);
        $context->set('cost_price_brl', $costBrl);
        $context->set('price_brl', $salePriceBrl);
        $context->set('sale_price_brl', $salePriceBrl);
        $context->set('suggested_price', $salePriceBrl);
        $context->set('profit_brl', $profitBrl);
        $context->set('margin_percent', $marginPercent);
        $context->set('markup_multiplier', $markupMultiplier);

        return $context->recordStationPass($this->getName(), [
            'cost_brl' => $costBrl,
            'sale_price_brl' => $salePriceBrl,
            'profit_brl' => $profitBrl,
            'margin_percent' => $marginPercent
        ]);
    }
}
