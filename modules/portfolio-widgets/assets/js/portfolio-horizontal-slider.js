(function ($) {
    "use strict";

    function kmpw_portfolio_swiper($scope, $) {
        const $wrapper = $scope.find('.kmpw-portfolio-swiper-wrapper');

        if (!$wrapper.length) return;

        const swiperSettings = $wrapper.data('swiper-settings');
        const $swiperContainer = $wrapper.find('.swiper');

        if (!$swiperContainer.length) return;

        // إعدادات Swiper
        const swiperConfig = {
            effect: "coverflow",
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: 1, // العدد الافتراضي للموبايل
            loop: true,
            coverflowEffect: {
                rotate: swiperSettings.rotate || 50,
                stretch: 0,
                depth: swiperSettings.depth || 100,
                modifier: swiperSettings.modifier || 1,
                slideShadows: true,
            },
            speed: 700,
            watchSlidesProgress: true,
            // إضافة Breakpoints للتحكم في عدد العناصر حسب حجم الشاشة
            breakpoints: {
                // عندما يكون عرض الشاشة >= 320px (موبايل)
                320: {
                    slidesPerView: 1,
                },
                // عندما يكون عرض الشاشة >= 768px (تابلت)
                768: {
                    slidesPerView: 1,
                },
                // عندما يكون عرض الشاشة >= 1024px (شاشة كبيرة)
                1024: {
                    slidesPerView: 3,
                }
            }
        };

        // إضافة Pagination إذا كان مفعل
        if (swiperSettings.show_pagination) {
            swiperConfig.pagination = {
                el: $swiperContainer.find('.swiper-pagination')[0],
                clickable: true,
            };
        }

        // إضافة Navigation إذا كان مفعل
        if (swiperSettings.show_navigation) {
            swiperConfig.navigation = {
                nextEl: $swiperContainer.find('.swiper-button-next')[0],
                prevEl: $swiperContainer.find('.swiper-button-prev')[0],
            };
        }

        // إضافة Autoplay إذا كان مفعل
        if (swiperSettings.autoplay) {
            swiperConfig.autoplay = {
                delay: swiperSettings.autoplay_delay || 3000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            };
        }

        // تهيئة Swiper
        const swiper = new Swiper($swiperContainer[0], swiperConfig);

        // إيقاف الـ autoplay عند hover على العنصر
        if (swiperSettings.autoplay) {
            $swiperContainer.on('mouseenter', function () {
                swiper.autoplay.stop();
            });

            $swiperContainer.on('mouseleave', function () {
                swiper.autoplay.start();
            });
        }

        // تحديث Swiper عند تغيير حجم الشاشة
        $(window).on('resize', function () {
            swiper.update();
        });
    }

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/kmpb-portfolio-horizontal-slider.default',
            kmpw_portfolio_swiper
        );
    });
})(jQuery);