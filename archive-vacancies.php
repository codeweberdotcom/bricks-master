<?php
/**
 * Template for Vacancies Archive Page
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
      $post_type = 'vacancies';
      global $opt_name;
      $templateloop = Redux::get_option($opt_name, 'archive_template_select_' . $post_type);
      // Если шаблон не выбран или равен 'default', используем по умолчанию vacancies_1
      if (empty($templateloop) || $templateloop === 'default') {
          $templateloop = 'vacancies_1';
      }
      $template_file = "templates/archives/vacancies/{$templateloop}.php";
      
      // Получаем позицию сайдбара
      $sidebar_position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
      $content_class = ($sidebar_position === 'none') ? 'col-12 py-14' : 'col-xl-9 pt-14';
      
      // Для vacancies_1 шаблон содержит всю разметку с фильтрами
      $is_vacancies_1 = ($templateloop === 'vacancies_1');
      ?>
      
      <?php if ($is_vacancies_1) : ?>
          <?php
          // Для vacancies_1 шаблон содержит всю разметку с фильтрами
          if (locate_template($template_file)) {
              get_template_part("templates/archives/vacancies/{$templateloop}");
          }
          ?>
      <?php else : ?>
          <div class="row">
              <?php get_sidebar('left'); ?>
              
              <div class="<?php echo esc_attr($content_class); ?>">
          
          <?php if ($templateloop === 'vacancies_2') : ?>
              <div class="row gy-6 mb-5">
                  <?php while (have_posts()) : 
                    the_post();
                    
                    // Используем выбранный шаблон
                    if (locate_template($template_file)) {
                        get_template_part("templates/archives/vacancies/{$templateloop}");
                    }
                  endwhile; ?>
              </div>
              <!-- /.row -->
          <?php elseif ($templateloop === 'vacancies_3') : ?>
              <div class="row g-3 mb-5">
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
              </div>
              <!-- /column -->
              
              <?php get_sidebar('right'); ?>
          </div>
          <!-- /.row -->
      <?php endif; ?>
  </div>
  <!-- /.container -->
</section>
<!-- /section -->
<?php else : ?>
<section class="wrapper bg-light">
  <div class="container py-14">
      <div class="row">
          <div class="col-12 text-center">
              <p><?php _e('No vacancies found.', 'codeweber'); ?></p>
          </div>
      </div>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
