<?php
/**
 * Template for Staff Archive Page
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
      $post_type = 'staff';
      global $opt_name;
      $templateloop = Redux::get_option($opt_name, 'archive_template_select_' . $post_type);
      // Если шаблон не выбран, используем по умолчанию staff_1
      if (empty($templateloop)) {
          $templateloop = 'staff_1';
      }
      $template_file = "templates/archives/staff/{$templateloop}.php";
      
      // Получаем позицию сайдбара
      $sidebar_position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
      $content_class = ($sidebar_position === 'none') ? 'col-12 py-14' : 'col-xl-9 pt-14';
      
      // Для staff_3, staff_4 и staff_5 используем row-cols структуру, для остальных - grid/isotope
      $use_row_cols = ($templateloop === 'staff_3' || $templateloop === 'staff_4' || $templateloop === 'staff_5');
      ?>
      
      <div class="row">
          <?php get_sidebar('left'); ?>
          
          <div class="<?php echo esc_attr($content_class); ?>">
      
      <?php if ($use_row_cols) : ?>
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-5">
              <?php while (have_posts()) : 
                the_post();
                
                // Используем выбранный шаблон (staff_3 или staff_4)
                if (locate_template($template_file)) {
                    get_template_part("templates/archives/staff/{$templateloop}");
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
                    
                    // Используем шаблоны из папки templates/archives/staff
                    if (!empty($templateloop) && locate_template($template_file)) {
                        get_template_part("templates/archives/staff/{$templateloop}");
                    } else {
                        // Fallback: используем шаблон по умолчанию (staff_1)
                        if (locate_template("templates/archives/staff/staff_1.php")) {
                            get_template_part("templates/archives/staff/staff_1");
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

<?php get_footer(); ?>

