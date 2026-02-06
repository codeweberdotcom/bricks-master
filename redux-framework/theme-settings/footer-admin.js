/**
 * Полное скрытие секций "Настройки столбцов нижнего колонтитуля" и "Нижняя панель"
 * когда выбран тип футера "Кастомный" (Custom, значение '2')
 */
(function($) {
	'use strict';

	function toggleFooterAccordions() {
		var $checked = $('input[data-id="global_footer_type"]:checked, input[name="redux_demo[global_footer_type]"]:checked');
		var val = $checked.length ? $checked.val() : $('input[name="redux_demo[global_footer_type]"]').val();
		var $acc1 = $('#footer-accordeon-offcanvas-right');
		var $acc2 = $('#footer_accordeon_topbar');
		if (val === '2') {
			$acc1.hide();
			$acc1.nextUntil('#footer_accordeon_topbar').hide();
			$acc2.hide();
			$acc2.nextUntil('.redux-accordion-field[data-position="end"]').hide();
		} else {
			$acc1.show();
			$acc1.nextUntil('#footer_accordeon_topbar').show();
			$acc2.show();
			$acc2.nextUntil('.redux-accordion-field[data-position="end"]').show();
		}
	}

	function init() {
		toggleFooterAccordions();
		$(document).on('click', '[data-id="global_footer_type"]', toggleFooterAccordions);
		$(document).on('change', 'input[data-id="global_footer_type"], input[name="redux_demo[global_footer_type]"]', toggleFooterAccordions);
		$(document).on('redux/options/redux_demo/change', toggleFooterAccordions);
		// Повторный вызов через 1с на случай динамической подгрузки Redux
		setTimeout(toggleFooterAccordions, 1000);
	}

	$(document).ready(init);
})(jQuery);
