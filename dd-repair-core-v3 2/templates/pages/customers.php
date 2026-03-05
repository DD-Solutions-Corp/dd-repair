<?php
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.Security.NonceVerification
$action = $action ?? 'list';
$id     = $id ?? 0;
$search = sanitize_text_field( $_GET['search'] ?? '' );

if ( in_array( $action, array('new','edit'), true ) ) :
  $c = ( $action==='edit' && $id ) ? DDRC_Customers::get($id) : null;
?>
<div class="page active">
  <div class="page-header">
    <a href="<?php echo esc_url(ddrc_url('customers')); ?>" class="btn-back">← 顧客一覧</a>
    <span class="page-title"><?php echo $c?'顧客編集':'新規顧客'; ?></span>
  </div>
  <form method="post" action="<?php echo esc_url(ddrc_url('customers')); ?>">
    <?php wp_nonce_field('ddrc_save_customer'); ?>
    <input type="hidden" name="ddrc_action" value="save_customer">
    <?php if($c): ?><input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>"><?php endif; ?>
    <div class="form-section">
      <div class="form-section-title">基本情報</div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">氏名 <span style="color:#ef4444">*</span></label><input type="text" name="name" class="form-input" required value="<?php echo esc_attr($c['name']??''); ?>"></div>
        <div class="form-group"><label class="form-label">フリガナ</label><input type="text" name="name_kana" class="form-input" value="<?php echo esc_attr($c['name_kana']??''); ?>"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">電話番号1</label><input type="tel" name="phone1" class="form-input" value="<?php echo esc_attr($c['phone1']??''); ?>"></div>
        <div class="form-group"><label class="form-label">電話番号2</label><input type="tel" name="phone2" class="form-input" value="<?php echo esc_attr($c['phone2']??''); ?>"></div>
      </div>
      <div class="form-group"><label class="form-label">メールアドレス</label><input type="email" name="email" class="form-input" value="<?php echo esc_attr($c['email']??''); ?>"></div>
    </div>
    <div class="form-section">
      <div class="form-section-title">住所</div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">郵便番号</label><input type="text" name="postal_code" class="form-input" placeholder="123-4567" value="<?php echo esc_attr($c['postal_code']??''); ?>"></div>
        <div class="form-group"><label class="form-label">都道府県</label><input type="text" name="prefecture" class="form-input" value="<?php echo esc_attr($c['prefecture']??''); ?>"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">市区町村</label><input type="text" name="city" class="form-input" value="<?php echo esc_attr($c['city']??''); ?>"></div>
        <div class="form-group"><label class="form-label">番地</label><input type="text" name="address1" class="form-input" value="<?php echo esc_attr($c['address1']??''); ?>"></div>
      </div>
    </div>
    <div class="form-section">
      <div class="form-group"><label class="form-label">メモ</label><textarea name="notes" class="form-textarea"><?php echo esc_textarea($c['notes']??''); ?></textarea></div>
    </div>
    <div class="form-actions"><button type="submit" class="btn btn-primary btn-full btn-lg">💾 保存する</button></div>
  </form>
</div>

<?php elseif ( $action==='detail' && $id ) :
  $c = DDRC_Customers::get($id);
  if(!$c){echo '<div class="page active"><p class="error-msg">顧客が見つかりません。</p></div>';return;}
  $initial = mb_strtoupper(mb_substr($c['name']??'?',0,1,'UTF-8'));
  $addr = trim(($c['prefecture']??'').' '.($c['city']??'').' '.($c['address1']??''));
  $cases = $c['related_cases'] ?? array();
?>
<div class="page active">
  <div class="page-header">
    <a href="<?php echo esc_url(ddrc_url('customers')); ?>" class="btn-back">← 顧客一覧</a>
    <a href="<?php echo esc_url(ddrc_url('customers',array('action'=>'edit','id'=>$id))); ?>" class="btn btn-outline btn-sm">✏️ 編集</a>
  </div>
  <div class="card">
    <div class="customer-card-header">
      <div class="customer-avatar"><?php echo esc_html($initial); ?></div>
      <div style="flex:1;">
        <div style="font-weight:600;font-size:20px;color:#1e293b;"><?php echo esc_html($c['name']); ?></div>
        <?php if($c['name_kana']): ?><div style="font-size:13px;color:#94a3b8;"><?php echo esc_html($c['name_kana']); ?></div><?php endif; ?>
      </div>
    </div>
    <?php if($c['phone1']): ?><div class="detail-row"><span class="detail-label">📞</span><a href="tel:<?php echo esc_attr($c['phone1']); ?>" style="color:#1B3A6B;font-size:16px;font-weight:600;"><?php echo esc_html($c['phone1']); ?></a></div><?php endif; ?>
    <?php if($c['phone2']): ?><div class="detail-row"><span class="detail-label">📞</span><a href="tel:<?php echo esc_attr($c['phone2']); ?>" style="color:#64748b;"><?php echo esc_html($c['phone2']); ?></a></div><?php endif; ?>
    <?php if($c['email']): ?><div class="detail-row"><span class="detail-label">📧</span><span style="font-size:14px;color:#64748b;"><?php echo esc_html($c['email']); ?></span></div><?php endif; ?>
    <?php if($addr): ?><div class="detail-row"><span class="detail-label">📍</span><span style="font-size:14px;color:#64748b;"><?php echo esc_html($addr); ?></span></div><?php endif; ?>
  </div>
  <div style="display:flex;gap:8px;margin-bottom:16px;">
    <a href="<?php echo esc_url(ddrc_url('cases',array('action'=>'new','customer_name'=>$c['name']))); ?>" class="btn btn-primary" style="flex:1;justify-content:center;">🔧 新規案件</a>
    <a href="<?php echo esc_url(ddrc_url('customers',array('action'=>'edit','id'=>$id))); ?>" class="btn btn-outline" style="flex:1;justify-content:center;">✏️ 編集</a>
  </div>
  <?php if(!empty($cases)): ?>
  <div style="font-size:16px;font-weight:600;color:#1e293b;margin-bottom:12px;">📋 過去の案件 (<?php echo count($cases); ?>件)</div>
  <?php foreach($cases as $case): ?>
  <a href="<?php echo esc_url(ddrc_url('cases',array('action'=>'detail','id'=>$case['id']))); ?>" class="card" style="display:block;text-decoration:none;color:inherit;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <span class="badge badge-<?php echo esc_attr($case['case_type']); ?>"><?php echo $case['case_type']==='repair'?'🔧 修理':'📦 販売'; ?></span>
      <span style="font-size:12px;color:#94a3b8;"><?php echo esc_html($case['visit_date']??''); ?></span>
    </div>
    <div style="font-size:14px;color:#64748b;margin-top:8px;"><?php echo esc_html(trim(($case['maker']??'').' '.($case['product_name']??''))); ?></div>
  </a>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php else :
  $customers = DDRC_Customers::get_list(array('search'=>$search));
?>
<div id="page-customers" class="page active">
  <div class="section-title" style="display:flex;justify-content:space-between;align-items:center;">
    <span>👥 顧客管理</span>
    <span style="font-size:14px;color:#64748b;font-weight:400;"><?php echo count($customers); ?>件</span>
  </div>

  <div style="margin-bottom:16px;">
    <form method="get" action="<?php echo esc_url(ddrc_url('customers')); ?>">
      <input type="hidden" name="tab" value="customers">
      <div class="search-input-wrap">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="顧客名、電話番号で検索..." value="<?php echo esc_attr($search); ?>" autocomplete="off">
      </div>
    </form>
  </div>

  <div style="display:flex;gap:8px;margin-bottom:16px;">
    <a href="<?php echo esc_url(ddrc_url('customers',array('action'=>'new'))); ?>" class="btn btn-primary" style="flex:1;justify-content:center;">➕ 新規顧客登録</a>
  </div>

  <?php if(empty($customers)): ?>
  <div class="empty-state"><span class="empty-state-icon">👥</span><div class="empty-state-text">顧客が見つかりません</div></div>
  <?php else: ?>
  <?php foreach($customers as $c):
    $initial=mb_strtoupper(mb_substr($c['name']??'?',0,1,'UTF-8'));
    $addr=trim(($c['city']??'').' '.($c['address1']??''));
  ?>
  <a href="<?php echo esc_url(ddrc_url('customers',array('action'=>'detail','id'=>$c['id']))); ?>" class="customer-card">
    <div class="customer-card-header">
      <div class="customer-avatar"><?php echo esc_html($initial); ?></div>
      <div style="flex:1;">
        <div class="customer-name"><?php echo esc_html($c['name']); ?></div>
        <div style="font-size:12px;color:#94a3b8;">ID: <?php echo (int)$c['id']; ?></div>
      </div>
    </div>
    <?php if($c['phone1']): ?><div class="customer-row">📞 <?php echo esc_html($c['phone1']); ?></div><?php endif; ?>
    <?php if($c['email']): ?><div class="customer-row">📧 <?php echo esc_html($c['email']); ?></div><?php endif; ?>
    <?php if($addr): ?><div class="customer-row">📍 <?php echo esc_html($addr); ?></div><?php endif; ?>
  </a>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php endif; ?>
