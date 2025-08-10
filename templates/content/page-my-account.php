<?php get_header(); ?>
<?php while (have_posts()) {
   the_post();
   global $opt_name;
   $login_page_image = Redux::get_option($opt_name, 'image_login_page')['url'];



?>

   <section class="wrapper bg-navy text-white">
      <div class="container pt-18 pt-md-16 pb-21 pb-md-21">
         <div class="row">
            <div class="col-lg-12">
               <h1 class="display-1 text-white mb-3"><?= universal_title(); ?></h1>
               <?php get_breadcrumbs('start', 'white', 'mb-0'); ?>
               <!-- /nav -->
            </div>
            <!-- /column -->
         </div>
         <!-- /.row -->
      </div>
      <!-- /.container -->
   </section>

   <section class="wrapper bg-light">
      <div class="container pb-14 pb-md-16">
         <div class="row">
            <div class="col mt-n19">
               <div class="card shadow-lg">
                  <div class="row gx-0">
                     <?php
                     if (!is_user_logged_in()) {
                     ?>
                        <?php if(!$login_page_image){
                           $login_page_image = get_template_directory_uri() . '/dist/assets/img/photos/tm3.jpg';
                        }
                        ?>
                        <div class="col image-wrapper bg-image bg-cover rounded-top rounded-lg-start d-none d-md-block" data-image-src="<?php echo $login_page_image; ?>">
                        </div>
                        <!--/column -->
                     <?php
                     }
                     ?>
                     <div class="col">
                        <div class="p-10 p-md-11 p-lg-13">
                           <?php
                           the_content();
                           ?>
                           <!-- /form -->
                        </div>
                        <!--/div -->
                     </div>
                     <!--/column -->
                  </div>
                  <!--/.row -->
               </div>
               <!-- /.card -->
            </div>
            <!-- /column -->
         </div>
         <!-- /.row -->
      </div>
      <!-- /.container -->
   </section>
<?php } ?>
<?php
get_footer();
