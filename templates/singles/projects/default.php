<?php
/**
 * Template: Single Projects — Default
 *
 * Режим A (pageheader выключен в Redux):
 *   — выводит шапку bg-soft-primary (категория, заголовок, short_description)
 *   — article.mt-n21, container.pb-14.pb-md-16
 *
 * Режим B (pageheader включён в Redux):
 *   — get_pageheader()
 *   — без шапки, без mt-n21, container использует get_content_padding_classes()
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

global $post, $opt_name;
if ( empty( $opt_name ) ) {
	$opt_name = 'redux_demo';
}

$product_id = get_the_ID();

// ── Определяем режим pageheader ───────────────────────────────────────────────
$_ph_option = class_exists( 'Redux' )
	? Redux::get_option( $opt_name, 'single_page_header_select_projects' )
	: '';
if ( $_ph_option === 'default' || empty( $_ph_option ) ) {
	$_ph_option = class_exists( 'Redux' )
		? Redux::get_option( $opt_name, 'global_page_header_model' )
		: '';
}
$has_pageheader = ( $_ph_option !== 'disabled' );

// ── Метаполя ─────────────────────────────────────────────────────────────────
$address           = get_post_meta( $product_id, 'main_information_address', true );
$architector       = get_post_meta( $product_id, 'main_information_architector', true );
$developer         = get_post_meta( $product_id, 'main_information_developer', true );
$date              = get_post_meta( $product_id, 'main_information_date', true );
$link              = get_post_meta( $product_id, 'main_information_link', true );
$cms               = get_post_meta( $product_id, 'main_information_cms', true );
$short_description = get_post_meta( $product_id, 'main_information_short_description', true );
$title_description = get_post_meta( $product_id, 'main_information_title_description', true );
$description       = get_post_meta( $product_id, 'main_information_description', true );

// ── Стили из Redux ───────────────────────────────────────────────────────────
$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';

// ── Категория ─────────────────────────────────────────────────────────────────
$categories    = get_the_terms( $product_id, 'projects_category' );
$category_name = '';
$category_link = '';
if ( $categories && ! is_wp_error( $categories ) ) {
	$cat           = $categories[0];
	$category_name = $cat->name;
	$category_link = get_term_link( $cat );
}

// ── Галерея ───────────────────────────────────────────────────────────────────
$gallery_ids = function_exists( 'codeweber_get_project_gallery_ids' )
	? codeweber_get_project_gallery_ids( $product_id )
	: [];

// ── Выполненные работы ────────────────────────────────────────────────────────
$works_title = get_post_meta( $product_id, 'main_information_title_works', true );
$works_count = (int) get_post_meta( $product_id, 'main_information_works', true );
$works_items = [];
for ( $i = 0; $i < $works_count; $i++ ) {
	$w = get_post_meta( $product_id, 'main_information_works_' . $i . '_work', true );
	if ( $w ) {
		$works_items[] = $w;
	}
}

// ── Featured image ────────────────────────────────────────────────────────────
$thumbnail_id  = get_post_thumbnail_id( $product_id );
$main_img_full = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'codeweber_extralarge' ) : '';

// ── Метаполя для сайдбара ─────────────────────────────────────────────────────
$meta_items = [];
if ( $date )        $meta_items[] = [ 'label' => __( 'Дата', 'codeweber' ),        'value' => esc_html( $date ) ];
if ( $developer )   $meta_items[] = [ 'label' => __( 'Застройщик', 'codeweber' ),  'value' => esc_html( $developer ) ];
if ( $architector ) $meta_items[] = [ 'label' => __( 'Архитектор', 'codeweber' ),  'value' => esc_html( $architector ) ];
if ( $address )     $meta_items[] = [ 'label' => __( 'Адрес', 'codeweber' ),       'value' => esc_html( $address ) ];
if ( $cms )         $meta_items[] = [ 'label' => __( 'CMS', 'codeweber' ),         'value' => esc_html( $cms ) ];
?>

<?php if ( $has_pageheader ) : ?>

	<?php /* ── Режим B: pageheader включён ─────────────────────────────── */ ?>
	<?php get_pageheader(); ?>

	<section class="wrapper wrapper-border">
		<div class="container <?php echo esc_attr( get_content_padding_classes() ); ?>">
			<div class="row">
				<div class="col-12">
					<article>

<?php else : ?>

	<?php /* ── Режим A: pageheader выключен — показываем шапку ────────── */ ?>
	<section class="wrapper text-reset bg-soft-primary">
		<div class="container pt-10 pb-19 pt-md-14 pb-md-22 text-center">
			<div class="row">
				<div class="col-md-10 col-lg-8 col-xl-7 mx-auto">
					<div class="post-header">

						<?php if ( $category_name ) : ?>
						<div class="post-category text-line mb-3">
							<a href="<?php echo esc_url( $category_link ); ?>" class="hover" rel="category">
								<?php echo esc_html( $category_name ); ?>
							</a>
						</div>
						<?php endif; ?>

						<h1 class="display-1 mb-3"><?php the_title(); ?></h1>

						<?php if ( $short_description ) : ?>
						<p class="lead px-md-12 px-lg-12 px-xl-15 px-xxl-18">
							<?php echo esc_html( $short_description ); ?>
						</p>
						<?php endif; ?>

					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="wrapper wrapper-border">
		<div class="container pb-14 pb-md-16">
			<div class="row">
				<div class="col-12">
					<article class="mt-n21">

<?php endif; ?>

					<?php /* Главное фото */ ?>
					<?php if ( $thumbnail_id ) : ?>
					<figure class="hover-scale hover-overlay <?php echo esc_attr( $card_radius ); ?> mb-8 mb-md-12">
						<a href="<?php echo esc_url( $main_img_full ); ?>" data-glightbox data-gallery="project-<?php echo esc_attr( $product_id ); ?>">
							<?php echo wp_get_attachment_image( $thumbnail_id, 'codeweber_extralarge', false, [ 'class' => 'w-100' ] ); ?>
							<span class="hover-icon text-white"><svg fill="currentColor" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg"><path d="M220,128a4.0002,4.0002,0,0,1-4,4H132v84a4,4,0,0,1-8,0V132H40a4,4,0,0,1,0-8h84V40a4,4,0,0,1,8,0v84h84A4.0002,4.0002,0,0,1,220,128Z"></path></svg></span>
						</a>
					</figure>
					<?php endif; ?>

					<?php /* Описание + выполненные работы + метаполя */ ?>
					<?php if ( $title_description || $works_title || $description || ! empty( $works_items ) || ! empty( $meta_items ) || $link ) : ?>
					<div class="row">
						<div class="col-lg-10 offset-lg-1">

							<?php $section_title = $title_description ?: $works_title; ?>
							<?php if ( $section_title ) : ?>
							<h2 class="display-6 mb-4"><?php echo esc_html( $section_title ); ?></h2>
							<?php endif; ?>

							<div class="row gx-0">

								<?php if ( $description || ! empty( $works_items ) ) : ?>
								<div class="col-md-9 text-justify">
									<?php if ( $description ) : ?>
									<?php
									$desc_output = preg_replace( '/<li>\s*<span>(.*?)<\/span>\s*<\/li>/s', '<li>$1</li>', $description );
									echo wp_kses_post( $desc_output );
									?>
									<?php endif; ?>
									<?php if ( ! empty( $works_items ) ) : ?>
									<ul class="unordered-list bullet-primary">
										<?php foreach ( $works_items as $work ) : ?>
										<li><?php echo esc_html( $work ); ?></li>
										<?php endforeach; ?>
									</ul>
									<?php endif; ?>
								</div>
								<?php endif; ?>

								<?php if ( ! empty( $meta_items ) || $link ) : ?>
								<div class="col-md-2 ms-auto">
									<ul class="list-unstyled">
										<?php foreach ( $meta_items as $item ) : ?>
										<li>
											<h5 class="mb-1"><?php echo esc_html( $item['label'] ); ?></h5>
											<p><?php echo $item['value']; ?></p>
										</li>
										<?php endforeach; ?>
									</ul>
									<?php if ( $link ) : ?>
									<a href="<?php echo esc_url( $link ); ?>" class="more hover" target="_blank" rel="noopener">
										<?php esc_html_e( 'Смотреть проект', 'codeweber' ); ?>
									</a>
									<?php endif; ?>
								</div>
								<?php endif; ?>

							</div>
						</div>
					</div>
					<?php endif; ?>

					<?php /* Галерея */ ?>
					<?php if ( ! empty( $gallery_ids ) ) : ?>
					<div class="row mt-5 gx-md-6 gy-6">
						<?php foreach ( $gallery_ids as $img_id ) :
							$full_url  = wp_get_attachment_image_url( $img_id, 'codeweber_extralarge' );
							$thumb_url = wp_get_attachment_image_url( $img_id, 'codeweber_project_900-900' );
							if ( ! $thumb_url ) continue;
						?>
						<div class="item col-md-6">
							<figure class="hover-scale hover-overlay <?php echo esc_attr( $card_radius ); ?>">
								<a href="<?php echo esc_url( $full_url ?: $thumb_url ); ?>"
								   data-glightbox
								   data-gallery="project-<?php echo esc_attr( $product_id ); ?>">
									<?php echo wp_get_attachment_image( $img_id, 'codeweber_project_900-900', false, [ 'class' => 'w-100' ] ); ?>
									<span class="hover-icon text-white"><svg fill="currentColor" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg"><path d="M220,128a4.0002,4.0002,0,0,1-4,4H132v84a4,4,0,0,1-8,0V132H40a4,4,0,0,1,0-8h84V40a4,4,0,0,1,8,0v84h84A4.0002,4.0002,0,0,1,220,128Z"></path></svg></span>
								</a>
							</figure>
						</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>

				</article>
			</div>
		</div>
	</div>
</section>

<?php /* ── Навигация ──────────────────────────────────────────────────────── */ ?>
<section class="wrapper">
	<div class="container py-10">
		<?php codeweber_posts_nav(); ?>
	</div>
</section>
