<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Assembly;

use DomainSystem\Plugins\domain_drop\Contracts\Assembly\ProductAssemblyContextInterface;

/**
 * Class ProductAssemblyContext
 *
 * Implementação do chassi de transporte de dados na linha de montagem.
 */
class ProductAssemblyContext implements ProductAssemblyContextInterface
{
    private array $rawData;
    private array $processedData;
    private array $sharedOptions;
    private bool $isAborted = false;
    private ?string $abortedStation = null;
    private ?string $abortReason = null;
    private array $history = [];

    public function __construct(array $rawData, array $sharedOptions = [])
    {
        $this->rawData = $rawData;
        $this->processedData = $rawData;
        $this->sharedOptions = $sharedOptions;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }

    public function getProcessedData(): array
    {
        return $this->processedData;
    }

    public function set(string $key, mixed $value): self
    {
        $this->processedData[$key] = $value;
        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->processedData[$key] ?? $default;
    }

    public function getSharedOptions(): array
    {
        return $this->sharedOptions;
    }

    public function isAborted(): bool
    {
        return $this->isAborted;
    }

    public function abort(string $stationName, string $reason): self
    {
        $this->isAborted = true;
        $this->abortedStation = $stationName;
        $this->abortReason = $reason;
        $this->history[] = [
            'station' => $stationName,
            'status' => 'ABORTED',
            'reason' => $reason,
            'timestamp' => microtime(true)
        ];
        return $this;
    }

    public function getAbortedStation(): ?string
    {
        return $this->abortedStation;
    }

    public function getAbortReason(): ?string
    {
        return $this->abortReason;
    }

    public function recordStationPass(string $stationName, array $metrics = []): self
    {
        $this->history[] = [
            'station' => $stationName,
            'status' => 'PASSED',
            'metrics' => $metrics,
            'timestamp' => microtime(true)
        ];
        return $this;
    }

    public function getHistory(): array
    {
        return $this->history;
    }
}
