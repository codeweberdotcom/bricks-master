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
		'main_information_latitude'           => __( 'Широта', 'codeweber' ),
		'main_information_longitude'          => __( 'Долгота', 'codeweber' ),
		'main_information_zoom'               => __( 'Масштаб карты (по умолч. 15)', 'codeweber' ),
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
		'main_information_latitude',
		'main_information_longitude',
		'main_information_zoom',
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

// ── Метабокс «Выполненные работы» ────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'cw_project_works',
		__( 'Выполненные работы', 'codeweber' ),
		'cw_project_works_render',
		'projects',
		'normal',
		'high'
	);
} );

function cw_project_works_render( WP_Post $post ): void {
	wp_nonce_field( 'cw_project_works_save', 'cw_project_works_nonce' );

	$title = get_post_meta( $post->ID, 'main_information_title_works', true );
	$count = (int) get_post_meta( $post->ID, 'main_information_works', true );
	$items = [];
	for ( $i = 0; $i < $count; $i++ ) {
		$items[] = get_post_meta( $post->ID, 'main_information_works_' . $i . '_work', true );
	}
	if ( empty( $items ) ) {
		$items = [ '' ];
	}
	?>
	<table class="form-table" style="margin:0;">
		<tr>
			<th scope="row" style="width:200px;">
				<label for="main_information_title_works"><?php esc_html_e( 'Заголовок', 'codeweber' ); ?></label>
			</th>
			<td>
				<input type="text" id="main_information_title_works" name="main_information_title_works"
					value="<?php echo esc_attr( $title ); ?>" style="width:100%;">
			</td>
		</tr>
	</table>

	<div id="cw-works-list" style="margin-top:12px;">
		<?php foreach ( $items as $i => $item ) : ?>
		<div class="cw-work-item" style="display:flex;gap:8px;margin-bottom:6px;">
			<input type="text" name="main_information_works_items[]"
				value="<?php echo esc_attr( $item ); ?>"
				style="flex:1;" placeholder="<?php esc_attr_e( 'Пункт работ', 'codeweber' ); ?>">
			<button type="button" class="button cw-work-remove">–</button>
		</div>
		<?php endforeach; ?>
	</div>

	<button type="button" class="button button-secondary" id="cw-work-add" style="margin-top:6px;">
		<?php esc_html_e( '+ Добавить пункт', 'codeweber' ); ?>
	</button>

	<script>
	(function() {
		var list = document.getElementById('cw-works-list');
		document.getElementById('cw-work-add').addEventListener('click', function() {
			var row = document.createElement('div');
			row.className = 'cw-work-item';
			row.style.cssText = 'display:flex;gap:8px;margin-bottom:6px;';
			row.innerHTML = '<input type="text" name="main_information_works_items[]" style="flex:1;" placeholder="<?php echo esc_js( __( 'Пункт работ', 'codeweber' ) ); ?>">'
				+ '<button type="button" class="button cw-work-remove">–</button>';
			list.appendChild(row);
			bindRemove(row.querySelector('.cw-work-remove'));
		});
		function bindRemove(btn) {
			btn.addEventListener('click', function() {
				btn.closest('.cw-work-item').remove();
			});
		}
		list.querySelectorAll('.cw-work-remove').forEach(bindRemove);
	})();
	</script>
	<?php
}

add_action( 'save_post_projects', function ( int $post_id, WP_Post $post ) {
	if (
		! isset( $_POST['cw_project_works_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cw_project_works_nonce'] ) ), 'cw_project_works_save' )
	) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Title
	$title = isset( $_POST['main_information_title_works'] )
		? sanitize_text_field( wp_unslash( $_POST['main_information_title_works'] ) )
		: '';
	update_post_meta( $post_id, 'main_information_title_works', $title );

	// Works list
	$raw_items = isset( $_POST['main_information_works_items'] )
		? (array) wp_unslash( $_POST['main_information_works_items'] )
		: [];

	// Remove old keys beyond new count
	$old_count = (int) get_post_meta( $post_id, 'main_information_works', true );
	$new_count  = 0;

	foreach ( $raw_items as $i => $value ) {
		$value = sanitize_text_field( $value );
		if ( $value === '' ) {
			continue;
		}
		update_post_meta( $post_id, 'main_information_works_' . $new_count . '_work', $value );
		$new_count++;
	}

	// Delete extra old keys
	for ( $i = $new_count; $i < $old_count; $i++ ) {
		delete_post_meta( $post_id, 'main_information_works_' . $i . '_work' );
	}

	update_post_meta( $post_id, 'main_information_works', $new_count );

}, 10, 2 );
