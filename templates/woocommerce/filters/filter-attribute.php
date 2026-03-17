<?php
/**
 * Attribute / tag filter — shop2.html Bootstrap style.
 *
 * Expected variables:
 *   $terms_data   array  — from cw_get_attribute_filter_terms() or cw_get_tag_filter_terms()
 *   $display_mode string — 'checkbox' | 'list' | 'button'
 *   $show_count   bool
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $terms_data ) ) {
	return;
}

$display_mode = $display_mode ?? 'checkbox';
$show_count   = $show_count ?? true;
?>

<?php if ( 'button' === $display_mode ) : ?>

	<div class="d-flex flex-wrap gap-1">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$count     = $item['count'];
			?>
			<a href="<?php echo esc_url( $item['url'] ); ?>"
				class="btn btn-sm pjax-link <?php echo $is_active ? 'btn-secondary' : 'btn-outline-secondary'; ?>"
				<?php if ( $show_count ) : ?>title="(<?php echo esc_attr( $count ); ?>)"<?php endif; ?>>
				<?php echo esc_html( $term->name ); ?>
			</a>
		<?php endforeach; ?>
	</div>

<?php elseif ( 'list' === $display_mode ) : ?>

	<ul class="list-unstyled ps-0 mb-0">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$count     = $item['count'];
			?>
			<li class="mb-1">
				<a href="<?php echo esc_url( $item['url'] ); ?>"
					class="link-body pjax-link<?php echo $is_active ? ' fw-semibold' : ''; ?>"
					style="text-decoration:none;">
					<?php echo esc_html( $term->name ); ?>
					<?php if ( $show_count ) : ?>
						<span class="fs-sm text-muted ms-1">(<?php echo esc_html( $count ); ?>)</span>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

<?php else : // checkbox — default ?>

	<?php foreach ( $terms_data as $item ) :
		$term      = $item['term'];
		$is_active = $item['is_active'];
		$count     = $item['count'];
		?>
		<div class="mb-1">
			<a href="<?php echo esc_url( $item['url'] ); ?>"
				class="cw-check-link pjax-link<?php echo $is_active ? ' active' : ''; ?>"
				aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
				<span class="cw-check-box" aria-hidden="true"></span>
				<span class="cw-check-label">
					<?php echo esc_html( $term->name ); ?>
					<?php if ( $show_count ) : ?>
						<span class="fs-sm text-muted ms-1">(<?php echo esc_html( $count ); ?>)</span>
					<?php endif; ?>
				</span>
			</a>
		</div>
	<?php endforeach; ?>

<?php endif; ?>
