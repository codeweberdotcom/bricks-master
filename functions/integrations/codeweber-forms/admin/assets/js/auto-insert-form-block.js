/**
 * CodeWeber Forms - Auto Insert Form Block
 * 
 * Автоматически вставляет блок формы при создании нового CPT поста
 * если контент пустой
 */

(function() {
    'use strict';

    if (typeof wp === 'undefined' || typeof wp.blocks === 'undefined' || typeof wp.data === 'undefined') {
        return;
    }

    const { select, dispatch, subscribe } = wp.data;
    const { createBlock } = wp.blocks;

    let blockInserted = false;

    // Функция для проверки и вставки блока
    function checkAndInsertBlock() {
        // Предотвращаем повторную вставку
        if (blockInserted) {
            return false;
        }

        try {
            // Проверяем, что мы в редакторе CPT codeweber_form
            const postType = select('core/editor')?.getCurrentPostType();
            if (postType !== 'codeweber_form') {
                return false;
            }

            // Проверяем, что это новый пост
            const isCleanNewPost = select('core/editor')?.isCleanNewPost();
            if (!isCleanNewPost) {
                return false;
            }

            // Получаем блоки через правильный селектор
            let blocks = [];
            try {
                // В WordPress 5.8+ используется core/block-editor
                const blockEditor = select('core/block-editor');
                if (blockEditor && typeof blockEditor.getBlocks === 'function') {
                    blocks = blockEditor.getBlocks();
                }
            } catch (e) {
                // Игнорируем ошибки
            }

            // Если уже есть блоки, не вставляем
            if (blocks && blocks.length > 0) {
                return false;
            }

            // Получаем ID поста
            const postId = select('core/editor')?.getCurrentPostId();
            if (!postId || postId <= 0) {
                return false;
            }

            // Получаем заголовок поста
            const postTitle = select('core/editor')?.getEditedPostAttribute('title') || 
                             (typeof codeweberFormsAutoInsert !== 'undefined' ? codeweberFormsAutoInsert.postTitle : 'Contact Form');

            // Создаем блок формы
            const formBlock = createBlock('codeweber-blocks/form', {
                formId: String(postId),
                formName: postTitle,
                submitButtonText: 'Send Message',
                submitButtonClass: 'btn btn-primary',
            });

            // Вставляем блок в редактор
            if (formBlock) {
                try {
                    const blockEditorDispatch = dispatch('core/block-editor');
                    if (blockEditorDispatch && typeof blockEditorDispatch.insertBlocks === 'function') {
                        blockEditorDispatch.insertBlocks(formBlock, 0);
                        blockInserted = true;
                        return true;
                    }
                } catch (e) {
                    console.error('Error inserting block:', e);
                    return false;
                }
            }
        } catch (e) {
            console.error('Error in checkAndInsertBlock:', e);
        }

        return false;
    }

    // Ждем загрузки редактора и пробуем вставить блок
    function tryInsertBlock() {
        if (typeof select === 'function' && typeof dispatch === 'function') {
            const postType = select('core/editor')?.getCurrentPostType();
            if (postType === 'codeweber_form') {
                checkAndInsertBlock();
            }
        }
    }

    // Пробуем вставить блок после загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(tryInsertBlock, 500);
        });
    } else {
        setTimeout(tryInsertBlock, 500);
    }

    // Подписываемся на изменения редактора
    let unsubscribe = null;
    function initSubscription() {
        if (unsubscribe || blockInserted) {
            return;
        }

        try {
            unsubscribe = subscribe(function() {
                if (!blockInserted) {
                    tryInsertBlock();
                }
            });
        } catch (e) {
            // Игнорируем ошибки подписки
        }
    }

    // Инициализируем подписку после загрузки редактора
    setTimeout(initSubscription, 1000);
})();

