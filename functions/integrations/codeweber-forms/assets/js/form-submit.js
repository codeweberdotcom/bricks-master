/**
 * CodeWeber Forms Submit Handler
 * 
 * AJAX form submission
 * 
 * @package Codeweber
 */

(function() {
    'use strict';

    /**
     * Get UTM parameters from URL or localStorage
     * 
     * @return object UTM parameters
     */
    function getUTMParams() {
        const utmParams = {};
        const utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id'];
        
        // Get from current URL
        const urlParams = new URLSearchParams(window.location.search);
        utmKeys.forEach(key => {
            const value = urlParams.get(key);
            if (value) {
                utmParams[key] = value;
            }
        });
        
        // Get from localStorage (stored from previous visits)
        try {
            const storedUTM = localStorage.getItem('codeweber_utm_params');
            const expiry = localStorage.getItem('codeweber_utm_params_expiry');
            
            // Check if stored UTM is still valid (30 days)
            if (storedUTM && expiry && Date.now() < parseInt(expiry)) {
                const parsed = JSON.parse(storedUTM);
                // Merge with current URL params (URL params take priority)
                utmKeys.forEach(key => {
                    if (!utmParams[key] && parsed[key]) {
                        utmParams[key] = parsed[key];
                    }
                });
            } else if (storedUTM) {
                // Expired, remove it
                localStorage.removeItem('codeweber_utm_params');
                localStorage.removeItem('codeweber_utm_params_expiry');
            }
        } catch (e) {
            console.error('[Forms] Error reading UTM from localStorage:', e);
        }
        
        // Store current UTM params in localStorage for future use
        if (Object.keys(utmParams).length > 0) {
            try {
                localStorage.setItem('codeweber_utm_params', JSON.stringify(utmParams));
                // Store expiration (30 days)
                localStorage.setItem('codeweber_utm_params_expiry', (Date.now() + (30 * 24 * 60 * 60 * 1000)).toString());
            } catch (e) {
                console.error('[Forms] Error storing UTM in localStorage:', e);
            }
        }
        
        return utmParams;
    }
    
    /**
     * Get additional tracking data
     * 
     * @return object Tracking data
     */
    function getTrackingData() {
        const data = {};
        
        // Referrer
        if (document.referrer) {
            data.referrer = document.referrer;
        }
        
        // Landing page (current page URL)
        data.landing_page = window.location.href;
        
        return data;
    }

    /**
     * Initialize form handlers
     */
    function initForms() {
        const forms = document.querySelectorAll('.codeweber-form');
        
        forms.forEach(form => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (!submitBtn) return;

            const formId = form.dataset.formId || form.id.replace('form-', '');
            const formMessages = form.querySelector('.form-messages');
            const originalBtnText = submitBtn.textContent || submitBtn.innerText;
            const loadingText = submitBtn.dataset.loadingText || 'Sending...';

            // Добавляем nonce поле, если его нет
            if (!form.querySelector('input[name="form_nonce"]')) {
                const nonceInput = document.createElement('input');
                nonceInput.type = 'hidden';
                nonceInput.name = 'form_nonce';
                nonceInput.value = codeweberForms?.restNonce || '';
                form.insertBefore(nonceInput, form.firstChild);
            }
            
            // Хук при открытии формы (отправляем один раз при загрузке)
            if (!form.dataset.opened) {
                form.dataset.opened = 'true';
                
                // JavaScript событие: форма открыта
                const openedEvent = new CustomEvent('codeweberFormOpened', {
                    detail: {
                        formId: formId,
                        form: form
                    }
                });
                form.dispatchEvent(openedEvent);
                
                if (formId && codeweberForms?.restUrl) {
                    fetch(codeweberForms.restUrl + 'form-opened', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': codeweberForms.restNonce
                        },
                        body: JSON.stringify({ form_id: formId })
                    }).catch(() => {}); // Игнорируем ошибки
                }
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // JavaScript событие: форма отправляется
                const submittingEvent = new CustomEvent('codeweberFormSubmitting', {
                    detail: {
                        formId: formId,
                        form: form,
                        formData: new FormData(form)
                    },
                    cancelable: true
                });
                const submittingResult = form.dispatchEvent(submittingEvent);
                
                // Если событие было отменено (preventDefault), не отправляем форму
                if (!submittingResult) {
                    return;
                }

                // Clear previous messages
                if (formMessages) {
                    formMessages.innerHTML = '';
                    formMessages.className = 'form-messages';
                    formMessages.style.display = 'none';
                }

                // Validate form
                if (!form.checkValidity()) {
                    // Добавляем класс was-validated для Bootstrap валидации (как в форме отзывов)
                    form.classList.add('was-validated');
                    
                    // JavaScript событие: ошибка валидации
                    const invalidEvent = new CustomEvent('codeweberFormInvalid', {
                        detail: {
                            formId: formId,
                            form: form,
                            message: 'Form validation failed'
                        }
                    });
                    form.dispatchEvent(invalidEvent);
                    
                    return;
                }

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.textContent = loadingText;

                // Collect form data
                const formData = new FormData(form);
                const fields = {};
                const honeypot = formData.get('form_honeypot') || '';

                // Convert FormData to object
                for (let [key, value] of formData.entries()) {
                    if (key === 'form_id' || key === 'form_nonce' || key === '_wp_http_referer' || key === 'form_honeypot') {
                        continue;
                    }
                    
                    // Handle form_consents as array (универсальный префикс) - формат 1: form_consents[ID]
                    if (key.startsWith('form_consents[')) {
                        // Extract document ID from key like "form_consents[4973]"
                        const match = key.match(/form_consents\[(\d+)\]/);
                        if (match) {
                            const docId = match[1];
                            if (!fields.form_consents) {
                                fields.form_consents = {};
                            }
                            fields.form_consents[docId] = value;
                        }
                        continue;
                    }
                    
                    // Handle form_consents_ID format (универсальный префикс) - формат 2: form_consents_ID
                    if (key.startsWith('form_consents_')) {
                        const match = key.match(/form_consents_(\d+)/);
                        if (match) {
                            const docId = match[1];
                            if (!fields.form_consents) {
                                fields.form_consents = {};
                            }
                            fields.form_consents[docId] = value;
                        }
                        continue;
                    }
                    
                    // Handle newsletter_consents as array (обратная совместимость)
                    if (key.startsWith('newsletter_consents[')) {
                        // Extract document ID from key like "newsletter_consents[4973]"
                        const match = key.match(/newsletter_consents\[(\d+)\]/);
                        if (match) {
                            const docId = match[1];
                            if (!fields.newsletter_consents) {
                                fields.newsletter_consents = {};
                            }
                            fields.newsletter_consents[docId] = value;
                        }
                        continue;
                    }
                    
                    if (fields[key]) {
                        // Multiple values (checkboxes, multiple files)
                        if (!Array.isArray(fields[key])) {
                            fields[key] = [fields[key]];
                        }
                        fields[key].push(value);
                    } else {
                        fields[key] = value;
                    }
                }

                // Collect UTM parameters
                const utmParams = getUTMParams();
                const trackingData = getTrackingData();
                
                // Prepare data for API
                const data = {
                    form_id: formId,
                    fields: fields,
                    nonce: formData.get('form_nonce') || '',
                    honeypot: honeypot,
                    utm_params: utmParams,
                    tracking_data: trackingData
                };
                
                // Add consents to data if present (приоритет form_consents)
                if (fields.form_consents) {
                    data.form_consents = fields.form_consents;
                } elseif (fields.newsletter_consents) {
                    data.newsletter_consents = fields.newsletter_consents;
                }

                // Get REST API URL and nonce
                const restUrl = codeweberForms.restUrl + 'submit';
                const restNonce = codeweberForms.restNonce;

                // Send AJAX request
                fetch(restUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': restNonce
                    },
                    body: JSON.stringify(data)
                })
                .then(async response => {
                    if (!response.ok) {
                        // Клонируем response перед чтением, чтобы можно было прочитать его несколько раз
                        const responseClone = response.clone();
                        try {
                            const err = await response.json();
                            const errorMessage = err.message || err.data?.message || err.code || `HTTP ${response.status}: ${response.statusText}`;
                            console.error('[Form Submit] Server error:', err);
                            throw new Error(errorMessage);
                        } catch (parseError) {
                            // If JSON parsing fails, try to get text from clone
                            try {
                                const text = await responseClone.text();
                                console.error('[Form Submit] Response text:', text);
                                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                            } catch (textError) {
                                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                            }
                        }
                    }
                    return response.json().catch(parseError => {
                        console.error('[Form Submit] JSON parse error:', parseError);
                        throw new Error('Invalid response from server');
                    });
                })
                .then(data => {
                    if (data.success) {
                        showMessage(formMessages, data.message || 'Thank you! Your message has been sent.', 'success');
                        form.reset();
                        
                        // JavaScript событие: успешная отправка
                        const successEvent = new CustomEvent('codeweberFormSubmitted', {
                            detail: {
                                formId: formId,
                                form: form,
                                submissionId: data.submission_id || null,
                                message: data.message || 'Thank you! Your message has been sent.',
                                apiResponse: data
                            }
                        });
                        form.dispatchEvent(successEvent);
                        
                        // Close modal if exists
                        const modal = form.closest('.modal');
                        if (modal) {
                            setTimeout(() => {
                                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                    const bsModal = bootstrap.Modal.getInstance(modal);
                                    if (bsModal) {
                                        bsModal.hide();
                                    }
                                } else if (typeof jQuery !== 'undefined' && jQuery(modal).modal) {
                                    jQuery(modal).modal('hide');
                                }
                            }, 2000);
                        }
                    } else {
                        showMessage(formMessages, data.message || 'An error occurred. Please try again.', 'error');
                        
                        // JavaScript событие: ошибка отправки
                        const errorEvent = new CustomEvent('codeweberFormError', {
                            detail: {
                                formId: formId,
                                form: form,
                                message: data.message || 'An error occurred. Please try again.',
                                apiResponse: data
                            }
                        });
                        form.dispatchEvent(errorEvent);
                    }
                })
                .catch(error => {
                    console.error('[Form Submit] Error:', error);
                    showMessage(formMessages, error.message || 'An error occurred. Please try again.', 'error');
                    
                    // JavaScript событие: ошибка сети/сервера
                    const networkErrorEvent = new CustomEvent('codeweberFormError', {
                        detail: {
                            formId: formId,
                            form: form,
                            message: error.message || 'Network error occurred',
                            error: error
                        }
                    });
                    form.dispatchEvent(networkErrorEvent);
                })
                .finally(() => {
                    // Restore button state
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                });
            });
        });
    }

    /**
     * Show message
     */
    function showMessage(container, message, type) {
        if (!container) return;

        container.innerHTML = '<div class="alert alert-' + (type === 'success' ? 'success' : 'danger') + '">' + message + '</div>';
        container.style.display = 'block';
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initForms);
    } else {
        initForms();
    }

})();

