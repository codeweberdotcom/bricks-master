<?php

add_action('tgmpa_register', 'my_theme_register_required_plugins');

function my_theme_register_required_plugins()
{
   $plugins = array(
      array(
         'name'     => 'Contact Form 7',
         'slug'     => 'contact-form-7',
         'required' => true,
      ),
      array(
         'name'     => 'Rank Math SEO',
         'slug'     => 'seo-by-rank-math',
         'required' => true,
      ),
      array(
         'name'     => 'WP Mail SMTP',
         'slug'     => 'wp-mail-smtp',
         'required' => false,
      ),
      array(
         'name'     => 'Loco Translate',
         'slug'     => 'loco-translate',
         'required' => false,
      ),
      array(
         'name'     => 'Yoast Duplicate Post',
         'slug'     => 'duplicate-post',
         'required' => false,
      ),
   );

   $config = array(
      'id'           => 'my_theme',
      'menu'         => 'tgmpa-install-plugins',
      'has_notices'  => true,
      'dismissable'  => true,
      'is_automatic' => true,
   );

   tgmpa($plugins, $config);
}


add_action('tgmpa_register', 'run_after_cf7_activation', 10, 0);

function run_after_cf7_activation()
{
   if (is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
      // Проверяем, существуют ли функции, и вызываем их только если они определены
      if (function_exists('create_custom_cf7_form')) {
         create_custom_cf7_form();
      }

      if (function_exists('create_custom_cf7_form_with_name_and_email')) {
         create_custom_cf7_form_with_name_and_email();
      }

      if (function_exists('create_custom_cf7_form_with_name_comment_and_email')) {
         create_custom_cf7_form_with_name_comment_and_email();
      }
   }
}