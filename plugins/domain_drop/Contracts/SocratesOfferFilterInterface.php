<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Contracts;

use DomainSystem\Plugins\domain_drop\DTO\DropshipProductDto;

/**
 * Contrato Oficial para o Container dos 3 Filtros de Sócrates (SOLID / Clean Architecture):
 * 
 * 1º Filtro: CATEGORIA (Verdade / Inviolabilidade)
 *    Isolamento estrito por categoria e família do nicho, garantindo Zero-Contaminação entre departamentos.
 * 
 * 2º Filtro: SEMÂNTICA (Pertinência / Núcleo do Produto)
 *    Identifica e pontua o tipo de produto (ex: top, calça, vestido, fone, relógio)
 *    para priorizar ofertas do mesmo produto ou substitutos diretos.
 * 
 * 3º Filtro: ESPECIFICIDADE (Utilidade / Competitividade de Preço & Reputação)
 *    Ordena por proximidade do preço de venda (targetPrice), volume de vendas e rating.
 */
interface SocratesOfferFilterInterface
{
    /**
     * Aplica os 3 Filtros de Sócrates para selecionar as ofertas mais semelhantes e acessíveis.
     *
     * @param int $excludeId ID do produto atual (a ser excluído da lista)
     * @param string $category Categoria cadastrada do produto
     * @param string $title Título completo do produto
     * @param int $limit Quantidade máxima de ofertas a retornar
     * @param float $targetPrice Preço de venda do produto atual (para aferição de competitividade)
     * @return array<int, DropshipProductDto>
     */
    public function filter(
        int $excludeId,
        string $category,
        string $title = '',
        int $limit = 4,
        float $targetPrice = 0.0
    ): array;

    /**
     * 1º Filtro: Retorna a lista de categorias canônicas permitidas para o nicho (Hard Gate).
     *
     * @return array<int, string>
     */
    public function resolveAllowedCategories(string $category, string $title = ''): array;

    /**
     * 2º Filtro: Extrai as palavras-chave do núcleo semântico utilizadas para pontuação.
     *
     * @return array<int, string>
     */
    public function extractSemanticKeywords(string $title): array;
}
