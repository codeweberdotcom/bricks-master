/**
 * Share Buttons - Floating Social Share Widget
 * Адаптировано под Bootstrap и Unicons
 */

(function() {
	'use strict';
	
	document.addEventListener('DOMContentLoaded', function() {
		// Находим все виджеты на странице
		const shareButtonsWidgets = document.querySelectorAll('.share-buttons');
		
		if (shareButtonsWidgets.length === 0) {
			return;
		}
		
		// Инициализируем каждый виджет отдельно
		shareButtonsWidgets.forEach(function(shareButtons, widgetIndex) {
			// Ищем основную кнопку - может быть share-button-main или share-icon-main
			const shareButtonMain = shareButtons.querySelector('.share-button-main') || shareButtons.querySelector('.share-icon-main');
			const shareButtonIcon = shareButtonMain ? shareButtonMain.querySelector('i') : null;
			
			// #region agent log
			fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'share-buttons.js:20',message:'Widget initialization',data:{widgetIndex:widgetIndex,foundButton:!!shareButtonMain,buttonClass:shareButtonMain?shareButtonMain.className:'none',hasIcon:!!shareButtonIcon},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
			// #endregion
			
			if (!shareButtonMain) {
				// #region agent log
				fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'share-buttons.js:26',message:'Button not found - skipping widget',data:{widgetIndex:widgetIndex},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
				// #endregion
				return;
			}
			
			// Обработчик клика на главную кнопку
			shareButtonMain.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				
				// #region agent log
				fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'share-buttons.js:30',message:'Button clicked',data:{widgetIndex:widgetIndex,buttonClass:shareButtonMain.className,currentOpen:shareButtons.classList.contains('open')},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
				// #endregion
				
				// Проверяем, нужно ли применять вращение (не применяем для типа button)
				const noRotate = shareButtonMain.classList.contains('no-rotate');
				
				if (!noRotate) {
					const isRotating = shareButtonMain.classList.contains('rotate');
					
					if (isRotating) {
						// Закрытие - обратный поворот
						shareButtonMain.classList.remove('rotate');
						shareButtonMain.classList.add('rotate-reverse');
						
						// Удаляем класс обратной анимации после завершения
						setTimeout(function() {
							shareButtonMain.classList.remove('rotate-reverse');
						}, 600);
					} else {
						// Открытие - прямой поворот
						shareButtonMain.classList.remove('rotate-reverse');
						shareButtonMain.classList.add('rotate');
					}
				}
				
				// Переключаем класс open для показа/скрытия социальных кнопок
				const isOpen = shareButtons.classList.contains('open');
				
				// #region agent log
				fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'share-buttons.js:56',message:'Toggle state check',data:{widgetIndex:widgetIndex,isOpen:isOpen,willClose:isOpen,willOpen:!isOpen},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
				// #endregion
				
				if (isOpen) {
					// Закрытие - меняем иконку обратно на comment-dots
					if (shareButtonIcon) {
						// Сохраняем все классы кроме классов uil и uil-*
						const allClasses = shareButtonIcon.className.split(' ');
						const preservedClasses = allClasses.filter(function(cls) {
							return cls !== 'uil' && !cls.startsWith('uil-');
						});
						shareButtonIcon.className = preservedClasses.join(' ') + ' uil uil-comment-dots';
					}
					
					// Закрытие - сначала запускаем анимацию исчезновения
					shareButtons.classList.add('closing');
					
					// Удаляем open после небольшой задержки, чтобы closing успел примениться
					setTimeout(function() {
						shareButtons.classList.remove('open');
					}, 10);
					
					// После завершения анимации убираем класс closing
					setTimeout(function() {
						shareButtons.classList.remove('closing');
					}, 500);
				} else {
					// Открытие - меняем иконку на times
					if (shareButtonIcon) {
						// Сохраняем все классы кроме классов uil и uil-*
						const allClasses = shareButtonIcon.className.split(' ');
						const preservedClasses = allClasses.filter(function(cls) {
							return cls !== 'uil' && !cls.startsWith('uil-');
						});
						shareButtonIcon.className = preservedClasses.join(' ') + ' uil uil-times';
					}
					
					// Открытие - убираем класс closing если был и добавляем open
					shareButtons.classList.remove('closing');
					shareButtons.classList.add('open');
					
					// #region agent log
					// Проверяем элементы после открытия - измеряем реальные размеры
					const socialElements = shareButtons.querySelectorAll('.widget-social');
					const buttonMainClass = shareButtonMain.classList.contains('share-button-main') ? 'share-button-main' : (shareButtonMain.classList.contains('share-icon-main') ? 'share-icon-main' : 'unknown');
					const widgetClasses = Array.from(shareButtons.classList);
					const mainButtonRect = shareButtonMain.getBoundingClientRect();
					const elementTypes = Array.from(socialElements).map(function(el, idx) {
						const computedStyle = window.getComputedStyle(el);
						const rect = el.getBoundingClientRect();
						return {
							index: idx,
							childIndex: idx + 2, // +2 потому что первый элемент - основная кнопка
							hasButtonElement: el.classList.contains('button-element'),
							hasIconElement: el.classList.contains('icon-element'),
							transform: computedStyle.transform,
							opacity: computedStyle.opacity,
							top: rect.top,
							bottom: rect.bottom,
							height: rect.height,
							width: rect.width,
							classes: el.className,
							tagName: el.tagName
						};
					});
					// Проверяем, какие CSS правила должны применяться
					const hasButtonMain = shareButtons.classList.contains('widget-main-button-elements-button');
					const hasButtonIcon = shareButtons.classList.contains('widget-main-button-elements-icon');
					const hasIconIcon = shareButtons.classList.contains('widget-main-icon-elements-icon');
					const hasIconButton = shareButtons.classList.contains('widget-main-icon-elements-button');
					// Ждем немного, чтобы transform применился, затем проверяем позиции
					setTimeout(function() {
						const elementPositions = Array.from(socialElements).map(function(el, idx) {
							const rect = el.getBoundingClientRect();
							const computedStyle = window.getComputedStyle(el);
							// Проверяем, какие CSS правила применяются к элементу
							const matchingRules = [];
							try {
								const stylesheets = Array.from(document.styleSheets);
								stylesheets.forEach(function(sheet) {
									try {
										const rules = Array.from(sheet.cssRules || sheet.rules || []);
										rules.forEach(function(rule) {
											if (rule.selectorText && el.matches && el.matches(rule.selectorText)) {
												if (rule.style && rule.style.transform) {
													matchingRules.push({
														selector: rule.selectorText,
														transform: rule.style.transform,
														specificity: rule.selectorText.split(' ').length
													});
												}
											}
										});
									} catch(e) {}
								});
							} catch(e) {}
							return {
								index: idx,
								childIndex: idx + 2, // +2 потому что первый элемент - основная кнопка
								hasButtonElement: el.classList.contains('button-element'),
								hasIconElement: el.classList.contains('icon-element'),
								transform: computedStyle.transform,
								top: rect.top,
								bottom: rect.bottom,
								height: rect.height,
								mainButtonBottom: mainButtonRect.bottom,
								distanceFromMain: mainButtonRect.bottom - rect.top,
								matchingRules: matchingRules
							};
						});
						// Вычисляем расстояния между соседними элементами
						const gaps = [];
						for (let i = 0; i < elementPositions.length - 1; i++) {
							const current = elementPositions[i];
							const next = elementPositions[i + 1];
							const gap = current.top - next.bottom;
							gaps.push({
								between: i + ' and ' + (i + 1),
								gap: gap,
								currentBottom: current.bottom,
								nextTop: next.top
							});
						}
						fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'share-buttons.js:140',message:'Widget opened - positions after transform with gaps',data:{widgetIndex:widgetIndex,mainButtonHeight:mainButtonRect.height,elementPositions:elementPositions,gaps:gaps,expectedGap:4},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
					}, 200);
					fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'share-buttons.js:120',message:'Widget opened - detailed check',data:{widgetIndex:widgetIndex,hasOpenClass:shareButtons.classList.contains('open'),buttonMainClass:buttonMainClass,widgetClasses:widgetClasses,mainButtonHeight:mainButtonRect.height,mainButtonWidth:mainButtonRect.width,socialElementsCount:socialElements.length,elementTypes:elementTypes,hasButtonMain:hasButtonMain,hasButtonIcon:hasButtonIcon,hasIconIcon:hasIconIcon,hasIconButton:hasIconButton},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
					// #endregion
				}
			});
		});
		
		// Закрытие при клике вне области кнопок (для всех виджетов)
		// Используем задержку, чтобы обработчик клика на кнопке сработал первым
		document.addEventListener('click', function(e) {
			// Небольшая задержка, чтобы обработчик клика на кнопке успел сработать
			setTimeout(function() {
				// Пропускаем клики на кнопках виджетов и внутри виджетов
				let clickedOnWidget = false;
				shareButtonsWidgets.forEach(function(shareButtons) {
					// Проверяем, был ли клик внутри виджета (включая кнопку и иконку)
					if (shareButtons.contains(e.target)) {
						clickedOnWidget = true;
					}
				});
				
				if (clickedOnWidget) {
					return;
				}
				
				shareButtonsWidgets.forEach(function(shareButtons, widgetIndex) {
					const isOpen = shareButtons.classList.contains('open');
					const containsTarget = shareButtons.contains(e.target);
					
					if (isOpen && !containsTarget) {
						// Ищем основную кнопку - может быть share-button-main или share-icon-main
						const shareButtonMain = shareButtons.querySelector('.share-button-main') || shareButtons.querySelector('.share-icon-main');
						const shareButtonIcon = shareButtonMain ? shareButtonMain.querySelector('i') : null;
						
						if (!shareButtonMain) {
							return;
						}
						
						// Проверяем, нужно ли применять вращение (не применяем для типа button)
						const noRotate = shareButtonMain.classList.contains('no-rotate');
						if (!noRotate) {
							shareButtonMain.classList.remove('rotate');
							shareButtonMain.classList.add('rotate-reverse');
						}
						
						// Закрытие - меняем иконку обратно на comment-dots
						if (shareButtonIcon) {
							// Сохраняем все классы кроме классов uil и uil-*
							const allClasses = shareButtonIcon.className.split(' ');
							const preservedClasses = allClasses.filter(function(cls) {
								return cls !== 'uil' && !cls.startsWith('uil-');
							});
							shareButtonIcon.className = preservedClasses.join(' ') + ' uil uil-comment-dots';
						}
						
						// Закрытие - сначала запускаем анимацию исчезновения
						shareButtons.classList.add('closing');
						
						// Удаляем open после небольшой задержки
						setTimeout(function() {
							shareButtons.classList.remove('open');
						}, 10);
						
						// После завершения анимации убираем класс closing
						setTimeout(function() {
							shareButtons.classList.remove('closing');
						}, 500);
						
						// Удаляем класс обратной анимации после завершения
						setTimeout(function() {
							shareButtonMain.classList.remove('rotate-reverse');
						}, 600);
					}
				});
			}, 10); // Небольшая задержка для обработчика клика на кнопке
		});
	});
})();

