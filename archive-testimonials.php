<?php
/**
 * Template for Testimonials Archive Page
 * 
 * @package Codeweber
 */

get_header(); 
get_pageheader();
?>

<?php if (have_posts()) : ?>
<section id="content-wrapper" class="wrapper bg-light">
  <div class="container">
      <?php 
      // Получаем выбранный шаблон из настроек Redux
      $post_type = 'testimonials';
      $templateloop = Redux::get_option($opt_name, 'archive_template_select_' . $post_type);
      $template_file = "templates/archives/testimonials/{$templateloop}.php";
      
      // Получаем позицию сайдбара
      $sidebar_position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
      $content_class = ($sidebar_position === 'none') ? 'col-12 pt-14 pt-md-16' : 'col-xl-9 pt-14';
      
      // Для testimonials_4 используем row-cols структуру, для остальных - grid/isotope
      $use_row_cols = ($templateloop === 'testimonials_4');
      ?>
      
      <div class="row gx-lg-8 gx-xl-12">
          <?php get_sidebar('left'); ?>
          
          <div class="<?php echo esc_attr($content_class); ?>">
      
      <?php if ($use_row_cols) : ?>
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-5">
              <?php while (have_posts()) : 
                the_post();
                
                // Используем шаблон testimonials_4
                if (locate_template("templates/archives/testimonials/testimonials_4.php")) {
                    get_template_part("templates/archives/testimonials/testimonials_4");
                }
              endwhile; ?>
          </div>
          <!-- /.row -->
      <?php else : ?>
          <div class="grid mb-5">
              <div class="row isotope g-3">
                  <?php 
                  while (have_posts()) : 
                    the_post();
                    
                    // Используем шаблоны из папки templates/archives/testimonials
                    if (!empty($templateloop) && locate_template($template_file)) {
                        get_template_part("templates/archives/testimonials/{$templateloop}");
                    } else {
                    // Fallback: используем шаблон по умолчанию (testimonials_1)
                    if (locate_template("templates/archives/testimonials/testimonials_1.php")) {
                        get_template_part("templates/archives/testimonials/testimonials_1");
                    } else {
                        // Если шаблоны не найдены, используем старый код
                        $testimonial_data = codeweber_get_testimonial_data(get_the_ID());
                        
                        if (!$testimonial_data) {
                            continue;
                        }
                        
                        $testimonial_text = !empty($testimonial_data['text']) ? wp_kses_post($testimonial_data['text']) : '';
                        $author_name = !empty($testimonial_data['author_name']) ? esc_html($testimonial_data['author_name']) : '';
                        $author_role = !empty($testimonial_data['author_role']) ? esc_html($testimonial_data['author_role']) : '';
                        
                        $avatar_url = '';
                        $avatar_url_2x = '';
                        $avatar_id = get_post_meta(get_the_ID(), '_testimonial_avatar', true);
                        
                        if ($avatar_id) {
                            $avatar_src = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                            if ($avatar_src) {
                                $avatar_url = esc_url($avatar_src[0]);
                            }
                            $avatar_2x_src = wp_get_attachment_image_src($avatar_id, 'medium');
                            if ($avatar_2x_src && $avatar_2x_src[0] !== $avatar_url) {
                                $avatar_url_2x = esc_url($avatar_2x_src[0]);
                            }
                        } elseif (!empty($testimonial_data['author_avatar'])) {
                            $avatar_url = esc_url($testimonial_data['author_avatar']);
                        }
                        
                        $rating = !empty($testimonial_data['rating']) ? intval($testimonial_data['rating']) : 0;
                        $rating_class = '';
                        if ($rating > 0 && $rating <= 5) {
                            $rating_names = ['', 'one', 'two', 'three', 'four', 'five'];
                            $rating_class = $rating_names[$rating];
                        } else {
                            $rating_class = 'five';
                        }
                        ?>
                        <?php $card_radius = getThemeCardImageRadius(); ?>
                        <div class="item col-md-6 col-xl-4">
                            <div class="card h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                                <div class="card-body">
                                    <?php if ($rating > 0) : ?>
                                        <span class="ratings <?php echo esc_attr($rating_class); ?> mb-3"></span>
                                    <?php endif; ?>
                                    
                                    <blockquote class="icon mb-0">
                                        <?php if ($testimonial_text) : ?>
                                            <p><?php echo $testimonial_text; ?></p>
                                        <?php endif; ?>
                                        
                                        <?php codeweber_testimonial_blockquote_details(get_the_ID()); ?>
                                    </blockquote>
                                </div>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                        <!--/column -->
                        <?php
                    }
                }
              endwhile; ?>
              </div>
              <!-- /.row -->
          </div>
          <!-- /.grid -->
      <?php endif; ?>
      
      <?php 
      // Pagination
      codeweber_posts_pagination();
      ?>
          </div>
          <!-- /column -->
          
          <?php get_sidebar('right'); ?>
      </div>
      <!-- /.row -->
  </div>
  <!-- /.container -->
</section>
<!-- /section -->
<?php endif; ?>

<section class="wrapper">
  <div class="container pb-14 pb-md-16">
    <div class="row">
      <div class="col-12">
        <?php $card_radius = getThemeCardImageRadius(); ?>
        <div class="card<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
          <div class="card-body p-6 p-md-11 d-lg-flex flex-row align-items-lg-center justify-content-md-between text-center text-lg-start">
            <h3 class="display-6 mb-6 mb-lg-0 pe-lg-10 pe-xl-5 pe-xxl-18"><?php esc_html_e('We would love to hear about your experience with us. Your feedback helps us improve and helps others make better decisions.', 'codeweber'); ?></h3>
            <a href="javascript:void(0)" id="submit-testimonial-btn" class="btn btn-primary<?php echo getThemeButton(); ?> mb-0 text-nowrap" data-bs-toggle="modal" data-bs-target="#modal" data-value="add-testimonial"><?php esc_html_e('Leave a Review', 'codeweber'); ?></a>
          </div>
          <!--/.card-body -->
        </div>
        <!--/.card -->
      </div>
      <!-- /column -->
    </div>
    <!-- /.row -->
  </div>
  <!-- /.container -->
</section>
<!-- /section -->


<?php get_footer(); ?>

