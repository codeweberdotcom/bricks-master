<?php
/**
 * Duplicate Post
 *
 * "Duplicate" row action for every registered post type (show_ui, minus a
 * denylist) — clones title/content/meta/taxonomies as a draft. Forms embedded
 * via CodeWeber Forms (codeweber-blocks/form, form-selector, [codeweber_form]
 * shortcodes) are forked into independent copies so the duplicate doesn't
 * share submissions/settings with the original.
 *
 * Spec: doc_claude/integrations/DUPLICATE_POST.md
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Scope: which post types get the "Duplicate" action
// ---------------------------------------------------------------------------

if ( ! function_exists( 'codeweber_duplicate_post_get_post_types' ) ) {
	function codeweber_duplicate_post_get_post_types(): array {
		$types   = get_post_types( [ 'show_ui' => true, '_builtin' => false ], 'names' );
		$types[] = 'post';
		$types[] = 'page';

		$denylist = apply_filters( 'codeweber_duplicate_post_denylist', [
			'attachment', // media files aren't duplicated through this link
			'product',    // WooCommerce — raw meta copy is unsafe (SKU uniqueness, lookup tables, variations); fork via WC API instead if ever needed
		] );
		$types = array_diff( $types, $denylist );

		return apply_filters( 'codeweber_duplicate_post_post_types', array_values( array_unique( $types ) ) );
	}
}

// ---------------------------------------------------------------------------
// Title uniqueness ("Title" -> "Title (1)" -> "Title (2)", scoped per post_type)
// ---------------------------------------------------------------------------

if ( ! function_exists( 'codeweber_duplicate_post_title_exists' ) ) {
	/**
	 * @param int $exclude_id Post ID to leave out of the check (not used by the
	 *                        duplicate-post call site itself — the new post
	 *                        doesn't exist yet at title-generation time, so
	 *                        there's nothing of its own to exclude — but a
	 *                        general-purpose title lookup needs the option, the
	 *                        same way core's wp_unique_post_slug() does).
	 */
	function codeweber_duplicate_post_title_exists( string $title, string $post_type, int $exclude_id = 0 ): bool {
		$args = [
			'post_type'        => $post_type,
			'post_status'      => get_post_stati(), // every registered status — draft/private/trash included
			'title'            => $title,           // exact match ($wpdb post_title = %s), not a fuzzy `s` search
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'no_found_rows'    => true,
			'suppress_filters' => true,
		];
		if ( $exclude_id > 0 ) {
			$args['post__not_in'] = [ $exclude_id ];
		}

		return (bool) get_posts( $args );
	}
}

if ( ! function_exists( 'codeweber_duplicate_post_unique_title' ) ) {
	/**
	 * Any existing trailing " (N)" suffix is stripped first, so duplicating an
	 * already-numbered copy renumbers from the same base instead of stacking
	 * suffixes ("Title (1) (1)") — it finds the next free slot in the same
	 * sequence regardless of which existing copy was duplicated.
	 */
	function codeweber_duplicate_post_unique_title( string $title, string $post_type, int $exclude_id = 0 ): string {
		if ( '' === trim( $title ) ) {
			return $title;
		}

		$base = preg_replace( '/\s\(\d+\)$/', '', $title );

		$candidate = $base;
		$suffix    = 1;
		while ( codeweber_duplicate_post_title_exists( $candidate, $post_type, $exclude_id ) ) {
			$candidate = $base . ' (' . $suffix . ')';
			$suffix++;
		}

		return $candidate;
	}
}

// ---------------------------------------------------------------------------
// Core: duplicate a single post
// ---------------------------------------------------------------------------

if ( ! function_exists( 'codeweber_duplicate_post_create_duplicate' ) ) {
	/**
	 * @param WP_Post    $post
	 * @param array|null $ctx  { fork_map: array<int,int>, created_ids: int[] } threaded
	 *                         by reference through recursive calls (form forking, see
	 *                         codeweber_duplicate_post_get_or_fork_form()). Left null on
	 *                         a top-level call — the function initializes it itself.
	 * @return int|WP_Error
	 */
	function codeweber_duplicate_post_create_duplicate( WP_Post $post, ?array &$ctx = null ) {
		// Captured BEFORE $ctx is normalized below — this is the only reliable
		// way to tell "user clicked Duplicate on this post directly" apart from
		// "this call is a recursive form fork triggered from inside another
		// duplication" (get_or_fork_form() always passes a non-null $ctx by
		// reference). Checking `null !== $ctx` after normalizing it would always
		// be true and misclassify every top-level duplication of a form itself.
		$is_top_level = ( null === $ctx );
		if ( $is_top_level ) {
			$ctx = [
				'fork_map'    => [],
				'created_ids' => [],
			];
		}

		if ( ! apply_filters( 'codeweber_duplicate_post_allow', true, $post ) ) {
			return new WP_Error(
				'codeweber_duplicate_post_denied',
				esc_html__( 'Duplication of this item is not allowed.', 'codeweber' )
			);
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->create_posts ) ) {
			return new WP_Error(
				'codeweber_duplicate_post_forbidden',
				esc_html__( 'You are not allowed to create items of this type.', 'codeweber' )
			);
		}

		// Forked dependency forms (see get_or_fork_form()) mirror the original
		// form's status so they work immediately; a directly-duplicated record
		// (including a form duplicated from its own list) always becomes a draft.
		$is_form_fork = ( 'codeweber_form' === $post->post_type ) && ! $is_top_level;
		$status       = $is_form_fork ? $post->post_status : 'draft';

		$new_post = apply_filters( 'codeweber_duplicate_post_new_post_args', [
			'post_title'            => codeweber_duplicate_post_unique_title( $post->post_title, $post->post_type ),
			'post_content'          => $post->post_content, // raw — rewritten below, phase 2
			'post_content_filtered' => $post->post_content_filtered,
			'post_excerpt'          => $post->post_excerpt,
			'post_status'           => $status,
			'post_type'             => $post->post_type,
			'post_author'           => get_current_user_id(),
			'post_parent'           => $post->post_parent,
			'post_password'         => $post->post_password,
			'comment_status'        => $post->comment_status,
			'ping_status'           => $post->ping_status,
			'menu_order'            => $post->menu_order,
		], $post );

		$new_id = wp_insert_post( wp_slash( $new_post ), true );
		if ( is_wp_error( $new_id ) ) {
			return $new_id;
		}
		$ctx['created_ids'][] = $new_id;

		// Register self-mapping BEFORE rewriting content — a form's own default
		// content self-references its own ID (see codeweber-forms-cpt.php), so
		// without this the rewrite below would try to fork this same form again
		// and recurse forever.
		if ( 'codeweber_form' === $post->post_type ) {
			$ctx['fork_map'][ $post->ID ] = $new_id;
		}

		$rewritten = codeweber_duplicate_post_rewrite_form_refs( $post->post_content, $ctx );
		if ( is_wp_error( $rewritten ) ) {
			return $rewritten; // rollback of $ctx['created_ids'] is the caller's job
		}
		if ( $rewritten !== $post->post_content ) {
			wp_update_post( [
				'ID'           => $new_id,
				'post_content' => $rewritten,
			] );
		}

		codeweber_duplicate_post_copy_meta( $new_id, $post->ID, $post->post_type );
		codeweber_duplicate_post_copy_taxonomies( $new_id, $post );
		add_post_meta( $new_id, '_cw_duplicate_of', $post->ID );

		do_action( 'codeweber_duplicate_post_after_duplicate', $new_id, $post );

		return $new_id;
	}
}

if ( ! function_exists( 'codeweber_duplicate_post_duplicate_top_level' ) ) {
	/**
	 * The single entry point for a user-initiated (top-level) duplication —
	 * used by BOTH the real admin_action handler and by tests, so there is
	 * exactly one place that can get the "$ctx must start as null" calling
	 * convention wrong, instead of two call sites that can silently drift
	 * apart (this is what happened before: the handler pre-filled $ctx with
	 * an array, so create_duplicate()'s top-level detection never triggered
	 * in production even though a test calling it with null passed).
	 *
	 * @return array{0: int|WP_Error, 1: array} [ $new_id_or_error, $ctx ]
	 */
	function codeweber_duplicate_post_duplicate_top_level( WP_Post $post ): array {
		$ctx    = null;
		$new_id = codeweber_duplicate_post_create_duplicate( $post, $ctx );
		return [ $new_id, $ctx ];
	}
}

// ---------------------------------------------------------------------------
// Meta
// ---------------------------------------------------------------------------

if ( ! function_exists( 'codeweber_duplicate_post_copy_meta' ) ) {
	function codeweber_duplicate_post_copy_meta( int $new_id, int $original_id, string $post_type ): void {
		$exclude = apply_filters( 'codeweber_duplicate_post_meta_exclude', [
			'_edit_lock',
			'_edit_last',
			'_cw_duplicate_of', // don't chain — a duplicate-of-a-duplicate points at its direct parent only
		], $post_type );

		$all_meta = get_post_meta( $original_id );
		$all_meta = apply_filters( 'codeweber_duplicate_post_meta', $all_meta, $original_id, $post_type );

		foreach ( $all_meta as $key => $values ) {
			if ( in_array( $key, $exclude, true ) ) {
				continue;
			}
			foreach ( $values as $value ) {
				$value = apply_filters(
					'codeweber_duplicate_post_meta_value',
					maybe_unserialize( $value ),
					$key,
					$post_type
				);
				add_post_meta( $new_id, $key, $value );
			}
		}
	}
}

// ---------------------------------------------------------------------------
// Taxonomies
// ---------------------------------------------------------------------------

if ( ! function_exists( 'codeweber_duplicate_post_copy_taxonomies' ) ) {
	function codeweber_duplicate_post_copy_taxonomies( int $new_id, WP_Post $post ): void {
		$taxonomies = get_object_taxonomies( $post->post_type );
		$taxonomies = apply_filters( 'codeweber_duplicate_post_taxonomies', $taxonomies, $post );

		foreach ( $taxonomies as $taxonomy ) {
			$term_ids = wp_get_object_terms( $post->ID, $taxonomy, [ 'fields' => 'ids' ] );
			if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
				continue;
			}
			wp_set_object_terms( $new_id, array_map( 'intval', $term_ids ), $taxonomy );
		}
	}
}

// ---------------------------------------------------------------------------
// Form forking (codeweber-blocks/form, form-selector, [codeweber_form] shortcodes)
// ---------------------------------------------------------------------------

if ( ! function_exists( 'codeweber_duplicate_post_walk_blocks' ) ) {
	function codeweber_duplicate_post_walk_blocks( array $blocks, callable $callback ): array {
		foreach ( $blocks as &$block ) {
			$block = $callback( $block );
			if ( ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = codeweber_duplicate_post_walk_blocks( $block['innerBlocks'], $callback );
			}
		}
		unset( $block );
		return $blocks;
	}
}

if ( ! function_exists( 'codeweber_duplicate_post_get_or_fork_form' ) ) {
	/**
	 * @return int|WP_Error
	 */
	function codeweber_duplicate_post_get_or_fork_form( int $old_form_id, array &$ctx ) {
		if ( isset( $ctx['fork_map'][ $old_form_id ] ) ) {
			return $ctx['fork_map'][ $old_form_id ]; // already forked (or self-mapping) — reuse
		}

		$form_post = get_post( $old_form_id );
		if ( ! $form_post || 'codeweber_form' !== $form_post->post_type ) {
			return $old_form_id; // not a form / not found — leave the reference alone, not an error
		}

		$new_id = codeweber_duplicate_post_create_duplicate( $form_post, $ctx );
		if ( is_wp_error( $new_id ) ) {
			return $new_id; // propagate — caller rolls back everything in $ctx['created_ids']
		}

		$ctx['fork_map'][ $old_form_id ] = $new_id;
		return $new_id;
	}
}

if ( ! function_exists( 'codeweber_duplicate_post_rewrite_form_refs' ) ) {
	/**
	 * @param string $content
	 * @param array  $ctx
	 * @return string|WP_Error
	 */
	function codeweber_duplicate_post_rewrite_form_refs( string $content, array &$ctx ) {
		if ( ! has_block( 'codeweber-blocks/form', $content )
			&& ! has_block( 'codeweber-blocks/form-selector', $content )
			&& ! has_shortcode( $content, 'codeweber_form' )
			&& ! has_shortcode( $content, 'codeweber_form_steps' )
		) {
			return $content; // nothing form-related — leave content untouched, no reserialization
		}

		$error  = null;
		$blocks = codeweber_duplicate_post_walk_blocks(
			parse_blocks( $content ),
			function ( array $block ) use ( &$ctx, &$error ) {
				if ( $error ) {
					return $block;
				}
				if ( in_array( $block['blockName'], [ 'codeweber-blocks/form', 'codeweber-blocks/form-selector' ], true )
					&& ! empty( $block['attrs']['formId'] )
				) {
					$result = codeweber_duplicate_post_get_or_fork_form( (int) $block['attrs']['formId'], $ctx );
					if ( is_wp_error( $result ) ) {
						$error = $result;
						return $block;
					}
					$block['attrs']['formId'] = (string) $result;
				}
				return $block;
			}
		);
		if ( $error ) {
			return $error;
		}
		$content = serialize_blocks( $blocks );

		// [codeweber_form id="N"] — numeric post ID (not the _steps variant: "\s"
		// right after "codeweber_form" fails to match "_steps", so this is safe).
		$content = preg_replace_callback(
			'/\[codeweber_form\s+([^\]]*\bid=["\'])(\d+)(["\'][^\]]*)\]/',
			function ( array $m ) use ( &$ctx, &$error ) {
				if ( $error ) {
					return $m[0];
				}
				$result = codeweber_duplicate_post_get_or_fork_form( (int) $m[2], $ctx );
				if ( is_wp_error( $result ) ) {
					$error = $result;
					return $m[0];
				}
				return '[codeweber_form ' . $m[1] . $result . $m[3] . ']';
			},
			$content
		);
		if ( $error ) {
			return $error;
		}

		// [codeweber_form_steps id="form-N"] — only the auto-generated fast-path
		// pattern; a custom (non "form-{id}") Block ID is left untouched, see
		// doc_claude/integrations/DUPLICATE_POST.md §10.
		$content = preg_replace_callback(
			'/\[codeweber_form_steps\s+([^\]]*\bid=["\'])form-(\d+)(["\'][^\]]*)\]/',
			function ( array $m ) use ( &$ctx, &$error ) {
				if ( $error ) {
					return $m[0];
				}
				$result = codeweber_duplicate_post_get_or_fork_form( (int) $m[2], $ctx );
				if ( is_wp_error( $result ) ) {
					$error = $result;
					return $m[0];
				}
				return '[codeweber_form_steps ' . $m[1] . 'form-' . $result . $m[3] . ']';
			},
			$content
		);

		return $error ?: $content;
	}
}

// ---------------------------------------------------------------------------
// UI: "Duplicate" row action
// ---------------------------------------------------------------------------

if ( ! function_exists( 'codeweber_duplicate_post_add_row_action' ) ) {
	add_filter( 'post_row_actions', 'codeweber_duplicate_post_add_row_action', 10, 2 );
	add_filter( 'page_row_actions', 'codeweber_duplicate_post_add_row_action', 10, 2 );

	function codeweber_duplicate_post_add_row_action( array $actions, WP_Post $post ): array {
		if ( ! in_array( $post->post_type, codeweber_duplicate_post_get_post_types(), true ) ) {
			return $actions;
		}
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->create_posts ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			add_query_arg( [
				'action' => 'codeweber_duplicate_post',
				'post'   => $post->ID,
			], admin_url( 'admin.php' ) ),
			'codeweber_duplicate_post_' . $post->ID
		);

		$actions['codeweber_duplicate'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			esc_url( $url ),
			esc_attr( sprintf(
				/* translators: %s: post title */
				__( 'Duplicate "%s"', 'codeweber' ),
				$post->post_title
			) ),
			esc_html__( 'Duplicate', 'codeweber' )
		);

		return $actions;
	}
}

// ---------------------------------------------------------------------------
// Handler
// ---------------------------------------------------------------------------

if ( ! function_exists( 'codeweber_duplicate_post_handle_action' ) ) {
	add_action( 'admin_action_codeweber_duplicate_post', 'codeweber_duplicate_post_handle_action' );

	function codeweber_duplicate_post_handle_action(): void {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		check_admin_referer( 'codeweber_duplicate_post_' . $post_id );

		$post = get_post( $post_id );
		if ( ! $post || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die(
				esc_html__( 'You are not allowed to duplicate this item.', 'codeweber' ),
				esc_html__( 'Access denied', 'codeweber' ),
				[ 'response' => 403 ]
			);
		}

		[ $new_id, $ctx ] = codeweber_duplicate_post_duplicate_top_level( $post );

		if ( is_wp_error( $new_id ) ) {
			foreach ( $ctx['created_ids'] as $created_id ) {
				wp_delete_post( $created_id, true );
			}
			wp_die(
				esc_html( $new_id->get_error_message() ),
				esc_html__( 'Duplication failed', 'codeweber' ),
				[ 'response' => 500, 'back_link' => true ]
			);
		}

		$redirect = add_query_arg(
			[
				'post_type'     => 'post' === $post->post_type ? false : $post->post_type,
				'cw_duplicated' => $new_id,
			],
			admin_url( 'edit.php' )
		);
		wp_safe_redirect( $redirect );
		exit;
	}
}

// ---------------------------------------------------------------------------
// Admin notice
// ---------------------------------------------------------------------------

if ( ! function_exists( 'codeweber_duplicate_post_admin_notice' ) ) {
	add_action( 'admin_notices', 'codeweber_duplicate_post_admin_notice' );

	function codeweber_duplicate_post_admin_notice(): void {
		$new_id = isset( $_GET['cw_duplicated'] ) ? absint( $_GET['cw_duplicated'] ) : 0;
		if ( ! $new_id ) {
			return;
		}

		$new_post = get_post( $new_id );
		if ( ! $new_post || ! current_user_can( 'edit_post', $new_id ) ) {
			return;
		}

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s %s</p></div>',
			esc_html__( 'Item duplicated. The copy was saved as a draft.', 'codeweber' ),
			sprintf(
				'<a href="%s">%s</a>',
				esc_url( get_edit_post_link( $new_id, 'raw' ) ),
				esc_html__( 'Edit the duplicate', 'codeweber' )
			)
		);

		// The "is-dismissible" × button only hides this DOM node for the current
		// page view — it doesn't touch the URL. As long as ?cw_duplicated=N sits
		// in the address bar, admin list-table links built from the current URL
		// (row actions, bulk-action redirects) keep carrying it forward, so the
		// notice keeps reappearing even after being dismissed. Strip it once,
		// client-side, right after rendering.
		?>
		<script>
		( function () {
			if ( ! window.history || ! window.history.replaceState ) {
				return;
			}
			var url = new URL( window.location.href );
			url.searchParams.delete( 'cw_duplicated' );
			window.history.replaceState( {}, document.title, url.toString() );
		}() );
		</script>
		<?php
	}
}
