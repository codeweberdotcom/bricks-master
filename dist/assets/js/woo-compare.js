/**
 * CW Compare — модуль сравнения товаров.
 *
 * Логика:
 * - Кнопки .cw-compare-btn[data-product-id] на карточках и странице товара
 * - На single product: обновляет data-product-id при выборе вариации
 * - Нижний бар #cw-compare-bar с миниатюрами и кнопкой «Сравнить»
 * - AJAX: cw_compare_toggle, cw_compare_clear
 * - Переключатель «только различия» на странице сравнения
 */
(function () {
    'use strict';

    var cfg = window.cwCompare || {};
    if (!cfg.ajaxUrl) return;

    var bar   = document.getElementById('cw-compare-bar');
    var state = { ids: cfg.ids || [], limit: cfg.limit || 4 };

    /* ── Request queue (предотвращает race condition при быстрых кликах) ─────── */

    var queue = [];
    var queueRunning = false;

    function enqueue(fn) {
        queue.push(fn);
        if (!queueRunning) runQueue();
    }

    function runQueue() {
        if (!queue.length) { queueRunning = false; return; }
        queueRunning = true;
        queue.shift()(runQueue);
    }

    /* ── Инициализация ──────────────────────────────────────────────────────── */

    function init() {
        syncButtons(state.ids);
        bindEvents();
        initDiffOnly();
        initVariationSync();
    }

    /* ── Привязка событий ───────────────────────────────────────────────────── */

    function bindEvents() {
        // Клики по кнопке «добавить к сравнению» на карточке / single
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.cw-compare-btn[data-product-id]');
            if (!btn) return;

            // На странице сравнения — кнопки не переключают, только уводят на сравнение
            if (btn.classList.contains('cw-compare-btn--single') && window.location.href.indexOf(cfg.compareUrl) !== -1) {
                return;
            }

            e.preventDefault();
            toggleCompare(btn);
        });

        // Удаление из слота бара
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.cw-compare-slot-remove[data-product-id]');
            if (!btn) return;
            e.preventDefault();
            var slot = btn.closest('.cw-compare-slot');
            if (slot) slot.classList.add('cw-compare-slot--loading');
            removeFromCompare(parseInt(btn.dataset.productId, 10));
        });

        // Удаление с кнопки в таблице
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.cw-compare-remove-product[data-product-id]');
            if (!btn) return;
            e.preventDefault();
            removeFromCompare(parseInt(btn.dataset.productId, 10));
        });

        // Очистить всё
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.cw-compare-clear');
            if (!btn) return;
            e.preventDefault();
            btn.classList.add('cw-compare-clear--loading');
            btn.disabled = true;
            clearCompare();
        });
    }

    /* ── Toggle (add / remove) ──────────────────────────────────────────────── */

    function toggleCompare(btn) {
        var id = parseInt(btn.dataset.productId, 10);
        if (!id) return;

        setLoading(btn, true);

        enqueue(function (done) {
            postAjax('cw_compare_toggle', { product_id: id, current_ids: state.ids.join(',') }, function (res) {
                setLoading(btn, false);

                if (!res.success) {
                    if (res.data && res.data.limit_reached) {
                        showNotice(cfg.i18n.limitReached || 'Лимит достигнут');
                    }
                    done();
                    return;
                }

                var data = res.data;
                state.ids = data.ids || [];

                syncButtons(state.ids);
                updateBar(data.bar_html, data.count);
                done();
            });
        });
    }

    /* ── Remove (из бара или таблицы) ──────────────────────────────────────── */

    function removeFromCompare(id) {
        postAjax('cw_compare_toggle', { product_id: id }, function (res) {
            if (!res.success) return;

            var data = res.data;
            state.ids = data.ids || [];

            syncButtons(state.ids);
            updateBar(data.bar_html, data.count);

            // На странице сравнения — убрать колонку товара из таблицы
            var col = document.querySelector('.cw-compare-product-col[data-product-id="' + id + '"]');
            if (col) {
                // Убираем <th> в заголовке
                var colIdx = Array.from(col.parentNode.children).indexOf(col);
                if (colIdx > -1) {
                    // Заголовок
                    col.remove();
                    // Все <td> в той же колонке
                    document.querySelectorAll('.cw-compare-table tbody tr').forEach(function (row) {
                        var cells = row.querySelectorAll('td');
                        // colIdx - 1 (минус label-col)
                        var cellIdx = colIdx - 1;
                        if (cells[cellIdx]) cells[cellIdx].remove();
                    });
                }
            }

            // Если осталось 0 товаров — редирект в каталог
            if (state.ids.length === 0) {
                // Небольшая задержка для плавности
                setTimeout(function () {
                    window.location.href = cfg.compareUrl || '/';
                }, 400);
            }
        });
    }

    /* ── Clear all ──────────────────────────────────────────────────────────── */

    function clearCompare() {
        postAjax('cw_compare_clear', {}, function (res) {
            if (!res.success) return;

            state.ids = [];
            syncButtons([]);
            hideBar();

            // На странице сравнения — редирект в каталог
            if (document.querySelector('.cw-compare-page, .cw-compare-empty')) {
                var shopUrl = (typeof woocommerce_params !== 'undefined' && woocommerce_params.shop_url)
                    ? woocommerce_params.shop_url
                    : '/shop/';
                window.location.href = shopUrl;
            }
        });
    }

    /* ── Bar ────────────────────────────────────────────────────────────────── */

    function updateBar(barHtml, count) {
        if (!bar) return;

        // Заменяем inner контент
        if (barHtml !== undefined) {
            bar.innerHTML = barHtml;
        }

        if (count > 0) {
            showBar();
        } else {
            hideBar();
        }
    }

    function showBar() {
        if (!bar) return;
        bar.style.display = '';
        // Небольшая задержка чтобы display успел применяться до transition
        requestAnimationFrame(function () {
            bar.classList.add('is-visible');
        });
    }

    function hideBar() {
        if (!bar) return;
        bar.classList.remove('is-visible');
        bar.addEventListener('transitionend', function onEnd() {
            bar.removeEventListener('transitionend', onEnd);
            if (!bar.classList.contains('is-visible')) {
                bar.style.display = 'none';
            }
        }, { once: true });
    }

    /* ── Sync buttons ───────────────────────────────────────────────────────── */

    function syncButtons(ids) {
        document.querySelectorAll('.cw-compare-btn[data-product-id]').forEach(function (btn) {
            var id = parseInt(btn.dataset.productId, 10);
            var inList = ids.indexOf(id) !== -1;
            markButton(btn, inList);
        });
    }

    function markButton(btn, active) {
        var label = active ? (cfg.i18n.added || 'В сравнении') : (cfg.i18n.add || 'Добавить к сравнению');

        if (active) {
            btn.classList.add('cw-compare-btn--active');
        } else {
            btn.classList.remove('cw-compare-btn--active');
        }

        // Обновляем title/aria-label
        btn.setAttribute('title', label);
        btn.setAttribute('aria-label', label);

        // Обновляем текст label внутри кнопки (single product)
        var labelEl = btn.querySelector('.cw-compare-label');
        if (labelEl) labelEl.textContent = label;

        // Tooltip Bootstrap (если инициализирован)
        if (window.bootstrap && window.bootstrap.Tooltip) {
            var tt = window.bootstrap.Tooltip.getInstance(btn);
            if (tt) {
                // Обновить атрибут и переинициализировать подсказку
                btn.setAttribute('data-bs-original-title', label);
            }
        }
    }

    function setLoading(btn, loading) {
        if (loading) {
            btn.classList.add('cw-compare-btn--loading');
        } else {
            btn.classList.remove('cw-compare-btn--loading');
        }
    }

    /* ── Variation sync (single product page) ───────────────────────────────── */

    function initVariationSync() {
        // При выборе вариации: обновляем data-product-id кнопки на ID вариации
        document.querySelectorAll('.variations_form').forEach(function (form) {
            form.addEventListener('found_variation.wc-variation-form', function (e, variation) {
                if (!variation) return;
                var btn = document.querySelector('.cw-compare-btn--single');
                if (!btn) return;

                btn.dataset.productId = variation.variation_id;

                // Синхронизируем active-состояние
                var inList = state.ids.indexOf(variation.variation_id) !== -1;
                markButton(btn, inList);
            });

            form.addEventListener('reset_data', function () {
                var btn = document.querySelector('.cw-compare-btn--single');
                if (!btn) return;

                // Возвращаем parent ID
                var parentId = parseInt(form.dataset.product_id, 10);
                if (parentId) {
                    btn.dataset.productId = parentId;
                    var inList = state.ids.indexOf(parentId) !== -1;
                    markButton(btn, inList);
                }
            });
        });

        // jQuery fallback для WC variation events (если vanilla не ловит)
        if (window.jQuery) {
            jQuery(document.body).on('found_variation', function (e, variation) {
                if (!variation) return;
                var btn = document.querySelector('.cw-compare-btn--single');
                if (!btn) return;
                btn.dataset.productId = variation.variation_id;
                var inList = state.ids.indexOf(variation.variation_id) !== -1;
                markButton(btn, inList);
            });

            jQuery(document.body).on('reset_data', function () {
                var btn = document.querySelector('.cw-compare-btn--single');
                if (!btn) return;
                var form = document.querySelector('.variations_form');
                var parentId = form ? parseInt(form.dataset.product_id, 10) : 0;
                if (parentId) {
                    btn.dataset.productId = parentId;
                    var inList = state.ids.indexOf(parentId) !== -1;
                    markButton(btn, inList);
                }
            });
        }
    }

    /* ── «Только различия» (страница сравнения) ────────────────────────────── */

    function initDiffOnly() {
        var toggle = document.getElementById('cw-compare-diff-only');
        if (!toggle) return;

        toggle.addEventListener('change', function () {
            var rows = document.querySelectorAll('.cw-compare-row--same');
            rows.forEach(function (row) {
                row.style.display = toggle.checked ? 'none' : '';
            });
        });
    }

    /* ── Notice (простой alert, можно заменить на CWNotify) ────────────────── */

    function showNotice(message) {
        if (window.CWNotify) {
            CWNotify.show(message, { type: 'warning', event: 'compare' });
        } else {
            alert(message);
        }
    }

    /* ── AJAX helper ────────────────────────────────────────────────────────── */

    function postAjax(action, data, callback) {
        var body = new URLSearchParams();
        body.append('action', action);
        body.append('nonce', cfg.nonce);
        Object.keys(data).forEach(function (key) {
            body.append(key, data[key]);
        });

        fetch(cfg.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString(),
        })
            .then(function (r) { return r.json(); })
            .then(callback)
            .catch(function (err) { console.error('[CWCompare]', err); });
    }

    /* ── Start ──────────────────────────────────────────────────────────────── */

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
