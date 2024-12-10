<div id="footer-columns" class="navbar navbar-expand navbar-dark">

	<div class="container mt-5">
		
		<div class="row w-100">  

			<div class="col-md-3 mb-4">
				
				<?php if ( has_custom_logo() ) {

					the_custom_logo();

				} else { ?>

					<a href="<?php echo esc_url_raw( home_url() ); ?>"><?php bloginfo( 'name' ); ?></a>

				<?php } ?>
	
			</div>

			<div class="col-md-3 mb-4">

				<h3 class="h4 mb-4"><?php esc_html_e( 'Contacts', 'bricks' ); ?></h3>

				<ul class="list-unstyled navbar-nav flex-column">

					

					</ul>

						<?php

						get_template_part( 'templates/components/socialicons', '' );

					
					?>
				
			</div>

			<div class="col-md-3 mb-4">

				<h3 class="h4 mb-4"><?php esc_html_e( 'Pages', 'bricks' ); ?></h3>

				<?php
				wp_nav_menu(
					array(
						'theme_location'    => 'header',
						'depth'             => 1,
						'container'         => 'nav',
						'container_class'   => 'navbar-nav',
						'container_id'      => '',
						'menu_class'        => 'footer-menu list-unstyled',
						'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
						'walker'            => new WP_Bootstrap_Navwalker(),
					)
				);
				?>

			</div>

			<div class="col-md-3 mb-4">

				<h3 class="h4 mb-4"><?php esc_html_e( 'Privacy', 'bricks' ); ?></h3>

				<?php
				wp_nav_menu(
					array(
						'theme_location'    => 'footer',
						'depth'             => 1,
						'container'         => 'nav',
						'container_class'   => 'navbar-nav',
						'container_id'      => '',
						'menu_class'        => 'footer-menu list-unstyled',
						'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
						'walker'            => new WP_Bootstrap_Navwalker(),
					)
				);
				?>

			</div>

		</div>

	</div>

</div> <!-- #footer-columns -->
