<?php

/**
 * Custom comment walker for this theme.
 */

if (!class_exists('codeweber_Walker_Comment')) {
   class codeweber_Walker_Comment extends Walker_Comment
   {
      protected function html5_comment($comment, $depth, $args)
      {
?>
         <li id="comment-<?php comment_ID(); ?>" <?php comment_class($this->has_children ? 'parent' : '', $comment); ?>>
            <div id="div-comment-<?php comment_ID(); ?>" class="comment-header d-md-flex align-items-center">
               <div class="d-flex align-items-center">
                  <?php
                  $comment_author_url = get_comment_author_url($comment);
                  $comment_author     = get_comment_author($comment);

                  // Получаем URL стандартного аватара
                  $avatar_url = get_avatar_url($comment, ['size' => 96]); // оригинальный размер

                  // Загружаем аватар в кастомном размере (если пользователь загружал через Media Library)
                  $user_id = $comment->user_id;
                  $avatar_img = get_avatar($comment, 96, '', $comment_author, ['class' => 'rounded-circle', 'size' => 'codeweber_square']);

                  echo '<figure class="user-avatar">' . $avatar_img . '</figure>';

                  printf(
                     '<div><div class="comment-author h6">%1$s</div><span class="screen-reader-text says">%2$s</span>',
                     esc_html($comment_author),
                     __('says:', 'codeweber')
                  );
                  ?>
                  <ol id="singlecomments" class="post-meta commentlist">
                     <li class="comment"><i class="uil uil-calendar-alt"></i>
                        <?php
                        $comment_timestamp = sprintf(__('%1$s ', 'codeweber'), get_comment_date('', $comment), get_comment_time('H:i'));
                        printf(
                           '<time datetime="%s" title="%s">%s</time>',
                           esc_url(get_comment_link($comment, $args)),
                           get_comment_time('c'),
                           esc_html($comment_timestamp)
                        );
                        if (get_edit_comment_link()) {
                           printf(
                              ' <span aria-hidden="true">&bull;</span> <a class="comment-edit-link" href="%s">%s</a>',
                              esc_url(get_edit_comment_link()),
                              __('Edit', 'codeweber')
                           );
                        }
                        ?>
                     </li>
                  </ol>
               </div>
            </div>

            <?php
            $comment_reply_link = get_comment_reply_link(
               array_merge(
                  $args,
                  array(
                     'add_below' => 'div-comment',
                     'depth'     => $depth,
                     'max_depth' => $args['max_depth'],
                     'before'    => '<div class="mt-3 mt-md-0 ms-auto comment-reply">', // сюда ничего не добавляем
                     'after'     => '</div>',
                  )
               )
            );

            if ($comment_reply_link) {
               // Только к <a class="..."> применяем нужные классы
               $custom_classes = 'do-not-scroll btn btn-soft-ash btn-sm ' . GetThemeButton() . ' btn-icon btn-icon-start mb-0';

               $comment_reply_link = preg_replace_callback(
                  '/<a\s+([^>]*class=")([^"]*)(")/i',
                  function ($matches) use ($custom_classes) {
                     return '<a ' . $matches[1] . $matches[2] . ' ' . esc_attr($custom_classes) . $matches[3];
                  },
                  $comment_reply_link
               );
               echo $comment_reply_link;
            }
            ?>

            </div>
            <?php comment_text(); ?>
            <?php if ('0' === $comment->comment_approved) : ?>
               <div class="alert alert-danger alert-icon" role="alert">
                  <i class="uil uil-star"></i> <?php _e('Your comment is awaiting moderation.', 'codeweber'); ?>
               </div>
            <?php endif; ?>
         </li>
<?php
      }
   }
}
?>