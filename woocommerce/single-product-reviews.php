<?php
/**
 * Display single product reviews (comments)
 *
 * Переопределяет single-product-reviews.php.
 * Bootstrap-стилизация формы и списка по образцу темы (comments.php).
 *
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}

$btn_style    = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : '';
$form_radius  = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'form-radius' ) : ' rounded';
$commenter    = wp_get_current_commenter();
?>
<div id="reviews" class="woocommerce-Reviews">

	<div id="comments">

		<?php if ( have_comments() ) : ?>
		<ol class="commentlist">
			<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', [ 'callback' => 'woocommerce_comments' ] ) ); ?>
		</ol>

		<?php
		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
			$pages = paginate_comments_links( [
				'echo'      => false,
				'type'      => 'array',
				'prev_text' => '',
				'next_text' => '',
			] );
			if ( is_array( $pages ) ) :
		?>
		<nav class="d-flex mt-10" aria-label="<?php esc_attr_e( 'Reviews navigation', 'woocommerce' ); ?>">
			<ul class="pagination mb-0">
				<?php foreach ( $pages as $page ) :
					$active = strpos( $page, 'current' ) !== false;
					$page   = str_replace( 'page-numbers', 'page-link', $page );
					echo '<li class="page-item' . ( $active ? ' active' : '' ) . '">' . $page . '</li>'; // phpcs:ignore
				endforeach; ?>
			</ul>
		</nav>
		<?php endif; endif; ?>

		<?php else : ?>
		<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
		<?php endif; ?>

	</div>
	<!-- /#comments -->

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>

	<div id="review_form_wrapper" class="mt-10">
		<div id="review_form">
			<?php
			$name_email_required = (bool) get_option( 'require_name_email', 1 );

			$fields = [];

			if ( $name_email_required || get_option( 'woocommerce_review_rating_verification_required' ) === 'no' ) {
				$fields['author'] = '<div class="form-floating mb-4">
					<input type="text" class="form-control' . esc_attr( $form_radius ) . '" id="author" name="author"
						value="' . esc_attr( $commenter['comment_author'] ) . '"
						placeholder="' . esc_attr__( 'Name', 'woocommerce' ) . '"
						autocomplete="name"' . ( $name_email_required ? ' required' : '' ) . ' />
					<label for="author">' . esc_html__( 'Name', 'woocommerce' ) . ( $name_email_required ? ' <span class="required">*</span>' : '' ) . '</label>
				</div>';

				$fields['email'] = '<div class="form-floating mb-4">
					<input type="email" class="form-control' . esc_attr( $form_radius ) . '" id="email" name="email"
						value="' . esc_attr( $commenter['comment_author_email'] ) . '"
						placeholder="' . esc_attr__( 'Email', 'woocommerce' ) . '"
						autocomplete="email"' . ( $name_email_required ? ' required' : '' ) . ' />
					<label for="email">' . esc_html__( 'Email', 'woocommerce' ) . ( $name_email_required ? ' <span class="required">*</span>' : '' ) . '</label>
				</div>';
			}

			$comment_field = '';

			if ( wc_review_ratings_enabled() ) {
				// WC JS сам генерирует <p class="stars"> из <select> — не дублируем.
				$comment_field .= '<div class="comment-form-rating d-flex align-items-center gap-3 mb-4">
					<label for="rating" id="comment-form-rating-label" class="form-label mb-0">'
						. esc_html__( 'Your rating', 'woocommerce' )
						. ( wc_review_ratings_required() ? ' <span class="required">*</span>' : '' )
					. '</label>
					<select name="rating" id="rating" required style="display:none;">
						<option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce' ) . '</option>
						<option value="5">' . esc_html__( 'Perfect', 'woocommerce' ) . '</option>
						<option value="4">' . esc_html__( 'Good', 'woocommerce' ) . '</option>
						<option value="3">' . esc_html__( 'Average', 'woocommerce' ) . '</option>
						<option value="2">' . esc_html__( 'Not that bad', 'woocommerce' ) . '</option>
						<option value="1">' . esc_html__( 'Very poor', 'woocommerce' ) . '</option>
					</select>
				</div>';
			}

			$comment_field .= '<div class="form-floating mb-4 comment-form-comment">
				<textarea class="form-control' . esc_attr( $form_radius ) . '" id="comment" name="comment"
					placeholder="' . esc_attr__( 'Your review', 'woocommerce' ) . '"
					style="height:150px" required></textarea>
				<label for="comment">' . esc_html__( 'Your review', 'woocommerce' ) . ' <span class="required">*</span></label>
			</div>';

			$account_page_url = wc_get_page_permalink( 'myaccount' );

			$comment_form = [
				'title_reply'         => have_comments()
					? esc_html__( 'Add a review', 'woocommerce' )
					: sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'woocommerce' ), get_the_title() ),
				'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
				'title_reply_before'  => '<h3 id="reply-title" class="mb-5">',
				'title_reply_after'   => '</h3>',
				'comment_notes_after' => '',
				'label_submit'        => esc_html__( 'Submit', 'woocommerce' ),
				'class_submit'        => 'btn btn-primary mb-0' . esc_attr( $btn_style ),
				'class_form'          => 'comment-form',
				'logged_in_as'        => '',
				'fields'              => $fields,
				'comment_field'       => $comment_field,
			];

			if ( $account_page_url ) {
				$comment_form['must_log_in'] = '<p class="must-log-in">'
					. sprintf(
						esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ),
						'<a href="' . esc_url( $account_page_url ) . '">',
						'</a>'
					)
					. '</p>';
			}

			comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
			?>
		</div>
		<!-- /#review_form -->
	</div>

	<?php else : ?>
	<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
	<?php endif; ?>

	<div class="clear"></div>
</div>
<!-- /.woocommerce-Reviews -->
