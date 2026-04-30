<?php
/**
 * Telegram Bot — отправка сообщений через Telegram Bot API.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CW_Telegram_Bot {

	private string $token;
	private string $chat_id;

	public function __construct( string $token, string $chat_id ) {
		$this->token   = $token;
		$this->chat_id = $chat_id;
	}

	/**
	 * Создаёт экземпляр из настроек Redux.
	 * Возвращает null если Telegram выключен или не настроен.
	 */
	public static function from_redux(): ?self {
		if ( ! self::get_opt( 'telegram_bot_enabled' ) ) {
			return null;
		}

		$token   = trim( (string) self::get_opt( 'telegram_bot_token' ) );
		$chat_id = trim( (string) self::get_opt( 'telegram_bot_chat_id' ) );

		if ( ! $token || ! $chat_id ) {
			return null;
		}

		return new self( $token, $chat_id );
	}

	/**
	 * Отправить сообщение в настроенный чат.
	 *
	 * @param string $text       Текст (поддерживается HTML-разметка Telegram).
	 * @param string $parse_mode 'HTML' | 'Markdown' | 'MarkdownV2'.
	 */
	public function send_message( string $text, string $parse_mode = 'HTML' ): bool {
		$url = 'https://api.telegram.org/bot' . $this->token . '/sendMessage';

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'chat_id'                  => $this->chat_id,
						'text'                     => $text,
						'parse_mode'               => $parse_mode,
						'disable_web_page_preview' => true,
					)
				),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return ! empty( $body['ok'] );
	}

	/**
	 * Helper: читает настройку Redux.
	 */
	private static function get_opt( string $key, mixed $default = '' ) {
		if ( ! class_exists( 'Redux' ) ) {
			return $default;
		}
		global $opt_name;
		return Redux::get_option( $opt_name, $key, $default );
	}
}
