<?php
/**
 * Component: Fullscreen Website Preview Modal
 *
 * Shared Quick View modal for IT/Web archive templates.
 * Trigger: <button data-bs-toggle="modal" data-bs-target="#cw-preview-modal"
 *                  data-website-url="..." data-website-title="...">
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;
?>
<style>
/* ── Fullscreen preview modal ── */
#cw-preview-modal .modal-dialog { margin: 0; max-width: 100%; height: 100%; }
#cw-preview-modal .modal-content { height: 100%; border: 0; border-radius: 0; background: transparent; }
#cw-preview-modal .modal-body { padding: 0; display: flex; flex-direction: row; height: 100%; overflow: hidden; }
/* ── Iframe area ── */
.cw-preview-content { flex: 1; min-height: 0; overflow: auto; }
/* ── Right sidebar ── */
.cw-preview-bar {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 8px;
	padding: 12px 8px;
	width: 88px;
	background: #2b2b2b;
	color: #fff;
	flex-shrink: 0;
}
.cw-preview-title {
	font-size: 1.25rem;
	font-weight: 600;
	color: #fff;
	text-align: center;
	padding: 16px 20px 8px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	max-width: 100%;
	flex-shrink: 0;
}
.cw-preview-devices { display: flex; flex-direction: column; gap: 6px; }
.cw-preview-bar-end { display: flex; flex-direction: column; align-items: center; gap: 6px; flex-shrink: 0; }
.cw-preview-bar-end .btn { color: rgba(255,255,255,.6); }
.cw-preview-bar-end .btn:hover { color: #fff; }
/* ── Device thumb buttons ── */
.cw-preview-thumb-btn {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 4px;
	width: 72px;
	height: 80px;
	background: rgba(255,255,255,.1);
	border: 1px solid rgba(255,255,255,.15);
	border-radius: 8px;
	color: rgba(255,255,255,.55);
	cursor: pointer;
	padding: 0;
	transition: background .15s, border-color .15s, color .15s;
}
.cw-preview-thumb-btn i {
	font-size: 28px;
	line-height: 1;
	width: 28px;
	height: 28px;
	display: flex;
	align-items: center;
	justify-content: center;
}
.cw-preview-thumb-btn span {
	font-size: 11px;
	font-weight: 500;
	letter-spacing: .02em;
}
.cw-preview-thumb-btn:hover,
.cw-preview-thumb-btn.active {
	background: rgba(255,255,255,.22);
	border-color: rgba(255,255,255,.4);
	color: #fff;
}
/* === Desktop: MacBook Pro (16:9) === */
.cw-preview-frame-wrap[data-device="desktop"] {
	width: min(96vw, calc((100vh - 100px) * 16 / 9));
	max-width: 1600px;
	aspect-ratio: 16 / 9;
	margin: 0 auto;
	position: relative;
	background: #1d1d1f;
	border-radius: 12px 12px 0 0;
	box-shadow: 0 0 0 1.5px #3a3a3c, 0 0 0 3px #0a0a0a;
	padding: 14px 12px 0;
	flex-shrink: 0;
}
.cw-preview-frame-wrap[data-device="desktop"]::before {
	content: '';
	position: absolute;
	top: 5px; left: 50%;
	transform: translateX(-50%);
	width: 7px; height: 7px;
	background: #3a3a3c;
	border-radius: 50%;
}
.cw-preview-frame-wrap[data-device="desktop"] iframe {
	width: 100%; height: 100%;
	border: 0; border-radius: 3px 3px 0 0;
	display: block;
}
.cw-device-base { display: none; }
.cw-preview-frame-wrap[data-device="desktop"] ~ .cw-device-base {
	display: block;
	width: min(96vw, calc((100vh - 100px) * 16 / 9));
	max-width: 1600px;
	height: 26px;
	background: linear-gradient(180deg, #1d1d1f 0%, #131315 100%);
	border-radius: 0 0 6px 6px;
	box-shadow: 0 0 0 1.5px #3a3a3c, 0 0 0 3px #0a0a0a, 0 12px 40px rgba(0,0,0,.7);
	margin: 0 auto;
	flex-shrink: 0;
	position: relative;
}
.cw-preview-frame-wrap[data-device="desktop"] ~ .cw-device-base::after {
	content: '';
	display: block;
	width: 14%; height: 5px;
	background: #0a0a0a;
	border-radius: 0 0 4px 4px;
	margin: 0 auto;
}
/* === Tablet: iPad Pro 11" === */
.cw-preview-frame-wrap[data-device="tablet"] {
	height: min(calc(100% - 60px), 1180px);
	width: auto;
	aspect-ratio: 820 / 1180;
	max-width: calc(100% - 60px);
	margin: 30px auto;
	position: relative;
	background: #1c1c1e;
	border-radius: 24px;
	box-shadow:
		0 0 0 1px #3a3a3c,
		0 0 0 2.5px #0a0a0a,
		inset 0 0 0 1px #2c2c2e,
		0 30px 80px rgba(0,0,0,.9);
	padding: 22px 16px;
	flex-shrink: 0;
}
.cw-preview-frame-wrap[data-device="tablet"]::before {
	content: '';
	position: absolute;
	top: 10px; left: 50%;
	transform: translateX(-50%);
	width: 8px; height: 8px;
	background: #3a3a3c;
	border-radius: 50%;
}
.cw-preview-frame-wrap[data-device="tablet"]::after {
	content: '';
	position: absolute;
	top: 22%; right: -3.5px;
	width: 3.5px; height: 52px;
	background: #2c2c2e;
	border-radius: 0 2px 2px 0;
}
.cw-preview-frame-wrap[data-device="tablet"] iframe {
	width: 100%; height: 100%;
	border: 0; border-radius: 6px;
	display: block;
}
.cw-preview-frame-wrap[data-device="tablet"] .cw-device-btn-l {
	display: block;
	position: absolute;
	left: -3.5px; top: 22%;
	width: 3.5px; height: 42px;
	background: #2c2c2e;
	border-radius: 2px 0 0 2px;
}
.cw-preview-frame-wrap[data-device="tablet"] .cw-device-btn-l::after {
	content: '';
	position: absolute;
	left: 0; top: 58px;
	width: 3.5px; height: 42px;
	background: #2c2c2e;
	border-radius: 2px 0 0 2px;
}
/* === Mobile: iPhone 14 Pro === */
.cw-preview-frame-wrap[data-device="mobile"] {
	height: min(calc(100% - 60px), 852px);
	width: auto;
	aspect-ratio: 393 / 852;
	max-width: calc(100% - 60px);
	margin: 30px auto;
	position: relative;
	background: #000;
	border-radius: 56px;
	box-shadow:
		0 0 0 1px #3a3a3c,
		0 0 0 2.5px #1a1a1a,
		inset 0 0 0 1px #1c1c1e,
		0 30px 80px rgba(0,0,0,.9);
	padding: 58px 18px 34px;
	flex-shrink: 0;
}
.cw-preview-frame-wrap[data-device="mobile"]::before {
	content: '';
	position: absolute;
	top: 12px; left: 50%;
	transform: translateX(-50%);
	width: 120px; height: 34px;
	background: #000;
	border-radius: 18px;
	z-index: 2;
}
.cw-preview-frame-wrap[data-device="mobile"]::after {
	content: '';
	position: absolute;
	bottom: 9px; left: 50%;
	transform: translateX(-50%);
	width: 130px; height: 5px;
	background: rgba(255,255,255,.3);
	border-radius: 3px;
}
.cw-preview-frame-wrap[data-device="mobile"] iframe {
	width: 100%; height: 100%;
	border: 0; border-radius: 38px;
	display: block; background: #fff;
}
.cw-preview-frame-wrap[data-device="mobile"] .cw-device-btn-l {
	display: block;
	position: absolute;
	left: -4px; top: 108px;
	width: 4px; height: 28px;
	background: #2c2c2e;
	border-radius: 2px 0 0 2px;
}
.cw-preview-frame-wrap[data-device="mobile"] .cw-device-btn-l::before {
	content: '';
	position: absolute;
	left: 0; top: 52px;
	width: 4px; height: 62px;
	background: #2c2c2e;
	border-radius: 2px 0 0 2px;
}
.cw-preview-frame-wrap[data-device="mobile"] .cw-device-btn-l::after {
	content: '';
	position: absolute;
	left: 0; top: 130px;
	width: 4px; height: 62px;
	background: #2c2c2e;
	border-radius: 2px 0 0 2px;
}
.cw-preview-frame-wrap[data-device="mobile"] .cw-device-btn-r {
	display: block;
	position: absolute;
	right: -4px; top: 160px;
	width: 4px; height: 80px;
	background: #2c2c2e;
	border-radius: 0 2px 2px 0;
}
.cw-device-btn-l, .cw-device-btn-r { display: none; }
</style>

<!-- Fullscreen website preview modal -->
<div class="modal fade" id="cw-preview-modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-fullscreen">
		<div class="modal-content">
			<div class="modal-body">

				<div class="cw-preview-content d-flex flex-column align-items-center justify-content-center">
					<h2 class="cw-preview-title" id="cw-preview-title"></h2>
					<div class="cw-preview-frame-wrap" id="cw-preview-frame-wrap" data-device="desktop">
						<span class="cw-device-btn-l" aria-hidden="true"></span>
						<span class="cw-device-btn-r" aria-hidden="true"></span>
						<iframe id="cw-preview-frame" src="" title="" loading="lazy"></iframe>
					</div>
					<div class="cw-device-base" id="cw-device-base"></div>
				</div>

				<div class="cw-preview-bar">
					<div class="cw-preview-devices">
						<button class="cw-preview-thumb-btn active" data-device="desktop">
							<i class="uil uil-desktop"></i>
							<span><?php esc_html_e( 'Desktop', 'codeweber' ); ?></span>
						</button>
						<button class="cw-preview-thumb-btn" data-device="tablet">
							<i class="uil uil-tablet"></i>
							<span><?php esc_html_e( 'Tablet', 'codeweber' ); ?></span>
						</button>
						<button class="cw-preview-thumb-btn" data-device="mobile">
							<i class="uil uil-mobile-android"></i>
							<span><?php esc_html_e( 'Mobile', 'codeweber' ); ?></span>
						</button>
					</div>
					<div class="cw-preview-bar-end">
						<a href="#" id="cw-preview-ext-link" target="_blank" rel="noopener noreferrer"
						   class="btn btn-circle btn-sm btn-frost has-ripple" title="<?php esc_attr_e( 'Open website', 'codeweber' ); ?>">
							<i class="uil uil-external-link-alt"></i>
						</a>
						<button type="button" class="btn btn-circle btn-sm btn-frost has-ripple" data-bs-dismiss="modal"
								aria-label="<?php esc_attr_e( 'Close', 'codeweber' ); ?>">
							<i class="uil uil-times"></i>
						</button>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

<script>
(function () {
	var previewModal      = document.getElementById('cw-preview-modal');
	var previewTitle      = document.getElementById('cw-preview-title');
	var previewExtLink    = document.getElementById('cw-preview-ext-link');
	var previewFrame      = document.getElementById('cw-preview-frame');
	var previewFrameWrap  = document.getElementById('cw-preview-frame-wrap');
	var previewDeviceBtns = previewModal ? previewModal.querySelectorAll('[data-device]') : [];

	if (!previewModal) return;

	previewModal.addEventListener('show.bs.modal', function (e) {
		var trigger = e.relatedTarget;
		if (!trigger) return;
		var url   = trigger.getAttribute('data-website-url') || '';
		var title = trigger.getAttribute('data-website-title') || '';
		if (previewTitle)   previewTitle.textContent = title;
		if (previewExtLink) previewExtLink.href = url;
		if (previewFrame)   { previewFrame.src = url; previewFrame.title = title; }
	});

	previewModal.addEventListener('hidden.bs.modal', function () {
		if (previewFrame)     previewFrame.src = '';
		if (previewTitle)     previewTitle.textContent = '';
		if (previewExtLink)   previewExtLink.href = '#';
		if (previewFrameWrap) previewFrameWrap.dataset.device = 'desktop';
		previewDeviceBtns.forEach(function (b) {
			b.classList.toggle('active', b.dataset.device === 'desktop');
		});
	});

	previewDeviceBtns.forEach(function (btn) {
		btn.addEventListener('click', function () {
			if (previewFrameWrap) previewFrameWrap.dataset.device = btn.dataset.device;
			previewDeviceBtns.forEach(function (b) {
				b.classList.toggle('active', b.dataset.device === btn.dataset.device);
			});
		});
	});
})();
</script>
