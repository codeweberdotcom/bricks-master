<?php $card_radius = getThemeCardImageRadius(); ?>
<a href="<?php the_permalink(); ?>" id="<?php echo $post->post_name; ?>" <?php post_class('post mb-3'); ?>>
   <div class="card lift<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
      <div class="card-body">
         <div class="post-header">
            <h2 class="post-title mt-1 mb-0"><?php the_title(); ?></h2>
         </div>
         <!-- /.post-header -->
         <div class="post-content text-dark">
            <p><?php the_excerpt(); ?></p>
         </div>
         <!-- /.post-content -->
      </div>
   </div>
</a> <!-- #post-<?php the_ID(); ?> -->