(function ($) {
    "use strict";

    function initPortfolioGridSlider($scope) {
        var $wrapper = $scope.find('.kmpw-portfolio-grid-slider-wrapper');
        if (!$wrapper.length) return;

        var $slider = $wrapper.find('.kmpw-portfolio-slider');
        if (!$slider.length) return;

        // Helper to parse settings
        function parseSettings($el) {
            var raw = $el.data('slider-settings') || $el.attr('data-slider-settings') || '{}';
            try {
                return (typeof raw === 'object') ? raw : JSON.parse(raw);
            } catch (e) {
                return {};
            }
        }

        var settings = parseSettings($slider);
        var slidesPerViewDesktop = settings.slidesPerView || 3;

        var swiperOptions = {
            loop: true,
            speed: settings.speed || 600,
            slidesPerView: 1,
            spaceBetween: 24,
            pagination: {
                el: $slider.find('.swiper-pagination').get(0),
                clickable: true
            },
            breakpoints: {
                0: { slidesPerView: 1 },
                768: { slidesPerView: 2 },
                1024: { slidesPerView: slidesPerViewDesktop }
            }
        };

        // Autoplay
        if (settings.autoplay && typeof settings.autoplay === 'object' && settings.autoplay.delay) {
            swiperOptions.autoplay = {
                delay: settings.autoplay.delay,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            };
        }

        // Initialize Swiper
        try {
            var containerEl = $slider.find('.swiper-container').get(0);
            if (containerEl) {
                var swiper = new Swiper(containerEl, swiperOptions);

                // Update on window resize
                $(window).on('load resize orientationchange', function () {
                    try {
                        swiper.update();
                    } catch (e) {
                        console.error('Swiper update error', e);
                    }
                });
            }
        } catch (err) {
            console.error('Swiper init error', err);
        }
    }

    // Initialize on Elementor frontend
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/kmpb-works-tabs-mini.default', function ($scope) {
            initPortfolioGridSlider($scope);
        });
    });

})(jQuery);