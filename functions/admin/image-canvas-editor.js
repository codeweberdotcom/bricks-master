/* global cwImgEditorData */
( function () {
	'use strict';

	var dialog, canvas, ctx, img, currentId, currentMime;

	// ── Build dialog HTML once ────────────────────────────────────────────
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
			'      <div class="cwice-control-group">',
			'        <label for="cwice-size">Canvas size (px)</label>',
			'        <div class="cwice-row">',
			'          <input type="number" id="cwice-size" min="100" max="6000" step="10">',
			'          <button type="button" class="button" id="cwice-make-square">Square</button>',
			'        </div>',
			'      </div>',
			'      <div class="cwice-control-group">',
			'        <label for="cwice-bg-color">Background</label>',
			'        <input type="color" id="cwice-bg-color" value="#ffffff">',
			'      </div>',
			'      <div class="cwice-control-group">',
			'        <label for="cwice-padding">Padding (px)</label>',
			'        <input type="number" id="cwice-padding" min="0" max="1000" step="5" value="0">',
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

		// Close on backdrop click
		dialog.addEventListener( 'click', function ( e ) {
			if ( e.target === dialog ) closeDialog();
		} );

		// Delegate open button clicks
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

	function openEditor( id, url, origW, origH ) {
		currentId   = id;
		currentMime = /\.png$/i.test( url ) ? 'image/png' : 'image/jpeg';

		document.getElementById( 'cwice-size' ).value    = Math.max( origW, origH );
		document.getElementById( 'cwice-padding' ).value = 0;

		var statusEl = document.getElementById( 'cwice-status' );
		statusEl.textContent  = '';
		statusEl.style.color  = '';

		img = new Image();
		img.crossOrigin = 'anonymous';
		img.onload  = function () { render(); dialog.showModal(); };
		img.onerror = function () { alert( cwImgEditorData.i18n.noImage ); };
		// Cache-bust so the browser re-fetches the current version
		img.src = url + ( url.indexOf( '?' ) === -1 ? '?' : '&' ) + 'nocache=' + Date.now();
	}

	function render() {
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

	function makeSquare() {
		if ( ! img ) return;
		document.getElementById( 'cwice-size' ).value = Math.max( img.naturalWidth, img.naturalHeight );
		render();
	}

	function closeDialog() {
		dialog.close();
		img = null;
	}

	function save() {
		var data      = cwImgEditorData;
		var statusEl  = document.getElementById( 'cwice-status' );
		var saveBtn   = document.getElementById( 'cwice-save' );

		statusEl.style.color = '';
		statusEl.textContent = data.i18n.saving;
		saveBtn.disabled     = true;

		var quality   = currentMime === 'image/png' ? 1 : 0.92;
		var imageData = canvas.toDataURL( currentMime, quality );

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

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
