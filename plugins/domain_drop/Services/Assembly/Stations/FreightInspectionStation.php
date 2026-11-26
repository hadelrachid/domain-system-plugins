<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Assembly\Stations;

use DomainSystem\Plugins\domain_drop\Contracts\Assembly\AssemblyStationInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Assembly\ProductAssemblyContextInterface;
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

    public function __construct(?WarehouseOriginResolverInterface $originResolver = null)
    {
        $this->originResolver = $originResolver;
    }

    public function getName(): string
    {
        return 'FreightInspection';
    }

    public function process(ProductAssemblyContextInterface $context): ProductAssemblyContextInterface
    {
        $shared = $context->getSharedOptions();
        $requiredOrigin = strtoupper((string)($shared['warehouse'] ?? $shared['warehouseOrigin'] ?? 'ALL'));

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
