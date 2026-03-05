<?php
defined( 'ABSPATH' ) || exit;

/**
 * テンプレートロード
 */
function ddrc_template( string $name, array $vars = array() ): void {
    extract( $vars, EXTR_SKIP ); // phpcs:ignore
    $file = DDRC_DIR . 'templates/' . $name . '.php';
    if ( file_exists( $file ) ) {
        require $file;
    }
}

/**
 * URL生成
 */
function ddrc_url( string $tab = '', array $extra = array() ): string {
    return DDRC_Router::url( $tab, $extra );
}

/**
 * フラッシュメッセージ表示
 */
function ddrc_flash(): void {
    // phpcs:disable WordPress.Security.NonceVerification
    $msg = sanitize_key( $_GET['msg'] ?? '' );
    $err = sanitize_key( $_GET['err'] ?? '' );
    // phpcs:enable
    if ( $msg === 'saved' ) {
        echo '<div class="toast toast-success">✅ 保存しました</div>';
    } elseif ( $msg === 'updated' ) {
        echo '<div class="toast toast-success">✅ 更新しました</div>';
    } elseif ( $err ) {
        echo '<div class="toast toast-error">❌ エラーが発生しました</div>';
    }
}

/**
 * ステータスラベル
 */
function ddrc_status_label( string $status ): string {
    $map = array(
        'draft'       => '📝 受付済み',
        'estimated'   => '📅 アポ確定',
        'in_progress' => '🔧 作業中',
        'completed'   => '✅ 完了',
        'cancelled'   => '❌ キャンセル',
    );
    return $map[ $status ] ?? $status;
}

/**
 * 支払いラベル
 */
function ddrc_payment_label( string $status ): string {
    $map = array(
        'unpaid'  => '未払い',
        'partial' => '一部払い',
        'paid'    => '完済',
    );
    return $map[ $status ] ?? $status;
}

/**
 * 日付フォーマット
 */
function ddrc_date( string $date ): string {
    if ( ! $date ) return '';
    try {
        $d = new DateTime( $date );
        return $d->format( 'Y年n月j日' );
    } catch ( Exception $e ) {
        return $date;
    }
}

/**
 * 金額フォーマット
 */
function ddrc_price( $amount ): string {
    if ( ! $amount ) return '¥0';
    return '¥' . number_format( (float) $amount );
}
