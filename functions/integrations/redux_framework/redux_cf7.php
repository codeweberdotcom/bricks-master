<?php

// Регистрируем шорткод для CF7 [getthemebutton default=" ... "]
add_shortcode('getthemebutton', function ($atts) {
   $atts = shortcode_atts([
      'default' => ' rounded-pill',
   ], $atts);

   return getThemeButton($atts['default']);
});