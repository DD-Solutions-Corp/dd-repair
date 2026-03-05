<?php
/**
 * project-card — 仕様書JSの createProjectCard() に完全準拠
 * @var array $case
 */
defined( 'ABSPATH' ) || exit;

// ステータスマップ（仕様書 PROJECT_STATUS と一致）
$status_map = array(
  'draft'              => array('badge'=>'received',    'label'=>'📝 受付済み'),
  'estimated'          => array('badge'=>'scheduled',   'label'=>'📅 アポ確定'),
  'in_progress'        => array('badge'=>'in-progress', 'label'=>'🔧 作業中'),
  'completed'          => array('badge'=>'completed',   'label'=>'✅ 完了'),
  'cancelled'          => array('badge'=>'pending',     'label'=>'⏸️ キャンセル'),
  'pending_completion' => array('badge'=>'pending',     'label'=>'⏸️ 未完了'),
  'visiting'           => array('badge'=>'visiting',    'label'=>'🚗 訪問予定'),
);
$type_map = array(
  'repair' => array('badge'=>'repair','label'=>'🔧 修理'),
  'sale'   => array('badge'=>'sale',  'label'=>'📦 販売'),
);

$status = $case['case_status'] ?? 'draft';
$type   = $case['case_type']   ?? 'repair';
$s      = $status_map[$status] ?? $status_map['draft'];
$t      = $type_map[$type]     ?? $type_map['repair'];
$url    = ddrc_url('cases', array('action'=>'detail','id'=>$case['id']));

$addr   = trim(($case['city']??'').' '.($case['address1']??''));
$dt     = '';
if(!empty($case['visit_date'])){
  $dt = ddrc_date($case['visit_date']);
  if(!empty($case['visit_time'])) $dt .= ' '.substr($case['visit_time'],0,5);
}
?>
<a href="<?php echo esc_url($url); ?>" class="project-card">
  <div class="project-card__customer"><?php echo esc_html($case['customer_name']??'—'); ?></div>
  <div class="project-card__case-number">#<?php echo esc_html($case['id']); ?></div>
  <div class="project-card__badges">
    <span class="badge badge--<?php echo esc_attr($t['badge']); ?>"><?php echo $t['label']; ?></span>
    <span class="badge badge--<?php echo esc_attr($s['badge']); ?>"><?php echo $s['label']; ?></span>
  </div>
  <?php if(!empty($case['product_category'])||!empty($case['product_name'])): ?>
  <div class="project-card__product">
    <?php echo esc_html(trim(($case['product_category']??'').' '.($case['maker']??'').' '.($case['product_name']??''))); ?>
  </div>
  <?php endif; ?>
  <?php if(!empty($case['symptom'])): ?>
  <div class="project-card__symptom">⚡ <?php echo esc_html(mb_substr($case['symptom'],0,50).(mb_strlen($case['symptom'])>50?'…':'')); ?></div>
  <?php endif; ?>
  <?php if($addr): ?>
  <div class="project-card__address">📍 <?php echo esc_html($addr); ?></div>
  <?php endif; ?>
  <?php if($dt): ?>
  <div class="project-card__datetime">🕐 <?php echo esc_html($dt); ?></div>
  <?php endif; ?>
  <?php if(!empty($case['notes'])): ?>
  <div class="project-card__note">📝 <?php echo esc_html(mb_substr($case['notes'],0,40)); ?></div>
  <?php endif; ?>
</a>
