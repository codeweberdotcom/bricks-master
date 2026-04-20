/**
 * Media Library: каскадный фильтр "Тип записи → Конкретная запись".
 *
 * Работает во всех media-фреймах (в т.ч. Gallery Create/Edit,
 * Featured Image, Customizer, Gutenberg MediaUpload).
 */
(function ($, wp) {
	'use strict';

	if (!wp || !wp.media || !wp.media.view || !wp.media.view.AttachmentFilters) {
		return;
	}

	var cfg = window.CW_MediaCptFilter || {};
	var L = cfg.i18n || {};
	var types = cfg.types || [];
	var tags = cfg.tags || [];
	var ajaxUrl = cfg.ajaxUrl || window.ajaxurl;
	var nonce = cfg.nonce || '';

	// Если ни CPT, ни тегов нет — делать нечего.
	if (!types.length && !tags.length) {
		return;
	}

	var postsCache = {};

	function fetchPostsForType(postType) {
		if (postsCache[postType]) {
			return $.Deferred().resolve(postsCache[postType]).promise();
		}
		return $.post(ajaxUrl, {
			action: 'cw_media_cpt_posts',
			nonce: nonce,
			post_type: postType,
		}).then(function (response) {
			if (response && response.success && response.data) {
				postsCache[postType] = response.data;
				return response.data;
			}
			return { items: [], truncated: false };
		});
	}

	/** Фильтр "Тип записи". */
	var CptTypeFilter = wp.media.view.AttachmentFilters.extend({
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

		change: function () {
			var filter = this.filters[this.el.value];
			if (filter) {
				this.model.set({
					parent_post_type: filter.props.parent_post_type,
					parent_post_id: '',
				});
			}
		},

		select: function () {
			var current = this.model.get('parent_post_type');
			var value = 'all';
			Object.keys(this.filters).forEach(function (id) {
				if (this.filters[id].props.parent_post_type === current) {
					value = id;
				}
			}, this);
			this.$el.val(value);
		},
	});

	/**
	 * Фильтр "Конкретная запись" — собственный <select>, без AttachmentFilters-базы,
	 * чтобы контролировать show/hide и ajax-populate без борьбы с базовым классом.
	 */
	var CptPostFilter = wp.media.View.extend({
		tagName: 'select',
		className: 'attachment-filters cw-cpt-post-filter',
		attributes: { id: 'media-attachment-cpt-post-filter' },

		events: { change: 'onChange' },

		initialize: function (options) {
			this.controller = options.controller;
			this.listenTo(this.model, 'change:parent_post_type', this.onTypeChange);
		},

		render: function () {
			this.onTypeChange();
			return this;
		},

		setVisible: function (visible) {
			this.$el.toggle(!!visible);
			if (this.labelView && this.labelView.$el) {
				this.labelView.$el.toggle(!!visible);
			}
		},

		onTypeChange: function () {
			var postType = this.model.get('parent_post_type');
			var self = this;

			if (!postType) {
				this.setVisible(false);
				this.model.set('parent_post_id', '');
				return;
			}

			this.setVisible(true);

			this.$el.empty().append(
				$('<option>').val('').text(L.loading || 'Loading…')
			);
			this.$el.prop('disabled', true);

			fetchPostsForType(postType).always(function (data) {
				self.$el.prop('disabled', false);
				self.populate(data || { items: [], truncated: false });
			});
		},

		populate: function (data) {
			var items = (data && data.items) || [];
			var truncated = !!(data && data.truncated);

			this.$el.empty();
			$('<option>').val('').text(L.allPosts || 'All posts').appendTo(this.$el);

			if (items.length === 0) {
				$('<option disabled>').val('').text(L.noPosts || 'No posts found').appendTo(this.$el);
				this.$el.prop('disabled', true);
				this.model.set('parent_post_id', '');
				return;
			}

			items.forEach(function (item) {
				$('<option>').val(String(item.id)).text(item.title).appendTo(this.$el);
			}.bind(this));

			if (truncated) {
				$('<option disabled>').val('').text('— ' + (L.truncated || 'Showing latest 200') + ' —').appendTo(this.$el);
			}

			var current = String(this.model.get('parent_post_id') || '');
			this.$el.val(current || '');
		},

		onChange: function () {
			this.model.set('parent_post_id', this.el.value);
		},
	});

	/** Фильтр "Тег изображения" — независим от CPT. */
	var CptTagFilter = wp.media.view.AttachmentFilters.extend({
		id: 'media-attachment-image-tag-filter',
		className: 'attachment-filters',

		createFilters: function () {
			var filters = {
				all: {
					text: L.allTags || 'All image tags',
					props: { image_tag: '' },
					priority: 10,
				},
			};
			var priority = 20;
			tags.forEach(function (t) {
				filters['tag-' + t.slug] = {
					text: t.name,
					props: { image_tag: t.slug },
					priority: priority,
				};
				priority += 10;
			});
			this.filters = filters;
		},

		change: function () {
			var filter = this.filters[this.el.value];
			if (filter) {
				this.model.set('image_tag', filter.props.image_tag);
			}
		},

		select: function () {
			var current = this.model.get('image_tag');
			var value = 'all';
			Object.keys(this.filters).forEach(function (id) {
				if (this.filters[id].props.image_tag === current) {
					value = id;
				}
			}, this);
			this.$el.val(value);
		},
	});

	/**
	 * Monkey-patch прототипа AttachmentsBrowser.createToolbar.
	 * Это важно: подкласс MediaFrame.Post (Create Gallery, Featured Image)
	 * может уже держать reference на конструктор AttachmentsBrowser до того,
	 * как наш JS загрузится, поэтому `extend` базы не всегда применяется.
	 * Patch прототипа действует на все существующие и будущие инстансы.
	 */
	var originalCreateToolbar = wp.media.view.AttachmentsBrowser.prototype.createToolbar;

	wp.media.view.AttachmentsBrowser.prototype.createToolbar = function () {
		originalCreateToolbar.apply(this, arguments);

		if (!this.toolbar) {
			return;
		}
		if (this.toolbar.get('cptTypeFilter') || this.toolbar.get('cptTagFilter')) {
			return;
		}

		// 0) Label + select для тега изображения (независимый фильтр).
		if (tags.length) {
			this.toolbar.set(
				'cptTagFilterLabel',
				new wp.media.View({
					el: $('<label>')
						.addClass('screen-reader-text')
						.attr('for', 'media-attachment-image-tag-filter')
						.text(L.filterTag || 'Filter by image tag')[0],
					priority: -76,
				}).render()
			);
			this.toolbar.set(
				'cptTagFilter',
				new CptTagFilter({
					controller: this.controller,
					model: this.collection.props,
					priority: -77,
				}).render()
			);
		}

		// Если CPT нет — дальше ничего не вставляем.
		if (!types.length) {
			return;
		}

		// CPT/Post фильтры вставляем ТОЛЬКО в классической Медиатеке
		// (/wp-admin/upload.php). В gallery-фрейме и других media-модалках
		// (Featured Image, MediaUpload в редакторе, Customizer) — только тег:
		// их Backbone-цикл чувствителен к доп. subviews и ломал WP 6.9.4.
		var isLibraryPage = /\/wp-admin\/upload\.php/i.test(
			(window.location && window.location.pathname) || ''
		);
		if (!isLibraryPage) {
			return;
		}

		// 1) Label + select для типа записи.
		this.toolbar.set(
			'cptTypeFilterLabel',
			new wp.media.View({
				el: $('<label>')
					.addClass('screen-reader-text')
					.attr('for', 'media-attachment-cpt-filter')
					.text(L.filter || 'Filter by post type')[0],
				priority: -74,
			}).render()
		);

		this.toolbar.set(
			'cptTypeFilter',
			new CptTypeFilter({
				controller: this.controller,
				model: this.collection.props,
				priority: -75,
			}).render()
		);

		// 2) Label + select для конкретной записи (show/hide через CptPostFilter).
		var postLabel = new wp.media.View({
			el: $('<label>')
				.addClass('screen-reader-text')
				.attr('for', 'media-attachment-cpt-post-filter')
				.text(L.filterPost || 'Filter by post')[0],
			priority: -73,
		}).render();

		var postFilter = new CptPostFilter({
			controller: this.controller,
			model: this.collection.props,
			priority: -72,
		});
		postFilter.labelView = postLabel;
		postFilter.render();

		this.toolbar.set('cptPostFilterLabel', postLabel);
		this.toolbar.set('cptPostFilter', postFilter);
	};
})(jQuery, window.wp);
