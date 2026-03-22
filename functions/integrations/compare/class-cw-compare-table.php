<?php
/**
 * Compare Table — строит HTML-таблицу сравнения для шорткода [cw_compare].
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CW_Compare_Table
 */
class CW_Compare_Table {

	/**
	 * WC_Product[] — могут быть WC_Product_Simple, WC_Product_Variable, WC_Product_Variation.
	 *
	 * @var WC_Product[]
	 */
	private $products = array();

	/**
	 * Redux options cache.
	 *
	 * @var array
	 */
	private $opts = array();

	/**
	 * Constructor.
	 *
	 * @param int[] $ids Product or variation IDs.
	 */
	public function __construct( array $ids ) {
		$this->opts = get_option( 'redux_demo', array() );

		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );

			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}

			// Для вариации проверяем, что родитель опубликован
			if ( $product->is_type( 'variation' ) ) {
				$parent = wc_get_product( $product->get_parent_id() );
				if ( ! $parent || 'publish' !== $parent->get_status() ) {
					continue;
				}
			}

			$this->products[] = $product;
		}
	}

	/**
	 * Get loaded products.
	 *
	 * @return WC_Product[]
	 */
	public function get_products() {
		return $this->products;
	}

	/**
	 * Check if there are enough products to compare.
	 *
	 * @return bool
	 */
	public function has_products() {
		return count( $this->products ) >= 1;
	}

	/**
	 * Render the comparison table HTML.
	 *
	 * @return string
	 */
	public function render() {
		if ( empty( $this->products ) ) {
			return '';
		}

		$attrs = $this->collect_attributes();

		ob_start();
		include get_template_directory() . '/woocommerce/content-compare-table.php';
		return ob_get_clean();
	}

	/**
	 * Collect all attribute labels from all products (union set).
	 *
	 * @return array Array of [ 'label' => string, 'key' => string ]
	 */
	public function collect_attributes() {
		$all = array();

		foreach ( $this->products as $product ) {
			foreach ( $this->get_product_attributes( $product ) as $key => $attr ) {
				if ( ! isset( $all[ $key ] ) ) {
					$all[ $key ] = $attr['label'];
				}
			}
		}

		return $all;
	}

	/**
	 * Get attribute label => value pairs for a product.
	 *
	 * @param WC_Product $product Product.
	 * @return array Keyed by attribute taxonomy/name, value = ['label'=>string, 'value'=>string]
	 */
	public function get_product_attributes( WC_Product $product ) {
		$result = array();

		$attributes = $product->get_attributes();

		foreach ( $attributes as $key => $attribute ) {
			if ( $attribute instanceof WC_Product_Attribute ) {
				// Обычный атрибут товара
				if ( ! $attribute->get_visible() ) {
					continue;
				}
				$label  = wc_attribute_label( $attribute->get_name() );
				$values = array();
				if ( $attribute->is_taxonomy() ) {
					$terms = $attribute->get_terms();
					if ( $terms ) {
						foreach ( $terms as $term ) {
							$values[] = $term->name;
						}
					}
				} else {
					$values = $attribute->get_options();
				}
				$result[ $key ] = array(
					'label' => $label,
					'value' => implode( ', ', array_filter( $values ) ),
				);
			} elseif ( is_string( $attribute ) ) {
				// Атрибут вариации (строковое значение выбранного варианта)
				$label          = wc_attribute_label( $key );
				$result[ $key ] = array(
					'label' => $label,
					'value' => $attribute,
				);
			}
		}

		return $result;
	}

	/**
	 * Get attribute value for a specific product by attribute key.
	 *
	 * @param WC_Product $product Product.
	 * @param string     $key     Attribute taxonomy key.
	 * @return string
	 */
	public function get_attribute_value( WC_Product $product, $key ) {
		$attrs = $this->get_product_attributes( $product );
		return isset( $attrs[ $key ]['value'] ) ? $attrs[ $key ]['value'] : '';
	}

	/**
	 * Check if all products have the same value for an attribute key.
	 *
	 * @param string $key Attribute key.
	 * @return bool
	 */
	public function all_same( $key ) {
		$values = array();
		foreach ( $this->products as $product ) {
			$values[] = $this->get_attribute_value( $product, $key );
		}
		return count( array_unique( $values ) ) === 1;
	}

	/**
	 * Get product image HTML (thumbnail size).
	 *
	 * @param WC_Product $product Product.
	 * @param int        $size    Image size.
	 * @return string
	 */
	public function get_image( WC_Product $product, $size = 100 ) {
		$img_id = $product->get_image_id();

		if ( ! $img_id ) {
			return wc_placeholder_img( array( $size, $size ) );
		}

		return wp_get_attachment_image(
			$img_id,
			array( $size, $size ),
			false,
			array( 'class' => 'cw-compare-product-img' )
		);
	}

	/**
	 * Get product URL (parent for variations).
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public function get_url( WC_Product $product ) {
		if ( $product->is_type( 'variation' ) ) {
			return get_permalink( $product->get_parent_id() );
		}
		return get_permalink( $product->get_id() );
	}

	/**
	 * Get product name (parent name + variation attributes for variations).
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public function get_name( WC_Product $product ) {
		if ( $product->is_type( 'variation' ) ) {
			$parent = wc_get_product( $product->get_parent_id() );
			$parent_name = $parent ? $parent->get_name() : '';
			$variation_attrs = array();
			foreach ( $product->get_variation_attributes() as $key => $value ) {
				if ( $value ) {
					$term = get_term_by( 'slug', $value, str_replace( 'attribute_', '', $key ) );
					$variation_attrs[] = $term ? $term->name : $value;
				}
			}
			if ( $variation_attrs ) {
				return $parent_name . ' — ' . implode( ', ', $variation_attrs );
			}
			return $parent_name;
		}
		return $product->get_name();
	}

	/**
	 * Check if `show_sku` is enabled in Redux.
	 *
	 * @return bool
	 */
	public function show_sku() {
		return ! isset( $this->opts['compare_show_sku'] ) || (bool) $this->opts['compare_show_sku'];
	}

	/**
	 * Check if `show_rating` is enabled in Redux.
	 *
	 * @return bool
	 */
	public function show_rating() {
		return ! isset( $this->opts['compare_show_rating'] ) || (bool) $this->opts['compare_show_rating'];
	}

	/**
	 * Check if `show_stock` is enabled in Redux.
	 *
	 * @return bool
	 */
	public function show_stock() {
		return ! isset( $this->opts['compare_show_stock'] ) || (bool) $this->opts['compare_show_stock'];
	}
}
