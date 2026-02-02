<?php
/**
 * Demo данные для CPT Footer
 *
 * Функции для создания demo футеров (Footer_01, Footer_02, Footer_03).
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
<div class="row"><!-- wp:codeweber-blocks/column {"columnClass":"mb-5 mb-lg-0","columnCol":"12","columnColMd":"6","columnColLg":"3"} -->
<div class="mb-5 mb-lg-0 col-12 col-md-6 col-lg-3"><!-- wp:codeweber-blocks/widget {"enableTitle":false} -->
<div class="widget"><!-- wp:codeweber-blocks/logo {"logoType":"dark","blockClass":"mb-4"} /-->

<!-- wp:codeweber-blocks/paragraph {"text":"Корпоративные консалтинговые решения для локальных, региональных и глобальных задач","textColor":"light","textClass":"mb-4"} -->
<p class="text-light mb-4">Корпоративные консалтинговые решения для локальных, региональных и глобальных задач</p>
<!-- /wp:codeweber-blocks/paragraph -->

<!-- wp:codeweber-blocks/social-icons {"styleType":"type9","size":"sm","buttonForm":"block"} /--></div>
<!-- /wp:codeweber-blocks/widget --></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnClass":"mb-5 mb-lg-0","columnCol":"12","columnColMd":"6","columnColLg":"3"} -->
<div class="mb-5 mb-lg-0 col-12 col-md-6 col-lg-3"><!-- wp:codeweber-blocks/menu {"mode":"wp-menu","wpMenuId":4,"theme":"dark","listType":"unordered","items":[{"id":"item-56aca98d898a4497bd079ba69e646fa4-1769952594417-0-298","text":"Colors","url":"' . $home . 'colors/"},{"id":"item-56aca98d898a4497bd079ba69e646fa4-1769952594417-1-255","text":"Typography","url":"' . $home . 'typography/"},{"id":"item-56aca98d898a4497bd079ba69e646fa4-1769952594417-2-356","text":"Elements","url":"' . $home . 'buttons/"},{"id":"item-56aca98d898a4497bd079ba69e646fa4-1769952594417-3-216","text":"Accordeon","url":"' . $home . 'accordeon/"},{"id":"item-56aca98d898a4497bd079ba69e646fa4-1769952594417-4-248","text":"Accordeon","url":"' . $home . 'accordeon-2/"},{"id":"item-56aca98d898a4497bd079ba69e646fa4-1769952594417-5-388","text":"Blog","url":"' . $home . 'blog/"},{"id":"item-56aca98d898a4497bd079ba69e646fa4-1769952594417-6-315","text":"Архив наград","url":"' . $home . 'awards/"},{"id":"item-56aca98d898a4497bd079ba69e646fa4-1769952594417-7-949","text":"Архив партнеров","url":"' . $home . 'partners/"},{"id":"item-56aca98d898a4497bd079ba69e646fa4-1769952594417-8-505","text":"Архив практик","url":"' . $home . 'practices/"},{"id":"item-56aca98d898a4497bd079ba69e646fa4-1769952594417-9-14","text":"Юридические документы Архив","url":"' . $home . 'legal/"},{"id":"item-56aca98d898a4497bd079ba69e646fa4-1769952594417-10-130","text":"Все вакансии","url":"' . $home . 'vacancies/"}],"menuClass":"list-unstyled text-reset mb-0 g-0","itemClass":"mb-0","linkClass":"mb-0","enableWidget":true,"enableTitle":true,"title":"Практики","titleTag":"div","titleColor":"light","titleSize":"h4","titleWeight":"fw-semibold","titleClass":"h4 widget-title text-white mb-3"} /--></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnClass":"mb-5 mb-lg-0","columnCol":"12","columnColMd":"6","columnColLg":"3"} -->
<div class="mb-5 mb-lg-0 col-12 col-md-6 col-lg-3"><!-- wp:codeweber-blocks/menu {"mode":"wp-menu","wpMenuId":4,"theme":"dark","listType":"unordered","items":[{"id":"item-44329dcf62e34633957ddd666ad1d8cd-1769952594586-0-592","text":"Colors","url":"' . $home . 'colors/"},{"id":"item-44329dcf62e34633957ddd666ad1d8cd-1769952594586-1-140","text":"Typography","url":"' . $home . 'typography/"},{"id":"item-44329dcf62e34633957ddd666ad1d8cd-1769952594586-2-321","text":"Elements","url":"' . $home . 'buttons/"},{"id":"item-44329dcf62e34633957ddd666ad1d8cd-1769952594586-3-609","text":"Accordeon","url":"' . $home . 'accordeon/"},{"id":"item-44329dcf62e34633957ddd666ad1d8cd-1769952594586-4-514","text":"Accordeon","url":"' . $home . 'accordeon-2/"},{"id":"item-44329dcf62e34633957ddd666ad1d8cd-1769952594586-5-597","text":"Blog","url":"' . $home . 'blog/"},{"id":"item-44329dcf62e34633957ddd666ad1d8cd-1769952594586-6-816","text":"Архив наград","url":"' . $home . 'awards/"},{"id":"item-44329dcf62e34633957ddd666ad1d8cd-1769952594586-7-801","text":"Архив партнеров","url":"' . $home . 'partners/"},{"id":"item-44329dcf62e34633957ddd666ad1d8cd-1769952594586-8-307","text":"Архив практик","url":"' . $home . 'practices/"},{"id":"item-44329dcf62e34633957ddd666ad1d8cd-1769952594586-9-614","text":"Юридические документы Архив","url":"' . $home . 'legal/"},{"id":"item-44329dcf62e34633957ddd666ad1d8cd-1769952594586-10-585","text":"Все вакансии","url":"' . $home . 'vacancies/"}],"enableWidget":true,"enableTitle":true,"title":"Информация","titleTag":"div","titleColor":"light","titleSize":"h4","titleWeight":"fw-semibold","titleClass":"h4 widget-title text-white mb-3"} /--></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnClass":"mb-5 mb-lg-0","columnCol":"12","columnColMd":"6","columnColLg":"3"} -->
<div class="mb-5 mb-lg-0 col-12 col-md-6 col-lg-3"><!-- wp:codeweber-blocks/widget {"title":"Контакты","titleColor":"light"} -->
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
	$home   = home_url( '/', 'https' );
	$img_src = get_template_directory_uri() . '/dist/assets/img/photos/bg3.jpg';
	return '<!-- wp:codeweber-blocks/section {"containerClass":"pt-14"} -->
<section class="wp-block-codeweber-blocks-section wrapper none" role="region" aria-label="Content section"><div class="container pt-14"><!-- wp:codeweber-blocks/cta {"ctaType":"cta-2","backgroundType":"image","backgroundColor":"telegram","cardClass":"bg-dusty-navy","blockClass":"my-n13 my-md-n15 my-lg-n10"} -->
<div class="card image-wrapper bg-image bg-overlay bg-overlay-400 my-n13 my-md-n15 my-lg-n10 bg-dusty-navy" data-image-src="' . esc_url( $img_src ) . '"><div class="card-body p-6 p-md-11 d-lg-flex flex-row align-items-lg-center justify-content-md-between text-center text-lg-start"><!-- wp:codeweber-gutenberg-blocks/heading-subtitle {"enableSubtitle":false,"enableText":true,"title":"Join Our Community","text":"We are trusted by over 5000+ clients. Join them by using our services and grow your business.","order":"title-first","titleTag":"h3","textColor":"light","titleSize":"display-6 mb-6 mb-lg-0 pe-lg-10 pe-xl-5 pe-xxl-18 text-white","subtitleSize":"fs-16 text-uppercase text-white mb-3","subtitleLine":false,"textClass":"lead mb-0"} -->
<div class="d-flex flex-column"><h3 class="text-left display-6 mb-6 mb-lg-0 pe-lg-10 pe-xl-5 pe-xxl-18 text-white">Join Our Community</h3><p class="text-light lead mb-0">We are trusted by over 5000+ clients. Join them by using our services and grow your business.</p></div>
<!-- /wp:codeweber-gutenberg-blocks/heading-subtitle -->

<!-- wp:codeweber-blocks/button {"ButtonContent":"Join Us","ButtonClass":"btn btn-white rounded-pill mb-0 text-nowrap"} -->
<a href="#" class="btn has-ripple btn-primary rounded-0" target="_blank" rel="noopener noreferrer">Join Us</a>
<!-- /wp:codeweber-blocks/button --></div></div>
<!-- /wp:codeweber-blocks/cta --></div></section>
<!-- /wp:codeweber-blocks/section -->

<!-- wp:codeweber-blocks/section {"backgroundColor":"navy","containerClass":"pb-14 pb-md-16 pt-16 pt-md-18","sectionTag":"footer","sectionClass":"bg-charcoal-blue"} -->
<footer class="wp-block-codeweber-blocks-section wrapper none  bg-charcoal-blue" role="region" aria-label="Content section"><div class="container pb-14 pb-md-16 pt-16 pt-md-18"><!-- wp:codeweber-blocks/columns {"columnsCount":4,"columnsRowCols":"","columnsRowColsMd":"","columnsGapType":"y","columnsGap":"","columnsGapMd":"","columnsSpacingType":""} -->
<div class="row"><!-- wp:codeweber-blocks/column {"columnClass":"mb-5 mb-lg-0","columnCol":"12","columnColMd":"6","columnColLg":"3"} -->
<div class="mb-5 mb-lg-0 col-12 col-md-6 col-lg-3"><!-- wp:codeweber-blocks/widget {"enableTitle":false} -->
<div class="widget"><!-- wp:codeweber-blocks/logo {"logoType":"dark","blockClass":"mb-4"} /-->

<!-- wp:codeweber-blocks/paragraph {"text":"Корпоративные консалтинговые решения для локальных, региональных и глобальных задач","textColor":"light","textClass":"mb-4"} -->
<p class="text-light mb-4">Корпоративные консалтинговые решения для локальных, региональных и глобальных задач</p>
<!-- /wp:codeweber-blocks/paragraph -->

<!-- wp:codeweber-blocks/social-icons {"styleType":"type9","size":"sm","buttonForm":"block"} /--></div>
<!-- /wp:codeweber-blocks/widget --></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnClass":"mb-5 mb-lg-0","columnCol":"12","columnColMd":"6","columnColLg":"3"} -->
<div class="mb-5 mb-lg-0 col-12 col-md-6 col-lg-3"><!-- wp:codeweber-blocks/menu {"mode":"wp-menu","wpMenuId":4,"theme":"dark","listType":"unordered","items":[{"id":"item-4e5fe3bf9f16472c8a9bb54581d42507-1769952734896-0-372","text":"Colors","url":"' . $home . 'colors/"},{"id":"item-4e5fe3bf9f16472c8a9bb54581d42507-1769952734896-1-668","text":"Typography","url":"' . $home . 'typography/"},{"id":"item-4e5fe3bf9f16472c8a9bb54581d42507-1769952734896-2-877","text":"Elements","url":"' . $home . 'buttons/"},{"id":"item-4e5fe3bf9f16472c8a9bb54581d42507-1769952734896-3-161","text":"Accordeon","url":"' . $home . 'accordeon/"},{"id":"item-4e5fe3bf9f16472c8a9bb54581d42507-1769952734896-4-766","text":"Accordeon","url":"' . $home . 'accordeon-2/"},{"id":"item-4e5fe3bf9f16472c8a9bb54581d42507-1769952734896-5-610","text":"Blog","url":"' . $home . 'blog/"},{"id":"item-4e5fe3bf9f16472c8a9bb54581d42507-1769952734896-6-623","text":"Архив наград","url":"' . $home . 'awards/"},{"id":"item-4e5fe3bf9f16472c8a9bb54581d42507-1769952734896-7-808","text":"Архив партнеров","url":"' . $home . 'partners/"},{"id":"item-4e5fe3bf9f16472c8a9bb54581d42507-1769952734896-8-93","text":"Архив практик","url":"' . $home . 'practices/"},{"id":"item-4e5fe3bf9f16472c8a9bb54581d42507-1769952734896-9-660","text":"Юридические документы Архив","url":"' . $home . 'legal/"},{"id":"item-4e5fe3bf9f16472c8a9bb54581d42507-1769952734896-10-654","text":"Все вакансии","url":"' . $home . 'vacancies/"}],"menuClass":"list-unstyled text-reset mb-0 g-0","itemClass":"mb-0","linkClass":"mb-0","enableWidget":true,"enableTitle":true,"title":"Практики","titleTag":"div","titleColor":"light","titleSize":"h4","titleWeight":"fw-semibold","titleClass":"h4 widget-title text-white mb-3"} /--></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnClass":"mb-5 mb-lg-0","columnCol":"12","columnColMd":"6","columnColLg":"3"} -->
<div class="mb-5 mb-lg-0 col-12 col-md-6 col-lg-3"><!-- wp:codeweber-blocks/menu {"mode":"wp-menu","wpMenuId":4,"theme":"dark","listType":"unordered","items":[{"id":"item-2f2c477e662b4600ba7427a3198ecc49-1769952734932-0-956","text":"Colors","url":"' . $home . 'colors/"},{"id":"item-2f2c477e662b4600ba7427a3198ecc49-1769952734932-1-959","text":"Typography","url":"' . $home . 'typography/"},{"id":"item-2f2c477e662b4600ba7427a3198ecc49-1769952734932-2-63","text":"Elements","url":"' . $home . 'buttons/"},{"id":"item-2f2c477e662b4600ba7427a3198ecc49-1769952734932-3-978","text":"Accordeon","url":"' . $home . 'accordeon/"},{"id":"item-2f2c477e662b4600ba7427a3198ecc49-1769952734932-4-409","text":"Accordeon","url":"' . $home . 'accordeon-2/"},{"id":"item-2f2c477e662b4600ba7427a3198ecc49-1769952734932-5-941","text":"Blog","url":"' . $home . 'blog/"},{"id":"item-2f2c477e662b4600ba7427a3198ecc49-1769952734932-6-319","text":"Архив наград","url":"' . $home . 'awards/"},{"id":"item-2f2c477e662b4600ba7427a3198ecc49-1769952734932-7-495","text":"Архив партнеров","url":"' . $home . 'partners/"},{"id":"item-2f2c477e662b4600ba7427a3198ecc49-1769952734932-8-717","text":"Архив практик","url":"' . $home . 'practices/"},{"id":"item-2f2c477e662b4600ba7427a3198ecc49-1769952734932-9-183","text":"Юридические документы Архив","url":"' . $home . 'legal/"},{"id":"item-2f2c477e662b4600ba7427a3198ecc49-1769952734932-10-344","text":"Все вакансии","url":"' . $home . 'vacancies/"}],"enableWidget":true,"enableTitle":true,"title":"Информация","titleTag":"div","titleColor":"light","titleSize":"h4","titleWeight":"fw-semibold","titleClass":"h4 widget-title text-white mb-3"} /--></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnClass":"mb-5 mb-lg-0","columnCol":"12","columnColMd":"6","columnColLg":"3"} -->
<div class="mb-5 mb-lg-0 col-12 col-md-6 col-lg-3"><!-- wp:codeweber-blocks/widget {"title":"Контакты","titleColor":"light"} -->
<div class="widget"><h4 class="widget-title text-light mb-3">Контакты</h4><!-- wp:codeweber-blocks/contacts {"items":[{"type":"address","enabled":true,"format":"simple","addressType":"legal"},{"type":"email","enabled":true,"format":"simple"},{"type":"phone","enabled":true,"format":"simple","phones":["phone_01","phone_02","phone_03"]}],"format":"icon-simple","iconFontSize":"","iconClass":"me-0","iconWrapper":true,"iconBtnSize":"btn-sm","iconBtnVariant":"solid","titleColor":"light","textColor":"light","itemClass":"mb-2"} /--></div>
<!-- /wp:codeweber-blocks/widget --></div>
<!-- /wp:codeweber-blocks/column --></div>
<!-- /wp:codeweber-blocks/columns --></div></footer>
<!-- /wp:codeweber-blocks/section -->';
}

/**
 * Получить контент Footer_03 (CTA card + footer с 4 колонками, Redux Demo).
 *
 * @return string
 */
function cw_demo_get_footer_03_content() {
	return '<!-- wp:codeweber-blocks/section {"containerClass":"pt-14"} -->
<section class="wp-block-codeweber-blocks-section wrapper none" role="region" aria-label="Content section"><div class="container pt-14"><!-- wp:codeweber-blocks/card {"backgroundColor":"dark","blockClass":"bg-dusty-navy my-n13 my-md-n15 my-lg-n10 rellax","blockData":"rellax-speed=1"} -->
<div class="card h-100 bg-dusty-navy my-n13 my-md-n15 my-lg-n10 rellax" data-rellax-speed="1"><div class="card-body"><!-- wp:codeweber-blocks/columns -->
<div class="row g-3 g-md-3"><!-- wp:codeweber-blocks/column {"columnCol":"12","columnColMd":"9"} -->
<div class="col-12 col-md-9"><!-- wp:codeweber-gutenberg-blocks/heading-subtitle {"enableSubtitle":false,"enableText":true,"title":"Получите персональную юридическую консультацию","text":"Профессиональная поддержка по вопросам международного и корпоративного права","titleTag":"div","titleColor":"light","textColor":"light","align":"","titleSize":"h3","textClass":"mb-0"} -->
<div class="d-flex flex-column"><div class="text-light h3">Получите персональную юридическую консультацию</div><p class="text-light mb-0">Профессиональная поддержка по вопросам международного и корпоративного права</p></div>
<!-- /wp:codeweber-gutenberg-blocks/heading-subtitle --></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnJustifyContent":"justify-content-center","columnCol":"12","columnColMd":"3"} -->
<div class="col-12 col-md-3 d-flex flex-column justify-content-center"><!-- wp:codeweber-blocks/group-button {"groupJustifyContent":"justify-content-end"} -->
<div class="d-flex justify-content-end"><!-- wp:codeweber-blocks/button -->
<a href="#" class="btn has-ripple btn-primary rounded-0" target="_blank" rel="noopener noreferrer">Large Button</a>
<!-- /wp:codeweber-blocks/button --></div>
<!-- /wp:codeweber-blocks/group-button --></div>
<!-- /wp:codeweber-blocks/column --></div>
<!-- /wp:codeweber-blocks/columns --></div></div>
<!-- /wp:codeweber-blocks/card --></div></section>
<!-- /wp:codeweber-blocks/section -->

<!-- wp:codeweber-blocks/section {"containerClass":"pb-14 pb-md-16 pt-16 pt-md-18","sectionTag":"footer","sectionClass":"bg-charcoal-blue"} -->
<footer class="wp-block-codeweber-blocks-section wrapper none  bg-charcoal-blue" role="region" aria-label="Content section"><div class="container pb-14 pb-md-16 pt-16 pt-md-18"><!-- wp:codeweber-blocks/columns {"columnsCount":4,"columnsClass":"mb-5 mb-lg-0","columnsRowCols":"","columnsRowColsMd":"","columnsGapType":"y","columnsGap":"","columnsGapMd":"","columnsSpacingType":""} -->
<div class="mb-5 mb-lg-0 row"><!-- wp:codeweber-blocks/column {"columnClass":"mb-5 mb-lg-0","columnCol":"12","columnColMd":"6","columnColLg":"3"} -->
<div class="mb-5 mb-lg-0 col-12 col-md-6 col-lg-3"><!-- wp:codeweber-blocks/widget {"enableTitle":false} -->
<div class="widget"><!-- wp:codeweber-blocks/logo {"logoType":"dark","blockClass":"mb-4"} /-->

<!-- wp:codeweber-blocks/paragraph {"text":"Корпоративные консалтинговые решения для локальных, региональных и глобальных задач","textColor":"light","textClass":"mb-4"} -->
<p class="text-light mb-4">Корпоративные консалтинговые решения для локальных, региональных и глобальных задач</p>
<!-- /wp:codeweber-blocks/paragraph -->

<!-- wp:codeweber-blocks/social-icons {"styleType":"type9","size":"sm","buttonForm":"block"} /--></div>
<!-- /wp:codeweber-blocks/widget --></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnClass":"mb-5 mb-lg-0","columnCol":"12","columnColMd":"6","columnColLg":"3"} -->
<div class="mb-5 mb-lg-0 col-12 col-md-6 col-lg-3"><!-- wp:codeweber-blocks/menu {"mode":"wp-menu","wpMenuId":228,"theme":"dark","listType":"unordered","items":[{"id":"item-95538a838dd1460b9a6ab356c40900cc-1769951526459-0-324","text":"Корпоративный сектор","url":"https://horizons.z-webstore.ru/practice_category/enterprise/"},{"id":"item-95538a838dd1460b9a6ab356c40900cc-1769951526459-1-823","text":"Налоги, Трудовые отношения","url":"https://horizons.z-webstore.ru/practice_category/tax-employment-and-employee-benefits/"},{"id":"item-95538a838dd1460b9a6ab356c40900cc-1769951526459-2-539","text":"Разрешение споров","url":"https://horizons.z-webstore.ru/practice_category/litigations-and-dispute-resolution/"},{"id":"item-95538a838dd1460b9a6ab356c40900cc-1769951526459-3-557","text":"Финансы и инвестиции","url":"https://horizons.z-webstore.ru/practice_category/finance/"},{"id":"item-95538a838dd1460b9a6ab356c40900cc-1769951526459-4-373","text":"Сопровождение ВЭД","url":"https://horizons.z-webstore.ru/practice_category/foreign-economic-activity/"}],"menuClass":"list-unstyled text-reset mb-0 g-0","itemClass":"mb-0","linkClass":"mb-0","enableWidget":true,"enableTitle":true,"title":"Практики","titleTag":"div","titleColor":"light","titleSize":"h4","titleWeight":"fw-semibold","titleClass":"h4 widget-title text-white mb-3"} /--></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnClass":"mb-5 mb-lg-0","columnCol":"12","columnColMd":"6","columnColLg":"3"} -->
<div class="mb-5 mb-lg-0 col-12 col-md-6 col-lg-3"><!-- wp:codeweber-blocks/menu {"mode":"wp-menu","wpMenuId":229,"theme":"dark","listType":"unordered","items":[{"id":"item-6682888830f94317a0cee76dabf44040-1769951526467-0-264","text":"Партнеры","url":"https://horizons.z-webstore.ru/partners/"},{"id":"item-6682888830f94317a0cee76dabf44040-1769951526467-1-332","text":"Награды и публикации","url":"https://horizons.z-webstore.ru/awards/"},{"id":"item-6682888830f94317a0cee76dabf44040-1769951526467-2-557","text":"Вакансии","url":"https://horizons.z-webstore.ru/vacancies/"},{"id":"item-6682888830f94317a0cee76dabf44040-1769951526467-3-811","text":"Юридические документы","url":"https://horizons.z-webstore.ru/legal/"},{"id":"item-6682888830f94317a0cee76dabf44040-1769951526467-4-106","text":"Блог","url":"https://horizons.z-webstore.ru/blog/"},{"id":"item-6682888830f94317a0cee76dabf44040-1769951526467-5-727","text":"Контакты","url":"https://horizons.z-webstore.ru/contact/"}],"enableWidget":true,"enableTitle":true,"title":"Информация","titleTag":"div","titleColor":"light","titleSize":"h4","titleWeight":"fw-semibold","titleClass":"h4 widget-title text-white mb-3"} /--></div>
<!-- /wp:codeweber-blocks/column -->

<!-- wp:codeweber-blocks/column {"columnClass":"mb-5 mb-lg-0","columnCol":"12","columnColMd":"6","columnColLg":"3"} -->
<div class="mb-5 mb-lg-0 col-12 col-md-6 col-lg-3"><!-- wp:codeweber-blocks/widget {"title":"Контакты","titleColor":"light"} -->
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
			'title'   => 'Footer_01',
			'slug'   => 'footer-01',
			'content' => cw_demo_get_footer_01_content(),
		),
		array(
			'title'   => 'Footer_02',
			'slug'   => 'footer-02',
			'content' => cw_demo_get_footer_02_content(),
		),
		array(
			'title'   => 'Footer_03',
			'slug'   => 'footer-03',
			'content' => cw_demo_get_footer_03_content(),
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
