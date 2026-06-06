<?php

/**
 * Footer model 4 — clean footer with logo + description + socials,
 * a compact weekly opening-hours column and a contacts column.
 *
 * Data sources (all theme helpers / shortcodes):
 *  - logo        : get_custom_logo_type()
 *  - description : [redux_option key="text-about-company"]
 *  - socials     : codeweber_social_links()
 *  - address     : codeweber_get_address()
 *  - phone/email : [get_contact]
 *  - hours       : \Codeweber\Blocks\OpeningHours (plugin shared helper)
 */

defined('ABSPATH') || exit;

$footer_color_text = $footer_vars['footer_color_text'] ?? false;
$footer_background = $footer_vars['footer_background'] ?? false;

if ($footer_background === 'solid') {
   $footer_background_color = $footer_vars['footer_solid_color'] ?? false;
} elseif ($footer_background === 'soft') {
   $footer_background_color = $footer_vars['footer_soft_color'] ?? false;
} else {
   $footer_background_color = 'dark';
}

// Классы для текста
$text_class_array = [];
if ($footer_color_text === 'dark') {
   $text_class_array[] = 'text-white';
   $text_inverse = 'text-inverse';
} else {
   $text_class_array[] = 'text-reset';
   $text_inverse = '';
}
if (!empty($GLOBALS['codeweber_footer_use_text_inverse'])) {
   $text_inverse = 'text-inverse';
}
$text_class = implode(' ', $text_class_array);

// Настройки соцсетей для футера
global $opt_name;
$social_icon_type_footer    = Redux::get_option($opt_name, 'social-icon-type-footer', '1');
$social_type_footer         = 'type' . $social_icon_type_footer;
$social_size_footer         = Redux::get_option($opt_name, 'social-button-size-footer', 'md');
$social_button_style_footer = Redux::get_option($opt_name, 'social-button-style-footer', 'circle');

// Цвет логотипа для футера
$footer_logo_color = Redux::get_option($opt_name, 'footer-logo-color', 'light');

// Класс для соцсетей (social-white для темного фона) и ссылок
$social_class = ($footer_background_color === 'dark' || $footer_color_text === 'dark') ? 'social-white' : 'social-muted';
$link_class   = ($footer_color_text === 'dark') ? 'text-white' : 'link-body';

?>

<footer class="bg-<?= esc_attr($footer_background_color); ?> <?= esc_attr($text_inverse); ?>">
  <div class="container pb-7 pt-13 pt-md-15">
    <div class="row gx-lg-0 gy-6">
      <?php
      // Колонка 1 — логотип + описание + соцсети
      codeweber_footer_column('footer-1', 'col-lg-4', function () use ($footer_logo_color, $text_class, $social_class, $social_type_footer, $social_size_footer, $social_button_style_footer) {
        ?>
        <div class="widget">
          <a href="<?= esc_url(home_url('/')); ?>" class="d-inline-block mb-4">
            <?= get_custom_logo_type($footer_logo_color); ?>
          </a>
          <p class="lead mb-5 <?= $text_class; ?>">
            <?= do_shortcode('[redux_option key="text-about-company"]'); ?>
          </p>
          <nav class="nav <?= esc_attr($social_class); ?>">
            <?= codeweber_social_links('', $social_type_footer, $social_size_footer, 'primary', 'solid', $social_button_style_footer); ?>
          </nav>
          <!-- /.social -->
        </div>
        <!-- /.widget -->
        <?php
      });

      // Колонка 2 — часы работы (вся неделя кратко) из общего хелпера плагина
      codeweber_footer_column('footer-2', 'col-lg-3 offset-lg-2', function () use ($text_class) {
        $oh = '\Codeweber\Blocks\OpeningHours';
        // Колонка выводится только если плагин активен и часы заполнены
        if (!class_exists($oh) || !$oh::hasData()) {
          return;
        }

        $display = $oh::buildDisplay(null, [
          'dayFormat'     => 'short',
          'breakMode'     => 'both',
          'groupSameDays' => true,
          'separator'     => 'ndash',
        ]);
        ?>
        <div class="widget">
          <div class="d-flex flex-row">
            <div>
              <div class="icon text-primary fs-28 me-4 mt-n1"><i class="uil uil-clock"></i></div>
            </div>
            <div>
              <div class="h5 mb-2 <?= $text_class; ?>"><?php esc_html_e('Working hours', 'codeweber'); ?></div>
              <ul class="list-unstyled mb-0 <?= $text_class; ?>">
                <?php foreach ($display as $row) : ?>
                  <li class="<?= !empty($row['is_today']) ? 'fw-bold' : ''; ?>">
                    <span class="me-2"><?= esc_html($row['label']); ?>:</span>
                    <?php if (!empty($row['closed'])) : ?>
                      <span><?= esc_html($row['lines'][0]); ?></span>
                    <?php else : ?>
                      <span><?= esc_html(implode(', ', $row['lines'])); ?></span>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
          <!--/div -->
        </div>
        <!-- /.widget -->
        <?php
      });

      // Колонка 3 — контакты: адрес, телефон, e-mail
      codeweber_footer_column('footer-3', 'col-lg-3', function () use ($text_class, $link_class) {
        $full_address = function_exists('codeweber_get_address')
          ? codeweber_get_address('fact', ', ')
          : 'Moonshine St. 14/05 Light City, London, United Kingdom';
        ?>
        <div class="widget">
          <div class="d-flex flex-row">
            <div>
              <div class="icon text-primary fs-28 me-4 mt-n1"><i class="uil uil-location-pin-alt"></i></div>
            </div>
            <div class="align-self-start justify-content-start">
              <div class="h5 mb-1 <?= $text_class; ?>"><?php esc_html_e('Contacts', 'codeweber'); ?></div>
              <address class="<?= $text_class; ?> mb-2"><?= esc_html($full_address); ?></address>
              <p class="mb-0 <?= $text_class; ?>">
                <?= do_shortcode('[get_contact field="phone_01" type="link" class="' . esc_attr($link_class) . '"]'); ?><br />
                <?= do_shortcode('[get_contact field="e-mail" type="link" class="' . esc_attr($link_class) . '"]'); ?>
              </p>
            </div>
          </div>
          <!--/div -->
        </div>
        <!-- /.widget -->
        <?php
      });
      ?>
    </div>
    <!--/.row -->
    <hr class="mt-13 mt-md-14 mb-7" />
    <div class="d-md-flex align-items-center justify-content-between">
      <p class="mb-2 mb-lg-0 <?= $text_class; ?>">
        © <?= esc_html(date('Y')); ?> <?= esc_html(get_bloginfo('name')); ?>. <?php esc_html_e('All rights reserved.', 'codeweber'); ?>
      </p>
      <?php if (is_active_sidebar('bottom-footer')) : ?>
        <div class="bottom-footer-widgets text-md-end <?= $text_class; ?>">
          <?php dynamic_sidebar('bottom-footer'); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <!-- /.container -->
</footer>
