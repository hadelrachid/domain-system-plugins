<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Contracts\Assembly;

/**
 * Interface ProductAssemblyContextInterface
 *
 * Representa o chassi e estado de inspeção do produto ao longo da linha de montagem.
 */
interface ProductAssemblyContextInterface
{
    /**
     * Obtém os dados brutos de entrada do produto.
     */
    public function getRawData(): array;

    /**
     * Obtém os dados normalizados / processados acumulados pelas estações.
     */
    public function getProcessedData(): array;

    /**
     * Define ou atualiza uma propriedade processada.
     */
    public function set(string $key, mixed $value): self;

    /**
     * Obtém uma propriedade processada com valor padrão opcional.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Obtém os parâmetros globais compartilhados da execução (ex: warehouseFilter, targetCurrency, etc.).
     */
    public function getSharedOptions(): array;

    /**
     * Verifica se o processamento deste item foi abortado (rejeitado pela estação).
     */
    public function isAborted(): bool;

    /**
     * Aborta o processamento do produto na esteira, registrando estação e motivo.
     */
    public function abort(string $stationName, string $reason): self;

    /**
     * Retorna o nome da estação que abortou o produto.
     */
    public function getAbortedStation(): ?string;

    /**
     * Retorna o motivo da rejeição.
     */
    public function getAbortReason(): ?string;

    /**
     * Registra a passagem bem-sucedida do produto por uma estação com métricas.
     */
    public function recordStationPass(string $stationName, array $metrics = []): self;

    /**
     * Retorna o histórico de estações pelas quais o produto transitou.
     */
    public function getHistory(): array;
}
