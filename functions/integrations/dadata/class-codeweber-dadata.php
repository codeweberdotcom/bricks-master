<?php
/**
 * DaData API integration: address standardization (clean/address).
 * Only for Russian addresses. Keys must be stored on server (Redux), never exposed to frontend.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Codeweber_Dadata
 */
class Codeweber_Dadata {

	const API_URL_CLEAN   = 'https://cleaner.dadata.ru/api/v1/clean/address';
	const API_URL_SUGGEST = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address';
	const MAX_INPUT_LENGTH = 500;
	const MAX_QUERY_LENGTH = 300;

	/**
	 * Option name for Redux.
	 *
	 * @var string
	 */
	protected $opt_name = 'redux_demo';

	/**
	 * API token.
	 *
	 * @var string|null
	 */
	protected $token;

	/**
	 * Secret key (X-Secret).
	 *
	 * @var string|null
	 */
	protected $secret;

	/**
	 * Constructor. Loads credentials from Redux.
	 */
	public function __construct() {
		global $opt_name;
		if ( ! empty( $opt_name ) ) {
			$this->opt_name = $opt_name;
		}
		if ( class_exists( 'Redux' ) ) {
			$this->token  = Redux::get_option( $this->opt_name, 'dadata' );
			$this->secret = Redux::get_option( $this->opt_name, 'dadata_secret' );
		}
	}

	/**
	 * Check if DaData is enabled and configured (clean needs token + secret).
	 *
	 * @return bool
	 */
	public function is_available() {
		return ! empty( $this->token ) && ! empty( $this->secret );
	}

	/**
	 * Check if suggest API is available (only token required).
	 *
	 * @return bool
	 */
	public function is_suggest_available() {
		return ! empty( $this->token );
	}

	/**
	 * Sanitize address string before sending to API.
	 *
	 * @param string $address Raw address.
	 * @return string
	 */
	protected function sanitize_address( $address ) {
		$address = is_string( $address ) ? $address : '';
		$address = wp_strip_all_tags( $address );
		$address = preg_replace( '/\s+/', ' ', trim( $address ) );
		if ( mb_strlen( $address ) > self::MAX_INPUT_LENGTH ) {
			$address = mb_substr( $address, 0, self::MAX_INPUT_LENGTH );
		}
		return $address;
	}

	/**
	 * Call DaData clean/address and return normalized fields for WooCommerce.
	 *
	 * @param string $address_string One-line address (e.g. "мск сухонска 11/-89").
	 * @return array{ success: bool, data?: array, error?: string, code?: int }
	 */
	public function clean_address( $address_string ) {
		if ( ! $this->is_available() ) {
			return array(
				'success' => false,
				'error'   => __( 'Сервис проверки адреса временно недоступен.', 'codeweber' ),
				'code'    => 0,
			);
		}

		$address_string = $this->sanitize_address( $address_string );
		if ( $address_string === '' ) {
			return array(
				'success' => false,
				'error'   => __( 'Введите адрес для проверки.', 'codeweber' ),
				'code'    => 400,
			);
		}

		$body    = wp_json_encode( array( $address_string ) );
		$headers = array(
			'Content-Type'   => 'application/json',
			'Accept'         => 'application/json',
			'Authorization'  => 'Token ' . $this->token,
			'X-Secret'       => $this->secret,
		);

		$response = wp_remote_post(
			self::API_URL_CLEAN,
			array(
				'timeout' => 15,
				'headers' => $headers,
				'body'    => $body,
			)
		);

		$code = wp_remote_retrieve_response_code( $response );
		$body_response = wp_remote_retrieve_body( $response );

		if ( is_wp_error( $response ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'DaData clean_address WP_Error: ' . $response->get_error_message() );
			}
			return array(
				'success' => false,
				'error'   => __( 'Сервис проверки адреса временно недоступен.', 'codeweber' ),
				'code'    => 0,
			);
		}

		if ( 401 === $code || 403 === $code ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'DaData clean_address auth error, code: ' . $code );
			}
			return array(
				'success' => false,
				'error'   => __( 'Сервис проверки адреса временно недоступен.', 'codeweber' ),
				'code'    => $code,
			);
		}

		if ( 429 === $code ) {
			return array(
				'success' => false,
				'error'   => __( 'Слишком много запросов. Повторите через несколько секунд.', 'codeweber' ),
				'code'    => 429,
			);
		}

		if ( 200 !== $code ) {
			return array(
				'success' => false,
				'error'   => __( 'Сервис проверки адреса временно недоступен.', 'codeweber' ),
				'code'    => $code,
			);
		}

		$decoded = json_decode( $body_response, true );
		if ( ! is_array( $decoded ) || empty( $decoded[0] ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Не удалось разобрать адрес. Проверьте ввод или введите адрес вручную.', 'codeweber' ),
				'code'    => 200,
			);
		}

		$item = $decoded[0];
		$wc   = $this->map_dadata_to_woocommerce( $item );
		return array(
			'success' => true,
			'data'    => $wc,
			'code'    => 200,
		);
	}

	/**
	 * Map DaData clean/address response to WooCommerce address fields.
	 *
	 * @param array $item Single item from DaData response.
	 * @return array Keys: country, state, city, address_1, address_2, postcode, etc.
	 */
	protected function map_dadata_to_woocommerce( $item ) {
		$country_iso = isset( $item['country_iso_code'] ) ? $item['country_iso_code'] : 'RU';
		$region_name = isset( $item['region'] ) ? $item['region'] : '';
		$region_iso  = isset( $item['region_iso_code'] ) ? $item['region_iso_code'] : '';
		$city        = isset( $item['city'] ) ? $item['city'] : '';
		// Крым, Севастополь (приоритет перед UA-40), ДНР, ЛНР, Запорожская, Херсонская — маппинг в коды WooCommerce.
		if ( $region_iso === 'UA-43' || ( $region_name && strpos( $region_name, 'Крым' ) !== false ) ) {
			$region = 'CR';
		} elseif ( ( $region_name && strpos( $region_name, 'Севастополь' ) !== false ) || ( $city && strpos( $city, 'Севастополь' ) !== false ) ) {
			$region = 'SEV';
		} elseif ( $region_name && ( strpos( $region_name, 'Донецк' ) !== false || strpos( $region_name, 'ДНР' ) !== false ) ) {
			$region = 'DNR';
		} elseif ( $region_name && ( strpos( $region_name, 'Луганск' ) !== false || strpos( $region_name, 'ЛНР' ) !== false ) ) {
			$region = 'LNR';
		} elseif ( ( $region_name && ( strpos( $region_name, 'Запорож' ) !== false || strpos( $region_name, 'Запорожська' ) !== false ) ) || $region_iso === 'UA-40' ) {
			$region = 'ZAP';
		} elseif ( ( $region_name && ( strpos( $region_name, 'Херсон' ) !== false || strpos( $region_name, 'Херсонська' ) !== false ) ) || $region_iso === 'UA-65' ) {
			$region = 'KHE';
		} elseif ( $region_iso !== '' ) {
			$region = preg_replace( '/^RU\-/i', '', $region_iso );
		} else {
			$region = $region_name;
		}
		if ( empty( $city ) && ! empty( $item['settlement'] ) ) {
			$city = $item['settlement'];
		}
		$street = isset( $item['street_with_type'] ) ? $item['street_with_type'] : '';
		$house  = isset( $item['house'] ) ? trim( $item['house'] ) : '';
		$flat   = isset( $item['flat'] ) ? trim( $item['flat'] ) : '';
		$postal = isset( $item['postal_code'] ) ? $item['postal_code'] : '';

		$address_1 = $street;
		if ( $house !== '' ) {
			$address_1 = $address_1 ? $address_1 . ', ' . $house : $house;
		}
		if ( empty( $address_1 ) && ! empty( $item['result'] ) ) {
			$address_1 = $item['result'];
		}

		return array(
			'country'   => $country_iso,
			'state'     => $region,
			'city'      => $city,
			'address_1' => $address_1,
			'address_2' => '',
			'postcode'  => $postal,
		);
	}

	/**
	 * Call DaData suggest/address and return suggestions for autocomplete.
	 *
	 * @param string $query Search query (partial address).
	 * @param int    $count Max suggestions (default 10, max 20).
	 * @return array{ success: bool, suggestions?: array, error?: string, code?: int }
	 */
	public function suggest_address( $query, $count = 10 ) {
		if ( ! $this->is_suggest_available() ) {
			return array(
				'success' => false,
				'error'   => __( 'Сервис подсказок адресов временно недоступен.', 'codeweber' ),
				'code'    => 0,
			);
		}

		$query = is_string( $query ) ? $query : '';
		$query = wp_strip_all_tags( $query );
		$query = preg_replace( '/\s+/', ' ', trim( $query ) );
		if ( mb_strlen( $query ) > self::MAX_QUERY_LENGTH ) {
			$query = mb_substr( $query, 0, self::MAX_QUERY_LENGTH );
		}

		$count = max( 1, min( 20, (int) $count ) );
		$body  = wp_json_encode( array(
			'query' => $query,
			'count'  => $count,
		) );
		$headers = array(
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
			'Authorization' => 'Token ' . $this->token,
		);

		$response = wp_remote_post(
			self::API_URL_SUGGEST,
			array(
				'timeout' => 10,
				'headers' => $headers,
				'body'    => $body,
			)
		);

		$code = wp_remote_retrieve_response_code( $response );
		$body_response = wp_remote_retrieve_body( $response );

		if ( is_wp_error( $response ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'DaData suggest_address WP_Error: ' . $response->get_error_message() );
			}
			return array(
				'success' => false,
				'error'   => __( 'Сервис подсказок адресов временно недоступен.', 'codeweber' ),
				'code'    => 0,
			);
		}

		if ( 401 === $code || 403 === $code || 429 === $code ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'DaData suggest_address error, code: ' . $code );
			}
			return array(
				'success' => false,
				'error'   => 429 === $code
					? __( 'Слишком много запросов. Повторите через несколько секунд.', 'codeweber' )
					: __( 'Сервис подсказок адресов временно недоступен.', 'codeweber' ),
				'code'    => $code,
			);
		}

		if ( 200 !== $code ) {
			return array(
				'success' => false,
				'error'   => __( 'Сервис подсказок адресов временно недоступен.', 'codeweber' ),
				'code'    => $code,
			);
		}

		$decoded = json_decode( $body_response, true );
		$suggestions_raw = isset( $decoded['suggestions'] ) && is_array( $decoded['suggestions'] ) ? $decoded['suggestions'] : array();
		$suggestions = array();
		foreach ( $suggestions_raw as $s ) {
			$value = isset( $s['value'] ) ? $s['value'] : '';
			$data  = isset( $s['data'] ) && is_array( $s['data'] ) ? $s['data'] : array();
			$wc    = ! empty( $data ) ? $this->map_dadata_to_woocommerce( $data ) : array();
			$suggestions[] = array(
				'value' => $value,
				'data'  => $data,
				'wc'    => $wc,
			);
		}

		return array(
			'success'      => true,
			'suggestions' => $suggestions,
			'code'        => 200,
		);
	}
}
