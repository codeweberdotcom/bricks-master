(function($) {
    'use strict';

    var config = window.codeweberEmailTemplates || {};
    if (!config.ajaxUrl || !config.nonce || !config.editorIds) {
        return;
    }

    function getEditorContent(editorId) {
        if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
            return tinymce.get(editorId).getContent();
        }
        var el = document.getElementById(editorId);
        return el ? el.value : '';
    }

    function updatePreview(panel) {
        var templateId = panel.getAttribute('data-template');
        var editorId = config.editorIds[templateId];
        var iframe = panel.querySelector('.codeweber-email-preview-iframe');
        var btn = panel.querySelector('.codeweber-email-preview-btn');
        if (!iframe || !editorId) return;

        if (btn) btn.disabled = true;

        $.post(config.ajaxUrl, {
            action: 'codeweber_forms_email_preview',
            nonce: config.nonce,
            template_id: templateId,
            html: getEditorContent(editorId)
        })
            .done(function(res) {
                if (res.success && res.data && res.data.html) {
                    iframe.srcdoc = res.data.html;
                }
            })
            .fail(function() {
                if (iframe.contentDocument) {
                    iframe.contentDocument.body.innerHTML = '<p style="padding:1em;color:#b32d2e;">Preview failed to load.</p>';
                }
            })
            .always(function() {
                if (btn) btn.disabled = false;
            });
    }

    $(document).on('click', '.codeweber-email-preview-btn', function() {
        var panel = $(this).closest('.codeweber-email-templates-panel')[0];
        if (panel) updatePreview(panel);
    });

    $(document).on('click', '.codeweber-email-preset-btn', function() {
        var preset = $(this).data('preset');
        if (!config.wrapperPresets || !config.wrapperPresets[preset]) return;
        if (config.presetConfirm && !window.confirm(config.presetConfirm)) return;
        var panel = $(this).closest('.codeweber-email-templates-panel')[0];
        if (!panel) return;
        var templateId = panel.getAttribute('data-template');
        var editorId = config.editorIds[templateId];
        if (!editorId) return;
        var html = config.wrapperPresets[preset];
        // For Branded preset: apply currently selected colors (overriding server-generated defaults)
        if (preset === 'branded' && config.wrapperColorDefaults) {
            $(panel).find('.codeweber-email-color-select').each(function() {
                var target = $(this).data('color-target');
                var defaultColor = config.wrapperColorDefaults[target];
                var selectedColor = $(this).val();
                if (defaultColor && selectedColor && selectedColor !== defaultColor) {
                    html = html.split('background-color:' + defaultColor).join('background-color:' + selectedColor);
                }
                $(this).data('prev-color', selectedColor);
            });
        }
        if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
            tinymce.get(editorId).setContent(html);
        } else {
            var el = document.getElementById(editorId);
            if (el) {
                el.value = html;
                $(el).trigger('input');
            }
        }
    });

    // Color select: update background-color in textarea + swatch + trigger preview
    $(document).on('change', '.codeweber-email-color-select', function() {
        var oldColor = $(this).data('prev-color');
        var newColor = $(this).val();
        if (!oldColor || oldColor === newColor) return;
        $(this).data('prev-color', newColor);
        // Update swatch
        $('.codeweber-color-swatch[data-for="' + $(this).attr('id') + '"]').css('background', newColor);
        var panel = $(this).closest('.codeweber-email-templates-panel')[0];
        if (!panel) return;
        var templateId = panel.getAttribute('data-template');
        var editorId = config.editorIds[templateId];
        var el = document.getElementById(editorId);
        if (!el) return;
        el.value = el.value.split('background-color:' + oldColor).join('background-color:' + newColor);
        $(el).trigger('input');
    });

    // Email logo upload via WP Media Library
    var logoMediaFrame;
    $(document).on('click', '.codeweber-email-logo-upload-btn', function(e) {
        e.preventDefault();
        if (logoMediaFrame) {
            logoMediaFrame.open();
            return;
        }
        logoMediaFrame = wp.media({
            title: 'Select Email Logo',
            button: { text: 'Use this logo' },
            multiple: false,
            library: { type: ['image/png', 'image/jpeg', 'image/gif', 'image/webp'] }
        });
        logoMediaFrame.on('select', function() {
            var attachment = logoMediaFrame.state().get('selection').first().toJSON();
            $('#wrapper_logo_id').val(attachment.id);
            var img = attachment.sizes && attachment.sizes.thumbnail
                ? attachment.sizes.thumbnail.url
                : attachment.url;
            $('#codeweber-email-logo-preview').html('<img src="' + img + '" style="max-height:48px;max-width:160px;border:1px solid #ddd;padding:4px;background:#fff;border-radius:3px;">');
        });
        logoMediaFrame.open();
    });

    $(document).on('click', '.codeweber-email-logo-remove-btn', function(e) {
        e.preventDefault();
        $('#wrapper_logo_id').val('0');
        $('#codeweber-email-logo-preview').html('');
    });

    // Insert variable at cursor position in a textarea
    $(document).on('click', '.codeweber-email-var-btn', function() {
        var variable = $(this).data('var');
        if (!variable) return;
        var panel = $(this).closest('.codeweber-email-templates-panel')[0];
        if (!panel) return;
        var templateId = panel.getAttribute('data-template');
        var editorId = config.editorIds[templateId];
        if (!editorId) return;

        if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
            tinymce.get(editorId).insertContent(variable);
            return;
        }

        var el = document.getElementById(editorId);
        if (!el) return;
        var start = el.selectionStart;
        var end = el.selectionEnd;
        var before = el.value.substring(0, start);
        var after = el.value.substring(end);
        el.value = before + variable + after;
        var pos = start + variable.length;
        el.selectionStart = pos;
        el.selectionEnd = pos;
        el.focus();
        $(el).trigger('input');
    });

    // Auto-preview on textarea input (debounced)
    var debounceTimers = {};
    $(document).on('input', 'textarea.codeweber-email-template-textarea', function() {
        var panel = $(this).closest('.codeweber-email-templates-panel')[0];
        if (!panel) return;
        var templateId = panel.getAttribute('data-template');
        clearTimeout(debounceTimers[templateId]);
        debounceTimers[templateId] = setTimeout(function() {
            updatePreview(panel);
        }, 800);
    });

    $(function() {
        // Store initial color for each color select (used for background-color replacement on change)
        $('.codeweber-email-color-select').each(function() {
            $(this).data('prev-color', $(this).val());
        });

        var active = document.querySelector('.codeweber-email-templates-panel.is-active');
        if (active) {
            updatePreview(active);
        }
    });
})(jQuery);
