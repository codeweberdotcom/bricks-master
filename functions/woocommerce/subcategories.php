<?php
/**
 * WooCommerce — Subcategories block.
 *
 * Выводит дочерние категории товаров (карточки / чипы) над сеткой товаров —
 * или вместо неё — на главной магазина и на страницах категорий.
 *
 * Настройки (приоритет сверху вниз):
 *   1. WooCommerce «Display type» категории (display_type) — если не Default,
 *      решает «показывать ли» и режим (both / subcategories / products)
 *   2. Мета-поля категории (cw_subcats_enable / cw_subcats_template /
 *      cw_subcats_layout / cw_subcats_cols_xs…xl)
 *   3. Redux: Woocommerce → Subcategories (ключи woo_subcats_*)
 *   4. Дефолты из cw_subcats_defaults()
 *
 * Карточки рендерятся через cw_render_term_card() и шаблоны
 * templates/post-cards/taxonomy/ — те же, что использует блок Post Grid.
 *
 * Документация: doc_claude/integrations/WC_SUBCATEGORIES.md
 *
 * @package CodeWeber
 */

defined( 'ABSPATH' ) || exit;

// ── Шаблоны и настройки ──────────────────────────────────────────────────────

/**
 * Шаблоны карточек, доступные для подкатегорий: slug => label.
 *
 * Берутся из реестра Post Cards (ключ `taxonomy`). Шаблоны `cells*` исключены —
 * они рассчитаны на сетку-«рамку» блока Post Grid и не подходят для категорий.
 *
 * @return array
 */
function cw_subcats_get_templates() {
	$templates = array();

	if ( function_exists( 'codeweber_get_post_card_templates_for' ) ) {
		foreach ( codeweber_get_post_card_templates_for( 'taxonomy' ) as $tpl ) {
			if ( in_array( $tpl['value'], array( 'cells', 'cells-1' ), true ) ) {
				continue;
			}
			$templates[ $tpl['value'] ] = $tpl['label'];
		}
	}

	$templates = apply_filters( 'cw_subcats_templates', $templates );

	return $templates ? $templates : array( 'card-image' => __( 'Card with Image', 'codeweber' ) );
}

/**
 * Шаблоны, которые выводятся «потоком» (flex-wrap), а не сеткой колонок.
 *
 * @param string $slug Slug шаблона.
 * @return bool
 */
function cw_subcats_is_flow_template( $slug ) {
	return in_array( $slug, apply_filters( 'cw_subcats_flow_templates', array( 'chips' ) ), true );
}

/**
 * Значения по умолчанию. Зеркалят default'ы полей Redux (woo_subcats_*).
 *
 * @return array
 */
function cw_subcats_defaults() {
	return array(
		'enable'      => false,
		'on_shop'     => true,
		'on_category' => true,
		'mode'        => 'above',
		'layout'      => 'grid',
		'template'    => 'card-image',
		'cols'        => array(
			'xs' => 2,
			'sm' => 2,
			'md' => 3,
			'lg' => 3,
			'xl' => 4,
		),
		'margin'      => 20,
		'nav'         => true,
		'dots'        => false,
		'autoplay'    => false,
		'show_count'  => true,
		'hide_empty'  => true,
		'exclude'     => array(),
		'image_size'  => 'medium_large',
		'lift'        => false,
	);
}

/**
 * Максимум колонок по брейкпоинтам (общий для Redux-полей, мета категории и валидации).
 *
 * @return array breakpoint => max
 */
function cw_subcats_cols_limits() {
	return array(
		'xs' => 2,
		'sm' => 3,
		'md' => 4,
		'lg' => 6,
		'xl' => 6,
	);
}

/**
 * Глобальные настройки из Redux (redux_demo → woo_subcats_*), с дефолтами.
 *
 * @return array
 */
function cw_subcats_global_settings() {
	$d   = cw_subcats_defaults();
	$raw = get_option( 'redux_demo', array() );
	$raw = is_array( $raw ) ? $raw : array();

	$str = function ( $key, $default ) use ( $raw ) {
		$k = 'woo_subcats_' . $key;
		return ( isset( $raw[ $k ] ) && '' !== $raw[ $k ] ) ? (string) $raw[ $k ] : $default;
	};
	$bool = function ( $key, $default ) use ( $raw ) {
		$k = 'woo_subcats_' . $key;
		return array_key_exists( $k, $raw ) ? ! empty( $raw[ $k ] ) : $default;
	};
	$int = function ( $key, $default, $min, $max ) use ( $raw ) {
		$k = 'woo_subcats_' . $key;
		$v = ( isset( $raw[ $k ] ) && '' !== $raw[ $k ] ) ? (int) $raw[ $k ] : $default;
		return max( $min, min( $max, $v ) );
	};

	$s = array(
		'enable'      => $bool( 'enable', $d['enable'] ),
		'on_shop'     => $bool( 'on_shop', $d['on_shop'] ),
		'on_category' => $bool( 'on_category', $d['on_category'] ),
		'mode'        => 'only' === $str( 'mode', $d['mode'] ) ? 'only' : 'above',
		'layout'      => 'slider' === $str( 'layout', $d['layout'] ) ? 'slider' : 'grid',
		'template'    => sanitize_key( $str( 'template', $d['template'] ) ),
		'cols'        => array(
			'xs' => $int( 'cols_xs', $d['cols']['xs'], 1, 2 ),
			'sm' => $int( 'cols_sm', $d['cols']['sm'], 1, 3 ),
			'md' => $int( 'cols_md', $d['cols']['md'], 1, 4 ),
			'lg' => $int( 'cols_lg', $d['cols']['lg'], 1, 6 ),
			'xl' => $int( 'cols_xl', $d['cols']['xl'], 1, 6 ),
		),
		'margin'      => $int( 'slider_margin', $d['margin'], 0, 60 ),
		'nav'         => $bool( 'slider_nav', $d['nav'] ),
		'dots'        => $bool( 'slider_dots', $d['dots'] ),
		'autoplay'    => $bool( 'slider_autoplay', $d['autoplay'] ),
		'show_count'  => $bool( 'show_count', $d['show_count'] ),
		'hide_empty'  => $bool( 'hide_empty', $d['hide_empty'] ),
		'exclude'     => array(),
		'image_size'  => $str( 'image_size', $d['image_size'] ),
		'lift'        => $bool( 'lift', $d['lift'] ),
	);

	if ( ! empty( $raw['woo_subcats_exclude'] ) ) {
		$s['exclude'] = array_values( array_filter( array_map( 'absint', (array) $raw['woo_subcats_exclude'] ) ) );
	}

	$sizes = array_merge( get_intermediate_image_sizes(), array( 'full' ) );
	if ( ! in_array( $s['image_size'], $sizes, true ) ) {
		$s['image_size'] = $d['image_size'];
	}

	$templates = cw_subcats_get_templates();
	if ( ! isset( $templates[ $s['template'] ] ) ) {
		$keys          = array_keys( $templates );
		$s['template'] = (string) reset( $keys );
	}

	return $s;
}

/**
 * Переопределения из мета-полей категории. Пустая строка / 0 = «наследовать».
 *
 * `display` — штатное поле WooCommerce «Display type» (term meta display_type).
 *
 * @param int $term_id ID категории.
 * @return array {
 *     enable: ''|on|off, display: ''|products|subcategories|both,
 *     template: string, layout: ''|grid|slider, cols: array breakpoint => 0|int
 * }
 */
function cw_subcats_get_term_overrides( $term_id ) {
	$term_id = absint( $term_id );

	$enable  = (string) get_term_meta( $term_id, 'cw_subcats_enable', true );
	$layout  = (string) get_term_meta( $term_id, 'cw_subcats_layout', true );
	$display = (string) get_term_meta( $term_id, 'display_type', true );

	$cols = array();
	foreach ( cw_subcats_cols_limits() as $bp => $max ) {
		$n           = absint( get_term_meta( $term_id, 'cw_subcats_cols_' . $bp, true ) );
		$cols[ $bp ] = ( $n >= 1 && $n <= $max ) ? $n : 0;
	}

	return array(
		'enable'   => in_array( $enable, array( 'on', 'off' ), true ) ? $enable : '',
		'display'  => in_array( $display, array( 'products', 'subcategories', 'both' ), true ) ? $display : '',
		'template' => sanitize_key( (string) get_term_meta( $term_id, 'cw_subcats_template', true ) ),
		'layout'   => in_array( $layout, array( 'grid', 'slider' ), true ) ? $layout : '',
		'cols'     => $cols,
	);
}

// ── Выборка ──────────────────────────────────────────────────────────────────

/**
 * Дочерние категории товаров.
 *
 * Аргументы совпадают с woocommerce_get_product_subcategories() (pad_counts,
 * фильтр woocommerce_product_subcategories_args), поэтому счётчики и порядок —
 * как у стандартного вывода WooCommerce.
 *
 * @param int   $parent_id ID родителя (0 — верхний уровень).
 * @param array $args      { hide_empty: bool, exclude: int[] }.
 * @return WP_Term[]
 */
function cw_subcats_get_terms( $parent_id, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'hide_empty' => true,
			'exclude'    => array(),
		)
	);

	$query = apply_filters(
		'woocommerce_product_subcategories_args',
		array(
			'parent'       => absint( $parent_id ),
			'hide_empty'   => 0,
			'hierarchical' => 1,
			'taxonomy'     => 'product_cat',
			'pad_counts'   => 1,
		)
	);

	$terms = get_categories( $query );
	if ( ! is_array( $terms ) ) {
		return array();
	}

	if ( $args['hide_empty'] ) {
		$terms = wp_list_filter( $terms, array( 'count' => 0 ), 'NOT' );
	}

	$exclude = array_map( 'absint', (array) $args['exclude'] );
	if ( $exclude ) {
		$terms = array_filter(
			$terms,
			function ( $term ) use ( $exclude ) {
				return ! in_array( (int) $term->term_id, $exclude, true );
			}
		);
	}

	return array_values( $terms );
}

/**
 * Решить, нужно ли показывать блок на текущей странице, и собрать данные.
 *
 * Блок не показывается: вне магазина/категорий, в поиске, на 2+ странице
 * пагинации, при активных фильтрах (как и у стандартного вывода WooCommerce),
 * а также если у категории выбрано «Hide» или нет ни одной подкатегории.
 *
 * @return array|null { settings: array, terms: WP_Term[] } или null.
 */
function cw_subcats_resolve() {
	if ( ! function_exists( 'is_shop' ) || ! function_exists( 'is_product_category' ) ) {
		return null;
	}

	$is_shop = is_shop();
	$is_cat  = is_product_category();

	if ( ! $is_shop && ! $is_cat ) {
		return null;
	}

	if ( is_search() || is_paged() || ( function_exists( 'cw_has_active_filters' ) && cw_has_active_filters() ) ) {
		return null;
	}

	$settings  = cw_subcats_global_settings();
	$templates = cw_subcats_get_templates();
	$term_id   = 0;
	$override  = '';

	if ( $is_cat ) {
		$term_id  = (int) get_queried_object_id();
		$over     = cw_subcats_get_term_overrides( $term_id );
		$override = $over['enable'];

		// WooCommerce «Display type» категории, если не Default, главнее нашего переключателя и режима из Redux.
		if ( 'products' === $over['display'] ) {
			$override = 'off';
		} elseif ( 'subcategories' === $over['display'] ) {
			$override         = 'on';
			$settings['mode'] = 'only';
		} elseif ( 'both' === $over['display'] ) {
			$override         = 'on';
			$settings['mode'] = 'above';
		}

		if ( $over['template'] && isset( $templates[ $over['template'] ] ) ) {
			$settings['template'] = $over['template'];
		}
		if ( $over['layout'] ) {
			$settings['layout'] = $over['layout'];
		}
		foreach ( $over['cols'] as $bp => $n ) {
			if ( $n ) {
				$settings['cols'][ $bp ] = $n;
			}
		}
	}

	if ( 'off' === $override ) {
		return null;
	}

	$allowed_here = $is_shop ? $settings['on_shop'] : $settings['on_category'];
	if ( 'on' !== $override && ! ( $settings['enable'] && $allowed_here ) ) {
		return null;
	}

	$terms = cw_subcats_get_terms(
		$term_id,
		array(
			'hide_empty' => $settings['hide_empty'],
			'exclude'    => $settings['exclude'],
		)
	);

	if ( ! $terms ) {
		return null;
	}

	return array(
		'settings' => $settings,
		'terms'    => $terms,
	);
}

// ── Рендер ───────────────────────────────────────────────────────────────────

/**
 * HTML одной карточки подкатегории (через систему карточек терминов).
 *
 * @param WP_Term $term Категория.
 * @param array   $s    Итоговые настройки.
 * @return string
 */
function cw_subcats_render_card( $term, $s ) {
	if ( ! function_exists( 'cw_render_term_card' ) ) {
		return '';
	}

	return cw_render_term_card(
		$term,
		$s['template'],
		array(
			'show_title'   => true,
			'show_excerpt' => false,
			'title_tag'    => 'h3',
		),
		array(
			'image_size'      => $s['image_size'],
			'enable_lift'     => $s['lift'],
			'show_term_count' => $s['show_count'],
			'hide_empty'      => $s['hide_empty'],
			'exclude'         => $s['exclude'],
			'full_overlay'    => true, // overlay-5*: gradient over the whole card, small cards need it for multi-line titles.
		)
	);
}

/**
 * Собрать блок подкатегорий для текущей страницы.
 *
 * @return array { html: string, only: bool } — only=true: показывать только
 *               подкатегории, без сетки товаров.
 */
function cw_subcats_render() {
	$empty = array(
		'html' => '',
		'only' => false,
	);

	$ctx = cw_subcats_resolve();
	if ( ! $ctx ) {
		return $empty;
	}

	$s     = $ctx['settings'];
	$items = array();

	foreach ( $ctx['terms'] as $term ) {
		$card = cw_subcats_render_card( $term, $s );
		if ( '' !== trim( $card ) ) {
			$items[] = $card;
		}
	}

	if ( ! $items ) {
		return $empty;
	}

	$flow    = cw_subcats_is_flow_template( $s['template'] );
	$cols    = $s['cols'];
	$classes = 'cw-subcats cw-subcats-' . sanitize_html_class( $s['layout'] ) . ' cw-subcats-tpl-' . sanitize_html_class( $s['template'] ) . ' mb-10';

	ob_start();

	if ( 'slider' === $s['layout'] ) {
		$data = array(
			'data-margin'   => (string) $s['margin'],
			'data-nav'      => $s['nav'] ? 'true' : 'false',
			'data-dots'     => $s['dots'] ? 'true' : 'false',
			'data-loop'     => 'false',
			'data-autoplay' => $s['autoplay'] ? 'true' : 'false',
		);
		if ( $s['autoplay'] ) {
			$data['data-autoplaytime'] = '4000';
		}
		if ( $flow ) {
			$data['data-items-auto'] = 'true';
		} else {
			$data['data-items']    = (string) $cols['xl'];
			$data['data-items-xs'] = (string) $cols['xs'];
			$data['data-items-sm'] = (string) $cols['sm'];
			$data['data-items-md'] = (string) $cols['md'];
			$data['data-items-lg'] = (string) $cols['lg'];
			$data['data-items-xl'] = (string) $cols['xl'];
		}

		$attrs = '';
		foreach ( $data as $name => $value ) {
			$attrs .= ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
		}

		// nav-visible: arrows are visible without hover (touch devices have none), otherwise the slider is not noticeable.
		$nav_class = $s['nav'] ? ' nav-visible' : '';

		echo '<div class="swiper-container ' . esc_attr( $classes . $nav_class ) . '"' . $attrs . '>'; // phpcs:ignore WordPress.Security.EscapeOutput -- attrs escaped above
		echo '<div class="swiper"><div class="swiper-wrapper">';
		foreach ( $items as $item ) {
			echo '<div class="swiper-slide' . ( $flow ? ' w-auto' : '' ) . '">' . $item . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- card templates escape their own output
		}
		echo '</div></div></div>';
	} elseif ( $flow ) {
		echo '<div class="' . esc_attr( $classes ) . ' d-flex flex-wrap gap-2">';
		foreach ( $items as $item ) {
			echo $item; // phpcs:ignore WordPress.Security.EscapeOutput -- card templates escape their own output
		}
		echo '</div>';
	} else {
		$row_cols = 'row-cols-' . $cols['xs']
			. ' row-cols-sm-' . $cols['sm']
			. ' row-cols-md-' . $cols['md']
			. ' row-cols-lg-' . $cols['lg']
			. ' row-cols-xl-' . $cols['xl'];
		$gap = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'gy-6 gx-6';

		echo '<div class="' . esc_attr( $classes ) . '">';
		echo '<div class="row ' . esc_attr( $row_cols . ' ' . $gap ) . '">';
		foreach ( $items as $item ) {
			echo '<div class="col">' . $item . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- card templates escape their own output
		}
		echo '</div></div>';
	}

	return array(
		'html' => ob_get_clean(),
		'only' => 'only' === $s['mode'],
	);
}

// ── Мета-поля категории (админка) ────────────────────────────────────────────

/**
 * <select> для мета-поля категории.
 *
 * @param string $name    Имя поля.
 * @param array  $options value => label.
 * @param string $current Текущее значение.
 * @param string $style   Необязательный inline-стиль <select> (задаётся в коде, не из ввода).
 * @return string
 */
function cw_subcats_term_select( $name, $options, $current, $style = '' ) {
	$html = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '"' . ( $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>';
	foreach ( $options as $value => $label ) {
		$html .= '<option value="' . esc_attr( $value ) . '"' . selected( $current, (string) $value, false ) . '>' . esc_html( $label ) . '</option>';
	}
	$html .= '</select>';

	return $html;
}

/**
 * Описание select-полей: name => [ label, options, desc? ].
 *
 * @return array
 */
function cw_subcats_term_field_defs() {
	$inherit = array( '' => __( 'Inherit (theme settings)', 'codeweber' ) );

	return array(
		'cw_subcats_enable'   => array(
			'label'   => __( 'Show subcategories', 'codeweber' ),
			'options' => $inherit + array(
				'on'  => __( 'Show', 'codeweber' ),
				'off' => __( 'Hide', 'codeweber' ),
			),
			'desc'    => __( 'Used when the WooCommerce "Display type" is Default. Any other Display type (Products / Subcategories / Both) overrides this.', 'codeweber' ),
		),
		'cw_subcats_template' => array(
			'label'   => __( 'Subcategories style', 'codeweber' ),
			'options' => $inherit + cw_subcats_get_templates(),
		),
		'cw_subcats_layout'   => array(
			'label'   => __( 'Subcategories layout', 'codeweber' ),
			'options' => $inherit + array(
				'grid'   => __( 'Grid', 'codeweber' ),
				'slider' => __( 'Slider', 'codeweber' ),
			),
		),
	);
}

/**
 * Пять <select> колонок (XS…XL): «Inherit (global: N)» или своё число.
 *
 * @param array $values breakpoint => сохранённое значение (0/'' = наследовать).
 * @return string
 */
function cw_subcats_cols_selects( $values = array() ) {
	$global = cw_subcats_global_settings();
	$global = $global['cols'];
	$widths = array(
		'xs' => '<576px',
		'sm' => '≥576px',
		'md' => '≥768px',
		'lg' => '≥992px',
		'xl' => '≥1200px',
	);

	$html = '<div style="display:flex;flex-wrap:wrap;gap:12px;">';
	foreach ( cw_subcats_cols_limits() as $bp => $max ) {
		$name    = 'cw_subcats_cols_' . $bp;
		$options = array(
			/* translators: %d: global number of columns */
			'' => sprintf( __( 'Inherit (global: %d)', 'codeweber' ), $global[ $bp ] ),
		);
		for ( $i = 1; $i <= $max; $i++ ) {
			$options[ $i ] = (string) $i;
		}
		$current = ! empty( $values[ $bp ] ) ? (string) (int) $values[ $bp ] : '';

		$html .= '<label for="' . esc_attr( $name ) . '" style="display:flex;flex-direction:column;gap:4px;">'
			. esc_html( strtoupper( $bp ) . ' ' . $widths[ $bp ] )
			. cw_subcats_term_select( $name, $options, $current, 'width:auto;min-width:9em;' )
			. '</label>';
	}
	$html .= '</div>';

	return $html;
}

/**
 * Подпись под строкой колонок.
 *
 * @return string
 */
function cw_subcats_cols_desc() {
	return __( 'Columns (Grid) or slides per view (Slider) for each screen width. Not used by the Chips style.', 'codeweber' );
}

/**
 * Форма «Add new category».
 */
function cw_subcats_add_term_fields() {
	wp_nonce_field( 'cw_subcats_term', 'cw_subcats_nonce' );

	echo '<h3>' . esc_html__( 'Subcategories', 'codeweber' ) . '</h3>';

	foreach ( cw_subcats_term_field_defs() as $name => $def ) {
		echo '<div class="form-field">';
		echo '<label for="' . esc_attr( $name ) . '">' . esc_html( $def['label'] ) . '</label>';
		echo cw_subcats_term_select( $name, $def['options'], '' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper
		if ( ! empty( $def['desc'] ) ) {
			echo '<p>' . esc_html( $def['desc'] ) . '</p>';
		}
		echo '</div>';
	}

	echo '<div class="form-field">';
	echo '<label for="cw_subcats_cols_xs">' . esc_html__( 'Subcategories columns', 'codeweber' ) . '</label>';
	echo cw_subcats_cols_selects(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper
	echo '<p>' . esc_html( cw_subcats_cols_desc() ) . '</p>';
	echo '</div>';
}
add_action( 'product_cat_add_form_fields', 'cw_subcats_add_term_fields' );

/**
 * Форма «Edit category».
 *
 * @param WP_Term $term Категория.
 */
function cw_subcats_edit_term_fields( $term ) {
	wp_nonce_field( 'cw_subcats_term', 'cw_subcats_nonce' );

	foreach ( cw_subcats_term_field_defs() as $name => $def ) {
		$current = (string) get_term_meta( $term->term_id, $name, true );

		echo '<tr class="form-field">';
		echo '<th scope="row"><label for="' . esc_attr( $name ) . '">' . esc_html( $def['label'] ) . '</label></th>';
		echo '<td>' . cw_subcats_term_select( $name, $def['options'], $current ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper
		if ( ! empty( $def['desc'] ) ) {
			echo '<p class="description">' . esc_html( $def['desc'] ) . '</p>';
		}
		echo '</td></tr>';
	}

	$over = cw_subcats_get_term_overrides( $term->term_id );

	echo '<tr class="form-field">';
	echo '<th scope="row">' . esc_html__( 'Subcategories columns', 'codeweber' ) . '</th>';
	echo '<td>' . cw_subcats_cols_selects( $over['cols'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper
	echo '<p class="description">' . esc_html( cw_subcats_cols_desc() ) . '</p>';
	echo '</td></tr>';
}
add_action( 'product_cat_edit_form_fields', 'cw_subcats_edit_term_fields' );

/**
 * Сохранение мета-полей. Пустое/неизвестное значение — «наследовать» (мета удаляется).
 *
 * @param int $term_id ID категории.
 */
function cw_subcats_save_term_fields( $term_id ) {
	if ( ! isset( $_POST['cw_subcats_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cw_subcats_nonce'] ) ), 'cw_subcats_term' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}

	$allowed = array(
		'cw_subcats_enable'   => array( 'on', 'off' ),
		'cw_subcats_layout'   => array( 'grid', 'slider' ),
		'cw_subcats_template' => array_keys( cw_subcats_get_templates() ),
	);

	foreach ( $allowed as $key => $values ) {
		$value = isset( $_POST[ $key ] ) ? sanitize_key( wp_unslash( $_POST[ $key ] ) ) : '';

		if ( in_array( $value, $values, true ) ) {
			update_term_meta( $term_id, $key, $value );
		} else {
			delete_term_meta( $term_id, $key );
		}
	}

	// Колонки по брейкпоинтам: целое 1…max, иначе «наследовать».
	foreach ( cw_subcats_cols_limits() as $bp => $max ) {
		$key = 'cw_subcats_cols_' . $bp;
		$n   = isset( $_POST[ $key ] ) ? absint( wp_unslash( $_POST[ $key ] ) ) : 0;

		if ( $n >= 1 && $n <= $max ) {
			update_term_meta( $term_id, $key, $n );
		} else {
			delete_term_meta( $term_id, $key );
		}
	}
}
add_action( 'created_product_cat', 'cw_subcats_save_term_fields' );
add_action( 'edited_product_cat', 'cw_subcats_save_term_fields' );
