/**
 * Form Selector Block
 * 
 * Блок для выбора и отображения формы из CPT codeweber_form
 * Доступен только на обычных страницах/постах (не в CPT форм)
 * 
 * @package Codeweber
 */

(function() {
    'use strict';

    if (typeof wp === 'undefined' || typeof wp.blocks === 'undefined') {
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { useEffect, useState } = wp.element;
    const { InspectorControls, useBlockProps, BlockControls } = wp.blockEditor;
    const { PanelBody, SelectControl, Spinner, ToolbarButton } = wp.components;
    const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;
    const { __ } = wp.i18n;

    /**
     * Edit component
     */
    function Edit({ attributes, setAttributes }) {
        const { formId, formProvider, cf7FormId } = attributes;
        const [forms, setForms] = useState([]);
        const [loading, setLoading] = useState(true);
        const [cf7Forms, setCf7Forms] = useState([]);
        const [cf7Loading, setCf7Loading] = useState(false);
        

        // Загружаем список форм при монтировании компонента
        useEffect(function() {
            const fetchForms = async function() {
                try {
                    const nonce = (typeof codeweberFormsBlock !== 'undefined' && codeweberFormsBlock.nonce) 
                        ? codeweberFormsBlock.nonce 
                        : ((typeof wpApiSettings !== 'undefined' && wpApiSettings.nonce) ? wpApiSettings.nonce : '');
                    const restUrl = (typeof codeweberFormsBlock !== 'undefined' && codeweberFormsBlock.restUrl) 
                        ? codeweberFormsBlock.restUrl 
                        : '/wp-json/codeweber-forms/v1/';
                    
                    const response = await fetch(restUrl + 'forms', {
                        headers: {
                            'X-WP-Nonce': nonce,
                        },
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.success && data.forms) {
                            setForms(data.forms);
                        }
                    }
                } catch (error) {
                    console.error('Error fetching forms:', error);
                } finally {
                    setLoading(false);
                }
            };

            fetchForms();
        }, []);

        // Загружаем CF7 формы
        useEffect(function() {
            if (formProvider !== 'cf7') return;
            setCf7Loading(true);
            var nonce = (typeof codeweberFormsBlock !== 'undefined' && codeweberFormsBlock.nonce)
                ? codeweberFormsBlock.nonce
                : ((typeof wpApiSettings !== 'undefined' && wpApiSettings.nonce) ? wpApiSettings.nonce : '');
            fetch('/wp-json/contact-form-7/v1/contact-forms?per_page=100', {
                headers: { 'X-WP-Nonce': nonce },
            })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    var items = (res && res.items) ? res.items : (Array.isArray(res) ? res : []);
                    setCf7Forms(items.map(function(f) {
                        return { label: f.title || String(f.id), value: String(f.id) };
                    }));
                })
                .catch(function() { setCf7Forms([]); })
                .finally(function() { setCf7Loading(false); });
        }, [formProvider]);

        // Формируем опции для SelectControl
        const formOptions = [
            { label: __('Select a form...', 'codeweber'), value: '' },
            ...forms.map(function(form) {
                return {
                    label: form.title + ' (ID: ' + form.id + ')',
                    value: String(form.id),
                };
            }),
        ];

        // Получаем выбранную форму для отображения превью
        const selectedForm = forms.find(function(f) {
            return String(f.id) === formId;
        });

        // Используем useBlockProps для правильной обертки блока
        const blockProps = useBlockProps({
            className: 'codeweber-form-selector-block',
        });


        // Функция для открытия страницы редактирования формы в новом окне
        const handleEditForm = function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            if (formId) {
                // Формируем URL для редактирования формы
                // Используем wp.url.addQueryArgs если доступен, иначе формируем вручную
                let editUrl;
                if (typeof wp !== 'undefined' && wp.url && wp.url.addQueryArgs) {
                    editUrl = wp.url.addQueryArgs('post.php', {
                        post: formId,
                        action: 'edit'
                    });
                } else {
                    // Fallback: формируем URL вручную
                    const adminUrl = (typeof ajaxurl !== 'undefined') 
                        ? ajaxurl.replace('/admin-ajax.php', '') 
                        : window.location.origin + '/wp-admin/';
                    editUrl = adminUrl + 'post.php?post=' + formId + '&action=edit';
                }
                window.open(editUrl, '_blank', 'noopener,noreferrer');
            }
        };

        return wp.element.createElement(
            wp.element.Fragment,
            null,
            // Добавляем кнопку редактирования в тулбар блока
            formId && wp.element.createElement(
                BlockControls,
                null,
                wp.element.createElement(
                    ToolbarButton,
                    {
                        icon: 'edit',
                        label: __('Edit Form', 'codeweber'),
                        onClick: handleEditForm,
                    }
                )
            ),
            wp.element.createElement(
                InspectorControls,
                null,
                wp.element.createElement(
                    PanelBody,
                    { title: __('Form Settings', 'codeweber'), initialOpen: true },
                    wp.element.createElement(
                        SelectControl,
                        {
                            label: __('Form Provider', 'codeweber'),
                            value: formProvider || 'codeweber',
                            options: [
                                { label: 'CodeWeber Form', value: 'codeweber' },
                                { label: 'Contact Form 7', value: 'cf7' },
                            ],
                            onChange: function(value) {
                                setAttributes({ formProvider: value });
                            },
                        }
                    ),
                    formProvider === 'cf7' ? (
                        cf7Loading ? wp.element.createElement(Spinner) : wp.element.createElement(
                            SelectControl,
                            {
                                label: __('Select CF7 Form', 'codeweber'),
                                value: cf7FormId || '',
                                options: [
                                    { label: __('Select a form...', 'codeweber'), value: '' },
                                    ...cf7Forms,
                                ],
                                onChange: function(value) {
                                    setAttributes({ cf7FormId: value });
                                },
                            }
                        )
                    ) : loading ? wp.element.createElement(Spinner) : wp.element.createElement(
                        SelectControl,
                        {
                            label: __('Select Form', 'codeweber'),
                            value: formId || '',
                            options: formOptions,
                            onChange: function(value) {
                                setAttributes({ formId: value });
                            },
                            help: __('Choose a form from Forms CPT to display', 'codeweber'),
                        }
                    ),
                    formProvider !== 'cf7' && selectedForm && wp.element.createElement(
                        'p',
                        { style: { marginTop: '10px', fontSize: '12px', color: '#666' } },
                        wp.element.createElement('strong', null, __('Shortcode:', 'codeweber')),
                        wp.element.createElement('br'),
                        wp.element.createElement('code', null, selectedForm.shortcode),
                        wp.element.createElement('br'),
                        wp.element.createElement('br'),
                        wp.element.createElement(
                            'a',
                            {
                                href: '#',
                                onClick: handleEditForm,
                                style: {
                                    fontSize: '12px',
                                    textDecoration: 'none',
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    gap: '4px',
                                },
                            },
                            wp.element.createElement('span', { className: 'dashicons dashicons-edit', style: { fontSize: '16px', width: '16px', height: '16px' } }),
                            __('Edit Form', 'codeweber')
                        )
                    )
                )
            ),
            wp.element.createElement(
                'div',
                blockProps,
                (formProvider === 'cf7' ? !cf7FormId : !formId) ? wp.element.createElement(
                    'div',
                    { style: { textAlign: 'center', color: '#666', padding: '20px' } },
                    wp.element.createElement('p', null, __('Select a form from the sidebar to display it here', 'codeweber'))
                ) : ServerSideRender ? wp.element.createElement(
                    ServerSideRender,
                    {
                        block: 'codeweber-blocks/form-selector',
                        attributes: attributes,
                        httpMethod: 'POST',
                        key: (formProvider === 'cf7' ? 'cf7-' + cf7FormId : 'cw-' + formId),
                    }
                ) : wp.element.createElement(
                    'div',
                    { style: { padding: '20px', textAlign: 'center', color: '#666' } },
                    wp.element.createElement('p', null, __('Loading form preview...', 'codeweber'))
                )
            )
        );
    }

    /**
     * Save component (null - динамический рендеринг через render.php)
     * Для динамических блоков save должен возвращать null
     */
    function Save() {
        return null;
    }

    const newAttributes = {
        formProvider: { type: 'string', default: 'codeweber' },
        cf7FormId: { type: 'string', default: '' },
        formId: { type: 'string', default: '' },
    };

    wp.domReady(function() {
        var existing = wp.blocks.getBlockType('codeweber-blocks/form-selector');
        if (existing) {
            wp.blocks.unregisterBlockType('codeweber-blocks/form-selector');
            registerBlockType('codeweber-blocks/form-selector', Object.assign({}, existing, {
                attributes: Object.assign({}, existing.attributes, newAttributes),
                edit: Edit,
                save: Save,
            }));
        } else {
            registerBlockType('codeweber-blocks/form-selector', {
                apiVersion: 2,
                title: __('Form Selector', 'codeweber'),
                icon: 'feedback',
                category: 'codeweber-gutenberg-blocks',
                description: __('Display a form from Forms CPT', 'codeweber'),
                supports: { html: false, customClassName: true, anchor: true },
                attributes: newAttributes,
                edit: Edit,
                save: Save,
            });
        }
    });
})();
