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

  function syncStickyHeaderMetrics() {
    var header = document.getElementById("header");
    if (!header) {
      return;
    }

    var root = document.documentElement;

    function apply() {
      var rect = header.getBoundingClientRect();
      var height = Math.ceil(rect && rect.height ? rect.height : header.offsetHeight || 0);
      if (height > 0) {
        root.style.setProperty("--csa-sticky-header-live", height + "px");
      }
    }

    apply();
    window.addEventListener("resize", apply, { passive: true });
    window.addEventListener("load", apply, { passive: true });
    setTimeout(apply, 220);
  }

  ready(function () {
    hydrateLazyImages();
    toggleSearch();
    toggleMobileMenu();
    toggleSubmenus();
    curriculumDesktopSwitcher();
    disableCurriculumLinks();
    faqToggle();
    scrollTopWidget();
    syncStickyHeaderMetrics();
  });
})();
