/**
 * DaData: подсказки адресов (виджет jQuery Suggestions, как в плагине dadata-ru)
 * и кнопка «Проверить адрес» (clean через бэкенд).
 */
(function () {
  'use strict';

  var config = window.codeweberDadata || {};
  var ajaxUrl = config.ajaxUrl || '';
  var nonce = config.nonce || '';
  var token = config.dadataToken || '';
  var defaultPrefix = config.addressPrefix || 'billing';
  var minChars = typeof config.minChars === 'number' ? config.minChars : 2;
  var count = typeof config.count === 'number' ? config.count : 10;
  var debug = !!(config.debug);

  function log() {
    if (typeof console !== 'undefined' && console.log) {
      console.log.apply(console, ['[DaData]'].concat(Array.prototype.slice.call(arguments)));
    }
  }

  function getForm() {
    var form = document.querySelector('.woocommerce-EditAddressForm, .woocommerce-checkout');
    if (form) return form;
    var wrap = document.querySelector('.woocommerce-address-fields');
    return wrap && wrap.closest ? wrap.closest('form') : null;
  }

  function getField(prefix, name) {
    var form = getForm();
    var id = prefix + '_' + name;
    var selector = '#' + id + ', [name="' + prefix + '_' + name + '"]';
    if (form) return form.querySelector(selector);
    return document.querySelector(selector);
  }

  function setFieldValue(el, value) {
    if (!el) return;
    value = value || '';
    if (el.tagName === 'SELECT') {
      var opt = Array.prototype.find.call(el.options, function (o) { return o.value === value; });
      if (opt) {
        el.value = value;
        el.dispatchEvent(new Event('change', { bubbles: true }));
      } else {
        el.value = value;
      }
    } else {
      el.value = value;
      el.dispatchEvent(new Event('input', { bubbles: true }));
      el.dispatchEvent(new Event('change', { bubbles: true }));
    }
  }

  function fillFields(data, prefix) {
    var map = {
      country: 'country',
      state: 'state',
      city: 'city',
      address_1: 'address_1',
      address_2: 'address_2',
      postcode: 'postcode'
    };
    Object.keys(map).forEach(function (key) {
      var val = data[key];
      if (val === undefined) return;
      var p = prefix || defaultPrefix;
      var el = getField(p, map[key]);
      setFieldValue(el, val);
    });
  }

  function initSuggestionsWidget() {
    if (!token) {
      log('initSuggestionsWidget: пропуск — нет token (проверьте Redux → API → DaData API Token)');
      return;
    }
    if (typeof jQuery === 'undefined') {
      log('initSuggestionsWidget: пропуск — jQuery не найден');
      return;
    }
    if (!jQuery.fn.suggestions) {
      log('initSuggestionsWidget: пропуск — jQuery.fn.suggestions отсутствует (подключите jquery.suggestions.min.js)');
      return;
    }

    var locations = [{ country_iso_code: 'RU' }];
    if (config.locations && Array.isArray(config.locations)) {
      locations = config.locations;
    } else if (config.locationsString) {
      locations = config.locationsString.split(',').map(function (c) {
        return { country_iso_code: c.trim() };
      }).filter(function (o) { return o.country_iso_code; });
    }
    if (locations.length === 0) locations = [{ country_iso_code: 'RU' }];

    var ids = ['billing_address_1', 'shipping_address_1'];
    var inited = 0;
    ids.forEach(function (id) {
      var elById = document.getElementById(id);
      var elByName = document.querySelector('input[name="' + id + '"]');
      var el = elById || elByName;
      if (debug) log('Поле', id, '— по id:', !!elById, 'по name:', !!elByName, 'элемент:', el ? el.tagName : null);
      if (!el) return;
      if (el.tagName !== 'INPUT') {
        log('Поле', id, '— пропуск, не INPUT:', el.tagName);
        return;
      }
      if (jQuery(el).data('suggestions')) {
        if (debug) log('Поле', id, '— уже инициализировано');
        return;
      }

      var prefix = id.indexOf('shipping') === 0 ? 'shipping' : 'billing';
      log('Инициализация виджета для', id);
      try {
        jQuery(el).suggestions({
        token: token,
        type: 'ADDRESS',
        count: count,
        minChars: minChars,
        hint: (config.messages && config.messages.hint) ? config.messages.hint : 'Выберите вариант или продолжите ввод',
        constraints: { locations: locations },
        onSelect: function (suggestion) {
          if (debug) log('onSelect:', suggestion.value);
          var d = suggestion.data || {};
          if (prefix === 'billing') {
            jQuery('#billing_city').val(d.city || '');
            jQuery('#billing_state').val(d.region || '');
            jQuery('#billing_postcode').val(d.postal_code || '');
            if (d.country_iso_code) setFieldValue(getField('billing', 'country'), d.country_iso_code);
            if (d.flat) setFieldValue(getField('billing', 'address_2'), d.flat);
          } else {
            jQuery('#shipping_city').val(d.city || '');
            jQuery('#shipping_state').val(d.region || '');
            jQuery('#shipping_postcode').val(d.postal_code || '');
            if (d.country_iso_code) setFieldValue(getField('shipping', 'country'), d.country_iso_code);
            if (d.flat) setFieldValue(getField('shipping', 'address_2'), d.flat);
          }
        }
      });
        inited++;
      } catch (err) {
        log('Ошибка инициализации виджета для', id, err);
      }
    });
    if (inited > 0) log('Виджет подсказок инициализирован для', inited, 'полей');
  }

  function runCleanRequest(button, addressEl, prefix) {
    var address = (addressEl && addressEl.value) ? addressEl.value.trim() : '';
    if (!address) {
      alert((config.messages && config.messages.enterAddress) ? config.messages.enterAddress : 'Введите адрес в поле «Адрес».');
      return;
    }
    var label = button.textContent;
    button.disabled = true;
    button.textContent = (config.messages && config.messages.loading) ? config.messages.loading : 'Проверка…';

    var formData = new FormData();
    formData.append('action', 'dadata_clean_address');
    formData.append('nonce', nonce);
    formData.append('address', address);

    fetch(ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        button.disabled = false;
        button.textContent = label;
        if (res.success && res.data) {
          fillFields(res.data, prefix);
        } else {
          alert((res && res.error) ? res.error : 'Не удалось проверить адрес.');
        }
      })
      .catch(function () {
        button.disabled = false;
        button.textContent = label;
        alert((config.messages && config.messages.error) || 'Ошибка сети.');
      });
  }

  function initButtons() {
    if (!ajaxUrl || !nonce) return;
    var form = getForm();
    var buttons = form ? form.querySelectorAll('[data-dadata-check-address]') : [];
    var prefixes = ['billing', 'shipping'];
    prefixes.forEach(function (prefix) {
      var addressEl = getField(prefix, 'address_1');
      if (!addressEl) return;
      var btn = form ? form.querySelector('[data-dadata-check-address][data-address-prefix="' + prefix + '"]') : null;
      if (!btn) return;
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        runCleanRequest(btn, addressEl, prefix);
      });
    });
  }

  function init() {
    initSuggestionsWidget();
    initButtons();
  }

  function run() {
    log('run(), readyState:', document.readyState);
    var form = getForm();
    log('Форма найдена:', !!form, form ? form.className : '-');
    log('#billing_address_1 в DOM:', !!document.getElementById('billing_address_1'));
    log('input[name=billing_address_1] в DOM:', !!document.querySelector('input[name="billing_address_1"]'));

    init();
    setTimeout(function () {
      log('Повтор initSuggestionsWidget (300 мс)');
      initSuggestionsWidget();
    }, 300);
    setTimeout(function () {
      log('Повтор initSuggestionsWidget (800 мс)');
      initSuggestionsWidget();
    }, 800);
  }

  if (document.readyState === 'loading') {
    log('Ожидание DOMContentLoaded');
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }

  // WooCommerce: при обновлении чекаута заново инициализировать подсказки (как в плагине dadata-ru)
  if (typeof jQuery !== 'undefined') {
    jQuery(document.body).on('updated_checkout', function () {
      initSuggestionsWidget();
    });
  }
})();
