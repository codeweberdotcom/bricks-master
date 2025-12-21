document.addEventListener("DOMContentLoaded", () => {
  const ENABLE_CACHE = false;
  const modalButtons = document.querySelectorAll('a[data-bs-toggle="modal"]');
  const downloadButtons = document.querySelectorAll('a[data-bs-toggle="download"]');
  const modalElement = document.getElementById("modal");
  const modalContent = document.getElementById("modal-content");
  const modalDialog = modalElement ? modalElement.querySelector(".modal-dialog") : null;
  
  // Функция для получения базового URL плагина
  function getPluginBaseUrl() {
    // Пытаемся найти уже загруженный скрипт FilePond и получить его базовый URL
    const existingFilePondScript = document.querySelector('script[src*="filepond.min.js"]');
    if (existingFilePondScript) {
      const src = existingFilePondScript.getAttribute('src');
      return src.replace('/assets/filepond/filepond.min.js', '');
    }
    // Если скрипт не найден, используем относительный путь от корня сайта
    return window.location.origin + '/wp-content/plugins/codeweber-gutenberg-blocks';
  }
  
  // Флаг для предотвращения множественных загрузок скрипта
  let filepondInitScriptLoading = false;
  let filepondInitScriptLoaded = false;
  
  // Функция для динамической загрузки filepond-init.js
  function loadFilePondInitScript() {
    // Проверяем, не загружен ли уже скрипт
    if (filepondInitScriptLoaded || document.querySelector('script[src*="filepond-init.js"]')) {
      filepondInitScriptLoaded = true;
      // Скрипт уже загружен, просто ждем его выполнения
      setTimeout(function() {
        if (typeof window.initFilePond === 'function') {
          // Проверяем, есть ли неинициализированные поля
          const uninitializedInputs = modalContent ? modalContent.querySelectorAll('input[type="file"][data-filepond="true"]:not([data-filepond-initialized])') : [];
          if (uninitializedInputs.length > 0) {
            window.initFilePond();
          }
        }
      }, 50); // Уменьшена задержка с 200ms до 50ms
      return;
    }
    
    // Проверяем, не загружается ли скрипт в данный момент
    if (filepondInitScriptLoading) {
      return;
    }
    
    // Устанавливаем флаг загрузки
    filepondInitScriptLoading = true;
    
    // Убеждаемся, что filepondSettings доступен (создаем, если не определен)
    // Проверяем, не определен ли он глобально (может быть локализован через wp_localize_script)
    if (typeof window.filepondSettings === 'undefined' && typeof filepondSettings === 'undefined') {
      // Создаем filepondSettings вручную, если он не был локализован
      window.filepondSettings = {
        uploadUrl: wpApiSettings.root + 'codeweber-forms/v1/upload',
        nonce: wpApiSettings.nonce,
        translations: {
          labelIdle: 'Drag & drop your files or <span class="filepond--label-action">browse</span>',
          maxFiles: 'Maximum number of files: %s. Please remove excess files.',
          fileTooLarge: 'File is too large. Maximum size: %s',
          totalSizeTooLarge: 'Total file size is too large. Maximum: %s',
          errorUploading: 'Error uploading file',
          errorAddingFile: 'Error adding file',
          filesRemoved: 'Maximum number of files: %s. Files removed: %s',
          totalSizeExceeded: 'Total file size exceeded. Maximum: %s',
          uploadComplete: 'Upload complete',
          tapToUndo: 'Tap to undo',
          uploading: 'Uploading',
          tapToCancel: 'tap to cancel'
        }
      };
    } else if (typeof filepondSettings !== 'undefined' && typeof window.filepondSettings === 'undefined') {
      // Если filepondSettings определен локально, но не глобально, делаем его глобальным
      window.filepondSettings = filepondSettings;
    }
    
    // Загружаем переводы перед загрузкой скрипта, если они еще не загружены
    const loadTranslationsPromise = new Promise(function(resolve) {
      // Проверяем, есть ли уже переводы (не английские по умолчанию)
      if (window.filepondSettings && window.filepondSettings.translations && 
          window.filepondSettings.translations.uploadComplete !== 'Upload complete') {
        // Переводы уже загружены (не английские по умолчанию)
        resolve();
      } else {
        // Загружаем переводы через REST API
        fetch(wpApiSettings.root + 'codeweber-forms/v1/filepond-translations', {
          credentials: 'include'
        })
          .then(function(response) {
            return response.json();
          })
          .then(function(translations) {
            if (translations && typeof translations === 'object') {
              if (!window.filepondSettings) {
                window.filepondSettings = {
                  uploadUrl: wpApiSettings.root + 'codeweber-forms/v1/upload',
                  nonce: wpApiSettings.nonce,
                  translations: {}
                };
              }
              window.filepondSettings.translations = translations;
            }
            resolve();
          })
          .catch(function(error) {
            console.warn('[REST API] Failed to load FilePond translations:', error);
            resolve(); // Продолжаем даже если переводы не загрузились
          });
      }
    });
    
    // Загружаем скрипт динамически после загрузки переводов
    loadTranslationsPromise.then(function() {
      const script = document.createElement('script');
      const pluginBaseUrl = getPluginBaseUrl();
      script.src = pluginBaseUrl + '/includes/js/filepond-init.js';
      script.onload = function() {
        filepondInitScriptLoading = false;
        filepondInitScriptLoaded = true;
        setTimeout(function() {
          if (typeof window.initFilePond === 'function') {
            // Проверяем, есть ли неинициализированные поля
            const uninitializedInputs = modalContent ? modalContent.querySelectorAll('input[type="file"][data-filepond="true"]:not([data-filepond-initialized])') : [];
            if (uninitializedInputs.length > 0) {
              window.initFilePond();
            }
          }
        }, 50); // Уменьшена задержка с 100ms до 50ms
      };
      script.onerror = function() {
        filepondInitScriptLoading = false;
        console.error('[REST API DEBUG] Failed to load filepond-init.js from', script.src);
      };
      document.head.appendChild(script);
    });
  }
  
  // Функция для динамической загрузки всех FilePond скриптов
  function loadFilePondScripts() {
    // Проверяем, загружена ли библиотека FilePond
    if (typeof FilePond === 'undefined') {
      const pluginBaseUrl = getPluginBaseUrl();
      
      // Загружаем FilePond CSS
      if (!document.querySelector('link[href*="filepond.min.css"]')) {
        const css = document.createElement('link');
        css.rel = 'stylesheet';
        css.href = pluginBaseUrl + '/assets/filepond/filepond.min.css';
        document.head.appendChild(css);
      }
      
      // Загружаем FilePond JS
      if (!document.querySelector('script[src*="filepond.min.js"]')) {
        const filepondScript = document.createElement('script');
        filepondScript.src = pluginBaseUrl + '/assets/filepond/filepond.min.js';
        filepondScript.onload = function() {
          loadFilePondInitScript();
        };
        filepondScript.onerror = function() {
          console.error('[REST API DEBUG] Failed to load filepond.min.js from', filepondScript.src);
        };
        document.head.appendChild(filepondScript);
      } else {
        loadFilePondInitScript();
      }
    } else {
      loadFilePondInitScript();
    }
  }
  
  // Функция для ожидания и вызова initFilePond или прямой инициализации
  function waitAndInitFilePond(attempts = 0) {
    const maxAttempts = 2; // Максимум 0.2 секунды (2 * 100ms) - минимальная задержка для проверки
    const fileInputsBeforeInit = modalContent ? modalContent.querySelectorAll('input[type="file"][data-filepond="true"]') : [];
    
    // Если нет файловых полей, не инициализируем
    if (fileInputsBeforeInit.length === 0) {
      return;
    }
    
    if (typeof window.initFilePond === 'function') {
      // Функция доступна, вызываем сразу
      const uninitializedInputs = modalContent ? modalContent.querySelectorAll('input[type="file"][data-filepond="true"]:not([data-filepond-initialized])') : [];
      // Вызываем initFilePond только если есть неинициализированные поля
      if (uninitializedInputs.length > 0) {
        window.initFilePond();
      }
    } else if (attempts < maxAttempts) {
      setTimeout(function() {
        waitAndInitFilePond(attempts + 1);
      }, 100);
    } else {
      
      // Загружаем скрипты сразу, без дополнительных проверок
      if (typeof FilePond !== 'undefined') {
        const fileInputs = modalContent ? modalContent.querySelectorAll('input[type="file"][data-filepond="true"]:not([data-filepond-initialized])') : [];
        if (fileInputs.length > 0) {
          console.log('[REST API DEBUG] Loading FilePond init script immediately for', fileInputs.length, 'input(s)');
          loadFilePondInitScript();
        }
      } else {
        // FilePond библиотека не загружена - загружаем скрипты сразу
        console.log('[REST API DEBUG] FilePond library not loaded, loading scripts immediately...');
        loadFilePondScripts();
      }
    }
  }
  
  // Инициализация модального окна (если есть)
  let modalInstance = null;
  if (modalElement && modalContent && modalDialog && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
    modalInstance = new bootstrap.Modal(modalElement);
  }
  
  // ============================================
  // Обработчик для data-bs-toggle="download"
  // ============================================
  if (downloadButtons.length > 0 && typeof wpApiSettings !== 'undefined') {
    downloadButtons.forEach((button) => {
      button.addEventListener("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        const dataValue = button.getAttribute("data-value");
        if (!dataValue) {
          console.error('Download: data-value not found');
          return;
        }
        
        // Извлекаем ID из data-value (формат: doc-{id}, staff-{id}, vac-{id} или просто {id})
        let postId = dataValue;
        let apiEndpoint = 'documents';
        
        if (dataValue.startsWith('doc-')) {
          postId = dataValue.replace('doc-', '');
          apiEndpoint = 'documents';
        } else if (dataValue.startsWith('staff-')) {
          postId = dataValue.replace('staff-', '');
          apiEndpoint = 'staff';
        } else if (dataValue.startsWith('vac-')) {
          postId = dataValue.replace('vac-', '');
          apiEndpoint = 'vacancies';
        }
        
        postId = parseInt(postId);
        
        if (!postId || isNaN(postId)) {
          console.error('Download: Invalid ID');
          return;
        }
        
        // Сохраняем оригинальное содержимое
        const originalHTML = button.innerHTML;
        const originalDisabled = button.disabled;
        const originalMinHeight = button.style.minHeight || '';
        const currentHeight = button.offsetHeight;
        
        // Устанавливаем минимальную высоту для предотвращения изменения размера кнопки
        if (currentHeight > 0) {
          button.style.minHeight = currentHeight + 'px';
        }
        
        button.disabled = true;
        
        // Показываем аккуратный спиннер (того же размера что и текст)
        const icon = button.querySelector('i');
        const span = button.querySelector('span');
        const loadingText = button.dataset.loadingText || (typeof codeweberDownload !== 'undefined' ? codeweberDownload.loadingText : 'Loading...');
        
        if (icon) {
          // Сохраняем размер иконки (fs-13 или другой), заменяем только иконку на спиннер
          const iconSize = icon.className.match(/fs-\d+/);
          const iconSizeClass = iconSize ? iconSize[0] : 'fs-13';
          
          // Заменяем иконку на спиннер с сохранением размера и добавляем отступ справа
          icon.className = 'uil uil-spinner-alt uil-spin ' + iconSizeClass;
          if (!icon.classList.contains('me-1')) {
            icon.classList.add('me-1');
          }
          
          // Удаляем span обертку, если есть
          if (span) {
            span.remove();
          }
          
          // Добавляем текст загрузки напрямую в кнопку после иконки
          // Удаляем все текстовые узлы после иконки
          let node = icon.nextSibling;
          while (node) {
            const next = node.nextSibling;
            if (node.nodeType === Node.TEXT_NODE || (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'SPAN')) {
              node.remove();
            }
            node = next;
          }
          
          // Добавляем новый текстовый узел с текстом загрузки
          button.appendChild(document.createTextNode(loadingText));
        } else {
          // Если структуры нет, создаем с правильным размером без span
          button.innerHTML = '<i class="uil uil-spinner-alt uil-spin fs-13 me-1"></i>' + loadingText;
        }
        
        // Получаем URL файла через REST API
        const endpoint = apiEndpoint === 'staff' ? 'vcf-url' : 'download-url';
        fetch(wpApiSettings.root + 'codeweber/v1/' + apiEndpoint + '/' + postId + '/' + endpoint, {
          method: 'GET',
          headers: {
            'X-WP-Nonce': wpApiSettings.nonce,
            'Content-Type': 'application/json'
          }
        })
        .then(function(response) {
          if (!response.ok) {
            throw new Error('Failed to get file URL: ' + response.status);
          }
          return response.json();
        })
        .then(function(data) {
          if (data.success && data.file_url) {
            // Программно инициируем загрузку
            const link = document.createElement('a');
            link.href = data.file_url;
            link.download = data.file_name || 'download';
            link.target = '_blank';
            link.style.display = 'none';
            
            document.body.appendChild(link);
            link.click();
            
            setTimeout(function() {
              document.body.removeChild(link);
            }, 100);
            
            // Восстанавливаем кнопку
            button.disabled = originalDisabled;
            button.innerHTML = originalHTML;
            // Восстанавливаем оригинальный minHeight или очищаем
            if (originalMinHeight) {
              button.style.minHeight = originalMinHeight;
            } else {
              button.style.minHeight = '';
            }
            
            // Аналитика
            if (typeof gtag !== 'undefined') {
              gtag('event', 'file_download', {
                'file_name': data.file_name,
                'post_id': data.post_id
              });
            }
          } else {
            throw new Error('Invalid response from server');
          }
        })
        .catch(function(error) {
          console.error('Download error:', error);
          
          // Восстанавливаем кнопку
          button.disabled = originalDisabled;
          button.innerHTML = originalHTML;
          // Восстанавливаем оригинальный minHeight или очищаем
          if (originalMinHeight) {
            button.style.minHeight = originalMinHeight;
          } else {
            button.style.minHeight = '';
          }
          
          alert(typeof codeweberDownload !== 'undefined' ? codeweberDownload.errorText : 'Ошибка при загрузке файла. Попробуйте еще раз.');
        });
      });
    });
  }

  /**
   * Apply modal size class to modal-dialog
   * @param {string} sizeClass - Modal size class (modal-sm, modal-lg, etc.)
   */
  const applyModalSize = (sizeClass) => {
    // Remove all existing size classes
    const sizeClasses = [
      'modal-sm', 'modal-lg', 'modal-xl', 
      'modal-fullscreen', 'modal-fullscreen-sm-down', 
      'modal-fullscreen-md-down', 'modal-fullscreen-lg-down',
      'modal-fullscreen-xl-down', 'modal-fullscreen-xxl-down'
    ];
    
    sizeClasses.forEach(cls => modalDialog.classList.remove(cls));
    
    // Add new size class if provided
    if (sizeClass && sizeClass.trim() !== '') {
      modalDialog.classList.add(sizeClass);
    }
  };

  /**
   * Initialize testimonial rating stars
   */
  function initTestimonialRatingStars() {
    // Use event delegation on modal-content to handle dynamically loaded content
    const modalContent = document.getElementById('modal-content');
    if (!modalContent) {
      return;
    }

    // Remove old event listeners by using a flag
    const ratingContainers = modalContent.querySelectorAll('.rating-stars-wrapper:not([data-initialized])');
    if (ratingContainers.length === 0) return;
    
    ratingContainers.forEach(function(container, index) {
      // Mark as initialized
      container.setAttribute('data-initialized', 'true');
      
      const stars = container.querySelectorAll('.rating-star-item');
      
      const inputId = container.dataset.ratingInput;
      
      let selectedRating = 0;
      
      // Get initial rating from input
      const input = document.getElementById(inputId);
      if (input) {
        if (input.value) {
          selectedRating = parseInt(input.value) || 0;
          updateStarsVisual(stars, selectedRating);
        }
      }
      
      // Click handler - use event delegation
      container.addEventListener('click', function(e) {
        const star = e.target.closest('.rating-star-item');
        if (!star) {
          return;
        }
        
        e.preventDefault();
        e.stopPropagation();
        
        const rating = parseInt(star.dataset.rating);
        selectedRating = rating;
        if (input) {
          input.value = rating;
        }
        updateStarsVisual(stars, rating);
      });
      
      // Hover handlers - highlight all stars from first to current
      stars.forEach(function(star, starIndex) {
        star.addEventListener('mouseenter', function() {
          const hoverRating = parseInt(this.dataset.rating);
          // Highlight all stars from 1 to hoverRating (left to right)
          stars.forEach(function(s) {
            const sRating = parseInt(s.dataset.rating);
            if (sRating <= hoverRating) {
              s.style.color = '#fcc032';
            } else {
              s.style.color = 'rgba(0, 0, 0, 0.1)';
            }
          });
        });
      });
      
      // Reset on mouse leave
      container.addEventListener('mouseleave', function() {
        updateStarsVisual(stars, selectedRating);
      });
    });
  }
  
  /**
   * Update stars visual state
   */
  function updateStarsVisual(stars, rating, isHover) {
    stars.forEach(function(star, index) {
      const starRating = parseInt(star.dataset.rating);
      if (starRating <= rating) {
        star.classList.add('active');
        if (!isHover) {
          star.style.color = '#fcc032';
        }
      } else {
        if (!isHover) {
          star.classList.remove('active');
          star.style.color = 'rgba(0, 0, 0, 0.1)';
        }
      }
    });
  }

  // ✅ Предзагрузка формы в кэш при появлении кнопки в viewport
  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const button = entry.target;
          const dataValue = button
            .getAttribute("data-value")
            ?.replace("modal-", "");
          if (!dataValue) return;

          const cachedContent = localStorage.getItem(dataValue);
          const cachedTime = localStorage.getItem(`${dataValue}_time`);

          if (
            ENABLE_CACHE &&
            cachedContent &&
            cachedTime &&
            Date.now() - cachedTime < 60000
          ) {
            // Уже в кеше — пропускаем
            obs.unobserve(button);
            return;
          }

          // Подгружаем в кэш
          fetch(`${wpApiSettings.root}wp/v2/modal/${dataValue}`)
            .then((response) => {
              if (!response.ok) {
                console.error(`Ошибка при загрузке данных: ${response.status} ${response.statusText}`);
                return response.json().then(err => {
                  throw new Error(err.message || `Ошибка при загрузке данных: ${response.status}`);
                }).catch(() => {
                  throw new Error(`Ошибка при загрузке данных: ${response.status}`);
                });
              }
              return response.json();
            })
            .then((data) => {
              if (data && data.content && data.content.rendered) {
                localStorage.setItem(dataValue, data.content.rendered);
                localStorage.setItem(
                  `${dataValue}_time`,
                  Date.now().toString()
                );
                // Cache modal size as well
                const modalSize = data.modal_size || '';
                localStorage.setItem(`${dataValue}_size`, modalSize);
              }
            })
            .catch((err) => {
              console.error("Ошибка предзагрузки:", err);
            });

          // Убираем наблюдение за этой кнопкой после загрузки
          obs.unobserve(button);
        }
      });
    });

    modalButtons.forEach((button) => {
      observer.observe(button);
    });
  }

  // ✅ Стандартный обработчик клика по кнопке
  modalButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault(); // Prevent default link behavior
      const dataValue = button
        .getAttribute("data-value")
        ?.replace("modal-", "");
      if (!dataValue) {
        return;
      }
      const cachedContent = localStorage.getItem(dataValue);
      const cachedTime = localStorage.getItem(`${dataValue}_time`);
      const cachedSize = localStorage.getItem(`${dataValue}_size`);
      
      // Show modal immediately (standard loader already exists in modal-container.php)
      // Apply cached modal size if available
      if (cachedSize) {
        applyModalSize(cachedSize);
      }
      
      // Open modal immediately
      modalInstance.show();
      
      // Check cache first
      if (
        ENABLE_CACHE &&
        cachedContent &&
        cachedTime &&
        Date.now() - cachedTime < 60000
      ) {
        // Use cached content
        modalContent.innerHTML = cachedContent;
        console.log('[REST API] Cached content loaded, initializing rating stars...');
        
        // КРИТИЧНО: Добавляем временный обработчик submit с preventDefault() СРАЗУ для кэшированного контента
        const cachedCodeweberForms = modalContent.querySelectorAll('.codeweber-form');
        cachedCodeweberForms.forEach(function(form) {
          // Добавляем временный обработчик, который будет удален после инициализации
          const tempSubmitHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            console.warn('[REST API] Form submit prevented - form not yet initialized (cached). Form ID:', form.id || form.dataset.formId || 'unknown');
            return false;
          };
          form.addEventListener('submit', tempSubmitHandler, true); // Используем capture phase
          // Сохраняем ссылку на обработчик для последующего удаления
          form._tempSubmitHandler = tempSubmitHandler;
          // Удаляем обработчик после инициализации (через небольшую задержку)
          setTimeout(function() {
            if (form._tempSubmitHandler) {
              form.removeEventListener('submit', form._tempSubmitHandler, true);
              delete form._tempSubmitHandler;
            }
          }, 1000); // Удаляем через 1 секунду (достаточно для инициализации)
        });
        
        // Initialize rating stars if present (with small delay to ensure DOM is ready)
        setTimeout(function() {
          console.log('[REST API] Calling initTestimonialRatingStars after timeout (cached)');
          initTestimonialRatingStars();
        }, 50);
        
        // Инициализируем Contact Form 7 формы
        const formElement = modalContent.querySelector("form.wpcf7-form");
        if (formElement && typeof wpcf7 !== "undefined") {
          wpcf7.init(formElement);
          custom.cf7CloseAfterSent();
          custom.formValidation();
          custom.addTelMask();
          custom.rippleEffect();
          custom.formSubmittingWatcher();
        }
        
        // Инициализируем Codeweber формы СРАЗУ для кэшированного контента
        // Формы будут инициализированы через shown.bs.modal и MutationObserver в form-submit-universal.js
        if (cachedCodeweberForms.length > 0) {
          console.log('[REST API] Found', cachedCodeweberForms.length, 'Codeweber form(s) in cached content');
        }
        
        // Инициализируем FilePond для кэшированного контента
        
        // Используем ту же функцию waitAndInitFilePond для кэшированного контента
        waitAndInitFilePond();
      } else {
        // Load content via API
        // Add user_id as query parameter if user is logged in
        let apiUrl = `${wpApiSettings.root}wp/v2/modal/${dataValue}`;
        if (wpApiSettings.isLoggedIn && wpApiSettings.currentUserId) {
          apiUrl += `?user_id=${wpApiSettings.currentUserId}`;
        }
        
        fetch(apiUrl, {
          credentials: 'include' // Include cookies for authentication
        })
          .then((response) => {
            if (!response.ok)
              throw new Error("Ошибка при загрузке данных с сервера");
            return response.json();
          })
          .then((data) => {
            if (data && data.content && data.content.rendered) {
              // Apply modal size from API
              const modalSize = data.modal_size || '';
              applyModalSize(modalSize);
              
              if (ENABLE_CACHE) {
                localStorage.setItem(dataValue, data.content.rendered);
                localStorage.setItem(
                  `${dataValue}_time`,
                  Date.now().toString()
                );
                localStorage.setItem(`${dataValue}_size`, modalSize);
              }
              
              modalContent.innerHTML = data.content.rendered;
              console.log('[REST API] Content loaded, initializing rating stars...');
              
              // КРИТИЧНО: Добавляем временный обработчик submit с preventDefault() СРАЗУ после загрузки контента
              // Это предотвратит обычную отправку формы до инициализации AJAX обработчика
              const codeweberForms = modalContent.querySelectorAll('.codeweber-form');
              console.log('[REST API] Found', codeweberForms.length, 'Codeweber form(s) in modal, adding temporary submit handlers');
              codeweberForms.forEach(function(form) {
                // Добавляем временный обработчик, который будет удален после инициализации
                const tempSubmitHandler = function(e) {
                  e.preventDefault();
                  e.stopPropagation();
                  e.stopImmediatePropagation();
                  console.warn('[REST API] Form submit prevented - form not yet initialized. Form ID:', form.id || form.dataset.formId || 'unknown', 'Initialized:', form.dataset.initialized);
                  return false;
                };
                form.addEventListener('submit', tempSubmitHandler, true); // Используем capture phase
                // Сохраняем ссылку на обработчик для последующего удаления
                form._tempSubmitHandler = tempSubmitHandler;
                console.log('[REST API] Temporary submit handler added to form:', form.id || form.dataset.formId || 'unknown');
                // Удаляем обработчик после инициализации (проверяем каждые 100ms)
                const checkAndRemoveHandler = function(attempts) {
                  if (attempts > 20) {
                    // Максимум 2 секунды, затем удаляем в любом случае
                    if (form._tempSubmitHandler) {
                      form.removeEventListener('submit', form._tempSubmitHandler, true);
                      delete form._tempSubmitHandler;
                      console.log('[REST API] Temporary submit handler removed (timeout) for form:', form.id || form.dataset.formId || 'unknown');
                    }
                    return;
                  }
                  if (form.dataset.initialized === 'true' && form._codeweberSubmitHandler) {
                    // Форма инициализирована, удаляем временный обработчик
                    if (form._tempSubmitHandler) {
                      form.removeEventListener('submit', form._tempSubmitHandler, true);
                      delete form._tempSubmitHandler;
                      console.log('[REST API] Temporary submit handler removed (form initialized) for form:', form.id || form.dataset.formId || 'unknown');
                    }
                  } else {
                    // Проверяем снова через 100ms
                    setTimeout(function() {
                      checkAndRemoveHandler(attempts + 1);
                    }, 100);
                  }
                };
                checkAndRemoveHandler(0);
              });
              
              
              // Initialize rating stars if present (with small delay to ensure DOM is ready)
              setTimeout(function() {
                console.log('[REST API] Calling initTestimonialRatingStars after timeout');
                initTestimonialRatingStars();
              }, 50);
              
              // Инициализируем обработчик формы отправки документа на email
              initDocumentEmailForm();
              
              // Инициализируем Contact Form 7 формы
              const formElement = modalContent.querySelector("form.wpcf7-form");
              if (formElement && typeof wpcf7 !== "undefined") {
                wpcf7.init(formElement);
                custom.cf7CloseAfterSent();
                custom.formValidation();
                custom.addTelMask();
                custom.rippleEffect();
                custom.formSubmittingWatcher();
              }
              
              // Инициализируем Codeweber формы СРАЗУ после загрузки контента
              // Это критично, чтобы форма не отправлялась через обычный POST
              // MutationObserver в form-submit-universal.js должен сработать автоматически,
              // но для надежности вызываем инициализацию явно
              if (codeweberForms.length > 0) {
                console.log('[REST API] Found', codeweberForms.length, 'Codeweber form(s), triggering initialization...');
                // Используем requestAnimationFrame для гарантии, что DOM готов
                requestAnimationFrame(function() {
                  // Вызываем initForms() явно, если она доступна
                  if (typeof window.initForms === 'function') {
                    console.log('[REST API] Calling window.initForms() explicitly');
                    window.initForms();
                  } else {
                    console.warn('[REST API] window.initForms is not available, MutationObserver should handle initialization');
                  }
                  // Проверяем через небольшую задержку, инициализированы ли формы
                  setTimeout(function() {
                    const uninitializedForms = Array.from(codeweberForms).filter(f => !f.dataset.initialized || f.dataset.initialized === 'false');
                    if (uninitializedForms.length > 0) {
                      console.warn('[REST API]', uninitializedForms.length, 'form(s) still not initialized after explicit initForms call');
                    }
                  }, 200);
                });
              }
              
              // Инициализируем FilePond для форм, загруженных через AJAX
              
              // Начинаем ожидание и инициализацию
              waitAndInitFilePond();
              
            } else {
              modalContent.innerHTML = "Контент не найден.";
            }
          })
          .catch(() => {
            modalContent.innerHTML = "Произошла ошибка при загрузке данных.";
          });
      }
    });
  });

  /**
   * Инициализация обработчика формы отправки документа на email
   */
  function initDocumentEmailForm() {
    const documentEmailForm = modalContent.querySelector("#document-email-form");
    if (!documentEmailForm) {
      return;
    }
    
    // Удаляем старый обработчик, если он есть
    const newForm = documentEmailForm.cloneNode(true);
    documentEmailForm.parentNode.replaceChild(newForm, documentEmailForm);
    
    // Добавляем новый обработчик
    newForm.addEventListener("submit", function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      console.log('[Document Email] Form submitted');
      
      const formData = new FormData(newForm);
      const submitButton = newForm.querySelector('button[type="submit"]');
      const originalButtonHTML = submitButton ? submitButton.innerHTML : '';
      const originalButtonDisabled = submitButton ? submitButton.disabled : false;
      const originalMinHeight = submitButton ? (submitButton.style.minHeight || '') : '';
      
        if (submitButton) {
          // Сохраняем текущую высоту кнопки для предотвращения изменения размера
          const currentHeight = submitButton.offsetHeight;
          if (currentHeight > 0) {
            submitButton.style.minHeight = currentHeight + 'px';
          }
          
          submitButton.disabled = true;
          const sendingText = typeof codeweberDocumentEmail !== 'undefined' ? codeweberDocumentEmail.sendingText : 'Sending...';
          const loadingText = submitButton.dataset.loadingText || sendingText;
          const icon = submitButton.querySelector('i');
          const span = submitButton.querySelector('span');
          
          if (icon && span) {
            // Сохраняем размер иконки (fs-15 или другой), заменяем только иконку на спиннер
            const iconSize = icon.className.match(/fs-\d+/);
            const iconSizeClass = iconSize ? iconSize[0] : 'fs-15';
            icon.className = 'uil uil-spinner-alt uil-spin ' + iconSizeClass;
            span.textContent = loadingText;
            // Добавляем отступ между спиннером и текстом
            if (!span.classList.contains('ms-1')) {
              span.classList.add('ms-1');
            }
          } else {
            submitButton.innerHTML = '<i class="uil uil-spinner-alt uil-spin fs-15"></i> <span class="ms-1">' + loadingText + '</span>';
          }
        }
      
      // Отправляем AJAX запрос
      const requestData = {
        document_id: parseInt(formData.get('document_id')),
        email: formData.get('email')
      };
      
      console.log('[Document Email] Sending request:', requestData);
      console.log('[Document Email] Using nonce:', wpApiSettings.nonce);
      
      fetch(wpApiSettings.root + 'codeweber/v1/documents/send-email', {
        method: 'POST',
        headers: {
          'X-WP-Nonce': wpApiSettings.nonce,
          'Content-Type': 'application/json'
        },
        credentials: 'include', // Важно для передачи cookies
        body: JSON.stringify(requestData)
      })
      .then(function(response) {
        console.log('[Document Email] Response status:', response.status);
        if (!response.ok) {
          return response.json().then(function(err) {
            const serverErrorText = typeof codeweberDocumentEmail !== 'undefined' ? codeweberDocumentEmail.serverError : 'Server error';
            throw new Error(err.message || serverErrorText + ': ' + response.status);
          });
        }
        return response.json();
      })
      .then(function(data) {
        console.log('[Document Email] Response data:', data);
        
        if (data.success) {
          // Получаем шаблон успешной отправки через REST API
          const successText = typeof codeweberDocumentEmail !== 'undefined' ? codeweberDocumentEmail.successText : 'Document sent successfully!';
          const message = data.message || successText;
          
          fetch(wpApiSettings.root + 'codeweber/v1/success-message-template?message=' + encodeURIComponent(message) + '&icon_type=svg', {
            method: 'GET',
            headers: {
              'X-WP-Nonce': wpApiSettings.nonce,
              'Content-Type': 'application/json'
            }
          })
          .then(function(response) {
            return response.json();
          })
          .then(function(templateData) {
            if (templateData.success && templateData.html) {
              // modal-body и btn-close уже есть в модальном окне, добавляем только контент
              modalContent.innerHTML = templateData.html;
              
              // Закрываем модальное окно через 2 секунды
              setTimeout(function() {
                modalInstance.hide();
              }, 2000);
            } else {
              // Fallback на простой шаблон
              modalContent.innerHTML = '<div class="text-center p-5"><div class="mb-3"><i class="uil uil-check-circle text-success" style="font-size: 3rem;"></i></div><h5>' + message + '</h5></div>';
              setTimeout(function() {
                modalInstance.hide();
              }, 2000);
            }
          })
          .catch(function(error) {
            console.error('[Document Email] Error loading success template:', error);
            // Fallback на простой шаблон
            modalContent.innerHTML = '<div class="text-center p-5"><div class="mb-3"><i class="uil uil-check-circle text-success" style="font-size: 3rem;"></i></div><h5>' + message + '</h5></div>';
            setTimeout(function() {
              modalInstance.hide();
            }, 2000);
          });
        } else {
          throw new Error(data.message || 'Failed to send email');
        }
      })
      .catch(function(error) {
        console.error('[Document Email] Error:', error);
        
        if (submitButton) {
          submitButton.disabled = originalButtonDisabled;
          submitButton.innerHTML = originalButtonHTML;
          // Восстанавливаем оригинальный minHeight или очищаем
          if (originalMinHeight) {
            submitButton.style.minHeight = originalMinHeight;
          } else {
            submitButton.style.minHeight = '';
          }
        }
        
        // Показываем ошибку в модальном окне, не закрывая его
        const errorText = typeof codeweberDocumentEmail !== 'undefined' ? codeweberDocumentEmail.errorText : 'Error sending email. Please try again.';
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger mt-3';
        errorDiv.textContent = error.message || errorText;
        newForm.appendChild(errorDiv);
        
        // Убираем сообщение об ошибке через 5 секунд
        setTimeout(function() {
          if (errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv);
          }
        }, 5000);
      });
    });
  }

  modalElement.addEventListener("shown.bs.modal", function () {
    const formElement = modalContent.querySelector("form.wpcf7-form");
    if (formElement && typeof wpcf7 !== "undefined") {
      wpcf7.init(formElement);
    }
    
    // Initialize rating stars if present
    initTestimonialRatingStars();
    
    // Инициализируем обработчик формы отправки документа на email
    initDocumentEmailForm();
  });
});
