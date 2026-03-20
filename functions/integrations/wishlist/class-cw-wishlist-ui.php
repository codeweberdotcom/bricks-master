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
				'removed'      => __( 'Убрано из избранного', 'codeweber' ),
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
		$this->render_button( 'cw-wishlist-btn--single btn btn-outline-red btn-icon-start' );
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
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-primary rounded-pill">
						<?php esc_html_e( 'Перейти в каталог', 'codeweber' ); ?>
					</a>
				</div>
			<?php else : ?>
				<div class="grid grid-view projects-masonry shop">
					<div class="row <?php echo esc_attr( class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'gx-md-8 gy-10 gy-md-13' ); ?> cw-wishlist-grid">
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
		$pid   = $product->get_id();
		$image = $product->get_image( 'woocommerce_thumbnail', array( 'class' => '' ) );
		$title = $product->get_name();
		$price = $product->get_price_html();
		$link  = get_permalink( $pid );

		$is_simple       = $product->is_type( 'simple' );
		$add_to_cart_url = $product->add_to_cart_url();
		$add_to_cart_text = $product->add_to_cart_text();

		// Badge (Sale / New)
		$badge = '';
		if ( $product->is_on_sale() ) {
			$badge = '<span class="avatar bg-pink text-white w-10 h-10 position-absolute text-uppercase fs-13" style="top:1rem;left:1rem;"><span>' . esc_html__( 'Sale!', 'woocommerce' ) . '</span></span>';
		} elseif ( $product->is_featured() ) {
			$badge = '<span class="avatar bg-aqua text-white w-10 h-10 position-absolute text-uppercase fs-13" style="top:1rem;left:1rem;"><span>' . esc_html__( 'New!', 'codeweber' ) . '</span></span>';
		}

		?>
		<div class="project item col-6 col-md-4 col-xl-3 cw-wishlist-card" data-product-id="<?php echo esc_attr( $pid ); ?>">
			<div class="card p-3">

			<figure class="rounded mb-4">
				<a href="<?php echo esc_url( $link ); ?>"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>

				<a class="item-like cw-wishlist-remove cw-wishlist-btn--active"
				   href="#"
				   data-product-id="<?php echo esc_attr( $pid ); ?>"
				   data-bs-toggle="white-tooltip"
				   title="<?php esc_attr_e( 'Убрать из избранного', 'codeweber' ); ?>"
				   aria-label="<?php esc_attr_e( 'Убрать из избранного', 'codeweber' ); ?>">
					<i class="uil uil-heart-alt" aria-hidden="true"></i>
				</a>

				<?php if ( $is_simple ) : ?>
					<a href="<?php echo esc_url( $add_to_cart_url ); ?>"
					   class="item-cart ajax_add_to_cart"
					   data-product_id="<?php echo esc_attr( $pid ); ?>"
					   data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
					   data-quantity="1"
					   rel="nofollow">
						<i class="uil uil-shopping-bag" aria-hidden="true"></i>
						<?php echo esc_html( $add_to_cart_text ); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( $link ); ?>" class="item-cart">
						<i class="uil uil-shopping-bag" aria-hidden="true"></i>
						<?php echo esc_html( $add_to_cart_text ); ?>
					</a>
				<?php endif; ?>

				<?php echo $badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</figure>

			<div class="post-header">
				<h2 class="post-title h3 fs-18">
					<a href="<?php echo esc_url( $link ); ?>" class="link-dark">
						<?php echo esc_html( $title ); ?>
					</a>
				</h2>
				<p class="price"><?php echo $price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			</div>

			</div><!-- /.card -->
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
			return '<i class="uil uil-heart-alt" aria-hidden="true"></i>';
		}

		return '<i class="uil uil-heart" aria-hidden="true"></i>';
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
