<?php

declare(strict_types=1);

return [
    'up' => function (\PDO $db) {
        $driver = $db->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            try {
                $colsStmt = $db->query("SHOW COLUMNS FROM domain_drop_orders");
                $existingCols = $colsStmt ? array_column($colsStmt->fetchAll(\PDO::FETCH_ASSOC), 'Field') : [];
            } catch (\Throwable $e) {
                $existingCols = [];
            }

            if (!empty($existingCols)) {
                $alterColumns = [
                    'selected_sku'        => "ALTER TABLE domain_drop_orders ADD COLUMN `selected_sku` VARCHAR(100) NULL AFTER `aliexpress_product_id`",
                    'selected_color'      => "ALTER TABLE domain_drop_orders ADD COLUMN `selected_color` VARCHAR(100) NULL AFTER `selected_sku`",
                    'selected_size'       => "ALTER TABLE domain_drop_orders ADD COLUMN `selected_size` VARCHAR(100) NULL AFTER `selected_color`",
                    'quantity'            => "ALTER TABLE domain_drop_orders ADD COLUMN `quantity` INT NOT NULL DEFAULT 1 AFTER `selected_size`",
                    'supplier_url'        => "ALTER TABLE domain_drop_orders ADD COLUMN `supplier_url` TEXT NULL AFTER `product_title`",
                    'supplier_store'      => "ALTER TABLE domain_drop_orders ADD COLUMN `supplier_store` VARCHAR(255) NULL AFTER `supplier_url`",
                    'shipping_type'       => "ALTER TABLE domain_drop_orders ADD COLUMN `shipping_type` VARCHAR(100) NULL AFTER `expected_delivery_days`",
                    'shipping_cost_brl'   => "ALTER TABLE domain_drop_orders ADD COLUMN `shipping_cost_brl` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `shipping_type`",
                    'fulfillment_dossier' => "ALTER TABLE domain_drop_orders ADD COLUMN `fulfillment_dossier` MEDIUMTEXT NULL AFTER `shipping_address`",
                ];

                foreach ($alterColumns as $col => $sql) {
                    if (!in_array($col, $existingCols, true)) {
                        try {
                            $db->exec($sql);
                        } catch (\Throwable $e) {
                            // Coluna pode já existir
                        }
                    }
                }

                // Ajusta tamanhos e colunas maiores para evitar overflow ou truncamento
                try {
                    $db->exec("ALTER TABLE domain_drop_orders MODIFY `customer_email` VARCHAR(190) NOT NULL, MODIFY `customer_name` VARCHAR(150) NOT NULL, MODIFY `seller_name` VARCHAR(255) NULL, MODIFY `shipping_address` MEDIUMTEXT NULL");
                } catch (\Throwable $e) {}

                // Índices de performance no MySQL
                try {
                    $idxStmt = $db->query("SHOW INDEX FROM domain_drop_orders");
                    $existingIndexes = $idxStmt ? array_column($idxStmt->fetchAll(\PDO::FETCH_ASSOC), 'Key_name') : [];
                    if (!in_array('idx_dd_order_st', $existingIndexes, true)) {
                        $db->exec("ALTER TABLE domain_drop_orders ADD INDEX idx_dd_order_st (status)");
                    }
                    if (!in_array('idx_dd_order_email', $existingIndexes, true)) {
                        $db->exec("ALTER TABLE domain_drop_orders ADD INDEX idx_dd_order_email (customer_email)");
                    }
                    if (!in_array('idx_dd_order_created', $existingIndexes, true)) {
                        $db->exec("ALTER TABLE domain_drop_orders ADD INDEX idx_dd_order_created (created_at)");
                    }
                } catch (\Throwable $e) {}
            }

            // Garante índices no catálogo e disputas
            try {
                $catIdx = $db->query("SHOW INDEX FROM domain_drop_catalog");
                $catIndexes = $catIdx ? array_column($catIdx->fetchAll(\PDO::FETCH_ASSOC), 'Key_name') : [];
                if (!in_array('idx_dd_status', $catIndexes, true)) {
                    $db->exec("ALTER TABLE domain_drop_catalog ADD INDEX idx_dd_status (status)");
                }
            } catch (\Throwable $e) {}

            return;
        }

        // SQLite
        try {
            $colsStmt = $db->query("PRAGMA table_info(domain_drop_orders)");
            $existingCols = $colsStmt ? array_column($colsStmt->fetchAll(\PDO::FETCH_ASSOC), 'name') : [];
        } catch (\Throwable $e) {
            $existingCols = [];
        }

        if (!empty($existingCols)) {
            $sqliteColumns = [
                'selected_sku'        => "ALTER TABLE domain_drop_orders ADD COLUMN selected_sku TEXT NULL",
                'selected_color'      => "ALTER TABLE domain_drop_orders ADD COLUMN selected_color TEXT NULL",
                'selected_size'       => "ALTER TABLE domain_drop_orders ADD COLUMN selected_size TEXT NULL",
                'quantity'            => "ALTER TABLE domain_drop_orders ADD COLUMN quantity INTEGER NOT NULL DEFAULT 1",
                'supplier_url'        => "ALTER TABLE domain_drop_orders ADD COLUMN supplier_url TEXT NULL",
                'supplier_store'      => "ALTER TABLE domain_drop_orders ADD COLUMN supplier_store TEXT NULL",
                'shipping_type'       => "ALTER TABLE domain_drop_orders ADD COLUMN shipping_type TEXT NULL",
                'shipping_cost_brl'   => "ALTER TABLE domain_drop_orders ADD COLUMN shipping_cost_brl REAL NOT NULL DEFAULT 0.0",
                'fulfillment_dossier' => "ALTER TABLE domain_drop_orders ADD COLUMN fulfillment_dossier TEXT NULL",
            ];

            foreach ($sqliteColumns as $col => $sql) {
                if (!in_array($col, $existingCols, true)) {
                    try {
                        $db->exec($sql);
                    } catch (\Throwable $e) {}
                }
            }

            // Índices SQLite
            try {
                $db->exec("CREATE INDEX IF NOT EXISTS idx_dd_order_st ON domain_drop_orders (status)");
                $db->exec("CREATE INDEX IF NOT EXISTS idx_dd_order_email ON domain_drop_orders (customer_email)");
                $db->exec("CREATE INDEX IF NOT EXISTS idx_dd_order_created ON domain_drop_orders (created_at)");
                $db->exec("CREATE INDEX IF NOT EXISTS idx_dd_cat_status ON domain_drop_catalog (status)");
                $db->exec("CREATE INDEX IF NOT EXISTS idx_dd_disp_status ON domain_drop_disputes (mediation_status)");
            } catch (\Throwable $e) {}
        }
    },
    'down' => function (\PDO $db) {
        // Rollback opcional
    }
];
