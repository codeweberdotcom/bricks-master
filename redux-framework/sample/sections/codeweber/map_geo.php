<?php

// Получение API ключа из настроек Redux
$yandex_api_key = Redux::get_option($opt_name, 'yandexapi');

Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Geo && Map", "codeweber"),
		'id'               => 'geomap',
		'desc'             => esc_html__("Settings Geo && Map", "codeweber"),
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'           => array(
			// Поле с картой
			array(
				'id'       => 'yandex_map',
				'type'     => 'raw',
				'title'    => esc_html__('Yandex Map', 'codeweber'),
				'subtitle' => esc_html__('Choose a point on the map', 'codeweber'),
				'content' => "<div id=\"yandex-map\" style=\"width: 100%; height: 400px;\"></div>
<script src=\"https://api-maps.yandex.ru/v3/?apikey=" . esc_attr($yandex_api_key) . "&lang=ru_RU\"></script>
<script>
(function() {
    var apiKey = '" . esc_js($yandex_api_key) . "';
    var geocodeUrl = 'https://geocode-maps.yandex.ru/1.x/?apikey=' + encodeURIComponent(apiKey) + '&format=json&lang=ru_RU';
    ymaps3.ready.then(function() {
        var YMap = ymaps3.YMap, YMapDefaultSchemeLayer = ymaps3.YMapDefaultSchemeLayer,
            YMapDefaultFeaturesLayer = ymaps3.YMapDefaultFeaturesLayer,
            YMapMarker = ymaps3.YMapMarker, YMapListener = ymaps3.YMapListener;

        var coordField   = document.querySelector(\"input[name='redux_demo[yandex_coordinates]']\");
        var zoomField    = document.querySelector(\"input[name='redux_demo[yandex_zoom]']\");
        var addressField = document.querySelector(\"input[name='redux_demo[yandex_address]']\");
        var searchInput  = addressField;

        // Redux stores coordinates as 'lat, lng'
        var lat = 55.76, lng = 37.64, zoom = 10;
        if (coordField && coordField.value) {
            var p = coordField.value.split(',').map(parseFloat);
            if (p.length === 2 && !p.some(isNaN)) { lat = p[0]; lng = p[1]; }
        }
        if (zoomField && zoomField.value) zoom = parseInt(zoomField.value) || 10;

        // v3: center = [lng, lat]
        var map = new YMap(document.getElementById('yandex-map'), {
            location: { center: [lng, lat], zoom: zoom }
        });
        map.addChild(new YMapDefaultSchemeLayer());
        map.addChild(new YMapDefaultFeaturesLayer());

        var el = document.createElement('div');
        el.style.cssText = 'cursor:grab;width:28px;height:28px;transform:translate(-50%,-100%)';
        el.innerHTML = '<svg viewBox=\"0 0 24 24\" fill=\"#d63638\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z\"/></svg>';

        var marker = new YMapMarker({
            coordinates: [lng, lat],
            draggable: true,
            onDragEnd: function(coords) { syncFields(coords[1], coords[0]); }
        }, el);
        map.addChild(marker);

        map.addChild(new YMapListener({
            onClick: function(obj, event) {
                var coords = event && event.coordinates ? event.coordinates : null;
                if (!coords) return;
                marker.update({ coordinates: coords });
                syncFields(coords[1], coords[0]);
            }
        }));

        map.addChild(new YMapListener({
            onActionEnd: function() {
                if (zoomField) { zoomField.value = Math.round(map.zoom); zoomField.dispatchEvent(new Event('input',{bubbles:true})); }
            }
        }));

        function syncFields(latVal, lngVal) {
            // Store as 'lat, lng' for backward compatibility
            if (coordField)   { coordField.value = latVal + ', ' + lngVal; coordField.dispatchEvent(new Event('input',{bubbles:true})); }
            if (zoomField)    { zoomField.value = Math.round(map.zoom);    zoomField.dispatchEvent(new Event('input',{bubbles:true})); }
            if (addressField) {
                fetch('https://geocode-maps.yandex.ru/1.x/?apikey=' + encodeURIComponent(apiKey) + '&format=json&geocode=' + lngVal + ',' + latVal + '&results=1&lang=ru_RU')
                    .then(function(r) { return r.json(); })
                    .then(function(d) {
                        var fm = d.response && d.response.GeoObjectCollection && d.response.GeoObjectCollection.featureMember;
                        if (fm && fm.length) { addressField.value = fm[0].GeoObject.metaDataProperty.GeocoderMetaData.text; addressField.dispatchEvent(new Event('input',{bubbles:true})); }
                    });
            }
        }

        function geocodeAndMove(query) {
            if (!query) return;
            fetch(geocodeUrl + '&geocode=' + encodeURIComponent(query) + '&results=1')
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    var fm = d.response && d.response.GeoObjectCollection && d.response.GeoObjectCollection.featureMember;
                    if (!fm || !fm.length) return;
                    var pos = fm[0].GeoObject.Point.pos.split(' ');
                    var fLng = parseFloat(pos[0]), fLat = parseFloat(pos[1]);
                    if (isNaN(fLat) || isNaN(fLng)) return;
                    marker.update({ coordinates: [fLng, fLat] });
                    map.update({ location: { center: [fLng, fLat], zoom: 15 } });
                    syncFields(fLat, fLng);
                }).catch(function() {});
        }

        function initSuggest(input) {
            var wrap = input.parentNode;
            var drop = document.createElement('div');
            drop.style.cssText = 'display:none;position:absolute;z-index:99999;left:0;right:0;top:100%;background:#fff;border:1px solid #c3c4c7;border-top:none;border-radius:0 0 4px 4px;box-shadow:0 4px 8px rgba(0,0,0,.12);max-height:220px;overflow-y:auto;font-size:13px;';
            wrap.style.position = 'relative';
            wrap.appendChild(drop);
            var timer, active = -1;
            function hide() { drop.style.display = 'none'; active = -1; }
            function hl(i) { active = i; Array.from(drop.children).forEach(function(c,j){c.style.background=j===i?'#f0f7ff':'';}); }
            function pick(t, s) { input.value = t + (s ? ', '+s : ''); hide(); geocodeAndMove(input.value); input.dispatchEvent(new Event('input',{bubbles:true})); }
            function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
            input.addEventListener('input', function() {
                clearTimeout(timer);
                var q = input.value.trim();
                if (q.length < 2) { hide(); return; }
                timer = setTimeout(function() {
                    fetch('https://suggest-maps.yandex.ru/v1/suggest?apikey=' + encodeURIComponent(apiKey) + '&text=' + encodeURIComponent(q) + '&lang=ru_RU&results=5&types=house,street,locality')
                        .then(function(r) { return r.json(); })
                        .then(function(d) {
                            drop.innerHTML = '';
                            var items = (d.results || []).filter(function(r) { return r.title && r.title.text; });
                            if (!items.length) { hide(); return; }
                            items.forEach(function(r, i) {
                                var t = r.title.text, s = r.subtitle && r.subtitle.text ? r.subtitle.text : '';
                                var div = document.createElement('div');
                                div.style.cssText = 'padding:7px 12px;cursor:pointer;border-bottom:1px solid #f0f0f1;line-height:1.3;';
                                div.innerHTML = '<span style=\"font-weight:600\">'+esc(t)+'</span>'+(s?'<br><span style=\"color:#777;font-size:12px\">'+esc(s)+'</span>':'');
                                div.addEventListener('mousedown', function(e) { e.preventDefault(); pick(t, s); });
                                div.addEventListener('mouseover', function() { hl(i); });
                                drop.appendChild(div);
                            });
                            drop.style.display = 'block';
                        }).catch(function() {});
                }, 250);
            });
            input.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown') { e.preventDefault(); hl(Math.min(active+1, drop.children.length-1)); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); hl(Math.max(active-1, 0)); }
                else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (active >= 0 && drop.children[active]) drop.children[active].dispatchEvent(new MouseEvent('mousedown',{bubbles:true}));
                    else geocodeAndMove(input.value.trim());
                    hide();
                } else if (e.key === 'Escape') { hide(); }
            });
            input.addEventListener('blur', function() { setTimeout(hide, 200); });
        }

        if (searchInput) initSuggest(searchInput);
    });
})();
</script>",

			),
			array(
				'id'       => 'yandex_coordinates',
				'type'     => 'text',
				'title'    => esc_html__('Coordinates', 'codeweber'),
				'subtitle' => esc_html__('Selected point coordinates', 'codeweber'),
				'default'  => '',
			),
			array(
				'id'       => 'yandex_zoom',
				'type'     => 'text',
				'title'    => esc_html__('Zoom Level', 'codeweber'),
				'subtitle' => esc_html__('Zoom level of the map', 'codeweber'),
				'default'  => '10',
			),
			array(
				'id'       => 'yandex_maps_marker_zoom',
				'type'     => 'text',
				'title'    => esc_html__('Marker Click Zoom', 'codeweber'),
				'subtitle' => esc_html__('Zoom level when clicking on a marker (v3 maps)', 'codeweber'),
				'default'  => '15',
			),
			array(
				'id'       => 'yandex_address',
				'type'     => 'text',
				'title'    => esc_html__('Address', 'codeweber'),
				'subtitle' => esc_html__('Selected point address', 'codeweber'),
				'default'  => '',
			),
		),
	)
);
