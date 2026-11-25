<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Contracts\Validation;

/**
 * Interface do Container de Ingestão de Mineração.
 * 
 * Funciona como buffer intermediário isolado (Map/Collection) que armazena todos
 * os itens minerados brutos de qualquer fonte (API Oficial, Live Scraping ou Catálogo Curado)
 * antes que qualquer item seja persistido no banco de dados da vitrine.
 */
interface ProductIngestionContainerInterface
{
    /**
     * Adiciona um item minerado bruto ao container pelo identificador de produto.
     *
     * @param string $id Identificador único do anúncio (ex.: AliExpress Product ID)
     * @param array<string, mixed> $rawData Carga bruta retornada pelo SDK/Scraper
     */
    public function add(string $id, array $rawData): self;

    /**
     * Verifica se o item já está presente no container.
     */
    public function has(string $id): bool;

    /**
     * Obtém os dados brutos de um item pelo identificador.
     *
     * @return array<string, mixed>|null
     */
    public function get(string $id): ?array;

    /**
     * Remove um item do container.
     */
    public function remove(string $id): self;

    /**
     * Retorna todos os itens atualmente válidos no container.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array;

    /**
     * Retorna o total de itens ativos no container.
     */
    public function count(): int;

    /**
     * Move um item com inconformidade para a lista de quarentena diagnóstica.
     *
     * @param array<string, mixed> $details
     */
    public function quarantine(string $id, string $reason, string $guardName, array $details = []): self;

    /**
     * Retorna todos os itens quarentenados com seus diagnósticos.
     *
     * @return array<string, array{reason: string, guard: string, details: array<string, mixed>, timestamp: int}>
     */
    public function getQuarantined(): array;

    /**
     * Limpa o container e a quarentena.
     */
    public function clear(): void;
}
