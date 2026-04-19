<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array $files */
/** @var string $current */
/** @var string $content */
/** @var string $filter */
?>
<div class="wrap cws3-wrap">
	<h1><?php esc_html_e( 'S3 Storage — Logs', 'codeweber-s3-storage' ); ?></h1>

	<form method="get" action="">
		<input type="hidden" name="page" value="cws3-logs" />
		<p>
			<label for="cws3-log-file"><?php esc_html_e( 'File', 'codeweber-s3-storage' ); ?>:</label>
			<select id="cws3-log-file" name="file">
				<?php foreach ( $files as $f ) : $n = basename( $f ); ?>
					<option value="<?php echo esc_attr( $n ); ?>" <?php selected( $current, $n ); ?>><?php echo esc_html( $n ); ?></option>
				<?php endforeach; ?>
				<?php if ( empty( $files ) ) : ?>
					<option value=""><?php esc_html_e( 'No log files yet', 'codeweber-s3-storage' ); ?></option>
				<?php endif; ?>
			</select>

			<label for="cws3-log-level" style="margin-left:10px;"><?php esc_html_e( 'Level', 'codeweber-s3-storage' ); ?>:</label>
			<select id="cws3-log-level" name="level">
				<?php foreach ( [ 'all' => __( 'All', 'codeweber-s3-storage' ), 'error' => 'ERROR', 'warn' => 'WARN', 'info' => 'INFO', 'debug' => 'DEBUG' ] as $val => $label ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $filter, $val ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>

			<button type="submit" class="button"><?php esc_html_e( 'View', 'codeweber-s3-storage' ); ?></button>
			<button type="button" class="button" id="cws3-clear-logs"><?php esc_html_e( 'Delete all log files', 'codeweber-s3-storage' ); ?></button>
		</p>
	</form>

	<?php if ( $content ) : ?>
		<pre class="cws3-log-view"><?php echo esc_html( $content ); ?></pre>
	<?php else : ?>
		<p><em><?php esc_html_e( 'Log file is empty or does not exist.', 'codeweber-s3-storage' ); ?></em></p>
	<?php endif; ?>
</div>
