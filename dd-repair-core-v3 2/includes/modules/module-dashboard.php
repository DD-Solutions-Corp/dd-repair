<?php
/**
 * DDRC_Dashboard — ダッシュボードモジュール
 */

defined( 'ABSPATH' ) || exit;

class DDRC_Dashboard {

    /**
     * KPI データを一括取得（wp_cache でキャッシュ）
     */
    public static function get_kpi(): array {
        $cache_key = 'ddrc_kpi_' . get_current_user_id();
        $cached    = wp_cache_get( $cache_key, 'ddrc' );
        if ( false !== $cached ) return $cached;

        $data = array(
            'today_cases'     => DDRC_Cases::count_today(),
            'pending_cases'   => DDRC_Cases::count_pending(),
            'stock_alerts'    => DDRC_Inventory::count_alerts(),
            'month_sales'     => DDRC_Cases::sum_month_sales(),
            'total_customers' => DDRC_Customers::count_total(),
        );

        wp_cache_set( $cache_key, $data, 'ddrc', 300 ); // 5分
        return $data;
    }
}
