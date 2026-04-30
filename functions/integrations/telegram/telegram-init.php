<?php
/**
 * Telegram Bot — инициализация и подключение к хукам.
 *
 * Telegram подключается как канал CW_Notify через хук cw_notify_server_notification.
 * Другие каналы (email, Slack и т.д.) могут подключиться туда же.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-cw-telegram-bot.php';

// ── Telegram как канал CW_Notify ──────────────────────────────────────────────

add_action( 'cw_notify_server_notification', 'codeweber_telegram_channel', 10, 2 );

function codeweber_telegram_channel( string $event, string $text ): void {
	if ( ! codeweber_telegram_event_enabled( $event ) ) {
		return;
	}

	$bot = CW_Telegram_Bot::from_redux();
	if ( $bot ) {
		$bot->send_message( $text );
	}
}

// ── CodeWeber Forms ───────────────────────────────────────────────────────────

add_action( 'codeweber_form_saved', 'codeweber_telegram_on_form_saved', 20, 3 );

function codeweber_telegram_on_form_saved( int $submission_id, $form_id, array $fields ): void {
	$form_name = '';
	if ( $form_id && is_numeric( $form_id ) ) {
		$post = get_post( (int) $form_id );
		if ( $post ) {
			$form_name = $post->post_title;
		}
	}
	if ( ! $form_name ) {
		$form_name = __( 'Form', 'codeweber' );
	}

	// IP из БД, URL страницы из заголовка AJAX-запроса.
	$ip       = codeweber_telegram_get_submission_ip( $submission_id );
	$page_url = wp_get_referer() ?: '';

	$text = codeweber_telegram_format_form( $submission_id, $form_name, $fields, $ip, $page_url );
	CW_Notify::send_server_notification( 'form', $text );
}

// ── WooCommerce: новый заказ ──────────────────────────────────────────────────

add_action( 'woocommerce_checkout_order_created', 'codeweber_telegram_on_order', 20, 1 );

function codeweber_telegram_on_order( $order ): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$order_id = $order->get_id();
	$total    = $order->get_formatted_order_total();
	$name     = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
	$phone    = $order->get_billing_phone();
	$email    = $order->get_billing_email();
	$site     = get_bloginfo( 'name' );

	$lines   = array();
	$lines[] = '🛒 <b>' . esc_html( $site ) . '</b>';
	$lines[] = __( 'New order', 'codeweber' ) . ' <b>#' . $order_id . '</b>';
	$lines[] = '';
	if ( $name ) {
		$lines[] = '<b>' . __( 'Name', 'codeweber' ) . ':</b> ' . esc_html( $name );
	}
	if ( $phone ) {
		$lines[] = '<b>' . __( 'Phone', 'codeweber' ) . ':</b> ' . esc_html( $phone );
	}
	if ( $email ) {
		$lines[] = '<b>' . __( 'Email', 'codeweber' ) . ':</b> ' . esc_html( $email );
	}
	$lines[] = '<b>' . __( 'Total', 'codeweber' ) . ':</b> ' . wp_strip_all_tags( $total );
	$lines[] = '';
	$lines[] = '🕐 ' . wp_date( 'd.m.Y H:i' );

	CW_Notify::send_server_notification( 'order', implode( "\n", $lines ) );
}

// ── Newsletter: новая подписка ────────────────────────────────────────────────

add_action( 'codeweber_newsletter_subscribed', 'codeweber_telegram_on_newsletter', 20, 1 );

function codeweber_telegram_on_newsletter( string $email ): void {
	$site    = get_bloginfo( 'name' );
	$lines   = array();
	$lines[] = '📧 <b>' . esc_html( $site ) . '</b>';
	$lines[] = __( 'New newsletter subscription', 'codeweber' );
	$lines[] = esc_html( $email );
	$lines[] = '';
	$lines[] = '🕐 ' . wp_date( 'd.m.Y H:i' );

	CW_Notify::send_server_notification( 'newsletter', implode( "\n", $lines ) );
}

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Проверяет, включено ли событие в настройках Telegram.
 *
 * @param string $event 'form' | 'order' | 'newsletter'
 */
function codeweber_telegram_event_enabled( string $event ): bool {
	if ( ! class_exists( 'Redux' ) ) {
		return false;
	}
	global $opt_name;
	$events = Redux::get_option( $opt_name, 'telegram_bot_events', array() );
	return ! empty( $events[ $event ] );
}

/**
 * Получает IP-адрес из записи сабмита в БД.
 */
function codeweber_telegram_get_submission_ip( int $submission_id ): string {
	if ( ! $submission_id || ! class_exists( 'CodeweberFormsDatabase' ) ) {
		return '';
	}
	$db  = new CodeweberFormsDatabase();
	$row = $db->get_submission( $submission_id );
	return $row ? (string) ( $row->ip_address ?? '' ) : '';
}

/**
 * Форматирует текст уведомления формы для Telegram.
 */
function codeweber_telegram_format_form(
	int $submission_id,
	string $form_name,
	array $fields,
	string $ip = '',
	string $page_url = ''
): string {
	$site    = get_bloginfo( 'name' );
	$lines   = array();
	$lines[] = '📬 <b>' . esc_html( $site ) . '</b>';
	$lines[] = __( 'New form submission', 'codeweber' ) . ': <b>' . esc_html( $form_name ) . '</b>';
	if ( $submission_id ) {
		$lines[] = '#' . $submission_id;
	}
	$lines[] = '';

	// Системные и служебные поля — не выводить.
	$skip = array( '_utm_data', 'newsletter_consents', 'form_name', 'form_type' );

	foreach ( $fields as $key => $value ) {
		if ( in_array( $key, $skip, true ) ) {
			continue;
		}
		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}
		$value = trim( (string) $value );
		if ( ! $value || preg_match( '/^[a-f0-9\-]{36}$/i', $value ) ) {
			continue;
		}
		$label   = ucfirst( str_replace( array( '_', '-' ), ' ', $key ) );
		$lines[] = '<b>' . esc_html( $label ) . ':</b> ' . esc_html( $value );
	}

	$lines[] = '';

	if ( $page_url ) {
		$lines[] = '🌐 <b>' . __( 'Page', 'codeweber' ) . ':</b> ' . esc_url( $page_url );
	}
	if ( $ip ) {
		$lines[] = '🔎 <b>IP:</b> ' . esc_html( $ip );
	}

	$lines[] = '🕐 ' . wp_date( 'd.m.Y H:i' );

	return implode( "\n", $lines );
}
