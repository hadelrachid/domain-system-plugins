<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Assembly\Stations;

use DomainSystem\Plugins\domain_drop\Contracts\Assembly\AssemblyStationInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Assembly\ProductAssemblyContextInterface;

/**
 * Estação 1: Normalização de Candidatos
 *
 * Valida a integridade mínima dos dados de entrada (ID, título inicial e estrutura).
 * Documentação de referência: dropshiper.pdf
 */
class CandidateNormalizationStation implements AssemblyStationInterface
{
    public function getName(): string
    {
        return 'CandidateNormalization';
    }

    public function process(ProductAssemblyContextInterface $context): ProductAssemblyContextInterface
    {
        $raw = $context->getRawData();

        $id = $raw['id'] 
            ?? $raw['productId'] 
            ?? $raw['product_id'] 
            ?? $raw['itemId'] 
            ?? $raw['item_id'] 
            ?? ($raw['ae_item_base_info_dto']['product_id'] ?? null);

        if (empty($id) || (!is_numeric($id) && !is_string($id))) {
            return $context->abort($this->getName(), 'Identificador do produto ausente ou inválido');
        }

        $id = (string) $id;
        $title = trim((string)(
            $raw['title'] 
            ?? $raw['product_title'] 
            ?? $raw['subject'] 
            ?? ($raw['ae_item_base_info_dto']['subject'] ?? '')
        ));

        if ($title === '') {
            $title = 'Produto ' . $id;
        }

        // Sanitização básica do título e dados essenciais
        $context->set('id', $id);
        $context->set('productId', $id);
        $context->set('title', $title);
        $context->set('original_title', $title);
        $context->set('category_id', $raw['categoryId'] ?? $raw['category_id'] ?? null);
        $context->set('category_name', $raw['category_name'] ?? $raw['category'] ?? 'Geral');

        // Pré-extração de imagem se já disponível no candidato
        $img = trim((string)(
            $raw['itemMainPic'] 
            ?? $raw['product_main_image_url'] 
            ?? $raw['image_url'] 
            ?? $raw['pic_url'] 
            ?? ($raw['image'] ?? '')
        ));
        if ($img !== '') {
            $context->set('main_image', $img);
            $context->set('image_url', $img);
        }

        // Pré-extração de preço de custo se já disponível
        $candidatePrice = (float)(
            $raw['targetSalePrice'] 
            ?? ($raw['target_sale_price']
            ?? ($raw['salePrice'] 
            ?? ($raw['sale_price']
            ?? ($raw['cost_price_usd'] 
            ?? ($raw['cost_usd']
            ?? ($raw['price'] ?? 0.0))))))
        );
        if ($candidatePrice > 0.0) {
            $context->set('raw_price', $candidatePrice);
            $context->set('cost_price_usd', $candidatePrice);
            $context->set('price', $candidatePrice);
        }

        // Informações de avaliação e volume se disponíveis
        $rating = $raw['evaluateRate'] ?? ($raw['evaluate_rate'] ?? ($raw['rating'] ?? null));
        if ($rating !== null && is_numeric($rating)) {
            $context->set('rating', (float)$rating);
        }
        $orders = $raw['orders'] ?? ($raw['volume'] ?? ($raw['orders_count'] ?? null));
        if ($orders !== null && is_numeric($orders)) {
            $context->set('orders_count', (int)$orders);
        }

        // Preservação de atributos e estruturas pré-existentes do candidato
        if (!empty($raw['variants'])) {
            $context->set('variants', $raw['variants']);
        }
        if (!empty($raw['variation_axes'])) {
            $context->set('variation_axes', $raw['variation_axes']);
        }
        if (!empty($raw['ae_item_sku_info_dtos'])) {
            $context->set('ae_item_sku_info_dtos', $raw['ae_item_sku_info_dtos']);
        }
        if (!empty($raw['ae_item_property_dtos'])) {
            $context->set('ae_item_property_dtos', $raw['ae_item_property_dtos']);
        }
        if (!empty($raw['properties'])) {
            $context->set('properties', $raw['properties']);
        }
        if (!empty($raw['attributes'])) {
            $context->set('attributes', $raw['attributes']);
        }
        if (!empty($raw['size_chart'])) {
            $context->set('size_chart', $raw['size_chart']);
        }
        if (!empty($raw['sellingPoints'])) {
            $context->set('sellingPoints', $raw['sellingPoints']);
        }
        if (!empty($raw['store_info'])) {
            $context->set('store_info', $raw['store_info']);
        }

        return $context->recordStationPass($this->getName(), [
            'id' => $id,
            'title_length' => mb_strlen($title)
        ]);
    }
}
