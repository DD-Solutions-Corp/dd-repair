<?php
/**
 * DDRC_Inventory — 在庫モジュール
 */

defined( 'ABSPATH' ) || exit;

class DDRC_Inventory {

    private static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'ddrc_items';
    }

    public static function get_list( array $args = array() ): array {
        global $wpdb;
        $args = wp_parse_args( $args, array(
            'search'   => '',
            'category' => '',
            'alert'    => false,
            'limit'    => 50,
            'offset'   => 0,
        ) );
        $t  = self::table();
        $w  = array( '1=1' ); $p = array();

        if ( $args['search'] ) {
            $l   = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $w[] = '(item_name LIKE %s OR maker_name LIKE %s OR jan_code LIKE %s)';
            $p   = array_merge( $p, array( $l, $l, $l ) );
        }
        if ( $args['category'] ) { $w[] = 'product_category=%s'; $p[] = $args['category']; }
        if ( $args['alert'] )    { $w[] = 'stock_quantity <= min_stock_quantity'; }

        $p[] = (int) $args['limit'];
        $p[] = (int) $args['offset'];

        return (array) $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM `{$t}` WHERE " . implode( ' AND ', $w ) . " ORDER BY item_name ASC LIMIT %d OFFSET %d", ...$p ),
            ARRAY_A
        );
    }

    public static function get( int $id ): ?array {
        global $wpdb;
        $t = self::table();
        $r = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$t}` WHERE id=%d LIMIT 1", $id ), ARRAY_A );
        return $r ?: null;
    }

    public static function save_item( array $d ) {
        global $wpdb;
        $t  = self::table();
        $id = absint( $d['id'] ?? 0 );

        $f = array(
            'item_name'          => sanitize_text_field( $d['item_name']        ?? '' ),
            'maker_name'         => sanitize_text_field( $d['maker_name']       ?? '' ),
            'jan_code'           => sanitize_text_field( $d['jan_code']         ?? '' ) ?: null,
            'product_category'   => sanitize_text_field( $d['product_category'] ?? '' ),
            'item_type'          => in_array( $d['item_type'] ?? '', array( 'product', 'part', 'consumable' ), true ) ? $d['item_type'] : 'product',
            'cost_price'         => is_numeric( $d['cost_price']   ?? '' ) ? (int) $d['cost_price']   : 0,
            'retail_price'       => is_numeric( $d['retail_price'] ?? '' ) ? (int) $d['retail_price'] : 0,
            'stock_quantity'     => isset( $d['stock_quantity'] )     ? (int) $d['stock_quantity']     : 0,
            'min_stock_quantity' => isset( $d['min_stock_quantity'] ) ? (int) $d['min_stock_quantity'] : 0,
            'notes'              => sanitize_textarea_field( $d['notes'] ?? '' ),
        );

        if ( empty( $f['item_name'] ) ) return false;

        if ( $id > 0 ) {
            $r = $wpdb->update( $t, $f, array( 'id' => $id ) );
            return $r !== false ? $id : false;
        } else {
            $r = $wpdb->insert( $t, $f );
            return $r !== false ? (int) $wpdb->insert_id : false;
        }
    }

    public static function update_stock( int $id, int $delta ): bool {
        global $wpdb;
        $t = self::table();
        $r = $wpdb->query( $wpdb->prepare(
            "UPDATE `{$t}` SET stock_quantity=GREATEST(0,stock_quantity+%d) WHERE id=%d",
            $delta, $id
        ) );
        return $r !== false;
    }

    public static function get_categories(): array {
        global $wpdb;
        $t = self::table();
        return (array) $wpdb->get_col(
            "SELECT DISTINCT product_category FROM `{$t}` WHERE product_category!='' ORDER BY product_category ASC"
        );
    }

    public static function count_alerts(): int {
        global $wpdb;
        $t = self::table();
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$t}` WHERE stock_quantity <= min_stock_quantity" );
    }
}
