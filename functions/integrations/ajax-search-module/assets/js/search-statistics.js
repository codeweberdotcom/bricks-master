// search-statistics.js - отслеживание статистики поиска (упрощенная версия)

(function () {
  "use strict";

  let lastSearchQuery = "";

  // Функция для отслеживания поисковых запросов
  function trackSearchQuery(searchQuery, resultsCount = 0, formId = "") {
    if (!searchQuery || searchQuery.length < 3) {
      return;
    }

    // Предотвращаем дублирование
    if (searchQuery === lastSearchQuery) {
      return;
    }

    lastSearchQuery = searchQuery;

    const formData = {
      action: "save_search_query",
      search_query: searchQuery,
      results_count: resultsCount,
      form_id: formId,
      nonce: window.search_stats_params.nonce,
    };

    fetch(window.search_stats_params.ajaxurl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams(formData),
    }).catch((error) => {
      // Ошибка обрабатывается бесшумно
    });
  }

  // Получаем ID формы из input элемента
  function getFormIdFromInput(input) {
    if (!input) return "";
    const form = input.closest("form");
    return form ? form.id || input.id || "" : input.id || "";
  }

  // Перехват ТОЛЬКО поисковых AJAX запросов
  function interceptAjaxSearches() {
    const originalFetch = window.fetch;

    window.fetch = function (...args) {
      const url = args[0];
      const options = args[1] || {};

      // Пропускаем ВСЕ запросы, кроме наших поисковых
      if (
        typeof url === "string" &&
        url.includes("admin-ajax.php") &&
        options.body
      ) {
        try {
          const urlParams = new URLSearchParams(options.body);
          if (urlParams.get("action") === "ajax_search") {
            const searchQuery = urlParams.get("search_query");

            if (searchQuery && searchQuery.length >= 3) {
              // ТОЛЬКО для поисковых запросов делаем перехват
              return originalFetch.apply(this, args).then((response) => {
                const clone = response.clone();

                clone
                  .json()
                  .then((data) => {
                    let resultsCount = 0;

                    if (data.success && data.data) {
                      if (data.data.found_posts !== undefined) {
                        resultsCount = data.data.found_posts;
                      } else if (data.data.total_found !== undefined) {
                        resultsCount = data.data.total_found;
                      } else if (data.data.all_results) {
                        data.data.all_results.forEach((group) => {
                          resultsCount += group.total_found || 0;
                        });
                      }
                    }

                    const activeInput = document.querySelector(
                      '.search-form input[type="text"]:focus'
                    );
                    const formId = activeInput
                      ? getFormIdFromInput(activeInput)
                      : "";

                    trackSearchQuery(searchQuery, resultsCount, formId);
                  })
                  .catch(() => {
                    // При ошибке все равно сохраняем запрос
                    const activeInput = document.querySelector(
                      '.search-form input[type="text"]:focus'
                    );
                    const formId = activeInput
                      ? getFormIdFromInput(activeInput)
                      : "";
                    trackSearchQuery(searchQuery, 0, formId);
                  });

                return response;
              });
            }
          }
        } catch (e) {
          // Если не можем разобрать body, пропускаем запрос
        }
      }

      // Для всех остальных URL пропускаем запрос без изменений
      return originalFetch.apply(this, args);
    };
  }

  // Инициализация
  function initializeSearchTracking() {
    interceptAjaxSearches();
  }

  // Запуск
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeSearchTracking);
  } else {
    initializeSearchTracking();
  }
})();
