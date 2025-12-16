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
        const { formId } = attributes;
        const [forms, setForms] = useState([]);
        const [loading, setLoading] = useState(true);
        
        // Отладка атрибутов
        if (typeof console !== 'undefined' && console.log) {
            console.log('Form Selector Block - Attributes:', attributes);
            console.log('Form Selector Block - formId:', formId);
        }

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
                    loading ? wp.element.createElement(Spinner) : wp.element.createElement(
                        SelectControl,
                        {
                            label: __('Select Form', 'codeweber'),
                            value: formId || '',
                            options: formOptions,
                            onChange: function(value) {
                                console.log('Setting formId to:', value);
                                setAttributes({ formId: value });
                            },
                            help: __('Choose a form from Forms CPT to display', 'codeweber'),
                        }
                    ),
                    selectedForm && wp.element.createElement(
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
                !formId ? wp.element.createElement(
                    'div',
                    { style: { textAlign: 'center', color: '#666', padding: '20px' } },
                    wp.element.createElement('p', null, __('Select a form from the sidebar to display it here', 'codeweber'))
                ) : ServerSideRender ? wp.element.createElement(
                    ServerSideRender,
                    {
                        block: 'codeweber-blocks/form-selector',
                        attributes: attributes,
                        httpMethod: 'GET',
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
     */
    function Save() {
        return null;
    }

    // Проверяем, зарегистрирован ли блок уже в PHP
    const existingBlock = wp.blocks.getBlockType('codeweber-blocks/form-selector');
    
    if (existingBlock) {
        // Блок уже зарегистрирован в PHP с render_callback
        // Обновляем только edit и save компоненты, сохраняя render_callback
        existingBlock.edit = Edit;
        existingBlock.save = Save;
        
        // Также обновляем метаданные блока для отображения в редакторе
        if (existingBlock.title !== __('Form Selector', 'codeweber')) {
            existingBlock.title = __('Form Selector', 'codeweber');
        }
        if (existingBlock.category !== 'codeweber-gutenberg-blocks') {
            existingBlock.category = 'codeweber-gutenberg-blocks';
        }
    } else {
        // Блок не зарегистрирован в PHP, регистрируем его в JavaScript
        registerBlockType('codeweber-blocks/form-selector', {
            apiVersion: 2,
            title: __('Form Selector', 'codeweber'),
            icon: 'feedback',
            category: 'codeweber-gutenberg-blocks',
            description: __('Display a form from Forms CPT', 'codeweber'),
            supports: {
                html: false,
                customClassName: true,
                anchor: true,
            },
            attributes: {
                formId: {
                    type: 'string',
                    default: '',
                },
            },
            edit: Edit,
            save: Save,
        });
    }
})();
