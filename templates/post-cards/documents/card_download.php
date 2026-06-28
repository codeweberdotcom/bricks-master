<?php
/**
 * Template: Document Card Download
 *
 * Шаблон карточки документа с кнопкой AJAX загрузки.
 * Используется только для типа записи 'documents'.
 *
 * @param array $post_data Данные поста
 * @param array $display_settings Настройки отображения
 * @param array $template_args Дополнительные аргументы
 */

if (!isset($post_data) || !$post_data) {
    return;
}

$display = cw_get_post_card_display_settings($display_settings ?? []);

$title = $post_data['title'];
if ($display['title_length'] > 0 && mb_strlen($title) > $display['title_length']) {
    $title = mb_substr($title, 0, $display['title_length']) . '...';
}

// Файл документа: URL, расширение, размер
$document_file = get_post_meta($post_data['id'], '_document_file', true);
$file_url      = '';
$file_ext      = '';
$file_size     = '';
$attachment_id = 0;

if ($document_file) {
    if (is_numeric($document_file)) {
        $attachment_id = (int) $document_file;
        $file_url      = wp_get_attachment_url($attachment_id);
    } else {
        $file_url      = $document_file;
        $attachment_id = attachment_url_to_postid($document_file);
    }

    if ($file_url) {
        $file_ext = strtoupper(pathinfo(parse_url($file_url, PHP_URL_PATH), PATHINFO_EXTENSION));
    }

    if ($attachment_id) {
        $path = get_attached_file($attachment_id);
        if ($path && file_exists($path)) {
            $file_size = size_format(filesize($path), 1);
        }
    }
}

$file_meta = trim($file_ext . ($file_size ? ' · ' . $file_size : ''));

// Тип документа (таксономия document_type)
$type_label = '';
$terms      = get_the_terms($post_data['id'], 'document_type');
if ($terms && !is_wp_error($terms)) {
    $type_label = $terms[0]->name;
}
?>
<div class="card p-6 card-lift rounded h-100">
  <div class="d-flex align-items-center gap-3">
    <div class="bg-primary bg-opacity-10 rounded-2 text-center p-3 flex-shrink-0 text-primary lh-1">
      <i class="uil uil-file fs-30 d-block"></i>
      <?php if ($file_ext) : ?><span class="cw-subtitle fs-11"><?php echo esc_html($file_ext); ?></span><?php endif; ?>
    </div>
    <div class="flex-grow-1">
      <?php if ($type_label) : ?><p class="cw-subtitle mb-0"><?php echo esc_html($type_label); ?></p><?php endif; ?>
      <h5 class="mb-1"><?php echo esc_html($title); ?></h5>
      <?php if ($file_meta) : ?><p class="mb-0 text-muted fs-sm"><?php echo esc_html($file_meta); ?></p><?php endif; ?>
    </div>
    <?php if ($file_url) : ?>
    <a href="javascript:void(0)" data-value="doc-<?php echo esc_attr($post_data['id']); ?>" data-bs-toggle="download" class="btn btn-sm btn-outline-primary rounded-pill flex-shrink-0"><span><?php esc_html_e('Download', 'codeweber'); ?></span> <i class="uil uil-import ms-1"></i></a>
    <?php else : ?>
    <a href="<?php echo esc_url($post_data['link']); ?>" class="btn btn-sm btn-outline-primary rounded-pill flex-shrink-0"><?php esc_html_e('Read more', 'codeweber'); ?></a>
    <?php endif; ?>
  </div>
</div>
