<?php

/**
 * The template file for displaying the comments and comment form for the
 */
if (post_password_required()) {
	return;
}
if (have_comments()) {
?>
	<div class="comments" id="comments" style="padding-top: 75px; margin-top: -75px;">
		<?php
		$comments_number = absint(get_comments_number());
		?>
		<h3 class="mb-6">
			<?php
			if (!have_comments()) {
				_e('Leave a comment', 'codeweber');
			} elseif (1 === $comments_number) {
				/* translators: %s: Post title. */
				printf(_x('One reply on &ldquo;%s&rdquo;', 'comments title', 'codeweber'), get_the_title());
			} else {
				printf(
					/* translators: 1: Number of comments, 2: Post title. */
					_nx(
						'%1$s reply on &ldquo;%2$s&rdquo;',
						'%1$s replies on &ldquo;%2$s&rdquo;',
						$comments_number,
						'comments title',
						'codeweber'
					),
					number_format_i18n($comments_number),
					get_the_title()
				);
			}
			?>
		</h3><!-- .comments-title -->
		<ol id="singlecomments" class="commentlist">
			<?php
			wp_list_comments(
				array(
					'walker'      => new codeweber_Walker_Comment(),
					'avatar_size' => 120,
					'style'       => 'li',
				)
			);

			/** Paginate change */
			$pages = paginate_comments_links(array('echo' => false, 'type' => 'array', 'prev_text' => '<span aria-hidden="true"><i class="uil uil-arrow-left"></i></span>', 'next_text' => '<span aria-hidden="true"><i class="uil uil-arrow-right"></i></span>'));
			if (is_array($pages)) {
				$output = '';
				foreach ($pages as $page) {
					$page = "\n<li>$page</li>\n";
					if (strpos($page, ' current') !== false) {
						$page = str_replace([' current', '<li>'], ['', '<li class="page-item page-link active">'], $page);
						$page = str_replace([' current', 'Ранее'], ['', '<span aria-hidden="true"><i class="uil uil-arrow-left"></i></span>'], $page);
						$page = str_replace([' current', 'page-numbers'], ['', 'page-links'], $page);
					} else {
						$page = str_replace([' current', '<li>'], ['', '<li class="page-item page-link">'], $page);
						$page = str_replace([' current', 'Далее'], ['', '<span aria-hidden="true"><i class="uil uil-arrow-right"></i></span>'], $page);
						$page = str_replace([' current', 'Ранее'], ['', '<span aria-hidden="true"><i class="uil uil-arrow-left"></i></span>'], $page);
						$page = str_replace([' current', 'page-numbers'], ['', 'page-links'], $page);
					}
					$output .= $page;
				}
			?>
				<nav class="d-flex" aria-label="pagination">
					<ul class="pagination mb-0">
						<?= $output ?>
					</ul>
				</nav>
			<?php
			}
			?>
		</ol><!-- .comments-inner -->
		<hr class="my-5"/>
	</div><!-- comments -->
<?php
}

if (comments_open() || pings_open()) {

	/** Get commenter Info */
	$commenter = wp_get_current_commenter();
	$consent   = empty($commenter['comment_author_email']) ? '' : ' checked="checked"';

	/** Reorder comment form field */

	if (!function_exists('codeweber_reorder_comment_fields')) {
	add_filter('comment_form_fields', 'codeweber_reorder_comment_fields');
	function codeweber_reorder_comment_fields($fields)
	{
		$new_fields = array();
		$myorder = array('author', 'email', 'comment');
		foreach ($myorder as $key) {
			$new_fields[$key] = $fields[$key];
			unset($fields[$key]);
		}
		if (
			$fields
		)
			foreach ($fields as $key => $val)
				$new_fields[$key] = $val;
		return $new_fields;
	}
   }

	/** Disable Url comment field */
	if (!function_exists('website_remove')) {
	add_filter('comment_form_default_fields', 'website_remove');
	function website_remove($fields)
	{
		if (isset($fields['url']))
			unset($fields['url']);
		return $fields;
	}
   }

	if (!function_exists('website_remove')) {
	/** Change class Awaiting Moderation Comment */
	add_filter('comment_class', 'wpse_259326_comment_classes', 10, 5);
	function wpse_259326_comment_classes($classes, $class, $comment_ID, $comment, $post_id)
	{
		if ('unapproved' === wp_get_comment_status($comment) && is_array($classes))
			$classes[] = 'wpse-comment-awaiting-moderation';
		return $classes;
	}
}

	/** Theme form radius and button style for comment form */
	$comment_form_radius = function_exists('getThemeFormRadius') ? getThemeFormRadius() : ' rounded';
	$comment_submit_btn  = function_exists('getThemeButton') ? getThemeButton() : '';

	/** Change Comment form fields */
	comment_form(
		array(
			'fields'               => [
				'cookies' => '<div class="form-check mb-4">
                          <input class="form-check-input comment-form-cookies-consent" type="checkbox"name="wp-comment-cookies-consent"  value="yes" id="wp-comment-cookies-consent" ' . $consent . '  required>
                          <label class="form-check-label" for="wp-comment-cookies-consent">' .  __("By using this comment form you agree with our Privacy Policy", "codeweber") . ' </label>
                           </div>',
				'author' => '<div class="form-floating mb-4">
					<input type="text" class="form-control' . esc_attr($comment_form_radius) . '" name="author" id="form_name" value="' . esc_attr($commenter["comment_author"]) . '" tabindex="1" placeholder="Name" required />
					<label for="author">' . __("Name *", "codeweber") . '</label>
					<div class="valid-feedback"> ' . esc_html__("Looks good!", "codeweber") . ' </div>
					<div class="invalid-feedback"> ' . esc_html__("Please enter your first name.", "codeweber") . ' </div>
				   </div> <!-- .form-group -->',

				'email'  => '<div class="form-floating mb-4">
					<input id="form_email" type="email" class="form-control' . esc_attr($comment_form_radius) . '" name="email" value="' . esc_attr($commenter["comment_author_email"]) . '" tabindex="2" placeholder="jane.doe@example.com" required />
					<label for="form_email">' . esc_html__("Email *", "codeweber") . '</label>
					<div class="valid-feedback"> ' . esc_html__("Looks good!", "codeweber") . ' </div>
					<div class="invalid-feedback"> ' . esc_html__("Please enter your E-Mail.", "codeweber") . ' </div>
				</div> <!-- .form-group -->',
			],

			'label_submit'       => __("Submit", "codeweber"),
			'title_reply'        => __('Would you like to share your thoughts?', 'codeweber'),
			'class_submit'       => 'btn btn-primary' . esc_attr($comment_submit_btn) . ' mb-0',
			'class_form'         => 'comment-form needs-validation' . esc_attr($comment_form_radius),
			'title_reply_before' => '<h3 id="reply-title" class="mb-3 me-2">',
			'title_reply_after'  => '</h3>',

			'comment_field' =>  '<div class="form-floating mb-4 comment-form-comment">
				<textarea class="form-control input-lg' . esc_attr($comment_form_radius) . '" name="comment" id="comment" tabindex="4" placeholder="Type your comment here..." style="height: 150px" required></textarea>
				<label for="comment">' . esc_html__("Comment *", "codeweber") . '</label>
				<div class="valid-feedback"> ' . esc_html__("Looks good!", "codeweber") . '</div>
				<div class="invalid-feedback"> ' . esc_html__("Please enter your comment.", "codeweber") . '</div>
			   </div> <!-- .form-group -->'
		)
	);
?>
<?php
} elseif (is_single()) {
	if ($comments) {
		echo '<hr/>';
	};
?>
	<div class="comment-respond" id="respond">
		<p class="comments-closed"><?php _e('Comments are closed.', 'codeweber'); ?></p>
	</div><!-- #respond -->
<?php
}
?>