<?php
/**
 * Category filter — Bootstrap form-check / list-unstyled / button style.
 *
 * Expected variables:
 *   $terms_data      array  — from cw_get_category_filter_terms()
 *   $display_mode    string — 'checkbox' | 'radio' | 'list' | 'button'
 *   $show_count      bool
 *   $empty_behavior  string — 'default' | 'hide' | 'disable' | 'disable_clickable'
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $terms_data ) ) {
	return;
}

$display_mode        = $display_mode ?? 'list';
$show_count          = $show_count ?? true;
$button_class        = $button_class ?? 'btn-outline-secondary';
$button_active_class = $button_active_class ?? 'btn-secondary';
$checkbox_size_class = $checkbox_size_class ?? '';
$checkbox_item_class = $checkbox_item_class ?? '';
$checkbox_columns    = $checkbox_columns ?? 1;
$radio_size_class    = $radio_size_class ?? '';
$radio_item_class    = $radio_item_class ?? '';
$radio_name          = $radio_name ?? 'cw_filter_radio_cat';
$empty_behavior      = $empty_behavior ?? 'disable';
$badge_size          = $badge_size ?? '';
$badge_shape         = $badge_shape ?? 'rounded-pill';
$badge_color         = $badge_color ?? 'primary';
$badge_extra_class   = $badge_extra_class ?? '';
$badge_item_class    = $badge_item_class ?? '';
?>

<?php if ( 'button' === $display_mode ) : ?>

	<div class="d-flex flex-wrap gap-1">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];

			if ( 'default' === $empty_behavior ) { $is_empty = false; }
			elseif ( 'hide' === $empty_behavior && $is_empty ) { continue; }
			?>
			<?php if ( $is_empty ) : // 'disable' — no link ?>
				<span class="btn has-ripple <?php echo esc_attr( $button_class ); ?> disabled opacity-50"
					aria-disabled="true"
					<?php if ( $show_count ) : ?>title="(0)"<?php endif; ?>>
					<?php echo esc_html( $term->name ); ?>
				</span>
			<?php else : ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>"
					class="btn has-ripple pjax-link <?php echo $is_active ? esc_attr( $button_active_class ) : esc_attr( $button_class ); ?><?php echo ( 'disable_clickable' === $empty_behavior && ( $item['is_empty'] ?? false ) && ! $is_active ) ? ' opacity-50' : ''; ?>"
					<?php if ( $show_count ) : ?>title="(<?php echo esc_attr( $count ); ?>)"<?php endif; ?>>
					<?php echo esc_html( $term->name ); ?>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

<?php elseif ( 'checkbox' === $display_mode ) : ?>

	<ul class="list-unstyled ps-0 mb-0<?php echo 2 === $checkbox_columns ? ' cc-2' : ''; ?>">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];
			$uid       = 'cw-cat-' . sanitize_html_class( $term->slug );

			if ( 'default' === $empty_behavior ) { $is_empty = false; }
			elseif ( 'hide' === $empty_behavior && $is_empty ) { continue; }

			$is_clickable_muted = ( 'disable_clickable' === $empty_behavior && $is_empty );
			?>
			<li>
				<div class="form-check mb-1 cw-filter-check<?php echo esc_attr( $checkbox_size_class ); ?><?php echo $checkbox_item_class ? ' ' . esc_attr( $checkbox_item_class ) : ''; ?><?php echo $is_empty ? ' opacity-50' : ''; ?>">
					<input class="form-check-input"
						type="checkbox"
						id="<?php echo esc_attr( $uid ); ?>"
						<?php checked( $is_active ); ?>
						<?php if ( $is_empty && ! $is_clickable_muted ) { disabled( true ); } ?>
						tabindex="-1"
						aria-hidden="true">
					<?php if ( $is_empty && ! $is_clickable_muted ) : ?>
						<span class="form-check-label text-muted pe-none">
							<?php echo esc_html( $term->name ); ?>
							<?php if ( $show_count ) : ?>
								<span class="fs-sm ms-1">(0)</span>
							<?php endif; ?>
						</span>
					<?php else : ?>
						<a href="<?php echo esc_url( $item['url'] ); ?>"
							class="form-check-label pjax-link<?php echo $is_clickable_muted ? ' text-muted' : ''; ?>"
							aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
							<?php echo esc_html( $term->name ); ?>
							<?php if ( $show_count ) : ?>
								<span class="fs-sm text-muted ms-1">(<?php echo esc_html( $count ); ?>)</span>
							<?php endif; ?>
						</a>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>

<?php elseif ( 'radio' === $display_mode ) : ?>

	<ul class="list-unstyled ps-0 mb-0">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];
			$uid       = 'cw-cat-' . sanitize_html_class( $term->slug );

			if ( 'default' === $empty_behavior ) { $is_empty = false; }
			elseif ( 'hide' === $empty_behavior && $is_empty ) { continue; }

			$is_clickable_muted = ( 'disable_clickable' === $empty_behavior && $is_empty );
			?>
			<li>
				<div class="form-check mb-1 cw-filter-check<?php echo esc_attr( $radio_size_class ); ?><?php echo $radio_item_class ? ' ' . esc_attr( $radio_item_class ) : ''; ?><?php echo $is_empty ? ' opacity-50' : ''; ?>">
					<input class="form-check-input"
						type="radio"
						name="<?php echo esc_attr( $radio_name ); ?>"
						id="<?php echo esc_attr( $uid ); ?>"
						<?php checked( $is_active ); ?>
						<?php if ( $is_empty && ! $is_clickable_muted ) { disabled( true ); } ?>
						tabindex="-1"
						aria-hidden="true">
					<?php if ( $is_empty && ! $is_clickable_muted ) : ?>
						<span class="form-check-label text-muted pe-none">
							<?php echo esc_html( $term->name ); ?>
							<?php if ( $show_count ) : ?>
								<span class="fs-sm ms-1">(0)</span>
							<?php endif; ?>
						</span>
					<?php else : ?>
						<a href="<?php echo esc_url( $item['url'] ); ?>"
							class="form-check-label pjax-link<?php echo $is_clickable_muted ? ' text-muted' : ''; ?>"
							aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
							<?php echo esc_html( $term->name ); ?>
							<?php if ( $show_count ) : ?>
								<span class="fs-sm text-muted ms-1">(<?php echo esc_html( $count ); ?>)</span>
							<?php endif; ?>
						</a>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>

<?php elseif ( 'badge' === $display_mode ) : ?>

	<div class="d-flex flex-wrap gap-1">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];

			if ( 'default' === $empty_behavior ) { $is_empty = false; }
			elseif ( 'hide' === $empty_behavior && $is_empty ) { continue; }

			$badge_cls = 'badge' . ( $badge_color ? ' bg-' . $badge_color : '' );
			if ( $badge_size ) {
				$badge_cls .= ' ' . $badge_size;
			}
			if ( $badge_shape ) {
				$badge_cls .= ' ' . $badge_shape;
			}
			if ( $badge_extra_class ) {
				$badge_cls .= ' ' . $badge_extra_class;
			}
			if ( $badge_item_class ) {
				$badge_cls .= ' ' . $badge_item_class;
			}
			if ( $is_active ) {
				$badge_cls .= ' active';
			}

			$is_clickable_muted = ( 'disable_clickable' === $empty_behavior && $is_empty );
			?>
			<?php if ( $is_empty && ! $is_clickable_muted ) : ?>
				<span class="<?php echo esc_attr( $badge_cls ); ?> disabled opacity-50"
					aria-disabled="true"
					<?php if ( $show_count ) : ?>title="(0)"<?php endif; ?>>
					<?php echo esc_html( $term->name ); ?>
				</span>
			<?php else : ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>"
					class="<?php echo esc_attr( $badge_cls ); ?> pjax-link<?php echo $is_clickable_muted ? ' opacity-50' : ''; ?>"
					<?php if ( $show_count ) : ?>title="(<?php echo esc_attr( $count ); ?>)"<?php endif; ?>>
					<?php echo esc_html( $term->name ); ?>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

<?php elseif ( 'collapse' === $display_mode ) :

	// Fetch ALL product_cat terms (all hierarchy levels) for tree building.
	$all_cat_terms = get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
		'number'     => 0,
	] );

	if ( ! is_wp_error( $all_cat_terms ) && ! empty( $all_cat_terms ) ) :
		$cat_counts     = cw_get_filtered_term_counts( 'product_cat', '' );
		$queried_cat    = is_product_category() ? get_queried_object() : null;
		$queried_cat_id = ( $queried_cat && isset( $queried_cat->term_id ) ) ? (int) $queried_cat->term_id : 0;

		// When showing unfiltered totals, build a counts map from WordPress term->count.
		if ( ! empty( $count_unfiltered ) ) {
			$cat_display_counts = [];
			foreach ( $all_cat_terms as $_ct ) {
				$cat_display_counts[ (int) $_ct->term_id ] = (int) $_ct->count;
			}
		} else {
			$cat_display_counts = $cat_counts;
		}

		// Group terms by parent ID into a tree array.
		$cat_tree = [];
		foreach ( $all_cat_terms as $cat_term ) {
			$pid = (int) $cat_term->parent;
			if ( ! isset( $cat_tree[ $pid ] ) ) {
				$cat_tree[ $pid ] = [];
			}
			$filtered_count = $cat_counts[ (int) $cat_term->term_id ] ?? 0;
			$display_count  = $cat_display_counts[ (int) $cat_term->term_id ] ?? 0;
			$term_link      = get_term_link( $cat_term );
			$cat_tree[ $pid ][] = [
				'term'      => $cat_term,
				'wp_id'     => (int) $cat_term->term_id,
				'url'       => is_wp_error( $term_link ) ? '#' : $term_link,
				'count'     => $display_count,
				'is_active' => ( $queried_cat_id > 0 && (int) $cat_term->term_id === $queried_cat_id ),
				'is_empty'  => ( 0 === $filtered_count ),
			];
		}

		// Optionally sum descendant product counts into each parent's count.
		if ( ! empty( $count_with_children ) ) {
			$sum_descendants = function ( $tree, $wp_id ) use ( &$sum_descendants, $cat_display_counts ) {
				$total = $cat_display_counts[ $wp_id ] ?? 0;
				foreach ( $tree[ $wp_id ] ?? [] as $child ) {
					$total += $sum_descendants( $tree, $child['wp_id'] );
				}
				return $total;
			};
			foreach ( $cat_tree as &$cat_siblings ) {
				foreach ( $cat_siblings as &$ci ) {
					$ci['count']    = $sum_descendants( $cat_tree, $ci['wp_id'] );
					$ci['is_empty'] = ( 0 === $ci['count'] );
				}
			}
			unset( $cat_siblings, $ci );
		}

		$cl_type          = $collapse_list_type ?? '1';
		$collapse_wrap_id = esc_attr( $section_id ) . '-cmenu';

		// Checks recursively whether any item in the subtree is the current category.
		$cat_subtree_has_current = function ( $tree, $pid ) use ( &$cat_subtree_has_current ) {
			foreach ( $tree[ $pid ] ?? [] as $child ) {
				if ( $child['is_active'] ) {
					return true;
				}
				if ( isset( $tree[ $child['wp_id'] ] ) && $cat_subtree_has_current( $tree, $child['wp_id'] ) ) {
					return true;
				}
			}
			return false;
		};

		// Recursive renderer for Bootstrap Collapse styles (types 1–3).
		$render_cat_collapse = function ( $tree, $pid, $root_id, $list_type, $lvl = 1 ) use ( &$render_cat_collapse, &$cat_subtree_has_current, $show_count, $empty_behavior ) {
			$children = $tree[ $pid ] ?? [];
			if ( empty( $children ) ) {
				return '';
			}
			$html = '';
			foreach ( $children as $ci ) {
				$has_sub   = isset( $tree[ $ci['wp_id'] ] ) && ! empty( $tree[ $ci['wp_id'] ] );
				$is_active = $ci['is_active'];
				$is_empty  = $ci['is_empty'];

				if ( 'default' === $empty_behavior ) {
					$is_empty = false;
				} elseif ( 'hide' === $empty_behavior && $is_empty && ! $is_active ) {
					continue;
				}

				$is_disabled        = ( 'disable' === $empty_behavior && $is_empty && ! $is_active );
				$is_clickable_muted = ( 'disable_clickable' === $empty_behavior && $is_empty && ! $is_active );
				$expand             = $has_sub && $cat_subtree_has_current( $tree, $ci['wp_id'] );
				$cid                = $root_id . '-' . $ci['wp_id'];

				$li_cls = array_filter( [ 'nav-item', 'parent-collapse-item', 1 === $lvl ? 'parent-item' : '', $has_sub ? 'collapse-has-children' : '', $is_active ? 'current-menu-item' : '' ] );
				$html  .= '<li class="' . esc_attr( implode( ' ', $li_cls ) ) . '">';

				$count_html = $show_count ? '<span class="fs-sm text-muted ms-1">(' . (int) $ci['count'] . ')</span>' : '';

				if ( $has_sub ) {
					$a_cls = array_filter( [ 'nav-link', 'd-block', 'flex-grow-1', 'pjax-link', $is_active ? 'fw-semibold' : '', $is_active ? 'current-menu-item' : '', ( $is_empty && ! $is_active ) ? 'opacity-50' : '', $is_clickable_muted ? 'text-muted' : '' ] );
					$html .= '<div class="menu-collapse-row d-flex align-items-center justify-content-between">';
					if ( $is_disabled ) {
						$html .= '<span class="' . esc_attr( implode( ' ', $a_cls ) ) . '">' . esc_html( $ci['term']->name ) . $count_html . '</span>';
					} else {
						$html .= '<a href="' . esc_url( $ci['url'] ) . '" class="' . esc_attr( implode( ' ', $a_cls ) ) . '"' . ( $is_active ? ' aria-current="page"' : '' ) . '>' . esc_html( $ci['term']->name ) . $count_html . '</a>';
					}
					$btn_cls = array_filter( [ 'btn-collapse', 'w-5', 'h-5', $expand ? '' : 'collapsed' ] );
					$html   .= '<button type="button" class="' . esc_attr( implode( ' ', $btn_cls ) ) . '" data-bs-toggle="collapse" data-bs-target="#' . esc_attr( $cid ) . '" aria-expanded="' . ( $expand ? 'true' : 'false' ) . '" aria-controls="' . esc_attr( $cid ) . '" aria-label="' . esc_attr__( 'Expand submenu', 'codeweber' ) . '">';
					$html   .= '<span class="toggle_block" aria-hidden="true"><i class="uil uil-angle-down sidebar-catalog-icon"></i></span>';
					$html   .= '</button></div>';
					$sub_cls = 'navbar-nav list-unstyled menu-collapse-' . $list_type;
					$html   .= '<div class="collapse' . ( $expand ? ' show' : '' ) . '" id="' . esc_attr( $cid ) . '" data-bs-parent="#' . esc_attr( $root_id ) . '">';
					$html   .= '<ul class="' . esc_attr( $sub_cls ) . '">';
					$html   .= $render_cat_collapse( $tree, $ci['wp_id'], $root_id, $list_type, $lvl + 1 );
					$html   .= '</ul></div>';
				} else {
					$a_cls = array_filter( [ 'nav-link', 'd-block', 'pjax-link', $is_active ? 'fw-semibold' : '', $is_active ? 'current-menu-item' : '', ( $is_empty && ! $is_active ) ? 'opacity-50' : '', $is_clickable_muted ? 'text-muted' : '' ] );
					if ( $is_disabled ) {
						$html .= '<span class="' . esc_attr( implode( ' ', $a_cls ) ) . '">' . esc_html( $ci['term']->name ) . $count_html . '</span>';
					} else {
						$html .= '<a href="' . esc_url( $ci['url'] ) . '" class="' . esc_attr( implode( ' ', $a_cls ) ) . '"' . ( $is_active ? ' aria-current="page"' : '' ) . '>' . esc_html( $ci['term']->name ) . $count_html . '</a>';
					}
				}

				$html .= '</li>';
			}
			return $html;
		};

		// Recursive renderer for Type 4 — simple nested list without Bootstrap Collapse.
		$render_cat_list4 = function ( $tree, $pid ) use ( &$render_cat_list4, $show_count, $empty_behavior ) {
			$children = $tree[ $pid ] ?? [];
			if ( empty( $children ) ) {
				return '';
			}
			$html = '';
			foreach ( $children as $ci ) {
				$has_sub   = isset( $tree[ $ci['wp_id'] ] ) && ! empty( $tree[ $ci['wp_id'] ] );
				$is_active = $ci['is_active'];
				$is_empty  = $ci['is_empty'];

				if ( 'default' === $empty_behavior ) {
					$is_empty = false;
				} elseif ( 'hide' === $empty_behavior && $is_empty && ! $is_active ) {
					continue;
				}

				$is_disabled        = ( 'disable' === $empty_behavior && $is_empty && ! $is_active );
				$is_clickable_muted = ( 'disable_clickable' === $empty_behavior && $is_empty && ! $is_active );

				$count_html = $show_count ? '<span class="fs-sm text-muted ms-1">(' . (int) $ci['count'] . ')</span>' : '';
				$a_cls      = array_filter( [ 'pjax-link', $is_active ? 'fw-semibold' : '', ( $is_empty && ! $is_active ) ? 'opacity-50' : '', $is_clickable_muted ? 'text-muted' : '' ] );

				$html .= '<li>';
				if ( $is_disabled ) {
					$html .= '<span class="' . esc_attr( implode( ' ', $a_cls ) ) . '">' . esc_html( $ci['term']->name ) . $count_html . '</span>';
				} else {
					$html .= '<a href="' . esc_url( $ci['url'] ) . '" class="' . esc_attr( implode( ' ', $a_cls ) ) . '"' . ( $is_active ? ' aria-current="page"' : '' ) . '>' . esc_html( $ci['term']->name ) . $count_html . '</a>';
				}
				if ( $has_sub ) {
					$html .= '<ul class="list-unstyled menu-type-4-sub">';
					$html .= $render_cat_list4( $tree, $ci['wp_id'] );
					$html .= '</ul>';
				}
				$html .= '</li>';
			}
			return $html;
		};

		// Recursive renderer for Type 5 — Bootstrap dropend (submenu opens to the right, hover-based).
		// Markup mirrors the theme's vertical-menu type 5: separate <span class="dropdown-toggle">
		// so the <a> stays navigable; opening is CSS hover, not JS click.
		$render_cat_dropdown5 = function ( $tree, $pid, $lvl = 1 ) use ( &$render_cat_dropdown5, $show_count, $empty_behavior ) {
			$children = $tree[ $pid ] ?? [];
			if ( empty( $children ) ) {
				return '';
			}
			$html = '';
			foreach ( $children as $ci ) {
				$has_sub   = isset( $tree[ $ci['wp_id'] ] ) && ! empty( $tree[ $ci['wp_id'] ] );
				$is_active = $ci['is_active'];
				$is_empty  = $ci['is_empty'];

				if ( 'default' === $empty_behavior ) {
					$is_empty = false;
				} elseif ( 'hide' === $empty_behavior && $is_empty && ! $is_active ) {
					continue;
				}

				$is_disabled        = ( 'disable' === $empty_behavior && $is_empty && ! $is_active );
				$is_clickable_muted = ( 'disable_clickable' === $empty_behavior && $is_empty && ! $is_active );
				$count_html         = $show_count ? '<span class="fs-sm text-muted ms-1">(' . (int) $ci['count'] . ')</span>' : '';

				// Unique id for aria-labelledby
				$link_id = 'cw-d5-' . $ci['wp_id'];

				if ( 1 === $lvl ) {
					// Root level
					$li_cls = array_filter( [ 'nav-item', 'parent-item', $has_sub ? 'dropdown parent-link dropend' : '', $is_active ? 'current-menu-item' : '' ] );
					$html  .= '<li class="' . esc_attr( implode( ' ', $li_cls ) ) . '">';
					$a_cls  = array_filter( [ 'nav-link', 'pjax-link', $is_active ? 'fw-semibold current-menu-item' : '', ( $is_empty && ! $is_active ) ? 'opacity-50' : '', $is_clickable_muted ? 'text-muted' : '' ] );
					if ( $is_disabled ) {
						$html .= '<span class="' . esc_attr( implode( ' ', $a_cls ) ) . '">' . esc_html( $ci['term']->name ) . $count_html . '</span>';
					} else {
						$id_attr = $has_sub ? ' id="' . esc_attr( $link_id ) . '"' : '';
						$html .= '<a href="' . esc_url( $ci['url'] ) . '" class="' . esc_attr( implode( ' ', $a_cls ) ) . '"' . $id_attr . ( $is_active ? ' aria-current="page"' : '' ) . '>' . esc_html( $ci['term']->name ) . $count_html . '</a>';
					}
				} else {
					// Nested level
					$li_cls = array_filter( [ 'nav-item', $has_sub ? 'dropdown dropend parent-link dropdown-submenu' : '', $is_active ? 'current-menu-item' : '' ] );
					$html  .= '<li class="' . esc_attr( implode( ' ', $li_cls ) ) . '">';
					$a_cls  = array_filter( [ 'dropdown-item', 'pjax-link', $is_active ? 'fw-semibold current-menu-item' : '', ( $is_empty && ! $is_active ) ? 'opacity-50' : '', $is_clickable_muted ? 'text-muted' : '' ] );
					if ( $is_disabled ) {
						$html .= '<span class="' . esc_attr( implode( ' ', $a_cls ) ) . '">' . esc_html( $ci['term']->name ) . $count_html . '</span>';
					} else {
						$id_attr = $has_sub ? ' id="' . esc_attr( $link_id ) . '"' : '';
						$html .= '<a href="' . esc_url( $ci['url'] ) . '" class="' . esc_attr( implode( ' ', $a_cls ) ) . '"' . $id_attr . ( $is_active ? ' aria-current="page"' : '' ) . '>' . esc_html( $ci['term']->name ) . $count_html . '</a>';
					}
				}

				if ( $has_sub ) {
					// Separate toggle span — keeps the <a> navigable (hover opens via CSS)
					$html .= '<span class="dropdown-toggle" aria-hidden="true"></span>';
					$html .= '<ul class="dropdown-menu rounded-0" aria-labelledby="' . esc_attr( $link_id ) . '" role="menu">';
					$html .= $render_cat_dropdown5( $tree, $ci['wp_id'], $lvl + 1 );
					$html .= '</ul>';
				}
				$html .= '</li>';
			}
			return $html;
		};

		$navbar_scheme_cls = isset( $navbar_scheme ) && 'navbar-dark' === $navbar_scheme ? 'navbar-dark' : 'navbar-light';
		if ( '5' === $cl_type ) :
			echo '<nav id="' . esc_attr( $collapse_wrap_id ) . '" class="navbar-vertical navbar-vertical-dropdown ' . esc_attr( $navbar_scheme_cls ) . '">';
			echo '<ul class="navbar-nav flex-column">';
			echo $render_cat_dropdown5( $cat_tree, 0 ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</ul></nav>';
		elseif ( '4' === $cl_type ) :
			echo '<nav id="' . esc_attr( $collapse_wrap_id ) . '" class="navbar-vertical ' . esc_attr( $navbar_scheme_cls ) . '">';
			echo '<ul class="list-unstyled menu-list-type-4">';
			echo $render_cat_list4( $cat_tree, 0 ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</ul></nav>';
		else :
			$nav_list_cls = 'navbar-nav list-unstyled menu-collapse-' . $cl_type;
			echo '<nav id="' . esc_attr( $collapse_wrap_id ) . '" class="navbar-vertical menu-collapse-nav ' . esc_attr( $navbar_scheme_cls ) . '">';
			echo '<ul class="' . esc_attr( $nav_list_cls ) . '">';
			echo $render_cat_collapse( $cat_tree, 0, $collapse_wrap_id, $cl_type ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</ul></nav>';
		endif;

	endif; // end if !is_wp_error

?><?php else : // list — default ?>

	<ul class="list-unstyled ps-0 mb-0">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];

			if ( 'default' === $empty_behavior ) { $is_empty = false; }
			elseif ( 'hide' === $empty_behavior && $is_empty ) { continue; }

			$is_clickable_muted = ( 'disable_clickable' === $empty_behavior && $is_empty );
			?>
			<li class="mb-1<?php echo $is_empty ? ' opacity-50' : ''; ?>">
				<?php if ( $is_empty && ! $is_clickable_muted ) : ?>
					<span class="link-body text-muted pe-none" style="text-decoration:none;">
						<?php echo esc_html( $term->name ); ?>
						<?php if ( $show_count ) : ?>
							<span class="fs-sm ms-1">(0)</span>
						<?php endif; ?>
					</span>
				<?php else : ?>
					<a href="<?php echo esc_url( $item['url'] ); ?>"
						class="link-body pjax-link<?php echo $is_active ? ' fw-semibold' : ''; ?><?php echo $is_clickable_muted ? ' text-muted' : ''; ?>"
						style="text-decoration:none;">
						<?php echo esc_html( $term->name ); ?>
						<?php if ( $show_count ) : ?>
							<span class="fs-sm text-muted ms-1">(<?php echo esc_html( $count ); ?>)</span>
						<?php endif; ?>
					</a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

<?php endif; ?>
