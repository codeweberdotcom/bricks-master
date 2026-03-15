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
 *   $class = Codeweber_Options::style('button');
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

		/** Кэш стилей — загружается один раз за запрос. */
		private static ?array $style_cache = null;

		/** Конфигурация стилей: ключ → Redux-опция + маппинг значений. */
		private static array $style_config = [
			'button' => [
				'option'   => 'opt_button_select_style',
				'default'  => '1',
				'map'      => [
					'1' => ' rounded-pill',
					'2' => '',
					'3' => ' rounded-xl',
					'4' => ' rounded-0',
				],
				'fallback' => ' rounded-pill',
			],
			'card-radius' => [
				'option'   => 'opt_card_image_border_radius',
				'default'  => '2',
				'map'      => [
					'2' => 'rounded',
					'3' => 'rounded-xl',
					'4' => 'rounded-0',
				],
				'fallback' => '',
			],
			'form-radius' => [
				'option'   => 'opt_form_border_radius',
				'default'  => '2',
				'map'      => [
					'2' => ' rounded',
					'3' => ' rounded-xl',
					'4' => ' rounded-0',
				],
				'fallback' => ' rounded',
			],
			'accordion-radius' => [
				'option'   => 'opt_card_image_border_radius',
				'default'  => '2',
				'map'      => [
					'4' => 'rounded-0',
				],
				'fallback' => '',
			],
		];

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
		 * Получить CSS-класс стиля из настроек темы.
		 *
		 * Все стили загружаются за один вызов get_option() и кэшируются на весь запрос.
		 *
		 * Доступные ключи: 'button', 'card-radius', 'form-radius', 'accordion-radius'.
		 *
		 * @param string      $key     Ключ стиля.
		 * @param string|null $default Значение по умолчанию (если null — из конфига).
		 * @return string CSS-класс.
		 */
		public static function style( string $key, ?string $default = null ): string {
			if ( self::$style_cache === null ) {
				self::load_styles();
			}

			if ( isset( self::$style_cache[ $key ] ) ) {
				return self::$style_cache[ $key ];
			}

			if ( $default !== null ) {
				return $default;
			}

			return isset( self::$style_config[ $key ] ) ? self::$style_config[ $key ]['fallback'] : '';
		}

		/**
		 * Загрузить все стили за один вызов get_option().
		 */
		private static function load_styles(): void {
			self::$style_cache = [];
			$all_options = get_option( self::$opt, [] );

			foreach ( self::$style_config as $key => $cfg ) {
				$style_key = $all_options[ $cfg['option'] ] ?? $cfg['default'];
				self::$style_cache[ $key ] = $cfg['map'][ $style_key ] ?? $cfg['fallback'];
			}
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
		 * Получить URL из Redux media-поля по attachment ID.
		 *
		 * Использует wp_get_attachment_url() по ID вложения, что возвращает URL
		 * с текущим доменом сайта. Это позволяет безболезненно переносить сайт
		 * на другой домен без пересохранения настроек.
		 *
		 * @param string $key     Ключ настройки Redux (media-поле).
		 * @param string $default URL по умолчанию.
		 * @return string URL файла или $default.
		 */
		public static function media_url( string $key, string $default = '' ): string {
			$data = self::get( $key, '' );
			$url  = codeweber_get_media_url( $data );
			return $url !== '' ? $url : $default;
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
