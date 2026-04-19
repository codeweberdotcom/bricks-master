<?php

namespace Codeweber\S3Storage\Admin;

use Codeweber\S3Storage\Client;
use Codeweber\S3Storage\DB\ItemsTable;
use Codeweber\S3Storage\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MediaLibrary {

	private $prefetch = [];

	public function register() {
		add_filter( 'manage_media_columns', [ $this, 'add_column' ] );
		add_action( 'manage_media_custom_column', [ $this, 'render_column' ], 10, 2 );
		add_filter( 'manage_upload_sortable_columns', [ $this, 'make_sortable' ] );
		add_action( 'pre_get_posts', [ $this, 'handle_sort' ] );
		add_filter( 'attachment_fields_to_edit', [ $this, 'add_grid_fields' ], 10, 2 );
		add_action( 'pre_get_posts', [ $this, 'prefetch_items' ], 5 );
	}

	public function add_column( $columns ) {
		$columns['cws3_storage'] = __( 'Storage', 'codeweber-s3-storage' );
		return $columns;
	}

	public function render_column( $column, $post_id ) {
		if ( $column !== 'cws3_storage' ) {
			return;
		}
		$rows  = $this->rows_for( (int) $post_id );
		$badge = self::badge_for( $rows );
		echo $badge; // badge HTML is self-escaped below
	}

	public function make_sortable( $columns ) {
		$columns['cws3_storage'] = 'cws3_storage';
		return $columns;
	}

	public function handle_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( $query->get( 'orderby' ) !== 'cws3_storage' ) {
			return;
		}
		global $wpdb;
		$query->set( 'meta_query', [] );
		$order = strtoupper( $query->get( 'order' ) ) === 'ASC' ? 'ASC' : 'DESC';
		add_filter( 'posts_clauses', function ( $clauses ) use ( $wpdb, $order ) {
			$table            = ItemsTable::name();
			$clauses['join'] .= " LEFT JOIN (SELECT attachment_id, MAX(is_offloaded) as off FROM {$table} GROUP BY attachment_id) cws3 ON cws3.attachment_id = {$wpdb->posts}.ID";
			$clauses['orderby'] = 'cws3.off ' . $order . ', ' . $wpdb->posts . '.ID DESC';
			return $clauses;
		} );
	}

	public function prefetch_items( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || $query->get( 'post_type' ) !== 'attachment' ) {
			return;
		}
		add_action( 'the_posts', function ( $posts ) {
			if ( ! empty( $posts ) ) {
				$ids            = array_map( function ( $p ) { return (int) $p->ID; }, $posts );
				$this->prefetch = ItemsTable::get_by_attachments( $ids );
			}
			return $posts;
		} );
	}

	public function add_grid_fields( $fields, $post ) {
		$rows = $this->rows_for( (int) $post->ID );
		$badge = self::badge_for( $rows );

		$html = '<div class="cws3-grid-info">' . $badge;
		if ( ! empty( $rows ) ) {
			$settings = Settings::get();
			foreach ( $rows as $row ) {
				if ( (int) $row->is_offloaded !== 1 ) {
					continue;
				}
				if ( $row->source_type === 'original' ) {
					$url = Client::public_url_for_key( $settings, $row->object_key );
					$html .= '<div class="cws3-kv"><strong>' . esc_html__( 'Bucket', 'codeweber-s3-storage' ) . ':</strong> ' . esc_html( $row->bucket ) . '</div>';
					$html .= '<div class="cws3-kv"><strong>' . esc_html__( 'Key', 'codeweber-s3-storage' ) . ':</strong> <code>' . esc_html( $row->object_key ) . '</code></div>';
					$html .= '<div class="cws3-kv"><strong>' . esc_html__( 'S3 URL', 'codeweber-s3-storage' ) . ':</strong> <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $url ) . '</a></div>';
					break;
				}
			}
		}
		$html .= '</div>';

		$fields['cws3_storage'] = [
			'label' => __( 'S3 Storage', 'codeweber-s3-storage' ),
			'input' => 'html',
			'html'  => $html,
		];
		return $fields;
	}

	private function rows_for( int $attachment_id ) {
		if ( isset( $this->prefetch[ $attachment_id ] ) ) {
			return $this->prefetch[ $attachment_id ];
		}
		return ItemsTable::get_by_attachment( $attachment_id );
	}

	public static function badge_for( $rows ) {
		$status = ItemsTable::summarize_status( is_array( $rows ) ? $rows : [] );
		$map    = [
			'local'          => [ 'Local', 'cws3-badge cws3-badge-local' ],
			'mirror'         => [ 'Mirror', 'cws3-badge cws3-badge-mirror' ],
			'mirror_partial' => [ 'Mirror (partial)', 'cws3-badge cws3-badge-mirror' ],
			's3'             => [ 'S3', 'cws3-badge cws3-badge-s3' ],
		];
		$row = $map[ $status ] ?? $map['local'];
		return '<span class="' . esc_attr( $row[1] ) . '" data-cws3-status="' . esc_attr( $status ) . '">' . esc_html( $row[0] ) . '</span>';
	}
}
