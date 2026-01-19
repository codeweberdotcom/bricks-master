<div id="footer-columns" class="navbar navbar-expand navbar-dark">

	<div class="container mt-5">
		
		<div class="row w-100">  
			<?php
			// Колонка 1 - Footer 1
			codeweber_footer_column('footer-1', 'col-md-3 mb-4', function() {
				?>
				<?php if ( has_custom_logo() ) {
					the_custom_logo();
				} else { ?>
					<a href="<?php echo esc_url_raw( home_url() ); ?>"><?php bloginfo( 'name' ); ?></a>
				<?php } ?>
				<?php
			});

			// Колонка 2 - Footer 2
			codeweber_footer_column('footer-2', 'col-md-3 mb-4', function() {
				?>
				<div class="h3 h4 mb-4"><?php esc_html_e( 'Contacts', 'codeweber' ); ?></div>
				<ul class="list-unstyled navbar-nav flex-column">
				</ul>
				<?php
				get_template_part( 'templates/components/socialicons', '' );
			});

			// Колонка 3 - Footer 3
			codeweber_footer_column('footer-3', 'col-md-3 mb-4', function() {
				?>
				<div class="h3 h4 mb-4"><?php esc_html_e( 'Pages', 'codeweber' ); ?></div>
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
			});

			// Колонка 4 - Footer 4
			codeweber_footer_column('footer-4', 'col-md-3 mb-4', function() {
				?>
				<div class="h3 h4 mb-4"><?php esc_html_e( 'Privacy', 'codeweber' ); ?></div>
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
			});
			?>
		</div>

	</div>

</div> <!-- #footer-columns -->
