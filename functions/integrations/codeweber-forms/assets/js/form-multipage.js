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
            var escaped = CSS.escape(name);

            // Radio — explicit type selector to avoid false-positive on first element
            var radios = form.querySelectorAll('input[type="radio"][name="' + escaped + '"]');
            if (radios.length > 0) {
                radios.forEach(function (rb) { rb.checked = rb.value === val; });
                return;
            }

            // Checkbox — explicit type selector (name may include [] suffix)
            var checkboxes = form.querySelectorAll('input[type="checkbox"][name="' + escaped + '"]');
            if (checkboxes.length > 0) {
                checkboxes.forEach(function (cb) {
                    cb.checked = Array.isArray(val) && val.indexOf(cb.value) !== -1;
                });
                return;
            }

            // Other inputs (text, email, select, textarea…) — skip file
            var el = form.querySelector('[name="' + escaped + '"]:not([type="file"])');
            if (el) el.value = val;
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

    function updateProgress(form, currentStep, totalSteps, pageTitles, visibleSteps) {
        var dispCurrent = visibleSteps ? (visibleSteps.indexOf(currentStep) + 1) : currentStep;
        var dispTotal   = visibleSteps ? visibleSteps.length : totalSteps;

        var bar = form.querySelector('.cwgb-form-progress .progress-bar');
        if (bar) {
            var pct = Math.round((dispCurrent / dispTotal) * 100);
            bar.style.width = pct + '%';
            bar.setAttribute('aria-valuenow', pct);
        }

        var currentEl = form.querySelector('.cwgb-form-progress-current');
        if (currentEl) currentEl.textContent = dispCurrent;

        var totalEl = form.querySelector('.cwgb-form-progress-total');
        if (totalEl) totalEl.textContent = dispTotal;

        var titleEl = form.querySelector('.cwgb-form-progress-title');
        if (titleEl) {
            var title = (pageTitles && pageTitles[currentStep - 1]) ? pageTitles[currentStep - 1] : '';
            titleEl.textContent = title;
            var titleWrap = titleEl.parentElement !== form.querySelector('.cwgb-form-progress-text') ? titleEl.parentElement : titleEl;
            titleWrap.style.display = title ? '' : 'none';
        }

        var ariaLabel = form.querySelector('.cwgb-form-progress');
        if (ariaLabel) {
            ariaLabel.setAttribute(
                'aria-label',
                'Step ' + currentStep + ' of ' + totalSteps
            );
        }
    }

    // ── Page conditional logic helpers ───────────────────────────────────────

    function getPageFieldValues(form, fieldName) {
        var escaped = CSS.escape(fieldName);
        var checkboxes = form.querySelectorAll(
            'input[type="checkbox"][name="' + escaped + '[]"], input[type="checkbox"][name="' + escaped + '"]'
        );
        if (checkboxes.length > 0) {
            var vals = [];
            checkboxes.forEach(function (cb) { if (cb.checked) vals.push(cb.value); });
            return vals;
        }
        var radios = form.querySelectorAll('input[type="radio"][name="' + escaped + '"]');
        if (radios.length > 0) {
            var checked = null;
            radios.forEach(function (r) { if (r.checked) checked = r.value; });
            return checked !== null ? [checked] : [];
        }
        var el = form.querySelector('[name="' + escaped + '"]');
        return el ? [el.value] : [];
    }

    function testPageRule(form, rule) {
        var values = getPageFieldValues(form, rule.field || '');
        var val = (rule.value || '').toLowerCase();
        switch (rule.operator) {
            case 'is':          return values.some(function (v) { return v.toLowerCase() === val; });
            case 'is_not':      return !values.some(function (v) { return v.toLowerCase() === val; });
            case 'contains':    return values.some(function (v) { return v.toLowerCase().indexOf(val) !== -1; });
            case 'not_contains':return !values.some(function (v) { return v.toLowerCase().indexOf(val) !== -1; });
            case 'is_empty':    return values.length === 0 || values.every(function (v) { return v === ''; });
            case 'is_not_empty':return values.some(function (v) { return v !== ''; });
            default:            return false;
        }
    }

    function shouldSkipPage(form, stepEl) {
        var rulesJson = stepEl.dataset.pageCondRules;
        if (!rulesJson) return false;
        var action = stepEl.dataset.pageCondAction || 'show';
        var match  = stepEl.dataset.pageCondMatch  || 'all';
        var rules;
        try { rules = JSON.parse(rulesJson); } catch (e) { return false; }
        if (!rules || !rules.length) return false;
        var results = rules.map(function (rule) { return testPageRule(form, rule); });
        var conditionMet = match === 'all' ? results.every(Boolean) : results.some(Boolean);
        // 'show': display page only when condition met → skip if NOT met
        // 'skip': skip page when condition met
        return action === 'show' ? !conditionMet : conditionMet;
    }

    function getVisibleSteps(form, totalSteps) {
        var visible = [];
        for (var i = 1; i <= totalSteps; i++) {
            var stepEl = form.querySelector('.cwgb-form-step[data-step="' + i + '"]');
            if (stepEl && !shouldSkipPage(form, stepEl)) visible.push(i);
        }
        return visible.length > 0 ? visible : [1];
    }

    function findNextStep(form, from, direction, totalSteps) {
        var step = from + direction;
        while (step >= 1 && step <= totalSteps) {
            var stepEl = form.querySelector('.cwgb-form-step[data-step="' + step + '"]');
            if (stepEl && !shouldSkipPage(form, stepEl)) return step;
            step += direction;
        }
        return from;
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

        var visibleSteps = getVisibleSteps(form, totalSteps);
        updateProgress(form, targetStep, totalSteps, pageTitles, visibleSteps);

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

            currentStep = findNextStep(form, currentStep, +1, totalSteps);
            saveState(formId, currentStep, collectFieldValues(form));
            goToStep(form, currentStep, totalSteps, pageTitles);
        });

        // ── Back button ─────────────────────────────────────────────────────
        form.addEventListener('click', function (e) {
            var backBtn = e.target.closest('.cwgb-form-back');
            if (!backBtn || !form.contains(backBtn)) return;

            currentStep = findNextStep(form, currentStep, -1, totalSteps);
            saveState(formId, currentStep, collectFieldValues(form));
            goToStep(form, currentStep, totalSteps, pageTitles);
        });

        // ── Persist on any field change ─────────────────────────────────────
        form.addEventListener('change', function () {
            saveState(formId, currentStep, collectFieldValues(form));
            var visibleSteps = getVisibleSteps(form, totalSteps);
            updateProgress(form, currentStep, totalSteps, pageTitles, visibleSteps);
        });

        // ── Clear state on successful submit ────────────────────────────────
        document.addEventListener('codeweberFormSubmitted', function (e) {
            if (e.detail && String(e.detail.formId) === String(formId)) {
                clearState(formId);
                currentStep = 1;
                form.reset();
                goToStep(form, 1, totalSteps, pageTitles);
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
