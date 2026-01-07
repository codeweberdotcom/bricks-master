<?php

$footer_color_text = $footer_vars['footer_color_text'] ?? false;
$footer_background = $footer_vars['footer_background'] ?? false;

if ($footer_background === 'solid') {
   $footer_background_color = $footer_vars['footer_solid_color'] ?? false;
} elseif ($footer_background === 'soft') {
   $footer_background_color = $footer_vars['footer_soft_color'] ?? false;
} else {
   $footer_background_color = 'soft-primary';
}

// Для footer-5 используем цвет из настроек Redux
if (!empty($footer_background_color)) {
   $footer_bg_class = 'bg-' . $footer_background_color;
} else {
   $footer_bg_class = 'bg-soft-primary';
}

// Определяем классы для текста
$text_class_array = [];
if ($footer_color_text === 'dark') {
   $text_class_array[] = 'text-white';
   $text_inverse = 'text-inverse';
} else {
   $text_class_array[] = 'text-reset';
   $text_inverse = '';
}
$text_class = implode(' ', $text_class_array);

// Получаем настройки соцсетей для футера
global $opt_name;
$social_icon_type_footer = Redux::get_option($opt_name, 'social-icon-type-footer', '1');
$social_type_footer = 'type' . $social_icon_type_footer;
$social_size_footer = Redux::get_option($opt_name, 'social-button-size-footer', 'md');
$social_button_style_footer = Redux::get_option($opt_name, 'social-button-style-footer', 'circle');

// Получаем текст для CTA секции
$cta_text = Redux::get_option($opt_name, 'footer-cta-text', '');
if (empty($cta_text)) {
   $cta_text = do_shortcode('[redux_option key="text-about-company"]');
}
$cta_button_text = Redux::get_option($opt_name, 'footer-cta-button-text', 'Join Us');
$cta_button_url = Redux::get_option($opt_name, 'footer-cta-button-url', '#');

// Получаем фоновое изображение для карточки
$cta_bg_image = Redux::get_option($opt_name, 'footer-cta-bg-image', '');
$cta_bg_image_url = '';
if (!empty($cta_bg_image) && is_array($cta_bg_image) && !empty($cta_bg_image['url'])) {
   $cta_bg_image_url = $cta_bg_image['url'];
} elseif (!empty($cta_bg_image) && is_string($cta_bg_image)) {
   $cta_bg_image_url = $cta_bg_image;
}

// Получаем адрес
$country = Redux::get_option($opt_name, 'fact-country', '');
$region = Redux::get_option($opt_name, 'fact-region', '');
$city = Redux::get_option($opt_name, 'fact-city', '');
$street = Redux::get_option($opt_name, 'fact-street', '');
$house = Redux::get_option($opt_name, 'fact-house', '');
$office = Redux::get_option($opt_name, 'fact-office', '');
$postal = Redux::get_option($opt_name, 'fact-postal', '');

$parts = [];
// Формируем строку улицы с домом и офисом
$street_line = trim(implode(' ', array_filter([$street, $house, $office])), ' ,');
// Порядок: индекс, страна, регион, город, улица
if (!empty($postal)) $parts[] = $postal;
if (!empty($country)) $parts[] = $country;
if (!empty($region)) $parts[] = $region;
if (!empty($city)) $parts[] = $city;
if (!empty($street_line)) $parts[] = $street_line;

$full_address = !empty($parts) ? implode(', ', $parts) : 'Moonshine St. 14/05 Light City, London, UK';

// Получаем телефоны
$phone1 = Redux::get_option($opt_name, 'phone_01', '');
$phone2 = Redux::get_option($opt_name, 'phone_02', '');

// Получаем email
$email1 = Redux::get_option($opt_name, 'e-mail', '');
$email2 = Redux::get_option($opt_name, 'e-mail-2', '');

// Определяем класс для соцсетей (по умолчанию без social-white, так как фон светлый)
$social_class = '';

// Определяем класс для ссылок (link-body или text-white)
$link_class = ($footer_color_text === 'dark') ? 'text-white' : 'link-body';

?>

<footer class="<?= esc_attr($footer_bg_class); ?>">
  <div class="container">
    <div class="row">
      <div class="col-xl-11 col-xxl-10 mx-auto">
        <div class="card image-wrapper bg-full bg-image bg-overlay bg-overlay-400 mt-n50p mb-n5"<?= !empty($cta_bg_image_url) ? ' style="background-image: url(' . esc_url($cta_bg_image_url) . ');"' : ' data-image-src="' . esc_url(get_template_directory_uri() . '/dist/assets/img/photos/bg3.jpg') . '"'; ?>>
          <div class="card-body p-6 p-md-11 d-lg-flex flex-row align-items-lg-center justify-content-md-between text-center text-lg-start">
            <div class="h3 display-6 mb-6 mb-lg-0 pe-lg-15 pe-xxl-18 text-white"><?= esc_html($cta_text); ?></div>
            <a href="<?= esc_url($cta_button_url); ?>" class="btn btn-white rounded-pill mb-0 text-nowrap"><?= esc_html($cta_button_text); ?></a>
          </div>
          <!--/.card-body -->
        </div>
        <!--/.card -->
      </div>
      <!-- /column -->
    </div>
    <!-- /.row -->
  </div>
  <div class="container pb-12 text-center">
    <div class="row mt-n10 mt-lg-0">
      <div class="col-xl-10 mx-auto">
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="widget">
              <div class="h4 widget-title <?= $text_class; ?>"><?php esc_html_e('Address', 'codeweber'); ?></div>
              <address class="<?= $text_class; ?>"><?= $full_address; ?></address>
            </div>
            <!-- /.widget -->
          </div>
          <!--/column -->
          <div class="col-md-4">
            <div class="widget">
              <div class="h4 widget-title <?= $text_class; ?>"><?php esc_html_e('Phone', 'codeweber'); ?></div>
              <p class="<?= $text_class; ?> mb-0">
                <?php if (!empty($phone1)): ?>
                  <?php echo do_shortcode('[get_contact field="phone_01" type="link" class="' . $text_class . '"]'); ?>
                  <?php if (!empty($phone2)): ?>
                    <br />
                  <?php endif; ?>
                <?php endif; ?>
                <?php if (!empty($phone2)): ?>
                  <?php echo do_shortcode('[get_contact field="phone_02" type="link" class="' . $text_class . '"]'); ?>
                <?php endif; ?>
                <?php if (empty($phone1) && empty($phone2)): ?>
                  00 (123) 456 78 90 <br />00 (987) 654 32 10
                <?php endif; ?>
              </p>
            </div>
            <!-- /.widget -->
          </div>
          <!--/column -->
          <div class="col-md-4">
            <div class="widget">
              <div class="h4 widget-title <?= $text_class; ?>"><?php esc_html_e('E-mail', 'codeweber'); ?></div>
              <p class="<?= $text_class; ?> mb-0">
                <?php if (!empty($email1)): ?>
                  <a href="mailto:<?= esc_attr($email1); ?>" class="<?= esc_attr($link_class); ?>"><?= esc_html($email1); ?></a>
                  <?php if (!empty($email2)): ?>
                    <br class="d-none d-md-block" />
                  <?php endif; ?>
                <?php endif; ?>
                <?php if (!empty($email2)): ?>
                  <a href="mailto:<?= esc_attr($email2); ?>" class="<?= esc_attr($link_class); ?>"><?= esc_html($email2); ?></a>
                <?php endif; ?>
                <?php if (empty($email1) && empty($email2)): ?>
                  <a href="mailto:sandbox@email.com" class="<?= esc_attr($link_class); ?>">sandbox@email.com</a> <br class="d-none d-md-block" /><a href="mailto:help@sandbox.com" class="<?= esc_attr($link_class); ?>">help@sandbox.com</a>
                <?php endif; ?>
              </p>
            </div>
            <!-- /.widget -->
          </div>
          <!--/column -->
        </div>
        <!--/.row -->
        <p class="<?= $text_class; ?>">© <?= date('Y'); ?> <?= get_bloginfo('name'); ?>. All rights reserved.</p>
        <nav class="nav <?= esc_attr($social_class); ?> justify-content-center">
          <?= social_links('', $social_type_footer, $social_size_footer, 'primary', 'solid', $social_button_style_footer); ?>
        </nav>
        <!-- /.social -->
      </div>
      <!-- /column -->
    </div>
    <!-- /.row -->
  </div>
  <!-- /.container -->
</footer>

