<?php
/**
 * Filter Panel wrapper — called by cw_render_filter_block().
 *
 * Available variables (from $atts):
 *   $atts['show_price']      bool
 *   $atts['show_categories'] bool
 *   $atts['attributes']      string[]  — array of 'pa_*' taxonomy slugs
 *   $atts['show_rating']     bool
 *   $atts['show_stock']      bool
 *   $atts['display_mode']    string    — 'checkbox' | 'list' | 'button'
 *   $atts['show_count']      bool
 *   $atts['title']           string
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filters_dir  = get_template_directory() . '/templates/woocommerce/filters/';
$display_mode = $atts['display_mode'] ?? 'checkbox';
$show_count   = ! empty( $atts['show_count'] );
$has_active   = function_exists( 'cw_has_active_filters' ) && cw_has_active_filters();
?>

<div class="widget">

	<?php if ( ! empty( $atts['title'] ) ) : ?>
		<h4 class="widget-title mb-3"><?php echo esc_html( $atts['title'] ); ?></h4>
	<?php endif; ?>

	<?php if ( $has_active ) :
		$active = cw_get_active_filter_params();
		include $filters_dir . 'filter-active.php';
	endif; ?>

	<?php // ── Price ───────────────────────────────────────────────────────── ?>
	<?php if ( ! empty( $atts['show_price'] ) ) : ?>
		<div class="border-bottom">
			<button class="cw-collapse-toggle btn btn-link px-0 py-3 d-flex w-100 justify-content-between align-items-center text-body text-decoration-none fw-semibold small"
				type="button"
				data-bs-toggle="collapse"
				data-bs-target="#cw-filter-price"
				aria-expanded="true"
				aria-controls="cw-filter-price">
				<?php esc_html_e( 'Цена', 'codeweber' ); ?>
			</button>
			<div id="cw-filter-price" class="collapse show">
				<div class="pb-3">
					<?php include $filters_dir . 'filter-price.php'; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php // ── Categories ─────────────────────────────────────────────────── ?>
	<?php if ( ! empty( $atts['show_categories'] ) ) :
		$terms_data = cw_get_category_filter_terms( 0, $show_count );
		if ( ! empty( $terms_data ) ) : ?>
		<div class="border-bottom">
			<button class="cw-collapse-toggle btn btn-link px-0 py-3 d-flex w-100 justify-content-between align-items-center text-body text-decoration-none fw-semibold small"
				type="button"
				data-bs-toggle="collapse"
				data-bs-target="#cw-filter-categories"
				aria-expanded="true"
				aria-controls="cw-filter-categories">
				<?php esc_html_e( 'Категории', 'codeweber' ); ?>
			</button>
			<div id="cw-filter-categories" class="collapse show">
				<div class="pb-3">
					<?php include $filters_dir . 'filter-category.php'; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php // ── Attributes ─────────────────────────────────────────────────── ?>
	<?php if ( ! empty( $atts['attributes'] ) && is_array( $atts['attributes'] ) ) :
		foreach ( $atts['attributes'] as $taxonomy ) :
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}
			$terms_data = cw_get_attribute_filter_terms( $taxonomy, $show_count );
			if ( empty( $terms_data ) ) {
				continue;
			}
			$attr_obj      = wc_get_attribute( wc_attribute_taxonomy_id_by_name( $taxonomy ) );
			$section_label = $attr_obj ? $attr_obj->name : $taxonomy;
			$section_id    = 'cw-filter-attr-' . sanitize_html_class( $taxonomy );
			?>
			<div class="border-bottom">
				<button class="cw-collapse-toggle btn btn-link px-0 py-3 d-flex w-100 justify-content-between align-items-center text-body text-decoration-none fw-semibold small"
					type="button"
					data-bs-toggle="collapse"
					data-bs-target="#<?php echo esc_attr( $section_id ); ?>"
					aria-expanded="true"
					aria-controls="<?php echo esc_attr( $section_id ); ?>">
					<?php echo esc_html( $section_label ); ?>
				</button>
				<div id="<?php echo esc_attr( $section_id ); ?>" class="collapse show">
					<div class="pb-3">
						<?php include $filters_dir . 'filter-attribute.php'; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php // ── Rating ─────────────────────────────────────────────────────── ?>
	<?php if ( ! empty( $atts['show_rating'] ) ) :
		$options = cw_get_rating_filter_options();
		if ( ! empty( $options ) ) : ?>
		<div class="border-bottom">
			<button class="cw-collapse-toggle btn btn-link px-0 py-3 d-flex w-100 justify-content-between align-items-center text-body text-decoration-none fw-semibold small"
				type="button"
				data-bs-toggle="collapse"
				data-bs-target="#cw-filter-rating"
				aria-expanded="true"
				aria-controls="cw-filter-rating">
				<?php esc_html_e( 'Рейтинг', 'codeweber' ); ?>
			</button>
			<div id="cw-filter-rating" class="collapse show">
				<div class="pb-3">
					<?php include $filters_dir . 'filter-rating.php'; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php // ── Stock ──────────────────────────────────────────────────────── ?>
	<?php if ( ! empty( $atts['show_stock'] ) ) :
		$options = cw_get_stock_filter_options();
		if ( ! empty( $options ) ) : ?>
		<div class="border-bottom">
			<button class="cw-collapse-toggle btn btn-link px-0 py-3 d-flex w-100 justify-content-between align-items-center text-body text-decoration-none fw-semibold small"
				type="button"
				data-bs-toggle="collapse"
				data-bs-target="#cw-filter-stock"
				aria-expanded="true"
				aria-controls="cw-filter-stock">
				<?php esc_html_e( 'Наличие', 'codeweber' ); ?>
			</button>
			<div id="cw-filter-stock" class="collapse show">
				<div class="pb-3">
					<?php include $filters_dir . 'filter-stock.php'; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	<?php endif; ?>

</div><!-- .widget -->
