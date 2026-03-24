<?php

/**
 * Social Links — функции вывода ссылок на соцсети.
 *
 * codeweber_social_links()         — универсальный рендер иконок (type1–type9)
 * codeweber_single_social_links()  — обёртка для single-страниц (дефолты из Redux)
 * codeweber_global_social_style()  — возвращает глобальный стиль из Redux
 * staff_social_links()             — соцсети сотрудника из метаполей _staff_*
 * vacancy_social_links()           — соцсети вакансии из метаполей _vacancy_*
 */

defined( 'ABSPATH' ) || exit;

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
 * - `type8`: кнопки с настраиваемым цветом и стилем (solid/outline), без обертки nav social (по умолчанию primary solid)
 * - `type9`: кнопки primary outline (фиксированные параметры), без обертки nav social
 *
 * Размеры:
 * - `lg`: большие кнопки
 * - `md`: средние (по умолчанию)
 * - `sm`: маленькие
 *
 * Для type8 доступны дополнительные параметры:
 * - `button_color`: цвет кнопки (primary, red, blue, green и т.д. - все цвета темы)
 * - `buttonstyle`: стиль кнопки (solid или outline)
 * - `button_form`: форма кнопки (circle или block). По умолчанию `'circle'`.
 *
 * @param string $class Дополнительные CSS-классы для обёртки `<nav>`.
 * @param string $type Тип отображения (например, `type1`, `type6`, и т.д.).
 * @param string $size Размер иконок или кнопок (`lg`, `md`, `sm`). По умолчанию `'md'`.
 * @param string $button_color Цвет кнопки для type8 (primary, red, blue и т.д.). По умолчанию `'primary'`.
 * @param string $buttonstyle Стиль кнопки для type8 (solid или outline). По умолчанию `'solid'`.
 * @param string $button_form Форма кнопки (circle или block). По умолчанию `'circle'`.
 * @param array|null $custom_socials Опционально. Кастомный список ссылок вместо глобальных. Формат: либо [ 'key' => 'url' ],
 *   либо расширенный [ 'key' => [ 'url' => '...', 'icon' => 'uil-name', 'label' => '...', 'social_name' => 'linkedin', 'target_blank' => false ] ].
 *
 * @return string HTML-код со ссылками на соцсети.
 */
function codeweber_social_links($class, $type, $size = 'md', $button_color = 'primary', $buttonstyle = 'solid', $button_form = 'circle', $custom_socials = null)
{
	$socials = ($custom_socials !== null && is_array($custom_socials)) ? $custom_socials : get_option('socials_urls');
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

	// Стиль скругления из темы (getThemeButton) применяем только для btn-block; для circle не меняем
	if ($button_form === 'block') {
		$theme_btn_form = class_exists('Codeweber_Options') ? Codeweber_Options::style('button') : '';
		$btn_form_class = 'btn-block' . $theme_btn_form;
	} else {
		$btn_form_class = 'btn-circle';
	}

	// Для type8 и type9 используем обертку nav gap-2 (без social)
	$use_nav_wrapper = true;

	$nav_class = 'nav social gap-2';
	if ($type === 'type2') {
		$nav_class .= ' social-muted';
	} elseif ($type === 'type4') {
		$nav_class .= ' social-white';
	} elseif ($type === 'type7') {
		$nav_class = 'nav gap-2 social-white';
	}

	if (isset($class) && $class !== NULL) {
		// Для type3 и type6 удаляем social-white, если он передан в $class (type3 и type6 должны быть цветными)
		if ($type === 'type3' || $type === 'type6') {
			$class = preg_replace('/\bsocial-white\b/', '', $class);
			$class = trim($class);
		}

		// Если в $class есть gap-*, заменяем gap-2 на новый gap
		if (preg_match('/\bgap-\d+\b/', $class)) {
			$nav_class = preg_replace('/\bgap-\d+\b/', '', $nav_class);
			$nav_class = trim($nav_class);
		}
		$nav_class .= ' ' . $class;
	}

	// Для type8 и type9 используем обертку nav gap-2 (без social)
	if ($type === 'type8' || $type === 'type9') {
		// Для type8 и type9 используем nav gap-2, можно добавить дополнительные классы из $class
		$nav_gap_class = 'nav gap-2';
		if (!empty($class)) {
			$nav_gap_class .= ' ' . esc_attr($class);
		}
		$output = '<nav class="' . $nav_gap_class . '">';
	} else {
		$output = '<nav class="' . esc_attr($nav_class) . '">';
	}
	foreach ($socials as $key => $item) {
		$is_extended = is_array($item) && isset($item['url']);
		if ($is_extended) {
			$url = $item['url'];
			if (empty($url)) {
				continue;
			}
			$icon_name = (isset($item['icon']) && (string) $item['icon'] !== '') ? $item['icon'] : 'link';
			$icon_class = 'uil uil-' . esc_attr($icon_name);
			$label = isset($item['label']) ? $item['label'] : $key;
			$btnlabel = esc_html($label);
			$btn_social = isset($item['social_name']) ? $item['social_name'] : 'primary';
			$btn_social_type7 = $btn_social;
			$target_attr = (isset($item['target_blank']) && $item['target_blank'] === false) ? '' : ' target="_blank" rel="noopener"';
		} else {
			$url = $item;
			if (empty($url)) {
				continue;
			}
			$original_social = $key;
			$social = $key;
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
			$label = $original_social;
			if (stripos($label, 'vk') === 0) {
				$btnlabel = strtoupper(substr($label, 0, 2)) . substr($label, 2);
			} else {
				$btnlabel = ucfirst($label);
			}
			$btn_social = $social;
			$btn_social_type7 = $original_social;
			$target_attr = ' target="_blank" rel="noopener"';
		}

		if ($type === 'type1') {
			$output .= '<a href="' . esc_url($url) . '" class="btn ' . esc_attr($btn_form_class) . ' lh-1 has-ripple ' . esc_attr($btn_size_class) . ' btn-' . esc_attr($btn_social) . '"' . $target_attr . '><i class="' . esc_attr( $icon_class ) . '"></i></a>';
		} elseif ($type === 'type5') {
			$output .= '<a href="' . esc_url($url) . '" class="btn ' . esc_attr($btn_form_class) . ' lh-1 has-ripple ' . esc_attr($btn_size_class) . ' btn-dark"' . $target_attr . '><i class="' . esc_attr( $icon_class ) . '"></i></a>';
		} elseif ($type === 'type2' || $type === 'type3' || $type === 'type4') {
			$output .= '<a href="' . esc_url($url) . '" class="lh-1 has-ripple"' . $target_attr . '><i class="' . esc_attr( $icon_class ) . '"></i></a>';
		} elseif ($type === 'type6') {
			$output .= '<a role="button" href="' . esc_url($url) . '"' . $target_attr . ' title="' . esc_attr($label) . '" class="btn btn-icon btn-sm border btn-icon-start btn-white justify-content-between w-100 fs-16 lh-1 has-ripple"><i class="fs-20 ' . esc_attr( $icon_class ) . '"></i>' . $btnlabel . '</a>';
		} elseif ($type === 'type7') {
			$output .= '<a role="button" href="' . esc_url($url) . '"' . $target_attr . ' title="' . esc_attr($label) . '" class="btn btn-icon btn-sm btn-icon-start btn-' . esc_attr($btn_social_type7) . ' justify-content-between w-100 lh-1 has-ripple"><i class="fs-20 ' . esc_attr( $icon_class ) . '"></i>' . $btnlabel . '</a>';
		} elseif ($type === 'type8') {
			$btn_color_val = !empty($button_color) ? esc_attr($button_color) : 'primary';
			$btn_style = ($buttonstyle === 'outline') ? 'outline' : 'solid';
			if ($btn_style === 'outline') {
				$btn_class = 'btn ' . esc_attr($btn_form_class) . ' lh-1 has-ripple btn-outline-' . $btn_color_val . ' ' . esc_attr($btn_size_class);
			} else {
				$btn_class = 'btn ' . esc_attr($btn_form_class) . ' lh-1 has-ripple btn-' . $btn_color_val . ' ' . esc_attr($btn_size_class);
			}
			$output .= '<a href="' . esc_url($url) . '" class="' . $btn_class . '"' . $target_attr . ' title="' . esc_attr($label) . '"><i class="' . esc_attr( $icon_class ) . '"></i></a>';
		} elseif ($type === 'type9') {
			$btn_class = 'btn ' . esc_attr($btn_form_class) . ' lh-1 has-ripple btn-outline-primary ' . esc_attr($btn_size_class);
			$output .= '<a href="' . esc_url($url) . '" class="' . $btn_class . '"' . $target_attr . ' title="' . esc_attr($label) . '"><i class="' . esc_attr( $icon_class ) . '"></i></a>';
		} else {
			$output .= '<a href="' . esc_url($url) . '" class="lh-1 has-ripple"' . $target_attr . '><i class="' . esc_attr( $icon_class ) . '"></i></a>';
		}
	}

	$output .= '</nav>';

	return $output;
}

/**
 * Единые соцссылки для single-страниц (блог, legal и т.д.).
 * Дефолты соответствуют single post (блог): type3, sm, primary, solid, circle.
 *
 * @param array $args {
 *     Опционально. Параметры, передаваемые в social_links().
 *     @type string $class       Доп. CSS-классы для обёртки. По умолчанию ''.
 *     @type string $type        Тип отображения. По умолчанию 'type3'.
 *     @type string $size        Размер: 'lg', 'md', 'sm'. По умолчанию 'sm'.
 *     @type string $button_color Цвет кнопки для type8. По умолчанию 'primary'.
 *     @type string $buttonstyle  solid|outline для type8. По умолчанию 'solid'.
 *     @type string $button_form  circle|block. По умолчанию 'circle'.
 * }
 * @return string HTML соцссылок или пустая строка, если social_links не существует.
 */
function codeweber_single_social_links($args = [])
{
	if (!function_exists('codeweber_social_links')) {
		return '';
	}
	$defaults = [
		'class'        => '',
		'type'         => 'type3',
		'size'         => 'sm',
		'button_color' => 'primary',
		'buttonstyle'  => 'solid',
		'button_form'  => 'circle',
	];
	// Стили из Redux (Theme Style → Codeweber Icons), если не переданы в $args
	if (Codeweber_Options::is_ready()) {
		if (!isset($args['type']) || $args['type'] === '') {
			$icon_type = Codeweber_Options::get('global-social-icon-type', Codeweber_Options::get('social-icon-type', '1'));
			$defaults['type'] = 'type' . ($icon_type ? $icon_type : '1');
		}
		if (!isset($args['size']) || $args['size'] === '') {
			$defaults['size'] = Codeweber_Options::get('global-social-button-size', 'md');
		}
		if (!isset($args['button_form']) || $args['button_form'] === '') {
			$defaults['button_form'] = Codeweber_Options::get('global-social-button-style', 'circle');
		}
	}
	$r = wp_parse_args($args, $defaults);
	return codeweber_social_links(
		$r['class'],
		$r['type'],
		$r['size'],
		$r['button_color'],
		$r['buttonstyle'],
		$r['button_form']
	);
}

/**
 * Возвращает параметры глобального стиля соцсетей из Redux (Theme Style → Global Social Style).
 * Используется в карточках staff на архивах и в синглах.
 *
 * @return array [ 'type' => string, 'size' => string, 'button_form' => string ]
 */
function codeweber_global_social_style()
{
	$type = 'type1';
	$size = 'md';
	$button_form = 'circle';
	if (Codeweber_Options::is_ready()) {
		$icon_type = Codeweber_Options::get('global-social-icon-type', Codeweber_Options::get('social-icon-type', '1'));
		$type = 'type' . ($icon_type ? $icon_type : '1');
		$size = Codeweber_Options::get('global-social-button-size', 'md');
		$button_form = Codeweber_Options::get('global-social-button-style', 'circle');
	}
	return compact('type', 'size', 'button_form');
}

/**
 * Выводит список ссылок на социальные сети для staff из метаполей записи.
 * Строится на базе social_links(): собирает массив ссылок из метаполей _staff_* и передаёт в social_links().
 *
 * Доступные типы отображения — те же, что у social_links(): type1–type9.
 *
 * @param int $post_id ID записи staff
 * @param string $class Дополнительные CSS-классы для обёртки <nav>.
 * @param string $type Тип отображения (type1, type2, …). По умолчанию `type1`.
 * @param string $size Размер иконок или кнопок (`lg`, `md`, `sm`). По умолчанию `'sm'`.
 * @param string $button_color Цвет кнопки для type8. По умолчанию `'primary'`.
 * @param string $buttonstyle Стиль кнопки для type8 (solid или outline). По умолчанию `'solid'`.
 * @param string $button_form Форма кнопки (circle или block). По умолчанию `'circle'`.
 * @return string HTML-код со ссылками на соцсети.
 */
function staff_social_links($post_id, $class = '', $type = 'type1', $size = 'sm', $button_color = 'primary', $buttonstyle = 'solid', $button_form = 'circle')
{
	$staff_social_fields = [
		'facebook'  => [ 'icon' => 'facebook-f', 'label' => 'Facebook', 'social_name' => 'facebook' ],
		'twitter'   => [ 'icon' => 'twitter', 'label' => 'Twitter', 'social_name' => 'twitter' ],
		'linkedin'  => [ 'icon' => 'linkedin', 'label' => 'LinkedIn', 'social_name' => 'linkedin' ],
		'instagram' => [ 'icon' => 'instagram', 'label' => 'Instagram', 'social_name' => 'instagram' ],
		'telegram'  => [ 'icon' => 'telegram-alt', 'label' => 'Telegram', 'social_name' => 'telegram' ],
		'vk'        => [ 'icon' => 'vk', 'label' => 'VKontakte', 'social_name' => 'primary' ],
		'whatsapp'  => [ 'icon' => 'whatsapp', 'label' => 'WhatsApp', 'social_name' => 'whatsapp' ],
		'skype'     => [ 'icon' => 'skype', 'label' => 'Skype', 'social_name' => 'skype' ],
		'website'   => [ 'icon' => 'globe', 'label' => 'Website', 'social_name' => 'primary' ],
	];

	$custom_socials = [];
	foreach ($staff_social_fields as $key => $field) {
		$url = get_post_meta($post_id, '_staff_' . $key, true);
		if (!empty($url)) {
			$custom_socials[$key] = [
				'url'         => $url,
				'icon'        => $field['icon'],
				'label'       => $field['label'],
				'social_name' => $field['social_name'],
				'target_blank' => true,
			];
		}
	}

	if (empty($custom_socials)) {
		return '';
	}

	return codeweber_social_links($class, $type, $size, $button_color, $buttonstyle, $button_form, $custom_socials);
}

/**
 * Выводит список ссылок на социальные сети для вакансий из метаполей записи.
 * Строится на базе social_links(): собирает кастомный массив ссылок из меты вакансии
 * и передаёт его в social_links() вместе с параметрами стиля — стилистикой иконок
 * управляет одна функция social_links().
 *
 * @param int $post_id ID записи vacancy
 * @param string $class Дополнительные CSS-классы для обёртки <nav>.
 * @param string $type Тип отображения (type1–type9). По умолчанию `type1`.
 * @param string $size Размер иконок или кнопок (`lg`, `md`, `sm`). По умолчанию `'sm'`.
 * @param string $button_color Цвет кнопки для type8. По умолчанию `'primary'`.
 * @param string $buttonstyle Стиль кнопки для type8 (solid или outline). По умолчанию `'solid'`.
 * @param string $button_form Форма кнопки (circle или block). По умолчанию `'circle'`.
 * @return string HTML-код со ссылками на соцсети.
 */
function vacancy_social_links($post_id, $class = '', $type = 'type1', $size = 'sm', $button_color = 'primary', $buttonstyle = 'solid', $button_form = 'circle')
{
	$vacancy_fields = [
		'email'        => [ 'icon' => 'envelope', 'label' => 'Email', 'social_name' => 'primary', 'target_blank' => false ],
		'linkedin_url' => [ 'icon' => 'linkedin', 'label' => 'LinkedIn', 'social_name' => 'linkedin', 'target_blank' => true ],
		'apply_url'    => [ 'icon' => 'link', 'label' => __('Vacancy URL', 'codeweber'), 'social_name' => 'primary', 'target_blank' => true ],
	];

	$custom_socials = [];
	foreach ($vacancy_fields as $field_key => $field_data) {
		$url = get_post_meta($post_id, '_vacancy_' . $field_key, true);
		if (!empty($url)) {
			if ($field_key === 'email') {
				$url = 'mailto:' . $url;
			}
			$custom_socials[$field_key] = [
				'url'         => $url,
				'icon'        => $field_data['icon'],
				'label'       => $field_data['label'],
				'social_name' => $field_data['social_name'],
				'target_blank'=> $field_data['target_blank'],
			];
		}
	}

	if (empty($custom_socials)) {
		return '';
	}

	return codeweber_social_links($class, $type, $size, $button_color, $buttonstyle, $button_form, $custom_socials);
}
