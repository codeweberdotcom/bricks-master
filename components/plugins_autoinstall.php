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