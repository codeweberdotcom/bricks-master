<?php


/**
 * Убирает пункт «Приватность» из меню «Настройки» в админке WordPress.
 *
 * Хук: admin_menu
 *
 * Логика:
 * - remove_submenu_page() удаляет подстраницы из меню «Настройки».
 * - Учитываются разные версии WordPress:
 *     - 'privacy.php' — старые версии (до WP 4.9.6).
 *     - 'options-privacy.php' — новые версии (после WP 4.9.6).
 *
 * Приоритет 999 — чтобы код сработал после того, как WordPress добавит все пункты меню.
 *
 * @return void
 */
// add_action('admin_menu', function () {
// 	remove_submenu_page('options-general.php', 'privacy.php'); // Старые версии WP
// 	remove_submenu_page('options-general.php', 'options-privacy.php'); // Новые версии WP
// }, 999);



// Регистрируем выпадающий список CPT 'legal'
function codeweber_dropdown_legal_posts($name, $selected = 0)
{
	$posts = get_posts([
		'post_type'      => 'legal',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
	]);

	echo '<select name="' . esc_attr($name) . '">';
	echo '<option value="0">' . __('— Select —', 'codeweber') . '</option>';

	foreach ($posts as $post) {
		printf(
			'<option value="%d"%s>%s</option>',
			$post->ID,
			selected($selected, $post->ID, false),
			esc_html($post->post_title)
		);
	}

	echo '</select>';
}

// Регистрируем раздел настроек
add_action('admin_menu', function () {
	add_options_page(
		__('Legal Settings', 'codeweber'),
		__('Legal Settings', 'codeweber'),
		'manage_options',
		'codeweber-legal-settings',
		'codeweber_legal_settings_page'
	);
});

/**
 * Регистрирует административные настройки для выбора юридических страниц (например, политика конфиденциальности, cookie и т.д.).
 *
 * Использует функцию `codeweber_get_legal_fields()` для получения массива полей, где каждое поле включает:
 * - 'id'   — ID параметра в БД (используется в get_option и register_setting)
 * - 'title' — Заголовок поля
 * - 'slug' — Слаг для генерации шорткода
 *
 * Для каждого поля:
 * - Регистрируется настройка (`register_setting`)
 * - Добавляется секция и поле (`add_settings_section`, `add_settings_field`)
 * - В качестве контента поля используется select-дропдаун с доступными страницами (через `codeweber_dropdown_legal_posts`)
 * - Добавляется кнопка "Создать" для генерации новой страницы
 * - Генерируется шорткод в виде `[url_{$slug}]`, который можно вставить на другие страницы
 *
 * Хук: `admin_init`
 *
 * Зависит от:
 * - codeweber_get_legal_fields() — функция, возвращающая массив полей с id, title, slug
 * - codeweber_dropdown_legal_posts($field_id, $selected_id) — функция, которая рендерит выпадающий список с выбором страницы
 *
 * Пример структуры поля:
 * [
 *   'id' => 'privacy_policy_page_id',
 *   'title' => 'Политика конфиденциальности',
 *   'slug' => 'privacy_policy'
 * ]
 *
 * Пример шорткода: [url_privacy_policy]
 *
 * @since 1.0.0
 */
add_action('admin_init', function () {
	$fields = codeweber_get_legal_fields();

	// Общая секция с текстом
	add_settings_section(
		'codeweber_legal_section',
		'',
		function () {
			echo '<p style="margin-bottom:15px; font-weight:500; color:#333;">'
				. __('Select or create pages for legal documents. These pages will be used in the plugin\'s templates and shortcodes. Some pages have pre-filled templates, and the template is created when the page is created. There is a checkbox to the right of the fields that allows you to hide the document from the Legal Documents archive page.', 'codeweber')
				. '</p>';
		},
		'codeweber-legal-settings'
	);

	foreach ($fields as $field) {
		register_setting('codeweber_legal_settings', $field['id']);

		add_settings_field(
			$field['id'],
			$field['title'],
			function () use ($field) {
				$page_id = get_option($field['id'], 0);
				codeweber_dropdown_legal_posts($field['id'], $page_id);

				// Кнопка создания
				echo '<button type="submit" name="create_page" value="' . esc_attr($field['id']) . '" class="button" style="margin-left:10px;">' . __('Create', 'codeweber') . '</button>';

				// Чекбокс
				$hide_checked = ($page_id && get_post_meta($page_id, '_hide_from_archive', true) == '1') ? 'checked' : '';
				echo '<label style="margin-left:10px;">
					<input type="checkbox" name="hide_from_archive_' . esc_attr($field['id']) . '" value="1" ' . $hide_checked . '>
					' . __('Hide from archive', 'codeweber') . '
				</label>';

				// Шорткод
				$shortcode = '[url_' . esc_attr($field['slug']) . ']';
				echo '<p style="margin-top:8px; font-style: italic; color: #555;">'
					. __('Shortcode:', 'codeweber') . ' <code>' . $shortcode . '</code></p>';
			},
			'codeweber-legal-settings',
			'codeweber_legal_section'
		);
	}
});

/**
 * Обрабатывает создание новой юридической страницы при нажатии кнопки "Create" в настройках.
 *
 * Работает на хук `admin_init` и выполняет следующие шаги:
 * 1. Проверяет:
 *    - наличие POST-параметра `create_page`
 *    - наличие прав `manage_options`
 *    - наличие соответствующего поля в конфигурации `codeweber_get_legal_fields()`
 *
 * 2. Если условия выполнены — создаёт новую запись типа `legal` (Custom Post Type) с данными:
 *    - post_title   ← `$field['title']`
 *    - post_name    ← `$field['slug']`
 *    - post_content ← `$field['content']`
 *    - post_type    ← `legal`
 *    - post_status  ← `publish`
 *
 * 3. После успешного создания:
 *    - Сохраняет ID страницы в настройке WordPress (`update_option`) с ключом из `$field['id']`
 *    - Сохраняет в метаполе `_hide_from_archive` значение чекбокса "Скрыть из архива" (1 или 0)
 *    - Если `slug` равен `privacy-policy`, автоматически назначает страницу как
 *      официальную "Политику конфиденциальности" (`update_option( 'wp_page_for_privacy_policy', $id )`)
 *
 * 4. Перенаправляет пользователя обратно на страницу настроек плагина (`codeweber-legal-settings`)
 *
 * Ожидаемый формат элемента в `codeweber_get_legal_fields()`:
 * [
 *   'id'      => 'privacy_policy_page_id',     // ключ опции в БД
 *   'title'   => 'Политика конфиденциальности', // заголовок страницы
 *   'slug'    => 'privacy-policy',              // slug страницы
 *   'content' => 'Шаблонный текст страницы',    // содержимое
 * ]
 *
 * Требования:
 * - Зарегистрирован CPT `legal`
 * - Пользователь с правами `manage_options`
 *
 * Безопасность:
 * - Очистка входных данных (`sanitize_text_field`)
 * - Проверка, что ID поля присутствует в конфигурации
 *
 * @since 1.0.0
 */
add_action('admin_init', function () {
	if (!current_user_can('manage_options') || !isset($_POST['create_page'])) {
		return;
	}

	$field_id = sanitize_text_field($_POST['create_page']);
	$fields   = codeweber_get_legal_fields();

	foreach ($fields as $field) {
		if ($field['id'] === $field_id) {
			$new_page_id = wp_insert_post([
				'post_title'   => $field['title'],
				'post_name'    => $field['slug'],
				'post_status'  => 'publish',
				'post_type'    => 'legal', // CPT
				'post_content' => $field['content'],
			]);

			if (!is_wp_error($new_page_id)) {
				// Сохраняем ID в настройке плагина
				update_option($field['id'], $new_page_id);

				// Сохраняем мету "Скрыть из архива"
				$checkbox_name = 'hide_from_archive_' . $field['id'];
				update_post_meta(
					$new_page_id,
					'_hide_from_archive',
					!empty($_POST[$checkbox_name]) ? '1' : '0'
				);

				// Если это страница политики конфиденциальности — назначаем её в WP
				if ($field['slug'] === 'privacy-policy') {
					update_option('wp_page_for_privacy_policy', $new_page_id);
				}
			}

			wp_redirect(admin_url('options-general.php?page=codeweber-legal-settings'));
			exit;
		}
	}
});


/**
 * Обрабатывает сохранение значений чекбоксов "Скрыть из архива" при сохранении настроек плагина.
 *
 * Механизм:
 * 1. Срабатывает на хук `admin_init` (только для пользователей с правами `manage_options`)
 * 2. Проверяет, что отправлена форма настроек плагина (`option_page === 'codeweber_legal_settings'`)
 * 3. Для каждого поля, определённого в `codeweber_get_legal_fields()`:
 *    - Получает ID связанной страницы из опций (`get_option($field['id'])`)
 *    - Если страница существует, обновляет метаполе `_hide_from_archive` значением:
 *      - `'1'` — если соответствующий чекбокс в форме был установлен
 *      - `'0'` — если чекбокс не установлен
 *
 * Ожидается, что `codeweber_get_legal_fields()` возвращает массив элементов вида:
 * [
 *   'id'    => 'privacy_policy_page_id', // ключ опции, где хранится ID страницы
 *   'title' => 'Политика конфиденциальности',
 *   'slug'  => 'privacy-policy',
 *   ...
 * ]
 *
 * Название чекбокса в форме должно соответствовать шаблону:
 *   hide_from_archive_{$field['id']}
 *
 * @since 1.0.0
 */
add_action('admin_init', function () {
	if (!current_user_can('manage_options')) {
		return;
	}

	$fields = codeweber_get_legal_fields();

	// При сохранении настроек
	if (!empty($_POST['option_page']) && $_POST['option_page'] === 'codeweber_legal_settings') {
		foreach ($fields as $field) {
			$page_id = get_option($field['id']);
			if ($page_id) {
				$checkbox_name = 'hide_from_archive_' . $field['id'];
				update_post_meta($page_id, '_hide_from_archive', !empty($_POST[$checkbox_name]) ? '1' : '0');
			}
		}
	}
});

/**
 * Сохраняет мета-данные чекбокса "hide_from_archive" при сохранении записи типа 'legal'.
 *
 * Хук: save_post_legal
 *
 * Логика:
 * - Проверяет, что не происходит авто-сохранение.
 * - Проверяет права текущего пользователя на редактирование записи.
 * - Сохраняет значение мета-поля '_hide_from_archive' в зависимости от наличия чекбокса в POST.
 *
 * @param int $post_id ID сохраняемой записи типа 'legal'.
 * @return void
 */
add_action('save_post_legal', function ($post_id) {

	// Проверяем авто-сохранение
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// Проверяем права пользователя на редактирование записи
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// Сохраняем значение чекбокса 'hide_from_archive'
	if (isset($_POST['hide_from_archive'])) {
		update_post_meta($post_id, '_hide_from_archive', '1');
	} else {
		update_post_meta($post_id, '_hide_from_archive', '0');
	}
});

/**
 * Модифицирует основной WP_Query для исключения записей CPT 'legal',
 * у которых в мета-поле '_hide_from_archive' значение '1'.
 *
 * Хук: pre_get_posts
 *
 * Логика:
 * - Проверяет, что это не админка и главный запрос.
 * - Применяет фильтр к архивам типа записи 'legal', таксономиям и главной странице.
 * - Добавляет meta_query с условием "ключ != '1'" для поля '_hide_from_archive'.
 *
 * @param WP_Query $query Объект текущего запроса WordPress.
 * @return void
 */
add_action('pre_get_posts', function ($query) {
	if (!is_admin() && $query->is_main_query() && (is_post_type_archive('legal') || is_tax() || is_home())) {
		$meta_query = [
			[
				'key'     => '_hide_from_archive',
				'value'   => '1',
				'compare' => '!=',
			]
		];
		$query->set('meta_query', $meta_query);
	}
});



/**
 * Регистрирует динамические шорткоды для вывода URL юридических страниц.
 *
 * Пример:
 *   Если поле имеет slug = 'cookie_policy', будет доступен шорткод:
 *   [url_cookie_policy] → https://example.com/cookie-policy/
 *
 * Условия:
 * - Использует get_option($field['id']) для получения ID страницы.
 * - Если страница существует и опубликована, возвращается её permalink.
 * - В противном случае возвращается пустая строка.
 *
 * Требует:
 * - Функция codeweber_get_legal_fields() должна вернуть массив с ключами: id, slug.
 *
 * @since 1.0.0
 */
add_action('init', function () {
	$fields = codeweber_get_legal_fields();
	foreach ($fields as $field) {
		add_shortcode('url_' . $field['slug'], function () use ($field) {
			$page_id = get_option($field['id']);
			if ($page_id && get_post_status($page_id) === 'publish') {
				return '<a href=' . esc_url(get_permalink($page_id)). ' >' . esc_url(get_permalink($page_id)) . '</a>';
			}
			return '';
		});
	}
});


/**
 * Регистрирует динамические шорткоды для вывода ссылок на юридические страницы с заголовками.
 *
 * Пример:
 *   Если поле имеет slug = 'cookie_policy', будет доступен шорткод:
 *   [link_cookie_policy] → <a href="https://example.com/cookie-policy/" target="_blank" title="политика использования cookie">политика использования cookie</a>
 *
 * Условия:
 * - Использует get_option($field['id']) для получения ID страницы
 * - Если страница существует и опубликована, возвращает ссылку с заголовком
 * - Заголовок приводится к нижнему регистру
 * - Ссылка открывается в новом окне (target="_blank")
 *
 * Требует:
 * - Функция codeweber_get_legal_fields() должна вернуть массив с ключами: id, slug
 *
 * @since 1.0.0
 */
add_action('init', function () {
	$fields = codeweber_get_legal_fields();
	foreach ($fields as $field) {
		add_shortcode('link_' . $field['slug'], function () use ($field) {
			$page_id = get_option($field['id']);
			if ($page_id && get_post_status($page_id) === 'publish') {
				$url = esc_url(get_permalink($page_id));
				$title = mb_strtolower(get_the_title($page_id), 'UTF-8');
				return '<a href="' . $url . '" target="_blank" title="' . esc_attr($title) . '">' . $url . '</a>';
			}
			return '';
		});
	}
});



/**
 * Шорткод [url_privacy_policy]
 * 
 * Возвращает URL выбранной в настройках страницы политики конфиденциальности
 * Использует ID страницы, сохраненный в базе данных через выпадающий список
 * 
 * @return string URL страницы или пустая строка, если страница не выбрана/не существует
 */
add_shortcode('url_privacy_policy', function () {
	// Получаем все legal поля
	$legal_fields = codeweber_get_legal_fields();

	// Находим поле для privacy policy по точному ID
	$privacy_field = null;
	foreach ($legal_fields as $field) {
		if ($field['id'] === 'codeweber_legal_privacy-policy') { // Точный ID из вашей системы
			$privacy_field = $field;
			break;
		}
	}

	if (!$privacy_field) return '';

	// Получаем ID сохраненной страницы из настроек
	$page_id = get_option($privacy_field['id']);

	// Проверяем, что страница существует и опубликована
	if (!$page_id || get_post_status($page_id) !== 'publish') {
		return '';
	}

	// Возвращаем URL с экранированием
	return esc_url(get_permalink($page_id));
});





/**
 * Шорткод [link_privacy_policy]
 * 
 * Возвращает HTML-ссылку на выбранную в настройках страницу политики конфиденциальности
 * Использует ID страницы, сохраненный в базе данных через выпадающий список
 * 
 * @return string HTML ссылка или пустая строка, если страница не выбрана/не существует
 */
add_shortcode('link_privacy_policy', function () {
	// Получаем все legal поля
	$legal_fields = codeweber_get_legal_fields();

	// Находим поле для privacy policy (по id или другому уникальному признаку)
	$privacy_field = null;
	foreach ($legal_fields as $field) {
		if ($field['id'] === 'codeweber_legal_privacy-policy') { // Используем точный ID поля
			$privacy_field = $field;
			break;
		}
	}

	if (!$privacy_field) return '';

	// Получаем ID сохраненной страницы
	$page_id = get_option($privacy_field['id']);

	// Проверяем, что страница существует и опубликована
	if (!$page_id || get_post_status($page_id) !== 'publish') {
		return '';
	}

	// Получаем URL страницы
	$url = get_permalink($page_id);

	return $url ? sprintf('<a href="%s">%s</a>', esc_url($url), esc_url($url)) : '';
});




/**
 * Выводит HTML-код страницы настроек для юридических документов в админке WordPress.
 *
 * Страница содержит заголовок, форму с полями настроек, секциями и кнопкой сохранения.
 * Использует стандартные функции WordPress для работы с настройками:
 * - settings_fields() — для вывода скрытых полей безопасности и идентификатора группы настроек;
 * - do_settings_sections() — для вывода зарегистрированных секций и полей по указанному меню;
 * - submit_button() — для вывода кнопки сохранения формы.
 *
 * Страница настроек вызывается через admin menu и связана с группой настроек 'codeweber_legal_settings'
 * и страницей с идентификатором 'codeweber-legal-settings'.
 *
 * @return void
 */
function codeweber_legal_settings_page()
{
?>
	<div class="wrap">
		<h1><?php _e('Legal Settings', 'codeweber'); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields('codeweber_legal_settings');
			do_settings_sections('codeweber-legal-settings');
			submit_button();
			?>
		</form>
	</div>
<?php
}

function codeweber_get_legal_fields()
{
	return [
		[
			'id'      => 'codeweber_legal_privacy-policy',
			'title'   => __('Privacy Policy', 'codeweber'),
			'slug'    => 'privacy-policy',
			'content' => <<<EOD
<!-- wp:paragraph -->
<p>Редакция от 28.07.2025</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">1. Общие положения</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>1.1. Настоящая Политика в отношении обработки персональных данных (политика конфиденциальности) (далее - Политика) разработана во исполнение требований п. 2 ч. 1 ст. 18.1 Федерального закона от 27.07.2006 № 152-ФЗ «О персональных данных» (далее - Закон о персональных данных) в целях обеспечения защиты прав и свобод человека и гражданина при обработке его персональных данных, в том числе защиты прав на неприкосновенность частной жизни, личную и семейную тайну.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>1.2. Политика действует в отношении всех персональных данных, которые обрабатывает Оператор.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>1.3. Политика распространяется на отношения в области обработки персональных данных, возникшие у Оператора как до, так и после утверждения настоящей Политики.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>1.4. Актуальная версия настоящей Политики публикуется в свободном доступе в информационно-телекоммуникационной сети Интернет на сайте по адресу: [link_privacy_policy]</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">2. Термины и определения</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>2.1. В настоящей Политике используются следующие термины и их определения:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><strong>Персональные данные</strong> — любая информация, относящаяся к прямо или косвенно определенному, или определяемому физическому лицу (субъекту персональных данных);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Оператор</strong> – [redux_option key="legal_entity"] (ОГРНИП [redux_option key="legal_ogrnip"] от [redux_option key="legal_ogrnip_date" format="d.m.Y"]);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Обработка персональных данных</strong> — любое действие (операция) или совокупность действий (операций) с персональными данными, совершаемых с использованием средств автоматизации или без их использования. Обработка персональных данных включает в себя в том числе:<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>сбор;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>запись;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>систематизацию;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>накопление;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>хранение;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>уточнение (обновление, изменение);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>извлечение;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>использование;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>передачу (распространение, предоставление, доступ);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>блокирование;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>удаление;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>уничтожение;</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><strong>Автоматизированная обработка персональных данных</strong> — обработка персональных данных с помощью средств вычислительной техники;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Распространение персональных данных</strong> — действия, направленные на раскрытие персональных данных неопределенному кругу лиц;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Предоставление персональных данных</strong> — действия, направленные на раскрытие персональных данных определенному лицу или определенному кругу лиц;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Блокирование персональных данных</strong> — временное прекращение обработки персональных данных (за исключением случаев, если обработка необходима для уточнения персональных данных);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Уничтожение персональных данных</strong> — действия, в результате которых становится невозможным восстановить содержание персональных данных в информационной системе персональных данных и (или) в результате которых уничтожаются материальные носители персональных данных;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Лицо, осуществляющее обработку персональных данных по поручению Оператора (Обработчик)</strong> — любое лицо, которое на основании договора с оператором осуществляет обработку персональных данных по поручению такого оператора, действуя от имени и (или) в интересах последнего при обработке персональных данных. Оператор несет ответственность перед субъектом персональных данных за действия или бездействия обработчика;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Файлы «cookies»</strong> – это небольшие файлы, размещаемые на устройствах пользователя сайта Оператора во время использования указанного сайта для улучшения его функционирования, которые могут содержать идентификатор пользователя, сведения об устройстве, браузере.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>2.2. Иные термины, используемые в настоящей Политике, используются в значении, предусмотренном действующим законодательством Российской Федерации.</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">3. Основные права и обязанности Оператора</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>3.1. Оператор имеет право:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>3.1.1. Самостоятельно определять состав и перечень мер, необходимых и достаточных для обеспечения выполнения обязанностей, предусмотренных Законом о персональных данных и принятыми в соответствии с ним нормативными правовыми актами, если иное не предусмотрено Законом о персональных данных или другими федеральными законами;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>3.1.2. Поручить обработку персональных данных другому лицу с согласия субъекта персональных данных, если иное не предусмотрено федеральным законом, на основании заключаемого с этим лицом договора. Обработчик обязан соблюдать принципы и правила обработки персональных данных, предусмотренные Законом о персональных данных;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>3.1.3. В случае отзыва субъектом персональных данных согласия на обработку персональных данных Оператор вправе продолжить обработку персональных данных без согласия субъекта персональных данных при наличии оснований, указанных в Законе о персональных данных.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>3.2. Оператор обязан:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>3.2.1. Организовывать обработку персональных данных в соответствии с требованиями Закона о персональных данных;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>3.2.2. Отвечать на обращения и запросы субъектов персональных данных и их законных представителей в соответствии с требованиями Закона о персональных данных;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>3.2.3. Сообщать в уполномоченный орган по защите прав субъектов персональных данных (Федеральную службу по надзору в сфере связи, информационных технологий и массовых коммуникаций (Роскомнадзор) по запросу этого органа необходимую информацию в течение 10 (десяти) рабочих дней с даты получения такого запроса.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">4. Основные права субъекта персональных данных</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>4.1. Субъект персональных данных имеет право:</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"list-unstyled"} -->
<ul class="wp-block-list list-unstyled"><!-- wp:list-item -->
<li>4.1.1. Получать информацию, касающуюся обработки его персональных данных, за исключением случаев, предусмотренных федеральными законами. Сведения предоставляются субъекту персональных данных Оператором в доступной форме, и в них не должны содержаться персональные данные, относящиеся к другим субъектам персональных данных, за исключением случаев, когда имеются законные основания для раскрытия таких персональных данных. Перечень информации и порядок ее получения установлен Законом о персональных данных;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>4.1.2. Отозвать ранее данное согласие на обработку персональных данных;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>4.1.3. Требовать от Оператора уточнения его персональных данных, их блокирования или уничтожения в случае, если персональные данные являются неполными, устаревшими, неточными, незаконно полученными или не являются необходимыми для заявленной цели обработки, а также принимать предусмотренные законом меры по защите своих прав;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>4.1.4. Выдвигать условие предварительного согласия при обработке персональных данных в целях продвижения на рынке товаров, работ и услуг;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>4.1.5. Обжаловать в Роскомнадзоре или в судебном порядке неправомерные действия или бездействие Оператора при обработке его персональных данных.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">5. Цели сбора персональных данных</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>5.1. Обработка персональных данных ограничивается достижением конкретных, заранее определенных и законных целей. Не допускается обработка персональных данных, несовместимая с целями сбора персональных данных.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>5.2. Обработке подлежат только персональные данные, которые отвечают целям их обработки.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>5.3. Обработка Оператором персональных данных осуществляется в следующих целях:</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>[redux_option key="purpose_of_collecting_personal_data"]</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">6. Правовые основания обработки персональных данных</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>6.1. Правовым основанием обработки персональных данных является совокупность нормативных правовых актов, во исполнение которых и в соответствии с которыми Оператор осуществляет обработку персональных данных, а именно:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Конституция Российской Федерации;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Гражданский кодекс Российской Федерации;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Закон РФ от 27.11.1992 № 4015-1 «Об организации страхового дела в Российской Федерации»;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Федеральный закон от 27.07.2006 № 152-ФЗ «О персональных данных»;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Федеральный закон от 27.07.2006 № 149-ФЗ «Об информации, информационных технологиях и о защите информации»;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Постановление Правительства Российской Федерации от 01.11.2012 № 1119 «Об утверждении требований к защите персональных данных при их обработке в информационных системах персональных данных»;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Постановление Правительства РФ от 15.09.2008 № 687 «Об утверждении Положения об особенностях обработки персональных данных, осуществляемой без использования средств автоматизации»;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Приказ ФСТЭК России от 18.02.2013 № 21 «Об утверждении Состава и содержания организационных и технических мер по обеспечению безопасности персональных данных при их обработке в информационных системах персональных данных»;</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>6.2. Правовым основанием обработки персональных данных также являются:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Согласие Пользователя на обработку его персональных данных;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Договоры (соглашения), стороной или выгодоприобретателем по которым является субъект персональных данных.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">7. Объем и категории обрабатываемых персональных данных, категории субъектов персональных данных</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>7.1. Содержание и объем обрабатываемых персональных данных должны соответствовать заявленным целям обработки, предусмотренным в разделе 5 настоящей Политики. Обрабатываемые персональные данные не должны быть избыточными по отношению к заявленным целям их обработки.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>7.2. Оператор обрабатывает следующие персональные данные:</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[pdn_sections]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p>7.3. Оператором не осуществляется обработка биометрических персональных данных (сведений, которые характеризуют физиологические и биологические особенности человека, на основании которых можно установить его личность).</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>7.4. Оператором не осуществляется обработка специальных категорий персональных данных, касающихся расовой, национальной принадлежности, политических взглядов, религиозных или философских убеждений, состояния здоровья, интимной жизни, за исключением случаев, предусмотренных законодательством Российской Федерации.</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">8. Порядок и условия обработки персональных данных</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>8.1. Обработка персональных данных осуществляется Оператором в соответствии с требованиями законодательства Российской Федерации.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>8.2. Обработка персональных данных осуществляется с согласия субъектов персональных данных или их представителей на обработку их персональных данных, а также без такового в случаях, предусмотренных законодательством Российской Федерации.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>8.3. Оператор осуществляет как автоматизированную, так и неавтоматизированную обработку персональных данных.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>8.4. К обработке персональных данных допускаются работники Оператора, в должностные обязанности которых входит обработка персональных данных.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>8.5. Обработка персональных данных осуществляется путем:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>получения персональных данных в письменной и электронной форме непосредственно от субъектов персональных данных и их представителей;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>внесения персональных данных в журналы, реестры и информационные системы Оператора;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>использования иных способов обработки персональных данных.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>8.6. Не допускается раскрытие третьим лицам и распространение персональных данных без согласия субъекта персональных данных, если иное не предусмотрено федеральным законом. Согласие на обработку персональных данных, разрешенных субъектом персональных данных для распространения, оформляется отдельно от иных согласий субъекта персональных данных на обработку его персональных данных.<br>Оператор осуществляет сбор согласий на обработку персональных данных, разрешенных субъектом персональных данных для распространения, в соответствии с требованиями, утвержденными Приказом Роскомнадзора от 24.02.2021 № 18.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>8.7. Оператор осуществляет хранение персональных данных в форме, позволяющей определить субъекта персональных данных:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>в течение срока, необходимого для обработки персональных данных в целях, указанных в Политике;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>на протяжении срока, когда обработка необходима для выполнения норм действующего законодательства Российской Федерации (в том числе, но не ограничиваясь в сфере бухгалтерского учета, налогового, гражданского, процессуального законодательства), если иной срок не установлен договором с субъектом персональных данных.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>8.8. Оператор прекращает любую обработку персональных данных в сроки, установленные законом:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>при достижении целей обработки персональных данных, указанных в Политике либо при утрате необходимости их достижения (при отсутствии других оснований для обработки, предусмотренных действующим законодательством);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>при выявлении неправомерной обработки персональных данных, если обеспечить законность обработки невозможно;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>при истечении срока согласия или его отзыва (при отсутствии других оснований для обработки, предусмотренных действующим законодательством);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>при ликвидации Оператора.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>8.9. Оператор осуществляет обработку технических данных устройств субъектов персональных данных – Файлы «cookies». Пользователи сайта могут самостоятельно ограничить или полностью отключить установку Файлов «cookies» через настройки своего веб-браузера, вследствие чего сайты Оператора могут работать некорректно, а часть их функционала может оказаться недоступна. Файлы «cookies» могут передаваться владельцами сервисов веб-аналитики (в т.ч. Яндекс Метрики) или других аналогичных сервисов без цели идентификации конкретного пользователя.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>8.10. На сайте Оператора используются следующие Файлы «cookies»:</p>
<!-- /wp:paragraph -->

<!-- wp:table -->
<figure class="wp-block-table"><table class="has-fixed-layout"><thead><tr><th>Категория</th><th>Имя cookie</th><th>Срок действия</th><th>Функция</th></tr></thead><tbody><tr><td>Собственные</td><td>—</td><td>—</td><td>Обеспечение нормальной работы сайта и сохранение настроек пользователя</td></tr><tr><td>CMS WordPress</td><td>wordpress_logged_in_*</td><td>сессионный</td><td>Идентификация авторизованного пользователя WordPress</td></tr><tr><td></td><td>wp-settings-*</td><td>постоянный</td><td>Настройки панели администратора WordPress</td></tr><tr><td>WooCommerce</td><td>woocommerce_cart_hash</td><td>постоянный</td><td>Идентификация содержимого корзины WooCommerce</td></tr><tr><td></td><td>woocommerce_items_in_cart</td><td>сессионный</td><td>Отслеживание количества товаров в корзине WooCommerce</td></tr><tr><td>Статистические и функциональные</td><td>_ym*_lsid</td><td>постоянный</td><td>Хранение уникального идентификатора пользователя (Yandex.Metrica)</td></tr><tr><td></td><td>_ym_zzlc</td><td>сессионный</td><td>Функциональная cookie (Yandex.Metrica)</td></tr><tr><td></td><td>_ym*_lastHit</td><td>постоянный</td><td>Хранение времени последнего визита (Yandex.Metrica)</td></tr><tr><td>Рекламные</td><td>yandexuid</td><td>постоянный</td><td>Идентификация пользователя для рекламных целей (Яндекс.Директ)</td></tr><tr><td></td><td>_ym_d</td><td>постоянный</td><td>Рекламные cookie Яндекс.Директ</td></tr><tr><td>Маркетинговые / отслеживающие</td><td>_ym_marketing</td><td>постоянный</td><td>Cookie для таргетинга и ретаргетинга Яндекс.Директ</td></tr><tr><td></td><td>_ym_rt</td><td>постоянный</td><td>Cookie для ретаргетинга и аналитики Яндекс.Директ</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>8.11. При сборе персональных данных, в том числе посредством информационно-телекоммуникационной сети Интернет, Оператор обеспечивает запись, систематизацию, накопление, хранение, уточнение (обновление, изменение), извлечение персональных данных граждан Российской Федерации с использованием баз данных, находящихся на территории Российской Федерации, за исключением случаев, указанных в Законе о персональных данных.</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">9. Актуализация, исправление, удаление и уничтожение персональных данных, ответы на запросы субъектов на доступ к персональным данным</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>9.1. Подтверждение факта обработки персональных данных Оператором, правовые основания и цели обработки персональных данных, а также иные сведения, указанные в ч. 7 ст. 14 Закона о персональных данных, предоставляются Оператором субъекту персональных данных или его представителю при обращении либо при получении запроса субъекта персональных данных или его представителя.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>В предоставляемые сведения не включаются персональные данные, относящиеся к другим субъектам персональных данных, за исключением случаев, когда имеются законные основания для раскрытия таких персональных данных.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Запрос должен содержать:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>номер основного документа, удостоверяющего личность субъекта персональных данных или его представителя, сведения о дате выдачи указанного документа и выдавшем его органе;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>сведения, подтверждающие участие субъекта персональных данных в отношениях с Оператором (номер договора, дата заключения договора, условное словесное обозначение и (или) иные сведения), либо сведения, иным образом подтверждающие факт обработки персональных данных Оператором;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>подпись субъекта персональных данных или его представителя.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Запрос может быть направлен в форме электронного документа и подписан электронной подписью в соответствии с законодательством Российской Федерации.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Если в обращении (запросе) субъекта персональных данных не отражены в соответствии с требованиями Закона о персональных данных все необходимые сведения или субъект не обладает правами доступа к запрашиваемой информации, то ему направляется мотивированный отказ.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Право субъекта персональных данных на доступ к его персональным данным может быть ограничено в соответствии с ч. 8 ст. 14 Закона о персональных данных, в том числе если доступ субъекта персональных данных к его персональным данным нарушает права и законные интересы третьих лиц.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>9.2. В случае выявления неточных персональных данных при обращении субъекта персональных данных или его представителя либо по их запросу или по запросу Роскомнадзора Оператор осуществляет блокирование персональных данных, относящихся к этому субъекту персональных данных, с момента такого обращения или получения указанного запроса на период проверки, если блокирование персональных данных не нарушает права и законные интересы субъекта персональных данных или третьих лиц.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>В случае подтверждения факта неточности персональных данных Оператор на основании сведений, представленных субъектом персональных данных или его представителем либо Роскомнадзором, или иных необходимых документов уточняет персональные данные в течение семи рабочих дней со дня представления таких сведений и снимает блокирование персональных данных.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>9.3. В случае выявления неправомерной обработки персональных данных при обращении (запросе) субъекта персональных данных или его представителя либо Роскомнадзора Оператор осуществляет блокирование неправомерно обрабатываемых персональных данных, относящихся к этому субъекту персональных данных, с момента такого обращения или получения запроса.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>9.4. В случае обращения субъекта персональных данных к Оператору с требованием о прекращении обработки персональных данных Общество обязано в срок, не превышающий десяти рабочих дней с даты получения Обществом соответствующего требования, прекратить их обработку или обеспечить прекращение такой обработки (если такая обработка осуществляется лицом, осуществляющим обработку персональных данных), за исключением случаев, предусмотренных пунктами 2 - 11 части 1 статьи 6, частью 2 статьи 10 и частью 2 статьи 11 Закона о персональных данных.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>9.5. При достижении целей обработки персональных данных, а также в случае отзыва субъектом персональных данных согласия на их обработку персональные данные подлежат уничтожению в срок, не превышающий 30 (тридцати) рабочих дней с даты достижения цели или отзыва согласия, если:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>иное не предусмотрено договором, стороной которого, выгодоприобретателем или поручителем, по которому является субъект персональных данных;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Оператор не вправе осуществлять обработку без согласия субъекта персональных данных на основаниях, предусмотренных Законом о персональных данных или иными федеральными законами;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>иное не предусмотрено другим соглашением между Оператором и субъектом персональных данных.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>9.6. Все запросы и обращения в должны быть направлены одним из указанных вариантов:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>путем направления письменного сообщения на адрес Оператора: [redux_option key="storage_address"];</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>путем направления электронного сообщения на электронную почту: [redux_option key="email_responsible_person"]. </li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">СВЕДЕНИЯ о реализуемых Оператором требованиях к защите персональных данных</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Определен уровень защищенности персональных данных, обрабатываемых в информационных системах персональных данных Оператора с установлением требований для необходимого уровня защищенности информационных систем персональных данных;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Осуществляется контроль за принимаемыми мерами по обеспечению безопасности персональных данных и уровнем защищенности информационных систем персональных данных;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Предусмотрен учет машинных носителей персональных данных;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Осуществляется обнаружение фактов несанкционированного доступа к персональным данным и принятие соответствующих мер.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->
EOD
		],
		[
			'id'      => 'codeweber_legal_terms_of_use',
			'title'   => __('Terms of Use', 'codeweber'),
			'slug'    => 'terms-of-use',
			'content' => <<<EOD
			<!-- wp:paragraph -->
<p>Редакция от 28.07.2025</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">1. Общие положения</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>1.1. Настоящее Пользовательское соглашение (далее — «Соглашение») заключено между: </p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><strong>Администрацией сайта</strong>: [redux_option key="legal_entity"] (ОГРНИП [redux_option key="legal_ogrnip"] от [redux_option key="legal_ogrnip_date" format="d.m.Y"]),<br>Юридический адрес: [redux_option key="storage_address"],<br>Контактный email: [redux_option key="email_responsible_person"];</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Пользователем</strong>&nbsp;— любым лицом, осуществляющим доступ к сайту [site_domain_link] (далее — «Сайт»).</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>1.2. Начиная использование Сайта, Пользователь:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Подтверждает, что полностью ознакомился с условиями настоящего Соглашения;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Дает Согласие на обработку персональных данных<strong>&nbsp;</strong>([url_consent-processing]);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Принимает условия&nbsp;Политики в отношении обработки персональных данных размещенной по адресу (<a target="_blank" rel="noreferrer noopener">[url_privacy-policy]</a>);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Соглашается с использованием файлов cookie согласно&nbsp;Политики в отношении использования файлов Куки размещенной по адресу ([url_cookie-policy]).</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">2. Право интеллектуальной собственности на контент сайта</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>2.1.  Весь контент Сайта (тексты, изображения, дизайн, видео, базы данных и иные материалы), за исключением явно обозначенного как пользовательский, является интеллектуальной собственностью [redux_option key="legal_entity"].</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>2.2. Запрещается без письменного разрешения:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Копирование, воспроизведение или распространение контента;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Публикация материалов Сайта на других ресурсах без активной гиперссылки на источник;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Любое коммерческое использование контента.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>2.3. Допускается цитирование (статья 1274 ГК РФ) с обязательным указанием автора и гиперссылки на источник в виде активной ссылки.</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">3. Условия использования</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>3.1. Пользователь обязуется:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Использовать Сайт только в законных целях;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Не нарушать права интеллектуальной собственности;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Не распространять вредоносное ПО и незаконный контент.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>3.2. Запрещенный контент и действия:</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Пользователю категорически запрещается:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Размещать, распространять или пропагандировать:<br>• Контент, разжигающий межнациональную, расовую или религиозную вражду (ст. 282 УК РФ);<br>• Материалы экстремистского характера;<br>• Пропаганду насилия и жестокости;<br>• Порнографию и материалы сексуального характера с участием несовершеннолетних;<br>• Информацию о способах изготовления наркотиков;<br>• Призывы к суициду и опасные "челленджи";<br>• Ложную информацию (фейки), порочащую честь и достоинство других лиц;<br>• Персональные данные третьих лиц без их согласия.<br></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Совершать действия, которые:<br>• Нарушают права интеллектуальной собственности;<br>• Содержат угрозы и оскорбления;<br>• Направлены на обход технических ограничений Сайта;<br>• Нарушают законодательство РФ или международное право.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>3.3. Особые ограничения на информацию о специальной военной операции:</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Пользователям запрещается:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Распространение ложной информации о действиях Вооруженных Сил РФ;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Призывы к санкциям против России;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Материалы, дискредитирующие российскую армию (ст. 207.3 УК РФ);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Карты с расположением войск и другой информации, составляющей гостайну;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Использование неутвержденных символов СВО;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Материалы запрещенных организаций (признанных в РФ экстремистскими);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Материалы иностранных агентов (без соответствующей маркировки);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Сбор помощи для ВСУ или иных запрещенных структур;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Пропаганда нацистской символики;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Оправдание военных преступлений.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>3.4. Последствия нарушений:</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>При обнаружении запрещенного контента Администрация вправе:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Удалить материал без предупреждения;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Заблокировать аккаунт пользователя;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Передать информацию в правоохранительные органы (при необходимости);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Взыскать убытки через суд.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">4. Персональные данные</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>4.1. &nbsp;Обработка персональных данных осуществляется согласно:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Политика в отношении обработки персональных данных размещенная по адресу <a target="_blank" rel="noreferrer noopener">(</a><a target="_blank" rel="noreferrer noopener">[url_privacy-policy]</a>);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Политика в отношении использования файлов Куки размещенная по адресу ([url_cookie-policy]).</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">5. Ответственность</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>5.1. Администрация не несет ответственности за:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Убытки, возникшие при использовании Сайта;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Работу сторонних ресурсов.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>5.2. Пользователь возмещает ущерб, причиненный нарушениями.</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">6. Заключительные положения</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>6.1. Соглашение вступает в силу с момента публикации.<br>6.2. Все споры разрешаются в суде по месту нахождения [redux_option key="legal_entity"].</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">7. Контактная информация:</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[redux_option key="legal_entity_short"]<br><strong>Адрес:</strong> [redux_option key="storage_address"]<br><strong>Тел.</strong> [redux_option key="phone_responsible_person"]<br><strong>E-mail:</strong> [redux_option key="email_responsible_person"]</p>
<!-- /wp:paragraph -->
EOD
		],
		[
			'id'      => 'codeweber_legal_cookie_policy',
			'title'   => __('Cookie Policy', 'codeweber'),
			'slug'    => 'cookie-policy',
			'content' => <<<EOD
			<!-- wp:paragraph -->
<p>Редакция от 28.07.2025</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">1. Общие положения</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>1.1. Настоящая Политика использования файлов cookie (далее — Политика) разработана во исполнение требований п. 2 ч. 1 ст. 18.1 Федерального закона от 27.07.2006 № 152-ФЗ «О персональных данных» (далее — Закон о персональных данных), Федерального закона от 27.07.2006 № 149-ФЗ «Об информации, информационных технологиях и о защите информации» и иных нормативных правовых актов Российской Федерации, регулирующих обработку персональных данных и защиту информации.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>1.2. Политика направлена на информирование Пользователей сайта об использовании файлов cookie, способах их обработки и целях обработки персональных данных с использованием файлов cookie.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>1.3. Политика распространяется на все файлы cookie, которые используются Оператором при посещении Пользователем сайта и иные технологии сбора и хранения информации, используемые для улучшения функциональности сайта и повышения качества обслуживания.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>1.4. Актуальная версия настоящей Политики публикуется в свободном доступе в информационно-телекоммуникационной сети Интернет на сайте по адресу: [url_cookie-policy]</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">2. Термины и определения</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>2.1. В настоящей Политике используются следующие термины и их определения:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><strong>Персональные данные</strong> — любая информация, относящаяся к прямо или косвенно определенному, или определяемому физическому лицу (субъекту персональных данных);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Оператор</strong> – [redux_option key="legal_entity"] (ОГРНИП [redux_option key="legal_ogrnip"] от [redux_option key="legal_ogrnip_date" format="d.m.Y"]);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Обработка персональных данных</strong> — любое действие (операция) или совокупность действий (операций) с персональными данными, совершаемых с использованием средств автоматизации или без их использования. Обработка персональных данных включает в себя в том числе:<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>сбор;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>запись;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>систематизацию;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>накопление;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>хранение;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>уточнение (обновление, изменение);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>извлечение;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>использование;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>передачу (распространение, предоставление, доступ);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>блокирование;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>удаление;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>уничтожение;</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><strong>Автоматизированная обработка персональных данных</strong> — обработка персональных данных с помощью средств вычислительной техники;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Распространение персональных данных</strong> — действия, направленные на раскрытие персональных данных неопределенному кругу лиц;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Предоставление персональных данных</strong> — действия, направленные на раскрытие персональных данных определенному лицу или определенному кругу лиц;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Блокирование персональных данных</strong> — временное прекращение обработки персональных данных (за исключением случаев, если обработка необходима для уточнения персональных данных);</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Уничтожение персональных данных</strong> — действия, в результате которых становится невозможным восстановить содержание персональных данных в информационной системе персональных данных и (или) в результате которых уничтожаются материальные носители персональных данных;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Лицо, осуществляющее обработку персональных данных по поручению Оператора (Обработчик)</strong> — любое лицо, которое на основании договора с оператором осуществляет обработку персональных данных по поручению такого оператора, действуя от имени и (или) в интересах последнего при обработке персональных данных. Оператор несет ответственность перед субъектом персональных данных за действия или бездействия обработчика;</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Файлы cookie (Cookies)</strong> – это небольшие файлы, размещаемые на устройствах пользователя сайта Оператора во время использования указанного сайта для улучшения его функционирования, которые могут содержать идентификатор пользователя, сведения об устройстве, браузере.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>2.2. Иные термины, используемые в настоящей Политике, используются в значении, предусмотренном действующим законодательством Российской Федерации.</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">3. Цели использования файлов cookie</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>3.1. Обработка персональных данных с использованием файлов cookie осуществляется на основании согласия Пользователя либо в иных случаях, предусмотренных законодательством Российской Федерации.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>3.2. Оператор использует файлы cookie в целях:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Обеспечения функционирования сайта и сохранения пользовательских настроек.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Идентификации авторизованных Пользователей.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Ведения статистики посещений сайта и анализа поведения Пользователей.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Повышения удобства пользования сайтом.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Проведения рекламных и маркетинговых кампаний, в том числе таргетинга.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">4. Использование файлов cookie</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>4.1. Оператор осуществляет обработку технических данных устройств субъектов персональных данных – Файлы «cookies». Пользователи сайта могут самостоятельно ограничить или полностью отключить установку Файлов «cookies» через настройки своего веб-браузера, вследствие чего сайты Оператора могут работать некорректно, а часть их функционала может оказаться недоступна. Файлы «cookies» могут передаваться владельцами сервисов веб-аналитики (в т.ч. Яндекс Метрики) или других аналогичных сервисов без цели идентификации конкретного пользователя.</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">5. Управление файлами cookie</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>5.1. Пользователь может самостоятельно ограничить или полностью отключить использование файлов cookie через настройки браузера. При этом Оператор предупреждает, что в таком случае возможна некорректная работа сайта и ограничение доступа к некоторым функциям.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>5.2. Для управления файлами cookie Пользователь может использовать стандартные средства браузера, в том числе очистку cookie и настройку блокировки.</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">6. Обработка персональных данных с использованием файлов cookie</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>6.1. Файлы cookie могут содержать идентификаторы, позволяющие осуществлять сбор и обработку персональных данных Пользователей в целях, указанных в разделе 3.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>6.2. Обработка персональных данных с использованием файлов cookie осуществляется Оператором в соответствии с требованиями законодательства Российской Федерации, в частности с Законом о персональных данных и Федеральным законом № 149-ФЗ «Об информации, информационных технологиях и о защите информации».</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>6.3. Сведения, собираемые с помощью файлов cookie, не включают персональные данные без согласия Пользователя, за исключением случаев, прямо предусмотренных законом.</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">7. Хранение и защита данных</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>7.1. Оператор обеспечивает технические и организационные меры по защите персональных данных, обрабатываемых с использованием файлов cookie, от несанкционированного доступа, уничтожения, изменения, блокирования и распространения.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>7.2. Файлы cookie хранятся на устройствах Пользователей в течение времени, указанного в сроках их действия, или до удаления Пользователем.</p>
<!-- /wp:paragraph -->

<!-- wp:separator {"className":"my-5"} -->
<hr class="wp-block-separator has-alpha-channel-opacity my-5"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">8. Заключительные положения</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>8.1. Оператор вправе вносить изменения в настоящую Политику в связи с изменениями законодательства, техническими обновлениями сайта или иными причинами.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>8.2. Актуальная версия Политики всегда доступна на сайте Оператора по адресу, указанному в пункте 1.4.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>8.3. Пользователь обязуется самостоятельно ознакомиться с Политикой и соблюдать её положения.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->
EOD
		],
		[
			'id'      => 'codeweber_legal_consent_processing',
			'title'   => __('Consent to Data Processing', 'codeweber'),
			'slug'    => 'consent-processing',
			'content' => <<<EOD
<!-- wp:paragraph -->
<p>Настоящим, при заполнении контактных форм на сайте, расположенном в информационно-телекоммуникационной сети Интернет по адресу [site_domain_link], в соответствии с Федеральным законом от 27.07.2006 №152-ФЗ «О персональных данных», действуя своей волей и в своем интересе, даю конкретное, информированное и сознательное согласие [redux_option key="legal_entity_dative"] (ОГРНИП [redux_option key="legal_ogrnip"] от [redux_option key="legal_ogrnip_date" format="d.m.Y"])(далее – Оператор)</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>на обработку моих персональных данных</strong> такими способами как [redux_option key="personal_data_actions" list="inline"] персональных данных, <strong>с целью </strong>получения информации о стоимости услуг или иной обратной связи от Оператора, в том числе информирование о статусе заявок (заказов) и статусе предоставления услуг.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Принятием (акцептом) настоящего согласия является проставление галочки в чек-боксе (активация чек-бокса) рядом с текстом «Я даю свое согласие на обработку моих персональных данных» и нажатие кнопки «Отправить» (иной аналогичной кнопки) после заполнения всех необходимых элементов контактной или иной формы на Сайте.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Перечень персональных данных, на обработку которых мной дается согласие:</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><a>[redux_option key="list_of_personal_data"]</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Я ознакомлен(а) и соглашаюсь с тем, что, что мои персональные данные будут подвергаться Оператором как автоматизированной (с использованием средств автоматизации), так и неавтоматизированной обработке (без использования средств автоматизации), то есть обработке при непосредственном участии человека согласно п.п. 1, 2 Постановления Правительства Российской Федерации от 15.09.2008 г. № 687 «Об утверждении Положения об особенностях обработки персональных данных, осуществляемой без использования средств автоматизации», с передачей по внутренней сети Оператора, с передачей по информационно-коммуникационной сети Интернет.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Подтверждаю, что проинформирован(а) о возможности отзыва настоящего Согласия в любой момент&nbsp;путем личного обращения или направления письменного обращения (в том числе в форме электронного документа, подписанного в соответствии с федеральным законом электронной подписью) в электронном виде по адресу электронной почты, указанному в разделе «Реквизиты Оператора» настоящего Согласия. Заявление об отзыве согласия на обработку персональных данных должно содержать: сведения о документе, удостоверяющем личность субъекта персональных данных; сведения об отношениях субъекта персональных данных с оператором; подпись субъекта персональных данных.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Настоящее Согласие действует со дня его принятия (акцепта) до дня его отзыва.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Оператор прекращает обработку персональных данных и уничтожает их в течение 30 (тридцати) календарных дней со дня получения отзыва настоящего Согласия или в сроки, предусмотренные действующим законодательством при отсутствии отзыва данного согласия.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Я уведомлен(а), что после получения отзыва согласия Оператор вправе осуществлять дальнейшую обработку персональных данных в случаях,&nbsp;указанных в&nbsp;пп. 2&nbsp;-&nbsp;11 ч. 1 ст. 6 Федерального закона от 27.07.2006 №152-ФЗ «О персональных данных».</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>С Политикой в отношении обработки персональных данныхСайта, размещенной по адресу в сети Интернет: [link_privacy_policy] ознакомлен (а) и согласен (а).</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>В случае, если пользователем Сайта, предоставляющим настоящее Согласие, является несовершеннолетнее или иное недееспособное лицо, настоящее Согласие считается предоставленным со стороны родителя (иного законного представителя) такого пользователя пока не будет установлено иное.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Реквизиты Оператора:</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[redux_option key="legal_entity_short"]<br>Адрес: [redux_option key="storage_address"]<br>ОГРНИП [redux_option key="legal_ogrnip"]<br>ИНН [redux_option key="taxpayer_identification_number"]<br>Тел. [redux_option key="phone_responsible_person"]<br>E-mail: [redux_option key="email_responsible_person"]</p>
<!-- /wp:paragraph -->
EOD
		],
		[
			'id'      => 'codeweber_legal_offer_agreement',
			'title'   => __('Public Offer Agreement', 'codeweber'),
			'slug'    => 'offer-agreement',
			'content' => 'This is the public offer agreement.',
		],
		[
			'id'      => 'codeweber_legal_site_rules',
			'title'   => __('Website Usage Rules', 'codeweber'),
			'slug'    => 'site-rules',
			'content' => 'These are the rules for using the website.',
		],
		[
			'id'      => 'codeweber_legal_delivery_terms',
			'title'   => __('Delivery Terms', 'codeweber'),
			'slug'    => 'delivery-terms',
			'content' => 'These are the delivery terms.',
		],
		[
			'id'      => 'codeweber_legal_return_policy',
			'title'   => __('Return Policy', 'codeweber'),
			'slug'    => 'return-policy',
			'content' => 'This is the return policy.',
		],
		[
			'id'      => 'codeweber_legal_email_consent',
			'title'   => __('Email Marketing Consent', 'codeweber'),
			'slug'    => 'email-consent',
			'content' => <<<EOD
<!-- wp:paragraph -->
<p>Настоящим, при заполнении контактных форм на сайте, расположенном в информационно-телекоммуникационной сети Интернет по адресу [site_domain_link], действуя своей волей и в своем интересе, даю конкретное, информированное и сознательное согласие [redux_option key="legal_entity_dative"] (ОГРНИП [redux_option key="legal_ogrnip"] от [redux_option key="legal_ogrnip_date" format="d.m.Y"])(далее – Оператор), на получение информационной и рекламной рассылки (рекламной и иной информации) Оператора об услугах (товарах), предложениях, новостях Оператора и его партнерах и (или) акциях посредством отправки электронных писем на указанный мной адрес электронной почты, отправки СМС-сообщений на указанный мной номер телефона.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Настоящим я выражаю согласие на получение рекламы, распространяемой по сетям электросвязи, в том числе сети Интернет, согласно ч. 1 ст. 18 Федерального закона от 13.03.2006 №38-ФЗ «О рекламе».</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Принятием (акцептом) настоящего согласия является проставление галочки в чек-боксе (активация чек-бокса) рядом с текстом «Я даю свое согласие на получение рекламной рассылки» и нажатие кнопки «Отправить» (иной аналогичной кнопки) после заполнения всех необходимых полей контактной или иной формы на Сайте.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Настоящее Согласие является бессрочным. Я уведомлен(а) о том, что могу отозвать настоящее Согласие в любой момент путем нажатия (активации) кнопки или перехода по ссылке «Отписаться» в каждом электронном письме (при наличии), в личном кабинете пользователя Сайта и (или) путем направления электронного письма на адрес электронной почты, указанной в разделе «Реквизиты Оператора», с пометкой «Отказ от рассылки» («Отказ от получения рекламы»).</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>В случае, если пользователем Сайта, предоставляющим настоящее Согласие, является несовершеннолетнее или иное недееспособное лицо, настоящее Согласие считается предоставленным со стороны родителя (иного законного представителя) такого пользователя пока не будет установлено иное.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Реквизиты Оператора:</strong></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>[redux_option key="legal_entity_short"]<br>Адрес: [redux_option key="storage_address"]<br>ОГРНИП [redux_option key="legal_ogrnip"]<br>ИНН [redux_option key="taxpayer_identification_number"]<br>Тел. [redux_option key="phone_responsible_person"]<br>E-mail: [redux_option key="email_responsible_person"]</p>
<!-- /wp:paragraph -->
EOD
		],

		[
			'id'      => 'codeweber_legal_license_agreement',
			'title'   => __('License Agreement', 'codeweber'),
			'slug'    => 'license-agreement',
			'content' => 'This is the license agreement.',
		],
		[
			'id'      => 'codeweber_legal_seller_info',
			'title'   => __('Seller Information', 'codeweber'),
			'slug'    => 'seller-information',
			'content' => <<<EOD
<!-- wp:paragraph -->
<p>Редакция от 28.07.2025</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">1. <strong>Реквизиты компании</strong></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:table -->
<figure class="wp-block-table"><table class="has-fixed-layout"><tbody><tr><td><strong>Название организации:</strong></td><td>[redux_option key="legal_entity_short"]</td></tr><tr><td><strong>ИНН:</strong></td><td>[redux_option key="taxpayer_identification_number"]</td></tr><tr><td><strong>ОГРН(ОГРНИП):</strong></td><td>[redux_option key="legal_ogrnip"]</td></tr><tr><td><strong>КПП:</strong></td><td>[redux_option key="legal_kpp"]</td></tr><tr><td><strong>Телефон:</strong></td><td>[redux_option key="phone_responsible_person"]</td></tr><tr><td><strong>E-mail:</strong></td><td>[redux_option key="email_responsible_person"]</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">2. <strong>Юридический адрес</strong></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:table -->
<figure class="wp-block-table"><table class="has-fixed-layout"><tbody><tr><td><strong>Страна:</strong> </td><td>[redux_option key="juri-country"]</td></tr><tr><td><strong>Регион:</strong></td><td>[redux_option key="juri-region"]</td></tr><tr><td><strong>Город:</strong></td><td>[redux_option key="juri-city"]</td></tr><tr><td><strong>Улица:</strong></td><td>[redux_option key="juri-street"]</td></tr><tr><td><strong>Номер дома:</strong></td><td>[redux_option key="juri-house"]</td></tr><tr><td><strong>Офис:</strong></td><td>[redux_option key="juri-office"]</td></tr><tr><td><strong>Индекс:</strong></td><td>[redux_option key="juri-postal"]</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">3. <strong>Фактический адрес</strong></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:table -->
<figure class="wp-block-table"><table class="has-fixed-layout"><tbody><tr><td><strong>Страна:</strong> </td><td>[redux_option key="fact-country"]</td></tr><tr><td><strong>Регион:</strong></td><td>[redux_option key="fact-region"]</td></tr><tr><td><strong>Город:</strong></td><td>[redux_option key="fact-city"]</td></tr><tr><td><strong>Улица:</strong></td><td>[redux_option key="fact-street"]</td></tr><tr><td><strong>Номер дома:</strong></td><td>[redux_option key="fact-house"]</td></tr><tr><td><strong>Офис:</strong></td><td>[redux_option key="fact-office"]</td></tr><tr><td><strong>Индекс:</strong></td><td>[redux_option key="fact-postal"]</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">4. <strong><strong>Банковские реквизиты</strong></strong></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:table -->
<figure class="wp-block-table"><table class="has-fixed-layout"><tbody><tr><td><strong>Наименование банка:</strong></td><td>[redux_option key="bank-name"]</td></tr><tr><td><strong>БИК:</strong></td><td>[redux_option key="bank-bic"]</td></tr><tr><td><strong>Корр. счет:</strong></td><td>[redux_option key="bank-corr-account"]</td></tr><tr><td><strong>Расчетный счет:</strong></td><td>[redux_option key="bank-settlement-account"]</td></tr><tr><td><strong>ИНН банка:</strong></td><td>[redux_option key="bank-bank-tin"]</td></tr><tr><td><strong>КПП банка:</strong></td><td>[redux_option key="bank-bank-kpp"]</td></tr><tr><td><strong>Адрес банка:</strong></td><td>[redux_option key="bank-bank-address"]</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">5. <strong><strong><strong>Дополнительная информация</strong></strong></strong><br></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>Режим работы:</strong><br>Пн-Пт: 9:00–18:00, Сб-Вс: выходной</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Руководитель:</strong><br>[redux_option key="responsible_person"]</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Лицензии/Свидетельства:</strong><br><em>(если есть, можно оформить списком)</em></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->
EOD
		],
	];
}

/**
 * Создает дефолтные страницы типа записи 'legal' при активации темы.
 *
 * При активации темы функция проверяет наличие настроек с сохраненными ID страниц для каждого
 * юридического документа, получаемого из `codeweber_get_legal_fields()`. Если страница с таким ID
 * существует и опубликована — пропускает создание.
 *
 * Если же страницы с сохраненным ID нет, функция проверяет наличие страницы с нужным слагом в CPT 'legal'.
 * Если такая страница найдена — сохраняет ее ID в опциях и пропускает создание.
 *
 * Если страницы с таким слагом нет — создается новая страница в CPT 'legal' с указанным заголовком,
 * слагом, статусом "publish" и контентом, указанным в поле 'content' массива настроек.
 * ID созданной страницы сохраняется в опции.
 *
 * Ограничение: функция выполняется только для пользователей с правами 'manage_options'.
 *
 * Хук: выполняется при переключении темы — 'after_switch_theme'.
 *
 * Зависит от функции `codeweber_get_legal_fields()`, которая должна возвращать массив массивов, где
 * каждый элемент массива содержит ключи:
 * - 'id' — имя опции для сохранения ID страницы,
 * - 'slug' — слаг страницы,
 * - 'title' — заголовок страницы,
 * - 'content' — содержимое страницы.
 *
 * @return void
 */
function codeweber_create_default_legal_pages()
{
	if (!current_user_can('manage_options')) {
		return;
	}

	$fields = codeweber_get_legal_fields();

	foreach ($fields as $field) {
		// Проверяем, есть ли уже опубликованная страница с сохраненным ID
		$page_id = get_option($field['id']);
		if ($page_id && get_post_status($page_id) === 'publish') {
			continue; // Есть — пропускаем
		}

		// Проверяем, есть ли страница с таким слагом (чтобы не создавать дубликаты)
		$existing_page = get_page_by_path($field['slug'], OBJECT, 'legal');
		if ($existing_page) {
			update_option($field['id'], $existing_page->ID);
			continue;
		}

		// Создаем новую страницу CPT 'legal'
		$new_page_id = wp_insert_post([
			'post_title'   => wp_strip_all_tags($field['title']),
			'post_name'    => sanitize_title($field['slug']),
			'post_status'  => 'publish',
			'post_type'    => 'legal',
			'post_content' => $field['content'],
		]);

		if (!is_wp_error($new_page_id)) {
			update_option($field['id'], $new_page_id);
		}
	}
}
add_action('after_switch_theme', 'codeweber_create_default_legal_pages');



/**
 * Основная функция вывода данных разделов персональных данных.
 *
 * Получает из настроек Redux Framework набор разделов с персональными данными,
 * начиная с индекса 721 и последовательно увеличивая индекс, пока в настройках есть заголовок.
 * Для каждого раздела выводит структурированную таблицу с данными по:
 * - категории субъектов персональных данных,
 * - перечню обрабатываемых данных,
 * - категории данных,
 * - способу обработки,
 * - сроку обработки,
 * - способу уничтожения.
 *
 * Формат номера раздела — "7.2.{N}", где N — порядковый номер раздела начиная с 1.
 *
 * @param string $opt_name Название опции Redux, из которой берутся настройки.
 *
 * @return string HTML с разметкой всех найденных разделов.
 */
function render_personal_data_sections($opt_name)
{
	ob_start();

	$index = 721;
	while (true) {
		$title = Redux::get_option($opt_name, "title_pdn_data$index");

		if (empty($title)) {
			break;
		}

		$cat_subjects = Redux::get_option($opt_name, "cat_sub_pdn_data$index");
		$list_data    = Redux::get_option($opt_name, "list_pdn_data$index");
		$cat_data     = Redux::get_option($opt_name, "cat_pdn_data$index");
		$method       = Redux::get_option($opt_name, "method_pdn_data$index");
		$period       = Redux::get_option($opt_name, "period_pdn_data$index");
		$destruction  = Redux::get_option($opt_name, "destruction_pdn_data$index");

		$section_number = '7.2.' . ($index - 720);

		echo '<div class="pdn-section">';
		echo '<p><strong>' . esc_html($section_number) . '. Цель обработки "' . esc_html($title) . '"</strong></p>';
		echo '<div class="table-responsive">';
		echo '<figure class="wp-block-table"><table class=" has-fixed-layout" style="width:100%; table-layout: fixed;">';
		echo '<tbody>';

		$rows = [
			'Категория субъектов персональных данных:'       => $cat_subjects,
			'Перечень обрабатываемых персональных данных:'   => $list_data,
			'Категория обрабатываемых персональных данных:'  => $cat_data,
			'Способ обработки персональных данных:'          => $method,
			'Срок обработки персональных данных:'            => $period,
			'Способ уничтожения:'                            => $destruction,
		];

		foreach ($rows as $label => $value) {
			if (!is_null($value)) {
				echo '<tr>';
				echo '<td style="width:50%; vertical-align: top;"><strong>' . esc_html($label) . '</strong></td>';
				echo '<td style="width:50%; vertical-align: top;">' . esc_html($value) . '</td>';
				echo '</tr>';
			}
		}

		echo '</tbody></table></figure><p></p></div></div>';

		$index++;
	}

	return ob_get_clean();
}


/**
 * Шорткод [pdn_sections]
 * 
 * Выводит определённые разделы персональных данных согласно заданным индексам.
 * 
 * Атрибуты:
 * - indexes (string) — список индексов разделов, разделённых запятыми. Например: indexes="1,2,3"
 *   Если не указан, будет передан пустой массив.
 * 
 * Внутри функция преобразует строку с индексами в массив целых чисел и вызывает функцию
 * render_personal_data_sections, передавая глобальную переменную $opt_name и массив индексов.
 * 
 * Использование в контенте:
 * [pdn_sections indexes="1,3,5"]
 * 
 * @param array $atts Атрибуты шорткода.
 * @return string HTML с содержимым разделов персональных данных.
 */
function shortcode_pdn_sections($atts)
{
	global $opt_name;

	$atts = shortcode_atts(array(
		'indexes' => '',
	), $atts, 'pdn_sections');

	$indexes = array_map('intval', explode(',', $atts['indexes']));

	return render_personal_data_sections($opt_name, $indexes);
}
add_shortcode('pdn_sections', 'shortcode_pdn_sections');


// Добавляем метабокс для управления скрытием из архива
add_action('add_meta_boxes', function () {
	add_meta_box(
		'legal_hide_from_archive',
		__('Hide from Archive', 'codeweber'),
		'render_hide_from_archive_meta_box',
		'legal',
		'normal',
		'default'
	);
});

// Функция отображения метабокса
function render_hide_from_archive_meta_box($post)
{
	$value = get_post_meta($post->ID, '_hide_from_archive', true);
	wp_nonce_field('save_hide_from_archive', 'hide_from_archive_nonce');
?>
	<label>
		<input type="checkbox" name="hide_from_archive" value="1" <?php checked($value, '1'); ?>>
		<?php _e('Hide this document from archive', 'codeweber'); ?>
	</label>
<?php
}
