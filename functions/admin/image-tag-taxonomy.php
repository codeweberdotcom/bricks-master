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
 * Enqueue jQuery UI Autocomplete для .cw-image-tag-input.
 * WP core `suggest` (jQuery plugin) помечен deprecated в WP 6+,
 * надёжнее использовать jquery-ui-autocomplete — он всегда в ядре.
 *
 * Делегированный init через focus — работает и для элементов,
 * создаваемых динамически (Attachment Details modal).
 */
add_action( 'admin_enqueue_scripts', 'cw_image_tag_enqueue_autocomplete' );
function cw_image_tag_enqueue_autocomplete(): void {
	if ( ! is_admin() ) {
		return;
	}
	wp_enqueue_script( 'jquery-ui-autocomplete' );

	// Минимальный стиль для списка подсказок — чтобы был поверх медиа-модалки
	// (у неё z-index около 160000) и выглядел как стандартный WP admin.
	$css = '.ui-autocomplete{position:absolute;z-index:200000;background:#fff;border:1px solid #c3c4c7;box-shadow:0 2px 8px rgba(0,0,0,.1);list-style:none;margin:0;padding:4px 0;max-height:240px;overflow-y:auto;}'
		. '.ui-autocomplete .ui-menu-item{padding:0;}'
		. '.ui-autocomplete .ui-menu-item-wrapper{display:block;padding:6px 12px;cursor:pointer;color:#1d2327;}'
		. '.ui-autocomplete .ui-menu-item-wrapper.ui-state-active{background:#2271b1;color:#fff;}';
	wp_register_style( 'cw-image-tag-autocomplete', false );
	wp_enqueue_style( 'cw-image-tag-autocomplete' );
	wp_add_inline_style( 'cw-image-tag-autocomplete', $css );

	$inline = <<<'JS'
(function($){
	function splitTerms(val){ return val.split(/,\s*/); }

	function init($input){
		if ($input.data('cw-ac-init')) return;
		$input.data('cw-ac-init', true);

		$input.autocomplete({
			minLength: 1,
			delay: 200,
			source: function(request, response){
				var term = splitTerms(request.term).pop();
				if (!term) { response([]); return; }
				$.get(ajaxurl, {
					action: 'ajax-tag-search',
					tax: 'image_tag',
					q: term
				}).done(function(data){
					var items = (data || '').split('\n')
						.map(function(s){ return s.trim(); })
						.filter(Boolean);
					response(items);
				}).fail(function(){ response([]); });
			},
			focus: function(){ return false; }, // не подставлять в input при наведении
			select: function(event, ui){
				var parts = splitTerms(this.value);
				parts.pop();
				parts.push(ui.item.value);
				this.value = parts.join(', ') + ', ';
				return false;
			}
		});
	}

	// Delegated init — срабатывает, когда элемент появляется в DOM.
	$(document).on('focus.cwImageTag', '.cw-image-tag-input', function(){
		init($(this));
	});
})(jQuery);
JS;

	wp_add_inline_script( 'jquery-ui-autocomplete', $inline );
}
