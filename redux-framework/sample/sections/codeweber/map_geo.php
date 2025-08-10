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
<script src=\"https://api-maps.yandex.ru/2.1/?apikey=" . esc_attr($yandex_api_key) . "&lang=ru_RU\"></script>
<script>
document.addEventListener(\"DOMContentLoaded\", function () {
    ymaps.ready(function () {
        var coordinatesField = document.querySelector(\"input[name='redux_demo[yandex_coordinates]']\");
        var zoomField = document.querySelector(\"input[name='redux_demo[yandex_zoom]']\");
        var addressField = document.querySelector(\"input[name='redux_demo[yandex_address]']\");

        var coords = coordinatesField?.value.split(',').map(parseFloat);
        if (!coords || coords.length !== 2 || coords.some(isNaN)) coords = [55.76, 37.64];
        var zoom = parseInt(zoomField?.value || \"10\");

        var map = new ymaps.Map(\"yandex-map\", {
            center: coords,
            zoom: zoom,
            controls: [\"zoomControl\", \"searchControl\"]
        });

        var placemark = new ymaps.Placemark(coords, {}, { draggable: true });
        map.geoObjects.add(placemark);

        function updateFields(coords, addressText = null) {
            var coordString = coords.join(\", \");
            if (coordinatesField) {
                coordinatesField.value = coordString;
                coordinatesField.dispatchEvent(new Event(\"input\", { bubbles: true }));
            }
            if (zoomField) {
                zoomField.value = map.getZoom();
                zoomField.dispatchEvent(new Event(\"input\", { bubbles: true }));
            }
            if (addressField) {
                if (addressText) {
                    addressField.value = addressText;
                    addressField.dispatchEvent(new Event(\"input\", { bubbles: true }));
                } else {
                    ymaps.geocode(coords).then(function (res) {
                        var first = res.geoObjects.get(0);
                        if (first) {
                            addressField.value = first.getAddressLine();
                            addressField.dispatchEvent(new Event(\"input\", { bubbles: true }));
                        }
                    });
                }
            }
        }

        placemark.events.add(\"dragend\", function () {
            var newCoords = placemark.geometry.getCoordinates();
            updateFields(newCoords);
        });

        map.events.add(\"click\", function (e) {
            var coords = e.get(\"coords\");
            placemark.geometry.setCoordinates(coords);
            updateFields(coords);
        });

        map.events.add(\"boundschange\", function () {
            if (zoomField) {
                zoomField.value = map.getZoom();
                zoomField.dispatchEvent(new Event(\"input\", { bubbles: true }));
            }
        });

        var searchControl = map.controls.get(\"searchControl\");
        searchControl.events.add(\"resultselect\", function (e) {
            var results = searchControl.getResultsArray();
            var selectedIndex = e.get(\"index\");
            var selectedResult = results[selectedIndex];

            if (selectedResult) {
                var coords = selectedResult.geometry.getCoordinates();
                placemark.geometry.setCoordinates(coords);
                map.setCenter(coords, 16);
                updateFields(coords, selectedResult.properties.get(\"name\"));
            }
        });

        updateFields(coords);
    });
});
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
