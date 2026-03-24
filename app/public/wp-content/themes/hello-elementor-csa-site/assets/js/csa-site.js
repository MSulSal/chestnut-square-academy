(function () {
  "use strict";

  function ready(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else {
      fn();
    }
  }

  function hydrateLazyImages() {
    var lazyImages = document.querySelectorAll("img[data-lazy-src]");
    lazyImages.forEach(function (img) {
      var lazySrc = img.getAttribute("data-lazy-src");
      if (lazySrc && !img.getAttribute("src")) {
        img.setAttribute("src", lazySrc);
      }

      var lazySrcSet = img.getAttribute("data-lazy-srcset");
      if (lazySrcSet && !img.getAttribute("srcset")) {
        img.setAttribute("srcset", lazySrcSet);
      }
    });
  }

  function toggleSearch() {
    var header = document.getElementById("header");
    if (!header) {
      return;
    }

    var trigger = header.querySelector(".header-search");
    if (!trigger) {
      return;
    }

    trigger.addEventListener("click", function () {
      header.classList.toggle("search-open");
    });
  }

  function toggleMobileMenu() {
    var checkbox = document.getElementById("menu_checkbox");
    var compactCheckbox = document.getElementById("compact_menu_checkbox");

    function syncMenuState(source) {
      var isOpen = source && source.checked;
      document.body.classList.toggle("mobile-menu-open", !!isOpen);

      if (checkbox && source !== checkbox) {
        checkbox.checked = !!isOpen;
      }

      if (compactCheckbox && source !== compactCheckbox) {
        compactCheckbox.checked = !!isOpen;
      }
    }

    if (checkbox) {
      checkbox.addEventListener("change", function () {
        syncMenuState(checkbox);
      });
    }

    if (compactCheckbox) {
      compactCheckbox.addEventListener("change", function () {
        syncMenuState(compactCheckbox);
      });
    }
  }

  function toggleSubmenus() {
    var toggles = document.querySelectorAll("#header .child-menu-icon");
    toggles.forEach(function (toggle) {
      toggle.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();

        var li = toggle.closest("li");
        if (!li) {
          return;
        }

        li.classList.toggle("submenu-open");
      });
    });
  }

  function curriculumDesktopSwitcher() {
    var curriculum = document.getElementById("curriculum");
    if (!curriculum) {
      return;
    }

    var slides = curriculum.querySelectorAll(".programs-list .slide[data-program]");
    var images = curriculum.querySelectorAll(".programs-image img[data-program]");

    if (!slides.length || !images.length) {
      return;
    }

    function activate(program) {
      slides.forEach(function (slide) {
        var active = slide.getAttribute("data-program") === program;
        slide.classList.toggle("hover", active);
      });

      images.forEach(function (image) {
        var active = image.getAttribute("data-program") === program;
        image.style.display = active ? "block" : "none";
      });
    }

    slides.forEach(function (slide) {
      slide.addEventListener("mouseenter", function () {
        activate(slide.getAttribute("data-program"));
      });
      slide.addEventListener("focusin", function () {
        activate(slide.getAttribute("data-program"));
      });
    });

    activate(slides[0].getAttribute("data-program"));
  }

  function disableCurriculumLinks() {
    var links = document.querySelectorAll("#curriculum .slides .slide a");

    links.forEach(function (link) {
      if (link.dataset.kmsNoNavBound) {
        return;
      }

      link.setAttribute("aria-disabled", "true");
      link.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
      });

      link.addEventListener("keydown", function (event) {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          event.stopPropagation();
        }
      });

      link.dataset.kmsNoNavBound = "1";
    });
  }

  function bindLifeAgeTabs() {
    function getFeaturedImage(scope) {
      if (!scope) {
        return null;
      }

      return (
        scope.querySelector(".life-age-featured img") ||
        scope.querySelector("img.life-age-featured-img") ||
        scope.querySelector(".life-age-featured-img img")
      );
    }

    function getGalleryImages() {
      var nodes = document.querySelectorAll(
        ".life-gallery-grid img, .column-3-image-text-cards img"
      );
      var seen = Object.create(null);
      var images = [];

      nodes.forEach(function (img) {
        var src = img.getAttribute("src") || img.getAttribute("data-lazy-src");
        if (!src || seen[src]) {
          return;
        }

        seen[src] = true;
        images.push({
          src: src,
          alt: img.getAttribute("alt") || "",
        });
      });

      return images;
    }

    function bindScope(scope) {
      if (!scope || scope.dataset.kmsLifeTabsBound === "1") {
        return;
      }

      var tabs = Array.prototype.slice.call(scope.querySelectorAll(".life-age-tab"));
      var panels = Array.prototype.slice.call(scope.querySelectorAll(".life-age-panel"));

      if (!tabs.length || !panels.length) {
        return;
      }

      var featured = getFeaturedImage(scope);
      var galleryImages = getGalleryImages();

      function resolvePanelIndex(tab, fallbackIndex) {
        var key = tab && tab.getAttribute ? tab.getAttribute("data-life-tab") : "";
        if (key) {
          var keyedPanel = scope.querySelector('.life-age-panel[data-life-panel="' + key + '"]');
          if (keyedPanel) {
            var keyedIndex = panels.indexOf(keyedPanel);
            if (keyedIndex >= 0) {
              return keyedIndex;
            }
          }
        }

        return fallbackIndex;
      }

      function activateByIndex(nextIndex) {
        var normalizedIndex = typeof nextIndex === "number" && nextIndex >= 0 ? nextIndex : 0;

        tabs.forEach(function (tab, index) {
          var active = index === normalizedIndex;
          tab.classList.toggle("is-active", active);
          tab.setAttribute("aria-selected", active ? "true" : "false");
        });

        panels.forEach(function (panel, index) {
          var active = index === normalizedIndex;
          panel.classList.toggle("is-active", active);
          panel.style.display = active ? "" : "none";
        });
      }

      function updateFeaturedImage() {
        if (!featured || !galleryImages.length) {
          return;
        }

        var currentSrc = featured.getAttribute("src") || featured.getAttribute("data-lazy-src") || "";
        var next = galleryImages[Math.floor(Math.random() * galleryImages.length)];

        if (galleryImages.length > 1 && next.src === currentSrc) {
          next = galleryImages[(galleryImages.indexOf(next) + 1) % galleryImages.length];
        }

        featured.setAttribute("src", next.src);
        featured.setAttribute("data-lazy-src", next.src);
        if (next.alt) {
          featured.setAttribute("alt", next.alt);
        }
      }

      tabs.forEach(function (tab, index) {
        if (tab.dataset.kmsLifeTabBound === "1") {
          return;
        }

        tab.addEventListener("click", function (event) {
          event.preventDefault();
          var panelIndex = resolvePanelIndex(tab, index);
          activateByIndex(panelIndex);
          updateFeaturedImage();
        });

        tab.addEventListener("keydown", function (event) {
          if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            var panelIndex = resolvePanelIndex(tab, index);
            activateByIndex(panelIndex);
            updateFeaturedImage();
          }
        });

        tab.dataset.kmsLifeTabBound = "1";
      });

      var activeTab = scope.querySelector(".life-age-tab.is-active");
      var activeIndex = activeTab ? tabs.indexOf(activeTab) : 0;
      activateByIndex(activeIndex >= 0 ? activeIndex : 0);
      scope.dataset.kmsLifeTabsBound = "1";
    }

    function initialize() {
      document.querySelectorAll(".life-age-groups").forEach(bindScope);
    }

    initialize();
    window.setTimeout(initialize, 240);
    window.setTimeout(initialize, 920);
  }

  function faqToggle() {
    if (document.body && document.body.classList.contains("kms-native-parity-mode")) {
      return;
    }

    var questions = document.querySelectorAll("[data-question]");

    if (!questions.length) {
      return;
    }

    function getAnswerFor(question) {
      var answerId = question.getAttribute("data-question");
      if (!answerId) {
        return null;
      }

      var parent = question.parentElement;
      if (parent) {
        var scoped = parent.querySelector('[data-answer="' + answerId + '"]');
        if (scoped) {
          return scoped;
        }

        var scopedClassFallback = parent.querySelector(".kms-data-answer-" + answerId);
        if (scopedClassFallback) {
          return scopedClassFallback;
        }
      }

      return (
        document.querySelector('[data-answer="' + answerId + '"]') ||
        document.querySelector(".kms-data-answer-" + answerId)
      );
    }

    function setOpen(question, open) {
      var answer = getAnswerFor(question);
      if (!answer) {
        return;
      }

      question.classList.toggle("open", !!open);
      question.setAttribute("aria-expanded", open ? "true" : "false");

      if (open) {
        answer.removeAttribute("hidden");
        answer.style.display = "block";
      } else {
        answer.setAttribute("hidden", "hidden");
        answer.style.display = "none";
      }
    }

    function toggleQuestion(question) {
      var answer = getAnswerFor(question);
      if (!answer) {
        return;
      }

      var computed = window.getComputedStyle ? window.getComputedStyle(answer) : null;
      var isVisibleByStyle = computed ? computed.display !== "none" && computed.visibility !== "hidden" : answer.style.display !== "none";
      var isOpen = !answer.hasAttribute("hidden") && isVisibleByStyle;
      setOpen(question, !isOpen);
    }

    questions.forEach(function (question) {
      if (question.dataset.kmsBoundFaq) {
        return;
      }

      question.setAttribute("role", "button");
      question.setAttribute("tabindex", "0");
      setOpen(question, false);

      question.addEventListener("click", function () {
        toggleQuestion(question);
      });

      question.addEventListener("keydown", function (event) {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          toggleQuestion(question);
        }
      });

      question.dataset.kmsBoundFaq = "1";
    });
  }

  function scrollTopWidget() {
    var button = document.getElementById("scroll-top");
    if (!button) {
      return;
    }

    var onScroll = function () {
      var visible = window.scrollY > 600;
      button.classList.toggle("visible", visible);
    };

    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();

    button.addEventListener("click", function (event) {
      event.preventDefault();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  function bindAboutAnchorScroll() {
    function normalizePath(pathname) {
      var value = pathname || "/";
      if (value.length > 1 && value.slice(-1) === "/") {
        return value.slice(0, -1);
      }
      return value || "/";
    }

    function getAnchorTarget() {
      return document.querySelector("section#about-home") || document.getElementById("about-home");
    }

    function getHeaderOffset() {
      var header = document.getElementById("header");
      if (!header) {
        return 0;
      }
      return Math.ceil(header.offsetHeight || 0);
    }

    function scrollToAbout(behavior) {
      var target = getAnchorTarget();
      if (!target) {
        return;
      }

      var offset = getHeaderOffset() + 10;
      var y = target.getBoundingClientRect().top + window.pageYOffset - offset;
      var top = Math.max(0, Math.round(y));
      window.scrollTo({
        top: top,
        behavior: behavior || "smooth",
      });
    }

    var links = document.querySelectorAll('a[href*="#about-home"]');
    links.forEach(function (link) {
      if (link.dataset.kmsAboutAnchorBound === "1") {
        return;
      }

      link.addEventListener("click", function (event) {
        var href = link.getAttribute("href") || "";
        if (!href || href.indexOf("#about-home") === -1) {
          return;
        }

        var url;
        try {
          url = new URL(href, window.location.href);
        } catch (error) {
          return;
        }

        if (url.hash !== "#about-home") {
          return;
        }

        var samePath =
          normalizePath(url.pathname) === normalizePath(window.location.pathname);

        if (!samePath) {
          return;
        }

        event.preventDefault();
        if (window.history && window.history.pushState) {
          window.history.pushState({}, "", url.href);
        }
        scrollToAbout("smooth");
      });

      link.dataset.kmsAboutAnchorBound = "1";
    });

    if (window.location.hash === "#about-home") {
      setTimeout(function () {
        scrollToAbout("auto");
      }, 80);
      setTimeout(function () {
        scrollToAbout("auto");
      }, 260);
    }

    window.addEventListener("hashchange", function () {
      if (window.location.hash === "#about-home") {
        scrollToAbout("smooth");
      }
    });
  }

  ready(function () {
    hydrateLazyImages();
    toggleSearch();
    toggleMobileMenu();
    toggleSubmenus();
    curriculumDesktopSwitcher();
    disableCurriculumLinks();
    bindLifeAgeTabs();
    faqToggle();
    scrollTopWidget();
    bindAboutAnchorScroll();
  });
})();
