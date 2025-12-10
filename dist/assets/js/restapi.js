document.addEventListener("DOMContentLoaded", () => {
  const ENABLE_CACHE = false;
  const modalButtons = document.querySelectorAll('a[data-bs-toggle="modal"]');
  const modalElement = document.getElementById("modal");
  const modalContent = document.getElementById("modal-content");
  const modalDialog = modalElement ? modalElement.querySelector(".modal-dialog") : null;
  
  if (!modalElement || !modalContent || !modalDialog) {
    return;
  }

  const modalInstance = new bootstrap.Modal(modalElement);

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
    console.log('[Rating Stars] Initialization started');
    
    // Use event delegation on modal-content to handle dynamically loaded content
    const modalContent = document.getElementById('modal-content');
    if (!modalContent) {
      console.log('[Rating Stars] Modal content not found');
      return;
    }
    
    console.log('[Rating Stars] Modal content found:', modalContent);
    
    // Remove old event listeners by using a flag
    const ratingContainers = modalContent.querySelectorAll('.rating-stars-wrapper:not([data-initialized])');
    console.log('[Rating Stars] Found containers:', ratingContainers.length);
    
    if (ratingContainers.length === 0) {
      console.log('[Rating Stars] No uninitialized containers found');
      return;
    }
    
    ratingContainers.forEach(function(container, index) {
      console.log('[Rating Stars] Initializing container', index + 1);
      
      // Mark as initialized
      container.setAttribute('data-initialized', 'true');
      
      const stars = container.querySelectorAll('.rating-star-item');
      console.log('[Rating Stars] Found stars:', stars.length);
      
      const inputId = container.dataset.ratingInput;
      console.log('[Rating Stars] Input ID:', inputId);
      
      let selectedRating = 0;
      
      // Get initial rating from input
      const input = document.getElementById(inputId);
      if (input) {
        console.log('[Rating Stars] Input found:', input);
        if (input.value) {
          selectedRating = parseInt(input.value) || 0;
          console.log('[Rating Stars] Initial rating:', selectedRating);
          updateStarsVisual(stars, selectedRating);
        }
      } else {
        console.log('[Rating Stars] Input not found for ID:', inputId);
      }
      
      // Click handler - use event delegation
      container.addEventListener('click', function(e) {
        console.log('[Rating Stars] Click event on container');
        const star = e.target.closest('.rating-star-item');
        if (!star) {
          console.log('[Rating Stars] Click not on star');
          return;
        }
        
        console.log('[Rating Stars] Click on star:', star);
        e.preventDefault();
        e.stopPropagation();
        
        const rating = parseInt(star.dataset.rating);
        console.log('[Rating Stars] Selected rating:', rating);
        selectedRating = rating;
        if (input) {
          input.value = rating;
          console.log('[Rating Stars] Input value set to:', rating);
        }
        updateStarsVisual(stars, rating);
        console.log('[Rating Stars] Stars visual updated');
      });
      
      // Hover handlers - highlight all stars from first to current
      stars.forEach(function(star, starIndex) {
        star.addEventListener('mouseenter', function() {
          console.log('[Rating Stars] Hover on star:', starIndex + 1);
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
        console.log('[Rating Stars] Mouse leave, resetting to:', selectedRating);
        updateStarsVisual(stars, selectedRating);
      });
      
      console.log('[Rating Stars] Container', index + 1, 'initialized successfully');
    });
    
    console.log('[Rating Stars] Initialization completed');
  }
  
  /**
   * Update stars visual state
   */
  function updateStarsVisual(stars, rating, isHover) {
    console.log('[Rating Stars] updateStarsVisual called with rating:', rating, 'isHover:', isHover);
    stars.forEach(function(star, index) {
      const starRating = parseInt(star.dataset.rating);
      if (starRating <= rating) {
        star.classList.add('active');
        if (!isHover) {
          star.style.color = '#fcc032';
          console.log('[Rating Stars] Star', index + 1, 'activated (rating:', starRating, ')');
        }
      } else {
        if (!isHover) {
          star.classList.remove('active');
          star.style.color = 'rgba(0, 0, 0, 0.1)';
          console.log('[Rating Stars] Star', index + 1, 'deactivated (rating:', starRating, ')');
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
              if (!response.ok) throw new Error("Ошибка при загрузке данных");
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
        
        // Initialize rating stars if present (with small delay to ensure DOM is ready)
        setTimeout(function() {
          console.log('[REST API] Calling initTestimonialRatingStars after timeout (cached)');
          initTestimonialRatingStars();
        }, 50);
        
        const formElement = modalContent.querySelector("form.wpcf7-form");
        if (formElement && typeof wpcf7 !== "undefined") {
          wpcf7.init(formElement);
          custom.cf7CloseAfterSent();
          custom.formValidation();
          custom.addTelMask();
          custom.rippleEffect();
          custom.formSubmittingWatcher();
        }
        
        // Initialize testimonial form if present
        const testimonialForm = modalContent.querySelector('#testimonial-form');
        if (testimonialForm && typeof initTestimonialForm === 'function') {
          setTimeout(function() {
            initTestimonialForm();
          }, 100);
        }
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
              
              // Initialize rating stars if present (with small delay to ensure DOM is ready)
              setTimeout(function() {
                console.log('[REST API] Calling initTestimonialRatingStars after timeout');
                initTestimonialRatingStars();
              }, 50);
              
              const formElement = modalContent.querySelector("form.wpcf7-form");
              if (formElement && typeof wpcf7 !== "undefined") {
                wpcf7.init(formElement);
                custom.cf7CloseAfterSent();
                custom.formValidation();
                custom.addTelMask();
                custom.rippleEffect();
                custom.formSubmittingWatcher();
              }
              
              // Initialize testimonial form if present
              const testimonialForm = modalContent.querySelector('#testimonial-form');
              if (testimonialForm && typeof initTestimonialForm === 'function') {
                setTimeout(function() {
                  initTestimonialForm();
                }, 100);
              }
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

  modalElement.addEventListener("shown.bs.modal", function () {
    const formElement = modalContent.querySelector("form.wpcf7-form");
    if (formElement && typeof wpcf7 !== "undefined") {
      wpcf7.init(formElement);
    }
  });
});
