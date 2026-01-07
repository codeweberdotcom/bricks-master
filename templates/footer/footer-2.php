<?php

$footer_color_text = $footer_vars['footer_color_text'] ?? false;
$footer_background = $footer_vars['footer_background'] ?? false;

if ($footer_background === 'solid') {
   $footer_background_color = $footer_vars['footer_solid_color'] ?? false;
} elseif ($footer_background === 'soft') {
   $footer_background_color = $footer_vars['footer_soft_color'] ?? false;
} else {
   $footer_background_color = 'dark';
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

// Получаем настройку цвета логотипа для футера
$footer_logo_color = Redux::get_option($opt_name, 'footer-logo-color', 'light');

// Получаем телефоны
$phone1 = Redux::get_option($opt_name, 'phone_01', '');
$phone2 = Redux::get_option($opt_name, 'phone_02', '');

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

$full_address = !empty($parts) ? implode(', ', $parts) : 'Moonshine St. 14/05 Light City, London, United Kingdom';

// Определяем класс для соцсетей (social-muted для светлого фона, social-white для темного)
$social_class = ($footer_background_color === 'dark' || $footer_color_text === 'dark') ? 'social-white' : 'social-muted';

?>

<footer class="bg-<?= $footer_background_color; ?> <?= $text_inverse; ?>">
  <div class="container pb-7">
    <div class="row gx-lg-0 gy-6">
      <div class="col-lg-4">
        <div class="widget">
          <a href="<?= esc_url(home_url('/')); ?>" class="d-inline-block mb-4">
            <?= get_custom_logo_type($footer_logo_color); ?>
          </a>
          <p class="lead mb-0 <?= $text_class; ?>">
            <?= do_shortcode('[redux_option key="text-about-company"]'); ?>
          </p>
        </div>
        <!-- /.widget -->
      </div>
      <!-- /column -->
      <div class="col-lg-3 offset-lg-2">
        <div class="widget">
          <div class="d-flex flex-row">
            <div>
              <div class="icon text-primary fs-28 me-4 mt-n1">
                <i class="uil uil-phone-volume"></i>
              </div>
            </div>
            <div>
              <div class="h5 mb-1 <?= $text_class; ?>"><?php esc_html_e('Phone', 'codeweber'); ?></div>
              <p class="mb-0 <?= $text_class; ?>">
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
          </div>
          <!--/div -->
        </div>
        <!-- /.widget -->
      </div>
      <!-- /column -->
      <div class="col-lg-3">
        <div class="widget">
          <div class="d-flex flex-row">
            <div>
              <div class="icon text-primary fs-28 me-4 mt-n1">
                <i class="uil uil-location-pin-alt"></i>
              </div>
            </div>
            <div class="align-self-start justify-content-start">
              <div class="h5 mb-1 <?= $text_class; ?>"><?php esc_html_e('Address', 'codeweber'); ?></div>
              <address class="<?= $text_class; ?> mb-0"><?= esc_html($full_address); ?></address>
            </div>
          </div>
          <!--/div -->
        </div>
        <!-- /.widget -->
      </div>
      <!-- /column -->
    </div>
    <!--/.row -->
    <hr class="mt-13 mt-md-14 mb-7" />
    <div class="d-md-flex align-items-center justify-content-between">
      <p class="mb-2 mb-lg-0 <?= $text_class; ?>">
        <a class="<?= $text_class; ?>" href="<?php echo esc_attr(wp_get_theme()->get('ThemeURI')); ?>" target="_blank">
          Made with Codeweber
        </a>
      </p>
      <nav class="nav <?= esc_attr($social_class); ?> mb-0 text-md-end">
        <?= social_links('', $social_type_footer, $social_size_footer, 'primary', 'solid', $social_button_style_footer); ?>
      </nav>
      <!-- /.social -->
    </div>
  </div>
  <!-- /.container -->
</footer>
