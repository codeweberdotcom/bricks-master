<?php
get_header();

if (class_exists('WooCommerce')) :
   while (have_posts()) {
      the_post();
      global $opt_name;
      $login_page_image = Redux::get_option($opt_name, 'image_login_page')['url'];
      $page_header_title_class = Redux::get_option($opt_name, 'opt-select-title-size');
      if ( empty( $page_header_title_class ) ) {
         $page_header_title_class = 'display-1';
      }
?>

      <section class="wrapper bg-navy text-white">
         <div class="container pt-8 pt-md-12 pb-21 pb-md-21">
            <div class="row">
               <div class="col-lg-12">
                  <?php get_breadcrumbs('start', 'white'); ?>
                  <h1 class="<?php echo esc_attr( $page_header_title_class ); ?> text-white mb-3"><?= universal_title(false, false); ?></h1>
               </div>
            </div>
         </div>
      </section>

      <section class="wrapper bg-light">
         <div class="container pb-14 pb-md-16">
            <div class="row">
               <div class="col mt-n19">
                  <?php $myaccount_card_radius = function_exists( 'getThemeCardImageRadius' ) ? getThemeCardImageRadius() : ''; ?>
                  <div class="card shadow-lg<?php echo $myaccount_card_radius ? ' ' . esc_attr( $myaccount_card_radius ) : ''; ?>">
                     <div class="row gx-0">
                        <?php if (!is_user_logged_in()) : ?>
                           <?php
                           if (!$login_page_image) {
                              $login_page_image = get_template_directory_uri() . '/dist/assets/img/photos/tm3.jpg';
                           }
                           ?>
                           <div class="col image-wrapper bg-image bg-cover rounded-top rounded-lg-start d-none d-md-block<?php echo $myaccount_card_radius ? ' ' . esc_attr( $myaccount_card_radius ) : ''; ?>" data-image-src="<?php echo esc_url($login_page_image); ?>">
                           </div>
                        <?php endif; ?>
                        <div class="col">
                           <div class="p-10 p-md-11 p-lg-13">
                              <?php the_content(); ?>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>

   <?php
   } // end while

else : // WooCommerce not active
   ?>
   <section class="wrapper bg-light">
      <div class="container py-20 text-center">
         <h2>WooCommerce не активирован</h2>
         <p>Пожалуйста, установите и активируйте плагин WooCommerce.</p>
      </div>
   </section>
<?php
endif;

get_footer();
?>