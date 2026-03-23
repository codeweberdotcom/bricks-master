<?php
/**
 * Compare UI — кнопки на карточках/single, нижний бар, шорткод, enqueue.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CW_Compare_UI
 */
class CW_Compare_UI {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Кнопка на странице товара
		if ( $this->get_opt( 'compare_btn_single', true ) ) {
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'render_single_button' ), 25 );
		}

		// Нижний бар
		add_action( 'wp_footer', array( $this, 'render_bar_container' ) );

		// Шорткод
		add_shortcode( 'cw_compare', array( $this, 'render_shortcode' ) );

		// На странице сравнения WooCommerce должен грузить свои скрипты
		add_filter( 'is_woocommerce', array( $this, 'is_woocommerce_on_compare' ) );

		// Enqueue
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 35 );
	}

	/**
	 * Enqueue compare JS and localize vars.
	 */
	public function enqueue_scripts() {
		if ( ! is_woocommerce() && ! is_shop() && ! is_product_category() && ! is_product_tag()
			&& ! cw_is_compare_page()
			&& ! ( function_exists( 'cw_is_wishlist_page' ) && cw_is_wishlist_page() )
		) {
			return;
		}

		$js_path = codeweber_get_dist_file_path( 'dist/assets/js/woo-compare.js' );
		$js_url  = codeweber_get_dist_file_url( 'dist/assets/js/woo-compare.js' );

		if ( ! $js_path || ! $js_url ) {
			return;
		}

		wp_enqueue_script(
			'cw-compare',
			$js_url,
			array(),
			codeweber_asset_version( $js_path ),
			true
		);

		wp_localize_script( 'cw-compare', 'cwCompare', array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'cw_compare_nonce' ),
			'compareUrl' => cw_get_compare_url(),
			'limit'      => cw_get_compare_limit(),
			'ids'        => CW_Compare_Storage::get_ids(),
			'i18n'       => array(
				'limitReached' => __( 'Достигнут лимит товаров для сравнения', 'codeweber' ),
				'add'          => __( 'Добавить к сравнению', 'codeweber' ),
				'added'        => __( 'В сравнении', 'codeweber' ),
				'removed'      => __( 'Удалён из сравнения', 'codeweber' ),
				'compare'      => __( 'Сравнить', 'codeweber' ),
				'clear'        => __( 'Очистить', 'codeweber' ),
				'emptySlot'    => __( 'Добавьте товар', 'codeweber' ),
			),
		) );
	}

	/**
	 * Render compare button on single product page.
	 * Called via hook woocommerce_after_add_to_cart_button.
	 */
	public function render_single_button() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$product_id = $product->get_id();
		$in_compare = CW_Compare_Storage::has( $product_id );
		$label      = $in_compare ? __( 'В сравнении', 'codeweber' ) : __( 'Добавить к сравнению', 'codeweber' );
		$btn_style  = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : '';
		$active     = $in_compare ? ' cw-compare-btn--active' : '';

		printf(
			'<a href="%1$s" class="cw-compare-btn cw-compare-btn--single btn btn-outline-secondary btn-icon has-ripple px-3 h-100%2$s%3$s"
				data-product-id="%4$s"
				aria-label="%5$s"
				title="%5$s">
				<i class="uil uil-exchange" aria-hidden="true"></i>
			</a>',
			esc_url( cw_get_compare_url() ),
			esc_attr( $btn_style ? ' ' . $btn_style : '' ),
			esc_attr( $active ),
			esc_attr( $product_id ),
			esc_html( $label )
		);
	}

	/**
	 * Render compare button for product loop card.
	 * Called directly from shop2.php template.
	 *
	 * @param int $product_id Product ID.
	 */
	public static function render_loop_button( $product_id ) {
		$in_compare = CW_Compare_Storage::has( $product_id );
		$label      = $in_compare ? __( 'В сравнении', 'codeweber' ) : __( 'Добавить к сравнению', 'codeweber' );
		$active     = $in_compare ? ' cw-compare-btn--active' : '';

		printf(
			'<a href="%1$s" class="item-compare cw-compare-btn%2$s"
				data-product-id="%3$s"
				data-bs-toggle="white-tooltip"
				title="%4$s"
				aria-label="%4$s">
				<i class="uil uil-exchange" aria-hidden="true"></i>
			</a>',
			esc_url( cw_get_compare_url() ),
			esc_attr( $active ),
			esc_attr( $product_id ),
			esc_html( $label )
		);
	}

	/**
	 * Render bar container in wp_footer.
	 * The wrapper stays static; only inner content is replaced via AJAX.
	 */
	public function render_bar_container() {
		if ( ! is_woocommerce() && ! is_shop() && ! is_product_category() && ! is_product_tag()
			&& ! cw_is_compare_page()
			&& ! ( function_exists( 'cw_is_wishlist_page' ) && cw_is_wishlist_page() )
		) {
			return;
		}

		$ids   = CW_Compare_Storage::get_ids();
		$count = count( $ids );
		$limit = cw_get_compare_limit();
		?>
		<div id="cw-compare-bar"
			class="cw-compare-bar bg-white border-top shadow-lg py-3 px-4<?php echo $count > 0 ? ' is-visible' : ''; ?>"
			style="z-index:1040;<?php echo $count > 0 ? '' : 'display:none;'; ?>">
			<?php $this->render_bar_inner( $ids, $limit ); ?>
		</div>
		<?php
	}

	/**
	 * Render bar inner HTML (returned in AJAX response too).
	 *
	 * @param int[] $ids   Current IDs.
	 * @param int   $limit Max items.
	 */
	public function render_bar_inner( $ids, $limit ) {
		get_template_part( 'woocommerce/content-compare', 'bar', array(
			'compare_ids' => $ids,
			'limit'       => $limit,
		) );
	}

	/**
	 * Render compare shortcode [cw_compare].
	 *
	 * @return string
	 */
	public function render_shortcode() {
		$ids       = CW_Compare_Storage::get_ids();
		$btn_style = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : '';

		if ( empty( $ids ) ) {
			ob_start();
			?>
			<div class="cw-compare-empty text-center py-16">
				<i class="uil uil-exchange fs-60 text-ash mb-4 d-block" aria-hidden="true"></i>
				<p class="mb-4"><?php esc_html_e( 'Добавьте товары для сравнения', 'codeweber' ); ?></p>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-primary has-ripple <?php echo esc_attr( $btn_style ); ?>">
					<?php esc_html_e( 'В каталог', 'codeweber' ); ?>
				</a>
			</div>
			<?php
			return ob_get_clean();
		}

		if ( count( $ids ) < 2 ) {
			ob_start();
			?>
			<div class="cw-compare-empty text-center py-16">
				<i class="uil uil-exchange fs-60 text-ash mb-4 d-block" aria-hidden="true"></i>
				<p class="mb-4"><?php esc_html_e( 'Добавьте ещё хотя бы один товар для сравнения', 'codeweber' ); ?></p>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-primary has-ripple <?php echo esc_attr( $btn_style ); ?>">
					<?php esc_html_e( 'В каталог', 'codeweber' ); ?>
				</a>
			</div>
			<?php
			return ob_get_clean();
		}

		$table       = new CW_Compare_Table( $ids );
		$btn_style   = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : '';

		ob_start();
		?>
		<div class="cw-compare-page">

			<!-- Переключатель "только различия" -->
			<?php if ( count( $table->get_products() ) >= 2 ) : ?>
			<div class="d-flex align-items-center justify-content-between mb-6 flex-wrap gap-3">
				<div class="form-check form-switch mb-0">
					<input class="form-check-input" type="checkbox" id="cw-compare-diff-only" role="switch">
					<label class="form-check-label" for="cw-compare-diff-only">
						<?php esc_html_e( 'Показать только различия', 'codeweber' ); ?>
					</label>
				</div>
				<button class="cw-compare-clear btn btn-sm btn-outline-danger <?php echo esc_attr( $btn_style ); ?>">
					<i class="uil uil-trash-alt me-1" aria-hidden="true"></i>
					<?php esc_html_e( 'Очистить всё', 'codeweber' ); ?>
				</button>
			</div>
			<?php endif; ?>

			<?php echo $table->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Tell WooCommerce this is a WC page on compare page (loads WC scripts/styles).
	 *
	 * @param bool $is_wc Current value.
	 * @return bool
	 */
	public function is_woocommerce_on_compare( $is_wc ) {
		if ( $is_wc ) {
			return true;
		}
		return cw_is_compare_page();
	}

	/**
	 * Get Redux option.
	 *
	 * @param string $key     Option key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	private function get_opt( $key, $default = '' ) {
		if ( ! class_exists( 'Redux' ) ) {
			return $default;
		}
		global $opt_name;
		return Redux::get_option( $opt_name, $key, $default );
	}
}
