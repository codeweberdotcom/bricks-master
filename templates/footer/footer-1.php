<?php

$footer_color_text = $footer_vars['footer_color_text'] ?? false;


$footer_background = $footer_vars['footer_background'] ?? false;

if ($footer_background === 'solid') {
   $footer_background_color = $footer_vars['footer_solid_color'] ?? false;
} elseif ($footer_background === 'soft') {
   $footer_background_color = $footer_vars['footer_soft_color'] ?? false;
} else {
   $footer_background_color = 'dark';
}

$text_class_array = [];
if ($footer_color_text === 'dark') {
   $text_class_array[] = 'text-white';
} else {
   $text_class_array[] = 'text-reset';
}
$text_class = implode(' ', $text_class_array);

?>

<footer class="bg-<?= $footer_background_color; ?>">
   <div class="container py-13 py-md-15">
      <div class="row gy-6 gy-lg-0">
         <div class="col-md-4 col-lg-3">
            <div class="widget">
               <?= get_custom_logo_type($footer_color_text); ?>
               <p class="mb-4 mt-3">
                  <?= do_shortcode('[redux_option key="text-about-company"]'); ?>
               </p>
               <nav class="nav social ">
                  <a href="#"><i class="uil uil-twitter"></i></a>
                  <a href="#"><i class="uil uil-facebook-f"></i></a>
                  <a href="#"><i class="uil uil-dribbble"></i></a>
                  <a href="#"><i class="uil uil-instagram"></i></a>
                  <a href="#"><i class="uil uil-youtube"></i></a>
               </nav>
               <!-- /.social -->
            </div>
            <!-- /.widget -->
         </div>
         <!-- /column -->
         <div class="col-md-4 col-lg-3">
            <div class="widget">
               <h4 class="widget-title mb-3 <?= $text_class; ?>">Get in Touch</h4>
               <address class="pe-xl-15 pe-xxl-17 <?= $text_class; ?>">Moonshine St. 14/05 Light City, London, United Kingdom</address>
               <?php echo do_shortcode('[get_contact field="e-mail" type="link" class="link-body"]
'); ?><br>
               <?php echo do_shortcode(' [get_contact field="phone_01" type="link" class="link-body"]
'); ?><br>
               <?php echo do_shortcode(' [get_contact field="phone_02" type="link" class="link-body"]
'); ?><br>



            </div>
            <!-- /.widget -->
         </div>
         <!-- /column -->
         <div class="col-md-4 col-lg-3">
            <div class="widget">
               <h4 class="widget-title  mb-3 <?= $text_class; ?>">Learn More</h4>
               <ul class="list-unstyled <?= $text_class; ?> mb-0">
                  <li><a class="<?= $text_class; ?>" href="#">About Us</a></li>
                  <li><a class="<?= $text_class; ?>" href="#">Our Story</a></li>
                  <li><a class="<?= $text_class; ?>" href="#">Projects</a></li>
                  <li><a class="<?= $text_class; ?>" href="#">Terms of Use</a></li>
                  <li><a class="<?= $text_class; ?>" href="#">Privacy Policy</a></li>
               </ul>
            </div>
            <!-- /.widget -->
         </div>
         <!-- /column -->
         <div class="col-md-12 col-lg-3">
            <div class="widget">
               <h4 class="widget-title  mb-3 <?= $text_class; ?>">Our Newsletter</h4>
               <p class="mb-5 <?= $text_class; ?>">Subscribe to our newsletter to get our news & deals delivered to you.</p>
               <div class="newsletter-wrapper">
                  <?= do_shortcode('[newsletter_form]'); ?>
               </div>
               <!-- /.newsletter-wrapper -->
            </div>
            <!-- /.widget -->
         </div>
         <!-- /column -->
      </div>
      <!--/.row -->
      <?php get_template_part('templates/footer/footer', 'copyright'); ?>
   </div>
   <!-- /.container -->
</footer>