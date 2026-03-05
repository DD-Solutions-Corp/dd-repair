<?php
defined( 'ABSPATH' ) || exit;
$user    = is_user_logged_in() ? wp_get_current_user() : null;
$initial = $user ? mb_strtoupper( mb_substr( $user->display_name ?? 'G', 0, 1, 'UTF-8' ) ) : 'G';
$email   = $user ? ( $user->user_email ?? '' ) : '';
?>
<div id="page-settings" class="page active">
  <div class="section-title">⚙️ 設定</div>

  <!-- アカウント情報（リファレンスHTML準拠） -->
  <div class="card" style="margin-bottom:16px;">
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
      <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:white;font-size:28px;font-weight:600;">
        <?php echo esc_html($initial); ?>
      </div>
      <div style="flex:1;">
        <div style="font-size:18px;font-weight:600;color:#1e293b;margin-bottom:4px;"><?php echo esc_html($user ? $user->display_name : 'ゲスト'); ?></div>
        <div style="font-size:14px;color:#64748b;"><?php echo esc_html($email); ?></div>
        <div style="display:inline-block;margin-top:4px;padding:4px 8px;background:#dbeafe;color:#1e40af;border-radius:4px;font-size:12px;font-weight:500;">
          <?php echo current_user_can('manage_options')?'管理者':'スタッフ'; ?>
        </div>
      </div>
    </div>
    <a href="<?php echo esc_url(admin_url('profile.php')); ?>" class="btn btn-outline btn-full">プロフィールを編集</a>
  </div>

  <!-- システム設定 -->
  <div class="card" style="margin-bottom:16px;">
    <h3 style="font-size:16px;font-weight:600;color:#1e293b;margin-bottom:16px;">システム設定</h3>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f1f5f9;">
      <div>
        <div style="font-size:14px;font-weight:500;color:#1e293b;">通知</div>
        <div style="font-size:12px;color:#64748b;">プッシュ通知を受け取る</div>
      </div>
      <label style="position:relative;display:inline-block;width:48px;height:28px;">
        <input type="checkbox" style="opacity:0;width:0;height:0;" checked>
        <span style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#10b981;transition:.4s;border-radius:28px;"></span>
      </label>
    </div>
    <div style="padding-top:12px;">
      <button onclick="alert('AirPrint機能（実装予定）')" class="btn btn-outline btn-full" style="justify-content:space-between;">
        <span>🖨️ AirPrint プリンター</span><span style="color:#94a3b8;">›</span>
      </button>
    </div>
  </div>

  <!-- アプリ情報 -->
  <div class="card" style="margin-bottom:16px;">
    <h3 style="font-size:16px;font-weight:600;color:#1e293b;margin-bottom:16px;">アプリ情報</h3>
    <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f1f5f9;">
      <div style="font-size:14px;color:#64748b;">バージョン</div>
      <div style="font-size:14px;font-weight:500;color:#1e293b;"><?php echo esc_html(DDRC_VERSION); ?></div>
    </div>
    <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f1f5f9;">
      <div style="font-size:14px;color:#64748b;">WordPress</div>
      <div style="font-size:14px;font-weight:500;color:#1e293b;"><?php echo esc_html(get_bloginfo('version')); ?></div>
    </div>
    <?php if(current_user_can('manage_options')): ?>
    <div style="padding-top:12px;">
      <a href="<?php echo esc_url(ddrc_url().'?ddrc_debug=1'); ?>" class="btn btn-outline btn-full">🔍 診断ページを開く</a>
    </div>
    <?php endif; ?>
  </div>

  <!-- ログアウト -->
  <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>"
     onclick="return confirm('ログアウトしますか？')"
     style="display:block;width:100%;padding:16px;background:#ef4444;color:white;border:none;border-radius:12px;font-weight:600;cursor:pointer;font-size:16px;box-shadow:0 4px 12px rgba(239,68,68,.3);text-align:center;text-decoration:none;">
    ログアウト
  </a>
</div>
