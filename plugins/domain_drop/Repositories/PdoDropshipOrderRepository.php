<?php

declare(strict_types=1);

namespace DomainSystem\Plugins\domain_drop\Repositories;

use DomainSystem\Core\Contracts\DatabaseConnectionInterface;
use DomainSystem\Plugins\domain_drop\Contracts\DropshipOrderRepositoryInterface;
use PDO;

/**
 * Repositório PDO para Ordens de Dropshipping (SRP / DIP).
 */
class PdoDropshipOrderRepository implements DropshipOrderRepositoryInterface
{
    private PDO $pdo;

    /** @var array<int, string>|null */
    private ?array $tableColumns = null;

    public function __construct(PDO|DatabaseConnectionInterface $connection)
    {
        $this->pdo = $connection instanceof DatabaseConnectionInterface
            ? $connection->getPdo()
            : $connection;
        DomainDropSchemaEnsurer::ensure($this->pdo);
    }

    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $createdAt = (string)($data['created_at'] ?? $now);
        $updatedAt = (string)($data['updated_at'] ?? $createdAt);

        $columns = [
            'order_ref',
            'customer_name',
            'customer_email',
            'customer_document',
            'shipping_address',
            'aliexpress_order_id',
            'tracking_code',
            'status',
            'total_paid_brl',
            'created_at',
            'updated_at',
        ];

        $params = [
            ':order_ref'           => (string)($data['order_ref'] ?? uniqid('ORD_DROP_')),
            ':customer_name'       => (string)($data['customer_name'] ?? 'Cliente'),
            ':customer_email'      => strtolower(trim((string)($data['customer_email'] ?? ''))),
            ':customer_document'   => isset($data['customer_document']) ? (string)$data['customer_document'] : null,
            ':shipping_address'    => isset($data['shipping_address'])
                ? (is_array($data['shipping_address']) ? json_encode($data['shipping_address'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (string)$data['shipping_address'])
                : null,
            ':aliexpress_order_id' => isset($data['aliexpress_order_id']) ? (string)$data['aliexpress_order_id'] : null,
            ':tracking_code'       => isset($data['tracking_code']) ? (string)$data['tracking_code'] : null,
            ':status'              => (string)($data['status'] ?? 'pending'),
            ':total_paid_brl'      => (float)($data['total_paid_brl'] ?? 0.0),
            ':created_at'          => $createdAt,
            ':updated_at'          => $updatedAt,
        ];

        $origin = strtoupper((string)($data['seller_origin'] ?? 'BR'));
        $defaultDays = ($origin === 'CN') ? 25 : 7;

        $optionalMap = [
            'seller_origin'          => $origin,
            'seller_name'            => isset($data['seller_name']) ? (string)$data['seller_name'] : null,
            'seller_email'           => isset($data['seller_email']) ? (string)$data['seller_email'] : null,
            'expected_delivery_days' => isset($data['expected_delivery_days']) ? max(1, (int)$data['expected_delivery_days']) : $defaultDays,
            'sdk_delivered_at'       => isset($data['sdk_delivered_at']) ? (string)$data['sdk_delivered_at'] : null,
            'sdk_tracking_summary'   => isset($data['sdk_tracking_summary']) ? (string)$data['sdk_tracking_summary'] : null,
            'product_slug'           => isset($data['product_slug']) ? (string)$data['product_slug'] : null,
            'product_title'          => isset($data['product_title']) ? (string)$data['product_title'] : null,
            'aliexpress_product_id'  => isset($data['aliexpress_product_id']) ? (string)$data['aliexpress_product_id'] : null,
            'selected_sku'           => isset($data['selected_sku']) ? (string)$data['selected_sku'] : null,
            'selected_color'         => isset($data['selected_color']) ? (string)$data['selected_color'] : null,
            'selected_size'          => isset($data['selected_size']) ? (string)$data['selected_size'] : null,
            'quantity'               => isset($data['quantity']) ? max(1, (int)$data['quantity']) : 1,
            'supplier_url'           => isset($data['supplier_url']) ? (string)$data['supplier_url'] : null,
            'supplier_store'         => isset($data['supplier_store']) ? (string)$data['supplier_store'] : null,
            'shipping_type'          => isset($data['shipping_type']) ? (string)$data['shipping_type'] : null,
            'shipping_cost_brl'      => isset($data['shipping_cost_brl']) ? (float)$data['shipping_cost_brl'] : 0.0,
            'fulfillment_dossier'    => isset($data['fulfillment_dossier'])
                ? (is_array($data['fulfillment_dossier']) ? (json_encode($data['fulfillment_dossier'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: null) : (string)$data['fulfillment_dossier'])
                : null,
        ];

        $existingCols = $this->getTableColumns();
        foreach ($optionalMap as $col => $val) {
            if (in_array($col, $existingCols, true)) {
                $columns[] = $col;
                $params[':' . $col] = $val;
            }
        }

        $placeholders = array_map(static fn (string $c): string => ':' . $c, $columns);
        $sql = 'INSERT INTO domain_drop_orders (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int)$this->pdo->lastInsertId();
    }

    public function findByOrderRef(string $orderRef): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM domain_drop_orders WHERE order_ref = :ref LIMIT 1");
        $stmt->execute([':ref' => $orderRef]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function findByCustomerEmail(string $email, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM domain_drop_orders WHERE LOWER(customer_email) = :email ORDER BY created_at DESC LIMIT " . max(1, (int)$limit)
        );
        $stmt->execute([':email' => strtolower(trim($email))]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function updateFulfillment(string $orderRef, string $status, ?string $aliExpressOrderId = null, ?string $trackingCode = null): bool
    {
        $fields = ['status = :status', 'updated_at = :updated_at'];
        $params = [
            ':ref'        => $orderRef,
            ':status'     => $status,
            ':updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($aliExpressOrderId !== null) {
            $fields[] = 'aliexpress_order_id = :ae_id';
            $params[':ae_id'] = $aliExpressOrderId;
        }

        if ($trackingCode !== null) {
            $fields[] = 'tracking_code = :track';
            $params[':track'] = $trackingCode;
        }

        if ($status === 'delivered' && in_array('sdk_delivered_at', $this->getTableColumns(), true)) {
            $fields[] = 'sdk_delivered_at = COALESCE(sdk_delivered_at, :delivered_at)';
            $params[':delivered_at'] = date('Y-m-d H:i:s');
        }

        $sql = "UPDATE domain_drop_orders SET " . implode(', ', $fields) . " WHERE order_ref = :ref";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public function updateOrderDossier(string $orderRef, array $data): bool
    {
        $existingCols = $this->getTableColumns();
        $fields = ['updated_at = :updated_at'];
        $params = [
            ':ref'        => $orderRef,
            ':updated_at' => date('Y-m-d H:i:s'),
        ];

        $mapping = [
            'status'              => 'status',
            'customer_name'       => 'customer_name',
            'customer_email'      => 'customer_email',
            'customer_document'   => 'customer_document',
            'total_paid_brl'      => 'total_paid_brl',
            'product_title'       => 'product_title',
            'product_slug'        => 'product_slug',
            'aliexpress_product_id' => 'aliexpress_product_id',
            'selected_sku'        => 'selected_sku',
            'selected_color'      => 'selected_color',
            'selected_size'       => 'selected_size',
            'quantity'            => 'quantity',
            'supplier_url'        => 'supplier_url',
            'supplier_store'      => 'supplier_store',
            'shipping_type'       => 'shipping_type',
            'shipping_cost_brl'   => 'shipping_cost_brl',
            'seller_origin'       => 'seller_origin',
            'seller_name'         => 'seller_name',
            'seller_email'        => 'seller_email',
            'expected_delivery_days' => 'expected_delivery_days',
        ];

        foreach ($mapping as $key => $col) {
            if (isset($data[$key]) && in_array($col, $existingCols, true)) {
                $fields[] = "{$col} = :{$key}";
                $params[":{$key}"] = $data[$key];
            }
        }

        if (isset($data['shipping_address']) && in_array('shipping_address', $existingCols, true)) {
            $fields[] = "shipping_address = :shipping_address";
            $params[':shipping_address'] = is_array($data['shipping_address'])
                ? (json_encode($data['shipping_address'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: null)
                : (string)$data['shipping_address'];
        }

        if (isset($data['fulfillment_dossier']) && in_array('fulfillment_dossier', $existingCols, true)) {
            $fields[] = "fulfillment_dossier = :fulfillment_dossier";
            $params[':fulfillment_dossier'] = is_array($data['fulfillment_dossier'])
                ? (json_encode($data['fulfillment_dossier'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: null)
                : (string)$data['fulfillment_dossier'];
        }

        $sql = "UPDATE domain_drop_orders SET " . implode(', ', $fields) . " WHERE order_ref = :ref";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public function updateSellerAndSla(
        string $orderRef,
        string $sellerOrigin,
        int $expectedDays,
        ?string $sellerName = null,
        ?string $sellerEmail = null
    ): bool {
        $existingCols = $this->getTableColumns();
        if (!in_array('seller_origin', $existingCols, true)) {
            return false;
        }

        $fields = [
            'seller_origin = :origin',
            'expected_delivery_days = :days',
            'updated_at = :updated_at',
        ];
        $params = [
            ':ref'        => $orderRef,
            ':origin'     => strtoupper($sellerOrigin) === 'CN' ? 'CN' : 'BR',
            ':days'       => max(1, min(90, $expectedDays)),
            ':updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($sellerName !== null && in_array('seller_name', $existingCols, true)) {
            $fields[] = 'seller_name = :sname';
            $params[':sname'] = $sellerName;
        }
        if ($sellerEmail !== null && in_array('seller_email', $existingCols, true)) {
            $fields[] = 'seller_email = :semail';
            $params[':semail'] = $sellerEmail;
        }

        $sql = "UPDATE domain_drop_orders SET " . implode(', ', $fields) . " WHERE order_ref = :ref";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public function updateSdkTracking(
        string $orderRef,
        ?string $sdkDeliveredAt,
        ?string $summaryJson,
        ?string $newStatus = null
    ): bool {
        $existingCols = $this->getTableColumns();
        $fields = ['updated_at = :updated_at'];
        $params = [
            ':ref'        => $orderRef,
            ':updated_at' => date('Y-m-d H:i:s'),
        ];

        if (in_array('sdk_delivered_at', $existingCols, true) && $sdkDeliveredAt !== null) {
            $fields[] = 'sdk_delivered_at = :delivered_at';
            $params[':delivered_at'] = $sdkDeliveredAt;
        }
        if (in_array('sdk_tracking_summary', $existingCols, true) && $summaryJson !== null) {
            $fields[] = 'sdk_tracking_summary = :summary';
            $params[':summary'] = $summaryJson;
        }
        if ($newStatus !== null && $newStatus !== '') {
            $fields[] = 'status = :status';
            $params[':status'] = $newStatus;
        }

        $sql = "UPDATE domain_drop_orders SET " . implode(', ', $fields) . " WHERE order_ref = :ref";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public function list(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = :st";
            $params[':st'] = (string)$filters['status'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . (string)$filters['search'] . '%';
            $where[] = "(order_ref LIKE :q1 OR customer_name LIKE :q2 OR customer_email LIKE :q3 OR tracking_code LIKE :q4 OR product_title LIKE :q5 OR aliexpress_order_id LIKE :q6 OR supplier_store LIKE :q7)";
            $params[':q1'] = $term;
            $params[':q2'] = $term;
            $params[':q3'] = $term;
            $params[':q4'] = $term;
            $params[':q5'] = $term;
            $params[':q6'] = $term;
            $params[':q7'] = $term;
        }

        $sql = "SELECT * FROM domain_drop_orders";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);
        $sql .= " ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<int, string>
     */
    private function getTableColumns(): array
    {
        if ($this->tableColumns !== null) {
            return $this->tableColumns;
        }

        try {
            $driver = (string)$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'mysql') {
                $stmt = $this->pdo->query("SHOW COLUMNS FROM domain_drop_orders");
                $this->tableColumns = $stmt ? array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field') : [];
            } else {
                $stmt = $this->pdo->query("PRAGMA table_info(domain_drop_orders)");
                $this->tableColumns = $stmt ? array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name') : [];
            }
        } catch (\Throwable $e) {
            $this->tableColumns = [];
        }

        return $this->tableColumns;
    }
}
