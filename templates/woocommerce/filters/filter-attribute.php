<?php
/**
 * Attribute / tag filter — Bootstrap form-check style (shop2.html).
 *
 * Expected variables:
 *   $terms_data      array  — from cw_get_attribute_filter_terms() or cw_get_tag_filter_terms()
 *   $display_mode    string — 'checkbox' | 'radio' | 'list' | 'button'
 *   $show_count      bool
 *   $radio_name      string — name attribute for radio inputs (default: taxonomy slug)
 *
 * Items with is_empty=true and is_active=false are rendered as disabled (no link, muted).
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $terms_data ) ) {
	return;
}

$display_mode        = $display_mode ?? 'checkbox';
$show_count          = $show_count ?? true;
$button_class        = $button_class ?? 'btn-outline-secondary';
$button_active_class = $button_active_class ?? 'btn-secondary';
$checkbox_size_class = $checkbox_size_class ?? '';
$checkbox_item_class = $checkbox_item_class ?? '';
$checkbox_columns    = $checkbox_columns ?? 1;
$radio_size_class    = $radio_size_class ?? '';
$radio_item_class    = $radio_item_class ?? '';
$radio_name          = $radio_name ?? 'cw_filter_radio';
?>

<?php if ( 'button' === $display_mode ) : ?>

	<div class="d-flex flex-wrap gap-1">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];
			?>
			<?php if ( $is_empty ) : ?>
				<span class="btn has-ripple <?php echo esc_attr( $button_class ); ?> disabled opacity-50"
					aria-disabled="true"
					<?php if ( $show_count ) : ?>title="(0)"<?php endif; ?>>
					<?php echo esc_html( $term->name ); ?>
				</span>
			<?php else : ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>"
					class="btn has-ripple pjax-link <?php echo $is_active ? esc_attr( $button_active_class ) : esc_attr( $button_class ); ?>"
					<?php if ( $show_count ) : ?>title="(<?php echo esc_attr( $count ); ?>)"<?php endif; ?>>
					<?php echo esc_html( $term->name ); ?>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

<?php elseif ( 'radio' === $display_mode ) : ?>

	<ul class="list-unstyled ps-0 mb-0">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];
			$uid       = 'cw-term-' . sanitize_html_class( $term->slug );
			?>
			<li>
				<div class="form-check mb-1 cw-filter-check<?php echo esc_attr( $radio_size_class ); ?><?php echo $radio_item_class ? ' ' . esc_attr( $radio_item_class ) : ''; ?><?php echo $is_empty ? ' opacity-50' : ''; ?>">
					<input class="form-check-input"
						type="radio"
						name="<?php echo esc_attr( $radio_name ); ?>"
						id="<?php echo esc_attr( $uid ); ?>"
						<?php checked( $is_active ); ?>
						<?php disabled( $is_empty ); ?>
						tabindex="-1"
						aria-hidden="true">
					<?php if ( $is_empty ) : ?>
						<span class="form-check-label text-muted pe-none"
							id="<?php echo esc_attr( $uid ); ?>">
							<?php echo esc_html( $term->name ); ?>
							<?php if ( $show_count ) : ?>
								<span class="fs-sm ms-1">(0)</span>
							<?php endif; ?>
						</span>
					<?php else : ?>
						<a href="<?php echo esc_url( $item['url'] ); ?>"
							class="form-check-label pjax-link"
							aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
							<?php echo esc_html( $term->name ); ?>
							<?php if ( $show_count ) : ?>
								<span class="fs-sm text-muted ms-1">(<?php echo esc_html( $count ); ?>)</span>
							<?php endif; ?>
						</a>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>

<?php elseif ( 'list' === $display_mode ) : ?>

	<ul class="list-unstyled ps-0 mb-0">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];
			?>
			<li class="mb-1<?php echo $is_empty ? ' opacity-50' : ''; ?>">
				<?php if ( $is_empty ) : ?>
					<span class="link-body text-muted pe-none" style="text-decoration:none;">
						<?php echo esc_html( $term->name ); ?>
						<?php if ( $show_count ) : ?>
							<span class="fs-sm ms-1">(0)</span>
						<?php endif; ?>
					</span>
				<?php else : ?>
					<a href="<?php echo esc_url( $item['url'] ); ?>"
						class="link-body pjax-link<?php echo $is_active ? ' fw-semibold' : ''; ?>"
						style="text-decoration:none;">
						<?php echo esc_html( $term->name ); ?>
						<?php if ( $show_count ) : ?>
							<span class="fs-sm text-muted ms-1">(<?php echo esc_html( $count ); ?>)</span>
						<?php endif; ?>
					</a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

<?php else : // checkbox — default ?>

	<ul class="list-unstyled ps-0 mb-0<?php echo 2 === $checkbox_columns ? ' cc-2' : ''; ?>">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];
			$uid       = 'cw-term-' . sanitize_html_class( $term->slug );
			?>
			<li>
				<div class="form-check mb-1 cw-filter-check<?php echo esc_attr( $checkbox_size_class ); ?><?php echo $checkbox_item_class ? ' ' . esc_attr( $checkbox_item_class ) : ''; ?><?php echo $is_empty ? ' opacity-50' : ''; ?>">
					<input class="form-check-input"
						type="checkbox"
						id="<?php echo esc_attr( $uid ); ?>"
						<?php checked( $is_active ); ?>
						<?php disabled( $is_empty ); ?>
						tabindex="-1"
						aria-hidden="true">
					<?php if ( $is_empty ) : ?>
						<span class="form-check-label text-muted pe-none">
							<?php echo esc_html( $term->name ); ?>
							<?php if ( $show_count ) : ?>
								<span class="fs-sm ms-1">(0)</span>
							<?php endif; ?>
						</span>
					<?php else : ?>
						<a href="<?php echo esc_url( $item['url'] ); ?>"
							class="form-check-label pjax-link"
							aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
							<?php echo esc_html( $term->name ); ?>
							<?php if ( $show_count ) : ?>
								<span class="fs-sm text-muted ms-1">(<?php echo esc_html( $count ); ?>)</span>
							<?php endif; ?>
						</a>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>

<?php endif; ?>
