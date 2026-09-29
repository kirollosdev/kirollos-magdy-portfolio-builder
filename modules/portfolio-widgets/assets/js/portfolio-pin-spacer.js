(function ($) {
    "use strict";

    if (typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined") {
        gsap.registerPlugin(ScrollTrigger);
    }

    // ============================================
    // Helpers
    // ============================================
    function isMobileOrTablet() {
        const userAgent = navigator.userAgent.toLowerCase();
        const mobileKeywords = ['android', 'webos', 'iphone', 'ipad', 'ipod', 'blackberry', 'windows phone', 'mobile', 'tablet'];
        const isMobileUA = mobileKeywords.some(k => userAgent.includes(k));
        const isTouchOnly = window.matchMedia("(hover: none) and (pointer: coarse)").matches;
        return isMobileUA || isTouchOnly;
    }

    function isDesktop() {
        return window.innerWidth > 1025 && !isMobileOrTablet();
    }

    // ============================================
    // Per-widget trigger storage
    // ============================================
    const widgetTriggers = new WeakMap();

    // ============================================
    // Hard-reset a card: strip ALL GSAP/pin inline styles immediately.
    // Must run BEFORE trigger.kill() so the element is clean when GSAP
    // tries to restore state during the kill sequence.
    // ============================================
    function hardResetCard(card) {
        gsap.set(card, { clearProps: "all" });
        card.style.cssText = "";
        card.classList.remove("gsap-pin-active");
        card.removeAttribute("data-ScrollTrigger-id");
    }

    // ============================================
    // Unwrap the pin-spacer <div> GSAP wraps around pinned elements
    // ============================================
    function unwrapPinSpacer(card) {
        const parent = card.parentElement;
        if (parent && parent.classList && parent.classList.contains('pin-spacer')) {
            const grandParent = parent.parentElement;
            if (grandParent) {
                grandParent.insertBefore(card, parent);
                grandParent.removeChild(parent);
            }
        }
    }

    // ============================================
    // Full teardown: reset styles -> kill triggers -> unwrap spacers
    // Order matters: reset FIRST so kill() doesn't re-apply stale state
    // ============================================
    function teardownWidget($root) {
        if (typeof ScrollTrigger === 'undefined') return;

        const rootEl = $root[0];

        // Step 1: hard-reset every card's inline styles BEFORE killing triggers
        const cards = Array.from(rootEl.querySelectorAll('.tc-card-item'));
        cards.forEach(hardResetCard);

        // Step 2: kill triggers stored in our WeakMap
        const stored = widgetTriggers.get(rootEl);
        if (stored && stored.length) {
            stored.forEach(t => { try { t.kill(true); } catch (e) {} });
        }
        widgetTriggers.set(rootEl, []);

        // Step 3: kill any orphan _kmpw_pin triggers that belong to this root
        ScrollTrigger.getAll().forEach(trigger => {
            if (!trigger || !trigger._kmpw_pin) return;
            const pinEl     = trigger.pin;
            const triggerEl = trigger.trigger;
            const spacerEl  = pinEl && pinEl.parentElement && pinEl.parentElement.classList.contains('pin-spacer')
                ? pinEl.parentElement : null;

            const inRoot = (triggerEl && rootEl.contains(triggerEl))
                        || (pinEl     && rootEl.contains(pinEl))
                        || (spacerEl  && rootEl.contains(spacerEl));

            if (inRoot) { try { trigger.kill(true); } catch (e) {} }
        });

        // Step 4: unwrap pin-spacer wrappers (safe now that triggers are dead)
        Array.from(rootEl.querySelectorAll('.tc-card-item')).forEach(unwrapPinSpacer);

        // Step 5: one final clearProps pass after unwrap (belt-and-suspenders)
        Array.from(rootEl.querySelectorAll('.tc-card-item')).forEach(card => {
            gsap.set(card, { clearProps: "all" });
            card.style.cssText = "";
        });
    }

    // ============================================
    // Main Init
    // ============================================
    function resolveWidgetRoot($scope) {
        if ($scope.hasClass("kmpw-portfolio-pin-spacer")) return $scope;
        return $scope.find(".kmpw-portfolio-pin-spacer").first();
    }

    function kmpw_portfolio_pin_spacer($scope, $) {
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

        const $root = resolveWidgetRoot($scope);
        if (!$root.length) return;

        // Full teardown first (styles cleared, triggers killed, spacers unwrapped)
        teardownWidget($root);

        // Mobile/Tablet: CSS sticky only, no GSAP needed
        if (!isDesktop()) return;

        const $container = $root.hasClass("tc-cards-animation")
            ? $root
            : $root.find(".tc-cards-animation").first();

        if (!$container.length) return;

        // Query cards AFTER teardown (DOM is clean now)
        const cards = gsap.utils.toArray($container.find('.tc-card-item'));
        if (cards.length === 0) return;

        const scopeTriggers = [];

        cards.forEach((card, index) => {
            if (index === cards.length - 1) return; // last card never gets pinned

            const trigger = ScrollTrigger.create({
                trigger: card,
                start: "top top",
                endTrigger: cards[index + 1],
                end: "top top",
                pin: true,
                pinSpacing: false,
                anticipatePin: 1,
                invalidateOnRefresh: true,
                onRefresh(self) { self.update(); },
            });

            trigger._kmpw_pin = true;
            scopeTriggers.push(trigger);
        });

        widgetTriggers.set($root[0], scopeTriggers);
        ScrollTrigger.refresh(true);
    }

    // ============================================
    // Reinit all widgets on page
    // ============================================
    function reinitAllPinSpacerWidgets() {
        if (typeof ScrollTrigger === 'undefined') return;

        // Kill every _kmpw_pin trigger site-wide first
        ScrollTrigger.getAll().forEach(trigger => {
            if (trigger && trigger._kmpw_pin) { try { trigger.kill(true); } catch (e) {} }
        });

        // Wait one frame for GSAP to finish cleanup, then reinit
        requestAnimationFrame(() => {
            $('.kmpw-portfolio-pin-spacer').each(function () {
                kmpw_portfolio_pin_spacer($(this), $);
            });
        });
    }

    // ============================================
    // Resize
    // ============================================
    let resizeTimer;
    let lastWidth  = window.innerWidth;
    let lastHeight = window.innerHeight;

    $(window).on('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const nw = window.innerWidth;
            const nh = window.innerHeight;
            if (Math.abs(nw - lastWidth) < 2 && Math.abs(nh - lastHeight) < 2) return;
            lastWidth  = nw;
            lastHeight = nh;
            $('.kmpw-portfolio-pin-spacer').each(function () {
                kmpw_portfolio_pin_spacer($(this), $);
            });
        }, 200);
    });

    // ============================================
    // Load
    // ============================================
    $(window).on('load', function () {
        setTimeout(reinitAllPinSpacerWidgets, 300);
    });

    // ============================================
    // Tab switch / visibility
    // ============================================
    let tabRestoreTimer;

    function scheduleTabRestoreReinit(delay) {
        if (typeof ScrollTrigger === 'undefined') return;
        clearTimeout(tabRestoreTimer);
        tabRestoreTimer = setTimeout(() => {
            if (typeof ScrollTrigger.clearScrollMemory === 'function') {
                ScrollTrigger.clearScrollMemory();
            }
            reinitAllPinSpacerWidgets();
        }, delay);
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) scheduleTabRestoreReinit(200);
    });

    window.addEventListener('pageshow', function (e) {
        scheduleTabRestoreReinit(e.persisted ? 300 : 80);
    });

    window.addEventListener('focus', function () {
        scheduleTabRestoreReinit(150);
    });

    // ============================================
    // Elementor hook
    // ============================================
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/kmpb-portfolio-pin-spacer.default',
            kmpw_portfolio_pin_spacer
        );
    });

})(jQuery);