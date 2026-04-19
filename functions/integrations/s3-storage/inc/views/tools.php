<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array $settings */
/** @var array $stats */

$sections = [
	'offload'      => [
		'title'       => __( 'Offload existing media', 'codeweber' ),
		'description' => __( 'Upload local attachments that are not yet in S3.', 'codeweber' ),
	],
	'restore'      => [
		'title'       => __( 'Restore from S3 to local', 'codeweber' ),
		'description' => __( 'Download offloaded attachments back to wp-content/uploads.', 'codeweber' ),
	],
	'delete_local' => [
		'title'       => __( 'Delete local copies', 'codeweber' ),
		'description' => __( 'Remove local files that are already safely stored in S3.', 'codeweber' ),
	],
	'sync'         => [
		'title'       => __( 'Sync', 'codeweber' ),
		'description' => __( 'Find local attachments missing from S3 and upload them.', 'codeweber' ),
	],
	'uninstall'    => [
		'title'       => __( 'Prepare for uninstall', 'codeweber' ),
		'description' => __( 'Download everything back to local and mark the module safe to disable.', 'codeweber' ),
	],
	'wipe'         => [
		'title'       => __( 'Wipe S3', 'codeweber' ),
		'description' => __( 'Delete every offloaded object from the bucket. Records stay in the database with is_offloaded=0. Local files are not touched.', 'codeweber' ),
		'danger'      => true,
	],
];
?>
<div class="wrap cws3-wrap">
	<h1><?php esc_html_e( 'S3 Storage — Tools', 'codeweber' ); ?></h1>

	<table class="widefat" style="max-width:700px; margin-bottom:20px;">
		<tbody>
			<tr><th><?php esc_html_e( 'Total attachments', 'codeweber' ); ?></th><td><?php echo (int) $stats['total']; ?></td></tr>
			<tr><th><?php esc_html_e( 'Offloaded to S3', 'codeweber' ); ?></th><td><?php echo (int) $stats['offloaded']; ?></td></tr>
			<tr><th><?php esc_html_e( 'Stored locally AND in S3', 'codeweber' ); ?></th><td><?php echo (int) $stats['orphan_local']; ?></td></tr>
			<tr><th><?php esc_html_e( 'Only in S3 (no local copy)', 'codeweber' ); ?></th><td><?php echo (int) $stats['remote_only']; ?></td></tr>
			<tr><th><?php esc_html_e( 'Current mode', 'codeweber' ); ?></th><td><code><?php echo esc_html( $settings['storage_mode'] ); ?></code></td></tr>
		</tbody>
	</table>

	<?php foreach ( $sections as $key => $section ) :
		$is_danger = ! empty( $section['danger'] );
		?>
		<div class="cws3-section <?php echo $is_danger ? 'cws3-section-danger' : ''; ?>" data-job-type="<?php echo esc_attr( $key ); ?>" <?php echo $is_danger ? 'data-confirm="1"' : ''; ?>>
			<h2><?php echo esc_html( $section['title'] ); ?></h2>
			<p class="description"><?php echo esc_html( $section['description'] ); ?></p>
			<?php if ( $is_danger && (int) $stats['remote_only'] > 0 ) : ?>
				<div class="notice notice-error inline" style="padding:8px 12px; margin:8px 0;">
					<strong><?php
					/* translators: %d: number of attachments */
					printf( esc_html__( 'Warning: %d files exist only in S3 (no local copy). Wiping will permanently delete them. Run Restore first if you want to keep them.', 'codeweber' ), (int) $stats['remote_only'] );
					?></strong>
				</div>
			<?php endif; ?>
			<p>
				<button type="button" class="button button-primary cws3-start"><?php esc_html_e( 'Start', 'codeweber' ); ?></button>
				<button type="button" class="button cws3-start" data-dry-run="1"><?php esc_html_e( 'Dry-run', 'codeweber' ); ?></button>
				<button type="button" class="button cws3-pause" disabled><?php esc_html_e( 'Pause', 'codeweber' ); ?></button>
				<button type="button" class="button cws3-resume" disabled><?php esc_html_e( 'Resume', 'codeweber' ); ?></button>
				<button type="button" class="button cws3-cancel" disabled><?php esc_html_e( 'Cancel', 'codeweber' ); ?></button>
			</p>
			<div class="cws3-progress" style="display:none;">
				<div class="cws3-progress-bar"><div class="cws3-progress-fill"></div></div>
				<p class="cws3-progress-text"></p>
			</div>
		</div>
	<?php endforeach; ?>
</div>
