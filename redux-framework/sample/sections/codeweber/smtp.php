<?php

/**
 * Redux Framework SMTP config with i18n.
 * For full documentation, please visit: https://devs.redux.io/
 *
 * @package Redux Framework
 */


use PHPMailer\PHPMailer\PHPMailer;

Redux::set_section($opt_name, array(
	'title'  => __('SMTP Settings', 'codeweber'),
	'id'     => 'smtp_settings',
	'desc'   => __('Configure SMTP settings for sending emails from your website.', 'codeweber'),
	'icon'   => 'el el-envelope',
	'fields' => array(

		array(
			'id'      => 'smtp_presets',
			'type'    => 'raw',
			'title'   => __('Presets', 'codeweber'),
			'content' => '
                <div style="margin-bottom: 15px;">
                    <button type="button" class="button" onclick="setSMTPPreset(\'yandex\')">'.esc_html__('Yandex', 'codeweber').'</button>
                    <button type="button" class="button" onclick="setSMTPPreset(\'gmail\')">'.esc_html__('Gmail', 'codeweber').'</button>
                    <button type="button" class="button" onclick="setSMTPPreset(\'mailru\')">'.esc_html__('Mail.ru', 'codeweber').'</button>
                    <button type="button" class="button" onclick="setSMTPPreset(\'outlook\')">'.esc_html__('Outlook', 'codeweber').'</button>
                    <button type="button" class="button" onclick="setSMTPPreset(\'icloud\')">'.esc_html__('iCloud', 'codeweber').'</button>
                    <button type="button" class="button" onclick="setSMTPPreset(\'zoho\')">'.esc_html__('Zoho', 'codeweber').'</button>
                    <button type="button" class="button" onclick="setSMTPPreset(\'yahoo\')">'.esc_html__('Yahoo', 'codeweber').'</button>
                    <button type="button" class="button" onclick="setSMTPPreset(\'regru\')">'.esc_html__('Reg.ru', 'codeweber').'</button>
                    <button type="button" class="button" onclick="setSMTPPreset(\'beget\')">'.esc_html__('Beget', 'codeweber').'</button>
                </div>
                <script>
function setSMTPPreset(provider) {
	const map = {
		yandex: {
			host: "smtp.yandex.ru",
			port: "465",
			encryption: "ssl"
		},
		gmail: {
			host: "smtp.gmail.com",
			port: "587",
			encryption: "tls"
		},
		mailru: {
			host: "smtp.mail.ru",
			port: "465",
			encryption: "ssl"
		},
		outlook: {
			host: "smtp.office365.com",
			port: "587",
			encryption: "tls"
		},
		icloud: {
			host: "smtp.mail.me.com",
			port: "587",
			encryption: "tls"
		},
		zoho: {
			host: "smtp.zoho.com",
			port: "465",
			encryption: "ssl"
		},
		yahoo: {
			host: "smtp.mail.yahoo.com",
			port: "465",
			encryption: "ssl"
		},
		regru: {
			host: "smtp.timeweb.ru",
			port: "465",
			encryption: "ssl"
		},
		beget: {
			host: "smtp.beget.com",
			port: "465",
			encryption: "ssl"
		}
	};

	const settings = map[provider];
	if (!settings) return;

	const prefix = "redux_demo"; // Заменить на твой $opt_name, если он другой

	const setField = (id, value) => {
		const input = document.querySelector(`[name="${prefix}[${id}]"]`);
		if (!input) return;

		if (input.tagName === "SELECT") {
			input.value = value;
			input.dispatchEvent(new Event("change", { bubbles: true }));
		} else {
			input.value = value;
			input.dispatchEvent(new Event("input", { bubbles: true }));
		}
	};

	setField("smtp_host", settings.host);
	setField("smtp_port", settings.port);
	setField("smtp_encryption", settings.encryption);
}
</script>

            ',
		),

		array(
			'id'       => 'smtp_enabled',
			'type'     => 'switch',
			'title'    => __('Enable SMTP', 'codeweber'),
			'default'  => false,
			'on'       => __('Yes', 'codeweber'),
			'off'      => __('No', 'codeweber'),
		),

		array(
			'id'       => 'smtp_host',
			'type'     => 'text',
			'title'    => __('SMTP Host', 'codeweber'),
			'default'  => '',
			'required' => array('smtp_enabled', '=', true),
		),

		array(
			'id'       => 'smtp_port',
			'type'     => 'text',
			'title'    => __('Port', 'codeweber'),
			'default'  => '',
			'required' => array('smtp_enabled', '=', true),
		),

		array(
			'id'       => 'smtp_encryption',
			'type'     => 'select',
			'title'    => __('Encryption', 'codeweber'),
			'options'  => array(
				'none' => __('None', 'codeweber'),
				'ssl'  => 'SSL',
				'tls'  => 'TLS',
			),
			'default'  => '',
			'required' => array('smtp_enabled', '=', true),
		),

		array(
			'id'       => 'smtp_username',
			'type'     => 'text',
			'title'    => __('Username (login)', 'codeweber'),
			'required' => array('smtp_enabled', '=', true),
		),

		array(
			'id'       => 'smtp_password',
			'type'     => 'password',
			'title'    => __('Password', 'codeweber'),
			'required' => array('smtp_enabled', '=', true),
		),

		array(
			'id'       => 'smtp_from_email',
			'type'     => 'text',
			'title'    => __('From Email', 'codeweber'),
			'default'  => get_option('admin_email'),
			'required' => array('smtp_enabled', '=', true),
		),

		array(
			'id'       => 'smtp_from_name',
			'type'     => 'text',
			'title'    => __('From Name', 'codeweber'),
			'default'  => get_bloginfo('name'),
			'required' => array('smtp_enabled', '=', true),
		),

		array(
			'id'      => 'smtp_send_test_email',
			'type'    => 'raw',
			'title'   => __('Send Test Email', 'codeweber'),

			'content' => '
    <button type="button" class="button button-primary" id="send-test-email-btn">' . esc_html__('Send Test Email', 'codeweber') . '</button>
    <span id="smtp-send-email-result" style="margin-left: 15px; line-height: 2.2;"></span>

    <script>
    jQuery(document).ready(function($) {
        $("#send-test-email-btn").on("click", function() {
            $("#smtp-send-email-result").text(' . json_encode(__('Sending...', 'codeweber')) . ');
            const defaultEmail = "' . esc_js(get_option('admin_email')) . '";
            const data = {
                action: "redux_smtp_send_test_email",
                to_email: window.prompt(' . json_encode(__('Enter recipient email for test:', 'codeweber')) . ', defaultEmail)
            };

            if (!data.to_email) {
                $("#smtp-send-email-result").css("color", "red").text(' . json_encode(__('Email is required.', 'codeweber')) . ');
                return;
            }

            $.post("' . admin_url('admin-ajax.php') . '", data, function(response) {
                if (response.success) {
                    $("#smtp-send-email-result").css("color", "green").text(' . json_encode(__('Test email sent successfully!', 'codeweber')) . ');
                } else {
                    $("#smtp-send-email-result").css("color", "red").text(' . json_encode(__('Error: ', 'codeweber')) . ' + response.data);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                $("#smtp-send-email-result").css("color", "red").text(' . json_encode(__('AJAX error: ', 'codeweber')) . ' + textStatus);
                console.error("AJAX error:", textStatus, errorThrown);
            });
        });
    });
    </script>',
		),

		array(
			'id'      => 'smtp_test_connection',
			'type'    => 'raw',
			'title'   => __('Test SMTP Connection', 'codeweber'),
			'content' => '
                <button type="button" class="button" id="test-smtp-connection">'.esc_html__('Check SMTP Connection', 'codeweber').'</button>
                <span id="smtp-test-result" style="margin-left: 15px; line-height: 2.2;"></span>

                <script>
                jQuery(document).ready(function($){
                    $("#test-smtp-connection").on("click", function() {
                        $("#smtp-test-result").text('. json_encode(__('Checking...', 'codeweber')) .');
                        const data = {
                            action: "redux_smtp_test_connection",
                            host: $("[name*=\'[smtp_host]\']").val(),
                            port: $("[name*=\'[smtp_port]\']").val(),
                            encryption: $("[name*=\'[smtp_encryption]\']").val(),
                            username: $("[name*=\'[smtp_username]\']").val(),
                            password: $("[name*=\'[smtp_password]\']").val(),
                        };

                        $.post("' . admin_url('admin-ajax.php') . '", data, function(response) {
                            if(response.success) {
                                $("#smtp-test-result").css("color", "green").text('. json_encode(__('Connection successful!', 'codeweber')) .');
                            } else {
                                $("#smtp-test-result").css("color", "red").text('. json_encode(__('Connection failed: ', 'codeweber')) .' + response.data);
                            }
                        }).fail(function(jqXHR, textStatus, errorThrown) {
                            $("#smtp-test-result").css("color", "red").text('. json_encode(__('AJAX error: ', 'codeweber')) .' + textStatus);
                            console.error("AJAX error:", textStatus, errorThrown);
                        });
                    });
                });
                </script>
            ',
		),

	)
));

// Обработчик AJAX-запроса для проверки SMTP
add_action('wp_ajax_redux_smtp_test_connection', 'redux_smtp_test_connection_callback');
function redux_smtp_test_connection_callback()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error(__('Permission denied.', 'codeweber'));
	}

	if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
		require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
		require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
		require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
	}

	$host       = sanitize_text_field($_POST['host'] ?? '');
	$port       = intval($_POST['port'] ?? 0);
	$encryption = sanitize_text_field($_POST['encryption'] ?? '');
	$username   = sanitize_text_field($_POST['username'] ?? '');
	$password   = sanitize_text_field($_POST['password'] ?? '');

	if (empty($host) || empty($port) || empty($username) || empty($password)) {
		wp_send_json_error(__('Please fill in all required SMTP fields.', 'codeweber'));
	}

	$phpmailer = new PHPMailer(true);

	try {
		$phpmailer->isSMTP();
		$phpmailer->Host       = $host;
		$phpmailer->Port       = $port;
		$phpmailer->SMTPAuth   = true;
		$phpmailer->Username   = $username;
		$phpmailer->Password   = $password;

		if ($encryption === 'ssl') {
			$phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		} elseif ($encryption === 'tls') {
			$phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		} else {
			$phpmailer->SMTPSecure = false;
		}

		$phpmailer->Timeout = 10;

		if (!$phpmailer->smtpConnect()) {
			wp_send_json_error(__('Could not connect to SMTP server.', 'codeweber'));
		}

		$phpmailer->smtpClose();
		wp_send_json_success();
	} catch (\Exception $e) {
		wp_send_json_error($e->getMessage());
	}

	wp_die();
}


add_action('wp_ajax_redux_smtp_send_test_email', 'redux_smtp_send_test_email_callback');
function redux_smtp_send_test_email_callback()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error(__('Permission denied.', 'codeweber'));
	}

	$to_email = sanitize_email($_POST['to_email'] ?? '');
	if (!$to_email || !is_email($to_email)) {
		wp_send_json_error(__('Invalid email address.', 'codeweber'));
	}

	global $opt_name;

	// Получаем настройки SMTP из Redux
	$enabled    = Redux::get_option($opt_name, 'smtp_enabled');
	$host       = Redux::get_option($opt_name, 'smtp_host');
	$port       = Redux::get_option($opt_name, 'smtp_port');
	$encryption = Redux::get_option($opt_name, 'smtp_encryption');
	$username   = Redux::get_option($opt_name, 'smtp_username');
	$password   = Redux::get_option($opt_name, 'smtp_password');
	$from_email = Redux::get_option($opt_name, 'smtp_from_email');
	$from_name  = Redux::get_option($opt_name, 'smtp_from_name');

	if (!$enabled) {
		wp_send_json_error(__('SMTP is not enabled.', 'codeweber'));
	}

	if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
		require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
		require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
		require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
	}

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host       = $host;
		$mail->Port       = $port;
		$mail->SMTPAuth   = true;
		$mail->Username   = $username;
		$mail->Password   = $password;

		if ($encryption === 'ssl') {
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		} elseif ($encryption === 'tls') {
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		} else {
			$mail->SMTPSecure = false;
		}

		$mail->setFrom($from_email ?: $username, $from_name ?: get_bloginfo('name'));
		$mail->addAddress($to_email);
		$mail->Subject = __('Test Email from SMTP Settings', 'codeweber');
		$mail->Body    = __('This is a test email sent to confirm SMTP settings are correct.', 'codeweber');

		if (!$mail->send()) {
			wp_send_json_error(__('Mailer Error: ', 'codeweber') . $mail->ErrorInfo);
		}

		wp_send_json_success();
	} catch (Exception $e) {
		wp_send_json_error($e->getMessage());
	}

	wp_die();
}
