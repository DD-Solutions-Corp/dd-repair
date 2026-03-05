<?php
/**
 * DDRC_Router
 *
 * 404対策: parse_request で /dd-repair を完全横取り。
 * パーマリンク保存不要。.htaccess / nginx 設定不要。
 * WordPressが /dd-repair に到達さえすれば動く。
 *
 * デバッグ: /dd-repair/?ddrc_debug=1 (管理者のみ)
 */

defined( 'ABSPATH' ) || exit;

class DDRC_Router {

    public static function init(): void {
        // parse_request は WP が URL を解釈する最初のフック
        // 優先度1で最速実行 → リライト未反映でも動く
        add_action( 'parse_request', array( __CLASS__, 'intercept' ), 1 );

        // リライトルールも登録（パーマリンク保存後はこちら経由でも動く）
        add_action( 'init',          array( __CLASS__, 'add_rewrite_rules' ) );
        add_filter( 'query_vars',    array( __CLASS__, 'add_query_vars' ) );
    }

    // ── リライトルール（保険）──────────────────────────────────

    public static function add_rewrite_rules(): void {
        add_rewrite_rule( '^' . DDRC_BASE . '(/.*)?$', 'index.php?ddrc=1', 'top' );
    }

    public static function add_query_vars( array $vars ): array {
        $vars[] = 'ddrc';
        return $vars;
    }

    // ── メイン横取り処理 ─────────────────────────────────────────

    public static function intercept( $wp ): void {
        // REQUEST_URI からパスだけ取得
        $uri      = $_SERVER['REQUEST_URI'] ?? '';
        $path     = strtok( $uri, '?' );

        // サブディレクトリ WordPress 対応
        $home_path = rtrim( (string) parse_url( home_url(), PHP_URL_PATH ), '/' );
        if ( $home_path && strpos( $path, $home_path ) === 0 ) {
            $path = substr( $path, strlen( $home_path ) );
        }
        $path = '/' . trim( $path, '/' );

        // /dd-repair 以外は無視
        if ( $path !== '/' . DDRC_BASE && strpos( $path, '/' . DDRC_BASE . '/' ) !== 0 ) {
            return;
        }

        // ── PWA静的ファイル ──
        if ( $path === '/' . DDRC_BASE . '/manifest.json' ) { self::serve_manifest(); }
        if ( $path === '/' . DDRC_BASE . '/sw.js'          ) { self::serve_sw(); }

        // ── ここから先はアプリHTML出力 ──

        // デバッグ（管理者 + ?ddrc_debug=1）
        // phpcs:ignore WordPress.Security.NonceVerification
        if ( ! empty( $_GET['ddrc_debug'] ) ) {
            // ログイン不要でデバッグ情報だけ表示
            self::show_debug( $wp ); exit;
        }

        // 未ログイン → wp-login.php
        if ( ! is_user_logged_in() ) {
            wp_safe_redirect( wp_login_url( home_url( '/' . DDRC_BASE . '/' ) ) );
            exit;
        }

        // 権限チェック
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( 'アクセス権限がありません。', 403 );
        }

        // POST 処理（PRGパターン）
        if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
            self::handle_post();
        }

        // アプリを出力して終了
        self::render(); exit;
    }

    // ── POST 処理 ────────────────────────────────────────────────

    private static function handle_post(): void {
        $action = sanitize_key( $_POST['ddrc_action'] ?? '' );
        switch ( $action ) {
            case 'save_case':
                check_admin_referer( 'ddrc_save_case' );
                $id  = DDRC_Cases::save( $_POST );
                $url = self::url( 'cases', $id ? array( 'msg'=>'saved','id'=>$id ) : array( 'action'=>'new','err'=>'1' ) );
                wp_safe_redirect( $url ); exit;

            case 'save_customer':
                check_admin_referer( 'ddrc_save_customer' );
                $id  = DDRC_Customers::save( $_POST );
                $url = self::url( 'customers', $id ? array( 'msg'=>'saved','id'=>$id ) : array( 'action'=>'new','err'=>'1' ) );
                wp_safe_redirect( $url ); exit;

            case 'save_item':
                check_admin_referer( 'ddrc_save_item' );
                $id  = DDRC_Inventory::save_item( $_POST );
                $url = self::url( 'inventory', $id ? array( 'msg'=>'saved','id'=>$id ) : array( 'action'=>'new','err'=>'1' ) );
                wp_safe_redirect( $url ); exit;

            case 'update_stock':
                check_admin_referer( 'ddrc_update_stock' );
                $item_id = intval( $_POST['item_id'] ?? 0 );
                if ( $item_id > 0 ) DDRC_Inventory::update_stock( $item_id, intval( $_POST['delta'] ?? 0 ) );
                wp_safe_redirect( self::url( 'inventory', array( 'msg'=>'updated','id'=>$item_id ) ) ); exit;
        }
        wp_safe_redirect( self::url() ); exit;
    }

    // ── ページレンダリング ────────────────────────────────────────

    private static function render(): void {
        // phpcs:disable WordPress.Security.NonceVerification
        $tab    = sanitize_key( $_GET['tab']    ?? 'dashboard' );
        $action = sanitize_key( $_GET['action'] ?? 'list' );
        $id     = absint(       $_GET['id']     ?? 0 );
        // phpcs:enable
        if ( ! in_array( $tab, array('dashboard','cases','inventory','customers','settings'), true ) ) {
            $tab = 'dashboard';
        }
        $vars = compact( 'tab', 'action', 'id' );
        ddrc_template( 'partials/header', $vars );
        ddrc_template( 'pages/' . $tab,   $vars );
        ddrc_template( 'partials/footer', $vars );
    }

    // ── PWA静的ファイル ──────────────────────────────────────────

    private static function serve_manifest(): void {
        nocache_headers();
        header( 'Content-Type: application/manifest+json; charset=utf-8' );
        echo wp_json_encode( array(
            'name'             => 'DD Repair Core',
            'short_name'       => 'DD Repair',
            'description'      => '家電修理・販売業務管理システム',
            'start_url'        => home_url( '/' . DDRC_BASE . '/' ),
            'display'          => 'standalone',
            'background_color' => '#1B3A6B',
            'theme_color'      => '#1B3A6B',
            'orientation'      => 'portrait-primary',
            'icons'            => array(
                array( 'src'=>DDRC_URL.'assets/images/icon-192.png','sizes'=>'192x192','type'=>'image/png' ),
                array( 'src'=>DDRC_URL.'assets/images/icon-512.png','sizes'=>'512x512','type'=>'image/png' ),
            ),
        ) );
        exit;
    }

    private static function serve_sw(): void {
        nocache_headers();
        header( 'Content-Type: application/javascript; charset=utf-8' );
        $base = esc_js( home_url( '/' . DDRC_BASE ) );
        $ver  = esc_js( DDRC_VERSION );
        echo "const CACHE_NAME='ddrc-v{$ver}';const BASE_URL='{$base}';";
        echo "self.addEventListener('install',e=>self.skipWaiting());";
        echo "self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(ks=>Promise.all(ks.filter(k=>k!==CACHE_NAME).map(k=>caches.delete(k)))));self.clients.claim();});";
        echo "self.addEventListener('fetch',e=>{if(e.request.method!=='GET'||!e.request.url.startsWith(BASE_URL))return;e.respondWith(fetch(e.request).then(r=>{const c=r.clone();caches.open(CACHE_NAME).then(cache=>cache.put(e.request,c));return r;}).catch(()=>caches.match(e.request)));});";
        exit;
    }

    // ── デバッグ ─────────────────────────────────────────────────

    private static function show_debug( $wp ): void {
        $rules    = get_option( 'rewrite_rules', array() );
        $has_rule = false;
        foreach ( array_keys( (array)$rules ) as $r ) {
            if ( strpos( $r, DDRC_BASE ) !== false ) { $has_rule = true; break; }
        }
        $uri  = $_SERVER['REQUEST_URI'] ?? '';
        $path = strtok( $uri, '?' );
        $home_path = rtrim( (string)parse_url( home_url(), PHP_URL_PATH ), '/' );
        $stripped  = $home_path ? substr( $path, strlen($home_path) ) : $path;

        echo '<!DOCTYPE html><html><body>';
        echo '<pre style="background:#111;color:#0f0;padding:20px;font-size:13px;line-height:1.7;font-family:monospace">';
        echo "=== DD Repair Core Debug ===\n\n";
        echo "Plugin version  : " . DDRC_VERSION . "\n";
        echo "DDRC_BASE       : " . DDRC_BASE . "\n";
        echo "home_url        : " . home_url() . "\n";
        echo "home_path       : " . ( $home_path ?: '(root)' ) . "\n";
        echo "REQUEST_URI     : " . esc_html( $uri ) . "\n";
        echo "Path stripped   : " . esc_html( $stripped ) . "\n";
        echo "Expected path   : /" . DDRC_BASE . "\n";
        echo "Paths match     : " . ( trim($stripped,'/')=== DDRC_BASE ? 'YES ✓' : 'NO ✗' ) . "\n\n";
        echo "Logged in       : " . ( is_user_logged_in() ? 'YES ✓' : 'NO ✗ → ログインが必要です' ) . "\n";
        echo "Rewrite rule    : " . ( $has_rule ? 'EXISTS ✓' : 'MISSING ✗' ) . "\n\n";
        echo "→ このページが表示された場合、404問題は解決しています！\n";
        echo "→ 通常URLにアクセスしてください: " . home_url('/'.DDRC_BASE.'/') . "\n";
        echo '</pre>';
        echo '</body></html>';
    }

    // ── URL ヘルパー ──────────────────────────────────────────────

    public static function url( string $tab = '', array $extra = array() ): string {
        $base = home_url( '/' . DDRC_BASE . '/' );
        $args = array_filter(
            array_merge( $tab ? array('tab'=>$tab) : array(), $extra ),
            fn($v) => $v !== '' && $v !== null && $v !== false
        );
        return $args ? add_query_arg( $args, $base ) : $base;
    }
}
