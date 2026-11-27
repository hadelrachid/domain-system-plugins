<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Cards;

/**
 * Especialização de Card para Moda, Roupas, Vestuário, Fitness e Moda Íntima.
 * 
 * Melhorias do filho:
 * - Renderização dinâmica de chips de tamanhos (P, M, G, GG, 36, etc.) e grade de cores.
 * - Ícone semântico de vestuário e badge de curadoria de estilo.
 */
class FashionProductCard extends AbstractProductCard
{
    protected function getCardCssClass(): string
    {
        return 'card-product-fashion';
    }

    protected function getCategoryIconHtml(): string
    {
        return '<i class="fas fa-tshirt" style="color: #f472b6; font-size: 9.5px;"></i> ';
    }

    protected function renderSpecializedTopBadges(): string
    {
        return '<div style="background: rgba(236, 72, 153, 0.25); border: 1px solid rgba(236, 72, 153, 0.5); color: #f472b6; padding: 2px 6px; border-radius: 4px; font-size: 9.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;" title="Curadoria Especial de Moda & Caimento"><i class="fas fa-magic"></i> Moda</div>';
    }

    protected function renderSpecializedAttributes(): string
    {
        $html = '';

        // 1. Extração de Tamanhos
        $sizes = $this->product->attributes['sizes'] ?? [];
        if (is_string($sizes)) {
            $sizes = array_filter(array_map('trim', explode(',', $sizes)));
        }

        // 2. Extração de Cores
        $colors = $this->product->attributes['colors'] ?? [];
        if (is_string($colors)) {
            $colors = array_filter(array_map('trim', explode(',', $colors)));
        }

        if (!empty($sizes) || !empty($colors)) {
            $html .= '<div style="margin-bottom: 8px; padding: 6px 8px; background: rgba(0,0,0,0.22); border-radius: 5px; border: 1px dashed rgba(244, 114, 182, 0.25); display: flex; flex-direction: column; gap: 4px;">';

            if (!empty($sizes)) {
                $html .= '<div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">';
                $html .= '<span style="font-size: 9.5px; color: #f472b6; font-weight: 700; text-transform: uppercase;">Tam:</span>';
                $displayedSizes = array_slice($sizes, 0, 5);
                foreach ($displayedSizes as $s) {
                    $html .= '<span style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 1px 5px; border-radius: 3px; font-size: 10px; font-weight: 600;">' . htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8') . '</span>';
                }
                if (count($sizes) > 5) {
                    $html .= '<span style="font-size: 9.5px; color: #94a3b8;">+' . (count($sizes) - 5) . '</span>';
                }
                $html .= '</div>';
            }

            if (!empty($colors)) {
                $html .= '<div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">';
                $html .= '<span style="font-size: 9.5px; color: #a78bfa; font-weight: 700; text-transform: uppercase;">Cores:</span>';
                $displayedColors = array_slice($colors, 0, 4);
                foreach ($displayedColors as $c) {
                    $html .= '<span style="background: rgba(167, 139, 250, 0.12); border: 1px solid rgba(167, 139, 250, 0.3); color: #c4b5fd; padding: 1px 5px; border-radius: 3px; font-size: 9.5px; font-weight: 600;">' . htmlspecialchars((string)$c, ENT_QUOTES, 'UTF-8') . '</span>';
                }
                if (count($colors) > 4) {
                    $html .= '<span style="font-size: 9.5px; color: #94a3b8;">+' . (count($colors) - 4) . '</span>';
                }
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        return $html;
    }
}
