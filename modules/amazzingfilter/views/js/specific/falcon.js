/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

af.events.theme.falcon = function() {
    af.productItemSelector = '.products-list__block';
    af.ajax_path_orig = af_ajax.path;
    af.on('prepareDynamicParams', function(params) {
        let url = new URL(af.ajax_path_orig, window.location.origin),
            grid = $('.display-toggle__link.active').data('display-type') || 'grid';
        url.searchParams.set('listingDisplayType', grid);
        if (af.nbItems.current !== af.nbItems.orig) {
            url.searchParams.set('resultsPerPage', af.nbItems.current);
        }
        af_ajax.path = url.toString();
    });
    if (typeof prestashop.pageLazyLoad == 'object') {
        af.on('afterAjax', function(r) {
            prestashop.pageLazyLoad.update();
        });
    }
}
af.events.theme.sorting = function() {
    $('#products').on('change', '[data-action="search-select"]', function(e) {
        let params = af.extractParamsFromURL($(this).find('option:selected').data('href'));
        if (params.order || params.resultsPerPage) {
            if (params.order) {
                params.order = params.order.split('.');
                $('#af_orderBy').val(params.order[1]);
                $('#af_orderWay').val(params.order[2]);
            }
            if (params.resultsPerPage) {
                af.nbItems.current = params.resultsPerPage;
                $('#af_nb_items').val(af.nbItems.current);
            }
            $('#af_orderWay').trigger('change');
        }
        e.stopImmediatePropagation();
    });
}
af.events.theme.pagination = function() {
    $('#products').on('click', '.js-search-link.page-link', function(e) {
        e.preventDefault();
        let params = af.extractParamsFromURL($(this).attr('href')),
            page = 1;
        if (af_param_names.p in params && params[af_param_names.p] > 1) {
            page = params[af_param_names.p];
        }
        af.$pageInput.val(page).change();
        e.stopImmediatePropagation();
    })
}
af.extractParamsFromURL = function(rawURL) {
    let params = {};
    for (let [key, value] of new URL(rawURL, window.location.origin).searchParams) {
        params[key] = value;
    }

    return params;
}
/* since 3.3.2 */
