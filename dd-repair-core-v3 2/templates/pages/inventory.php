<?php
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.Security.NonceVerification
$action   = $action ?? 'list';
$id       = $id ?? 0;
$search   = sanitize_text_field( $_GET['search'] ?? '' );
$category = sanitize_text_field( $_GET['category'] ?? '' );
$alert    = ! empty( $_GET['alert'] );

if ( in_array( $action, array('new','edit'), true ) ) :
  $item = ( $action==='edit' && $id ) ? DDRC_Inventory::get($id) : null;
?>
<div class="page active">
  <div class="page-header">
    <a href="<?php echo esc_url(ddrc_url('inventory')); ?>" class="btn-back">← 在庫一覧</a>
    <span class="page-title"><?php echo $item?'商品編集':'新規商品'; ?></span>
  </div>
  <form method="post" action="<?php echo esc_url(ddrc_url('inventory')); ?>">
    <?php wp_nonce_field('ddrc_save_item'); ?>
    <input type="hidden" name="ddrc_action" value="save_item">
    <?php if($item): ?><input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>"><?php endif; ?>
    <div class="form-section">
      <div class="form-section-title">商品情報</div>
      <div class="form-group"><label class="form-label">商品名 <span style="color:#ef4444">*</span></label><input type="text" name="item_name" class="form-input" required value="<?php echo esc_attr($item['item_name']??''); ?>"></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">メーカー</label><input type="text" name="maker_name" class="form-input" value="<?php echo esc_attr($item['maker_name']??''); ?>"></div>
        <div class="form-group"><label class="form-label">カテゴリ</label><input type="text" name="product_category" class="form-input" placeholder="洗濯機" value="<?php echo esc_attr($item['product_category']??''); ?>"></div>
      </div>
      <div class="form-group"><label class="form-label">JANコード</label><input type="text" name="jan_code" class="form-input" value="<?php echo esc_attr($item['jan_code']??''); ?>"></div>
    </div>
    <div class="form-section">
      <div class="form-section-title">価格・在庫</div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">定価 (¥)</label><input type="number" name="retail_price" class="form-input" min="0" value="<?php echo esc_attr($item['retail_price']??''); ?>"></div>
        <div class="form-group"><label class="form-label">原価 (¥)</label><input type="number" name="cost_price" class="form-input" min="0" value="<?php echo esc_attr($item['cost_price']??''); ?>"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">在庫数</label><input type="number" name="stock_quantity" class="form-input" min="0" value="<?php echo esc_attr($item['stock_quantity']??0); ?>"></div>
        <div class="form-group"><label class="form-label">最小在庫数</label><input type="number" name="min_stock_quantity" class="form-input" min="0" value="<?php echo esc_attr($item['min_stock_quantity']??3); ?>"></div>
      </div>
    </div>
    <div class="form-section">
      <div class="form-group"><label class="form-label">メモ</label><textarea name="notes" class="form-textarea"><?php echo esc_textarea($item['notes']??''); ?></textarea></div>
    </div>
    <div class="form-actions"><button type="submit" class="btn btn-primary btn-full btn-lg">💾 保存する</button></div>
  </form>
</div>

<?php elseif ( $action==='detail' && $id ) :
  $item = DDRC_Inventory::get($id);
  if(!$item){echo '<div class="page active"><p class="error-msg">商品が見つかりません。</p></div>';return;}
  $qty = (int)($item['stock_quantity']??0);
  $min = (int)($item['min_stock_quantity']??3);
  $stock_cls = $qty>$min?'stock-ok':($qty>0?'stock-low':'stock-out');
?>
<div class="page active">
  <div class="page-header">
    <a href="<?php echo esc_url(ddrc_url('inventory')); ?>" class="btn-back">← 在庫一覧</a>
    <a href="<?php echo esc_url(ddrc_url('inventory',array('action'=>'edit','id'=>$id))); ?>" class="btn btn-outline btn-sm">✏️ 編集</a>
  </div>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
      <div>
        <div style="font-weight:600;font-size:18px;color:#1e293b;margin-bottom:4px;"><?php echo esc_html($item['item_name']); ?></div>
        <div style="font-size:14px;color:#64748b;">🏭 <?php echo esc_html($item['maker_name']??'メーカー未登録'); ?></div>
      </div>
      <span class="stock-badge <?php echo $stock_cls; ?>">在庫 <?php echo $qty; ?></span>
    </div>
    <div class="detail-row"><span class="detail-label">📦 カテゴリ</span><span><?php echo esc_html($item['product_category']??'未分類'); ?></span></div>
    <?php if($item['jan_code']): ?><div class="detail-row"><span class="detail-label">📱 JAN</span><span style="font-family:monospace;"><?php echo esc_html($item['jan_code']); ?></span></div><?php endif; ?>
    <div class="detail-row"><span class="detail-label">💴 定価</span><span style="font-weight:700;color:#10b981;font-size:18px;">¥<?php echo number_format((float)($item['retail_price']??0)); ?></span></div>
    <div class="detail-row"><span class="detail-label">💰 原価</span><span>¥<?php echo number_format((float)($item['cost_price']??0)); ?></span></div>
    <?php if($item['notes']): ?><div style="margin-top:12px;padding:12px;background:#f8fafc;border-radius:8px;font-size:14px;color:#64748b;">📝 <?php echo nl2br(esc_html($item['notes'])); ?></div><?php endif; ?>
  </div>
  <!-- 在庫調整 -->
  <div class="card">
    <div style="font-weight:600;font-size:16px;color:#1e293b;margin-bottom:16px;">在庫調整</div>
    <form method="post" action="<?php echo esc_url(ddrc_url('inventory')); ?>">
      <?php wp_nonce_field('ddrc_update_stock'); ?>
      <input type="hidden" name="ddrc_action" value="update_stock">
      <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
      <div style="display:flex;align-items:center;justify-content:center;gap:24px;margin-bottom:16px;">
        <button type="submit" name="delta" value="-1" class="btn btn-danger" style="width:52px;height:52px;border-radius:50%;font-size:24px;padding:0;justify-content:center;">－</button>
        <div style="text-align:center;">
          <div style="font-size:36px;font-weight:700;color:#1e293b;"><?php echo $qty; ?></div>
          <div style="font-size:14px;color:#64748b;">個</div>
        </div>
        <button type="submit" name="delta" value="1" class="btn btn-primary" style="width:52px;height:52px;border-radius:50%;font-size:24px;padding:0;justify-content:center;">＋</button>
      </div>
    </form>
  </div>
</div>

<?php else :
  $categories = DDRC_Inventory::get_categories();
  $items = DDRC_Inventory::get_list(array('search'=>$search,'category'=>$category,'alert'=>$alert));
?>
<div id="page-inventory" class="page active">
  <div class="section-title" style="display:flex;justify-content:space-between;align-items:center;">
    <span>📦 在庫管理</span>
    <span style="font-size:14px;color:#64748b;font-weight:400;"><?php echo count($items); ?>件</span>
  </div>

  <div style="margin-bottom:16px;">
    <form method="get" action="<?php echo esc_url(ddrc_url('inventory')); ?>">
      <input type="hidden" name="tab" value="inventory">
      <div class="search-input-wrap">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="商品名、JANコードで検索..." value="<?php echo esc_attr($search); ?>" autocomplete="off">
      </div>
    </form>
  </div>

  <div class="filter-chips">
    <a href="<?php echo esc_url(ddrc_url('inventory',array('search'=>$search))); ?>" class="chip<?php echo !$category&&!$alert?' active':''; ?>">全て</a>
    <?php foreach($categories as $cat): ?>
    <a href="<?php echo esc_url(ddrc_url('inventory',array('search'=>$search,'category'=>$cat))); ?>" class="chip<?php echo $category===$cat?' active':''; ?>"><?php echo esc_html($cat); ?></a>
    <?php endforeach; ?>
    <a href="<?php echo esc_url(ddrc_url('inventory',array('search'=>$search,'alert'=>'1'))); ?>" class="chip<?php echo $alert?' active':''; ?>">⚠️ アラート</a>
  </div>

  <div style="text-align:right;margin-bottom:12px;">
    <a href="<?php echo esc_url(ddrc_url('inventory',array('action'=>'new'))); ?>" class="btn btn-primary btn-sm">＋ 新規商品</a>
  </div>

  <?php if(empty($items)): ?>
  <div class="empty-state"><span class="empty-state-icon">📦</span><div class="empty-state-text">商品が見つかりません</div></div>
  <?php else: ?>
  <?php foreach($items as $item):
    $qty=$item['stock_quantity']??0;
    $min=$item['min_stock_quantity']??3;
    $stock_cls=$qty>$min?'stock-ok':($qty>0?'stock-low':'stock-out');
  ?>
  <a href="<?php echo esc_url(ddrc_url('inventory',array('action'=>'detail','id'=>$item['id']))); ?>" class="inventory-card">
    <div class="inventory-card-thumb">📦</div>
    <div style="flex:1;min-width:0;">
      <div class="inventory-card-name"><?php echo esc_html($item['item_name']); ?></div>
      <div class="inventory-card-meta">🏭 <?php echo esc_html($item['maker_name']??'メーカー未登録'); ?></div>
      <?php if($item['jan_code']): ?><div class="inventory-card-meta">📱 <?php echo esc_html($item['jan_code']); ?></div><?php endif; ?>
      <div class="inventory-card-price">定価: ¥<?php echo number_format((float)($item['retail_price']??0)); ?></div>
    </div>
    <span class="stock-badge <?php echo $stock_cls; ?>">在庫 <?php echo (int)$qty; ?></span>
  </a>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php endif; ?>
