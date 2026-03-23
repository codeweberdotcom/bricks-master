<?php
/**
 * CPT: Event Registrations (Заявки на мероприятия)
 *
 * Приватный CPT для хранения заявок.
 * Доступен в меню «Мероприятия» → «Заявки».
 *
 * Статусы: reg_pending, reg_confirmed, reg_cancelled, reg_awaiting
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Custom Post Statuses
// ---------------------------------------------------------------------------

function codeweber_event_registrations_register_statuses(): void {
	register_post_status( 'reg_pending', [
		'label'                     => _x( 'New', 'event registration status', 'codeweber' ),
		'label_count'               => _n_noop( 'New <span class="count">(%s)</span>', 'New <span class="count">(%s)</span>', 'codeweber' ),
		'public'                    => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'exclude_from_search'       => true,
	] );

	register_post_status( 'reg_confirmed', [
		'label'                     => _x( 'Confirmed', 'event registration status', 'codeweber' ),
		'label_count'               => _n_noop( 'Confirmed <span class="count">(%s)</span>', 'Confirmed <span class="count">(%s)</span>', 'codeweber' ),
		'public'                    => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'exclude_from_search'       => true,
	] );

	register_post_status( 'reg_cancelled', [
		'label'                     => _x( 'Cancelled', 'event registration status', 'codeweber' ),
		'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'codeweber' ),
		'public'                    => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'exclude_from_search'       => true,
	] );

	register_post_status( 'reg_awaiting', [
		'label'                     => _x( 'Awaiting Payment', 'event registration status', 'codeweber' ),
		'label_count'               => _n_noop( 'Awaiting Payment <span class="count">(%s)</span>', 'Awaiting Payment <span class="count">(%s)</span>', 'codeweber' ),
		'public'                    => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'exclude_from_search'       => true,
	] );
}
add_action( 'init', 'codeweber_event_registrations_register_statuses' );

// ---------------------------------------------------------------------------
// Post Type
// ---------------------------------------------------------------------------

function cptui_register_my_cpts_event_registrations(): void {
	$labels = [
		'name'               => esc_html__( 'Registrations', 'codeweber' ),
		'singular_name'      => esc_html__( 'Registration', 'codeweber' ),
		'menu_name'          => esc_html__( 'Registrations', 'codeweber' ),
		'all_items'          => esc_html__( 'All Registrations', 'codeweber' ),
		'add_new_item'       => esc_html__( 'Add Registration', 'codeweber' ),
		'edit_item'          => esc_html__( 'Edit Registration', 'codeweber' ),
		'view_item'          => esc_html__( 'View Registration', 'codeweber' ),
		'search_items'       => esc_html__( 'Search Registrations', 'codeweber' ),
		'not_found'          => esc_html__( 'No registrations found', 'codeweber' ),
		'not_found_in_trash' => esc_html__( 'No registrations in trash', 'codeweber' ),
	];

	$args = [
		'label'               => esc_html__( 'Registrations', 'codeweber' ),
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'show_in_rest'        => false,
		'has_archive'         => false,
		'show_in_menu'        => 'edit.php?post_type=events',
		'show_in_nav_menus'   => false,
		'delete_with_user'    => false,
		'exclude_from_search' => true,
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'hierarchical'        => false,
		'can_export'          => true,
		'rewrite'             => false,
		'query_var'           => false,
		'supports'            => [ 'title' ],
		'show_in_graphql'     => false,
	];

	register_post_type( 'event_registrations', $args );
}
add_action( 'init', 'cptui_register_my_cpts_event_registrations' );

// ---------------------------------------------------------------------------
// Admin: custom columns
// ---------------------------------------------------------------------------

function codeweber_event_reg_columns( array $columns ): array {
	return [
		'cb'           => $columns['cb'],
		'reg_event'    => __( 'Event', 'codeweber' ),
		'reg_name'     => __( 'Name', 'codeweber' ),
		'reg_email'    => __( 'Email', 'codeweber' ),
		'reg_phone'    => __( 'Phone', 'codeweber' ),
		'reg_seats'    => __( 'Seats', 'codeweber' ),
		'reg_status'   => __( 'Status', 'codeweber' ),
		'date'         => __( 'Date', 'codeweber' ),
	];
}
add_filter( 'manage_event_registrations_posts_columns', 'codeweber_event_reg_columns' );

function codeweber_event_reg_column_content( string $column, int $post_id ): void {
	switch ( $column ) {
		case 'reg_event':
			$event_id = (int) get_post_meta( $post_id, '_reg_event_id', true );
			if ( $event_id ) {
				echo '<a href="' . esc_url( get_edit_post_link( $event_id ) ) . '">' . esc_html( get_the_title( $event_id ) ) . '</a>';
			} else {
				echo '—';
			}
			break;

		case 'reg_name':
			echo esc_html( get_post_meta( $post_id, '_reg_name', true ) ?: '—' );
			break;

		case 'reg_email':
			$email = get_post_meta( $post_id, '_reg_email', true );
			if ( $email ) {
				echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
			} else {
				echo '—';
			}
			break;

		case 'reg_phone':
			echo esc_html( get_post_meta( $post_id, '_reg_phone', true ) ?: '—' );
			break;

		case 'reg_seats':
			$seats = (int) get_post_meta( $post_id, '_reg_seats', true );
			echo esc_html( $seats > 0 ? $seats : 1 );
			break;

		case 'reg_status':
			$status = get_post_field( 'post_status', $post_id );
			$labels = [
				'reg_pending'   => [ 'label' => __( 'New', 'codeweber' ),             'color' => '#856404', 'bg' => '#fff3cd' ],
				'reg_confirmed' => [ 'label' => __( 'Confirmed', 'codeweber' ),        'color' => '#0f5132', 'bg' => '#d1e7dd' ],
				'reg_cancelled' => [ 'label' => __( 'Cancelled', 'codeweber' ),        'color' => '#842029', 'bg' => '#f8d7da' ],
				'reg_awaiting'  => [ 'label' => __( 'Awaiting Payment', 'codeweber' ), 'color' => '#084298', 'bg' => '#cfe2ff' ],
			];
			if ( isset( $labels[ $status ] ) ) {
				$s = $labels[ $status ];
				printf(
					'<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;color:%s;background:%s;">%s</span>',
					esc_attr( $s['color'] ),
					esc_attr( $s['bg'] ),
					esc_html( $s['label'] )
				);
			} else {
				echo esc_html( $status );
			}
			break;
	}
}
add_action( 'manage_event_registrations_posts_custom_column', 'codeweber_event_reg_column_content', 10, 2 );

// ---------------------------------------------------------------------------
// Admin: sortable columns
// ---------------------------------------------------------------------------

function codeweber_event_reg_sortable_columns( array $columns ): array {
	$columns['reg_status'] = 'reg_status';
	$columns['date']       = 'date';
	return $columns;
}
add_filter( 'manage_edit-event_registrations_sortable_columns', 'codeweber_event_reg_sortable_columns' );

// ---------------------------------------------------------------------------
// Admin: filter by event
// ---------------------------------------------------------------------------

function codeweber_event_reg_filter_bar(): void {
	global $typenow;
	if ( $typenow !== 'event_registrations' ) {
		return;
	}

	$events = get_posts( [
		'post_type'      => 'events',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	] );

	$selected = isset( $_GET['filter_event_id'] ) ? absint( $_GET['filter_event_id'] ) : 0;

	echo '<select name="filter_event_id">';
	echo '<option value="">' . esc_html__( 'All events', 'codeweber' ) . '</option>';
	foreach ( $events as $event ) {
		printf(
			'<option value="%d" %s>%s</option>',
			esc_attr( $event->ID ),
			selected( $selected, $event->ID, false ),
			esc_html( $event->post_title )
		);
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'codeweber_event_reg_filter_bar' );

function codeweber_event_reg_filter_query( \WP_Query $query ): void {
	global $pagenow, $typenow;
	if ( $pagenow !== 'edit.php' || $typenow !== 'event_registrations' || ! is_admin() ) {
		return;
	}
	if ( ! empty( $_GET['filter_event_id'] ) ) {
		$query->query_vars['meta_query'][] = [
			'key'   => '_reg_event_id',
			'value' => absint( $_GET['filter_event_id'] ),
			'type'  => 'NUMERIC',
		];
	}
}
add_action( 'parse_query', 'codeweber_event_reg_filter_query' );

function codeweber_event_reg_all_statuses_query( \WP_Query $query ): void {
	global $pagenow, $typenow;
	if ( ! is_admin() || $pagenow !== 'edit.php' || $typenow !== 'event_registrations' ) {
		return;
	}
	if ( ! $query->is_main_query() ) {
		return;
	}
	// Если статус не задан явно (режим "Все") — показывать все кастомные статусы
	if ( empty( $_GET['post_status'] ) ) {
		$query->set( 'post_status', [ 'reg_pending', 'reg_confirmed', 'reg_cancelled', 'reg_awaiting' ] );
	}
}
add_action( 'pre_get_posts', 'codeweber_event_reg_all_statuses_query' );

// ---------------------------------------------------------------------------
// Admin: bulk actions
// ---------------------------------------------------------------------------

function codeweber_event_reg_bulk_actions( array $actions ): array {
	$actions['bulk_confirm']  = __( 'Confirm selected', 'codeweber' );
	$actions['bulk_cancel']   = __( 'Cancel selected', 'codeweber' );
	return $actions;
}
add_filter( 'bulk_actions-edit-event_registrations', 'codeweber_event_reg_bulk_actions' );

function codeweber_event_reg_handle_bulk( string $redirect, string $action, array $post_ids ): string {
	if ( ! in_array( $action, [ 'bulk_confirm', 'bulk_cancel' ], true ) ) {
		return $redirect;
	}
	$new_status = $action === 'bulk_confirm' ? 'reg_confirmed' : 'reg_cancelled';
	foreach ( $post_ids as $post_id ) {
		wp_update_post( [ 'ID' => absint( $post_id ), 'post_status' => $new_status ] );
	}
	$redirect = add_query_arg( 'bulk_updated', count( $post_ids ), $redirect );
	return $redirect;
}
add_filter( 'handle_bulk_actions-edit-event_registrations', 'codeweber_event_reg_handle_bulk', 10, 3 );

// ---------------------------------------------------------------------------
// Admin: metabox on registration edit screen
// ---------------------------------------------------------------------------

function codeweber_event_reg_add_meta_boxes(): void {
	add_meta_box(
		'codeweber_reg_details',
		__( 'Registration Details', 'codeweber' ),
		'codeweber_event_reg_render_details_metabox',
		'event_registrations',
		'normal',
		'high'
	);
	add_meta_box(
		'codeweber_reg_status',
		__( 'Status', 'codeweber' ),
		'codeweber_event_reg_render_status_metabox',
		'event_registrations',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'codeweber_event_reg_add_meta_boxes' );

function codeweber_event_reg_render_details_metabox( \WP_Post $post ): void {
	$event_id = (int) get_post_meta( $post->ID, '_reg_event_id', true );
	$name     = get_post_meta( $post->ID, '_reg_name', true );
	$email    = get_post_meta( $post->ID, '_reg_email', true );
	$phone    = get_post_meta( $post->ID, '_reg_phone', true );
	$seats    = (int) get_post_meta( $post->ID, '_reg_seats', true );
	$message  = get_post_meta( $post->ID, '_reg_message', true );
	?>
	<table class="form-table">
		<tr>
			<th><?php esc_html_e( 'Event', 'codeweber' ); ?></th>
			<td>
				<?php if ( $event_id ) : ?>
					<a href="<?php echo esc_url( get_edit_post_link( $event_id ) ); ?>"><?php echo esc_html( get_the_title( $event_id ) ); ?></a>
				<?php else : ?>—<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Name', 'codeweber' ); ?></th>
			<td><?php echo esc_html( $name ?: '—' ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Email', 'codeweber' ); ?></th>
			<td><?php echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '—'; ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Phone', 'codeweber' ); ?></th>
			<td><?php echo esc_html( $phone ?: '—' ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Seats', 'codeweber' ); ?></th>
			<td><?php echo esc_html( $seats > 0 ? $seats : 1 ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Message', 'codeweber' ); ?></th>
			<td><?php echo $message ? esc_html( $message ) : '—'; ?></td>
		</tr>
	</table>
	<?php
}

function codeweber_event_reg_render_status_metabox( \WP_Post $post ): void {
	wp_nonce_field( 'codeweber_reg_status_save', 'codeweber_reg_status_nonce' );
	$current = $post->post_status;
	$statuses = [
		'reg_pending'   => __( 'New', 'codeweber' ),
		'reg_confirmed' => __( 'Confirmed', 'codeweber' ),
		'reg_cancelled' => __( 'Cancelled', 'codeweber' ),
		'reg_awaiting'  => __( 'Awaiting Payment', 'codeweber' ),
	];
	?>
	<select name="codeweber_reg_status" style="width:100%;margin-bottom:8px;">
		<?php foreach ( $statuses as $slug => $label ) : ?>
			<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current, $slug ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description" style="margin-top:4px;"><?php esc_html_e( 'Save changes to update status.', 'codeweber' ); ?></p>
	<?php
}

function codeweber_event_reg_save_status( int $post_id, \WP_Post $post ): void {
	if ( $post->post_type !== 'event_registrations' ) {
		return;
	}
	if ( ! isset( $_POST['codeweber_reg_status_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['codeweber_reg_status_nonce'] ) ), 'codeweber_reg_status_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$allowed = [ 'reg_pending', 'reg_confirmed', 'reg_cancelled', 'reg_awaiting' ];
	if ( isset( $_POST['codeweber_reg_status'] ) ) {
		$new_status = sanitize_key( $_POST['codeweber_reg_status'] );
		if ( in_array( $new_status, $allowed, true ) ) {
			remove_action( 'save_post', 'codeweber_event_reg_save_status', 10 );
			wp_update_post( [ 'ID' => $post_id, 'post_status' => $new_status ] );
			add_action( 'save_post', 'codeweber_event_reg_save_status', 10, 2 );
		}
	}
}
add_action( 'save_post', 'codeweber_event_reg_save_status', 10, 2 );

// ---------------------------------------------------------------------------
// Admin: badge count (new registrations) in menu
// ---------------------------------------------------------------------------

function codeweber_event_reg_menu_badge(): void {
	global $menu, $submenu;

	$count = (int) ( new WP_Query( [
		'post_type'      => 'event_registrations',
		'post_status'    => 'reg_pending',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	] ) )->found_posts;

	if ( $count <= 0 ) {
		return;
	}

	$badge = ' <span class="awaiting-mod count-' . $count . '"><span class="pending-count">' . $count . '</span></span>';

	if ( isset( $submenu['edit.php?post_type=events'] ) ) {
		foreach ( $submenu['edit.php?post_type=events'] as &$item ) {
			if ( isset( $item[2] ) && $item[2] === 'edit.php?post_type=event_registrations' ) {
				$item[0] .= $badge;
				break;
			}
		}
	}
}
add_action( 'admin_menu', 'codeweber_event_reg_menu_badge', 999 );

// ---------------------------------------------------------------------------
// Admin: fix post status display in list
// ---------------------------------------------------------------------------

function codeweber_event_reg_display_post_states( array $states, \WP_Post $post ): array {
	if ( $post->post_type !== 'event_registrations' ) {
		return $states;
	}
	$custom_statuses = [
		'reg_pending'   => __( 'New', 'codeweber' ),
		'reg_confirmed' => __( 'Confirmed', 'codeweber' ),
		'reg_cancelled' => __( 'Cancelled', 'codeweber' ),
		'reg_awaiting'  => __( 'Awaiting Payment', 'codeweber' ),
	];
	if ( isset( $custom_statuses[ $post->post_status ] ) ) {
		$states[ $post->post_status ] = $custom_statuses[ $post->post_status ];
	}
	return $states;
}
add_filter( 'display_post_states', 'codeweber_event_reg_display_post_states', 10, 2 );
