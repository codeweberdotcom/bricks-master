<?php
/**
 * Wishlist UI — кнопки, страница вишлиста, меню аккаунта, шорткод.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CW_Wishlist_UI
 */
class CW_Wishlist_UI {

	/**
	 * Wishlist item instance.
	 *
	 * @var CW_Wishlist_Item
	 */
	private $wishlist;

	/**
	 * Constructor.
	 *
	 * @param CW_Wishlist_Item|null $wishlist Wishlist item instance.
	 */
	public function __construct( $wishlist = null ) {
		$this->wishlist = $wishlist;

		// Кнопка на карточке в каталоге рендерится прямо в шаблоне shop2.php,
		// поэтому хук через woocommerce_after_shop_loop_item не используем.

		// Кнопка на странице товара.
		if ( $this->get_opt( 'wishlist_btn_on_single', 1 ) ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'render_single_button' ), 35 );
		}

		// Ссылка «Избранное» в меню «Мой аккаунт».
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_account_menu_item' ), 15 );
		add_filter( 'woocommerce_get_endpoint_url', array( $this, 'account_menu_url' ), 15, 4 );
		add_filter( 'woocommerce_account_menu_item_classes', array( $this, 'account_menu_active_class' ), 15, 2 );

		// Шорткод страницы вишлиста.
		add_shortcode( 'cw_wishlist', array( $this, 'render_wishlist_page' ) );

		// Enqueue JS + локализация.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue wishlist JS and localize vars.
	 */
	public function enqueue_scripts() {
		$js_path = get_template_directory() . '/functions/integrations/wishlist/assets/wishlist.js';
		$js_url  = get_template_directory_uri() . '/functions/integrations/wishlist/assets/wishlist.js';

		if ( ! file_exists( $js_path ) ) {
			return;
		}

		wp_enqueue_script(
			'cw-wishlist',
			$js_url,
			array( 'jquery' ),
			filemtime( $js_path ),
			true
		);

		wp_localize_script( 'cw-wishlist', 'cwWishlist', array(
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'cw_wishlist_nonce' ),
			'wishlistUrl'    => cw_get_wishlist_url(),
			'isLoggedIn'     => is_user_logged_in() ? 'yes' : 'no',
			'guestsAllowed'  => $this->get_opt( 'wishlist_guests', 1 ) ? 'yes' : 'no',
			'loginUrl'       => wc_get_page_permalink( 'myaccount' ),
			'count'          => $this->wishlist ? $this->wishlist->get_count() : 0,
			'feedbackType'   => $this->get_opt( 'wishlist_feedback', 'spinner' ),
			'i18n'           => array(
				'added'        => __( 'В избранном', 'codeweber' ),
				'add'          => __( 'В избранное', 'codeweber' ),
				'loginNotice'  => __( 'Войдите, чтобы сохранить товар в избранное.', 'codeweber' ),
				'removeNotice' => __( 'Убрать из избранного?', 'codeweber' ),
			),
		) );
	}

	/**
	 * Render wishlist button on product loop card.
	 */
	public function render_loop_button() {
		$this->render_button( 'cw-wishlist-btn--loop btn btn-outline-secondary btn-sm' );
	}

	/**
	 * Render wishlist button on single product page.
	 */
	public function render_single_button() {
		$this->render_button( 'cw-wishlist-btn--single btn btn-outline-secondary' );
	}

	/**
	 * Render wishlist button HTML.
	 *
	 * @param string $extra_classes Additional CSS classes.
	 */
	public function render_button( $extra_classes = '' ) {
		$product_id  = get_the_ID();
		$in_wishlist = $this->wishlist ? $this->wishlist->is_in_wishlist( $product_id ) : false;
		$classes     = 'cw-wishlist-btn ' . esc_attr( $extra_classes );

		if ( $in_wishlist ) {
			$classes .= ' cw-wishlist-btn--active';
		}

		$label = $in_wishlist
			? __( 'В избранном', 'codeweber' )
			: __( 'В избранное', 'codeweber' );

		$href = $in_wishlist
			? esc_url( cw_get_wishlist_url() )
			: '#';

		?>
		<a
			href="<?php echo $href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
			class="<?php echo esc_attr( $classes ); ?>"
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
			aria-label="<?php echo esc_attr( $label ); ?>"
			title="<?php echo esc_attr( $label ); ?>"
		>
			<span class="cw-wishlist-icon">
				<?php echo $this->get_heart_icon( $in_wishlist ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
			<span class="cw-wishlist-label"><?php echo esc_html( $label ); ?></span>
		</a>
		<?php
	}

	/**
	 * Render the full wishlist page via shortcode [cw_wishlist].
	 *
	 * @return string
	 */
	public function render_wishlist_page() {
		ob_start();

		$products    = $this->wishlist ? $this->wishlist->get_all() : array();
		$product_ids = array_column( $products, 'product_id' );

		?>
		<div class="cw-wishlist-page">
			<?php if ( empty( $product_ids ) ) : ?>
				<div class="cw-wishlist-empty">
					<p><?php esc_html_e( 'В избранном пока ничего нет.', 'codeweber' ); ?></p>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-primary">
						<?php esc_html_e( 'Перейти в каталог', 'codeweber' ); ?>
					</a>
				</div>
			<?php else : ?>
				<div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4 cw-wishlist-grid" data-nonce="<?php echo esc_attr( wp_create_nonce( 'cw_wishlist_nonce' ) ); ?>">
					<?php
					foreach ( $product_ids as $pid ) {
						$pid     = (int) $pid;
						$product = wc_get_product( $pid );

						if ( ! $product || $product->get_status() !== 'publish' ) {
							continue;
						}

						$this->render_wishlist_card( $product );
					}
					?>
				</div>
			<?php endif; ?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render single product card on wishlist page.
	 *
	 * @param \WC_Product $product WooCommerce product.
	 */
	private function render_wishlist_card( $product ) {
		$pid         = $product->get_id();
		$image       = $product->get_image( 'woocommerce_thumbnail' );
		$title       = $product->get_name();
		$price       = $product->get_price_html();
		$link        = get_permalink( $pid );
		$add_to_cart = apply_filters( 'woocommerce_loop_add_to_cart_link',
			sprintf(
				'<a href="%s" data-quantity="1" class="btn btn-primary btn-sm add_to_cart_button ajax_add_to_cart" data-product_id="%d" rel="nofollow">%s</a>',
				esc_url( $product->add_to_cart_url() ),
				$pid,
				esc_html( $product->add_to_cart_text() )
			),
			$product,
			array()
		);

		?>
		<div class="col">
			<div class="card h-100 cw-wishlist-card" data-product-id="<?php echo esc_attr( $pid ); ?>">
				<a href="<?php echo esc_url( $link ); ?>" class="card-img-top cw-wishlist-card__img d-block overflow-hidden">
					<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<div class="card-body d-flex flex-column">
					<h5 class="card-title fs-6">
						<a href="<?php echo esc_url( $link ); ?>" class="text-decoration-none text-body">
							<?php echo esc_html( $title ); ?>
						</a>
					</h5>
					<div class="cw-wishlist-card__price mt-auto mb-2">
						<?php echo $price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="d-flex gap-2 align-items-center">
						<?php echo $add_to_cart; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<button
							type="button"
							class="btn btn-outline-danger btn-sm cw-wishlist-remove ms-auto"
							data-product-id="<?php echo esc_attr( $pid ); ?>"
							title="<?php esc_attr_e( 'Убрать из избранного', 'codeweber' ); ?>"
							aria-label="<?php esc_attr_e( 'Убрать из избранного', 'codeweber' ); ?>"
						>
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
								<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
								<path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
							</svg>
						</button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Add «Wishlist» item to My Account menu.
	 *
	 * @param array $items Menu items.
	 * @return array
	 */
	public function add_account_menu_item( $items ) {
		if ( ! $this->get_opt( 'wishlist_page' ) ) {
			return $items;
		}

		$logout = array();
		if ( isset( $items['customer-logout'] ) ) {
			$logout = array( 'customer-logout' => $items['customer-logout'] );
			unset( $items['customer-logout'] );
		}

		$items['cw-wishlist'] = __( 'Избранное', 'codeweber' );
		$items               += $logout;

		return $items;
	}

	/**
	 * Return wishlist page URL for account menu item.
	 *
	 * @param string $url      Current URL.
	 * @param string $endpoint Endpoint key.
	 * @return string
	 */
	public function account_menu_url( $url, $endpoint ) {
		if ( 'cw-wishlist' === $endpoint ) {
			return cw_get_wishlist_url();
		}

		return $url;
	}

	/**
	 * Mark wishlist item as active when on wishlist page.
	 *
	 * @param array  $classes  Item classes.
	 * @param string $endpoint Endpoint key.
	 * @return array
	 */
	public function account_menu_active_class( $classes, $endpoint ) {
		$wishlist_page = (int) $this->get_opt( 'wishlist_page' );

		if ( 'cw-wishlist' === $endpoint && $wishlist_page && get_queried_object_id() === $wishlist_page ) {
			$classes[] = 'is-active';
		}

		return $classes;
	}

	/**
	 * Get heart SVG icon.
	 *
	 * @param bool $filled Filled or outline.
	 * @return string
	 */
	private function get_heart_icon( $filled = false ) {
		if ( $filled ) {
			return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314"/></svg>';
		}

		return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"/></svg>';
	}

	/**
	 * Helper: get Redux option.
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
