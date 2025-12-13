/**
 * Universal AJAX Filter
 * 
 * Универсальный AJAX фильтр для:
 * - Вакансий (vacancies)
 * - Статей (posts)
 * - WooCommerce товаров (products)
 * - Staff
 * 
 * Использование:
 * 1. Добавьте класс .codeweber-filter-form к форме фильтрации
 * 2. Добавьте data-* атрибуты:
 *    - data-post-type="vacancies" (тип записи)
 *    - data-template="vacancies_1" (опционально, шаблон для рендеринга)
 *    - data-container=".vacancies-container" (селектор контейнера для результатов)
 * 3. Добавьте data-filter-name к полям формы (name будет использован как ключ фильтра)
 * 
 * Пример:
 * <form class="codeweber-filter-form" data-post-type="vacancies" data-template="vacancies_1" data-container=".vacancies-results">
 *   <select name="position" data-filter-name="position">
 *     <option value="">All</option>
 *   </select>
 * </form>
 * <div class="vacancies-results">...</div>
 */

(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const filterForms = document.querySelectorAll('.codeweber-filter-form');
        
        if (!filterForms.length) {
            return;
        }
        
        // Проверяем наличие необходимых данных
        if (typeof codeweberFilter === 'undefined') {
            console.error('codeweberFilter is not defined. Make sure ajax-filter.js is enqueued properly.');
            return;
        }
        
        filterForms.forEach(function(form) {
            initFilterForm(form);
        });
    });
    
    /**
     * Инициализирует форму фильтрации
     */
    function initFilterForm(form) {
        const postType = form.getAttribute('data-post-type');
        const template = form.getAttribute('data-template') || '';
        const containerSelector = form.getAttribute('data-container') || '.filter-results';
        const container = document.querySelector(containerSelector);
        
        if (!postType) {
            console.error('data-post-type attribute is required on filter form');
            return;
        }
        
        if (!container) {
            console.error('Container not found:', containerSelector);
            return;
        }
        
        // Получаем все поля фильтрации
        const filterFields = form.querySelectorAll('[data-filter-name], select[name], input[name]');
        
        // Обработчик изменения полей
        filterFields.forEach(function(field) {
            const fieldType = field.tagName.toLowerCase();
            
            if (fieldType === 'select') {
                field.addEventListener('change', function() {
                    performFilter(form, postType, template, container);
                });
            } else if (fieldType === 'input') {
                const inputType = field.getAttribute('type');
                
                if (inputType === 'checkbox' || inputType === 'radio') {
                    field.addEventListener('change', function() {
                        performFilter(form, postType, template, container);
                    });
                } else if (inputType === 'text' || inputType === 'number') {
                    // Debounce для текстовых полей
                    let timeout;
                    field.addEventListener('input', function() {
                        clearTimeout(timeout);
                        timeout = setTimeout(function() {
                            performFilter(form, postType, template, container);
                        }, 500);
                    });
                }
            }
        });
        
        // Обработчик отправки формы (если есть кнопка submit)
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            performFilter(form, postType, template, container);
        });
    }
    
    /**
     * Выполняет AJAX фильтрацию
     */
    function performFilter(form, postType, template, container) {
        // Собираем данные формы
        const filters = {};
        
        // Получаем все поля с data-filter-name или name
        const filterFields = form.querySelectorAll('[data-filter-name], select[name], input[name]');
        
        filterFields.forEach(function(field) {
            const filterName = field.getAttribute('data-filter-name') || field.getAttribute('name');
            
            if (!filterName) {
                return;
            }
            
            const fieldType = field.tagName.toLowerCase();
            let value = '';
            
            if (fieldType === 'select') {
                value = field.value;
            } else if (fieldType === 'input') {
                const inputType = field.getAttribute('type');
                
                if (inputType === 'checkbox' || inputType === 'radio') {
                    if (field.checked) {
                        value = field.value;
                    } else {
                        return; // Пропускаем невыбранные чекбоксы/радио
                    }
                } else {
                    value = field.value;
                }
            }
            
            // Добавляем только непустые значения
            if (value) {
                filters[filterName] = value;
            }
        });
        
        // Показываем индикатор загрузки
        showLoading(container);
        
        // Формируем данные для отправки
        const ajaxData = new FormData();
        ajaxData.append('action', 'codeweber_filter');
        ajaxData.append('nonce', codeweberFilter.nonce);
        ajaxData.append('post_type', postType);
        ajaxData.append('template', template);
        ajaxData.append('container_selector', container.className || container.id || '');
        ajaxData.append('filters', JSON.stringify(filters));
        
        // Отправляем AJAX запрос
        const xhr = new XMLHttpRequest();
        xhr.open('POST', codeweberFilter.ajaxUrl, true);
        
        xhr.onload = function() {
            hideLoading(container);
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        // Обновляем контент
                        container.innerHTML = response.data.html;
                        
                        // Обновляем URL без перезагрузки страницы
                        updateURL(filters);
                        
                        // Вызываем событие для других скриптов
                        const event = new CustomEvent('codeweber:filter:updated', {
                            detail: {
                                postType: postType,
                                filters: filters,
                                foundPosts: response.data.found_posts
                            }
                        });
                        document.dispatchEvent(event);
                    } else {
                        showError(container, response.data.message || 'Error filtering content');
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    showError(container, 'Error processing response');
                }
            } else {
                showError(container, 'Server error: ' + xhr.status);
            }
        };
        
        xhr.onerror = function() {
            hideLoading(container);
            showError(container, 'Network error');
        };
        
        xhr.send(ajaxData);
    }
    
    /**
     * Показывает индикатор загрузки
     */
    function showLoading(container) {
        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';
        
        // Добавляем класс для стилизации
        container.classList.add('filter-loading');
    }
    
    /**
     * Скрывает индикатор загрузки
     */
    function hideLoading(container) {
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
        container.classList.remove('filter-loading');
    }
    
    /**
     * Показывает сообщение об ошибке
     */
    function showError(container, message) {
        container.innerHTML = '<div class="alert alert-danger">' + 
            (codeweberFilter.translations?.error || 'Error') + ': ' + 
            message + 
            '</div>';
    }
    
    /**
     * Обновляет URL без перезагрузки страницы
     */
    function updateURL(filters) {
        const url = new URL(window.location.href);
        
        // Очищаем старые параметры фильтра
        url.searchParams.forEach(function(value, key) {
            if (key.startsWith('filter_')) {
                url.searchParams.delete(key);
            }
        });
        
        // Добавляем новые параметры
        Object.keys(filters).forEach(function(key) {
            url.searchParams.set('filter_' + key, filters[key]);
        });
        
        // Обновляем URL без перезагрузки
        window.history.pushState({}, '', url);
    }
})();

