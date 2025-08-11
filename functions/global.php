<?php

/**
 * Custom global functions.
 */

/**
 *  Bootstrap Integration
 */
require 'bootstrap/bootstrap_pagination.php';
require 'bootstrap/bootstrap_post-nav.php';
require 'bootstrap/bootstrap_share-page.php';
require 'bootstrap/bootstrap_nav-menu.php';

/**
 *  Shortcodes
 */
require 'shortcodes.php';

/**
 *  SEO Integration
 */
require 'integrations/yoast_rankmath.php';

/**
 *  Redux Integration
 */
require 'integrations/redux-framework.php';

/**
 *  Personal Data Integration - Registration Form WP
 */
require 'personal-data.php';



/**
 * Разрешает загрузку файлов форматов SVG и SVGZ в WordPress.
 *
 * По умолчанию WordPress запрещает загрузку SVG из соображений безопасности.
 * Эта функция добавляет поддержку MIME-типов для SVG и SVGZ.
 *
 * @param array $mimes Массив разрешенных типов файлов.
 * @return array Обновленный массив MIME-типов с добавленной поддержкой SVG.
 */

function codeweber_svg_upload($mimes)
{
	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';

	return $mimes;
}
add_filter('upload_mimes', 'codeweber_svg_upload');



/**
 * Устанавливает корректный MIME-тип для SVG-файлов.
 *
 * WordPress по умолчанию блокирует загрузку SVG из соображений безопасности.
 * Эта функция исправляет MIME-тип, чтобы разрешить загрузку SVG и SVGZ файлов.
 *
 * @param array|null  $data     Данные о файле (тип, расширение).
 * @param string|null $file     Полный путь к файлу (необязательно).
 * @param string|null $filename Имя файла.
 * @param array|null  $mimes    Список разрешенных MIME-типов.
 * @return array|null Массив данных о файле с исправленным MIME-типом.
 */

function codeweber_svg_mimetype($data = null, $file = null, $filename = null, $mimes = null)
{
	$ext = isset($data['ext']) ? $data['ext'] : '';
	if (strlen($ext) < 1) {
		$exploded = explode('.', $filename);
		$ext      = strtolower(end($exploded));
	}
	if ('svg' === $ext) {
		$data['type'] = 'image/svg+xml';
		$data['ext']  = 'svg';
	} elseif ('svgz' === $ext) {
		$data['type'] = 'image/svg+xml';
		$data['ext']  = 'svgz';
	}

	return $data;
}
add_filter('wp_check_filetype_and_ext', 'codeweber_svg_mimetype', 10, 4);



/**
 * Изменяет длину отзыва (excerpt).
 *
 * Эта функция позволяет настроить количество слов в отзывах (excerpt) на 40 слов.
 *
 * @param int $length Длина отзыва в словах.
 * @return int Измененная длина отзыва.
 */
function codeweber_excerpt_length($length)
{
	return 40;
}
// add_filter( 'excerpt_length', 'codeweber_excerpt_length', 999 );



/**
 * Выводит атрибут "alt" для миниатюры записи.
 *
 * Функция получает альтернативный текст (alt) для миниатюры текущей записи и безопасно выводит его.
 * Полезно для улучшения SEO и доступности изображений.
 */
function codeweber_thumbnail_alt()
{
	$codeweber_thumbnail_alt = get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true);
	echo esc_attr($codeweber_thumbnail_alt);
}



/**
 * Форматирует номер телефона, оставляя только цифры.
 * - Если цифр больше трёх, добавляет в начале `+`.
 * - Если первая цифра `8` и номер длиннее трёх цифр, заменяет `8` на `7`.
 * - Если цифр три или меньше, оставляет их без изменений.
 *
 * @param string $text Входной текст, содержащий номер телефона.
 * @return string Отформатированный номер.
 */
function cleanNumber($digits)
{
	// Удаляем все символы, кроме цифр
	$digits = preg_replace('/\D/', '', $digits);

	// Если цифр больше трёх, обрабатываем номер
	if (strlen($digits) > 3) {
		// Если номер начинается с 8, заменяем на 7
		if ($digits[0] === '8') {
			$digits = '7' . substr($digits, 1);
		}
		return '+' . $digits;
	}

	return $digits;
}

/**
 * Выводит список ссылок на социальные сети в разных стилях.
 *
 * Функция берет ссылки из настроек WordPress (`get_option('socials_urls')`)
 * и отображает их в виде иконок, кнопок или комбинированных блоков.
 *
 * Доступные типы отображения:
 * - `type1`: круглые кнопки с фоном, каждая соцсеть — свой стиль
 * - `type2`: иконки в muted-стиле (серые)
 * - `type3`: обычные цветные иконки без кнопок
 * - `type4`: белые иконки
 * - `type5`: тёмные круглые кнопки
 * - `type6`: кнопки с иконками и названиями соцсетей (широкие)
 * - `type7`: кнопки с кастомным фоном соцсети (например, `btn-telegram`)
 *
 * Размеры:
 * - `lg`: большие кнопки
 * - `md`: средние (по умолчанию)
 * - `sm`: маленькие
 *
 * @param string $class Дополнительные CSS-классы для обёртки `<nav>`.
 * @param string $type Тип отображения (например, `type1`, `type6`, и т.д.).
 * @param string $size Размер иконок или кнопок (`lg`, `md`, `sm`). По умолчанию `'md'`.
 *
 * @return string HTML-код со ссылками на соцсети.
 */
function social_links($class, $type, $size = 'md')
{
	$socials = get_option('socials_urls');
	if (empty($socials)) {
		return '';
	}

	$size_classes = [
		'lg' => ['fs-60', 'btn-lg'],
		'md' => ['fs-45', 'btn-md'],
		'sm' => ['', 'btn-sm'],
	];

	$size_class = isset($size_classes[$size]) ? $size_classes[$size][0] : 'fs-35';
	$btn_size_class = isset($size_classes[$size]) ? $size_classes[$size][1] : 'btn-md';

	$nav_class = 'nav social gap-1';
	if ($type === 'type2') {
		$nav_class .= ' social-muted';
	} elseif ($type === 'type4') {
		$nav_class .= ' social-white';
	} elseif ($type === 'type7') {
		$nav_class = '';
	}

	if (isset($class) && $class !== NULL) {
		$nav_class .= ' ' . $class;
	}

	$output = '<nav class="' . esc_attr($nav_class) . '">';
	foreach ($socials as $social => $url) {
		if (!empty($url)) {
			$original_social = $social;

			switch ($social) {
				case 'telegram':
					$social = 'telegram-alt';
					break;
				case 'rutube':
					$social = 'rutube-1';
					break;
				case 'github':
					$social = 'github-alt';
					break;
				case 'ok':
					$social = 'ok-1';
					break;
				case 'vkmusic':
					$social = 'vk-music';
					break;
				case 'tik-tok':
					$social = 'tiktok';
					break;
				case 'googledrive':
					$social = 'google-drive';
					break;
				case 'googleplay':
					$social = 'google-play';
					break;
				case 'odnoklassniki':
					$social = 'square-odnoklassniki';
					break;
			}

			$icon_class = 'uil uil-' . esc_attr($social);
			$label = $original_social; // Можно заменить на перевод, если нужно

			if (stripos($label, 'vk') === 0) {
				$btnlabel = strtoupper(substr($label, 0, 2)) . substr($label, 2);
			} else {
				$btnlabel = ucfirst($label);
			}

			if ($type === 'type1') {
				$output .= '<a href="' . esc_url($url) . '" class="btn btn-circle ' . esc_attr($btn_size_class) . ' btn-' . esc_attr($social) . '" target="_blank"><i class="' . $icon_class . '"></i></a>';
			} elseif ($type === 'type5') {
				$output .= '<a href="' . esc_url($url) . '" class="btn btn-circle ' . esc_attr($btn_size_class) . ' btn-dark" target="_blank"><i class="' . $icon_class . '"></i></a>';
			} elseif ($type === 'type2' || $type === 'type3' || $type === 'type4') {
				$output .= '<a href="' . esc_url($url) . '" target="_blank"><i class="' . $icon_class . ' ' . esc_attr($size_class) . '"></i></a>';
			} elseif ($type === 'type6') {
				$output .= '<a role="button" href="' . esc_url($url) . '" target="_blank" title="' . esc_attr($label) . '" class="btn btn-icon btn-sm border btn-icon-start btn-white justify-content-between w-100 mb-2 me-2 fs-16"><i class="fs-20 ' . $icon_class . '"></i>' . $btnlabel . '</a>';
			} elseif ($type === 'type7') {
				$output .= '<a role="button" href="' . esc_url($url) . '" target="_blank" title="' . esc_attr($label) . '" class="btn btn-icon btn-sm btn-icon-start btn-' . $label . ' justify-content-between w-100 mb-2 me-2"><i class="fs-20 ' . $icon_class . '"></i>' . $btnlabel . '</a>';
			} else {
				$output .= '<a href="' . esc_url($url) . '" target="_blank"><i class="' . $icon_class . '"></i></a>';
			}
		}
	}
	$output .= '</nav>';
	return $output;
}


/**
 * Подключает файл шаблона pageheader из каталога /templates/pageheader/ темы.
 *
 * Работает аналогично get_header(), но подключает:
 * - templates/pageheader/pageheader-{name}.php
 * - или templates/pageheader/pageheader.php
 *
 * Шорткод [pageheader name="название"] подключает шаблон pageheader.
 *
 * Пример использования: [pageheader name="main"]
 * @param string|null $name Имя подшаблона (опционально).
 */
function get_pageheader($name = null)
{
	do_action('get_pageheader', $name);

	// Если имя не передано — берем из Redux Framework
	if (empty($name) && class_exists('Redux')) {
		global $opt_name;
		$name = Redux::get_option($opt_name, 'global-page-header-model');
	}

	// Путь к шаблону в корне темы
	$template = get_theme_file_path('pageheader.php');

	if (file_exists($template)) {

		// Подготавливаем переменные, которые хотим передать
		$pageheader_vars = [
			'name' => $name,
			// Здесь можно добавить любые другие переменные,
			// например, из Redux
		];

		// Распаковываем переменные в локальную область видимости шаблона
		extract($pageheader_vars);

		// Подключаем шаблон
		require $template;
	}
}




/**
 * Удобная обёртка для вывода отформатированных данных с помощью print_r.
 *
 * Используется для отладки, позволяет красиво вывести массивы и объекты.
 *
 * @param mixed $data Данные для вывода (массив, объект, строка и т.д.).
 * @param bool $return Если true — функция вернёт строку, вместо вывода её на экран.
 * @return string|null Возвращает отформатированную строку, если $return = true, иначе null.
 */
function printr($data, $return = false)
{
	$output = '<pre>' . print_r($data, true) . '</pre>';
	if ($return) {
		return $output;
	} else {
		echo $output;
	}
}




/**
 * Получает универсальный заголовок текущей страницы WordPress.
 *
 * Эта функция автоматически определяет тип текущей страницы и возвращает
 * соответствующий заголовок:
 * - Для одиночных записей и страниц — заголовок записи.
 * - Для архивов категорий, тегов, авторов, дат, таксономий и других архивов — заголовок архива.
 * - Для главной страницы и страницы блога — название сайта.
 * - Для страницы поиска — строка поиска.
 * - Для 404 страницы — сообщение об ошибке.
 * - Для архива магазина WooCommerce — заголовок, заданный WooCommerce.
 *
 * @return string Заголовок текущей страницы.
 */
function universal_title()
{
	// Получаем текущую страницу/запись и тип
	if (is_singular()) {
		// Для одиночных записей и страниц
		$post_id = get_the_ID();
		$post_type = get_post_type($post_id);

		// Проверяем, какой тип записи и выводим соответствующий заголовок
		if ('post' === $post_type) {
			$title = get_the_title($post_id);
		} elseif ('page' === $post_type) {
			$title = get_the_title($post_id);
		} elseif ('product' === $post_type) {
			$title = get_the_title($post_id);
		} else {
			$title = get_the_title($post_id);
		}
	} elseif (is_archive()) {
		// Для архивов
		if (is_category()) {
			$title = single_cat_title('', false);
		} elseif (is_tag()) {
			$title = single_tag_title('', false);
		} elseif (is_author()) {
			$title = get_the_author_meta('display_name');
		} elseif (is_date()) {
			$title = get_the_date();
		} elseif (is_tax()) {
			$title = single_term_title('', false);
		} elseif (is_shop() && class_exists('WooCommerce')) {
			// Для страницы архива магазина WooCommerce
			$title = woocommerce_page_title(false); // Используем функцию WooCommerce для вывода правильного заголовка
		} else {
			$title = get_the_archive_title();
		}

		// Убираем тег <span>, если он есть, для архивных страниц
		$title = strip_tags($title);
	} elseif (is_home()) {
		$title = get_bloginfo('name');
	} elseif (is_front_page()) {
		$title = get_bloginfo('name');
	} elseif (is_search()) {
		$title = sprintf(__('Search Results for: %s', 'codeweber'), get_search_query());
	} elseif (is_404()) {
		$title = __('Page Not Found', 'codeweber');
	} else {
		$title = get_bloginfo('name');
	}

	return esc_html($title);
}



/**
 * Изменяет заголовок архивной страницы для произвольных типов записей.
 * Заголовок берется из настроек Redux по ключу 'cpt-custom-title{PostType}'.
 *
 * Пример ключа: 'cpt-custom-titleFaq' для CPT с именем 'faq'.
 * Удаляет префикс "Архивы:" или "Archives:" из стандартного заголовка.
 *
 * @param string $title Стандартный заголовок архива.
 * @return string Новый заголовок архива.
 */
add_filter('get_the_archive_title', function ($title) {
	if (is_post_type_archive() && !is_admin()) {
		$post_type = get_post_type() ?: get_query_var('post_type');

		if ($post_type) {
			global $opt_name;

			$custom_title_id = 'cpt-custom-title' . ucwords($post_type);
			$custom_title = Redux::get_option($opt_name, $custom_title_id);

			if (!empty($custom_title)) {
				return $custom_title;
			}
		}

		$title = preg_replace('/^(Архивы|Archives):\s*/u', '', $title);
	}

	return $title;
});




/**
 * Возвращает подзаголовок для архивных страниц в зависимости от типа записи.
 * Подзаголовок берется из настроек Redux и выводится в заданной HTML-структуре.
 *
 * @global string $opt_name Имя настроек Redux.
 * @param string $html_structure Строка с HTML-разметкой, в которую будет вставлен подзаголовок.
 * 
 * @return string HTML-структура с подзаголовком.
 */
function the_subtitle($html_structure = '<p class="lead">%s</p>')
{
	// Проверяем, что это архивная страница и не админка
	if (is_archive() && !is_admin()) {
		// Получаем тип записи для текущего архива
		$post_type = get_post_type() ?: get_query_var('post_type');

		// Если тип записи определён
		if ($post_type) {
			global $opt_name;

			// Формируем ID для поля custom subtitle в зависимости от типа записи
			$custom_subtitle_id = 'cpt-custom-sub-title' . ucwords($post_type);

			// Получаем подзаголовок из настроек Redux
			$custom_subtitle = Redux::get_option($opt_name, $custom_subtitle_id);

			// Если подзаголовок найден, возвращаем его в указанной HTML-структуре
			if (!empty($custom_subtitle)) {
				return sprintf($html_structure, esc_html($custom_subtitle));
			}
		}
	}

	// Если подзаголовок не найден, возвращаем пустую строку в HTML-структуре
	return '';
}



/**
 * Получение стиля формы кнопки из Redux Framework с поддержкой класса по умолчанию
 * Также доступно как шорткод: [getthemebutton default=" rounded-pill"]
 *
 * @param string $default_class Класс по умолчанию
 * @return string CSS-класс формы кнопки
 */
if (! function_exists('getThemeButton')) {
	function getThemeButton($default_class = ' rounded-pill')
	{
		global $opt_name;

		// Карта соответствий опций Redux → CSS классы
		$style_map = [
			'1' => ' rounded-pill',
			'2' => '',
			'3' => ' rounded-xl',
			'4' => ' rounded-0',
		];

		// Получаем значение из Redux (по умолчанию '1')
		$style_key = Redux::get_option($opt_name, 'opt-button-select-style', '1');

		// Возвращаем класс из карты или переданный по умолчанию
		return isset($style_map[$style_key]) ? $style_map[$style_key] : $default_class;
	}
}

// Регистрируем шорткод [getthemebutton default=" ... "]
add_shortcode('getthemebutton', function ($atts) {
	$atts = shortcode_atts([
		'default' => ' rounded-pill',
	], $atts);

	return getThemeButton($atts['default']);
});


add_action('wp_footer', function () {
	global $opt_name;

	// Включен ли баннер
	$cookieBool = Redux::get_option($opt_name, 'enable_cookie_banner');

	// Текст из редактора
	$cookietext = do_shortcode(wp_kses_post(Redux::get_option($opt_name, 'welcome_text_cookie_banneer') ?? ''));

	// Кол-во дней хранения куки
	$cookie_days = (int) Redux::get_option($opt_name, 'cookie_expiration_date');
	if ($cookie_days <= 0) $cookie_days = 180;

	// Уникальное имя куки (на основе домена)
	$host = parse_url(home_url(), PHP_URL_HOST);
	$cookie_name = 'user_cookie_consent_' . md5($host);

	// Текущий URL
	$current_url = home_url(add_query_arg([], $_SERVER['REQUEST_URI']));
	// URL политики
	$cookie_policy_url = trim(do_shortcode('[url_cookie-policy]'));

	// 🧠 Проверка на поискового робота
	$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
	$is_bot = preg_match('/bot|crawl|slurp|spider|yandex|google|bing|baidu|duckduckgo/i', $user_agent);

	// Условия показа баннера
	if ($cookieBool && !$is_bot && !isset($_COOKIE[$cookie_name]) && $current_url !== $cookie_policy_url) {
?>
		<!-- Cookie Modal -->
		<div class="modal fade modal-popup modal-bottom-center" id="cookieModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-body p-6">
						<div class="row">
							<div class="col-md-12 col-lg-10 mb-4 mb-lg-0 my-auto align-items-center">
								<div class="mb-2 h4"><?php _e('Cookie Usage Policy', 'codeweber'); ?></div>
								<div class="cookie-modal-text fs-14"><?php echo $cookietext; ?></div>
							</div>
							<div class="col-md-5 col-lg-2 text-lg-end my-auto">
								<a href="#" class="btn btn-primary <?php getThemeButton(); ?>" id="acceptCookie" data-bs-dismiss="modal" aria-label="<?php esc_attr_e('Close', 'codeweber'); ?>">
									<?php _e('Accept', 'codeweber'); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- JS логика -->
		<script>
			document.getElementById('acceptCookie')?.addEventListener('click', function() {
				const days = <?php echo (int) $cookie_days; ?>;
				const now = new Date();
				const fd = now.toISOString().replace('T', ' ').substring(0, 19); // Дата согласия
				const ep = location.href; // Страница согласия
				const rf = document.referrer; // Откуда пришёл
				const value = `fd=${fd}|||ep=${ep}|||rf=${rf}`;
				const expires = new Date(Date.now() + days * 864e5).toUTCString();
				document.cookie = "<?php echo $cookie_name; ?>=" + encodeURIComponent(value) + "; expires=" + expires + "; path=/";
			});
		</script>
<?php
	}
});


/**
 * Подключение настроек SMTP из Redux к отправке почты WordPress.
 *
 * Этот код использует хук 'phpmailer_init', чтобы настроить PHPMailer
 * для отправки писем через SMTP сервер, параметры которого берутся
 * из Redux Framework опций.
 *
 * @global string $opt_name Имя опций Redux.
 *
 * Работает только если в настройках включен SMTP (smtp_enabled = true).
 *
 * Использует следующие поля из Redux:
 * - smtp_enabled    (bool)   — Включить SMTP или нет.
 * - smtp_host       (string) — Адрес SMTP сервера.
 * - smtp_port       (int)    — Порт SMTP.
 * - smtp_encryption (string) — Тип шифрования: 'none', 'ssl', 'tls'.
 * - smtp_username   (string) — Логин для SMTP.
 * - smtp_password   (string) — Пароль для SMTP.
 * - smtp_from_email (string) — Email отправителя.
 * - smtp_from_name  (string) — Имя отправителя.
 *
 * @param PHPMailer $phpmailer Объект PHPMailer, инициализируемый WP.
 */
add_action('phpmailer_init', function ($phpmailer) {
	global $opt_name;

	$settings = [
		'enabled'    => Redux::get_option($opt_name, 'smtp_enabled'),
		'host'       => Redux::get_option($opt_name, 'smtp_host'),
		'port'       => Redux::get_option($opt_name, 'smtp_port'),
		'encryption' => Redux::get_option($opt_name, 'smtp_encryption'),
		'username'   => Redux::get_option($opt_name, 'smtp_username'),
		'password'   => Redux::get_option($opt_name, 'smtp_password'),
		'from_email' => Redux::get_option($opt_name, 'smtp_from_email'),
		'from_name'  => Redux::get_option($opt_name, 'smtp_from_name'),
	];

	if (!$settings['enabled']) {
		// SMTP не включен — ничего не меняем
		return;
	}

	$phpmailer->isSMTP();
	$phpmailer->Host       = $settings['host'];
	$phpmailer->Port       = $settings['port'];
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Username   = $settings['username'];
	$phpmailer->Password   = $settings['password'];

	if ($settings['encryption'] === 'ssl') {
		$phpmailer->SMTPSecure = 'ssl';
	} elseif ($settings['encryption'] === 'tls') {
		$phpmailer->SMTPSecure = 'tls';
	} else {
		$phpmailer->SMTPSecure = false;
	}

	// Устанавливаем от кого письмо
	if (!empty($settings['from_email'])) {
		$phpmailer->From = $settings['from_email'];
	}
	if (!empty($settings['from_name'])) {
		$phpmailer->FromName = $settings['from_name'];
	}
});
