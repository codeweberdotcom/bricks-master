document.querySelectorAll("[data-fetch]").forEach((button) => {
  button.addEventListener("click", async (e) => {
    e.preventDefault();

    const action = button.getAttribute("data-fetch");
    const params = button.getAttribute("data-params")
      ? JSON.parse(button.getAttribute("data-params"))
      : {};
    const wrapperSelector = button.getAttribute("data-wrapper");
    const wrapperElement = wrapperSelector
      ? document.querySelector(wrapperSelector)
      : null;

    if (!action) {
      if (wrapperElement) {
        wrapperElement.innerHTML =
          '<p style="color: red;">Ошибка: не указано действие!</p>';
      }
      return;
    }

    // Показываем skeleton до запроса
    if (wrapperElement) {
      wrapperElement.innerHTML =
        '<div class="p-4">' +
          '<div class="cw-skeleton-block mb-3" style="height:1.1em;width:55%"></div>' +
          '<div class="cw-skeleton-block mb-2" style="height:.85em;width:100%"></div>' +
          '<div class="cw-skeleton-block mb-2" style="height:.85em;width:80%"></div>' +
          '<div class="cw-skeleton-block"      style="height:.85em;width:65%"></div>' +
        '</div>';
    }

    const formData = new FormData();
    formData.append("action", "fetch_action");
    formData.append("actionType", action);
    formData.append("params", JSON.stringify(params));
    formData.append("nonce", fetch_vars.nonce);

    try {
      const response = await fetch(fetch_vars.ajaxurl, {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (wrapperElement) {
        if (result.status === "success") {
          wrapperElement.innerHTML = result.data;
        } else {
          wrapperElement.innerHTML = `<p style="color: red;">Ошибка: ${result.message}</p>`;
        }
      }
    } catch (error) {
      if (wrapperElement) {
        wrapperElement.innerHTML = `<p style="color: red;">Ошибка: ${error.message}</p>`;
      }
      console.error("Ошибка:", error);
    }
  });
});
