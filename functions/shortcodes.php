<?php

/**
 * Шорткод [site_domain] выводит домен сайта (без http/https и www).
 *
 * Пример: example.com
 *
 * @return string
 */
add_shortcode('site_domain', function () {
   $host = parse_url(home_url(), PHP_URL_HOST);
   $host = preg_replace('/^www\./', '', $host); // убираем www
   return esc_html($host);
});


/**
 * Шорткод [site_domain_link]
 * Выводит ссылку на главную страницу сайта в виде <a href="...">...</a>
 *
 * Пример:
 * <a href="https://example.com">https://example.com</a>
 *
 * @return string
 */
add_shortcode('site_domain_link', function () {
   $url = home_url();
   return '<a href="' . esc_url($url) . '">' . esc_html($url) . '</a>';
});
