<?php
/**
 * @var string $tab
 */
defined( 'ABSPATH' ) || exit;

// リファレンスHTMLのナビ構造に完全準拠
$nav_items = array(
    'dashboard' => array( 'icon' => '🏠', 'label' => 'ダッシュボード', 'slug' => '' ),
    'cases'     => array( 'icon' => '📋', 'label' => '案件',           'slug' => 'cases' ),
    'inventory' => array( 'icon' => '📦', 'label' => '在庫',           'slug' => 'inventory' ),
    'customers' => array( 'icon' => '👥', 'label' => '顧客',           'slug' => 'customers' ),
    'settings'  => array( 'icon' => '⚙️', 'label' => '設定',           'slug' => 'settings' ),
);
$current = $tab ?? 'dashboard';
$js_url  = DDRC_URL . 'assets/js/app.js?v=' . DDRC_VERSION;
$sw_url  = home_url( '/' . DDRC_BASE . '/sw.js' );
?>
</div><!-- /.main-content -->

<!-- ボトムナビゲーション（リファレンスHTML完全準拠） -->
<nav class="bottom-nav">
<?php foreach ( $nav_items as $key => $item ) :
    $active = ( $current === $key );
    $href   = ddrc_url( $item['slug'] );
?>
  <a href="<?php echo esc_url( $href ); ?>"
     class="nav-item<?php echo $active ? ' active' : ''; ?>"
     style="text-decoration:none;">
    <div class="nav-icon"><?php echo $item['icon']; ?></div>
    <div class="nav-label"><?php echo esc_html( $item['label'] ); ?></div>
  </a>
<?php endforeach; ?>
</nav>

<!-- JS: defer でレンダリングをブロックしない -->
<script defer src="<?php echo esc_url( $js_url ); ?>"></script>
<script>
if('serviceWorker'in navigator){
  navigator.serviceWorker.register('<?php echo esc_url( $sw_url ); ?>')
    .catch(function(e){console.warn('[SW]',e)});
}
</script>
</body>
</html>
