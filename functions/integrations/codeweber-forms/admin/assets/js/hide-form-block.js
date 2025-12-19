/**
 * Hide codeweber-blocks/form block on regular pages
 * 
 * Блок codeweber-blocks/form должен быть доступен только в CPT codeweber_form
 * На обычных страницах используется блок codeweber-blocks/form-selector
 */

(function() {
    'use strict';

    if (typeof wp === 'undefined' || typeof wp.blocks === 'undefined' || typeof wp.data === 'undefined') {
        return;
    }

    const { select } = wp.data;
    const { unregisterBlockType } = wp.blocks;

    // Проверяем тип поста
    function checkAndHideBlock() {
        try {
            const postType = select('core/editor')?.getCurrentPostType();
            
            // Если это не CPT codeweber_form, скрываем блок формы
            if (postType && postType !== 'codeweber_form') {
                // Пробуем скрыть блок codeweber-blocks/form
                try {
                    unregisterBlockType('codeweber-blocks/form');
                } catch (e) {
                    // Блок может быть уже скрыт или не зарегистрирован
                }
                
                // Также скрываем блок form-field (он только для редактирования форм)
                try {
                    unregisterBlockType('codeweber-blocks/form-field');
                } catch (e) {
                    // Блок может быть уже скрыт или не зарегистрирован
                }
                
                // Скрываем блок submit-button (он только для редактирования форм)
                try {
                    unregisterBlockType('codeweber-blocks/submit-button');
                } catch (e) {
                    // Блок может быть уже скрыт или не зарегистрирован
                }
            }
        } catch (e) {
            // Игнорируем ошибки
        }
    }

    // Пробуем скрыть блок после загрузки редактора
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(checkAndHideBlock, 500);
        });
    } else {
        setTimeout(checkAndHideBlock, 500);
    }

    // Также подписываемся на изменения редактора
    let unsubscribe = null;
    function initSubscription() {
        if (unsubscribe) {
            return;
        }

        try {
            unsubscribe = wp.data.subscribe(function() {
                checkAndHideBlock();
            });
        } catch (e) {
            // Игнорируем ошибки подписки
        }
    }

    setTimeout(initSubscription, 1000);
})();










