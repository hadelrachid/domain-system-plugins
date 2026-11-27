<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Repositories;

use DomainSystem\Core\Contracts\DatabaseConnectionInterface;
use DomainSystem\Plugins\domain_drop\Contracts\CategoryTaxonomyInterface;
use DomainSystem\Plugins\domain_drop\Contracts\DropshipCatalogRepositoryInterface;
use DomainSystem\Plugins\domain_drop\DTO\DropshipProductDto;
use DomainSystem\Plugins\domain_drop\Services\CategoryTaxonomyService;
use PDO;

/**
 * Repositório PDO para o Catálogo de Produtos Minerados do DomainDrop (DIP / SRP).
 */
class PdoDropshipCatalogRepository implements DropshipCatalogRepositoryInterface
{
    private DatabaseConnectionInterface|PDO $connection;
    private PDO $pdo;
    private CategoryTaxonomyInterface $taxonomy;

    public function __construct(
        DatabaseConnectionInterface|PDO $connection,
        ?CategoryTaxonomyInterface $taxonomy = null
    ) {
        $this->connection = $connection;
        $this->pdo = $connection instanceof DatabaseConnectionInterface
            ? $connection->getPdo()
            : $connection;
        $this->taxonomy = $taxonomy ?? new CategoryTaxonomyService();
        DomainDropSchemaEnsurer::ensure($this->getPdo());
    }

    private function getPdo(): PDO
    {
        if ($this->connection instanceof DatabaseConnectionInterface) {
            $this->pdo = $this->connection->getPdo();
        }
        return $this->pdo;
    }

    private function executeWithRetry(\Closure $callback): mixed
    {
        try {
            return $callback($this->getPdo());
        } catch (\PDOException $e) {
            $isGoneAway = in_array((int)($e->errorInfo[1] ?? 0), [2006, 2013], true)
                || stripos($e->getMessage(), 'server has gone away') !== false
                || stripos($e->getMessage(), 'Lost connection') !== false;

            if ($isGoneAway && $this->connection instanceof DatabaseConnectionInterface) {
                $this->pdo = $this->connection->getPdo();
                return $callback($this->pdo);
            }
            throw $e;
        }
    }

    public function save(DropshipProductDto $product): int
    {
        return $this->executeWithRetry(function (PDO $pdo) use ($product) {
            $now = date('Y-m-d H:i:s');
            $imagesJson = json_encode($product->images, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $attrJson = json_encode($product->attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $targetId = $product->id;
            if ($targetId === null && !empty($product->aliexpressId)) {
                $existing = $this->findByAliExpressId($product->aliexpressId);
                if ($existing !== null && $existing->id !== null) {
                    $targetId = $existing->id;
                }
            }

            if ($targetId !== null) {
                $stmt = $pdo->prepare("
                    UPDATE domain_drop_catalog
                    SET title = ?, slug = ?, cost_price_usd = ?, sale_price_brl = ?,
                        main_image_url = ?, images = ?, category = ?, description = ?,
                        rating = ?, orders_count = ?, attributes = ?, status = ?, updated_at = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $product->title,
                    $product->slug,
                    $product->costPriceUsd,
                    $product->salePriceBrl,
                    $product->mainImageUrl,
                    $imagesJson,
                    $product->category,
                    $product->description,
                    $product->rating,
                    $product->ordersCount,
                    $attrJson,
                    $product->status,
                    $now,
                    $targetId,
                ]);

                return $targetId;
            }

            $stmt = $pdo->prepare("
                INSERT INTO domain_drop_catalog
                    (aliexpress_id, title, slug, cost_price_usd, sale_price_brl, main_image_url, images, category, description, rating, orders_count, attributes, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $product->aliexpressId,
                $product->title,
                $product->slug,
                $product->costPriceUsd,
                $product->salePriceBrl,
                $product->mainImageUrl,
                $imagesJson,
                $product->category,
                $product->description,
                $product->rating,
                $product->ordersCount,
                $attrJson,
                $product->status,
                $now,
                $now,
            ]);

            return (int)$pdo->lastInsertId();
        });
    }

    public function findById(int $id): ?DropshipProductDto
    {
        $stmt = $this->pdo->prepare("SELECT * FROM domain_drop_catalog WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function findByAliExpressId(string $aliexpressId): ?DropshipProductDto
    {
        $stmt = $this->pdo->prepare("SELECT * FROM domain_drop_catalog WHERE aliexpress_id = ? LIMIT 1");
        $stmt->execute([$aliexpressId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function findBySlug(string $slug): ?DropshipProductDto
    {
        $stmt = $this->pdo->prepare("SELECT * FROM domain_drop_catalog WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function list(array $criteria = [], int $limit = 20, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if (!empty($criteria['category'])) {
            $where[] = $this->buildCategoryListClause($criteria['category'], $params);
        }

        if (!empty($criteria['slug'])) {
            $where[] = "slug = ?";
            $params[] = (string)$criteria['slug'];
        }

        if (!empty($criteria['status'])) {
            $where[] = "status = ?";
            $params[] = (string)$criteria['status'];
        }

        if (!empty($criteria['search'])) {
            $where[] = $this->buildSearchClause((string)$criteria['search'], $params);
        }

        $sql = "SELECT * FROM domain_drop_catalog";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $orderBy = "id DESC";
        if (!empty($criteria['sort'])) {
            $sortVal = (string)$criteria['sort'];
            if ($sortVal === 'sales' || $sortVal === 'best_sellers' || $sortVal === 'volume_desc') {
                $orderBy = "orders_count DESC, id DESC";
            } elseif ($sortVal === 'updated_desc' || $sortVal === 'recent' || $sortVal === 'cycle') {
                $orderBy = "updated_at DESC, id DESC";
            } elseif ($sortVal === 'price_asc') {
                $orderBy = "sale_price_brl ASC, id DESC";
            } elseif ($sortVal === 'price_desc') {
                $orderBy = "sale_price_brl DESC, id DESC";
            } elseif ($sortVal === 'rating') {
                $orderBy = "rating DESC, id DESC";
            }
        }

        $limit = max(1, $limit);
        $offset = max(0, $offset);
        $sql .= " ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map([$this, 'hydrate'], $rows);
    }

    public function count(array $criteria = []): int
    {
        $where = [];
        $params = [];

        if (!empty($criteria['category'])) {
            $where[] = $this->buildCategoryListClause($criteria['category'], $params);
        }

        if (!empty($criteria['slug'])) {
            $where[] = "slug = ?";
            $params[] = (string)$criteria['slug'];
        }

        if (!empty($criteria['status'])) {
            $where[] = "status = ?";
            $params[] = (string)$criteria['status'];
        }

        if (!empty($criteria['search'])) {
            $where[] = $this->buildSearchClause((string)$criteria['search'], $params);
        }

        $sql = "SELECT COUNT(*) FROM domain_drop_catalog";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Constrói cláusula SQL inteligente para busca textual tolerante a tokens, plural/singular,
     * remoção de preposições (stop words) e sinônimos frequentes de mercado.
     *
     * @param array<int, mixed> $params
     */
    private function buildSearchClause(string $search, array &$params): string
    {
        $search = trim($search);
        if ($search === '') {
            return '1=1';
        }

        $stopWords = ['de', 'da', 'do', 'das', 'dos', 'em', 'na', 'no', 'nas', 'nos', 'com', 'por', 'para', 'pra', 'um', 'uma', 'uns', 'umas', 'e', 'ou', 'a', 'o', 'as', 'os', 'ao', 'aos', 'que', 'se'];

        // Separa em palavras/tokens limpos
        $rawTokens = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($search, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // Filtra stop words se sobrarem outros termos significativos
        $filtered = array_values(array_filter($rawTokens, fn($t) => !in_array($t, $stopWords, true)));
        $tokens = !empty($filtered) ? $filtered : $rawTokens;

        if (empty($tokens)) {
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            return "(title LIKE ? OR description LIKE ? OR category LIKE ?)";
        }

        $tokenClauses = [];
        foreach ($tokens as $token) {
            $variants = [$token];

            // 1. Versão sem acentos
            $unaccented = preg_replace(
                ['/[áàãâä]/u', '/[éèêë]/u', '/[íìîï]/u', '/[óòõôö]/u', '/[úùûü]/u', '/[ç]/u'],
                ['a', 'e', 'i', 'o', 'u', 'c'],
                $token
            );
            if ($unaccented !== $token) {
                $variants[] = $unaccented;
            }

            // 2. Singular / Plural inteligente em português
            if (mb_strlen($token, 'UTF-8') > 3 && str_ends_with($token, 's')) {
                $variants[] = mb_substr($token, 0, -1, 'UTF-8');
                if (str_ends_with($token, 'es')) {
                    $variants[] = mb_substr($token, 0, -2, 'UTF-8');
                }
            } elseif (mb_strlen($token, 'UTF-8') >= 3) {
                $variants[] = $token . 's';
            }

            // 3. Normalização das variantes geradas
            foreach ($variants as $v) {
                $unacc = preg_replace(
                    ['/[áàãâä]/u', '/[éèêë]/u', '/[íìîï]/u', '/[óòõôö]/u', '/[úùûü]/u', '/[ç]/u'],
                    ['a', 'e', 'i', 'o', 'u', 'c'],
                    $v
                );
                if ($unacc !== $v) {
                    $variants[] = $unacc;
                }
            }

            // 4. Sinônimos de mercado comuns
            if (in_array($unaccented, ['relogio', 'relogios', 'smartwatch', 'smartwatches', 'watch'], true)) {
                $variants[] = 'relogio';
                $variants[] = 'relógio';
                $variants[] = 'smartwatch';
                $variants[] = 'watch';
            } elseif (in_array($unaccented, ['fone', 'fones', 'ouvido', 'tws', 'headset'], true)) {
                $variants[] = 'fone';
                $variants[] = 'tws';
                $variants[] = 'ouvido';
            } elseif (in_array($unaccented, ['lampada', 'lampadas', 'luminaria', 'luminarias'], true)) {
                $variants[] = 'lampada';
                $variants[] = 'lâmpada';
                $variants[] = 'luminaria';
                $variants[] = 'luminária';
            } elseif (in_array($unaccented, ['secador', 'secadora', 'secadores', 'escova', 'escovas'], true)) {
                $variants[] = 'secador';
                $variants[] = 'secadora';
                $variants[] = 'escova';
            }

            $variants = array_unique(array_filter($variants, fn($v) => mb_strlen($v, 'UTF-8') >= 2));

            $subClauses = [];
            foreach ($variants as $v) {
                $subClauses[] = "(title LIKE ? OR description LIKE ? OR category LIKE ?)";
                $params[] = '%' . $v . '%';
                $params[] = '%' . $v . '%';
                $params[] = '%' . $v . '%';
            }

            $tokenClauses[] = '(' . implode(' OR ', $subClauses) . ')';
        }

        return '(' . implode(' AND ', $tokenClauses) . ')';
    }

    /**
     * Suporta uma única categoria ou lista de categorias oficiais combinadas com OR.
     *
     * @param string|array<int, string> $category
     * @param array<int, mixed> $params
     */
    private function buildCategoryListClause(string|array $category, array &$params): string
    {
        if (is_array($category)) {
            $parts = [];
            foreach ($category as $catItem) {
                $catStr = trim((string)$catItem);
                if ($catStr !== '') {
                    $parts[] = $this->buildCategoryClause($catStr, $params);
                }
            }
            if (!empty($parts)) {
                return '(' . implode(' OR ', $parts) . ')';
            }
            return '1=1';
        }

        return $this->buildCategoryClause($category, $params);
    }

    /**
     * Constrói cláusula SQL inteligente para categoria delegando para a Taxonomia Desacoplada (SOLID / DIP).
     *
     * @param array<int, mixed> $params
     */
    private function buildCategoryClause(string $category, array &$params): string
    {
        return $this->taxonomy->buildSqlFilter($category, $params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM domain_drop_catalog WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updatePrice(int $id, float $salePriceBrl, ?float $costPriceUsd = null): bool
    {
        $now = date('Y-m-d H:i:s');
        if ($costPriceUsd !== null && $costPriceUsd >= 0) {
            $stmt = $this->pdo->prepare("
                UPDATE domain_drop_catalog
                SET sale_price_brl = ?, cost_price_usd = ?, updated_at = ?
                WHERE id = ?
            ");
            return $stmt->execute([$salePriceBrl, $costPriceUsd, $now, $id]);
        }

        $stmt = $this->pdo->prepare("
            UPDATE domain_drop_catalog
            SET sale_price_brl = ?, updated_at = ?
            WHERE id = ?
        ");
        return $stmt->execute([$salePriceBrl, $now, $id]);
    }

    public function updateCategory(int $id, string $category): bool
    {
        $category = trim($category);
        if ($category === '') {
            return false;
        }

        $stmt = $this->pdo->prepare("
            UPDATE domain_drop_catalog 
            SET category = ?, updated_at = ? 
            WHERE id = ?
        ");

        return $stmt->execute([$category, date('Y-m-d H:i:s'), $id]);
    }

    public function updateOrigin(int $id, string $origin): bool
    {
        $stmt = $this->pdo->prepare("SELECT attributes FROM domain_drop_catalog WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $rawAttrs = $stmt->fetchColumn();
        if ($rawAttrs === false) {
            return false;
        }

        $attributes = json_decode((string)$rawAttrs, true);
        if (!is_array($attributes)) {
            $attributes = [];
        }

        $origin = strtoupper(trim($origin));

        if ($origin === 'BR') {
            $attributes['ship_from'] = 'BR';
            $attributes['origem_brasil'] = true;
            $attributes['entreposto_brasil'] = true;
            $attributes['despacho_nacional'] = 'Estoque no Brasil (Entreposto Nacional)';
            unset($attributes['origem_china'], $attributes['entreposto_china'], $attributes['origem_desconhecida']);
        } elseif ($origin === 'CN') {
            $attributes['ship_from'] = 'CN';
            $attributes['origem_china'] = true;
            $attributes['entreposto_china'] = true;
            $attributes['despacho_origem'] = 'Entreposto Central AliExpress (China)';
            $attributes['remessa_conforme'] = 'Importação Oficial com DDP e Rastreamento';
            unset($attributes['origem_brasil'], $attributes['entreposto_brasil'], $attributes['despacho_nacional'], $attributes['origem_desconhecida']);
        } else {
            $attributes['ship_from'] = 'UNKNOWN';
            $attributes['origem_desconhecida'] = true;
            $attributes['despacho_origem'] = 'Origem Internacional Certificada';
            unset($attributes['origem_brasil'], $attributes['entreposto_brasil'], $attributes['despacho_nacional'], $attributes['origem_china'], $attributes['entreposto_china']);
        }

        $stmt = $this->pdo->prepare("
            UPDATE domain_drop_catalog 
            SET attributes = ?, updated_at = ? 
            WHERE id = ?
        ");

        return $stmt->execute([json_encode($attributes, JSON_UNESCAPED_UNICODE), date('Y-m-d H:i:s'), $id]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE domain_drop_catalog 
            SET status = ?, updated_at = ? 
            WHERE id = ?
        ");

        return $stmt->execute([$status, date('Y-m-d H:i:s'), $id]);
    }

    public function batchUpdateStatus(array $ids, string $status): int
    {
        $intIds = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
        if (empty($intIds)) {
            return 0;
        }

        return $this->executeWithRetry(function (PDO $pdo) use ($intIds, $status) {
            $placeholders = implode(',', array_fill(0, count($intIds), '?'));
            $stmt = $pdo->prepare("
                UPDATE domain_drop_catalog
                SET status = ?, updated_at = ?
                WHERE id IN ({$placeholders})
            ");
            $params = array_merge([$status, date('Y-m-d H:i:s')], $intIds);
            $stmt->execute($params);
            return $stmt->rowCount();
        });
    }

    public function batchDelete(array $ids, bool $preserveOrdered = true): int
    {
        $intIds = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
        if (empty($intIds)) {
            return 0;
        }

        return $this->executeWithRetry(function (PDO $pdo) use ($intIds, $preserveOrdered) {
            $placeholders = implode(',', array_fill(0, count($intIds), '?'));
            if ($preserveOrdered) {
                $sql = "
                    DELETE FROM domain_drop_catalog
                    WHERE id IN ({$placeholders})
                      AND aliexpress_id NOT IN (
                          SELECT aliexpress_product_id 
                          FROM domain_drop_orders 
                          WHERE aliexpress_product_id IS NOT NULL AND aliexpress_product_id != ''
                      )
                ";
            } else {
                $sql = "DELETE FROM domain_drop_catalog WHERE id IN ({$placeholders})";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($intIds);
            return $stmt->rowCount();
        });
    }

    public function getCategories(): array
    {
        $canonicals = $this->taxonomy->getCanonicalCategories();
        $stmt = $this->pdo->query("SELECT DISTINCT category FROM domain_drop_catalog WHERE category != '' ORDER BY category ASC");
        $dbCats = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        return array_values(array_unique(array_merge($canonicals, $dbCats)));
    }

    public function clearAll(bool $preserveOrdered = true): int
    {
        return $this->executeWithRetry(function (PDO $pdo) use ($preserveOrdered) {
            if ($preserveOrdered) {
                try {
                    $sql = "
                        DELETE FROM domain_drop_catalog
                        WHERE aliexpress_id NOT IN (
                            SELECT aliexpress_product_id 
                            FROM domain_drop_orders 
                            WHERE aliexpress_product_id IS NOT NULL AND aliexpress_product_id != ''
                        )
                    ";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    return $stmt->rowCount();
                } catch (\Throwable $e) {
                    error_log('[PdoDropshipCatalogRepository::clearAll] Falha ao executar exclusao segura: ' . $e->getMessage());
                    return 0;
                }
            }

            $stmt = $pdo->prepare("DELETE FROM domain_drop_catalog");
            $stmt->execute();
            return $stmt->rowCount();
        });
    }

    public function purgeDrafts(): int
    {
        return $this->executeWithRetry(function (PDO $pdo) {
            $stmt = $pdo->prepare("DELETE FROM domain_drop_catalog WHERE status != 'published'");
            $stmt->execute();
            return $stmt->rowCount();
        });
    }

    public function publishAll(): int
    {
        return $this->executeWithRetry(function (PDO $pdo) {
            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE domain_drop_catalog SET status = 'published', updated_at = ? WHERE status != 'published'");
            $stmt->execute([$now]);
            return $stmt->rowCount();
        });
    }

    public function findSimilarOffers(int $excludeId, string $category, string $title = '', int $limit = 4): array
    {
        $limit = max(1, min(20, $limit));
        $params = [$excludeId];

        $where = ["status = 'published'", "id != ?"];

        // 1. Prioriza filtro por categoria oficial
        if ($category !== '') {
            $catClause = $this->buildCategoryListClause($category, $params);
            $where[] = $catClause;
        }

        $sql = "SELECT * FROM domain_drop_catalog WHERE " . implode(' AND ', $where) . " ORDER BY sale_price_brl ASC, orders_count DESC LIMIT {$limit}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 2. Se a categoria tiver poucos itens, busca ofertas de produtos com alta reputação geral
        if (count($rows) < $limit) {
            $needed = $limit - count($rows);
            $existingIds = array_merge([$excludeId], array_column($rows, 'id'));
            $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
            $fallbackSql = "SELECT * FROM domain_drop_catalog WHERE status = 'published' AND id NOT IN ({$placeholders}) ORDER BY sale_price_brl ASC, rating DESC LIMIT {$needed}";
            $fbStmt = $this->pdo->prepare($fallbackSql);
            $fbStmt->execute($existingIds);
            $fbRows = $fbStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $rows = array_merge($rows, $fbRows);
        }

        return array_map([$this, 'hydrate'], $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): DropshipProductDto
    {
        $images = [];
        if (!empty($row['images'])) {
            $decoded = json_decode((string)$row['images'], true);
            if (is_array($decoded)) {
                $images = $decoded;
            }
        }

        $attributes = [];
        if (!empty($row['attributes'])) {
            $decoded = json_decode((string)$row['attributes'], true);
            if (is_array($decoded)) {
                $attributes = $decoded;
            }
        }

        // Auto-Cura e Retrocompatibilidade de Variantes para Produtos Históricos no Banco de Dados
        $cat = (string)($row['category'] ?? 'Geral');
        $title = (string)($row['title'] ?? '');
        $isClothing = in_array($cat, [
            'Moda, Roupas & Vestuário',
            'Roupas, Bolsas & Calçados',
            'Moda Íntima, Lingeries & Pijamas',
            'Moda Íntima & Lingerie',
            'Roupa Íntima & Pijamas',
            'Moda Fitness, Leggings & Academia',
        ], true) || preg_match('/\b(jaqueta|casaco|calça|calca|tênis|tenis|sapato|vestido|camisa|camiseta|shorts|bermuda|agasalho|conjunto|meia|roupa|blusa|moletom|lingerie|macacão|macacao|macaquinho|playsuit|playsuits|romper|rompers|body|bodies|top|cropped|biquíni|biquini|maiô|maio|tricô|trico|canelado|pijama|calçado|calcado|sandália|sandalia|chinelo|bota|sutiã|sutia|cueca|calcinha|bra|bras|bralette|bralettes|bojo|push up|push-up|corset|espartilho|underwear)\b/iu', $title);

        $isAccessory = (bool)preg_match('/\b(mochila|bolsa|carteira|mala|maleta|nécessaire|necessaire|cinto|porta-cartões|porta-cartoes)\b/iu', $title);

        $hasSize = false;
        if (!empty($attributes['variation_axes']) && is_array($attributes['variation_axes'])) {
            foreach (array_keys($attributes['variation_axes']) as $ax) {
                if (preg_match('/(?:tamanho|size|medida|aro)/i', (string)$ax)) {
                    $hasSize = true;
                    break;
                }
            }
        }

        if (empty($attributes['variants']) || ($isClothing && !$isAccessory && !$hasSize)) {
            $aeId = (string)($row['aliexpress_id'] ?? ($row['id'] ?? 'item'));
            $priceBrl = (float)($row['sale_price_brl'] ?? 0.0);
            $costUsd = (float)($row['cost_price_usd'] ?? 0.0);
            $mainImg = (string)($row['main_image_url'] ?? '');
            $isKids = isset($attributes['publico_alvo']) && str_contains(mb_strtolower((string)$attributes['publico_alvo'], 'UTF-8'), 'infantil');

            $synthesized = \DomainSystem\Plugins\domain_drop\Services\ProductMiningService::synthesizeMissingVariants(
                title: $title,
                category: $cat,
                aeId: $aeId,
                salePriceBrl: $priceBrl,
                costPriceUsd: $costUsd,
                mainImage: $mainImg,
                isKids: $isKids,
                existingProps: $attributes
            );
            if (!empty($synthesized['variants'])) {
                if ($isClothing && !$hasSize && !empty($attributes['variants'])) {
                    // Se já tinha variantes de Cor, preserva e combina com os tamanhos sintetizados
                    $sizes = $synthesized['variation_axes']['Tamanho'] ?? [];
                    if (!empty($sizes)) {
                        $attributes['variation_axes']['Tamanho'] = $sizes;
                        $expanded = [];
                        foreach ($attributes['variants'] as $v) {
                            foreach ($sizes as $sIdx => $sz) {
                                $szCode = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', (string)$sz) ?: (string)$sIdx);
                                $vClone = $v;
                                $vClone['sku_id'] = ($v['sku_id'] ?? $aeId) . '-' . $szCode;
                                $vClone['options']['Tamanho'] = $sz;
                                $expanded[] = $vClone;
                            }
                        }
                        $attributes['variants'] = $expanded;
                        $attributes['tamanhos_disponiveis'] = $sizes;
                    }
                } else {
                    $attributes['variants'] = $synthesized['variants'];
                    $attributes['variation_axes'] = $synthesized['variation_axes'];
                    if (!empty($synthesized['cores_disponiveis'])) {
                        $attributes['cores_disponiveis'] = $synthesized['cores_disponiveis'];
                    }
                    if (!empty($synthesized['tamanhos_disponiveis'])) {
                        $attributes['tamanhos_disponiveis'] = $synthesized['tamanhos_disponiveis'];
                    }
                }
            }
        }

        return new DropshipProductDto(
            aliexpressId: (string)$row['aliexpress_id'],
            title: (string)$row['title'],
            slug: (string)$row['slug'],
            costPriceUsd: (float)($row['cost_price_usd'] ?? 0.0),
            salePriceBrl: (float)($row['sale_price_brl'] ?? 0.0),
            mainImageUrl: (string)$row['main_image_url'],
            images: $images,
            category: (string)($row['category'] ?? 'Geral'),
            description: (string)($row['description'] ?? ''),
            rating: (float)($row['rating'] ?? 4.8),
            ordersCount: (int)($row['orders_count'] ?? 0),
            attributes: $attributes,
            status: (string)($row['status'] ?? 'published'),
            id: (int)$row['id']
        );
    }
}
