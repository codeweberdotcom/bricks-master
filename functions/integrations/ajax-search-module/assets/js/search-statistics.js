// search-statistics.js - отслеживание статистики поиска (нативный JavaScript)
(function () {
  "use strict";

  let searchTrackingEnabled = true;
  let lastSearchQuery = "";
  let searchTimeout = null;

  // Функция для получения cookie по имени
  function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
    return "";
  }

  // Функция для получения ВСЕХ возможных Matomo cookies
  function getAllMatomoCookies() {
    console.log("=== COMPLETE MATOMO COOKIES SCAN ===");

    // Все возможные имена cookies Matomo
    const allMatomoCookieNames = [
      // Стандартные Matomo
      "_pk_id",
      "_pk_ref",
      "_pk_ses",
      "_pk_cvar",
      "_pk_hsr",
      // Альтернативные/кастомные
      "_matomo_visitor_id",
      "MATOMO_SESSID",
      "matomo_sessid",
      "piwik_visitor",
      "_piwik_visitor",
      // Consent cookies
      "matomo_ignore",
      "mtm_consent",
      "mtm_cookie_consent",
    ];

    const foundCookies = {};

    allMatomoCookieNames.forEach((cookieName) => {
      const value = getCookie(cookieName);
      if (value) {
        foundCookies[cookieName] = value;
        console.log(`✅ FOUND: ${cookieName} = ${value}`);
      } else {
        console.log(`❌ MISSING: ${cookieName}`);
      }
    });

    console.log("Final Matomo cookies:", foundCookies);
    console.log("=== END COOKIES SCAN ===");

    return foundCookies;
  }

  // Функция для получения основного visitor_id
  function getMatomoVisitorId() {
    const cookies = getAllMatomoCookies();

    // Приоритет поиска visitor_id
    if (cookies["_matomo_visitor_id"]) {
      return cookies["_matomo_visitor_id"];
    } else if (cookies["_pk_id"]) {
      return cookies["_pk_id"].split(".")[0]; // Берем первую часть
    } else if (cookies["MATOMO_SESSID"]) {
      return cookies["MATOMO_SESSID"];
    }

    // Fallback: создаем на основе user agent + timestamp
    return "fallback_" + Math.random().toString(36).substr(2, 9);
  }

  // Функция для получения session_id
  function getMatomoSessionId() {
    const cookies = getAllMatomoCookies();

    // Приоритет поиска session_id
    if (cookies["MATOMO_SESSID"]) {
      return cookies["MATOMO_SESSID"];
    } else if (cookies["_pk_ses"]) {
      return cookies["_pk_ses"].split(".")[0];
    } else if (cookies["_matomo_visitor_id"]) {
      return cookies["_matomo_visitor_id"].substr(0, 16); // Берем часть visitor_id
    }

    // Fallback
    return "session_" + Date.now();
  }

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

    // НЕ сохраняем если результатов 0
    if (resultsCount === 0) {
      console.log("DEBUG - Skipping save: zero results");
      return;
    }

    // Предотвращаем дублирование
    if (searchQuery === lastSearchQuery) {
      console.log("DEBUG - Duplicate query, skipping");
      return;
    }

    lastSearchQuery = searchQuery;

    // Получаем Matomo данные
    const visitorId = getMatomoVisitorId();
    const sessionId = getMatomoSessionId();
    const allCookies = getAllMatomoCookies();

    console.log("Using Matomo data:", {
      visitorId: visitorId,
      sessionId: sessionId,
      allCookies: allCookies,
    });

    const formData = {
      action: "save_search_query",
      search_query: searchQuery,
      results_count: resultsCount,
      form_id: formId,
      nonce: window.search_stats_params.nonce,
      // Передаем найденные Matomo данные
      matomo_visitor_id: visitorId,
      matomo_session_id: sessionId,
      matomo_cookies_found: JSON.stringify(allCookies),
      ...searchParams,
    };

    console.log("DEBUG - Sending to server:", formData);

    fetch(window.search_stats_params.ajaxurl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams(formData),
    })
      .then((response) => response.json())
      .then((data) => {
        console.log("DEBUG - Server response:", data);
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
    const form = input.closest("form");
    if (form) {
      return form.id || input.id || "";
    }
    return input.id || "";
  }

  // Отслеживаем события ввода в поисковых полях
  function initializeSearchTracking() {
    // Перехватываем AJAX запросы поиска
    interceptAjaxSearches();
  }

  // Перехват AJAX запросов поиска
  function interceptAjaxSearches() {
    const originalFetch = window.fetch;
    window.fetch = function (...args) {
      return originalFetch.apply(this, args).then((response) => {
        const clone = response.clone();

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
                    let resultsCount = 0;

                    if (data.data.found_posts !== undefined) {
                      resultsCount = data.data.found_posts;
                    } else if (data.data.total_found !== undefined) {
                      resultsCount = data.data.total_found;
                    } else if (data.data.all_results) {
                      data.data.all_results.forEach((group) => {
                        resultsCount += group.total_found || 0;
                      });
                    }

                    console.log("DEBUG - Search results:", resultsCount);

                    const activeInput = document.querySelector(
                      '.search-form input[type="text"]:focus'
                    );
                    const formId = activeInput
                      ? getFormIdFromInput(activeInput)
                      : "";

                    trackSearchQuery(searchQuery, resultsCount, formId);
                  }
                })
                .catch((error) => {
                  console.error(
                    "DEBUG - Error processing search response:",
                    error
                  );
                });
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
