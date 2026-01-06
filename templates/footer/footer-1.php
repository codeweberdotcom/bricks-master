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
$social_size_footer = 'md'; // Можно сделать настраиваемым

// Получаем настройку цвета логотипа для футера
$footer_logo_color = Redux::get_option($opt_name, 'footer-logo-color', 'light');

// Определяем класс для соцсетей (social-white для темного фона)
$social_class = ($footer_background_color === 'dark' || $footer_color_text === 'dark') ? 'social-white' : '';

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
          <?= social_links($social_class, $social_type_footer, $social_size_footer); ?>
          <!-- /.social -->
        </div>
        <!-- /.widget -->
      </div>
      <!-- /column -->
      <div class="col-md-4 col-lg-3">
        <div class="widget">
          <h4 class="widget-title <?= $text_class; ?> mb-3">Get in Touch</h4>
          <address class="pe-xl-15 pe-xxl-17 <?= $text_class; ?>">
            <?php
            global $opt_name;
            $address_data = Redux::get_option($opt_name, 'fact-company-adress', array());
            $parts = [];
            if (!empty($address_data['box1'])) $parts[] = $address_data['box1'];
            if (!empty($address_data['box2'])) $parts[] = $address_data['box2'];
            if (!empty($address_data['box3']) || !empty($address_data['box4'])) {
              $city_street = trim("{$address_data['box3']} {$address_data['box4']}");
              $parts[] = $city_street;
            }
            if (!empty($address_data['box5']) || !empty($address_data['box6'])) {
              $house = !empty($address_data['box5']) ? "д. {$address_data['box5']}" : '';
              $office = !empty($address_data['box6']) ? "оф. {$address_data['box6']}" : '';
              $house_office = trim("{$house}, {$office}", ', ');
              $parts[] = $house_office;
            }
            if (!empty($address_data['box7'])) $parts[] = $address_data['box7'];
            $full_address = !empty($parts) ? implode('<br> ', $parts) : 'Moonshine St. 14/05 Light City, London, United Kingdom';
            echo $full_address;
            ?>
          </address>
          <?php echo do_shortcode('[get_contact field="e-mail" type="link" class="link-body"]'); ?><br />
          <?php echo do_shortcode('[get_contact field="phone_01" type="link" class="link-body"]'); ?>
        </div>
        <!-- /.widget -->
      </div>
      <!-- /column -->
      <div class="col-md-4 col-lg-3">
        <div class="widget">
          <h4 class="widget-title <?= $text_class; ?> mb-3">Learn More</h4>
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
          <h4 class="widget-title <?= $text_class; ?> mb-3">Our Newsletter</h4>
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
