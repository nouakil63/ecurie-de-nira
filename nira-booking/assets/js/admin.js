/* Nira Booking – Admin helpers.
 * Minor UX glue for the server-rendered admin (pricing form toggle,
 * delete confirmations, copy-to-clipboard for iCal export URLs).
 */
(function ($) {
    'use strict';

    // --- Confirm dangerous actions -------------------------------------------
    $(document).on('submit', 'form[data-confirm]', function (e) {
        var msg = $(this).data('confirm') || 'Confirmer cette action ?';
        if (!window.confirm(msg)) e.preventDefault();
    });

    // --- Copy-to-clipboard helper --------------------------------------------
    $(document).on('click', '[data-copy]', function () {
        var btn = this, text = btn.getAttribute('data-copy') || btn.textContent;
        if (!navigator.clipboard) return;
        navigator.clipboard.writeText(text).then(function () {
            var orig = btn.textContent;
            btn.textContent = 'Copié !';
            setTimeout(function () { btn.textContent = orig; }, 1400);
        });
    });

    // --- Pricing rule type toggle (defensive, template has inline fallback) --
    var $ruleType = $('#nira-rule-type');
    if ($ruleType.length) {
        var $season  = $('.nira-rule-season');
        var $weekday = $('.nira-rule-weekday');
        function sync() {
            if ($ruleType.val() === 'season') { $season.show(); $weekday.hide(); }
            else { $season.hide(); $weekday.show(); }
        }
        $ruleType.on('change', sync); sync();
    }

    // --- Auto-grow slug from name on property-edit ---------------------------
    var $name = $('input[name="name"]');
    var $slug = $('input[name="slug"]');
    if ($name.length && $slug.length && !$slug.val()) {
        $name.on('blur', function () {
            if (!$slug.val()) {
                $slug.val(
                    ($name.val() || '')
                        .toLowerCase()
                        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '')
                );
            }
        });
    }

    // --- Sticky "save" reminder when settings form has changes ---------------
    var $settingsForm = $('form.nira-card:has(input[name="nira_action"][value="save_settings"])');
    if ($settingsForm.length) {
        var dirty = false;
        $settingsForm.on('change input', ':input', function () { dirty = true; });
        $settingsForm.on('submit', function () { dirty = false; });
        window.addEventListener('beforeunload', function (e) {
            if (dirty) { e.preventDefault(); e.returnValue = ''; }
        });
    }

    // --- Quick status filter chips on bookings page --------------------------
    $('.nira-status-chips [data-status]').on('click', function (e) {
        e.preventDefault();
        var status = $(this).data('status');
        var url = new URL(window.location.href);
        if (status) url.searchParams.set('status', status);
        else url.searchParams.delete('status');
        window.location.href = url.toString();
    });

})(jQuery);
