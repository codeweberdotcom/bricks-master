<?php
/**
 * Demo данные для CPT Forms (codeweber_form)
 * 
 * Функции для создания demo форм
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить данные demo форм
 * 
 * @return array Массив данных форм
 */
function cw_demo_get_forms_data() {
    return array(
        array(
            'title' => 'Testimonial Form',
            'slug' => 'testimonial-form',
            'content' => '<!-- wp:codeweber-blocks/form {"formId":"6362","formFields":[{"fieldType":"text","fieldName":"name","fieldLabel":"Имя","placeholder":"","isRequired":true,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"6","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","showForGuestsOnly":true},{"fieldType":"email","fieldName":"email","fieldLabel":"Email","placeholder":"","isRequired":true,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"6","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","showForGuestsOnly":true},{"fieldType":"company","fieldName":"company","fieldLabel":"","placeholder":"","isRequired":false,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"6","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","showForGuestsOnly":false},{"fieldType":"author_role","fieldName":"role","fieldLabel":"","placeholder":"","isRequired":false,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"6","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","showForGuestsOnly":false},{"fieldType":"textarea","fieldName":"message","fieldLabel":"Ваш отзыв","placeholder":"","isRequired":true,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","showForGuestsOnly":false},{"fieldType":"rating","fieldName":"rating","fieldLabel":"","placeholder":"","isRequired":true,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","showForGuestsOnly":false},{"fieldType":"consents_block","fieldName":"","fieldLabel":"","placeholder":"","isRequired":false,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[{"label":"Я даю свое <a href="{document_url}">согласие</a> на обработку моих персональных данных.","document_id":6248,"required":true},{"label":"Я согласен <a href="{document_url}">получать информационные и рекламные рассылки</a>.","document_id":4981,"required":false}],"buttonText":"","buttonClass":"btn btn-primary","showForGuestsOnly":false}],"formGap":"1","formType":"testimonial"} -->
<div class="wp-block-codeweber-blocks-form codeweber-form-wrapper"><form id="form-6362" class="codeweber-form" data-form-id="6362" data-form-type="testimonial" method="post" enctype="multipart/form-data"><input type="hidden" name="form_id" value="6362"/><div class="form-messages" style="display:none"></div><div class="row g-1"><!-- wp:codeweber-blocks/form-field {"fieldName":"name","fieldLabel":"Имя","isRequired":true,"fieldColumnsMd":"6","showForGuestsOnly":true} -->
<div class="wp-block-codeweber-blocks-form-field form-field-wrapper col-12 col-md-6"><div class="form-floating"><input type="text" class="form-control" id="field-name" name="name" placeholder="Имя" value="" required/><label for="field-name">Имя <span class="text-danger">*</span></label></div></div>
<!-- /wp:codeweber-blocks/form-field -->

<!-- wp:codeweber-blocks/form-field {"fieldType":"email","fieldName":"email","fieldLabel":"Email","isRequired":true,"fieldColumnsMd":"6","showForGuestsOnly":true} -->
<div class="wp-block-codeweber-blocks-form-field form-field-wrapper col-12 col-md-6"><div class="form-floating"><input type="email" class="form-control" id="field-email" name="email" placeholder="Email" value="" required/><label for="field-email">Email <span class="text-danger">*</span></label></div></div>
<!-- /wp:codeweber-blocks/form-field -->

<!-- wp:codeweber-blocks/form-field {"fieldType":"company","fieldName":"company","fieldColumnsMd":"6"} -->
<div class="wp-block-codeweber-blocks-form-field form-field-wrapper col-12 col-md-6"><div class="form-floating"><input type="company" class="form-control" id="field-company" name="company" placeholder="" value=""/><label for="field-company"></label></div></div>
<!-- /wp:codeweber-blocks/form-field -->

<!-- wp:codeweber-blocks/form-field {"fieldType":"author_role","fieldName":"role","fieldColumnsMd":"6"} -->
<div class="wp-block-codeweber-blocks-form-field form-field-wrapper col-12 col-md-6"><div class="form-floating"><input type="author_role" class="form-control" id="field-role" name="role" placeholder="" value=""/><label for="field-role"></label></div></div>
<!-- /wp:codeweber-blocks/form-field -->

<!-- wp:codeweber-blocks/form-field {"fieldType":"textarea","fieldName":"message","fieldLabel":"Ваш отзыв","isRequired":true} -->
<div class="wp-block-codeweber-blocks-form-field form-field-wrapper col-12"><div class="form-floating"><textarea class="form-control" id="field-message" name="message" placeholder="Ваш отзыв" required style="height:120px" defaultvalue=""></textarea><label for="field-message">Ваш отзыв <span class="text-danger">*</span></label></div></div>
<!-- /wp:codeweber-blocks/form-field -->

<!-- wp:codeweber-blocks/form-field {"fieldType":"rating","fieldName":"rating","isRequired":true} -->
<div class="wp-block-codeweber-blocks-form-field form-field-wrapper col-12"><div class="form-floating"><input type="rating" class="form-control" id="field-rating" name="rating" placeholder="" value="" required/><label for="field-rating"> <span class="text-danger">*</span></label></div></div>
<!-- /wp:codeweber-blocks/form-field -->

<!-- wp:codeweber-blocks/form-field {"fieldType":"consents_block","consents":[{"label":"Я даю свое <a href="{document_url}">согласие</a> на обработку моих персональных данных.","document_id":6248,"required":true},{"label":"Я согласен <a href="{document_url}">получать информационные и рекламные рассылки</a>.","document_id":4981,"required":false}]} /-->

<!-- wp:codeweber-blocks/submit-button {"buttonText":"Отправить"} -->
<div class="wp-block-codeweber-blocks-submit-button form-submit-wrapper mt-4"><button type="submit" class="btn btn-primary" data-loading-text="Sending..."><i class="uil uil-send fs-13"></i>Отправить</button></div>
<!-- /wp:codeweber-blocks/submit-button --></div></form></div>
<!-- /wp:codeweber-blocks/form -->',
            'form_type' => 'testimonial',
        ),
        array(
            'title' => 'Newsletter',
            'slug' => 'newsletter',
            'content' => '<!-- wp:codeweber-blocks/form {"formId":"6344","formFields":[{"fieldType":"newsletter","fieldName":"999","fieldLabel":"","placeholder":"","isRequired":true,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","showForGuestsOnly":false},{"fieldType":"consents_block","fieldName":"","fieldLabel":"","placeholder":"","isRequired":false,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[{"label":"Я согласен <a href="{document_url}">получать информационные и рекламные рассылки</a>.","document_id":4981,"required":true},{"label":"Я ознакомился с <a href="{document_url}">документом о политике обработки персональных данных.</a>","document_id":4973,"required":true}],"buttonText":"","buttonClass":"btn btn-primary","showForGuestsOnly":false}]} -->
<div class="wp-block-codeweber-blocks-form codeweber-form-wrapper"><form id="form-6344" class="codeweber-form" data-form-id="6344" data-form-type="form" method="post" enctype="multipart/form-data"><input type="hidden" name="form_id" value="6344"/><div class="form-messages" style="display:none"></div><div class="row g-4"><!-- wp:codeweber-blocks/form-field {"fieldType":"newsletter","fieldName":"999","isRequired":true} /-->

<!-- wp:codeweber-blocks/form-field {"fieldType":"consents_block","consents":[{"label":"Я согласен <a href="{document_url}">получать информационные и рекламные рассылки</a>.","document_id":4981,"required":true},{"label":"Я ознакомился с <a href="{document_url}">документом о политике обработки персональных данных.</a>","document_id":4973,"required":true}]} /--></div></form></div>
<!-- /wp:codeweber-blocks/form -->',
            'form_type' => 'newsletter',
        ),
        array(
            'title' => 'Callback',
            'slug' => 'callback',
            'content' => '<!-- wp:codeweber-blocks/form {"formId":"7527","formFields":[{"fieldType":"tel","fieldName":"phone","fieldLabel":"Номер телефона","placeholder":"+7 (___) ___-__-__","isRequired":true,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","fieldClass":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","enableInlineButton":true,"inlineButtonText":"","inlineButtonClass":"btn btn-primary","showForGuestsOnly":false,"phoneMask":"+7 (___) ___-__-__","phoneMaskCaret":"_","phoneMaskSoftCaret":"_","maxFiles":0,"maxFileSize":"10MB","maxTotalFileSize":"100MB"},{"fieldType":"consents_block","fieldName":"","fieldLabel":"Согласия","placeholder":"","isRequired":false,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","fieldClass":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","enableInlineButton":false,"inlineButtonText":"","inlineButtonClass":"btn btn-primary","showForGuestsOnly":false,"phoneMask":"","phoneMaskCaret":"_","phoneMaskSoftCaret":"_","maxFiles":0,"maxFileSize":"10MB","maxTotalFileSize":"100MB"}]} -->
<div class="wp-block-codeweber-blocks-form codeweber-form-wrapper"><form id="form-7527" class="codeweber-form" data-form-id="7527" data-form-type="form" method="post" enctype="multipart/form-data"><input type="hidden" name="form_id" value="7527"/><div class="form-messages" style="display:none"></div><div class="row g-4"><!-- wp:codeweber-gutenberg-blocks/heading-subtitle {"enableSubtitle":false,"enableText":true,"title":"Заказ обратного звонка","text":"Оставьте заявку на обратный звонок. Мы свяжемся с вами в ближайшее время."} -->
<h2 class="text-left">Заказ обратного звонка</h2><p>Оставьте заявку на обратный звонок. Мы свяжемся с вами в ближайшее время.</p>
<!-- /wp:codeweber-gutenberg-blocks/heading-subtitle -->

<!-- wp:codeweber-blocks/form-field {"fieldType":"tel","fieldName":"phone","fieldLabel":"Номер телефона","placeholder":"+7 (___) ___-__-__","isRequired":true,"enableInlineButton":true,"phoneMask":"+7 (___) ___-__-__"} /-->

<!-- wp:codeweber-blocks/form-field {"fieldType":"consents_block","fieldLabel":"Согласия"} /--></div></form></div>
<!-- /wp:codeweber-blocks/form -->',
            'form_type' => 'callback',
        ),
        array(
            'title' => 'Resume',
            'slug' => 'resume',
            'content' => '<!-- wp:codeweber-blocks/form {"formId":"7545","formFields":[{"fieldType":"text","fieldName":"name","fieldLabel":"Имя","placeholder":"","isRequired":true,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","fieldClass":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","enableInlineButton":false,"inlineButtonText":"","inlineButtonClass":"btn btn-primary","showForGuestsOnly":false,"phoneMask":"","phoneMaskCaret":"_","phoneMaskSoftCaret":"_","maxFiles":0,"maxFileSize":"10MB","maxTotalFileSize":"100MB"},{"fieldType":"email","fieldName":"email","fieldLabel":"Email","placeholder":"","isRequired":true,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","fieldClass":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","enableInlineButton":false,"inlineButtonText":"","inlineButtonClass":"btn btn-primary","showForGuestsOnly":false,"phoneMask":"","phoneMaskCaret":"_","phoneMaskSoftCaret":"_","maxFiles":0,"maxFileSize":"10MB","maxTotalFileSize":"100MB"},{"fieldType":"tel","fieldName":"phone","fieldLabel":"Номер телефона","placeholder":"+7 (___) ___-__-__","isRequired":true,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","fieldClass":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","enableInlineButton":false,"inlineButtonText":"","inlineButtonClass":"btn btn-primary","showForGuestsOnly":false,"phoneMask":"+7 (___) ___-__-__","phoneMaskCaret":"_","phoneMaskSoftCaret":"_","maxFiles":0,"maxFileSize":"10MB","maxTotalFileSize":"100MB"},{"fieldType":"file","fieldName":"file","fieldLabel":"Загрузка файла","placeholder":"","isRequired":false,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","fieldClass":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"pdf,doc,docx","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","enableInlineButton":false,"inlineButtonText":"","inlineButtonClass":"btn btn-primary","showForGuestsOnly":false,"phoneMask":"","phoneMaskCaret":"_","phoneMaskSoftCaret":"_","maxFiles":1,"maxFileSize":"10MB","maxTotalFileSize":"10MB"},{"fieldType":"consents_block","fieldName":"","fieldLabel":"Согласия","placeholder":"","isRequired":false,"maxLength":0,"minLength":0,"width":"col-12","fieldColumns":"12","fieldColumnsXs":"","fieldColumnsSm":"","fieldColumnsMd":"","fieldColumnsLg":"","fieldColumnsXl":"","fieldColumnsXxl":"","fieldClass":"","blockClass":"","blockData":"","blockId":"","validationType":"none","accept":"","multiple":false,"options":[],"defaultValue":"","helpText":"","consents":[],"buttonText":"","buttonClass":"btn btn-primary","enableInlineButton":false,"inlineButtonText":"","inlineButtonClass":"btn btn-primary","showForGuestsOnly":false,"phoneMask":"","phoneMaskCaret":"_","phoneMaskSoftCaret":"_","maxFiles":0,"maxFileSize":"10MB","maxTotalFileSize":"100MB"}],"formType":"resume"} -->
<div class="wp-block-codeweber-blocks-form codeweber-form-wrapper"><form id="form-7545" class="codeweber-form" data-form-id="7545" data-form-type="resume" method="post" enctype="multipart/form-data"><input type="hidden" name="form_id" value="7545"/><div class="form-messages" style="display:none"></div><div class="row g-4"><!-- wp:codeweber-gutenberg-blocks/heading-subtitle {"enableSubtitle":false,"enableText":true,"title":"Форма отправки Резюме","text":"Заполните форму ниже и прикрепите резюме."} -->
<h2 class="text-left">Форма отправки Резюме</h2><p>Заполните форму ниже и прикрепите резюме.</p>
<!-- /wp:codeweber-gutenberg-blocks/heading-subtitle -->

<!-- wp:codeweber-blocks/form-field {"fieldName":"name","fieldLabel":"Имя","isRequired":true} -->
<div class="wp-block-codeweber-blocks-form-field form-field-wrapper col-12"><div class="form-floating"><input type="text" class="form-control" id="field-name" name="name" placeholder="Имя" value="" required/><label for="field-name">Имя <span class="text-danger">*</span></label></div></div>
<!-- /wp:codeweber-blocks/form-field -->

<!-- wp:codeweber-blocks/form-field {"fieldType":"email","fieldName":"email","fieldLabel":"Email","isRequired":true} -->
<div class="wp-block-codeweber-blocks-form-field form-field-wrapper col-12"><div class="form-floating"><input type="email" class="form-control" id="field-email" name="email" placeholder="Email" value="" required/><label for="field-email">Email <span class="text-danger">*</span></label></div></div>
<!-- /wp:codeweber-blocks/form-field -->

<!-- wp:codeweber-blocks/form-field {"fieldType":"tel","fieldName":"phone","fieldLabel":"Номер телефона","placeholder":"+7 (___) ___-__-__","isRequired":true,"phoneMask":"+7 (___) ___-__-__"} -->
<div class="wp-block-codeweber-blocks-form-field form-field-wrapper col-12"><div class="form-floating"><input type="tel" class="form-control" id="field-phone" name="phone" placeholder="+7 (___) ___-__-__" value="" required data-mask="+7 (___) ___-__-__" data-mask-caret="_" data-mask-soft-caret="_"/><label for="field-phone">Номер телефона <span class="text-danger">*</span></label></div></div>
<!-- /wp:codeweber-blocks/form-field -->

<!-- wp:codeweber-blocks/form-field {"fieldType":"file","fieldName":"file","fieldLabel":"Загрузка файла","accept":"pdf,doc,docx","maxFiles":1,"maxTotalFileSize":"10MB"} -->
<div class="wp-block-codeweber-blocks-form-field form-field-wrapper col-12"><div><label for="field-file" class="form-label">Загрузка файла</label><div class="input-group"><input type="file" class="filepond" id="field-file" name="file" data-filepond="true" data-max-files="1" data-max-file-size="10MB" data-max-total-file-size="10MB" accept=".pdf,.doc,.docx"/></div></div></div>
<!-- /wp:codeweber-blocks/form-field -->

<!-- wp:codeweber-blocks/form-field {"fieldType":"consents_block","fieldLabel":"Согласия"} /-->

<!-- wp:codeweber-blocks/submit-button {"buttonText":"Отправить"} -->
<div class="wp-block-codeweber-blocks-submit-button form-submit-wrapper mt-4"><button type="submit" class="btn btn-primary" data-loading-text="Sending..."><span>Отправить</span></button></div>
<!-- /wp:codeweber-blocks/submit-button --></div></form></div>
<!-- /wp:codeweber-blocks/form -->',
            'form_type' => 'resume',
        ),
    );
}

/**
 * Рекурсивно обновить formId в блоках
 * 
 * @param array $blocks Массив блоков
 * @param int $form_id Новый ID формы
 * @return array Обновленные блоки
 */
function cw_demo_update_form_blocks($blocks, $form_id) {
    foreach ($blocks as &$block) {
        // Обновляем formId в атрибутах блока формы
        if (!empty($block['blockName']) && $block['blockName'] === 'codeweber-blocks/form') {
            if (isset($block['attrs']['formId'])) {
                $block['attrs']['formId'] = (string) $form_id;
            }
        }
        
        // Рекурсивно обрабатываем вложенные блоки
        if (!empty($block['innerBlocks'])) {
            $block['innerBlocks'] = cw_demo_update_form_blocks($block['innerBlocks'], $form_id);
        }
        
        // Обновляем innerContent: JSON атрибуты в комментариях и HTML атрибуты в сгенерированном HTML
        if (!empty($block['innerContent']) && is_array($block['innerContent'])) {
            foreach ($block['innerContent'] as &$inner_content) {
                if (is_string($inner_content)) {
                    // Обновляем JSON атрибуты в комментариях блоков (<!-- wp:... -->)
                    if (preg_match('/<!--\s*wp:/', $inner_content)) {
                        $inner_content = preg_replace(
                            '/(["\']formId["\']\s*:\s*["\']?)(\d+)(["\']?)/',
                            '$1' . $form_id . '$3',
                            $inner_content
                        );
                    }
                    
                    // Обновляем HTML атрибуты в сгенерированном HTML (только для блока формы)
                    // Делаем это только если это HTML, а не комментарий блока
                    if (!empty($block['blockName']) && $block['blockName'] === 'codeweber-blocks/form' && !preg_match('/<!--\s*wp:/', $inner_content)) {
                        // Обновляем id="form-число" - используем точный паттерн
                        $inner_content = preg_replace_callback(
                            '/(id=["\']form-)(\d+)(["\'])/',
                            function($matches) use ($form_id) {
                                return $matches[1] . $form_id . $matches[3];
                            },
                            $inner_content
                        );
                        // Обновляем data-form-id="число"
                        $inner_content = preg_replace_callback(
                            '/(data-form-id=["\'])(\d+)(["\'])/',
                            function($matches) use ($form_id) {
                                return $matches[1] . $form_id . $matches[3];
                            },
                            $inner_content
                        );
                        // Обновляем скрытое поле name="form_id" value="число"
                        $inner_content = preg_replace_callback(
                            '/(name=["\']form_id["\']\s+value=["\'])(\d+)(["\'])/',
                            function($matches) use ($form_id) {
                                return $matches[1] . $form_id . $matches[3];
                            },
                            $inner_content
                        );
                    }
                }
            }
            unset($inner_content);
        }
    }
    unset($block);
    
    return $blocks;
}

/**
 * Создать одну demo форму
 * 
 * @param array $form_data Данные формы
 * @return int|false ID созданной записи или false при ошибке
 */
function cw_demo_create_form_post($form_data) {
    // Проверяем обязательные поля
    if (empty($form_data['title']) || empty($form_data['content'])) {
        error_log('Demo Forms: Отсутствуют обязательные поля');
        return false;
    }
    
    // Сначала создаем пост без контента, чтобы получить ID
    $post_data = array(
        'post_title'    => sanitize_text_field($form_data['title']),
        'post_name'     => !empty($form_data['slug']) ? sanitize_title($form_data['slug']) : sanitize_title($form_data['title']),
        'post_status'   => 'publish',
        'post_type'     => 'codeweber_form',
        'post_author'   => get_current_user_id(),
        'post_content'  => '', // Временно пустой, обновим после получения ID
    );
    
    // Вставляем запись
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        error_log('Demo Forms: ' . __('Error creating record', 'codeweber') . ' - ' . $post_id->get_error_message());
        return false;
    }
    
    // Обновляем formId в контенте на актуальный ID поста
    $content = $form_data['content'];
    
    // Если контент содержит Gutenberg блоки, используем parse_blocks для правильной обработки
    if (function_exists('parse_blocks') && has_blocks($content)) {
        $blocks = parse_blocks($content);
        
        // Рекурсивно обновляем formId в блоках
        $updated_blocks = cw_demo_update_form_blocks($blocks, $post_id);
        
        // Преобразуем обратно в HTML
        $content = '';
        foreach ($updated_blocks as $block) {
            $content .= serialize_block($block);
        }
    } else {
        // Fallback: используем regex для замены в HTML
        // Заменяем formId в JSON атрибутах комментария блока (с кавычками или без)
        $json_form_id_pattern = '/(["\']formId["\']\s*:\s*["\']?)(\d+)(["\']?)/';
        $content = preg_replace($json_form_id_pattern, '$1' . $post_id . '$3', $content);
        
        // Заменяем data-form-id
        $data_form_id_pattern = '/(data-form-id=["\'])(\d+)(["\'])/';
        $content = preg_replace($data_form_id_pattern, '$1' . $post_id . '$3', $content);
        
        // Заменяем id формы (form-6362)
        $form_id_pattern = '/(id=["\']form-)(\d+)(["\'])/';
        $content = preg_replace($form_id_pattern, '$1' . $post_id . '$3', $content);
        
        // Заменяем скрытое поле form_id
        $hidden_form_id_pattern = '/(name=["\']form_id["\']\s+value=["\'])(\d+)(["\'])/';
        $content = preg_replace($hidden_form_id_pattern, '$1' . $post_id . '$3', $content);
    }
    
    // Обновляем пост с правильным контентом
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $content,
    ));
    
    // Добавляем мета-поле для идентификации demo записей
    update_post_meta($post_id, '_demo_created', true);
    
    // Устанавливаем тип формы, если указан
    if (!empty($form_data['form_type'])) {
        update_post_meta($post_id, '_form_type', sanitize_text_field($form_data['form_type']));
    }
    
    return $post_id;
}

/**
 * Создать все demo формы
 * 
 * @return array Результат выполнения
 */
function cw_demo_create_forms() {
    $data = cw_demo_get_forms_data();
    
    if (empty($data)) {
        return array(
            'success' => false,
            'message' => __('No data found', 'codeweber'),
            'created' => 0,
            'errors' => array()
        );
    }
    
    $created = 0;
    $errors = array();
    
    foreach ($data as $item) {
        $post_id = cw_demo_create_form_post($item);
        
        if ($post_id) {
            $created++;
        } else {
            $errors[] = __('Failed to create:', 'codeweber') . ' ' . (!empty($item['title']) ? $item['title'] : __('unknown', 'codeweber'));
        }
    }
    
    $message = sprintf(__('%1$d of %2$d forms created', 'codeweber'), $created, count($data));
    
    return array(
        'success' => true,
        'message' => $message,
        'created' => $created,
        'total' => count($data),
        'errors' => $errors
    );
}

/**
 * Удалить все demo формы
 * 
 * @return array Результат выполнения
 */
function cw_demo_delete_forms() {
    $args = array(
        'post_type' => 'codeweber_form',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_demo_created',
                'value' => true,
                'compare' => '='
            )
        ),
        'fields' => 'ids'
    );
    
    $posts = get_posts($args);
    $deleted = 0;
    $errors = array();
    
    foreach ($posts as $post_id) {
        // Удаляем запись
        $result = wp_delete_post($post_id, true);
        
        if ($result) {
            $deleted++;
        } else {
            $errors[] = sprintf(__('Failed to delete record ID: %d', 'codeweber'), $post_id);
        }
    }
    
    return array(
        'success' => true,
        'message' => sprintf(__('%d forms deleted', 'codeweber'), $deleted),
        'deleted' => $deleted,
        'errors' => $errors
    );
}










