<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Cards;

/**
 * Especialização de Card para Produtos com Estoque e Despacho no Brasil (Entreposto Nacional).
 * 
 * Melhorias do filho:
 * - Destaque verde esmeralda de envio rápido nacional sem taxas alfandegárias.
 * - Priorização de entrega rápida pelos Correios / Transportadoras brasileiras.
 */
class NationalStockProductCard extends AbstractProductCard
{
    protected function getCardBorderColor(bool $isLive): string
    {
        return $isLive ? 'rgba(16, 185, 129, 0.7)' : 'rgba(245, 158, 11, 0.7)';
    }

    protected function getCardCssClass(): string
    {
        return 'card-product-national';
    }

    protected function getCategoryIconHtml(): string
    {
        return '<span>🇧🇷</span> ';
    }

    protected function renderSpecializedTopBadges(): string
    {
        return '<div style="background: rgba(16, 185, 129, 0.3); border: 1px solid rgba(16, 185, 129, 0.6); color: #34d399; padding: 2px 7px; border-radius: 4px; font-size: 9.5px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;" title="Estoque no Brasil com Envio Expresso em 24-48 horas"><i class="fas fa-shipping-fast"></i> Estoque Brasil</div>';
    }

    protected function renderSpecializedAttributes(): string
    {
        return '<div style="margin-bottom: 8px; padding: 5px 8px; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 5px; font-size: 10.5px; color: #34d399; font-weight: 700; display: flex; align-items: center; gap: 5px;"><i class="fas fa-check-double"></i> Sem taxa de importação • Despacho imediato 24h</div>';
    }
}
