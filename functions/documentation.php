<?php
/**
 * Документация Сайта — только в админке. Дочерний пункт меню.
 * Просмотр всех .md из папки doc темы.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Регистрация дочерних пунктов меню в админке (Внешний вид → Документация Сайта, Функционал Сайта).
 */
function codeweber_documentation_admin_menu() {
	add_submenu_page(
		'themes.php',
		__( 'Документация Сайта', 'codeweber' ),
		__( 'Документация Сайта', 'codeweber' ),
		'manage_options',
		'codeweber-documentation',
		'codeweber_documentation_admin_page'
	);
	add_submenu_page(
		'themes.php',
		__( 'Функционал Сайта', 'codeweber' ),
		__( 'Функционал Сайта', 'codeweber' ),
		'manage_options',
		'codeweber-functionality',
		'codeweber_functionality_admin_page'
	);
}

add_action( 'admin_menu', 'codeweber_documentation_admin_menu' );

/**
 * Вывод страницы документации в админке.
 */
function codeweber_documentation_admin_page() {
	$doc_dir = get_template_directory() . '/doc';
	codeweber_doc_render_page( $doc_dir, 'themes.php?page=codeweber-documentation', __( 'Документация Сайта', 'codeweber' ), __( 'Документация темы Codeweber', 'codeweber' ) );
}

/**
 * Вывод страницы «Функционал Сайта» — документы из doc-theme.
 */
function codeweber_functionality_admin_page() {
	$doc_dir = get_template_directory() . '/doc-theme';
	codeweber_doc_render_page( $doc_dir, 'themes.php?page=codeweber-functionality', __( 'Функционал Сайта', 'codeweber' ), __( 'Функционал Сайта', 'codeweber' ) );
}

/**
 * Общий вывод страницы со списком .md и контентом выбранного документа.
 *
 * @param string $doc_dir     Полный путь к папке с .md (doc или doc-theme).
 * @param string $page_query  Строка страницы для ссылок (themes.php?page=...).
 * @param string $sidebar_title Заголовок блока в сайдбаре.
 * @param string $default_title Заголовок по умолчанию, когда документ не выбран.
 */
function codeweber_doc_render_page( $doc_dir, $page_query, $sidebar_title, $default_title ) {
	$page_url   = admin_url( $page_query );
	$doc_tree   = codeweber_doc_get_tree( $doc_dir );
	$doc_param  = isset( $_GET['doc'] ) ? sanitize_text_field( wp_unslash( $_GET['doc'] ) ) : '';
	$doc_path   = codeweber_doc_resolve_path( $doc_param, $doc_dir );
	$doc_content = '';
	$doc_title  = $default_title;

	if ( $doc_path ) {
		$doc_content = codeweber_doc_markdown_to_html( file_get_contents( $doc_path ) );
		$doc_content = codeweber_doc_rewrite_md_links( $doc_content, $page_url );
		$doc_title   = basename( $doc_path, '.md' );
	} else {
		$readme = $doc_dir . '/README.md';
		if ( file_exists( $readme ) ) {
			$doc_content = codeweber_doc_markdown_to_html( file_get_contents( $readme ) );
			$doc_content = codeweber_doc_rewrite_md_links( $doc_content, $page_url );
		}
	}

	?>
	<div class="wrap codeweber-documentation-wrap">
		<h1 class="wp-heading-inline"><?php echo esc_html( $doc_title ); ?></h1>
		<hr class="wp-header-end">

		<div class="codeweber-doc-layout" style="display:flex; gap: 24px; align-items: flex-start;">
			<aside class="codeweber-doc-sidebar" style="width: 400px; flex-shrink: 0;">
				<div class="card" style="padding: 12px 16px;">
					<h2 class="card-title" style="margin: 0 0 12px; font-size: 14px;"><?php echo esc_html( $sidebar_title ); ?></h2>
					<ul style="list-style: none; margin: 0; padding: 0; font-size: 13px;">
						<li style="margin-bottom: 6px;">
							<a href="<?php echo esc_url( $page_url ); ?>"><?php esc_html_e( 'Начало', 'codeweber' ); ?></a>
						</li>
						<?php foreach ( $doc_tree as $group ) : ?>
							<?php if ( empty( $group['children'] ) ) continue; ?>
							<li style="margin-top: 10px;">
								<?php if ( ! empty( $group['label'] ) ) : ?>
									<span style="color: #646970; font-size: 12px; display: block; margin-bottom: 4px;"><?php echo esc_html( $group['label'] ); ?></span>
								<?php endif; ?>
								<ul style="list-style: none; margin: 0 0 0 8px; padding: 0;">
									<?php foreach ( $group['children'] as $item ) : ?>
										<?php
										$item_url  = add_query_arg( 'doc', $item['path'], $page_url );
										$is_active = $doc_param === $item['path'];
										?>
										<li style="margin-bottom: 4px;">
											<?php if ( $is_active ) : ?>
												<strong><?php echo esc_html( $item['name'] ); ?></strong>
											<?php else : ?>
												<a href="<?php echo esc_url( $item_url ); ?>"><?php echo esc_html( $item['name'] ); ?></a>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</aside>
			<div class="codeweber-doc-content" style="flex: 1; min-width: 0;">
				<div class="card" style="padding: 20px 24px; min-width: 100% !important;">
					<div class="doc-markdown-body codeweber-doc-markdown">
						<?php echo $doc_content; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<style>
		.codeweber-doc-markdown { font-size: 14px; line-height: 1.6; }
		.codeweber-doc-markdown h2 { font-size: 1.25em; margin-top: 1.5em; margin-bottom: 0.6em; }
		.codeweber-doc-markdown h3 { font-size: 1.1em; margin-top: 1.2em; margin-bottom: 0.5em; }
		.codeweber-doc-markdown ul, .codeweber-doc-markdown ol { padding-left: 1.5em; margin-bottom: 0.8em; }
		.codeweber-doc-markdown pre { background: #f0f0f1; padding: 12px 16px; border-radius: 4px; overflow-x: auto; }
		.codeweber-doc-markdown code { background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-size: 0.92em; }
		.codeweber-doc-markdown pre code { padding: 0; background: none; }
		.codeweber-doc-markdown table { width: 100%; margin-bottom: 1em; border-collapse: collapse; }
		.codeweber-doc-markdown th, .codeweber-doc-markdown td { border: 1px solid #c3c4c7; padding: 8px 12px; text-align: left; }
		.codeweber-doc-markdown th { background: #f0f0f1; }
		.codeweber-doc-markdown a { color: #2271b1; }
	</style>
	<?php
}

/**
 * Сканирует папку с .md и возвращает дерево файлов.
 *
 * @param string|null $doc_dir Полный путь к папке (по умолчанию doc темы).
 * @return array
 */
function codeweber_doc_get_tree( $doc_dir = null ) {
	if ( $doc_dir === null ) {
		$doc_dir = get_template_directory() . '/doc';
	}
	$doc_dir = rtrim( $doc_dir, '/\\' );
	if ( ! is_dir( $doc_dir ) ) {
		return [];
	}

	$tree = [];
	$root_files = [];

	$doc_dir_sep = $doc_dir . DIRECTORY_SEPARATOR;
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $doc_dir, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS ),
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ( $iterator as $file ) {
		if ( ! $file->isFile() || strtolower( $file->getExtension() ) !== 'md' ) {
			continue;
		}

		$full = $file->getPathname();
		$rel  = str_replace( $doc_dir_sep, '', $full );
		$rel  = str_replace( '\\', '/', $rel );
		$name = $file->getFilename();

		if ( strpos( $rel, '/' ) === false ) {
			$root_files[] = array( 'path' => $rel, 'name' => $name );
			continue;
		}

		$parts = explode( '/', $rel );
		$dir   = $parts[0];

		if ( ! isset( $tree[ $dir ] ) ) {
			$tree[ $dir ] = array( 'label' => $dir, 'children' => [] );
		}
		$tree[ $dir ]['children'][] = array( 'path' => $rel, 'name' => $name );
	}

	if ( ! empty( $root_files ) ) {
		$tree = array_merge( array( '' => array( 'label' => '', 'children' => $root_files ) ), $tree );
	}

	ksort( $tree );
	return array_values( $tree );
}

/**
 * Безопасный путь к файлу: только .md внутри указанной папки.
 *
 * @param string      $doc_query Значение параметра doc.
 * @param string|null $doc_dir   Полный путь к папке (по умолчанию doc темы).
 * @return string|null Полный путь к файлу или null.
 */
function codeweber_doc_resolve_path( $doc_query, $doc_dir = null ) {
	if ( ! is_string( $doc_query ) || $doc_query === '' ) {
		return null;
	}
	if ( $doc_dir === null ) {
		$doc_dir = get_template_directory() . '/doc';
	}
	$doc_dir = rtrim( $doc_dir, '/\\' );

	$doc_query = ltrim( str_replace( '\\', '/', $doc_query ), '/' );
	if ( preg_match( '#\.\.|/\.#', $doc_query ) ) {
		return null;
	}
	if ( substr( strtolower( $doc_query ), -3 ) !== '.md' ) {
		$doc_query .= '.md';
	}

	$full_path = realpath( $doc_dir . '/' . $doc_query );
	$doc_real  = realpath( $doc_dir );

	if ( $full_path === false || $doc_real === false || strpos( $full_path, $doc_real ) !== 0 ) {
		return null;
	}

	return is_file( $full_path ) ? $full_path : null;
}

/**
 * Конвертация Markdown в HTML (Redux Parsedown при наличии).
 *
 * @param string $md Исходный Markdown.
 * @return string HTML.
 */
function codeweber_doc_markdown_to_html( $md ) {
	$md = (string) $md;
	if ( $md === '' ) {
		return '';
	}

	$parsedown_file = get_template_directory() . '/redux-framework/redux-core/inc/fields/raw/parsedown.php';
	if ( file_exists( $parsedown_file ) && ! class_exists( 'Redux_Parsedown', false ) ) {
		require_once $parsedown_file;
	}

	if ( class_exists( 'Redux_Parsedown', false ) ) {
		$parser = new Redux_Parsedown();
		return $parser->text( $md );
	}

	return '<pre>' . esc_html( $md ) . '</pre>';
}

/**
 * Заменяет в HTML ссылки на .md на ссылки страницы документации (админка).
 *
 * @param string $html   HTML контент.
 * @param string $base_url Базовый URL страницы (admin.php?page=codeweber-documentation).
 * @return string
 */
function codeweber_doc_rewrite_md_links( $html, $base_url = '' ) {
	if ( $base_url === '' ) {
		$base_url = admin_url( 'admin.php?page=codeweber-documentation' );
	}
	return preg_replace_callback(
		'#<a\s+([^>]*?)href=(["\'])([^"\']+\.md)(["\'])([^>]*)>#i',
		function ( $m ) use ( $base_url ) {
			$path = $m[3];
			$anchor = '';
			if ( strpos( $path, '#' ) !== false ) {
				$parts  = explode( '#', $path, 2 );
				$path   = trim( $parts[0] );
				$anchor = ! empty( $parts[1] ) ? '#' . $parts[1] : '';
			}
			$url = add_query_arg( 'doc', $path, $base_url ) . $anchor;
			return '<a ' . $m[1] . 'href="' . esc_url( $url ) . '"' . $m[5] . '>';
		},
		$html
	);
}
