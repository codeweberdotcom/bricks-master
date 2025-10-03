// AJAX Search for multiple forms with class .search-form - LOCALIZED VERSION
document.addEventListener("DOMContentLoaded", function () {
  const searchInputs = document.querySelectorAll(
    '.search-form input[type="text"], .search-form input.search-form'
  );

  if (searchInputs.length === 0) {
    return;
  }

  // Глобальные переменные для хранения состояния
  let currentSearchData = {};
  let currentSearchParams = {};
  let currentResultsId = "";

  // Инициализируем каждый input поиска
  searchInputs.forEach((searchInput, index) => {
    initializeSearchForm(searchInput, index);
  });

  function initializeSearchForm(searchInput, formIndex) {
    // Получаем параметры из data-атрибутов для конкретной формы
    const searchParams = {
      postsPerPage: searchInput.dataset.postsPerPage || "10",
      postTypes: searchInput.dataset.postTypes || "",
      searchContent: searchInput.dataset.searchContent || "false",
      taxonomy: searchInput.dataset.taxonomy || "",
      term: searchInput.dataset.term || "",
      includeTaxonomies: searchInput.dataset.includeTaxonomies || "false",
      showExcerpt: searchInput.dataset.showExcerpt || "true",
    };

    let timer;
    const originalPlaceholder = searchInput.placeholder;
    const delay = 300;
    const minChars = 3;

    // Создаем уникальный ID для контейнера результатов
    const resultsId = `search-results-${formIndex}`;

    // Создаем уникальный ID для loader
    const loaderId = `search-loader-${formIndex}`;

    // Создаем контейнер для индикатора загрузки заранее
    createLoaderContainer(searchInput, loaderId);

    searchInput.addEventListener("input", function (e) {
      clearTimeout(timer);
      const query = e.target.value.trim();

      if (query.length < minChars) {
        clearResults(resultsId);
        hideLoader(loaderId);
        return;
      }

      // Показываем индикатор загрузки
      showLoader(loaderId);

      timer = setTimeout(() => {
        performSearch(query, searchInput, searchParams, resultsId, loaderId);
      }, delay);
    });

    searchInput.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        clearResults(resultsId);
        hideLoader(loaderId);
      }
    });

    // Обработчик для закрытия результатов при клике вне области
    document.addEventListener("click", function globalClickHandler(e) {
      if (!searchInput.contains(e.target)) {
        const resultsContainer = document.getElementById(resultsId);
        if (resultsContainer && !resultsContainer.contains(e.target)) {
          clearResults(resultsId);
          hideLoader(loaderId);
        }
      }
    });
  }

  // Создаем контейнер для индикатора загрузки под input
  function createLoaderContainer(searchInput, loaderId) {
    // Находим родительский контейнер - либо форму, либо div с классом search-form
    let formContainer = searchInput.closest(".search-form");

    // Если input находится внутри формы, берем родительскую форму
    if (searchInput.parentNode.tagName === "FORM") {
      formContainer = searchInput.parentNode;
    }

    // Создаем контейнер для индикатора загрузки
    const loaderContainer = document.createElement("div");
    loaderContainer.id = loaderId;
    loaderContainer.className = "search-loader-container mt-1";
    loaderContainer.style.cssText = `
      opacity: 0;
      height: 2px;
      transition: opacity 0.2s ease;
      pointer-events: none;
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      z-index: 4;
    `;

    // Создаем элемент прогресс-бара
    const progressBar = document.createElement("div");
    progressBar.className = "search-progress-bar";
    progressBar.style.cssText = `
      height: 100%;
      width: 0%;
      background-color: var(--bs-primary, #0d6efd);
      border-radius: 1px;
      transition: width 0.3s ease;
      animation: searchLoading 1.5s ease-in-out infinite;
    `;

    loaderContainer.appendChild(progressBar);

    // Вставляем индикатор загрузки в форму
    if (formContainer) {
      formContainer.style.position = "relative";
      formContainer.appendChild(loaderContainer);
    }
  }

  // Функция для показа индикатора загрузки
  function showLoader(loaderId) {
    const loaderContainer = document.getElementById(loaderId);
    if (loaderContainer) {
      loaderContainer.style.opacity = "1";

      // Перезапускаем анимацию
      const progressBar = loaderContainer.querySelector(".search-progress-bar");
      if (progressBar) {
        progressBar.style.animation = "none";
        void progressBar.offsetWidth; // Trigger reflow
        progressBar.style.animation = "searchLoading 1.5s ease-in-out infinite";
      }
    }
  }

  // Функция для скрытия индикатора загрузки
  function hideLoader(loaderId) {
    const loaderContainer = document.getElementById(loaderId);
    if (loaderContainer) {
      loaderContainer.style.opacity = "0";
    }
  }

  function performSearch(
    query,
    searchInput,
    searchParams,
    resultsId,
    loaderId
  ) {
    const originalPlaceholder = searchInput.placeholder;
    searchInput.placeholder = ajax_search_params.i18n.searching;

    const formData = new FormData();
    formData.append("action", "ajax_search");
    formData.append("search_query", query);
    formData.append("nonce", ajax_search_params.nonce);

    // Добавляем параметры из data-атрибутов
    formData.append("posts_per_page", searchParams.postsPerPage);
    formData.append("post_types", searchParams.postTypes);
    formData.append("search_content", searchParams.searchContent);
    formData.append("taxonomy", searchParams.taxonomy);
    formData.append("term", searchParams.term);
    formData.append("include_taxonomies", searchParams.includeTaxonomies);
    formData.append("show_excerpt", searchParams.showExcerpt);

    fetch(ajax_search_params.ajaxurl, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) throw new Error("HTTP error " + response.status);
        return response.json();
    })
      .then((data) => {
        // Сохраняем данные для возможной загрузки всех результатов
        currentSearchData = data;
        currentSearchParams = searchParams;
        currentResultsId = resultsId;

        displayResults(data, searchInput, resultsId, false);
      })
      .catch((error) => {
        showError(
          ajax_search_params.i18n.connection_error,
          searchInput,
          resultsId
        );
      })
      .finally(() => {
        searchInput.placeholder = originalPlaceholder;
        // Скрываем индикатор загрузки после завершения запроса
        hideLoader(loaderId);
      });
  }

  function displayResults(data, searchInput, resultsId, isAllResults = false) {
    clearResults(resultsId);

    if (
      data.success &&
      data.data &&
      data.data.results &&
      data.data.results.all_results &&
      Object.keys(data.data.results.all_results).length > 0
    ) {
      const container = document.createElement("div");
      container.id = resultsId;
      container.className =
        "search-results-container position-absolute top-100 start-0 end-0 bg-white border shadow-lg overflow-auto z-3";
      container.style.maxHeight = isAllResults ? "600px" : "500px";
      container.style.marginTop = "2px";

      const allResults = data.data.results.all_results;
      let totalDisplayed = 0;

      // Добавляем группы результатов
      Object.keys(allResults).forEach((groupLabel) => {
        const group = allResults[groupLabel];
        const groupElement = document.createElement("div");
        groupElement.className = "search-result-group";

        // Заголовок группы с количеством в скобках
        const groupHeader = document.createElement("div");
        groupHeader.className =
          "px-3 py-2 bg-light text-dark fw-bold fs-12 d-flex justify-content-between align-items-center";

        // Основной заголовок с цифрой в скобках
        const groupTitle = document.createElement("span");
        groupTitle.textContent = `${groupLabel} (${group.count})`;

        // Дополнительная информация справа (из/из)
        const groupCount = document.createElement("span");
        groupCount.className = "text-muted fw-normal fs-11";

        if (isAllResults) {
          groupCount.textContent = `${group.count} ${getNumericEnding(
            group.count
          )}`;
        } else {
          groupCount.textContent = `${group.count} ${ajax_search_params.i18n.of} ${group.total_found}`;
        }

        groupHeader.appendChild(groupTitle);
        groupHeader.appendChild(groupCount);
        groupElement.appendChild(groupHeader);

        totalDisplayed += group.count;

        // Элементы группы
        group.items.forEach((item) => {
          const resultItem = document.createElement("a");
          resultItem.href = item.permalink;
          resultItem.className =
            "search-result-item d-block px-3 text-dark text-decoration-none hover-bg-light mb-1 pb-2 position-relative";

          let itemHtml = `
            <div class="fw-medium fs-14">${
              item.title || item.name || ajax_search_params.i18n.no_title
            }</div>
          `;

          // Показываем отрывки если есть
          if (item.excerpts && item.excerpts.length > 0) {
            itemHtml += `<div class="fs-12 text-body mt-1 lh-sm">
                            ${item.excerpts.join("<br>")}
                        </div>`;
          }

          // Для таксономий показываем тип
          if (item.type === "taxonomy") {
            itemHtml += `<div class="fs-12 text-muted">
                            <small>${ajax_search_params.i18n.taxonomy}</small>
                        </div>`;
          }

          resultItem.innerHTML = itemHtml;

          resultItem.addEventListener("mouseenter", () => {
            resultItem.classList.add("bg-light");
          });
          resultItem.addEventListener("mouseleave", () => {
            resultItem.classList.remove("bg-light");
          });

          groupElement.appendChild(resultItem);
        });

        container.appendChild(groupElement);
      });

      // Добавляем общий счетчик результатов и кнопку "Показать все"
      const totalItems = data.data.found_posts;
      const footerElement = document.createElement("div");
      footerElement.className = "p-2 bg-light border-top";

      if (!isAllResults && data.data.has_more) {
        footerElement.classList.add("text-center");

        const showMoreLink = document.createElement("a");
        showMoreLink.href = "#";
        showMoreLink.className = "text-decoration-none fs-14";
        showMoreLink.innerHTML = `
        <span>${
          ajax_search_params.i18n.show_all
        } ${totalItems} ${getNumericEnding(totalItems)}</span>
        <i class="uil uil-angle-down ms-1 fs-14"></i>
        `;
        showMoreLink.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();
          loadAllResults(searchInput, resultsId);
        });

        footerElement.appendChild(showMoreLink);
      } else {
        footerElement.classList.add("text-center");
        const counterText = document.createElement("span");
        counterText.className = "fs-14 text-muted";
        counterText.textContent = `${
          ajax_search_params.i18n.showing
        }: ${totalItems} ${getNumericEnding(totalItems)}`;
        footerElement.appendChild(counterText);
      }

      container.appendChild(footerElement);

      // Добавляем контейнер в DOM
      if (searchInput.parentNode) {
        searchInput.parentNode.classList.add("position-relative");
        searchInput.parentNode.appendChild(container);
      }
    } else {
      showNoResults(searchInput, resultsId);
    }
  }

  // Функция для загрузки всех результатов
  function loadAllResults(searchInput, resultsId) {
    const loaderId = resultsId.replace("search-results", "search-loader");
    showLoader(loaderId);

    const formData = new FormData();
    formData.append("action", "ajax_search_load_all");
    formData.append("search_query", currentSearchData.data.search_query);
    formData.append("nonce", ajax_search_params.nonce);

    // Добавляем параметры из data-атрибутов
    formData.append("post_types", currentSearchParams.postTypes);
    formData.append("search_content", currentSearchParams.searchContent);
    formData.append("taxonomy", currentSearchParams.taxonomy);
    formData.append("term", currentSearchParams.term);
    formData.append(
      "include_taxonomies",
      currentSearchParams.includeTaxonomies
    );
    formData.append("show_excerpt", currentSearchParams.showExcerpt);

    fetch(ajax_search_params.ajaxurl, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) throw new Error("HTTP error " + response.status);
        return response.json();
      })
      .then((data) => {
        displayResults(data, searchInput, resultsId, true);
      })
      .catch((error) => {
        showError(
          ajax_search_params.i18n.connection_error,
          searchInput,
          resultsId
        );
      })
      .finally(() => {
        hideLoader(loaderId);
      });
  }

  // Функция для правильного склонения числительных для русского языка
  function getNumericEnding(number) {
    const i18n = ajax_search_params.i18n;

    const n = Math.abs(number) % 100;
    const n1 = n % 10;

    if (n1 === 1 && n !== 11) {
      return i18n.result.singular; // 1, 21, 31... результат
    } else if (n1 >= 2 && n1 <= 4 && (n < 10 || n > 20)) {
      return i18n.result.few; // 2-4, 22-24, 32-34... результата
    } else {
      return i18n.result.many; // 5-20, 25-30, 35-40... результатов
    }
  }

  function showNoResults(searchInput, resultsId) {
    clearResults(resultsId);

    const div = document.createElement("div");
    div.id = resultsId;
    div.className =
      "search-no-results position-absolute top-100 start-0 end-0 bg-white border p-3 text-muted text-center z-3";
    div.textContent = ajax_search_params.i18n.no_results;
    div.style.marginTop = "2px";

    if (searchInput.parentNode) {
      searchInput.parentNode.classList.add("position-relative");
      searchInput.parentNode.appendChild(div);
    }
  }

  function showError(message, searchInput, resultsId) {
    clearResults(resultsId);
    const div = document.createElement("div");
    div.id = resultsId;
    div.className =
      "search-error position-absolute top-100 start-0 end-0 bg-white border border-danger px-3 text-danger text-center z-3";
    div.textContent = message;
    div.style.marginTop = "2px";

    if (searchInput.parentNode) {
      searchInput.parentNode.classList.add("position-relative");
      searchInput.parentNode.appendChild(div);
    }
  }

  function clearResults(resultsId) {
    const existingResults = document.getElementById(resultsId);
    if (existingResults) {
      existingResults.remove();
    }
  }
});

// Добавляем CSS анимацию для индикатора загрузки
const style = document.createElement("style");
style.textContent = `
  @keyframes searchLoading {
    0% {
      width: 0%;
      opacity: 0.7;
    }
    50% {
      width: 70%;
      opacity: 1;
    }
    100% {
      width: 100%;
      opacity: 0.7;
    }
  }
    .offcanvas:not(.offcanvas-nav) {
  overflow-y: visible;
}

.search-result-item::after {
  content: '';
  background: #e6e6e6;
  width: 90%;
  position: absolute;
  height: 1px;
  bottom: -2px;
}
  
  .search-loader-container {
    background-color: rgba(0,0,0,0.1);
    border-radius: 1px;
  }
`;
document.head.appendChild(style);
