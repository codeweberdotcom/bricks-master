<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var WP_Post $post */
/** @var array $rows */
/** @var string $status */
/** @var string $badge */
/** @var array $settings */
/** @var int $total_sizes */
/** @var int $offloaded_sizes */
/** @var object|null $original */
?>
<div class="cws3-metabox" data-attachment-id="<?php echo (int) $post->ID; ?>">
	<p><?php echo $badge; ?></p>

	<?php if ( $original ) : ?>
		<p>
			<strong><?php esc_html_e( 'Bucket', 'codeweber' ); ?>:</strong>
			<code><?php echo esc_html( $original->bucket ); ?></code>
		</p>
		<p>
			<strong><?php esc_html_e( 'Key', 'codeweber' ); ?>:</strong><br />
			<code><?php echo esc_html( $original->object_key ); ?></code>
		</p>
		<?php $url = \Codeweber\S3Storage\Client::public_url_for_key( $settings, $original->object_key ); ?>
		<p>
			<strong><?php esc_html_e( 'URL', 'codeweber' ); ?>:</strong><br />
			<input type="text" readonly value="<?php echo esc_attr( $url ); ?>" style="width:100%;" onclick="this.select();" />
		</p>
		<?php if ( $total_sizes ) : ?>
			<p>
				<?php
				/* translators: 1: offloaded sizes count, 2: total sizes count */
				printf( esc_html__( 'Sizes: %1$d / %2$d offloaded', 'codeweber' ), $offloaded_sizes, $total_sizes );
				?>
			</p>
		<?php endif; ?>
	<?php else : ?>
		<p><em><?php esc_html_e( 'Not yet offloaded.', 'codeweber' ); ?></em></p>
	<?php endif; ?>

	<hr />

	<p>
		<button type="button" class="button button-primary cws3-metabox-action" data-op="offload"><?php esc_html_e( 'Offload now', 'codeweber' ); ?></button>
		<button type="button" class="button cws3-metabox-action" data-op="restore"><?php esc_html_e( 'Restore to local', 'codeweber' ); ?></button>
	</p>
	<p>
		<button type="button" class="button cws3-metabox-action" data-op="delete_local"><?php esc_html_e( 'Delete local copy', 'codeweber' ); ?></button>
		<button type="button" class="button cws3-metabox-action" data-op="resync"><?php esc_html_e( 'Re-sync', 'codeweber' ); ?></button>
	</p>
	<p>
		<button type="button" class="button cws3-metabox-action" data-op="verify"><?php esc_html_e( 'Verify', 'codeweber' ); ?></button>
	</p>
	<p class="cws3-metabox-result" style="min-height:1.5em;"></p>
</div>
