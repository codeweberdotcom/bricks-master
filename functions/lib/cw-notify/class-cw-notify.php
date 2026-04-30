<?php
/**
 * CW_Notify — универсальный менеджер уведомлений.
 *
 * Подключает cw-notify.js и передаёт в него настройки из Redux.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CW_Notify
 */
class CW_Notify {

	/**
	 * Constructor — регистрирует enqueue.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue cw-notify.js и локализует конфиг.
	 */
	public function enqueue_scripts() {
		$js_path = get_template_directory() . '/functions/lib/cw-notify/cw-notify.js';
		$js_url  = get_template_directory_uri() . '/functions/lib/cw-notify/cw-notify.js';

		if ( ! file_exists( $js_path ) ) {
			return;
		}

		wp_enqueue_script(
			'cw-notify',
			$js_url,
			array( 'plugins-scripts' ),
			filemtime( $js_path ),
			true
		);

		wp_localize_script( 'cw-notify', 'cwNotifyConfig', self::get_config() );
	}

	/**
	 * Возвращает конфиг для JS из настроек Redux.
	 *
	 * @return array
	 */
	public static function get_config() {
		return array(
			'enabled'  => self::get_opt( 'notify_enabled', 1 ) ? true : false,
			'position' => self::get_opt( 'notify_position', 'bottom-end' ),
			'delay'    => (int) self::get_opt( 'notify_delay', 3000 ),
			'events'   => array(
				'wishlist'   => (bool) self::get_opt( 'notify_event_wishlist', 1 ),
				'cart'       => (bool) self::get_opt( 'notify_event_cart', 1 ),
				'form'       => (bool) self::get_opt( 'notify_event_form', 1 ),
				'newsletter' => (bool) self::get_opt( 'notify_event_newsletter', 1 ),
				'dadata'     => (bool) self::get_opt( 'notify_event_dadata', 1 ),
				'copy'       => (bool) self::get_opt( 'notify_event_copy', 1 ),
			),
		);
	}

	/**
	 * Проверяет, включены ли уведомления для конкретного события (PHP-сторона).
	 *
	 * @param  string $event  Ключ события.
	 * @return bool
	 */
	public static function is_event_enabled( $event = '' ) {
		if ( ! self::get_opt( 'notify_enabled', 1 ) ) {
			return false;
		}
		if ( $event ) {
			return (bool) self::get_opt( 'notify_event_' . $event, 1 );
		}
		return true;
	}

	/**
	 * Отправить server-side уведомление по всем подключённым каналам.
	 *
	 * Channels (Telegram и др.) подключаются через хук:
	 * add_action( 'cw_notify_server_notification', function( $event, $text ) { ... }, 10, 2 );
	 *
	 * @param string $event Событие: 'form', 'order', 'newsletter', ...
	 * @param string $text  Текст уведомления (plain или HTML Telegram).
	 */
	public static function send_server_notification( string $event, string $text ): void {
		do_action( 'cw_notify_server_notification', $event, $text );
	}

	/**
	 * Helper: получить опцию Redux.
	 *
	 * @param  string $key     Ключ.
	 * @param  mixed  $default Значение по умолчанию.
	 * @return mixed
	 */
	private static function get_opt( $key, $default = '' ) {
		if ( ! class_exists( 'Redux' ) ) {
			return $default;
		}
		global $opt_name;
		return Redux::get_option( $opt_name, $key, $default );
	}
}
