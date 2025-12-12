<?php
/**
 * Template for Vacancies Archive Page
 * 
 * @package Codeweber
 */

get_header(); 
get_pageheader();
?>

<section id="content-wrapper" class="wrapper bg-light">
  <div class="container">
      <?php 
      // Получаем выбранный шаблон из настроек Redux
      $post_type = 'vacancies';
      global $opt_name;
      $templateloop = Redux::get_option($opt_name, 'archive_template_select_' . $post_type);
      // Если шаблон не выбран, используем по умолчанию vacancies_1
      if (empty($templateloop)) {
          $templateloop = 'vacancies_1';
      }
      $template_file = "templates/archives/vacancies/{$templateloop}.php";
      
      // Получаем позицию сайдбара
      $sidebar_position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
      $content_class = ($sidebar_position === 'none') ? 'col-12 py-14' : 'col-xl-9 pt-14';
      
      // Для vacancies используем row-cols структуру
      $use_row_cols = true;
      ?>
      
      <div class="row gx-lg-8 gx-xl-12">
          <?php get_sidebar('left'); ?>
          
          <div class="<?php echo esc_attr($content_class); ?>">
      
      <?php if (have_posts()) : ?>
          <?php if ($use_row_cols) : ?>
              <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-5">
                  <?php while (have_posts()) : 
                    the_post();
                    
                    // Используем выбранный шаблон
                    if (locate_template($template_file)) {
                        get_template_part("templates/archives/vacancies/{$templateloop}");
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
                        
                        // Используем шаблоны из папки templates/archives/vacancies
                        if (!empty($templateloop) && locate_template($template_file)) {
                            get_template_part("templates/archives/vacancies/{$templateloop}");
                        } else {
                            // Fallback: используем шаблон по умолчанию (vacancies_1)
                            if (locate_template("templates/archives/vacancies/vacancies_1.php")) {
                                get_template_part("templates/archives/vacancies/vacancies_1");
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
      <?php else : ?>
          <div class="py-14">
              <p><?php _e('No vacancies found.', 'codeweber'); ?></p>
          </div>
      <?php endif; ?>
          </div>
          <!-- /column -->
          
          <?php get_sidebar('right'); ?>
      </div>
      <!-- /.row -->
  </div>
  <!-- /.container -->
</section>
<!-- /section -->

<?php get_footer(); ?>

