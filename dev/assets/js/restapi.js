document.addEventListener("DOMContentLoaded", () => {
  const ENABLE_CACHE = false;
  const modalButtons = document.querySelectorAll('a[data-bs-toggle="modal"]');
  const modalElement = document.getElementById("modal");
  const modalContent = document.getElementById("modal-content");
  if (!modalElement || !modalContent) {
    return;
  }

  const modalInstance = new bootstrap.Modal(modalElement);

  // ✅ Предзагрузка формы в кэш при появлении кнопки в viewport
  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const button = entry.target;
          const dataValueAttr = button.getAttribute("data-value");
          const dataValue = dataValueAttr
            ? dataValueAttr.replace("modal-", "")
            : null;
          if (!dataValue) return;

          const cachedContent = localStorage.getItem(dataValue);
          const cachedTime = localStorage.getItem(dataValue + "_time");

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
          fetch(wpApiSettings.root + "wp/v2/modal/" + dataValue)
            .then((response) => {
              if (!response.ok) throw new Error("Ошибка при загрузке данных");
              return response.json();
            })
            .then((data) => {
              if (data && data.content && data.content.rendered) {
                localStorage.setItem(dataValue, data.content.rendered);
                localStorage.setItem(
                  dataValue + "_time",
                  Date.now().toString()
                );
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
      const dataValueAttr = button.getAttribute("data-value");
      const dataValue = dataValueAttr
        ? dataValueAttr.replace("modal-", "")
        : null;
      if (!dataValue) {
        return;
      }
      const cachedContent = localStorage.getItem(dataValue);
      const cachedTime = localStorage.getItem(dataValue + "_time");
      if (
        ENABLE_CACHE &&
        cachedContent &&
        cachedTime &&
        Date.now() - cachedTime < 60000
      ) {
        modalContent.innerHTML = cachedContent;
        modalInstance.show();
      } else {
        modalContent.innerHTML = '<div class="modal-loader"></div>';
        fetch(wpApiSettings.root + "wp/v2/modal/" + dataValue)
          .then((response) => {
            if (!response.ok)
              throw new Error("Ошибка при загрузке данных с сервера");
            return response.json();
          })
          .then((data) => {
            if (data && data.content && data.content.rendered) {
              if (ENABLE_CACHE) {
                localStorage.setItem(dataValue, data.content.rendered);
                localStorage.setItem(
                  dataValue + "_time",
                  Date.now().toString()
                );
              }
              modalContent.innerHTML = data.content.rendered;
              const formElement = modalContent.querySelector("form.wpcf7-form");
              if (formElement && typeof wpcf7 !== "undefined") {
                wpcf7.init(formElement);
                if (typeof custom !== "undefined") {
                  if (custom.cf7CloseAfterSent) custom.cf7CloseAfterSent();
                  if (custom.formValidation) custom.formValidation();
                  if (custom.addTelMask) custom.addTelMask();
                  if (custom.rippleEffect) custom.rippleEffect();
                  if (custom.formSubmittingWatcher)
                    custom.formSubmittingWatcher();
                }
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
