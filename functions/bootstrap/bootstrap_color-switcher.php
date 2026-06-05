<?php
/**
 * Floating Theme Color Switcher (demo / sites-for-sale preview)
 *
 * Lets a visitor preview theme colors live by swapping the `theme-color-style`
 * stylesheet. Pure client-side preview stored in sessionStorage — it does NOT
 * write to Redux and does NOT change the admin default (`opt-select-color-theme`).
 *
 * Fully gated by the Redux switch `opt-color-switcher-enabled` (OFF by default).
 * When OFF nothing is printed and no script is registered — zero frontend footprint.
 *
 * Uses Bootstrap classes for layout.
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('codeweber_color_switcher_enabled')) {
	/**
	 * Whether the floating color switcher is enabled via Redux.
	 *
	 * @return bool
	 */
	function codeweber_color_switcher_enabled() {
		if (!class_exists('Redux')) {
			return false;
		}
		global $opt_name;
		if (empty($opt_name)) {
			$opt_name = 'redux_demo';
		}
		return (bool) Redux::get_option($opt_name, 'opt-color-switcher-enabled');
	}
}

if (!function_exists('codeweber_color_switcher_position')) {
	/**
	 * Side the widget is pinned to: 'left' (default) or 'right'.
	 *
	 * @return string
	 */
	function codeweber_color_switcher_position() {
		global $opt_name;
		if (empty($opt_name)) {
			$opt_name = 'redux_demo';
		}
		$pos = class_exists('Redux') ? Redux::get_option($opt_name, 'opt-color-switcher-position') : 'left';
		return ($pos === 'right') ? 'right' : 'left';
	}
}

if (!function_exists('codeweber_color_switcher_colors')) {
	/**
	 * Build the list of selectable colors by scanning the compiled color CSS files.
	 *
	 * Each entry: [ 'name' => slug, 'url' => absolute css url ('' for default),
	 *               'var' => bootstrap color var name used for the swatch fill ].
	 *
	 * @return array
	 */
	function codeweber_color_switcher_colors() {
		$colors = array();

		// "Default" first — no CSS file, reverts to the base theme color (blue).
		$colors[] = array('name' => 'default', 'url' => '', 'var' => 'blue');

		$rel  = 'dist/assets/css/colors';
		$dirs = array();
		if (is_child_theme()) {
			$dirs[] = get_stylesheet_directory() . '/' . $rel;
		}
		$dirs[] = get_template_directory() . '/' . $rel;

		$dir = '';
		foreach ($dirs as $candidate) {
			if (is_dir($candidate)) {
				$dir = $candidate;
				break;
			}
		}

		if ($dir) {
			$files = scandir($dir);
			foreach ($files as $file) {
				if (pathinfo($file, PATHINFO_EXTENSION) !== 'css') {
					continue;
				}
				$name = pathinfo($file, PATHINFO_FILENAME);
				$url  = function_exists('codeweber_get_dist_file_url')
					? codeweber_get_dist_file_url($rel . '/' . $file)
					: false;
				if (!$url) {
					continue;
				}
				$colors[] = array('name' => $name, 'url' => $url, 'var' => $name);
			}
		}

		return $colors;
	}
}

if (!function_exists('codeweber_color_switcher_head')) {
	/**
	 * Early <head> script: applies the visitor's previously chosen color (from
	 * sessionStorage) before first paint, so navigating between pages does not
	 * flash the default color.
	 */
	function codeweber_color_switcher_head() {
		if (!codeweber_color_switcher_enabled()) {
			return;
		}

		$map = array();
		foreach (codeweber_color_switcher_colors() as $c) {
			$map[$c['name']] = $c['url'];
		}
		?>
<script id="cwgb-color-switcher-head">
(function(){
  try {
    var MAP = <?php echo wp_json_encode($map); ?>;
    var c = sessionStorage.getItem('cwThemeColor');
    if (c === null || !(c in MAP)) { return; }
    var id = 'theme-color-style-css';
    var link = document.getElementById(id) || document.querySelector('link[href*="/dist/assets/css/colors/"]');
    var url = MAP[c];
    if (!url) { if (link) { link.parentNode.removeChild(link); } return; }
    if (!link) {
      link = document.createElement('link');
      link.id = id; link.rel = 'stylesheet';
      document.head.appendChild(link);
    }
    link.id = id;
    link.setAttribute('href', url);
  } catch (e) {}
})();
</script>
		<?php
	}
	add_action('wp_head', 'codeweber_color_switcher_head', 999);
}

if (!function_exists('codeweber_color_switcher_widget')) {
	/**
	 * Render the floating color switcher widget. Returns '' when disabled.
	 *
	 * @return string
	 */
	function codeweber_color_switcher_widget() {
		if (!codeweber_color_switcher_enabled()) {
			return '';
		}

		$colors = codeweber_color_switcher_colors();
		if (count($colors) <= 1) {
			return ''; // only "default" — nothing to switch between
		}

		$pos        = codeweber_color_switcher_position();
		$side_style  = ($pos === 'right') ? 'right:1.25rem;' : 'left:1.25rem;';
		$align_class = ($pos === 'right') ? 'align-items-end' : 'align-items-start';

		ob_start();
		?>
<div class="cwgb-color-switcher d-flex flex-column <?php echo esc_attr($align_class); ?>" style="position:fixed;<?php echo $side_style; ?>bottom:1.25rem;z-index:1040;">
	<div class="cwgb-color-switcher-panel d-none flex-wrap gap-2 p-3 mb-2 bg-white rounded shadow" style="max-width:10rem;">
		<?php foreach ($colors as $c) : ?>
			<button type="button"
				class="cwgb-color-swatch border border-2 border-white"
				data-color="<?php echo esc_attr($c['name']); ?>"
				data-href="<?php echo esc_attr($c['url']); ?>"
				style="background-color:var(--bs-<?php echo esc_attr($c['var']); ?>);"
				title="<?php echo esc_attr(ucfirst($c['name'])); ?>"
				aria-label="<?php echo esc_attr(ucfirst($c['name'])); ?>"></button>
		<?php endforeach; ?>
	</div>
	<button type="button" class="btn btn-circle btn-primary cwgb-color-switcher-toggle" aria-label="<?php esc_attr_e('Change theme color', 'codeweber'); ?>">
		<i class="uil uil-palette"></i>
	</button>
</div>
<style>
.cwgb-color-swatch{width:1.75rem;height:1.75rem;border-radius:50%;padding:0;cursor:pointer;transition:transform .15s ease;box-shadow:0 0 0 1px rgba(0,0,0,.1);}
.cwgb-color-swatch:hover{transform:scale(1.15);}
.cwgb-color-swatch.active{box-shadow:0 0 0 2px var(--bs-dark);}
</style>
<script>
(function(){
	var wrap = document.querySelector('.cwgb-color-switcher');
	if (!wrap) { return; }
	var toggle   = wrap.querySelector('.cwgb-color-switcher-toggle');
	var panel    = wrap.querySelector('.cwgb-color-switcher-panel');
	var swatches = wrap.querySelectorAll('.cwgb-color-swatch');
	var KEY = 'cwThemeColor', LINKID = 'theme-color-style-css';

	toggle.addEventListener('click', function(){ panel.classList.toggle('d-none'); });

	function markActive(name){
		swatches.forEach(function(s){
			s.classList.toggle('active', s.getAttribute('data-color') === name);
		});
	}

	function apply(name, href){
		var link = document.getElementById(LINKID) || document.querySelector('link[href*="/dist/assets/css/colors/"]');
		if (!href) {
			if (link) { link.parentNode.removeChild(link); }
		} else {
			if (!link) {
				link = document.createElement('link');
				link.id = LINKID; link.rel = 'stylesheet';
				document.head.appendChild(link);
			}
			link.id = LINKID;
			link.setAttribute('href', href);
		}
		markActive(name);
	}

	swatches.forEach(function(s){
		s.addEventListener('click', function(){
			var name = s.getAttribute('data-color');
			var href = s.getAttribute('data-href');
			try { sessionStorage.setItem(KEY, name); } catch (e) {}
			apply(name, href);
		});
	});

	// Reflect the already-applied color (the head script set the CSS, here we only highlight).
	try {
		var cur = sessionStorage.getItem(KEY);
		if (cur) { markActive(cur); }
	} catch (e) {}
})();
</script>
		<?php
		return ob_get_clean();
	}
}
