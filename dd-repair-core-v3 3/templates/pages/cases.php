<?php
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.Security.NonceVerification
$action   = $action ?? 'list';
$id       = $id ?? 0;
$search   = sanitize_text_field( $_GET['search'] ?? '' );
$type_f   = sanitize_key( $_GET['case_type'] ?? '' );
$status_f = sanitize_key( $_GET['case_status'] ?? '' );

/* ─── 新規/編集 ─── */
if ( in_array( $action, array( 'new', 'edit' ), true ) ) :
  $c  = ( $action === 'edit' && $id ) ? DDRC_Cases::get( $id ) : null;
  $pt = sanitize_key( $_GET['case_type'] ?? ( $c['case_type'] ?? 'repair' ) );
?>
<div class="page active">
  <div class="page-header">
    <a href="<?php echo esc_url( ddrc_url('cases') ); ?>" class="btn-back">← 案件一覧</a>
    <span class="page-title"><?php echo $c ? '案件編集' : '新規案件'; ?></span>
  </div>
  <form method="post" action="<?php echo esc_url( ddrc_url('cases') ); ?>">
    <?php wp_nonce_field('ddrc_save_case'); ?>
    <input type="hidden" name="ddrc_action" value="save_case">
    <?php if ( $c ) : ?><input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>"><?php endif; ?>

    <div class="form-section">
      <div class="form-radio-group">
        <label class="form-radio<?php echo $pt==='repair'?' active':''; ?>">
          <input type="radio" name="case_type" value="repair" <?php checked($pt,'repair'); ?>> 🔧 修理
        </label>
        <label class="form-radio<?php echo $pt==='sale'?' active':''; ?>">
          <input type="radio" name="case_type" value="sale" <?php checked($pt,'sale'); ?>> 📦 販売
        </label>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">顧客情報</div>
      <div class="form-group">
        <label class="form-label">顧客名 <span style="color:#ef4444">*</span></label>
        <input type="text" name="customer_name" class="form-input" required value="<?php echo esc_attr($c['customer_name']??''); ?>">
      </div>
      <div class="form-group">
        <label class="form-label">電話番号</label>
        <input type="tel" name="phone1" class="form-input" value="<?php echo esc_attr($c['phone1']??''); ?>">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">市区町村</label>
          <input type="text" name="city" class="form-input" value="<?php echo esc_attr($c['city']??''); ?>">
        </div>
        <div class="form-group">
          <label class="form-label">番地</label>
          <input type="text" name="address1" class="form-input" value="<?php echo esc_attr($c['address1']??''); ?>">
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">製品情報</div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">カテゴリ</label>
          <input type="text" name="product_category" class="form-input" placeholder="エアコン" value="<?php echo esc_attr($c['product_category']??''); ?>">
        </div>
        <div class="form-group">
          <label class="form-label">メーカー</label>
          <input type="text" name="maker" class="form-input" placeholder="Panasonic" value="<?php echo esc_attr($c['maker']??''); ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">製品名・型番</label>
        <input type="text" name="product_name" class="form-input" value="<?php echo esc_attr($c['product_name']??''); ?>">
      </div>
      <div class="form-group">
        <label class="form-label">症状・依頼内容</label>
        <textarea name="symptom" class="form-textarea"><?php echo esc_textarea($c['symptom']??''); ?></textarea>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">日時・ステータス</div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">訪問日</label>
          <input type="date" name="visit_date" class="form-input" value="<?php echo esc_attr($c['visit_date']??''); ?>">
        </div>
        <div class="form-group">
          <label class="form-label">訪問時間</label>
          <input type="time" name="visit_time" class="form-input" value="<?php echo esc_attr($c['visit_time']??''); ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">案件ステータス</label>
          <select name="case_status" class="form-select">
            <?php foreach ( array('draft'=>'📝 受付済み','estimated'=>'📅 アポ確定','in_progress'=>'🔧 作業中','completed'=>'✅ 完了','cancelled'=>'キャンセル') as $v=>$l ) : ?>
            <option value="<?php echo esc_attr($v); ?>" <?php selected($c['case_status']??'draft',$v); ?>><?php echo esc_html($l); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">支払ステータス</label>
          <select name="payment_status" class="form-select">
            <?php foreach ( array('unpaid'=>'未払','partial'=>'一部払','paid'=>'完済') as $v=>$l ) : ?>
            <option value="<?php echo esc_attr($v); ?>" <?php selected($c['payment_status']??'unpaid',$v); ?>><?php echo esc_html($l); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">金額</div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">見積金額 (¥)</label>
          <input type="number" name="estimate_amount" class="form-input" min="0" value="<?php echo esc_attr($c['estimate_amount']??''); ?>">
        </div>
        <div class="form-group">
          <label class="form-label">請求金額 (¥)</label>
          <input type="number" name="total_amount" class="form-input" min="0" value="<?php echo esc_attr($c['total_amount']??''); ?>">
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-group">
        <label class="form-label">メモ</label>
        <textarea name="notes" class="form-textarea"><?php echo esc_textarea($c['notes']??''); ?></textarea>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary btn-full btn-lg">💾 保存する</button>
    </div>
  </form>
</div>

<?php elseif ( $action === 'detail' && $id ) :
  $c = DDRC_Cases::get( $id );
  if ( ! $c ) { echo '<div class="page active"><p class="error-msg">案件が見つかりません。</p></div>'; return; }
  $type_icon  = $c['case_type'] === 'repair' ? '🔧' : '💰';
  $type_label = $c['case_type'] === 'repair' ? '修理案件' : '販売案件';
  $addr = trim( ( $c['city'] ?? '' ) . ' ' . ( $c['address1'] ?? '' ) );
?>
<div class="page active">
  <div class="page-header">
    <a href="<?php echo esc_url( ddrc_url('cases') ); ?>" class="btn-back">← 一覧</a>
    <a href="<?php echo esc_url( ddrc_url('cases',array('action'=>'edit','id'=>$id)) ); ?>" class="btn btn-outline btn-sm">✏️ 編集</a>
  </div>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
      <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:24px;"><?php echo $type_icon; ?></span>
        <div>
          <div style="font-weight:600;font-size:18px;color:#1e293b;"><?php echo esc_html($c['customer_name']); ?></div>
          <div style="font-size:13px;color:#64748b;"><?php echo esc_html($type_label); ?></div>
        </div>
      </div>
      <div style="text-align:right;">
        <div style="font-weight:700;color:#3b82f6;font-size:20px;">¥<?php echo number_format((float)($c['total_amount']??0)); ?></div>
        <div style="font-size:12px;color:#94a3b8;">ID: <?php echo (int)$c['id']; ?></div>
      </div>
    </div>
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;">
      <span class="badge badge-<?php echo esc_attr($c['case_type']); ?>"><?php echo esc_html($c['case_type']==='repair'?'🔧 修理':'📦 販売'); ?></span>
      <span class="badge badge-<?php echo esc_attr($c['case_status']??'draft'); ?>"><?php echo esc_html(ddrc_status_label($c['case_status']??'draft')); ?></span>
      <span class="badge badge-<?php echo esc_attr($c['payment_status']??'unpaid'); ?>"><?php echo esc_html(ddrc_payment_label($c['payment_status']??'unpaid')); ?></span>
    </div>
    <div class="detail-row"><span class="detail-label">📅 訪問</span><span class="detail-value"><?php echo esc_html( ($c['visit_date']??'未定') . ((!empty($c['visit_time']))?' '.substr($c['visit_time'],0,5):'') ); ?></span></div>
    <?php if ( $c['phone1'] ) : ?><div class="detail-row"><span class="detail-label">📞 電話</span><span class="detail-value"><a href="tel:<?php echo esc_attr($c['phone1']); ?>" style="color:#1B3A6B;"><?php echo esc_html($c['phone1']); ?></a></span></div><?php endif; ?>
    <?php if ( $addr ) : ?><div class="detail-row"><span class="detail-label">📍 住所</span><span class="detail-value"><?php echo esc_html($addr); ?></span></div><?php endif; ?>
    <?php $prod = trim(($c['maker']??'').' '.($c['product_name']??'')); if($prod): ?><div class="detail-row"><span class="detail-label">🔧 製品</span><span class="detail-value"><?php echo esc_html($prod); ?></span></div><?php endif; ?>
    <?php if ( $c['symptom'] ) : ?><div class="detail-row"><span class="detail-label">⚡ 症状</span><span class="detail-value" style="color:#ef4444;"><?php echo nl2br(esc_html($c['symptom'])); ?></span></div><?php endif; ?>
    <div class="detail-row"><span class="detail-label">💴 見積</span><span class="detail-value">¥<?php echo number_format((float)($c['estimate_amount']??0)); ?></span></div>
    <?php if ( $c['notes'] ) : ?><div style="margin-top:12px;padding:12px;background:#f8fafc;border-radius:8px;font-size:14px;color:#64748b;">📝 <?php echo nl2br(esc_html($c['notes'])); ?></div><?php endif; ?>
  </div>
  <div style="display:flex;gap:8px;">
    <a href="<?php echo esc_url(ddrc_url('cases',array('action'=>'edit','id'=>$id))); ?>" class="btn btn-primary" style="flex:1;justify-content:center;">✏️ 編集する</a>
  </div>
</div>

<?php else :
  $cases = DDRC_Cases::get_list( array('search'=>$search,'case_type'=>$type_f,'case_status'=>$status_f) );
?>
<div id="page-projects" class="page active">
  <div class="section-title" style="display:flex;justify-content:space-between;align-items:center;">
    <span>📋 案件管理</span>
    <span style="font-size:14px;color:#64748b;font-weight:400;"><?php echo count($cases); ?>件</span>
  </div>

  <!-- 検索バー（リファレンスHTML準拠） -->
  <div style="margin-bottom:16px;">
    <form method="get" action="<?php echo esc_url(ddrc_url('cases')); ?>">
      <input type="hidden" name="tab" value="cases">
      <div class="search-input-wrap">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="案件番号、顧客名で検索..." value="<?php echo esc_attr($search); ?>" autocomplete="off">
      </div>
    </form>
  </div>

  <!-- フィルター（リファレンスHTML準拠） -->
  <div class="filter-chips">
    <?php foreach ( array(''=>'全て','repair'=>'修理','sale'=>'販売','in_progress'=>'進行中','completed'=>'完了') as $v=>$l ) : ?>
    <a href="<?php echo esc_url(ddrc_url('cases',array_filter(array('search'=>$search,'case_type'=>in_array($v,array('repair','sale'))?$v:'','case_status'=>in_array($v,array('in_progress','completed'))?$v:'')))); ?>"
       class="chip<?php echo ($type_f===$v||$status_f===$v||($v===''&&!$type_f&&!$status_f))?' active':''; ?>">
      <?php echo esc_html($l); ?>
    </a>
    <?php endforeach; ?>
  </div>

  <div style="text-align:right;margin-bottom:12px;">
    <a href="<?php echo esc_url(ddrc_url('cases',array('action'=>'new'))); ?>" class="btn btn-primary btn-sm">＋ 新規案件</a>
  </div>

  <?php if ( empty($cases) ) : ?>
  <div class="empty-state">
    <span class="empty-state-icon">📭</span>
    <div class="empty-state-text">該当する案件がありません</div>
  </div>
  <?php else : ?>
  <?php foreach ( $cases as $c ) :
    $type_icon  = $c['case_type']==='repair'?'🔧':'💰';
    $type_cls   = $c['case_type']==='repair'?'badge-repair':'badge-sale';
    $type_label = $c['case_type']==='repair'?'🔧 修理':'📦 販売';
    $addr = trim(($c['city']??'').' '.($c['address1']??''));
  ?>
  <a href="<?php echo esc_url(ddrc_url('cases',array('action'=>'detail','id'=>$c['id']))); ?>" class="card" style="display:block;text-decoration:none;color:inherit;cursor:pointer;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
      <span class="badge <?php echo $type_cls; ?>"><?php echo $type_label; ?></span>
      <div style="font-size:12px;color:#94a3b8;">ID: <?php echo (int)$c['id']; ?></div>
    </div>
    <div style="font-weight:600;font-size:18px;margin-bottom:8px;color:#1e293b;"><?php echo esc_html($c['customer_name']??'顧客名なし'); ?></div>
    <div style="font-size:14px;color:#64748b;margin-bottom:4px;">📅 訪問日: <?php echo esc_html($c['visit_date']??'未定'); ?></div>
    <?php if($c['phone1']): ?><div style="font-size:14px;color:#64748b;margin-bottom:12px;">📞 電話: <?php echo esc_html($c['phone1']); ?></div><?php endif; ?>
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <div style="font-weight:700;color:#3b82f6;font-size:20px;">¥<?php echo number_format((float)($c['total_amount']??0)); ?></div>
      <span class="badge badge-<?php echo esc_attr($c['case_status']??'draft'); ?>"><?php echo esc_html(ddrc_status_label($c['case_status']??'draft')); ?></span>
    </div>
  </a>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php endif; ?>
