<?php
/**
 * Review Comments Template
 *
 * Переопределяет single-product/review.php.
 * Вёрстка по образцу dist/shop-product.html.
 *
 * Closing </li> intentionally omitted — walker добавляет его сам.
 *
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$rating      = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
$rating_word = [ 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five' ];
$rating_cls  = isset( $rating_word[ $rating ] ) ? $rating_word[ $rating ] : '';
$author      = get_comment_author( $comment );
$author_url  = get_comment_author_url( $comment );
$date        = get_comment_date( get_option( 'date_format' ), $comment );
$avatar_url  = get_avatar_url( $comment, [ 'size' => 60 ] );
?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">

	<div class="comment-header d-md-flex align-items-center">
		<figure class="user-avatar">
			<?php if ( $avatar_url ) : ?>
			<img src="<?php echo esc_url( $avatar_url ); ?>" class="rounded-circle" width="60" height="60" alt="<?php echo esc_attr( $author ); ?>" loading="lazy">
			<?php endif; ?>
		</figure>
		<div>
			<h6 class="comment-author">
				<?php if ( $author_url ) : ?>
					<a href="<?php echo esc_url( $author_url ); ?>" class="link-dark text-reset"><?php echo esc_html( $author ); ?></a>
				<?php else : ?>
					<span class="link-dark"><?php echo esc_html( $author ); ?></span>
				<?php endif; ?>
			</h6>
			<ul class="post-meta">
				<li class="mb-0"><i class="uil uil-calendar-alt"></i><?php echo esc_html( $date ); ?></li>
			</ul>
		</div>
	</div>
	<!-- /.comment-header -->

	<?php if ( $rating_cls ) : ?>
	<div class="d-flex flex-row align-items-center mt-2 mb-2">
		<span class="ratings <?php echo esc_attr( $rating_cls ); ?>"></span>
	</div>
	<?php endif; ?>

	<div class="description mb-3">
		<?php comment_text(); ?>
	</div>

<?php
// Closing </li> добавляет walker (intentional WC pattern)
