<?php

$searchtext = esc_html__('Search', 'codeweber');

?>

<form class="search-form" action="<?php echo esc_url_raw(home_url()); ?>" method="get">
	<div class="mc-field-group input-group form-floating">
		<input
			type="search"
			name="s"
			id="search-input"
			class="form-control form-control-sm"
			placeholder="<?php echo esc_attr($searchtext); ?>"
			aria-label="<?php echo esc_attr($searchtext); ?>"
			autocomplete="off">
		<label for="search-input"><?php echo esc_html($searchtext); ?></label>
		<button
			type="submit"
			class="btn btn-primary"
			aria-label="<?php echo esc_attr($searchtext); ?>">
			<i class="fa-solid fa-magnifying-glass"></i>
			<?php echo $searchtext; ?>
		</button>
	</div>
</form>