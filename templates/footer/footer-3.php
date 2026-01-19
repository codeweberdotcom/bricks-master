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

// Определяем класс для соцсетей (social-white для темного фона)
$social_class = ($footer_background_color === 'dark' || $footer_color_text === 'dark') ? 'social-white' : '';

// Получаем текст для CTA секции (можно использовать text-about-company или создать отдельную настройку)
$cta_text = Redux::get_option($opt_name, 'footer-cta-text', '');
if (empty($cta_text)) {
   $cta_text = do_shortcode('[redux_option key="text-about-company"]');
}
$cta_button_text = Redux::get_option($opt_name, 'footer-cta-button-text', 'Try It For Free');
$cta_button_url = Redux::get_option($opt_name, 'footer-cta-button-url', '#');

?>

<footer class="bg-<?= $footer_background_color; ?> <?= $text_inverse; ?>">
  <div class="container pt-15 pt-md-17 pb-13 pb-md-15">
    <div class="d-lg-flex flex-row align-items-lg-center">
      <div class="h3 display-4 mb-6 mb-lg-0 pe-lg-20 pe-xl-22 pe-xxl-25 text-white"><?= esc_html($cta_text); ?></div>
      <a href="<?= esc_url($cta_button_url); ?>" class="btn btn-primary rounded-pill mb-0 text-nowrap"><?= esc_html($cta_button_text); ?></a>
    </div>
    <!--/div -->
    <hr class="mt-11 mb-12" />
    <div class="row gy-6 gy-lg-0">
      <?php
      // Колонка 1 - Footer 1
      codeweber_footer_column('footer-1', 'col-md-4 col-lg-3', function() use ($footer_logo_color, $text_class, $social_class, $social_type_footer, $social_size_footer, $social_button_style_footer) {
        ?>
        <div class="widget">
          <a href="<?= esc_url(home_url('/')); ?>" class="d-inline-block mb-4">
            <?= get_custom_logo_type($footer_logo_color); ?>
          </a>
          <p class="mb-4 <?= $text_class; ?>">
            © <?= date('Y'); ?> <?= get_bloginfo('name'); ?>. <br class="d-none d-lg-block" />All rights reserved.
          </p>
          <nav class="nav <?= esc_attr($social_class); ?>">
            <?= social_links('', $social_type_footer, $social_size_footer, 'primary', 'solid', $social_button_style_footer); ?>
          </nav>
          <!-- /.social -->
        </div>
        <!-- /.widget -->
        <?php
      });

      // Колонка 2 - Footer 2
      codeweber_footer_column('footer-2', 'col-md-4 col-lg-3', function() use ($text_class) {
        global $opt_name;
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
        ?>
        <div class="widget">
          <div class="h4 widget-title <?= $text_class; ?> mb-3"><?php esc_html_e('Get in Touch', 'codeweber'); ?></div>
          <address class="pe-xl-15 pe-xxl-17 <?= $text_class; ?>">
            <?= esc_html($full_address); ?>
          </address>
          <?php echo do_shortcode('[get_contact field="e-mail" type="link" class="' . $text_class . '"]'); ?><br />
          <?php echo do_shortcode('[get_contact field="phone_01" type="link" class="' . $text_class . '"]'); ?>
        </div>
        <!-- /.widget -->
        <?php
      });

      // Колонка 3 - Footer 3
      codeweber_footer_column('footer-3', 'col-md-4 col-lg-3', function() use ($text_class) {
        ?>
        <div class="widget">
          <div class="h4 widget-title <?= $text_class; ?> mb-3"><?php esc_html_e('Navigation', 'codeweber'); ?></div>
          <?php
          wp_nav_menu(
            array(
              'theme_location'  => 'footer',
              'depth'           => 1,
              'container'       => false,
              'menu_class'      => 'list-unstyled mb-0',
              'fallback_cb'     => false,
              'items_wrap'      => '<ul class="list-unstyled mb-0">%3$s</ul>',
              'walker'          => new WP_Bootstrap_Navwalker(),
            )
          );
          // Если меню не назначено, показываем fallback
          if (!has_nav_menu('footer')) {
            echo '<ul class="list-unstyled mb-0">';
            echo '<li><a href="#" class="' . $text_class . '">About Us</a></li>';
            echo '<li><a href="#" class="' . $text_class . '">Our Story</a></li>';
            echo '<li><a href="#" class="' . $text_class . '">Projects</a></li>';
            echo '<li><a href="#" class="' . $text_class . '">Terms of Use</a></li>';
            echo '<li><a href="#" class="' . $text_class . '">Privacy Policy</a></li>';
            echo '</ul>';
          }
          ?>
        </div>
        <!-- /.widget -->
        <?php
      });

      // Колонка 4 - Footer 4
      codeweber_footer_column('footer-4', 'col-md-12 col-lg-3', function() use ($text_class) {
        ?>
        <div class="widget">
          <div class="h4 widget-title <?= $text_class; ?> mb-3"><?php esc_html_e('Our Newsletter', 'codeweber'); ?></div>
          <p class="mb-5 <?= $text_class; ?>"><?php esc_html_e('Subscribe to our newsletter to get our news & deals delivered to you.', 'codeweber'); ?></p>
          <div class="newsletter-wrapper">
            <?php
            // Выводим default newsletter форму
            if (class_exists('CodeweberFormsDefaultForms')) {
               $is_logged_in = is_user_logged_in();
               $user_id = $is_logged_in ? get_current_user_id() : 0;
               $default_forms = new CodeweberFormsDefaultForms();
               echo $default_forms->get_default_form_html('newsletter', $is_logged_in, $user_id);
            }
            ?>
          </div>
          <!-- /.newsletter-wrapper -->
        </div>
        <!-- /.widget -->
        <?php
      });
      ?>
    </div>
    <!--/.row -->
  </div>
  <!-- /.container -->
</footer>
