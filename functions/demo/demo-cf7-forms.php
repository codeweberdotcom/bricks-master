<?php
/**
 * Функции для создания demo форм Contact Form 7
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Создать demo формы CF7
 * 
 * @return array Результат создания
 */
function cw_demo_create_cf7_forms() {
    if (!post_type_exists('wpcf7_contact_form')) {
        return array(
            'success' => false,
            'message' => 'Contact Form 7 не активирован',
            'created' => 0,
            'errors' => array()
        );
    }

    $created = 0;
    $errors = array();
    $forms = array();

    // Форма 1: Форма обратной связи
    $form1_slug = 'forma-obratnoj-svyazi';
    $form1_existing = get_page_by_path($form1_slug, OBJECT, 'wpcf7_contact_form');
    
    if (!$form1_existing) {
        $form1_content = <<<EOD
<div class="h3 text-start">Форма обратной связи</div>
<p class="lead mb-4 text-start">Перезвоним в течение 15 минут</p>

<div class="form-floating mb-3 text-dark"> 
  [text* name id:floatingName class:form-control placeholder "Ваше Имя"]
  <label for="floatingName">Ваше Имя</label>
<div class="invalid-feedback">
        Введите ваше Имя.
      </div>
</div>

<div class="form-floating mb-3 text-dark"> 
  [text* lastname id:floatingName1 class:form-control placeholder "Ваше фамилия"]
  <label for="floatingName1">Ваша Фамилия</label>
<div class="invalid-feedback">
        Введите вашу Фамилию.
      </div>
</div>

<div class="form-floating mb-3 text-dark"> 
  [text* patronymic id:floatingName3 class:form-control placeholder "Ваше отчество"]
  <label for="floatingName3">Ваше Отчество</label>
<div class="invalid-feedback">
        Введите ваше Отчество.
      </div>
</div>

<div class="form-floating mb-3 text-dark"> 
  [tel* phone id:floatingTel class:form-control placeholder "+7(___)___-__-__"]
  <label for="floatingTel">Телефон</label>
  <div class="invalid-feedback">
        Введите ваш телефон.
      </div>
</div>

<div class="col-12">
        <div class="form-floating mb-4">
            [textarea* message id:floatingMessage class:form-control placeholder "Ваше сообщение"]
            <label for="floatingMessage">Ваше сообщение</label>
            <div class="invalid-feedback">
        Введите ваше Сообщение.
      </div>
        </div>
    </div>

<div class="form-floating mb-3 text-dark"> 
  [email* email id:floatingEmail class:form-control placeholder "Ваш Email"]
  <label for="floatingEmail">Ваш Email</label>
<div class="invalid-feedback">
        Введите ваш E-Mail.
      </div>
</div>

[cf7_consent_checkbox]

<button type="submit" class="wpcf7-submit has-ripple btn [getthemebutton] btn-md btn-primary mx-5 mx-md-0">
  Отправить
</button>
EOD;

        $form1_post = array(
            'post_title'   => 'Форма обратной связи',
            'post_name'    => $form1_slug,
            'post_content' => $form1_content,
            'post_status'  => 'publish',
            'post_type'    => 'wpcf7_contact_form',
        );

        $form1_id = wp_insert_post($form1_post);

        if (is_wp_error($form1_id)) {
            $errors[] = 'Не удалось создать форму "Форма обратной связи": ' . $form1_id->get_error_message();
        } else {
            update_post_meta($form1_id, '_form', $form1_content);
            update_post_meta($form1_id, '_demo_created', true);
            
            // Шаблон письма для формы 1
            $mail1 = array(
                'subject'            => '[Форма обратной связи] Запрос от [name] [lastname]',
                'sender'             => '[email]',
                'body'               => "Имя: [name] [lastname] [patronymic]\nEmail: [email]\nТелефон: [phone]\nСообщение: [message]",
                'recipient'          => get_option('admin_email'),
                'additional_headers' => "Reply-To: [email]",
                'attachments'        => '',
                'use_html'           => true
            );
            update_post_meta($form1_id, '_mail', $mail1);
            
            $created++;
            $forms[] = 'Форма обратной связи';
        }
    } else {
        $errors[] = 'Форма "Форма обратной связи" уже существует';
    }

    // Форма 2: Заказать звонок
    $form2_slug = 'zakazat-zvonok-demo';
    $form2_existing = get_page_by_path($form2_slug, OBJECT, 'wpcf7_contact_form');
    
    if (!$form2_existing) {
        $form2_content = <<<EOD
<div class="h3 text-start">Заказать звонок</div>
<p class="lead mb-4 text-start">Перезвоним в течение 15 минут</p>

<div class="form-floating mb-3 text-dark"> 
  [text* name id:floatingName class:form-control placeholder "Ваше Имя"]
  <label for="floatingName">Ваше Имя</label>
<div class="invalid-feedback">
        Введите ваше Имя.
      </div>
</div>

<div class="form-floating mb-3 text-dark"> 
  [tel* phone id:floatingTel class:form-control placeholder "+7(___)___-__-__"]
  <label for="floatingTel">Телефон</label>
  <div class="invalid-feedback">
        Введите ваш телефон.
      </div>
</div>

[cf7_consent_checkbox]

<button type="submit" class="wpcf7-submit has-ripple btn [getthemebutton] btn-md btn-primary mx-5 mx-md-0">
  Отправить
</button>
EOD;

        $form2_post = array(
            'post_title'   => 'Заказать звонок',
            'post_name'    => $form2_slug,
            'post_content' => $form2_content,
            'post_status'  => 'publish',
            'post_type'    => 'wpcf7_contact_form',
        );

        $form2_id = wp_insert_post($form2_post);

        if (is_wp_error($form2_id)) {
            $errors[] = 'Не удалось создать форму "Заказать звонок": ' . $form2_id->get_error_message();
        } else {
            update_post_meta($form2_id, '_form', $form2_content);
            update_post_meta($form2_id, '_demo_created', true);
            
            // Шаблон письма для формы 2
            $mail2 = array(
                'subject'            => '[Заказать звонок] Заказ звонка от [name]',
                'sender'             => get_option('admin_email'),
                'body'               => "Имя: [name]\nТелефон: [phone]",
                'recipient'          => get_option('admin_email'),
                'additional_headers' => '',
                'attachments'        => '',
                'use_html'           => true
            );
            update_post_meta($form2_id, '_mail', $mail2);
            
            $created++;
            $forms[] = 'Заказать звонок';
        }
    } else {
        $errors[] = 'Форма "Заказать звонок" уже существует';
    }

    $message = sprintf('Создано форм: %d из 2', $created);
    if (!empty($forms)) {
        $message .= '. Формы: ' . implode(', ', $forms);
    }

    return array(
        'success' => true,
        'message' => $message,
        'created' => $created,
        'total' => 2,
        'forms' => $forms,
        'errors' => $errors
    );
}

/**
 * Удалить все demo формы CF7
 * 
 * @return array Результат удаления
 */
function cw_demo_delete_cf7_forms() {
    if (!post_type_exists('wpcf7_contact_form')) {
        return array(
            'success' => false,
            'message' => 'Contact Form 7 не активирован',
            'deleted' => 0,
            'errors' => array()
        );
    }

    $deleted = 0;
    $errors = array();
    
    // Находим все формы с мета-полем _demo_created
    $forms = get_posts(array(
        'post_type' => 'wpcf7_contact_form',
        'posts_per_page' => -1,
        'meta_key' => '_demo_created',
        'meta_value' => true,
        'post_status' => 'any'
    ));

    foreach ($forms as $form) {
        $result = wp_delete_post($form->ID, true);
        
        if ($result) {
            $deleted++;
        } else {
            $errors[] = 'Не удалось удалить форму: ' . $form->post_title;
        }
    }

    $message = sprintf('Удалено форм: %d', $deleted);

    return array(
        'success' => true,
        'message' => $message,
        'deleted' => $deleted,
        'errors' => $errors
    );
}

