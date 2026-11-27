<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Cards;

use DomainSystem\Plugins\domain_drop\Contracts\ProductCardInterface;
use DomainSystem\Plugins\domain_drop\DTO\DropshipProductDto;

/**
 * Fábrica responsável por resolver e instanciar a especialização adequada de ProductCard
 * com base na categoria, atributos e características do produto minerado (GoF Factory Pattern).
 */
class ProductCardFactory
{
    /**
     * Instancia a especialização correta de Card para o produto.
     */
    public static function create(DropshipProductDto $product, array $options = []): ProductCardInterface
    {
        $category = mb_strtolower($product->category ?? '');
        $title = mb_strtolower($product->title ?? '');
        $attrs = $product->attributes ?? [];

        // 1. Estoque Nacional / Remessa BR
        $isNational = !empty($attrs['origem_brasil'])
            || !empty($attrs['national_stock'])
            || ($attrs['ship_from'] ?? '') === 'BR'
            || str_contains($title, 'estoque no brasil')
            || str_contains($title, 'envio nacional')
            || str_contains($title, 'pronta entrega');

        if ($isNational) {
            return new NationalStockProductCard($product, $options);
        }

        // 2. Moda, Vestuário, Fitness, Calçados, Acessórios Pessoais
        $fashionKeywords = [
            'moda', 'roupa', 'vestuário', 'vestuario', 'camisa', 'camiseta', 'calça', 'calca',
            'vestido', 'short', 'lingerie', 'fitness', 'tênis', 'tenis', 'sapato', 'sandália',
            'sandalia', 'cueca', 'sutiã', 'sutia', 'jaqueta', 'casaco', 'biquini', 'maiô'
        ];
        $isFashion = false;
        foreach ($fashionKeywords as $kw) {
            if (str_contains($category, $kw) || str_contains($title, $kw)) {
                $isFashion = true;
                break;
            }
        }
        if ($isFashion || !empty($attrs['sizes']) || !empty($attrs['colors'])) {
            return new FashionProductCard($product, $options);
        }

        // 3. Eletrônicos, Gadgets, Smartphones, Smartwatch, Games, Tecnologia
        $techKeywords = [
            'eletrônico', 'eletronico', 'celular', 'smartphone', 'smartwatch', 'fone', 'bluetooth',
            'carregador', 'cabo', 'computador', 'teclado', 'mouse', 'gamer', 'gadget', 'câmera',
            'camera', 'dvr', 'led', 'smart', 'drone', 'tablet', 'headset', 'áudio', 'audio'
        ];
        $isTech = false;
        foreach ($techKeywords as $kw) {
            if (str_contains($category, $kw) || str_contains($title, $kw)) {
                $isTech = true;
                break;
            }
        }
        if ($isTech || !empty($attrs['video_ad_url']) || !empty($attrs['has_video'])) {
            return new TechProductCard($product, $options);
        }

        // 4. Card Padrão para Utilidades, Decoração, Ferramentas e Casa
        return new StandardProductCard($product, $options);
    }

    /**
     * Renderiza o Card de forma transparente e segura.
     */
    public static function render(DropshipProductDto $product, array $options = []): string
    {
        return self::create($product, $options)->render();
    }
}
