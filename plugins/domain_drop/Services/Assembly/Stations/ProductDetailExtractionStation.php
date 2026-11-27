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
        $price = (float) $context->get('price', $context->get('target_sale_price', $context->get('cost_price_usd', 0.0)));
        $images = $context->get('images', []);
        $mainImage = (string) $context->get('main_image', $context->get('image_url', ''));

        // Se o cliente da API estiver disponível e faltarem detalhes críticos, consulta a API oficial
        if ($this->apiClient !== null && (empty($skus) || $price <= 0.0 || empty($mainImage))) {
            try {
                if (method_exists($this->apiClient, 'getProductDetails')) {
                    $detailResponse = $this->apiClient->getProductDetails($id);
                } elseif (method_exists($this->apiClient, 'getProductDetail')) {
                    $detailResponse = $this->apiClient->getProductDetail($id);
                } else {
                    $detailResponse = [];
                }
                $result = $detailResponse['aliexpress_ds_product_get_response']['result'] ?? $detailResponse['result'] ?? null;

                if (!$result) {
                    // Se a API não retornou detalhes mas o produto já possui preço e imagem válidos (ex: fallback curado), prossegue sem abortar
                    if ($price <= 0.0 || empty($mainImage)) {
                        return $context->abort($this->getName(), "Produto {$id} não retornou detalhes ou foi removido (404)");
                    }
                } else {
                    $title = $result['item_title'] ?? $result['subject'] ?? $result['product_title'] ?? $context->get('title');
                    $context->set('title', $title);

                    // Imagem principal e galeria
                    if (!empty($result['item_main_image_url'])) {
                        $mainImage = $result['item_main_image_url'];
                    } elseif (!empty($result['product_main_image_url'])) {
                        $mainImage = $result['product_main_image_url'];
                    } elseif (!empty($result['image_url'])) {
                        $mainImage = $result['image_url'];
                    }
                    if (!empty($result['item_image_urls'])) {
                        $images = is_array($result['item_image_urls']) ? $result['item_image_urls'] : explode(';', (string)$result['item_image_urls']);
                    }

                    // Preço bruto vindo da resposta do detalhe
                    $resPrice = (float)($result['target_sale_price'] ?? ($result['targetSalePrice'] ?? ($result['sale_price'] ?? ($result['salePrice'] ?? ($result['price'] ?? 0.0)))));
                    if ($price <= 0.0 && $resPrice > 0.0) {
                        $price = $resPrice;
                    }

                    // Extração de SKUs
                    $skuMatrix = $result['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'] ?? [];
                    if (!empty($skuMatrix)) {
                        $parsedSkus = [];
                        foreach ($skuMatrix as $sku) {
                            $skuPrice = (float)($sku['offer_sale_price'] ?? $sku['sku_price'] ?? 0.0);
                            $skuStock = (int)($sku['sku_available_stock'] ?? $sku['ipm_sku_stock'] ?? 0);
                            $skuId = (string)($sku['sku_id'] ?? '');

                            $parsedSku = $sku;
                            $parsedSku['sku_id'] = $skuId;
                            $parsedSku['price'] = $skuPrice;
                            $parsedSku['offer_sale_price'] = $skuPrice;
                            $parsedSku['sku_price'] = $skuPrice;
                            $parsedSku['stock'] = $skuStock;
                            $parsedSku['sku_available_stock'] = $skuStock;
                            $parsedSku['sku_attr'] = $sku['sku_attr'] ?? '';
                            $parsedSkus[] = $parsedSku;

                            if ($price <= 0.0 && $skuPrice > 0.0) {
                                $price = $skuPrice;
                            }
                        }
                        $context->set('skus', $parsedSkus);
                        $context->set('ae_item_sku_info_dtos', $result['ae_item_sku_info_dtos']);
                    }

                    if (!empty($result['store_info'])) {
                        $context->set('store_info', $result['store_info']);
                    }
                    if (!empty($result['ae_store_info'])) {
                        $context->set('store_info', $result['ae_store_info']);
                        $context->set('ae_store_info', $result['ae_store_info']);
                    }

                    // Extração de Propriedades Técnicas Oficiais (ae_item_property_dtos)
                    if (!empty($result['ae_item_property_dtos'])) {
                        $context->set('ae_item_property_dtos', $result['ae_item_property_dtos']);
                    }
                    if (!empty($result['properties'])) {
                        $context->set('properties', $result['properties']);
                    }

                    // Extração de Guia e Tabela de Tamanhos Oficial
                    $sizeChartData = $result['size_chart'] ?? ($result['size_chart_info'] ?? ($result['size_chart_url'] ?? null));
                    if (!empty($sizeChartData)) {
                        $context->set('size_chart', $sizeChartData);
                    }

                    // Extração de Destaques Comerciais
                    if (!empty($result['sellingPoints'])) {
                        $context->set('sellingPoints', $result['sellingPoints']);
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
                }
            } catch (\Throwable $e) {
                // Se falhar e não tivermos dados prévios válidos, aborta
                if ($price <= 0.0 || empty($mainImage)) {
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
