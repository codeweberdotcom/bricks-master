// AJAX Search for multiple forms with class .search-form - LOCALIZED VERSION
document.addEventListener("DOMContentLoaded", function () {
  // Находим все input внутри форм с классом .search-form ИЛИ прямые input с классом .search-form
  const searchInputs = document.querySelectorAll(
    '.search-form input[type="text"], .search-form input.search-form'
  );

  if (searchInputs.length === 0) {
    return;
  }

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
        displayResults(data, searchInput, resultsId);
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

  function displayResults(data, searchInput, resultsId) {
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
      container.style.maxHeight = "500px";
      container.style.marginTop = "2px";

      const allResults = data.data.results.all_results;

      // Добавляем группы результатов
      Object.keys(allResults).forEach((groupLabel) => {
        const group = allResults[groupLabel];
        const groupElement = document.createElement("div");
        groupElement.className = "search-result-group";

        const groupHeader = document.createElement("div");
        groupHeader.className = "px-3 my-2 bg-light text-dark fw-bold small";
        groupHeader.textContent = groupLabel;
        groupElement.appendChild(groupHeader);

        // Элементы группы
        group.items.forEach((item) => {
          const resultItem = document.createElement("a");
          resultItem.href = item.permalink;
          resultItem.className =
            "search-result-item d-block px-3 text-dark text-decoration-none hover-bg-light";

          let itemHtml = `
            <div class="mb-1">${
              item.title || item.name || ajax_search_params.i18n.no_title
            }</div>
          `;

          // Показываем отрывки если есть
          if (item.excerpts && item.excerpts.length > 0) {
            itemHtml += `<div class="small text-body mt-1 lh-sm">
                            ${item.excerpts.join("<br>")}
                        </div>`;
          }

          // Для таксономий показываем тип
          if (item.type === "taxonomy") {
            itemHtml += `<div class="small text-muted">
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

      // Добавляем общий счетчик результатов
      const totalItems = data.data.found_posts;
      const counterElement = document.createElement("div");
      counterElement.className =
        "p-2 bg-secondary bg-opacity-10 border-top small text-muted text-center";
      counterElement.textContent = `${
        ajax_search_params.i18n.total_found
      }: ${totalItems} ${getNumericEnding(totalItems)}`;
      container.appendChild(counterElement);

      // Добавляем контейнер в DOM
      if (searchInput.parentNode) {
        searchInput.parentNode.classList.add("position-relative");
        searchInput.parentNode.appendChild(container);
      }
    } else {
      showNoResults(searchInput, resultsId);
    }
  }

  // Функция для правильного склонения числительных для русского языка
  function getNumericEnding(number) {
    const i18n = ajax_search_params.i18n;

    // Русские правила склонения:
    // 1 результат
    // 2,3,4 результата
    // 5,6... результатов
    // 11,12...14 результатов

    if (number % 100 >= 11 && number % 100 <= 14) {
      return i18n.result_many;
    }

    const lastDigit = number % 10;

    if (lastDigit === 1) {
      return i18n.result_singular;
    } else if (lastDigit >= 2 && lastDigit <= 4) {
      return i18n.result_few;
    } else {
      return i18n.result_many;
    }
  }

  function showNoResults(searchInput, resultsId) {
    clearResults(resultsId);

    const div = document.createElement("div");
    div.id = resultsId;
    div.className =
      "search-no-results position-absolute top-100 start-0 end-0 bg-white border p-4 text-muted text-center z-3";
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
  
  .search-loader-container {
    background-color: rgba(0,0,0,0.1);
    border-radius: 1px;
  }
`;
document.head.appendChild(style);
