<?php
function codeweber_create_legal_pages()
{
   $pages = [
      [
         'title' => 'Политика конфиденциальности',
         'slug'  => 'privacy-policy',
         'content' => '',
      ],
      [
         'title' => 'Пользовательское соглашение',
         'slug'  => 'terms-of-use',
         'content' => '',
      ],
      [
         'title' => 'Согласие на обработку ПД',
         'slug'  => 'data-processing-consent',
         'content' => '',
      ],
      [
         'title' => 'Договор оферты',
         'slug'  => 'offer-agreement',
         'content' => '',
      ],
      [
         'title' => 'Cookie Policy',
         'slug'  => 'cookie-policy',
         'content' => '',
      ],
      [
         'title' => 'Информация об организации',
         'slug'  => 'about-company',
         'content' => '',
      ],
   ];

   foreach ($pages as $page) {
      $existing_page = get_page_by_path($page['slug']);
      if ($existing_page) {
         error_log("Page '{$page['title']}' already exists (ID: {$existing_page->ID})");
      } else {
         $new_page_id = wp_insert_post([
            'post_title'     => $page['title'],
            'post_name'      => $page['slug'],
            'post_content'   => $page['content'],
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => 1,
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
         ]);
         if (is_wp_error($new_page_id)) {
            error_log("Failed to create page '{$page['title']}': " . $new_page_id->get_error_message());
         } else {
            error_log("Page '{$page['title']}' created successfully (ID: $new_page_id)");
         }
      }
   }
}
add_action('after_switch_theme', 'codeweber_create_legal_pages');
