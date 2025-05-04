<?php

/**
 * Integration with Contact Form 7
 */

// Load CSS & JS only when needed
// https://contactform7.com/loading-javascript-and-stylesheet-only-when-it-is-necessary/
// https://orbitingweb.com/blog/prevent-cf7-from-loading-css-js/

function codeweber_cf7_styles_scripts()
{
   //wp_dequeue_script('contact-form-7');
   //wp_dequeue_style('contact-form-7');
}




add_filter('wpcf7_messages', function ($messages) {

   // Очищаем конкретное сообщение об ошибке
   if (isset($messages['invalid_fields'])) {
      $messages['invalid_fields']['default'] = '';
   }

   return $messages;
});

add_action('wp_enqueue_scripts', 'codeweber_cf7_styles_scripts');



/**
 * Integration with Contact Form 7
 */

// Load CSS & JS only when needed
// https://contactform7.com/loading-javascript-and-stylesheet-only-when-it-is-necessary/
// https://orbitingweb.com/blog/prevent-cf7-from-loading-css-js/


add_filter('wpcf7_form_elements', function ($content) {
   $content = preg_replace('/<(span).*?class="\s*(?:.*\s)?wpcf7-form-control-wrap(?:\s[^"]+)?\s*"[^\>]*>(.*)<\/\1>/i', '\2', $content);
   $content = str_replace('<br />', '', $content);
   return $content;
});

add_filter('wpcf7_autop_or_not', '__return_false');



/**
 * Modal after sent CF7
 */
add_action('wp_footer', 'сf7_modal_after_sent');

function сf7_modal_after_sent()
{
   echo '<div class="modal fade" id="modal-0166" tabindex="-1">
  <div class="modal-dialog  modal-dialog-centered  modal-fullscreen-sm-down">
    <div class="modal-content min-vh-50 text-center">
    <div class="modal-body align-content-center">
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="container">
            <div class="row">
              <div class="col-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 395.7" class="mb-3 svg-inject icon-svg icon-svg-lg text-primary">
                  <path class="lineal-stroke" d="M483.6 395.7H53.3C23.9 395.7 0 371.9 0 342.4V53.3C0 23.9 23.9 0 53.3 0h405.4C488.1 0 512 23.9 512 53.3v222.8c0 7.9-6.4 14.2-14.2 14.2s-14.2-6.4-14.2-14.2V53.3c0-13.7-11.1-24.8-24.8-24.8H53.3c-13.7 0-24.8 11.1-24.8 24.8v289.2c0 13.7 11.1 24.8 24.8 24.8h430.3c7.9.2 14.1 6.7 13.8 14.6-.2 7.5-6.3 13.6-13.8 13.8z"></path>
                  <path class="lineal-fill" d="M497.8 53.3L256 236.4 14.2 53.3c0-21.6 17.5-39.1 39.1-39.1h405.4c21.6 0 39.1 17.5 39.1 39.1z"></path>
                  <path class="lineal-stroke" d="M256 250.6c-3.1 0-6.1-1-8.6-2.9L5.6 64.6C2.1 61.9 0 57.7 0 53.3 0 23.9 23.9 0 53.3 0h405.4C488.1 0 512 23.9 512 53.3c0 4.4-2.1 8.6-5.6 11.3L264.6 247.7c-2.5 1.9-5.5 2.9-8.6 2.9zM29.3 46.8L256 218.6 482.7 46.8c-2.9-10.9-12.8-18.4-24-18.4H53.3c-11.3.1-21.1 7.6-24 18.4zm454.2 348.7c-3.1 0-6.1-1-8.6-2.9l-99.6-75.4c-6.3-4.7-7.5-13.7-2.7-19.9 4.7-6.3 13.7-7.5 19.9-2.7l99.6 75.4c6.3 4.7 7.5 13.7 2.8 19.9-2.7 3.6-6.9 5.7-11.4 5.6zm-449-4.6c-7.9 0-14.2-6.4-14.2-14.2 0-4.5 2.1-8.7 5.6-11.4l93.5-70.8c6.3-4.7 15.2-3.5 19.9 2.7 4.7 6.3 3.5 15.2-2.7 19.9L43.1 388c-2.5 1.9-5.5 2.9-8.6 2.9z"></path>
                </svg>
              </div>
            </div>
            <div class="row">
              <div class="col-12 text-center">
                <div class="card-title h4">Сообщение успешно отправлено.</div>
              </div>
            </div>
          </div>
        <!--/.row -->
      </div>
      <!--/.modal-body -->
    </div>
    <!--/.modal-content -->
  </div>
  <!--/.modal-dialog -->
</div>
<!--/.modal -->';
?>

   <script type="text/javascript">
      document.addEventListener('wpcf7mailsent', function(event) {
         var myModal = new bootstrap.Modal(document.getElementById('modal-0166'), {
            keyboard: false
         });
         myModal.show();

         var form = event.target;

         // Убрать класс валидации с формы
         form.classList.remove('was-validated');

         // Убрать все классы is-valid и is-invalid с инпутов + aria-invalid
         form.querySelectorAll('.form-control, .form-check-input').forEach(function(input) {
            input.classList.remove('is-valid', 'is-invalid');
            input.removeAttribute('aria-invalid');
         });
      }, false);
   </script>

<?php
}

add_filter('wpcf7_form_class_attr', 'custom_custom_form_class_attr');

function custom_custom_form_class_attr($class)
{
   $class .= ' needs-validation';
   return $class;
}

// Filter Form Elements
// Include in your child theme/theme's functions.php
add_filter('wpcf7_form_elements', 'dd_wpcf7_form_elements_replace');
function dd_wpcf7_form_elements_replace($content)
{
   $content = preg_replace('/aria-required="true"/', 'aria-required="true"  required', $content);
   $content = preg_replace('/checked="checked"/', 'checked="checked" aria-required="true"  required', $content);

   return $content;
}



add_filter('wpcf7_form_elements', function ($content) {
   $content = str_replace('type="checkbox"', 'type="checkbox" required', $content);
   return $content;
});


add_filter('wpcf7_form_elements', function ($content) {
   // 1. Убираем <span class="wpcf7-list-item"> вокруг input
   $content = preg_replace_callback(
      '/<span class="wpcf7-list-item">\s*(<input[^>]+>)\s*<\/span>/i',
      function ($matches) {
         return $matches[1]; // Оставляем только <input>
      },
      $content
   );

   // 2. Убираем <span class="wpcf7-form-control wpcf7-acceptance"> вокруг input
   $content = preg_replace_callback(
      '/<span class="wpcf7-form-control wpcf7-acceptance">\s*(<input[^>]+>)\s*<\/span>/i',
      function ($matches) {
         return $matches[1]; // Оставляем только <input>
      },
      $content
   );

   return $content;
});



function create_custom_cf7_form()
{
   if (!post_type_exists('wpcf7_contact_form')) {
      error_log('CF7 не активен.');
      return;
   }

   // Проверяем, нет ли формы с таким слагом уже
   $slug = 'zakazat-zvonok'; // <-- зафиксированный slug
   $existing_form = get_page_by_path($slug, OBJECT, 'wpcf7_contact_form');
   if ($existing_form) {
      error_log('Форма уже существует.');
      return;
   }

   // Содержимое формы
   $form_content = <<<EOD
<h2 class="mb-3 text-start">Заказать звонок</h2>
                    <p class="lead mb-6 text-start">Перезвоним в течение 15 минут</p>

<div class="form-floating mb-3 text-dark"> 
  [text* text-name id:floatingName class:form-control placeholder "Ваше Имя"]
  <label for="floatingName">Ваше Имя</label>
  <div class="invalid-feedback">
        Не заполнено.
      </div>
</div>

<div class="form-floating mb-3 text-dark"> 
  [tel* tel-463 id:floatingTel class:phone-mask class:form-control placeholder "+7(000)123-45-67"]
  <label for="floatingTel">+7(000)123-45-67</label>
  <div class="invalid-feedback">
        Не заполнено.
      </div>
</div>

<div class="form-check mb-3 fs-14 small-chekbox">
  [acceptance acceptance-123 id:flexCheckDefault1 class:form-check-input use_label_element] 
  <label for="flexCheckDefault1" class="form-check-label">
    Я согласен с <a href="/privacy-policy/" target="_blank">политикой обработки персональных данных</a>
  </label>
 <div class="invalid-feedback">Согласие обязательно.</div>
</div>

<button type="submit" class="wpcf7-submit has-ripple btn rounded-pill btn-md btn-primary mx-5 mx-md-0">
  Отправить
</button>
EOD;

   // Создание формы
   $form_post = array(
      'post_title'   => 'Заказать звонок',
      'post_name'    => $slug, // <-- задаем стабильный слаг
      'post_content' => $form_content,
      'post_status'  => 'publish',
      'post_type'    => 'wpcf7_contact_form',
   );

   $post_id = wp_insert_post($form_post);

   if (is_wp_error($post_id)) {
      error_log('Ошибка создания формы: ' . $post_id->get_error_message());
      return;
   }

   // Установка содержимого формы
   update_post_meta($post_id, '_form', $form_content);

   // (опционально) Установка шаблона письма
   $mail = array(
      'subject'            => '[Заказать звонок] Заказ звонка от [text-name]',
      'sender'             => '[your-email]',
      'body'               => "Имя: [text-name]\nТелефон: [tel-463]\nEmail: [email-address]\nКомментарий: [textarea-471]",
      'recipient'          => get_option('admin_email'),
      'additional_headers' => "Reply-To: [email-address]",
      'attachments'        => '',
      'use_html'           => true
   );

   update_post_meta($post_id, '_mail', $mail);

   // Логирование
   error_log('Создана форма: ID ' . $post_id . ' | SLUG ' . $slug);
}

function create_custom_cf7_form_with_name_and_email()
{
   if (!post_type_exists('wpcf7_contact_form')) {
      error_log('CF7 не активен.');
      return;
   }

   // Проверяем, нет ли формы с таким слагом уже
   $slug = 'zakazat-zvonok-name-email'; // Новый слаг для этой формы
   $existing_form = get_page_by_path($slug, OBJECT, 'wpcf7_contact_form');
   if ($existing_form) {
      error_log('Форма уже существует.');
      return;
   }

   // Содержимое формы
   $form_content = <<<EOD
<h2 class="mb-3 text-start">Заказать звонок</h2>
                    <p class="lead mb-6 text-start">Перезвоним в течение 15 минут</p>

<div class="form-floating mb-3 text-dark"> 
  [text* text-name id:floatingName class:form-control placeholder "Ваше Имя"]
  <label for="floatingName">Ваше Имя</label>
  <div class="invalid-feedback">
        Не заполнено.
      </div>
</div>

<div class="form-floating mb-3 text-dark"> 
  [email* email-address id:floatingEmail class:form-control placeholder "Ваш Email"]
  <label for="floatingEmail">Ваш Email</label>
  <div class="invalid-feedback">
        Не заполнено.
      </div>
</div>

<div class="form-floating mb-3 text-dark"> 
  [tel* tel-463 id:floatingTel class:phone-mask class:form-control placeholder "+7(000)123-45-67"]
  <label for="floatingTel">+7(000)123-45-67</label>
  <div class="invalid-feedback">
        Не заполнено.
      </div>
</div>

<div class="form-check mb-3 fs-14 small-chekbox">
  [acceptance acceptance-123 id:flexCheckDefault1 class:form-check-input use_label_element] 
  <label for="flexCheckDefault1" class="form-check-label">
    Я согласен с <a href="/privacy-policy/" target="_blank">политикой обработки персональных данных</a>
  </label>
 <div class="invalid-feedback">Согласие обязательно.</div>
</div>

<button type="submit" class="wpcf7-submit has-ripple btn rounded-pill btn-md btn-primary mx-5 mx-md-0">
  Отправить
</button>
EOD;

   // Создание формы
   $form_post = array(
      'post_title'   => 'Заказать звонок с Имя и Email',
      'post_name'    => $slug, // <-- новый стабильный слаг
      'post_content' => $form_content,
      'post_status'  => 'publish',
      'post_type'    => 'wpcf7_contact_form',
   );

   $post_id = wp_insert_post($form_post);

   if (is_wp_error($post_id)) {
      error_log('Ошибка создания формы: ' . $post_id->get_error_message());
      return;
   }

   // Установка содержимого формы
   update_post_meta($post_id, '_form', $form_content);

   // (опционально) Установка шаблона письма
   $mail = array(
      'subject'            => '[Заказать звонок] Заказ звонка от [text-name]',
      'sender'             => '[your-email]',
      'body'               => "Имя: [text-name]\nEmail: [email-address]\nТелефон: [tel-463]",
      'recipient'          => get_option('admin_email'),
      'additional_headers' => "Reply-To: [email-address]",
      'attachments'        => '',
      'use_html'           => true
   );

   update_post_meta($post_id, '_mail', $mail);

   // Логирование
   error_log('Создана форма: ID ' . $post_id . ' | SLUG ' . $slug);
}


function create_custom_cf7_form_with_name_comment_and_email()
{
   if (!post_type_exists('wpcf7_contact_form')) {
      error_log('CF7 не активен.');
      return;
   }

   // Проверяем, нет ли формы с таким слагом уже
   $slug = 'svyazatsya-s-nami'; // Новый слаг для этой формы
   $existing_form = get_page_by_path($slug, OBJECT, 'wpcf7_contact_form');
   if ($existing_form) {
      error_log('Форма уже существует.');
      return;
   }

   // Содержимое формы
   $form_content = <<<EOD
<h2 class="mb-3 text-start">Связаться с нами</h2>
<p class="lead mb-6 text-start">После отправки формы с вами свяжется наш менеджер</p>

<div class="row gx-4">
    <div class="col-md-6">
        <div class="form-floating mb-4">
            [text* text-name id:floatingName class:form-control placeholder "Ваше Имя"]
            <label for="floatingName">Ваше Имя</label>
            <div class="invalid-feedback">Не заполнено.</div>
        </div>
    </div>
    <!-- /column -->
    <div class="col-md-6">
        <div class="form-floating mb-4">
            [text* text-lastname id:floatingLastName class:form-control placeholder "Ваша Фамилия"]
            <label for="floatingLastName">Ваша Фамилия</label>
            <div class="invalid-feedback">Не заполнено.</div>
        </div>
    </div>
    <!-- /column -->
    <div class="col-md-6">
        <div class="form-floating mb-4">
            [email* email-address id:floatingEmail class:form-control placeholder "Ваш Email"]
            <label for="floatingEmail">Ваш Email</label>
            <div class="invalid-feedback">Не заполнено.</div>
        </div>
    </div>
    <!-- /column -->
    <div class="col-md-6">
        <div class="form-floating mb-4">
            [tel* tel-463 id:floatingTel class:phone-mask class:form-control placeholder "+7(000)123-45-67"]
            <label for="floatingTel">+7(000)123-45-67</label>
            <div class="invalid-feedback">Не заполнено.</div>
        </div>
    </div>
    <!-- /column -->
    <div class="col-12">
        <div class="form-floating mb-4">
            [textarea* textarea-937 id:floatingMessage class:form-control placeholder "Ваше сообщение"]
            <label for="floatingMessage">Ваше сообщение</label>
            <div class="invalid-feedback">Не заполнено.</div>
        </div>
    </div>
    <!-- /column -->
    <div class="col-12">
        <div class="form-check mb-3 fs-14 small-chekbox">
            [acceptance acceptance-123 id:flexCheckDefault1 class:form-check-input use_label_element]
            <label for="flexCheckDefault1" class="form-check-label">
                Я согласен с <a href="/privacy-policy/" target="_blank">политикой обработки персональных данных</a>
            </label>
            <div class="invalid-feedback">Согласие обязательно.</div>
        </div>
    </div>
    <!-- /column -->
    <div class="col-12 text-center">
        <button type="submit" class="wpcf7-submit has-ripple btn rounded-pill btn-md btn-primary mx-5 mx-md-0">
   Отправить запрос
</button>
    </div>
    <!-- /column -->
</div>
EOD;

   // Создание формы
   $form_post = array(
      'post_title'   => 'Связаться с нами',
      'post_name'    => $slug, // <-- новый стабильный слаг
      'post_content' => $form_content,
      'post_status'  => 'publish',
      'post_type'    => 'wpcf7_contact_form',
   );

   $post_id = wp_insert_post($form_post);

   if (is_wp_error($post_id)) {
      error_log('Ошибка создания формы: ' . $post_id->get_error_message());
      return;
   }

   // Установка содержимого формы
   update_post_meta($post_id, '_form', $form_content);

   // (опционально) Установка шаблона письма
   $mail = array(
      'subject'            => '[Связаться с нами] Запрос от [text-name] [text-lastname]',
      'sender'             => '[your-email]',
      'body'               => "Имя: [text-name] [text-lastname]\nEmail: [email-address]\nТелефон: [tel-463]\nСообщение: [textarea-937]",
      'recipient'          => get_option('admin_email'),
      'additional_headers' => "Reply-To: [email-address]",
      'attachments'        => '',
      'use_html'           => true
   );

   update_post_meta($post_id, '_mail', $mail);

   // Логирование
   error_log('Создана форма: ID ' . $post_id . ' | SLUG ' . $slug);
}
