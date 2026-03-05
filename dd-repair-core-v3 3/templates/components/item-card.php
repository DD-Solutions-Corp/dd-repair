<?php
/**
 * @var array $item
 */
defined( 'ABSPATH' ) || exit;

$qty   = (int) ( $item['stock_quantity']     ?? 0 );
$min   = (int) ( $item['min_stock_quantity'] ?? 0 );
$url   = ddrc_url( 'inventory', array( 'action' => 'detail', 'id' => $item['id'] ) );
?>
<a href="<?php echo esc_url( $url ); ?>" class="page-inventory__item">
  <div class="page-inventory__item-image">
    <i class="fas fa-box" style="font-size:32px;color:#9CA3AF;"></i>
  </div>
  <div class="page-inventory__item-info">
    <div class="page-inventory__item-name"><?php echo esc_html( $item['item_name'] ); ?></div>
    <?php if ( $item['maker_name'] ) : ?>
    <div class="page-inventory__item-quantity" style="color:#64748B;font-size:12px;"><?php echo esc_html( $item['maker_name'] ); ?></div>
    <?php endif; ?>
    <div class="page-inventory__item-quantity">
      在庫: <strong><?php echo esc_html( $qty ); ?></strong>個
      <span class="badge badge--sm <?php echo esc_attr( ddrc_stock_badge( $qty, $min ) ); ?>" style="margin-left:6px;"><?php echo esc_html( ddrc_stock_label( $qty, $min ) ); ?></span>
    </div>
    <?php if ( ! empty( $item['retail_price'] ) && (int)$item['retail_price'] > 0 ) : ?>
    <div class="page-inventory__item-price"><?php echo esc_html( ddrc_price( $item['retail_price'] ) ); ?></div>
    <?php endif; ?>
  </div>
</a>
