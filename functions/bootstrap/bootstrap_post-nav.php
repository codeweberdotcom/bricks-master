<?php

//** Pagination with buttons */
function codeweber_posts_nav()
{
   $next_post = get_next_post();
   $prev_post = get_previous_post();

   if ($next_post || $prev_post) : ?>
      <!--/column -->
      <div class="col-md-8 align-self-center text-center text-md-start navigation">
         <?php if (!empty($prev_post)) : ?>
            <a href="<?php echo get_permalink($prev_post); ?>" class="btn btn-soft-ash <?php echo GetThemeButton(); ?> btn-icon btn-icon-start mb-0 me-1"><i class="uil uil-arrow-left"></i> <?php _e('Prev', 'codeweber') ?></a>
         <?php endif; ?>

         <?php if (!empty($next_post)) : ?>
            <a href="<?php echo get_permalink($next_post); ?>" class="btn btn-soft-ash <?php echo GetThemeButton(); ?> btn-icon btn-icon-end mb-0"><?php _e('Next', 'codeweber') ?> <i class="uil uil-arrow-right"></i></a>
         <?php endif; ?>
      </div>
      <!--/column -->
<?php endif;
}
