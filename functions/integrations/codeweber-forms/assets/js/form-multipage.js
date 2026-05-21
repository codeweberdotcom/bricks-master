/**
 * Multi-page form navigation
 *
 * Handles step navigation, per-step validation and localStorage persistence
 * for forms using the cwgb-form-multipage class.
 *
 * @package Codeweber
 */

(function () {
    'use strict';

    var STORAGE_TTL = 24 * 60 * 60 * 1000; // 24 hours

    function storageKey(formId) {
        return 'cwf_mp_' + formId;
    }

    // ── Storage helpers ──────────────────────────────────────────────────────

    function saveState(formId, step, fields) {
        try {
            localStorage.setItem(
                storageKey(formId),
                JSON.stringify({ step: step, fields: fields, expires: Date.now() + STORAGE_TTL })
            );
        } catch (e) {}
    }

    function loadState(formId) {
        try {
            var raw = localStorage.getItem(storageKey(formId));
            if (!raw) return null;
            var data = JSON.parse(raw);
            if (!data || Date.now() > data.expires) {
                localStorage.removeItem(storageKey(formId));
                return null;
            }
            return data;
        } catch (e) {
            return null;
        }
    }

    function clearState(formId) {
        try {
            localStorage.removeItem(storageKey(formId));
        } catch (e) {}
    }

    // ── Field value helpers ──────────────────────────────────────────────────

    function collectFieldValues(form) {
        var values = {};
        form.querySelectorAll('[name]').forEach(function (el) {
            var name = el.getAttribute('name');
            // Skip hidden wp-specific fields and security tokens
            if (!name || name === 'form_nonce' || name === '_wpnonce' || name === 'cwf_token' || name === 'form_id' || name === 'form_honeypot') {
                return;
            }
            // Skip file inputs
            if (el.type === 'file') return;

            if (el.type === 'checkbox') {
                if (!values[name]) values[name] = [];
                if (el.checked) values[name].push(el.value);
            } else if (el.type === 'radio') {
                if (el.checked) values[name] = el.value;
            } else {
                values[name] = el.value;
            }
        });
        return values;
    }

    function restoreFieldValues(form, values) {
        if (!values) return;
        Object.keys(values).forEach(function (name) {
            var val = values[name];
            var els = form.querySelectorAll('[name="' + CSS.escape(name) + '"]');
            if (!els.length) return;

            var first = els[0];
            if (first.type === 'checkbox') {
                els.forEach(function (cb) {
                    cb.checked = Array.isArray(val) && val.indexOf(cb.value) !== -1;
                });
            } else if (first.type === 'radio') {
                els.forEach(function (rb) {
                    rb.checked = rb.value === val;
                });
            } else if (first.type !== 'file') {
                first.value = val;
            }
        });
    }

    // ── Validation helper ────────────────────────────────────────────────────

    function validateStep(step) {
        var valid = true;

        // Phone mask: same logic as form-submit-universal.js
        step.querySelectorAll('input[type="tel"][data-mask]').forEach(function (input) {
            var value = input.value || '';
            var hasRealDigits = value.replace(/\D/g, '').length > 0 && value.indexOf('_') === -1;
            if (input.required && !hasRealDigits) {
                input.setCustomValidity(
                    (typeof codeweberForms !== 'undefined' && codeweberForms.i18n && codeweberForms.i18n.phonePlaceholder)
                        ? codeweberForms.i18n.phonePlaceholder
                        : 'Введите номер телефона'
                );
            } else {
                input.setCustomValidity('');
            }
        });

        // Standard HTML5 validation for all fields inside this step
        step.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!el.checkValidity()) {
                el.reportValidity();
                valid = false;
            }
        });

        return valid;
    }

    // ── Progress indicator ───────────────────────────────────────────────────

    function updateProgress(form, currentStep, totalSteps, pageTitles) {
        var bar = form.querySelector('.cwgb-form-progress .progress-bar');
        if (bar) {
            var pct = Math.round((currentStep / totalSteps) * 100);
            bar.style.width = pct + '%';
            bar.setAttribute('aria-valuenow', pct);
        }

        var currentEl = form.querySelector('.cwgb-form-progress-current');
        if (currentEl) currentEl.textContent = currentStep;

        var titleEl = form.querySelector('.cwgb-form-progress-title');
        if (titleEl) {
            var title = (pageTitles && pageTitles[currentStep - 1]) ? pageTitles[currentStep - 1] : '';
            titleEl.textContent = title;
            titleEl.style.display = title ? '' : 'none';
        }

        var ariaLabel = form.querySelector('.cwgb-form-progress');
        if (ariaLabel) {
            ariaLabel.setAttribute(
                'aria-label',
                'Step ' + currentStep + ' of ' + totalSteps
            );
        }
    }

    // ── Show/hide step ───────────────────────────────────────────────────────

    function goToStep(form, targetStep, totalSteps, pageTitles) {
        form.querySelectorAll('.cwgb-form-step').forEach(function (stepEl) {
            var n = parseInt(stepEl.getAttribute('data-step'), 10);
            if (n === targetStep) {
                stepEl.style.display = '';
                stepEl.classList.add('cwgb-form-step--active');
            } else {
                stepEl.style.display = 'none';
                stepEl.classList.remove('cwgb-form-step--active');
            }
        });

        updateProgress(form, targetStep, totalSteps, pageTitles);

        // Scroll only if form is not already fully visible (account for fixed header)
        var rect = form.getBoundingClientRect();
        var headerEl = document.querySelector('.site-header') || document.querySelector('header');
        var headerH = headerEl ? headerEl.offsetHeight : 0;
        if (rect.top < headerH || rect.bottom > window.innerHeight) {
            var scrollTarget = window.scrollY + rect.top - headerH - 16;
            window.scrollTo({ top: Math.max(0, scrollTarget), behavior: 'smooth' });
        }
    }

    // ── Init ─────────────────────────────────────────────────────────────────

    function initForm(form) {
        var formId     = form.getAttribute('data-form-id') || form.id;
        var totalSteps = parseInt(form.getAttribute('data-total-steps'), 10) || 1;

        if (totalSteps <= 1) return;

        // Read page titles from embedded JSON
        var titlesEl  = form.querySelector('.cwgb-form-page-titles');
        var pageTitles = [];
        if (titlesEl) {
            try {
                pageTitles = JSON.parse(titlesEl.textContent || '[]');
            } catch (e) {}
        }

        var currentStep = 1;

        // ── Restore state from localStorage ────────────────────────────────
        var savedState = loadState(formId);
        if (savedState) {
            var savedStep = parseInt(savedState.step, 10) || 1;
            if (savedStep >= 1 && savedStep <= totalSteps) {
                currentStep = savedStep;
                restoreFieldValues(form, savedState.fields);
            }
        }

        goToStep(form, currentStep, totalSteps, pageTitles);

        // ── Next button ─────────────────────────────────────────────────────
        form.addEventListener('click', function (e) {
            var nextBtn = e.target.closest('.cwgb-form-next');
            if (!nextBtn || !form.contains(nextBtn)) return;

            var activeStep = form.querySelector('.cwgb-form-step--active');
            if (!activeStep) return;

            if (!validateStep(activeStep)) return;

            currentStep = Math.min(currentStep + 1, totalSteps);
            saveState(formId, currentStep, collectFieldValues(form));
            goToStep(form, currentStep, totalSteps, pageTitles);
        });

        // ── Back button ─────────────────────────────────────────────────────
        form.addEventListener('click', function (e) {
            var backBtn = e.target.closest('.cwgb-form-back');
            if (!backBtn || !form.contains(backBtn)) return;

            currentStep = Math.max(currentStep - 1, 1);
            saveState(formId, currentStep, collectFieldValues(form));
            goToStep(form, currentStep, totalSteps, pageTitles);
        });

        // ── Persist on any field change ─────────────────────────────────────
        form.addEventListener('change', function () {
            saveState(formId, currentStep, collectFieldValues(form));
        });

        // ── Clear state on successful submit ────────────────────────────────
        document.addEventListener('codeweberFormSubmitted', function (e) {
            if (e.detail && String(e.detail.formId) === String(formId)) {
                clearState(formId);
            }
        });
    }

    // ── Bootstrap ────────────────────────────────────────────────────────────

    function init() {
        document.querySelectorAll('.cwgb-form-multipage').forEach(initForm);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Support forms added dynamically (e.g. inside a modal)
    document.addEventListener('codeweberFormOpened', function () {
        document.querySelectorAll('.cwgb-form-multipage').forEach(function (form) {
            if (!form.dataset.cwgbMpInit) {
                form.dataset.cwgbMpInit = '1';
                initForm(form);
            }
        });
    });
})();
