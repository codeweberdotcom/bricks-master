<?php

add_action('tgmpa_register', 'my_theme_register_required_plugins');

function my_theme_register_required_plugins()
{
   $plugins = array(
      array(
         'name'     => 'Contact Form 7',
         'slug'     => 'contact-form-7',
         'required' => false,
      ),
      array(
         'name'     => 'Rank Math SEO',
         'slug'     => 'seo-by-rank-math',
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
      array(
         'name'      => 'Matomo Analytics',
         'slug'      => 'matomo',
         'required'  => false, // Сделать обязательным
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

// Show TGMPA notice only on the Plugins admin page.
add_action( 'current_screen', function ( WP_Screen $screen ) {
   if ( 'plugins' !== $screen->id && class_exists( 'TGM_Plugin_Activation' ) ) {
      remove_action( 'admin_notices', [ TGM_Plugin_Activation::$instance, 'notices' ] );
   }
} );