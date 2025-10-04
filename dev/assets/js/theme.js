"use strict";

var theme = {
  /**
   * Theme's components/functions list
   * Comment out or delete the unnecessary component.
   * Some components have dependencies (plugins).
   * Do not forget to remove dependency from src/js/vendor/ and recompile.
   */
  init: function () {
    // Add form validation
    theme.stickyHeader();
    theme.subMenu();
    theme.offCanvas();
    theme.isotope();
    theme.onepageHeaderOffset();
    theme.spyScroll();
    theme.anchorSmoothScroll();
    theme.svgInject();
    theme.backgroundImage();
    theme.backgroundImageMobile();
    theme.imageHoverOverlay();
    theme.rellax();
    theme.scrollCue();
    theme.swiperSlider();
    //theme.lightbox();
    theme.plyr();
    theme.progressBar();
    theme.loader();
    theme.pageProgress();
    theme.counterUp();
    theme.bsTooltips();
    theme.bsPopovers();
    theme.bsModal();
    theme.iTooltip();
    //theme.forms();
    theme.passVisibility();
    theme.pricingSwitcher();
    theme.textRotator();
    theme.codeSnippet();
  },

  /**
   * Sticky Header
   * Enables sticky behavior on navbar on page scroll
   * Requires assets/js/vendor/headhesive.min.js
   */
  stickyHeader: () => {
    var navbar = document.querySelector(".navbar");
    if (navbar == null) return;
    var options = {
      offset: 350,
      offsetSide: "top",
      classes: {
        clone: "navbar-clone fixed",
        stick: "navbar-stick",
        unstick: "navbar-unstick",
      },
      onStick: function () {
        var navbarClonedClass = this.clonedElem.classList;
        if (
          navbarClonedClass.contains("transparent") &&
          navbarClonedClass.contains("navbar-dark")
        ) {
          this.clonedElem.className = this.clonedElem.className.replace(
            "navbar-dark",
            "navbar-light"
          );
        }
      },
    };
    var banner = new Headhesive(".navbar", options);
  },
  /**
   * Sub Menus
   * Enables multilevel dropdown
   */
  subMenu: () => {
    (function ($bs) {
      const CLASS_NAME = "has-child-dropdown-show";
      $bs.Dropdown.prototype.toggle = (function (_original) {
        return function () {
          document.querySelectorAll("." + CLASS_NAME).forEach(function (e) {
            e.classList.remove(CLASS_NAME);
          });
          let dd = this._element
            .closest(".dropdown")
            .parentNode.closest(".dropdown");
          for (
            ;
            dd && dd !== document;
            dd = dd.parentNode.closest(".dropdown")
          ) {
            dd.classList.add(CLASS_NAME);
          }
          return _original.call(this);
        };
      })($bs.Dropdown.prototype.toggle);
      document.querySelectorAll(".dropdown").forEach(function (dd) {
        dd.addEventListener("hide.bs.dropdown", function (e) {
          if (this.classList.contains(CLASS_NAME)) {
            this.classList.remove(CLASS_NAME);
            e.preventDefault();
          }
          e.stopPropagation();
        });
      });
    })(bootstrap);
  },
  /**
   * Offcanvas
   * Enables offcanvas-nav, closes offcanvas on anchor clicks, focuses on input in search offcanvas
   */
  offCanvas: () => {
    var navbar = document.querySelector(".navbar");
    if (navbar == null) return;
    const navOffCanvasBtn = document.querySelectorAll(".offcanvas-nav-btn");
    const navOffCanvas = document.querySelector(
      ".navbar:not(.navbar-clone) .offcanvas-nav"
    );
    const bsOffCanvas = new bootstrap.Offcanvas(navOffCanvas, { scroll: true });
    const scrollLink = document.querySelectorAll(
      ".onepage .navbar li a.scroll"
    );
    const searchOffcanvas = document.getElementById("offcanvas-search");
    navOffCanvasBtn.forEach((e) => {
      e.addEventListener("click", (event) => {
        bsOffCanvas.show();
      });
    });
    scrollLink.forEach((e) => {
      e.addEventListener("click", (event) => {
        bsOffCanvas.hide();
      });
    });
    if (searchOffcanvas != null) {
      searchOffcanvas.addEventListener("shown.bs.offcanvas", function () {
        document.getElementById("search-form").focus();
      });
    }
  },
  /**
   * Isotope
   * Enables isotope grid layout and filtering
   * Requires assets/js/vendor/isotope.pkgd.min.js
   * Requires assets/js/vendor/imagesloaded.pkgd.min.js
   */
  isotope: () => {
    var grids = document.querySelectorAll(".grid");
    if (grids != null) {
      grids.forEach((g) => {
        var grid = g.querySelector(".isotope");
        var filtersElem = g.querySelector(".isotope-filter");
        var buttonGroups = g.querySelectorAll(".isotope-filter");
        var iso = new Isotope(grid, {
          itemSelector: ".item",
          layoutMode: "masonry",
          masonry: {
            columnWidth: grid.offsetWidth / 12,
          },
          percentPosition: true,
          transitionDuration: "0.7s",
        });
        imagesLoaded(grid).on("progress", function () {
          iso.layout({
            masonry: {
              columnWidth: grid.offsetWidth / 12,
            },
          });
        }),
          window.addEventListener(
            "resize",
            function () {
              iso.arrange({
                masonry: {
                  columnWidth: grid.offsetWidth / 12,
                },
              });
            },
            true
          );
        if (filtersElem != null) {
          filtersElem.addEventListener("click", function (event) {
            if (!matchesSelector(event.target, ".filter-item")) {
              return;
            }
            var filterValue = event.target.getAttribute("data-filter");
            iso.arrange({
              filter: filterValue,
            });
          });
          for (var i = 0, len = buttonGroups.length; i < len; i++) {
            var buttonGroup = buttonGroups[i];
            buttonGroup.addEventListener("click", function (event) {
              if (!matchesSelector(event.target, ".filter-item")) {
                return;
              }
              buttonGroup.querySelector(".active").classList.remove("active");
              event.target.classList.add("active");
            });
          }
        }
      });
    }
  },
  /**
   * Onepage Header Offset
   * Adds an offset value to anchor point equal to sticky header height on a onepage
   */
  onepageHeaderOffset: () => {
    var navbar = document.querySelector(".navbar");
    if (navbar == null) return;
    const header_height = document.querySelector(".navbar").offsetHeight;
    const shrinked_header_height = 75;
    const sections = document.querySelectorAll(".onepage section");
    sections.forEach((section) => {
      section.style.paddingTop = shrinked_header_height + "px";
      section.style.marginTop = "-" + shrinked_header_height + "px";
    });
    const first_section = document.querySelector(
      ".onepage section:first-of-type"
    );
    if (first_section != null) {
      first_section.style.paddingTop = header_height + "px";
      first_section.style.marginTop = "-" + header_height + "px";
    }
  },
  /**
   * Spy Scroll
   * Highlights the active nav link while scrolling through sections
   */
  spyScroll: () => {
    let section = document.querySelectorAll("section[id]");
    let navLinks = document.querySelectorAll(".nav-link.scroll");
    window.onscroll = () => {
      section.forEach((sec) => {
        let top = window.scrollY; //returns the number of pixels that the document is currently scrolled vertically.
        let offset = sec.offsetTop - 0; //returns the distance of the outer border of the current element relative to the inner border of the top of the offsetParent, the closest positioned ancestor element
        let height = sec.offsetHeight; //returns the height of an element, including vertical padding and borders, as an integer
        let id = sec.getAttribute("id"); //gets the value of an attribute of an element
        if (top >= offset && top < offset + height) {
          navLinks.forEach((links) => {
            links.classList.remove("active");
            document
              .querySelector(`.nav-link.scroll[href*=${id}]`)
              .classList.add("active");
            //[att*=val] Represents an element with the att attribute whose value contains at least one instance of the substring "val". If "val" is the empty string then the selector does not represent anything.
          });
        }
      });
    };
  },
  /**
   * Anchor Smooth Scroll
   * Adds smooth scroll animation to links with .scroll class
   * Requires assets/js/vendor/smoothscroll.js
   */
  anchorSmoothScroll: () => {
    const links = document.querySelectorAll(".scroll");
    for (const link of links) {
      link.addEventListener("click", clickHandler);
    }
    function clickHandler(e) {
      e.preventDefault();
      this.blur();
      const href = this.getAttribute("href");
      const offsetTop = document.querySelector(href).offsetTop;
      scroll({
        top: offsetTop,
        behavior: "smooth",
      });
    }
  },
  /**
   * SVGInject
   * Replaces an img element with an inline SVG so you can apply colors to your SVGs
   * Requires assets/js/vendor/svg-inject.min.js
   */
  svgInject: () => {
    SVGInject.setOptions({
      onFail: function (img, svg) {
        img.classList.remove("svg-inject");
      },
    });
    document.addEventListener("DOMContentLoaded", function () {
      SVGInject(document.querySelectorAll("img.svg-inject"), {
        useCache: true,
      });
    });
  },
  /**
   * Background Image
   * Adds a background image link via data attribute "data-image-src"
   */
  backgroundImage: () => {
    var bg = document.querySelectorAll(".bg-image");
    for (var i = 0; i < bg.length; i++) {
      var url = bg[i].getAttribute("data-image-src");
      bg[i].style.backgroundImage = "url('" + url + "')";
    }
  },
  /**
   * Background Image Mobile
   * Adds .mobile class to background images on mobile devices for styling purposes
   */
  backgroundImageMobile: () => {
    var isMobile =
      navigator.userAgent.match(/Android/i) ||
      navigator.userAgent.match(/webOS/i) ||
      navigator.userAgent.match(/iPhone/i) ||
      navigator.userAgent.match(/iPad/i) ||
      (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1) ||
      navigator.userAgent.match(/iPod/i) ||
      navigator.userAgent.match(/BlackBerry/i)
        ? true
        : false;
    if (isMobile) {
      document.querySelectorAll(".image-wrapper").forEach((e) => {
        e.classList.add("mobile");
      });
    }
  },
  /**
   * Image Hover Overlay
   * Adds span.bg inside .overlay for simpler markup and styling purposes
   */
  imageHoverOverlay: () => {
    var overlay = document.querySelectorAll(".overlay > a, .overlay > span");
    for (var i = 0; i < overlay.length; i++) {
      var overlay_bg = document.createElement("span");
      overlay_bg.className = "bg";
      overlay[i].appendChild(overlay_bg);
    }
  },
  /**
   * Rellax.js
   * Adds parallax animation to shapes and elements
   * Requires assets/js/vendor/rellax.min.js
   */
  rellax: () => {
    if (document.querySelector(".rellax") != null) {
      window.onload = function () {
        var rellax = new Rellax(".rellax", {
          speed: 2,
          center: true,
          breakpoints: [576, 992, 1201],
        });
        var projects_overflow = document.querySelectorAll(".projects-overflow");
        imagesLoaded(projects_overflow, function () {
          rellax.refresh();
        });
      };
    }
  },
  /**
   * scrollCue.js
   * Enables showing elements by scrolling
   * Requires assets/js/vendor/scrollCue.min.js
   */
  scrollCue: () => {
    scrollCue.init({
      interval: -400,
      duration: 700,
      percentage: 0.8,
    });
    scrollCue.update();
  },
  /**
   * Swiper Slider
   * Enables carousels and sliders
   * Requires assets/js/vendor/swiper-bundle.min.js
   */
  swiperSlider: function () {
    var carousel = document.querySelectorAll(".swiper-container");
    for (let i = 0; i < carousel.length; i++) {
      var slider1 = carousel[i];
      slider1.classList.add("swiper-container-" + i);
      var controls = document.createElement("div");
      controls.className = "swiper-controls";
      var pagi = document.createElement("div");
      pagi.className = "swiper-pagination";
      var navi = document.createElement("div");
      navi.className = "swiper-navigation";
      var prev = document.createElement("div");
      prev.className = "swiper-button swiper-button-prev";
      var next = document.createElement("div");
      next.className = "swiper-button swiper-button-next";
      slider1.appendChild(controls);
      controls.appendChild(navi);
      navi.appendChild(prev);
      navi.appendChild(next);
      controls.appendChild(pagi);
      var sliderEffect = slider1.getAttribute("data-effect")
        ? slider1.getAttribute("data-effect")
        : "slide";
      if (slider1.getAttribute("data-items-auto") === "true") {
        var slidesPerViewInit = "auto";
        var breakpointsInit = null;
      } else {
        var sliderItems = slider1.getAttribute("data-items")
          ? slider1.getAttribute("data-items")
          : 3; // items in all devices
        var sliderItemsXs = slider1.getAttribute("data-items-xs")
          ? slider1.getAttribute("data-items-xs")
          : 1; // start - 575
        var sliderItemsSm = slider1.getAttribute("data-items-sm")
          ? slider1.getAttribute("data-items-sm")
          : Number(sliderItemsXs); // 576 - 767
        var sliderItemsMd = slider1.getAttribute("data-items-md")
          ? slider1.getAttribute("data-items-md")
          : Number(sliderItemsSm); // 768 - 991
        var sliderItemsLg = slider1.getAttribute("data-items-lg")
          ? slider1.getAttribute("data-items-lg")
          : Number(sliderItemsMd); // 992 - 1199
        var sliderItemsXl = slider1.getAttribute("data-items-xl")
          ? slider1.getAttribute("data-items-xl")
          : Number(sliderItemsLg); // 1200 - end
        var sliderItemsXxl = slider1.getAttribute("data-items-xxl")
          ? slider1.getAttribute("data-items-xxl")
          : Number(sliderItemsXl); // 1500 - end
        var slidesPerViewInit = sliderItems;
        var breakpointsInit = {
          0: {
            slidesPerView: Number(sliderItemsXs),
          },
          576: {
            slidesPerView: Number(sliderItemsSm),
          },
          768: {
            slidesPerView: Number(sliderItemsMd),
          },
          992: {
            slidesPerView: Number(sliderItemsLg),
          },
          1200: {
            slidesPerView: Number(sliderItemsXl),
          },
          1400: {
            slidesPerView: Number(sliderItemsXxl),
          },
        };
      }
      var sliderSpeed = slider1.getAttribute("data-speed")
        ? slider1.getAttribute("data-speed")
        : 500;
      var sliderAutoPlay = slider1.getAttribute("data-autoplay") !== "false";
      var sliderAutoPlayTime = slider1.getAttribute("data-autoplaytime")
        ? slider1.getAttribute("data-autoplaytime")
        : 5000;
      var sliderAutoHeight = slider1.getAttribute("data-autoheight") === "true";
      var sliderResizeUpdate =
        slider1.getAttribute("data-resizeupdate") !== "false";
      var sliderAllowTouchMove = slider1.getAttribute("data-drag") !== "false";
      var sliderReverseDirection =
        slider1.getAttribute("data-reverse") === "true";
      var sliderMargin = slider1.getAttribute("data-margin")
        ? slider1.getAttribute("data-margin")
        : 30;
      var sliderLoop = slider1.getAttribute("data-loop") === "true";
      var sliderCentered = slider1.getAttribute("data-centered") === "true";
      var swiper = slider1.querySelector(".swiper:not(.swiper-thumbs)");
      var swiperTh = slider1.querySelector(".swiper-thumbs");
      var sliderTh = new Swiper(swiperTh, {
        slidesPerView: 5,
        spaceBetween: 10,
        loop: false,
        threshold: 2,
        slideToClickedSlide: true,
      });
      if (slider1.getAttribute("data-thumbs") === "true") {
        var thumbsInit = sliderTh;
        var swiperMain = document.createElement("div");
        swiperMain.className = "swiper-main";
        swiper.parentNode.insertBefore(swiperMain, swiper);
        swiperMain.appendChild(swiper);
        slider1.removeChild(controls);
        swiperMain.appendChild(controls);
      } else {
        var thumbsInit = null;
      }
      let slider = new Swiper(swiper, {
        on: {
          beforeInit: function () {
            if (
              slider1.getAttribute("data-nav") !== "true" &&
              slider1.getAttribute("data-dots") !== "true"
            ) {
              controls.remove();
            }
            if (slider1.getAttribute("data-dots") !== "true") {
              pagi.remove();
            }
            if (slider1.getAttribute("data-nav") !== "true") {
              navi.remove();
            }
          },
          init: function () {
            if (slider1.getAttribute("data-autoplay") !== "true") {
              this.autoplay.stop();
            }
            this.update();
          },
        },
        autoplay: {
          delay: sliderAutoPlayTime,
          disableOnInteraction: false,
          reverseDirection: sliderReverseDirection,
          pauseOnMouseEnter: false,
        },
        allowTouchMove: sliderAllowTouchMove,
        speed: parseInt(sliderSpeed),
        slidesPerView: slidesPerViewInit,
        loop: sliderLoop,
        centeredSlides: sliderCentered,
        spaceBetween: Number(sliderMargin),
        effect: sliderEffect,
        autoHeight: sliderAutoHeight,
        grabCursor: true,
        resizeObserver: false,
        updateOnWindowResize: sliderResizeUpdate,
        breakpoints: breakpointsInit,
        pagination: {
          el: carousel[i].querySelector(".swiper-pagination"),
          clickable: true,
        },
        navigation: {
          prevEl: slider1.querySelector(".swiper-button-prev"),
          nextEl: slider1.querySelector(".swiper-button-next"),
        },
        thumbs: {
          swiper: thumbsInit,
        },
      });
      if (thumbsInit !== null) {
        if (document.querySelector("input.variation_id") !== null) {
          document.querySelector("input.variation_id").onchange = function () {
            document.querySelector(
              ".swiper-thumbs .swiper-wrapper .swiper-slide img"
            ).src = document.querySelector(
              ".woocommerce-product-gallery__wrapper .swiper-slide img"
            ).src;
            slider.slideTo(slider.activeIndex + 1, 1, true);
            slider.slideTo(0, 500, true);
            theme.lightbox.reload();
          };
        }
      }
    }
  },
  /**
   * GLightbox
   * Enables lightbox functionality
   * Requires assets/js/vendor/glightbox.js
   */
  lightbox: GLightbox({
    selector: "*[data-glightbox]",
    touchNavigation: true,
    loop: false,
    zoomable: true,
    autoplayVideos: true,
    moreLength: 0,
    slideExtraAttributes: {
      poster: "",
    },
    plyr: {
      css: "",
      js: "",
      config: {
        ratio: "",
        fullscreen: {
          enabled: false,
          iosNative: false,
        },
        youtube: {
          noCookie: true,
          rel: 0,
          showinfo: 0,
          iv_load_policy: 3,
        },
        vimeo: {
          byline: false,
          portrait: false,
          title: false,
          transparent: false,
        },
      },
    },
  }),

  /**
   * Plyr
   * Enables media player
   * Requires assets/js/vendor/plyr.js
   */
  plyr: () => {
    var players = Plyr.setup(".player", {
      loadSprite: true,
    });
  },
  /**
   * Progressbar
   * Enables animated progressbars
   * Requires assets/js/vendor/progressbar.min.js
   * Requires assets/js/vendor/noframework.waypoints.min.js
   */
  progressBar: () => {
    const pline = document.querySelectorAll(".progressbar.line");
    const pcircle = document.querySelectorAll(".progressbar.semi-circle");
    pline.forEach((e) => {
      var line = new ProgressBar.Line(e, {
        strokeWidth: 6,
        trailWidth: 6,
        duration: 3000,
        easing: "easeInOut",
        text: {
          style: {
            color: "inherit",
            position: "absolute",
            right: "0",
            top: "-30px",
            padding: 0,
            margin: 0,
            transform: null,
          },
          autoStyleContainer: false,
        },
        step: (state, line) => {
          line.setText(Math.round(line.value() * 100) + " %");
        },
      });
      var value = e.getAttribute("data-value") / 100;
      new Waypoint({
        element: e,
        handler: function () {
          line.animate(value);
        },
        offset: "bottom-in-view",
      });
    });
    pcircle.forEach((e) => {
      var circle = new ProgressBar.SemiCircle(e, {
        strokeWidth: 6,
        trailWidth: 6,
        duration: 2000,
        easing: "easeInOut",
        step: (state, circle) => {
          circle.setText(Math.round(circle.value() * 100));
        },
      });
      var value = e.getAttribute("data-value") / 100;
      new Waypoint({
        element: e,
        handler: function () {
          circle.animate(value);
        },
        offset: "bottom-in-view",
      });
    });
  },
  /**
   * Loader
   *
   */
  loader: () => {
    var preloader = document.querySelector(".page-loader");
    if (preloader != null) {
      document.body.onload = function () {
        setTimeout(function () {
          if (!preloader.classList.contains("done")) {
            preloader.classList.add("done");
          }
        }, 1000);
      };
    }
  },
  /**
   * Page Progress
   * Shows page progress on the bottom right corner of pages
   */
  pageProgress: () => {
    var progressWrap = document.querySelector(".progress-wrap");
    if (progressWrap != null) {
      var progressPath = document.querySelector(".progress-wrap path");
      var pathLength = progressPath.getTotalLength();
      var offset = 50;
      progressPath.style.transition = progressPath.style.WebkitTransition =
        "none";
      progressPath.style.strokeDasharray = pathLength + " " + pathLength;
      progressPath.style.strokeDashoffset = pathLength;
      progressPath.getBoundingClientRect();
      progressPath.style.transition = progressPath.style.WebkitTransition =
        "stroke-dashoffset 10ms linear";
      window.addEventListener("scroll", function (event) {
        var scroll =
          document.body.scrollTop || document.documentElement.scrollTop;
        var height =
          document.documentElement.scrollHeight -
          document.documentElement.clientHeight;
        var progress = pathLength - (scroll * pathLength) / height;
        progressPath.style.strokeDashoffset = progress;
        var scrollElementPos =
          document.body.scrollTop || document.documentElement.scrollTop;
        if (scrollElementPos >= offset) {
          progressWrap.classList.add("active-progress");
        } else {
          progressWrap.classList.remove("active-progress");
        }
      });
      progressWrap.addEventListener("click", function (e) {
        e.preventDefault();
        window.scroll({
          top: 0,
          left: 0,
          behavior: "smooth",
        });
      });
    }
  },
  /**
   * Counter Up
   * Counts up to a targeted number when the number becomes visible
   * Requires assets/js/vendor/counterup.min.js
   * Requires assets/js/vendor/noframework.waypoints.min.js
   */
  counterUp: () => {
    var counterUp = window.counterUp["default"];
    const counters = document.querySelectorAll(".counter");
    counters.forEach((el) => {
      new Waypoint({
        element: el,
        handler: function () {
          counterUp(el, {
            duration: 1000,
            delay: 50,
          });
          this.destroy();
        },
        offset: "bottom-in-view",
      });
    });
  },
  /**
   * Bootstrap Tooltips
   * Enables Bootstrap tooltips
   * Requires Poppers library
   */
  bsTooltips: () => {
    var tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl, {
        trigger: "hover",
      });
    });
    var tooltipTriggerWhite = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="white-tooltip"]')
    );
    var tooltipWhite = tooltipTriggerWhite.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl, {
        customClass: "white-tooltip",
        trigger: "hover",
        placement: "left",
      });
    });
  },
  /**
   * Bootstrap Popovers
   * Enables Bootstrap popovers
   * Requires Poppers library
   */
  bsPopovers: () => {
    var popoverTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="popover"]')
    );
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl);
    });
  },
  /**
   * Bootstrap Modal
   * Enables Bootstrap modal popup
   */
  bsModal: () => {
    if (document.querySelector(".modal-popup") != null) {
      var myModalPopup = new bootstrap.Modal(
        document.querySelector(".modal-popup")
      );
      setTimeout(function () {
        myModalPopup.show();
      }, 200);
    }
    // Fixes jumping of page progress caused by modal
    var innerWidth = window.innerWidth;
    var clientWidth = document.body.clientWidth;
    var scrollSize = innerWidth - clientWidth;
    var myModalEl = document.querySelectorAll(".modal");
    var navbarFixed = document.querySelector(".navbar.fixed");
    var pageProgress = document.querySelector(".progress-wrap");
    function setPadding() {
      if (navbarFixed != null) {
        navbarFixed.style.paddingRight = scrollSize + "px";
      }
      if (pageProgress != null) {
        pageProgress.style.marginRight = scrollSize + "px";
      }
    }
    function removePadding() {
      if (navbarFixed != null) {
        navbarFixed.style.paddingRight = "";
      }
      if (pageProgress != null) {
        pageProgress.style.marginRight = "";
      }
    }
    myModalEl.forEach((myModalEl) => {
      myModalEl.addEventListener("show.bs.modal", function (e) {
        setPadding();
      });
      myModalEl.addEventListener("hidden.bs.modal", function (e) {
        removePadding();
      });
    });
  },
  /**
   * iTooltip
   * Enables custom tooltip style for image hover docs/elements/hover.html
   * Requires assets/js/vendor/itooltip.min.js
   */
  iTooltip: () => {
    var tooltip = new iTooltip(".itooltip");
    tooltip.init({
      className: "itooltip-inner",
      indentX: 15,
      indentY: 15,
      positionX: "right",
      positionY: "bottom",
    });
  },
  /**
   * Form Validation and Contact Form submit
   * Bootstrap validation - Only sends messages if form has class ".contact-form" and is validated and shows success/fail messages
   */
  forms: () => {
    (function () {
      "use strict";
      window.addEventListener(
        "load",
        function () {
          var forms = document.querySelectorAll(".needs-validation");
          var inputRecaptcha = document.querySelector("input[data-recaptcha]");
          window.verifyRecaptchaCallback = function (response) {
            inputRecaptcha.value = response;
            inputRecaptcha.dispatchEvent(new Event("change"));
          };
          window.expiredRecaptchaCallback = function () {
            var inputRecaptcha = document.querySelector(
              "input[data-recaptcha]"
            );
            inputRecaptcha.value = "";
            inputRecaptcha.dispatchEvent(new Event("change"));
          };
          var validation = Array.prototype.filter.call(forms, function (form) {
            form.addEventListener(
              "submit",
              function (event) {
                if (form.checkValidity() === false) {
                  event.preventDefault();
                  event.stopPropagation();
                }
                form.classList.add("was-validated");
                if (form.checkValidity() === true) {
                  event.preventDefault();
                  form.classList.remove("was-validated");
                  // Send message only if the form has class .contact-form
                  var isContactForm = form.classList.contains("contact-form");
                  if (isContactForm) {
                    var data = new FormData(form);
                    var alertClass = "alert-danger";
                    fetch("assets/php/contact.php", {
                      method: "post",
                      body: data,
                    })
                      .then((data) => {
                        if (data.ok) {
                          alertClass = "alert-success";
                        }
                        return data.text();
                      })
                      .then((txt) => {
                        var alertBox =
                          '<div class="alert ' +
                          alertClass +
                          ' alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                          txt +
                          "</div>";
                        if (alertClass && txt) {
                          form
                            .querySelector(".messages")
                            .insertAdjacentHTML("beforeend", alertBox);
                          form.reset();
                          grecaptcha.reset();
                        }
                      })
                      .catch((err) => {
                        console.log(err);
                      });
                  }
                }
              },
              false
            );
          });
        },
        false
      );
    })();
  },
  /**
   * Password Visibility Toggle
   * Toggles password visibility in password input
   */
  passVisibility: () => {
    let pass = document.querySelectorAll(".password-field");
    for (let i = 0; i < pass.length; i++) {
      let passInput = pass[i].querySelector(".form-control");
      let passToggle = pass[i].querySelector(".password-toggle > i");
      passToggle.addEventListener(
        "click",
        (e) => {
          if (passInput.type === "password") {
            passInput.type = "text";
            passToggle.classList.remove("uil-eye");
            passToggle.classList.add("uil-eye-slash");
          } else {
            passInput.type = "password";
            passToggle.classList.remove("uil-eye-slash");
            passToggle.classList.add("uil-eye");
          }
        },
        false
      );
    }
  },
  /**
   * Pricing Switcher
   * Enables monthly/yearly switcher seen on pricing tables
   */
  pricingSwitcher: () => {
    if (document.querySelector(".pricing-switchers") != null) {
      const wrapper = document.querySelectorAll(".pricing-wrapper");
      wrapper.forEach((wrap) => {
        const switchers = wrap.querySelector(".pricing-switchers");
        const switcher = wrap.querySelectorAll(".pricing-switcher");
        const price = wrap.querySelectorAll(".price");
        switchers.addEventListener("click", (e) => {
          switcher.forEach((s) => {
            s.classList.toggle("pricing-switcher-active");
          });
          price.forEach((p) => {
            p.classList.remove("price-hidden");
            p.classList.toggle("price-show");
            p.classList.toggle("price-hide");
          });
        });
      });
    }
  },
  /**
   * ReplaceMe.js
   * Enables text rotator
   * Requires assets/js/vendor/replaceme.min.js
   */
  textRotator: () => {
    // Обрабатываем элементы с классом rotator-zoom
    document.querySelectorAll(".rotator-zoom").forEach((element) => {
      new ReplaceMe(element, {
        animation: "animate__animated animate__zoomIn",
        speed: 2500,
        separator: ",",
        clickChange: false,
        loopCount: "infinite",
      });
    });

    // Обрабатываем элементы с классом rotator-fade
    document.querySelectorAll(".rotator-fade").forEach((element) => {
      new ReplaceMe(element, {
        animation: "animate__animated animate__fadeInDown",
        speed: 2500,
        separator: ",",
        clickChange: false,
        loopCount: "infinite",
      });
    });
  },
  /**
   * Clipboard.js
   * Enables clipboard on docs
   * Requires assets/js/vendor/clipboard.min.js
   */
  codeSnippet: () => {
    var btnHtml =
      '<button type="button" class="btn btn-sm btn-white rounded-pill btn-clipboard">Copy</button>';
    document
      .querySelectorAll(".code-wrapper-inner")
      .forEach(function (element) {
        element.insertAdjacentHTML("beforebegin", btnHtml);
      });
    var clipboard = new ClipboardJS(".btn-clipboard", {
      target: function (trigger) {
        return trigger.nextElementSibling;
      },
    });
    clipboard.on("success", (event) => {
      event.trigger.textContent = "Copied!";
      event.clearSelection();
      setTimeout(function () {
        event.trigger.textContent = "Copy";
      }, 2000);
    });
    var copyIconCode = new ClipboardJS(".btn-copy-icon");
    copyIconCode.on("success", function (event) {
      event.clearSelection();
      event.trigger.textContent = "Copied!";
      window.setTimeout(function () {
        event.trigger.textContent = "Copy";
      }, 2300);
    });
  },
};

theme.init();

// Custom.js

var custom = {
  /**
   * Theme's custom components/functions list
   * Comment out or delete the unnecessary component.
   * Some components have dependencies (plugins).
   * Do not forget to remove dependency from src/js/vendor/ and recompile.
   */
  init: function () {
    custom.rippleEffect();
    custom.addTelMask();
    custom.cf7CloseAfterSent();
    custom.formValidation();
    custom.formSubmittingWatcher();
  },

  cf7CloseAfterSent: function () {
    const forms = document.querySelectorAll(".wpcf7-form");
    forms.forEach(function (form) {
      form.addEventListener("submit", function (event) {
        // Проверка на валидность формы перед отправкой
        if (!form.checkValidity()) {
          event.preventDefault();
          const submitButton = form.querySelector('button[type="submit"]');
          if (submitButton) {
            submitButton.disabled = false; // можно оставить, чтобы кнопка не залипала
          }
          return;
        }

        // Если форма валидна, показываем модальное окно
        const modalContent = document.getElementById("modal-content");
        if (modalContent) {
          modalContent.innerHTML = `
          <div class="container">
            <div class="row">
              <div class="col-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 395.7" class="mb-3 svg-inject icon-svg icon-svg-lg text-primary">
                  <path class="lineal-stroke" d="M483.6 395.7H53.3C23.9 395.7 0 371.9 0 342.4V53.3C0 23.9 23.9 0 53.3 0h405.4C488.1 0 512 23.9 512 53.3v222.8c0 7.9-6.4 14.2-14.2 14.2s-14.2-6.4-14.2-14.2V53.3c0-13.7-11.1-24.8-24.8-24.8H53.3c-13.7 0-24.8 11.1-24.8 24.8v289.2c0 13.7 11.1 24.8 24.8 24.8h430.3c7.9.2 14.1 6.7 13.8 14.6-.2 7.5-6.3 13.6-13.8 13.8z"></path>
                  <path class="lineal-fill" d="M497.8 53.3L256 236.4 14.2 53.3c0-21.6 17.5-39.1 39.1-39.1h405.4c21.6 0 39.1 17.5 39.1 39.1z"></path>
                  <path class="lineal-stroke" d="M256 250.6c-3.1 0-6.1-1-8.6-2.9L5.6 64.6C2.1 61.9 0 57.7 0 53.3 0 23.9 23.9 0 53.3 0h405.4C488.1 0 512 23.9 512 53.3c0 4.4-2.1 8.6-5.6 11.3L264.6 247.7c-2.5 1.9-5.5 2.9-8.6 2.9zM29.3 46.8L256 218.6 482.7 46.8c-2.9-10.9-12.8-18.4-24-18.4H53.3c-11.3.1-21.1 7.6-24 18.4zm454.2 348.7c-3.1 0-6.1-1-8.6-2.9l-99.6-75.4c-6.3-4.7-7.5-13.7-2.7-19.9 4.7-6.3 13.7-7.5 19.9-2.7l99.6 75.4c6.3 4.7 7.5 13.7 2.8 19.9-2.7 3.6-6.9 5.7-11.4 5.6zm-449-4.6c-7.9 0-14.2-6.4-14.2-14.2 0-4.5 2.1-8.7 5.6-11.4l93.5-70.8c6.3-4.7 15.2-3.5 19.9 2.7 4.7 6.3 3.5 15.2-2.7 19.9L43.1 388c-2.5 1.9-5.5 2.9-8.6 2.9z"></path>
                </svg>
              </div>
            </div>
            <div class="row">
              <div class="col-12 text-center">
                <div class="card-title h4">Сообщение успешно отправлено.</div>
              </div>
            </div>
          </div>
        `;
          const modal = document.getElementById("modal");
          if (modal) {
            modal.classList.add("show");
          }
        }
      });
    });
  },

  addTelMask: function () {
    const telInputs = document.querySelectorAll("input.phone-mask");

    telInputs.forEach((input) => {
      const placeholder = input.getAttribute("placeholder");
      let mask;

      if (placeholder && /\d/.test(placeholder)) {
        const prefixMatch = placeholder.match(/^[^(\d]*[\d ]*/);
        const prefix = prefixMatch ? prefixMatch[0] : "";
        const rest = placeholder.slice(prefix.length).replace(/[0-9]/g, "_");
        mask = prefix + rest;
      } else {
        mask = "+7 ___ ___-__-__";
      }

      const phoneMask = new PhoneMask(input, {
        mask: mask,
      });

      // Функция проверки валидности
      const validatePhone = () => {
        const unmaskedValue = phoneMask.unmask;
        const requiredLength = phoneMask.options.unmaskMaxLength;

        if (unmaskedValue.length < requiredLength) {
          input.setCustomValidity("Введите полный номер телефона");
          input.classList.add("is-invalid");
          input.classList.remove("is-valid");
        } else {
          input.setCustomValidity("");
          input.classList.remove("is-invalid");
          input.classList.add("is-valid");
        }
      };

      // Проверка при вводе и стирании
      input.addEventListener("input", validatePhone);

      // Проверка при потере фокуса
      input.addEventListener("blur", validatePhone);

      // Проверка при попытке отправки формы
      const form = input.closest("form");
      if (form) {
        form.addEventListener("submit", (e) => {
          validatePhone();
          if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
          }
          form.classList.add("was-validated");
        });
      }
    });
  },

  /**
   * Form Validation
   * Adds Bootstrap 4/5 form validation behavior
   */
  formValidation: function () {
    var forms = document.getElementsByClassName("needs-validation");

    Array.prototype.forEach.call(forms, function (form) {
      form.addEventListener(
        "submit",
        function (event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add("was-validated");
        },
        false
      );
    });
  },

  /**
   * Form Submit Status Watcher
   * Listens for 'submitting', 'invalid', 'unaccepted' and 'sent' classes on forms and updates the submit button text
   */
  formSubmittingWatcher: function () {
    var forms = document.getElementsByClassName("wpcf7-form");

    Array.prototype.forEach.call(forms, function (form) {
      // Найти кнопку отправки внутри формы
      var submitButton = form.querySelector(
        'button[type="submit"], input[type="submit"], div[type="submit"], span[type="submit"]'
      );

      if (submitButton) {
        // Сохраняем оригинальный текст в data-атрибут
        if (submitButton.tagName.toLowerCase() === "input") {
          submitButton.setAttribute("data-original-text", submitButton.value);
        } else {
          submitButton.setAttribute(
            "data-original-text",
            submitButton.innerHTML
          );
        }
      }

      var observer = new MutationObserver(function (mutationsList) {
        mutationsList.forEach(function (mutation) {
          if (mutation.attributeName === "class") {
            if (form.classList.contains("submitting")) {
              // Если форма отправляется — меняем текст
              if (submitButton) {
                var loadingText =
                  'Отправка... <i class="uil uil-envelope-upload ms-2"></i>';
                if (submitButton.tagName.toLowerCase() === "input") {
                  submitButton.value = "Отправка...";
                } else {
                  submitButton.innerHTML = loadingText;
                }
              }
            } else if (
              form.classList.contains("invalid") ||
              form.classList.contains("unaccepted")
            ) {
              // Если форма вернула invalid или unaccepted — возвращаем оригинальный текст
              if (submitButton) {
                var originalText =
                  submitButton.getAttribute("data-original-text");
                if (submitButton.tagName.toLowerCase() === "input") {
                  submitButton.value = originalText;
                } else {
                  submitButton.innerHTML = originalText;
                }
              }
            } else if (form.classList.contains("sent")) {
              // Если форма успешно отправлена — меняем текст на "Отправлено"
              if (submitButton) {
                var successText =
                  'Отправлено <i class="uil uil-check-circle ms-2"></i>';
                if (submitButton.tagName.toLowerCase() === "input") {
                  submitButton.value = "Отправлено";
                } else {
                  submitButton.innerHTML = successText;
                }
              }
            }
          }
        });
      });

      observer.observe(form, { attributes: true });
    });
  },

  rippleEffect: () => {
    document.querySelectorAll(".has-ripple").forEach((button) => {
      // Проверяем, была ли кнопка уже инициализирована
      if (button.dataset.rippleInitialized) {
        return;
      }

      const createRipple = (e) => {
        const rect = button.getBoundingClientRect();
        const ripple = document.createElement("span");
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        ripple.style.width = ripple.style.height = `${size}px`;
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;
        ripple.className = "a-ripple a-ripple-animate";
        button.appendChild(ripple);
        ripple.addEventListener("animationend", () => {
          ripple.remove();
        });
      };

      button.addEventListener("click", createRipple);
      button.addEventListener("mouseenter", createRipple);
      button.dataset.rippleInitialized = "true";
    });
  },
};
custom.init();