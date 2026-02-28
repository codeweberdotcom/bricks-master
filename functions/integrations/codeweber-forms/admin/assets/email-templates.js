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

    // Optional: auto-load preview for the active panel on load
    $(function() {
        var active = document.querySelector('.codeweber-email-templates-panel.is-active');
        if (active) {
            updatePreview(active);
        }
    });
})(jQuery);
