<?php get_header(); ?>
<?php
while (have_posts()) {
   the_post();
   get_pageheader("6");
?>
   <main id="content-wrapper">
      <section class="wrapper bg-light">
         <div class="container pb-14 pb-md-16">
            <?php
            $slug = $post->post_name;
            $template_name = (is_file(get_theme_file_path('templates/content/pages/page-' . $slug . '.php'))) ? $slug : '';
            get_template_part('templates/content/pages/page', $template_name);
            ?>
         </div>
      </section>
      <?php } ?>
   </main> <!-- #content-wrapper -->
   <?php
   get_footer();
