<?php
/**
 * header.php — リファレンスHTML完全準拠版
 *
 * 速度戦略:
 * - CSSを <link rel="preload"> で非ブロッキングロード
 * - インラインCSSで即座にヘッダーを描画（FOUC防止）
 * - wp_head() を一切呼ばない（テーマのJS/CSS完全遮断）
 * - Google Fonts を非同期ロード
 * - JSは defer で最後にロード
 *
 * @var string $tab
 */
defined( 'ABSPATH' ) || exit;

$user         = is_user_logged_in() ? wp_get_current_user() : null;
$initial      = $user ? mb_strtoupper( mb_substr( $user->display_name ?: 'G', 0, 1, 'UTF-8' ) ) : 'G';
$display_name = $user ? $user->display_name : 'ゲスト';
$today_js     = date_i18n( 'Y年n月j日（D）' );
$logo_url     = DDRC_URL . 'assets/images/dd-logo.png';
$css_url      = DDRC_URL . 'assets/css/main.css?v=' . DDRC_VERSION;
$js_url       = DDRC_URL . 'assets/js/app.js?v='    . DDRC_VERSION;
$manifest_url = home_url( '/' . DDRC_BASE . '/manifest.json' );
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="DD Repair">
<meta name="theme-color" content="#1B3A6B">
<title>DD Repair Core</title>
<link rel="manifest" href="<?php echo esc_url( $manifest_url ); ?>">

<!-- Google Fonts: preload → 非ブロッキング -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap"></noscript>

<!-- アプリCSS: preload → 非ブロッキング -->
<link rel="preload" as="style" href="<?php echo esc_url( $css_url ); ?>" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="<?php echo esc_url( $css_url ); ?>"></noscript>

<!-- インラインCritical CSS: ヘッダーだけ即描画 (FOUC防止) -->
<style>
*{margin:0!important;padding:0!important;box-sizing:border-box}
.header{padding:0 16px!important}
.kpi-card,.quick-btn,.nav-item{padding:16px!important}
html,body{width:100%;height:100%;overflow-x:hidden}
body{font-family:-apple-system,sans-serif;background:#f5f7fa;color:#2d3748;padding-top:60px!important;padding-bottom:72px!important;-webkit-font-smoothing:antialiased}
.header{position:fixed;top:0;left:0;right:0;width:100%;height:60px;background:#1B3A6B;color:white;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 8px rgba(0,0,0,.15);z-index:1000}
.header-left{display:flex;align-items:center;gap:12px}
.header-logo-img{width:40px;height:40px;object-fit:contain}
.header-title{font-size:14px;font-weight:500;opacity:.95}
.header-center{font-size:13px;font-weight:400;opacity:.9}
.header-right{display:flex;align-items:center;gap:16px}
.header-notification{position:relative;font-size:22px;cursor:pointer;opacity:.9}
.notification-badge{position:absolute;top:-4px;right:-4px;background:#EF4444;color:white;font-size:10px;font-weight:600;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center}
.header-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#29ABE2,#1B3A6B);color:white;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:14px}
.bottom-nav{position:fixed;bottom:0;left:0;right:0;width:100%;height:72px;background:white;border-top:1px solid #e2e8f0;display:flex;justify-content:space-around;align-items:center;z-index:1000}
.main-content{width:100%}
.page{display:none;width:100%;padding:16px!important}
.page.active{display:block!important}
</style>
</head>
<body>

<!-- ヘッダー（リファレンスHTML完全準拠） -->
<header class="header">
  <div class="header-left">
    <img src="<?php echo esc_url( $logo_url ); ?>" alt="DD" class="header-logo-img" width="40" height="40">
    <div class="header-title">Repair Core</div>
  </div>
  <div class="header-center" id="header-date"><?php echo esc_html( $today_js ); ?></div>
  <div class="header-right">
    <div class="header-notification">
      🔔
      <span class="notification-badge">0</span>
    </div>
    <div class="header-avatar" title="<?php echo esc_attr( $display_name ); ?>">
      <?php echo esc_html( $initial ); ?>
    </div>
  </div>
</header>

<?php ddrc_flash(); ?>

<div class="main-content">
