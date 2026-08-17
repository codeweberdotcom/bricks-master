/**
 * CodeWeber Yandex Maps v3 JavaScript
 *
 * @package Codeweber
 * @version 1.0.0
 */

(function () {
	'use strict';

	function officeStatus( markerData ) {
		const hours = markerData.officeHours;
		if ( ! hours || typeof hours !== 'object' ) return null;
		const weekdayMap = { Mon: 'monday', Tue: 'tuesday', Wed: 'wednesday', Thu: 'thursday', Fri: 'friday', Sat: 'saturday', Sun: 'sunday' };
		let currentDate = new Date();
		let timeZone = markerData.timezone || undefined;
		if ( timeZone && /^[+-]\d{2}:\d{2}$/.test( timeZone ) ) {
			const sign = timeZone[0] === '-' ? -1 : 1;
			const offset = sign * ( parseInt( timeZone.slice( 1, 3 ), 10 ) * 60 + parseInt( timeZone.slice( 4, 6 ), 10 ) );
			currentDate = new Date( currentDate.getTime() + offset * 60000 );
			timeZone = 'UTC';
		}
		const parts = new Intl.DateTimeFormat( 'en-US', {
			timeZone, weekday: 'short', hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
		} ).formatToParts( currentDate ).reduce( ( result, part ) => ( result[ part.type ] = part.value, result ), {} );
		const row = hours[ weekdayMap[ parts.weekday ] ];
		const now = parseInt( parts.hour, 10 ) * 60 + parseInt( parts.minute, 10 );
		const minutes = value => {
			if ( ! value || ! /^\d{2}:\d{2}$/.test( value ) ) return null;
			const bits = value.split( ':' ).map( Number );
			return bits[0] * 60 + bits[1];
		};
		const result = ( text, state, isOpen = false ) => ( { text, state, isOpen } );
		const plural = number => number + ' ' + ( number % 10 === 1 && number % 100 !== 11 ? 'минуту' : ( [2, 3, 4].includes( number % 10 ) && ! [12, 13, 14].includes( number % 100 ) ? 'минуты' : 'минут' ) );

		if ( ! row ) return result( 'Закрыто', 'closed' );
		if ( row.closed ) return result( 'Выходной', 'closed' );
		const open1 = minutes( row.opens_1 );
		const close1 = minutes( row.closes_1 );
		const open2 = minutes( row.opens_2 );
		const close2 = minutes( row.closes_2 );
		if ( open1 === null ) return result( 'Закрыто', 'closed' );
		const finalClose = close2 !== null ? close2 : close1;
		const firstClose = open2 === null && close1 === null ? close2 : close1;
		if ( now < open1 ) {
			const left = open1 - now;
			return left <= 60 ? result( 'Откроется через ' + plural( left ), 'soon' ) : result( 'Закрыто', 'closed' );
		}
		if ( firstClose !== null && now < firstClose ) {
			const left = firstClose - now;
			if ( open2 !== null && left <= 30 ) return result( 'До перерыва ' + plural( left ), 'soon', true );
			if ( open2 === null && left <= 60 ) return result( 'Закроется через ' + plural( left ), 'soon', true );
			return result( 'Работает', 'open', true );
		}
		if ( open2 !== null && now < open2 ) return result( 'Перерыв до ' + row.opens_2, 'break' );
		if ( open2 !== null && close2 !== null && now < close2 ) {
			const left = close2 - now;
			return left <= 60 ? result( 'Закроется через ' + plural( left ), 'soon', true ) : result( 'Работает', 'open', true );
		}
		if ( finalClose !== null && now >= finalClose ) return result( 'Закрыто', 'closed' );
		return result( 'Работает', 'open', true );
	}

	function officeStatusHtml( markerData ) {
		const status = officeStatus( markerData );
		if ( ! status ) return '';
		const colors = { open: '#198754', closed: '#dc3545', soon: '#fd7e14', break: '#fd7e14' };
		return `<div class="cwgb-office-status" data-state="${ status.state }" style="color:${ colors[ status.state ] };font-weight:600;margin-bottom:6px;">${ status.text }</div>`;
	}

	function officeHoursHtml( workingHours ) {
		return String( workingHours || '' )
			.split( /\s*;\s*/ )
			.filter( Boolean )
			.map( line => {
				const isDayOff = /(?:Выходной|Day off)/i.test( line );
				return `<span class="cwgb-office-hours-line${ isDayOff ? ' cwgb-office-hours-line--day-off' : '' }">${ line }</span>`;
			} )
			.join( '' );
	}

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
				await new Promise( ( resolve, reject ) => {
					const start = Date.now();
					const check = setInterval( () => {
						if ( typeof ymaps3 !== 'undefined' ) {
							clearInterval( check );
							resolve();
						} else if ( Date.now() - start > 10000 ) {
							clearInterval( check );
							reject( new Error( 'ymaps3 load timeout' ) );
						}
					}, 200 );
				} );
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
			this.statusTimer = setInterval( () => this.refreshOfficeStatus(), 60000 );

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
			if ( scheme === 'custom' ) {
				const jsonStr = this.config.colorSchemeCustom || this.config.styleJson || '';
				if ( jsonStr ) {
					try {
						return { customization: JSON.parse( jsonStr ) };
					} catch ( e ) {
						console.warn( '[yandex-maps-v3] Invalid custom style JSON', e );
					}
				}
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

			markers.forEach( ( markerData ) => {
				const built = this.buildMarkerElement();
				const el    = built.el;
				el.dataset.markerId = markerData.id;

				el.addEventListener( 'click', ( e ) => {
					e.stopPropagation();
					this.onMarkerClick( markerData, el );
				} );

				const marker = new YMapMarker(
					{ coordinates: [ markerData.longitude, markerData.latitude ] },
					el
				);
				this.map.addChild( marker );
				this.markerEls[ markerData.id ] = { el, marker, data: markerData, w: built.w, h: built.h };
			} );
		}

		/**
		 * Строит DOM-элемент маркера по глобальным настройкам markerSettings.
		 * Типы: dot (кружок), pin (SVG-пин), icon (своя картинка), logo (логотип из настроек темы).
		 * @return {{el: HTMLElement, w: number, h: number}}
		 */
		buildMarkerElement() {
			const ms    = this.config.markerSettings || {};
			const color = ms.color || '#FF0000';
			const size  = parseInt( ms.size, 10 ) > 0 ? parseInt( ms.size, 10 ) : 0;
			let type    = ms.type || 'dot';

			// Фолбэки: нет картинки → рисуем точку
			if ( type === 'icon' && ! ms.icon ) type = 'dot';
			if ( type === 'logo' && ! ms.logo ) type = 'dot';

			const el = document.createElement( 'div' );

			if ( type === 'pin' ) {
				const w = size || 32;
				const h = Math.round( w * 34 / 24 );
				el.style.cssText = 'width:' + w + 'px;height:' + h + 'px;cursor:pointer;position:relative;transform:translate(-50%,-100%);';
				el.innerHTML =
					'<svg width="' + w + '" height="' + h + '" viewBox="0 0 24 34" xmlns="http://www.w3.org/2000/svg" style="display:block;filter:drop-shadow(0 1px 2px rgba(0,0,0,.35));">' +
					'<path d="M12 0C5.373 0 0 5.373 0 12c0 8.75 12 22 12 22s12-13.25 12-22C24 5.373 18.627 0 12 0z" fill="' + color + '"/>' +
					'<circle cx="12" cy="12" r="4.5" fill="#fff"/>' +
					'</svg>';
				return { el, w, h };
			}

			if ( type === 'icon' || type === 'logo' ) {
				const url = type === 'icon' ? ms.icon : ms.logo;
				const w   = size || ( type === 'logo' ? ( parseInt( ms.logoSize, 10 ) || 40 ) : 40 );
				el.style.cssText = 'width:' + w + 'px;height:' + w + 'px;cursor:pointer;position:relative;transform:translate(-50%,-50%);';
				const img = document.createElement( 'img' );
				img.src = url;
				img.alt = '';
				img.style.cssText = 'width:100%;height:100%;object-fit:contain;display:block;pointer-events:none;';
				el.appendChild( img );
				return { el, w, h: w };
			}

			// dot — как раньше (без transform, чтобы не менять существующие карты)
			const d = size || 14;
			el.style.cssText = [
				'width:' + d + 'px', 'height:' + d + 'px',
				'background:' + color,
				'border:2px solid #fff',
				'border-radius:50%',
				'cursor:pointer',
				'box-shadow:0 1px 3px rgba(0,0,0,.4)',
				'position:relative',
			].join( ';' );
			return { el, w: d, h: d };
		}

		onMarkerClick( markerData, el ) {
			if ( this.activeMarkerId === String( markerData.id ) ) {
				this.closeBalloon();
				return;
			}
			this.closeBalloon();

			const balloon = this.buildBalloon( markerData );
			// Позиция балуна зависит от размера маркера: над маркером, по центру
			const entry = this.markerEls[ markerData.id ];
			if ( entry && entry.w ) {
				balloon.style.bottom = ( entry.h + 8 ) + 'px';
				balloon.style.left   = ( entry.w / 2 ) + 'px';
			}
			el.appendChild( balloon );
			this.setMarkerZIndex( entry, 10000 );
			this.activeMarkerId = String( markerData.id );

			this.highlightSidebarItem( markerData.id );
			const zoom   = this.config.markerClickZoom || this.config.zoom || 15;
			const center = this.calcCenterWithBalloonOffset( markerData.longitude, markerData.latitude, zoom );
			this.map.update( { location: { center, zoom, duration: 400 } } );
		}

		refreshOfficeStatus() {
			if ( this.openNowOnly ) this.applySidebarFilters();
			if ( this.sidebar ) {
				this.sidebar.querySelectorAll( '.codeweber-map-sidebar-item' ).forEach( item => {
					const entry = this.markerEls[ item.dataset.markerId ];
					const current = item.querySelector( '.cwgb-office-status' );
					const status = entry ? officeStatus( entry.data ) : null;
					if ( current && status ) {
						current.textContent = status.text;
						current.dataset.state = status.state;
						current.style.color = { open: '#198754', closed: '#dc3545', soon: '#fd7e14', break: '#fd7e14' }[ status.state ];
					}
				} );
			}
			if ( ! this.activeMarkerId ) return;
			const entry = this.markerEls[ this.activeMarkerId ];
			const current = entry && entry.el.querySelector( '.cwgb-office-status' );
			const status = entry ? officeStatus( entry.data ) : null;
			if ( current && status ) {
				current.textContent = status.text;
				current.dataset.state = status.state;
				current.style.color = { open: '#198754', closed: '#dc3545', soon: '#fd7e14', break: '#fd7e14' }[ status.state ];
			}
		}

		/**
		 * Raise the whole YMapMarker while its balloon is open.
		 * A balloon z-index alone is not enough because every marker is rendered
		 * in its own stacking context by Yandex Maps.
		 */
		setMarkerZIndex( entry, zIndex ) {
			if ( ! entry ) return;

			if ( entry.marker && typeof entry.marker.update === 'function' ) {
				entry.marker.update( { zIndex } );
			}

			// DOM fallback for API builds where marker.update does not repaint zIndex.
			entry.el.style.zIndex = String( zIndex );
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
				this.setMarkerZIndex( entry, 0 );
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
			const minWidth = balloonCfg.minWidth || 280;
			const i18n     = ( typeof codeweberYandexMaps !== 'undefined' && codeweberYandexMaps.i18n ) ? codeweberYandexMaps.i18n : {};

			let body = '';
			body += officeStatusHtml( markerData );
			if ( fields.showCity && markerData.city ) {
				body += `<div style="margin-bottom:2px;line-height:1.35;"><small class="text-muted" style="display:block;">${ i18n.city || 'City' }:</small>${ markerData.city }</div>`;
			}
			if ( fields.showAddress && markerData.address ) {
				body += `<div style="margin-bottom:2px;line-height:1.35;">${ markerData.address }</div>`;
			}
			if ( fields.showAddress && markerData.landmark ) {
				body += `<div class="mb-1" style="line-height:1.35;"><small class="text-muted" style="display:block;">${ i18n.landmark || 'Landmark' }:</small>${ markerData.landmark }</div>`;
			}
			const phones = Array.isArray( markerData.phones )
				? markerData.phones.filter( Boolean )
				: ( markerData.phone ? markerData.phone.split( /\s*[·|]\s*/ ).filter( Boolean ) : [] );
			if ( fields.showPhone && phones.length ) {
				const phoneLinks = phones.map( phone => {
					const tel = phone.replace( /[^0-9+]/g, '' );
					return `<a href="tel:${ tel }" style="display:block;">${ phone }</a>`;
				} ).join( '' );
				body += `<div class="mb-1"><small class="text-muted">${ i18n.phone || 'Phone' }:</small>${ phoneLinks }</div>`;
			}
			if ( fields.showWorkingHours && markerData.workingHours ) {
				const hoursLines = officeHoursHtml( markerData.workingHours );
				body += `<div class="mb-1"><small class="text-muted">${ i18n.workingHours || 'Working Hours' }:</small>${ hoursLines }</div>`;
			}
			if ( fields.showDescription && markerData.description ) {
				body += `<div class="mb-2">${ markerData.description }</div>`;
			}

			const linkHtml = ( fields.showLink && markerData.link )
				? `<div><a href="${ markerData.link }" class="btn btn-primary btn-xs">${ i18n.viewDetails || 'View Details' }</a></div>`
				: '';

			const div = document.createElement( 'div' );
			div.className = 'cwgb-balloon-v3';
			div.style.cssText = `position:absolute;bottom:22px;left:7px;transform:translateX(-50%);background:#fff;color:#333;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.18);padding:12px 16px;min-width:${ minWidth }px;max-width:${ maxWidth }px;z-index:100;white-space:normal;`;

			const titleHtml = markerData.title ? `<div style="font-weight:600;margin-bottom:4px;padding-right:20px;font-size:14px;">${ markerData.title }</div>` : '';
			const textHtml  = `${ titleHtml }<div style="font-size:13px;">${ body }</div>`;

			div.innerHTML = `
				<button class="cwgb-balloon-close" style="position:absolute;top:6px;right:8px;background:none;border:none;font-size:18px;cursor:pointer;color:#999;line-height:1;padding:0;" aria-label="Close">&times;</button>
				${ markerData.image
					? `<div style="display:flex;gap:10px;align-items:flex-start;">`
						+ `<img src="${ markerData.image }" alt="${ markerData.title || '' }" style="width:120px;height:120px;object-fit:cover;flex-shrink:0;border-radius:4px;">`
						+ `<div style="min-width:0;display:flex;flex-direction:column;justify-content:space-between;height:120px;">${ textHtml }${ linkHtml }</div>`
						+ `</div>`
					: `${ textHtml }${ linkHtml }`
				}
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
			const sidebarStyle = sidebarCfg.style === 'compact' ? 'compact' : 'default';
			sidebar.className = `codeweber-map-sidebar codeweber-map-sidebar-${ sidebarCfg.position } codeweber-map-sidebar-${ sidebarStyle } bg-white text-dark rounded shadow overflow-auto d-none d-md-block`;

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
			const fields = Object.assign( {
				showTitle: true, showCity: true, showAddress: false,
				showLandmark: false, showStatus: false, showPhone: false,
				showWorkingHours: true, showDescription: true,
			}, sidebarCfg.fields || {} );

			const item = document.createElement( 'div' );
			item.className = 'codeweber-map-sidebar-item border-bottom p-5';
			item.dataset.markerId = marker.id;
			item.dataset.city     = marker.city || '';
			item.dataset.category = marker.category || '';

			let html = '';
			if ( fields.showTitle && marker.title ) html += `<div class="h6 mb-1 text-reset">${ marker.title }</div>`;
			if ( fields.showStatus ) html += officeStatusHtml( marker );
			if ( fields.showDescription && marker.description ) html += `<p class="fs-sm mb-0 text-reset">${ marker.description }</p>`;
			const locationParts = [];
			if ( fields.showCity && marker.city ) locationParts.push( marker.city );
			if ( fields.showAddress && marker.address ) locationParts.push( marker.address );
			if ( locationParts.length ) html += `<p class="fs-sm mb-0 text-reset"><i class="uil uil-map-marker me-1"></i>${ locationParts.join( ', ' ) }</p>`;
			if ( fields.showLandmark && marker.landmark ) html += `<p class="fs-sm mb-0 text-reset"><i class="uil uil-compass me-1"></i>${ marker.landmark }</p>`;
			if ( fields.showPhone && marker.phone ) {
				const tel = marker.phone.replace( /[^0-9+]/g, '' );
				html += `<p class="fs-sm mb-0 text-reset"><i class="uil uil-phone me-1"></i><a href="tel:${ tel }">${ marker.phone }</a></p>`;
			}
			if ( fields.showWorkingHours && marker.workingHours ) html += `<div class="fs-sm mb-0 text-reset cwgb-office-hours"><i class="uil uil-clock me-1"></i><span>${ officeHoursHtml( marker.workingHours ) }</span></div>`;

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
			this.createOpenNowFilter( list );
		}

		getFilterContainer( list ) {
			let container = list.parentElement.querySelector( '.codeweber-map-filter' );
			if ( container ) return container;
			container = document.createElement( 'div' );
			container.className = 'codeweber-map-filter position-sticky bg-white p-3 border-bottom';
			container.style.zIndex = '3';
			const sidebarTitle = this.sidebar ? this.sidebar.querySelector( '.codeweber-map-sidebar-title' ) : null;
			container.style.top = sidebarTitle ? sidebarTitle.offsetHeight + 'px' : '0';
			list.parentElement.insertBefore( container, list );
			return container;
		}

		createCityFilter( list ) {
			const i18n   = ( typeof codeweberYandexMaps !== 'undefined' && codeweberYandexMaps.i18n ) ? codeweberYandexMaps.i18n : {};
			const cities = new Set();
			( this.config.markers || [] ).forEach( m => { if ( m.city ) cities.add( m.city ); } );
			if ( cities.size === 0 ) return;

			const fc = this.getFilterContainer( list );

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
		}

		createOpenNowFilter( list ) {
			const fc = this.getFilterContainer( list );
			const row = document.createElement( 'div' );
			row.className = 'form-check mt-2';
			const checkbox = document.createElement( 'input' );
			checkbox.type = 'checkbox';
			checkbox.id = `${ this.config.id }-open-now-filter`;
			checkbox.className = 'form-check-input';
			const label = document.createElement( 'label' );
			label.className = 'form-check-label fs-sm';
			label.htmlFor = checkbox.id;
			label.textContent = 'Открыто сейчас';
			checkbox.addEventListener( 'change', () => {
				this.openNowOnly = checkbox.checked;
				this.applySidebarFilters();
			} );
			row.appendChild( checkbox );
			row.appendChild( label );
			fc.appendChild( row );
		}

		filterByCity( city ) {
			this.activeCityFilter = city;
			this.applySidebarFilters();
		}

		applySidebarFilters() {
			const city = this.activeCityFilter || '';
			const visibleMarkers = [];

			this.closeBalloon();

			( this.config.markers || [] ).forEach( markerData => {
				const entry = this.markerEls[ markerData.id ];
				if ( ! entry ) return;
				try { this.map.removeChild( entry.marker ); } catch ( e ) {}
				const cityMatches = ! city || markerData.city === city;
				const status = officeStatus( markerData );
				const openMatches = ! this.openNowOnly || ( status && status.isOpen );
				const visible = cityMatches && openMatches;
				const item = this.sidebar ? this.sidebar.querySelector( `.codeweber-map-sidebar-item[data-marker-id="${ markerData.id }"]` ) : null;
				if ( item ) item.style.display = visible ? '' : 'none';

				if ( visible ) {
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

	function observeEditorFrames() {
		document.querySelectorAll( 'iframe' ).forEach( function ( frame ) {
			try {
				const frameDocument = frame.contentDocument;
				if ( ! frameDocument || ! frameDocument.head || ! frame.contentWindow ) return;
				if ( ! frameDocument.querySelector( '.codeweber-yandex-map-wrapper' ) ) return;

				frame.contentWindow.codeweberYandexMaps = window.codeweberYandexMaps || {};
				const editorStyles = ( window.codeweberYandexMaps && window.codeweberYandexMaps.editorStyles ) || [];
				editorStyles.forEach( function ( href, index ) {
					const id = 'codeweber-map-editor-style-' + index;
					if ( frameDocument.getElementById( id ) ) return;
					const link = frameDocument.createElement( 'link' );
					link.id = id;
					link.rel = 'stylesheet';
					link.href = href;
					frameDocument.head.appendChild( link );
				} );

				const copyElement = function ( source, targetId ) {
					if ( ! source || frameDocument.getElementById( targetId ) ) return null;
					const clone = frameDocument.createElement( source.tagName.toLowerCase() );
					Array.from( source.attributes ).forEach( attr => clone.setAttribute( attr.name, attr.value ) );
					clone.id = targetId;
					if ( clone.tagName === 'SCRIPT' ) clone.async = false;
					frameDocument.head.appendChild( clone );
					return clone;
				};

				const cssSource = document.getElementById( 'codeweber-yandex-maps-v3-css' )
					|| document.querySelector( 'link[href*="yandex-maps.css"]' );
				copyElement( cssSource, 'codeweber-yandex-maps-v3-editor-css' );

				const apiSource = document.getElementById( 'yandex-maps-api-v3-js' )
					|| document.querySelector( 'script[src*="api-maps.yandex.ru/v3/"]' );
				const runtimeSource = document.getElementById( 'codeweber-yandex-maps-v3-js' )
					|| document.querySelector( 'script[src*="yandex-maps-v3.js"]' );
				const apiClone = copyElement( apiSource, 'yandex-maps-api-v3-editor-js' );
				const loadRuntime = function () {
					copyElement( runtimeSource, 'codeweber-yandex-maps-v3-editor-js' );
				};
				if ( frame.contentWindow.ymaps3 ) {
					loadRuntime();
				} else if ( apiClone ) {
					apiClone.addEventListener( 'load', loadRuntime, { once: true } );
				}
			} catch ( e ) {
				// Ignore unrelated cross-origin frames.
			}
		} );
	}

	function initMaps() {
		observeEditorFrames();
		setInterval( observeEditorFrames, 1000 );
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
