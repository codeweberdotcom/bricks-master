/**
 * Shop PJAX
 *
 * Перехватывает клики по ссылкам с классом .pjax-link на страницах магазина,
 * загружает контент через fetch с заголовком X-PJAX и заменяет содержимое
 * #shop-pjax-wrapper (page header + товары + сайдбар) без полной перезагрузки.
 *
 * document.title обновляется из атрибута data-page-title нового контента.
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

	/**
	 * Инициализация при первой загрузке страницы.
	 * Ждём document.fonts.ready чтобы шрифты были готовы до расчёта высот,
	 * иначе двухстрочные заголовки ломают masonry-раскладку из theme.js.
	 */
	document.addEventListener( 'DOMContentLoaded', function () {
		var container = getContainer();
		if ( ! container ) return;

		var run = function () { initIsotope( container ); };

		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( run );
		} else {
			run();
		}
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
} )();
