<?php
/**
 * Term Thumbnail — загрузка изображения для кастомных таксономий темы.
 *
 * Добавляет поле thumbnail_id (wp_termmeta) на страницы создания/редактирования
 * термина для всех таксономий из CW_TERM_THUMBNAIL_TAXONOMIES.
 *
 * Получение в шаблонах:
 *   cw_get_term_thumbnail_url( $term_id, 'medium' )
 *   cw_get_term_thumbnail_id( $term_id )
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

/**
 * Таксономии, для которых активируется поле изображения.
 * Дочерняя тема может расширить список через фильтр cw_term_thumbnail_taxonomies.
 */
const CW_TERM_THUMBNAIL_TAXONOMIES = [
	'category',
	'post_tag',
	'faq_categories',
	'faq_tag',
	'projects_category',
	'towns',
	'departments',
	'document_category',
	'document_type',
	'event_category',
	'event_format',
	'service_category',
	'types_of_services',
	'clients_category',
	'vacancy_type',
	'vacancy_schedule',
];

/**
 * Возвращает список таксономий с поддержкой thumbnail.
 */
function cw_term_thumbnail_get_taxonomies(): array {
	return (array) apply_filters( 'cw_term_thumbnail_taxonomies', CW_TERM_THUMBNAIL_TAXONOMIES );
}

// ---------------------------------------------------------------------------
// Регистрация хуков для каждой таксономии
// ---------------------------------------------------------------------------

add_action( 'init', 'cw_term_thumbnail_register_hooks', 20 );
function cw_term_thumbnail_register_hooks(): void {
	foreach ( cw_term_thumbnail_get_taxonomies() as $taxonomy ) {
		add_action( "{$taxonomy}_add_form_fields",  'cw_term_thumbnail_add_field' );
		add_action( "{$taxonomy}_edit_form_fields", 'cw_term_thumbnail_edit_field', 10, 2 );
	}
	add_action( 'created_term', 'cw_term_thumbnail_save', 10, 3 );
	add_action( 'edit_term',    'cw_term_thumbnail_save', 10, 3 );
}

// ---------------------------------------------------------------------------
// Поле на странице создания термина
// ---------------------------------------------------------------------------

function cw_term_thumbnail_add_field( string $taxonomy ): void {
	?>
	<div class="form-field term-thumbnail-wrap">
		<label><?php esc_html_e( 'Image', 'codeweber' ); ?></label>
		<div class="cw-term-thumbnail" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">
			<div class="cw-term-thumbnail__preview" style="display:none;">
				<img src="" alt="" style="max-width:150px;height:auto;display:block;margin-bottom:8px;">
			</div>
			<input type="hidden" name="cw_term_thumbnail_id" id="cw_term_thumbnail_id" value="">
			<button type="button" class="button cw-term-thumbnail__upload">
				<?php esc_html_e( 'Upload image', 'codeweber' ); ?>
			</button>
			<button type="button" class="button-link cw-term-thumbnail__remove" style="display:none;margin-left:8px;color:#b32d2e;">
				<?php esc_html_e( 'Remove', 'codeweber' ); ?>
			</button>
		</div>
		<p class="description"><?php esc_html_e( 'Optional thumbnail for this term.', 'codeweber' ); ?></p>
	</div>
	<?php
}

// ---------------------------------------------------------------------------
// Поле на странице редактирования термина
// ---------------------------------------------------------------------------

function cw_term_thumbnail_edit_field( \WP_Term $term, string $taxonomy ): void {
	$thumbnail_id  = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	$thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' ) : '';
	?>
	<tr class="form-field term-thumbnail-wrap">
		<th scope="row">
			<label><?php esc_html_e( 'Image', 'codeweber' ); ?></label>
		</th>
		<td>
			<div class="cw-term-thumbnail" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">
				<div class="cw-term-thumbnail__preview" <?php echo $thumbnail_url ? '' : 'style="display:none;"'; ?>>
					<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="" style="max-width:150px;height:auto;display:block;margin-bottom:8px;">
				</div>
				<input type="hidden" name="cw_term_thumbnail_id" id="cw_term_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ?: '' ); ?>">
				<button type="button" class="button cw-term-thumbnail__upload">
					<?php echo $thumbnail_url ? esc_html__( 'Change image', 'codeweber' ) : esc_html__( 'Upload image', 'codeweber' ); ?>
				</button>
				<button type="button" class="button-link cw-term-thumbnail__remove" <?php echo $thumbnail_url ? '' : 'style="display:none;"'; ?> style="margin-left:8px;color:#b32d2e;">
					<?php esc_html_e( 'Remove', 'codeweber' ); ?>
				</button>
			</div>
			<p class="description"><?php esc_html_e( 'Optional thumbnail for this term.', 'codeweber' ); ?></p>
		</td>
	</tr>
	<?php
}

// ---------------------------------------------------------------------------
// Сохранение
// ---------------------------------------------------------------------------

function cw_term_thumbnail_save( int $term_id, int $_tt_id, string $taxonomy ): void {
	if ( ! in_array( $taxonomy, cw_term_thumbnail_get_taxonomies(), true ) ) {
		return;
	}
	if ( ! isset( $_POST['cw_term_thumbnail_id'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	$image_id = absint( $_POST['cw_term_thumbnail_id'] );

	if ( $image_id ) {
		update_term_meta( $term_id, 'thumbnail_id', $image_id );
	} else {
		delete_term_meta( $term_id, 'thumbnail_id' );
	}
}

// ---------------------------------------------------------------------------
// Enqueue wp.media на страницах таксономий
// ---------------------------------------------------------------------------

add_action( 'admin_enqueue_scripts', 'cw_term_thumbnail_enqueue' );
function cw_term_thumbnail_enqueue( string $hook ): void {
	if ( ! in_array( $hook, [ 'edit-tags.php', 'term.php' ], true ) ) {
		return;
	}

	$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( $_GET['taxonomy'] ) : '';
	if ( $taxonomy && ! in_array( $taxonomy, cw_term_thumbnail_get_taxonomies(), true ) ) {
		return;
	}

	wp_enqueue_media();

	$js = <<<'JS'
(function ($) {
    'use strict';

    function initTermThumbnail($wrap) {
        if ($wrap.data('cw-thumb-init')) return;
        $wrap.data('cw-thumb-init', true);

        var $hidden  = $wrap.find('input[name="cw_term_thumbnail_id"]');
        var $preview = $wrap.find('.cw-term-thumbnail__preview');
        var $img     = $preview.find('img');
        var $upload  = $wrap.find('.cw-term-thumbnail__upload');
        var $remove  = $wrap.find('.cw-term-thumbnail__remove');
        var frame;

        $upload.on('click', function (e) {
            e.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: cwTermThumbnail.l10n.select,
                button: { text: cwTermThumbnail.l10n.use },
                multiple: false,
                library: { type: 'image' }
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                var url = attachment.sizes && attachment.sizes.thumbnail
                    ? attachment.sizes.thumbnail.url
                    : attachment.url;

                $hidden.val(attachment.id);
                $img.attr('src', url);
                $preview.show();
                $remove.show();
                $upload.text(cwTermThumbnail.l10n.change);
            });

            frame.open();
        });

        $remove.on('click', function (e) {
            e.preventDefault();
            $hidden.val('');
            $img.attr('src', '');
            $preview.hide();
            $remove.hide();
            $upload.text(cwTermThumbnail.l10n.upload);
        });
    }

    $(function () {
        $('.cw-term-thumbnail').each(function () {
            initTermThumbnail($(this));
        });
    });

    // Поддержка inline-формы добавления термина (AJAX-submit не перезагружает страницу).
    $(document).on('ajaxComplete', function () {
        $('.cw-term-thumbnail').each(function () {
            initTermThumbnail($(this));
        });
        // Сброс поля после успешного создания термина.
        if ($('#ajax-response .notice-success').length) {
            var $wrap = $('.cw-term-thumbnail');
            $wrap.find('input[name="cw_term_thumbnail_id"]').val('');
            $wrap.find('.cw-term-thumbnail__preview').hide();
            $wrap.find('.cw-term-thumbnail__remove').hide();
            $wrap.find('.cw-term-thumbnail__upload').text(cwTermThumbnail.l10n.upload);
        }
    });

}(jQuery));
JS;

	wp_add_inline_script( 'media-upload', $js );

	wp_localize_script( 'media-upload', 'cwTermThumbnail', [
		'l10n' => [
			'select' => __( 'Select image', 'codeweber' ),
			'use'    => __( 'Use this image', 'codeweber' ),
			'change' => __( 'Change image', 'codeweber' ),
			'upload' => __( 'Upload image', 'codeweber' ),
		],
	] );
}

// ---------------------------------------------------------------------------
// Публичные хелперы для шаблонов
// ---------------------------------------------------------------------------

/**
 * Возвращает ID вложения (attachment) для термина.
 *
 * @param int $term_id
 * @return int  0 если изображение не задано.
 */
function cw_get_term_thumbnail_id( int $term_id ): int {
	return (int) get_term_meta( $term_id, 'thumbnail_id', true );
}

/**
 * Возвращает URL изображения термина или пустую строку.
 *
 * @param int    $term_id
 * @param string $size  Размер изображения (thumbnail, medium, large, full …).
 * @return string
 */
function cw_get_term_thumbnail_url( int $term_id, string $size = 'thumbnail' ): string {
	$id = cw_get_term_thumbnail_id( $term_id );
	if ( ! $id ) {
		return '';
	}
	return (string) wp_get_attachment_image_url( $id, $size );
}

/**
 * Выводит тег <img> для изображения термина.
 *
 * @param int    $term_id
 * @param string $size
 * @param array  $attr  Дополнительные атрибуты для wp_get_attachment_image().
 */
function cw_term_thumbnail( int $term_id, string $size = 'medium', array $attr = [] ): void {
	$id = cw_get_term_thumbnail_id( $term_id );
	if ( $id ) {
		echo wp_get_attachment_image( $id, $size, false, $attr );
	}
}
