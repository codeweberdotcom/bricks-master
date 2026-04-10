<?php
/**
 * Projects — Main Information Metabox
 *
 * Поля для миграции с ACF. Ключи мета совпадают с ACF-ключами.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

// ── Регистрация метабокса ─────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'cw_project_main_information',
		__( 'Main Information', 'codeweber' ),
		'cw_project_main_information_render',
		'projects',
		'normal',
		'high'
	);
} );

// ── Рендер ───────────────────────────────────────────────────────────────────

function cw_project_main_information_render( WP_Post $post ): void {
	wp_nonce_field( 'cw_project_main_information_save', 'cw_project_main_information_nonce' );

	$fields = [
		'main_information_address'           => __( 'Адрес', 'codeweber' ),
		'main_information_architector'        => __( 'Архитектор', 'codeweber' ),
		'main_information_developer'          => __( 'Застройщик', 'codeweber' ),
		'main_information_date'               => __( 'Год / Дата', 'codeweber' ),
		'main_information_link'               => __( 'Ссылка', 'codeweber' ),
		'main_information_cms'                => __( 'CMS', 'codeweber' ),
		'main_information_short_description'  => __( 'Краткое описание', 'codeweber' ),
		'main_information_title_description'  => __( 'Заголовок описания', 'codeweber' ),
		'main_information_description'        => __( 'Описание', 'codeweber' ),
	];

	$textareas = [
		'main_information_short_description',
		'main_information_description',
	];

	echo '<table class="form-table" style="margin:0;">';

	foreach ( $fields as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );
		$is_textarea = in_array( $key, $textareas, true );
		?>
		<tr>
			<th scope="row" style="width:200px;">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
			</th>
			<td>
				<?php if ( $is_textarea ) : ?>
					<textarea
						id="<?php echo esc_attr( $key ); ?>"
						name="<?php echo esc_attr( $key ); ?>"
						rows="4"
						style="width:100%;"
					><?php echo esc_textarea( $value ); ?></textarea>
				<?php else : ?>
					<input
						type="text"
						id="<?php echo esc_attr( $key ); ?>"
						name="<?php echo esc_attr( $key ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						style="width:100%;"
					>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	echo '</table>';
}

// ── Сохранение ───────────────────────────────────────────────────────────────

add_action( 'save_post_projects', function ( int $post_id, WP_Post $post ) {
	if (
		! isset( $_POST['cw_project_main_information_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cw_project_main_information_nonce'] ) ), 'cw_project_main_information_save' )
	) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = [
		'main_information_address',
		'main_information_architector',
		'main_information_developer',
		'main_information_date',
		'main_information_link',
		'main_information_cms',
		'main_information_short_description',
		'main_information_title_description',
		'main_information_description',
	];

	$textareas = [
		'main_information_short_description',
		'main_information_description',
	];

	foreach ( $fields as $key ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );
		$value = in_array( $key, $textareas, true )
			? sanitize_textarea_field( $raw )
			: sanitize_text_field( $raw );

		update_post_meta( $post_id, $key, $value );
	}
}, 10, 2 );
