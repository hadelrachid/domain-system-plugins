<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Assembly\Stations;

use DomainSystem\Plugins\domain_drop\Contracts\Assembly\AssemblyStationInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Assembly\ProductAssemblyContextInterface;
use DomainSystem\Plugins\domain_drop\Contracts\AliExpressApiClientInterface;

/**
 * Estação 2: Extração de Detalhes e SKUs
 *
 * Obtém a árvore completa do produto (preço de custo original, variações de SKU e estoque).
 * Documentação de referência: ae-product-management.pdf (aliexpress.ds.product.get)
 */
class ProductDetailExtractionStation implements AssemblyStationInterface
{
    private ?AliExpressApiClientInterface $apiClient;

    public function __construct(?AliExpressApiClientInterface $apiClient = null)
    {
        $this->apiClient = $apiClient;
    }

    public function getName(): string
    {
        return 'ProductDetailExtraction';
    }

    public function process(ProductAssemblyContextInterface $context): ProductAssemblyContextInterface
    {
        $id = (string) $context->get('id');

        // Se já tiver dados enriquecidos de SKU no contexto, aproveita
        $skus = $context->get('skus', []);
        $price = (float) $context->get('price', $context->get('target_sale_price', 0.0));
        $images = $context->get('images', []);
        $mainImage = (string) $context->get('main_image', $context->get('image_url', ''));

        // Se o cliente da API estiver disponível e faltarem detalhes críticos, consulta a API oficial
        if ($this->apiClient !== null && (empty($skus) || $price <= 0.0 || empty($mainImage))) {
            try {
                $detailResponse = $this->apiClient->getProductDetail($id);
                $result = $detailResponse['aliexpress_ds_product_get_response']['result'] ?? $detailResponse['result'] ?? null;

                if (!$result) {
                    // Produto inexistente ou indisponível na plataforma
                    return $context->abort($this->getName(), "Produto {$id} não retornou detalhes ou foi removido (404)");
                }

                $title = $result['item_title'] ?? $result['subject'] ?? $context->get('title');
                $context->set('title', $title);

                // Imagem principal e galeria
                if (!empty($result['item_main_image_url'])) {
                    $mainImage = $result['item_main_image_url'];
                }
                if (!empty($result['item_image_urls'])) {
                    $images = is_array($result['item_image_urls']) ? $result['item_image_urls'] : explode(';', (string)$result['item_image_urls']);
                }

                // Extração de SKUs
                $skuMatrix = $result['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? [];
                if (!empty($skuMatrix)) {
                    $parsedSkus = [];
                    foreach ($skuMatrix as $sku) {
                        $skuPrice = (float)($sku['offer_sale_price'] ?? $sku['sku_price'] ?? 0.0);
                        $skuStock = (int)($sku['sku_available_stock'] ?? $sku['ipm_sku_stock'] ?? 0);
                        $skuId = (string)($sku['sku_id'] ?? '');

                        $parsedSkus[] = [
                            'sku_id' => $skuId,
                            'price' => $skuPrice,
                            'stock' => $skuStock,
                            'sku_attr' => $sku['sku_attr'] ?? ''
                        ];

                        if ($price <= 0.0 && $skuPrice > 0.0) {
                            $price = $skuPrice;
                        }
                    }
                    $context->set('skus', $parsedSkus);
                }

                if (!empty($result['store_info'])) {
                    $context->set('store_info', $result['store_info']);
                }

                // Extração Oficial de Vídeo Demonstrativo (ae-product-management.pdf / ae_multimedia_info_dto)
                $videoUrl = (string)$context->get('video_url', '');
                if (empty($videoUrl) && !empty($result['ae_multimedia_info_dto']['ae_video_dtos'])) {
                    $vDtos = $result['ae_multimedia_info_dto']['ae_video_dtos'];
                    $vItem = $vDtos['ae_video_d_t_o'] ?? ($vDtos[0] ?? null);
                    if (is_array($vItem)) {
                        $firstV = isset($vItem[0]) ? $vItem[0] : $vItem;
                        if (!empty($firstV['media_url'])) {
                            $videoUrl = (string)$firstV['media_url'];
                        }
                    }
                }
                if (empty($videoUrl) && !empty($result['product_video_url'])) {
                    $videoUrl = (string)$result['product_video_url'];
                }
                if (!empty($videoUrl)) {
                    if (str_starts_with($videoUrl, '//')) {
                        $videoUrl = 'https:' . $videoUrl;
                    }
                    $context->set('video_url', $videoUrl);
                }
            } catch (\Throwable $e) {
                // Se falhar e não tivermos dados prévios válidos, aborta
                if ($price <= 0.0) {
                    return $context->abort($this->getName(), "Falha ao extrair detalhes do produto {$id}: " . $e->getMessage());
                }
            }
        }

        $context->set('main_image', $mainImage);
        $context->set('images', $images);
        $context->set('raw_price', $price);

        return $context->recordStationPass($this->getName(), [
            'has_skus' => !empty($context->get('skus')),
            'raw_price' => $price,
            'has_images' => !empty($mainImage)
        ]);
    }
}
