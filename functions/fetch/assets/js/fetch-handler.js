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

    try {
      const response = await fetch(ajaxurl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "fetch_action",
          actionType: action,
          params,
        }),
      });

      const result = await response.json();

      if (wrapperElement) {
        if (result.status === "success") {
          wrapperElement.innerHTML = `<pre>${JSON.stringify(
            result.data,
            null,
            2
          )}</pre>`;
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
