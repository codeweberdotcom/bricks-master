/**
 * Testimonial Form Handler
 * 
 * Handles AJAX submission of testimonial form
 * 
 * @package Codeweber
 */

(function() {
    'use strict';

    /**
     * Initialize testimonial form
     */
    function initTestimonialForm() {
        const form = document.getElementById('testimonial-form');
        if (!form) return;

        const submitBtn = form.querySelector('[type="submit"]');
        if (!submitBtn) return;

        const originalBtnText = submitBtn.textContent || submitBtn.innerText;
        const formMessages = form.querySelector('.testimonial-form-messages');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Clear previous messages
            if (formMessages) {
                formMessages.innerHTML = '';
                formMessages.className = 'testimonial-form-messages';
            }

            // Validate form
            if (!validateForm(form)) {
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            const loadingText = submitBtn.dataset.loadingText || 'Отправка...';
            submitBtn.textContent = loadingText;

            // Collect form data
            const formData = new FormData(form);
            const formNonce = formData.get('testimonial_nonce');
            const userId = formData.get('user_id'); // Check if user is logged in
            const isLoggedIn = !!userId;
            
            const data = {
                testimonial_text: formData.get('testimonial_text'),
                rating: formData.get('rating'),
                nonce: formNonce,
                honeypot: formData.get('testimonial_honeypot') || '' // Honeypot field
            };
            
            // If user is logged in, send user_id; otherwise send name and email
            if (isLoggedIn && userId) {
                data.user_id = parseInt(userId, 10); // Ensure it's a number
                console.log('[Testimonial Form] User logged in, sending user_id:', userId);
            } else {
                data.author_name = formData.get('author_name');
                data.author_email = formData.get('author_email');
                data.author_role = formData.get('author_role') || '';
                data.company = formData.get('company') || '';
                console.log('[Testimonial Form] User not logged in, sending guest data');
            }

            // Get REST API URL and nonce
            const restUrl = codeweberTestimonialForm?.restUrl || '/wp-json/codeweber/v1/submit-testimonial';
            const restNonce = codeweberTestimonialForm?.nonce || '';
            console.log('[Testimonial Form] REST URL:', restUrl);
            console.log('[Testimonial Form] REST Nonce:', restNonce);
            console.log('[Testimonial Form] Data to send:', JSON.stringify(data, null, 2));

            // Send AJAX request
            fetch(restUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': restNonce
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Network response was not ok');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showMessage(formMessages, data.message || 'Thank you for your testimonial! It will be reviewed and published soon.', 'success');
                    form.reset();
                    
                    // Close modal if exists
                    const modal = form.closest('.modal');
                    if (modal) {
                        setTimeout(() => {
                            // Try Bootstrap 5
                            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                const bsModal = bootstrap.Modal.getInstance(modal);
                                if (bsModal) {
                                    bsModal.hide();
                                }
                            }
                            // Fallback to jQuery Bootstrap
                            else if (typeof jQuery !== 'undefined' && jQuery(modal).modal) {
                                jQuery(modal).modal('hide');
                            }
                        }, 2000);
                    }
                } else {
                    showMessage(formMessages, data.message || 'An error occurred. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Testimonial Form Error:', error);
                showMessage(formMessages, error.message || 'An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            });
        });
    }

    /**
     * Validate form fields
     * 
     * @param {HTMLFormElement} form
     * @return {boolean}
     */
    function validateForm(form) {
        const testimonialText = form.querySelector('[name="testimonial_text"]');
        const authorName = form.querySelector('[name="author_name"]');
        const authorEmail = form.querySelector('[name="author_email"]');
        const rating = form.querySelector('[name="rating"]');
        const userId = form.querySelector('[name="user_id"]');
        const formMessages = form.querySelector('.testimonial-form-messages');
        const isLoggedIn = !!userId;

        let isValid = true;
        const errors = [];

        // Validate testimonial text
        if (!testimonialText || !testimonialText.value.trim()) {
            isValid = false;
            errors.push('Please enter your testimonial text.');
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
                errors.push('Please enter your name.');
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
                errors.push('Please enter your email address.');
                if (authorEmail) {
                    authorEmail.classList.add('is-invalid');
                }
            } else if (authorEmail && !isValidEmail(authorEmail.value)) {
                isValid = false;
                errors.push('Please enter a valid email address.');
                authorEmail.classList.add('is-invalid');
            } else {
                if (authorEmail) {
                    authorEmail.classList.remove('is-invalid');
                }
            }
        }

        // Validate rating
        if (!rating || !rating.value) {
            isValid = false;
            errors.push('Please select a rating.');
            if (rating) {
                rating.classList.add('is-invalid');
            }
        } else {
            if (rating) {
                rating.classList.remove('is-invalid');
            }
        }

        if (!isValid && formMessages) {
            showMessage(formMessages, errors.join('<br>'), 'error');
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
     * Show message to user
     * 
     * @param {HTMLElement} container
     * @param {string} message
     * @param {string} type
     */
    function showMessage(container, message, type) {
        if (!container) return;

        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        container.className = 'testimonial-form-messages alert ' + alertClass;
        container.innerHTML = '<p class="mb-0">' + message + '</p>';
        container.style.display = 'block';

        // Scroll to message
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Make function globally available
    window.initTestimonialForm = initTestimonialForm;
    
    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTestimonialForm);
    } else {
        initTestimonialForm();
    }

    // Reinitialize if form is added dynamically (e.g., modal opened)
    document.addEventListener('shown.bs.modal', function(e) {
        if (e.target.querySelector('#testimonial-form')) {
            // Small delay to ensure DOM is ready
            setTimeout(function() {
                initTestimonialForm();
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
                        setTimeout(initTestimonialForm, 100);
                    }
                }
            });
        });
        observer.observe(modalContent, { childList: true, subtree: true });
    }

})();

