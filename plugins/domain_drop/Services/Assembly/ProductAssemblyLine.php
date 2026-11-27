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
    private array $defaultSharedOptions = [];
    private array $lastRejections = [];
    private array $lastTelemetry = [];

    /**
     * @param array<int, AssemblyStationInterface> $stations
     */
    public function __construct(
        array $stations = [],
        ?string $warehouseMode = null,
        ?float $exchangeRate = null,
        ?float $markup = null
    ) {
        foreach ($stations as $station) {
            $this->addStation($station);
        }
        if ($warehouseMode !== null) {
            $this->defaultSharedOptions['warehouse'] = $warehouseMode;
            $this->defaultSharedOptions['warehouseOrigin'] = $warehouseMode;
        }
        if ($exchangeRate !== null) {
            $this->defaultSharedOptions['exchange_rate'] = $exchangeRate;
        }
        if ($markup !== null) {
            $this->defaultSharedOptions['markup'] = $markup;
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
            new FreightInspectionStation($originResolver, null, $apiClient),
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
        $sharedOptions = array_merge($this->defaultSharedOptions, $sharedOptions);
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

    /**
     * Executa a montagem em lote retornando objetos de produto compatíveis com a esteira.
     *
     * @param array<int, array> $candidates
     * @return array<int, object>
     */
    public function assembleBatch(array $candidates): array
    {
        $batchResult = $this->runBatch($candidates, $this->defaultSharedOptions);
        $this->lastRejections = $batchResult['rejected'];
        $this->lastTelemetry = $batchResult['telemetry'];

        $objects = [];
        foreach ($batchResult['approved'] as $item) {
            $obj = (object)$item;
            $obj->aliexpressId = (string)($item['id'] ?? ($item['productId'] ?? ($item['aliexpressId'] ?? '')));
            $objects[] = $obj;
        }

        return $objects;
    }

    public function getRejections(): array
    {
        return $this->lastRejections;
    }

    public function getTelemetry(): array
    {
        return $this->lastTelemetry;
    }
}
