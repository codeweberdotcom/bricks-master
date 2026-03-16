<?php
/**
 * Demo данные для WooCommerce Products
 *
 * Создание/удаление demo товаров с категориями, тегами и изображениями.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Данные demo товаров.
 *
 * @return array
 */
function cw_demo_get_products_data() {
	$locale     = get_locale();
	$is_russian = ( strpos( $locale, 'ru' ) === 0 );

	if ( $is_russian ) {
		return array(
			'categories' => array(
				'Одежда',
				'Обувь',
				'Аксессуары',
				'Косметика',
				'Дом и сад',
			),
			'tags'       => array(
				'новинка',
				'популярное',
				'распродажа',
				'хит',
				'премиум',
				'спорт',
				'подарок',
				'лимитед',
				'тренд',
			),
			'items'      => array(
				array(
					'title'         => 'Летнее платье',
					'desc'          => 'Лёгкое летнее платье из натуральных материалов. Идеально для тёплых дней.',
					'image'         => 'sh1.jpg',
					'category'      => 'Одежда',
					'tags'          => array( 'новинка', 'тренд' ),
					'regular_price' => '5990',
					'sale_price'    => '3990',
					'sku'           => 'DEMO-DRESS-001',
				),
				array(
					'title'         => 'Кожаная сумка',
					'desc'          => 'Стильная кожаная сумка ручной работы. Вместительная и практичная.',
					'image'         => 'sh2.jpg',
					'category'      => 'Аксессуары',
					'tags'          => array( 'премиум', 'хит' ),
					'regular_price' => '9900',
					'sale_price'    => '',
					'sku'           => 'DEMO-BAG-001',
					'featured'      => true,
				),
				array(
					'title'         => 'Кроссовки беговые',
					'desc'          => 'Лёгкие беговые кроссовки с амортизирующей подошвой.',
					'image'         => 'sh3.jpg',
					'category'      => 'Обувь',
					'tags'          => array( 'спорт', 'популярное' ),
					'regular_price' => '7500',
					'sale_price'    => '5900',
					'sku'           => 'DEMO-SHOE-001',
				),
				array(
					'title'         => 'Солнцезащитные очки',
					'desc'          => 'Классические солнцезащитные очки с UV400 защитой.',
					'image'         => 'sh4.jpg',
					'category'      => 'Аксессуары',
					'tags'          => array( 'тренд', 'лимитед' ),
					'regular_price' => '4500',
					'sale_price'    => '',
					'sku'           => 'DEMO-GLASS-001',
				),
				array(
					'title'         => 'Парфюм',
					'desc'          => 'Изысканный аромат с нотами бергамота и сандалового дерева.',
					'image'         => 'sh5.jpg',
					'category'      => 'Косметика',
					'tags'          => array( 'премиум', 'подарок' ),
					'regular_price' => '3990',
					'sale_price'    => '',
					'sku'           => 'DEMO-PERF-001',
				),
				array(
					'title'         => 'Наручные часы',
					'desc'          => 'Элегантные часы с кожаным ремешком и сапфировым стеклом.',
					'image'         => 'sh6.jpg',
					'category'      => 'Аксессуары',
					'tags'          => array( 'премиум', 'хит', 'подарок' ),
					'regular_price' => '15900',
					'sale_price'    => '12900',
					'sku'           => 'DEMO-WATCH-001',
					'featured'      => true,
				),
				array(
					'title'         => 'Куртка демисезонная',
					'desc'          => 'Стильная куртка для весны и осени. Водоотталкивающая ткань.',
					'image'         => 'sh7.jpg',
					'category'      => 'Одежда',
					'tags'          => array( 'новинка', 'популярное' ),
					'regular_price' => '12500',
					'sale_price'    => '',
					'sku'           => 'DEMO-JACK-001',
				),
				array(
					'title'         => 'Кеды повседневные',
					'desc'          => 'Удобные кеды для каждодневной носки. Доступны в нескольких цветах.',
					'image'         => 'sh8.jpg',
					'category'      => 'Обувь',
					'tags'          => array( 'популярное', 'распродажа' ),
					'regular_price' => '4900',
					'sale_price'    => '3500',
					'sku'           => 'DEMO-SNEAK-001',
				),
				array(
					'title'         => 'Набор косметики',
					'desc'          => 'Подарочный набор уходовой косметики: крем, сыворотка, тоник.',
					'image'         => 'sh9.jpg',
					'category'      => 'Косметика',
					'tags'          => array( 'подарок', 'хит', 'новинка' ),
					'regular_price' => '6900',
					'sale_price'    => '',
					'sku'           => 'DEMO-COSM-001',
					'featured'      => true,
				),
			),
		);
	}

	return array(
		'categories' => array(
			'Clothing',
			'Footwear',
			'Accessories',
			'Cosmetics',
			'Home & Garden',
		),
		'tags'       => array(
			'new-arrival',
			'popular',
			'sale',
			'bestseller',
			'premium',
			'sport',
			'gift-idea',
			'limited',
			'trending',
		),
		'items'      => array(
			array(
				'title'         => 'Summer Dress',
				'desc'          => 'Light summer dress made from natural materials. Perfect for warm days.',
				'image'         => 'sh1.jpg',
				'category'      => 'Clothing',
				'tags'          => array( 'new-arrival', 'trending' ),
				'regular_price' => '89.99',
				'sale_price'    => '59.99',
				'sku'           => 'DEMO-DRESS-001',
			),
			array(
				'title'         => 'Leather Bag',
				'desc'          => 'Stylish handcrafted leather bag. Spacious and practical.',
				'image'         => 'sh2.jpg',
				'category'      => 'Accessories',
				'tags'          => array( 'premium', 'bestseller' ),
				'regular_price' => '149.99',
				'sale_price'    => '',
				'sku'           => 'DEMO-BAG-001',
				'featured'      => true,
			),
			array(
				'title'         => 'Running Shoes',
				'desc'          => 'Lightweight running shoes with cushioned sole.',
				'image'         => 'sh3.jpg',
				'category'      => 'Footwear',
				'tags'          => array( 'sport', 'popular' ),
				'regular_price' => '120.00',
				'sale_price'    => '89.00',
				'sku'           => 'DEMO-SHOE-001',
			),
			array(
				'title'         => 'Sunglasses',
				'desc'          => 'Classic sunglasses with UV400 protection.',
				'image'         => 'sh4.jpg',
				'category'      => 'Accessories',
				'tags'          => array( 'trending', 'limited' ),
				'regular_price' => '79.00',
				'sale_price'    => '',
				'sku'           => 'DEMO-GLASS-001',
			),
			array(
				'title'         => 'Perfume',
				'desc'          => 'Exquisite fragrance with notes of bergamot and sandalwood.',
				'image'         => 'sh5.jpg',
				'category'      => 'Cosmetics',
				'tags'          => array( 'premium', 'gift-idea' ),
				'regular_price' => '65.00',
				'sale_price'    => '',
				'sku'           => 'DEMO-PERF-001',
			),
			array(
				'title'         => 'Wrist Watch',
				'desc'          => 'Elegant watch with leather strap and sapphire glass.',
				'image'         => 'sh6.jpg',
				'category'      => 'Accessories',
				'tags'          => array( 'premium', 'bestseller', 'gift-idea' ),
				'regular_price' => '249.00',
				'sale_price'    => '199.00',
				'sku'           => 'DEMO-WATCH-001',
				'featured'      => true,
			),
			array(
				'title'         => 'Autumn Jacket',
				'desc'          => 'Stylish jacket for spring and autumn. Water-repellent fabric.',
				'image'         => 'sh7.jpg',
				'category'      => 'Clothing',
				'tags'          => array( 'new-arrival', 'popular' ),
				'regular_price' => '199.00',
				'sale_price'    => '',
				'sku'           => 'DEMO-JACK-001',
			),
			array(
				'title'         => 'Casual Sneakers',
				'desc'          => 'Comfortable everyday sneakers. Available in several colors.',
				'image'         => 'sh8.jpg',
				'category'      => 'Footwear',
				'tags'          => array( 'popular', 'sale' ),
				'regular_price' => '79.00',
				'sale_price'    => '59.00',
				'sku'           => 'DEMO-SNEAK-001',
			),
			array(
				'title'         => 'Cosmetic Set',
				'desc'          => 'Gift set of skincare cosmetics: cream, serum, toner.',
				'image'         => 'sh9.jpg',
				'category'      => 'Cosmetics',
				'tags'          => array( 'gift-idea', 'bestseller', 'new-arrival' ),
				'regular_price' => '99.00',
				'sale_price'    => '',
				'sku'           => 'DEMO-COSM-001',
				'featured'      => true,
			),
		),
	);
}

/**
 * Импортировать изображение товара в медиабиблиотеку.
 *
 * @param string $image_filename Имя файла из src/assets/img/photos/.
 * @param int    $post_id        ID записи товара.
 * @return int|false ID attachment или false при ошибке.
 */
function cw_demo_import_product_image( $image_filename, $post_id ) {
	$source_path = get_template_directory() . '/src/assets/img/photos/' . $image_filename;

	if ( ! file_exists( $source_path ) ) {
		error_log( 'Demo Products: Image not found — ' . $image_filename );
		return false;
	}

	$file_type = wp_check_filetype( basename( $source_path ), null );
	if ( ! $file_type['type'] ) {
		return false;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload_dir = wp_upload_dir();
	$dest_path  = $upload_dir['path'] . '/' . basename( $source_path );

	if ( ! copy( $source_path, $dest_path ) ) {
		error_log( 'Demo Products: Could not copy file — ' . $image_filename );
		return false;
	}

	$file_array = array(
		'name'     => basename( $source_path ),
		'tmp_name' => $dest_path,
	);

	$attachment_id = media_handle_sideload( $file_array, $post_id );

	if ( file_exists( $dest_path ) ) {
		@unlink( $dest_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	if ( is_wp_error( $attachment_id ) ) {
		error_log( 'Demo Products: Upload error — ' . $attachment_id->get_error_message() );
		return false;
	}

	wp_update_post( array( 'ID' => $attachment_id, 'post_parent' => $post_id ) );
	set_post_thumbnail( $post_id, $attachment_id );

	$meta = wp_generate_attachment_metadata( $attachment_id, get_attached_file( $attachment_id ) );
	wp_update_attachment_metadata( $attachment_id, $meta );

	return $attachment_id;
}

/**
 * Получить или создать термин таксономии.
 *
 * @param string $name     Название.
 * @param string $taxonomy Таксономия.
 * @return int|false
 */
function cw_demo_get_or_create_product_term( $name, $taxonomy ) {
	$term = get_term_by( 'name', $name, $taxonomy );
	if ( $term ) {
		return $term->term_id;
	}

	$result = wp_insert_term( $name, $taxonomy, array( 'slug' => sanitize_title( $name ) ) );
	if ( is_wp_error( $result ) ) {
		return false;
	}

	return $result['term_id'];
}

/**
 * Создать один demo товар.
 *
 * @param array $item Данные товара.
 * @return int|false ID поста или false.
 */
function cw_demo_create_product_post( $item ) {
	if ( empty( $item['title'] ) ) {
		return false;
	}

	$post_id = wp_insert_post( array(
		'post_title'   => sanitize_text_field( $item['title'] ),
		'post_content' => wp_kses_post( $item['desc'] ?? '' ),
		'post_status'  => 'publish',
		'post_type'    => 'product',
		'post_author'  => get_current_user_id(),
	) );

	if ( is_wp_error( $post_id ) ) {
		error_log( 'Demo Products: wp_insert_post error — ' . $post_id->get_error_message() );
		return false;
	}

	// Маркируем как demo
	update_post_meta( $post_id, '_demo_created', true );

	// Тип товара — simple
	wp_set_object_terms( $post_id, 'simple', 'product_type' );

	// Цены
	$regular_price = isset( $item['regular_price'] ) ? sanitize_text_field( $item['regular_price'] ) : '';
	$sale_price    = isset( $item['sale_price'] ) ? sanitize_text_field( $item['sale_price'] ) : '';

	update_post_meta( $post_id, '_regular_price', $regular_price );
	update_post_meta( $post_id, '_price', $sale_price !== '' ? $sale_price : $regular_price );

	if ( $sale_price !== '' ) {
		update_post_meta( $post_id, '_sale_price', $sale_price );
		update_post_meta( $post_id, '_on_sale', 'yes' );
	}

	// SKU
	if ( ! empty( $item['sku'] ) ) {
		update_post_meta( $post_id, '_sku', sanitize_text_field( $item['sku'] ) );
	}

	// Видимость
	update_post_meta( $post_id, '_visibility', 'visible' );
	update_post_meta( $post_id, '_stock_status', 'instock' );

	// Featured
	if ( ! empty( $item['featured'] ) ) {
		update_post_meta( $post_id, '_featured', 'yes' );
	}

	// Категория
	if ( ! empty( $item['category'] ) ) {
		$cat_id = cw_demo_get_or_create_product_term( $item['category'], 'product_cat' );
		if ( $cat_id ) {
			wp_set_post_terms( $post_id, array( $cat_id ), 'product_cat' );
		}
	}

	// Теги
	if ( ! empty( $item['tags'] ) && is_array( $item['tags'] ) ) {
		$tag_ids = array();
		foreach ( $item['tags'] as $tag_name ) {
			$tag_id = cw_demo_get_or_create_product_term( sanitize_text_field( $tag_name ), 'product_tag' );
			if ( $tag_id ) {
				$tag_ids[] = $tag_id;
			}
		}
		if ( $tag_ids ) {
			wp_set_post_terms( $post_id, $tag_ids, 'product_tag' );
		}
	}

	// Изображение
	if ( ! empty( $item['image'] ) ) {
		cw_demo_import_product_image( $item['image'], $post_id );
	}

	// Сбросить кэш WooCommerce для товара
	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $post_id );
	}

	return $post_id;
}

/**
 * Создать все demo товары.
 *
 * @return array
 */
function cw_demo_create_products() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array(
			'success' => false,
			'message' => __( 'WooCommerce is not active.', 'codeweber' ),
			'created' => 0,
			'total'   => 0,
			'errors'  => array(),
		);
	}

	$data = cw_demo_get_products_data();

	if ( empty( $data['items'] ) ) {
		return array(
			'success' => false,
			'message' => __( 'No product data found.', 'codeweber' ),
			'created' => 0,
			'total'   => 0,
			'errors'  => array(),
		);
	}

	$created = 0;
	$errors  = array();

	foreach ( $data['items'] as $item ) {
		$post_id = cw_demo_create_product_post( $item );
		if ( $post_id ) {
			$created++;
		} else {
			$errors[] = sprintf( __( 'Failed to create: %s', 'codeweber' ), $item['title'] ?? __( 'unknown', 'codeweber' ) );
		}
	}

	// Сбросить кэш WooCommerce
	if ( function_exists( 'wc_delete_shop_order_transients' ) ) {
		delete_transient( 'wc_products_onsale' );
	}

	return array(
		'success' => true,
		'message' => sprintf( __( '%1$d of %2$d products created.', 'codeweber' ), $created, count( $data['items'] ) ),
		'created' => $created,
		'total'   => count( $data['items'] ),
		'errors'  => $errors,
	);
}

/**
 * Удалить все demo товары.
 *
 * @return array
 */
function cw_demo_delete_products() {
	$posts = get_posts( array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => '_demo_created',
				'value' => true,
			),
		),
		'fields'         => 'ids',
	) );

	$deleted = 0;
	$errors  = array();

	foreach ( $posts as $post_id ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $thumbnail_id ) {
			wp_delete_attachment( $thumbnail_id, true );
		}

		$result = wp_delete_post( $post_id, true );
		if ( $result ) {
			$deleted++;
		} else {
			$errors[] = sprintf( __( 'Failed to delete record ID: %d', 'codeweber' ), $post_id );
		}
	}

	// Чистим пустые demo-термины (категории без товаров)
	foreach ( array( 'product_cat', 'product_tag' ) as $taxonomy ) {
		$terms = get_terms( array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'all',
		) );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( 0 === $term->count ) {
					wp_delete_term( $term->term_id, $taxonomy );
				}
			}
		}
	}

	if ( function_exists( 'wc_delete_shop_order_transients' ) ) {
		delete_transient( 'wc_products_onsale' );
	}

	return array(
		'success' => true,
		'message' => sprintf( __( 'Deleted %d products.', 'codeweber' ), $deleted ),
		'deleted' => $deleted,
		'errors'  => $errors,
	);
}
