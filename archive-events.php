<?php
/**
 * Archive Template: Events
 *
 * Dispatches to templates/archives/events/{template}.php based on Redux setting.
 *
 * @package Codeweber
 */

get_header();
get_pageheader();

global $opt_name;
$post_type    = 'events';
$templateloop = Redux::get_option( $opt_name, 'archive_template_select_events' );
if ( empty( $templateloop ) ) {
	$templateloop = 'events_1';
}

$sidebar_position = get_sidebar_position( $opt_name );
$padding          = get_content_padding_classes();
$content_class    = ( $sidebar_position === 'none' ) ? 'col-12 ' . $padding : 'col-xl-8 ' . $padding;
?>

<section id="content-wrapper" class="wrapper">
	<div class="container">
		<div class="row">
			<?php get_sidebar( 'left' ); ?>

			<div class="<?php echo esc_attr( $content_class ); ?>">
				<?php
				if ( locate_template( "templates/archives/events/{$templateloop}.php" ) ) {
					get_template_part( "templates/archives/events/{$templateloop}" );
				} else {
					get_template_part( 'templates/archives/events/events_1' );
				}
				?>
			</div>

			<?php get_sidebar( 'right' ); ?>
		</div>
	</div>
</section>

<?php
get_footer();
