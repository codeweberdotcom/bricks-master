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

    // Показываем лоадер до запроса
    if (wrapperElement) {
      wrapperElement.innerHTML = `
        <div class="fetch-loader" style="text-align:center;padding:1em;">
          <span class="spinner" style="display:inline-block;width:36px;height:36px;border:5px solid #ccc;border-top-color:#000;border-radius:50%;animation:spin 1s linear infinite;"></span>
        </div>
      `;
    }

    const formData = new FormData();
    formData.append("action", "fetch_action");
    formData.append("actionType", action);
    formData.append("params", JSON.stringify(params));

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
