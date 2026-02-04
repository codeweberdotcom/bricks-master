/**
 * Success message display for CF7 forms
 * Uses codeweber forms mechanism to show success message template
 * Similar to replaceModalContentWithEnvelope function from codeweber forms
 */

(function() {
    'use strict';

    /**
     * Replaces modal content with success message template
     * Uses REST API to get template from codeweber forms
     * 
     * @param {HTMLElement} form - CF7 form element
     * @param {string} message - Message text (optional, not used - server uses translated default)
     */
    function replaceModalContentWithSuccessTemplate(form, message) {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:replaceModalContentWithSuccessTemplate:entry',message:'Function called',data:{hasForm:!!form,message:message||'(default)'},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        
        console.log('[CF7 Success Message] replaceModalContentWithSuccessTemplate called', { form, message });
        
        // Use existing modal #modal from footer
        const modal = document.getElementById('modal');
        console.log('[CF7 Success Message] Modal element:', modal);
        
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:replaceModalContentWithSuccessTemplate:modal-check',message:'Modal element check',data:{modalFound:!!modal,modalId:modal?.id},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        
        if (!modal) {
            console.error('[CF7 Success Message] Modal #modal not found in DOM');
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:replaceModalContentWithSuccessTemplate:modal-not-found',message:'Modal #modal not found',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
            // #endregion
            return; // Modal not found
        }

        const modalContent = modal.querySelector('.modal-body');
        console.log('[CF7 Success Message] Modal body:', modalContent);
        
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:replaceModalContentWithSuccessTemplate:modal-body-check',message:'Modal body check',data:{modalBodyFound:!!modalContent},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        
        if (!modalContent) {
            console.error('[CF7 Success Message] Modal body not found');
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:replaceModalContentWithSuccessTemplate:modal-body-not-found',message:'Modal body not found',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
            // #endregion
            return;
        }

        // Check if cookie modal is open (highest priority)
        const cookieModal = document.getElementById('cookieModal');
        if (cookieModal && cookieModal.classList.contains('show')) {
            console.log('[CF7 Success Message] Cookie modal is open, waiting for it to close');
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:replaceModalContentWithSuccessTemplate',message:'Cookie modal is open, deferring CF7 success modal',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
            // #endregion
            
            // Wait for cookie modal to close, then show CF7 success modal
            const checkCookieModal = setInterval(function() {
                if (!cookieModal.classList.contains('show')) {
                    clearInterval(checkCookieModal);
                    // Close notification modal if it's open (to prevent conflicts)
                    const notificationModal = document.getElementById('notification-modal');
                    if (notificationModal) {
                        const notificationBsModal = bootstrap.Modal.getInstance(notificationModal);
                        if (notificationBsModal && notificationModal.classList.contains('show')) {
                            notificationBsModal.hide();
                            console.log('[CF7 Success Message] Closed notification modal before showing success message');
                        }
                    }
                    // Now show CF7 success modal
                    showCf7SuccessModal();
                }
            }, 100);
            return; // Exit early, will show modal after cookie modal closes
        }
        
        // Close notification modal if it's open (to prevent conflicts)
        const notificationModal = document.getElementById('notification-modal');
        if (notificationModal) {
            const notificationBsModal = bootstrap.Modal.getInstance(notificationModal);
            if (notificationBsModal && notificationModal.classList.contains('show')) {
                notificationBsModal.hide();
                console.log('[CF7 Success Message] Closed notification modal before showing success message');
            }
        }
        
        // Show existing modal only if it's not already open
        showCf7SuccessModal();
    }
    
    function showCf7SuccessModal() {
        const modal = document.getElementById('modal');
        if (!modal) return;
        
        // Проверяем, не открыт ли cookie modal (самый высокий приоритет)
        const cookieModal = document.getElementById('cookieModal');
        if (cookieModal && cookieModal.classList.contains('show')) {
            console.log('[CF7 Success Message] Cookie modal is open, cannot show CF7 success modal');
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:showCf7SuccessModal',message:'Cookie modal is open, blocking CF7 success modal',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
            // #endregion
            return; // Блокируем открытие
        }
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            console.log('[CF7 Success Message] Bootstrap available, checking modal state');
            let bsModal = bootstrap.Modal.getInstance(modal);
            
            // If modal is not initialized yet, create new instance
            if (!bsModal) {
                bsModal = new bootstrap.Modal(modal, {
                    backdrop: 'static', // Prevent closing on backdrop click
                    keyboard: false // Prevent closing on ESC
                });
            }
            
            // Check if modal is already open
            const isModalShown = modal.classList.contains('show') || modal.style.display === 'block';
            console.log('[CF7 Success Message] Modal is already shown:', isModalShown);
            
            // Show modal only if it's not already open
            if (!isModalShown) {
                bsModal.show();
                console.log('[CF7 Success Message] Modal shown');
            } else {
                console.log('[CF7 Success Message] Modal already shown, skipping show() call');
            }
        } else {
            console.error('[CF7 Success Message] Bootstrap not available');
        }
        
        // Get success message template via REST API
        let apiRoot = '/wp-json/';
        let apiNonce = '';

        if (typeof wpApiSettings !== 'undefined') {
            apiRoot = wpApiSettings.root;
            apiNonce = wpApiSettings.nonce;
        } else {
            // Try to get nonce from meta tag
            const nonceMeta = document.querySelector('meta[name="wp-api-nonce"]');
            if (nonceMeta) {
                apiNonce = nonceMeta.getAttribute('content');
            }
        }

        // Don't pass CF7 message to use default translated message
        // from codeweber_get_success_message_template on server
        // Server uses __('Message sent successfully.', 'codeweber') if message is empty
        const apiUrl = apiRoot + 'codeweber/v1/success-message-template?icon_type=svg';
        console.log('[CF7 Success Message] Fetching template from:', apiUrl);
        console.log('[CF7 Success Message] API nonce:', apiNonce ? 'present' : 'missing');
        
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:replaceModalContentWithSuccessTemplate:fetch-start',message:'Starting fetch request',data:{apiUrl:apiUrl,hasNonce:!!apiNonce},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
        // #endregion
        
        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'X-WP-Nonce': apiNonce,
                'Content-Type': 'application/json'
            }
        })
        .then(function(response) {
            console.log('[CF7 Success Message] Fetch response status:', response.status, response.statusText);
            
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:replaceModalContentWithSuccessTemplate:fetch-response',message:'Fetch response received',data:{status:response.status,statusText:response.statusText,ok:response.ok},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
            // #endregion
            
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(function(templateData) {
            console.log('[CF7 Success Message] Template data received:', templateData);
            
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:replaceModalContentWithSuccessTemplate:template-data',message:'Template data received',data:{success:templateData.success,hasHtml:!!templateData.html},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
            // #endregion
            
            if (templateData.success && templateData.html) {
                console.log('[CF7 Success Message] Replacing modal content with template');
                // Replace modal content with template
                modalContent.innerHTML = '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' + templateData.html;
                console.log('[CF7 Success Message] Modal content replaced successfully');
                
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:replaceModalContentWithSuccessTemplate:content-replaced',message:'Modal content replaced successfully',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
                // #endregion

                // Close modal after 5 seconds (same as codeweber forms)
                setTimeout(function() {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    } else if (typeof jQuery !== 'undefined' && jQuery(modal).modal) {
                        jQuery(modal).modal('hide');
                    }
                }, 5000);
            } else {
                // Fallback: просто закрываем модальное окно
                setTimeout(function() {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    } else if (typeof jQuery !== 'undefined' && jQuery(modal).modal) {
                        jQuery(modal).modal('hide');
                    }
                }, 500);
            }
        })
        .catch(function(error) {
            console.error('[CF7 Success Message] Error loading success template:', error);
            console.error('[CF7 Success Message] Error details:', {
                message: error.message,
                stack: error.stack,
                apiRoot: apiRoot,
                apiNonce: apiNonce ? 'present' : 'missing'
            });
            
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:replaceModalContentWithSuccessTemplate:fetch-error',message:'Fetch error',data:{errorMessage:error.message,apiRoot:apiRoot,hasNonce:!!apiNonce},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
            // #endregion
            // Fallback: закрываем модальное окно
            setTimeout(function() {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                } else if (typeof jQuery !== 'undefined' && jQuery(modal).modal) {
                    jQuery(modal).modal('hide');
                }
            }, 500);
        });
    }

    /**
     * CF7 form success handler
     */
    function handleCf7Success(event) {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:handleCf7Success:entry',message:'wpcf7mailsent event triggered',data:{hasTarget:!!event.target,hasDetail:!!event.detail},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
        // #endregion
        
        console.log('[CF7 Success Message] wpcf7mailsent event triggered', event);
        const form = event.target;
        console.log('[CF7 Success Message] Form element:', form);

        // Don't use CF7 message - use default translated message from codeweber forms
        // Server will return translated message via __('Message sent successfully.', 'codeweber')
        console.log('[CF7 Success Message] Using default translated message from API');

        // Use codeweber forms mechanism to show success message
        replaceModalContentWithSuccessTemplate(form, '');

        // Clear validation classes
        form.classList.remove('was-validated');
        form.querySelectorAll('.form-control, .form-check-input').forEach(function(input) {
            input.classList.remove('is-valid', 'is-invalid');
            input.removeAttribute('aria-invalid');
        });
    }

    /**
     * Initialize wpcf7mailsent event handler
     */
    function init() {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:init:entry',message:'Initialization started',data:{readyState:document.readyState},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
        // #endregion
        
        // Используем data-атрибут на body вместо document.dataset
        const body = document.body;
        if (!body) {
            console.error('[CF7 Success Message] Body element not found');
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:init:body-not-found',message:'Body element not found',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
            // #endregion
            return;
        }
        
        // Проверяем, не инициализирован ли уже обработчик
        if (body.dataset.cf7SuccessMessageInitialized === 'true') {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:init:already-initialized',message:'Already initialized, skipping',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
            // #endregion
            return;
        }

        // Слушаем событие успешной отправки CF7
        document.addEventListener('wpcf7mailsent', handleCf7Success, false);
        
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:init:event-listener-added',message:'Event listener added',data:{event:'wpcf7mailsent'},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
        // #endregion

        // Помечаем как инициализированный через data-атрибут body
        body.dataset.cf7SuccessMessageInitialized = 'true';
        
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'cf7-success-message.js:init:complete',message:'Initialization complete',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
        // #endregion
    }

    // Инициализация при загрузке DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

