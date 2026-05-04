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
    ymaps3.ready.then(function() {
        var YMap = ymaps3.YMap, YMapDefaultSchemeLayer = ymaps3.YMapDefaultSchemeLayer,
            YMapDefaultFeaturesLayer = ymaps3.YMapDefaultFeaturesLayer,
            YMapMarker = ymaps3.YMapMarker, YMapListener = ymaps3.YMapListener;

        var coordField   = document.querySelector(\"input[name='redux_demo[yandex_coordinates]']\");
        var zoomField    = document.querySelector(\"input[name='redux_demo[yandex_zoom]']\");
        var addressField = document.querySelector(\"input[name='redux_demo[yandex_address]']\");

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
            onClick: function(obj, coords) {
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
				'id'       => 'yandex_address',
				'type'     => 'text',
				'title'    => esc_html__('Address', 'codeweber'),
				'subtitle' => esc_html__('Selected point address', 'codeweber'),
				'default'  => '',
			),
		),
	)
);
