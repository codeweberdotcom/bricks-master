<?php
/**
 * Тестовый платёжный шлюз для проверки «Способы оплаты» в Мой аккаунт.
 * Без регистраций и API: добавляет фейковую карту по кнопке.
 * Для продакшена отключить или удалить.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

class WC_Gateway_Codeweber_Test extends WC_Payment_Gateway {

	public function __construct() {
		$this->id                 = 'codeweber_test';
		$this->method_title       = __( 'Test (no registration)', 'codeweber' );
		$this->method_description = __( 'Test gateway to add payment methods in My Account. No API or signup required.', 'codeweber' );
		$this->title              = __( 'Test payment', 'codeweber' );
		$this->description       = __( 'For testing: add a fake saved payment method.', 'codeweber' );
		$this->has_fields         = true;
		$this->supports           = array( 'products', 'tokenization' );
		$this->enabled            = 'yes';

		$this->init_form_fields();
		$this->init_settings();
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable test gateway', 'codeweber' ),
				'default' => 'yes',
			),
		);
	}

	public function payment_fields() {
		echo '<p class="form-row form-row-wide">';
		esc_html_e( 'Click "Add payment method" below to save a test card. No real payment.', 'codeweber' );
		echo '</p>';
	}

	public function add_payment_method() {
		$token = new WC_Payment_Token_CC();
		$token->set_token( 'test_' . get_current_user_id() . '_' . time() );
		$token->set_gateway_id( $this->id );
		$token->set_card_type( 'visa' );
		$token->set_last4( '4242' );
		$token->set_expiry_month( '12' );
		$token->set_expiry_year( '2030' );
		$token->set_user_id( get_current_user_id() );

		if ( $token->save() ) {
			return array(
				'result'   => 'success',
				'redirect' => wc_get_endpoint_url( 'payment-methods', '', wc_get_page_permalink( 'myaccount' ) ),
			);
		}

		return array(
			'result'   => 'failure',
			'redirect' => wc_get_endpoint_url( 'payment-methods', '', wc_get_page_permalink( 'myaccount' ) ),
		);
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return array( 'result' => 'failure' );
		}
		$order->payment_complete();
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}
}
