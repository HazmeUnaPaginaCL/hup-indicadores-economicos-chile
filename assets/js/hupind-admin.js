/* HUP Indicadores Chile — Admin JS */
(function () {
  'use strict';

  /* ── Tabs ─────────────────────────────────────────────────────────── */
  function initTabs() {
    var btns   = document.querySelectorAll('.hupind-tab-btn');
    var panels = document.querySelectorAll('.hupind-tab-panel');

    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = btn.dataset.tab;

        btns.forEach(function (b) { b.classList.remove('is-active'); });
        panels.forEach(function (p) { p.classList.add('hupind-hidden'); });

        btn.classList.add('is-active');
        var panel = document.getElementById('hupind-tab-' + target);
        if (panel) panel.classList.remove('hupind-hidden');

        if (window.history && window.history.replaceState) {
          var url = new URL(window.location.href);
          url.searchParams.set('tab', target);
          window.history.replaceState(null, '', url.toString());
        }
      });
    });
  }

  /* ── Shortcode builder live ───────────────────────────────────────── */
  function initShortcodeBuilder() {
    var preview = document.getElementById('hupind-sc-preview');
    if (!preview) return;

    var dateFormatRow = document.getElementById('hupind-date-format-row');

    function toggleDateFormatRow(visible) {
      if (dateFormatRow) dateFormatRow.style.display = visible ? '' : 'none';
    }

    function buildShortcode() {
      // Indicadores activos (en orden del DOM)
      var checks = document.querySelectorAll('.hupind-indicator-check:checked');
      var ids = Array.from(checks).map(function (c) {
        return c.closest('[data-indicator-id]').dataset.indicatorId;
      });

      // Opciones
      var layoutEl     = document.querySelector('[name="sc_layout"]:checked');
      var sepEl        = document.getElementById('sc-separator');
      var fmtEl        = document.getElementById('sc-format');
      var classEl      = document.getElementById('sc-class');
      var varEl        = document.getElementById('sc-show-variation');
      var dateEl       = document.getElementById('sc-show-date');
      var dateFmtEl    = document.getElementById('sc-date-format');

      var layout   = layoutEl  ? layoutEl.value        : 'horizontal';
      var sep      = sepEl     ? sepEl.value            : '|';
      var fmt      = fmtEl     ? fmtEl.value            : 'auto';
      var cls      = classEl   ? classEl.value.trim()   : '';
      var showVar  = varEl     ? varEl.checked          : false;
      var showDate = dateEl    ? dateEl.checked         : false;
      var dateFmt  = dateFmtEl ? dateFmtEl.value        : 'short';

      // Mostrar/ocultar campo separador según layout
      var sepRow = document.getElementById('hupind-separator-row');
      if (sepRow) sepRow.style.display = layout === 'vertical' ? 'none' : '';

      // Mostrar/ocultar selector de formato de fecha
      toggleDateFormatRow(showDate);

      // Construir shortcode omitiendo defaults
      var sc = '[hupind_indicadores';
      if (ids.length)                sc += ' show="'          + ids.join(',')     + '"';
      if (layout !== 'horizontal')   sc += ' layout="'        + layout            + '"';
      if (sep && sep !== '|')        sc += ' separator="'     + escAttr(sep)      + '"';
      if (cls)                       sc += ' class="'         + escAttr(cls)      + '"';
      if (fmt && fmt !== 'auto')      sc += ' format="'        + escAttr(fmt)      + '"';
      if (showVar)                   sc += ' show_variation="true"';
      if (showDate)                  sc += ' show_date="true"';
      if (showDate && dateFmt && dateFmt !== 'short')
                                     sc += ' date_format="'   + escAttr(dateFmt)  + '"';
      sc += ']';

      preview.textContent = sc;
    }

    // Escuchar checkboxes de indicadores
    document.querySelectorAll('.hupind-indicator-check').forEach(function (cb) {
      cb.addEventListener('change', function () {
        var item = cb.closest('.hupind-indicator-item');
        if (item) item.classList.toggle('is-active', cb.checked);
        buildShortcode();
      });
    });

    // Escuchar controles de opciones
    var controls = document.querySelectorAll(
      '[name="sc_layout"], #sc-separator, #sc-format, #sc-class, #sc-show-variation, #sc-show-date, #sc-date-format'
    );
    controls.forEach(function (el) {
      el.addEventListener('change', buildShortcode);
      el.addEventListener('input',  buildShortcode);
    });

    buildShortcode();
  }

  /* ── Nota del Dólar Aduanero (visible solo cuando está activo) ─────── */
  function initAduaneroNote() {
    var note = document.getElementById('hupind-aduanero-note');
    if (!note) return;

    var item = document.querySelector('.hupind-indicator-item[data-indicator-id="dolar_aduanero"]');
    var cb   = item ? item.querySelector('.hupind-indicator-check') : null;
    if (!cb) return;

    function sync() {
      note.classList.toggle('hupind-hidden', !cb.checked);
    }
    cb.addEventListener('change', sync);
    sync();
  }

  /* ── Botón copiar (con fallback para HTTP) ────────────────────────── */
  function initCopyBtn() {
    var btn = document.getElementById('hupind-copy-btn');
    if (!btn) return;

    btn.addEventListener('click', function () {
      var preview = document.getElementById('hupind-sc-preview');
      if (!preview) return;
      var text = preview.textContent;

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function () {
          showToast('Shortcode copiado.', 'success');
        }).catch(function () {
          fallbackCopy(text);
        });
      } else {
        fallbackCopy(text);
      }
    });
  }

  function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0;';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try {
      document.execCommand('copy');
      showToast('Shortcode copiado.', 'success');
    } catch (e) {
      showToast('No se pudo copiar. Selecciona el texto manualmente.', 'error');
    }
    document.body.removeChild(ta);
  }

  /* ── AJAX: limpiar caché ─────────────────────────────────────────── */
  function initClearCache() {
    var btn = document.getElementById('hupind-clear-cache-btn');
    if (!btn) return;

    btn.addEventListener('click', function () {
      btn.disabled = true;
      btn.innerHTML = '<span class="dashicons dashicons-update"></span> ' + hupindData.i18n.clearing;

      var data = new FormData();
      data.append('action', 'hupind_clear_cache');
      data.append('_ajax_nonce', hupindData.nonceClear);

      fetch(hupindData.ajaxUrl, { method: 'POST', body: data })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          if (json.success) {
            showToast(hupindData.i18n.cleared, 'success');
            var el = document.getElementById('hupind-last-update');
            if (el && json.data && json.data.last_update) {
              el.textContent = json.data.last_update;
            }
          } else {
            showToast(hupindData.i18n.error, 'error');
          }
        })
        .catch(function () {
          showToast(hupindData.i18n.error, 'error');
        })
        .finally(function () {
          btn.disabled = false;
          btn.innerHTML = '<span class="dashicons dashicons-trash"></span> ' + (btn.dataset.label || 'Limpiar caché');
        });
    });
  }

  /* ── Toast ───────────────────────────────────────────────────────── */
  function showToast(message, type) {
    var toast = document.getElementById('hupind-toast');
    if (!toast) return;

    toast.className = 'hupind-toast hupind-toast--' + (type || 'success');
    toast.textContent = message;

    requestAnimationFrame(function () {
      toast.classList.add('is-visible');
    });

    setTimeout(function () {
      toast.classList.remove('is-visible');
    }, 3500);
  }

  /* ── Toast automático al guardar ─────────────────────────────────── */
  function maybeShowSavedToast() {
    var url = new URL(window.location.href);
    if (url.searchParams.get('saved') === '1') {
      showToast(hupindData.i18n.saved, 'success');
      url.searchParams.delete('saved');
      window.history.replaceState(null, '', url.toString());
    }
  }

  function escAttr(str) {
    return String(str).replace(/"/g, '&quot;');
  }

  /* ── Init ────────────────────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', function () {
    initTabs();
    initShortcodeBuilder();
    initAduaneroNote();
    initCopyBtn();
    initClearCache();
    maybeShowSavedToast();
  });
}());
