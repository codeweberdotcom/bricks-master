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
    button.addEventListener("click", () => {
      const dataValue = button
        .getAttribute("data-value")
        ?.replace("modal-", "");
      if (!dataValue) {
        return;
      }
      const cachedContent = localStorage.getItem(dataValue);
      const cachedTime = localStorage.getItem(`${dataValue}_time`);
      const cachedSize = localStorage.getItem(`${dataValue}_size`);
      
      if (
        ENABLE_CACHE &&
        cachedContent &&
        cachedTime &&
        Date.now() - cachedTime < 60000
      ) {
        // Apply cached modal size
        if (cachedSize) {
          applyModalSize(cachedSize);
        }
        modalContent.innerHTML = cachedContent;
        modalInstance.show();
      } else {
        modalContent.innerHTML = '<div class="modal-loader"></div>';
        fetch(`${wpApiSettings.root}wp/v2/modal/${dataValue}`)
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
              const formElement = modalContent.querySelector("form.wpcf7-form");
              if (formElement && typeof wpcf7 !== "undefined") {
                wpcf7.init(formElement);
                custom.cf7CloseAfterSent();
                custom.formValidation();
                custom.addTelMask();
                custom.rippleEffect();
                custom.formSubmittingWatcher();

              }
              modalInstance.show();
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
