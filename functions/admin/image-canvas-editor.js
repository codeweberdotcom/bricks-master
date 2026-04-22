/* global cwImgEditorData */
( function () {
	'use strict';

	var dialog, canvas, ctx, img, currentId, currentMime;
	var mode = 'pad'; // 'pad' | 'crop'

	// Crop state (in image pixel coordinates)
	var cropX = 0, cropY = 0;
	var drag = null; // { startMX, startMY, startCX, startCY }
	var dispScale = 1; // image px → canvas display px (crop mode only)

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
			'          <input type="number" id="cwice-size" min="100" max="6000" step="10">',
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
			'      <div class="cwice-control-group cwice-crop-only">',
			'        <p class="cwice-hint">Drag the bright area to reposition the crop.</p>',
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
			document.getElementById( id ).addEventListener( 'input', render );
		} );

		// Mode toggle (delegated)
		document.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '.cwice-mode-btn' );
			if ( btn ) setMode( btn.dataset.mode );
		} );

		// Crop drag
		canvas.addEventListener( 'mousedown', onMouseDown );
		canvas.addEventListener( 'mousemove', onMouseMove );
		canvas.addEventListener( 'mouseup',   onMouseUp );
		canvas.addEventListener( 'mouseleave', onMouseUp );

		// Backdrop close
		dialog.addEventListener( 'click', function ( e ) {
			if ( e.target === dialog ) closeDialog();
		} );

		// Delegate edit button clicks
		document.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '.cwice-open-btn' );
			if ( ! btn ) return;
			openEditor(
				btn.dataset.id,
				btn.dataset.url,
				parseInt( btn.dataset.w, 10 ),
				parseInt( btn.dataset.h, 10 )
			);
		} );
	}

	// ── Mode switch ───────────────────────────────────────────────────────
	function setMode( m ) {
		mode = m;

		document.querySelectorAll( '.cwice-mode-btn' ).forEach( function ( btn ) {
			btn.classList.toggle( 'cwice-mode-active', btn.dataset.mode === m );
		} );

		var padOnly  = document.querySelectorAll( '.cwice-pad-only' );
		var cropOnly = document.querySelectorAll( '.cwice-crop-only' );
		var label    = document.getElementById( 'cwice-size-label' );

		padOnly.forEach( function ( el )  { el.style.display = m === 'pad'  ? 'flex' : 'none'; } );
		cropOnly.forEach( function ( el ) { el.style.display = m === 'crop' ? 'block' : 'none'; } );

		label.textContent = m === 'crop' ? 'Output size (px)' : 'Canvas size (px)';

		canvas.style.cursor = m === 'crop' ? 'grab' : 'default';

		if ( m === 'crop' ) {
			// Auto-set size to 80% of the smaller dimension so crop rect is visible
			if ( img && img.naturalWidth ) {
				var maxSq = Math.min( img.naturalWidth, img.naturalHeight );
				document.getElementById( 'cwice-size' ).value = Math.round( maxSq * 0.8 );
			}
			initCropCenter();
		}
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

		// Start in pad mode each time
		setMode( 'pad' );

		img = new Image();
		img.onload  = function () { render(); dialog.showModal(); };
		img.onerror = function () { alert( cwImgEditorData.i18n.noImage ); };
		img.src = url + ( url.indexOf( '?' ) === -1 ? '?' : '&' ) + 'nocache=' + Date.now();
	}

	// ── Render dispatcher ─────────────────────────────────────────────────
	function render() {
		if ( mode === 'crop' ) renderCrop();
		else renderPad();
	}

	// ── Pad mode render ───────────────────────────────────────────────────
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
		var ratio = Math.min( available / img.naturalWidth, available / img.naturalHeight );
		var drawW = img.naturalWidth  * ratio;
		var drawH = img.naturalHeight * ratio;
		var drawX = ( size - drawW ) / 2;
		var drawY = ( size - drawH ) / 2;

		ctx.drawImage( img, drawX, drawY, drawW, drawH );

		document.getElementById( 'cwice-dimensions' ).textContent =
			'Canvas: ' + size + '×' + size +
			' | Image: ' + Math.round( drawW ) + '×' + Math.round( drawH ) +
			' | Pad: ' + padding + 'px';
	}

	// ── Crop mode render ──────────────────────────────────────────────────
	function renderCrop() {
		if ( ! img || ! img.naturalWidth ) return;

		// Scale image to fit the preview area
		var MAX_W = 680, MAX_H = 460;
		var scale = Math.min( MAX_W / img.naturalWidth, MAX_H / img.naturalHeight, 1 );
		dispScale = scale;

		var dW = Math.round( img.naturalWidth  * scale );
		var dH = Math.round( img.naturalHeight * scale );
		canvas.width  = dW;
		canvas.height = dH;

		// Draw full image
		ctx.drawImage( img, 0, 0, dW, dH );

		// Crop rect in display coords
		var csz = getCropSizePx();
		cropX = clamp( cropX, 0, img.naturalWidth  - csz );
		cropY = clamp( cropY, 0, img.naturalHeight - csz );

		var cx = Math.round( cropX * scale );
		var cy = Math.round( cropY * scale );
		var cw = Math.round( csz   * scale );
		var ch = Math.round( csz   * scale );

		// Dark overlay on areas outside crop rect (4 rectangles)
		ctx.fillStyle = 'rgba(0,0,0,0.55)';
		ctx.fillRect( 0,       0,        cx,          dH        ); // left
		ctx.fillRect( cx + cw, 0,        dW - cx - cw, dH       ); // right
		ctx.fillRect( cx,      0,        cw,           cy        ); // top
		ctx.fillRect( cx,      cy + ch,  cw,           dH - cy - ch ); // bottom

		// Crop border
		ctx.strokeStyle = '#fff';
		ctx.lineWidth   = 2;
		ctx.strokeRect( cx, cy, cw, ch );
		ctx.strokeStyle = 'rgba(0,0,0,0.6)';
		ctx.lineWidth   = 1;
		ctx.strokeRect( cx - 1, cy - 1, cw + 2, ch + 2 );

		// Corner handles
		drawHandle( cx,      cy );
		drawHandle( cx + cw, cy );
		drawHandle( cx,      cy + ch );
		drawHandle( cx + cw, cy + ch );

		var outputSize = parseInt( document.getElementById( 'cwice-size' ).value, 10 ) || 800;
		document.getElementById( 'cwice-dimensions' ).textContent =
			'Crop area: ' + csz + '×' + csz + 'px' +
			( outputSize !== csz ? ' → output: ' + outputSize + 'px (scaled)' : '' );
	}

	function drawHandle( x, y ) {
		ctx.fillStyle = '#fff';
		ctx.fillRect( x - 4, y - 4, 8, 8 );
	}

	// ── Crop helpers ──────────────────────────────────────────────────────
	function getCropSizePx() {
		if ( ! img ) return 800;
		var requested = parseInt( document.getElementById( 'cwice-size' ).value, 10 ) || 800;
		// Crop area in the source image cannot be larger than the image itself
		return Math.min( requested, img.naturalWidth, img.naturalHeight );
	}

	function initCropCenter() {
		if ( ! img ) return;
		var csz = getCropSizePx();
		cropX = Math.max( 0, ( img.naturalWidth  - csz ) / 2 );
		cropY = Math.max( 0, ( img.naturalHeight - csz ) / 2 );
	}

	function clamp( val, min, max ) {
		return Math.max( min, Math.min( max, val ) );
	}

	// ── Crop drag ─────────────────────────────────────────────────────────
	function onMouseDown( e ) {
		if ( mode !== 'crop' ) return;
		drag = { startMX: e.offsetX, startMY: e.offsetY, startCX: cropX, startCY: cropY };
		canvas.style.cursor = 'grabbing';
	}

	function onMouseMove( e ) {
		if ( ! drag ) return;
		cropX = drag.startCX + ( e.offsetX - drag.startMX ) / dispScale;
		cropY = drag.startCY + ( e.offsetY - drag.startMY ) / dispScale;
		render();
	}

	function onMouseUp() {
		if ( ! drag ) return;
		drag = null;
		canvas.style.cursor = 'grab';
	}

	// ── Make square ───────────────────────────────────────────────────────
	function makeSquare() {
		if ( ! img ) return;
		if ( mode === 'crop' ) {
			// Crop: use the smaller dimension (max square that fits without padding)
			document.getElementById( 'cwice-size' ).value = Math.min( img.naturalWidth, img.naturalHeight );
			initCropCenter();
		} else {
			// Pad: use the larger dimension
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
		// Canvas already contains the final output — just send it
		sendToServer( canvas.toDataURL( currentMime, currentMime === 'image/png' ? 1 : 0.92 ) );
	}

	function saveCrop() {
		var outputSize = Math.max( 1, parseInt( document.getElementById( 'cwice-size' ).value, 10 ) || 800 );
		var csz = getCropSizePx();
		cropX = clamp( cropX, 0, img.naturalWidth  - csz );
		cropY = clamp( cropY, 0, img.naturalHeight - csz );

		// Render final crop to output size
		canvas.width  = outputSize;
		canvas.height = outputSize;
		ctx.drawImage( img, cropX, cropY, csz, csz, 0, 0, outputSize, outputSize );

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
			.finally( function () {
				saveBtn.disabled = false;
			} );
	}

	// ── Boot ──────────────────────────────────────────────────────────────
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
