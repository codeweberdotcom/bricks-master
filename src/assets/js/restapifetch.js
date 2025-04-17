document.addEventListener("DOMContentLoaded", () => {
  // Константа для включения/отключения кэширования
  const ENABLE_CACHE = false;
  const modalButtons = document.querySelectorAll('a[data-bs-toggle="modal"]');
  const modalElement = document.getElementById("modal");
  const modalContent = document.getElementById("modal-content");
  if (!modalElement || !modalContent) {
    return;
  }

  const modalInstance = new bootstrap.Modal(modalElement);
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

        fetch(`${wpApiSettings.root}wp/v2/modal/${dataValue}`)
          .then((response) => {
            if (!response.ok) {
              throw new Error("Ошибка при загрузке данных с сервера");
            }
            return response.json();
          })
          .then((data) => {
            if (data && data.content && data.content.rendered) {
              if (ENABLE_CACHE) {
                localStorage.setItem(dataValue, data.content.rendered);
                localStorage.setItem(
                  `${dataValue}_time`,
                  Date.now().toString()
                );
              }
              modalContent.innerHTML = data.content.rendered;
              const formElement = modalContent.querySelector("form.wpcf7-form");
              if (formElement && typeof wpcf7 !== "undefined") {
                wpcf7.init(formElement);
                theme.cf7BlockDoubleClick();
                theme.formValidation();
                theme.addTelMask();

                // Обработка нажатия на кнопку отправки
                const submitButton = formElement.querySelector(
                  'button[type="submit"], input[type="submit"]'
                );
                if (submitButton) {
                  const originalText = submitButton.innerHTML;

                  formElement.addEventListener("submit", () => {
                    submitButton.disabled = true;
                    submitButton.innerHTML = `
                       Отправка... <i class="uil uil-envelope-upload ms-2"></i>
                    `;
                  });

                  // Восстановление текста кнопки после отправки
                  const resetButton = () => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                  };

                  formElement.addEventListener("wpcf7mailsent", resetButton);
                  formElement.addEventListener("wpcf7invalid", resetButton);
                  formElement.addEventListener("wpcf7spam", resetButton);
                  formElement.addEventListener("wpcf7error", resetButton);
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
