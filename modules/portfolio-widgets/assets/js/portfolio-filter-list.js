(function ($) {
    'use strict';

    function parseIds(raw) {
        if (!raw || typeof raw !== 'string') {
            return [];
        }
        return raw.split(',').map(function (id) {
            return String(id).trim();
        }).filter(Boolean);
    }

    function cardMatches($card, serviceId, industryId) {
        const services = parseIds($card.attr('data-kmpw-services'));
        const industries = parseIds($card.attr('data-kmpw-industries'));

        const serviceOk = !serviceId || services.indexOf(serviceId) !== -1;
        const industryOk = !industryId || industries.indexOf(industryId) !== -1;

        return serviceOk && industryOk;
    }

    function applyFilter($root) {
        const serviceId = String($root.find('[data-kmpw-pfl-filter="service"]').val() || '');
        const industryId = String($root.find('[data-kmpw-pfl-filter="industry"]').val() || '');
        const $links = $root.find('[data-kmpw-pfl-card]');
        const $empty = $root.find('.kmpw-pfl-empty');

        let visible = 0;
        $links.each(function () {
            const $card = $(this);
            const show = cardMatches($card, serviceId, industryId);
            $card.toggle(show);
            if (show) {
                visible += 1;
            }
        });

        if ($empty.length) {
            $empty.toggle(visible === 0);
        }
    }

    function bind($root) {
        $root.find('[data-kmpw-pfl-filter]').on('change', function () {
            applyFilter($root);
        });
        applyFilter($root);
    }

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/kmpb-portfolio-filter-list.default',
            function ($scope) {
                const $root = $scope.find('.kmpw-portfolio-filter-list');
                if ($root.length) {
                    bind($root);
                }
            }
        );
    });

    $(function () {
        $('.kmpw-portfolio-filter-list').each(function () {
            bind($(this));
        });
    });
})(jQuery);
