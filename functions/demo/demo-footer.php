<?php
/**
 * Demo данные для CPT Footer
 *
 * Функции для создания demo футеров (Footer_01, Footer_02).
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Получить контент Footer_01 (один footer с 4 колонками).
 *
 * @return string
 */
function cw_demo_get_footer_01_content() {
	$home = home_url( '/', 'https' );
	return '<!-- wp:codeweber-blocks/section {"backgroundType":"color","backgroundColor":"navy","sectionTag":"footer"} -->
<footer class="wp-block-codeweber-blocks-section wrapper bg-navy none" role="region" aria-label="Content section"><div class="container py-14 py-md-16"><!-- wp:codeweber-blocks/columns {"columnsCount":4,"columnsRowCols":"","columnsRowColsMd":"","columnsGapType":"y","columnsGap":"","columnsGapMd":"","columnsSpacingType":""} -->
<div class="row"><!-- wp:codeweber-blocks/column {"columnCol":"12","columnColMd":"3"} -->
<div class="col-12 col-md-3"><!-- wp:codeweber-blocks/widget {"enableTitle":false} -->
<div class="widget"><!-- wp:codeweber-blocks/logo {"logoType":"dark","blockClass":"mb-4"} /-->

<!-- wp:codeweber-blocks/paragraph {"text":"Корпоративные консалтинговые решения для локальных, региональных и глобальных задач","textColor":"light","textClass":"mb-4"} -->
<p class="text-light mb-4">Корпоративные консалтинговые решения для локальных, региональных и глобальных задач</p>
<!-- /wp:codeweber-blocks/paragraph -->

<!-- wp:codeweber-blocks/social-icons {"styleType":"type9","size":"sm","buttonForm":"block"} /--></div>
<!-- /wp:codeweber-blocks/widget --></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnCol":"12","columnColMd":"3"} -->
<div class="col-12 col-md-3"><!-- wp:codeweber-blocks/menu {"mode":"wp-menu","wpMenuId":4,"theme":"dark","listType":"unordered","items":[{"id":"item-c08e4537fbc349b0a7ac045d1f70edac-1769883033161-0-508","text":"Colors","url":"' . $home . 'colors/"},{"id":"item-c08e4537fbc349b0a7ac045d1f70edac-1769883033161-1-712","text":"Typography","url":"' . $home . 'typography/"},{"id":"item-c08e4537fbc349b0a7ac045d1f70edac-1769883033161-2-63","text":"Elements","url":"' . $home . 'buttons/"},{"id":"item-c08e4537fbc349b0a7ac045d1f70edac-1769883033161-3-693","text":"Accordeon","url":"' . $home . 'accordeon/"},{"id":"item-c08e4537fbc349b0a7ac045d1f70edac-1769883033161-4-392","text":"Accordeon","url":"' . $home . 'accordeon-2/"},{"id":"item-c08e4537fbc349b0a7ac045d1f70edac-1769883033161-5-335","text":"Blog","url":"' . $home . 'blog/"},{"id":"item-c08e4537fbc349b0a7ac045d1f70edac-1769883033161-6-529","text":"Архив наград","url":"' . $home . 'awards/"},{"id":"item-c08e4537fbc349b0a7ac045d1f70edac-1769883033161-7-791","text":"Архив партнеров","url":"' . $home . 'partners/"},{"id":"item-c08e4537fbc349b0a7ac045d1f70edac-1769883033161-8-295","text":"Архив практик","url":"' . $home . 'practices/"},{"id":"item-c08e4537fbc349b0a7ac045d1f70edac-1769883033161-9-853","text":"Юридические документы Архив","url":"' . $home . 'legal/"},{"id":"item-c08e4537fbc349b0a7ac045d1f70edac-1769883033161-10-14","text":"Все вакансии","url":"' . $home . 'vacancies/"}],"menuClass":"list-unstyled text-reset mb-0 g-0","itemClass":"mb-0","linkClass":"mb-0","enableWidget":true,"enableTitle":true,"title":"Практики","titleTag":"div","titleColor":"light","titleSize":"h4","titleWeight":"fw-semibold","titleClass":"h4 widget-title text-white mb-3"} /--></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnCol":"12","columnColMd":"3"} -->
<div class="col-12 col-md-3"><!-- wp:codeweber-blocks/menu {"mode":"wp-menu","wpMenuId":4,"theme":"dark","listType":"unordered","items":[{"id":"item-53543a79d64a4d74b1e901deec8050ca-1769883033166-0-935","text":"Colors","url":"' . $home . 'colors/"},{"id":"item-53543a79d64a4d74b1e901deec8050ca-1769883033166-1-113","text":"Typography","url":"' . $home . 'typography/"},{"id":"item-53543a79d64a4d74b1e901deec8050ca-1769883033166-2-25","text":"Elements","url":"' . $home . 'buttons/"},{"id":"item-53543a79d64a4d74b1e901deec8050ca-1769883033166-3-803","text":"Accordeon","url":"' . $home . 'accordeon/"},{"id":"item-53543a79d64a4d74b1e901deec8050ca-1769883033166-4-417","text":"Accordeon","url":"' . $home . 'accordeon-2/"},{"id":"item-53543a79d64a4d74b1e901deec8050ca-1769883033166-5-510","text":"Blog","url":"' . $home . 'blog/"},{"id":"item-53543a79d64a4d74b1e901deec8050ca-1769883033166-6-147","text":"Архив наград","url":"' . $home . 'awards/"},{"id":"item-53543a79d64a4d74b1e901deec8050ca-1769883033166-7-866","text":"Архив партнеров","url":"' . $home . 'partners/"},{"id":"item-53543a79d64a4d74b1e901deec8050ca-1769883033166-8-264","text":"Архив практик","url":"' . $home . 'practices/"},{"id":"item-53543a79d64a4d74b1e901deec8050ca-1769883033166-9-173","text":"Юридические документы Архив","url":"' . $home . 'legal/"},{"id":"item-53543a79d64a4d74b1e901deec8050ca-1769883033166-10-880","text":"Все вакансии","url":"' . $home . 'vacancies/"}],"enableWidget":true,"enableTitle":true,"title":"Информация","titleTag":"div","titleColor":"light","titleSize":"h4","titleWeight":"fw-semibold","titleClass":"h4 widget-title text-white mb-3"} /--></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnCol":"12","columnColMd":"3"} -->
<div class="col-12 col-md-3"><!-- wp:codeweber-blocks/widget {"title":"Контакты","titleColor":"light"} -->
<div class="widget"><h4 class="widget-title text-light mb-3">Контакты</h4><!-- wp:codeweber-blocks/contacts {"items":[{"type":"address","enabled":true,"format":"simple","addressType":"legal"},{"type":"email","enabled":true,"format":"simple"},{"type":"phone","enabled":true,"format":"simple","phones":["phone_01","phone_02","phone_03"]}],"format":"icon-simple","iconFontSize":"","iconClass":"me-0","iconWrapper":true,"iconBtnSize":"btn-sm","iconBtnVariant":"solid","titleColor":"light","textColor":"light","itemClass":"mb-2"} /--></div>
<!-- /wp:codeweber-blocks/widget --></div>
<!-- /wp:codeweber-blocks/column --></div>
<!-- /wp:codeweber-blocks/columns --></div></footer>
<!-- /wp:codeweber-blocks/section -->';
}

/**
 * Получить контент Footer_02 (CTA + footer с 4 колонками).
 *
 * @return string
 */
function cw_demo_get_footer_02_content() {
	$home = home_url( '/', 'https' );
	$img_src = get_template_directory_uri() . '/dist/assets/img/photos/bg3.jpg';
	return '<!-- wp:codeweber-blocks/section {"containerClass":"pt-14"} -->
<section class="wp-block-codeweber-blocks-section wrapper none" role="region" aria-label="Content section"><div class="container pt-14"><!-- wp:codeweber-blocks/cta {"ctaType":"cta-2","backgroundType":"image","backgroundColor":"telegram","cardClass":"bg-dusty-navy","blockClass":"my-n13 my-md-n15 my-lg-n10","className":"image-wrapper bg-full bg-image bg-overlay bg-overlay-400"} -->
<div class="card image-wrapper bg-image bg-overlay bg-overlay-400 my-n13 my-md-n15 my-lg-n10 bg-dusty-navy" data-image-src="' . esc_url( $img_src ) . '"><div class="card-body p-6 p-md-11 d-lg-flex flex-row align-items-lg-center justify-content-md-between text-center text-lg-start"><!-- wp:codeweber-gutenberg-blocks/heading-subtitle {"enableSubtitle":false,"title":"Join Our Community","text":"We are trusted by over 5000+ clients. Join them by using our services and grow your business.","order":"title-first","titleTag":"h3","titleSize":"display-6 mb-6 mb-lg-0 pe-lg-10 pe-xl-5 pe-xxl-18 text-white","subtitleSize":"fs-16 text-uppercase text-white mb-3","subtitleLine":false,"textClass":"lead mb-5 px-md-16 px-lg-3"} -->
<h3 class="text-left display-6 mb-6 mb-lg-0 pe-lg-10 pe-xl-5 pe-xxl-18 text-white">Join Our Community</h3>
<!-- /wp:codeweber-gutenberg-blocks/heading-subtitle -->

<!-- wp:codeweber-blocks/button {"ButtonContent":"Join Us","ButtonClass":"btn btn-white rounded-pill mb-0 text-nowrap"} -->
<a href="#" class="btn has-ripple btn-primary rounded-0" target="_blank" rel="noopener noreferrer">Join Us</a>
<!-- /wp:codeweber-blocks/button --></div></div>
<!-- /wp:codeweber-blocks/cta --></div></section>
<!-- /wp:codeweber-blocks/section -->

<!-- wp:codeweber-blocks/section {"backgroundColor":"navy","sectionTag":"footer","sectionClass":"bg-charcoal-blue"} -->
<footer class="wp-block-codeweber-blocks-section wrapper none  bg-charcoal-blue" role="region" aria-label="Content section"><div class="container py-14 py-md-16"><!-- wp:codeweber-blocks/columns {"columnsCount":4,"columnsRowCols":"","columnsRowColsMd":"","columnsGapType":"y","columnsGap":"","columnsGapMd":"","columnsSpacingType":""} -->
<div class="row"><!-- wp:codeweber-blocks/column {"columnCol":"12","columnColMd":"3"} -->
<div class="col-12 col-md-3"><!-- wp:codeweber-blocks/widget {"enableTitle":false} -->
<div class="widget"><!-- wp:codeweber-blocks/logo {"logoType":"dark","blockClass":"mb-4"} /-->

<!-- wp:codeweber-blocks/paragraph {"text":"Корпоративные консалтинговые решения для локальных, региональных и глобальных задач","textColor":"light","textClass":"mb-4"} -->
<p class="text-light mb-4">Корпоративные консалтинговые решения для локальных, региональных и глобальных задач</p>
<!-- /wp:codeweber-blocks/paragraph -->

<!-- wp:codeweber-blocks/social-icons {"styleType":"type9","size":"sm","buttonForm":"block"} /--></div>
<!-- /wp:codeweber-blocks/widget --></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnCol":"12","columnColMd":"3"} -->
<div class="col-12 col-md-3"><!-- wp:codeweber-blocks/menu {"mode":"wp-menu","wpMenuId":4,"theme":"dark","listType":"unordered","items":[{"id":"item-4ce7d4325de848db831494d637e47093-1769880501292-0-181","text":"Colors","url":"' . $home . 'colors/"},{"id":"item-4ce7d4325de848db831494d637e47093-1769880501292-1-774","text":"Typography","url":"' . $home . 'typography/"},{"id":"item-4ce7d4325de848db831494d637e47093-1769880501292-2-278","text":"Elements","url":"' . $home . 'buttons/"},{"id":"item-4ce7d4325de848db831494d637e47093-1769880501292-3-981","text":"Accordeon","url":"' . $home . 'accordeon/"},{"id":"item-4ce7d4325de848db831494d637e47093-1769880501292-4-700","text":"Accordeon","url":"' . $home . 'accordeon-2/"},{"id":"item-4ce7d4325de848db831494d637e47093-1769880501292-5-618","text":"Blog","url":"' . $home . 'blog/"},{"id":"item-4ce7d4325de848db831494d637e47093-1769880501292-6-209","text":"Архив наград","url":"' . $home . 'awards/"},{"id":"item-4ce7d4325de848db831494d637e47093-1769880501292-7-2","text":"Архив партнеров","url":"' . $home . 'partners/"},{"id":"item-4ce7d4325de848db831494d637e47093-1769880501292-8-269","text":"Архив практик","url":"' . $home . 'practices/"},{"id":"item-4ce7d4325de848db831494d637e47093-1769880501292-9-255","text":"Юридические документы Архив","url":"' . $home . 'legal/"},{"id":"item-4ce7d4325de848db831494d637e47093-1769880501292-10-576","text":"Все вакансии","url":"' . $home . 'vacancies/"}],"menuClass":"list-unstyled text-reset mb-0 g-0","itemClass":"mb-0","linkClass":"mb-0","enableWidget":true,"enableTitle":true,"title":"Практики","titleTag":"div","titleColor":"light","titleSize":"h4","titleWeight":"fw-semibold","titleClass":"h4 widget-title text-white mb-3"} /--></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnCol":"12","columnColMd":"3"} -->
<div class="col-12 col-md-3"><!-- wp:codeweber-blocks/menu {"mode":"wp-menu","wpMenuId":4,"theme":"dark","listType":"unordered","items":[{"id":"item-9e0407c2d2ce4011b64e1418c0b0d917-1769880501299-0-365","text":"Colors","url":"' . $home . 'colors/"},{"id":"item-9e0407c2d2ce4011b64e1418c0b0d917-1769880501299-1-958","text":"Typography","url":"' . $home . 'typography/"},{"id":"item-9e0407c2d2ce4011b64e1418c0b0d917-1769880501299-2-467","text":"Elements","url":"' . $home . 'buttons/"},{"id":"item-9e0407c2d2ce4011b64e1418c0b0d917-1769880501299-3-881","text":"Accordeon","url":"' . $home . 'accordeon/"},{"id":"item-9e0407c2d2ce4011b64e1418c0b0d917-1769880501299-4-962","text":"Accordeon","url":"' . $home . 'accordeon-2/"},{"id":"item-9e0407c2d2ce4011b64e1418c0b0d917-1769880501299-5-71","text":"Blog","url":"' . $home . 'blog/"},{"id":"item-9e0407c2d2ce4011b64e1418c0b0d917-1769880501299-6-489","text":"Архив наград","url":"' . $home . 'awards/"},{"id":"item-9e0407c2d2ce4011b64e1418c0b0d917-1769880501299-7-175","text":"Архив партнеров","url":"' . $home . 'partners/"},{"id":"item-9e0407c2d2ce4011b64e1418c0b0d917-1769880501299-8-336","text":"Архив практик","url":"' . $home . 'practices/"},{"id":"item-9e0407c2d2ce4011b64e1418c0b0d917-1769880501299-9-502","text":"Юридические документы Архив","url":"' . $home . 'legal/"},{"id":"item-9e0407c2d2ce4011b64e1418c0b0d917-1769880501299-10-182","text":"Все вакансии","url":"' . $home . 'vacancies/"}],"enableWidget":true,"enableTitle":true,"title":"Информация","titleTag":"div","titleColor":"light","titleSize":"h4","titleWeight":"fw-semibold","titleClass":"h4 widget-title text-white mb-3"} /--></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnCol":"12","columnColMd":"3"} -->
<div class="col-12 col-md-3"><!-- wp:codeweber-blocks/widget {"title":"Контакты","titleColor":"light"} -->
<div class="widget"><h4 class="widget-title text-light mb-3">Контакты</h4><!-- wp:codeweber-blocks/contacts {"items":[{"type":"address","enabled":true,"format":"simple","addressType":"legal"},{"type":"email","enabled":true,"format":"simple"},{"type":"phone","enabled":true,"format":"simple","phones":["phone_01","phone_02","phone_03"]}],"format":"icon-simple","iconFontSize":"","iconClass":"me-0","iconWrapper":true,"iconBtnSize":"btn-sm","iconBtnVariant":"solid","titleColor":"light","textColor":"light","itemClass":"mb-2"} /--></div>
<!-- /wp:codeweber-blocks/widget --></div>
<!-- /wp:codeweber-blocks/column --></div>
<!-- /wp:codeweber-blocks/columns --></div></footer>
<!-- /wp:codeweber-blocks/section -->';
}

/**
 * Получить данные demo футеров.
 *
 * @return array
 */
function cw_demo_get_footers_data() {
	return array(
		array(
			'title' => 'Footer_01',
			'slug'  => 'footer-01',
			'content' => cw_demo_get_footer_01_content(),
		),
		array(
			'title' => 'Footer_02',
			'slug'  => 'footer-02',
			'content' => cw_demo_get_footer_02_content(),
		),
	);
}

/**
 * Создать один demo футер.
 *
 * @param array $footer_data Данные футера.
 * @return int|false ID созданной записи или false при ошибке.
 */
function cw_demo_create_footer_post( $footer_data ) {
	if ( empty( $footer_data['title'] ) || empty( $footer_data['content'] ) ) {
		return false;
	}

	$post_data = array(
		'post_title'   => sanitize_text_field( $footer_data['title'] ),
		'post_name'    => ! empty( $footer_data['slug'] ) ? sanitize_title( $footer_data['slug'] ) : sanitize_title( $footer_data['title'] ),
		'post_status'  => 'publish',
		'post_type'    => 'footer',
		'post_author'  => get_current_user_id(),
		'post_content' => $footer_data['content'],
	);

	$post_id = wp_insert_post( $post_data );

	if ( is_wp_error( $post_id ) ) {
		return false;
	}

	update_post_meta( $post_id, '_demo_created', true );

	return $post_id;
}

/**
 * Создать все demo футеры.
 *
 * @return array
 */
function cw_demo_create_footers() {
	$data = cw_demo_get_footers_data();

	if ( empty( $data ) ) {
		return array(
			'success' => false,
			'message' => __( 'Данные не найдены', 'codeweber' ),
			'created' => 0,
			'errors'  => array(),
		);
	}

	$created = 0;
	$errors  = array();

	foreach ( $data as $item ) {
		$post_id = cw_demo_create_footer_post( $item );
		if ( $post_id ) {
			$created++;
		} else {
			$errors[] = __( 'Не удалось создать:', 'codeweber' ) . ' ' . ( ! empty( $item['title'] ) ? $item['title'] : '—' );
		}
	}

	return array(
		'success' => true,
		'message' => sprintf( __( 'Создано футеров: %1$d из %2$d', 'codeweber' ), $created, count( $data ) ),
		'created' => $created,
		'total'   => count( $data ),
		'errors'  => $errors,
	);
}

/**
 * Удалить все demo футеры.
 *
 * @return array
 */
function cw_demo_delete_footers() {
	$query = new WP_Query(
		array(
			'post_type'      => 'footer',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'   => '_demo_created',
					'value' => true,
					'compare' => '=',
				),
			),
			'fields'         => 'ids',
		)
	);

	$ids = $query->posts;
	$deleted = 0;

	foreach ( $ids as $post_id ) {
		if ( wp_delete_post( $post_id, true ) ) {
			$deleted++;
		}
	}

	return array(
		'success' => true,
		'message' => sprintf( __( 'Удалено футеров: %d', 'codeweber' ), $deleted ),
		'deleted' => $deleted,
		'errors'  => array(),
	);
}
