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
        constructor(config) {
            this.config = config;
            this.map = null;
            this.placemarks = {};
            this.clusterer = null;
            this.route = null;
            this.sidebar = null;
            this.filters = {};
            
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({
                    sessionId:'debug-session',
                    runId:'pre-fix',
                    hypothesisId:'H1',
                    location:'yandex-maps.js:constructor',
                    message:'Yandex map constructor called',
                    data:{mapId:config.id,markersCount:config.markers ? config.markers.length : 0},
                    timestamp:Date.now()
                })
            }).catch(()=>{});
            // #endregion

            this.init();
        }

        init() {
            // Загружаем API 3.0 динамически, если он еще не загружен
            if (typeof ymaps3 === 'undefined' && typeof ymaps === 'undefined') {
                this.loadAPI3();
            }
            
            // Проверяем, какой API доступен - новый (ymaps3) или старый (ymaps)
            // Ждем загрузки API через готовность
            const checkAPI = () => {
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({
                        sessionId:'debug-session',
                        runId:'pre-fix',
                        hypothesisId:'API_CHECK',
                        location:'yandex-maps.js:init',
                        message:'Checking available API',
                        data:{
                            hasYmaps3:typeof ymaps3 !== 'undefined',
                            hasYmaps:typeof ymaps !== 'undefined',
                            hasStyleJson:!!this.config.styleJson,
                            styleJsonLength:this.config.styleJson ? this.config.styleJson.length : 0
                        },
                        timestamp:Date.now()
                    })
                }).catch(()=>{});
                // #endregion
                
                // Если есть новый API и задан стиль в новом формате, используем новый API
                if (typeof ymaps3 !== 'undefined' && 
                    this.config.styleJson && typeof this.config.styleJson === 'string' && this.config.styleJson.trim() !== '') {
                    try {
                        const styleJson = JSON.parse(this.config.styleJson);
                        const isNewFormat = Array.isArray(styleJson) && styleJson.length > 0 && 
                                           ('tags' in styleJson[0] || 'elements' in styleJson[0]);
                        
                        if (isNewFormat) {
                            // Используем новый API для нового формата JSON
                            ymaps3.ready.then(() => {
                                this.initNewAPI();
                            });
                            return;
                        }
                    } catch (e) {
                        console.warn('Error parsing styleJson, falling back to old API:', e);
                    }
                }
                
                // Если новый API еще не загрузился, ждем немного и проверяем снова
                if (typeof ymaps3 === 'undefined' && typeof ymaps === 'undefined') {
                    setTimeout(checkAPI, 100);
                    return;
                }
                
                // Используем старый API для обратной совместимости
                if (typeof ymaps !== 'undefined') {
                    ymaps.ready(() => {
                        this.createMap();
                        this.addMarkers();
                        this.initSidebar();
                        this.initFilters();
                        this.initRoute();
                        this.hideLoader();
                    });
                } else {
                    console.error('Yandex Maps API is not loaded');
                    // #region agent log
                    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                        method:'POST',
                        headers:{'Content-Type':'application/json'},
                        body:JSON.stringify({
                            sessionId:'debug-session',
                            runId:'pre-fix',
                            hypothesisId:'H2',
                            location:'yandex-maps.js:init',
                            message:'Both APIs are undefined',
                            data:{},
                            timestamp:Date.now()
                        })
                    }).catch(()=>{});
                    // #endregion
                }
            };
            
            // Проверяем API с небольшой задержкой для загрузки скрипта
            if (typeof ymaps3 === 'undefined' && typeof ymaps === 'undefined') {
                setTimeout(checkAPI, 100);
            } else {
                checkAPI();
            }
        }
        
        /**
         * Динамическая загрузка API 3.0
         */
        loadAPI3() {
            const apiKey = this.config.apiKey || window.codeweberYandexMaps?.apiKey;
            const lang = this.config.language || window.codeweberYandexMaps?.language || 'ru_RU';
            
            if (!apiKey) {
                console.error('Yandex Maps API key is not set');
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({
                        sessionId:'debug-session',
                        runId:'pre-fix',
                        hypothesisId:'LOAD_API3',
                        location:'yandex-maps.js:loadAPI3',
                        message:'API key not set',
                        data:{},
                        timestamp:Date.now()
                    })
                }).catch(()=>{});
                // #endregion
                return;
            }
            
            const apiUrl = `https://api-maps.yandex.ru/v3/?apikey=${encodeURIComponent(apiKey)}&lang=${encodeURIComponent(lang)}`;
            
            // Проверяем, не загружается ли уже скрипт
            if (document.querySelector(`script[src*="api-maps.yandex.ru/v3"]`)) {
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({
                        sessionId:'debug-session',
                        runId:'pre-fix',
                        hypothesisId:'LOAD_API3',
                        location:'yandex-maps.js:loadAPI3',
                        message:'API 3.0 script already loading',
                        data:{url:apiUrl},
                        timestamp:Date.now()
                    })
                }).catch(()=>{});
                // #endregion
                return;
            }
            
            // #region agent log
            const hostname = window.location.hostname;
            const protocol = window.location.protocol;
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({
                    sessionId:'debug-session',
                    runId:'pre-fix',
                    hypothesisId:'LOAD_API3',
                    location:'yandex-maps.js:loadAPI3',
                    message:'Starting to load API 3.0',
                    data:{
                        url:apiUrl,
                        hasApiKey:!!apiKey,
                        currentUrl:window.location.href,
                        hostname:hostname,
                        protocol:protocol,
                        referer:document.referrer || '(empty)',
                        userAgent:navigator.userAgent.substring(0,100),
                        instruction:'In Yandex Developer Console, add HTTP Referer restriction: ' + hostname + ' (without protocol and port)'
                    },
                    timestamp:Date.now()
                })
            }).catch(()=>{});
            // #endregion
            
            // Проверяем, не загружается ли уже скрипт (более надежная проверка)
            const existingScript = document.querySelector(`script[src="${apiUrl}"]`);
            if (existingScript) {
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({
                        sessionId:'debug-session',
                        runId:'pre-fix',
                        hypothesisId:'LOAD_API3',
                        location:'yandex-maps.js:loadAPI3',
                        message:'API 3.0 script already exists, waiting for load',
                        data:{url:apiUrl},
                        timestamp:Date.now()
                    })
                }).catch(()=>{});
                // #endregion
                
                // Если скрипт уже есть, ждем его загрузки
                if (existingScript.onload) {
                    return;
                }
                existingScript.onload = () => {
                    setTimeout(() => this.init(), 100);
                };
                existingScript.onerror = () => {
                    this.loadAPI21(apiKey, lang);
                };
                return;
            }
            
            const script = document.createElement('script');
            script.src = apiUrl;
            script.async = true;
            script.defer = true;
            // НЕ используем crossOrigin для script тегов - это вызывает CORS проверку, которая не нужна
            
            script.onload = () => {
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({
                        sessionId:'debug-session',
                        runId:'pre-fix',
                        hypothesisId:'LOAD_API3',
                        location:'yandex-maps.js:loadAPI3',
                        message:'API 3.0 script loaded successfully',
                        data:{
                            hasYmaps3:typeof ymaps3 !== 'undefined',
                            hasYMap:typeof ymaps3?.YMap !== 'undefined'
                        },
                        timestamp:Date.now()
                    })
                }).catch(()=>{});
                // #endregion
                
                // Повторно запускаем проверку API
                this.init();
            };
            
            script.onerror = (error) => {
                const errorMsg = 'Failed to load Yandex Maps API 3.0. Status 403 indicates API key restrictions issue. Please check HTTP Referer settings in Yandex Developer Console. Current URL: ' + window.location.href;
                console.error(errorMsg, error);
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({
                        sessionId:'debug-session',
                        runId:'pre-fix',
                        hypothesisId:'LOAD_API3',
                        location:'yandex-maps.js:loadAPI3',
                        message:'API 3.0 script failed to load - Status 403',
                        data:{
                            url:apiUrl,
                            currentUrl:window.location.href,
                            referer:document.referrer,
                            error:error?.message || 'unknown',
                            statusCode:403,
                            suggestion:'API key returned 403 Forbidden. Check HTTP Referer restrictions in Yandex Developer Console. Current domain: ' + window.location.hostname
                        },
                        timestamp:Date.now()
                    })
                }).catch(()=>{});
                // #endregion
                
                // Попробуем загрузить API 2.1 как fallback
                this.loadAPI21(apiKey, lang);
            };
            
            document.head.appendChild(script);
        }
        
        /**
         * Загрузка API 2.1 как fallback
         */
        loadAPI21(apiKey, lang) {
            const apiUrl = `https://api-maps.yandex.ru/2.1/?apikey=${encodeURIComponent(apiKey)}&lang=${encodeURIComponent(lang)}`;
            
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({
                    sessionId:'debug-session',
                    runId:'pre-fix',
                    hypothesisId:'LOAD_API21',
                    location:'yandex-maps.js:loadAPI21',
                    message:'Loading API 2.1 as fallback',
                    data:{url:apiUrl},
                    timestamp:Date.now()
                })
            }).catch(()=>{});
            // #endregion
            
            const script = document.createElement('script');
            script.src = apiUrl;
            script.async = true;
            script.defer = true;
            
            script.onload = () => {
                // Повторно запускаем проверку API
                this.init();
            };
            
            script.onerror = (error) => {
                console.error('Failed to load Yandex Maps API 2.1:', error);
            };
            
            document.head.appendChild(script);
        }
        
        /**
         * Инициализация с новым API (YMap)
         */
        initNewAPI() {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({
                    sessionId:'debug-session',
                    runId:'pre-fix',
                    hypothesisId:'NEW_API',
                    location:'yandex-maps.js:initNewAPI',
                    message:'Initializing with new API',
                    data:{},
                    timestamp:Date.now()
                })
            }).catch(()=>{});
            // #endregion
            
            this.createMapNewAPI();
            this.addMarkersNewAPI();
            this.initSidebar();
            this.initFilters();
            this.initRoute();
            this.hideLoader();
        }

        /**
         * Создание карты
         */
        createMap() {
            const mapElement = document.getElementById(this.config.id);
            if (!mapElement) {
                console.error('Map element not found:', this.config.id);
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({
                        sessionId:'debug-session',
                        runId:'pre-fix',
                        hypothesisId:'H3',
                        location:'yandex-maps.js:createMap',
                        message:'Map element not found',
                        data:{mapId:this.config.id},
                        timestamp:Date.now()
                    })
                }).catch(()=>{});
                // #endregion
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

            this.map = new ymaps.Map(this.config.id, {
                center: this.config.center,
                zoom: this.config.zoom,
                type: this.config.mapType,
                controls: baseControls
            });

            // Применение кастомного стиля из JSON, если задан
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({
                    sessionId:'debug-session',
                    runId:'pre-fix',
                    hypothesisId:'STYLE0',
                    location:'yandex-maps.js:createMap',
                    message:'Checking styleJson before application',
                    data:{
                        hasStyleJson:!!this.config.styleJson,
                        type:typeof this.config.styleJson,
                        length:this.config.styleJson ? this.config.styleJson.length : 0,
                        firstChars:this.config.styleJson ? this.config.styleJson.substring(0,50) : ''
                    },
                    timestamp:Date.now()
                })
            }).catch(()=>{});
            // #endregion
            
            if (this.config.styleJson && typeof this.config.styleJson === 'string' && this.config.styleJson.trim() !== '') {
                try {
                    const styleJson = JSON.parse(this.config.styleJson);
                    // #region agent log
                    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                        method:'POST',
                        headers:{'Content-Type':'application/json'},
                        body:JSON.stringify({
                            sessionId:'debug-session',
                            runId:'pre-fix',
                            hypothesisId:'STYLE1',
                            location:'yandex-maps.js:createMap',
                            message:'JSON parsed successfully',
                            data:{
                                isArray:Array.isArray(styleJson),
                                isObject:typeof styleJson === 'object',
                                keys:typeof styleJson === 'object' && !Array.isArray(styleJson) ? Object.keys(styleJson) : [],
                                firstItem:Array.isArray(styleJson) && styleJson.length > 0 ? styleJson[0] : null,
                                hasTags:Array.isArray(styleJson) && styleJson.length > 0 ? 'tags' in styleJson[0] : false
                            },
                            timestamp:Date.now()
                        })
                    }).catch(()=>{});
                    // #endregion
                    
                    // Определяем формат JSON: новый формат (tags/elements/stylers) или старый (featureType/elementType)
                    let stylesArray = null;
                    if (Array.isArray(styleJson)) {
                        stylesArray = styleJson;
                    } else if (styleJson.styles && Array.isArray(styleJson.styles)) {
                        stylesArray = styleJson.styles;
                    } else if (styleJson.preset && styleJson.options) {
                        // Если передан объект с preset и options (старый формат)
                        this.map.setType(styleJson);
                        // #region agent log
                        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                            method:'POST',
                            headers:{'Content-Type':'application/json'},
                            body:JSON.stringify({
                                sessionId:'debug-session',
                                runId:'pre-fix',
                                hypothesisId:'STYLE5',
                                location:'yandex-maps.js:createMap',
                                message:'Applied via setType with preset',
                                data:{preset:styleJson.preset},
                                timestamp:Date.now()
                            })
                        }).catch(()=>{});
                        // #endregion
                        return; // Выходим, так как стиль уже применен
                    } else {
                        stylesArray = [styleJson];
                    }
                    
                    if (stylesArray && stylesArray.length > 0) {
                        // Проверяем формат: если есть 'tags' - это новый формат, иначе старый
                        const isNewFormat = stylesArray[0] && ('tags' in stylesArray[0] || 'elements' in stylesArray[0]);
                        
                        // #region agent log
                        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                            method:'POST',
                            headers:{'Content-Type':'application/json'},
                            body:JSON.stringify({
                                sessionId:'debug-session',
                                runId:'pre-fix',
                                hypothesisId:'STYLE9',
                                location:'yandex-maps.js:createMap',
                                message:'Before applying style',
                                data:{
                                    stylesCount:stylesArray.length,
                                    isNewFormat:isNewFormat,
                                    mapType:this.map.getType(),
                                    hasOptions:!!this.map.options,
                                    optionsKeys:this.map.options ? Object.keys(this.map.options) : []
                                },
                                timestamp:Date.now()
                            })
                        }).catch(()=>{});
                        // #endregion
                        
                        // В Yandex Maps API 2.1 применяем стиль через options.set('customMapStyle', ...)
                        // ВАЖНО: В API 2.1 кастомизация доступна только в коммерческой версии
                        // Новый формат JSON (tags/elements/stylers) может не работать в API 2.1
                        try {
                            // Пробуем установить стиль
                            this.map.options.set('customMapStyle', stylesArray);
                            
                            // Проверяем, установился ли стиль
                            const appliedStyle = this.map.options.get('customMapStyle');
                            
                            // #region agent log
                            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                                method:'POST',
                                headers:{'Content-Type':'application/json'},
                                body:JSON.stringify({
                                    sessionId:'debug-session',
                                    runId:'pre-fix',
                                    hypothesisId:'STYLE3',
                                    location:'yandex-maps.js:createMap',
                                    message:'Custom style applied via options.set',
                                    data:{
                                        stylesCount:stylesArray.length,
                                        mapType:this.map.getType(),
                                        format:isNewFormat ? 'new' : 'old',
                                        styleApplied:!!appliedStyle,
                                        appliedStyleLength:appliedStyle ? (Array.isArray(appliedStyle) ? appliedStyle.length : 'not array') : 'null',
                                        firstStyle:stylesArray[0]
                                    },
                                    timestamp:Date.now()
                                })
                            }).catch(()=>{});
                            // #endregion
                            
                            // Если стиль не применился и это новый формат, выводим предупреждение
                            if (isNewFormat && !appliedStyle) {
                                console.warn('Yandex Maps API 2.1 may not support new format JSON (tags/elements/stylers). Customization is only available in commercial version of API 2.1.');
                            }
                        } catch (styleError) {
                            console.error('Error setting customMapStyle:', styleError);
                            // #region agent log
                            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                                method:'POST',
                                headers:{'Content-Type':'application/json'},
                                body:JSON.stringify({
                                    sessionId:'debug-session',
                                    runId:'pre-fix',
                                    hypothesisId:'STYLE8',
                                    location:'yandex-maps.js:createMap',
                                    message:'Error setting customMapStyle',
                                    data:{
                                        error:styleError.message,
                                        stack:styleError.stack,
                                        isNewFormat:isNewFormat
                                    },
                                    timestamp:Date.now()
                                })
                            }).catch(()=>{});
                            // #endregion
                        }
                    }
                } catch (e) {
                    console.error('Error parsing or applying styleJson:', e);
                    // #region agent log
                    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                        method:'POST',
                        headers:{'Content-Type':'application/json'},
                        body:JSON.stringify({
                            sessionId:'debug-session',
                            runId:'pre-fix',
                            hypothesisId:'STYLE2',
                            location:'yandex-maps.js:createMap',
                            message:'Error parsing styleJson',
                            data:{error:e.message,stack:e.stack},
                            timestamp:Date.now()
                        })
                    }).catch(()=>{});
                    // #endregion
                }
            } else {
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({
                        sessionId:'debug-session',
                        runId:'pre-fix',
                        hypothesisId:'STYLE4',
                        location:'yandex-maps.js:createMap',
                        message:'No styleJson or empty',
                        data:{hasStyleJson:!!this.config.styleJson,type:typeof this.config.styleJson},
                        timestamp:Date.now()
                    })
                }).catch(()=>{});
                // #endregion
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

            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({
                    sessionId:'debug-session',
                    runId:'pre-fix',
                    hypothesisId:'DZ2',
                    location:'yandex-maps.js:createMap',
                    message:'Double click zoom behavior state',
                    data:{
                        configEnableDblClickZoom:this.config.enableDblClickZoom,
                        dblClickZoomEnabled:this.map.behaviors.isEnabled('dblClickZoom')
                    },
                    timestamp:Date.now()
                })
            }).catch(()=>{});
            // #endregion

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
         * Создание карты с новым API (ymaps3.YMap)
         */
        createMapNewAPI() {
            const mapElement = document.getElementById(this.config.id);
            if (!mapElement) {
                console.error('Map element not found:', this.config.id);
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({
                        sessionId:'debug-session',
                        runId:'pre-fix',
                        hypothesisId:'NEW_API_MAP',
                        location:'yandex-maps.js:createMapNewAPI',
                        message:'Map element not found',
                        data:{mapId:this.config.id},
                        timestamp:Date.now()
                    })
                }).catch(()=>{});
                // #endregion
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

            // Создаем карту с новым API
            this.map = new ymaps3.YMap(mapElement, {
                location: {
                    center: this.config.center,
                    zoom: this.config.zoom
                },
                mode: 'vector'
            });

            // Парсим и применяем кастомный стиль
            if (this.config.styleJson && typeof this.config.styleJson === 'string' && this.config.styleJson.trim() !== '') {
                try {
                    const styleJson = JSON.parse(this.config.styleJson);
                    let stylesArray = null;
                    if (Array.isArray(styleJson)) {
                        stylesArray = styleJson;
                    } else if (styleJson.styles && Array.isArray(styleJson.styles)) {
                        stylesArray = styleJson.styles;
                    } else {
                        stylesArray = [styleJson];
                    }

                    if (stylesArray && stylesArray.length > 0) {
                        // Создаем слой с кастомным стилем
                        const layer = new ymaps3.YMapDefaultSchemeLayer({
                            customization: stylesArray
                        });
                        this.map.addChild(layer);
                        
                        // #region agent log
                        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                            method:'POST',
                            headers:{'Content-Type':'application/json'},
                            body:JSON.stringify({
                                sessionId:'debug-session',
                                runId:'pre-fix',
                                hypothesisId:'NEW_API_STYLE',
                                location:'yandex-maps.js:createMapNewAPI',
                                message:'Custom style applied with new API',
                                data:{
                                    stylesCount:stylesArray.length,
                                    layerAdded:true,
                                    mapCreated:!!this.map
                                },
                                timestamp:Date.now()
                            })
                        }).catch(()=>{});
                        // #endregion
                    }
                } catch (e) {
                    console.error('Error applying custom style with new API:', e);
                    // #region agent log
                    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                        method:'POST',
                        headers:{'Content-Type':'application/json'},
                        body:JSON.stringify({
                            sessionId:'debug-session',
                            runId:'pre-fix',
                            hypothesisId:'NEW_API_STYLE_ERROR',
                            location:'yandex-maps.js:createMapNewAPI',
                            message:'Error applying style with new API',
                            data:{error:e.message,stack:e.stack},
                            timestamp:Date.now()
                        })
                    }).catch(()=>{});
                    // #endregion
                }
            } else {
                // Если стиля нет, добавляем стандартный слой
                const layer = new ymaps3.YMapDefaultSchemeLayer();
                this.map.addChild(layer);
            }
        }

        /**
         * Добавление маркеров на карту (новый API)
         */
        addMarkersNewAPI() {
            if (!this.map || !this.config.markers || this.config.markers.length === 0) {
                return;
            }

            // В новом API маркеры добавляются через YMapMarker
            // Пока оставляем базовую реализацию - можно расширить позже
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({
                    sessionId:'debug-session',
                    runId:'pre-fix',
                    hypothesisId:'NEW_API_MARKERS',
                    location:'yandex-maps.js:addMarkersNewAPI',
                    message:'Markers will be added (basic implementation)',
                    data:{markersCount:this.config.markers.length},
                    timestamp:Date.now()
                })
            }).catch(()=>{});
            // #endregion
            
            // TODO: Реализовать добавление маркеров для нового API
            // Пока используем старый метод addMarkers для обратной совместимости
            console.warn('Markers addition for new API not fully implemented yet');
        }

        /**
         * Добавление маркеров на карту
         */
        addMarkers() {
            if (!this.map || !this.config.markers || this.config.markers.length === 0) {
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({
                        sessionId:'debug-session',
                        runId:'pre-fix',
                        hypothesisId:'H5',
                        location:'yandex-maps.js:addMarkers',
                        message:'No markers or map not initialized',
                        data:{hasMap:!!this.map,markersCount:this.config.markers ? this.config.markers.length : 0},
                        timestamp:Date.now()
                    })
                }).catch(()=>{});
                // #endregion
                return;
            }

            const markers = this.config.markers;
            const markerSettings = this.config.markerSettings || {};

            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({
                    sessionId:'debug-session',
                    runId:'pre-fix',
                    hypothesisId:'H6',
                    location:'yandex-maps.js:addMarkers',
                    message:'Adding markers',
                    data:{markersCount:markers.length},
                    timestamp:Date.now()
                })
            }).catch(()=>{});
            // #endregion

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
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({
                    sessionId:'debug-session',
                    runId:'pre-fix',
                    hypothesisId:'S1',
                    location:'yandex-maps.js:initSidebar',
                    message:'Init sidebar called',
                    data:{
                        hasSidebarConfig:!!this.config.sidebar,
                        sidebarConfig:this.config.sidebar||null
                    },
                    timestamp:Date.now()
                })
            }).catch(()=>{});
            // #endregion

            if (!this.config.sidebar || !this.config.sidebar.show) {
                return;
            }

            const mapElement = document.getElementById(this.config.id);
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

            const list = document.getElementById(`${this.config.id}-sidebar-list`);
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
            const loaderElement = document.getElementById(this.config.id + '-loader');
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
    }

    /**
     * Инициализация всех карт на странице
     */
    function initMaps() {
        const mapWrappers = document.querySelectorAll('.codeweber-yandex-map-wrapper');
        
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({
                sessionId:'debug-session',
                runId:'pre-fix',
                hypothesisId:'H7',
                location:'yandex-maps.js:initMaps',
                message:'Found map wrappers',
                data:{wrappersCount:mapWrappers.length},
                timestamp:Date.now()
            })
        }).catch(()=>{});
        // #endregion

        const initWrapper = function(wrapper) {
            const configData = wrapper.getAttribute('data-map-config');
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({
                    sessionId:'debug-session',
                    runId:'pre-fix',
                    hypothesisId:'C',
                    location:'yandex-maps.js:initWrapper',
                    message:'Reading map config',
                    data:{hasConfig:!!configData,configLength:configData ? configData.length : 0},
                    timestamp:Date.now()
                })
            }).catch(()=>{});
            // #endregion
            if (!configData) return;
            try {
                const config = JSON.parse(configData);
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({
                        sessionId:'debug-session',
                        runId:'pre-fix',
                        hypothesisId:'C',
                        location:'yandex-maps.js:initWrapper',
                        message:'Config parsed',
                        data:{mapId:config.id,markersCount:config.markers ? config.markers.length : 0,showSidebar:config.sidebar ? config.sidebar.show : false},
                        timestamp:Date.now()
                    })
                }).catch(()=>{});
                // #endregion
                if (config.lazyLoad) {
                    const observer = new IntersectionObserver((entries, obs) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                new CodeweberYandexMaps(config);
                                obs.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.1 });
                    observer.observe(wrapper);
                } else {
                    new CodeweberYandexMaps(config);
                }
            } catch (e) {
                console.error('Error parsing map config:', e);
            }
        };

        mapWrappers.forEach(initWrapper);
    }

    // Инициализация при загрузке DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMaps);
    } else {
        initMaps();
    }

})();


