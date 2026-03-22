(function () {
  function isEditorActive() {
    return !!document.body && document.body.classList.contains("elementor-editor-active");
  }

  function isParityMode() {
    return !!document.body && document.body.classList.contains("kms-native-parity-mode");
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

    if (isEditorActive()) {
      return;
    }

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
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", unwrapParityWidgets);
  } else {
    unwrapParityWidgets();
  }
})();
