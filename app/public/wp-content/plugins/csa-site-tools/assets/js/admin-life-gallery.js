(function ($) {
  "use strict";

  function rowTemplate(index) {
    return (
      '<tr class="kms-life-gallery-row">' +
        '<td>' +
          '<input type="hidden" name="kms_life_gallery[' + index + '][image_id]" value="0" class="kms-life-image-id">' +
          '<input type="url" name="kms_life_gallery[' + index + '][image_url]" value="" class="regular-text code kms-life-image-url" style="width: 100%;">' +
          '<p>' +
            '<button type="button" class="button kms-life-select-media">Choose</button> ' +
            '<button type="button" class="button kms-life-clear-media">Clear</button>' +
          '</p>' +
        '</td>' +
        '<td>' +
          '<img src="" class="kms-life-preview" alt="" style="width: 140px; height: 92px; object-fit: cover; border: 1px solid #dcdcde; border-radius: 4px; background: #f6f7f7;">' +
        '</td>' +
        '<td><input type="text" name="kms_life_gallery[' + index + '][title]" value="" class="regular-text" style="width: 100%;"></td>' +
        '<td><input type="text" name="kms_life_gallery[' + index + '][description]" value="" class="regular-text" style="width: 100%;"></td>' +
        '<td><input type="text" name="kms_life_gallery[' + index + '][alt]" value="" class="regular-text" style="width: 100%;"></td>' +
        '<td style="text-align:center;"><button type="button" class="button-link-delete kms-life-remove-row">Remove</button></td>' +
      '</tr>'
    );
  }

  function renumberRows() {
    $("#kms-life-gallery-table tbody .kms-life-gallery-row").each(function (index) {
      $(this).find("input[name]").each(function () {
        var current = $(this).attr("name");
        if (!current) {
          return;
        }

        var next = current.replace(/kms_life_gallery\[\d+\]/, "kms_life_gallery[" + index + "]");
        $(this).attr("name", next);
      });
    });
  }

  function openMediaFrame(row) {
    var frame = wp.media({
      title: "Select Gallery Image",
      button: { text: "Use this image" },
      multiple: false
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      if (!attachment || !attachment.url) {
        return;
      }

      row.find(".kms-life-image-id").val(attachment.id || 0);
      row.find(".kms-life-image-url").val(attachment.url).trigger("change");
      row.find(".kms-life-preview").attr("src", attachment.url).css("opacity", "1");

      var altInput = row.find('input[name$="[alt]"]');
      if (altInput.length && !altInput.val() && attachment.alt) {
        altInput.val(attachment.alt);
      }
    });

    frame.open();
  }

  function bindEvents() {
    $(document).on("click", ".kms-life-add-row", function (event) {
      event.preventDefault();
      var tbody = $("#kms-life-gallery-table tbody");
      if (!tbody.length) {
        return;
      }

      var nextIndex = tbody.find(".kms-life-gallery-row").length;
      tbody.append(rowTemplate(nextIndex));
      renumberRows();
    });

    $(document).on("click", ".kms-life-remove-row", function (event) {
      event.preventDefault();
      var row = $(this).closest(".kms-life-gallery-row");
      if (!row.length) {
        return;
      }

      row.remove();
      renumberRows();
    });

    $(document).on("click", ".kms-life-select-media", function (event) {
      event.preventDefault();
      var row = $(this).closest(".kms-life-gallery-row");
      if (!row.length) {
        return;
      }

      openMediaFrame(row);
    });

    $(document).on("click", ".kms-life-clear-media", function (event) {
      event.preventDefault();
      var row = $(this).closest(".kms-life-gallery-row");
      if (!row.length) {
        return;
      }

      row.find(".kms-life-image-id").val("0");
      row.find(".kms-life-image-url").val("");
      row.find(".kms-life-preview").attr("src", "").css("opacity", "0.35");
    });

    $(document).on("input", ".kms-life-image-url", function () {
      var row = $(this).closest(".kms-life-gallery-row");
      if (!row.length) {
        return;
      }

      var url = String($(this).val() || "").trim();
      row.find(".kms-life-preview").attr("src", url).css("opacity", url ? "1" : "0.35");
      if (!url) {
        row.find(".kms-life-image-id").val("0");
      }
    });
  }

  $(function () {
    bindEvents();
    renumberRows();
  });
})(jQuery);
