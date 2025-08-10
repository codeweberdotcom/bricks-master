<?php

/**
 * Добавляет чекбоксы согласий на форму регистрации WordPress
 */
add_action('register_form', function () {
	$privacy_page_id = (int) get_option('wp_page_for_privacy_policy');
	$privacy_url = $privacy_page_id ? get_permalink($privacy_page_id) : '';

	$processing_doc_id = (int) get_option('codeweber_legal_consent_processing');
	$processing_url = ($processing_doc_id && get_post_status($processing_doc_id) === 'publish') ? get_permalink($processing_doc_id) : '';
?>
	<p>
		<label>
			<input type="checkbox" name="privacy_policy_consent" value="1" <?php checked(!empty($_POST['privacy_policy_consent'])); ?> required>
			<?php
			if ($privacy_url) {
				printf(
					__('I have read and agree to the <a href="%s" target="_blank">Privacy Policy</a>', 'codeweber'),
					esc_url($privacy_url)
				);
			} else {
				echo __('I have read and agree to the Privacy Policy', 'codeweber');
				echo ' <span style="color:red; font-weight:bold;">(' . __('No Privacy Policy page selected by administrator', 'codeweber') . ')</span>';
			}
			?>
		</label>
	</p>
	<p>
		<label>
			<input type="checkbox" name="pdn_consent" value="1" <?php checked(!empty($_POST['pdn_consent'])); ?> required>
			<?php
			if ($processing_url) {
				printf(
					__('I agree to the <a href="%s" target="_blank">processing of personal data</a>', 'codeweber'),
					esc_url($processing_url)
				);
			} else {
				echo __('I agree to the processing of personal data', 'codeweber');
				echo ' <span style="color:red; font-weight:bold;">(' . __('No consent document selected by administrator', 'codeweber') . ')</span>';
			}
			?>
		</label>
	</p>
<?php
});


/**
 * Проверяет, были ли отмечены чекбоксы согласий при регистрации
 */
add_filter('registration_errors', function ($errors, $sanitized_user_login, $user_email) {
	if (empty($_POST['privacy_policy_consent'])) {
		$errors->add('privacy_policy_consent_error', __('You must agree to the Privacy Policy.', 'codeweber'));
	}
	if (empty($_POST['pdn_consent'])) {
		$errors->add('pdn_consent_error', __('You must agree to the processing of personal data.', 'codeweber'));
	}
	return $errors;
}, 10, 3);


function get_latest_revision_link($post_id)
{
	if (!$post_id) {
		return '';
	}

	$revisions = wp_get_post_revisions($post_id);

	if (empty($revisions)) {
		return '';
	}

	// Получаем самую последнюю ревизию (по дате)
	$last_revision = array_shift($revisions); // array is sorted DESC by default

	$rev_id = $last_revision->ID;
	$rev_date = get_the_date('Y-m-d H:i', $rev_id);
	$link = admin_url('revision.php?revision=' . $rev_id);

	return sprintf(
		'<a href="%s" target="_blank">%s</a>',
		esc_url($link),
		sprintf(
			/* translators: %s — дата ревизии документа */
			__('Version from %s', 'codeweber'),
			esc_html($rev_date)
		)
	);
}

/**
 * Сохраняет согласия пользователя после успешной регистрации
 */
add_action('user_register', function ($user_id) {
	$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
	$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
	$timestamp = current_time('mysql');

	$privacy_page_id = (int) get_option('wp_page_for_privacy_policy');
	$processing_doc_id = (int) get_option('codeweber_legal_consent_processing');

	$consents = [];

	$session_id = uniqid('wp_', true);

	// Согласие на политику конфиденциальности
	if (!empty($_POST['privacy_policy_consent'])) {
		$consents['privacy_policy'] = [
			'title'      => get_the_title($privacy_page_id),
			'url'        => get_permalink($privacy_page_id),
			'ip'         => $ip_address,
			'user_agent' => $user_agent,
			'date'       => $timestamp,
			'session_id'  => $session_id,
			'revision'    => get_latest_revision_link($privacy_page_id),
			'form_title'  => __('Wordpress Register Form', 'codeweber'),
			'page_url'   => esc_url(site_url('/wp-login.php?action=register')),
		];
	}

	// Согласие на обработку персональных данных (пример)
	if (!empty($_POST['pdn_consent'])) {
		$consents['pdn_processing'] = [
			'title'      => get_the_title($processing_doc_id),
			'url'        => get_permalink($processing_doc_id),
			'ip'         => $ip_address,
			'user_agent' => $user_agent,
			'date'       => $timestamp,
			'session_id'  => $session_id,
			'revision'    => get_latest_revision_link($processing_doc_id),
			'form_title'  => __('Wordpress Register Form', 'codeweber'),
			'page_url'   => esc_url(site_url('/wp-login.php?action=register')),
		];
	}

	if (!empty($consents)) {
		update_user_meta($user_id, 'codeweber_user_consents', $consents);
	}
});



/**
 * Экспорт пользовательских согласий в WordPress (GDPR Personal Data Export)
 *
 * Добавляет секцию "User Consents" при экспорте персональных данных пользователя,
 * извлекая информацию о согласиях, сохранённую в user_meta с ключом 'codeweber_user_consents'.
 * Форматирует данные с переносами строк и читаемыми URL.
 *
 * Поддерживает:
 * - HTML-форматирование с <br> и ссылками
 * - Преобразование page_id в permalink
 * - Получение заголовка документа по ID
 * - Локализацию с помощью __()
 *
 * @see https://developer.wordpress.org/reference/hooks/wp_privacy_personal_data_exporters/
 * @return array
 */
add_filter('wp_privacy_personal_data_exporters', function ($exporters) {
	$exporters['user_consents'] = [
		'exporter_friendly_name'  => __('User Consents', 'codeweber'),
		'exporter_description'    => __(
			'This section includes all user consents stored in the system. ' .
				'For each consent, the date, IP address, document title, and a link are displayed. ' .
				'Links to WordPress pages are automatically converted to permalinks with titles. ' .
				'Data is formatted with HTML support for line breaks and clickable links.',
			'codeweber'
		),
		'callback' => 'codeweber_user_consents_exporter',
	];
	return $exporters;
});

function codeweber_user_consents_exporter($email_address)
{
	$user = get_user_by('email', $email_address);
	if (!$user) {
		return ['data' => [], 'done' => true];
	}

	$consents = get_user_meta($user->ID, 'codeweber_user_consents', true);
	if (!is_array($consents)) {
		return ['data' => [], 'done' => true];
	}


	$export_items = [];

	foreach ($consents as $key => $data) {
		$label_key = preg_replace('/_\d+$/', '', $key);
		$label = ucfirst(str_replace('_', ' ', $label_key));

		$url = esc_url($data['url'] ?? '');
		$url_display = $url;

		if (preg_match('/[?&]page_id=(\d+)/', $url, $matches)) {
			$page_id = (int)$matches[1];
			$permalink = get_permalink($page_id);

			if ($permalink) {
				$url = esc_url($permalink);
				$url_display = $permalink;
			}

			if (empty($data['title'])) {
				$page = get_post($page_id);
				if ($page) {
					$data['title'] = get_the_title($page_id);
				}
			}
		}

		// Получаем телефон пользователя (замените 'phone' на актуальный мета-ключ)
		$phone = $data['phone'];

		$page_url = !empty($data['page_url'])
			? esc_url($data['page_url'])
			: esc_url(site_url('/wp-login.php?action=register'));

		$entry_data = [
			[
				'name'  => __('Consent Label', 'codeweber'),
				'value' => $label,
			],
			[
				'name'  => __('Session ID', 'codeweber'),
				'value' => $data['session_id'] ?? '',
			],
			[
				'name'  => __('Form Title', 'codeweber'),
				'value' => $data['form_title'] ?? '',
			],
			[
				'name'  => __('Agreed on', 'codeweber'),
				'value' => $data['date'] ?? '',
			],
			[
				'name'  => __('IP Address', 'codeweber'),
				'value' => $data['ip'] ?? '',
			],
			[
				'name'  => __('User Agent', 'codeweber'),
				'value' => $data['user_agent'] ?? __('Not provided', 'codeweber'),
			],
			[
				'name'  => __('Document', 'codeweber'),
				'value' => $data['title'] ?? '',
			],
			[
				'name'  => __('Consent Html', 'codeweber'),
				'value' => $data['acceptance_html'] ?? '',
			],
			[
				'name'  => __('Document Link', 'codeweber'),
				'value' => $url_display,
			],
			[
				'name'  => __('Agreed on Page', 'codeweber'),
				'value' => $page_url,
			],
			[
				'name'  => __('Phone', 'codeweber'),
				'value' => $phone ?: __('Not provided', 'codeweber'),
			],
		];

		if (!empty($data['revision'])) {
			$entry_data[] = [
				'name'  => __('Revision', 'codeweber'),
				'value' => $data['revision'],
			];
		}

		$export_items[] = [
			'group_id'    => 'user-consents',
			'group_label' => __('User Consents', 'codeweber'),
			'item_id'     => "user-consent-{$key}",
			'data'        => $entry_data,
		];
	}

	return ['data' => $export_items, 'done' => true];
}


/**
 * Регистрирует eraser для пользовательских согласий в интерфейсе WordPress
 */
add_filter('wp_privacy_personal_data_erasers', function ($erasers) {
	$erasers['codeweber_user_consents'] = [
		'eraser_friendly_name' => __('User Consents', 'codeweber'),
		'callback'             => 'codeweber_user_consents_eraser',
	];
	return $erasers;
});

/**
 * Callback-функция для удаления пользовательских согласий
 *
 * @param string $email_address Email пользователя
 * @param int    $page          Номер страницы (поддержка пагинации)
 * @return array
 */
function codeweber_user_consents_eraser($email_address, $page = 1)
{

	$user = get_user_by('email', $email_address);
	if (!$user) {
		return [
			'items_removed'  => [],
			'items_retained' => [],
			'done'           => true,
		];
	}

	// Проверим, есть ли мета-данные
	$meta = get_user_meta($user->ID, 'codeweber_user_consents', true);

	if (empty($meta)) {
		return [
			'items_removed'  => [],
			'items_retained' => [],
			'done'           => true,
		];
	}

	// Удаляем
	$deleted = delete_user_meta($user->ID, 'codeweber_user_consents');

	return [
		'items_removed'  => $deleted ? [
			[
				'id'    => 'codeweber_user_consents',
				'name'  => __('User Consents', 'codeweber'),
			]
		] : [],
		'items_retained' => [],
		'done'           => true,
	];
}