<?php
/**
 * @var array $customer
 */
defined( 'ABSPATH' ) || exit;

$url  = ddrc_url( 'customers', array( 'action' => 'detail', 'id' => $customer['id'] ) );
$addr = trim( ( $customer['prefecture'] ?? '' ) . ( $customer['city'] ?? '' ) . ( $customer['address1'] ?? '' ) );
?>
<a href="<?php echo esc_url( $url ); ?>" class="page-customers__card">
  <div class="page-customers__card-header">
    <div class="page-customers__card-name"><?php echo esc_html( $customer['name'] ); ?></div>
    <?php if ( $customer['name_kana'] ) : ?>
    <div style="font-size:11px;color:#94A3B8;"><?php echo esc_html( $customer['name_kana'] ); ?></div>
    <?php endif; ?>
  </div>
  <?php if ( $customer['phone1'] ) : ?>
  <div class="page-customers__card-phone"><i class="fas fa-phone" style="font-size:11px;"></i> <?php echo esc_html( $customer['phone1'] ); ?></div>
  <?php endif; ?>
  <?php if ( $addr ) : ?>
  <div class="page-customers__card-address"><i class="fas fa-map-marker-alt"></i> <?php echo esc_html( $addr ); ?></div>
  <?php endif; ?>
</a>
