<?php
defined( 'ABSPATH' ) || exit;
$kpi      = DDRC_Dashboard::get_kpi();
$schedule = DDRC_Cases::get_today();
?>
<div id="page-dashboard" class="page active">

  <!-- KPIカード（リファレンスHTML準拠） -->
  <div class="kpi-grid">
    <a href="<?php echo esc_url( ddrc_url('cases', array('visit_date'=>date('Y-m-d'))) ); ?>" class="kpi-card blue" style="text-decoration:none;">
      <div class="kpi-icon">📋</div>
      <div class="kpi-label">今日の案件</div>
      <div class="kpi-value"><?php echo (int)$kpi['today_cases']; ?>件</div>
    </a>
    <a href="<?php echo esc_url( ddrc_url('cases', array('case_status'=>'draft')) ); ?>" class="kpi-card amber" style="text-decoration:none;">
      <div class="kpi-icon">⚠️</div>
      <div class="kpi-label">要対応</div>
      <div class="kpi-value"><?php echo (int)$kpi['pending_cases']; ?>件</div>
    </a>
    <a href="<?php echo esc_url( ddrc_url('inventory', array('alert'=>'1')) ); ?>" class="kpi-card red" style="text-decoration:none;">
      <div class="kpi-icon">📦</div>
      <div class="kpi-label">在庫アラート</div>
      <div class="kpi-value"><?php echo (int)$kpi['stock_alerts']; ?>件</div>
    </a>
    <div class="kpi-card green">
      <div class="kpi-icon">💰</div>
      <div class="kpi-label">今月の売上</div>
      <div class="kpi-value">¥<?php echo number_format( (float)$kpi['month_sales'] ); ?></div>
    </div>
  </div>

  <!-- クイックアクション（リファレンスHTML準拠） -->
  <div class="section-title">📌 クイックアクション</div>
  <div class="quick-actions">
    <?php
    $actions = array(
      array( 'icon'=>'📦', 'label'=>'新規販売',  'url'=>ddrc_url('cases',     array('action'=>'new','case_type'=>'sale')) ),
      array( 'icon'=>'🔧', 'label'=>'新規修理',  'url'=>ddrc_url('cases',     array('action'=>'new','case_type'=>'repair')) ),
      array( 'icon'=>'📥', 'label'=>'入荷・引当', 'url'=>ddrc_url('inventory', array('action'=>'receive')) ),
      array( 'icon'=>'👤', 'label'=>'顧客検索',  'url'=>ddrc_url('customers') ),
      array( 'icon'=>'📦', 'label'=>'在庫一覧',  'url'=>ddrc_url('inventory') ),
      array( 'icon'=>'➕', 'label'=>'新規顧客',  'url'=>ddrc_url('customers', array('action'=>'new')) ),
    );
    foreach ( $actions as $a ) : ?>
    <a href="<?php echo esc_url( $a['url'] ); ?>" class="quick-btn" style="text-decoration:none;">
      <div class="quick-btn-icon"><?php echo $a['icon']; ?></div>
      <div class="quick-btn-text"><?php echo esc_html( $a['label'] ); ?></div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- 本日のスケジュール（リファレンスHTML準拠） -->
  <div class="section-title">📅 今日のスケジュール</div>
  <?php if ( empty( $schedule ) ) : ?>
  <div class="empty-state">
    <span class="empty-state-icon">📭</span>
    <div class="empty-state-text">本日の案件はありません</div>
    <div style="font-size:13px;margin-top:8px;opacity:.8;color:#94a3b8;">新しい案件は自動的にここに表示されます</div>
  </div>
  <?php else : ?>
  <?php foreach ( $schedule as $c ) :
    $type_icon  = $c['case_type'] === 'repair' ? '🔧' : '💰';
    $type_label = $c['case_type'] === 'repair' ? '修理案件' : '販売案件';
    $addr = trim( ( $c['address1'] ?? '' ) . ' ' . ( $c['address2'] ?? '' ) );
  ?>
  <a href="<?php echo esc_url( ddrc_url('cases', array('action'=>'detail','id'=>$c['id'])) ); ?>" class="card" style="display:block;text-decoration:none;color:inherit;cursor:pointer;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
      <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:24px;"><?php echo $type_icon; ?></span>
        <div>
          <div style="font-weight:600;font-size:16px;color:#1e293b;"><?php echo esc_html( $c['customer_name'] ?? '顧客名なし' ); ?></div>
          <div style="font-size:13px;color:#64748b;margin-top:2px;"><?php echo esc_html( $type_label ); ?></div>
        </div>
      </div>
      <div style="text-align:right;">
        <div style="font-weight:700;color:#3b82f6;font-size:18px;">¥<?php echo number_format( (float)( $c['total_amount'] ?? 0 ) ); ?></div>
        <div style="font-size:12px;color:#64748b;margin-top:2px;">案件ID: <?php echo esc_html( $c['id'] ); ?></div>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:auto 1fr;gap:8px;font-size:14px;color:#64748b;margin-bottom:12px;">
      <div>📅</div><div>訪問日: <?php echo esc_html( $c['visit_date'] ?? '未定' ); ?></div>
      <div>📍</div><div>住所: <?php echo esc_html( $addr ?: '未登録' ); ?></div>
    </div>
    <span class="badge badge-<?php echo esc_attr( $c['case_status'] ?? 'draft' ); ?>">
      <?php echo esc_html( ddrc_status_label( $c['case_status'] ?? 'draft' ) ); ?>
    </span>
  </a>
  <?php endforeach; ?>
  <?php endif; ?>

</div>
