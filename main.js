// ============================================================
// FENIX CAR HIRE - Main JavaScript
// assets/js/main.js
// Written with var and function() for maximum compatibility
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // ── NAVBAR SCROLL EFFECT ──
    var navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 40) {
                navbar.style.background = 'rgba(5,13,31,0.98)';
                navbar.style.boxShadow  = '0 4px 24px rgba(0,0,0,0.4)';
            } else {
                navbar.style.background = 'rgba(5,13,31,0.95)';
                navbar.style.boxShadow  = 'none';
            }
        });
    }

    // ── MOBILE NAV TOGGLE ──
    var toggle   = document.getElementById('navToggle');
    var navLinks = document.getElementById('navLinks');

    if (toggle && navLinks) {
        toggle.addEventListener('click', function () {
            if (navLinks.classList.contains('open')) {
                navLinks.classList.remove('open');
                toggle.textContent = '\u2630';
            } else {
                navLinks.classList.add('open');
                toggle.textContent = '\u2715';
            }
        });

        // Close nav when clicking outside
        document.addEventListener('click', function (e) {
            if (navbar && !navbar.contains(e.target)) {
                navLinks.classList.remove('open');
                toggle.textContent = '\u2630';
            }
        });
    }

    // ── SMOOTH SCROLL for anchor links ──
    var anchorLinks = document.querySelectorAll('a[href^="#"]');
    for (var i = 0; i < anchorLinks.length; i++) {
        anchorLinks[i].addEventListener('click', function (e) {
            var href   = this.getAttribute('href');
            var target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                if (navLinks) {
                    navLinks.classList.remove('open');
                }
            }
        });
    }

    // ── ACTIVE NAV LINK on scroll ──
    var sections  = document.querySelectorAll('section[id]');
    var navAnchors = document.querySelectorAll('.nav-links a');
    if (sections.length) {
        window.addEventListener('scroll', function () {
            var current = '';
            for (var s = 0; s < sections.length; s++) {
                if (window.scrollY >= sections[s].offsetTop - 100) {
                    current = sections[s].id;
                }
            }
            for (var n = 0; n < navAnchors.length; n++) {
                navAnchors[n].style.color = '';
                var href = navAnchors[n].getAttribute('href');
                if (href && href.indexOf(current) !== -1 && current !== '') {
                    navAnchors[n].style.color = '#60a5fa';
                }
            }
        });
    }

    // ── FADE-IN ANIMATION on scroll ──
    var fadeEls = document.querySelectorAll(
        '.car-card, .service-card, .info-card, .term-item, .stat-card, .admin-card'
    );
    for (var f = 0; f < fadeEls.length; f++) {
        fadeEls[f].style.opacity   = '0';
        fadeEls[f].style.transform = 'translateY(20px)';
        fadeEls[f].style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    }

    function checkFade() {
        for (var f = 0; f < fadeEls.length; f++) {
            var rect = fadeEls[f].getBoundingClientRect();
            if (rect.top < window.innerHeight - 60) {
                fadeEls[f].style.opacity   = '1';
                fadeEls[f].style.transform = 'translateY(0)';
            }
        }
    }
    window.addEventListener('scroll', checkFade);
    checkFade(); // run on load too

    // ── RETURN DATE must be after pickup date ──
    var pickupInput = document.querySelector('[name="pickup_date"]');
    var returnInput = document.querySelector('[name="return_date"]');
    if (pickupInput && returnInput) {
        pickupInput.addEventListener('change', function () {
            returnInput.min = this.value;
            if (returnInput.value && returnInput.value < this.value) {
                returnInput.value = this.value;
            }
        });
    }

    // ── AUTO-DISMISS ALERTS after 5 seconds ──
    var alerts = document.querySelectorAll('.alert');
    for (var a = 0; a < alerts.length; a++) {
        (function (alertEl) {
            setTimeout(function () {
                alertEl.style.transition = 'opacity 0.6s';
                alertEl.style.opacity    = '0';
                setTimeout(function () {
                    if (alertEl.parentNode) {
                        alertEl.parentNode.removeChild(alertEl);
                    }
                }, 600);
            }, 5000);
        })(alerts[a]);
    }

    // ── TABLE ROW HIGHLIGHT on click ──
    var tableRows = document.querySelectorAll('.admin-table tbody tr');
    for (var r = 0; r < tableRows.length; r++) {
        tableRows[r].addEventListener('click', function () {
            for (var x = 0; x < tableRows.length; x++) {
                tableRows[x].style.background = '';
            }
            this.style.background = 'rgba(59,130,246,0.08)';
        });
    }

    // ── ADMIN: LIVE INVOICE CALCULATION ──
    function calcInvoice() {
        var rateEl = document.querySelector('[name="rate_per_day"]');
        var qtyEl  = document.querySelector('[name="quantity"]');
        var daysEl = document.querySelector('[name="days"]');
        var cfEl   = document.querySelector('[name="contract_fee"]');
        var exKmEl = document.querySelector('[name="excess_kms"]');
        var exREl  = document.querySelector('[name="excess_rate"]');
        var prev   = document.getElementById('calcPreview');

        if (!rateEl || !prev) return;

        var rate    = parseFloat(rateEl.value)  || 0;
        var qty     = parseInt(qtyEl ? qtyEl.value : 1)   || 1;
        var days    = parseInt(daysEl ? daysEl.value : 1)  || 1;
        var cf      = parseFloat(cfEl ? cfEl.value : 0)   || 0;
        var exkm    = parseInt(exKmEl ? exKmEl.value : 0) || 0;
        var exr     = parseFloat(exREl ? exREl.value : 0) || 0;
        var subtotal = (rate * qty * days) + cf + (exkm * exr);
        var vat      = subtotal * 0.15;
        var total    = subtotal + vat;

        prev.innerHTML =
            '<div class="calc-box">' +
            '<span>Subtotal: <strong>E ' + subtotal.toFixed(2) + '</strong></span>' +
            '<span>VAT 15%: <strong>E ' + vat.toFixed(2) + '</strong></span>' +
            '<span style="color:#22c55e;font-size:18px">Total: <strong>E ' + total.toFixed(2) + '</strong></span>' +
            '</div>';
    }

    var invFields = ['rate_per_day', 'quantity', 'days', 'contract_fee', 'excess_kms', 'excess_rate'];
    for (var iv = 0; iv < invFields.length; iv++) {
        var invEl = document.querySelector('[name="' + invFields[iv] + '"]');
        if (invEl) {
            invEl.addEventListener('input', calcInvoice);
        }
    }
    calcInvoice();

    // ── ADMIN: LIVE QUOTATION CALCULATION ──
    function calcQuote() {
        var rateEl = document.querySelector('[name="rate_per_day"]');
        var qtyEl  = document.querySelector('[name="quantity"]');
        var daysEl = document.querySelector('[name="days"]');
        var cfEl   = document.querySelector('[name="contract_fee"]');
        var depEl  = document.querySelector('[name="deposit"]');
        var prev   = document.getElementById('calcPreview2');

        if (!rateEl || !prev) return;

        var rate   = parseFloat(rateEl.value)  || 0;
        var qty    = parseInt(qtyEl ? qtyEl.value : 1)  || 1;
        var days   = parseInt(daysEl ? daysEl.value : 1) || 1;
        var cf     = parseFloat(cfEl ? cfEl.value : 0)  || 0;
        var dep    = parseFloat(depEl ? depEl.value : 0) || 0;
        var rental = (rate * qty * days) + cf;
        var vat    = rental * 0.15;
        var total  = (rental * 1.15) + dep;

        prev.innerHTML =
            '<div class="calc-box">' +
            '<span>Rental + VAT: <strong>E ' + (rental * 1.15).toFixed(2) + '</strong></span>' +
            '<span>Deposit: <strong>E ' + dep.toFixed(2) + '</strong></span>' +
            '<span style="color:#60a5fa;font-size:18px">Total: <strong>E ' + total.toFixed(2) + '</strong></span>' +
            '</div>';
    }

    var quoteFields = ['rate_per_day', 'quantity', 'days', 'contract_fee', 'deposit'];
    for (var qv = 0; qv < quoteFields.length; qv++) {
        var qEl = document.querySelector('[name="' + quoteFields[qv] + '"]');
        if (qEl) {
            qEl.addEventListener('input', calcQuote);
        }
    }
    calcQuote();

});