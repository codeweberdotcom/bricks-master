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
        console.log('[CF7 Success Message] replaceModalContentWithSuccessTemplate called', { form, message });
        
        // Use existing modal #modal from footer
        const modal = document.getElementById('modal');
        console.log('[CF7 Success Message] Modal element:', modal);
        
        if (!modal) {
            console.error('[CF7 Success Message] Modal #modal not found in DOM');
            return; // Modal not found
        }

        const modalContent = modal.querySelector('.modal-body');
        console.log('[CF7 Success Message] Modal body:', modalContent);
        
        if (!modalContent) {
            console.error('[CF7 Success Message] Modal body not found');
            return;
        }

        // Check if cookie modal is open (highest priority)
        const cookieModal = document.getElementById('cookieModal');
        if (cookieModal && cookieModal.classList.contains('show')) {
            console.log('[CF7 Success Message] Cookie modal is open, waiting for it to close');
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
        
        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'X-WP-Nonce': apiNonce,
                'Content-Type': 'application/json'
            }
        })
        .then(function(response) {
            console.log('[CF7 Success Message] Fetch response status:', response.status, response.statusText);
            
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(function(templateData) {
            console.log('[CF7 Success Message] Template data received:', templateData);
            
            if (templateData.success && templateData.html) {
                console.log('[CF7 Success Message] Replacing modal content with template');
                // Replace modal content with template
                modalContent.innerHTML = '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' + templateData.html;
                console.log('[CF7 Success Message] Modal content replaced successfully');

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
        const body = document.body;
        if (!body) {
            console.error('[CF7 Success Message] Body element not found');
            return;
        }
        
        if (body.dataset.cf7SuccessMessageInitialized === 'true') {
            return;
        }

        document.addEventListener('wpcf7mailsent', handleCf7Success, false);
        body.dataset.cf7SuccessMessageInitialized = 'true';
    }

    // Инициализация при загрузке DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

