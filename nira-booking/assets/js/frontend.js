/* ==========================================================================
 * Nira Booking — Frontend widget
 * Handles : date range picker, 2-month calendar, guest stepper,
 *           live quote, Stripe checkout modal, success state.
 * Requires : window.NiraBooking (localized), optional window.Stripe.
 * ========================================================================== */
(function () {
    'use strict';

    if (typeof window.NiraBooking === 'undefined') {
        return;
    }

    var CFG  = window.NiraBooking;
    var I18N = CFG.i18n || {};

    // ---------- Utils -------------------------------------------------------

    function qs(root, sel)  { return root.querySelector(sel); }
    function qsa(root, sel) { return Array.prototype.slice.call(root.querySelectorAll(sel)); }

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function toISO(d) {
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    function parseISO(s) {
        if (!s) return null;
        var p = s.split('-');
        return new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10));
    }

    function addDays(d, n) {
        var r = new Date(d.getTime());
        r.setDate(r.getDate() + n);
        return r;
    }

    function addMonths(d, n) {
        return new Date(d.getFullYear(), d.getMonth() + n, 1);
    }

    function daysBetween(a, b) {
        var ms = 1000 * 60 * 60 * 24;
        return Math.round((b.getTime() - a.getTime()) / ms);
    }

    function formatDateFR(d) {
        if (!d) return '';
        var months = I18N.monthsShort || ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
        return d.getDate() + ' ' + months[d.getMonth()].toLowerCase();
    }

    function formatMonthFR(d) {
        var months = I18N.months || ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        return months[d.getMonth()] + ' ' + d.getFullYear();
    }

    function money(amount, symbol) {
        if (isNaN(amount)) return '';
        var s = Math.round(amount * 100) / 100;
        var whole = Math.floor(s);
        var str = (s % 1 === 0)
            ? String(whole).replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
            : s.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        return str + ' ' + symbol;
    }

    // Fetches a fresh nonce from an uncached endpoint. On a page served from a
    // full-page cache (CDN / cache plugin), the nonce embedded in the HTML can be
    // expired, which makes every AJAX call fail with HTTP 403. A single in-flight
    // refresh is shared by all concurrent callers.
    var noncePromise = null;
    function refreshNonce() {
        if (!noncePromise) {
            var body = new URLSearchParams();
            body.append('action', 'nira_refresh_nonce');
            noncePromise = fetch(CFG.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString()
            }).then(function (r) { return r.json(); })
              .then(function (res) {
                  if (res && res.success && res.data && res.data.nonce) {
                      CFG.nonce = res.data.nonce;
                  }
                  return CFG.nonce;
              }).catch(function () { return CFG.nonce; })
              .then(function (n) { noncePromise = null; return n; });
        }
        return noncePromise;
    }

    function post(action, data, _retried) {
        var body = new URLSearchParams();
        body.append('action', action);
        body.append('nonce', CFG.nonce);
        Object.keys(data || {}).forEach(function (k) { body.append(k, data[k]); });
        return fetch(CFG.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        }).then(function (r) {
            // Stale nonce from a cached page → fetch a fresh one and retry once.
            if (r.status === 403 && !_retried) {
                return refreshNonce().then(function () { return post(action, data, true); });
            }
            return r.json();
        });
    }

    // ---------- Widget ------------------------------------------------------

    function NiraWidget(root) {
        this.root = root;
        this.slug = root.dataset.slug;
        this.propertyId = parseInt(root.dataset.propertyId, 10) || 0;
        this.months = parseInt(root.dataset.months, 10) || 2;
        this.capacity = parseInt(root.dataset.capacity, 10) || 2;
        this.minNights = parseInt(root.dataset.minNights, 10) || 1;
        this.basePrice = parseFloat(root.dataset.basePrice) || 0;
        this.depositPct = parseInt(root.dataset.depositPct, 10) || 30;
        this.currencySymbol = root.dataset.currencySymbol || '€';
        this.chargeMode = CFG.chargeMode || 'deposit';

        this.checkIn = null;
        this.checkOut = null;
        this.guests = 1;
        this.unavailable = {};        // { 'YYYY-MM-DD': true }
        this.minStayByDate = {};      // { 'YYYY-MM-DD': n }
        this.priceByDate = {};        // { 'YYYY-MM-DD': 120 }
        this.activeField = 'checkin'; // 'checkin' | 'checkout'
        this.viewMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
        this.quote = null;
        this.booking = null;

        this.els = {
            amount      : qs(root, '.nira-bc-amount'),
            pricePrefix : qs(root, '.nira-bc-prefix'),
            datePicker  : qs(root, '.nira-date-picker'),
            checkinField: qs(root, '.nira-date-field[data-field="checkin"]'),
            checkoutField: qs(root, '.nira-date-field[data-field="checkout"]'),
            guestMinus  : qs(root, '.nira-step[data-step="-1"]'),
            guestPlus   : qs(root, '.nira-step[data-step="1"]'),
            guestValue  : qs(root, '.nira-guest-value'),
            reserveBtn  : qs(root, '.nira-booking-card > .nira-btn-primary'),
            breakdown   : qs(root, '.nira-price-breakdown'),
            calPopover  : qs(root, '.nira-calendar-popover'),
            calTitle    : qs(root, '.nira-cal-title'),
            calMonths   : qs(root, '.nira-cal-months'),
            calPrev     : qs(root, '.nira-cal-nav[data-dir="-1"]'),
            calNext     : qs(root, '.nira-cal-nav[data-dir="1"]'),
            calClear    : qs(root, '.nira-cal-clear'),
            calClose    : qs(root, '.nira-cal-close'),
            calBackdrop : qs(root, '.nira-cal-backdrop'),
            modal       : qs(root, '.nira-modal'),
            modalClose  : qs(root, '.nira-modal-close'),
            modalBackdrop: qs(root, '.nira-modal-backdrop'),
            modalSummary: qs(root, '.nira-modal-summary'),
            checkoutForm: qs(root, '.nira-checkout-form'),
            stripeMount : qs(root, '.nira-stripe-mount'),
            stripeError : qs(root, '.nira-stripe-error'),
            payBtn      : qs(root, '.nira-pay-btn'),
            payLabel    : qs(root, '.nira-pay-label'),
            paySpinner  : qs(root, '.nira-pay-spinner'),
            successBox  : qs(root, '.nira-success'),
            successRef  : qs(root, '.nira-success-ref')
        };

        // Move overlay elements to <body> so no transformed/filtered ancestor in
        // the host theme can break their `position: fixed`. WordPress themes
        // frequently put transforms or will-change on page wrappers, which turns
        // any `position: fixed` descendant into a position relative to that
        // ancestor instead of the viewport. Re-parenting to <body> avoids that.
        this.relocateOverlays();

        this.bind();
        this.loadCalendar();
    }

    NiraWidget.prototype.relocateOverlays = function () {
        var body = document.body;
        if (!body) return;
        var nodes = [this.els.calBackdrop, this.els.calPopover, this.els.modal];
        for (var i = 0; i < nodes.length; i++) {
            var n = nodes[i];
            if (n && n.parentNode !== body) body.appendChild(n);
        }
    };

    NiraWidget.prototype.bind = function () {
        var self = this;
        // Open calendar when clicking date picker
        this.els.datePicker.addEventListener('click', function (e) {
            var field = e.target.closest('.nira-date-field');
            self.openCalendar(field ? field.dataset.field : 'checkin');
        });
        this.els.datePicker.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); self.openCalendar('checkin'); }
        });

        // Calendar nav
        this.els.calPrev.addEventListener('click', function () { self.navMonth(-1); });
        this.els.calNext.addEventListener('click', function () { self.navMonth(1); });
        this.els.calClear.addEventListener('click', function () { self.clearDates(); });
        if (this.els.calClose) {
            this.els.calClose.addEventListener('click', function (e) {
                e.stopPropagation();
                self.closeCalendar();
            });
        }
        if (this.els.calBackdrop) {
            this.els.calBackdrop.addEventListener('click', function () { self.closeCalendar(); });
        }
        // Esc key closes the calendar (in addition to the existing modal Esc handler)
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && self.isCalOpen()) self.closeCalendar();
        });

        // Outside click closes calendar.
        // Note 1: we use composedPath() because clicking a day button triggers
        //   renderCalendar() which removes that button from the DOM. By the time
        //   the click bubbles here, e.target is detached, so contains(e.target)
        //   would wrongly return false. composedPath() captures the path BEFORE
        //   any DOM mutation happened during the click handlers.
        // Note 2: the popover and the date-picker live in different DOM subtrees
        //   (the popover is re-parented to <body> by relocateOverlays). We
        //   accept clicks inside either: the date picker (which opens it) or
        //   the popover itself.
        document.addEventListener('click', function (e) {
            if (!self.isCalOpen()) return;
            var insideTrigger = self.els.datePicker;
            var insidePopover = self.els.calPopover;
            var path = (typeof e.composedPath === 'function') ? e.composedPath() : null;
            if (path && path.length) {
                for (var i = 0; i < path.length; i++) {
                    if (path[i] === insideTrigger || path[i] === insidePopover) return;
                }
            } else {
                if (insideTrigger && insideTrigger.contains(e.target)) return;
                if (insidePopover && insidePopover.contains(e.target)) return;
            }
            // Backdrop click is allowed to close (handled separately, but
            // would also fall through here — closeCalendar() is idempotent).
            self.closeCalendar();
        });

        // Guests
        this.els.guestMinus.addEventListener('click', function () { self.setGuests(self.guests - 1); });
        this.els.guestPlus.addEventListener('click',  function () { self.setGuests(self.guests + 1); });

        // Reserve
        this.els.reserveBtn.addEventListener('click', function () { self.openCheckout(); });

        // Modal
        this.els.modalClose.addEventListener('click', function () { self.closeCheckout(); });
        this.els.modalBackdrop.addEventListener('click', function () { self.closeCheckout(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && self.els.modal && !self.els.modal.hidden) self.closeCheckout();
        });

        // Checkout submit
        this.els.checkoutForm.addEventListener('submit', function (e) {
            e.preventDefault();
            self.submitCheckout();
        });

        // Auto-mount Stripe dès que nom + e-mail sont saisis (sinon pas de carte
        // à remplir avant le clic « Payer »).
        var onCheckoutFieldChange = function () {
            if (self.booking || self._stripeElement) return; // déjà monté
            var fd = new FormData(self.els.checkoutForm);
            var name  = (fd.get('guest_name') || '').toString().trim();
            var email = (fd.get('guest_email') || '').toString().trim();
            if (name && email && /.+@.+\..+/.test(email)) {
                self.createHold();
            }
        };
        ['guest_name', 'guest_email'].forEach(function (field) {
            var el = self.els.checkoutForm.querySelector('[name="' + field + '"]');
            if (el) {
                el.addEventListener('blur',  onCheckoutFieldChange);
                el.addEventListener('input', onCheckoutFieldChange);
            }
        });
    };

    // ---------- Calendar data -----------------------------------------------

    NiraWidget.prototype.loadCalendar = function () {
        var self = this;
        var month = toISO(this.viewMonth).slice(0, 7);
        return post('nira_get_calendar', {
            slug: this.slug,
            month: month,
            months: this.months + 1
        }).then(function (res) {
            if (res && res.success && res.data) {
                (res.data.months || []).forEach(function (m) {
                    (m.days || []).forEach(function (d) {
                        if (!d.available) self.unavailable[d.date] = true;
                        if (d.minNights)  self.minStayByDate[d.date] = d.minNights;
                        if (d.price != null) self.priceByDate[d.date] = parseFloat(d.price);
                    });
                });
            }
        }).catch(function () {})
        // Always re-render, success or not, so the view never stays stale.
        .then(function () { self.renderCalendar(); });
    };

    // ---------- Calendar UI -------------------------------------------------

    NiraWidget.prototype.isCalOpen = function () {
        return !this.els.calPopover.hidden;
    };

    NiraWidget.prototype.openCalendar = function (field) {
        this.activeField = field || 'checkin';
        this.els.datePicker.classList.add('is-open');
        this.els.checkinField.classList.toggle('is-active', this.activeField === 'checkin');
        this.els.checkoutField.classList.toggle('is-active', this.activeField === 'checkout');
        if (this.els.calBackdrop) this.els.calBackdrop.hidden = false;
        this.els.calPopover.hidden = false;
        document.body.style.overflow = 'hidden';
        this.renderCalendar();
    };

    NiraWidget.prototype.closeCalendar = function () {
        if (this.els.calBackdrop) this.els.calBackdrop.hidden = true;
        this.els.calPopover.hidden = true;
        this.els.datePicker.classList.remove('is-open');
        this.els.checkinField.classList.remove('is-active');
        this.els.checkoutField.classList.remove('is-active');
        // Ne libérer le scroll que si aucun modal checkout n'est ouvert
        if (!this.els.modal || this.els.modal.hidden) {
            document.body.style.overflow = '';
        }
    };

    NiraWidget.prototype.navMonth = function (dir) {
        this.viewMonth = addMonths(this.viewMonth, dir);
        // Switch the month immediately — the grid is built client-side and must
        // never depend on the availability request succeeding (a stale nonce on a
        // cached page or any network hiccup would otherwise leave the arrows dead).
        this.renderCalendar();
        // Then refresh availability in the background; it re-renders when it lands.
        this.loadCalendar();
    };

    NiraWidget.prototype.clearDates = function () {
        this.checkIn = null;
        this.checkOut = null;
        this.quote = null;
        this.activeField = 'checkin';
        this.renderDates();
        this.renderBreakdown();
        this.renderCalendar();
    };

    NiraWidget.prototype.renderCalendar = function () {
        var self = this;
        var wdLabels = I18N.weekdays || ['L','M','M','J','V','S','D'];
        this.els.calTitle.textContent = '';
        this.els.calMonths.innerHTML = '';

        var today = new Date(); today.setHours(0, 0, 0, 0);

        for (var m = 0; m < this.months; m++) {
            var monthDate = addMonths(this.viewMonth, m);
            var monthEl = document.createElement('div');
            monthEl.className = 'nira-cal-month';

            var h = document.createElement('h4');
            h.textContent = formatMonthFR(monthDate);
            monthEl.appendChild(h);

            var grid = document.createElement('div');
            grid.className = 'nira-cal-grid';

            wdLabels.forEach(function (lbl) {
                var d = document.createElement('div');
                d.className = 'nira-cal-dow';
                d.textContent = lbl;
                grid.appendChild(d);
            });

            // Offset before 1st (Mon=0 .. Sun=6)
            var first = new Date(monthDate.getFullYear(), monthDate.getMonth(), 1);
            var startOffset = (first.getDay() + 6) % 7;
            for (var i = 0; i < startOffset; i++) {
                var empty = document.createElement('span');
                empty.className = 'nira-cal-day is-empty';
                grid.appendChild(empty);
            }

            var lastDay = new Date(monthDate.getFullYear(), monthDate.getMonth() + 1, 0).getDate();
            for (var day = 1; day <= lastDay; day++) {
                var date = new Date(monthDate.getFullYear(), monthDate.getMonth(), day);
                var iso = toISO(date);
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'nira-cal-day';
                btn.dataset.date = iso;

                var dayNum = document.createElement('span');
                dayNum.className = 'nira-cal-day__num';
                dayNum.textContent = day;
                btn.appendChild(dayNum);

                // Prix par nuit sous le numéro du jour (sauf dates passées/indispo)
                var price = this.priceByDate[iso];
                if (price != null && price > 0) {
                    var priceEl = document.createElement('span');
                    priceEl.className = 'nira-cal-day__price';
                    priceEl.textContent = Math.round(price) + this.currencySymbol;
                    btn.appendChild(priceEl);
                }

                var isPast = date < today;
                var isUnavail = !!this.unavailable[iso];

                if (isPast) btn.classList.add('is-past');
                if (!isPast && isUnavail) btn.classList.add('is-unavailable');
                if (isPast || isUnavail) btn.disabled = true;

                // Range highlight
                if (this.checkIn && this.checkOut) {
                    if (iso === toISO(this.checkIn))  btn.classList.add('is-selected', 'is-range-start');
                    if (iso === toISO(this.checkOut)) btn.classList.add('is-selected', 'is-range-end');
                    if (date > this.checkIn && date < this.checkOut) btn.classList.add('is-in-range');
                } else if (this.checkIn && iso === toISO(this.checkIn)) {
                    btn.classList.add('is-selected', 'is-range-start');
                }

                btn.addEventListener('click', function (e) {
                    // Stop the bubble: renderCalendar() will detach this button,
                    // and the document-level "outside click" handler would then
                    // mistakenly close the calendar.
                    e.stopPropagation();
                    self.onDayClick(e.currentTarget.dataset.date);
                });

                grid.appendChild(btn);
            }

            monthEl.appendChild(grid);
            this.els.calMonths.appendChild(monthEl);
        }

        // Disable prev if at/before current month
        var nowMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        this.els.calPrev.disabled = this.viewMonth <= nowMonth;
    };

    NiraWidget.prototype.onDayClick = function (iso) {
        var date = parseISO(iso);
        if (this.activeField === 'checkin' || !this.checkIn || (this.checkIn && this.checkOut)) {
            this.checkIn = date;
            this.checkOut = null;
            this.activeField = 'checkout';
            this.els.checkinField.classList.remove('is-active');
            this.els.checkoutField.classList.add('is-active');
        } else {
            if (date <= this.checkIn) {
                this.checkIn = date;
                this.checkOut = null;
            } else {
                // Reject range if any day between is unavailable
                if (!this.isRangeFree(this.checkIn, date)) {
                    this.showInlineError(I18N.unavailable || 'Ces dates ne sont pas disponibles.');
                    return;
                }
                this.checkOut = date;
            }
        }
        this.renderDates();
        this.renderCalendar();
        if (this.checkIn && this.checkOut) {
            this.fetchQuote();
            // Once both dates are picked, close the popover after a short
            // delay so the user sees the range highlighted before it disappears.
            var self = this;
            setTimeout(function () { self.closeCalendar(); }, 280);
        }
    };

    NiraWidget.prototype.isRangeFree = function (a, b) {
        var cursor = new Date(a.getTime());
        cursor.setDate(cursor.getDate() + 1); // check-in night itself is fine
        while (cursor < b) {
            if (this.unavailable[toISO(cursor)]) return false;
            cursor.setDate(cursor.getDate() + 1);
        }
        return true;
    };

    NiraWidget.prototype.renderDates = function () {
        this.els.checkinField.classList.toggle('has-value', !!this.checkIn);
        this.els.checkoutField.classList.toggle('has-value', !!this.checkOut);
        qs(this.els.checkinField,  '.nira-date-value').textContent =
            this.checkIn ? formatDateFR(this.checkIn) : (I18N.selectCheckIn || 'Ajouter une date');
        qs(this.els.checkoutField, '.nira-date-value').textContent =
            this.checkOut ? formatDateFR(this.checkOut) : (I18N.selectCheckOut || 'Ajouter une date');
    };

    // ---------- Guests ------------------------------------------------------

    NiraWidget.prototype.setGuests = function (n) {
        n = Math.max(1, Math.min(this.capacity, n));
        this.guests = n;
        this.els.guestValue.textContent = n;
        this.els.guestMinus.disabled = n <= 1;
        this.els.guestPlus.disabled  = n >= this.capacity;
        if (this.checkIn && this.checkOut) this.fetchQuote();
    };

    // ---------- Quote -------------------------------------------------------

    NiraWidget.prototype.fetchQuote = function () {
        var self = this;
        if (!this.checkIn || !this.checkOut) return;
        var nights = daysBetween(this.checkIn, this.checkOut);
        if (nights < this.minNights) {
            this.quote = null;
            this.renderBreakdown(
                (I18N.minStay || 'Séjour minimum de %d nuits').replace('%d', this.minNights)
            );
            return;
        }
        this.setReserveLoading(true);
        post('nira_get_quote', {
            slug: this.slug,
            check_in: toISO(this.checkIn),
            check_out: toISO(this.checkOut),
            guests: this.guests
        }).then(function (res) {
            self.setReserveLoading(false);
            if (!res || !res.success) {
                self.quote = null;
                self.renderBreakdown((res && res.data && res.data.message) || (I18N.unavailable || 'Indisponible'));
                return;
            }
            self.quote = res.data;
            self.renderBreakdown();
            self.updateHeaderPrice();
        }).catch(function () {
            self.setReserveLoading(false);
            self.quote = null;
            self.renderBreakdown('Erreur réseau.');
        });
    };

    /**
     * Met à jour le prix affiché dans le header de la carte
     * (".nira-bc-amount") avec le tarif moyen des dates choisies.
     * Appelé après chaque devis valide.
     */
    NiraWidget.prototype.updateHeaderPrice = function () {
        if (!this.quote || !this.els.amount) return;
        var per = this.quote.avg_nightly || this.quote.subtotal_raw / Math.max(1, this.quote.nights);
        if (!per || isNaN(per)) return;
        // Format identique au rendu serveur
        this.els.amount.innerHTML = this.formatPriceShort(per);
        if (this.els.pricePrefix) this.els.pricePrefix.hidden = true;
    };

    NiraWidget.prototype.formatPriceShort = function (n) {
        var rounded = Math.round(n);
        var withSpaces = rounded.toLocaleString('fr-FR');
        return withSpaces + '&nbsp;' + (this.currencySymbol || '€');
    };

    NiraWidget.prototype.renderBreakdown = function (errorMsg) {
        var bd = this.els.breakdown;
        if (!bd) return;
        if (errorMsg) {
            bd.hidden = false;
            bd.innerHTML = '<div class="nira-error">' + errorMsg + '</div>';
            this.setReserveLabel(I18N.reserve || 'Réserver', true);
            return;
        }
        if (!this.quote) {
            bd.hidden = true;
            this.setReserveLabel(I18N.selectCheckIn ? 'Sélectionnez vos dates' : 'Sélectionnez vos dates', true);
            return;
        }
        var q = this.quote;
        var sym = this.currencySymbol;
        var subLabel = money(q.avg_nightly || (q.subtotal_raw / Math.max(1, q.nights)), sym) + ' × ' +
                       q.nights + ' ' + (q.nights > 1 ? (I18N.nights || 'nuits') : (I18N.night || 'nuit'));
        var subRowValue = q.subtotal_raw || q.base || (q.subtotal + (q.discount || 0));
        bd.hidden = false;
        bd.innerHTML =
            '<div class="nira-row nira-sub"><span class="nira-row-label">' + subLabel + '</span>' +
                '<span class="nira-row-value">' + money(subRowValue, sym) + '</span></div>' +
            (q.discount > 0
                ? ('<div class="nira-row nira-discount-row" style="color:#A41C2B;"><span>' +
                   (q.discount_type === 'monthly' ? 'Remise au mois' : 'Remise à la semaine') +
                   ' (−' + q.discount_pct + ' %)</span><span>− ' + money(q.discount, sym) + '</span></div>')
                : '') +
            (q.cleaning_fee ? ('<div class="nira-row nira-cleaning-row"><span>Frais de ménage</span><span>' + money(q.cleaning_fee, sym) + '</span></div>') : '') +
            (q.tourist_tax ? ('<div class="nira-row nira-taxes-row"><span>Taxe de séjour</span><span>' + money(q.tourist_tax, sym) + '</span></div>') : '') +
            ((q.taxes - (q.tourist_tax || 0)) > 0.001 ? ('<div class="nira-row nira-taxes-row"><span>TVA</span><span>' + money(q.taxes - (q.tourist_tax || 0), sym) + '</span></div>') : '') +
            '<div class="nira-row nira-total-row"><span>' + (I18N.total || 'Total') + '</span><span>' + money(q.total, sym) + '</span></div>' +
            (this.chargeMode === 'deposit' && q.deposit
                ? ('<div class="nira-row nira-deposit-row"><span>Acompte à verser</span><span>' + money(q.deposit, sym) + '</span></div>')
                : '');

        this.setReserveLabel(I18N.reserve || 'Réserver', false);
    };

    NiraWidget.prototype.setReserveLabel = function (label, disabled) {
        this.els.reserveBtn.textContent = label;
        this.els.reserveBtn.disabled = !!disabled;
    };

    NiraWidget.prototype.setReserveLoading = function (loading) {
        if (loading) {
            this.els.reserveBtn.disabled = true;
            this.els.reserveBtn.textContent = I18N.loading || 'Chargement…';
        }
    };

    NiraWidget.prototype.showInlineError = function (msg) {
        var bd = this.els.breakdown;
        bd.hidden = false;
        bd.innerHTML = '<div class="nira-error">' + msg + '</div>';
    };

    // ---------- Checkout modal ----------------------------------------------

    NiraWidget.prototype.openCheckout = function () {
        if (!this.quote) return;
        this.renderModalSummary();
        this.els.modal.hidden = false;
        document.body.style.overflow = 'hidden';
        this.createHoldAndStripe();
    };

    NiraWidget.prototype.closeCheckout = function () {
        this.els.modal.hidden = true;
        // Ne libérer le scroll que si le calendrier n'est pas ouvert
        if (!this.isCalOpen()) {
            document.body.style.overflow = '';
        }
        if (this._stripeElement) { try { this._stripeElement.unmount(); } catch(e){} this._stripeElement = null; }
        this.booking = null;
        this.els.checkoutForm.hidden = false;
        this.els.successBox.hidden = true;
    };

    NiraWidget.prototype.renderModalSummary = function () {
        var q = this.quote, sym = this.currencySymbol;
        var html = '';
        html += '<div class="nira-modal-summary-row"><span>Dates</span><strong>' +
                formatDateFR(this.checkIn) + ' → ' + formatDateFR(this.checkOut) + '</strong></div>';
        html += '<div class="nira-modal-summary-row"><span>Voyageurs</span><strong>' + this.guests + '</strong></div>';
        html += '<div class="nira-modal-summary-row"><span>' + q.nights + ' ' + (q.nights > 1 ? 'nuits' : 'nuit') + '</span><span>' + money(q.subtotal_raw, sym) + '</span></div>';
        if (q.discount > 0) html += '<div class="nira-modal-summary-row"><span>' + (q.discount_type === 'monthly' ? 'Remise au mois' : 'Remise à la semaine') + ' (−' + q.discount_pct + ' %)</span><span>− ' + money(q.discount, sym) + '</span></div>';
        if (q.cleaning_fee) html += '<div class="nira-modal-summary-row"><span>Ménage</span><span>' + money(q.cleaning_fee, sym) + '</span></div>';
        if (q.tourist_tax)  html += '<div class="nira-modal-summary-row"><span>Taxe de séjour</span><span>' + money(q.tourist_tax, sym) + '</span></div>';
        if ((q.taxes - (q.tourist_tax || 0)) > 0.001) html += '<div class="nira-modal-summary-row"><span>TVA</span><span>' + money(q.taxes - (q.tourist_tax || 0), sym) + '</span></div>';
        html += '<div class="nira-modal-summary-row nira-modal-summary-total"><span>Total</span><span>' + money(q.total, sym) + '</span></div>';
        if (this.chargeMode === 'deposit' && q.deposit) {
            html += '<div class="nira-modal-summary-row" style="color:var(--nb-bordeaux);font-weight:600"><span>Acompte aujourd\'hui</span><span>' + money(q.deposit, sym) + '</span></div>';
        }
        this.els.modalSummary.innerHTML = html;
    };

    NiraWidget.prototype.createHoldAndStripe = function () {
        var self = this;
        // Minimal guest info snapshot; real details collected on submit.
        var form = this.els.checkoutForm;
        var fd = new FormData(form);
        var name = (fd.get('guest_name') || '').toString().trim();
        var email = (fd.get('guest_email') || '').toString().trim();

        if (!name || !email) {
            // Defer hold creation until user fills required fields in-form;
            // we still mount Stripe now to keep UX responsive.
            this.mountStripePlaceholder();
            return;
        }
        this.createHold();
    };

    NiraWidget.prototype.mountStripePlaceholder = function () {
        if (!window.Stripe || !CFG.stripeKey) {
            this.els.stripeMount.innerHTML =
                '<em style="color:var(--nb-muted);font-size:13px">Paiement sécurisé Stripe — les informations apparaîtront après saisie de vos coordonnées.</em>';
        }
    };

    NiraWidget.prototype.createHold = function () {
        var self = this;
        var fd = new FormData(this.els.checkoutForm);
        this.setPayLoading(true);

        return post('nira_create_hold', {
            property_id: this.propertyId,
            check_in: toISO(this.checkIn),
            check_out: toISO(this.checkOut),
            guest_name: (fd.get('guest_name') || '').toString(),
            guest_email: (fd.get('guest_email') || '').toString(),
            guest_phone: (fd.get('guest_phone') || '').toString(),
            guest_count: this.guests,
            notes: (fd.get('notes') || '').toString()
        }).then(function (res) {
            if (!res || !res.success) {
                self.setPayLoading(false);
                self.showStripeError((res && res.data && res.data.message) || 'Erreur serveur.');
                return null;
            }
            self.booking = res.data;
            self.setPayLoading(false);
            return self.initStripe(res.data.client_secret);
        }).catch(function (err) {
            self.setPayLoading(false);
            self.showStripeError('Erreur réseau.');
            return null;
        });
    };

    NiraWidget.prototype.initStripe = function (clientSecret) {
        if (!window.Stripe || !CFG.stripeKey) {
            this.showStripeError('Stripe n\'est pas configuré.');
            return null;
        }
        if (!this._stripe) this._stripe = window.Stripe(CFG.stripeKey, { locale: 'fr' });
        var self = this;
        var elements = this._stripe.elements({
            clientSecret: clientSecret,
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#A41C2B',
                    colorText: '#2D2D2D',
                    fontFamily: 'Inter, system-ui, sans-serif',
                    borderRadius: '10px'
                }
            }
        });
        this._stripeElements = elements;
        var payEl = elements.create('payment', { layout: { type: 'tabs', defaultCollapsed: false } });
        this.els.stripeMount.innerHTML = '';
        payEl.mount(this.els.stripeMount);
        payEl.on('focus', function () { self.els.stripeMount.classList.add('is-focused'); });
        payEl.on('blur',  function () { self.els.stripeMount.classList.remove('is-focused'); });
        this._stripeElement = payEl;
        this.hideStripeError();
        return payEl;
    };

    NiraWidget.prototype.submitCheckout = function () {
        var self = this;
        this.hideStripeError();

        // Validate required
        var fd = new FormData(this.els.checkoutForm);
        if (!fd.get('guest_name') || !fd.get('guest_email')) {
            this.showStripeError('Merci de renseigner votre nom et votre e-mail.');
            return;
        }

        // If no hold yet, create it then continue
        var ready = this.booking
            ? Promise.resolve(this._stripeElement)
            : this.createHold();

        Promise.resolve(ready).then(function (el) {
            if (!el || !self._stripe) return; // error already shown
            self.setPayLoading(true);
            return self._stripe.confirmPayment({
                elements: self._stripeElements,
                confirmParams: {
                    return_url: window.location.href,
                    payment_method_data: {
                        billing_details: {
                            name: fd.get('guest_name'),
                            email: fd.get('guest_email'),
                            phone: fd.get('guest_phone') || undefined
                        }
                    }
                },
                redirect: 'if_required'
            }).then(function (result) {
                self.setPayLoading(false);
                if (result.error) {
                    self.showStripeError(result.error.message || (I18N.paymentError || 'Erreur de paiement.'));
                    return;
                }
                if (result.paymentIntent && (result.paymentIntent.status === 'succeeded' || result.paymentIntent.status === 'processing')) {
                    // Confirmation serveur : ne PAS dépendre du seul webhook
                    // Stripe, sinon le hold expire et la réservation payée
                    // disparaît de l'admin si le webhook est mal configuré.
                    if (self.booking && result.paymentIntent.status === 'succeeded') {
                        post('nira_confirm_payment', {
                            booking_id: self.booking.booking_id,
                            payment_intent: result.paymentIntent.id
                        }).catch(function () { /* le webhook reste le second filet */ });
                    }
                    self.renderSuccess();
                }
            });
        }).catch(function () {
            self.setPayLoading(false);
            self.showStripeError(I18N.paymentError || 'Erreur de paiement.');
        });
    };

    NiraWidget.prototype.renderSuccess = function () {
        this.els.checkoutForm.hidden = true;
        this.els.successBox.hidden = false;
        if (this.booking && this.booking.reference) {
            this.els.successRef.textContent = 'Référence : ' + this.booking.reference;
        }
        // Refresh calendar in background
        this.loadCalendar();
    };

    NiraWidget.prototype.setPayLoading = function (loading) {
        this.els.payBtn.disabled = !!loading;
        this.els.payBtn.classList.toggle('is-loading', !!loading);
        if (this.els.paySpinner) this.els.paySpinner.hidden = !loading;
        if (this.els.payLabel) this.els.payLabel.style.opacity = loading ? '0.7' : '1';
    };

    NiraWidget.prototype.showStripeError = function (msg) {
        this.els.stripeError.hidden = false;
        this.els.stripeError.textContent = msg;
    };

    NiraWidget.prototype.hideStripeError = function () {
        this.els.stripeError.hidden = true;
        this.els.stripeError.textContent = '';
    };

    // ---------- Boot --------------------------------------------------------

    function init() {
        qsa(document, '.nira-booking-wrap').forEach(function (root) {
            if (root.__niraInit) return;
            root.__niraInit = true;
            new NiraWidget(root);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
