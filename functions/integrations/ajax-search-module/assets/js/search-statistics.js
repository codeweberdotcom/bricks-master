// search-statistics.js - отслеживание статистики поиска (нативный JavaScript)
(function () {
  "use strict";

  let searchTrackingEnabled = true;
  let lastSearchQuery = "";
  let searchTimeout = null;

  // Функция для отслеживания поисковых запросов
  function trackSearchQuery(
    searchQuery,
    resultsCount = 0,
    formId = "",
    searchParams = {}
  ) {
    if (!searchTrackingEnabled || searchQuery.length < 3) {
      return;
    }

    // Предотвращаем дублирование одинаковых запросов в короткий промежуток времени
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
      ...searchParams,
    };

    fetch(window.search_stats_params.ajaxurl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams(formData),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          console.log(
            "Search query saved:",
            searchQuery,
            "Results:",
            resultsCount
          );
        } else {
          console.error("Error:", data.data);
        }
      })
      .catch((error) => {
        console.error("Search tracking error:", error);
      });
  }

  // Получаем ID формы из input элемента
  function getFormIdFromInput(input) {
    if (!input) return "";

    // Ищем ближайший form элемент
    const form = input.closest("form");
    if (form) {
      return form.id || input.id || "";
    }
    return input.id || "";
  }

  // Отслеживаем события ввода в поисковых полях
  function initializeSearchTracking() {
    // Отслеживаем стандартные формы поиска WordPress
    const searchInputs = document.querySelectorAll(
      'form[role="search"] input[type="search"]'
    );
    searchInputs.forEach((input) => {
      input.addEventListener("input", function () {
        const query = this.value.trim();
        const formId = getFormIdFromInput(this);
        clearTimeout(searchTimeout);

        if (query.length >= 3) {
          searchTimeout = setTimeout(
            () => trackSearchQuery(query, 0, formId),
            1000
          );
        }
      });
    });

    // Отслеживаем AJAX поиск
    const ajaxSearchInputs = document.querySelectorAll(
      '.search-form input[type="text"]'
    );
    ajaxSearchInputs.forEach((input) => {
      input.addEventListener("input", function () {
        const query = this.value.trim();
        const formId = getFormIdFromInput(this);
        clearTimeout(searchTimeout);

        if (query.length >= 3) {
          searchTimeout = setTimeout(
            () => trackSearchQuery(query, 0, formId),
            1000
          );
        }
      });
    });

    // Отслеживаем отправку форм поиска
    const searchForms = document.querySelectorAll('form[role="search"]');
    searchForms.forEach((form) => {
      form.addEventListener("submit", function (e) {
        const searchInput = this.querySelector('input[type="search"]');
        const query = searchInput ? searchInput.value.trim() : "";
        const formId = getFormIdFromInput(this);
        if (query.length >= 3) {
          trackSearchQuery(query, 0, formId);
        }
      });
    });

    // Перехватываем AJAX запросы поиска с помощью fetch interception
    interceptAjaxSearches();
  }

  // Перехват AJAX запросов поиска
  function interceptAjaxSearches() {
    const originalFetch = window.fetch;
    window.fetch = function (...args) {
      return originalFetch.apply(this, args).then((response) => {
        const clone = response.clone();

        // Проверяем, это ли наш поисковый запрос
        if (
          args[0] &&
          typeof args[0] === "string" &&
          args[0].includes("admin-ajax.php")
        ) {
          const urlParams = new URLSearchParams(args[1]?.body || "");
          if (urlParams.get("action") === "ajax_search") {
            const searchQuery = urlParams.get("search_query");
            if (searchQuery && searchQuery.length >= 3) {
              clone
                .json()
                .then((data) => {
                  if (data.success && data.data) {
                    const resultsCount = data.data.found_posts || 0;
                    const activeInput = document.querySelector(
                      '.search-form input[type="text"]:focus'
                    );
                    const formId = activeInput
                      ? getFormIdFromInput(activeInput)
                      : "";
                    trackSearchQuery(searchQuery, resultsCount, formId);
                  }
                })
                .catch(() => {});
            }
          }
        }

        return response;
      });
    };
  }

  // Инициализация при загрузке документа
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeSearchTracking);
  } else {
    initializeSearchTracking();
  }
})();
