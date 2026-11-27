<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Assembly\Stations;

use DomainSystem\Plugins\domain_drop\Contracts\Assembly\AssemblyStationInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Assembly\ProductAssemblyContextInterface;
use DomainSystem\Plugins\domain_drop\Contracts\AliExpressApiClientInterface;
use DomainSystem\Plugins\domain_drop\Contracts\WarehouseOriginResolverInterface;

/**
 * Estação 3: Inspeção de Frete e Entreposto Físico
 *
 * Aplica estritamente o modelo Tri-State (BR, CN, UNKNOWN) e o princípio Zero-Contamination.
 * Documentação de referência: ae-freight-shipment.pdf e ae-logistics.pdf (aliexpress.ds.freight.query)
 */
class FreightInspectionStation implements AssemblyStationInterface
{
    private ?WarehouseOriginResolverInterface $originResolver;
    private ?string $defaultOriginMode;
    private ?AliExpressApiClientInterface $apiClient;

    public function __construct(
        WarehouseOriginResolverInterface|string|null $originResolver = null,
        ?string $warehouseMode = null,
        ?AliExpressApiClientInterface $apiClient = null
    ) {
        if (is_string($originResolver)) {
            $this->originResolver = null;
            $this->defaultOriginMode = $originResolver;
            $this->apiClient = $apiClient;
        } else {
            $this->originResolver = $originResolver;
            $this->defaultOriginMode = $warehouseMode;
            $this->apiClient = $apiClient;
        }
    }

    public function getName(): string
    {
        return 'FreightInspection';
    }

    public function process(ProductAssemblyContextInterface $context): ProductAssemblyContextInterface
    {
        $shared = $context->getSharedOptions();
        $requiredOrigin = strtoupper((string)($this->defaultOriginMode ?? $shared['warehouse'] ?? $shared['warehouseOrigin'] ?? 'ALL'));

        // Se tiver o resolver de entreposto injetado, executa a resolução formal
        if ($this->originResolver !== null) {
            $productPayload = array_merge($context->getRawData(), $context->getProcessedData());
            $origin = $this->originResolver->resolve($productPayload);
        } else {
            // Resolução direta baseada em dados explícitos de frete e despacho físico
            $shipFrom = strtoupper(trim((string)$context->get('ship_from', $context->get('ship_from_country', ''))));
            if ($shipFrom === 'BR') {
                $origin = 'BR';
            } elseif ($shipFrom === 'CN') {
                $origin = 'CN';
            } else {
                $origin = 'UNKNOWN';
            }
        }

        // Se a procedência for UNKNOWN e tivermos cliente oficial da API injetado, executa consulta de frete oficial
        // Endpoint: aliexpress.ds.freight.query (ae-freight-shipment.pdf e documentacao-completa.pdf Pág. 229)
        if ($origin === 'UNKNOWN' && $this->apiClient !== null) {
            $id = (string)$context->get('id', $context->get('productId', ''));
            if ($id !== '' && is_numeric($id)) {
                try {
                    $selectedSku = '';
                    $skus = $context->get('skus', []);
                    if (!empty($skus) && is_array($skus)) {
                        $selectedSku = (string)($skus[0]['sku_id'] ?? '');
                    }
                    $freightReq = [
                        'productId'     => $id,
                        'shipToCountry' => 'BR',
                        'quantity'      => 1
                    ];
                    if ($selectedSku !== '') {
                        $freightReq['selectedSkuId'] = $selectedSku;
                    }
                    $freightRes = $this->apiClient->queryFreight($freightReq);
                    if (!empty($freightRes) && is_array($freightRes)) {
                        $context->set('freight_info', $freightRes);

                        $deliveryOpts = $freightRes['delivery_options']['delivery_option_d_t_o']
                            ?? ($freightRes['delivery_options'] ?? ($freightRes['aeop_freight_calculate_result_for_buyer_d_t_o_list'] ?? []));
                        if (is_array($deliveryOpts) && isset($deliveryOpts['code'])) {
                            $deliveryOpts = [$deliveryOpts];
                        }
                        if (is_array($deliveryOpts) && !empty($deliveryOpts[0]) && is_array($deliveryOpts[0])) {
                            $firstOpt = $deliveryOpts[0];
                            $context->set('shipping_fee', (float)($firstOpt['shipping_fee_cent'] ?? ($firstOpt['shipping_fee'] ?? 0.0)));
                            $context->set('is_free_shipping', !empty($firstOpt['free_shipping']) || ((float)($firstOpt['shipping_fee'] ?? 0.0) <= 0.0));
                            if (!empty($firstOpt['delivery_date_desc']) && preg_match('/(\d+)/', (string)$firstOpt['delivery_date_desc'], $dm)) {
                                $context->set('expected_delivery_days', (int)$dm[1]);
                            }
                            if (empty($freightRes['ship_from_country']) && !empty($firstOpt['ship_from_country'])) {
                                $freightRes['ship_from_country'] = $firstOpt['ship_from_country'];
                                $context->set('freight_info', $freightRes);
                            }
                        }

                        $productPayload = array_merge($context->getRawData(), $context->getProcessedData(), ['freight' => $freightRes]);
                        if ($this->originResolver !== null) {
                            $origin = $this->originResolver->resolve($productPayload);
                        } else {
                            $shipFromFreight = strtoupper((string)($freightRes['ship_from_country'] ?? ($freightRes['send_goods_country_code'] ?? '')));
                            if ($shipFromFreight === 'BR') {
                                $origin = 'BR';
                            } elseif ($shipFromFreight === 'CN') {
                                $origin = 'CN';
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Isolamento gracioso
                }
            }
        }

        $context->set('warehouse_origin', $origin);

        // Validação contra o filtro solicitado
        if ($requiredOrigin === 'BR' && $origin !== 'BR') {
            return $context->abort(
                $this->getName(),
                "Origem '{$origin}' incompatível com a exigência estrita de estoque nacional comprovado (BR)"
            );
        }

        if ($requiredOrigin === 'CN' && $origin === 'BR') {
            return $context->abort(
                $this->getName(),
                "Origem nacional (BR) incompatível com a exigência estrita de importado China (CN)"
            );
        }

        return $context->recordStationPass($this->getName(), [
            'origin' => $origin,
            'required' => $requiredOrigin,
            'is_national' => ($origin === 'BR')
        ]);
    }
}
