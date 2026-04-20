<?php
/**
 * Таксономия "Теги изображений" (image_tag) для attachments.
 *
 * Позволяет помечать файлы в медиатеке тегами и фильтровать по ним.
 * UI появляется в Edit Attachment, List-mode медиатеки и, через
 * media-cpt-filter.js / .php, в Grid-mode.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'cw_register_image_tag_taxonomy' );
function cw_register_image_tag_taxonomy(): void {
	register_taxonomy(
		'image_tag',
		[ 'attachment' ],
		[
			'labels'            => [
				'name'          => __( 'Image Tags', 'codeweber' ),
				'singular_name' => __( 'Image Tag', 'codeweber' ),
				'menu_name'     => __( 'Image Tags', 'codeweber' ),
				'add_new_item'  => __( 'Add new image tag', 'codeweber' ),
				'edit_item'     => __( 'Edit image tag', 'codeweber' ),
				'search_items'  => __( 'Search image tags', 'codeweber' ),
				'not_found'     => __( 'No image tags found', 'codeweber' ),
			],
			'public'            => true,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'show_tagcloud'     => false,
			'hierarchical'      => false,
			'rewrite'           => [ 'slug' => 'image-tag' ],
		]
	);
}

/**
 * Подмена дефолтного textarea на input с autocomplete в Attachment Details modal.
 * Использует WP core `suggest` (тот же, что в поле тегов стандартного post.php)
 * и core endpoint admin-ajax.php?action=ajax-tag-search.
 */
add_filter( 'attachment_fields_to_edit', 'cw_image_tag_attachment_field', 10, 2 );
function cw_image_tag_attachment_field( array $form_fields, \WP_Post $post ): array {
	if ( ! isset( $form_fields['image_tag'] ) ) {
		return $form_fields;
	}

	$terms = wp_get_object_terms( $post->ID, 'image_tag', [ 'fields' => 'names' ] );
	$value = is_wp_error( $terms ) ? '' : implode( ', ', $terms );

	$form_fields['image_tag']['input'] = 'html';
	$form_fields['image_tag']['html'] = sprintf(
		'<input type="text" class="cw-image-tag-input widefat" id="attachments-%1$d-image_tag" name="attachments[%1$d][image_tag]" value="%2$s" autocomplete="off">',
		(int) $post->ID,
		esc_attr( $value )
	);
	$form_fields['image_tag']['helps'] = __( 'Separate tags with commas', 'codeweber' );

	return $form_fields;
}

/**
 * Сохранение значения из input → wp_set_object_terms.
 */
add_filter( 'attachment_fields_to_save', 'cw_image_tag_attachment_save', 10, 2 );
function cw_image_tag_attachment_save( array $post, array $attachment ): array {
	if ( ! array_key_exists( 'image_tag', $attachment ) ) {
		return $post;
	}
	$raw  = (string) $attachment['image_tag'];
	$tags = array_filter(
		array_map( 'trim', explode( ',', $raw ) ),
		static function ( $s ) { return $s !== ''; }
	);
	wp_set_object_terms( (int) $post['ID'], $tags, 'image_tag', false );
	return $post;
}

/**
 * Enqueue WP core `suggest` и делегированный init для .cw-image-tag-input.
 * Срабатывает при фокусе — значит работает и для элементов, созданных
 * динамически (Attachment Details modal строится на JS после AJAX).
 */
add_action( 'admin_enqueue_scripts', 'cw_image_tag_enqueue_suggest' );
function cw_image_tag_enqueue_suggest(): void {
	if ( ! is_admin() ) {
		return;
	}
	wp_enqueue_script( 'suggest' );

	$inline = <<<'JS'
(function($){
	$(document).on('focus', '.cw-image-tag-input', function(){
		var $i = $(this);
		if ($i.data('suggest-init')) return;
		$i.suggest(ajaxurl + '?action=ajax-tag-search&tax=image_tag', {
			delay: 200,
			multiple: true,
			multipleSep: ', '
		});
		$i.data('suggest-init', true);
	});
})(jQuery);
JS;

	wp_add_inline_script( 'suggest', $inline );
}
