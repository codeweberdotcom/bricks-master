<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Codeweber\S3Storage\Settings;
use Codeweber\S3Storage\DB\ErrorsTable;

/** @var array $settings */
$sdk_ok = class_exists( 'Aws\\S3\\S3Client' );
?>
<div class="wrap cws3-wrap">
	<h1><?php esc_html_e( 'S3 Storage', 'codeweber-s3-storage' ); ?></h1>

	<?php if ( ! $sdk_ok ) : ?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'AWS SDK is missing. Run `composer install` inside the module directory.', 'codeweber-s3-storage' ); ?></p>
			<p><code><?php echo esc_html( CWS3_MODULE_DIR ); ?></code></p>
		</div>
	<?php endif; ?>

	<p>
		<a href="<?php echo esc_url( admin_url( 'tools.php?page=cws3-tools' ) ); ?>" class="button"><?php esc_html_e( 'Open Tools →', 'codeweber-s3-storage' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'tools.php?page=cws3-logs' ) ); ?>" class="button"><?php esc_html_e( 'Open Logs →', 'codeweber-s3-storage' ); ?></a>
	</p>

	<form method="post" action="options.php">
		<?php settings_fields( 'cws3_settings_group' ); ?>

		<h2><?php esc_html_e( 'Connection', 'codeweber-s3-storage' ); ?></h2>
		<table class="form-table" role="presentation">
			<?php
			$fields = [
				'endpoint'   => __( 'Endpoint URL', 'codeweber-s3-storage' ),
				'region'     => __( 'Region', 'codeweber-s3-storage' ),
				'access_key' => __( 'Access Key', 'codeweber-s3-storage' ),
				'secret_key' => __( 'Secret Key', 'codeweber-s3-storage' ),
				'bucket'     => __( 'Bucket', 'codeweber-s3-storage' ),
				'public_url' => __( 'Public / CDN URL (optional)', 'codeweber-s3-storage' ),
			];
			foreach ( $fields as $key => $label ) :
				$is_const = Settings::is_defined_by_constant( $key );
				$type     = $key === 'secret_key' ? 'password' : 'text';
				$value    = $settings[ $key ];
				?>
				<tr>
					<th scope="row"><label for="cws3-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
					<td>
						<input
							type="<?php echo esc_attr( $type ); ?>"
							id="cws3-<?php echo esc_attr( $key ); ?>"
							name="cws3_settings[<?php echo esc_attr( $key ); ?>]"
							value="<?php echo esc_attr( $value ); ?>"
							class="regular-text"
							<?php echo $is_const ? 'readonly' : ''; ?>
							autocomplete="off"
						/>
						<?php if ( $is_const ) : ?>
							<p class="description"><?php esc_html_e( 'Defined in wp-config.php (read-only).', 'codeweber-s3-storage' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>

			<tr>
				<th scope="row"><?php esc_html_e( 'Path-style URLs', 'codeweber-s3-storage' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="cws3_settings[path_style]" value="1" <?php checked( $settings['path_style'] ); ?> />
						<?php esc_html_e( 'Use path-style endpoint (required for MinIO and most S3-compatible servers).', 'codeweber-s3-storage' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Verify SSL', 'codeweber-s3-storage' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="cws3_settings[verify_ssl]" value="1" <?php checked( $settings['verify_ssl'] ); ?> />
						<?php esc_html_e( 'Verify TLS certificate (disable for self-signed dev servers).', 'codeweber-s3-storage' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cws3-key_prefix"><?php esc_html_e( 'Key prefix', 'codeweber-s3-storage' ); ?></label></th>
				<td>
					<input type="text" id="cws3-key_prefix" name="cws3_settings[key_prefix]" value="<?php echo esc_attr( $settings['key_prefix'] ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Placeholders: {year}, {month}, {day}. Default: {year}/{month}/', 'codeweber-s3-storage' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<button type="button" class="button" id="cws3-test-connection"><?php esc_html_e( 'Test connection', 'codeweber-s3-storage' ); ?></button>
					<span id="cws3-test-result" style="margin-left:10px;"></span>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Storage mode', 'codeweber-s3-storage' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Mode', 'codeweber-s3-storage' ); ?></th>
				<td>
					<?php
					$modes = [
						'local'  => __( 'Local only — new uploads stay on the server (S3 disabled).', 'codeweber-s3-storage' ),
						's3'     => __( 'S3 only — upload to S3, remove local copy after success.', 'codeweber-s3-storage' ),
						'mirror' => __( 'Mirror — keep a local copy AND upload to S3 (safest).', 'codeweber-s3-storage' ),
					];
					foreach ( $modes as $mode => $label ) : ?>
						<label style="display:block; margin-bottom:6px;">
							<input type="radio" name="cws3_settings[storage_mode]" value="<?php echo esc_attr( $mode ); ?>" <?php checked( $settings['storage_mode'], $mode ); ?> />
							<?php echo esc_html( $label ); ?>
						</label>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Force HTTPS', 'codeweber-s3-storage' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="cws3_settings[force_https]" value="1" <?php checked( $settings['force_https'] ); ?> />
						<?php esc_html_e( 'Always output HTTPS URLs for offloaded media.', 'codeweber-s3-storage' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Rewrite post content', 'codeweber-s3-storage' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="cws3_settings[rewrite_content]" value="1" <?php checked( $settings['rewrite_content'] ); ?> />
						<?php esc_html_e( 'Rewrite hard-coded URLs in post_content (heavier on large sites).', 'codeweber-s3-storage' ); ?>
					</label>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Logging', 'codeweber-s3-storage' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="cws3-log_level"><?php esc_html_e( 'Log level', 'codeweber-s3-storage' ); ?></label></th>
				<td>
					<select id="cws3-log_level" name="cws3_settings[log_level]">
						<?php foreach ( [ 'off', 'error', 'info', 'debug' ] as $level ) : ?>
							<option value="<?php echo esc_attr( $level ); ?>" <?php selected( $settings['log_level'], $level ); ?>><?php echo esc_html( $level ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Logs are stored in the module folder: functions/integrations/s3-storage/logs/', 'codeweber-s3-storage' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cws3-log_retention"><?php esc_html_e( 'Retention (days)', 'codeweber-s3-storage' ); ?></label></th>
				<td>
					<input type="number" id="cws3-log_retention" name="cws3_settings[log_retention]" value="<?php echo esc_attr( $settings['log_retention'] ); ?>" min="1" max="90" class="small-text" />
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>

	<h2><?php esc_html_e( 'Error log (database)', 'codeweber-s3-storage' ); ?></h2>
	<p>
		<button type="button" class="button" id="cws3-clear-errors">
			<?php
			/* translators: %d: number of log rows */
			printf( esc_html__( 'Clear DB log (%d)', 'codeweber-s3-storage' ), ErrorsTable::count() );
			?>
		</button>
		<a href="<?php echo esc_url( admin_url( 'tools.php?page=cws3-logs' ) ); ?>" class="button"><?php esc_html_e( 'Open file-based logs →', 'codeweber-s3-storage' ); ?></a>
	</p>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Time', 'codeweber-s3-storage' ); ?></th>
				<th><?php esc_html_e( 'Operation', 'codeweber-s3-storage' ); ?></th>
				<th><?php esc_html_e( 'Attachment', 'codeweber-s3-storage' ); ?></th>
				<th><?php esc_html_e( 'Message', 'codeweber-s3-storage' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php $rows = ErrorsTable::recent( 50 ); ?>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="4"><?php esc_html_e( 'No errors logged.', 'codeweber-s3-storage' ); ?></td></tr>
			<?php endif; ?>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><?php echo esc_html( $row->created_at ); ?></td>
					<td><code><?php echo esc_html( $row->operation ); ?></code></td>
					<td><?php echo $row->attachment_id ? (int) $row->attachment_id : '—'; ?></td>
					<td><?php echo esc_html( $row->error_message ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
