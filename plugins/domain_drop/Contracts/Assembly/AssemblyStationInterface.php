<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Contracts\Assembly;

/**
 * Interface AssemblyStationInterface
 *
 * Representa uma estação isolada na linha de montagem de produtos dropshipping.
 * Cada estação cumpre estritamente o Princípio da Responsabilidade Única (SRP).
 */
interface AssemblyStationInterface
{
    /**
     * Retorna o nome identificador da estação.
     */
    public function getName(): string;

    /**
     * Processa o contexto do produto na esteira.
     * Se houver não conformidade, a estação deve chamar $context->abort($this->getName(), $reason).
     */
    public function process(ProductAssemblyContextInterface $context): ProductAssemblyContextInterface;
}
