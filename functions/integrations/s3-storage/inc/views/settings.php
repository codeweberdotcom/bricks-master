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
	<h1><?php esc_html_e( 'S3 Storage', 'codeweber' ); ?></h1>

	<?php if ( ! $sdk_ok ) : ?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'AWS SDK is missing. Run `composer install` inside the module directory.', 'codeweber' ); ?></p>
			<p><code><?php echo esc_html( CWS3_MODULE_DIR ); ?></code></p>
		</div>
	<?php endif; ?>

	<p>
		<a href="<?php echo esc_url( admin_url( 'tools.php?page=cws3-tools' ) ); ?>" class="button"><?php esc_html_e( 'Open Tools →', 'codeweber' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'tools.php?page=cws3-logs' ) ); ?>" class="button"><?php esc_html_e( 'Open Logs →', 'codeweber' ); ?></a>
	</p>

	<form method="post" action="options.php">
		<?php settings_fields( 'cws3_settings_group' ); ?>

		<h2><?php esc_html_e( 'Connection', 'codeweber' ); ?></h2>
		<table class="form-table" role="presentation">
			<?php
			$fields = [
				'endpoint'   => __( 'Endpoint URL', 'codeweber' ),
				'region'     => __( 'Region', 'codeweber' ),
				'access_key' => __( 'Access Key', 'codeweber' ),
				'secret_key' => __( 'Secret Key', 'codeweber' ),
				'bucket'     => __( 'Bucket', 'codeweber' ),
				'public_url' => __( 'Public / CDN URL (optional)', 'codeweber' ),
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
							<p class="description"><?php esc_html_e( 'Defined in wp-config.php (read-only).', 'codeweber' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>

			<tr>
				<th scope="row"><?php esc_html_e( 'Path-style URLs', 'codeweber' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="cws3_settings[path_style]" value="1" <?php checked( $settings['path_style'] ); ?> />
						<?php esc_html_e( 'Use path-style endpoint (required for MinIO and most S3-compatible servers).', 'codeweber' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Verify SSL', 'codeweber' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="cws3_settings[verify_ssl]" value="1" <?php checked( $settings['verify_ssl'] ); ?> />
						<?php esc_html_e( 'Verify TLS certificate (disable for self-signed dev servers).', 'codeweber' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cws3-key_prefix"><?php esc_html_e( 'Key prefix', 'codeweber' ); ?></label></th>
				<td>
					<input type="text" id="cws3-key_prefix" name="cws3_settings[key_prefix]" value="<?php echo esc_attr( $settings['key_prefix'] ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Placeholders: {year}, {month}, {day}. Default: {year}/{month}/', 'codeweber' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<button type="button" class="button" id="cws3-test-connection"><?php esc_html_e( 'Test connection', 'codeweber' ); ?></button>
					<span id="cws3-test-result" style="margin-left:10px;"></span>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Storage mode', 'codeweber' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Mode', 'codeweber' ); ?></th>
				<td>
					<?php
					$modes = [
						'local'  => __( 'Local only — new uploads stay on the server (S3 disabled).', 'codeweber' ),
						's3'     => __( 'S3 only — upload to S3, remove local copy after success.', 'codeweber' ),
						'mirror' => __( 'Mirror — keep a local copy AND upload to S3 (safest).', 'codeweber' ),
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
				<th scope="row"><?php esc_html_e( 'Force HTTPS', 'codeweber' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="cws3_settings[force_https]" value="1" <?php checked( $settings['force_https'] ); ?> />
						<?php esc_html_e( 'Always output HTTPS URLs for offloaded media.', 'codeweber' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Rewrite post content', 'codeweber' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="cws3_settings[rewrite_content]" value="1" <?php checked( $settings['rewrite_content'] ); ?> />
						<?php esc_html_e( 'Rewrite hard-coded URLs in post_content (heavier on large sites).', 'codeweber' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cws3-cache_max_age"><?php esc_html_e( 'Cache-Control max-age (seconds)', 'codeweber' ); ?></label></th>
				<td>
					<input type="number" id="cws3-cache_max_age" name="cws3_settings[cache_max_age]" value="<?php echo esc_attr( $settings['cache_max_age'] ); ?>" min="0" max="31536000" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Sent as Cache-Control header on every offloaded object. Default 31536000 = 1 year. 0 disables the header. Existing objects can be updated via Tools → Re-apply cache headers.', 'codeweber' ); ?></p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Logging', 'codeweber' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="cws3-log_level"><?php esc_html_e( 'Log level', 'codeweber' ); ?></label></th>
				<td>
					<select id="cws3-log_level" name="cws3_settings[log_level]">
						<?php foreach ( [ 'off', 'error', 'info', 'debug' ] as $level ) : ?>
							<option value="<?php echo esc_attr( $level ); ?>" <?php selected( $settings['log_level'], $level ); ?>><?php echo esc_html( $level ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Logs are stored in the module folder: functions/integrations/s3-storage/logs/', 'codeweber' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cws3-log_retention"><?php esc_html_e( 'Retention (days)', 'codeweber' ); ?></label></th>
				<td>
					<input type="number" id="cws3-log_retention" name="cws3_settings[log_retention]" value="<?php echo esc_attr( $settings['log_retention'] ); ?>" min="1" max="90" class="small-text" />
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>

	<h2><?php esc_html_e( 'Error log (database)', 'codeweber' ); ?></h2>
	<p>
		<button type="button" class="button" id="cws3-clear-errors">
			<?php
			/* translators: %d: number of log rows */
			printf( esc_html__( 'Clear DB log (%d)', 'codeweber' ), ErrorsTable::count() );
			?>
		</button>
		<a href="<?php echo esc_url( admin_url( 'tools.php?page=cws3-logs' ) ); ?>" class="button"><?php esc_html_e( 'Open file-based logs →', 'codeweber' ); ?></a>
	</p>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Time', 'codeweber' ); ?></th>
				<th><?php esc_html_e( 'Operation', 'codeweber' ); ?></th>
				<th><?php esc_html_e( 'Attachment', 'codeweber' ); ?></th>
				<th><?php esc_html_e( 'Message', 'codeweber' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php $rows = ErrorsTable::recent( 50 ); ?>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="4"><?php esc_html_e( 'No errors logged.', 'codeweber' ); ?></td></tr>
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
