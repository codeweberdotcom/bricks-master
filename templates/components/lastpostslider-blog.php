<?php

/**
 * Blog Lats Posts - Slider
 */
?>

<?php
$my_posts = new WP_Query;
$myposts = $my_posts->query(array(
   'post_type' => 'post'
)); ?>
<h3 class="mb-6"><?php esc_html_e('You Might Also Like', 'codeweber'); ?></h3>
<div class="swiper-container blog grid-view mb-16" data-margin="30" data-nav="false" data-dots="true" data-items-md="2" data-items-xs="1">
   <div class="swiper">
      <div class="swiper-wrapper">
         <?php
         // обрабатываем результат
         foreach ($myposts as $post_single) {
            setup_postdata($post_single);
         ?>
            <div class="swiper-slide">
               <article>
                  <figure class="overlay overlay-1 hover-scale rounded mb-5">
                     <a href="<?php the_permalink($post_single->ID); ?>">
                        <img src="<?php echo get_the_post_thumbnail_url($post_single->ID, 'codeweber_single'); ?>" alt=""><span class="bg"></span></a>
                     <figcaption>
                        <div class="from-top mb-0 h5"><?php esc_html_e('Read More', 'codeweber'); ?></div>
                     </figcaption>
                  </figure>
                  <div class="post-header">
                     <div class="post-category text-line">
                        <?php the_category(', '); ?>
                     </div>
                     <!-- /.post-category -->
                     <h2 class="post-title h3 mt-1 mb-3"><a class="link-dark" href="<?php the_permalink($post_single->ID); ?>"><?php echo esc_html($post_single->post_title); ?></a></h2>
                  </div>
                  <!-- /.post-header -->
                  <div class="post-footer">
                     <ul class="post-meta mb-0">
                        <li class="post-date"><i class="uil uil-calendar-alt"></i><span><?php the_time(get_option('date_format')); ?></span></li>
                        <li class="post-comments"><a href="<?php echo get_post_permalink($post_single->ID); ?>/#comments"><i class="uil uil-comment"></i><?php echo $post_single->comment_count; ?></a></li>
                     </ul>
                     <!-- /.post-meta -->
                  </div>
                  <!-- /.post-footer -->
               </article>
               <!-- /article -->
            </div>
            <!--/.swiper-slide -->
         <?php } ?>
         <?php wp_reset_postdata(); ?>
      </div>
      <!--/.swiper-wrapper -->
   </div>
   <!-- /.swiper -->
</div>