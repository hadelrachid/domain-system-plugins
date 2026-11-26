<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Assembly;

use DomainSystem\Plugins\domain_drop\Contracts\Assembly\AssemblyStationInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Assembly\ProductAssemblyContextInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Assembly\ProductAssemblyLineInterface;
use DomainSystem\Plugins\domain_drop\Contracts\AliExpressApiClientInterface;
use DomainSystem\Plugins\domain_drop\Contracts\WarehouseOriginResolverInterface;
use DomainSystem\Plugins\domain_drop\Contracts\CurrencyConverterInterface;
use DomainSystem\Plugins\domain_drop\Services\Assembly\Stations\CandidateNormalizationStation;
use DomainSystem\Plugins\domain_drop\Services\Assembly\Stations\ProductDetailExtractionStation;
use DomainSystem\Plugins\domain_drop\Services\Assembly\Stations\FreightInspectionStation;
use DomainSystem\Plugins\domain_drop\Services\Assembly\Stations\MediaSanitizationStation;
use DomainSystem\Plugins\domain_drop\Services\Assembly\Stations\FinancialPricingStation;
use DomainSystem\Plugins\domain_drop\Services\Assembly\Stations\CatalogPackagingStation;

/**
 * Class ProductAssemblyLine
 *
 * Orquestrador da linha de montagem com execução linear e isolamento de falhas.
 */
class ProductAssemblyLine implements ProductAssemblyLineInterface
{
    /**
     * @var array<int, AssemblyStationInterface>
     */
    private array $stations = [];

    /**
     * @param array<int, AssemblyStationInterface> $stations
     */
    public function __construct(array $stations = [])
    {
        foreach ($stations as $station) {
            $this->addStation($station);
        }
    }

    public function addStation(AssemblyStationInterface $station): self
    {
        $this->stations[] = $station;
        return $this;
    }

    /**
     * Fábrica estática para criar a Linha de Montagem Padrão alinhada à documentação oficial.
     */
    public static function createStandard(
        ?AliExpressApiClientInterface $apiClient = null,
        ?WarehouseOriginResolverInterface $originResolver = null,
        ?CurrencyConverterInterface $currencyConverter = null
    ): self {
        return new self([
            new CandidateNormalizationStation(),
            new ProductDetailExtractionStation($apiClient),
            new FreightInspectionStation($originResolver),
            new MediaSanitizationStation(),
            new FinancialPricingStation($currencyConverter),
            new CatalogPackagingStation()
        ]);
    }

    public function run(ProductAssemblyContextInterface $context): ProductAssemblyContextInterface
    {
        foreach ($this->stations as $station) {
            if ($context->isAborted()) {
                break;
            }

            try {
                $context = $station->process($context);
            } catch (\Throwable $e) {
                $context->abort($station->getName(), 'Exceção não tratada na estação: ' . $e->getMessage());
                break;
            }
        }

        return $context;
    }

    public function runBatch(array $candidates, array $sharedOptions = []): array
    {
        $approved = [];
        $rejected = [];
        $telemetry = [
            'total_candidates' => count($candidates),
            'approved_count' => 0,
            'rejected_count' => 0,
            'br_confirmed' => 0,
            'cn_confirmed' => 0,
            'unknown_origin' => 0,
            'rejections_by_station' => []
        ];

        foreach ($candidates as $candidate) {
            $context = new ProductAssemblyContext($candidate, $sharedOptions);
            $result = $this->run($context);

            if ($result->isAborted()) {
                $station = $result->getAbortedStation() ?? 'Unknown';
                $reason = $result->getAbortReason() ?? 'Sem motivo especificado';

                $rejected[] = [
                    'id' => $candidate['id'] ?? $candidate['productId'] ?? null,
                    'station' => $station,
                    'reason' => $reason
                ];

                $telemetry['rejected_count']++;
                $telemetry['rejections_by_station'][$station] = ($telemetry['rejections_by_station'][$station] ?? 0) + 1;
            } else {
                $packaged = $result->get('packaged_product');
                if (is_array($packaged)) {
                    $approved[] = $packaged;
                    $telemetry['approved_count']++;

                    $origin = $packaged['warehouse_origin'] ?? 'UNKNOWN';
                    if ($origin === 'BR') {
                        $telemetry['br_confirmed']++;
                    } elseif ($origin === 'CN') {
                        $telemetry['cn_confirmed']++;
                    } else {
                        $telemetry['unknown_origin']++;
                    }
                }
            }
        }

        return [
            'approved' => $approved,
            'rejected' => $rejected,
            'telemetry' => $telemetry
        ];
    }
}
