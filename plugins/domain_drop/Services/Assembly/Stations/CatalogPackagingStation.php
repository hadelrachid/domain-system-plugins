<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Assembly\Stations;

use DomainSystem\Plugins\domain_drop\Contracts\Assembly\AssemblyStationInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Assembly\ProductAssemblyContextInterface;

/**
 * Estação 6: Empacotamento Final para Catálogo e Vitrine
 *
 * Unifica todos os atributos enriquecidos no formato esperado pela camada de apresentação e persistência.
 */
class CatalogPackagingStation implements AssemblyStationInterface
{
    public function getName(): string
    {
        return 'CatalogPackaging';
    }

    public function process(ProductAssemblyContextInterface $context): ProductAssemblyContextInterface
    {
        $id = (string)$context->get('id');
        $title = (string)$context->get('title');
        $mainImage = (string)$context->get('main_image');
        $costBrl = (float)$context->get('cost_brl');
        $priceBrl = (float)$context->get('price_brl');
        $profitBrl = (float)$context->get('profit_brl');
        $marginPercent = (float)$context->get('margin_percent');
        $origin = (string)$context->get('warehouse_origin', 'UNKNOWN');
        $categoryName = (string)$context->get('category_name', 'Geral');

        $rating = (float)$context->get('rating', $context->get('evaluation_rate', 4.8));
        $ordersCount = (int)$context->get('orders_count', $context->get('sales_count', 0));
        $stock = (int)$context->get('stock', 100);

        // Montagem do payload canônico compatível com as views e repositórios
        $packaged = [
            'id' => $id,
            'productId' => $id,
            'title' => $title,
            'image' => $mainImage,
            'image_url' => $mainImage,
            'images' => $context->get('images', [$mainImage]),
            'cost_usd' => (float)$context->get('raw_price', 0.0),
            'cost_brl' => $costBrl,
            'price_brl' => $priceBrl,
            'price' => $priceBrl,
            'suggested_price' => $priceBrl,
            'profit_brl' => $profitBrl,
            'margin_percent' => $marginPercent,
            'warehouse_origin' => $origin,
            'ship_from' => $origin,
            'is_national' => ($origin === 'BR'),
            'category_name' => $categoryName,
            'category' => $categoryName,
            'rating' => $rating,
            'orders_count' => $ordersCount,
            'stock' => $stock,
            'skus' => $context->get('skus', []),
            'history' => $context->getHistory(),
            'processed_at' => date('Y-m-d H:i:s')
        ];

        $context->set('packaged_product', $packaged);

        return $context->recordStationPass($this->getName(), [
            'ready_for_catalog' => true,
            'product_id' => $id
        ]);
    }
}
