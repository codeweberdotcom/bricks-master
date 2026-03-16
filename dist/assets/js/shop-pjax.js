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
	var PJAX_HEADER = 'X-PJAX';

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
				initIsotope( container );
				// Скролл к началу сетки товаров
				container.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			} )
			.catch( function () {
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
