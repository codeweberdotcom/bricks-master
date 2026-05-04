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
		'main_information_city'              => __( 'Город', 'codeweber' ),
		'main_information_address'           => __( 'Адрес', 'codeweber' ),
		'main_information_architector'       => __( 'Архитектор', 'codeweber' ),
		'main_information_developer'         => __( 'Застройщик', 'codeweber' ),
		'main_information_date'              => __( 'Год / Дата', 'codeweber' ),
		'main_information_link'              => __( 'Ссылка', 'codeweber' ),
		'main_information_cms'               => __( 'CMS', 'codeweber' ),
		'main_information_short_description' => __( 'Краткое описание', 'codeweber' ),
		'main_information_title_description' => __( 'Заголовок описания', 'codeweber' ),
		'main_information_description'       => __( 'Описание', 'codeweber' ),
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

	// Поле загрузки изображения объекта
	$img_id = (int) get_post_meta( $post->ID, 'main_information_image', true );
	?>
	<table class="form-table" style="margin:0;">
		<tr>
			<th scope="row" style="width:200px;">
				<label><?php esc_html_e( 'Изображение объекта', 'codeweber' ); ?></label>
			</th>
			<td>
				<div style="display:flex;align-items:flex-start;gap:12px;">
					<div id="cw-project-image-preview" style="<?php echo $img_id ? '' : 'display:none;'; ?>">
						<?php if ( $img_id ) : ?>
							<?php echo wp_get_attachment_image( $img_id, [ 120, 80 ], false, [ 'style' => 'border-radius:4px;' ] ); ?>
						<?php endif; ?>
					</div>
					<div>
						<input type="hidden" id="main_information_image" name="main_information_image" value="<?php echo esc_attr( $img_id ?: '' ); ?>">
						<button type="button" class="button" id="cw-project-image-upload">
							<?php esc_html_e( 'Выбрать изображение', 'codeweber' ); ?>
						</button>
						<button type="button" class="button" id="cw-project-image-remove" style="<?php echo $img_id ? '' : 'display:none;'; ?>margin-left:4px;">
							<?php esc_html_e( 'Удалить', 'codeweber' ); ?>
						</button>
					</div>
				</div>
				<script>
				(function() {
					var frame;
					document.getElementById('cw-project-image-upload').addEventListener('click', function(e) {
						e.preventDefault();
						if (frame) { frame.open(); return; }
						frame = wp.media({
							title: '<?php echo esc_js( __( 'Выбрать изображение объекта', 'codeweber' ) ); ?>',
							button: { text: '<?php echo esc_js( __( 'Использовать', 'codeweber' ) ); ?>' },
							multiple: false,
							library: { type: 'image' }
						});
						frame.on('select', function() {
							var attachment = frame.state().get('selection').first().toJSON();
							document.getElementById('main_information_image').value = attachment.id;
							var preview = document.getElementById('cw-project-image-preview');
							preview.innerHTML = '<img src="' + (attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url) + '" style="border-radius:4px;max-width:120px;max-height:80px;">';
							preview.style.display = '';
							document.getElementById('cw-project-image-remove').style.display = '';
						});
						frame.open();
					});
					document.getElementById('cw-project-image-remove').addEventListener('click', function() {
						document.getElementById('main_information_image').value = '';
						document.getElementById('cw-project-image-preview').style.display = 'none';
						document.getElementById('cw-project-image-preview').innerHTML = '';
						this.style.display = 'none';
					});
				})();
				</script>
			</td>
		</tr>
	</table>
	<?php
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
		'main_information_city',
		'main_information_address',
		'main_information_architector',
		'main_information_developer',
		'main_information_date',
		'main_information_link',
		'main_information_cms',
		'main_information_image',
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

// ── Метабокс «Карта» ─────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'cw_project_map',
		__( 'Карта', 'codeweber' ),
		'cw_project_map_render',
		'projects',
		'normal',
		'high'
	);
} );

function cw_project_map_render( WP_Post $post ): void {
	global $opt_name;
	if ( empty( $opt_name ) ) {
		$opt_name = 'redux_demo';
	}

	$yandex_api_key = class_exists( 'Redux' ) ? Redux::get_option( $opt_name, 'yandexapi' ) : '';
	$latitude       = get_post_meta( $post->ID, 'main_information_latitude', true );
	$longitude      = get_post_meta( $post->ID, 'main_information_longitude', true );
	$zoom           = get_post_meta( $post->ID, 'main_information_zoom', true ) ?: '10';
	$address        = get_post_meta( $post->ID, 'main_information_address', true );
	?>
	<div style="margin-bottom:15px;">
		<?php if ( ! empty( $yandex_api_key ) ) : ?>
		<div style="position:relative;margin-bottom:12px;">
			<input type="text" id="cw-project-map-search" placeholder="<?php esc_attr_e( 'Search address...', 'codeweber' ); ?>"
				style="width:100%;padding:8px;border:1px solid #8c8f94;border-radius:4px;box-sizing:border-box;">
		</div>
		<?php endif; ?>

		<div id="project-yandex-map" style="width:100%;height:400px;margin-bottom:15px;"></div>

		<?php if ( ! empty( $yandex_api_key ) ) : ?>
		<script src="https://api-maps.yandex.ru/v3/?apikey=<?php echo esc_attr( $yandex_api_key ); ?>&lang=ru_RU"></script>
		<script>
		(function() {
			var apiKey    = '<?php echo esc_js( $yandex_api_key ); ?>';
			var geocodeUrl = 'https://geocode-maps.yandex.ru/1.x/?apikey=' + encodeURIComponent(apiKey) + '&format=json&lang=ru_RU';

			ymaps3.ready.then(function() {
				var YMap = ymaps3.YMap, YMapDefaultSchemeLayer = ymaps3.YMapDefaultSchemeLayer,
				    YMapDefaultFeaturesLayer = ymaps3.YMapDefaultFeaturesLayer,
				    YMapMarker = ymaps3.YMapMarker, YMapListener = ymaps3.YMapListener;

				var latField     = document.querySelector("input[name='main_information_latitude']");
				var lngField     = document.querySelector("input[name='main_information_longitude']");
				var zoomField    = document.querySelector("input[name='main_information_zoom']");
				var addressField = document.querySelector("input[name='main_information_address']");
				var searchInput  = document.getElementById('cw-project-map-search');

				var lat  = parseFloat(latField && latField.value ? latField.value : '55.76') || 55.76;
				var lng  = parseFloat(lngField && lngField.value ? lngField.value : '37.64') || 37.64;
				var zoom = parseInt(zoomField && zoomField.value ? zoomField.value : '<?php echo esc_js( $zoom ); ?>') || 10;

				var map = new YMap(document.getElementById('project-yandex-map'), {
					location: { center: [lng, lat], zoom: zoom }
				});
				map.addChild(new YMapDefaultSchemeLayer());
				map.addChild(new YMapDefaultFeaturesLayer());

				var el = document.createElement('div');
				el.style.cssText = 'cursor:grab;width:28px;height:28px;transform:translate(-50%,-100%)';
				el.innerHTML = '<svg viewBox="0 0 24 24" fill="#d63638" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>';

				var marker = new YMapMarker({
					coordinates: [lng, lat],
					draggable: true,
					onDragEnd: function(coords) { syncFields(coords[1], coords[0]); }
				}, el);
				map.addChild(marker);

				map.addChild(new YMapListener({
					onClick: function(obj, event) {
						var coords = event && event.coordinates ? event.coordinates : null;
						if (!coords) return;
						marker.update({ coordinates: coords });
						syncFields(coords[1], coords[0]);
					}
				}));

				map.addChild(new YMapListener({
					onActionEnd: function() {
						if (zoomField) { zoomField.value = Math.round(map.zoom); zoomField.dispatchEvent(new Event('input',{bubbles:true})); }
					}
				}));

				function reverseGeocode(latVal, lngVal) {
					if (!addressField) return;
					fetch(geocodeUrl + '&geocode=' + encodeURIComponent(lngVal + ',' + latVal) + '&results=1')
						.then(function(r) { return r.json(); })
						.then(function(d) {
							var fm = d.response && d.response.GeoObjectCollection && d.response.GeoObjectCollection.featureMember;
							if (fm && fm.length) {
								addressField.value = fm[0].GeoObject.metaDataProperty.GeocoderMetaData.text;
								addressField.dispatchEvent(new Event('input', {bubbles:true}));
							}
						}).catch(function() {});
				}

				function syncFields(latVal, lngVal) {
					if (latField)  { latField.value  = latVal; latField.dispatchEvent(new Event('input',{bubbles:true})); }
					if (lngField)  { lngField.value  = lngVal; lngField.dispatchEvent(new Event('input',{bubbles:true})); }
					if (zoomField) { zoomField.value = Math.round(map.zoom); zoomField.dispatchEvent(new Event('input',{bubbles:true})); }
					reverseGeocode(latVal, lngVal);
				}

				function geocodeAndMove(query) {
					if (!query) return;
					fetch(geocodeUrl + '&geocode=' + encodeURIComponent(query) + '&results=1')
						.then(function(r) { return r.json(); })
						.then(function(d) {
							var fm = d.response && d.response.GeoObjectCollection && d.response.GeoObjectCollection.featureMember;
							if (!fm || !fm.length) return;
							var pos = fm[0].GeoObject.Point.pos.split(' ');
							var fLng = parseFloat(pos[0]), fLat = parseFloat(pos[1]);
							if (isNaN(fLat) || isNaN(fLng)) return;
							marker.update({ coordinates: [fLng, fLat] });
							map.update({ location: { center: [fLng, fLat], zoom: 15 } });
							syncFields(fLat, fLng);
						}).catch(function() {});
				}

				function initSuggest(input) {
					var wrap = input.parentNode;
					var drop = document.createElement('div');
					drop.style.cssText = 'display:none;position:absolute;z-index:99999;left:0;right:0;top:100%;background:#fff;border:1px solid #c3c4c7;border-top:none;border-radius:0 0 4px 4px;box-shadow:0 4px 8px rgba(0,0,0,.12);max-height:220px;overflow-y:auto;font-size:13px;';
					wrap.appendChild(drop);
					var timer, active = -1;
					function hide() { drop.style.display = 'none'; active = -1; }
					function hl(i) { active = i; Array.from(drop.children).forEach(function(c,j){c.style.background=j===i?'#f0f7ff':'';}); }
					function pick(t, s) { input.value = t + (s ? ', '+s : ''); hide(); geocodeAndMove(input.value); }
					function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
					input.addEventListener('input', function() {
						clearTimeout(timer);
						var q = input.value.trim();
						if (q.length < 2) { hide(); return; }
						timer = setTimeout(function() {
							fetch('https://suggest-maps.yandex.ru/v1/suggest?apikey=' + encodeURIComponent(apiKey) + '&text=' + encodeURIComponent(q) + '&lang=ru_RU&results=5&types=house,street,locality')
								.then(function(r) { return r.json(); })
								.then(function(d) {
									drop.innerHTML = '';
									var items = (d.results || []).filter(function(r) { return r.title && r.title.text; });
									if (!items.length) { hide(); return; }
									items.forEach(function(r, i) {
										var t = r.title.text, s = r.subtitle && r.subtitle.text ? r.subtitle.text : '';
										var div = document.createElement('div');
										div.style.cssText = 'padding:7px 12px;cursor:pointer;border-bottom:1px solid #f0f0f1;line-height:1.3;';
										div.innerHTML = '<span style="font-weight:600">'+esc(t)+'</span>'+(s?'<br><span style="color:#777;font-size:12px">'+esc(s)+'</span>':'');
										div.addEventListener('mousedown', function(e) { e.preventDefault(); pick(t, s); });
										div.addEventListener('mouseover', function() { hl(i); });
										drop.appendChild(div);
									});
									drop.style.display = 'block';
								}).catch(function() {});
						}, 250);
					});
					input.addEventListener('keydown', function(e) {
						if (e.key === 'ArrowDown') { e.preventDefault(); hl(Math.min(active+1, drop.children.length-1)); }
						else if (e.key === 'ArrowUp') { e.preventDefault(); hl(Math.max(active-1, 0)); }
						else if (e.key === 'Enter') {
							e.preventDefault();
							if (active >= 0 && drop.children[active]) drop.children[active].dispatchEvent(new MouseEvent('mousedown',{bubbles:true}));
							else geocodeAndMove(input.value.trim());
							hide();
						} else if (e.key === 'Escape') { hide(); }
					});
					input.addEventListener('blur', function() { setTimeout(hide, 200); });
				}

				if (searchInput) initSuggest(searchInput);
			});
		})();
		</script>
		<?php else : ?>
		<p style="color:#d63638;padding:10px;background:#fcf0f1;border-left:4px solid #d63638;">
			<?php esc_html_e( 'Yandex Maps API key is not configured. Set it in Redux settings.', 'codeweber' ); ?>
		</p>
		<?php endif; ?>
	</div>

	<div style="margin-bottom:12px;">
		<label for="main_information_address" style="display:block;margin-bottom:5px;font-weight:bold;">
			<?php esc_html_e( 'Address', 'codeweber' ); ?>
		</label>
		<input type="text" id="main_information_address" name="main_information_address"
			value="<?php echo esc_attr( $address ); ?>" style="width:100%;padding:8px;">
	</div>

	<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
		<div>
			<label for="main_information_latitude" style="display:block;margin-bottom:5px;font-weight:bold;">
				<?php esc_html_e( 'Latitude', 'codeweber' ); ?>
			</label>
			<input type="number" step="any" id="main_information_latitude" name="main_information_latitude"
				value="<?php echo esc_attr( $latitude ); ?>" style="width:100%;padding:8px;" placeholder="55.7558">
		</div>
		<div>
			<label for="main_information_longitude" style="display:block;margin-bottom:5px;font-weight:bold;">
				<?php esc_html_e( 'Longitude', 'codeweber' ); ?>
			</label>
			<input type="number" step="any" id="main_information_longitude" name="main_information_longitude"
				value="<?php echo esc_attr( $longitude ); ?>" style="width:100%;padding:8px;" placeholder="37.6173">
		</div>
		<div>
			<label for="main_information_zoom" style="display:block;margin-bottom:5px;font-weight:bold;">
				<?php esc_html_e( 'Zoom (1-19)', 'codeweber' ); ?>
			</label>
			<input type="number" id="main_information_zoom" name="main_information_zoom"
				value="<?php echo esc_attr( $zoom ); ?>" min="1" max="19" style="width:100%;padding:8px;" placeholder="10">
		</div>
	</div>
	<?php
}

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

// ── Метабокс «Товары проекта» ────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'cw_project_products',
		__( 'Товары проекта', 'codeweber' ),
		'cw_project_products_render',
		'projects',
		'normal',
		'default'
	);
} );

function cw_project_products_render( WP_Post $post ): void {
	wp_nonce_field( 'cw_project_products_save', 'cw_project_products_nonce' );

	$saved_ids = get_post_meta( $post->ID, 'main_information_products', true );
	if ( ! is_array( $saved_ids ) ) {
		$saved_ids = [];
	}

	$saved_products = [];
	foreach ( $saved_ids as $pid ) {
		$pid = (int) $pid;
		if ( ! $pid ) continue;
		$p = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
		if ( $p ) {
			$img_url = $p->get_image_id() ? wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ) : '';
			$saved_products[] = [
				'id'    => $pid,
				'title' => $p->get_name(),
				'thumb' => $img_url,
			];
		}
	}
	?>
	<input type="hidden" id="cw-products-ids" name="main_information_products_ids" value="<?php echo esc_attr( implode( ',', array_column( $saved_products, 'id' ) ) ); ?>">

	<div style="margin-bottom:10px;position:relative;">
		<input type="text" id="cw-products-search" placeholder="<?php esc_attr_e( 'Поиск товара...', 'codeweber' ); ?>" style="width:100%;padding:8px;" autocomplete="off">
		<div id="cw-products-dropdown" style="display:none;border:1px solid #ddd;background:#fff;max-height:220px;overflow-y:auto;position:absolute;z-index:9999;width:100%;top:100%;left:0;"></div>
	</div>

	<div id="cw-products-selected" style="display:flex;flex-wrap:wrap;gap:8px;">
		<?php foreach ( $saved_products as $sp ) : ?>
		<div class="cw-product-tag" data-id="<?php echo esc_attr( $sp['id'] ); ?>" style="display:flex;align-items:center;gap:6px;background:#f0f0f0;border:1px solid #ddd;border-radius:4px;padding:4px 8px;">
			<?php if ( $sp['thumb'] ) : ?>
				<img src="<?php echo esc_url( $sp['thumb'] ); ?>" style="width:28px;height:28px;object-fit:cover;border-radius:3px;">
			<?php endif; ?>
			<span><?php echo esc_html( $sp['title'] ); ?></span>
			<button type="button" class="cw-product-remove" style="background:none;border:none;cursor:pointer;font-size:16px;line-height:1;color:#999;" aria-label="<?php esc_attr_e( 'Удалить', 'codeweber' ); ?>">×</button>
		</div>
		<?php endforeach; ?>
	</div>

	<script>
	(function() {
		var searchInput = document.getElementById('cw-products-search');
		var dropdown    = document.getElementById('cw-products-dropdown');
		var selected    = document.getElementById('cw-products-selected');
		var idsInput    = document.getElementById('cw-products-ids');
		var searchTimer;
		var nonce       = '<?php echo esc_js( wp_create_nonce( 'cw_search_products_nonce' ) ); ?>';

		function syncIds() {
			idsInput.value = Array.from(selected.querySelectorAll('.cw-product-tag')).map(function(el) {
				return el.dataset.id;
			}).join(',');
		}

		function addTag(id, title, thumb) {
			if (selected.querySelector('[data-id="' + id + '"]')) return;
			var tag = document.createElement('div');
			tag.className = 'cw-product-tag';
			tag.dataset.id = id;
			tag.style.cssText = 'display:flex;align-items:center;gap:6px;background:#f0f0f0;border:1px solid #ddd;border-radius:4px;padding:4px 8px;';
			tag.innerHTML = (thumb ? '<img src="' + thumb + '" style="width:28px;height:28px;object-fit:cover;border-radius:3px;">' : '')
				+ '<span>' + title + '</span>'
				+ '<button type="button" class="cw-product-remove" style="background:none;border:none;cursor:pointer;font-size:16px;line-height:1;color:#999;">×</button>';
			tag.querySelector('.cw-product-remove').addEventListener('click', function() {
				tag.remove(); syncIds();
			});
			selected.appendChild(tag);
			syncIds();
		}

		selected.querySelectorAll('.cw-product-remove').forEach(function(btn) {
			btn.addEventListener('click', function() { btn.closest('.cw-product-tag').remove(); syncIds(); });
		});

		searchInput.addEventListener('input', function() {
			clearTimeout(searchTimer);
			var q = searchInput.value.trim();
			if (q.length < 2) { dropdown.style.display = 'none'; return; }
			searchTimer = setTimeout(function() {
				var xhr = new XMLHttpRequest();
				xhr.open('GET', ajaxurl + '?action=cw_search_products&nonce=' + nonce + '&term=' + encodeURIComponent(q));
				xhr.onload = function() {
					if (xhr.status !== 200) return;
					var res = JSON.parse(xhr.responseText);
					if (!res.success || !res.data.length) {
						dropdown.innerHTML = '<div style="padding:8px;color:#999;"><?php echo esc_js( __( 'Ничего не найдено', 'codeweber' ) ); ?></div>';
						dropdown.style.display = 'block'; return;
					}
					dropdown.innerHTML = '';
					res.data.forEach(function(item) {
						var row = document.createElement('div');
						row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:6px 10px;cursor:pointer;border-bottom:1px solid #eee;';
						row.onmouseenter = function() { row.style.background = '#f7f7f7'; };
						row.onmouseleave = function() { row.style.background = ''; };
						row.innerHTML = (item.thumbnail ? '<img src="' + item.thumbnail + '" style="width:32px;height:32px;object-fit:cover;border-radius:3px;">' : '')
							+ '<span>' + item.title + '</span>';
						row.addEventListener('click', function() {
							addTag(item.id, item.title, item.thumbnail);
							searchInput.value = '';
							dropdown.style.display = 'none';
						});
						dropdown.appendChild(row);
					});
					dropdown.style.display = 'block';
				};
				xhr.send();
			}, 300);
		});

		document.addEventListener('click', function(e) {
			if (!dropdown.contains(e.target) && e.target !== searchInput) dropdown.style.display = 'none';
		});
	})();
	</script>
	<?php
}

add_action( 'save_post_projects', function ( int $post_id, WP_Post $post ) {
	if (
		! isset( $_POST['cw_project_products_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cw_project_products_nonce'] ) ), 'cw_project_products_save' )
	) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	$raw = isset( $_POST['main_information_products_ids'] )
		? sanitize_text_field( wp_unslash( $_POST['main_information_products_ids'] ) )
		: '';
	$ids = array_filter( array_map( 'intval', $raw !== '' ? explode( ',', $raw ) : [] ) );
	update_post_meta( $post_id, 'main_information_products', array_values( $ids ) );
}, 10, 2 );

// ── Hotspot Annotation Editor для Projects ────────────────────────────────────

add_filter( 'cw_hotspot_extra_post_types', function ( array $types ) {
	$types['projects'] = [
		'image_meta_key'    => '_thumbnail_id',
		'data_meta_key'     => '_project_hotspot_data',
		'settings_meta_key' => '_project_hotspot_settings',
		'show_image_upload' => false,
		'nonce_action'      => 'save_project_hotspot',
		'nonce_field'       => 'cw_project_hotspot_nonce',
		'metabox_title'     => __( 'Hotspot Annotation', 'codeweber' ),
		'enable_toggle'     => true,
		'enable_meta_key'   => '_project_hotspot_enabled',
	];
	return $types;
} );

add_action( 'save_post_projects', function ( int $post_id, WP_Post $post ) {
	// DEBUG — временно, удалить после диагностики
	error_log( '[HOTSPOT DEBUG] save_post_projects fired. post_id=' . $post_id );
	error_log( '[HOTSPOT DEBUG] nonce present: ' . ( isset( $_POST['cw_project_hotspot_nonce'] ) ? 'YES' : 'NO' ) );

	if (
		! isset( $_POST['cw_project_hotspot_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cw_project_hotspot_nonce'] ) ), 'save_project_hotspot' )
	) {
		error_log( '[HOTSPOT DEBUG] nonce FAILED — exiting.' );
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		error_log( '[HOTSPOT DEBUG] autosave — skipping.' );
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	error_log( '[HOTSPOT DEBUG] _project_hotspot_settings POST value: ' . ( isset( $_POST['_project_hotspot_settings'] ) ? $_POST['_project_hotspot_settings'] : 'NOT SET' ) );

	update_post_meta( $post_id, '_project_hotspot_enabled', isset( $_POST['_project_hotspot_enabled'] ) ? 1 : 0 );

	if ( isset( $_POST['_project_hotspot_data'] ) ) {
		$data = stripslashes( $_POST['_project_hotspot_data'] );
		json_decode( $data );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			update_post_meta( $post_id, '_project_hotspot_data', wp_slash( $data ) );
		}
	}

	if ( isset( $_POST['_project_hotspot_settings'] ) ) {
		$settings = stripslashes( $_POST['_project_hotspot_settings'] );
		json_decode( $settings );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			update_post_meta( $post_id, '_project_hotspot_settings', wp_slash( $settings ) );
			error_log( '[HOTSPOT DEBUG] settings SAVED OK: ' . $settings );
		} else {
			error_log( '[HOTSPOT DEBUG] settings JSON invalid: ' . json_last_error_msg() );
		}
	}
}, 10, 2 );

// ── AJAX: поиск товаров ───────────────────────────────────────────────────────

add_action( 'wp_ajax_cw_search_products', function () {
	check_ajax_referer( 'cw_search_products_nonce', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( [], 403 );
	}
	if ( ! function_exists( 'wc_get_products' ) ) {
		wp_send_json_error( [] );
	}
	$term     = sanitize_text_field( wp_unslash( $_GET['term'] ?? '' ) );
	$products = wc_get_products( [ 's' => $term, 'status' => 'publish', 'limit' => 12 ] );
	$results  = [];
	foreach ( $products as $p ) {
		$img_id    = $p->get_image_id();
		$results[] = [
			'id'        => $p->get_id(),
			'title'     => $p->get_name(),
			'thumbnail' => $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '',
		];
	}
	wp_send_json_success( $results );
} );
