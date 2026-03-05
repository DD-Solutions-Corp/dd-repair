<?php
/**
 * DDRC_Assets
 *
 * parse_request で直接HTMLを出力するため wp_enqueue_scripts は不要。
 * このクラスはテーマのFont AwesomeをKIT URL経由で読み込まれないよう
 * 念のためdequeueするだけの役割（保険）。
 */
defined( 'ABSPATH' ) || exit;

class DDRC_Assets {

    public static function init(): void {
        // テーマが kit.fontawesome.com を登録する前に除去
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'remove_fa_kit' ), 1 );
    }

    public static function remove_fa_kit(): void {
        // アプリページ以外は何もしない
        // ※ parse_request 段階で exit するので通常はここに到達しない
        // ただし他のページで kit が邪魔にならないよう念のためそのまま
    }
}
