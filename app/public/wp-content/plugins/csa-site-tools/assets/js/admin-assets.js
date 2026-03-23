(function ($) {
  "use strict";

  function setPreview(previewId, url) {
    var preview = document.getElementById(previewId);
    if (!preview) {
      return;
    }

    if (url) {
      preview.setAttribute("src", url);
      preview.style.opacity = "1";
    } else {
      preview.style.opacity = "0.3";
    }
  }

  function attachMediaPicker() {
    $(document).on("click", ".kms-select-media", function (event) {
      event.preventDefault();

      var button = $(this);
      var targetId = button.data("target");
      var previewId = button.data("preview");
      var targetInput = $("#" + targetId);

      if (!targetInput.length) {
        return;
      }

      var frame = wp.media({
        title: "Select Image",
        button: { text: "Use this image" },
        multiple: false
      });

      frame.on("select", function () {
        var attachment = frame.state().get("selection").first().toJSON();
        if (!attachment || !attachment.url) {
          return;
        }

        targetInput.val(attachment.url).trigger("change");
        setPreview(previewId, attachment.url);
      });

      frame.open();
    });

    $(document).on("click", ".kms-clear-media", function (event) {
      event.preventDefault();

      var button = $(this);
      var targetId = button.data("target");
      var previewId = button.data("preview");
      var targetInput = $("#" + targetId);

      if (targetInput.length) {
        targetInput.val("").trigger("change");
      }

      setPreview(previewId, "");
    });
  }

  function attachSearchFilter() {
    var search = document.getElementById("kms-asset-search");
    if (!search) {
      return;
    }

    search.addEventListener("input", function () {
      var query = search.value.toLowerCase().trim();
      var rows = document.querySelectorAll("[data-kms-row]");

      rows.forEach(function (row) {
        var haystack = String(row.getAttribute("data-kms-label") || "");
        var visible = !query || haystack.indexOf(query) !== -1;
        row.style.display = visible ? "" : "none";
      });
    });
  }

  $(function () {
    attachMediaPicker();
    attachSearchFilter();
  });
})(jQuery);
