<?php
/**
 * Template: Single Legal Default
 * 
 * Шаблон для отображения страницы Legal (Fallback)
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<section id="post-<?php the_ID(); ?>" <?php post_class('blog single'); ?>>
   <?php $card_radius = getThemeCardImageRadius(); ?>
   <div class="card<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
      <figure class="card-img-top<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
         <?php
         // Получаем ID миниатюры текущего поста
         $thumbnail_id = get_post_thumbnail_id();

         // Получаем URL изображения размера 'large'
         $large_image_url = wp_get_attachment_image_src($thumbnail_id, 'codeweber_extralarge');

         if ($large_image_url) :
            $img_classes = 'img-fluid';
            if ($card_radius) {
               $img_classes .= ' ' . esc_attr($card_radius);
            }
         ?>
            <a href="<?php echo esc_url($large_image_url[0]); ?>" data-glightbox data-gallery="g1">
               <?php the_post_thumbnail('codeweber_extralarge', array('class' => $img_classes)); ?>
            </a>
         <?php endif; ?>
      </figure>
      <!-- /.figure -->
      <div class="card-body">
         <div class="classic-view">
            <article class="post">
               <div class="post-content mb-5">
                  <?php the_content(); ?>
               </div>
               <!-- /.post-content -->
               <?php codeweber_single_post_footer(); ?>
               <!-- /.post-footer -->
            </article>
            <!-- /.post -->
         </div>
         <!-- /.classic-view -->
         <hr class="mt-5 mb-5">
         <?php codeweber_single_link_pages(); ?>
         <!-- /.link-pages -->
         <?php codeweber_single_post_author(); ?>
         <!-- /.author-info -->
         <?php echo codeweber_single_social_links(); ?>
         <!-- /.social -->
         <?php codeweber_single_related('legal'); ?>
         <!-- /.related -->
         <?php codeweber_single_comments(); ?>
         <!-- /.comments -->
      </div>
      <!-- /.card-body -->
   </div>
   <!-- /.card -->
</section> <!-- #post-<?php the_ID(); ?> -->
