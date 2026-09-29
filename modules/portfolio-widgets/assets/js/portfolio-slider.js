(function ($) {
    "use strict";

    function kmpw_portfolio_slider($scope, $) {
        console.log("Portfolio Slider Initialized");

        const $wrapper = $scope.find('.kmpw-portfolio-slider-wrapper');
        if (!$wrapper.length) return;

        const device_width = $(window).width();
        const $filterBtns = $wrapper.find('.kmpw-portfolio-filter-btn');
        const $portfolioItems = $wrapper.find('.kmpw-portfolio-item'); // Fixed: target items not container

        // Filter functionality
        $filterBtns.on('click', function () {
            const $btn = $(this);
            const category = $btn.data('category');

            // Update active state
            $filterBtns.removeClass('active');
            $btn.addClass('active');

            // Filter items
            if (category === 'all') {
                $portfolioItems.fadeIn(400);
            } else {
                $portfolioItems.each(function () {
                    const $item = $(this);
                    const categories = ($item.data('categories') || '').toString().split(',');

                    if (categories.includes(category.toString())) {
                        $item.fadeIn(400);
                    } else {
                        $item.fadeOut(400);
                    }
                });
            }

            // Reinitialize animation after filter
            setTimeout(function () {
                initScrollAnimation();
            }, 450);
        });

        // Scroll Animation
        function initScrollAnimation() {
            // Kill existing ScrollTriggers for this wrapper
            ScrollTrigger.getAll().forEach(st => {
                if (st.vars && st.vars.trigger && $(st.vars.trigger).closest('.kmpw-portfolio-slider-wrapper').is($wrapper)) {
                    st.kill(true); // Kill and clear props
                }
            });

            // Only run on desktop
            if (device_width <= 1025) {
                // Clear any transforms on mobile
                $portfolioItems.css({
                    'transform': 'none',
                    'position': 'relative'
                });
                return;
            }

            // Get only visible cards
            let cards = $wrapper.find('.kmpw-portfolio-item:visible').toArray();

            if (cards.length === 0) return;

            // Add spacing between cards for smoother animation
            const cardHeight = 650;
            const spacing = 50; // Space between each card pin

            cards.forEach((card, index) => {
                const $card = $(card);

                // Reset styles
                $card.css({
                    'transition-duration': '0s',
                    'transform-origin': '50% 50%',
                    'position': 'relative'
                });

                // Calculate scale - each card slightly smaller than previous
                const scale = 1 - (cards.length - index - 1) * 0.05;

                // Create the scale down animation
                const scaleDown = gsap.to(card, {
                    scale: scale,
                    ease: "none",
                    paused: true
                });

                // Calculate when this card should stop being pinned
                const nextCardIndex = index + 1;
                let endTrigger;

                if (nextCardIndex < cards.length) {
                    // Pin until next card reaches the top
                    endTrigger = `+=${cardHeight + spacing}`;
                } else {
                    // Last card - pin for the full height
                    endTrigger = `+=${cardHeight}`;
                }

                // Create ScrollTrigger for pinning and scaling
                ScrollTrigger.create({
                    trigger: card,
                    start: "top top",
                    end: endTrigger,
                    pin: true,
                    pinSpacing: false,
                    scrub: 0.5,
                    animation: scaleDown,
                    invalidateOnRefresh: true,
                    anticipatePin: 1,
                    onUpdate: (self) => {
                        // Apply scale based on progress
                        scaleDown.progress(self.progress);
                    },
                    onLeave: () => {
                        // Ensure proper cleanup
                        gsap.set(card, { scale: scale });
                    },
                    onEnterBack: () => {
                        // Reset when scrolling back up
                        gsap.set(card, { scale: 1 });
                    }
                });
            });

            // Refresh ScrollTrigger after setup
            ScrollTrigger.refresh();
        }

        // Initialize animation on page load
        if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);

            // Small delay to ensure DOM is ready
            setTimeout(function () {
                initScrollAnimation();
            }, 100);

            // Refresh on window resize (with debounce)
            let resizeTimer;
            $(window).on('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () {
                    const newWidth = $(window).width();
                    // Only reinit if crossing mobile/desktop boundary
                    if ((device_width <= 1025 && newWidth > 1025) ||
                        (device_width > 1025 && newWidth <= 1025)) {
                        location.reload(); // Reload on breakpoint change
                    } else {
                        initScrollAnimation();
                    }
                }, 250);
            });
        } else {
            console.error('GSAP or ScrollTrigger not loaded');
        }
    }

    // Initialize on Elementor frontend
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/kmpb-portfolio-slider.default',
            kmpw_portfolio_slider
        );
    });

})(jQuery);