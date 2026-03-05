<?php
/**
 * DDRC_Cases — 案件モジュール
 * 案件データの全CRUD操作を担当。
 */

defined( 'ABSPATH' ) || exit;

class DDRC_Cases {

    private static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'ddrc_cases';
    }

    /**
     * 案件一覧取得
     */
    public static function get_list( array $args = array() ): array {
        global $wpdb;
        $args = wp_parse_args( $args, array(
            'case_type'   => '',
            'case_status' => '',
            'search'      => '',
            'date_from'   => '',
            'date_to'     => '',
            'limit'       => 30,
            'offset'      => 0,
        ) );

        $t  = self::table();
        $w  = array( '1=1' );
        $p  = array();

        if ( $args['case_type'] ) {
            $w[] = 'case_type = %s'; $p[] = $args['case_type'];
        }
        if ( $args['case_status'] ) {
            $w[] = 'case_status = %s'; $p[] = $args['case_status'];
        }
        if ( $args['search'] ) {
            $l   = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $w[] = '(customer_name LIKE %s OR phone1 LIKE %s OR address1 LIKE %s OR product_name LIKE %s)';
            $p   = array_merge( $p, array( $l, $l, $l, $l ) );
        }
        if ( $args['date_from'] ) { $w[] = 'visit_date >= %s'; $p[] = $args['date_from']; }
        if ( $args['date_to'] )   { $w[] = 'visit_date <= %s'; $p[] = $args['date_to']; }

        $p[] = (int) $args['limit'];
        $p[] = (int) $args['offset'];

        $sql = "SELECT * FROM `{$t}` WHERE " . implode( ' AND ', $w ) . " ORDER BY updated_at DESC LIMIT %d OFFSET %d";

        return (array) $wpdb->get_results( $wpdb->prepare( $sql, ...$p ), ARRAY_A );
    }

    /**
     * 案件1件取得
     */
    public static function get( int $id ): ?array {
        global $wpdb;
        $t = self::table();
        $r = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$t}` WHERE id=%d LIMIT 1", $id ), ARRAY_A );
        return $r ?: null;
    }

    /**
     * 今日のスケジュール
     */
    public static function get_today( int $limit = 30 ): array {
        global $wpdb;
        $t     = self::table();
        $today = gmdate( 'Y-m-d' );
        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `{$t}` WHERE visit_date=%s AND case_status NOT IN ('cancelled') ORDER BY COALESCE(visit_time,'23:59') ASC LIMIT %d",
                $today, $limit
            ),
            ARRAY_A
        );
    }

    /**
     * 案件保存（新規＋更新）
     * @return int|false  保存成功時: ID, 失敗時: false
     */
    public static function save( array $d ) {
        global $wpdb;
        $t  = self::table();
        $id = absint( $d['id'] ?? 0 );

        $f = array(
            'customer_id'      => $d['customer_id'] ? absint( $d['customer_id'] ) : null,
            'customer_name'    => sanitize_text_field( $d['customer_name'] ?? '' ),
            'case_type'        => in_array( $d['case_type'] ?? '', array( 'repair', 'sale' ), true ) ? $d['case_type'] : 'repair',
            'case_status'      => in_array( $d['case_status'] ?? '', array( 'draft', 'estimated', 'in_progress', 'completed', 'cancelled' ), true ) ? $d['case_status'] : 'draft',
            'payment_status'   => in_array( $d['payment_status'] ?? '', array( 'unpaid', 'partial', 'paid' ), true ) ? $d['payment_status'] : 'unpaid',
            'visit_date'       => sanitize_text_field( $d['visit_date'] ?? '' ) ?: null,
            'visit_time'       => sanitize_text_field( $d['visit_time'] ?? '' ) ?: null,
            'phone1'           => sanitize_text_field( $d['phone1'] ?? '' ),
            'city'             => sanitize_text_field( $d['city'] ?? '' ),
            'address1'         => sanitize_text_field( $d['address1'] ?? '' ),
            'product_category' => sanitize_text_field( $d['product_category'] ?? '' ),
            'maker'            => sanitize_text_field( $d['maker'] ?? '' ),
            'product_name'     => sanitize_text_field( $d['product_name'] ?? '' ),
            'symptom'          => sanitize_textarea_field( $d['symptom'] ?? '' ),
            'work_detail'      => sanitize_textarea_field( $d['work_detail'] ?? '' ),
            'estimate_amount'  => is_numeric( $d['estimate_amount'] ?? '' ) ? (int) $d['estimate_amount'] : 0,
            'total_amount'     => is_numeric( $d['total_amount'] ?? '' )    ? (int) $d['total_amount']    : 0,
            'notes'            => sanitize_textarea_field( $d['notes'] ?? '' ),
        );

        if ( $id > 0 ) {
            $r = $wpdb->update( $t, $f, array( 'id' => $id ) );
            return $r !== false ? $id : false;
        } else {
            $r = $wpdb->insert( $t, $f );
            return $r !== false ? (int) $wpdb->insert_id : false;
        }
    }

    // ── KPI用集計 ──

    public static function count_today(): int {
        global $wpdb;
        $t = self::table();
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM `{$t}` WHERE visit_date=%s AND case_status!='cancelled'",
            gmdate( 'Y-m-d' )
        ) );
    }

    public static function count_pending(): int {
        global $wpdb;
        $t = self::table();
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$t}` WHERE case_status IN ('draft','estimated')" );
    }

    public static function sum_month_sales(): int {
        global $wpdb;
        $t = self::table();
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COALESCE(SUM(total_amount),0) FROM `{$t}` WHERE visit_date>=%s AND case_status IN ('completed')",
            gmdate( 'Y-m-01' )
        ) );
    }
}
