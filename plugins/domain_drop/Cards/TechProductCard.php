<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Cards;

/**
 * Especialização de Card para Eletrônicos, Gadgets, Drones, TVs e Smartphones.
 * 
 * Melhorias do filho:
 * - Destaque para especificações técnicas, vídeo de alta definição para anúncios virais
 *   e ícone tecnológico.
 */
class TechProductCard extends AbstractProductCard
{
    protected function getCardCssClass(): string
    {
        return 'card-product-tech';
    }

    protected function getCategoryIconHtml(): string
    {
        return '<i class="fas fa-microchip" style="color: #38bdf8; font-size: 9.5px;"></i> ';
    }

    protected function renderSpecializedTopBadges(): string
    {
        return '<div style="background: rgba(56, 189, 248, 0.2); border: 1px solid rgba(56, 189, 248, 0.45); color: #38bdf8; padding: 2px 6px; border-radius: 4px; font-size: 9.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;" title="Dispositivo Tecnológico Certificado"><i class="fas fa-bolt"></i> Tech</div>';
    }

    protected function renderSpecializedAttributes(): string
    {
        $html = '';
        $hasVideo = !empty($this->product->attributes['video_url']);
        $freteGratis = !empty($this->product->attributes['frete_gratis']);

        if ($hasVideo || $freteGratis) {
            $html .= '<div style="margin-bottom: 8px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">';
            if ($hasVideo) {
                $html .= '<span style="background: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.35); color: #c084fc; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;"><i class="fas fa-video"></i> Vídeo de Anúncio Disponível</span>';
            }
            if ($freteGratis) {
                $html .= '<span style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;"><i class="fas fa-truck"></i> Frete Grátis</span>';
            }
            $html .= '</div>';
        }

        return $html;
    }
}
