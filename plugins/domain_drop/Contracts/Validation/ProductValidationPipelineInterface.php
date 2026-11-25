<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Contracts\Validation;

/**
 * Interface do Pipeline Orquestrador de Validação Contratual.
 */
interface ProductValidationPipelineInterface
{
    /**
     * Adiciona um Guard à cadeia de validação sequencial.
     */
    public function addGuard(ProductValidationGuardInterface $guard): self;

    /**
     * Retorna a lista de Guards registrados no pipeline.
     *
     * @return array<ProductValidationGuardInterface>
     */
    public function getGuards(): array;

    /**
     * Processa todos os itens do container através da esteira de Guards.
     * Os itens que falharem em qualquer Guard são movidos para quarentena diagnóstica.
     * Apenas itens 100% aprovados permanecem no container para persistência.
     */
    public function process(ProductIngestionContainerInterface $container): ProductIngestionContainerInterface;
}
