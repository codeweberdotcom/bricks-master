<?php $card_radius = Codeweber_Options::style('card-radius'); ?>
<a href="<?php the_permalink(); ?>" id="<?php echo $post->post_name; ?>" <?php post_class('post mb-3'); ?>>
   <div class="card lift<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
      <div class="card-body">
         <div class="post-header">
            <?php
            $post_id   = get_the_ID();
            $alt_title = get_post_meta( $post_id, '_alt_title', true );
            ?>
            <h2 class="post-title mt-1 mb-0"><?php echo $alt_title ? wp_kses_post( $alt_title ) : esc_html( get_the_title() ); ?></h2>
         </div>
         <!-- /.post-header -->
         <div class="post-content text-dark">
            <p><?php the_excerpt(); ?></p>
         </div>
         <!-- /.post-content -->
      </div>
   </div>
</a> <!-- #post-<?php the_ID(); ?> -->
