<?php
/**
 * Codeweber_Options — обёртка над Redux Framework.
 *
 * Устраняет повторяющийся шаблон:
 *   global $opt_name; if (empty($opt_name)) { $opt_name = 'redux_demo'; }
 *   if (class_exists('Redux')) { $val = Redux::get_option($opt_name, 'key'); }
 *
 * Использование:
 *   $val = Codeweber_Options::get('key', 'default');
 *   $val = Codeweber_Options::get_post_meta($post_id, 'key');
 *   if (Codeweber_Options::is_ready()) { ... }
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Codeweber_Options' ) ) {

	class Codeweber_Options {

		/** Ключ опций Redux Framework. */
		private static string $opt = 'redux_demo';

		/**
		 * Получить значение настройки темы из Redux.
		 *
		 * @param string $key     Ключ настройки.
		 * @param mixed  $default Значение по умолчанию.
		 * @return mixed
		 */
		public static function get( string $key, $default = '' ) {
			if ( ! class_exists( 'Redux' ) ) {
				return $default;
			}
			$value = Redux::get_option( self::$opt, $key );
			return ( $value !== null && $value !== '' ) ? $value : $default;
		}

		/**
		 * Получить мета-значение поста из Redux (Redux::get_post_meta).
		 * Возвращает false, если мета не задана (поведение совместимо с оригинальным Redux).
		 *
		 * @param int    $post_id ID записи.
		 * @param string $key     Ключ мета-поля.
		 * @return mixed
		 */
		public static function get_post_meta( int $post_id, string $key ) {
			if ( ! class_exists( 'Redux' ) ) {
				return false;
			}
			return Redux::get_post_meta( self::$opt, $post_id, $key );
		}

		/**
		 * Проверить, инициализирован ли экземпляр Redux.
		 * Используется вместо: Redux_Instances::get_instance($opt_name) !== null
		 *
		 * @return bool
		 */
		public static function is_ready(): bool {
			if ( ! class_exists( 'Redux' ) || ! class_exists( 'Redux_Instances' ) ) {
				return false;
			}
			return Redux_Instances::get_instance( self::$opt ) !== null;
		}
	}
}
