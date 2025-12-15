/**
 * Universal Form Submit Handler
 * 
 * Handles AJAX submission for all forms (testimonial, newsletter, etc.)
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
     * Заменяет контент модального окна на шаблон с конвертом (для testimonial формы)
     * 
     * @param {HTMLElement} form - Элемент формы или модальное окно
     * @param {string} message - Сообщение для отображения
     */
    function replaceModalContentWithEnvelope(form, message) {
        // Если передан модальный элемент напрямую, используем его
        let modal = form.classList && form.classList.contains('modal') ? form : 
                    (form.closest('#modal') || document.getElementById('modal'));
        const modalContent = modal ? modal.querySelector('.modal-body') : null;

        if (!modal || !modalContent) {
            return; // Модального окна нет
        }

        // Получаем шаблон успешной отправки через REST API
        let apiRoot = '/wp-json/';
        let apiNonce = '';

        if (typeof wpApiSettings !== 'undefined') {
            apiRoot = wpApiSettings.root;
            apiNonce = wpApiSettings.nonce;
        } else {
            // Пытаемся получить nonce из мета-тега
            const nonceMeta = document.querySelector('meta[name="wp-api-nonce"]');
            if (nonceMeta) {
                apiNonce = nonceMeta.getAttribute('content');
            }
        }

        fetch(apiRoot + 'codeweber/v1/success-message-template?message=' + encodeURIComponent(message) + '&icon_type=svg', {
            method: 'GET',
            headers: {
                'X-WP-Nonce': apiNonce,
                'Content-Type': 'application/json'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(templateData) {
            if (templateData.success && templateData.html) {
                // Заменяем содержимое модального окна на шаблон с конвертом
                modalContent.innerHTML = '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' + templateData.html;

                // Закрываем модальное окно через 5 секунд
                setTimeout(function() {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
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
                    }
                }, 500);
            }
        })
        .catch(function(error) {
            console.error('[Forms] Error loading success template:', error);
            // Fallback: закрываем модальное окно
            setTimeout(function() {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }
            }, 500);
        });
    }

    /**
     * Validate testimonial form (custom validation)
     * 
     * @param {HTMLFormElement} form
     * @return {boolean}
     */
    function validateTestimonialForm(form) {
        const testimonialText = form.querySelector('[name="testimonial_text"]');
        const authorName = form.querySelector('[name="author_name"]');
        const authorEmail = form.querySelector('[name="author_email"]');
        const rating = form.querySelector('[name="rating"]');
        const userId = form.querySelector('[name="user_id"]');
        const isLoggedIn = !!userId;

        let isValid = true;

        // Validate testimonial text
        if (!testimonialText || !testimonialText.value.trim()) {
            isValid = false;
            if (testimonialText) {
                testimonialText.classList.add('is-invalid');
            }
        } else {
            if (testimonialText) {
                testimonialText.classList.remove('is-invalid');
            }
        }

        // Validate author name and email only if user is not logged in
        if (!isLoggedIn) {
            // Validate author name
            if (!authorName || !authorName.value.trim()) {
                isValid = false;
                if (authorName) {
                    authorName.classList.add('is-invalid');
                }
            } else {
                if (authorName) {
                    authorName.classList.remove('is-invalid');
                }
            }

            // Validate email
            if (!authorEmail || !authorEmail.value.trim()) {
                isValid = false;
                if (authorEmail) {
                    authorEmail.classList.add('is-invalid');
                }
            } else if (authorEmail && !isValidEmail(authorEmail.value)) {
                isValid = false;
                authorEmail.classList.add('is-invalid');
            } else {
                if (authorEmail) {
                    authorEmail.classList.remove('is-invalid');
                }
            }
        }

        // Validate rating - add visual indicator to stars wrapper
        const ratingWrapper = form.querySelector('.rating-stars-wrapper');
        const ratingValue = rating ? parseInt(rating.value) : 0;
        
        if (!rating || !rating.value || ratingValue < 1 || ratingValue > 5) {
            isValid = false;
            if (rating) {
                rating.classList.add('is-invalid');
                // Set custom validity message
                rating.setCustomValidity('Please select a rating');
            }
            // Add visual indicator to stars wrapper
            if (ratingWrapper) {
                ratingWrapper.classList.add('is-invalid');
            }
        } else {
            if (rating) {
                rating.classList.remove('is-invalid');
                // Clear custom validity
                rating.setCustomValidity('');
            }
            // Remove visual indicator from stars wrapper
            if (ratingWrapper) {
                ratingWrapper.classList.remove('is-invalid');
            }
        }

        // Validate required consents
        const consentCheckboxes = form.querySelectorAll('input[name^="testimonial_consents["][required]');
        consentCheckboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                isValid = false;
                checkbox.classList.add('is-invalid');
            } else {
                checkbox.classList.remove('is-invalid');
            }
        });

        // If validation failed, add was-validated class for Bootstrap styles
        if (!isValid) {
            form.classList.add('was-validated');
        }

        return isValid;
    }

    /**
     * Validate email format
     * 
     * @param {string} email
     * @return {boolean}
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Determine form type and configuration
     */
    function getFormConfig(form) {
        const config = {
            type: 'generic',
            formId: null,
            apiEndpoint: null,
            nonceField: 'form_nonce',
            nonceAction: 'codeweber_form_submit',
            consentPrefix: 'newsletter_consents',
            messagesContainer: '.form-messages',
            messagesClass: 'form-messages',
            onSuccess: null,
            customValidation: null
        };

        // Testimonial form
        if (form.id === 'testimonial-form' || form.classList.contains('testimonial-form')) {
            config.type = 'testimonial';
            config.formId = 'testimonial-form';
            config.apiEndpoint = codeweberTestimonialForm?.restUrl || '/wp-json/codeweber/v1/submit-testimonial';
            config.nonceField = 'testimonial_nonce';
            config.nonceAction = 'submit_testimonial';
            config.consentPrefix = 'testimonial_consents';
            config.messagesContainer = '.testimonial-form-messages';
            config.messagesClass = 'testimonial-form-messages';
            config.customValidation = validateTestimonialForm;
            // onSuccess убран - обработка через универсальный хук codeweberFormSubmitted
        }
        // Codeweber forms (newsletter, etc.)
        else if (form.classList.contains('codeweber-form')) {
            config.type = 'codeweber';
            config.formId = form.dataset.formId || form.id.replace('form-', '');
            config.formName = form.dataset.formName || '';
            config.apiEndpoint = (codeweberForms?.restUrl || '/wp-json/codeweber-forms/v1/') + 'submit';
            config.nonceField = 'form_nonce';
            config.nonceAction = 'codeweber_form_submit';
            config.consentPrefix = 'newsletter_consents';
            config.messagesContainer = '.form-messages';
            config.messagesClass = 'form-messages';
            // onSuccess убран - обработка через универсальный хук codeweberFormSubmitted
        }

        return config;
    }

    /**
     * Collect form data based on form type
     */
    function collectFormData(form, config) {
        const formData = new FormData(form);
        const data = {};

        // Get nonce
        const nonce = formData.get(config.nonceField);
        if (nonce) {
            data.nonce = nonce;
        } else {
            console.warn('[Form Submit Debug] Nonce not found in form field:', config.nonceField);
            // For testimonial forms, nonce is required
            if (config.type === 'testimonial') {
                console.warn('[Form Submit Debug] Testimonial form requires nonce!');
            }
        }

        // Collect consents
        const consents = {};
        const consentCheckboxes = form.querySelectorAll(`input[name^="${config.consentPrefix}["]`);
        console.log('[Form Submit Debug] Looking for consents with prefix:', config.consentPrefix);
        console.log('[Form Submit Debug] Found checkboxes:', consentCheckboxes.length);
        consentCheckboxes.forEach(checkbox => {
            console.log('[Form Submit Debug] Checkbox:', checkbox.name, 'checked:', checkbox.checked);
            if (checkbox.checked) {
                const match = checkbox.name.match(new RegExp(`${config.consentPrefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\[(\\d+)\\]`));
                if (match && match[1]) {
                    consents[match[1]] = '1';
                    console.log('[Form Submit Debug] Added consent for doc_id:', match[1]);
                }
            }
        });
        if (Object.keys(consents).length > 0) {
            data[config.consentPrefix] = consents;
            console.log('[Form Submit Debug] Final consents object:', consents);
        } else {
            console.warn('[Form Submit Debug] No consents collected!');
        }

        // Collect other fields based on form type
        if (config.type === 'testimonial') {
            // Get testimonial_text and ensure it's not empty
            const testimonialText = formData.get('testimonial_text');
            if (testimonialText && testimonialText.trim()) {
                data.testimonial_text = testimonialText.trim();
            } else {
                data.testimonial_text = '';
            }
            
            // Get rating and convert to integer (required: 1-5)
            const ratingValue = formData.get('rating');
            if (ratingValue) {
                const ratingInt = parseInt(ratingValue, 10);
                if (!isNaN(ratingInt) && ratingInt >= 1 && ratingInt <= 5) {
                    data.rating = ratingInt;
                } else {
                    data.rating = null; // Will cause validation error
                }
            } else {
                data.rating = null; // Will cause validation error
            }
            
            data.honeypot = formData.get('testimonial_honeypot') || '';
            
            const userId = formData.get('user_id');
            if (userId) {
                data.user_id = parseInt(userId, 10);
            } else {
                data.author_name = formData.get('author_name') || '';
                data.author_email = formData.get('author_email') || '';
                data.author_role = formData.get('author_role') || '';
                data.company = formData.get('company') || '';
            }
        } else {
            // Generic codeweber form
            data.honeypot = formData.get('form_honeypot') || '';
            data.form_id = config.formId;
            if (config.formName) {
                data.form_name = config.formName;
            }
            
            // Collect all fields except system ones
            const fields = {};
            for (let [key, value] of formData.entries()) {
                if (['form_id', 'form_nonce', '_wp_http_referer', 'form_honeypot'].includes(key)) {
                    continue;
                }
                
                if (key.startsWith(config.consentPrefix + '[')) {
                    continue; // Already handled
                }
                
                if (fields[key]) {
                    if (!Array.isArray(fields[key])) {
                        fields[key] = [fields[key]];
                    }
                    fields[key].push(value);
                } else {
                    fields[key] = value;
                }
            }
            data.fields = fields;
        }

        // Add UTM and tracking
        data.utm_params = getUTMParams();
        data.tracking_data = getTrackingData();

        return data;
    }

    /**
     * Show message to user
     * 
     * @param {HTMLElement} container
     * @param {string} message
     * @param {string} type
     * @param {string} containerClass
     */
    function showMessage(container, message, type, containerClass = 'form-messages') {
        if (!container) return;

        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        container.className = containerClass + ' alert ' + alertClass;
        container.innerHTML = '<p class="mb-0">' + message + '</p>';
        container.style.display = 'block';

        // Scroll to message
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Initialize single form
     */
    function initForm(form) {
        // Проверяем, не инициализирована ли форма уже
        if (form.dataset.initialized === 'true') {
            return;
        }
        form.dataset.initialized = 'true';
        
        const submitBtn = form.querySelector('button[type="submit"]');
        if (!submitBtn) return;

        const config = getFormConfig(form);
        if (!config.apiEndpoint) {
            return;
        }

        const formMessages = form.querySelector(config.messagesContainer);
        const originalBtnHTML = submitBtn.innerHTML;
        const originalBtnText = submitBtn.textContent || submitBtn.innerText;
        const loadingText = submitBtn.dataset.loadingText || 'Отправка...';
        const originalMinHeight = submitBtn.style.minHeight || '';
        
        // Флаг для отслеживания наших изменений кнопки
        let isOurControl = false;
        
        // Сохраняем оригинальный setter для disabled
        const buttonPrototype = Object.getOwnPropertyDescriptor(HTMLButtonElement.prototype, 'disabled');
        
        // Перехватываем изменения disabled
        try {
            Object.defineProperty(submitBtn, 'disabled', {
                set: function(value) {
                    const wasDisabled = this.disabled;
                    const willBeDisabled = value;
                    
                    // Если кнопка разблокируется во время отправки (когда текст "Отправка...")
                    if (wasDisabled && !willBeDisabled && (this.textContent === loadingText || this.textContent.trim() === loadingText.trim())) {
                        if (!isOurControl) {
                            // Сохраняем информацию о том, кто разблокировал
                            const unlockInfo = {
                                timestamp: Date.now(),
                                text: this.textContent,
                                stack: new Error().stack
                            };
                            submitBtn._unlockInfo = unlockInfo;
                        }
                    }
                    
                    // Устанавливаем значение
                    if (buttonPrototype && buttonPrototype.set) {
                        buttonPrototype.set.call(this, value);
                    } else {
                        if (value) {
                            this.setAttribute('disabled', 'disabled');
                        } else {
                            this.removeAttribute('disabled');
                        }
                    }
                },
                get: function() {
                    if (buttonPrototype && buttonPrototype.get) {
                        return buttonPrototype.get.call(this);
                    }
                    return this.hasAttribute('disabled');
                },
                configurable: true
            });
        } catch (e) {
            // Ignore interception errors
        }
        
        // Перехватываем события, которые могут разблокировать кнопку
        const eventWatcher = function(event) {
            if (event.type === 'codeweberFormSubmitted' || event.type === 'codeweberFormError') {
                setTimeout(() => {
                    if (submitBtn.disabled === false && (submitBtn.textContent === loadingText || submitBtn.textContent.trim() === loadingText.trim())) {
                        // Button was unlocked by external script
                    }
                }, 0);
            }
        };
        
        // Слушаем события на форме и документе
        form.addEventListener('codeweberFormSubmitted', eventWatcher, true);
        form.addEventListener('codeweberFormError', eventWatcher, true);
        document.addEventListener('codeweberFormSubmitted', eventWatcher, true);
        document.addEventListener('codeweberFormError', eventWatcher, true);

        // Add nonce field if needed (for codeweber forms)
        if (config.type === 'codeweber' && !form.querySelector(`input[name="${config.nonceField}"]`)) {
            const nonceInput = document.createElement('input');
            nonceInput.type = 'hidden';
            nonceInput.name = config.nonceField;
            nonceInput.value = codeweberForms?.restNonce || '';
            form.insertBefore(nonceInput, form.firstChild);
        }

        // Form opened tracking (only for codeweber forms)
        if (config.type === 'codeweber' && !form.dataset.opened) {
            form.dataset.opened = 'true';
            
            // JavaScript событие: форма открыта
            const openedEvent = new CustomEvent('codeweberFormOpened', {
                detail: {
                    formId: config.formId,
                    form: form
                }
            });
            form.dispatchEvent(openedEvent);
            
            if (config.formId && codeweberForms?.restUrl) {
                fetch(codeweberForms.restUrl + 'form-opened', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': codeweberForms.restNonce
                    },
                    body: JSON.stringify({ form_id: config.formId })
                }).catch(() => {}); // Игнорируем ошибки
            }
        }

        // Form opened tracking for testimonial form
        if (config.type === 'testimonial' && !form.dataset.opened) {
            form.dataset.opened = 'true';
            
            const openedEvent = new CustomEvent('codeweberFormOpened', {
                detail: {
                    formId: config.formId,
                    form: form
                }
            });
            form.dispatchEvent(openedEvent);
            
            const restUrl = codeweberForms?.restUrl || '/wp-json/codeweber-forms/v1/';
            const restNonce = codeweberForms?.restNonce || '';
            
            if (restUrl) {
                fetch(restUrl + 'form-opened', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': restNonce
                    },
                    body: JSON.stringify({ form_id: config.formId })
                }).catch(() => {}); // Игнорируем ошибки
            }
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();

            // JavaScript событие: форма отправляется
            const submittingEvent = new CustomEvent('codeweberFormSubmitting', {
                detail: {
                    formId: config.formId,
                    form: form,
                    formData: new FormData(form)
                },
                cancelable: true
            });
            const submittingResult = form.dispatchEvent(submittingEvent);
            
            // Если событие было отменено, не отправляем форму
            if (!submittingResult) {
                return;
            }

            // Clear previous messages
            if (formMessages) {
                formMessages.innerHTML = '';
                formMessages.className = config.messagesClass;
                formMessages.style.display = 'none';
            }

            // Run custom validation first (for testimonial forms, this checks rating)
            // This ensures visual indicators are updated before HTML5 validation
            if (config.customValidation) {
                const customValid = config.customValidation(form);
                
                if (!customValid) {
                    form.classList.add('was-validated');
                    
                    // JavaScript событие: ошибка валидации
                    const invalidEvent = new CustomEvent('codeweberFormInvalid', {
                        detail: {
                            formId: config.formId,
                            form: form,
                            message: 'Form validation failed'
                        }
                    });
                    form.dispatchEvent(invalidEvent);
                    
                    // Focus on first invalid field (prefer rating if invalid)
                    const rating = form.querySelector('[name="rating"]');
                    const ratingWrapper = form.querySelector('.rating-stars-wrapper');
                    if (rating && rating.classList.contains('is-invalid')) {
                        // Focus on rating input (hidden) or scroll to stars
                        if (ratingWrapper) {
                            ratingWrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    } else {
                        const firstInvalid = form.querySelector(':invalid, .is-invalid');
                        if (firstInvalid) {
                            firstInvalid.focus();
                        }
                    }
                    
                    return;
                }
            }

            // Validate form with HTML5 validation
            const isValid = form.checkValidity();
            
            if (!isValid) {
                form.classList.add('was-validated');
                
                // JavaScript событие: ошибка валидации
                const invalidEvent = new CustomEvent('codeweberFormInvalid', {
                    detail: {
                        formId: config.formId,
                        form: form,
                        message: 'Form validation failed'
                    }
                });
                form.dispatchEvent(invalidEvent);
                
                // Focus on first invalid field
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
                
                return;
            }

            // Remove was-validated class if form is valid (for re-submission)
            form.classList.remove('was-validated');

            // Show loading state with spinner (like in testimonials download button)
            isOurControl = true;
            
            // Сохраняем текущую высоту и устанавливаем minHeight для предотвращения изменения размера
            const currentHeight = submitBtn.offsetHeight;
            if (currentHeight > 0) {
                submitBtn.style.minHeight = currentHeight + 'px';
            }
            
            submitBtn.disabled = true;
            
            // Показываем спиннер (того же размера что и текст)
            // Работает для всех форм: testimonial, newsletter subscription, и других codeweber форм
            const icon = submitBtn.querySelector('i');
            const span = submitBtn.querySelector('span');
            
            if (icon) {
                // Сохраняем размер иконки (fs-13 или другой), заменяем только иконку на спиннер
                const iconSize = icon.className.match(/fs-\d+/);
                const iconSizeClass = iconSize ? iconSize[0] : 'fs-13';
                
                // Заменяем иконку на спиннер с сохранением размера и добавляем отступ справа
                icon.className = 'uil uil-spinner-alt uil-spin ' + iconSizeClass;
                if (!icon.classList.contains('me-1')) {
                    icon.classList.add('me-1');
                }
                
                // Удаляем span обертку с текстом, если есть
                if (span) {
                    span.remove();
                }
                
                // Удаляем все текстовые узлы после иконки
                let node = icon.nextSibling;
                while (node) {
                    const next = node.nextSibling;
                    if (node.nodeType === Node.TEXT_NODE || (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'SPAN')) {
                        node.remove();
                    }
                    node = next;
                }
                
                // Добавляем новый текстовый узел с текстом загрузки
                submitBtn.appendChild(document.createTextNode(loadingText));
            } else {
                // Если структуры нет (старые формы без иконки), создаем с правильным размером
                submitBtn.innerHTML = '<i class="uil uil-spinner-alt uil-spin fs-13 me-1"></i>' + loadingText;
            }
            
            isOurControl = false;

            // JavaScript событие: запрос начат (кнопка заблокирована)
            const requestStartedEvent = new CustomEvent('codeweberFormRequestStarted', {
                detail: {
                    formId: config.formId,
                    form: form
                }
            });
            form.dispatchEvent(requestStartedEvent);

            try {
                // Collect data
                const data = collectFormData(form, config);
                console.log('[Form Submit Debug] Collected form data:', data);
                console.log('[Form Submit Debug] newsletter_consents in data:', data.newsletter_consents);
                
                // Additional validation for testimonial form
                if (config.type === 'testimonial') {
                    if (!data.testimonial_text || !data.testimonial_text.trim()) {
                        throw new Error('Testimonial text is required');
                    }
                    // Only validate rating range if it's set, don't show error if just not selected
                    if (data.rating !== null && data.rating !== undefined && (data.rating < 1 || data.rating > 5)) {
                        throw new Error('Rating must be between 1 and 5');
                    }
                    // If rating is not selected (null/undefined/0), let HTML5 validation handle it
                    if (!data.nonce) {
                        throw new Error('Security nonce is missing');
                    }
                    console.log('[Form Submit Debug] Testimonial validation passed:', {
                        testimonial_text: data.testimonial_text ? data.testimonial_text.substring(0, 50) + '...' : 'empty',
                        rating: data.rating,
                        has_nonce: !!data.nonce
                    });
                }

                // Get API nonce
                const apiNonce = config.type === 'testimonial' 
                    ? (codeweberTestimonialForm?.nonce || '')
                    : (codeweberForms?.restNonce || '');

                // JavaScript событие: запрос отправляется на сервер
                const requestSendingEvent = new CustomEvent('codeweberFormRequestSending', {
                    detail: {
                        formId: config.formId,
                        form: form,
                        endpoint: config.apiEndpoint
                    }
                });
                form.dispatchEvent(requestSendingEvent);

                // Send request
                console.log('[Form Submit Debug] Sending request to:', config.apiEndpoint);
                console.log('[Form Submit Debug] Request data (full):', JSON.stringify(data, null, 2));
                console.log('[Form Submit Debug] newsletter_consents in request:', data.newsletter_consents);
                if (config.type === 'testimonial') {
                    console.log('[Form Submit Debug] Testimonial data check:', {
                        testimonial_text_length: data.testimonial_text ? data.testimonial_text.length : 0,
                        rating_type: typeof data.rating,
                        rating_value: data.rating,
                        has_nonce: !!data.nonce,
                        nonce_length: data.nonce ? data.nonce.length : 0
                    });
                }
                const response = await fetch(config.apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': apiNonce
                    },
                    body: JSON.stringify(data)
                });

                // JavaScript событие: ответ получен от сервера
                const responseReceivedEvent = new CustomEvent('codeweberFormResponseReceived', {
                    detail: {
                        formId: config.formId,
                        form: form,
                        status: response.status,
                        ok: response.ok
                    }
                });
                form.dispatchEvent(responseReceivedEvent);

                let responseData;
                
                if (!response.ok) {
                    try {
                        const errorData = await response.json();
                        console.error('[Form Submit Debug] Server error response:', errorData);
                        
                        // Handle WordPress REST API validation errors
                        let errorMessage = errorData.message || errorData.data?.message || errorData.code || `HTTP ${response.status}: ${response.statusText}`;
                        
                        // If there are specific parameter errors, add them to the message
                        if (errorData.data?.params && typeof errorData.data.params === 'object') {
                            const paramErrors = Object.values(errorData.data.params).filter(msg => msg);
                            if (paramErrors.length > 0) {
                                errorMessage = paramErrors.join('. ');
                            }
                        }
                        
                        // Translate common error codes to user-friendly messages
                        if (errorData.code === 'rest_invalid_param') {
                            errorMessage = errorMessage || __('Please check the form fields and try again.', 'codeweber');
                        } else if (errorData.code === 'invalid_nonce') {
                            errorMessage = __('Security check failed. Please refresh the page and try again.', 'codeweber');
                        } else if (errorData.code === 'rate_limit_exceeded') {
                            errorMessage = __('Too many submissions. Please try again later.', 'codeweber');
                        } else if (errorData.code === 'already_subscribed') {
                            // Special handling for newsletter формы: показываем модальное окно "уже подписан",
                            // а не стандартное сообщение об ошибке / HTTP 400.
                            const isNewsletter = form.classList.contains('newsletter-subscription-form') ||
                                                 config.formId === '6119' || config.formId === 6119;
                            
                            if (isNewsletter) {
                                // Скрываем стандартный контейнер сообщений
                                if (formMessages) {
                                    formMessages.style.display = 'none';
                                    formMessages.innerHTML = '';
                                }

                                // Ищем (или создаем) модальное окно
                                let modal = form.closest('#modal') || document.getElementById('modal');

                                if (!modal) {
                                    const oldModal = document.getElementById('newsletter-success-modal');
                                    if (oldModal) {
                                        oldModal.remove();
                                    }

                                    const modalHtml = `
                                        <div class="modal fade" id="newsletter-success-modal" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-body"></div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                                    modal = document.getElementById('newsletter-success-modal');

                                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                        const bsModal = new bootstrap.Modal(modal);
                                        bsModal.show();
                                    }
                                } else {
                                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                        const bsModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                                        bsModal.show();
                                    }
                                }

                                // Показываем сообщение "уже подписан" внутри модалки с тем же оформлением конверта
                                if (modal && modal.id === 'newsletter-success-modal') {
                                    replaceModalContentWithEnvelope(modal, errorMessage);
                                } else {
                                    replaceModalContentWithEnvelope(form, errorMessage);
                                }

                                // Дополнительное событие для кастомных обработчиков
                                const alreadySubscribedEvent = new CustomEvent('codeweberNewsletterAlreadySubscribed', {
                                    detail: {
                                        formId: config.formId,
                                        form: form,
                                        message: errorMessage,
                                        apiResponse: errorData
                                    }
                                });
                                form.dispatchEvent(alreadySubscribedEvent);
                                document.dispatchEvent(alreadySubscribedEvent);

                                // Завершаем обработчик, чтобы не показывать стандартное сообщение об ошибке
                                return;
                            }
                        }
                        
                        throw new Error(errorMessage);
                    } catch (parseError) {
                        // If JSON parsing failed, use status text
                        console.error('[Form Submit Debug] Failed to parse error response:', parseError);
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                }

                try {
                    responseData = await response.json();
                } catch (parseError) {
                    throw new Error('Invalid response from server');
                }

                // JavaScript событие: ответ обработан
                const responseProcessedEvent = new CustomEvent('codeweberFormResponseProcessed', {
                    detail: {
                        formId: config.formId,
                        form: form,
                        success: responseData.success
                    }
                });
                form.dispatchEvent(responseProcessedEvent);

                if (responseData.success) {
                    // Reset form first
                    form.reset();
                    
                    // JavaScript событие: успешная отправка
                    // Универсальный обработчик setupUniversalSuccessHandler() обработает это событие
                    const successEvent = new CustomEvent('codeweberFormSubmitted', {
                        detail: {
                            formId: config.formId,
                            form: form,
                            submissionId: responseData.submission_id || responseData.data?.post_id || null,
                            message: responseData.message || 'Thank you!',
                            apiResponse: responseData
                        }
                    });
                    // Диспатчим событие на форме и на документе для универсальных обработчиков
                    form.dispatchEvent(successEvent);
                    document.dispatchEvent(successEvent);
                    
                    // Call custom success handler if exists (for backward compatibility)
                    if (config.onSuccess) {
                        config.onSuccess(form, responseData, formMessages);
                    }
                } else {
                    // Filter out rating validation errors for testimonial forms (we show visual indicator instead)
                    let errorMessage = responseData.message || 'An error occurred. Please try again.';
                    if (config.type === 'testimonial' && errorMessage.includes('Rating must be between 1 and 5')) {
                        // Don't show this message, visual indicator on stars is enough
                        errorMessage = '';
                    }
                    
                    if (formMessages && errorMessage) {
                        showMessage(formMessages, errorMessage, 'error', config.messagesClass);
                    }
                    
                    // JavaScript событие: ошибка отправки
                    const errorEvent = new CustomEvent('codeweberFormError', {
                        detail: {
                            formId: config.formId,
                            form: form,
                            message: errorMessage || 'An error occurred. Please try again.',
                            apiResponse: responseData
                        }
                    });
                    form.dispatchEvent(errorEvent);
                }

            } catch (error) {
                // Filter out rating validation errors for testimonial forms
                let errorMessage = error.message || 'An error occurred. Please try again.';
                if (config.type === 'testimonial' && errorMessage.includes('Rating must be between 1 and 5')) {
                    // Don't show this message, visual indicator on stars is enough
                    errorMessage = '';
                }
                
                if (formMessages && errorMessage) {
                    showMessage(formMessages, errorMessage, 'error', config.messagesClass);
                }
                
                // JavaScript событие: ошибка сети/сервера
                const networkErrorEvent = new CustomEvent('codeweberFormError', {
                    detail: {
                        formId: config.formId,
                        form: form,
                        message: errorMessage || 'Network error occurred',
                        error: error
                    }
                });
                form.dispatchEvent(networkErrorEvent);
            } finally {
                // Проверяем, не разблокирована ли кнопка другим скриптом
                if (!submitBtn.disabled && (submitBtn.textContent === loadingText || submitBtn.textContent.trim() === loadingText.trim())) {
                    // Button was unlocked by external script
                }
                
                // JavaScript событие: запрос полностью завершен
                const requestCompletedEvent = new CustomEvent('codeweberFormRequestCompleted', {
                    detail: {
                        formId: config.formId,
                        form: form
                    }
                });
                form.dispatchEvent(requestCompletedEvent);
                
                // Восстанавливаем состояние кнопки
                isOurControl = true;
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHTML;
                // Восстанавливаем оригинальный minHeight или очищаем
                if (originalMinHeight) {
                    submitBtn.style.minHeight = originalMinHeight;
                } else {
                    submitBtn.style.minHeight = '';
                }
                isOurControl = false;
            }
        });
    }

    /**
     * Initialize all forms
     */
    function initForms() {
        // Testimonial form
        const testimonialForm = document.getElementById('testimonial-form');
        if (testimonialForm) {
            initForm(testimonialForm);
        }

        // Codeweber forms
        const codeweberForms = document.querySelectorAll('.codeweber-form');
        if (codeweberForms.length > 0) {
            codeweberForms.forEach(form => {
                initForm(form);
            });
        }
    }

    /**
     * Универсальный обработчик успешной отправки всех форм codeweber по хуку
     * Определяет тип формы и применяет соответствующую логику
     */
    function setupUniversalSuccessHandler() {
        document.addEventListener('codeweberFormSubmitted', function(event) {
            const { formId, form, message, apiResponse } = event.detail;
            
            if (!form) return;
            
            // Определяем тип формы
            const isTestimonial = form.id === 'testimonial-form' || form.classList.contains('testimonial-form');
            const isNewsletter = form.classList.contains('newsletter-subscription-form') || 
                                formId === '6119' || formId === 6119;
            const isCodeweberForm = form.classList.contains('codeweber-form');
            
            // Получаем контейнер для сообщений
            const formMessages = form.querySelector('.form-messages') || 
                                form.querySelector('.testimonial-form-messages');
            
            // Обработка для testimonial формы
            if (isTestimonial) {
                // Testimonial форма всегда в модальном окне - заменяем содержимое на конверт
                replaceModalContentWithEnvelope(form, message);
                // Если модального окна нет, показываем сообщение
                const modal = form.closest('#modal') || document.getElementById('modal');
                if (!modal && formMessages) {
                    showMessage(formMessages, message, 'success', 'testimonial-form-messages');
                }
            }
            // Обработка для newsletter формы (ТОЛЬКО для newsletter формы)
            else if (isNewsletter) {
                // Скрываем стандартное сообщение, если оно есть
                if (formMessages) {
                    formMessages.style.display = 'none';
                    formMessages.innerHTML = '';
                }
                
                // Проверяем, есть ли модальное окно (сначала ищем внутри формы, потом универсальное из футера)
                let modal = form.closest('#modal') || document.getElementById('modal');
                
                // Если модального окна нет, создаем его динамически
                if (!modal) {
                    // Удаляем старое модальное окно, если оно есть
                    const oldModal = document.getElementById('newsletter-success-modal');
                    if (oldModal) {
                        oldModal.remove();
                    }
                    
                    // Создаем модальное окно
                    const modalHtml = `
                        <div class="modal fade" id="newsletter-success-modal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-body"></div>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                    modal = document.getElementById('newsletter-success-modal');
                    
                    // Показываем модальное окно
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    }
                } else {
                    // Используем существующее модальное окно - показываем его
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                        bsModal.show();
                    }
                }
                
                // Используем ту же функцию, что и для testimonial формы
                if (modal && modal.id === 'newsletter-success-modal') {
                    replaceModalContentWithEnvelope(modal, message);
                } else {
                    replaceModalContentWithEnvelope(form, message);
                }
            }
            // Обработка для обычных codeweber форм
            else if (isCodeweberForm) {
                // Показываем стандартное сообщение
                if (formMessages) {
                    showMessage(formMessages, message, 'success', 'form-messages');
                }
                
                // Закрываем модальное окно, если оно есть
                const modal = form.closest('#modal') || document.getElementById('modal');
                if (modal) {
                    setTimeout(() => {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const bsModal = bootstrap.Modal.getInstance(modal);
                            if (bsModal) bsModal.hide();
                        }
                    }, 5000);
                }
            }
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initForms();
            setupUniversalSuccessHandler();
        });
    } else {
        initForms();
        setupUniversalSuccessHandler();
    }

    // Reinitialize if form is added dynamically (e.g., modal opened)
    document.addEventListener('shown.bs.modal', function(e) {
        if (e.target.querySelector('#testimonial-form')) {
            setTimeout(function() {
                initForms();
            }, 100);
        }
    });
    
    // Also listen for content loaded in modal-content
    const modalContent = document.getElementById('modal-content');
    if (modalContent) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    const form = modalContent.querySelector('#testimonial-form');
                    if (form) {
                        setTimeout(initForms, 100);
                    }
                }
            });
        });
        observer.observe(modalContent, { childList: true, subtree: true });
    }

    // Make function globally available for testimonial form compatibility
    window.initTestimonialForm = function() {
        initForms();
    };

})();

