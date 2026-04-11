<?php

namespace Codeweber\Functions\Fetch;

defined( 'ABSPATH' ) || exit;

function filterPosts( $params ) {
	$allowed_post_types = [ 'post', 'vacancies', 'products', 'staff', 'events', 'projects' ];
	$post_type          = sanitize_key( $params['post_type'] ?? 'post' );

	if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
		return [
			'status'  => 'error',
			'message' => __( 'Invalid post type', 'codeweber' ),
		];
	}

	$filters  = is_array( $params['filters'] ?? null ) ? $params['filters'] : [];
	$template = sanitize_text_field( $params['template'] ?? '' );

	$args = [
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	];

	if ( $post_type === 'vacancies' ) {
		$args = _fp_apply_vacancy_filters( $args, $filters );
	} elseif ( $post_type === 'post' ) {
		$args = _fp_apply_post_filters( $args, $filters );
	} elseif ( $post_type === 'products' && class_exists( 'WooCommerce' ) ) {
		$args = _fp_apply_product_filters( $args, $filters );
	} elseif ( $post_type === 'staff' ) {
		$args = _fp_apply_staff_filters( $args, $filters );
	} elseif ( $post_type === 'events' ) {
		$args = _fp_apply_events_filters( $args, $filters );
	} elseif ( $post_type === 'projects' ) {
		$args = _fp_apply_projects_filters( $args, $filters );
	}

	$query = new \WP_Query( $args );

	ob_start();

	if ( $query->have_posts() ) {
		if ( $post_type === 'vacancies' && $template === 'vacancies_1' ) {
			_fp_render_vacancies_list( $query );
		} elseif ( $post_type === 'vacancies' && in_array( $template, [ 'vacancies_2', 'vacancies_3', 'vacancies_4', 'vacancies_5', 'vacancies_6' ], true ) ) {
			global $wp_query;
			$wp_query = $query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			get_template_part( 'templates/archives/vacancies/' . $template );
			wp_reset_query();
		} elseif ( $post_type === 'staff' && $template === 'staff_1' ) {
			_fp_render_staff_horizontal( $query );
		} elseif ( $post_type === 'events' && $template === 'events_3' ) {
			_fp_render_events_cards( $query );
		} elseif ( $post_type === 'events' && in_array( $template, [ 'events_4', 'events_5' ], true ) ) {
			_fp_render_events_horizontal( $query, $template );
		} elseif ( $post_type === 'events' ) {
			_fp_render_events_table( $query );
		} elseif ( $post_type === 'projects' ) {
			_fp_render_projects_grid( $query, $template );
		} else {
			while ( $query->have_posts() ) {
				$query->the_post();
				get_template_part( 'template-parts/content', get_post_type() );
			}
		}
	} else {
		echo '<div class="py-14"><p>' . esc_html__( 'No items found.', 'codeweber' ) . '</p></div>';
	}

	wp_reset_postdata();
	$html = ob_get_clean();

	return [
		'status' => 'success',
		'data'   => [
			'html'        => $html,
			'found_posts' => $query->found_posts,
		],
	];
}

// ---------------------------------------------------------------------------
// Filter builders
// ---------------------------------------------------------------------------

function _fp_apply_vacancy_filters( $args, $filters ) {
	$meta_query = [];
	$tax_query  = [];

	$vacancy_type_id = ! empty( $filters['vacancy_type'] ) ? $filters['vacancy_type'] : ( ! empty( $filters['position'] ) ? $filters['position'] : null );
	if ( ! empty( $vacancy_type_id ) ) {
		$tax_query[] = [
			'taxonomy' => 'vacancy_type',
			'field'    => 'term_id',
			'terms'    => intval( $vacancy_type_id ),
		];
	}

	if ( ! empty( $filters['location'] ) ) {
		$meta_query[] = [
			'key'     => '_vacancy_location',
			'value'   => sanitize_text_field( $filters['location'] ),
			'compare' => '=',
		];
	}

	if ( ! empty( $meta_query ) ) {
		if ( count( $meta_query ) > 1 ) {
			$meta_query['relation'] = 'AND';
		}
		$args['meta_query'] = $meta_query;
	}

	if ( ! empty( $tax_query ) ) {
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}
		$args['tax_query'] = $tax_query;
	}

	return $args;
}

function _fp_apply_post_filters( $args, $filters ) {
	$tax_query = [];

	if ( ! empty( $filters['category'] ) ) {
		$tax_query[] = [
			'taxonomy' => 'category',
			'field'    => 'term_id',
			'terms'    => intval( $filters['category'] ),
		];
	}

	if ( ! empty( $filters['tag'] ) ) {
		$tax_query[] = [
			'taxonomy' => 'post_tag',
			'field'    => 'term_id',
			'terms'    => intval( $filters['tag'] ),
		];
	}

	if ( ! empty( $tax_query ) ) {
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}
		$args['tax_query'] = $tax_query;
	}

	return $args;
}

function _fp_apply_product_filters( $args, $filters ) {
	$tax_query  = [];
	$meta_query = [];

	if ( ! empty( $filters['category'] ) ) {
		$tax_query[] = [
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => intval( $filters['category'] ),
		];
	}

	if ( ! empty( $filters['tag'] ) ) {
		$tax_query[] = [
			'taxonomy' => 'product_tag',
			'field'    => 'term_id',
			'terms'    => intval( $filters['tag'] ),
		];
	}

	if ( ! empty( $filters['price_min'] ) || ! empty( $filters['price_max'] ) ) {
		$meta_query[] = [
			'key'     => '_price',
			'value'   => [
				floatval( $filters['price_min'] ?: 0 ),
				floatval( $filters['price_max'] ?: 999999 ),
			],
			'compare' => 'BETWEEN',
			'type'    => 'NUMERIC',
		];
	}

	if ( ! empty( $meta_query ) ) {
		if ( count( $meta_query ) > 1 ) {
			$meta_query['relation'] = 'AND';
		}
		$args['meta_query'] = $meta_query;
	}

	if ( ! empty( $tax_query ) ) {
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}
		$args['tax_query'] = $tax_query;
	}

	return $args;
}

function _fp_apply_staff_filters( $args, $filters ) {
	if ( ! empty( $filters['department'] ) ) {
		$args['tax_query'] = [
			[
				'taxonomy' => 'staff_department',
				'field'    => 'term_id',
				'terms'    => intval( $filters['department'] ),
			],
		];
	}

	if ( ! empty( $filters['city'] ) ) {
		$matching_ids = _fp_resolve_staff_ids_by_city( sanitize_text_field( $filters['city'] ) );
		$args['post__in'] = ! empty( $matching_ids ) ? $matching_ids : [ 0 ];
	}

	return $args;
}

/**
 * Resolve staff IDs by city name.
 * Priority: office's towns taxonomy → staff's own _staff_city meta.
 */
function _fp_resolve_staff_ids_by_city( $city_name ) {
	$matching_ids = [];

	// 1. Staff linked to an office whose towns term matches the city
	if ( post_type_exists( 'offices' ) ) {
		$towns = get_terms( [
			'taxonomy'   => 'towns',
			'name'       => $city_name,
			'hide_empty' => false,
		] );

		if ( ! empty( $towns ) && ! is_wp_error( $towns ) ) {
			$town_ids = wp_list_pluck( $towns, 'term_id' );
			$offices  = get_posts( [
				'post_type'      => 'offices',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'tax_query'      => [ [
					'taxonomy' => 'towns',
					'field'    => 'term_id',
					'terms'    => $town_ids,
					'operator' => 'IN',
				] ],
			] );

			if ( ! empty( $offices ) ) {
				$via_office = get_posts( [
					'post_type'      => 'staff',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => [ [
						'key'     => '_staff_office',
						'value'   => $offices,
						'compare' => 'IN',
						'type'    => 'NUMERIC',
					] ],
				] );
				$matching_ids = array_merge( $matching_ids, $via_office );
			}
		}
	}

	// 2. Staff without an office whose _staff_city matches
	$direct = get_posts( [
		'post_type'      => 'staff',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => [
			'relation' => 'AND',
			[ 'key' => '_staff_city', 'value' => $city_name, 'compare' => '=' ],
			[ 'relation' => 'OR',
				[ 'key' => '_staff_office', 'compare' => 'NOT EXISTS' ],
				[ 'key' => '_staff_office', 'value' => '', 'compare' => '=' ],
			],
		],
	] );
	$matching_ids = array_merge( $matching_ids, $direct );

	return array_unique( array_map( 'intval', $matching_ids ) );
}

function _fp_apply_events_filters( $args, $filters ) {
	if ( ! empty( $filters['event_category'] ) ) {
		$args['tax_query'] = [
			[
				'taxonomy' => 'event_category',
				'field'    => 'term_id',
				'terms'    => intval( $filters['event_category'] ),
			],
		];
	}

	$args['meta_key'] = '_event_date_start';
	$args['orderby']  = 'meta_value';
	$args['order']    = 'ASC';

	return $args;
}

// ---------------------------------------------------------------------------
// Render helpers
// ---------------------------------------------------------------------------

function _fp_render_vacancies_list( $query ) {
	$vacancies_by_type = [];

	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id      = get_the_ID();
		$vacancy_data = get_vacancy_data_array( $post_id );
		$types        = get_the_terms( $post_id, 'vacancy_type' );

		if ( $types && ! is_wp_error( $types ) ) {
			foreach ( $types as $type ) {
				if ( ! isset( $vacancies_by_type[ $type->term_id ] ) ) {
					$vacancies_by_type[ $type->term_id ] = [ 'term' => $type, 'vacancies' => [] ];
				}
				$vacancies_by_type[ $type->term_id ]['vacancies'][] = [ 'post_id' => $post_id, 'data' => $vacancy_data ];
			}
		} else {
			if ( ! isset( $vacancies_by_type['no-type'] ) ) {
				$vacancies_by_type['no-type'] = [ 'term' => null, 'vacancies' => [] ];
			}
			$vacancies_by_type['no-type']['vacancies'][] = [ 'post_id' => $post_id, 'data' => $vacancy_data ];
		}
	}

	$avatar_colors       = [ 'bg-red', 'bg-green', 'bg-yellow', 'bg-purple', 'bg-orange', 'bg-pink', 'bg-blue' ];
	$color_index         = 0;
	$archive_card_radius = class_exists( 'Codeweber_Options' ) ? \Codeweber_Options::style( 'card-radius' ) : '';

	if ( ! empty( $vacancies_by_type ) ) {
		foreach ( $vacancies_by_type as $type_id => $type_data ) {
			$term           = $type_data['term'];
			$vacancies_list = $type_data['vacancies'];
			?>
			<div class="job-list mb-10" data-type-id="<?php echo esc_attr( $type_id ); ?>">
				<?php if ( $term ) : ?>
					<h3 class="mb-4"><?php echo esc_html( $term->name ); ?></h3>
				<?php else : ?>
					<h3 class="mb-4"><?php esc_html_e( 'Other Vacancies', 'codeweber' ); ?></h3>
				<?php endif; ?>

				<?php foreach ( $vacancies_list as $vacancy ) :
					$avatar_color = $avatar_colors[ $color_index % count( $avatar_colors ) ];
					$color_index++;
					set_query_var( 'vacancy_list_item_post_id', $vacancy['post_id'] );
					set_query_var( 'vacancy_list_item_data', $vacancy['data'] );
					set_query_var( 'vacancy_list_item_avatar_color', $avatar_color );
					set_query_var( 'vacancy_list_item_card_radius', $archive_card_radius );
					get_template_part( 'templates/post-cards/vacancies/list-item' );
				endforeach; ?>
			</div>
			<?php
		}
	} else {
		echo '<div class="py-14"><p>' . esc_html__( 'No vacancies found.', 'codeweber' ) . '</p></div>';
	}
}

function _fp_render_events_table( $query ) {
	if ( ! function_exists( 'codeweber_events_get_registration_status' ) ) {
		echo '<p>' . esc_html__( 'No events found.', 'codeweber' ) . '</p>';
		return;
	}

	echo '<div class="table-responsive">';
	echo '<table class="table table-hover align-middle events-table">';
	echo '<thead class="table-light"><tr>';
	echo '<th>' . esc_html__( 'Date', 'codeweber' ) . '</th>';
	echo '<th>' . esc_html__( 'Event', 'codeweber' ) . '</th>';
	echo '<th class="d-none d-md-table-cell">' . esc_html__( 'Location', 'codeweber' ) . '</th>';
	echo '<th class="d-none d-lg-table-cell">' . esc_html__( 'Format', 'codeweber' ) . '</th>';
	echo '<th class="d-none d-md-table-cell">' . esc_html__( 'Price', 'codeweber' ) . '</th>';
	echo '<th></th>';
	echo '</tr></thead>';
	echo '<tbody>';

	$status_map = [
		'open'                => 'badge bg-soft-green text-green rounded-pill',
		'not_open_yet'        => 'badge bg-soft-yellow text-yellow rounded-pill',
		'registration_closed' => 'badge bg-soft-ash text-muted rounded-pill',
		'no_seats'            => 'badge bg-soft-red text-red rounded-pill',
		'event_ended'         => 'badge bg-soft-ash text-muted rounded-pill',
	];

	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id    = get_the_ID();
		$date_start = get_post_meta( $post_id, '_event_date_start', true );
		$date_end   = get_post_meta( $post_id, '_event_date_end', true );
		$location   = get_post_meta( $post_id, '_event_location', true );
		$price      = get_post_meta( $post_id, '_event_price', true );
		$reg_status = codeweber_events_get_registration_status( $post_id );
		$formats    = get_the_terms( $post_id, 'event_format' );

		$status_class = isset( $status_map[ $reg_status['status'] ] ) ? $status_map[ $reg_status['status'] ] : '';

		echo '<tr>';

		echo '<td class="event-date-cell">';
		if ( $date_start ) {
			echo '<span class="fw-semibold">' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_start ) ) ) . '</span>';
			if ( $date_end && $date_end !== $date_start ) {
				echo '<br><small class="text-muted">' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_end ) ) ) . '</small>';
			}
		} else {
			echo '<span class="text-muted">—</span>';
		}
		echo '</td>';

		echo '<td>';
		echo '<a href="' . esc_url( get_permalink() ) . '" class="fw-semibold text-reset text-decoration-none">' . esc_html( get_the_title() ) . '</a>';
		if ( $status_class && ! empty( $reg_status['label'] ) ) {
			echo '<br><span class="event-status-badge ' . esc_attr( $status_class ) . ' mt-1">' . esc_html( $reg_status['label'] ) . '</span>';
		}
		echo '</td>';

		echo '<td class="d-none d-md-table-cell">';
		echo $location ? esc_html( $location ) : '<span class="text-muted">—</span>';
		echo '</td>';

		echo '<td class="d-none d-lg-table-cell">';
		if ( $formats && ! is_wp_error( $formats ) ) {
			foreach ( $formats as $fmt ) {
				echo '<span class="badge bg-soft-ash text-navy event-format-badge">' . esc_html( $fmt->name ) . '</span> ';
			}
		} else {
			echo '<span class="text-muted">—</span>';
		}
		echo '</td>';

		echo '<td class="d-none d-md-table-cell event-card-price">';
		echo $price ? esc_html( $price ) : '<span class="text-muted">—</span>';
		echo '</td>';

		$btn_style = class_exists( 'Codeweber_Options' ) ? \Codeweber_Options::style( 'button' ) : ' rounded-pill';
		echo '<td class="text-end">';
		echo '<a href="' . esc_url( get_permalink() ) . '" class="btn btn-sm btn-primary has-ripple' . esc_attr( $btn_style ) . '">' . esc_html__( 'Details', 'codeweber' ) . '</a>';
		echo '</td>';

		echo '</tr>';
	}

	echo '</tbody></table></div>';
}

function _fp_render_events_cards( $query ) {
	if ( ! function_exists( 'codeweber_events_get_registration_status' ) ) {
		echo '<p>' . esc_html__( 'No events found.', 'codeweber' ) . '</p>';
		return;
	}

	$avatar_colors = [ 'red', 'green', 'yellow', 'purple', 'orange', 'pink', 'blue', 'grape', 'violet', 'fuchsia' ];
	$card_radius   = class_exists( 'Codeweber_Options' ) ? \Codeweber_Options::style( 'card-radius' ) : '';

	$status_map = [
		'open'                => 'badge bg-soft-green text-green rounded-pill',
		'not_open_yet'        => 'badge bg-soft-yellow text-yellow rounded-pill',
		'registration_closed' => 'badge bg-soft-ash text-muted rounded-pill',
		'no_seats'            => 'badge bg-soft-red text-red rounded-pill',
		'event_ended'         => 'badge bg-soft-ash text-muted rounded-pill',
	];

	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id    = get_the_ID();
		$date_start = get_post_meta( $post_id, '_event_date_start', true );
		$date_end   = get_post_meta( $post_id, '_event_date_end', true );
		$location   = get_post_meta( $post_id, '_event_location', true );
		$price      = get_post_meta( $post_id, '_event_price', true );
		$reg_status = codeweber_events_get_registration_status( $post_id );
		$formats    = get_the_terms( $post_id, 'event_format' );
		$cats       = get_the_terms( $post_id, 'event_category' );

		$cat_index    = ( $cats && ! is_wp_error( $cats ) ) ? ( $cats[0]->term_id % count( $avatar_colors ) ) : 0;
		$avatar_color = $avatar_colors[ $cat_index ];
		$avatar_label = $date_start ? date_i18n( 'j', strtotime( $date_start ) ) : '?';
		$month_label  = $date_start ? date_i18n( 'M', strtotime( $date_start ) ) : '';
		$status_class = isset( $status_map[ $reg_status['status'] ] ) ? $status_map[ $reg_status['status'] ] : '';
		$format_str   = ( $formats && ! is_wp_error( $formats ) ) ? implode( ', ', wp_list_pluck( $formats, 'name' ) ) : '';
		?>
		<a href="<?php echo esc_url( get_permalink() ); ?>" class="card mb-4 lift<?php echo $card_radius ? ' ' . esc_attr( trim( $card_radius ) ) : ''; ?>">
			<div class="card-body p-5">
				<span class="row justify-content-between align-items-center">
					<span class="col-md-5 mb-2 mb-md-0 d-flex align-items-center text-body">
						<span class="avatar bg-<?php echo esc_attr( $avatar_color ); ?> text-white w-9 h-9 fs-17 me-3 flex-shrink-0">
							<?php echo esc_html( $avatar_label ); ?>
						</span>
						<span>
							<?php echo esc_html( get_the_title() ); ?>
							<?php if ( $month_label ) : ?>
								<small class="text-muted ms-1"><?php echo esc_html( $month_label ); ?></small>
							<?php endif; ?>
							<?php if ( $status_class && ! empty( $reg_status['label'] ) ) : ?>
								<br><span class="event-status-badge <?php echo esc_attr( $status_class ); ?> mt-1">
									<?php echo esc_html( $reg_status['label'] ); ?>
								</span>
							<?php endif; ?>
						</span>
					</span>
					<span class="col-5 col-md-3 text-body d-flex align-items-center">
						<i class="uil uil-presentation me-1"></i>
						<?php echo $format_str ? esc_html( $format_str ) : '<span class="text-muted">—</span>'; ?>
					</span>
					<span class="col-7 col-md-4 col-lg-3 text-body d-flex align-items-center">
						<?php if ( $location ) : ?>
							<i class="uil uil-location-arrow me-1"></i><?php echo esc_html( $location ); ?>
						<?php elseif ( $price ) : ?>
							<i class="uil uil-tag-alt me-1"></i><?php echo esc_html( $price ); ?>
						<?php else : ?>
							<span class="text-muted">—</span>
						<?php endif; ?>
					</span>
					<span class="d-none d-lg-block col-1 text-center text-body">
						<i class="uil uil-angle-right-b"></i>
					</span>
				</span>
			</div>
		</a>
		<?php
	}
}

function _fp_render_staff_horizontal( $query ) {
	$grid_gap = class_exists( 'Codeweber_Options' ) ? \Codeweber_Options::style( 'grid-gap' ) : 'gx-md-8 gy-6';

	echo '<div class="row ' . esc_attr( $grid_gap ) . ' mb-5">';

	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id   = get_the_ID();
		$card_html = cw_render_post_card( get_post(), 'horizontal', [], [
			'show_description' => true,
			'image_size'       => 'codeweber_staff',
		] );

		if ( empty( $card_html ) ) {
			continue;
		}

		echo '<div id="' . esc_attr( $post_id ) . '" class="col-12">' . $card_html . '</div>';
	}

	echo '</div>';
}

function _fp_render_events_horizontal( $query, $template ) {
	if ( ! function_exists( 'codeweber_events_get_registration_status' ) ) {
		echo '<p>' . esc_html__( 'No events found.', 'codeweber' ) . '</p>';
		return;
	}

	$btn_style     = class_exists( 'Codeweber_Options' ) ? \Codeweber_Options::style( 'button' ) : ' rounded-pill';
	$card_radius   = class_exists( 'Codeweber_Options' ) ? \Codeweber_Options::style( 'card-radius' ) : '';
	$grid_gap      = class_exists( 'Codeweber_Options' ) ? \Codeweber_Options::style( 'grid-gap' ) : 'gx-md-8 gy-6';
	$is_style5     = ( $template === 'events_5' );
	$figure_radius = ( $card_radius && $card_radius !== 'rounded-0' ) ? ' rounded-start' : ( $card_radius ? ' ' . trim( $card_radius ) : '' );

	$status_map = [
		'open'                => 'badge bg-soft-green text-green rounded-pill',
		'not_open_yet'        => 'badge bg-soft-yellow text-yellow rounded-pill',
		'registration_closed' => 'badge bg-soft-ash text-muted rounded-pill',
		'no_seats'            => 'badge bg-soft-red text-red rounded-pill',
		'event_ended'         => 'badge bg-soft-ash text-muted rounded-pill',
	];

	echo '<div class="row ' . esc_attr( $grid_gap ) . ' mb-5">';

	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id    = get_the_ID();
		$date_start = get_post_meta( $post_id, '_event_date_start', true );
		$date_end   = get_post_meta( $post_id, '_event_date_end', true );
		$location   = get_post_meta( $post_id, '_event_location', true );
		$price      = get_post_meta( $post_id, '_event_price', true );
		$reg_status = codeweber_events_get_registration_status( $post_id );
		$formats    = get_the_terms( $post_id, 'event_format' );

		$thumbnail_id = get_post_thumbnail_id( $post_id );
		$image_url    = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'codeweber_event_400-267' ) : '';
		if ( empty( $image_url ) ) {
			$image_url = get_template_directory_uri() . '/dist/assets/img/photos/about6.jpg';
		}

		$status_class = isset( $status_map[ $reg_status['status'] ] ) ? $status_map[ $reg_status['status'] ] : '';
		$format_str   = ( $formats && ! is_wp_error( $formats ) ) ? implode( ', ', wp_list_pluck( $formats, 'name' ) ) : '';
		$link         = esc_url( get_permalink() );
		$title        = esc_html( get_the_title() );
		$radius_cls   = $card_radius ? ' ' . esc_attr( $card_radius ) : '';
		$fig_cls      = $figure_radius ? ' ' . esc_attr( trim( $figure_radius ) ) : '';
		$img_cls      = 'img-fluid' . $radius_cls;

		$date_html = '';
		if ( $date_start ) {
			$date_html = '<p class="mb-1 text-muted small"><i class="uil uil-calendar-alt me-1"></i>'
				. esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_start ) ) );
			if ( $date_end && $date_end !== $date_start ) {
				$date_html .= ' — ' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_end ) ) );
			}
			$date_html .= '</p>';
		}

		$status_html = ( $status_class && ! empty( $reg_status['label'] ) )
			? '<p class="mb-3"><span class="event-status-badge ' . esc_attr( $status_class ) . '">' . esc_html( $reg_status['label'] ) . '</span></p>'
			: '';

		$list_html = '';
		if ( $location ) {
			$list_html .= '<li class="mb-1 d-flex align-items-center"><i class="uil uil-map-marker-alt text-primary me-2"></i><span>' . esc_html( $location ) . '</span></li>';
		}
		if ( $format_str ) {
			$list_html .= '<li class="mb-1 d-flex align-items-center"><i class="uil uil-presentation text-primary me-2"></i><span>' . esc_html( $format_str ) . '</span></li>';
		}
		if ( $price ) {
			$list_html .= '<li class="mb-1 d-flex align-items-center"><i class="uil uil-tag-alt text-primary me-2"></i><span>' . esc_html( $price ) . '</span></li>';
		}

		echo '<div class="col-12">';

		if ( $is_style5 ) {
			echo '<a href="' . $link . '" class="card card-horizontal lift text-inherit text-decoration-none' . $radius_cls . '">';
			echo '<figure class="card-img mb-0' . $fig_cls . '">';
			echo '<img src="' . esc_url( $image_url ) . '" alt="' . $title . '" class="' . esc_attr( trim( $img_cls ) ) . '">';
			echo '</figure>';
			echo '<div class="card-body position-relative">';
			echo $date_html;
			echo '<h2 class="mb-3 display-6">' . $title . '</h2>';
			echo $status_html;
			echo '<ul class="list-unstyled cc-2 mb-0">' . $list_html . '</ul>';
			echo '<div class="hover_card_button position-absolute p-7 top-0 end-0"><i class="fs-25 uil uil-arrow-right lh-1"></i></div>';
			echo '</div>';
			echo '</a>';
		} else {
			echo '<div class="card card-horizontal' . $radius_cls . '">';
			echo '<figure class="card-img overlay overlay-1 hover-scale' . $fig_cls . '">';
			echo '<a href="' . $link . '"><img src="' . esc_url( $image_url ) . '" alt="' . $title . '" class="' . esc_attr( trim( $img_cls ) ) . '"></a>';
			echo '<figcaption><h5 class="from-top mb-0">' . esc_html__( 'Read More', 'codeweber' ) . '</h5></figcaption>';
			echo '</figure>';
			echo '<div class="card-body">';
			echo $date_html;
			echo '<h2 class="mb-3 display-6">' . $title . '</h2>';
			echo $status_html;
			echo '<ul class="list-unstyled cc-2 mb-4">' . $list_html . '</ul>';
			echo '<div data-group="page-title-buttons" class="text-end">';
			echo '<a href="' . $link . '" class="btn btn-primary btn-icon btn-icon-start has-ripple' . esc_attr( $btn_style ) . '">';
			echo '<i class="uil uil-arrow-right"></i>' . esc_html__( 'Details', 'codeweber' );
			echo '</a></div>';
			echo '</div>';
			echo '</div>';
		}

		echo '</div>';
	}

	echo '</div>';
}

function _fp_apply_projects_filters( $args, $filters ) {
	if ( ! empty( $filters['projects_category'] ) ) {
		$args['tax_query'] = [
			[
				'taxonomy' => 'projects_category',
				'field'    => 'term_id',
				'terms'    => intval( $filters['projects_category'] ),
			],
		];
	}

	$args['orderby'] = 'menu_order date';
	$args['order']   = 'ASC';

	return $args;
}

function _fp_render_projects_grid( $query, $template ) {
	$card_radius = class_exists( 'Codeweber_Options' ) ? \Codeweber_Options::style( 'card-radius' ) : 'rounded';
	$col_class   = ( $template === 'projects_4' ) ? 'col-md-6 col-xl-4' : 'col-md-6';
	$img_size    = ( $template === 'projects_4' ) ? 'codeweber_project_600-600' : 'codeweber_project_900-900';
	$grid_gap    = class_exists( 'Codeweber_Options' ) ? \Codeweber_Options::style( 'grid-gap' ) : 'gx-md-8 gy-10 gy-md-13';

	echo '<div class="row ' . esc_attr( $grid_gap ) . '">';

	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id      = get_the_ID();
		$cats         = get_the_terms( $post_id, 'projects_category' );
		$cat_name     = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
		$thumbnail_id = get_post_thumbnail_id( $post_id );

		echo '<div class="project ' . esc_attr( $col_class ) . '">';

		if ( $thumbnail_id ) {
			echo '<figure class="lift ' . esc_attr( $card_radius ) . ' mb-6">';
			echo '<a href="' . esc_url( get_permalink() ) . '">';
			echo wp_get_attachment_image( $thumbnail_id, $img_size, false, [ 'class' => 'w-100', 'alt' => esc_attr( get_the_title() ) ] );
			echo '</a></figure>';
		}

		echo '<div class="project-details d-flex justify-content-center flex-column">';
		echo '<div class="post-header">';
		if ( $cat_name ) {
			echo '<div class="post-category text-line mb-2">' . esc_html( $cat_name ) . '</div>';
		}
		echo '<h2 class="post-title h3"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>';
		echo '</div></div>';
		echo '</div>';
	}

	echo '</div>';
}
