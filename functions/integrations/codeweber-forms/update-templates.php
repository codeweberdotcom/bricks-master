<?php
/**
 * Script to update email templates in database
 * 
 * Run this once to update templates with DOCTYPE
 * Access via: /wp-content/themes/codeweber/functions/integrations/codeweber-forms/update-templates.php
 * 
 * @package Codeweber
 */

// Load WordPress
$wp_load_paths = [
    __DIR__ . '/../../../../../../wp-load.php',
    __DIR__ . '/../../../../../wp-load.php',
    dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/wp-load.php'
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('WordPress not found. Please check the path.');
}

if (!current_user_can('manage_options')) {
    die('Access denied. You must be logged in as administrator.');
}

// Load the templates class
require_once(__DIR__ . '/admin/codeweber-forms-email-templates.php');

$templates = new CodeweberFormsEmailTemplates();

// Get current options
$current_options = get_option('codeweber_forms_email_templates', []);

// Update templates with new versions using class methods
$updated_options = $current_options;

// Admin Notification Template
$updated_options['admin_notification_template'] = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0073aa; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background-color: #f5f5f5; padding: 10px; text-align: left; border: 1px solid #ddd; }
        td { padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Новая отправка формы</h2>
        </div>
        <div class="content">
            <p><strong>Форма:</strong> {form_name}</p>
            <p><strong>Дата:</strong> {submission_date} {submission_time}</p>
            <p><strong>От:</strong> {user_name} ({user_email})</p>
            <hr>
            <h3>Поля формы:</h3>
            {form_fields}
        </div>
        <div class="footer">
            <p><small>IP: {user_ip}<br>User Agent: {user_agent}</small></p>
        </div>
    </div>
</body>
</html>';

// Auto-Reply Template
$updated_options['auto_reply_template'] = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0073aa; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Спасибо за обращение!</h2>
        </div>
        <div class="content">
            <p>Здравствуйте, {user_name}!</p>
            <p>Мы получили ваше сообщение и свяжемся с вами в ближайшее время.</p>
            <p>С уважением,<br>{site_name}</p>
        </div>
    </div>
</body>
</html>';

// Testimonial Reply Template
$updated_options['testimonial_reply_template'] = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Спасибо за ваш отзыв!</h2>
        </div>
        <div class="content">
            <p>Здравствуйте, {user_name}!</p>
            <p>Благодарим вас за оставленный отзыв. Ваше мнение очень важно для нас!</p>
            <p>После модерации ваш отзыв будет опубликован на нашем сайте.</p>
            <p>С уважением,<br>{site_name}</p>
        </div>
    </div>
</body>
</html>';

// Resume Reply Template
$updated_options['resume_reply_template'] = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #17a2b8; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Ваше резюме получено</h2>
        </div>
        <div class="content">
            <p>Здравствуйте, {user_name}!</p>
            <p>Мы получили ваше резюме и внимательно его изучим.</p>
            <p>Если ваша кандидатура нас заинтересует, мы свяжемся с вами в ближайшее время.</p>
            <p>С уважением,<br>Отдел кадров<br>{site_name}</p>
        </div>
    </div>
</body>
</html>';

// Update database
$result = update_option('codeweber_forms_email_templates', $updated_options);

if ($result) {
    echo '<h1>✅ Шаблоны успешно обновлены!</h1>';
    echo '<p>Все шаблоны писем обновлены с DOCTYPE и правильной структурой HTML.</p>';
    echo '<p><a href="' . admin_url('admin.php?page=codeweber-forms-email-templates') . '">Перейти к шаблонам</a></p>';
} else {
    echo '<h1>⚠️ Ошибка обновления</h1>';
    echo '<p>Шаблоны не были обновлены. Возможно, они уже имеют такие же значения.</p>';
}

echo '<hr>';
echo '<h2>Обновленные шаблоны:</h2>';
echo '<ul>';
echo '<li>Admin Notification Template</li>';
echo '<li>Auto-Reply Template</li>';
echo '<li>Testimonial Reply Template</li>';
echo '<li>Resume Reply Template</li>';
echo '</ul>';

