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
  <div class="invalid-feedback">
        Введите ваше Имя.
      </div>
</div>

<div class="form-floating mb-3 text-dark"> 
  [tel* tel-463 id:floatingTel class:phone-mask class:form-control placeholder "+7(000)123-45-67"]
  <label for="floatingTel">+7(000)123-45-67</label>
  <div class="invalid-feedback">
        Введите ваш телефон.
      </div>
</div>

 <div class="form-check mb-2 fs-12 small-chekbox wpcf7-acceptance">
  [acceptance soglasie-na-obrabotku id:flexCheckDefault1 class:form-check-input use_label_element]
  <label for="flexCheckDefault1" class="form-check-label text-start">
    Я даю свое <a class="text-primary" href="[cf7_legal_consent_link]" target="_blank">согласие</a> на обработку моих персональных данных.<br> С документом <a href="[cf7_privacy_policy]" target="_blank">политика обработки персональных данных</a> ознакомлен.
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
}


/**
 * Создаёт форму "Подписка на рассылку" с email и согласием.
 */
function create_newsletter_cf7_form()
{
   if (!post_type_exists('wpcf7_contact_form')) {
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
}



function create_custom_cf7_form_with_name_and_email()
{
   if (!post_type_exists('wpcf7_contact_form')) {
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
  <div class="invalid-feedback">
        Введите ваше Имя.
      </div>
</div>

<div class="form-floating mb-3 text-dark"> 
  [email* email-address id:floatingEmail class:form-control placeholder "Ваш Email"]
  <label for="floatingEmail">Ваш Email</label>
  <div class="invalid-feedback">
        Введите ваш E-Mail.
      </div>
</div>

<div class="form-floating mb-3 text-dark"> 
  [tel* tel-463 id:floatingTel class:phone-mask class:form-control placeholder "+7(000)123-45-67"]
  <label for="floatingTel">+7(000)123-45-67</label>
  <div class="invalid-feedback">
        Введите ваш телефон.
      </div>
</div>

<div class="form-check mb-2 fs-12 small-chekbox wpcf7-acceptance">
  [acceptance soglasie-na-obrabotku id:flexCheckDefault1 class:form-check-input use_label_element]
  <label for="flexCheckDefault1" class="form-check-label text-start">
    Я даю свое <a class="text-primary" href="[cf7_legal_consent_link]" target="_blank">согласие</a> на обработку моих персональных данных.<br> С документом <a href="[cf7_privacy_policy]" target="_blank">политика обработки персональных данных</a> ознакомлен.
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
}


function create_custom_cf7_form_with_name_comment_and_email()
{
   if (!post_type_exists('wpcf7_contact_form')) {
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
            <div class="invalid-feedback">
        Введите ваше Имя.
      </div>
        </div>
    </div>
    <!-- /column -->
    <div class="col-md-6">
        <div class="form-floating mb-4">
            [text* text-lastname id:floatingLastName class:form-control placeholder "Ваша Фамилия"]
            <label for="floatingLastName">Ваша Фамилия</label>
            <div class="invalid-feedback">
        Введите вашу Фамилию.
      </div>
        </div>
    </div>
    <!-- /column -->
    <div class="col-md-6">
        <div class="form-floating mb-4">
            [email* email-address id:floatingEmail class:form-control placeholder "Ваш Email"]
            <label for="floatingEmail">Ваш Email</label>
            <div class="invalid-feedback">
        Введите ваш E-Mail.
      </div>
        </div>
    </div>
    <!-- /column -->
    <div class="col-md-6">
        <div class="form-floating mb-4">
            [tel* tel-463 id:floatingTel class:phone-mask class:form-control placeholder "+7(000)123-45-67"]
            <label for="floatingTel">+7(000)123-45-67</label>
            <div class="invalid-feedback">
        Введите ваш телефон.
      </div>
        </div>
    </div>
    <!-- /column -->
    <div class="col-12">
        <div class="form-floating mb-4">
            [textarea* textarea-937 id:floatingMessage class:form-control placeholder "Ваше сообщение"]
            <label for="floatingMessage">Ваше сообщение</label>
            <div class="invalid-feedback">
        Введите ваше Сообщение.
      </div>
        </div>
    </div>
    <!-- /column -->
    <div class="col-12">
  

   <div class="form-check mb-2 fs-12 small-chekbox wpcf7-acceptance">
  [acceptance soglasie-na-obrabotku id:flexCheckDefault1 class:form-check-input use_label_element]
  <label for="flexCheckDefault1" class="form-check-label text-start">
    Я даю свое <a class="text-primary" href="[cf7_legal_consent_link]" target="_blank">согласие</a> на обработку моих персональных данных.<br> С документом <a href="[cf7_privacy_policy]" target="_blank">политика обработки персональных данных</a> ознакомлен.
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
}


function create_custom_cf7_form_with_name_comment_and_email_2()
{
   if (!post_type_exists('wpcf7_contact_form')) {
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
            <div class="invalid-feedback">
        Введите ваше Имя.
      </div>
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
             <div class="invalid-feedback">
        Введите ваше Сообщение.
      </div>
        </div>
    </div>
    <!-- /column -->
    <div class="col-12">

       <div class="form-check mb-2 fs-12 small-chekbox wpcf7-acceptance">
          [acceptance soglasie-na-obrabotku id:flexCheckDefault1 class:form-check-input use_label_element]
          <label for="flexCheckDefault1" class="form-check-label text-start">
            Я даю свое <a class="text-primary" href="[cf7_legal_consent_link id='1004']" target="_blank">согласие</a> на обработку моих персональных данных.<br> С документом <a href="[cf7_privacy_policy id='1004']" target="_blank">политика обработки персональных данных</a> ознакомлен.
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
         add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p>Contact Form 7 активирован — формы созданы успешно.</p></div>';
         });
      }
   }
});




add_action('rest_api_init', function () {
   register_rest_route('custom/v1', '/cf7-title/(?P<id>\d+)', [
      'methods' => 'GET',
      'callback' => 'get_cf7_form_title',
      'permission_callback' => '__return_true'
   ]);
});

function get_cf7_form_title($data)
{
   $form_id = $data['id'];

   // Находим пост формы по ID
   $form_post = get_post($form_id);

   if (!$form_post || $form_post->post_type !== 'wpcf7_contact_form') {
      return new WP_Error('form_not_found', 'Форма не найдена', ['status' => 404]);
   }

   return [
      'id' => $form_id,
      'title' => $form_post->post_title,
      'slug' => $form_post->post_name
   ];
}