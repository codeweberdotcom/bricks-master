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
        
        // Используем стандартные имена полей: message, name, email
        const testimonialText = form.querySelector('[name="message"]');
        const authorName = form.querySelector('[name="name"]');
        const authorEmail = form.querySelector('[name="email"]');
        const rating = form.querySelector('[name="rating"]');
        const userId = form.querySelector('[name="user_id"]');
        const isLoggedIn = !!userId;


        let isValid = true;

        // Validate testimonial text (используем стандартное имя поля: message)
        const testimonialTextValid = testimonialText && testimonialText.value.trim();
        
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
            const authorNameValid = authorName && authorName.value.trim();
            
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
            const authorEmailValid = authorEmail && authorEmail.value.trim() && isValidEmail(authorEmail.value);
            
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

        // НОВОЕ: Приоритет 1 - определяем тип из data-form-type атрибута
        const formType = form.dataset.formType;
        
        if (formType) {
            // Используем тип из атрибута
            config.formId = form.dataset.formId || form.id.replace('form-', '');
            config.formName = form.dataset.formName || '';
            
            // Настраиваем конфигурацию в зависимости от типа
            switch (formType) {
                case 'testimonial':
                    config.type = 'testimonial';
                    config.apiEndpoint = codeweberTestimonialForm?.restUrl || '/wp-json/codeweber/v1/submit-testimonial';
                    config.nonceField = 'testimonial_nonce';
                    config.nonceAction = 'submit_testimonial';
                    config.consentPrefix = 'testimonial_consents';
                    config.messagesContainer = '.testimonial-form-messages';
                    config.messagesClass = 'testimonial-form-messages';
                    config.customValidation = validateTestimonialForm;
                    break;
                    
                case 'newsletter':
                case 'resume':
                case 'callback':
                case 'form':
                default:
                    // Все типы форм Codeweber используют единый API
                    config.type = 'codeweber';
                    config.apiEndpoint = (codeweberForms?.restUrl || '/wp-json/codeweber-forms/v1/') + 'submit';
                    config.nonceField = 'form_nonce';
                    config.nonceAction = 'codeweber_form_submit';
                    config.consentPrefix = 'newsletter_consents';
                    config.messagesContainer = '.form-messages';
                    config.messagesClass = 'form-messages';
                    break;
            }
        }
        // LEGACY: Обратная совместимость - проверка по ID и классам
        else if (form.id === 'testimonial-form' || form.classList.contains('testimonial-form')) {
            config.type = 'testimonial';
            config.formId = 'testimonial-form';
            config.apiEndpoint = codeweberTestimonialForm?.restUrl || '/wp-json/codeweber/v1/submit-testimonial';
            config.nonceField = 'testimonial_nonce';
            config.nonceAction = 'submit_testimonial';
            config.consentPrefix = 'testimonial_consents';
            config.messagesContainer = '.testimonial-form-messages';
            config.messagesClass = 'testimonial-form-messages';
            config.customValidation = validateTestimonialForm;
        }
        // LEGACY: Codeweber forms (newsletter, etc.)
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
        }

        return config;
    }

    /**
     * Collect form data based on form type
     */
    function collectFormData(form, config) {
        const formData = new FormData(form);
        const data = {};
        
        // Сохраняем formType из атрибута формы для передачи на сервер
        const formTypeFromAttr = form.dataset.formType;

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
            // Используем стандартные имена полей: message, name, email
            const testimonialText = formData.get('message');
            if (testimonialText && testimonialText.trim()) {
                data.message = testimonialText.trim();
            } else {
                data.message = '';
            }
            
            // Получаем name и email (стандартные имена)
            const authorName = formData.get('name');
            if (authorName && authorName.trim()) {
                data.name = authorName.trim();
            }
            const authorEmail = formData.get('email');
            if (authorEmail && authorEmail.trim()) {
                data.email = authorEmail.trim();
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
            data.form_id = config.formId; // Передаем form_id для CPT форм, чтобы сервер мог получить согласия из блоков
            // Передаем тип формы из data-form-type атрибута (для правильного определения success message)
            if (formTypeFromAttr) {
                data.form_type = formTypeFromAttr;
            } else if (config.type) {
                data.form_type = config.type;
            }
            
            const userId = formData.get('user_id');
            if (userId) {
                data.user_id = parseInt(userId, 10);
            } else {
                // Используем стандартные имена полей: name, email, role
                data.name = formData.get('name') || '';
                data.email = formData.get('email') || '';
                data.role = formData.get('role') || '';
                data.company = formData.get('company') || '';
            }
        } else {
            // Generic codeweber form
            // Collect FilePond file IDs
            const fileIds = [];
            const filepondInputs = form.querySelectorAll('input[type="file"][data-filepond="true"]');
            console.log('[Form Submit] Found FilePond inputs:', filepondInputs.length);
            filepondInputs.forEach((input, index) => {
                console.log('[Form Submit] Processing input', index, ':', input.id, 'filepondInstance:', !!input.filepondInstance);
                if (input.filepondInstance) {
                    const files = input.filepondInstance.getFiles();
                    console.log('[Form Submit] Input', index, 'has', files.length, 'files');
                    files.forEach((file, fileIndex) => {
                        console.log('[Form Submit] File', fileIndex, ':', {
                            id: file.id,
                            serverId: file.serverId,
                            status: file.status,
                            filename: file.filename
                        });
                        // FilePond returns file ID from server in serverId property
                        if (file.serverId) {
                            fileIds.push(file.serverId);
                            console.log('[Form Submit] Added file ID:', file.serverId);
                        } else {
                            console.warn('[Form Submit] File', fileIndex, 'has no serverId');
                        }
                    });
                } else {
                    console.warn('[Form Submit] Input', index, 'has no filepondInstance');
                    // Try to get file IDs from dataset as fallback
                    if (input.dataset.fileIds) {
                        const idsFromDataset = input.dataset.fileIds.split(',').filter(id => id.trim());
                        console.log('[Form Submit] Found file IDs in dataset:', idsFromDataset);
                        fileIds.push(...idsFromDataset);
                    }
                }
            });
            if (fileIds.length > 0) {
                data.file_ids = fileIds;
                console.log('[Form Submit] Collected FilePond file IDs:', fileIds);
            } else {
                console.warn('[Form Submit] No file IDs collected!');
            }
            data.honeypot = formData.get('form_honeypot') || '';
            data.form_id = config.formId;
            // Передаем тип формы из data-form-type атрибута (для правильного определения success message)
            // Приоритет: data-form-type из атрибута формы (для newsletter, resume, callback и т.д.)
            if (formTypeFromAttr) {
                data.form_type = formTypeFromAttr;
            } else if (config.type) {
                data.form_type = config.type;
            }
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
        
        // Для ошибок валидации не показываем сообщения - используем только Bootstrap валидацию (классы is-invalid)
        if (type === 'error') {
            return;
        }

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
        console.log('[Form Init] Initializing form:', form.id || form.className, form);
        
        // Ищем кнопку submit (может быть как button, так и input)
        let submitBtn = form.querySelector('button[type="submit"]');
        const isButtonSubmit = !!submitBtn;
        if (!submitBtn) {
            submitBtn = form.querySelector('input[type="submit"]');
        }
        if (!submitBtn) {
            console.warn('[Form Init] No submit button found in form:', form.id || form.className);
            return;
        }
        console.log('[Form Init] Submit button found:', submitBtn.tagName, 'isButton:', isButtonSubmit, 'isInput:', !isButtonSubmit);
        
        const config = getFormConfig(form);
        console.log('[Form Init] Form config:', config);
        if (!config.apiEndpoint) {
            console.warn('[Form Init] No API endpoint configured for form:', form.id || form.className);
            return;
        }
        
        // Проверяем, не инициализирована ли форма уже
        // Если форма уже инициализирована, проверяем наличие обработчика
        if (form.dataset.initialized === 'true') {
            // Проверяем, есть ли обработчик submit (должен быть функцией)
            const hasSubmitHandler = form._codeweberSubmitHandler !== undefined && typeof form._codeweberSubmitHandler === 'function';
            // Также проверяем, что обработчик действительно привязан к событию submit
            // Для этого проверяем, что обработчик существует и форма имеет правильный nonce для своего типа
            const expectedNonceField = config.nonceField;
            const hasCorrectNonce = form.querySelector(`input[name="${expectedNonceField}"]`);
            
            if (hasSubmitHandler && hasCorrectNonce) {
                // Обработчик уже есть и nonce правильный, не переинициализируем
                return;
            }
            // Если обработчика нет или nonce неправильный, сбрасываем флаг и продолжаем инициализацию
            form.dataset.initialized = 'false';
            // Удаляем старый обработчик если он был
            if (form._codeweberSubmitHandler) {
                form.removeEventListener('submit', form._codeweberSubmitHandler);
                delete form._codeweberSubmitHandler;
            }
        }

        const formMessages = form.querySelector(config.messagesContainer);
        
        // Для input[type="submit"] используем value, для button - innerHTML
        const isInputSubmit = submitBtn.tagName === 'INPUT';
        const originalBtnHTML = isInputSubmit ? submitBtn.value : submitBtn.innerHTML;
        const originalBtnText = isInputSubmit ? submitBtn.value : (submitBtn.textContent || submitBtn.innerText);
        const loadingText = submitBtn.dataset.loadingText || 'Отправка';
        const originalMinHeight = submitBtn.style.minHeight || '';
        
        // Флаг для отслеживания наших изменений кнопки
        let isOurControl = false;
        
        // Сохраняем оригинальный setter для disabled (для button и input одинаково)
        const elementPrototype = isInputSubmit 
            ? Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'disabled')
            : Object.getOwnPropertyDescriptor(HTMLButtonElement.prototype, 'disabled');
        
        // Перехватываем изменения disabled
        try {
            Object.defineProperty(submitBtn, 'disabled', {
                set: function(value) {
                    const wasDisabled = this.disabled;
                    const willBeDisabled = value;
                    
                    // Получаем текущий текст кнопки (для input - value, для button - textContent)
                    const currentText = isInputSubmit ? this.value : (this.textContent || this.innerText);
                    
                    // Если кнопка разблокируется во время отправки (когда текст "Отправка...")
                    if (wasDisabled && !willBeDisabled && (currentText === loadingText || currentText.trim() === loadingText.trim())) {
                        if (!isOurControl) {
                            // Сохраняем информацию о том, кто разблокировал
                            const unlockInfo = {
                                timestamp: Date.now(),
                                text: currentText,
                                stack: new Error().stack
                            };
                            submitBtn._unlockInfo = unlockInfo;
                        }
                    }
                    
                    // Устанавливаем значение
                    if (elementPrototype && elementPrototype.set) {
                        elementPrototype.set.call(this, value);
                    } else {
                        if (value) {
                            this.setAttribute('disabled', 'disabled');
                        } else {
                            this.removeAttribute('disabled');
                        }
                    }
                },
                get: function() {
                    if (elementPrototype && elementPrototype.get) {
                        return elementPrototype.get.call(this);
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
                    const currentText = isInputSubmit ? submitBtn.value : (submitBtn.textContent || submitBtn.innerText);
                    if (submitBtn.disabled === false && (currentText === loadingText || currentText.trim() === loadingText.trim())) {
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

        // Add nonce field if needed
        const existingNonceField = form.querySelector(`input[name="${config.nonceField}"]`);
        if (!existingNonceField) {
            // Если для testimonial формы есть form_nonce вместо testimonial_nonce, переименовываем его
            if (config.type === 'testimonial' && config.nonceField === 'testimonial_nonce') {
                const formNonceField = form.querySelector('input[name="form_nonce"]');
                if (formNonceField) {
                    formNonceField.name = 'testimonial_nonce';
                    console.log('[Form Init] Renamed form_nonce to testimonial_nonce for testimonial form');
                } else {
                    // Создаем новый nonce field
                    const nonceInput = document.createElement('input');
                    nonceInput.type = 'hidden';
                    nonceInput.name = 'testimonial_nonce';
                    nonceInput.value = codeweberTestimonialForm?.nonce || '';
                    form.insertBefore(nonceInput, form.firstChild);
                    console.log('[Form Init] Added testimonial_nonce field');
                }
            } else {
                // Для codeweber форм создаем form_nonce если его нет
                const nonceInput = document.createElement('input');
                nonceInput.type = 'hidden';
                nonceInput.name = config.nonceField;
                nonceInput.value = codeweberForms?.restNonce || '';
                form.insertBefore(nonceInput, form.firstChild);
            }
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

        // Добавляем обработчик submit
        const submitHandler = async function(e) {
            console.log('[Form Submit] Submit handler called for form:', config.formId, 'type:', config.type, 'event:', e.type);
            
            // Критически важно предотвратить стандартное поведение формы
            try {
                e.preventDefault();
            } catch (err) {
                console.warn('[Form Submit] Could not call preventDefault:', err);
            }
            try {
                e.stopPropagation();
                e.stopImmediatePropagation();
            } catch (err) {
                console.warn('[Form Submit] Could not call stopPropagation:', err);
            }

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
            console.log('[Form Submit] submittingResult:', submittingResult);
            
            // Если событие было отменено, не отправляем форму
            if (!submittingResult) {
                console.log('[Form Submit] Form submission cancelled by event');
                return;
            }

            // Clear previous messages (всегда скрываем - для ошибок используем только Bootstrap валидацию)
            if (formMessages) {
                formMessages.innerHTML = '';
                formMessages.className = config.messagesClass;
                formMessages.style.display = 'none';
                formMessages.classList.remove('alert', 'alert-danger', 'alert-success');
            }

            // Run custom validation first (for testimonial forms, this checks rating)
            // This ensures visual indicators are updated before HTML5 validation
            if (config.customValidation) {
                console.log('[Form Submit] Running custom validation...');
                const customValid = config.customValidation(form);
                console.log('[Form Submit] Custom validation result:', customValid);
                
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
            console.log('[Form Submit] Checking HTML5 validation...');
            const isValid = form.checkValidity();
            console.log('[Form Submit] HTML5 validation result:', isValid);
            
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
            
            // Для input[type="submit"] заменяем на button с иконкой спиннера, для button - innerHTML с иконкой
            if (isInputSubmit) {
                // Сохраняем оригинальный input для восстановления
                const originalInputValue = submitBtn.value;
                const originalInputClass = submitBtn.className;
                
                // Создаем временный button с иконкой спиннера
                const tempButton = document.createElement('button');
                tempButton.type = 'submit';
                tempButton.className = originalInputClass + ' btn-icon btn-icon-start';
                tempButton.disabled = true;
                tempButton.innerHTML = '<i class="uil uil-spinner-alt uil-spin fs-13 me-1"></i>' + loadingText;
                
                // Сохраняем ссылку на оригинальный input для восстановления
                // Копируем dataset правильно
                const datasetCopy = {};
                if (submitBtn.dataset) {
                    for (const key in submitBtn.dataset) {
                        if (submitBtn.dataset.hasOwnProperty(key)) {
                            datasetCopy[key] = submitBtn.dataset[key];
                        }
                    }
                }
                tempButton._originalInput = {
                    value: originalInputValue,
                    className: originalInputClass,
                    dataset: datasetCopy
                };
                // Флаг для отслеживания замены
                tempButton._wasInputReplaced = true;
                
                // Заменяем input на button
                submitBtn.parentNode.replaceChild(tempButton, submitBtn);
                
                // Обновляем ссылку на submitBtn для дальнейшего использования
                submitBtn = tempButton;
            } else {
                // Добавляем иконку uil-send перед текстом при отправке
                // Работает для всех форм: testimonial, newsletter subscription, и других codeweber форм
                const span = submitBtn.querySelector('span');
                const buttonText = span ? (span.textContent || span.innerText || loadingText) : (submitBtn.textContent || submitBtn.innerText || loadingText);
                
                // Заменяем содержимое кнопки на иконку + текст через innerHTML
                // Это гарантирует правильную работу псевдоэлементов Unicons
                submitBtn.innerHTML = '<i class="uil uil-send fs-13 me-1"></i> <span>' + buttonText + '</span>';
                
                // Принудительно применяем стили для иконки (для правильной работы псевдоэлементов)
                const icon = submitBtn.querySelector('i');
                if (icon) {
                    // Принудительно пересчитываем стили для применения псевдоэлементов
                    void icon.offsetHeight; // Trigger reflow
                    // Убеждаемся, что иконка видима и имеет правильные стили
                    icon.style.display = 'inline-block';
                    icon.style.visibility = 'visible';
                    icon.style.fontFamily = '"Unicons"';
                    icon.style.fontStyle = 'normal';
                    icon.style.fontWeight = 'normal';
                }
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

            console.log('[Form Submit] Starting data collection and submission...');
            try {
                // Collect data
                console.log('[Form Submit] Calling collectFormData...');
                const data = collectFormData(form, config);
                console.log('[Form Submit Debug] Collected form data:', data);
                console.log('[Form Submit Debug] newsletter_consents in data:', data.newsletter_consents);
                
                // Additional validation for testimonial form
                // Используем только HTML5 валидацию через setCustomValidity - не бросаем исключения
                // Валидация уже выполнена через validateTestimonialForm (добавлены классы is-invalid)
                if (config.type === 'testimonial') {
                    console.log('[Form Submit Debug] Testimonial validation passed:', {
                        message: data.message ? data.message.substring(0, 50) + '...' : 'empty',
                        rating: data.rating,
                        has_nonce: !!data.nonce
                    });
                }

                // Get API nonce
                const apiNonce = config.type === 'testimonial' 
                    ? (codeweberTestimonialForm?.nonce || codeweberTestimonialForm?.restNonce || '')
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
                        message_length: data.message ? data.message.length : 0,
                        rating_type: typeof data.rating,
                        rating_value: data.rating,
                        has_nonce: !!data.nonce,
                        nonce_length: data.nonce ? data.nonce.length : 0
                    });
                }
                console.log('[Form Submit] Making fetch request to:', config.apiEndpoint);
                console.log('[Form Submit] Request data keys:', Object.keys(data));
                const response = await fetch(config.apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': apiNonce
                    },
                    body: JSON.stringify(data)
                });
                console.log('[Form Submit] Fetch response received, status:', response.status);

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

                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'form-submit-universal.js:1173',message:'Response processed, checking success',data:{formId:config.formId,success:responseData.success,hasResponseData:!!responseData},timestamp:Date.now(),sessionId:'debug-session',runId:'filepond-reinit',hypothesisId:'A'})}).catch(()=>{});
                // #endregion

                if (responseData.success) {
                    // Reset form first
                    form.reset();
                    
                    // Очистка FilePond выполняется в универсальном обработчике с задержкой
                    // Здесь только диспатчим событие, чтобы не мешать показу модального окна
                    
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
                    // Не показываем сообщения об ошибках - используем только Bootstrap валидацию
                    // Все ошибки валидации обрабатываются через HTML5 валидацию и классы is-invalid
                    
                    // JavaScript событие: ошибка отправки (для внешних обработчиков, но без показа сообщений)
                    const errorEvent = new CustomEvent('codeweberFormError', {
                        detail: {
                            formId: config.formId,
                            form: form,
                            message: responseData.message || 'An error occurred. Please try again.',
                            apiResponse: responseData
                        }
                    });
                    form.dispatchEvent(errorEvent);
                }

            } catch (error) {
                // Не показываем сообщения об ошибках валидации - используем только Bootstrap валидацию
                // Ошибки валидации обрабатываются через HTML5 валидацию и классы is-invalid
                // Сообщения об ошибках не показываются - только визуальные индикаторы через Bootstrap
                
                // JavaScript событие: ошибка (для внешних обработчиков, но без показа сообщений)
                const networkErrorEvent = new CustomEvent('codeweberFormError', {
                    detail: {
                        formId: config.formId,
                        form: form,
                        message: error.message || 'Network error occurred',
                        error: error
                    }
                });
                form.dispatchEvent(networkErrorEvent);
            } finally {
                // Проверяем, не разблокирована ли кнопка другим скриптом
                const currentText = isInputSubmit ? submitBtn.value : (submitBtn.textContent || submitBtn.innerText);
                if (!submitBtn.disabled && (currentText === loadingText || currentText.trim() === loadingText.trim())) {
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
                
                // Если input был заменен на button, восстанавливаем input
                if (submitBtn._wasInputReplaced && submitBtn._originalInput) {
                    const originalInputData = submitBtn._originalInput;
                    // Восстанавливаем оригинальный input
                    const restoredInput = document.createElement('input');
                    restoredInput.type = 'submit';
                    restoredInput.value = originalInputData.value || originalBtnHTML;
                    restoredInput.className = originalInputData.className || '';
                    restoredInput.disabled = false;
                    // Копируем все data-атрибуты
                    if (originalInputData.dataset) {
                        for (const key in originalInputData.dataset) {
                            if (originalInputData.dataset.hasOwnProperty(key)) {
                                restoredInput.dataset[key] = originalInputData.dataset[key];
                            }
                        }
                    }
                    // Заменяем button обратно на input
                    submitBtn.parentNode.replaceChild(restoredInput, submitBtn);
                    // Обновляем ссылку для дальнейшего использования
                    submitBtn = restoredInput;
                } else {
                    // Для input используем value, для button - innerHTML
                    if (isInputSubmit) {
                        submitBtn.value = originalBtnHTML;
                    } else {
                        submitBtn.innerHTML = originalBtnHTML;
                    }
                }
                
                // Восстанавливаем оригинальный minHeight или очищаем
                if (originalMinHeight) {
                    submitBtn.style.minHeight = originalMinHeight;
                } else {
                    submitBtn.style.minHeight = '';
                }
                isOurControl = false;
            }
        };
        
        // Добавляем обработчик клика для button[type="submit"] в capture фазе
        // чтобы гарантировать перехват до стандартного поведения формы
        // ВАЖНО: этот обработчик должен быть определён ПОСЛЕ объявления submitHandler
        // поэтому переносим его ниже, после addEventListener('submit', submitHandler)
        
        form.addEventListener('submit', submitHandler);
        console.log('[Form Init] Submit event listener added to form:', config.formId, 'type:', config.type);
        
        // Сохраняем ссылку на обработчик для использования в обработчике клика
        const submitHandlerRef = submitHandler;
        
        // Добавляем обработчик клика для button[type="submit"] в capture фазе
        // чтобы гарантировать перехват до стандартного поведения формы
        if (!isInputSubmit) {
            // Для button[type="submit"] добавляем обработчик клика
            submitBtn.addEventListener('click', function(e) {
                console.log('[Form Submit] Button submit clicked for form:', config.formId, 'type:', config.type);
                
                // Проверяем валидность формы перед отправкой
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                    }
                    return false;
                }
                
                // Если форма валидна, предотвращаем стандартную отправку
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                // Создаем синтетическое событие submit для передачи в обработчик
                const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
                // Предотвращаем стандартное поведение в событии (как будто уже вызвали preventDefault)
                Object.defineProperty(submitEvent, 'defaultPrevented', {
                    get: function() { return true; },
                    configurable: true
                });
                
                // Вызываем обработчик submit напрямую (используем сохранённую ссылку)
                console.log('[Form Submit] Calling submitHandler directly for form:', config.formId);
                console.log('[Form Submit] submitHandlerRef type:', typeof submitHandlerRef);
                
                try {
                    if (typeof submitHandlerRef === 'function') {
                        console.log('[Form Submit] About to call submitHandlerRef...');
                        const result = submitHandlerRef(submitEvent);
                        console.log('[Form Submit] submitHandlerRef called, result:', result);
                    } else {
                        console.error('[Form Submit] submitHandlerRef is not a function!');
                    }
                } catch (error) {
                    console.error('[Form Submit] Error calling submitHandler:', error);
                    console.error('[Form Submit] Error stack:', error.stack);
                }
                
                return false;
            }, true); // Capture phase
        }
        
        // Для input[type="submit"] также добавляем обработчик клика
        // так как input[type="submit"] может вызвать стандартную отправку формы до того, как сработает submit
        if (isInputSubmit) {
            // Используем capture phase для перехвата события раньше всех других обработчиков
            submitBtn.addEventListener('click', function(e) {
                console.log('[Form Submit] Submit button clicked for form:', config.formId, 'type:', config.type);
                
                // Предотвращаем стандартное поведение (отправку формы)
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                // Проверяем валидность формы
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    // Фокусируемся на первом невалидном поле
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                    }
                    return false;
                }
                
                // Вызываем обработчик submit напрямую
                // Создаем синтетическое событие submit
                const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
                // Устанавливаем preventDefault в событии
                Object.defineProperty(submitEvent, 'defaultPrevented', {
                    get: function() { return true; },
                    configurable: true
                });
                // Вызываем обработчик напрямую
                submitHandler(submitEvent);
                
                return false;
            }, true); // Используем capture phase для перехвата события раньше
        }
        
        // Сохраняем ссылку на обработчик для проверки
        form._codeweberSubmitHandler = submitHandler;
        
        // Устанавливаем флаг инициализации
        form.dataset.initialized = 'true';
        
        // Логирование для отладки
        if (window.console && console.log) {
            console.log('[Form Init] Form initialized:', {
                formId: config.formId,
                formType: config.type,
                apiEndpoint: config.apiEndpoint,
                nonceField: config.nonceField,
                consentPrefix: config.consentPrefix
            });
        }
        
        // Помечаем форму как инициализированную после успешной настройки
        form.dataset.initialized = 'true';
    }

    /**
     * Initialize rating stars
     */
    function initRatingStars() {
        const ratingContainers = document.querySelectorAll('.rating-stars-wrapper:not([data-initialized])');
        
        if (ratingContainers.length === 0) {
            return;
        }
        
        ratingContainers.forEach(function(container) {
            // Mark as initialized to prevent double initialization
            container.setAttribute('data-initialized', 'true');
            
            const stars = container.querySelectorAll('.rating-star-item');
            const inputId = container.dataset.ratingInput;
            let selectedRating = 0;
            
            if (!inputId) {
                console.warn('[Rating Stars] No data-rating-input attribute found on container');
                return;
            }
            
            // Get initial rating from input
            const input = document.getElementById(inputId);
            if (!input) {
                console.warn('[Rating Stars] Input element not found with ID:', inputId);
                return;
            }
            
            if (input.value) {
                selectedRating = parseInt(input.value) || 0;
                updateStarsVisual(stars, selectedRating);
            }
            
            // Click handler - attach directly to each star
            stars.forEach(function(star) {
                star.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const rating = parseInt(this.dataset.rating);
                    if (isNaN(rating) || rating < 1 || rating > 5) {
                        return;
                    }
                    
                    selectedRating = rating;
                    if (input) {
                        input.value = rating;
                        
                        // Validate rating immediately when star is clicked
                        // Remove validation error classes
                        input.classList.remove('is-invalid');
                        container.classList.remove('is-invalid');
                        
                        // Trigger validation event to update form state
                        const form = input.closest('form');
                        if (form) {
                            // Trigger input event for HTML5 validation
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                            
                            // If form has was-validated class, re-validate rating field
                            if (form.classList.contains('was-validated')) {
                                // Manually validate rating field
                                input.setCustomValidity('');
                            }
                        }
                    }
                    
                    // Update visual state immediately (show selected stars)
                    updateStarsVisual(stars, rating);
                });
            });
            
            // Hover handlers - highlight all stars from first to current
            stars.forEach(function(star) {
                star.addEventListener('mouseenter', function() {
                    const hoverRating = parseInt(this.dataset.rating);
                    if (isNaN(hoverRating)) {
                        return;
                    }
                    // Highlight all stars from 1 to hoverRating (left to right)
                    stars.forEach(function(s) {
                        const sRating = parseInt(s.dataset.rating);
                        if (sRating <= hoverRating) {
                            s.style.color = '#fcc032';
                        } else {
                            s.style.color = 'rgba(0, 0, 0, 0.1)';
                        }
                    });
                });
            });
            
            // Reset on mouse leave
            container.addEventListener('mouseleave', function() {
                updateStarsVisual(stars, selectedRating);
            });
        });
    }
    
    /**
     * Update stars visual state
     */
    function updateStarsVisual(stars, rating) {
        stars.forEach(function(star) {
            const starRating = parseInt(star.dataset.rating);
            if (starRating <= rating) {
                star.style.color = '#fcc032';
                star.classList.add('active');
            } else {
                star.style.color = 'rgba(0, 0, 0, 0.1)';
                star.classList.remove('active');
            }
        });
    }

    /**
     * Initialize file input translations
     */
    function initFileInputs() {
        const fileInputs = document.querySelectorAll('input[type="file"].form-control:not([data-file-initialized])');
        
        // Helper function to format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
        
        
        fileInputs.forEach(function(input) {
            // Mark as initialized to prevent double initialization
            input.setAttribute('data-file-initialized', 'true');
            
            const displayInput = document.getElementById(input.id + '-display');
            const browseButton = document.querySelector('[data-file-input="' + input.id + '"]');
            const fileListContainer = document.getElementById(input.id + '-list');
            
            const noFileText = input.dataset.noFileText || 'Файл не выбран';
            const isMultiple = input.hasAttribute('multiple');
            
            // Helper function to render file list
            function renderFileList(files) {
                if (!fileListContainer) return;
                
                if (!files || files.length === 0) {
                    fileListContainer.innerHTML = '';
                    return;
                }
                
                // For single file (non-multiple), show simple text in input
                if (files.length === 1 && !isMultiple) {
                    const file = files[0];
                    if (displayInput) {
                        displayInput.value = file.name;
                        displayInput.classList.remove('text-muted');
                        displayInput.classList.add('text-success');
                    }
                    fileListContainer.innerHTML = '';
                    return;
                }
                
                // For multiple files, show beautiful list
                if (isMultiple && files.length > 0) {
                    // Update input with summary
                    if (displayInput) {
                        displayInput.value = files.length + ' файл' + (files.length > 1 ? (files.length < 5 ? 'а' : 'ов') : '') + ' выбрано';
                        displayInput.classList.remove('text-muted');
                        displayInput.classList.add('text-success');
                    }
                    
                    // Create list group
                    let listHTML = '<ul class="list-group list-group-flush">';
                    Array.from(files).forEach(function(file, index) {
                        const fileSize = formatFileSize(file.size);
                        const fileNumber = index + 1;
                        listHTML += '<li class="list-group-item d-flex align-items-center px-0 py-2">';
                        listHTML += '<span class="me-2 text-primary fw-bold">' + fileNumber + '.</span>';
                        listHTML += '<div class="flex-grow-1">';
                        listHTML += '<span class="fw-semibold small">' + file.name + '</span> <small class="text-muted">' + fileSize + '</small>';
                        listHTML += '</div>';
                        listHTML += '</li>';
                    });
                    listHTML += '</ul>';
                    fileListContainer.innerHTML = listHTML;
                } else {
                    // Single file in multiple mode - show in list too
                    const file = files[0];
                    if (displayInput) {
                        displayInput.value = file.name;
                        displayInput.classList.remove('text-muted');
                        displayInput.classList.add('text-success');
                    }
                    const fileSize = formatFileSize(file.size);
                    let listHTML = '<ul class="list-group list-group-flush">';
                    listHTML += '<li class="list-group-item d-flex align-items-center px-0 py-2">';
                    listHTML += '<span class="me-2 text-primary fw-bold">1.</span>';
                    listHTML += '<div class="flex-grow-1">';
                    listHTML += '<span class="fw-semibold small">' + file.name + '</span> <small class="text-muted">' + fileSize + '</small>';
                    listHTML += '</div>';
                    listHTML += '</li>';
                    listHTML += '</ul>';
                    fileListContainer.innerHTML = listHTML;
                }
            }
            
            // Browse button click handler
            if (browseButton) {
                browseButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    input.click();
                });
            }
            
            // Update display on change
            input.addEventListener('change', function(e) {
                const files = e.target.files;
                renderFileList(files);
            });
            
            // Set initial display
            if (!input.files || input.files.length === 0) {
                if (displayInput) {
                    displayInput.value = '';
                    displayInput.placeholder = noFileText;
                    displayInput.classList.remove('text-success');
                    displayInput.classList.add('text-muted');
                }
                if (fileListContainer) {
                    fileListContainer.innerHTML = '';
                }
            } else {
                renderFileList(input.files);
            }
        });
    }

    /**
     * Initialize all forms
     */
    function initForms() {
        // Initialize file inputs
        initFileInputs();
        
        // Testimonial form (legacy)
        const testimonialForm = document.getElementById('testimonial-form');
        if (testimonialForm) {
            initForm(testimonialForm);
        }

        // Codeweber forms (включая testimonial с data-form-type="testimonial")
        const codeweberForms = document.querySelectorAll('.codeweber-form');
        if (codeweberForms.length > 0) {
            codeweberForms.forEach(form => {
                // Проверяем тип формы и правильность nonce
                const formType = form.dataset.formType;
                if (formType === 'testimonial') {
                    // Для testimonial форм проверяем наличие правильного nonce
                    const hasTestimonialNonce = form.querySelector('input[name="testimonial_nonce"]');
                    const hasFormNonce = form.querySelector('input[name="form_nonce"]');
                    // Если есть form_nonce, но нет testimonial_nonce - переинициализируем
                    if (hasFormNonce && !hasTestimonialNonce) {
                        form.dataset.initialized = 'false';
                        if (form._codeweberSubmitHandler) {
                            form.removeEventListener('submit', form._codeweberSubmitHandler);
                            delete form._codeweberSubmitHandler;
                        }
                    }
                }
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
            
            // Определяем тип формы (приоритет: data-form-type, затем ID и классы)
            const formType = form.dataset.formType;
            const isTestimonial = formType === 'testimonial' || 
                                form.id === 'testimonial-form' || 
                                form.classList.contains('testimonial-form');
            const isNewsletter = formType === 'newsletter' ||
                                form.classList.contains('newsletter-subscription-form') || 
                                formId === '6119' || formId === 6119;
            const isCodeweberForm = form.classList.contains('codeweber-form');
            
            // Debug log: cleanup context
            try {
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        sessionId: 'debug-session',
                        runId: 'filepond-clear',
                        timestamp: Date.now(),
                        location: 'form-submit-universal.js:success-start',
                        data: {
                            formId,
                            formType,
                            pondsInForm: (typeof FilePond !== 'undefined' && typeof FilePond.find === 'function') ? FilePond.find(form).length : 'no-FilePond',
                            pondsInBody: (typeof FilePond !== 'undefined' && typeof FilePond.find === 'function') ? FilePond.find(document.body).length : 'no-FilePond',
                            fileInputs: Array.from(form.querySelectorAll('input[type="file"][data-filepond="true"]')).map(i => ({id:i.id, dataset:i.dataset.fileIds||'', hasInstance:!!i.filepondInstance}))
                        }
                    })
                }).catch(()=>{});
            } catch(e) {
                console.warn('[Form Submit] Debug log failed', e);
            }
            
            // Получаем контейнер для сообщений
            const formMessages = form.querySelector('.form-messages') || 
                                form.querySelector('.testimonial-form-messages');
            
            // ВСЕГДА скрываем стандартное сообщение над формой для всех форм
            if (formMessages) {
                formMessages.style.display = 'none';
                formMessages.innerHTML = '';
            }
            
            // ВСЕГДА показываем модальное окно с сообщением об успехе для всех форм
            let modal = form.closest('#modal') || document.getElementById('modal');
            
            // Если модального окна нет, создаем его динамически
            if (!modal) {
                // Удаляем старое модальное окно, если оно есть
                const oldModal = document.getElementById('codeweber-form-success-modal');
                if (oldModal) {
                    oldModal.remove();
                }
                
                // Создаем модальное окно
                const modalHtml = `
                    <div class="modal fade" id="codeweber-form-success-modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body"></div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                modal = document.getElementById('codeweber-form-success-modal');
                
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
            
            // Заменяем содержимое модального окна на конверт с сообщением
            if (modal) {
                replaceModalContentWithEnvelope(modal, message);
            } else {
                // Если модального окна нет, заменяем содержимое формы
                replaceModalContentWithEnvelope(form, message);
            }
            
            // Очистка FilePond после успешной отправки - выполняем с задержкой, чтобы не мешать показу модального окна
            setTimeout(() => {
                try {
                    // #region agent log
                    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'form-submit-universal.js:1795',message:'Universal handler: Starting FilePond cleanup',data:{formId,hasFilePond:typeof FilePond!=='undefined',hasFind:typeof FilePond!=='undefined'&&typeof FilePond.find==='function'},timestamp:Date.now(),sessionId:'debug-session',runId:'filepond-reinit',hypothesisId:'E'})}).catch(()=>{});
                    // #endregion
                    
                    // Переинициализация FilePond: находим инстансы через FilePond.find() и уничтожаем их
                    if (typeof FilePond !== 'undefined' && typeof FilePond.find === 'function') {
                        // Пробуем найти инстансы в форме и во всем документе
                        const pondsInForm = FilePond.find(form);
                        const pondsInBody = FilePond.find(document.body);
                        // #region agent log
                        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'form-submit-universal.js:1802',message:'Universal handler: FilePond instances found',data:{countInForm:pondsInForm.length,countInBody:pondsInBody.length,filepondRootsInForm:form.querySelectorAll('.filepond--root').length,filepondRootsInBody:document.querySelectorAll('.filepond--root').length},timestamp:Date.now(),sessionId:'debug-session',runId:'filepond-reinit',hypothesisId:'E'})}).catch(()=>{});
                        // #endregion
                        // Используем инстансы из body, если в форме не найдено
                        const ponds = pondsInForm.length > 0 ? pondsInForm : pondsInBody;
                        ponds.forEach((pond, index) => {
                            try {
                                // #region agent log
                                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'form-submit-universal.js:1807',message:'Universal handler: Destroying pond',data:{pondIndex:index,hasDestroy:typeof pond.destroy==='function'},timestamp:Date.now(),sessionId:'debug-session',runId:'filepond-reinit',hypothesisId:'E'})}).catch(()=>{});
                                // #endregion
                                if (typeof pond.destroy === 'function') {
                                    pond.destroy();
                                    // #region agent log
                                    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'form-submit-universal.js:1811',message:'Universal handler: Pond destroyed',data:{pondIndex:index},timestamp:Date.now(),sessionId:'debug-session',runId:'filepond-reinit',hypothesisId:'E'})}).catch(()=>{});
                                    // #endregion
                                }
                            } catch (e) {
                                console.warn('[Form Submit] Failed to destroy FilePond instance', e);
                                // #region agent log
                                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'form-submit-universal.js:1816',message:'Universal handler: Error destroying pond',data:{pondIndex:index,error:e.message},timestamp:Date.now(),sessionId:'debug-session',runId:'filepond-reinit',hypothesisId:'E'})}).catch(()=>{});
                                // #endregion
                            }
                        });
                    }
                    
                    // Также ищем оригинальные input'ы и очищаем их атрибуты
                    const filepondInputs = form.querySelectorAll('input[type="file"][data-filepond="true"]');
                    // #region agent log
                    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'form-submit-universal.js:1823',message:'Universal handler: Cleaning file inputs',data:{count:filepondInputs.length},timestamp:Date.now(),sessionId:'debug-session',runId:'filepond-reinit',hypothesisId:'E'})}).catch(()=>{});
                    // #endregion
                    filepondInputs.forEach((input) => {
                        input.removeAttribute('data-filepond-initialized');
                        input.dataset.fileIds = '';
                        input.value = '';
                        if (input.filepondInstance) {
                            input.filepondInstance = null;
                        }
                    });
                    
                    // Удаляем все FilePond root элементы (они могут остаться после destroy)
                    const filepondRoots = form.querySelectorAll('.filepond--root');
                    // #region agent log
                    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'form-submit-universal.js:1833',message:'Universal handler: Removing root elements',data:{count:filepondRoots.length},timestamp:Date.now(),sessionId:'debug-session',runId:'filepond-reinit',hypothesisId:'E'})}).catch(()=>{});
                    // #endregion
                    filepondRoots.forEach((root) => {
                        root.remove();
                    });
                    
                    // Удаляем скрытые input'ы с file[] значениями
                    const filepondDataInputs = form.querySelectorAll('input[type="hidden"][name^="file"]');
                    filepondDataInputs.forEach((input) => {
                        if (input.name === 'file[]' || input.closest('.filepond--data')) {
                            input.remove();
                        }
                    });
                    
                    // Удаляем fieldset.filepond--data
                    const filepondDataFieldsets = form.querySelectorAll('fieldset.filepond--data');
                    filepondDataFieldsets.forEach((fieldset) => {
                        fieldset.remove();
                    });
                    
                    // Переинициализируем FilePond
                    // #region agent log
                    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'form-submit-universal.js:1848',message:'Universal handler: Calling initFilePond',data:{hasInitFilePond:typeof window.initFilePond==='function'},timestamp:Date.now(),sessionId:'debug-session',runId:'filepond-reinit',hypothesisId:'E'})}).catch(()=>{});
                    // #endregion
                    if (typeof window.initFilePond === 'function') {
                        window.initFilePond();
                    }
                } catch (e) {
                    console.error('[Form Submit] Error cleaning up FilePond', e);
                    // #region agent log
                    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'form-submit-universal.js:1855',message:'Universal handler: Error in cleanup',data:{error:e.message,errorStack:e.stack},timestamp:Date.now(),sessionId:'debug-session',runId:'filepond-reinit',hypothesisId:'E'})}).catch(()=>{});
                    // #endregion
                }
            }, 300); // Задержка, чтобы модальное окно успело показаться
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initRatingStars(); // Initialize rating stars first
            initFileInputs(); // Initialize file inputs
            initForms();
            setupUniversalSuccessHandler();
            // Re-initialize after forms are initialized
            setTimeout(function() {
                initRatingStars();
                initFileInputs();
            }, 100);
        });
    } else {
        initRatingStars(); // Initialize rating stars first
        initFileInputs(); // Initialize file inputs
        initForms();
        setupUniversalSuccessHandler();
        // Re-initialize after forms are initialized
        setTimeout(function() {
            initRatingStars();
            initFileInputs();
        }, 100);
    }

    // Reinitialize if form is added dynamically (e.g., modal opened)
    document.addEventListener('shown.bs.modal', function(e) {
        // Проверяем наличие любой codeweber формы в модалке (не только testimonial-form)
        if (e.target.querySelector('.codeweber-form')) {
            setTimeout(function() {
                initRatingStars(); // Reinitialize rating stars
                initFileInputs(); // Reinitialize file inputs
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
                    // Проверяем наличие любой codeweber формы (не только testimonial-form)
                    const form = modalContent.querySelector('.codeweber-form');
                    if (form) {
                        setTimeout(function() {
                            initRatingStars(); // Reinitialize rating stars
                            initFileInputs(); // Reinitialize file inputs
                            initForms();
                        }, 100);
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

