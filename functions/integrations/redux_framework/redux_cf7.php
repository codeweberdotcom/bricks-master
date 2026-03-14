<?php

// Регистрируем шорткод для CF7 [getthemebutton default=" ... "]
add_shortcode('getthemebutton', function ($atts) {
   $atts = shortcode_atts([
      'default' => ' rounded-pill',
   ], $atts);

   return Codeweber_Options::style('button', $atts['default']);
});

// Регистрируем шорткод для CF7 [getthemeform default=" ... "]
add_shortcode('getthemeform', function ($atts) {
   $atts = shortcode_atts([
      'default' => ' rounded',
   ], $atts);

   return Codeweber_Options::style('form-radius', $atts['default']);
});