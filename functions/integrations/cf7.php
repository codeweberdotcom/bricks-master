<?php

/**
 * Отключает автоматическую загрузку стилей и скриптов Contact Form 7 на всех страницах.
 *
 * По умолчанию Contact Form 7 подключает свои стили и скрипты на каждой странице сайта.
 * Эта функция позволяет отменить их автоматическую загрузку, чтобы подключать их
 * вручную только на нужных страницах (например, где используется форма).
 *
 * Ссылки:
 * @link https://contactform7.com/loading-javascript-and-stylesheet-only-when-it-is-necessary/
 * @link https://orbitingweb.com/blog/prevent-cf7-from-loading-css-js/
 *
 * Для активации — раскомментируйте нужные строки внутри функции.
 *
 * @return void
 */
function codeweber_cf7_styles_scripts()
{
   // Раскомментируйте строки ниже, чтобы отключить глобальную загрузку ресурсов CF7
   // wp_dequeue_script('contact-form-7');
   // wp_dequeue_style('contact-form-7');
}





/**
 * Удаляет стандартное сообщение об ошибке "Некорректные поля" в Contact Form 7.
 *
 * Contact Form 7 по умолчанию выводит сообщение при наличии невалидных полей,
 * но этот фильтр очищает его, чтобы не отображалось общее сообщение об ошибке.
 *
 * @param array $messages Массив сообщений Contact Form 7.
 * @return array Модифицированный массив сообщений.
 */
add_filter('wpcf7_messages', function ($messages) {
   // Очищаем конкретное сообщение об ошибке
   if (isset($messages['invalid_fields'])) {
      $messages['invalid_fields']['default'] = '';
   }

   return $messages;
});


/**
 * Регистрирует скрипты и стили для фронтенда, связанные с Contact Form 7.
 * 
 * Эта функция должна быть определена отдельно. Обычно используется для подключения
 * дополнительных JS или CSS, нужных для обработки согласий, кастомных полей и т.п.
 */
add_action('wp_enqueue_scripts', 'codeweber_cf7_styles_scripts');




/**
 * Интеграция с Contact Form 7
 */

// Загружаем CSS и JS только при необходимости
// https://contactform7.com/loading-javascript-and-stylesheet-only-when-it-is-necessary/
// https://orbitingweb.com/blog/prevent-cf7-from-loading-css-js/

// Удаляет лишние обертки <span class="wpcf7-form-control-wrap">...</span> вокруг полей формы
add_filter('wpcf7_form_elements', function ($content) {
   $content = preg_replace('/<(span).*?class="\s*(?:.*\s)?wpcf7-form-control-wrap(?:\s[^"]+)?\s*"[^\>]*>(.*)<\/\1>/i', '\2', $content);
   $content = str_replace('<br />', '', $content); // Удаляет теги <br />
   return $content;
});

// Отключает автоматическое добавление абзацев и <br> тегов в разметку формы
add_filter('wpcf7_autop_or_not', '__return_false');




/**
 * Отображает модальное окно после успешной отправки формы Contact Form 7.
 *
 * Добавляет HTML разметку модального окна с сообщением об успешной отправке
 * и JS-скрипт, который показывает это окно при событии 'wpcf7mailsent'.
 * Также скрипт очищает классы валидации и aria-атрибуты у полей формы.
 *
 * Хук: wp_footer — выводит модальное окно перед закрывающим тегом </body>.
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

         // Убираем класс валидации с формы
         form.classList.remove('was-validated');

         // Убираем все классы is-valid и is-invalid с полей + aria-invalid атрибуты
         form.querySelectorAll('.form-control, .form-check-input').forEach(function(input) {
            input.classList.remove('is-valid', 'is-invalid');
            input.removeAttribute('aria-invalid');
         });
      }, false);
   </script>

<?php
}


/**
 * Добавляет дополнительный CSS класс к атрибуту class формы Contact Form 7.
 *
 * Позволяет добавить кастомный класс (в данном случае 'needs-validation'), 
 * который можно использовать для стилизации или валидации формы.
 *
 * @param string $class Текущий список CSS классов формы.
 * @return string Обновленный список CSS классов с добавленным 'needs-validation'.
 */
add_filter('wpcf7_form_class_attr', 'custom_custom_form_class_attr');

function custom_custom_form_class_attr($class)
{
   $class .= ' needs-validation';
   return $class;
}


/**
 * Фильтр для элементов формы Contact Form 7
 *
 * Добавляет атрибуты required для обязательных полей и для отмеченных чекбоксов.
 * Это улучшает валидацию на стороне браузера, делая поля обязательными по стандарту HTML5.
 *
 * Рекомендуется подключать этот код в functions.php дочерней темы или основной темы.
 *
 * @param string $content HTML код формы CF7.
 * @return string Модифицированный HTML с добавленными атрибутами required.
 */
add_filter('wpcf7_form_elements', 'dd_wpcf7_form_elements_replace');
function dd_wpcf7_form_elements_replace($content)
{
   // Добавляем атрибут required для элементов с aria-required="true"
   $content = preg_replace('/aria-required="true"/', 'aria-required="true"  required', $content);

   // Добавляем атрибуты required и aria-required="true" для отмеченных чекбоксов
   $content = preg_replace('/checked="checked"/', 'checked="checked" aria-required="true"  required', $content);

   return $content;
}




/**
 * Добавляет атрибут required ко всем checkbox в формах Contact Form 7.
 *
 * Это заставляет браузер требовать отметку всех чекбоксов перед отправкой формы.
 * Используйте с осторожностью, так как может сделать все чекбоксы обязательными,
 * что не всегда желательно.
 *
 * @param string $content HTML код формы CF7.
 * @return string Модифицированный HTML с добавленным required у чекбоксов.
 */
add_filter('wpcf7_form_elements', function ($content) {
   // Используем preg_replace_callback для точечной обработки каждого input[type="checkbox"]
   $content = preg_replace_callback(
      '/<input([^>]+type="checkbox"[^>]*)>/i',
      function ($matches) {
         $input = $matches[1];

         // Если в атрибутах уже есть класс "optional" — не добавляем required
         if (preg_match('/class="[^"]*optional[^"]*"/i', $input)) {
            return '<input' . $input . '>';
         }

         // Если already required — не дублируем
         if (strpos($input, 'required') !== false) {
            return '<input' . $input . '>';
         }

         // Добавляем required перед закрытием тега
         return '<input' . $input . ' required>';
      },
      $content
   );

   return $content;
});



/**
 * Фильтр для Contact Form 7, который удаляет обертки <span> вокруг input-элементов
 * с классами "wpcf7-list-item" и "wpcf7-form-control wpcf7-acceptance".
 * 
 * Это позволяет упростить HTML-разметку формы, оставляя только сами input элементы,
 * что может быть полезно для кастомной стилизации и улучшения контроля над формой.
 *
 * @param string $content HTML-код формы CF7.
 * @return string Модифицированный HTML-код без указанных span-оберток.
 */
add_filter('wpcf7_form_elements', function ($content) {
   // Удаляем <span class="wpcf7-list-item"> вокруг input
   $content = preg_replace_callback(
      '/<span class="wpcf7-list-item">\s*(<input[^>]+>)\s*<\/span>/i',
      function ($matches) {
         return $matches[1]; // Оставляем только <input>
      },
      $content
   );

   // Удаляем <span class="wpcf7-form-control wpcf7-acceptance"> вокруг input
   $content = preg_replace_callback(
      '/<span class="wpcf7-form-control wpcf7-acceptance">\s*(<input[^>]+>)\s*<\/span>/i',
      function ($matches) {
         return $matches[1]; // Оставляем только <input>
      },
      $content
   );

   return $content;
});



/**
 * Создание пользовательских форм Contact Form 7 программно.
 *
 * Каждая функция создает форму с уникальным слагом, если такая форма еще не существует.
 * В форму включены поля с валидацией, согласие на обработку моих персональных данных,
 * и задается шаблон письма для уведомлений.
 */

/**
 * Создает простую форму "Заказать звонок" с полями Имя и Телефон.
 */
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
      return;
   }

   // Содержимое формы
   $form_content = <<<EOD
<h2 class="mb-3 text-start">Заказать звонок</h2>
<p class="lead mb-6 text-start">Перезвоним в течение 15 минут</p>

<div class="form-floating mb-3 text-dark"> 
  [text* text-name id:floatingName class:form-control placeholder "Ваше Имя"]
  <label for="floatingName">Ваше Имя</label>
</div>

<div class="form-floating mb-3 text-dark"> 
  [tel* tel-463 id:floatingTel class:phone-mask class:form-control placeholder "+7(000)123-45-67"]
  <label for="floatingTel">+7(000)123-45-67</label>
</div>

 <div class="form-check mb-2 fs-12 small-chekbox wpcf7-acceptance">
  [acceptance soglasie-na-obrabotku id:flexCheckDefault1 class:form-check-input use_label_element]
  <label for="flexCheckDefault1" class="form-check-label text-start">
    Я даю свое <a class="text-primary" href="[cf7_legal_consent_link]" target="_blank">согласие</a> на обработку моих персональных данных.<br> С документом <a href="[cf7_privacy_policy]">политика обработки персональных данных</a> ознакомлен.
  </label>

</div>

<div class="form-check mb-3 fs-12 small-chekbox">
  [acceptance soglasie-na-rassilku id:flexCheckDefault14 class:form-check-input class:optional use_label_element optional]
  <label for="flexCheckDefault14" class="form-check-label text-start">
    Я даю свое <a class="text-primary" href="[cf7_mailing_consent_link]" target="_blank">согласие</a> на получение информационной и рекламной рассылки
  </label>
</div>

<button type="submit" class="wpcf7-submit has-ripple btn [getthemebutton] btn-md btn-primary mx-5 mx-md-0">
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


/**
 * Создаёт форму "Подписка на рассылку" с email и согласием.
 */
function create_newsletter_cf7_form()
{
   if (!post_type_exists('wpcf7_contact_form')) {
      error_log('CF7 не активен.');
      return;
   }

   $slug = 'newsletter-subscription';
   $existing_form = get_page_by_path($slug, OBJECT, 'wpcf7_contact_form');
   if ($existing_form) {
      return;
   }

   $form_content = <<<EOD
<div class="form-floating mb-3 text-dark input-group needs-validation" novalidate>
  [email* email-address id:floatingEmail class:form-control class:border-0 placeholder "Ваш Email"]
  <label class="z-index50" for="floatingEmail">Ваш Email</label>
  <button type="submit" class="wpcf7-submit has-ripple btn btn-primary">
    Отправить
  </button>
</div>

<div class="form-check mb-2 fs-12 small-chekbox wpcf7-acceptance">
  [acceptance soglasie-na-obrabotku id:flexCheckDefault1 class:form-check-input use_label_element]
  <label for="flexCheckDefault1" class="form-check-label text-start">
    Я даю свое <a class="text-primary" href="[cf7_legal_consent_link id='988']" target="_blank">согласие</a> на обработку моих персональных данных.<br>
    Я даю свое <a class="text-primary" href="[cf7_mailing_consent_link id='988']" target="_blank">согласие</a> на получение информационной и рекламной рассылки
  </label>
  
</div>
EOD;

   $form_post = array(
      'post_title'   => 'Подписка на рассылку',
      'post_name'    => $slug,
      'post_content' => $form_content,
      'post_status'  => 'publish',
      'post_type'    => 'wpcf7_contact_form',
   );

   $post_id = wp_insert_post($form_post);

   if (is_wp_error($post_id)) {
      error_log('Ошибка создания формы: ' . $post_id->get_error_message());
      return;
   }

   update_post_meta($post_id, '_form', $form_content);

   $mail = array(
      'subject'            => '[Подписка] Новый email: [email-address]',
      'sender'             => get_option('admin_email'),
      'body'               => "Email: [email-address]",
      'recipient'          => get_option('admin_email'),
      'additional_headers' => "Reply-To: [email-address]",
      'attachments'        => '',
      'use_html'           => true,
   );

   update_post_meta($post_id, '_mail', $mail);

   error_log('Создана форма "Подписка на рассылку", ID: ' . $post_id);
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
      return;
   }

   // Содержимое формы
   $form_content = <<<EOD
<h2 class="mb-3 text-start">Заказать звонок</h2>
                    <p class="lead mb-6 text-start">Перезвоним в течение 15 минут</p>

<div class="form-floating mb-3 text-dark"> 
  [text* text-name id:floatingName class:form-control placeholder "Ваше Имя"]
  <label for="floatingName">Ваше Имя</label>
</div>

<div class="form-floating mb-3 text-dark"> 
  [email* email-address id:floatingEmail class:form-control placeholder "Ваш Email"]
  <label for="floatingEmail">Ваш Email</label>
</div>

<div class="form-floating mb-3 text-dark"> 
  [tel* tel-463 id:floatingTel class:phone-mask class:form-control placeholder "+7(000)123-45-67"]
  <label for="floatingTel">+7(000)123-45-67</label>
</div>

<div class="form-check mb-2 fs-12 small-chekbox wpcf7-acceptance">
  [acceptance soglasie-na-obrabotku id:flexCheckDefault1 class:form-check-input use_label_element]
  <label for="flexCheckDefault1" class="form-check-label text-start">
    Я даю свое <a class="text-primary" href="[cf7_legal_consent_link]" target="_blank">согласие</a> на обработку моих персональных данных.<br> С документом <a href="[cf7_privacy_policy]">политика обработки персональных данных</a> ознакомлен.
  </label>
</div>

<div class="form-check mb-3 fs-12 small-chekbox">
  [acceptance soglasie-na-rassilku id:flexCheckDefault14 class:form-check-input class:optional use_label_element optional]
  <label for="flexCheckDefault14" class="form-check-label text-start">
    Я даю свое <a class="text-primary" href="[cf7_mailing_consent_link]" target="_blank">согласие</a> на получение информационной и рекламной рассылки
  </label>
</div>

<button type="submit" class="wpcf7-submit has-ripple btn [getthemebutton] btn-md btn-primary mx-5 mx-md-0">
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
   $slug = 'svyazatsya-s-nami-1'; // Новый слаг для этой формы
   $existing_form = get_page_by_path($slug, OBJECT, 'wpcf7_contact_form');
   if ($existing_form) {
      return;
   }

   // Содержимое формы
   $form_content = <<<EOD
<div class="row gx-4">
    <div class="col-md-6">
        <div class="form-floating mb-4">
            [text* text-name id:floatingName class:form-control placeholder "Ваше Имя"]
            <label for="floatingName">Ваше Имя</label>
        </div>
    </div>
    <!-- /column -->
    <div class="col-md-6">
        <div class="form-floating mb-4">
            [text* text-lastname id:floatingLastName class:form-control placeholder "Ваша Фамилия"]
            <label for="floatingLastName">Ваша Фамилия</label>
        </div>
    </div>
    <!-- /column -->
    <div class="col-md-6">
        <div class="form-floating mb-4">
            [email* email-address id:floatingEmail class:form-control placeholder "Ваш Email"]
            <label for="floatingEmail">Ваш Email</label>
        </div>
    </div>
    <!-- /column -->
    <div class="col-md-6">
        <div class="form-floating mb-4">
            [tel* tel-463 id:floatingTel class:phone-mask class:form-control placeholder "+7(000)123-45-67"]
            <label for="floatingTel">+7(000)123-45-67</label>
        </div>
    </div>
    <!-- /column -->
    <div class="col-12">
        <div class="form-floating mb-4">
            [textarea* textarea-937 id:floatingMessage class:form-control placeholder "Ваше сообщение"]
            <label for="floatingMessage">Ваше сообщение</label>
        </div>
    </div>
    <!-- /column -->
    <div class="col-12">
  

   <div class="form-check mb-2 fs-12 small-chekbox wpcf7-acceptance">
  [acceptance soglasie-na-obrabotku id:flexCheckDefault1 class:form-check-input use_label_element]
  <label for="flexCheckDefault1" class="form-check-label text-start">
    Я даю свое <a class="text-primary" href="[cf7_legal_consent_link]" target="_blank">согласие</a> на обработку моих персональных данных.<br> С документом <a href="[cf7_privacy_policy]">политика обработки персональных данных</a> ознакомлен.
  </label>
</div>

<div class="form-check mb-3 fs-12 small-chekbox">
  [acceptance soglasie-na-rassilku id:flexCheckDefault14 class:form-check-input class:optional use_label_element optional]
  <label for="flexCheckDefault14" class="form-check-label text-start">
    Я даю свое <a class="text-primary" href="[cf7_mailing_consent_link]" target="_blank">согласие</a> на получение информационной и рекламной рассылки
  </label>
</div>

    </div>
    <!-- /column -->
    <div class="col-12">
        <button type="submit" class="wpcf7-submit has-ripple btn [getthemebutton] btn-md btn-primary mx-5 mx-md-0">
   Отправить запрос
</button>
    </div>
    <!-- /column -->
</div>
EOD;

   // Создание формы
   $form_post = array(
      'post_title'   => 'Связаться с нами 1',
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


function create_custom_cf7_form_with_name_comment_and_email_2()
{
   if (!post_type_exists('wpcf7_contact_form')) {
      error_log('CF7 не активен.');
      return;
   }

   $slug = 'svyazatsya-s-nami-2';
   $existing_form = get_page_by_path($slug, OBJECT, 'wpcf7_contact_form');
   if ($existing_form) {
      return; // Форма уже существует
   }

   $form_content = <<<EOD
<div class="row gx-4">
    <div class="col-md-6">
        <div class="form-floating mb-4">
            [text* text-name id:floatingName class:form-control placeholder "Ваше Имя"]
            <label for="floatingName">Ваше Имя</label>
        </div>
    </div>
    <!-- /column -->

    <div class="col-md-6">
        <div class="form-floating mb-4">
            [email* email-address id:floatingEmail class:form-control placeholder "Ваш Email"]
            <label for="floatingEmail">Ваш Email</label>
        </div>
    </div>
    <!-- /column -->
    
    <div class="col-12">
        <div class="form-floating mb-4">
            [textarea* textarea-937 id:floatingMessage class:form-control placeholder "Ваше сообщение"]
            <label for="floatingMessage">Ваше сообщение</label>
        </div>
    </div>
    <!-- /column -->
    <div class="col-12">

       <div class="form-check mb-2 fs-12 small-chekbox wpcf7-acceptance">
          [acceptance soglasie-na-obrabotku id:flexCheckDefault1 class:form-check-input use_label_element]
          <label for="flexCheckDefault1" class="form-check-label text-start">
            Я даю свое <a class="text-primary" href="[cf7_legal_consent_link id='1004']" target="_blank">согласие</a> на обработку моих персональных данных.<br> С документом <a href="[cf7_privacy_policy id='1004']">политика обработки персональных данных</a> ознакомлен.
          </label>
       </div>

       <div class="form-check mb-3 fs-12 small-chekbox">
          [acceptance soglasie-na-rassilku id:flexCheckDefault14 class:form-check-input class:optional use_label_element optional]
          <label for="flexCheckDefault14" class="form-check-label text-start">
            Я даю свое <a class="text-primary" href="[cf7_mailing_consent_link id='1004']" target="_blank">согласие</a> на получение информационной и рекламной рассылки
          </label>
       </div>

    </div>
    <!-- /column -->
    <div class="col-12">
        <button type="submit" class="wpcf7-submit has-ripple btn [getthemebutton] btn-md btn-primary mx-5 mx-md-0">
            Отправить запрос
        </button>
    </div>
    <!-- /column -->
</div>
EOD;

   $form_post = [
      'post_title'   => 'Связаться с нами 2',
      'post_name'    => $slug,
      'post_content' => $form_content,
      'post_status'  => 'publish',
      'post_type'    => 'wpcf7_contact_form',
   ];

   $post_id = wp_insert_post($form_post);

   if (is_wp_error($post_id)) {
      error_log('Ошибка создания формы: ' . $post_id->get_error_message());
      return;
   }

   update_post_meta($post_id, '_form', $form_content);

   // Настройки письма (можешь подкорректировать по своему)
   $mail = [
      'subject'            => '[Связаться с нами] Запрос от [text-name]',
      'sender'             => '[your-email]',
      'body'               => "Имя: [text-name]\nEmail: [email-address]\nСообщение: [textarea-937]",
      'recipient'          => get_option('admin_email'),
      'additional_headers' => "Reply-To: [email-address]",
      'attachments'        => '',
      'use_html'           => true,
   ];

   update_post_meta($post_id, '_mail', $mail);

   error_log('Создана форма: ID ' . $post_id . ' | SLUG ' . $slug);
}



/**
 * Включает поддержку шорткодов внутри HTML-кода форм CF7.
 * Это позволяет использовать шорткоды вроде [cf7_legal_consent_link] прямо в шаблоне формы.
 */
add_filter('wpcf7_form_elements', function ($content) {
   return do_shortcode($content);
});

/**
 * Добавляет новую панель «Legal Consent» в редактор Contact Form 7.
 * Эта панель позволяет выбрать связанные юридические документы:
 * - Документ согласия на обработку данных (post_type = 'legal')
 * - Страницу с политикой конфиденциальности (post_type = 'page')
 */
add_filter('wpcf7_editor_panels', function ($panels) {
   $panels['legal_consent'] = [
      'title'    => __('Legal Consent', 'codeweber'),
      'callback' => 'codeweber_cf7_legal_consent_panel',
   ];
   return $panels;
});


/**
 * Отображает HTML-содержимое панели «Legal Consent» в редакторе формы.
 *
 * @param WPCF7_ContactForm $contact_form Объект текущей формы CF7.
 */
function codeweber_cf7_legal_consent_panel($contact_form)
{
   $form_id = $contact_form->id();

   $legal_posts = get_posts([
      'post_type'      => 'legal',
      'posts_per_page' => -1,
      'post_status'    => 'publish',
      'orderby'        => 'title',
      'order'          => 'ASC',
   ]);


   // Legal consent
   $selected_legal_id = get_post_meta($form_id, '_legal_consent_doc', true);
   if (empty($selected_legal_id)) {
      $legal_post = get_page_by_path('consent-processing', OBJECT, 'legal');
      if ($legal_post) $selected_legal_id = $legal_post->ID;
   }

   // Mailing consent (новое поле)
   $selected_mailing_id = get_post_meta($form_id, '_mailing_consent_doc', true);
   if (empty($selected_mailing_id)) {
      $mailing_post = get_page_by_path('email-consent', OBJECT, 'legal');
      if ($mailing_post) $selected_mailing_id = $mailing_post->ID;
   }

   $selected_privacy_id = get_post_meta($form_id, '_privacy_policy_page', true);
   if (empty($selected_privacy_id)) {
      $default_privacy_page_id = (int) get_option('wp_page_for_privacy_policy');
      if ($default_privacy_page_id) $selected_privacy_id = $default_privacy_page_id;
   }

?>
   <fieldset>
      <h2><?php _e('Select Legal Documents for This contact form:', 'codeweber'); ?></h2>

      <!-- Legal Consent -->
      <p>
         <label>
            <?php _e('Consent Document - Legal:', 'codeweber'); ?><br>
            <p><?php _e('Shortcode for displaying a document link in the form code: [cf7_legal_consent_link]', 'codeweber'); ?></p>
            <select name="legal_consent_doc">
               <option value=""><?php _e('— Select —', 'codeweber'); ?></option>
               <?php foreach ($legal_posts as $post): ?>
                  <option value="<?php echo esc_attr($post->ID); ?>" <?php selected($selected_legal_id, $post->ID); ?>>
                     <?php echo esc_html($post->post_title); ?>
                  </option>
               <?php endforeach; ?>
            </select>
         </label>
      </p>

      <!-- Mailing Consent (НОВОЕ) -->
      <p>
         <label>
            <?php _e('Consent Document - Mailing:', 'codeweber'); ?><br>
            <p><?php _e('Shortcode for displaying a document link in the form code: [cf7_mailing_consent_link]', 'codeweber'); ?></p>
            <select name="mailing_consent_doc">
               <option value=""><?php _e('— Select —', 'codeweber'); ?></option>
               <?php foreach ($legal_posts as $post): ?>
                  <option value="<?php echo esc_attr($post->ID); ?>" <?php selected($selected_mailing_id, $post->ID); ?>>
                     <?php echo esc_html($post->post_title); ?>
                  </option>
               <?php endforeach; ?>
            </select>
         </label>
      </p>

      <!-- Privacy Policy -->
      <p>
         <label>
            <?php _e('Privacy Policy - Page:', 'codeweber'); ?><br>
            <p><?php _e('Shortcode for displaying a document link in the form code: [cf7_privacy_policy]', 'codeweber'); ?></p>
            <select name="privacy_policy_page">
               <option value=""><?php _e('— Select —', 'codeweber'); ?></option>
               <?php foreach ($legal_posts as $page): ?>
                  <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($selected_privacy_id, $page->ID); ?>>
                     <?php echo esc_html($page->post_title); ?>
                  </option>
               <?php endforeach; ?>
            </select>
         </label>
      </p>
   </fieldset>

<?php
   $form_content = $contact_form->prop('form');

   // Privacy Policy check
   if (preg_match('/\[cf7_privacy_policy[^\]]*\]/i', $form_content)) {
      $doc_id = get_post_meta($form_id, '_privacy_policy_page', true);

      if ($doc_id) {
         if (get_post_status($doc_id) === 'publish') {
            echo '<p><strong><a href="' . esc_url(get_permalink($doc_id)) . '" target="_blank" rel="noopener noreferrer">'
               . sprintf(__('Privacy Policy: %s', 'codeweber'), esc_html(get_the_title($doc_id)))
               . '</a></strong></p>';
         } else {
            echo '<p><strong style="color:red;">'
               . __('The privacy policy document is selected but not published. Please publish the document.', 'codeweber')
               . '</strong></p>';
         }
      } else {
         echo '<p><strong style="color:red;">'
            . __('No privacy policy document selected. Please select a document in the form settings.', 'codeweber')
            . '</strong></p>';
      }
   } else {
      echo '<p><strong style="color:red;"><em>' . __('Privacy policy shortcode not found in the form.', 'codeweber') . '</em></p>';
   }

   // Legal Consent check
   if (preg_match('/\[cf7_legal_consent_link[^\]]*\]/i', $form_content)) {
      $doc_id = get_post_meta($form_id, '_legal_consent_doc', true);

      if ($doc_id) {
         if (get_post_status($doc_id) === 'publish') {
            echo '<p><strong><a href="' . esc_url(get_permalink($doc_id)) . '" target="_blank" rel="noopener noreferrer">'
               . sprintf(__('Consent Document: %s', 'codeweber'), esc_html(get_the_title($doc_id)))
               . '</a></strong></p>';
         } else {
            echo '<p><strong style="color:red;">'
               . __('The consent document is selected but not published. Please publish the document.', 'codeweber')
               . '</strong></p>';
         }
      } else {
         echo '<p><strong style="color:red;">'
            . __('No consent document selected. Please select a document in the form settings.', 'codeweber')
            . '</strong></p>';
      }
   } else {
      echo '<p><strong style="color:red;"><em>' . __('Consent document shortcode not found in the form.', 'codeweber') . '</em></p>';
   }

   // Mailing Consent check
   if (preg_match('/\[cf7_mailing_consent_link[^\]]*\]/i', $form_content)) {
      $doc_id = get_post_meta($form_id, '_mailing_consent_doc', true);

      if ($doc_id) {
         if (get_post_status($doc_id) === 'publish') {
            echo '<p><strong><a href="' . esc_url(get_permalink($doc_id)) . '" target="_blank" rel="noopener noreferrer">'
               . sprintf(__('Mailing Consent: %s', 'codeweber'), esc_html(get_the_title($doc_id)))
               . '</a></strong></p>';
         } else {
            echo '<p><strong style="color:red;">'
               . __('The mailing consent document is selected but not published. Please publish the document.', 'codeweber')
               . '</strong></p>';
         }
      } else {
         echo '<p><strong style="color:red;">'
            . __('No mailing consent document selected. Please select a document in the form settings.', 'codeweber')
            . '</strong></p>';
      }
   } else {
      echo '<p><strong style="color:red;"><em>' . __('Mailing consent shortcode not found in the form.', 'codeweber') . '</em></p>';
   }
}




/**
 * Сохраняет мета-данные формы CF7 после её сохранения в админке.
 * 
 * Сохраняются ID юридических документов:
 * - `_legal_consent_doc`: документ согласия на обработку данных
 * - `_privacy_policy_page`: страница политики конфиденциальности
 * 
 * @hook wpcf7_after_save
 * @param WPCF7_ContactForm $contact_form Объект формы CF7.
 */
add_action('wpcf7_after_save', function ($contact_form) {
   $form_id = $contact_form->id();

   if (isset($_POST['legal_consent_doc'])) {
      update_post_meta($form_id, '_legal_consent_doc', intval($_POST['legal_consent_doc']));
   }

   if (isset($_POST['privacy_policy_page'])) {
      update_post_meta($form_id, '_privacy_policy_page', intval($_POST['privacy_policy_page']));
   }

   // Добавь это для mailing_consent_doc
   if (isset($_POST['mailing_consent_doc'])) {
      update_post_meta($form_id, '_mailing_consent_doc', intval($_POST['mailing_consent_doc']));
   }
});




/**
 * Шорткод `[cf7_legal_consent_link id="123"]`
 * 
 * Возвращает ссылку на опубликованный документ согласия, 
 * связанный с указанной формой Contact Form 7.
 * 
 * @param array $atts Атрибуты шорткода. Требуется `id` — ID формы.
 * @return string URL документа или пустая строка, если не найден.
 */
add_shortcode('cf7_legal_consent_link', function ($atts) {

   $atts = shortcode_atts(['id' => 0], $atts);

   $form_id = intval($atts['id']);

   if (!$form_id) {
      return '';
   }

   $doc_id = get_post_meta($form_id, '_legal_consent_doc', true);

   if (!$doc_id) {
      return '';
   }

   $post_status = get_post_status($doc_id);

   if ($post_status !== 'publish') {
      return '';
   }

   $url = esc_url(get_permalink($doc_id));

   return $url;
});


/**
 * Шорткод `[cf7_mailing_consent_link id="123"]`
 * 
 * Возвращает ссылку на опубликованный документ согласия для рассылки,
 * связанный с указанной формой Contact Form 7.
 * 
 * @param array $atts Атрибуты шорткода. Требуется `id` — ID формы.
 * @return string URL документа или пустая строка, если не найден.
 */
add_shortcode('cf7_mailing_consent_link', function ($atts) {

   $atts = shortcode_atts(['id' => 0], $atts);

   $form_id = intval($atts['id']);

   if (!$form_id) {
      return '';
   }

   $doc_id = get_post_meta($form_id, '_mailing_consent_doc', true);

   if (!$doc_id) {
      return '';
   }

   $post_status = get_post_status($doc_id);

   if ($post_status !== 'publish') {
      return '';
   }

   $url = esc_url(get_permalink($doc_id));

   return $url;
});


/**
 * Шорткод `[cf7_privacy_policy id="123"]`
 * 
 * Возвращает ссылку на опубликованную страницу политики конфиденциальности,
 * связанную с указанной формой Contact Form 7.
 * 
 * @param array $atts Атрибуты шорткода. Требуется `id` — ID формы.
 * @return string URL страницы политики или пустая строка, если не найдена.
 */
add_shortcode('cf7_privacy_policy', function ($atts) {
   $atts = shortcode_atts([
      'id' => 0, // ID формы CF7
   ], $atts);

   $form_id = intval($atts['id']);
   if (!$form_id) {
      return ''; // ID не передан — ничего не возвращаем
   }

   $page_id = get_post_meta($form_id, '_privacy_policy_page', true);
   if (!$page_id || get_post_status($page_id) !== 'publish') {
      return ''; // Страница не задана или не опубликована
   }

   return esc_url(get_permalink($page_id));
});


/**
 * Автоматически обновляет шорткоды `[cf7_legal_consent_link]`, `[cf7_privacy_policy]`
 * и `[cf7_mailing_consent_link]` в контенте формы Contact Form 7 при её сохранении.
 *
 * Заменяет их на версии с текущим ID формы, чтобы обеспечить корректную работу ссылок.
 * Это необходимо, если формы дублируются или создаются новые, чтобы ID формы передавался внутрь шорткодов.
 *
 * @hook wpcf7_after_save
 * @param WPCF7_ContactForm $contact_form Объект сохранённой формы.
 */
add_action('wpcf7_after_save', function ($contact_form) {
   $form_id = $contact_form->id();
   $form_content = $contact_form->prop('form');

   if (empty($form_content)) {
      return;
   }

   $new_content = $form_content;

   // Шорткод [cf7_legal_consent_link ...]
   $pattern1 = '/\[cf7_legal_consent_link[^\]]*\]/i';
   $replacement1 = "[cf7_legal_consent_link id='{$form_id}']";
   $new_content = preg_replace($pattern1, $replacement1, $new_content);

   // Шорткод [cf7_privacy_policy ...]
   $pattern2 = '/\[cf7_privacy_policy[^\]]*\]/i';
   $replacement2 = "[cf7_privacy_policy id='{$form_id}']";
   $new_content = preg_replace($pattern2, $replacement2, $new_content);

   // Шорткод [cf7_mailing_consent_link ...]
   $pattern3 = '/\[cf7_mailing_consent_link[^\]]*\]/i';
   $replacement3 = "[cf7_mailing_consent_link id='{$form_id}']";
   $new_content = preg_replace($pattern3, $replacement3, $new_content);

   // Сохраняем только если что-то изменилось
   if ($new_content !== $form_content) {
      $contact_form->set_properties(['form' => $new_content]);
      $contact_form->save();
   }
});


/**
 *  * Добавляет новую вкладку "Field Mapping" (Сопоставление полей) в редактор форм Contact Form 7.
 *
 * Contact Form 7 (CF7) предоставляет в админке редактор форм,
 * который разбит на несколько панелей (вкладок).
 * Этот фильтр позволяет расширить набор вкладок, добавляя свои.
 *
 * @param array $panels Массив панелей редактора CF7
 * @return array Обновленный массив панелей с добавленной вкладкой
 */
add_filter('wpcf7_editor_panels', 'cw_add_field_mapping_panel');
function cw_add_field_mapping_panel($panels)
{
   $panels['field_mapping'] = [
      'title'    => __('Field Mapping', 'codeweber'),
      'callback' => 'cw_render_field_mapping_panel',
   ];
   return $panels;
}

/**
 * Отображает содержимое вкладки "Field Mapping" в редакторе CF7
 * Позволяет сопоставить поля формы с заданными типами данных
 *
 * @param WPCF7_ContactForm $contact_form Объект формы CF7
 * @return void
 */
/**
 * Отображает вкладку "Field Mapping" в редакторе CF7
 * Добавляет под таблицей ссылки на Политику конфиденциальности и документ согласия,
 * если соответствующие шорткоды найдены в форме.
 *
 * @param WPCF7_ContactForm $contact_form Объект формы CF7
 * @return void
 */
function cw_render_field_mapping_panel($contact_form)
{
   $form_id = $contact_form->id();
   $form_content = $contact_form->prop('form');

   // Получаем имена полей из формы CF7
   preg_match_all('/\[(?:[^\s\]]+\*?)\s+([^\s\]]+)/', $form_content, $matches);
   $form_fields = !empty($matches[1]) ? $matches[1] : [];

   // Field types for mapping
   $fields = [
      'first_name'  => __('First Name', 'codeweber'),
      'last_name'   => __('Last Name', 'codeweber'),
      'middle_name' => __('Middle Name', 'codeweber'),
      'phone'       => __('Phone', 'codeweber'),
      'email'       => __('E-mail', 'codeweber'),
      'checkboxprivacy'     => __('Checkbox for Privacy Policy', 'codeweber'),
      'checkboxconsent'     => __('Checkbox for Consent', 'codeweber'),
      'checkboxnewsletter'  => __('Checkbox for Newsletter', 'codeweber'),
   ];

   echo '<h2>' . __('Field Mapping:', 'codeweber') . '</h2>';
   echo '<p>' . __("Mapping form fields to module fields for storing user consent logs with the site's legal documents.", "codeweber") . '</p>';
   echo '<p>' . __("How is data saved to the log?
When a user submits a form, we search the database for the user's email, and if it is found, we write the data to the log for that user. If the email is missing from the database, a new user is created. If there is no email in the form, a user is created with an email of the following format: 79493411166@domainsite", "codeweber") . '</p>';

   echo '<table class="form-table">';
   foreach ($fields as $key => $label) {
      echo '<tr>';
      echo '<th scope="row"><label for="field_mapping_' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
      echo '<td>';
      echo '<select name="cw_field_mapping[' . esc_attr($key) . ']" id="field_mapping_' . esc_attr($key) . '">';
      echo '<option value="">' . __('— Not selected —', 'codeweber') . '</option>';

      foreach ($form_fields as $field_name) {
         $selected = selected(cw_get_saved_field_mapping($form_id, $key), $field_name, false);
         echo '<option value="' . esc_attr($field_name) . '"' . $selected . '>' . esc_html($field_name) . '</option>';
      }

      echo '</select>';
      echo '</td>';
      echo '</tr>';
   }

   echo '</table>';

}


/**
 * Получает сохранённое сопоставление поля формы CF7 по ключу.
 *
 * @param int    $form_id ID формы CF7
 * @param string $key     Ключ поля для сопоставления (например, 'first_name', 'email' и т.п.)
 * @return string Значение сохранённого сопоставления или пустая строка
 */
function cw_get_saved_field_mapping($form_id, $key)
{
   $mappings = get_post_meta($form_id, '_cw_field_mapping', true);
   return isset($mappings[$key]) ? $mappings[$key] : '';
}


/**
 * Сохраняет сопоставление полей формы при сохранении CF7
 *
 * @param WPCF7_ContactForm $contact_form Объект формы CF7
 * @return void
 */
add_action('wpcf7_save_contact_form', function ($contact_form) {
   if (isset($_POST['cw_field_mapping']) && is_array($_POST['cw_field_mapping'])) {
      $cleaned = array_map('sanitize_text_field', $_POST['cw_field_mapping']);
      update_post_meta($contact_form->id(), '_cw_field_mapping', $cleaned);
   }
});


/**
 * Хук, который срабатывает после успешной отправки формы Contact Form 7
 * 
 * Этот код автоматически создаёт или обновляет пользователя WordPress на основе данных из формы CF7.
 * 
 * Основные шаги:
 * 1. Получение данных, отправленных через форму.
 * 2. Получение сопоставления полей (mapping) из метаданных формы.
 * 3. Извлечение и очистка основных данных пользователя (имя, фамилия, email, телефон).
 * 4. Получение IP-адреса, user-agent и URL страницы, где была отправлена форма.
 * 5. Генерация email на основе телефона, если email не указан.
 * 6. Транслитерация имени для формирования логина и отображаемого имени.
 * 7. Проверка, существует ли уже пользователь с таким email или логином.
 * 8. Создание нового пользователя, если его нет.
 * 9. Сохранение дополнительных данных пользователя (телефон, имя, фамилия, никнейм).
 * 10. Сохранение информации о согласиях пользователя с политикой конфиденциальности и обработкой данных.
 * 
 * @param WPCF7_ContactForm $contact_form Объект отправленной формы Contact Form 7
 */

add_action('wpcf7_mail_sent', function ($contact_form) {
   $submission = WPCF7_Submission::get_instance();
   if (!$submission) return;

   $data = $submission->get_posted_data();
   $form_id = $contact_form->id();
   $form_title = get_the_title($form_id);

   $field_map = get_post_meta($form_id, '_cw_field_mapping', true);
   if (!is_array($field_map)) $field_map = [];

   $first_name  = trim($data[$field_map['first_name']] ?? '');
   $last_name   = trim($data[$field_map['last_name']] ?? '');
   $middle_name = trim($data[$field_map['middle_name']] ?? '');
   $phone       = trim($data[$field_map['phone']] ?? '');
   $email       = trim($data[$field_map['email']] ?? '');

   $ip_address  = $_SERVER['REMOTE_ADDR'] ?? '';
   $user_agent  = $_SERVER['HTTP_USER_AGENT'] ?? '';
   $timestamp   = current_time('mysql');
   $page_url    = $submission->get_meta('url');

   $phone_digits = preg_replace('/\D+/', '', $phone);

   if (empty($email) && !empty($phone_digits)) {
      $site_url = parse_url(home_url(), PHP_URL_HOST);
      $email = $phone_digits . '@' . $site_url;
   }

   $user_login = $phone_digits ?: 'user_' . wp_rand(1000, 9999);

   $translit = function ($text) {
      $translit_table = [
         'а' => 'a',
         'б' => 'b',
         'в' => 'v',
         'г' => 'g',
         'д' => 'd',
         'е' => 'e',
         'ё' => 'e',
         'ж' => 'zh',
         'з' => 'z',
         'и' => 'i',
         'й' => 'y',
         'к' => 'k',
         'л' => 'l',
         'м' => 'm',
         'н' => 'n',
         'о' => 'o',
         'п' => 'p',
         'р' => 'r',
         'с' => 's',
         'т' => 't',
         'у' => 'u',
         'ф' => 'f',
         'х' => 'h',
         'ц' => 'c',
         'ч' => 'ch',
         'ш' => 'sh',
         'щ' => 'shch',
         'ы' => 'y',
         'э' => 'e',
         'ю' => 'yu',
         'я' => 'ya',
         'ь' => '',
         'ъ' => '',
         ' ' => '-'
      ];
      $text = mb_strtolower($text, 'UTF-8');
      return strtr($text, $translit_table);
   };

   $display_name = $translit($first_name ?: $user_login);
   $nickname = $display_name;

   if (empty($email) && !empty($phone_digits)) {
      $user_query = new WP_User_Query([
         'meta_key'    => 'phone',
         'meta_value'  => $phone,
         'number'      => 1,
         'count_total' => false,
         'fields'      => 'all',
      ]);
      $found_users = $user_query->get_results();
      if (!empty($found_users)) {
         $user = $found_users[0];
         $user_id = $user->ID;
      } else {
         $site_url = parse_url(home_url(), PHP_URL_HOST);
         $email = $phone_digits . '@' . $site_url;
         $user = false;
      }
   } else {
      $user = get_user_by('email', $email);
   }

   if (!$user) {
      $password = wp_generate_password(12, false);
      $user_id = wp_create_user($user_login, $password, $email);
      if (is_wp_error($user_id)) return;

      wp_update_user([
         'ID'           => $user_id,
         'first_name'   => $first_name,
         'last_name'    => $last_name,
         'middle_name'    => $middle_name,
         'nickname'     => $nickname,
         'display_name' => $display_name,
      ]);

      update_user_meta($user_id, 'phone', $phone);
   } else {
      $user_id = $user->ID;
      if (!get_user_meta($user_id, 'phone', true) && !empty($phone)) {
         update_user_meta($user_id, 'phone', $phone);
      }
   }

   $session_id = uniqid('cf7_', true);
   $existing_consents = get_user_meta($user_id, 'codeweber_user_consents', true);
   if (!is_array($existing_consents)) $existing_consents = [];

   // Получаем ID документов
   $privacy_page_id    = (int) get_option('wp_page_for_privacy_policy');
   $processing_doc_id  = (int) get_post_meta($form_id, '_legal_consent_doc', true);
   $mailing_doc_id     = (int) get_post_meta($form_id, '_mailing_consent_doc', true);

   // Получаем поля acceptance
   $privacy_field    = $field_map['checkboxprivacy'] ?? '';
   $processing_field = $field_map['checkboxconsent'] ?? '';
   $mailing_field    = $field_map['checkboxnewsletter'] ?? '';

   if (!empty($data[$privacy_field]) && $privacy_page_id && get_post_status($privacy_page_id) === 'publish') {

      $checkboxtext = get_acceptance_label_html($processing_field, $form_id);


      $key = 'privacy_policy';
      $existing_consents[$key . '_' . $session_id] = [
         'type'        => 'privacy_policy',
         'title'       => get_the_title($privacy_page_id),
         'url'         => get_permalink($privacy_page_id),
         'revision'    => get_latest_revision_link($privacy_page_id),
         'ip'          => $ip_address,
         'user_agent'  => $user_agent,
         'date'        => $timestamp,
         'page_url'    => $page_url,
         'session_id'  => $session_id,
         'form_title'  => $form_title,
         'phone'       => $phone,
         'acceptance_html' => $checkboxtext,
      ];
   }

   if (!empty($data[$mailing_field]) && $mailing_doc_id && get_post_status($mailing_doc_id) === 'publish') {

      $checkboxtext = get_acceptance_label_html($processing_field, $form_id);


      $key = 'mailing_consent';
      $existing_consents[$key . '_' . $session_id] = [
         'type'        => 'mailing_consent',
         'title'       => get_the_title($mailing_doc_id),
         'url'         => get_permalink($mailing_doc_id),
         'revision'    => get_latest_revision_link($mailing_doc_id),
         'ip'          => $ip_address,
         'user_agent'  => $user_agent,
         'date'        => $timestamp,
         'page_url'    => $page_url,
         'session_id'  => $session_id,
         'form_title'  => $form_title,
         'phone'       => $phone,
         'acceptance_html' => $checkboxtext,
      ];
   }

   if (!empty($data[$processing_field]) && $processing_doc_id && get_post_status($processing_doc_id) === 'publish') {

      $checkboxtext = get_acceptance_label_html($processing_field, $form_id);

      $key = 'pdn_processing';
      $existing_consents[$key . '_' . $session_id] = [
         'type'        => 'pdn_processing',
         'title'       => get_the_title($processing_doc_id),
         'url'         => get_permalink($processing_doc_id),
         'revision'    => get_latest_revision_link($processing_doc_id),
         'ip'          => $ip_address,
         'user_agent'  => $user_agent,
         'date'        => $timestamp,
         'page_url'    => $page_url,
         'session_id'  => $session_id,
         'form_title'  => $form_title,
         'phone'       => $phone,
         'acceptance_html' => $checkboxtext,
      ];
   }

   update_user_meta($user_id, 'codeweber_user_consents', $existing_consents);
});




/**
 * Извлекает HTML-текст согласия по имени acceptance-поля из формы Contact Form 7
 *
 * @param string $acceptance_name — имя поля (например, 'acceptance-1')
 * @param int $form_id — ID формы CF7
 * @return string|null — HTML текста согласия или null, если не найден
 */
function get_acceptance_label_html($acceptance_name, $form_id)
{
   if (!function_exists('do_shortcode')) return null;

   $form_post = get_post($form_id);
   if (!$form_post || $form_post->post_type !== 'wpcf7_contact_form') return null;

   $content = $form_post->post_content;

   // Ищем блок с нужным acceptance
   $pattern = '/\[acceptance[^\]]*?' . preg_quote($acceptance_name, '/') . '[^\]]*\].*?<label[^>]*>(.*?)<\/label>/is';

   if (preg_match($pattern, $content, $matches)) {
      $raw_label_html = $matches[1];

      // Обрабатываем шорткоды внутри текста
      $processed = do_shortcode($raw_label_html);

      return $processed;
   }

   return null;
}



// Шаг 1: Отслеживаем активацию CF7 и ставим флаг
add_action('activated_plugin', function ($plugin) {
   if ($plugin === 'contact-form-7/wp-contact-form-7.php') {
      update_option('my_cf7_just_activated', 1);
   }
});

// Шаг 2: На admin_init проверяем флаг и создаем формы
add_action('admin_init', function () {
   if (get_option('my_cf7_just_activated')) {
      // Удаляем флаг сразу, чтобы не сработало повторно
      delete_option('my_cf7_just_activated');

      // Проверяем, активен ли CF7
      if (!function_exists('is_plugin_active')) {
         include_once(ABSPATH . 'wp-admin/includes/plugin.php');
      }
      if (is_plugin_active('contact-form-7/wp-contact-form-7.php')) {

         // Теперь функции должны быть доступны — вызываем их
         if (function_exists('create_custom_cf7_form')) {
            create_custom_cf7_form();
         }
         if (function_exists('create_custom_cf7_form_with_name_and_email')) {
            create_custom_cf7_form_with_name_and_email();
         }
         if (function_exists('create_custom_cf7_form_with_name_comment_and_email')) {
            create_custom_cf7_form_with_name_comment_and_email();
         }
         if (function_exists('create_custom_cf7_form_with_name_comment_and_email_2')) {
            create_custom_cf7_form_with_name_comment_and_email_2();
         }
         if (function_exists('create_newsletter_cf7_form')) {
            create_newsletter_cf7_form();
         }

         error_log('Contact Form 7 активирован — формы созданы (через admin_init).');

         add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p>Contact Form 7 активирован — формы созданы успешно.</p></div>';
         });
      }
   }
});