/**
 * Media Library: фильтр по типу родительской записи (CPT).
 *
 * Добавляет в toolbar медиатеки дополнительный dropdown "Тип записи".
 * При выборе опции шлёт в query-attachments параметр parent_post_type,
 * серверный PHP-фильтр (media-cpt-filter.php) применяет post_parent__in.
 */
(function ($, wp) {
	'use strict';

	if (!wp || !wp.media || !wp.media.view || !wp.media.view.AttachmentFilters) {
		return;
	}

	var L = (window.CW_MediaCptFilter && window.CW_MediaCptFilter.i18n) || {};
	var types = (window.CW_MediaCptFilter && window.CW_MediaCptFilter.types) || [];

	// Если CPT не переданы — ничего не делаем.
	if (!types.length) {
		return;
	}

	/**
	 * Фильтр-view для выпадающего списка "Тип записи".
	 */
	var CptFilter = wp.media.view.AttachmentFilters.extend({
		id: 'media-attachment-cpt-filter',
		className: 'attachment-filters',

		createFilters: function () {
			var filters = {
				all: {
					text: L.all || 'All post types',
					props: { parent_post_type: '' },
					priority: 10,
				},
			};

			var priority = 20;
			types.forEach(function (t) {
				filters[t.slug] = {
					text: t.label,
					props: { parent_post_type: t.slug },
					priority: priority,
				};
				priority += 10;
			});

			this.filters = filters;
		},

		/**
		 * Переопределяем change — при смене значения пишем в library.props,
		 * чтобы query ушёл с нужным параметром.
		 */
		change: function () {
			var filter = this.filters[this.el.value];
			if (filter) {
				this.model.set('parent_post_type', filter.props.parent_post_type);
			}
		},

		select: function () {
			var model = this.model,
				value = 'all',
				current = model.get('parent_post_type');

			Object.keys(this.filters).forEach(function (id) {
				var filter = this.filters[id];
				if (filter.props.parent_post_type === current) {
					value = id;
				}
			}, this);

			this.$el.val(value);
		},
	});

	/**
	 * Расширяем AttachmentsBrowser — вставляем наш фильтр в toolbar
	 * после стандартных фильтров типа/даты.
	 */
	var OriginalBrowser = wp.media.view.AttachmentsBrowser;

	wp.media.view.AttachmentsBrowser = OriginalBrowser.extend({
		createToolbar: function () {
			OriginalBrowser.prototype.createToolbar.apply(this, arguments);

			// Вставляем только если в toolbar уже есть стандартные фильтры
			// (иначе это не media-library, а например featured-image frame с ограниченной toolbar).
			if (!this.toolbar || !this.toolbar.get('filters')) {
				return;
			}

			// Уже добавили — не дублируем.
			if (this.toolbar.get('cptFilter')) {
				return;
			}

			var label = $('<label>')
				.addClass('screen-reader-text')
				.attr('for', 'media-attachment-cpt-filter')
				.text(L.filter || 'Filter by post type');

			this.toolbar.set(
				'cptFilterLabel',
				new wp.media.View({
					el: label[0],
					priority: -74,
				}).render()
			);

			this.toolbar.set(
				'cptFilter',
				new CptFilter({
					controller: this.controller,
					model: this.collection.props,
					priority: -75,
				}).render()
			);
		},
	});
})(jQuery, window.wp);
