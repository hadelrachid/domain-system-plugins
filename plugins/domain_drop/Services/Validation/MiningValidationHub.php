<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Validation;

use DomainSystem\Plugins\domain_drop\Contracts\Validation\ProductIngestionContainerInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Validation\ProductValidationPipelineInterface;

/**
 * Hub Central de Ingestão, Bufferização e Validação Contratual de Produtos Minerados.
 * 
 * Funciona como o Gateway Protetor que intercepta qualquer requisição de produtos
 * brutos vindos do SDK ou Scraper do AliExpress, insere-os em um Container isolado,
 * executa as validações contratuais de integridade e retorna apenas produtos 100%
 * comprovados e seguros para persistência na vitrine.
 */
class MiningValidationHub
{
    private ProductValidationPipelineInterface $pipeline;

    public function __construct(?ProductValidationPipelineInterface $pipeline = null)
    {
        $this->pipeline = $pipeline ?? new ProductValidationPipeline();
    }

    /**
     * Cria um novo container de ingestão.
     */
    public function createContainer(): ProductIngestionContainerInterface
    {
        return new ProductIngestionContainer();
    }

    /**
     * Recebe um lote de produtos brutos, carrega no container e executa o pipeline.
     *
     * @param array<int|string, array<string, mixed>> $rawItems
     * @return array{
     *     valid_items: array<string, array<string, mixed>>,
     *     quarantined_items: array<string, array{reason: string, guard: string, details: array<string, mixed>, timestamp: int}>,
     *     total_ingested: int,
     *     total_approved: int,
     *     total_quarantined: int
     * }
     */
    public function ingestAndValidateBatch(array $rawItems): array
    {
        $container = $this->createContainer();

        foreach ($rawItems as $key => $item) {
            if (!is_array($item)) {
                continue;
            }

            $baseInfo = $item['ae_item_base_info_dto'] ?? [];
            $itemId = (string)(
                $item['id']
                ?? ($item['itemId'] 
                ?? ($item['product_id'] 
                ?? ($baseInfo['product_id'] 
                ?? ($item['item_id'] 
                ?? ($item['productId'] ?? (is_numeric($key) ? '' : (string)$key))))))
            );

            if ($itemId !== '') {
                $container->add($itemId, $item);
            }
        }

        $totalIngested = $container->count();

        // Processa o container pelo pipeline contratual
        $validatedContainer = $this->pipeline->process($container);

        $validItems = $validatedContainer->all();
        $quarantined = $validatedContainer->getQuarantined();

        return [
            'valid_items'       => $validItems,
            'quarantined_items' => $quarantined,
            'total_ingested'    => $totalIngested,
            'total_approved'    => count($validItems),
            'total_quarantined' => count($quarantined),
        ];
    }

    public function getPipeline(): ProductValidationPipelineInterface
    {
        return $this->pipeline;
    }
}
