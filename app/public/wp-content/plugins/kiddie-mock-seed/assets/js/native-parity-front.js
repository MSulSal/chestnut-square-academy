(function () {
  function isEditorActive() {
    return !!document.body && document.body.classList.contains("elementor-editor-active");
  }

  function isEditorPreviewQuery() {
    if (!window.location || !window.location.search) {
      return false;
    }
    return /(?:^|[?&])(elementor-preview=|action=elementor(?:&|$))/i.test(window.location.search);
  }

  function isParityMode() {
    return !!document.body && document.body.classList.contains("kms-native-parity-mode");
  }

  function restoreDomIdsFromClasses() {
    var nodes = document.querySelectorAll('.kms-native-parity-mode [class*="kms-dom-id-"]');
    nodes.forEach(function (node) {
      if (node.id) {
        return;
      }
      var token = Array.from(node.classList).find(function (cls) {
        return cls.indexOf("kms-dom-id-") === 0;
      });
      if (!token) {
        return;
      }
      var domId = token.replace("kms-dom-id-", "").trim();
      if (!domId) {
        return;
      }
      node.id = domId;
    });
  }

  function restoreDataAttrsFromClasses() {
    var nodes = document.querySelectorAll('.kms-native-parity-mode [class*="kms-data-"]');
    nodes.forEach(function (node) {
      Array.from(node.classList).forEach(function (cls) {
        if (!cls || cls.indexOf("kms-data-") !== 0) {
          return;
        }

        var match = cls.match(/^kms-data-([a-z0-9]+)-(.+)$/i);
        if (!match || match.length < 3) {
          return;
        }

        var key = (match[1] || "").trim().toLowerCase();
        var value = (match[2] || "").trim();
        if (!key || !value) {
          return;
        }

        var attrName = "data-" + key;
        if (!node.hasAttribute(attrName)) {
          node.setAttribute(attrName, value);
        }
      });
    });
  }

  function stripElementorClassesForParity() {
    var removableExact = {
      "e-con": true,
      "e-con-full": true,
      "e-con-boxed": true,
      "e-parent": true,
      "e-child": true,
      "e-flex": true,
      "e-grid": true,
      "e-lazyloaded": true,
    };

    var nodes = document.querySelectorAll(
      ".kms-native-parity-mode main#main-content, .kms-native-parity-mode main#main-content [class]"
    );
    nodes.forEach(function (node) {
      var retained = [];

      Array.from(node.classList).forEach(function (cls) {
        if (!cls) {
          return;
        }

        if (removableExact[cls]) {
          return;
        }

        if (cls.indexOf("e-con") === 0) {
          return;
        }

        if (cls !== "elementor" && cls.indexOf("elementor-") === 0) {
          return;
        }

        if (cls.indexOf("e--") === 0) {
          return;
        }

        retained.push(cls);
      });

      var nextClass = retained.join(" ").trim();
      if (node.className !== nextClass) {
        node.className = nextClass;
      }
    });
  }

  function bindCurriculumDesktopSwitcher() {
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
      if (!program) {
        return;
      }

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
      if (!slide.dataset.kmsBoundProgram) {
        slide.addEventListener("mouseenter", function () {
          activate(slide.getAttribute("data-program"));
        });
        slide.addEventListener("focusin", function () {
          activate(slide.getAttribute("data-program"));
        });
        slide.dataset.kmsBoundProgram = "1";
      }
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

  function bindFaqToggle() {
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

  function getTransferClasses(widget) {
    return Array.from(widget.classList).filter(function (cls) {
      return cls && cls.indexOf("elementor-") !== 0;
    });
  }

  function transferIdentity(widget, target) {
    if (!target || !target.classList) {
      return;
    }

    getTransferClasses(widget).forEach(function (cls) {
      target.classList.add(cls);
    });

    if (widget.id && !target.id) {
      target.id = widget.id;
    }
  }

  function unwrapTextEditorWidget(widget) {
    if (!widget.parentNode) {
      return;
    }

    var root = widget.firstElementChild;
    var hasContainerRoot = !!(
      root &&
      root.classList &&
      root.classList.contains("elementor-widget-container")
    );

    if (hasContainerRoot) {
      var firstElementChild = root.children.length === 1 ? root.children[0] : null;
      if (firstElementChild) {
        transferIdentity(widget, firstElementChild);
      }

      while (root.firstChild) {
        widget.parentNode.insertBefore(root.firstChild, widget);
      }
    } else {
      var directElementChildren = Array.from(widget.childNodes).filter(function (node) {
        return node && node.nodeType === 1;
      });
      if (directElementChildren.length === 1) {
        transferIdentity(widget, directElementChildren[0]);
      }

      while (widget.firstChild) {
        widget.parentNode.insertBefore(widget.firstChild, widget);
      }
    }

    widget.parentNode.removeChild(widget);
  }

  function unwrapSingleChildWidget(widget, selector) {
    var root = widget.firstElementChild;
    if (!root) {
      return;
    }
    if (!widget.parentNode) {
      return;
    }

    var target = null;
    if (root.classList && root.classList.contains("elementor-widget-container")) {
      target = root.querySelector(selector);
    } else if (root.matches && root.matches(selector)) {
      target = root;
    } else if (root.querySelector) {
      target = root.querySelector(selector);
    }

    if (!target) {
      return;
    }

    transferIdentity(widget, target);
    widget.parentNode.insertBefore(target, widget);
    widget.parentNode.removeChild(widget);
  }

  function unwrapParityWidgets() {
    if (!isParityMode()) {
      return;
    }

    if (isEditorActive() || isEditorPreviewQuery()) {
      return;
    }

    restoreDomIdsFromClasses();
    restoreDataAttrsFromClasses();

    var textWidgets = document.querySelectorAll(".kms-native-parity-mode .elementor-widget-text-editor");
    textWidgets.forEach(unwrapTextEditorWidget);

    var headingWidgets = document.querySelectorAll(".kms-native-parity-mode .elementor-widget-heading");
    headingWidgets.forEach(function (widget) {
      unwrapSingleChildWidget(widget, ".elementor-heading-title");
    });

    var imageWidgets = document.querySelectorAll(".kms-native-parity-mode .elementor-widget-image");
    imageWidgets.forEach(function (widget) {
      unwrapSingleChildWidget(widget, "img");
    });

    var buttonWidgets = document.querySelectorAll(".kms-native-parity-mode .elementor-widget-button");
    buttonWidgets.forEach(function (widget) {
      unwrapSingleChildWidget(widget, "a.elementor-button-link, button");
    });

    stripElementorClassesForParity();
    bindCurriculumDesktopSwitcher();
    disableCurriculumLinks();
    bindFaqToggle();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", unwrapParityWidgets);
  } else {
    unwrapParityWidgets();
  }
})();
