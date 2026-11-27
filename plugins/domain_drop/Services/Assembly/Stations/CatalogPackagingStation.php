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

        $rating = (float)$context->get('rating', $context->get('evaluation_rate', 0.0));
        $ordersCount = (int)$context->get('orders_count', $context->get('sales_count', 0));
        $stock = (int)$context->get('stock', $context->get('inventory', 0));

        $raw = $context->getRawData();

        // Montagem do payload canônico compatível com as views e repositórios
        $packaged = [
            'id' => $id,
            'productId' => $id,
            'aliexpressId' => $id,
            'aliexpress_id' => $id,
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
            'video_url' => (string)$context->get('video_url', ''),
            'skus' => $context->get('skus', []),
            'ae_item_sku_info_dtos' => $context->get('ae_item_sku_info_dtos', $raw['ae_item_sku_info_dtos'] ?? []),
            'variants' => $context->get('variants', $raw['variants'] ?? []),
            'variation_axes' => $context->get('variation_axes', $raw['variation_axes'] ?? []),
            'ae_item_property_dtos' => $context->get('ae_item_property_dtos', $raw['ae_item_property_dtos'] ?? []),
            'properties' => $context->get('properties', $raw['properties'] ?? []),
            'attributes' => $context->get('attributes', $raw['attributes'] ?? []),
            'size_chart' => $context->get('size_chart', $raw['size_chart'] ?? null),
            'sellingPoints' => $context->get('sellingPoints', $raw['sellingPoints'] ?? []),
            'store_info' => $context->get('store_info', $raw['store_info'] ?? []),
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
