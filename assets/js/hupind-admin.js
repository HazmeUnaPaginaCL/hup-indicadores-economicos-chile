/* HUP Indicadores Chile — interacciones propias del panel. */
(function () {
  'use strict';

  function initShortcodeBuilder() {
    var preview = document.getElementById('hupind-sc-preview');
    if (!preview) return;

    var dateFormatRow = document.getElementById('hupind-date-format-row');
    var separatorRow = document.getElementById('hupind-separator-row');
    var noIndicators = document.getElementById('hupind-no-indicators');
    var copyButton = document.getElementById('hupind-copy-btn');

    function escAttr(value) {
      return String(value).replace(/"/g, '&quot;');
    }

    function buildShortcode() {
      var checks = document.querySelectorAll('.hupind-indicator-check:checked');
      var ids = Array.from(checks).map(function (check) {
        return check.closest('[data-indicator-id]').dataset.indicatorId;
      });
      var layoutEl = document.querySelector('[name="sc_layout"]:checked');
      var separatorEl = document.getElementById('sc-separator');
      var formatEl = document.getElementById('sc-format');
      var classEl = document.getElementById('sc-class');
      var variationEl = document.getElementById('sc-show-variation');
      var dateEl = document.getElementById('sc-show-date');
      var dateFormatEl = document.getElementById('sc-date-format');

      var layout = layoutEl ? layoutEl.value : 'horizontal';
      var separator = separatorEl ? separatorEl.value : '|';
      var format = formatEl ? formatEl.value : 'auto';
      var extraClass = classEl ? classEl.value.trim() : '';
      var showVariation = variationEl && variationEl.checked;
      var showDate = dateEl && dateEl.checked;
      var dateFormat = dateFormatEl ? dateFormatEl.value : 'short';

      if (separatorRow) separatorRow.hidden = layout === 'vertical';
      if (dateFormatRow) dateFormatRow.hidden = !showDate;
      if (noIndicators) noIndicators.hidden = ids.length > 0;
      if (copyButton) copyButton.disabled = ids.length === 0;

      var shortcode = '[hupind_indicadores';
      if (ids.length) shortcode += ' show="' + ids.join(',') + '"';
      if (layout !== 'horizontal') shortcode += ' layout="' + layout + '"';
      if (separator && separator !== '|') shortcode += ' separator="' + escAttr(separator) + '"';
      if (extraClass) shortcode += ' class="' + escAttr(extraClass) + '"';
      if (format && format !== 'auto') shortcode += ' format="' + escAttr(format) + '"';
      if (showVariation) shortcode += ' show_variation="true"';
      if (showDate) shortcode += ' show_date="true"';
      if (showDate && dateFormat !== 'short') shortcode += ' date_format="' + escAttr(dateFormat) + '"';
      shortcode += ']';
      preview.textContent = shortcode;
    }

    document.querySelectorAll('.hupind-indicator-check, [name="sc_layout"], #sc-separator, #sc-format, #sc-class, #sc-show-variation, #sc-show-date, #sc-date-format').forEach(function (control) {
      control.addEventListener('change', buildShortcode);
      control.addEventListener('input', buildShortcode);
    });
    buildShortcode();
  }

  function initAduaneroNote() {
    var note = document.getElementById('hupind-aduanero-note');
    var item = document.querySelector('[data-indicator-id="dolar_aduanero"]');
    var checkbox = item ? item.querySelector('.hupind-indicator-check') : null;
    if (!note || !checkbox) return;

    function sync() {
      note.hidden = !checkbox.checked;
    }
    checkbox.addEventListener('change', sync);
    sync();
  }

  function initClearCache() {
    // HUP UI registra antes su confirmación en document. Si se cancela, marca el evento.
    document.addEventListener('click', function (event) {
      var button = event.target.closest('#hupind-clear-cache-btn');
      if (!button || event.defaultPrevented || button.disabled) return;

      var label = button.querySelector('.hup-btn__label');
      var icon = button.querySelector('.dashicons-trash');
      var spinner = button.querySelector('.hup-spinner');
      button.disabled = true;
      if (label) label.textContent = hupindData.i18n.clearing;
      if (icon) icon.hidden = true;
      if (spinner) spinner.hidden = false;

      var data = new FormData();
      data.append('action', 'hupind_clear_cache');
      data.append('_ajax_nonce', hupindData.nonceClear);

      fetch(hupindData.ajaxUrl, { method: 'POST', body: data })
        .then(function (response) { return response.json(); })
        .then(function (result) {
          if (!result.success) throw new Error('clear-cache-failed');
          if (window.HUP && HUP.toast) HUP.toast(hupindData.i18n.cleared);
          var lastUpdate = document.getElementById('hupind-last-update');
          if (lastUpdate && result.data && result.data.last_update) {
            lastUpdate.textContent = result.data.last_update;
          }
        })
        .catch(function () {
          if (window.HUP && HUP.toast) HUP.toast(hupindData.i18n.error);
        })
        .finally(function () {
          button.disabled = false;
          if (label) label.textContent = 'Limpiar caché';
          if (icon) icon.hidden = false;
          if (spinner) spinner.hidden = true;
        });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initShortcodeBuilder();
    initAduaneroNote();
    initClearCache();
  });
}());
