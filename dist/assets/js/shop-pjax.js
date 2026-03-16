/**
 * Shop PJAX
 *
 * Перехватывает клики по ссылкам с классом .pjax-link на страницах магазина,
 * загружает контент через fetch с заголовком X-PJAX и заменяет
 * содержимое #shop-pjax-container без полной перезагрузки страницы.
 *
 * Расширяемость: любая ссылка с классом .pjax-link (фильтры, сортировка,
 * пагинация) будет автоматически работать через PJAX.
 */
(function () {
	'use strict';

	var CONTAINER_ID = 'shop-pjax-container';
	var LOADING_CLASS = 'shop-pjax-loading';
	var SPINNER_CLASS = 'shop-pjax-spinner';
	var PJAX_HEADER = 'X-PJAX';

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

		// Рассчитать центр видимой части контейнера
		var rect = container.getBoundingClientRect();
		var headerOffset = getHeaderOffset();
		var visibleTop = Math.max( headerOffset, rect.top );
		var visibleBottom = Math.min( window.innerHeight, rect.bottom );
		var centerY = ( visibleTop + visibleBottom ) / 2;
		var centerX = rect.left + rect.width / 2;

		el.style.top = centerY + 'px';
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
	 * Найти контейнер PJAX.
	 * @returns {HTMLElement|null}
	 */
	function getContainer() {
		return document.getElementById( CONTAINER_ID );
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
				container.innerHTML = html;
				history.pushState( { pjax: true, url: url }, '', url );
				container.classList.remove( LOADING_CLASS );
				hideSpinner( spinner );
				initIsotope( container );

				// Скроллить только если верх контейнера скрыт за хедером
				var headerOffset = getHeaderOffset();
				var containerTop = container.getBoundingClientRect().top;
				if ( containerTop < headerOffset ) {
					window.scrollTo( {
						top: window.pageYOffset + containerTop - headerOffset,
						behavior: 'smooth',
					} );
				}
			} )
			.catch( function () {
				hideSpinner( spinner );
				// Fallback: обычная навигация
				window.location.href = url;
			} );
	}

	/**
	 * Инициализировать Isotope в контейнере после PJAX-замены.
	 * @param {HTMLElement} container
	 */
	function initIsotope( container ) {
		var grid = container.querySelector( '.isotope' );
		if ( ! grid ) return;

		// Если Isotope не загружен — выходим (макет уже корректный через CSS)
		if ( typeof window.Isotope === 'undefined' ) return;

		var doLayout = function () {
			new window.Isotope( grid, {
				itemSelector: '.item',
				layoutMode: 'fitRows',
			} );
		};

		// Ждём загрузки изображений перед расчётом позиций
		if ( typeof window.imagesLoaded !== 'undefined' ) {
			window.imagesLoaded( grid, doLayout );
		} else {
			doLayout();
		}
	}

	/**
	 * Делегированный обработчик кликов — работает и после PJAX-замены контента.
	 */
	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest( '.pjax-link' );
		if ( ! link ) return;

		// Игнорируем внешние ссылки и ссылки с модификаторами
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
