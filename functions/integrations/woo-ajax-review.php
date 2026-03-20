<?php
/**
 * WooCommerce AJAX Review Submission
 *
 * Обрабатывает отправку отзыва без перезагрузки страницы.
 * action: cw_submit_review
 *
 * @package CodeWeber
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_cw_submit_review',        'cw_ajax_submit_review' );
add_action( 'wp_ajax_nopriv_cw_submit_review', 'cw_ajax_submit_review' );

/**
 * Handle AJAX review submission.
 */
function cw_ajax_submit_review() {

	// ── Nonce ────────────────────────────────────────────────────────────────
	if ( ! check_ajax_referer( 'cw_review_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => __( 'Security check failed.', 'codeweber' ) ], 403 );
	}

	// ── Данные формы ─────────────────────────────────────────────────────────
	$post_id = absint( $_POST['comment_post_ID'] ?? 0 );
	$content = sanitize_textarea_field( wp_unslash( $_POST['comment'] ?? '' ) );
	$rating  = absint( $_POST['rating'] ?? 0 );
	$author  = sanitize_text_field( wp_unslash( $_POST['author'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

	// ── Базовая валидация ─────────────────────────────────────────────────────
	if ( ! $post_id || empty( $content ) ) {
		wp_send_json_error( [ 'message' => __( 'Please fill in required fields.', 'woocommerce' ) ] );
	}

	$product = wc_get_product( $post_id );
	if ( ! $product ) {
		wp_send_json_error( [ 'message' => __( 'Invalid product.', 'woocommerce' ) ] );
	}

	if ( wc_review_ratings_required() && ( $rating < 1 || $rating > 5 ) ) {
		wp_send_json_error( [ 'message' => __( 'Please select a rating.', 'woocommerce' ) ] );
	}

	// ── Проверка: нужна ли покупка ────────────────────────────────────────────
	if ( get_option( 'woocommerce_review_rating_verification_required' ) !== 'no' ) {
		if ( ! wc_customer_bought_product( $email, get_current_user_id(), $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ) ] );
		}
	}

	// ── Данные комментария ────────────────────────────────────────────────────
	$user         = wp_get_current_user();
	$comment_data = [
		'comment_post_ID'      => $post_id,
		'comment_content'      => $content,
		'comment_type'         => 'review',
		'comment_author'       => $user->ID ? $user->display_name : $author,
		'comment_author_email' => $user->ID ? $user->user_email   : $email,
		'comment_author_url'   => '',
		'user_id'              => $user->ID,
	];

	// ── Создание комментария ──────────────────────────────────────────────────
	$comment_id = wp_new_comment( wp_slash( $comment_data ), true );

	if ( is_wp_error( $comment_id ) ) {
		wp_send_json_error( [ 'message' => $comment_id->get_error_message() ] );
	}

	// ── Сохранение рейтинга ───────────────────────────────────────────────────
	if ( $rating ) {
		add_comment_meta( $comment_id, 'rating', $rating, true );
		WC_Comments::clear_transients( $post_id );
	}

	// ── Статус и HTML ─────────────────────────────────────────────────────────
	$comment = get_comment( $comment_id );
	$status  = wp_get_comment_status( $comment_id );

	$html = '';
	if ( 'approved' === $status ) {
		ob_start();
		// Устанавливаем глобал — review.php его использует
		$GLOBALS['comment'] = $comment; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		wc_get_template( 'single-product/review.php', [ 'comment' => $comment ] );
		$html  = ob_get_clean();
		$html .= '</li>'; // Walker_Comment::end_el() в обычном потоке — добавляем вручную
	}

	wp_send_json_success( [
		'status'  => $status,
		'html'    => $html,
		'message' => 'approved' === $status
			? __( 'Your review has been submitted. Thank you!', 'codeweber' )
			: __( 'Your review is awaiting moderation.', 'woocommerce' ),
	] );
}
