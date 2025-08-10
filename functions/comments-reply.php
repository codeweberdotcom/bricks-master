<?php

/**
 * Checks if the specified comment is written by the author of the post commented on.
 */
function codeweber_is_comment_by_post_author($comment = null)
{
   if (is_object($comment) && $comment->user_id > 0) {
      $user = get_userdata($comment->user_id);
      $post = get_post($comment->comment_post_ID);
      if (!empty($user) && !empty($post)) {
         return $comment->user_id === $post->post_author;
      }
   }
   return false;
}


add_filter('cancel_comment_reply_link', function ($link) {
   // Добавим класс btn btn-outline-secondary
   $link = str_replace(
      '<a ',
      '<a class="ms-2" ',
      $link
   );
   return $link;
});