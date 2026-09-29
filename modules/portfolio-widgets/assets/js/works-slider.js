(function ($) {
    "use strict";

    function kmpw_works_slider($scope, $) {
        const $slider = $scope.find('.kmpw-works-slider');

        if (!$slider.length) {
            return;
        }

        const sliderSettings = $slider.data('slider-settings') || {};
        const $swiperContainer = $slider.find('.swiper-container');
        const $nextArrow = $slider.find('.swiper-button-next');
        const $prevArrow = $slider.find('.swiper-button-prev');
        const $pagination = $slider.find('.swiper-pagination');

        // Check if Swiper is loaded
        if (typeof Swiper === 'undefined') {
            console.error('Swiper library is not loaded!');
            return;
        }

        // Make sure container exists
        if (!$swiperContainer.length) {
            console.error('Swiper container not found!');
            return;
        }

        // Animate slide content
        function animateSlide(slide) {
            if (!slide) return;

            const $slide = $(slide);
            const $title = $slide.find('.slide-title');
            const $description = $slide.find('.slide-description');
            const $icon = $slide.find('.read-more-icon');

            // Reset animations
            $title.css({
                opacity: 0,
                transform: 'translateY(30px)'
            });
            $description.css({
                opacity: 0,
                transform: 'translateY(30px)'
            });
            $icon.css({
                opacity: 0,
                transform: 'scale(0.5)'
            });

            // Animate elements with delays
            setTimeout(function () {
                $title.css({
                    opacity: 1,
                    transform: 'translateY(0)',
                    transition: 'all 0.8s ease'
                });
            }, 100);

            setTimeout(function () {
                $description.css({
                    opacity: 1,
                    transform: 'translateY(0)',
                    transition: 'all 0.8s ease'
                });
            }, 300);

            setTimeout(function () {
                $icon.css({
                    opacity: 1,
                    transform: 'scale(1)',
                    transition: 'all 0.6s ease'
                });
            }, 500);
        }

        // Swiper options
        const swiperOptions = {
            loop: true,
            effect: sliderSettings.effect || 'slide',
            speed: sliderSettings.speed || 500,
            slidesPerView: 1,
            spaceBetween: 0,
            watchOverflow: true,
            on: {
                init: function () {
                    console.log('Swiper initialized');
                    // Animation on init
                    if (this.slides && this.slides[this.activeIndex]) {
                        animateSlide(this.slides[this.activeIndex]);
                    }
                },
                slideChange: function () {
                    // Animation on slide change
                    if (this.slides && this.slides[this.activeIndex]) {
                        animateSlide(this.slides[this.activeIndex]);
                    }
                }
            }
        };

        // Add navigation if arrows exist
        if ($nextArrow.length && $prevArrow.length) {
            swiperOptions.navigation = {
                nextEl: $nextArrow.get(0),
                prevEl: $prevArrow.get(0),
            };
        }

        // Add pagination if exists
        if ($pagination.length) {
            swiperOptions.pagination = {
                el: $pagination.get(0),
                clickable: true,
                dynamicBullets: true,
            };
        }

        // Add autoplay if enabled
        if (sliderSettings.autoplay) {
            swiperOptions.autoplay = {
                delay: sliderSettings.autoplay.delay || 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            };
        }

        // Initialize Swiper
        try {
            const swiper = new Swiper($swiperContainer.get(0), swiperOptions);

            // Pause autoplay on hover
            $slider.on('mouseenter', function () {
                if (swiper.autoplay && swiper.autoplay.running) {
                    swiper.autoplay.stop();
                }
            });

            $slider.on('mouseleave', function () {
                if (swiper.autoplay && sliderSettings.autoplay) {
                    swiper.autoplay.start();
                }
            });

            console.log('Swiper instance created successfully');
        } catch (error) {
            console.error('Error initializing Swiper:', error);
        }
    }

    // Initialize on Elementor frontend
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/kmpb-works-slider.default',
            kmpw_works_slider
        );
    });

})(jQuery);