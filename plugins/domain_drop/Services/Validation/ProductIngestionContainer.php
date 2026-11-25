<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Validation;

use DomainSystem\Plugins\domain_drop\Contracts\Validation\ProductIngestionContainerInterface;

/**
 * Container de Ingestão de Mineração e Filtragem.
 * 
 * Implementação concreta de coleção e mapa em memória com isolamento
 * de quarentena diagnóstica para produtos minerados.
 */
class ProductIngestionContainer implements ProductIngestionContainerInterface
{
    /** @var array<string, array<string, mixed>> */
    private array $items = [];

    /** @var array<string, array{reason: string, guard: string, details: array<string, mixed>, timestamp: int}> */
    private array $quarantined = [];

    public function add(string $id, array $rawData): self
    {
        $cleanId = trim($id);
        if ($cleanId !== '') {
            $this->items[$cleanId] = $rawData;
        }
        return $this;
    }

    public function has(string $id): bool
    {
        return isset($this->items[trim($id)]);
    }

    public function get(string $id): ?array
    {
        return $this->items[trim($id)] ?? null;
    }

    public function remove(string $id): self
    {
        unset($this->items[trim($id)]);
        return $this;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function quarantine(string $id, string $reason, string $guardName, array $details = []): self
    {
        $cleanId = trim($id);
        if (isset($this->items[$cleanId])) {
            unset($this->items[$cleanId]);
        }

        $this->quarantined[$cleanId] = [
            'reason'    => $reason,
            'guard'     => $guardName,
            'details'   => $details,
            'timestamp' => time(),
        ];

        return $this;
    }

    public function getQuarantined(): array
    {
        return $this->quarantined;
    }

    public function clear(): void
    {
        $this->items = [];
        $this->quarantined = [];
    }
}
