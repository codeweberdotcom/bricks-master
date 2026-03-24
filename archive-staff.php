<?php
/**
 * Template for Staff Archive Page
 * 
 * @package Codeweber
 */

get_header(); 
get_pageheader();
?>

<?php
// Получаем выбранный шаблон из настроек Redux
$post_type = 'staff';
global $opt_name;
$templateloop = Redux::get_option($opt_name, 'archive_template_select_' . $post_type);
if (empty($templateloop)) {
    $templateloop = 'staff_1';
}
$template_file = "templates/archives/staff/{$templateloop}.php";

// Шаблоны, которые управляют своим циклом и вёрсткой самостоятельно
$self_contained = [ 'staff_7' ];
?>

<?php if ( in_array( $templateloop, $self_contained, true ) ) : ?>
    <?php get_template_part( "templates/archives/staff/{$templateloop}" ); ?>
<?php elseif (have_posts()) : ?>
<section id="content-wrapper" class="wrapper bg-light">
  <div class="container">
      <?php
      // Получаем позицию сайдбара
      $sidebar_position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
      $content_class = ($sidebar_position === 'none') ? 'col-12 py-14' : 'col-xl-9 pt-14';

      // Для staff_3, staff_4, staff_5, staff_6 используем row-cols структуру, для остальных - grid/isotope
      $use_row_cols = in_array( $templateloop, ['staff_3', 'staff_4', 'staff_5', 'staff_6'] );
      ?>

      <div class="row">
          <?php get_sidebar('left'); ?>

          <div class="<?php echo esc_attr($content_class); ?>">

      <?php if ($use_row_cols) : ?>
          <?php
          $_gap = Codeweber_Options::style( 'grid-gap' );
          if ( $templateloop === 'staff_3' ) {
              $row_cols_class = 'row row-cols-1 row-cols-md-3 row-cols-lg-4 ' . $_gap . ' mb-5';
          } elseif ( $templateloop === 'staff_6' ) {
              $row_cols_class = 'row ' . $_gap . ' mb-5';
          } else {
              $row_cols_class = 'row row-cols-1 row-cols-md-2 row-cols-lg-3 ' . $_gap . ' mb-5';
          }
          ?>
          <div class="<?php echo esc_attr($row_cols_class); ?>">
              <?php while (have_posts()) :
                the_post();
                if (locate_template($template_file)) {
                    get_template_part("templates/archives/staff/{$templateloop}");
                }
              endwhile; ?>
          </div>
          <!-- /.row -->
      <?php else : ?>
          <div class="grid mb-5">
              <div class="row isotope <?php echo esc_attr( Codeweber_Options::style( 'grid-gap' ) ); ?>">
                  <?php
                  while (have_posts()) :
                    the_post();
                    if (!empty($templateloop) && locate_template($template_file)) {
                        get_template_part("templates/archives/staff/{$templateloop}");
                    } else {
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

      <?php codeweber_posts_pagination(); ?>
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

