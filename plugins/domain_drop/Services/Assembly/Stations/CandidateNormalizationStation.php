<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services\Assembly\Stations;

use DomainSystem\Plugins\domain_drop\Contracts\Assembly\AssemblyStationInterface;
use DomainSystem\Plugins\domain_drop\Contracts\Assembly\ProductAssemblyContextInterface;

/**
 * Estação 1: Normalização de Candidatos
 *
 * Valida a integridade mínima dos dados de entrada (ID, título inicial e estrutura).
 * Documentação de referência: dropshiper.pdf
 */
class CandidateNormalizationStation implements AssemblyStationInterface
{
    public function getName(): string
    {
        return 'CandidateNormalization';
    }

    public function process(ProductAssemblyContextInterface $context): ProductAssemblyContextInterface
    {
        $raw = $context->getRawData();

        $id = $raw['id'] ?? $raw['productId'] ?? $raw['product_id'] ?? null;
        if (empty($id) || !is_numeric($id) && !is_string($id)) {
            return $context->abort($this->getName(), 'Identificador do produto ausente ou inválido');
        }

        $id = (string) $id;
        $title = trim((string)($raw['title'] ?? $raw['product_title'] ?? $raw['subject'] ?? ''));

        if ($title === '') {
            return $context->abort($this->getName(), 'Título do produto ausente');
        }

        // Sanitização básica do título e dados essenciais
        $context->set('id', $id);
        $context->set('productId', $id);
        $context->set('title', $title);
        $context->set('original_title', $title);
        $context->set('category_id', $raw['categoryId'] ?? $raw['category_id'] ?? null);
        $context->set('category_name', $raw['category_name'] ?? $raw['category'] ?? 'Geral');

        return $context->recordStationPass($this->getName(), [
            'id' => $id,
            'title_length' => mb_strlen($title)
        ]);
    }
}
