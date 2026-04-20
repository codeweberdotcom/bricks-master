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
