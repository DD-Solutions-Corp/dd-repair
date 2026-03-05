<?php
/**
 * Plugin Name: DD Repair Core
 * Plugin URI:  https://dd-solutions.jp
 * Description: 家電修理・販売業務管理PWA。/dd-repair でアクセス。
 * Version:     3.2.0
 * Author:      DD Solutions
 * Text Domain: dd-repair-core
 * Requires PHP: 7.4
 * Requires at least: 6.0
 */

defined( 'ABSPATH' ) || exit;

define( 'DDRC_VERSION', '3.2.0' );
define( 'DDRC_DIR',     plugin_dir_path( __FILE__ ) );
define( 'DDRC_URL',     plugin_dir_url( __FILE__ ) );
define( 'DDRC_BASE',    'dd-repair' );

// コアクラス
require_once DDRC_DIR . 'includes/class-ddrc-db.php';
require_once DDRC_DIR . 'includes/functions-helpers.php';

// モジュール
require_once DDRC_DIR . 'includes/modules/module-cases.php';
require_once DDRC_DIR . 'includes/modules/module-customers.php';
require_once DDRC_DIR . 'includes/modules/module-inventory.php';
require_once DDRC_DIR . 'includes/modules/module-dashboard.php';

// ルーター（最後）
require_once DDRC_DIR . 'includes/class-ddrc-router.php';

register_activation_hook( __FILE__, 'ddrc_activate' );
register_deactivation_hook( __FILE__, 'ddrc_deactivate' );

function ddrc_activate(): void {
    DDRC_DB::activate();
    DDRC_Router::add_rewrite_rules();
    flush_rewrite_rules();
}

function ddrc_deactivate(): void {
    flush_rewrite_rules();
}

add_action( 'plugins_loaded', 'ddrc_init' );

function ddrc_init(): void {
    DDRC_DB::maybe_upgrade();
    DDRC_Router::init();
}
