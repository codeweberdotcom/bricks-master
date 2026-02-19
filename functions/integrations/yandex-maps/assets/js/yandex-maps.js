/**
 * CodeWeber Yandex Maps JavaScript
 * 
 * @package Codeweber
 * @version 1.0.0
 */

(function() {
    'use strict';

    /**
     * Основной класс для работы с Яндекс картами
     */
    class CodeweberYandexMaps {
        constructor(config, wrapper) {
            this.config = config;
            this.wrapper = wrapper || null;
            this.map = null;
            this.placemarks = {};
            this.clusterer = null;
            this.route = null;
            this.sidebar = null;
            this.filters = {};

            this.init();
        }

        init() {
            if (typeof ymaps === 'undefined') {
                console.error('Yandex Maps API is not loaded');
                return;
            }

            ymaps.ready(() => {
                try {
                    this.createMap();
                    this.addMarkers();
                    this.initSidebar();
                    this.initFilters();
                    this.initRoute();
                } catch (err) {
                    // ignore
                }
                this.hideLoader();
            });
        }

        /**
         * Создание карты
         */
        createMap() {
            const mapElement = this.wrapper
                ? this.wrapper.querySelector('#' + CSS.escape(this.config.id))
                : document.getElementById(this.config.id);
            if (!mapElement) {
                console.error('Map element not found:', this.config.id);
                return;
            }

            // Применяем кастомные стили к обертке, если заданы
            if (this.config.customStyle && typeof this.config.customStyle === 'string') {
                try {
                    mapElement.parentElement.style.cssText += this.config.customStyle;
                } catch (e) {
                    console.error('Invalid customStyle for map wrapper', e);
                }
            }

            // Формируем список контролов с учетом дополнительных опций
            const baseControls = Array.isArray(this.config.controls) ? this.config.controls.slice() : [];
            if (this.config.geolocationControl) {
                baseControls.push('geolocationControl');
            }
            if (this.config.routeButton) {
                baseControls.push('routeButtonControl');
            }

            const container = this.wrapper ? mapElement : this.config.id;
            this.map = new ymaps.Map(container, {
                center: this.config.center,
                zoom: this.config.zoom,
                type: this.config.mapType,
                controls: baseControls
            });

            var wrapperEl = this.wrapper || mapElement.closest('.codeweber-yandex-map-wrapper');
            if (wrapperEl) {
                wrapperEl._cwgbYandexMapInstance = this;
            }

            // Применение кастомного стиля из JSON, если задан
            if (this.config.styleJson && typeof this.config.styleJson === 'string' && this.config.styleJson.trim() !== '') {
                try {
                    const styleJson = JSON.parse(this.config.styleJson);
                    // Применяем кастомный стиль через options.set('customMapStyle', ...)
                    // Yandex Maps API 2.1 ожидает массив правил стиля
                    if (Array.isArray(styleJson)) {
                        this.map.options.set('customMapStyle', styleJson);
                    } else if (styleJson.styles && Array.isArray(styleJson.styles)) {
                        this.map.options.set('customMapStyle', styleJson.styles);
                    } else if (styleJson.preset && styleJson.options) {
                        // Если передан объект с preset и options (старый формат)
                        this.map.setType(styleJson);
                    } else {
                        // Пробуем применить как массив
                        this.map.options.set('customMapStyle', [styleJson]);
                    }
                } catch (e) {
                    console.error('Error parsing or applying styleJson:', e);
                }
            }

            // Настройки карты
            if (!this.config.enableScrollZoom) {
                this.map.behaviors.disable('scrollZoom');
            }
            if (!this.config.enableDrag) {
                this.map.behaviors.disable('drag');
            }

            // Дополнительные поведения
            if (typeof this.config.enableDblClickZoom !== 'undefined' && !this.config.enableDblClickZoom) {
                this.map.behaviors.disable('dblClickZoom');
            }
            if (typeof this.config.enableMultiTouch !== 'undefined' && !this.config.enableMultiTouch) {
                this.map.behaviors.disable('multiTouch');
            }

            // Настройки авто-прокрутки для балунов и подсказок
            if (this.config.markerBehavior) {
                if (typeof this.config.markerBehavior.hintAutoPan !== 'undefined') {
                    this.map.options.set('hintAutoPan', !!this.config.markerBehavior.hintAutoPan);
                }
                if (typeof this.config.markerBehavior.balloonAutoPan !== 'undefined') {
                    this.map.options.set('balloonAutoPan', !!this.config.markerBehavior.balloonAutoPan);
                }
            }

            // Инициализация кластеризатора
            if (this.config.clusterer && this.config.clusterer.enabled) {
                this.clusterer = new ymaps.Clusterer({
                    preset: this.config.clusterer.preset || 'islands#invertedVioletClusterIcons',
                    groupByCoordinates: false,
                    clusterDisableClickZoom: true,
                    clusterHideIconOnBalloonOpen: false,
                    geoObjectHideIconOnBalloonOpen: false
                });
                this.map.geoObjects.add(this.clusterer);
            }
        }

        /**
         * Добавление маркеров на карту
         */
        addMarkers() {
            if (!this.map || !this.config.markers || this.config.markers.length === 0) {
                return;
            }

            const markers = this.config.markers;
            const markerSettings = this.config.markerSettings || {};

            markers.forEach((markerData) => {
                const placemark = this.createPlacemark(markerData, markerSettings);
                
                if (this.clusterer) {
                    this.clusterer.add(placemark);
                } else {
                    this.map.geoObjects.add(placemark);
                }

                this.placemarks[markerData.id] = placemark;
            });

            // Автоматическая подгонка границ
            if (this.config.autoFitBounds && markers.length > 0) {
                this.fitBounds();
            }

            // Автоматическое открытие балуна для первого маркера, если включено в настройках
            if (this.config.markerBehavior && this.config.markerBehavior.autoOpenBalloon && markers.length > 0) {
                const firstId = markers[0].id;
                const firstPlacemark = this.placemarks[firstId];
                if (firstPlacemark) {
                    const coords = firstPlacemark.geometry.getCoordinates();
                    // Центрируем карту мягко и открываем балун
                    this.map.setCenter(coords, this.map.getZoom(), { duration: 300 })
                        .then(() => {
                            firstPlacemark.balloon.open();
                        });
                }
            }
        }

        /**
         * Создание маркера
         */
        createPlacemark(markerData, settings) {
            const coords = [markerData.latitude, markerData.longitude];
            
            // Настройки иконки
            let iconOptions = {};
            
            // Проверяем, есть ли индивидуальные настройки для маркера
            const markerIcon = markerData.icon || settings;
            
            if (markerIcon.type === 'logo' && markerIcon.logo) {
                // Кастомная иконка с логотипом
                iconOptions = this.createLogoIcon(markerIcon.logo, markerIcon.logoSize || 40);
            } else if (markerIcon.type === 'custom' && markerIcon.color) {
                // Кастомный цвет
                iconOptions = {
                    preset: 'islands#circleIcon',
                    iconColor: markerIcon.color
                };
            } else {
                // Стандартный пресет
                iconOptions = {
                    preset: markerIcon.preset || 'islands#redDotIcon'
                };
            }

            // Содержимое балуна
            const balloonContent = this.buildBalloonContent(markerData);

            // Опции балуна / подсказок
            const balloonOptions = {};
            if (this.config.markerBehavior) {
                if (typeof this.config.markerBehavior.openBalloonOnClick !== 'undefined') {
                    balloonOptions.openBalloonOnClick = !!this.config.markerBehavior.openBalloonOnClick;
                }
            }
            if (this.config.balloon) {
                if (typeof this.config.balloon.closeButton !== 'undefined') {
                    balloonOptions.balloonCloseButton = !!this.config.balloon.closeButton;
                }
                if (typeof this.config.balloon.autoPan !== 'undefined') {
                    balloonOptions.balloonAutoPan = !!this.config.balloon.autoPan;
                }
            }

            const placemark = new ymaps.Placemark(
                coords,
                {
                    balloonContentHeader: markerData.balloonContentHeader || markerData.title || '',
                    balloonContentBody: balloonContent,
                    hintContent: markerData.hintContent || markerData.title || ''
                },
                Object.assign({}, iconOptions, balloonOptions)
            );

            // Обработчик клика на маркер
            placemark.events.add('click', () => {
                this.onMarkerClick(markerData.id);
            });

            return placemark;
        }

        /**
         * Создание иконки с логотипом
         * Использует SVG для создания круглого маркера с логотипом
         */
        createLogoIcon(logoUrl, size = 40) {
            // Используем SVG для создания иконки
            const iconSize = size + 10;
            const padding = 5;
            
            // Создаем SVG с круглым фоном и логотипом
            const svg = `
                <svg width="${iconSize}" height="${iconSize}" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="${iconSize / 2}" cy="${iconSize / 2}" r="${iconSize / 2 - 1}" fill="#ffffff" stroke="#cccccc" stroke-width="2"/>
                    <image href="${logoUrl}" x="${padding}" y="${padding}" width="${size}" height="${size}" preserveAspectRatio="xMidYMid meet"/>
                </svg>
            `;
            
            const svgBlob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(svgBlob);
            
            return {
                iconLayout: 'default#image',
                iconImageHref: url,
                iconImageSize: [iconSize, iconSize],
                iconImageOffset: [-iconSize / 2, -iconSize / 2]
            };
        }

        /**
         * Построение содержимого балуна
         */
        buildBalloonContent(markerData) {
            let content = '';

            if (markerData.balloonContent) {
                return markerData.balloonContent;
            }

            // Получаем настройки полей балуна
            const balloonFields = this.config.balloon?.fields || {
                showCity: true,
                showAddress: true,
                showPhone: true,
                showWorkingHours: true,
                showLink: true,
                showDescription: false,
            };

            // Стандартный шаблон балуна: ключевая информация об офисе
            if (balloonFields.showCity && markerData.city) {
                content += `<div style="margin-bottom: 8px;"><strong>${codeweberYandexMaps.i18n.city}:</strong><br>${markerData.city}</div>`;
            }
            if (balloonFields.showAddress && markerData.address) {
                content += `<div style="margin-bottom: 8px;"><strong>${codeweberYandexMaps.i18n.address}:</strong><br>${markerData.address}</div>`;
            }
            if (balloonFields.showPhone && markerData.phone) {
                content += `<div style="margin-bottom: 8px;"><strong>${codeweberYandexMaps.i18n.phone}:</strong><br><a href="tel:${markerData.phone.replace(/[^0-9+]/g, '')}">${markerData.phone}</a></div>`;
            }
            if (balloonFields.showWorkingHours && markerData.workingHours) {
                content += `<div style="margin-bottom: 8px;"><strong>${codeweberYandexMaps.i18n.workingHours}:</strong><br>${markerData.workingHours}</div>`;
            }
            if (balloonFields.showDescription && markerData.description) {
                content += `<div style="margin-bottom: 8px;">${markerData.description}</div>`;
            }
            if (balloonFields.showLink && markerData.link) {
                content += `<div style="margin-top: 10px;"><a href="${markerData.link}" style="display: inline-block; padding: 6px 12px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 4px;">${codeweberYandexMaps.i18n.viewDetails}</a></div>`;
            }

            return content || markerData.title || '';
        }

        /**
         * Обработчик клика на маркер
         */
        onMarkerClick(markerId) {
            // Подсветка в сайдбаре
            if (this.sidebar) {
                this.highlightSidebarItem(markerId);
            }
        }

        /**
         * Инициализация бокового меню
         */
        initSidebar() {
            if (!this.config.sidebar || !this.config.sidebar.show) {
                return;
            }

            const mapElement = this.wrapper
                ? this.wrapper.querySelector('#' + CSS.escape(this.config.id))
                : document.getElementById(this.config.id);
            if (!mapElement) return;

            const sidebar = document.createElement('div');
            sidebar.className = `codeweber-map-sidebar codeweber-map-sidebar-${this.config.sidebar.position}`;
            
            // Заголовок
            if (this.config.sidebar.title) {
                const title = document.createElement('div');
                title.className = 'codeweber-map-sidebar-title';
                title.textContent = this.config.sidebar.title;
                sidebar.appendChild(title);
            }

            // Список маркеров
            const list = document.createElement('div');
            list.className = 'codeweber-map-sidebar-list';
            list.id = `${this.config.id}-sidebar-list`;

            this.config.markers.forEach((marker) => {
                const item = this.createSidebarItem(marker);
                list.appendChild(item);
            });

            sidebar.appendChild(list);

            // Кнопка закрытия для мобильных
            const closeBtn = document.createElement('button');
            closeBtn.className = 'codeweber-map-sidebar-close d-md-none';
            closeBtn.innerHTML = '<i class="uil uil-times"></i>';
            closeBtn.addEventListener('click', () => {
                sidebar.classList.remove('active');
            });
            sidebar.appendChild(closeBtn);

            // Кнопка открытия для мобильных
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'codeweber-map-sidebar-toggle btn-icon btn-icon-start btn btn-sm btn-primary d-md-none';
            toggleBtn.innerHTML = `<i class="uil uil-list-ul"></i> ${codeweberYandexMaps.i18n.offices}`;
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.add('active');
            });

            mapElement.parentElement.appendChild(sidebar);
            mapElement.parentElement.appendChild(toggleBtn);
            this.sidebar = sidebar;
        }

        /**
         * Создание элемента сайдбара
         */
        createSidebarItem(marker) {
            const item = document.createElement('div');
            item.className = 'codeweber-map-sidebar-item';
            item.dataset.markerId = marker.id;
            item.dataset.city = marker.city || '';
            item.dataset.category = marker.category || '';

            // Получаем настройки полей сайдбара
            const sidebarFields = this.config.sidebar?.fields || {
                showCity: true,
                showAddress: false,
                showPhone: false,
                showWorkingHours: true,
                showDescription: true,
            };

            let html = '';
            if (marker.title) {
                html += `<h6>${marker.title}</h6>`;
            }
            if (sidebarFields.showDescription && marker.description && marker.description.trim() !== '') {
                html += `<p style="font-size: 13px; color: #666; margin-bottom: 8px;">${marker.description}</p>`;
            }
            if (sidebarFields.showCity && marker.city) {
                html += `<p><i class="uil uil-location-pin-alt me-1"></i> ${marker.city}</p>`;
            }
            if (sidebarFields.showAddress && marker.address) {
                html += `<p><i class="uil uil-map-marker me-1"></i> ${marker.address}</p>`;
            }
            if (sidebarFields.showPhone && marker.phone) {
                html += `<p><i class="uil uil-phone me-1"></i> <a href="tel:${marker.phone.replace(/[^0-9+]/g, '')}">${marker.phone}</a></p>`;
            }
            if (sidebarFields.showWorkingHours && marker.workingHours) {
                html += `<p><i class="uil uil-clock me-1"></i> ${marker.workingHours}</p>`;
            }

            item.innerHTML = html;

            item.addEventListener('click', () => {
                this.onSidebarItemClick(marker.id);
            });

            return item;
        }

        /**
         * Обработчик клика на элемент сайдбара
         */
        onSidebarItemClick(markerId) {
            const placemark = this.placemarks[markerId];
            if (!placemark) return;

            const coords = placemark.geometry.getCoordinates();
            this.map.setCenter(coords, 15, {
                duration: 300
            }).then(() => {
                placemark.balloon.open();
            });

            this.highlightSidebarItem(markerId);

            // Закрываем сайдбар на мобильных
            if (window.innerWidth < 768 && this.sidebar) {
                setTimeout(() => {
                    this.sidebar.classList.remove('active');
                }, 500);
            }
        }

        /**
         * Подсветка элемента в сайдбаре
         */
        highlightSidebarItem(markerId) {
            // Сначала убираем подсветку со всех элементов
            document.querySelectorAll('.codeweber-map-sidebar-item').forEach(item => {
                item.classList.remove('active');
            });

            // Затем добавляем подсветку к выбранному элементу
            const items = document.querySelectorAll(`.codeweber-map-sidebar-item[data-marker-id="${markerId}"]`);
            items.forEach(item => {
                item.classList.add('active');
                item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        }

        /**
         * Инициализация фильтров
         */
        initFilters() {
            if (!this.config.sidebar || !this.config.sidebar.showFilters) {
                return;
            }

            const listId = this.config.id + '-sidebar-list';
            const list = this.wrapper
                ? this.wrapper.querySelector('#' + CSS.escape(listId))
                : document.getElementById(listId);
            if (!list) return;

            // Фильтр по городу
            if (this.config.sidebar.filterByCity) {
                this.createCityFilter(list);
            }

            // Фильтр по категории
            if (this.config.sidebar.filterByCategory) {
                this.createCategoryFilter(list);
            }
        }

        /**
         * Создание фильтра по городу
         */
        createCityFilter(list) {
            const cities = new Set();
            this.config.markers.forEach(marker => {
                if (marker.city) {
                    cities.add(marker.city);
                }
            });

            if (cities.size === 0) return;

            const filterContainer = document.createElement('div');
            filterContainer.className = 'codeweber-map-filter';
            
            const label = document.createElement('label');
            label.textContent = codeweberYandexMaps.i18n.filterByCity;
            label.setAttribute('for', `${this.config.id}-city-filter`);

            const select = document.createElement('select');
            select.id = `${this.config.id}-city-filter`;
            select.className = 'form-select form-select-sm';

            const allOption = document.createElement('option');
            allOption.value = '';
            allOption.textContent = codeweberYandexMaps.i18n.allCities;
            select.appendChild(allOption);

            Array.from(cities).sort().forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                select.appendChild(option);
            });

            select.addEventListener('change', (e) => {
                this.filterByCity(e.target.value);
            });

            filterContainer.appendChild(label);
            filterContainer.appendChild(select);

            list.parentElement.insertBefore(filterContainer, list);
        }

        /**
         * Фильтрация по городу
         */
        filterByCity(city) {
            const items = document.querySelectorAll('.codeweber-map-sidebar-item');
            const visiblePlacemarks = [];

            items.forEach(item => {
                const markerId = item.dataset.markerId;
                const itemCity = item.dataset.city;
                const placemark = this.placemarks[markerId];

                if (!placemark) return;

                if (city === '' || itemCity === city) {
                    item.style.display = 'block';
                    if (this.clusterer) {
                        this.clusterer.add(placemark);
                    } else {
                        this.map.geoObjects.add(placemark);
                    }
                    visiblePlacemarks.push(placemark);
                } else {
                    item.style.display = 'none';
                    if (this.clusterer) {
                        this.clusterer.remove(placemark);
                    } else {
                        this.map.geoObjects.remove(placemark);
                    }
                }
            });

            // Подгонка границ под видимые маркеры
            if (visiblePlacemarks.length > 0) {
                this.fitBounds(visiblePlacemarks);
            }
        }

        /**
         * Создание фильтра по категории
         */
        createCategoryFilter(list) {
            // Аналогично фильтру по городу
        }

        /**
         * Инициализация маршрутов
         */
        initRoute() {
            if (!this.config.route || !this.config.route.show) {
                return;
            }

            // Добавляем кнопку построения маршрута
            const routeControl = new ymaps.control.Button({
                data: { content: codeweberYandexMaps.i18n.buildRoute },
                options: { selectOnClick: false }
            });

            routeControl.events.add('click', () => {
                this.buildRoute();
            });

            this.map.controls.add(routeControl);
        }

        /**
         * Построение маршрута
         */
        buildRoute() {
            // Реализация построения маршрута
            // Используется Yandex Maps Router
        }

        /**
         * Подгонка границ карты под маркеры
         */
        fitBounds(placemarks = null) {
            const markers = placemarks || Object.values(this.placemarks);
            if (markers.length === 0) return;

            if (markers.length === 1) {
                const coords = markers[0].geometry.getCoordinates();
                this.map.setCenter(coords, 15);
                return;
            }

            const bounds = this.map.geoObjects.getBounds();
            if (bounds) {
                this.map.setBounds(bounds, {
                    checkZoomRange: true,
                    duration: 300,
                    margin: [50, 50, 50, 50]
                });
            }
        }

        /**
         * Скрытие спиннера загрузки карты
         */
        hideLoader() {
            const loaderId = this.config.id + '-loader';
            const loaderElement = this.wrapper
                ? this.wrapper.querySelector('#' + CSS.escape(loaderId))
                : document.getElementById(loaderId);
            if (loaderElement) {
                loaderElement.classList.add('done');
                // Удаляем элемент после анимации
                setTimeout(() => {
                    if (loaderElement.parentNode) {
                        loaderElement.remove();
                    }
                }, 300);
            }
        }

        /**
         * Обновить размер карты после изменения размеров контейнера (например при открытии offcanvas).
         * Вызывать при событии shown.bs.offcanvas для карт внутри панели.
         */
        invalidateSize() {
            const hasFit = !!(this.map && this.map.container && typeof this.map.container.fitToViewport === 'function');
            if (hasFit) {
                this.map.container.fitToViewport();
            }
            this.hideLoader();
        }
    }

    /**
     * Инициализация всех карт на странице
     */
    function initMaps() {
        const mapWrappers = document.querySelectorAll('.codeweber-yandex-map-wrapper');

        const initWrapper = function(wrapper) {
            if (wrapper.getAttribute('data-cwgb-map-inited') === '1') return;
            const configData = wrapper.getAttribute('data-map-config');
            if (!configData) return;
            try {
                wrapper.setAttribute('data-cwgb-map-inited', '1');
                const config = JSON.parse(configData);
                if (config.lazyLoad) {
                    const observer = new IntersectionObserver((entries, obs) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                new CodeweberYandexMaps(config, wrapper);
                                obs.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.1 });
                    observer.observe(wrapper);
                } else {
                    new CodeweberYandexMaps(config, wrapper);
                }
            } catch (e) {
                wrapper.removeAttribute('data-cwgb-map-inited');
                console.error('Error parsing map config:', e);
            }
        };

        mapWrappers.forEach(initWrapper);

        // При открытии любого offcanvas обновить размер карт внутри него (Navbar mobile и др.)
        document.addEventListener('shown.bs.offcanvas', function(e) {
            var offcanvas = e.target;
            if (!offcanvas || !offcanvas.querySelectorAll) return;
            var wrappers = offcanvas.querySelectorAll('.codeweber-yandex-map-wrapper');
            var instances = 0;
            wrappers.forEach(function(wrapper) {
                if (wrapper._cwgbYandexMapInstance && typeof wrapper._cwgbYandexMapInstance.invalidateSize === 'function') {
                    instances++;
                    wrapper._cwgbYandexMapInstance.invalidateSize();
                }
            });
        });

        // Поддержка динамически добавленных карт (например в редакторе Gutenberg через ServerSideRender)
        if (typeof MutationObserver !== 'undefined') {
            var mapObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType !== 1) return;
                        if (node.classList && node.classList.contains('codeweber-yandex-map-wrapper')) {
                            initWrapper(node);
                        }
                        if (node.querySelectorAll) {
                            var list = node.querySelectorAll('.codeweber-yandex-map-wrapper');
                            for (var i = 0; i < list.length; i++) {
                                initWrapper(list[i]);
                            }
                        }
                    });
                });
            });
            mapObserver.observe(document.body, { childList: true, subtree: true });
        }
    }

    // Инициализация при загрузке DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMaps);
    } else {
        initMaps();
    }

})();


