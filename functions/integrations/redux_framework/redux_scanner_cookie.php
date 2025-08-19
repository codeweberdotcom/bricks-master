<?php

/**
 * Добавляет скрипт на фронтенд (только на главную страницу),
 * который после полной загрузки страницы отправляет cookies текущего пользователя
 * в окно-родитель (если оно открыто) через postMessage.
 * 
 * Используется для получения cookies с фронтенда в админке через окно popup.
 */
add_action('wp_footer', function () {
   if (is_front_page()) : ?>
      <script>
         window.addEventListener('load', function() {
            if (window.opener) {
               window.opener.postMessage({
                  type: "frontend_cookies",
                  cookies: document.cookie
               }, "*");
            }
         });
      </script>
<?php
   endif;
});
