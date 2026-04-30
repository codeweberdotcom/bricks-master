<?php
/**
 * Telegram Bot — инициализация и подключение к хукам.
 *
 * Telegram подключается как канал CW_Notify через хук cw_notify_server_notification.
 * Отправка — асинхронно через WP Cron, не блокирует ответ пользователю.
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
	// Ставим в очередь WP Cron — не блокирует текущий запрос.
	wp_schedule_single_event( time(), 'codeweber_telegram_send_async', array( $text ) );
}

// WP Cron handler — вызывается в фоне.
add_action( 'codeweber_telegram_send_async', 'codeweber_telegram_send_async_handler' );

function codeweber_telegram_send_async_handler( string $text ): void {
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

	$ip       = codeweber_telegram_get_submission_ip( $submission_id );
	$ua       = codeweber_telegram_get_submission_ua( $submission_id );
	$page_url = wp_get_referer() ?: '';

	$text = codeweber_telegram_format_form( $submission_id, $form_name, $fields, $ip, $page_url, $ua );
	CW_Notify::send_server_notification( 'form', $text );
}

// ── SMTP: ошибка отправки почты ──────────────────────────────────────────────

add_action( 'wp_mail_failed', 'codeweber_telegram_on_mail_failed' );

function codeweber_telegram_on_mail_failed( WP_Error $error ): void {
	$bot = CW_Telegram_Bot::from_redux();
	if ( ! $bot ) {
		return;
	}

	$data    = $error->get_error_data();
	$to      = is_array( $data['to'] ?? null ) ? implode( ', ', $data['to'] ) : ( $data['to'] ?? '' );
	$subject = $data['subject'] ?? '';
	$message = $error->get_error_message();
	$site    = get_bloginfo( 'name' );

	$lines   = array();
	$lines[] = '⚠️ <b>' . esc_html( $site ) . '</b>';
	$lines[] = __( 'SMTP error', 'codeweber' );
	$lines[] = '';
	if ( $to ) {
		$lines[] = '<b>To:</b> ' . esc_html( $to );
	}
	if ( $subject ) {
		$lines[] = '<b>' . __( 'Subject', 'codeweber' ) . ':</b> ' . esc_html( $subject );
	}
	$lines[] = '<b>' . __( 'Error', 'codeweber' ) . ':</b> ' . esc_html( $message );
	$lines[] = '';
	$lines[] = '🕐 ' . wp_date( 'd.m.Y H:i' );

	$bot->send_message( implode( "\n", $lines ) );
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
 * Получает User-Agent из записи сабмита в БД.
 */
function codeweber_telegram_get_submission_ua( int $submission_id ): string {
	if ( ! $submission_id || ! class_exists( 'CodeweberFormsDatabase' ) ) {
		return '';
	}
	$db  = new CodeweberFormsDatabase();
	$row = $db->get_submission( $submission_id );
	$ua  = $row ? (string) ( $row->user_agent ?? '' ) : '';
	return $ua ? codeweber_telegram_parse_ua( $ua ) : '';
}

/**
 * Парсит UA-строку в краткий вид «Browser Ver / OS».
 */
function codeweber_telegram_parse_ua( string $ua ): string {
	$browser = 'Unknown';
	$os      = 'Unknown';

	// Браузер — порядок важен: Edge перед Chrome, OPR перед Chrome.
	if ( preg_match( '/Edg(?:e|)\/([\d]+)/', $ua, $m ) ) {
		$browser = 'Edge ' . $m[1];
	} elseif ( preg_match( '/OPR\/([\d]+)/', $ua, $m ) ) {
		$browser = 'Opera ' . $m[1];
	} elseif ( preg_match( '/YaBrowser\/([\d]+)/', $ua, $m ) ) {
		$browser = 'Yandex ' . $m[1];
	} elseif ( preg_match( '/Chrome\/([\d]+)/', $ua, $m ) ) {
		$browser = 'Chrome ' . $m[1];
	} elseif ( preg_match( '/Firefox\/([\d]+)/', $ua, $m ) ) {
		$browser = 'Firefox ' . $m[1];
	} elseif ( preg_match( '/Safari\/([\d]+)/', $ua, $m ) && preg_match( '/Version\/([\d]+)/', $ua, $mv ) ) {
		$browser = 'Safari ' . $mv[1];
	}

	// ОС.
	if ( preg_match( '/Windows NT ([\d.]+)/', $ua, $m ) ) {
		$map = array( '10.0' => 'Windows 10/11', '6.3' => 'Windows 8.1', '6.2' => 'Windows 8', '6.1' => 'Windows 7' );
		$os  = $map[ $m[1] ] ?? 'Windows';
	} elseif ( preg_match( '/Android ([\d.]+)/', $ua, $m ) ) {
		$os = 'Android ' . $m[1];
	} elseif ( strpos( $ua, 'iPhone' ) !== false ) {
		$os = 'iPhone';
	} elseif ( strpos( $ua, 'iPad' ) !== false ) {
		$os = 'iPad';
	} elseif ( preg_match( '/Mac OS X ([\d_]+)/', $ua, $m ) ) {
		$os = 'macOS ' . str_replace( '_', '.', $m[1] );
	} elseif ( strpos( $ua, 'Linux' ) !== false ) {
		$os = 'Linux';
	}

	return $browser . ' / ' . $os;
}

/**
 * Форматирует текст уведомления формы для Telegram.
 */
function codeweber_telegram_format_form(
	int $submission_id,
	string $form_name,
	array $fields,
	string $ip = '',
	string $page_url = '',
	string $ua = ''
): string {
	$site    = get_bloginfo( 'name' );
	$lines   = array();
	$lines[] = '📬 <b>' . esc_html( $site ) . '</b>';
	$lines[] = __( 'New form submission', 'codeweber' ) . ': <b>' . esc_html( $form_name ) . '</b>';
	if ( $submission_id ) {
		$lines[] = '#' . $submission_id;
	}
	$lines[] = '';

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
	if ( $ua ) {
		$lines[] = '💻 <b>UA:</b> ' . esc_html( $ua );
	}

	$lines[] = '🕐 ' . wp_date( 'd.m.Y H:i' );

	return implode( "\n", $lines );
}
