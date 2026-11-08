(function () {
    'use strict';

    var each = function (list, fn) { Array.prototype.forEach.call(list, fn); };

    /* ---------- Chuyển cảnh giữa các trang ---------- */
    var curtain = document.querySelector('.hcmus-curtain');
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (curtain && !reduce && curtain.animate) {
        document.addEventListener('click', function (e) {
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return; }
            var a = e.target.closest ? e.target.closest('a[href]') : null;
            if (!a || a.target === '_blank' || a.hasAttribute('download') || a.closest('#wpadminbar')) { return; }
            if (/(^|\s)(remove|woocommerce-remove-coupon|showcoupon|showlogin|add_to_cart_button|ajax_add_to_cart|shipping-calculator-button)(\s|$)/.test(a.className || '')) { return; }
            var raw = a.getAttribute('href') || '';
            if (raw.charAt(0) === '#' || /^(mailto:|tel:|javascript:)/i.test(raw)) { return; }
            var url;
            try { url = new URL(a.href, window.location.href); } catch (err) { return; }
            if (url.origin !== window.location.origin) { return; }
            if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) { return; }
            if (/\/wp-admin\/|wp-login\.php/.test(url.pathname)) { return; }
            e.preventDefault();
            var done = false;
            var go = function () { if (!done) { done = true; window.location.href = a.href; } };
            var anim = curtain.animate(
                [{ transform: 'translateY(100%)' }, { transform: 'translateY(0)' }],
                { duration: 450, easing: 'cubic-bezier(.77,0,.18,1)', fill: 'forwards' }
            );
            anim.onfinish = go;
            setTimeout(go, 800);
        });
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                each(curtain.getAnimations(), function (x) { x.cancel(); });
            }
        });
    }

    /* ---------- Hiện dần khi cuộn ---------- */
    var reveals = document.querySelectorAll('.hcmus-reveal');
    if (reveals.length) {
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                each(entries, function (en) {
                    if (en.isIntersecting) {
                        var el = en.target;
                        el.classList.add('is-in');
                        io.unobserve(el);
                        setTimeout(function () {
                            el.classList.remove('hcmus-reveal', 'is-in');
                            el.style.transitionDelay = '';
                        }, 1000);
                    }
                });
            }, { threshold: 0.12 });
            each(reveals, function (el, i) {
                el.style.transitionDelay = ((i % 4) * 80) + 'ms';
                io.observe(el);
            });
        } else {
            each(reveals, function (el) { el.classList.add('is-in'); });
        }
    }

    /* ---------- Menu trượt ---------- */
    var menu = document.getElementById('hcmus-menu');
    var openBtn = document.querySelector('.hcmus-menu-btn');
    if (menu && openBtn) {
        var closeBtn = menu.querySelector('.hcmus-menu__close');
        var lists = menu.querySelectorAll('.hcmus-menu__list');
        var cars = menu.querySelectorAll('.hcmus-menu__car');
        var img = menu.querySelector('.hcmus-menu__img');
        var caption = menu.querySelector('.hcmus-menu__caption');
        var nameEl = menu.querySelector('.hcmus-menu__name');
        var priceEl = menu.querySelector('.hcmus-menu__price');
        var discover = menu.querySelector('.hcmus-menu__discover');

        var clearPreview = function () {
            img.classList.remove('is-visible');
            caption.classList.remove('is-visible');
            each(cars, function (c) { c.classList.remove('is-current'); });
        };
        var preview = function (car) {
            each(cars, function (c) { c.classList.toggle('is-current', c === car); });
            var src = car.getAttribute('data-img');
            if (src) {
                img.src = src;
                img.classList.add('is-visible');
            } else {
                img.classList.remove('is-visible');
            }
            nameEl.textContent = car.getAttribute('data-name') || '';
            priceEl.textContent = car.getAttribute('data-price') || '';
            discover.setAttribute('href', car.getAttribute('href'));
            caption.classList.add('is-visible');
        };
        var showLevel = function (level) {
            each(lists, function (l) { l.classList.toggle('is-active', l.getAttribute('data-level') === level); });
            if (level === 'models' && cars.length) { preview(cars[0]); } else { clearPreview(); }
        };
        var openMenu = function () {
            menu.classList.add('is-open');
            menu.setAttribute('aria-hidden', 'false');
            openBtn.setAttribute('aria-expanded', 'true');
            document.body.classList.add('hcmus-menu-open');
            showLevel('main');
            closeBtn.focus();
        };
        var closeMenu = function () {
            menu.classList.remove('is-open');
            menu.setAttribute('aria-hidden', 'true');
            openBtn.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('hcmus-menu-open');
        };

        openBtn.addEventListener('click', openMenu);
        closeBtn.addEventListener('click', function () { closeMenu(); openBtn.focus(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && menu.classList.contains('is-open')) { closeMenu(); openBtn.focus(); }
        });
        each(menu.querySelectorAll('[data-open]'), function (b) {
            b.addEventListener('click', function () { showLevel(b.getAttribute('data-open')); });
        });
        each(cars, function (c) {
            c.addEventListener('mouseenter', function () { preview(c); });
            c.addEventListener('focus', function () { preview(c); });
        });
        each(menu.querySelectorAll('a'), function (a) {
            a.addEventListener('click', closeMenu);
        });
    }

    /* ---------- Thanh tìm kiếm ---------- */
    var form = document.querySelector('.hcmus-filter__form');
    if (form) {
        form.addEventListener('submit', function () {
            each(form.querySelectorAll('input[name], select[name]'), function (f) {
                if (f.type !== 'hidden' && f.value === '') { f.disabled = true; }
            });
        });
    }

    /* ---------- Tab loại hình xe (trang Dòng xe) ---------- */
    var tabs = document.querySelectorAll('.hcmus-tabs .hcmus-tab');
    var groups = document.querySelectorAll('.hcmus-type');
    each(tabs, function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            var key = tab.getAttribute('data-filter');
            each(tabs, function (t) { t.classList.toggle('is-active', t === tab); });
            each(groups, function (g) {
                if (key === 'all' || g.getAttribute('data-cat') === key) {
                    g.removeAttribute('hidden');
                } else {
                    g.setAttribute('hidden', 'hidden');
                }
            });
        });
    });

    /* ---------- Thư viện ảnh xe ---------- */
    var mainImg = document.querySelector('.hcmus-gallery__img');
    each(document.querySelectorAll('.hcmus-gallery__thumb'), function (th) {
        th.addEventListener('click', function (e) {
            e.preventDefault();
            if (mainImg && th.getAttribute('data-full')) {
                mainImg.removeAttribute('srcset');
                mainImg.removeAttribute('sizes');
                mainImg.src = th.getAttribute('data-full');
            }
        });
    });

    /* ---------- Dự toán chi phí lăn bánh ---------- */
    var cost = document.querySelector('.hcmus-cost');
    if (cost) {
        var cfg = {};
        try { cfg = JSON.parse(cost.getAttribute('data-config')) || {}; } catch (err) { cfg = {}; }
        var q = function (sel) { return cost.querySelector(sel); };
        var carSel = q('[name="car"]');
        var priceIn = q('[name="price"]');
        var areaSel = q('[name="area"]');
        var typeSel = q('[name="type"]');
        var hullCb = q('[name="hull"]');
        var loanPct = q('[name="loan_pct"]');
        var loanYears = q('[name="loan_years"]');
        var loanRate = q('[name="loan_rate"]');

        var toInt = function (v) { return parseInt(String(v).replace(/\D/g, ''), 10) || 0; };
        var toFloat = function (v) { var n = parseFloat(String(v).replace(',', '.')); return isNaN(n) ? 0 : n; };
        var num = function (k) { var n = parseFloat(cfg[k]); return isNaN(n) ? 0 : n; };
        var grp = function (n) { return Math.round(n).toLocaleString('vi-VN'); };
        var money = function (n) { return grp(n) + ' ₫'; };
        var todayIso = function () {
            var d = new Date();
            var p = function (n) { return (n < 10 ? '0' : '') + n; };
            return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate());
        };
        var viDate = function (iso) { var a = String(iso).split('-'); return a.length === 3 ? a[2] + '/' + a[1] + '/' + a[0] : iso; };
        var out = function (key, text) {
            var el = q('[data-out="' + key + '"]');
            if (el) { el.textContent = text; }
        };

        var calc = function () {
            var price = toInt(priceIn.value);
            var area = areaSel.value;
            var type = typeSel.value;
            var rate = area === 'hn' ? num('tax_hn') : (area === 'hcm' ? num('tax_hcm') : num('tax_other'));
            var evNote = '';
            if (type === 'pickup') { rate = rate * num('pickup_pct') / 100; }
            if (type === 'ev') {
                var until = cfg.ev_until || '';
                if (until && todayIso() > until) {
                    rate = rate * num('ev_after_pct') / 100;
                    evNote = 'Ưu đãi lệ phí trước bạ xe điện đã hết hạn từ ' + viDate(until) + ', đang tính theo mức sau ưu đãi.';
                } else {
                    rate = num('tax_ev');
                    if (until) { evNote = 'Xe điện được ưu đãi lệ phí trước bạ đến hết ngày ' + viDate(until) + '. Sau ngày này mức thu có thể thay đổi.'; }
                }
            }
            var tax = price * rate / 100;
            var plates = { hn: num('plate_hn'), hcm: num('plate_hcm'), city: num('plate_city'), other: num('plate_other') };
            var plate = price > 0 ? plates[area] : 0;
            var inspection = price > 0 ? num('inspection') : 0;
            var road = price > 0 ? num('road_fee') : 0;
            var tnds = price > 0 ? num('tnds') : 0;
            var hull = hullCb.checked ? price * num('hull_pct') / 100 : 0;
            var total = price > 0 ? price + tax + plate + inspection + road + tnds + hull : 0;

            out('price', money(price));
            out('taxrate', price > 0 ? '(' + String(Math.round(rate * 100) / 100).replace('.', ',') + '%)' : '');
            out('tax', money(tax));
            out('plate', money(plate));
            out('inspection', money(inspection));
            out('road', money(road));
            out('tnds', money(tnds));
            out('hull', money(hull));
            var evEl = q('[data-out="evnote"]');
            if (evEl) { evEl.textContent = evNote; evEl.hidden = !evNote; }
            out('total', money(total));
            var hullRow = q('[data-row="hull"]');
            if (hullRow) { hullRow.style.display = hullCb.checked ? '' : 'none'; }

            var pct = Math.min(100, Math.max(0, toFloat(loanPct.value)));
            var years = Math.max(1, toFloat(loanYears.value));
            var yearly = Math.max(0, toFloat(loanRate.value));
            var loan = price * pct / 100;
            var n = years * 12;
            var r = yearly / 100 / 12;
            var monthly = 0;
            if (loan > 0) {
                monthly = r > 0 ? loan * r / (1 - Math.pow(1 + r, -n)) : loan / n;
            }
            out('loan', money(loan));
            out('monthly', money(monthly));
            out('interest', money(Math.max(0, monthly * n - loan)));
            out('upfront', money(Math.max(0, total - loan)));
        };

        carSel.addEventListener('change', function () {
            var opt = carSel.options[carSel.selectedIndex];
            var p = opt ? opt.getAttribute('data-price') : '';
            if (p) { priceIn.value = grp(parseInt(p, 10)); }
            calc();
        });
        priceIn.addEventListener('input', function () {
            var digits = toInt(priceIn.value);
            priceIn.value = digits ? grp(digits) : '';
            carSel.value = '';
            calc();
        });
        each([areaSel, typeSel, hullCb, loanPct, loanYears, loanRate], function (el) {
            el.addEventListener('input', calc);
            el.addEventListener('change', calc);
        });
        calc();
    }
})();
