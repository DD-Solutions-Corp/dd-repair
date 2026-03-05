<?php
defined( 'ABSPATH' ) || exit;

class DDRC_DB {

    const VERSION_KEY = 'ddrc_db_version';
    const VERSION     = '3.0.0';

    public static function activate(): void {
        self::create_tables();
        update_option( self::VERSION_KEY, self::VERSION );
        flush_rewrite_rules();
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }

    public static function maybe_upgrade(): void {
        if ( version_compare( (string) get_option( self::VERSION_KEY, '0' ), self::VERSION, '<' ) ) {
            self::create_tables();
            update_option( self::VERSION_KEY, self::VERSION );
        }
    }

    private static function create_tables(): void {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $c = $wpdb->get_charset_collate();

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dd_customers` (
            `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name`        VARCHAR(255) NOT NULL DEFAULT '',
            `name_kana`   VARCHAR(255) NOT NULL DEFAULT '',
            `phone1`      VARCHAR(50)  NOT NULL DEFAULT '',
            `phone2`      VARCHAR(50)  NOT NULL DEFAULT '',
            `email`       VARCHAR(255) NOT NULL DEFAULT '',
            `postal_code` VARCHAR(20)  NOT NULL DEFAULT '',
            `prefecture`  VARCHAR(50)  NOT NULL DEFAULT '',
            `city`        VARCHAR(100) NOT NULL DEFAULT '',
            `address1`    VARCHAR(255) NOT NULL DEFAULT '',
            `address2`    VARCHAR(255) NOT NULL DEFAULT '',
            `notes`       TEXT,
            `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_name`  (`name`(100)),
            KEY `idx_phone` (`phone1`(20))
        ) ENGINE=InnoDB $c;" );

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ddrc_cases` (
            `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `customer_id`      BIGINT UNSIGNED DEFAULT NULL,
            `customer_name`    VARCHAR(255) NOT NULL DEFAULT '',
            `case_type`        ENUM('repair','sale') NOT NULL DEFAULT 'repair',
            `case_status`      ENUM('draft','estimated','in_progress','completed','cancelled') NOT NULL DEFAULT 'draft',
            `payment_status`   ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
            `visit_date`       DATE DEFAULT NULL,
            `visit_time`       TIME DEFAULT NULL,
            `phone1`           VARCHAR(50) NOT NULL DEFAULT '',
            `postal_code`      VARCHAR(20) NOT NULL DEFAULT '',
            `prefecture`       VARCHAR(50) NOT NULL DEFAULT '',
            `city`             VARCHAR(100) NOT NULL DEFAULT '',
            `address1`         VARCHAR(255) NOT NULL DEFAULT '',
            `product_category` VARCHAR(100) NOT NULL DEFAULT '',
            `maker`            VARCHAR(100) NOT NULL DEFAULT '',
            `product_name`     VARCHAR(255) NOT NULL DEFAULT '',
            `symptom`          TEXT,
            `work_detail`      TEXT,
            `estimate_amount`  INT NOT NULL DEFAULT 0,
            `total_amount`     INT NOT NULL DEFAULT 0,
            `paid_amount`      INT NOT NULL DEFAULT 0,
            `notes`            TEXT,
            `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_customer`  (`customer_id`),
            KEY `idx_status`    (`case_status`),
            KEY `idx_visit`     (`visit_date`),
            KEY `idx_type_status_date` (`case_type`,`case_status`,`visit_date`)
        ) ENGINE=InnoDB $c;" );

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ddrc_items` (
            `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `item_name`          VARCHAR(255) NOT NULL DEFAULT '',
            `maker_name`         VARCHAR(255) NOT NULL DEFAULT '',
            `jan_code`           VARCHAR(64)  DEFAULT NULL,
            `product_category`   VARCHAR(100) NOT NULL DEFAULT '',
            `item_type`          ENUM('product','part','consumable') NOT NULL DEFAULT 'product',
            `cost_price`         INT NOT NULL DEFAULT 0,
            `retail_price`       INT NOT NULL DEFAULT 0,
            `stock_quantity`     INT NOT NULL DEFAULT 0,
            `min_stock_quantity` INT NOT NULL DEFAULT 0,
            `notes`              TEXT,
            `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_jan`      (`jan_code`),
            KEY       `idx_category`  (`product_category`(50)),
            KEY       `idx_stock`     (`stock_quantity`)
        ) ENGINE=InnoDB $c;" );
    }
}
