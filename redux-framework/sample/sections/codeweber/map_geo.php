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
				'content'  => '<div id="yandex-map" style="width: 100%; height: 400px;"></div>
                               <script src="https://api-maps.yandex.ru/2.1/?apikey=' . esc_attr($yandex_api_key) . '&lang=ru_RU"></script>
                               <script>
                                   document.addEventListener("DOMContentLoaded", function () {
                                       ymaps.ready(init);

                                       function init() {
                                           var coordinatesField = document.querySelector("input[name=\'redux_demo[yandex_coordinates]\']");
                                           var zoomField = document.querySelector("input[name=\'redux_demo[yandex_zoom]\']");
                                           var addressField = document.querySelector("input[name=\'redux_demo[yandex_address]\']");

                                           var coordsString = coordinatesField ? coordinatesField.value : "55.76, 37.64";
                                           var coords = coordsString.split(", ").map(function(coord) { return parseFloat(coord); });

                                           if (coords.length !== 2 || isNaN(coords[0]) || isNaN(coords[1])) {
                                               coords = [55.76, 37.64];
                                           }

                                           var zoom = zoomField ? parseInt(zoomField.value) : 10;

                                           var map = new ymaps.Map("yandex-map", {
                                               center: coords,
                                               zoom: zoom,
                                           });

                                           var placemark = new ymaps.Placemark(coords, {}, { draggable: true });
                                           map.geoObjects.add(placemark);

                                           function geocodeCoordinates(coords) {
                                               ymaps.geocode(coords).then(function (result) {
                                                   var firstGeoObject = result.geoObjects.get(0);
                                                   var address = firstGeoObject ? firstGeoObject.getAddressLine() : "Адрес не найден";

                                                   if (addressField) {
                                                       addressField.value = address;
                                                       var event = new Event("input", { bubbles: true });
                                                       addressField.dispatchEvent(event);
                                                   }
                                               });
                                           }

                                           function updateCoordinates(newCoords) {
                                               var coordsString = newCoords.join(", ");
                                               if (coordinatesField) {
                                                   coordinatesField.value = coordsString;
                                                   var event = new Event("input", { bubbles: true });
                                                   coordinatesField.dispatchEvent(event);
                                               }
                                               geocodeCoordinates(newCoords);
                                           }

                                           function updateZoom(newZoom) {
                                               if (zoomField) {
                                                   zoomField.value = newZoom;
                                                   var event = new Event("input", { bubbles: true });
                                                   zoomField.dispatchEvent(event);
                                               }
                                           }

                                           placemark.events.add("dragend", function () {
                                               var newCoords = placemark.geometry.getCoordinates();
                                               updateCoordinates(newCoords);
                                           });

                                           map.events.add("click", function (e) {
                                               var coords = e.get("coords");
                                               placemark.geometry.setCoordinates(coords);
                                               updateCoordinates(coords);
                                           });

                                           map.events.add("boundschange", function (e) {
                                               updateZoom(map.getZoom());
                                           });

                                           geocodeCoordinates(coords);
                                       }
                                   });
                               </script>',
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
