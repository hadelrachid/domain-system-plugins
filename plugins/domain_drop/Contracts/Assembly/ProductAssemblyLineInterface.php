<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Contracts\Assembly;

/**
 * Interface ProductAssemblyLineInterface
 *
 * Orquestrador da linha de montagem linear com desacoplamento SOLID.
 */
interface ProductAssemblyLineInterface
{
    /**
     * Adiciona uma estação de trabalho à esteira.
     */
    public function addStation(AssemblyStationInterface $station): self;

    /**
     * Executa a linha de montagem completa para um único produto.
     */
    public function run(ProductAssemblyContextInterface $context): ProductAssemblyContextInterface;

    /**
     * Executa a linha de montagem para um lote de produtos brutos.
     * Retorna um array com 'approved' (produtos aprovados) e 'rejected' (rejeições com motivos).
     *
     * @param array<int, array> $candidates
     * @param array<string, mixed> $sharedOptions
     * @return array{approved: array, rejected: array, telemetry: array}
     */
    public function runBatch(array $candidates, array $sharedOptions = []): array;
}
