<?php
/**
 * DDRC_Customers — 顧客モジュール
 */

defined( 'ABSPATH' ) || exit;

class DDRC_Customers {

    private static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'dd_customers';
    }

    public static function get_list( array $args = array() ): array {
        global $wpdb;
        $args = wp_parse_args( $args, array( 'search' => '', 'limit' => 50, 'offset' => 0 ) );
        $t    = self::table();
        $w    = array( '1=1' ); $p = array();

        if ( $args['search'] ) {
            $l   = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $w[] = '(name LIKE %s OR phone1 LIKE %s OR city LIKE %s OR address1 LIKE %s)';
            $p   = array_merge( $p, array( $l, $l, $l, $l ) );
        }
        $p[] = (int) $args['limit'];
        $p[] = (int) $args['offset'];

        return (array) $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM `{$t}` WHERE " . implode( ' AND ', $w ) . " ORDER BY updated_at DESC LIMIT %d OFFSET %d", ...$p ),
            ARRAY_A
        );
    }

    public static function get( int $id ): ?array {
        global $wpdb;
        $t   = self::table();
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$t}` WHERE id=%d LIMIT 1", $id ), ARRAY_A );
        if ( ! $row ) return null;

        // 関連案件を添付
        $ct         = $wpdb->prefix . 'ddrc_cases';
        $row['cases'] = (array) $wpdb->get_results(
            $wpdb->prepare( "SELECT id,case_type,case_status,visit_date,product_category,product_name,total_amount FROM `{$ct}` WHERE customer_id=%d ORDER BY updated_at DESC LIMIT 20", $id ),
            ARRAY_A
        );
        return $row;
    }

    public static function save( array $d ) {
        global $wpdb;
        $t  = self::table();
        $id = absint( $d['id'] ?? 0 );

        $f = array(
            'name'        => sanitize_text_field( $d['name']        ?? '' ),
            'name_kana'   => sanitize_text_field( $d['name_kana']   ?? '' ),
            'phone1'      => sanitize_text_field( $d['phone1']      ?? '' ),
            'phone2'      => sanitize_text_field( $d['phone2']      ?? '' ),
            'email'       => sanitize_email(      $d['email']       ?? '' ),
            'postal_code' => sanitize_text_field( $d['postal_code'] ?? '' ),
            'prefecture'  => sanitize_text_field( $d['prefecture']  ?? '' ),
            'city'        => sanitize_text_field( $d['city']        ?? '' ),
            'address1'    => sanitize_text_field( $d['address1']    ?? '' ),
            'address2'    => sanitize_text_field( $d['address2']    ?? '' ),
            'notes'       => sanitize_textarea_field( $d['notes']   ?? '' ),
        );

        if ( empty( $f['name'] ) ) return false;

        if ( $id > 0 ) {
            $r = $wpdb->update( $t, $f, array( 'id' => $id ) );
            return $r !== false ? $id : false;
        } else {
            $r = $wpdb->insert( $t, $f );
            return $r !== false ? (int) $wpdb->insert_id : false;
        }
    }

    public static function count_total(): int {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}dd_customers`" );
    }
}
