/* global cwImgEditorData */
( function () {
	'use strict';

	var dialog, canvas, ctx, img, currentId, currentMime;
	var mode = 'pad'; // 'pad' | 'crop'

	// Crop state — all in IMAGE pixel coordinates
	var cropX = 0, cropY = 0, cropSize = 800;

	// Drag state
	var drag = null;
	// { type: 'move'|'resize', corner: 'tl'|'tr'|'bl'|'br',
	//   startMX, startMY, startCX, startCY, startSZ }

	// Last rendered crop rect in CANVAS display pixels (for hit-testing)
	var dispCrop = { cx: 0, cy: 0, cw: 0, ch: 0 };
	var dispScale = 1;
	var dispOffX = 0, dispOffY = 0; // image offset on canvas (crop extends beyond image)

	var HANDLE_R = 6; // handle hit radius in px

	// ── Dialog template ───────────────────────────────────────────────────
	function createDialog() {
		var tpl = [
			'<dialog id="cwice-dialog">',
			'  <div class="cwice-header">',
			'    <span class="cwice-title">Image Canvas Editor</span>',
			'    <button type="button" class="cwice-close" id="cwice-close">&times;</button>',
			'  </div>',
			'  <div class="cwice-body">',
			'    <div class="cwice-canvas-wrap">',
			'      <canvas id="cwice-canvas"></canvas>',
			'    </div>',
			'    <div class="cwice-controls">',
			'      <div class="cwice-mode-toggle">',
			'        <button type="button" class="cwice-mode-btn cwice-mode-active" data-mode="pad">Pad</button>',
			'        <button type="button" class="cwice-mode-btn" data-mode="crop">Crop</button>',
			'      </div>',
			'      <div class="cwice-control-group">',
			'        <label for="cwice-size" id="cwice-size-label">Canvas size (px)</label>',
			'        <div class="cwice-row">',
			'          <input type="number" id="cwice-size" min="50" max="8000" step="10">',
			'          <button type="button" class="button" id="cwice-make-square">Square</button>',
			'        </div>',
			'      </div>',
			'      <div class="cwice-control-group cwice-pad-only">',
			'        <label for="cwice-bg-color">Background</label>',
			'        <input type="color" id="cwice-bg-color" value="#ffffff">',
			'      </div>',
			'      <div class="cwice-control-group cwice-pad-only">',
			'        <label for="cwice-padding">Padding (px)</label>',
			'        <input type="number" id="cwice-padding" min="0" max="1000" step="5" value="0">',
			'      </div>',
			'      <div class="cwice-control-group cwice-crop-only" style="display:none">',
			'        <p class="cwice-hint">Drag inside to move.<br>Drag corners to resize.</p>',
			'      </div>',
			'      <div class="cwice-info" id="cwice-dimensions"></div>',
			'    </div>',
			'  </div>',
			'  <div class="cwice-footer">',
			'    <span class="cwice-status" id="cwice-status"></span>',
			'    <button type="button" class="button button-primary" id="cwice-save">Save</button>',
			'    <button type="button" class="button" id="cwice-cancel">Cancel</button>',
			'  </div>',
			'</dialog>',
		].join( '\n' );
		document.body.insertAdjacentHTML( 'beforeend', tpl );
	}

	// ── Init ──────────────────────────────────────────────────────────────
	function init() {
		createDialog();

		dialog = document.getElementById( 'cwice-dialog' );
		canvas = document.getElementById( 'cwice-canvas' );
		ctx    = canvas.getContext( '2d' );

		document.getElementById( 'cwice-close' ).addEventListener( 'click', closeDialog );
		document.getElementById( 'cwice-cancel' ).addEventListener( 'click', closeDialog );
		document.getElementById( 'cwice-save' ).addEventListener( 'click', save );
		document.getElementById( 'cwice-make-square' ).addEventListener( 'click', makeSquare );

		[ 'cwice-size', 'cwice-bg-color', 'cwice-padding' ].forEach( function ( id ) {
			document.getElementById( id ).addEventListener( 'input', function () {
				if ( mode === 'crop' ) {
					cropSize = Math.max( 50, parseInt( document.getElementById( 'cwice-size' ).value, 10 ) || 50 );
				}
				render();
			} );
		} );

		canvas.addEventListener( 'mousedown',  onMouseDown );
		canvas.addEventListener( 'mousemove',  onMouseMove );
		canvas.addEventListener( 'mouseup',    onMouseUp );
		canvas.addEventListener( 'mouseleave', onMouseUp );

		dialog.addEventListener( 'click', function ( e ) {
			if ( e.target === dialog ) closeDialog();
		} );

		// Delegated clicks
		document.addEventListener( 'click', function ( e ) {
			var modeBtn = e.target.closest( '.cwice-mode-btn' );
			if ( modeBtn ) { setMode( modeBtn.dataset.mode ); return; }

			var openBtn = e.target.closest( '.cwice-open-btn' );
			if ( openBtn ) {
				openEditor(
					openBtn.dataset.id,
					openBtn.dataset.url,
					parseInt( openBtn.dataset.w, 10 ),
					parseInt( openBtn.dataset.h, 10 )
				);
			}
		} );
	}

	// ── Mode ─────────────────────────────────────────────────────────────
	function setMode( m ) {
		mode = m;

		document.querySelectorAll( '.cwice-mode-btn' ).forEach( function ( b ) {
			b.classList.toggle( 'cwice-mode-active', b.dataset.mode === m );
		} );

		document.querySelectorAll( '.cwice-pad-only' ).forEach( function ( el ) {
			el.style.display = m === 'pad' ? '' : 'none';
		} );
		document.querySelectorAll( '.cwice-crop-only' ).forEach( function ( el ) {
			el.style.display = m === 'crop' ? 'block' : 'none';
		} );

		document.getElementById( 'cwice-size-label' ).textContent =
			m === 'crop' ? 'Crop size (px)' : 'Canvas size (px)';

		if ( m === 'crop' && img && img.naturalWidth ) {
			// Default crop = 80% of smaller dimension, centered
			cropSize = Math.round( Math.min( img.naturalWidth, img.naturalHeight ) * 0.8 );
			document.getElementById( 'cwice-size' ).value = cropSize;
			centerCrop();
		}

		canvas.style.cursor = m === 'crop' ? 'default' : 'default';
		render();
	}

	// ── Open editor ───────────────────────────────────────────────────────
	function openEditor( id, url, origW, origH ) {
		currentId   = id;
		currentMime = /\.png$/i.test( url ) ? 'image/png' : 'image/jpeg';

		var size = Math.max( origW || 0, origH || 0 );
		document.getElementById( 'cwice-size' ).value    = size > 0 ? size : 800;
		document.getElementById( 'cwice-padding' ).value = 0;

		var statusEl = document.getElementById( 'cwice-status' );
		statusEl.textContent = '';
		statusEl.style.color = '';

		setMode( 'pad' );

		img = new Image();
		img.onload  = function () { render(); dialog.showModal(); };
		img.onerror = function () { alert( cwImgEditorData.i18n.noImage ); };
		img.src = url + ( url.indexOf( '?' ) === -1 ? '?' : '&' ) + 'nc=' + Date.now();
	}

	// ── Render dispatcher ─────────────────────────────────────────────────
	function render() {
		if ( mode === 'crop' ) renderCrop();
		else renderPad();
	}

	// ── Pad render ────────────────────────────────────────────────────────
	function renderPad() {
		var size    = Math.max( 1, parseInt( document.getElementById( 'cwice-size' ).value, 10 ) || 800 );
		var bgColor = document.getElementById( 'cwice-bg-color' ).value;
		var padding = Math.max( 0, parseInt( document.getElementById( 'cwice-padding' ).value, 10 ) || 0 );

		canvas.width  = size;
		canvas.height = size;
		ctx.fillStyle = bgColor;
		ctx.fillRect( 0, 0, size, size );

		if ( ! img || ! img.naturalWidth ) return;

		var available = Math.max( 1, size - padding * 2 );
		var ratio     = Math.min( available / img.naturalWidth, available / img.naturalHeight );
		var drawW     = img.naturalWidth  * ratio;
		var drawH     = img.naturalHeight * ratio;
		var drawX     = ( size - drawW ) / 2;
		var drawY     = ( size - drawH ) / 2;

		ctx.drawImage( img, drawX, drawY, drawW, drawH );

		document.getElementById( 'cwice-dimensions' ).textContent =
			'Canvas: ' + size + '×' + size +
			' | Image: ' + Math.round( drawW ) + '×' + Math.round( drawH ) +
			' | Pad: ' + padding + 'px';
	}

	// ── Crop render ───────────────────────────────────────────────────────
	function renderCrop() {
		if ( ! img || ! img.naturalWidth ) return;

		// Canvas covers cropSize + any overflow beyond image bounds
		var padL = Math.max( 0, -cropX );
		var padT = Math.max( 0, -cropY );
		var padR = Math.max( 0, cropX + cropSize - img.naturalWidth );
		var padB = Math.max( 0, cropY + cropSize - img.naturalHeight );

		// Display scale: fit the whole canvas in ~680×460
		var totalW = cropSize + padL + padR;
		var totalH = cropSize + padT + padB;
		// But minimum shows the image
		var viewW  = Math.max( img.naturalWidth, totalW );
		var viewH  = Math.max( img.naturalHeight, totalH );

		var MAX_W = 680, MAX_H = 460;
		var scale = Math.min( MAX_W / viewW, MAX_H / viewH, 1 );
		dispScale = scale;

		var canvasW = Math.round( viewW  * scale );
		var canvasH = Math.round( viewH  * scale );
		canvas.width  = canvasW;
		canvas.height = canvasH;

		// Image offset on display canvas (image top-left in display px)
		// Image is placed so that region [cropX, cropY] maps to the canvas
		// The canvas top-left is: imageOrigin - padL, - padT (in image px)
		dispOffX = Math.round( padL * scale );
		dispOffY = Math.round( padT * scale );
		// But we need offset for the image itself relative to canvas:
		// canvas origin in image coords = -padL, -padT
		// image origin in canvas coords = padL*scale, padT*scale
		var imgDispX = dispOffX;
		var imgDispY = dispOffY;
		var imgDispW = Math.round( img.naturalWidth  * scale );
		var imgDispH = Math.round( img.naturalHeight * scale );

		// Hatch (gray) background for area outside image
		ctx.fillStyle = '#d0d0d0';
		ctx.fillRect( 0, 0, canvasW, canvasH );

		// Draw image
		ctx.drawImage( img, imgDispX, imgDispY, imgDispW, imgDispH );

		// Crop rect in display coords
		var cx = Math.round( ( cropX + padL ) * scale );
		var cy = Math.round( ( cropY + padT ) * scale );
		var cw = Math.round( cropSize * scale );
		var ch = Math.round( cropSize * scale );

		// Store for hit testing
		dispCrop = { cx: cx, cy: cy, cw: cw, ch: ch };

		// Dark overlay — 4 rectangles outside crop rect
		ctx.fillStyle = 'rgba(0,0,0,0.52)';
		if ( cx > 0 )          ctx.fillRect( 0,      0,      cx,              canvasH );
		if ( cx + cw < canvasW ) ctx.fillRect( cx + cw, 0,    canvasW - cx - cw, canvasH );
		if ( cy > 0 )          ctx.fillRect( cx,     0,      cw,              cy );
		if ( cy + ch < canvasH ) ctx.fillRect( cx,     cy + ch, cw,            canvasH - cy - ch );

		// Crop rect border
		ctx.strokeStyle = '#fff';
		ctx.lineWidth   = 2;
		ctx.strokeRect( cx, cy, cw, ch );
		ctx.strokeStyle = 'rgba(0,0,0,0.5)';
		ctx.lineWidth   = 1;
		ctx.strokeRect( cx - 1, cy - 1, cw + 2, ch + 2 );

		// Corner handles
		[
			[ cx,      cy      ],
			[ cx + cw, cy      ],
			[ cx,      cy + ch ],
			[ cx + cw, cy + ch ],
		].forEach( function ( p ) {
			ctx.fillStyle = '#fff';
			ctx.fillRect( p[0] - HANDLE_R, p[1] - HANDLE_R, HANDLE_R * 2, HANDLE_R * 2 );
			ctx.strokeStyle = '#333';
			ctx.lineWidth = 1;
			ctx.strokeRect( p[0] - HANDLE_R, p[1] - HANDLE_R, HANDLE_R * 2, HANDLE_R * 2 );
		} );

		document.getElementById( 'cwice-dimensions' ).textContent =
			'Crop: ' + Math.round( cropSize ) + '×' + Math.round( cropSize ) + 'px' +
			( cropX < 0 || cropY < 0 || cropX + cropSize > img.naturalWidth || cropY + cropSize > img.naturalHeight
				? ' (extends beyond image — white fill)' : '' );
	}

	// ── Crop helpers ──────────────────────────────────────────────────────
	function centerCrop() {
		if ( ! img ) return;
		cropX = ( img.naturalWidth  - cropSize ) / 2;
		cropY = ( img.naturalHeight - cropSize ) / 2;
	}

	function hitCorner( mx, my ) {
		var d = dispCrop;
		var corners = {
			tl: [ d.cx,        d.cy        ],
			tr: [ d.cx + d.cw, d.cy        ],
			bl: [ d.cx,        d.cy + d.ch ],
			br: [ d.cx + d.cw, d.cy + d.ch ],
		};
		var found = null;
		Object.keys( corners ).forEach( function ( k ) {
			if ( Math.abs( mx - corners[ k ][ 0 ] ) <= HANDLE_R + 4 &&
			     Math.abs( my - corners[ k ][ 1 ] ) <= HANDLE_R + 4 ) {
				found = k;
			}
		} );
		return found;
	}

	function insideCrop( mx, my ) {
		var d = dispCrop;
		return mx >= d.cx && mx <= d.cx + d.cw && my >= d.cy && my <= d.cy + d.ch;
	}

	function cornerCursor( corner ) {
		return { tl: 'nw-resize', tr: 'ne-resize', bl: 'sw-resize', br: 'se-resize' }[ corner ] || 'default';
	}

	// ── Mouse events ──────────────────────────────────────────────────────
	function onMouseDown( e ) {
		if ( mode !== 'crop' ) return;
		var mx = e.offsetX, my = e.offsetY;
		var corner = hitCorner( mx, my );
		if ( corner ) {
			drag = { type: 'resize', corner: corner,
				startMX: mx, startMY: my,
				startSZ: cropSize, startCX: cropX, startCY: cropY };
			canvas.style.cursor = cornerCursor( corner );
		} else if ( insideCrop( mx, my ) ) {
			drag = { type: 'move',
				startMX: mx, startMY: my,
				startCX: cropX, startCY: cropY };
			canvas.style.cursor = 'grabbing';
		}
	}

	function onMouseMove( e ) {
		var mx = e.offsetX, my = e.offsetY;

		if ( mode === 'crop' && ! drag ) {
			var c = hitCorner( mx, my );
			canvas.style.cursor = c ? cornerCursor( c )
				: insideCrop( mx, my ) ? 'grab' : 'default';
			return;
		}

		if ( ! drag ) return;

		var dx = ( mx - drag.startMX ) / dispScale;
		var dy = ( my - drag.startMY ) / dispScale;

		if ( drag.type === 'move' ) {
			cropX = drag.startCX + dx;
			cropY = drag.startCY + dy;

		} else {
			// Resize — maintain square by using the axis that gives bigger delta
			var delta;
			if ( drag.corner === 'br' ) {
				delta = Math.max( dx, dy );
				cropX = drag.startCX;
				cropY = drag.startCY;
			} else if ( drag.corner === 'bl' ) {
				delta = Math.max( -dx, dy );
				cropX = drag.startCX + drag.startSZ - ( drag.startSZ + delta );
				cropY = drag.startCY;
			} else if ( drag.corner === 'tr' ) {
				delta = Math.max( dx, -dy );
				cropX = drag.startCX;
				cropY = drag.startCY + drag.startSZ - ( drag.startSZ + delta );
			} else { // tl
				delta = Math.max( -dx, -dy );
				cropX = drag.startCX + drag.startSZ - ( drag.startSZ + delta );
				cropY = drag.startCY + drag.startSZ - ( drag.startSZ + delta );
			}
			cropSize = Math.max( 50, drag.startSZ + delta );
			document.getElementById( 'cwice-size' ).value = Math.round( cropSize );
		}

		render();
	}

	function onMouseUp() {
		drag = null;
		if ( mode === 'crop' ) canvas.style.cursor = 'default';
	}

	// ── Make square ───────────────────────────────────────────────────────
	function makeSquare() {
		if ( ! img ) return;
		if ( mode === 'crop' ) {
			cropSize = Math.min( img.naturalWidth, img.naturalHeight );
			document.getElementById( 'cwice-size' ).value = cropSize;
			centerCrop();
		} else {
			document.getElementById( 'cwice-size' ).value = Math.max( img.naturalWidth, img.naturalHeight );
		}
		render();
	}

	// ── Close ─────────────────────────────────────────────────────────────
	function closeDialog() {
		dialog.close();
		img  = null;
		drag = null;
	}

	// ── Save ──────────────────────────────────────────────────────────────
	function save() {
		if ( mode === 'crop' ) saveCrop();
		else savePad();
	}

	function savePad() {
		sendToServer( canvas.toDataURL( currentMime, currentMime === 'image/png' ? 1 : 0.92 ) );
	}

	function saveCrop() {
		var outSize = Math.round( cropSize );

		// Build output canvas
		canvas.width  = outSize;
		canvas.height = outSize;

		// Fill white for areas outside the image
		ctx.fillStyle = '#ffffff';
		ctx.fillRect( 0, 0, outSize, outSize );

		// Source: portion of image that intersects crop rect
		var srcX = Math.max( 0, cropX );
		var srcY = Math.max( 0, cropY );
		var srcX2 = Math.min( img.naturalWidth,  cropX + cropSize );
		var srcY2 = Math.min( img.naturalHeight, cropY + cropSize );
		var srcW  = srcX2 - srcX;
		var srcH  = srcY2 - srcY;

		if ( srcW > 0 && srcH > 0 ) {
			// Destination: proportional position within output canvas
			var dstX = ( srcX - cropX ) / cropSize * outSize;
			var dstY = ( srcY - cropY ) / cropSize * outSize;
			var dstW = srcW / cropSize * outSize;
			var dstH = srcH / cropSize * outSize;
			ctx.drawImage( img, srcX, srcY, srcW, srcH, dstX, dstY, dstW, dstH );
		}

		sendToServer( canvas.toDataURL( currentMime, currentMime === 'image/png' ? 1 : 0.92 ) );
	}

	function sendToServer( imageData ) {
		var data     = cwImgEditorData;
		var statusEl = document.getElementById( 'cwice-status' );
		var saveBtn  = document.getElementById( 'cwice-save' );

		statusEl.style.color = '';
		statusEl.textContent = data.i18n.saving;
		saveBtn.disabled     = true;

		var body = new FormData();
		body.append( 'action',        'cw_img_editor_save' );
		body.append( 'nonce',         data.nonce );
		body.append( 'attachment_id', currentId );
		body.append( 'image_data',    imageData );
		body.append( 'mime_type',     currentMime );

		fetch( data.ajaxUrl, { method: 'POST', body: body } )
			.then( function ( r ) { return r.json(); } )
			.then( function ( res ) {
				if ( res.success ) {
					statusEl.style.color = '#2e7d32';
					statusEl.textContent = data.i18n.saved;
					setTimeout( closeDialog, 1400 );
				} else {
					statusEl.style.color = '#c62828';
					statusEl.textContent = data.i18n.error + ': ' + ( ( res.data && res.data.message ) || '' );
				}
			} )
			.catch( function () {
				statusEl.style.color = '#c62828';
				statusEl.textContent = data.i18n.error;
			} )
			.finally( function () { saveBtn.disabled = false; } );
	}

	// ── Boot ──────────────────────────────────────────────────────────────
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
