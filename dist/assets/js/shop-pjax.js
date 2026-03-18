/**
 * Shop PJAX
 *
 * Перехватывает клики по ссылкам с классом .pjax-link на страницах магазина,
 * загружает контент через fetch с заголовком X-PJAX и заменяет содержимое
 * #shop-pjax-wrapper (page header + товары + сайдбар) без полной перезагрузки.
 *
 * document.title обновляется из атрибута data-page-title нового контента.
 *
 * После замены DOM диспатчит событие 'cw:pjax:complete' для реинициализации
 * модулей (price slider и др.).
 */
(function () {
	'use strict';

	var CONTAINER_ID   = 'shop-pjax-wrapper';
	var LOADING_CLASS  = 'shop-pjax-loading';
	var SPINNER_CLASS  = 'shop-pjax-spinner';

	/**
	 * Получить высоту sticky-хедера.
	 * @returns {number}
	 */
	function getHeaderOffset() {
		var stickyHeader = document.querySelector(
			'.navbar.fixed-top, .navbar.sticky-top, header.fixed-top, header.sticky-top'
		);
		return stickyHeader ? stickyHeader.offsetHeight : 0;
	}

	/**
	 * Показать spinner в центре видимой части контейнера.
	 * @param {HTMLElement} container
	 * @returns {HTMLElement}
	 */
	function showSpinner( container ) {
		var el = document.createElement( 'div' );
		el.className = SPINNER_CLASS;
		el.innerHTML = '<div class="spinner"></div>';

		var rect        = container.getBoundingClientRect();
		var headerOffset = getHeaderOffset();
		var visibleTop  = Math.max( headerOffset, rect.top );
		var visibleBottom = Math.min( window.innerHeight, rect.bottom );
		var centerY     = ( visibleTop + visibleBottom ) / 2;
		var centerX     = rect.left + rect.width / 2;

		el.style.top  = centerY + 'px';
		el.style.left = centerX + 'px';

		document.body.appendChild( el );
		return el;
	}

	/**
	 * Убрать spinner.
	 * @param {HTMLElement} el
	 */
	function hideSpinner( el ) {
		if ( el && el.parentNode ) {
			el.parentNode.removeChild( el );
		}
	}

	/**
	 * Найти PJAX-контейнер.
	 * @returns {HTMLElement|null}
	 */
	function getContainer() {
		return document.getElementById( CONTAINER_ID );
	}

	/**
	 * Обновить document.title из атрибута data-page-title нового контейнера.
	 * @param {HTMLElement} container
	 */
	function updateDocTitle( container ) {
		var title = container.getAttribute( 'data-page-title' );
		if ( title ) {
			document.title = title;
		}
	}

	/**
	 * Загрузить URL через PJAX и заменить контент контейнера.
	 * @param {string} url
	 */
	function pjaxLoad( url ) {
		var container = getContainer();
		if ( ! container ) {
			window.location.href = url;
			return;
		}

		container.classList.add( LOADING_CLASS );
		var spinner = showSpinner( container );

		fetch( url, {
			headers: {
				'X-PJAX': 'true',
				'X-Requested-With': 'XMLHttpRequest',
			},
			credentials: 'same-origin',
		} )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'Network response was not ok' );
				}
				return response.text();
			} )
			.then( function ( html ) {
				// Парсим ответ и извлекаем новый контейнер
				var tmp = document.createElement( 'div' );
				tmp.innerHTML = html;
				var newContainer = tmp.firstElementChild;

				// Заменяем содержимое
				container.innerHTML = newContainer ? newContainer.innerHTML : html;

				// Обновляем data-page-title и document.title
				if ( newContainer ) {
					var newTitle = newContainer.getAttribute( 'data-page-title' );
					if ( newTitle ) {
						container.setAttribute( 'data-page-title', newTitle );
						document.title = newTitle;
					}
				}

				history.pushState( { pjax: true, url: url }, document.title, url );
				container.classList.remove( LOADING_CLASS );
				hideSpinner( spinner );
				initIsotope( container );
				initPriceSliders();

				// Оповещаем другие модули о завершении PJAX-навигации
				document.dispatchEvent( new CustomEvent( 'cw:pjax:complete', { bubbles: true } ) );

				// Скролл к верху контейнера с отступом под sticky-хедер
				var containerTop = container.getBoundingClientRect().top + window.pageYOffset - getHeaderOffset() - 16;
				window.scrollTo( { top: containerTop, behavior: 'smooth' } );
			} )
			.catch( function () {
				hideSpinner( spinner );
				window.location.href = url;
			} );
	}

	/**
	 * Инициализировать Isotope в контейнере.
	 * Уничтожает существующий инстанс (masonry из theme.js), затем создаёт
	 * fitRows и дожидается imagesLoaded перед финальным layout.
	 * @param {HTMLElement} container
	 */
	function initIsotope( container ) {
		var grid = container.querySelector( '.isotope' );
		if ( ! grid ) return;

		if ( typeof window.Isotope === 'undefined' ) return;

		// Уничтожаем инстанс masonry, созданный theme.js при первой загрузке
		var existing = window.Isotope.data( grid );
		if ( existing ) {
			existing.destroy();
		}

		var doLayout = function () {
			new window.Isotope( grid, {
				itemSelector: '.item',
				layoutMode: 'fitRows',
			} );
		};

		if ( typeof window.imagesLoaded !== 'undefined' ) {
			window.imagesLoaded( grid, doLayout );
		} else {
			doLayout();
		}
	}

	// ==========================================================================
	// Price range slider (native dual <input type="range">)
	// ==========================================================================

	/**
	 * Инициализировать все ценовые слайдеры на странице.
	 * Безопасно вызывается повторно после PJAX-обновления.
	 */
	function initPriceSliders() {
		var panels = document.querySelectorAll( '.cw-filter-price' );
		panels.forEach( initSinglePriceSlider );
	}

	/**
	 * Инициализировать один ценовой слайдер.
	 * @param {HTMLElement} panel
	 */
	function initSinglePriceSlider( panel ) {
		var rangeMin = panel.querySelector( '.cw-range-min' );
		var rangeMax = panel.querySelector( '.cw-range-max' );
		var rangeBar = panel.querySelector( '.cw-price-range' );
		var inputMin = panel.querySelector( '.cw-price-input--min' );
		var inputMax = panel.querySelector( '.cw-price-input--max' );

		if ( ! rangeMin || ! rangeMax ) return;

		// Помечаем как инициализированный
		if ( panel.dataset.sliderInit ) return;
		panel.dataset.sliderInit = '1';

		var absMin = parseFloat( panel.dataset.min ) || 0;
		var absMax = parseFloat( panel.dataset.max ) || 100;

		/** Построить URL с текущими min/max ценами */
		function buildUrl( min, max ) {
			var params = new URLSearchParams( window.location.search );
			params.set( 'min_price', Math.round( min ) );
			params.set( 'max_price', Math.round( max ) );
			params.delete( 'paged' );
			params.delete( 'page' );
			return window.location.pathname + '?' + params.toString();
		}

		/** Обновить цветную полосу трека */
		function updateBar( min, max ) {
			if ( ! rangeBar ) return;
			var pctMin = ( ( min - absMin ) / ( absMax - absMin ) ) * 100;
			var pctMax = ( ( max - absMin ) / ( absMax - absMin ) ) * 100;
			rangeBar.style.left  = pctMin + '%';
			rangeBar.style.width = ( pctMax - pctMin ) + '%';
		}

		/** Слайдер двигается — обновить поля и полосу (без навигации) */
		function onSliderInput() {
			var min = parseFloat( rangeMin.value );
			var max = parseFloat( rangeMax.value );

			if ( min > max ) { rangeMin.value = max; min = max; }
			if ( max < min ) { rangeMax.value = min; max = min; }

			updateBar( min, max );

			if ( inputMin ) inputMin.value = Math.round( min );
			if ( inputMax ) inputMax.value = Math.round( max );
		}

		/** Слайдер отпущен — навигация */
		function onSliderChange() {
			var min = parseFloat( rangeMin.value );
			var max = parseFloat( rangeMax.value );
			pjaxLoad( buildUrl( min, max ) );
		}

		/** Поле ввода изменено — обновить слайдер и перейти */
		function onInputChange() {
			var min = Math.max( absMin, Math.min( absMax, parseFloat( inputMin ? inputMin.value : rangeMin.value ) || absMin ) );
			var max = Math.max( absMin, Math.min( absMax, parseFloat( inputMax ? inputMax.value : rangeMax.value ) || absMax ) );

			if ( min > max ) { min = max; }

			rangeMin.value = min;
			rangeMax.value = max;
			if ( inputMin ) inputMin.value = Math.round( min );
			if ( inputMax ) inputMax.value = Math.round( max );

			updateBar( min, max );
			pjaxLoad( buildUrl( min, max ) );
		}

		rangeMin.addEventListener( 'input',  onSliderInput );
		rangeMax.addEventListener( 'input',  onSliderInput );
		rangeMin.addEventListener( 'change', onSliderChange );
		rangeMax.addEventListener( 'change', onSliderChange );

		if ( inputMin ) {
			inputMin.addEventListener( 'change', onInputChange );
			inputMin.addEventListener( 'keydown', function( e ) { if ( e.key === 'Enter' ) { e.preventDefault(); onInputChange(); } } );
		}
		if ( inputMax ) {
			inputMax.addEventListener( 'change', onInputChange );
			inputMax.addEventListener( 'keydown', function( e ) { if ( e.key === 'Enter' ) { e.preventDefault(); onInputChange(); } } );
		}

		// Первичная расстановка полосы
		updateBar( parseFloat( rangeMin.value ), parseFloat( rangeMax.value ) );
	}

	/**
	 * Простое форматирование цены (используется только для live-отображения).
	 * Полное форматирование через wc_price() остаётся на сервере.
	 * @param {number} value
	 * @returns {string}
	 */
	function formatPrice( value ) {
		return Math.round( value ).toLocaleString( document.documentElement.lang || 'ru' );
	}

	// ==========================================================================
	// Init
	// ==========================================================================

	/**
	 * Инициализация при первой загрузке страницы.
	 * Ждём document.fonts.ready чтобы шрифты были готовы до расчёта высот,
	 * иначе двухстрочные заголовки ломают masonry-раскладку из theme.js.
	 */
	document.addEventListener( 'DOMContentLoaded', function () {
		var container = getContainer();
		if ( container ) {
			var run = function () { initIsotope( container ); };

			if ( document.fonts && document.fonts.ready ) {
				document.fonts.ready.then( run );
			} else {
				run();
			}

			initPriceSliders();
		}

		// Работает независимо от наличия PJAX-контейнера
		initFilterLimits();
	} );

	/**
	 * Перехват сортировки WooCommerce через PJAX.
	 */
	document.addEventListener( 'change', function ( e ) {
		var select = e.target.closest( 'select.orderby' );
		if ( ! select ) return;
		e.stopPropagation();
		var form = select.closest( 'form' );
		if ( ! form ) return;
		var url = new URL( form.action || window.location.href );
		url.search = new URLSearchParams( new FormData( form ) ).toString();
		pjaxLoad( url.toString() );
	}, true ); // capture phase

	/**
	 * Делегированный обработчик кликов — работает и после PJAX-замены контента.
	 */
	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( '.pjax-link' );
		if ( ! link ) return;

		if ( link.hostname !== window.location.hostname ) return;
		if ( e.ctrlKey || e.metaKey || e.shiftKey ) return;

		e.preventDefault();
		pjaxLoad( link.href );
	} );

	/**
	 * Обработка браузерных кнопок Back/Forward.
	 */
	window.addEventListener( 'popstate', function ( e ) {
		if ( e.state && e.state.pjax ) {
			pjaxLoad( window.location.href );
		}
	} );

	// ── Filter limit: show-more by count or height ─────────────────────────

	/**
	 * Инициализирует «Показать ещё» для фильтров с data-limit-type.
	 * Вызывается при загрузке страницы и после каждого PJAX-обновления.
	 */
	function initFilterLimits() {
		document.querySelectorAll( '.cw-filter-limit[data-limit-type]' ).forEach( function ( el ) {
			// Не инициализировать повторно
			if ( el.dataset.cwLimitInit ) return;
			el.dataset.cwLimitInit = '1';

			var type      = el.dataset.limitType;
			var limit     = parseInt( el.dataset.limit, 10 );
			var moreText  = el.dataset.showMore || 'Показать ещё';
			var lessText  = el.dataset.showLess || 'Свернуть';

			if ( isNaN( limit ) || limit <= 0 ) return;

			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'cw-filter-more-btn';

			if ( type === 'count' ) {
				// Убираем pre-render CSS (PHP добавил для скрытия без FOUC), JS берёт управление
				var preStyle = el.id ? document.getElementById( el.id + '-css' ) : null;
				if ( preStyle ) { preStyle.parentNode.removeChild( preStyle ); }

				var items = el.querySelectorAll( ':scope > ul > li, :scope > .d-flex > *' );
				var hideable = Array.prototype.slice.call( items, limit );

				if ( hideable.length === 0 ) return;

				hideable.forEach( function ( item ) { item.hidden = true; } );
				btn.textContent = moreText + ' (' + hideable.length + ')';

				btn.addEventListener( 'click', function () {
					var isOpen = ! hideable[ 0 ].hidden;
					hideable.forEach( function ( item ) { item.hidden = isOpen; } );
					btn.textContent = isOpen
						? moreText + ' (' + hideable.length + ')'
						: lessText;
				} );

				el.appendChild( btn );

			} else if ( type === 'height' ) {
				// Set initial max-height
				el.style.maxHeight = limit + 'px';

				// No overflow — no button needed
				if ( el.scrollHeight <= limit ) return;

				btn.textContent = moreText;

				btn.addEventListener( 'click', function () {
					var isOpen = el.classList.contains( 'is-open' );
					if ( isOpen ) {
						el.classList.remove( 'is-open' );
						el.style.maxHeight = limit + 'px';
						btn.textContent = moreText;
					} else {
						el.classList.add( 'is-open' );
						el.style.maxHeight = el.scrollHeight + 'px';
						btn.textContent = lessText;
					}
				} );

				// Кнопка — ПОСЛЕ контейнера (overflow:hidden обрезал бы её внутри)
				el.insertAdjacentElement( 'afterend', btn );
			}
		} );
	}

	document.addEventListener( 'cw:pjax:complete', function () {
		// Reset init flags so limits are re-applied to fresh DOM
		document.querySelectorAll( '.cw-filter-limit[data-cw-limit-init]' ).forEach( function ( el ) {
			delete el.dataset.cwLimitInit;
		} );
		initFilterLimits();
	} );

} )();
