<?php

/**
 * Blog Lats Posts - Slider
 */
?>

<?php
$my_posts = new WP_Query;
$myposts = $my_posts->query(array(
   'post_type' => 'legal'
)); ?>
<h3 class="mb-6"><?php esc_html_e('Other documents', 'codeweber'); ?></h3>
<div class="swiper-container blog grid-view mb-6" data-margin="15" data-nav="false" data-dots="true" data-items-md="2" data-items-xs="1">
   <div class="swiper">
      <div class="swiper-wrapper">
         <?php
         // обрабатываем результат
         foreach ($myposts as $post_single) {
            setup_postdata($post_single);
         ?>
            <div class="swiper-slide m-1 card">
               <article class="card-body p-6">
                  
                     <div class="post-header">
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
            </div>
            <!--/.swiper-slide -->
         <?php } ?>
         <?php wp_reset_postdata(); ?>
      </div>
      <!--/.swiper-wrapper -->
   </div>
   <!-- /.swiper -->
</div>