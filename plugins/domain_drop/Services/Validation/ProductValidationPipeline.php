<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Validation;

use DomainSystem\Plugins\domain_drop\Contracts\Validation\ProductIngestionContainerInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Validation\ProductValidationGuardInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Validation\ProductValidationPipelineInterface;
use DomainSystem\Plugins\domain_drop\Services\Validation\Guards\ContentIntegrityGuard;
use DomainSystem\Plugins\domain_drop\Services\Validation\Guards\PriceIntegrityGuard;
use DomainSystem\Plugins\domain_drop\Services\Validation\Guards\ProductExistenceGuard;
use DomainSystem\Plugins\domain_drop\Services\Validation\Guards\RegionalLogisticsGuard;
use DomainSystem\Plugins\domain_drop\Services\Validation\Guards\WarehouseOriginGuard;

/**
 * Esteira Orquestradora de Validação Contratual (SRP / Pipeline Pattern).
 * 
 * Executa a cadeia sequencial de Guards sobre todos os produtos bufferizados
 * no ProductIngestionContainer antes que qualquer produto alcance o catálogo.
 */
class ProductValidationPipeline implements ProductValidationPipelineInterface
{
    /** @var array<ProductValidationGuardInterface> */
    private array $guards = [];

    /**
     * @param array<ProductValidationGuardInterface>|null $guards
     */
    public function __construct(?array $guards = null)
    {
        if ($guards !== null) {
            foreach ($guards as $guard) {
                $this->addGuard($guard);
            }
        } else {
            // Guards padrão alinhados às documentações oficiais
            $this->addGuard(new ProductExistenceGuard());
            $this->addGuard(new RegionalLogisticsGuard());
            $this->addGuard(new WarehouseOriginGuard());
            $this->addGuard(new PriceIntegrityGuard());
            $this->addGuard(new ContentIntegrityGuard());
        }
    }

    public function addGuard(ProductValidationGuardInterface $guard): self
    {
        $this->guards[] = $guard;
        return $this;
    }

    public function getGuards(): array
    {
        return $this->guards;
    }

    public function process(ProductIngestionContainerInterface $container): ProductIngestionContainerInterface
    {
        $allItems = $container->all();

        foreach ($allItems as $id => $rawData) {
            $idStr = (string)$id;
            foreach ($this->guards as $guard) {
                $result = $guard->validate($idStr, $rawData);
                if (!$result->isValid()) {
                    // Item falhou na inspeção contratual: move para a quarentena diagnóstica
                    $container->quarantine(
                        $idStr,
                        $result->getReason(),
                        $result->getGuardName(),
                        $result->getDetails()
                    );
                    break; // Interrompe os demais guards para este item
                }
            }
        }

        return $container;
    }
}
