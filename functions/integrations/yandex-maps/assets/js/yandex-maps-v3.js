/**
 * CodeWeber Yandex Maps v3 JavaScript
 *
 * @package Codeweber
 * @version 1.0.0
 */

(function () {
	'use strict';

	var COLOR_PRESETS = {
		light:     { theme: 'light' },
		dark:      { theme: 'dark' },
		grayscale: { customization: [ { stylers: [ { saturation: -1 } ] } ] },
		pale:      { customization: [ { stylers: [ { saturation: -0.5 }, { lightness: 0.3 } ] } ] },
		sepia: {
			customization: [
				{ tags: { any: [ 'water' ] },    stylers: [ { color: '#c9a87a' } ] },
				{ tags: { any: [ 'landscape' ] }, stylers: [ { color: '#e8d5b0' } ] },
				{ tags: { any: [ 'road' ] },      stylers: [ { color: '#d4b896' } ] },
				{ tags: { any: [ 'building' ] },  stylers: [ { color: '#c8b090' } ] },
			],
		},
	};

	class CodeweberYandexMapsV3 {
		constructor( config, wrapper ) {
			this.config          = config;
			this.wrapper         = wrapper;
			this.container       = wrapper.querySelector( '#' + CSS.escape( config.id ) );
			this.map             = null;
			this.markerEls       = {}; // markerId → { el, marker, data }
			this.sidebar         = null;
			this.activeMarkerId  = null;
		}

		async init() {
			if ( typeof ymaps3 === 'undefined' ) {
				console.error( '[yandex-maps-v3] ymaps3 not loaded' );
				return;
			}
			await ymaps3.ready;
			try {
				this.createMap();
			} catch ( err ) {
				console.error( '[yandex-maps-v3]', err );
			}
			this.hideLoader();
		}

		// ─── Map ──────────────────────────────────────────────────────────────

		createMap() {
			const {
				YMap,
				YMapDefaultSchemeLayer,
				YMapDefaultFeaturesLayer,
				YMapDefaultSatelliteLayer,
				YMapControls,
			} = ymaps3;

			const markers = this.config.markers || [];

			let center = this.config.center; // [lng, lat]
			let zoom   = this.config.zoom;

			if ( this.config.autoFitBounds && markers.length > 0 ) {
				center = this.calcBoundsCenter( markers );
				const fz = this.calcBoundsZoom( markers, this.container.offsetWidth, this.container.offsetHeight );
				if ( fz !== null ) zoom = fz;
			}

			this.map = new YMap( this.container, {
				location:  { center, zoom },
				behaviors: this.buildBehaviors(),
			} );

			// Map layer / scheme
			const mapType = this.config.mapType || 'normal';
			if ( mapType === 'satellite' ) {
				this.map.addChild( new YMapDefaultSatelliteLayer() );
			} else if ( mapType === 'hybrid' ) {
				this.map.addChild( new YMapDefaultSatelliteLayer() );
				this.map.addChild( new YMapDefaultSchemeLayer( { theme: 'light' } ) );
			} else {
				this.map.addChild( new YMapDefaultSchemeLayer( this.buildSchemeOptions() ) );
			}

			this.map.addChild( new YMapDefaultFeaturesLayer() );

			// Optional zoom control
			this.addZoomControl();

			// Markers
			this.addMarkers();

			// Sidebar
			if ( this.config.sidebar && this.config.sidebar.show ) {
				this.initSidebar();
				this.initFilters();
			}

			// Close balloon on map background click
			this.container.addEventListener( 'click', ( e ) => {
				if ( ! e.target.closest( '[data-marker-id]' ) && ! e.target.closest( '.cwgb-balloon-v3' ) ) {
					this.closeBalloon();
				}
			} );

			this.wrapper._cwgbYandexMapInstance = this;
		}

		buildBehaviors() {
			const behaviors = [ 'drag', 'pinchZoom', 'dblClick', 'mouseRotate', 'mouseTilt' ];
			if ( this.config.enableScrollZoom ) behaviors.push( 'scrollZoom' );
			if ( ! this.config.enableDrag ) return behaviors.filter( b => b !== 'drag' );
			return behaviors;
		}

		buildSchemeOptions() {
			const scheme = this.config.colorScheme || 'light';
			if ( scheme === 'custom' && this.config.colorSchemeCustom ) {
				try {
					return { customization: JSON.parse( this.config.colorSchemeCustom ) };
				} catch ( e ) {}
			}
			return COLOR_PRESETS[ scheme ] || { theme: 'light' };
		}

		async addZoomControl() {
			try {
				const { YMapControls } = ymaps3;
				const pkg = await ymaps3.import( '@yandex/ymaps3-controls@0.0.1' );
				if ( pkg && pkg.YMapZoomControl && YMapControls ) {
					const controls = new YMapControls( { position: 'right' } );
					controls.addChild( new pkg.YMapZoomControl() );
					this.map.addChild( controls );
				}
			} catch ( e ) {
				// zoom control optional — silently ignore
			}
		}

		// ─── Markers ──────────────────────────────────────────────────────────

		addMarkers() {
			const { YMapMarker } = ymaps3;
			const markers = this.config.markers || [];
			const color   = ( this.config.markerSettings && this.config.markerSettings.color ) ? this.config.markerSettings.color : '#FF0000';

			markers.forEach( ( markerData ) => {
				const el = document.createElement( 'div' );
				el.dataset.markerId = markerData.id;
				el.style.cssText = [
					'width:14px', 'height:14px',
					'background:' + color,
					'border:2px solid #fff',
					'border-radius:50%',
					'cursor:pointer',
					'box-shadow:0 1px 3px rgba(0,0,0,.4)',
					'position:relative',
				].join( ';' );

				el.addEventListener( 'click', ( e ) => {
					e.stopPropagation();
					this.onMarkerClick( markerData, el );
				} );

				const marker = new YMapMarker(
					{ coordinates: [ markerData.longitude, markerData.latitude ] },
					el
				);
				this.map.addChild( marker );
				this.markerEls[ markerData.id ] = { el, marker, data: markerData };
			} );
		}

		onMarkerClick( markerData, el ) {
			if ( this.activeMarkerId === String( markerData.id ) ) {
				this.closeBalloon();
				return;
			}
			this.closeBalloon();

			const balloon = this.buildBalloon( markerData );
			el.appendChild( balloon );
			this.activeMarkerId = String( markerData.id );

			this.highlightSidebarItem( markerData.id );
			const zoom   = this.config.markerClickZoom || this.config.zoom || 15;
			const center = this.calcCenterWithBalloonOffset( markerData.longitude, markerData.latitude, zoom );
			this.map.update( { location: { center, zoom, duration: 400 } } );
		}

		getCurrentZoom() {
			try {
				if ( this.map && this.map.zoom != null ) return this.map.zoom;
			} catch ( e ) {}
			return this.config.zoom || 10;
		}

		calcCenterWithBalloonOffset( lng, lat, zoom ) {
			const containerH  = this.container.offsetHeight || 500;
			// Position marker at 65% from top so balloon (above marker) fits within the map.
			// pixelOffset > 0 → center shifts north → marker appears lower in viewport.
			const pixelOffset = containerH * 0.65 - containerH * 0.5; // = containerH * 0.15
			const z           = zoom != null ? zoom : ( this.config.zoom || 10 );
			const degPerPx    = 360 / ( 256 * Math.pow( 2, z ) );
			return [ lng, lat + pixelOffset * degPerPx ];
		}

		closeBalloon() {
			if ( ! this.activeMarkerId ) return;
			const entry = this.markerEls[ this.activeMarkerId ];
			if ( entry ) {
				const b = entry.el.querySelector( '.cwgb-balloon-v3' );
				if ( b ) b.remove();
			}
			this.activeMarkerId = null;
			this.wrapper.querySelectorAll( '.codeweber-map-sidebar-item.active' )
				.forEach( i => i.classList.remove( 'active' ) );
		}

		// ─── Balloon ──────────────────────────────────────────────────────────

		buildBalloon( markerData ) {
			const balloonCfg = this.config.balloon || {};
			const fields     = balloonCfg.fields || {
				showCity: true, showAddress: true, showPhone: true,
				showWorkingHours: true, showLink: true, showDescription: false,
			};
			const maxWidth = balloonCfg.maxWidth || 380;
			const i18n     = ( typeof codeweberYandexMaps !== 'undefined' && codeweberYandexMaps.i18n ) ? codeweberYandexMaps.i18n : {};

			let body = '';
			if ( fields.showCity && markerData.city ) {
				body += `<div class="mb-1"><small class="text-muted">${ i18n.city || 'City' }:</small><br>${ markerData.city }</div>`;
			}
			if ( fields.showAddress && markerData.address ) {
				body += `<div class="mb-1"><small class="text-muted">${ i18n.address || 'Address' }:</small><br>${ markerData.address }</div>`;
			}
			if ( fields.showPhone && markerData.phone ) {
				const tel = markerData.phone.replace( /[^0-9+]/g, '' );
				body += `<div class="mb-1"><small class="text-muted">${ i18n.phone || 'Phone' }:</small><br><a href="tel:${ tel }">${ markerData.phone }</a></div>`;
			}
			if ( fields.showWorkingHours && markerData.workingHours ) {
				body += `<div class="mb-1"><small class="text-muted">${ i18n.workingHours || 'Working Hours' }:</small><br>${ markerData.workingHours }</div>`;
			}
			if ( fields.showDescription && markerData.description ) {
				body += `<div class="mb-2">${ markerData.description }</div>`;
			}
			if ( fields.showLink && markerData.link ) {
				body += `<div class="mt-2"><a href="${ markerData.link }" class="btn btn-primary btn-xs">${ i18n.viewDetails || 'View Details' }</a></div>`;
			}

			const div = document.createElement( 'div' );
			div.className = 'cwgb-balloon-v3';
			div.style.cssText = `position:absolute;bottom:22px;left:7px;transform:translateX(-50%);background:#fff;color:#333;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.18);padding:12px 16px;min-width:280px;max-width:${ maxWidth }px;z-index:100;white-space:normal;`;
			div.innerHTML = `
				<button class="cwgb-balloon-close" style="position:absolute;top:6px;right:8px;background:none;border:none;font-size:18px;cursor:pointer;color:#999;line-height:1;padding:0;" aria-label="Close">&times;</button>
				${ markerData.title ? `<div style="font-weight:600;margin-bottom:8px;padding-right:20px;font-size:14px;">${ markerData.title }</div>` : '' }
				<div style="font-size:13px;">${ body }</div>
			`;

			div.querySelector( '.cwgb-balloon-close' ).addEventListener( 'click', ( e ) => {
				e.stopPropagation();
				this.closeBalloon();
			} );

			return div;
		}

		// ─── Sidebar ──────────────────────────────────────────────────────────

		initSidebar() {
			if ( ! this.container ) return;
			const sidebarCfg = this.config.sidebar;
			const i18n       = ( typeof codeweberYandexMaps !== 'undefined' && codeweberYandexMaps.i18n ) ? codeweberYandexMaps.i18n : {};

			const sidebar = document.createElement( 'div' );
			sidebar.className = `codeweber-map-sidebar codeweber-map-sidebar-${ sidebarCfg.position } bg-white text-dark rounded shadow overflow-auto d-none d-md-block`;

			if ( sidebarCfg.title ) {
				const title = document.createElement( 'div' );
				title.className = 'codeweber-map-sidebar-title d-flex justify-content-between align-items-center border-bottom p-3 sticky-top bg-white text-reset';
				title.textContent = sidebarCfg.title;
				sidebar.appendChild( title );
			}

			const list = document.createElement( 'div' );
			list.className = 'codeweber-map-sidebar-list';
			list.id = `${ this.config.id }-sidebar-list`;

			( this.config.markers || [] ).forEach( ( marker ) => {
				list.appendChild( this.createSidebarItem( marker ) );
			} );

			sidebar.appendChild( list );

			// Mobile toggle
			const toggleBtn = document.createElement( 'button' );
			toggleBtn.className = 'codeweber-map-sidebar-toggle btn-icon btn-icon-start btn btn-sm btn-primary d-md-none';
			toggleBtn.innerHTML = `<i class="uil uil-list-ul"></i> ${ sidebarCfg.title || i18n.offices || 'Offices' }`;
			toggleBtn.addEventListener( 'click', () => sidebar.classList.toggle( 'd-none' ) );

			sidebar.addEventListener( 'click', ( e ) => {
				if ( window.innerWidth >= 768 ) return;
				if ( e.target.closest( '.codeweber-map-sidebar-item' ) ) return;
				if ( e.target.closest( 'select, label, button' ) ) return;
				sidebar.classList.add( 'd-none' );
			} );

			this.container.parentElement.appendChild( sidebar );
			this.container.parentElement.appendChild( toggleBtn );
			this.sidebar = sidebar;
		}

		createSidebarItem( marker ) {
			const sidebarCfg = this.config.sidebar || {};
			const fields     = sidebarCfg.fields || {
				showCity: true, showAddress: false, showPhone: false,
				showWorkingHours: true, showDescription: true,
			};

			const item = document.createElement( 'div' );
			item.className = 'codeweber-map-sidebar-item border-bottom p-3';
			item.dataset.markerId = marker.id;
			item.dataset.city     = marker.city || '';
			item.dataset.category = marker.category || '';

			let html = '';
			if ( marker.title ) html += `<div class="h6 mb-1 text-reset">${ marker.title }</div>`;
			if ( fields.showDescription && marker.description ) html += `<p class="fs-sm mb-0 text-reset">${ marker.description }</p>`;
			if ( fields.showCity && marker.city ) html += `<p class="fs-sm mb-0 text-reset"><i class="uil uil-location-pin-alt me-1"></i>${ marker.city }</p>`;
			if ( fields.showAddress && marker.address ) html += `<p class="fs-sm mb-0 text-reset"><i class="uil uil-map-marker me-1"></i>${ marker.address }</p>`;
			if ( fields.showPhone && marker.phone ) {
				const tel = marker.phone.replace( /[^0-9+]/g, '' );
				html += `<p class="fs-sm mb-0 text-reset"><i class="uil uil-phone me-1"></i><a href="tel:${ tel }">${ marker.phone }</a></p>`;
			}
			if ( fields.showWorkingHours && marker.workingHours ) html += `<p class="fs-sm mb-0 text-reset"><i class="uil uil-clock me-1"></i>${ marker.workingHours }</p>`;

			item.innerHTML = html;
			item.addEventListener( 'click', () => this.onSidebarItemClick( marker.id ) );
			return item;
		}

		onSidebarItemClick( markerId ) {
			const entry = this.markerEls[ markerId ];
			if ( ! entry ) return;

			this.onMarkerClick( entry.data, entry.el );

			if ( window.innerWidth < 768 && this.sidebar ) {
				setTimeout( () => this.sidebar.classList.add( 'd-none' ), 500 );
			}
		}

		highlightSidebarItem( markerId ) {
			this.wrapper.querySelectorAll( '.codeweber-map-sidebar-item' )
				.forEach( i => i.classList.remove( 'active' ) );
			this.wrapper.querySelectorAll( `.codeweber-map-sidebar-item[data-marker-id="${ markerId }"]` )
				.forEach( i => {
					i.classList.add( 'active' );
					i.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
				} );
		}

		// ─── Filters ──────────────────────────────────────────────────────────

		initFilters() {
			const sidebarCfg = this.config.sidebar || {};
			if ( ! sidebarCfg.showFilters ) return;

			const listId = this.config.id + '-sidebar-list';
			const list   = this.wrapper.querySelector( '#' + CSS.escape( listId ) );
			if ( ! list ) return;

			if ( sidebarCfg.filterByCity ) this.createCityFilter( list );
			if ( sidebarCfg.filterByCategory ) this.createCategoryFilter( list );
		}

		createCityFilter( list ) {
			const i18n   = ( typeof codeweberYandexMaps !== 'undefined' && codeweberYandexMaps.i18n ) ? codeweberYandexMaps.i18n : {};
			const cities = new Set();
			( this.config.markers || [] ).forEach( m => { if ( m.city ) cities.add( m.city ); } );
			if ( cities.size === 0 ) return;

			const fc = document.createElement( 'div' );
			fc.className = 'codeweber-map-filter p-3 border-bottom';

			const label = document.createElement( 'label' );
			label.className = 'form-label fs-sm';
			label.textContent = i18n.filterByCity || 'Filter by City';
			label.setAttribute( 'for', `${ this.config.id }-city-filter` );

			const select = document.createElement( 'select' );
			select.id = `${ this.config.id }-city-filter`;
			select.className = 'form-select fs-sm py-1 px-2';

			const allOpt = document.createElement( 'option' );
			allOpt.value = '';
			allOpt.textContent = i18n.allCities || 'All Cities';
			select.appendChild( allOpt );

			Array.from( cities ).sort().forEach( city => {
				const opt = document.createElement( 'option' );
				opt.value = city;
				opt.textContent = city;
				select.appendChild( opt );
			} );

			select.addEventListener( 'change', ( e ) => {
				this.filterByCity( e.target.value );
				if ( window.innerWidth < 768 && this.sidebar ) this.sidebar.classList.add( 'd-none' );
			} );

			fc.appendChild( label );
			fc.appendChild( select );
			list.parentElement.insertBefore( fc, list );
		}

		filterByCity( city ) {
			const showAll = city === '';
			const { YMapMarker } = ymaps3;
			const visibleMarkers = [];

			if ( this.sidebar ) {
				this.sidebar.querySelectorAll( '.codeweber-map-sidebar-item' ).forEach( item => {
					item.style.display = ( showAll || item.dataset.city === city ) ? '' : 'none';
				} );
			}

			this.closeBalloon();

			( this.config.markers || [] ).forEach( markerData => {
				const entry = this.markerEls[ markerData.id ];
				if ( ! entry ) return;
				try { this.map.removeChild( entry.marker ); } catch ( e ) {}

				if ( showAll || markerData.city === city ) {
					this.map.addChild( entry.marker );
					visibleMarkers.push( markerData );
				}
			} );

			if ( visibleMarkers.length > 0 ) {
				setTimeout( () => {
					const center = this.calcBoundsCenter( visibleMarkers );
					const zoom   = this.calcBoundsZoom( visibleMarkers, this.container.offsetWidth, this.container.offsetHeight );
					this.map.update( { location: { center, zoom: zoom || this.config.zoom } } );
				}, 50 );
			}
		}

		createCategoryFilter( list ) {
			// Stub — extend when category data is available
		}

		// ─── Bounds ───────────────────────────────────────────────────────────

		calcBoundsCenter( markers ) {
			const lngs = markers.map( m => m.longitude );
			const lats = markers.map( m => m.latitude );
			return [
				( Math.min( ...lngs ) + Math.max( ...lngs ) ) / 2,
				( Math.min( ...lats ) + Math.max( ...lats ) ) / 2,
			];
		}

		calcBoundsZoom( markers, containerW, containerH ) {
			if ( markers.length < 2 ) return null;
			const lngs   = markers.map( m => m.longitude );
			const lats   = markers.map( m => m.latitude );
			let minLng   = Math.min( ...lngs ), maxLng = Math.max( ...lngs );
			let minLat   = Math.min( ...lats ), maxLat = Math.max( ...lats );
			const lngPad = ( maxLng - minLng ) * 0.15 || 0.02;
			const latPad = ( maxLat - minLat ) * 0.15 || 0.02;
			minLng -= lngPad; maxLng += lngPad;
			minLat -= latPad; maxLat += latPad;

			function latRad( lat ) {
				const sin = Math.sin( lat * Math.PI / 180 );
				const r   = Math.log( ( 1 + sin ) / ( 1 - sin ) ) / 2;
				return Math.max( Math.min( r, Math.PI ), -Math.PI ) / 2;
			}
			const latFraction = ( latRad( maxLat ) - latRad( minLat ) ) / Math.PI;
			const lngFraction = ( maxLng - minLng ) / 360;
			const latZoom     = Math.log( containerH / 256 / latFraction ) / Math.LN2;
			const lngZoom     = Math.log( containerW / 256 / lngFraction ) / Math.LN2;
			return Math.floor( Math.min( latZoom, lngZoom, 17 ) );
		}

		// ─── Utility ──────────────────────────────────────────────────────────

		hideLoader() {
			const loader = this.wrapper.querySelector( '#' + CSS.escape( this.config.id + '-loader' ) );
			if ( ! loader ) return;
			loader.classList.add( 'done' );
			setTimeout( () => { if ( loader.parentNode ) loader.remove(); }, 300 );
		}

		fitBounds() {
			const markers = this.config.markers || [];
			if ( ! markers.length ) return;
			const center = this.calcBoundsCenter( markers );
			const zoom   = this.calcBoundsZoom( markers, this.container.offsetWidth, this.container.offsetHeight );
			this.map.update( { location: { center, zoom: zoom || this.config.zoom, duration: 400 } } );
		}

		invalidateSize() {
			// ymaps3 handles container resize via ResizeObserver automatically
			this.hideLoader();
		}
	}

	// ─── Init ─────────────────────────────────────────────────────────────────

	function initWrapper( wrapper ) {
		if ( wrapper.getAttribute( 'data-cwgb-map-v3-inited' ) === '1' ) return;
		const configData = wrapper.getAttribute( 'data-map-config' );
		if ( ! configData ) return;
		try {
			const config = JSON.parse( configData );
			if ( config.apiVersion !== 3 ) return;
			wrapper.setAttribute( 'data-cwgb-map-v3-inited', '1' );
			const instance = new CodeweberYandexMapsV3( config, wrapper );
			instance.init().catch( err => {
				wrapper.removeAttribute( 'data-cwgb-map-v3-inited' );
				console.error( '[yandex-maps-v3] init error', err );
			} );
		} catch ( e ) {
			console.error( '[yandex-maps-v3] config parse error', e );
		}
	}

	function initMaps() {
		document.querySelectorAll( '.codeweber-yandex-map-wrapper' ).forEach( function ( w ) {
			// Skip maps inside closed offcanvases — init lazily on shown.bs.offcanvas
			var offcanvas = w.closest( '.offcanvas' );
			if ( offcanvas && ! offcanvas.classList.contains( 'show' ) ) return;
			initWrapper( w );
		} );

		document.addEventListener( 'shown.bs.offcanvas', function ( e ) {
			if ( ! e.target || ! e.target.querySelectorAll ) return;
			e.target.querySelectorAll( '.codeweber-yandex-map-wrapper' ).forEach( function ( w ) {
				if ( w._cwgbYandexMapInstance ) {
					if ( typeof w._cwgbYandexMapInstance.invalidateSize === 'function' ) {
						w._cwgbYandexMapInstance.invalidateSize();
					}
				} else {
					initWrapper( w );
				}
			} );
		} );

		if ( typeof MutationObserver !== 'undefined' ) {
			new MutationObserver( function ( mutations ) {
				mutations.forEach( function ( mutation ) {
					mutation.addedNodes.forEach( function ( node ) {
						if ( node.nodeType !== 1 ) return;
						if ( node.classList && node.classList.contains( 'codeweber-yandex-map-wrapper' ) ) {
							initWrapper( node );
						}
						if ( node.querySelectorAll ) {
							node.querySelectorAll( '.codeweber-yandex-map-wrapper' ).forEach( initWrapper );
						}
					} );
				} );
			} ).observe( document.body, { childList: true, subtree: true } );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initMaps );
	} else {
		initMaps();
	}
} )();
