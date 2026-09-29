(function ($) {
    "use strict";

    function kmpwFeaturedImage($scope, $) {

        // Height same as width functionality
        function setImageHeightSameAsWidth() {
            $(".img-h-w").each(function () {
                var imgWidth = $(this).width();
                $(this).css("height", imgWidth);
            });
        }

        const floatImgs = $scope.find('.kmpw-float-animation img');
        if (floatImgs.length > 0) {
            document.addEventListener('mousemove', (e) => {
                const x = (e.clientX / window.innerWidth - 0.5) * 300;
                const y = (e.clientY / window.innerHeight - 0.5) * 300;
                floatImgs.each(function () {
                    this.style.transform = `translate(${x}px, ${y}px)`;
                });
            });
        }

        // Initialize on load
        $(window).on('load', function () {
            // Set height same as width for images with img-h-w class
            setImageHeightSameAsWidth();

            // Container hover effects
            $('.e-parent .elementor-widget-kmpb-post-image.activate-on-scroll').each(function () {
                $scope.parent().on('mouseenter', function () {
                    $scope.find('.selector-type-container').addClass('kmpw-post-image-container-active');
                }).on('mouseleave', function () {
                    $scope.find('.selector-type-container').removeClass('kmpw-post-image-container-active');
                });
            });

            // Parent container hover effects
            $('.e-parent .elementor-widget-kmpb-post-image.activate-on-scroll').each(function () {
                $scope.parent().parent().on('mouseenter', function () {
                    $scope.find('.selector-type-parent-container').addClass('kmpw-post-image-parent-container-active');
                }).on('mouseleave', function () {
                    $scope.find('.selector-type-parent-container').removeClass('kmpw-post-image-parent-container-active');
                });
            });
        });

        // Recalculate height on window resize
        $(window).on('resize', function () {
            setImageHeightSameAsWidth();
        });

        // Also recalculate when images load (for lazy loading)
        $scope.find('.img-h-w').on('load', function () {
            var imgWidth = $(this).width();
            $(this).css("height", imgWidth);
        });

        // Ensure height adjustment works with Elementor's responsive breakpoints
        var resizeTimeout;
        $(window).on('resize', function () {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function () {
                setImageHeightSameAsWidth();
            }, 250); // Debounce resize events
        });

        // Handle container animations
        if ($scope.hasClass('activate-on-scroll')) {
            // For container selector type
            $scope.parent().on('mouseenter', function () {
                $scope.find('.selector-type-container').addClass('kmpw-post-image-container-active');
            }).on('mouseleave', function () {
                $scope.find('.selector-type-container').removeClass('kmpw-post-image-container-active');
            });

            // For parent container selector type
            $scope.parent().parent().on('mouseenter', function () {
                $scope.find('.selector-type-parent-container').addClass('kmpw-post-image-parent-container-active');
            }).on('mouseleave', function () {
                $scope.find('.selector-type-parent-container').removeClass('kmpw-post-image-parent-container-active');
            });
        }
    }

    // Initialize when Elementor frontend is ready
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/kmpb-post-image.default', kmpwFeaturedImage);
    });

})(jQuery);