<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Services;

use DomainSystem\Plugins\domain_drop\Contracts\CategoryTaxonomyInterface;
use DomainSystem\Plugins\domain_drop\Contracts\SocratesOfferFilterInterface;
use DomainSystem\Plugins\domain_drop\DTO\DropshipProductDto;
use PDO;

/**
 * Container de Filtragem dos 3 Filtros de Sócrates (Clean Architecture / SOLID / GoF Pipeline).
 * 
 * Implementa com rigor matemático os 3 crivos socráticos para ofertas comparativas:
 * 1. CATEGORIA (Hard Gate): Inviolabilidade do nicho (Zero-Contaminação entre departamentos).
 * 2. SEMÂNTICA (Relevância Semântica): Pontuação do tipo e palavras-chave substantivas no título.
 * 3. ESPECIFICIDADE (Competitividade & Reputação): Preço na média ou mais acessível, volume e notas.
 */
class SocratesOfferFilterService implements SocratesOfferFilterInterface
{
    private PDO $pdo;
    private CategoryTaxonomyInterface $taxonomy;

    public function __construct(PDO $pdo, ?CategoryTaxonomyInterface $taxonomy = null)
    {
        $this->pdo = $pdo;
        $this->taxonomy = $taxonomy ?? new CategoryTaxonomyService();
    }

    /**
     * Executa a esteira dos 3 Filtros de Sócrates.
     */
    public function filter(
        int $excludeId,
        string $category,
        string $title = '',
        int $limit = 4,
        float $targetPrice = 0.0
    ): array {
        $limit = max(1, min(20, $limit));

        // =========================================================================
        // 1º FILTRO DE SÓCRATES: CATEGORIA (Verdade / Inviolabilidade de Nicho)
        // Hard Gate: NENHUM item fora da família autorizada de categorias é admitido.
        // =========================================================================
        $allowedCategories = $this->resolveAllowedCategories($category, $title);
        if (empty($allowedCategories)) {
            return [];
        }

        $lowerAllowed = array_values(array_unique(array_filter(array_map(function (string $c): string {
            return mb_strtolower(trim($c), 'UTF-8');
        }, $allowedCategories))));

        if (empty($lowerAllowed)) {
            return [];
        }

        // =========================================================================
        // 2º FILTRO DE SÓCRATES: SEMÂNTICA (Pertinência / Núcleo do Produto)
        // Pontua outros vendedores do MESMO tipo de item (ex: tops, calças, vestidos).
        // =========================================================================
        $keywords = $this->extractSemanticKeywords($title);

        $params = [];
        $scoreClauses = [];

        if (!empty($keywords)) {
            $weight = 100;
            foreach ($keywords as $kw) {
                $kwLower = mb_strtolower($kw, 'UTF-8');
                $scoreClauses[] = "CASE WHEN LOWER(title) LIKE ? THEN {$weight} ELSE 0 END";
                $params[] = '%' . $kwLower . '%';

                // Tratamento singular/plural (ex: tops -> top, calças -> calça)
                if (str_ends_with($kwLower, 's') && mb_strlen($kwLower, 'UTF-8') > 3) {
                    $singular = mb_substr($kwLower, 0, -1, 'UTF-8');
                    $scoreClauses[] = "CASE WHEN LOWER(title) LIKE ? THEN " . (int)($weight * 0.8) . " ELSE 0 END";
                    $params[] = '%' . $singular . '%';
                }

                $weight = max(10, $weight - 25);
            }
        }

        $semanticScoreSql = !empty($scoreClauses)
            ? '(' . implode(' + ', $scoreClauses) . ')'
            : '0';

        // =========================================================================
        // 3º FILTRO DE SÓCRATES: ESPECIFICIDADE (Utilidade / Competitividade & Reputação)
        // Ordena por maior pontuação semântica, proximidade de preço, volume e notas.
        // =========================================================================
        $catPlaceholders = implode(',', array_fill(0, count($lowerAllowed), '?'));
        foreach ($lowerAllowed as $cat) {
            $params[] = $cat;
        }

        $params[] = $excludeId;

        $orderBy = $targetPrice > 0.0
            ? "semantic_score DESC, ABS(sale_price_brl - {$targetPrice}) ASC, sale_price_brl ASC, orders_count DESC, rating DESC"
            : "semantic_score DESC, sale_price_brl ASC, orders_count DESC, rating DESC";

        $sql = "
            SELECT *, {$semanticScoreSql} AS semantic_score
            FROM domain_drop_catalog
            WHERE status = 'published'
              AND LOWER(TRIM(category)) IN ({$catPlaceholders})
              AND id != ?
            ORDER BY {$orderBy}
            LIMIT {$limit}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map([$this, 'hydrate'], $rows);
    }

    /**
     * 1º Crivo: Resolução estrita das categorias autorizadas para o nicho (Hard Gate).
     */
    public function resolveAllowedCategories(string $category, string $title = ''): array
    {
        $allowed = $this->taxonomy->getFamilyCategories($category, $title);
        if (empty($allowed)) {
            $allowed = [$category];
        }

        if ($category !== '' && !in_array($category, $allowed, true)) {
            array_unshift($allowed, $category);
        }

        return array_values(array_unique(array_filter($allowed)));
    }

    /**
     * 2º Crivo: Extração das palavras-chave do núcleo semântico.
     */
    public function extractSemanticKeywords(string $title): array
    {
        if (trim($title) === '') {
            return [];
        }

        $clean = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $title) ?? $title, 'UTF-8');
        $words = array_filter(explode(' ', $clean), function (string $w): bool {
            $len = mb_strlen($w, 'UTF-8');
            if ($len < 3 || is_numeric($w)) {
                return false;
            }
            $stopWords = [
                'para', 'com', 'sem', 'por', 'uma', 'uns', 'umas', 'que', 'pro', 'max', 'plus',
                'kit', 'pecas', 'peças', 'peca', 'peça', 'lote', 'novo', 'nova', 'novos', 'novas',
                'original', 'alta', 'qualidade', 'frete', 'gratis', 'grátis', 'brasil', 'pronta',
                'entrega', 'tipo', 'modelo', 'super', 'mini', 'premium', 'luxo', 'unidades',
                'unidade', 'unid', 'versao', 'versão', 'tam', 'tamanho', 'cores', 'envio', 'rapido',
                'rápido', 'graus', 'portatil', 'portátil', 'fashion', 'casual', 'estilo', 'elegante',
                'tecido', 'unica', 'única', 'costura', 'costuras', 'anti', 'baixo', 'dia', 'ate', 'até'
            ];
            return !in_array($w, $stopWords, true);
        });

        $result = [];
        foreach ($words as $w) {
            $result[] = $w;
        }

        return array_values(array_slice(array_unique($result), 0, 5));
    }

    private function hydrate(array $row): DropshipProductDto
    {
        $images = [];
        if (!empty($row['images'])) {
            $decoded = json_decode((string)$row['images'], true);
            if (is_array($decoded)) {
                $images = array_values(array_filter($decoded, 'is_string'));
            }
        }

        $attributes = [];
        if (!empty($row['attributes'])) {
            $decodedAttr = json_decode((string)$row['attributes'], true);
            if (is_array($decodedAttr)) {
                $attributes = $decodedAttr;
            }
        }

        return new DropshipProductDto(
            aliexpressId: (string)($row['aliexpress_id'] ?? ''),
            title: (string)($row['title'] ?? ''),
            slug: (string)($row['slug'] ?? ''),
            costPriceUsd: (float)($row['cost_price_usd'] ?? 0.0),
            salePriceBrl: (float)($row['sale_price_brl'] ?? 0.0),
            mainImageUrl: (string)($row['main_image_url'] ?? ''),
            images: $images,
            category: (string)($row['category'] ?? 'Geral'),
            description: (string)($row['description'] ?? ''),
            rating: (float)($row['rating'] ?? 4.8),
            ordersCount: (int)($row['orders_count'] ?? 0),
            attributes: $attributes,
            status: (string)($row['status'] ?? 'draft'),
            id: isset($row['id']) ? (int)$row['id'] : null
        );
    }
}
