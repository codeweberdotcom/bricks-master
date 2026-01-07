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

// Определяем класс для ссылок (link-body или text-white)
$link_class = ($footer_color_text === 'dark') ? 'text-white' : 'link-body';

?>

<footer class="bg-<?= $footer_background_color; ?> <?= $text_inverse; ?>">
  <div class="container py-13 py-md-15">
    <div class="row gy-6 gy-lg-0">
      <div class="col-md-4 col-lg-3">
        <div class="widget">
          <a href="<?= esc_url(home_url('/')); ?>" class="d-inline-block mb-4">
            <?= get_custom_logo_type($footer_logo_color); ?>
          </a>
          <p class="mb-4 <?= $text_class; ?>">
            <?= do_shortcode('[redux_option key="text-about-company"]'); ?>
          </p>
          <?= social_links($social_class, $social_type_footer, $social_size_footer, 'primary', 'solid', $social_button_style_footer); ?>
          <!-- /.social -->
        </div>
        <!-- /.widget -->
      </div>
      <!-- /column -->
      <div class="col-md-4 col-lg-3">
        <div class="widget">
          <div class="h4 widget-title <?= $text_class; ?> mb-3"><?php esc_html_e('Get in Touch', 'codeweber'); ?></div>
          <address class="pe-xl-15 pe-xxl-17 <?= $text_class; ?>">
            <?php
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
            echo $full_address;
            ?>
          </address>
          <?php echo do_shortcode('[get_contact field="e-mail" type="link" class="' . esc_attr($link_class) . '"]'); ?><br />
          <?php echo do_shortcode('[get_contact field="phone_01" type="link" class="' . esc_attr($link_class) . '"]'); ?>
        </div>
        <!-- /.widget -->
      </div>
      <!-- /column -->
      <div class="col-md-4 col-lg-3">
        <div class="widget">
          <div class="h4 widget-title <?= $text_class; ?> mb-3"><?php esc_html_e('Learn More', 'codeweber'); ?></div>
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
      </div>
      <!-- /column -->
      <div class="col-md-12 col-lg-3">
        <div class="widget">
          <div class="h4 widget-title <?= $text_class; ?> mb-3"><?php esc_html_e('Our Newsletter', 'codeweber'); ?></div>
          <p class="mb-5 <?= $text_class; ?>">Subscribe to our newsletter to get our news & deals delivered to you.</p>
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
      </div>
      <!-- /column -->
    </div>
    <!--/.row -->
    <?php get_template_part('templates/footer/footer', 'copyright'); ?>
  </div>
  <!-- /.container -->
</footer>
