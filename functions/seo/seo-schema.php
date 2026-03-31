<?php
/**
 * Schema.org JSON-LD — base schemas for all pages.
 *
 * Outputs WebSite, Organization, BreadcrumbList and WebPage.
 * CPT-specific schemas (Article, Event, Person, etc.) are added
 * via filters in separate files.
 *
 * @package codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collect and output all Schema.org JSON-LD graphs.
 *
 * Each schema piece is a separate @graph node. Other modules
 * can add CPT-specific schemas via the codeweber_schema_graph filter.
 */
add_action( 'wp_footer', function (): void {
	$graph = [];

	$site_url  = trailingslashit( home_url() );
	$site_name = get_bloginfo( 'name' );

	// ── WebSite ───────────────────────────────────────────────────────────────
	$website = [
		'@type' => 'WebSite',
		'@id'   => $site_url . '#website',
		'url'   => $site_url,
		'name'  => $site_name,
	];

	$search_url = home_url( '/?s={search_term_string}' );
	$website['potentialAction'] = [
		'@type'       => 'SearchAction',
		'target'      => [
			'@type'       => 'EntryPoint',
			'urlTemplate' => $search_url,
		],
		'query-input' => 'required name=search_term_string',
	];

	$graph[] = $website;

	// ── Organization ──────────────────────────────────────────────────────────
	$org = codeweber_schema_organization( $site_url );
	if ( $org ) {
		$graph[] = $org;
	}

	// ── BreadcrumbList ────────────────────────────────────────────────────────
	$breadcrumbs = codeweber_schema_breadcrumblist();
	if ( $breadcrumbs ) {
		$graph[] = $breadcrumbs;
	}

	// ── WebPage ───────────────────────────────────────────────────────────────
	$webpage = codeweber_schema_webpage( $site_url );
	if ( $webpage ) {
		$graph[] = $webpage;
	}

	/**
	 * Filter the JSON-LD @graph array.
	 *
	 * CPT-specific schema modules append their nodes here.
	 *
	 * @param array $graph Array of schema nodes.
	 */
	$graph = apply_filters( 'codeweber_schema_graph', $graph );

	if ( empty( $graph ) ) {
		return;
	}

	$output = [
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	];

	printf(
		"\n" . '<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT )
	);
}, 20 );

/**
 * Build Organization schema from Redux settings.
 *
 * @param string $site_url Site URL with trailing slash.
 * @return array|null Organization schema node or null.
 */
function codeweber_schema_organization( string $site_url ): ?array {
	$name = Codeweber_Options::get( 'legal_entity_short', '' );

	if ( empty( $name ) ) {
		$name = get_bloginfo( 'name' );
	}

	$org = [
		'@type' => 'Organization',
		'@id'   => $site_url . '#organization',
		'name'  => $name,
		'url'   => $site_url,
	];

	// Description.
	$desc = Codeweber_Options::get( 'text-about-company', '' );
	if ( ! empty( $desc ) ) {
		$org['description'] = wp_strip_all_tags( $desc );
	}

	// Logo.
	$logo = Codeweber_Options::get( 'opt-dark-logo', [] );
	if ( ! empty( $logo['url'] ) ) {
		$org['logo'] = [
			'@type' => 'ImageObject',
			'url'   => $logo['url'],
		];
		if ( ! empty( $logo['id'] ) ) {
			$meta = wp_get_attachment_metadata( $logo['id'] );
			if ( $meta ) {
				$org['logo']['width']  = $meta['width'] ?? 0;
				$org['logo']['height'] = $meta['height'] ?? 0;
			}
		}
		$org['image'] = $org['logo'];
	}

	// Contact.
	$phone = Codeweber_Options::get( 'phone_01', '' );
	if ( ! empty( $phone ) ) {
		$org['telephone'] = $phone;
	}

	$email = Codeweber_Options::get( 'e-mail', '' );
	if ( ! empty( $email ) ) {
		$org['email'] = $email;
	}

	// Address.
	$address = codeweber_schema_postal_address( 'juri' );
	if ( $address ) {
		$org['address'] = $address;
	}

	// Social links (sameAs).
	$same_as = codeweber_schema_same_as();
	if ( ! empty( $same_as ) ) {
		$org['sameAs'] = $same_as;
	}

	return $org;
}

/**
 * Build PostalAddress from Redux address fields.
 *
 * @param string $prefix Field prefix: 'juri' (legal) or 'fact' (actual).
 * @return array|null PostalAddress schema or null.
 */
function codeweber_schema_postal_address( string $prefix ): ?array {
	$street  = Codeweber_Options::get( $prefix . '-street', '' );
	$city    = Codeweber_Options::get( $prefix . '-city', '' );

	if ( empty( $street ) && empty( $city ) ) {
		return null;
	}

	$address = [
		'@type' => 'PostalAddress',
	];

	$house  = Codeweber_Options::get( $prefix . '-house', '' );
	$office = Codeweber_Options::get( $prefix . '-office', '' );

	$street_full = $street;
	if ( ! empty( $house ) ) {
		$street_full .= ', ' . $house;
	}
	if ( ! empty( $office ) ) {
		$street_full .= ', ' . $office;
	}

	if ( ! empty( $street_full ) ) {
		$address['streetAddress'] = $street_full;
	}
	if ( ! empty( $city ) ) {
		$address['addressLocality'] = $city;
	}

	$region = Codeweber_Options::get( $prefix . '-region', '' );
	if ( ! empty( $region ) ) {
		$address['addressRegion'] = $region;
	}

	$postal = Codeweber_Options::get( $prefix . '-postal', '' );
	if ( ! empty( $postal ) ) {
		$address['postalCode'] = $postal;
	}

	$country = Codeweber_Options::get( $prefix . '-country', '' );
	if ( ! empty( $country ) ) {
		$address['addressCountry'] = $country;
	}

	return $address;
}

/**
 * Collect social profile URLs from Redux for sameAs.
 *
 * @return string[] Array of non-empty social URLs.
 */
function codeweber_schema_same_as(): array {
	$keys = [
		'facebook', 'instagram', 'twitter', 'linkedin', 'youtube',
		'tiktok', 'telegram', 'vk', 'github', 'pinterest',
		'vimeo', 'odnoklassniki', 'rutube', 'yandex-dzen',
	];

	$urls = [];

	foreach ( $keys as $key ) {
		$url = Codeweber_Options::get( $key, '' );
		if ( ! empty( $url ) ) {
			$urls[] = $url;
		}
	}

	return $urls;
}

/**
 * Build BreadcrumbList schema from the current page context.
 *
 * @return array|null BreadcrumbList schema node or null.
 */
function codeweber_schema_breadcrumblist(): ?array {
	$crumbs = [];
	$pos    = 1;

	// Home.
	$crumbs[] = [
		'@type'    => 'ListItem',
		'position' => $pos++,
		'name'     => __( 'Home', 'codeweber' ),
		'item'     => home_url( '/' ),
	];

	if ( is_front_page() ) {
		return null; // No breadcrumbs on home page.
	}

	if ( is_singular() ) {
		$post_type = get_post_type();

		// Archive link for CPTs with archive.
		if ( 'post' !== $post_type && 'page' !== $post_type ) {
			$post_type_obj = get_post_type_object( $post_type );
			if ( $post_type_obj && $post_type_obj->has_archive ) {
				$crumbs[] = [
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => $post_type_obj->labels->name,
					'item'     => get_post_type_archive_link( $post_type ),
				];
			}
		}

		// Parent pages.
		if ( 'page' === $post_type ) {
			$parent_id = wp_get_post_parent_id( get_the_ID() );
			$parents   = [];

			while ( $parent_id ) {
				$parents[]  = [
					'name' => get_the_title( $parent_id ),
					'item' => get_permalink( $parent_id ),
				];
				$parent_id = wp_get_post_parent_id( $parent_id );
			}

			$parents = array_reverse( $parents );
			foreach ( $parents as $parent ) {
				$crumbs[] = [
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => $parent['name'],
					'item'     => $parent['item'],
				];
			}
		}

		// Category for posts.
		if ( 'post' === $post_type ) {
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				$cat = $categories[0];
				$crumbs[] = [
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => $cat->name,
					'item'     => get_category_link( $cat->term_id ),
				];
			}
		}

		// WooCommerce product category.
		if ( 'product' === $post_type && function_exists( 'wc_get_page_id' ) ) {
			$shop_id = wc_get_page_id( 'shop' );
			if ( $shop_id > 0 ) {
				$crumbs[] = [
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => get_the_title( $shop_id ),
					'item'     => get_permalink( $shop_id ),
				];
			}

			$terms = get_the_terms( get_the_ID(), 'product_cat' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$term = $terms[0];
				$crumbs[] = [
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => $term->name,
					'item'     => get_term_link( $term ),
				];
			}
		}

		// Current page (last item — no 'item' URL per Google spec).
		$crumbs[] = [
			'@type'    => 'ListItem',
			'position' => $pos,
			'name'     => get_the_title(),
		];

	} elseif ( is_post_type_archive() ) {
		$post_type_obj = get_queried_object();
		if ( $post_type_obj ) {
			$crumbs[] = [
				'@type'    => 'ListItem',
				'position' => $pos,
				'name'     => $post_type_obj->labels->name ?? $post_type_obj->label,
			];
		}

	} elseif ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		if ( $term ) {
			$crumbs[] = [
				'@type'    => 'ListItem',
				'position' => $pos,
				'name'     => $term->name,
			];
		}

	} elseif ( is_search() ) {
		$crumbs[] = [
			'@type'    => 'ListItem',
			'position' => $pos,
			'name'     => sprintf( __( 'Search results for "%s"', 'codeweber' ), get_search_query() ),
		];
	}

	if ( count( $crumbs ) < 2 ) {
		return null;
	}

	return [
		'@type'           => 'BreadcrumbList',
		'@id'             => get_permalink() . '#breadcrumb',
		'itemListElement' => $crumbs,
	];
}

/**
 * Build WebPage schema for the current page.
 *
 * @param string $site_url Site URL with trailing slash.
 * @return array|null WebPage schema node or null.
 */
function codeweber_schema_webpage( string $site_url ): ?array {
	$url = is_singular() ? get_permalink() : codeweber_schema_current_url();

	if ( empty( $url ) ) {
		return null;
	}

	$page = [
		'@type'      => 'WebPage',
		'@id'        => $url . '#webpage',
		'url'        => $url,
		'name'       => wp_get_document_title(),
		'isPartOf'   => [ '@id' => $site_url . '#website' ],
	];

	// Description.
	if ( is_singular() ) {
		$desc = codeweber_get_seo_description();
		if ( ! empty( $desc ) ) {
			$page['description'] = $desc;
		}

		// Thumbnail.
		$thumb_id = get_post_thumbnail_id();
		if ( $thumb_id ) {
			$image_url = wp_get_attachment_url( $thumb_id );
			if ( $image_url ) {
				$page['primaryImageOfPage'] = [
					'@type' => 'ImageObject',
					'url'   => $image_url,
				];
			}
		}

		// Dates.
		$page['datePublished'] = get_the_date( 'c' );
		$page['dateModified']  = get_the_modified_date( 'c' );
	}

	// Breadcrumb reference.
	$page['breadcrumb'] = [ '@id' => ( $url ?: $site_url ) . '#breadcrumb' ];

	return $page;
}

/**
 * Get the current page URL for non-singular pages.
 *
 * @return string Current URL.
 */
function codeweber_schema_current_url(): string {
	if ( is_post_type_archive() ) {
		$post_type = get_query_var( 'post_type' );
		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}
		return get_post_type_archive_link( $post_type ) ?: '';
	}

	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		return $term ? get_term_link( $term ) : '';
	}

	if ( is_home() ) {
		return get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' );
	}

	return home_url( '/' );
}
