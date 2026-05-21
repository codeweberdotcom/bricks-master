/**
 * CodeWeber Forms — Conditional Logic
 *
 * Evaluates data-cond-* attributes on field wrappers and shows/hides
 * them based on current values of sibling form fields.
 *
 * Attributes on the wrapper div:
 *   data-cond-action = "show" | "hide"
 *   data-cond-match  = "all"  | "any"
 *   data-cond-rules  = JSON array of { field, operator, value }
 *
 * Operators: is | is_not | contains | not_contains | is_empty | is_not_empty
 */
(function () {
	'use strict';

	/**
	 * Get current value(s) of a field by name within a form element.
	 * Returns an array to handle checkboxes/radios uniformly.
	 */
	function getFieldValues(form, fieldName) {
		// radio / checkbox
		const checkables = form.querySelectorAll(
			'[name="' +
				CSS.escape(fieldName) +
				'"], [name="' +
				CSS.escape(fieldName + '[]') +
				'"]'
		);
		if (checkables.length > 0) {
			const type = checkables[0].type;
			if (type === 'radio' || type === 'checkbox') {
				return Array.from(checkables)
					.filter((el) => el.checked)
					.map((el) => el.value);
			}
		}

		// select / text / textarea / hidden
		const el = form.querySelector('[name="' + CSS.escape(fieldName) + '"]');
		if (!el) return [];
		if (el.tagName === 'SELECT') {
			return Array.from(el.selectedOptions).map((o) => o.value);
		}
		return [el.value];
	}

	/**
	 * Test a single rule against current field values.
	 */
	function testRule(form, rule) {
		const values = getFieldValues(form, rule.field);
		const ruleValue = (rule.value || '').toLowerCase();

		switch (rule.operator) {
			case 'is':
				return values.some(
					(v) => v.toLowerCase() === ruleValue
				);
			case 'is_not':
				return values.every(
					(v) => v.toLowerCase() !== ruleValue
				);
			case 'contains':
				return values.some((v) =>
					v.toLowerCase().includes(ruleValue)
				);
			case 'not_contains':
				return values.every(
					(v) => !v.toLowerCase().includes(ruleValue)
				);
			case 'is_empty':
				return (
					values.length === 0 ||
					values.every((v) => v.trim() === '')
				);
			case 'is_not_empty':
				return (
					values.length > 0 &&
					values.some((v) => v.trim() !== '')
				);
			default:
				return true;
		}
	}

	/**
	 * Evaluate all conditional wrappers in a form and toggle visibility.
	 */
	function evaluateForm(form) {
		const wrappers = form.querySelectorAll('[data-cond-rules]');
		wrappers.forEach(function (wrapper) {
			let rules;
			try {
				rules = JSON.parse(wrapper.dataset.condRules);
			} catch (e) {
				return;
			}
			if (!Array.isArray(rules) || rules.length === 0) return;

			const action = wrapper.dataset.condAction || 'show';
			const match = wrapper.dataset.condMatch || 'all';

			// Filter out incomplete rules
			const validRules = rules.filter((r) => r.field);
			if (validRules.length === 0) return;

			const results = validRules.map((rule) => testRule(form, rule));
			const passed =
				match === 'any'
					? results.some(Boolean)
					: results.every(Boolean);

			if (action === 'show') {
				wrapper.style.display = passed ? '' : 'none';
			} else {
				wrapper.style.display = passed ? 'none' : '';
			}
		});
	}

	/**
	 * Initialize conditional logic for all forms on the page.
	 */
	function init() {
		document.querySelectorAll('form').forEach(function (form) {
			if (!form.querySelector('[data-cond-rules]')) return;

			// Initial evaluation
			evaluateForm(form);

			// Re-evaluate on any input/change within the form
			form.addEventListener('input', function () {
				evaluateForm(form);
			});
			form.addEventListener('change', function () {
				evaluateForm(form);
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
