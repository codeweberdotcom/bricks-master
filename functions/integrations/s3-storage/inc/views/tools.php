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

	<?php foreach ( $sections as $key => $section ) : ?>
		<div class="cws3-section" data-job-type="<?php echo esc_attr( $key ); ?>">
			<h2><?php echo esc_html( $section['title'] ); ?></h2>
			<p class="description"><?php echo esc_html( $section['description'] ); ?></p>
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
