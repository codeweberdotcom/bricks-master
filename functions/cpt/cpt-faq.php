<?php

// Регистрация CPT FAQ и таксономий (ваш существующий код)
function cptui_register_my_cpts_faq()
{
	/**
	 * Post Type: FAQ.
	 */
	$labels = [
		"name" => esc_html__("FAQs", "codeweber"),
		"singular_name" => esc_html__("FAQ", "codeweber"),
		"menu_name" => esc_html__("Faq", "codeweber"),
		"add_new" => esc_html__("Add New FAQ", "codeweber"),
		"add_new_item" => esc_html__("Add New FAQ", "codeweber"),
		"edit_item" => esc_html__("Edit FAQ", "codeweber"),
		"new_item" => esc_html__("New FAQ", "codeweber"),
		"view_item" => esc_html__("View FAQ", "codeweber"),
		"view_items" => esc_html__("View FAQs", "codeweber"),
		"search_items" => esc_html__("Search FAQs", "codeweber"),
		"not_found" => esc_html__("No FAQs found", "codeweber"),
		"not_found_in_trash" => esc_html__("No FAQs found in Trash", "codeweber"),
		"all_items" => esc_html__("All FAQs", "codeweber"),
		"archives" => esc_html__("FAQ Archives", "codeweber"),
		"insert_into_item" => esc_html__("Insert into FAQ", "codeweber"),
		"uploaded_to_this_item" => esc_html__("Uploaded to this FAQ", "codeweber"),
	];

	$args = [
		"label" => esc_html__("FAQ", "codeweber"),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "faqs",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => true,
		"show_in_menu" => true,
		"rewrite" => ["slug" => "faq", "with_front" => true],
		"supports" => ["title", "editor", "comments", "revisions", "author"],
	];
	register_post_type("faq", $args);
}
add_action('init', 'cptui_register_my_cpts_faq');

function cptui_register_my_taxes_faq_categories()
{
	/**
	 * Taxonomy: FAQ Categories.
	 */
	$labels = [
		"name" => esc_html__("FAQ Categories", "codeweber"),
		"singular_name" => esc_html__("FAQ Category", "codeweber"),
		"menu_name" => esc_html__("FAQ Categories", "codeweber"),
		"all_items" => esc_html__("All FAQ Categories", "codeweber"),
		"edit_item" => esc_html__("Edit FAQ Category", "codeweber"),
		"view_item" => esc_html__("View FAQ Category", "codeweber"),
		"add_new_item" => esc_html__("Add New FAQ Category", "codeweber"),
		"new_item_name" => esc_html__("New FAQ Category Name", "codeweber"),
		"search_items" => esc_html__("Search FAQ Categories", "codeweber"),
		"not_found" => esc_html__("No FAQ Categories Found", "codeweber"),
	];

	$args = [
		"label" => esc_html__("FAQ Categories", "codeweber"),
		"labels" => $labels,
		"public" => false,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "faq_categories",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rewrite" => ["slug" => "faq-categories", "with_front" => true],
	];
	register_taxonomy("faq_categories", ["faq"], $args);
}
add_action('init', 'cptui_register_my_taxes_faq_categories');

function cptui_register_my_taxes_faq_tag()
{
	/**
	 * Taxonomy: FAQ Tags.
	 */
	$labels = [
		"name" => esc_html__("FAQ Tags", "codeweber"),
		"singular_name" => esc_html__("FAQ Tag", "codeweber"),
		"menu_name" => esc_html__("FAQ Tags", "codeweber"),
		"all_items" => esc_html__("All FAQ Tags", "codeweber"),
		"edit_item" => esc_html__("Edit FAQ Tag", "codeweber"),
		"view_item" => esc_html__("View FAQ Tag", "codeweber"),
		"update_item" => esc_html__("Update FAQ Tag", "codeweber"),
		"add_new_item" => esc_html__("Add New FAQ Tag", "codeweber"),
		"new_item_name" => esc_html__("New FAQ Tag Name", "codeweber"),
		"search_items" => esc_html__("Search FAQ Tags", "codeweber"),
		"not_found" => esc_html__("No FAQ Tags Found", "codeweber"),
	];

	$args = [
		"label" => esc_html__("FAQ Tags", "codeweber"),
		"labels" => $labels,
		"public" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "faq_tag",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rewrite" => ["slug" => "faq-tag", "with_front" => true],
	];
	register_taxonomy("faq_tag", ["faq"], $args);
}
add_action('init', 'cptui_register_my_taxes_faq_tag');

// ==================== КОЛОНКИ И ФИЛЬТРЫ ДЛЯ АДМИНКИ ====================

/**
 * Добавляем колонки в список FAQ
 */
function add_faq_admin_columns($columns)
{
	$new_columns = array();

	foreach ($columns as $key => $value) {
		$new_columns[$key] = $value;

		// Добавляем после колонки "Title"
		if ($key === 'title') {
			$new_columns['faq_categories'] = __('Categories', 'codeweber');
			$new_columns['faq_tag'] = __('Tags', 'codeweber');
		}
	}

	// Добавляем колонку даты в конец, если её нет
	if (!isset($new_columns['date'])) {
		$new_columns['date'] = __('Date', 'codeweber');
	}

	return $new_columns;
}
add_filter('manage_faq_posts_columns', 'add_faq_admin_columns');

/**
 * Заполняем кастомные колонки данными
 */
function fill_faq_admin_columns($column, $post_id)
{
	switch ($column) {
		case 'faq_categories':
			$terms = get_the_terms($post_id, 'faq_categories');
			if ($terms && !is_wp_error($terms)) {
				$term_links = array();
				foreach ($terms as $term) {
					$term_links[] = sprintf(
						'<a href="%s">%s</a>',
						esc_url(add_query_arg(array('post_type' => 'faq', 'faq_categories' => $term->slug), 'edit.php')),
						esc_html($term->name)
					);
				}
				echo implode(', ', $term_links);
			} else {
				echo '<span aria-hidden="true">—</span>';
			}
			break;

		case 'faq_tag':
			$terms = get_the_terms($post_id, 'faq_tag');
			if ($terms && !is_wp_error($terms)) {
				$term_links = array();
				foreach ($terms as $term) {
					$term_links[] = sprintf(
						'<a href="%s">%s</a>',
						esc_url(add_query_arg(array('post_type' => 'faq', 'faq_tag' => $term->slug), 'edit.php')),
						esc_html($term->name)
					);
				}
				echo implode(', ', $term_links);
			} else {
				echo '<span aria-hidden="true">—</span>';
			}
			break;
	}
}
add_action('manage_faq_posts_custom_column', 'fill_faq_admin_columns', 10, 2);

/**
 * Делаем колонки сортируемыми
 */
function make_faq_columns_sortable($columns)
{
	$columns['faq_categories'] = 'faq_categories';
	$columns['faq_tag'] = 'faq_tag';
	return $columns;
}
add_filter('manage_edit-faq_sortable_columns', 'make_faq_columns_sortable');

/**
 * Добавляем фильтры по таксономиям над таблицей
 */
function add_faq_taxonomy_filters()
{
	global $typenow;

	// Только для нашего CPT
	if ($typenow !== 'faq') {
		return;
	}

	// Фильтр по категориям
	$faq_categories_taxonomy = 'faq_categories';
	$selected_category = isset($_GET[$faq_categories_taxonomy]) ? $_GET[$faq_categories_taxonomy] : '';
	$categories_args = array(
		'show_option_all' => __('All Categories', 'codeweber'),
		'taxonomy' => $faq_categories_taxonomy,
		'name' => $faq_categories_taxonomy,
		'value_field' => 'slug',
		'selected' => $selected_category,
		'hierarchical' => true,
		'show_count' => true,
		'hide_empty' => false,
	);
	wp_dropdown_categories($categories_args);

	// Фильтр по тегам
	$faq_tag_taxonomy = 'faq_tag';
	$selected_tag = isset($_GET[$faq_tag_taxonomy]) ? $_GET[$faq_tag_taxonomy] : '';
	$tags_args = array(
		'show_option_all' => __('All Tags', 'codeweber'),
		'taxonomy' => $faq_tag_taxonomy,
		'name' => $faq_tag_taxonomy,
		'value_field' => 'slug',
		'selected' => $selected_tag,
		'hierarchical' => false,
		'show_count' => true,
		'hide_empty' => false,
	);
	wp_dropdown_categories($tags_args);
}
add_action('restrict_manage_posts', 'add_faq_taxonomy_filters');

/**
 * Добавляем быстрые действия (Quick Edit) для таксономий
 */
function add_faq_quick_edit_fields($column_name, $post_type)
{
	if ($post_type !== 'faq') return;

	static $print_nonce = true;
	if ($print_nonce) {
		$print_nonce = false;
		wp_nonce_field('faq_quick_edit', 'faq_quick_edit_nonce');
	}
?>
	<fieldset class="inline-edit-col-right">
		<div class="inline-edit-col">
			<?php if ($column_name === 'faq_categories'): ?>
				<span class="title"><?php _e('Categories', 'codeweber'); ?></span>
				<ul class="cat-checklist category-checklist">
					<?php
					$terms = get_terms(array(
						'taxonomy' => 'faq_categories',
						'hide_empty' => false
					));
					foreach ($terms as $term) {
						echo '<li id="faq_categories-' . $term->term_id . '">';
						echo '<label class="selectit">';
						echo '<input value="' . $term->term_id . '" type="checkbox" name="tax_input[faq_categories][]" id="in-faq_categories-' . $term->term_id . '"> ';
						echo $term->name;
						echo '</label>';
						echo '</li>';
					}
					?>
				</ul>
			<?php endif; ?>

			<?php if ($column_name === 'faq_tag'): ?>
				<span class="title"><?php _e('Tags', 'codeweber'); ?></span>
				<?php
				$terms = get_terms(array(
					'taxonomy' => 'faq_tag',
					'hide_empty' => false
				));
				$term_names = array();
				foreach ($terms as $term) {
					$term_names[] = $term->name;
				}
				?>
				<textarea name="tax_input[faq_tag]" class="tax_input_faq_tag" rows="4" cols="50" placeholder="<?php _e('Add tags separated by commas', 'codeweber'); ?>"><?php echo implode(', ', $term_names); ?></textarea>
			<?php endif; ?>
		</div>
	</fieldset>
<?php
}
add_action('quick_edit_custom_box', 'add_faq_quick_edit_fields', 10, 2);

/**
 * Сохраняем данные из Quick Edit
 */
function save_faq_quick_edit_data($post_id)
{
	if (!isset($_POST['faq_quick_edit_nonce']) || !wp_verify_nonce($_POST['faq_quick_edit_nonce'], 'faq_quick_edit')) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// Сохраняем таксономии
	if (isset($_POST['tax_input'])) {
		foreach ($_POST['tax_input'] as $taxonomy => $terms) {
			if (taxonomy_exists($taxonomy)) {
				wp_set_post_terms($post_id, $terms, $taxonomy);
			}
		}
	}
}
add_action('save_post', 'save_faq_quick_edit_data');

/**
 * CSS для админки
 */
function faq_admin_css()
{
	global $typenow;

	if ($typenow !== 'faq') return;
?>
	<style type="text/css">
		.column-faq_categories,
		.column-faq_tag {
			width: 15%;
		}

		.column-faq_categories a,
		.column-faq_tag a {
			white-space: nowrap;
		}

		.inline-edit-col .cat-checklist.category-checklist {
			max-height: 120px;
			overflow-y: auto;
			border: 1px solid #ddd;
			padding: 10px;
			margin-top: 5px;
		}

		.tax_input_faq_tag {
			width: 100%;
			margin-top: 5px;
		}
	</style>
<?php
}
add_action('admin_head', 'faq_admin_css');



add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_faq', 10, 2);
function disable_gutenberg_for_faq($current_status, $post_type)
{
	if ($post_type === 'faq') {
		return false;
	}
	return $current_status;
}




//Shortcode


/**
 * FAQ Accordion Shortcode
 * 
 * Выводит аккордеон с вопросами и ответами из CPT 'faq' с поддержкой таксономий и сортировки.
 * 
 * @since 1.0.0
 * 
 * @param array $atts {
 *     Атрибуты шорткода
 * 
 *     @type string $category          Категории FAQ (слаги через запятую). Таксономия: faq_categories
 *     @type string $tag               Метки FAQ (слаги через запятую). Таксономия: faq_tag  
 *     @type int    $posts_per_page    Количество элементов для вывода. По умолчанию: -1 (все)
 *     @type string $order             Порядок сортировки: 'ASC' (по возрастанию) или 'DESC' (по убыванию). По умолчанию: 'ASC'
 *     @type string $orderby           Поле для сортировки. По умолчанию: 'date'
 * }
 * 
 * @return string HTML разметка аккордеона
 * 
 * @examples
 * // Вывод всех FAQ элементов
 * [faq_accordion]
 * 
 * // Вывод 5 FAQ из категории "general"
 * [faq_accordion category="general" posts_per_page="5"]
 * 
 * // Вывод FAQ с метками "important" и "popular"
 * [faq_accordion tag="important,popular" posts_per_page="10"]
 * 
 * // Вывод с сортировкой по убыванию даты
 * [faq_accordion order="DESC" posts_per_page="8"]
 * 
 * // Комбинирование категории и метки
 * [faq_accordion category="payment" tag="new" posts_per_page="6" order="DESC"]
 * 
 * // Несколько категорий через запятую
 * [faq_accordion category="general,technical" posts_per_page="10"]
 * 
 * // Сортировка по названию по возрастанию
 * [faq_accordion orderby="title" order="ASC"]
 */
function faq_accordion_shortcode($atts)
{
	// Default parameters
	$atts = shortcode_atts(array(
		'category' => '',
		'tag' => '',
		'posts_per_page' => -1,
		'orderby' => 'date',
		'order' => 'ASC'
	), $atts);

	// Build query args
	$args = array(
		'post_type' => 'faq',
		'posts_per_page' => $atts['posts_per_page'],
		'orderby' => $atts['orderby'],
		'order' => $atts['order'],
		'suppress_filters' => false // Enable translation
	);

	// Add taxonomy filters if provided
	$tax_query = array();

	if (!empty($atts['category'])) {
		$tax_query[] = array(
			'taxonomy' => 'faq_categories',
			'field' => 'slug',
			'terms' => array_map('trim', explode(',', $atts['category']))
		);
	}

	if (!empty($atts['tag'])) {
		$tax_query[] = array(
			'taxonomy' => 'faq_tag',
			'field' => 'slug',
			'terms' => array_map('trim', explode(',', $atts['tag']))
		);
	}

	// If both taxonomies are used, set relation
	if (count($tax_query) > 1) {
		$tax_query['relation'] = 'AND';
	}

	if (!empty($tax_query)) {
		$args['tax_query'] = $tax_query;
	}

	$faq_query = new WP_Query($args);

	if (!$faq_query->have_posts()) {
		return '<p>' . __('FAQ items not found.', 'codeweber') . '</p>';
	}

	$output = '<div class="accordion accordion-wrapper" id="accordionFaq">';
	$counter = 0;

	while ($faq_query->have_posts()) {
		$faq_query->the_post();
		$counter++;
		$post_id = get_the_ID();

		// Generate unique IDs for accordion elements
		$heading_id = 'headingFaq' . $counter;
		$collapse_id = 'collapseFaq' . $counter;

		// Determine if first item should be expanded
		$expanded = ($counter === 1) ? 'true' : 'false';
		$show_class = ($counter === 1) ? 'show' : '';
		$button_class = ($counter === 1) ? 'accordion-button' : 'accordion-button collapsed';

		$output .= '
        <div class="card plain accordion-item">
            <div class="card-header" id="' . esc_attr($heading_id) . '">
                <button class="' . esc_attr($button_class) . '" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#' . esc_attr($collapse_id) . '" aria-expanded="' . esc_attr($expanded) . '" 
                        aria-controls="' . esc_attr($collapse_id) . '">
                    ' . esc_html(get_the_title()) . '
                </button>
            </div>
            <!--/.card-header -->
            <div id="' . esc_attr($collapse_id) . '" class="accordion-collapse collapse ' . esc_attr($show_class) . '" 
                 aria-labelledby="' . esc_attr($heading_id) . '" data-bs-parent="#accordionFaq">
                <div class="card-body">
                    ' . apply_filters('the_content', get_the_content()) . '
                </div>
                <!--/.card-body -->
            </div>
            <!--/.accordion-collapse -->
        </div>
        <!--/.accordion-item -->';
	}

	$output .= '</div><!--/.accordion -->';

	wp_reset_postdata();

	return $output;
}
add_shortcode('faq_accordion', 'faq_accordion_shortcode');




/**
 * Добавляет пункт меню для документации FAQ шорткода
 */
function add_faq_shortcode_doc_menu()
{
	add_submenu_page(
		'edit.php?post_type=faq', // Родительское меню CPT FAQ
		__('FAQ Shortcode Documentation', 'codeweber'), // Заголовок страницы
		__('Shortcode Docs', 'codeweber'), // Название в меню
		'manage_options', // Права доступа
		'faq-shortcode-docs', // SLUG страницы
		'display_faq_shortcode_documentation' // Функция вывода
	);
}
add_action('admin_menu', 'add_faq_shortcode_doc_menu');

/**
 * Выводит документацию по шорткоду FAQ
 */
function display_faq_shortcode_documentation()
{
?>
	<div class="wrap">
		<h1><?php _e('FAQ Accordion Shortcode Documentation', 'codeweber'); ?></h1>

		<div class="card" style="max-width: 100%">
			<h2><?php _e('Basic Usage', 'codeweber'); ?></h2>
			<p><?php _e('Use the following shortcode to display FAQ accordion:', 'codeweber'); ?></p>
			<pre><code>[faq_accordion]</code></pre>
		</div>

		<div class="card" style="max-width: 100%">
			<h2><?php _e('Parameters', 'codeweber'); ?></h2>
			<table class="widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<th><?php _e('Parameter', 'codeweber'); ?></th>
						<th><?php _e('Description', 'codeweber'); ?></th>
						<th><?php _e('Default', 'codeweber'); ?></th>
						<th><?php _e('Example', 'codeweber'); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong>category</strong></td>
						<td><?php _e('FAQ categories (slugs separated by commas). Taxonomy: faq_categories', 'codeweber'); ?></td>
						<td><?php _e('empty', 'codeweber'); ?></td>
						<td><code>category="general,technical"</code></td>
					</tr>
					<tr>
						<td><strong>tag</strong></td>
						<td><?php _e('FAQ tags (slugs separated by commas). Taxonomy: faq_tag', 'codeweber'); ?></td>
						<td><?php _e('empty', 'codeweber'); ?></td>
						<td><code>tag="important,popular"</code></td>
					</tr>
					<tr>
						<td><strong>posts_per_page</strong></td>
						<td><?php _e('Number of FAQ items to display. Use -1 for all items', 'codeweber'); ?></td>
						<td>-1</td>
						<td><code>posts_per_page="5"</code></td>
					</tr>
					<tr>
						<td><strong>order</strong></td>
						<td><?php _e('Sort order: ASC (ascending) or DESC (descending)', 'codeweber'); ?></td>
						<td>ASC</td>
						<td><code>order="DESC"</code></td>
					</tr>
					<tr>
						<td><strong>orderby</strong></td>
						<td><?php _e('Field to sort by: date, title, menu_order, etc.', 'codeweber'); ?></td>
						<td>date</td>
						<td><code>orderby="title"</code></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="card" style="max-width: 100%">
			<h2><?php _e('Usage Examples', 'codeweber'); ?></h2>

			<h3><?php _e('1. Basic usage', 'codeweber'); ?></h3>
			<pre><code>[faq_accordion]</code></pre>
			<p><em><?php _e('Displays all FAQ items sorted by date ascending', 'codeweber'); ?></em></p>

			<h3><?php _e('2. Specific category', 'codeweber'); ?></h3>
			<pre><code>[faq_accordion category="general" posts_per_page="5"]</code></pre>
			<p><em><?php _e('Displays 5 FAQ items from "general" category', 'codeweber'); ?></em></p>

			<h3><?php _e('3. With tags', 'codeweber'); ?></h3>
			<pre><code>[faq_accordion tag="important,popular" posts_per_page="10"]</code></pre>
			<p><em><?php _e('Displays 10 FAQ items with "important" or "popular" tags', 'codeweber'); ?></em></p>

			<h3><?php _e('4. Sort by title', 'codeweber'); ?></h3>
			<pre><code>[faq_accordion orderby="title" order="ASC"]</code></pre>
			<p><em><?php _e('Displays FAQ items sorted by title alphabetically', 'codeweber'); ?></em></p>

			<h3><?php _e('5. Combined parameters', 'codeweber'); ?></h3>
			<pre><code>[faq_accordion category="payment" tag="new" posts_per_page="6" order="DESC"]</code></pre>
			<p><em><?php _e('Displays 6 latest FAQ items from "payment" category with "new" tag', 'codeweber'); ?></em></p>

			<h3><?php _e('6. Multiple categories', 'codeweber'); ?></h3>
			<pre><code>[faq_accordion category="general,technical" posts_per_page="10"]</code></pre>
			<p><em><?php _e('Displays 10 FAQ items from "general" OR "technical" categories', 'codeweber'); ?></em></p>
		</div>

		<div class="card" style="max-width: 100%">
			<h2><?php _e('Template Structure', 'codeweber'); ?></h2>
			<p><?php _e('The shortcode generates the following HTML structure:', 'codeweber'); ?></p>
			<pre><code>&lt;div class="accordion accordion-wrapper" id="accordionFaq"&gt;
    &lt;div class="card plain accordion-item"&gt;
        &lt;div class="card-header" id="headingFaq1"&gt;
            &lt;button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#collapseFaq1"&gt;
                Question Title
            &lt;/button&gt;
        &lt;/div&gt;
        &lt;div id="collapseFaq1" class="accordion-collapse collapse show"&gt;
            &lt;div class="card-body"&gt;
                Answer Content
            &lt;/div&gt;
        &lt;/div&gt;
    &lt;/div&gt;
&lt;/div&gt;</code></pre>
		</div>

		<div class="card" style="max-width: 100%">
			<h2><?php _e('CSS Classes for Styling', 'codeweber'); ?></h2>
			<ul>
				<li><strong>.accordion-wrapper</strong> - <?php _e('Main wrapper', 'codeweber'); ?></li>
				<li><strong>.accordion-item</strong> - <?php _e('Each FAQ item', 'codeweber'); ?></li>
				<li><strong>.card-header</strong> - <?php _e('Question header', 'codeweber'); ?></li>
				<li><strong>.accordion-button</strong> - <?php _e('Question button', 'codeweber'); ?></li>
				<li><strong>.accordion-collapse</strong> - <?php _e('Answer container', 'codeweber'); ?></li>
				<li><strong>.card-body</strong> - <?php _e('Answer content', 'codeweber'); ?></li>
			</ul>
		</div>

		<style>
			.card {
				background: #fff;
				padding: 20px;
				margin: 20px 0;
				border: 1px solid #ccd0d4;
				border-radius: 4px;
			}

			.card h2 {
				margin-top: 0;
				color: #23282d;
			}

			pre {
				background: #f1f1f1;
				padding: 15px;
				border-radius: 3px;
				overflow-x: auto;
			}

			table.widefat {
				margin: 10px 0;
			}

			table.widefat th {
				font-weight: 600;
			}
		</style>
	</div>
<?php
}

/**
 * Добавляет ссылку на документацию в список плагинов (опционально)
 */
function add_faq_docs_plugin_link($links)
{
	$docs_link = '<a href="edit.php?post_type=faq&page=faq-shortcode-docs">' . __('Documentation', 'codeweber') . '</a>';
	array_unshift($links, $docs_link);
	return $links;
}
