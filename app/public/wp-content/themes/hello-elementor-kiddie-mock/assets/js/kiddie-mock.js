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
        slide.classList.toggle("active", active);
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

  function faqToggle() {
    var questions = document.querySelectorAll("[data-question]");
    questions.forEach(function (question) {
      question.addEventListener("click", function () {
        question.classList.toggle("open");

        var answerId = question.getAttribute("data-question");
        if (!answerId) {
          return;
        }

        var answer = document.querySelector('[data-answer="' + answerId + '"]');
        if (!answer) {
          return;
        }

        var isHidden = answer.hasAttribute("hidden");
        if (isHidden) {
          answer.removeAttribute("hidden");
        } else {
          answer.setAttribute("hidden", "hidden");
        }
      });
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

  ready(function () {
    hydrateLazyImages();
    toggleSearch();
    toggleMobileMenu();
    toggleSubmenus();
    curriculumDesktopSwitcher();
    faqToggle();
    scrollTopWidget();
  });
})();
