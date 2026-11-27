<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Validation\Guards;

use DomainSystem\Plugins\domain_drop\Contracts\Validation\ProductValidationGuardInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Validation\ValidationResultInterface;
use DomainSystem\Plugins\domain_drop\Contracts\WarehouseOriginResolverInterface;
use DomainSystem\Plugins\domain_drop\DTO\Validation\ValidationResult;
use DomainSystem\Plugins\domain_drop\Services\WarehouseOriginResolver;

/**
 * Guard de Verificação Estrita de Entreposto Físico (BR / CN / UNKNOWN).
 * 
 * Atende às diretrizes das auditorias técnicas e documentação oficial (Pág. 229-230):
 * - Aplica política Zero-Contamination para mineração de produtos no Brasil.
 * - Desconsidera store_country_code (país da empresa vendedora) e exige comprovação de expedição.
 * - Não converte UNKNOWN em CN, preservando o estado real retornado pela logística.
 */
class WarehouseOriginGuard implements ProductValidationGuardInterface
{
    private ?WarehouseOriginResolverInterface $originResolver;
    private ?string $defaultTargetOrigin;

    public function __construct(
        WarehouseOriginResolverInterface|string|null $originResolverOrMode = null,
        ?string $defaultTargetOrigin = null
    ) {
        if (is_string($originResolverOrMode)) {
            $this->originResolver = null;
            $this->defaultTargetOrigin = $originResolverOrMode;
        } else {
            $this->originResolver = $originResolverOrMode;
            $this->defaultTargetOrigin = $defaultTargetOrigin;
        }
    }

    public function getName(): string
    {
        return 'warehouse_origin_guard';
    }

    public function validate(string|array $id, array $rawData = []): ValidationResultInterface
    {
        if (is_array($id)) {
            $rawData = $id;
            $id = (string)($rawData['id'] ?? ($rawData['product_id'] ?? 'unknown'));
        }
        $resolver = $this->originResolver ?? new WarehouseOriginResolver();
        $targetOrigin = strtoupper((string)($this->defaultTargetOrigin ?? ($rawData['requested_warehouse_origin'] ?? ($rawData['target_warehouse_origin'] ?? 'ALL'))));
        
        $resolvedOrigin = $resolver->resolve($rawData, (string)($rawData['title'] ?? ($rawData['product_title'] ?? '')));

        if ($targetOrigin === 'BR') {
            if ($resolvedOrigin !== WarehouseOriginResolverInterface::ORIGIN_BR) {
                return ValidationResult::fail(
                    $this->getName(),
                    "Produto descartado pelo filtro de entreposto: expedição física no Brasil não comprovada (origem detectada: {$resolvedOrigin}).",
                    [
                        'product_id'      => $id,
                        'expected_origin' => 'BR',
                        'detected_origin' => $resolvedOrigin,
                    ]
                );
            }
            return ValidationResult::pass(
                $this->getName(),
                'Origem física brasileira confirmada para expedição nacional.',
                ['origin' => 'BR']
            );
        }

        if ($targetOrigin === 'CN') {
            if ($resolvedOrigin === WarehouseOriginResolverInterface::ORIGIN_BR) {
                return ValidationResult::fail(
                    $this->getName(),
                    'Produto descartado pelo filtro de entreposto: produto possui estoque local brasileiro, incompatível com filtro China.',
                    [
                        'product_id'      => $id,
                        'expected_origin' => 'CN',
                        'detected_origin' => 'BR',
                    ]
                );
            }
            return ValidationResult::pass(
                $this->getName(),
                'Origem internacional / China confirmada.',
                ['origin' => $resolvedOrigin]
            );
        }

        // Modo ALL: aceita BR, CN e UNKNOWN, preservando o estado autêntico
        return ValidationResult::pass(
            $this->getName(),
            "Origem física verificada com sucesso (Classificação: {$resolvedOrigin}).",
            ['origin' => $resolvedOrigin]
        );
    }
}
