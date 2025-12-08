<?php
/**
 * Template for FAQ Archive Page
 * Based on terms.html structure
 */

get_header(); ?>
<?php get_pageheader(); ?>

<section id="content-wrapper" class="wrapper">
	<div class="container">
		<div class="row gx-lg-8 gx-xl-12">
		<?php
		// Get all FAQ categories for sidebar navigation
		$faq_categories = get_terms(array(
			'taxonomy' => 'faq_categories',
			'hide_empty' => true,
		));

		// Get all FAQs grouped by category
		$faqs_by_category = array();
		if (have_posts()) {
			while (have_posts()) {
				the_post();
				$terms = get_the_terms(get_the_ID(), 'faq_categories');
				if ($terms && !is_wp_error($terms)) {
					foreach ($terms as $term) {
						if (!isset($faqs_by_category[$term->term_id])) {
							$faqs_by_category[$term->term_id] = array(
								'term' => $term,
								'posts' => array()
							);
						}
						$faqs_by_category[$term->term_id]['posts'][] = get_post();
					}
				} else {
					// FAQs without category
					if (!isset($faqs_by_category[0])) {
						$faqs_by_category[0] = array(
							'term' => null,
							'posts' => array()
						);
					}
					$faqs_by_category[0]['posts'][] = get_post();
				}
			}
		}
		wp_reset_postdata();

		// Re-fetch all FAQs for display
		$all_faqs = new WP_Query(array(
			'post_type' => 'faq',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order' => 'ASC',
		));
		?>

		<aside class="col-xl-4 sidebar sticky-sidebar mt-md-0 py-14 d-none d-xl-block">
			<div class="widget">
				<nav id="sidebar-nav">
					<ul class="list-unstyled text-reset">
						<?php if (!empty($faq_categories) && !is_wp_error($faq_categories)) : ?>
							<?php 
							$index = 1;
							foreach ($faq_categories as $category) : 
								$category_id = sanitize_title($category->name);
							?>
								<li><a class="nav-link scroll" href="#<?php echo esc_attr($category_id); ?>"><?php echo esc_html($index); ?>. <?php echo esc_html($category->name); ?></a></li>
							<?php 
								$index++;
							endforeach; 
							?>
						<?php else : ?>
							<?php if ($all_faqs->have_posts()) : ?>
								<li><a class="nav-link scroll" href="#faq-all"><?php esc_html_e('All FAQs', 'codeweber'); ?></a></li>
							<?php endif; ?>
						<?php endif; ?>
					</ul>
				</nav>
				<!-- /nav -->
			</div>
			<!-- /.widget -->
		</aside>
		<!-- /column -->

		<div class="col-md-8 py-14">
			<?php if ($all_faqs->have_posts()) : ?>
				<?php
				// If we have categories, group by category
				if (!empty($faq_categories) && !is_wp_error($faq_categories)) :
					$category_index = 1;
					foreach ($faq_categories as $category) :
						$category_id = sanitize_title($category->name);
						$category_faqs = new WP_Query(array(
							'post_type' => 'faq',
							'posts_per_page' => -1,
							'orderby' => 'menu_order',
							'order' => 'ASC',
							'tax_query' => array(
								array(
									'taxonomy' => 'faq_categories',
									'field' => 'term_id',
									'terms' => $category->term_id,
								),
							),
						));
						
						if ($category_faqs->have_posts()) :
							$section_class = ($category_index === 1) ? 'wrapper' : 'wrapper pt-5';
				?>
							<section id="<?php echo esc_attr($category_id); ?>" class="<?php echo esc_attr($section_class); ?>">
								<div class="card">
									<div class="card-body p-10">
										<h2 class="mb-6"><?php echo esc_html($category_index); ?>. <?php echo esc_html($category->name); ?></h2>
										<?php if (!empty($category->description)) : ?>
											<p class="lead mb-6"><?php echo esc_html($category->description); ?></p>
										<?php endif; ?>
										
										<div class="accordion accordion-wrapper" id="accordionFaq<?php echo esc_attr($category->term_id); ?>">
											<?php
											$counter = 0;
											while ($category_faqs->have_posts()) :
												$category_faqs->the_post();
												$counter++;
												$heading_id = 'headingFaq' . $category->term_id . '_' . $counter;
												$collapse_id = 'collapseFaq' . $category->term_id . '_' . $counter;
												$expanded = 'false';
												$show_class = '';
												$button_class = 'accordion-button collapsed';
											?>
												<div class="card plain accordion-item">
													<div class="card-header" id="<?php echo esc_attr($heading_id); ?>">
														<button class="<?php echo esc_attr($button_class); ?>" type="button" data-bs-toggle="collapse" 
																data-bs-target="#<?php echo esc_attr($collapse_id); ?>" aria-expanded="<?php echo esc_attr($expanded); ?>" 
																aria-controls="<?php echo esc_attr($collapse_id); ?>">
															<?php echo esc_html(get_the_title()); ?>
														</button>
													</div>
													<!--/.card-header -->
													<div id="<?php echo esc_attr($collapse_id); ?>" class="accordion-collapse collapse <?php echo esc_attr($show_class); ?>" 
														 aria-labelledby="<?php echo esc_attr($heading_id); ?>" data-bs-parent="#accordionFaq<?php echo esc_attr($category->term_id); ?>">
														<div class="card-body">
															<?php the_content(); ?>
														</div>
														<!--/.card-body -->
													</div>
													<!--/.accordion-collapse -->
												</div>
												<!--/.accordion-item -->
											<?php endwhile; ?>
										</div>
										<!--/.accordion -->
									</div>
									<!--/.card-body -->
								</div>
								<!--/.card -->
							</section>
				<?php
							$category_index++;
							wp_reset_postdata();
						endif;
					endforeach;
					
					// Show FAQs without category
					$uncategorized_faqs = new WP_Query(array(
						'post_type' => 'faq',
						'posts_per_page' => -1,
						'orderby' => 'menu_order',
						'order' => 'ASC',
						'tax_query' => array(
							array(
								'taxonomy' => 'faq_categories',
								'operator' => 'NOT EXISTS',
							),
						),
					));
					
					if ($uncategorized_faqs->have_posts()) :
				?>
						<section id="faq-uncategorized" class="wrapper pt-5">
							<div class="card">
								<div class="card-body p-10">
									<h2 class="mb-6"><?php echo esc_html($category_index); ?>. <?php esc_html_e('Other Questions', 'codeweber'); ?></h2>
									
									<div class="accordion accordion-wrapper" id="accordionFaqUncategorized">
										<?php
										$counter = 0;
										while ($uncategorized_faqs->have_posts()) :
											$uncategorized_faqs->the_post();
											$counter++;
											$heading_id = 'headingFaqUncat_' . $counter;
											$collapse_id = 'collapseFaqUncat_' . $counter;
											$expanded = 'false';
											$show_class = '';
											$button_class = 'accordion-button collapsed';
										?>
											<div class="card plain accordion-item">
												<div class="card-header" id="<?php echo esc_attr($heading_id); ?>">
													<button class="<?php echo esc_attr($button_class); ?>" type="button" data-bs-toggle="collapse" 
															data-bs-target="#<?php echo esc_attr($collapse_id); ?>" aria-expanded="<?php echo esc_attr($expanded); ?>" 
															aria-controls="<?php echo esc_attr($collapse_id); ?>">
														<?php echo esc_html(get_the_title()); ?>
													</button>
												</div>
												<!--/.card-header -->
												<div id="<?php echo esc_attr($collapse_id); ?>" class="accordion-collapse collapse <?php echo esc_attr($show_class); ?>" 
													 aria-labelledby="<?php echo esc_attr($heading_id); ?>" data-bs-parent="#accordionFaqUncategorized">
													<div class="card-body">
														<?php the_content(); ?>
													</div>
													<!--/.card-body -->
												</div>
												<!--/.accordion-collapse -->
											</div>
											<!--/.accordion-item -->
										<?php endwhile; ?>
									</div>
									<!--/.accordion -->
								</div>
								<!--/.card-body -->
							</div>
							<!--/.card -->
						</section>
				<?php
						wp_reset_postdata();
					endif;
				else :
					// No categories, show all FAQs in one accordion
				?>
					<section id="faq-all" class="wrapper">
						<div class="card">
							<div class="card-body p-10">
								<div class="accordion accordion-wrapper" id="accordionFaqAll">
									<?php
									$counter = 0;
									while ($all_faqs->have_posts()) :
										$all_faqs->the_post();
										$counter++;
										$heading_id = 'headingFaqAll_' . $counter;
										$collapse_id = 'collapseFaqAll_' . $counter;
										$expanded = 'false';
										$show_class = '';
										$button_class = 'accordion-button collapsed';
									?>
										<div class="card plain accordion-item">
											<div class="card-header" id="<?php echo esc_attr($heading_id); ?>">
												<button class="<?php echo esc_attr($button_class); ?>" type="button" data-bs-toggle="collapse" 
														data-bs-target="#<?php echo esc_attr($collapse_id); ?>" aria-expanded="<?php echo esc_attr($expanded); ?>" 
														aria-controls="<?php echo esc_attr($collapse_id); ?>">
													<?php echo esc_html(get_the_title()); ?>
												</button>
											</div>
											<!--/.card-header -->
											<div id="<?php echo esc_attr($collapse_id); ?>" class="accordion-collapse collapse <?php echo esc_attr($show_class); ?>" 
												 aria-labelledby="<?php echo esc_attr($heading_id); ?>" data-bs-parent="#accordionFaqAll">
												<div class="card-body">
													<?php the_content(); ?>
												</div>
												<!--/.card-body -->
											</div>
											<!--/.accordion-collapse -->
										</div>
										<!--/.accordion-item -->
									<?php endwhile; ?>
								</div>
								<!--/.accordion -->
							</div>
							<!--/.card-body -->
						</div>
						<!--/.card -->
					</section>
				<?php endif; ?>
			<?php else : ?>
				<section class="wrapper">
					<div class="card">
						<div class="card-body p-10">
							<p><?php esc_html_e('No FAQs found.', 'codeweber'); ?></p>
						</div>
					</div>
				</section>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
		</div>
		<!-- /column -->
	</div>
	<!-- /.row -->
	</div>
	<!-- /.container -->
</section>
<!-- /#content-wrapper -->
</section>
<!-- /#content-wrapper -->

<?php get_footer(); ?>
