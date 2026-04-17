/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    SmartPresta <tehran.alishov@gmail.com>
 *  @copyright 2023 SmartPresta
 *  @license   Commercial License
 */

$(document).ready(function () {
// Left item group click 
    $('.catalogEITabs .list-group a.list-group-item').click(function (e) {
        e.preventDefault();
        $(this).siblings('.active').removeClass('active');
        $(this).addClass('active');
        $('.catalogEI-tab-content').hide();
        $('#catalogEI-tab-content-' + $(this).attr('id').replace('link-', '')).show();
    });

    var x = document.querySelectorAll('input[type=range]');
    for (var i = 0; i < x.length; i++) {
        x[i].oninput = function () {
            var value = (this.value - this.min) / (this.max - this.min) * 100;
            this.style.background = 'linear-gradient(to right, #3586AE ' + value + '%, #3586AE ' + value + '%, #EFEFEF ' + value + '%, #EFEFEF 100%)';
        };
    }
    
    $('.module-block button.btn.btn-default').on('click', function (e) {
        e.preventDefault();
        open($(this).parent().attr('href'), '_blank');
    });

});