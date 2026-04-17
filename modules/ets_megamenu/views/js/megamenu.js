/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
$(function() {
    $(document).mouseup(function (e)
    {
        var container_block_search = $('.mm_extra_item.active');
        if (!container_block_search.is(e.target)&& container_block_search.has(e.target).length === 0)
        {
            $('.mm_extra_item').removeClass('active');
        }
    });
    
    if($('.mm_extra_item button[type="submit"]').length)
    {
        $(document).on('click','.mm_extra_item button[type="submit"]',function(){
           if(!$(this).closest('.mm_extra_item').hasClass('mm_display_search_default') )
           {
                if ( !$(this).closest('.mm_extra_item').hasClass('active') ){
                    $(this).closest('.mm_extra_item').addClass('active');
                    return false;
                } else {
                    if ($(this).prev('input').val() == 0){
                        $('.mm_extra_item').removeClass('active');
                        return false;
                    }
                }
           } 
        });
    }
    displayHeightTab();
     if ($('.ets_mm_megamenu.sticky_enabled').length > 0)
     {
        var sticky_navigation_offset_top = $('.ets_mm_megamenu.sticky_enabled').offset().top;
        var headerFloatingHeight = $('.ets_mm_megamenu.sticky_enabled').height()+($('#header').length > 0 ? parseInt($('.ets_mm_megamenu.sticky_enabled').css('marginTop').replace('px',''))+parseInt($('.ets_mm_megamenu.sticky_enabled').css('marginBottom').replace('px','')) : 0);
        var oldHeaderMarginBottom = $('#header').length > 0 ? parseInt($('#header').css('marginBottom').replace('px','')) : 0;
        var sticky_navigation = function(){
            if(!$('.ets_mm_megamenu').hasClass('sticky_enabled'))
                return false;
            var scroll_top = $(window).scrollTop(); 
            if (scroll_top > sticky_navigation_offset_top) {
                $('.ets_mm_megamenu.sticky_enabled').addClass('scroll_heading');
                if($('#header').length > 0)
                    $('#header').css({'marginBottom':headerFloatingHeight+'px'});
            } else {
                $('.ets_mm_megamenu.sticky_enabled').removeClass('scroll_heading');
                if($('#header').length > 0)
                    $('#header').css({'marginBottom':oldHeaderMarginBottom+'px'});
            } 
        };
        sticky_navigation();
        $(window).scroll(function() {
             sticky_navigation();
        });
        if($(window).width() < 1025 && !$('body').hasClass('disable-sticky'))
                    $('body').addClass('disable-sticky');
        $(window).on('resize',function(e){
                if($(window).width() < 1025 && !$('body').hasClass('disable-sticky'))
                    $('body').addClass('disable-sticky');
                else
                    if($(window).width() >= 1025 && $('body').hasClass('disable-sticky'))
                        $('body').removeClass('disable-sticky');
         });
     }
     
     $(window).load(function(){
        if ($('.ets_mn_submenu_full_height').length > 0 ){
        var ver_sub_height = $('.ets_mn_submenu_full_height').height();
        $('.ets_mn_submenu_full_height').find('.mm_columns_ul').css("min-height",ver_sub_height);
     }
     });
     
     if ( $('.mm_columns_ul_tab_content').length > 0 && $('body#index').length >0 ){
        $('.mm_columns_ul_tab_content').addClass('active').prev('.arrow').removeClass('closed').addClass('opened');
    }
     
     $(window).resize(function(){
        $('.mm_menus_ul:not(.ets_mm_all_show_resize)').removeClass('ets_mn_active');
     });
     $(document).on('click','.mm_has_sub > .arrow',function(){
            var wrapper = $(this).next('.mm_columns_ul');
            if($(this).hasClass('closed'))
            {
                $('.mm_columns_ul').removeClass('active');
                $('.mm_has_sub > .arrow').removeClass('opened');
                $('.mm_has_sub > .arrow').addClass('closed');
                var btnObj = $(this); 
                btnObj.removeClass('closed');
                btnObj.addClass('opened');
                wrapper.stop(true,true).addClass('active');
            } else {
                var btnObj = $(this); 
                btnObj.removeClass('opened');
                btnObj.addClass('closed');
                wrapper.stop(true,true).removeClass('active');
            }
    }); 
     $('.transition_slide:not(.changestatus):not(.click_open_submenu) li.mm_menus_li').hover(function(){
        if($(window).width() >= 1025){
            $(this).find('.mm_columns_ul').stop(true,true).slideDown(200);
        }
    }, function(){
        if($(window).width() >= 1025){
            $(this).find('.mm_columns_ul').stop(true,true).slideUp(0);
        }
    });


    $(document).on('mouseenter','.ets_mm_megamenu .mm_menus_li:not(.mm_menus_li_tab),.ets_mm_megamenu .mm_menus_li.mm_menus_li_tab',function(e){
        if ( $('#header .ets_mm_megamenu:not(.scroll_heading)').length > 0 ){
            var mm_content_height = $('#header').height();
        } else if ( $('#header .ets_mm_megamenu.scroll_heading').length > 0 ){
            var mm_content_height = $('.ets_mm_megamenu').height();
        } else {
            var mm_content_height = $('#header').height() + $('.ets_mm_megamenu').height();
        }


    });

    $(document).on('mouseenter','.ets_mm_megamenu .mm_menus_li.mm_has_sub',function(e){
if ($(window).width() > 1024) {
            var _mm_item_position = $(this).position().top + $(this).outerHeight();
            $(this).find('.mm_columns_ul').css('top',_mm_item_position+'px');
        }
    });

    $(document).on('mouseleave','.ets_mm_megamenu',function(e){
        $('.mm_columns_ul').css('top','');
    });

    $('.transition_slide.click_open_submenu:not(.changestatus) li.mm_menus_li').on('click',function(e){
        if($(window).width() >= 1025){
            $(this).find('.mm_columns_ul').stop(true,true).slideDown(200);
        }
    });
    
    $('.ybc-menu-toggle, .ybc-menu-vertical-button').on('click',function(){
        var wrapper = $(this).next('.mm_menus_ul');
        if($(this).hasClass('closed'))
        {
            var btnObj = $(this);
            $('body').addClass('noscroll');
            btnObj.removeClass('closed');
            btnObj.addClass('opened');
            wrapper.stop(true,true).addClass('active');
            if ( $('.transition_slide.transition_default').length != '' ){
                wrapper.stop(true,true).slideDown(0);
            }
        } else {
            $('body').removeClass('noscroll');
            var btnObj = $(this);
            btnObj.removeClass('opened');
            btnObj.addClass('closed');
            wrapper.stop(true,true).removeClass('active');
            if ( $('.transition_slide.transition_default').length != '' ){
                wrapper.stop(true,true).slideUp(0);
            }
        }
    });
    $('.close_menu').on('click',function(){
        $('body').removeClass('noscroll');
        $(this).parent().prev().removeClass('opened');
        $(this).parent().prev().addClass('closed');
        $(this).parent().stop(true,true).removeClass('active');
    });
    if($('.ets_mm_megamenu').hasClass('enable_active_menu') && $('.mm_menus_ul > li').length > 0)
    {
        var currentUrl = window.location.href;      
        $('.mm_menus_ul > li').each(function(){
            if($(this).find('a[href="'+currentUrl+'"]').length > 0)
            {
                $(this).addClass('active');
                return false;
            }
        });
    }
    if($('.mm_breaker').length > 0 && $('.mm_breaker').prev('li').length > 0)
    {
        $('.mm_breaker').prev('li').addClass('mm_before_breaker');
    }

    $('.mm_menus_li .mm_tabs_li').hover(function(){
        if(!$(this).hasClass('open'))
        {
            $(this).parents('.mm_columns_ul_tab').find('.mm_tabs_li').removeClass('open');
            $(this).addClass('open');
            $(this).closest('.mm_columns_ul').removeClass('mm_tab_no_content');
            if ( !$(this).find('.mm_columns_contents_ul').length ){
                $(this).closest('.mm_columns_ul').addClass('mm_tab_no_content');
            }
            displayHeightTab();
            var mmlitab_height = $(this).outerHeight() * ($('.mm_tabs_li:not(.mm_tab_content_hoz)').index($('.open')) + 1) + 1;
            $('.mm_columns_contents_ul').css('min-height',mmlitab_height);
        }
    }, function() {
        $('.mm_tabs_li.menu_ver_alway_open_first').addClass('open').parents('.mm_columns_ul_tab').removeClass('mm_tab_no_content');
        $('.mm_tabs_li:not(.menu_ver_alway_open_first)').removeClass('open');
    });

    $('.mm_menus_li:not(.menu_ver_alway_show_sub )').hover(function(){
        $('.menu_ver_alway_show_sub .mm_columns_ul_tab_content').removeClass('active');
    }, function() {
        $('.menu_ver_alway_show_sub .mm_columns_ul_tab_content').addClass('active');
    });

    if ($('.clicktext_show_submenu').length <= 0)
    {
        $(document).on('click touchstar', '.mm_tab_li_content', function (evt) {
            var btnObj = $(this), wrapper = $(this).next();
            if (!btnObj.find('.mm_tab_toggle_title a').is(evt.target))
            {
                if(btnObj.hasClass('closed'))
                {
                    $('.mm_tab_li_content').removeClass('opened');
                    $('.mm_tab_li_content').addClass('closed');
                    $('.mm_columns_contents_ul').removeClass('active');
                    btnObj.removeClass('closed');
                    btnObj.addClass('opened');
                    wrapper.stop(true,true).addClass('active');
                } else {
                    btnObj.removeClass('opened');
                    btnObj.addClass('closed');
                    wrapper.stop(true,true).removeClass('active');
                }
        }
        });
    }

});
// function autoChangeStatus()
// {
//     var width_ul_menu = $('ul.mm_menus_ul').width();
//     var width_li_menu=0;
//     $('ul.mm_menus_ul li.mm_menus_li').each(function(){
//         width_li_menu += parseFloat($(this).width());
//     });
    
//     if(width_li_menu > width_ul_menu+5)
//     {
//         $('.ets_mm_megamenu').addClass('changestatus'); 
//         $('.menu_ver_alway_show_sub .mm_columns_ul_tab_content').removeClass('active');
//         $('#index .menu_ver_alway_show_sub .arrow').removeClass('opened').addClass('closed');
//     }
//     else
//     {
//         $('.ets_mm_megamenu').removeClass('changestatus');
//         if ( $(window).width() > 1024 ){
//             $('#index .menu_ver_alway_show_sub .arrow').addClass('opened').removeClass('closed');
//             $('#index .menu_ver_alway_show_sub .mm_columns_ul_tab_content').addClass('active');
//         }
//     }
//     if ( $(window).width() < 1025 ){
//         $('.menu_ver_alway_show_sub .mm_columns_ul_tab_content').removeClass('active');
//         $('.menu_ver_alway_show_sub .arrow').removeClass('opened').addClass('closed');
//     }
// }

function autoChangeStatus()
{
    if ($(window).width() <= 1024) {
        $('.ets_mm_megamenu').addClass('changestatus');
        $('.menu_ver_alway_show_sub .mm_columns_ul_tab_content').removeClass('active');
        $('#index .menu_ver_alway_show_sub .arrow').removeClass('opened').addClass('closed');
    } else {
        $('.ets_mm_megamenu').removeClass('changestatus');
        $('#index .menu_ver_alway_show_sub .arrow').addClass('opened').removeClass('closed');
        $('#index .menu_ver_alway_show_sub .mm_columns_ul_tab_content').addClass('active');
    }
}



function itemClickMenu($this){
    var btnObj =  $($this).next('.arrow');
     var wrapper =  btnObj.next();
    if ( ! btnObj.length ){
        var btn_temp = $($this).closest('.mm_tab_li_content').first();
        var wrapper =  btn_temp.next();
        if( btn_temp.hasClass('closed')){
            $('.mm_tab_li_content').removeClass('opened');
                    $('.mm_tab_li_content').addClass('closed');
                    $('.mm_tab_li_content + .mm_columns_contents_ul').removeClass('active');
            btn_temp.removeClass('closed');
            btn_temp.addClass('opened');
            wrapper.stop(true,true).addClass('active');
        }else{
            btn_temp.removeClass('opened');
            btn_temp.addClass('closed');      
            wrapper.stop(true,true).removeClass('active');
        }
    }else{
        if(btnObj.hasClass('closed'))
        {
            $('.mm_has_sub > .arrow').removeClass('opened');
            $('.mm_has_sub > .arrow').addClass('closed');      
            $('.mm_columns_ul').removeClass('active');
            
            btnObj.removeClass('closed');
            btnObj.addClass('opened');
            wrapper.stop(true,true).addClass('active');
        } else {
            btnObj.removeClass('opened');
            btnObj.addClass('closed');      
            wrapper.stop(true,true).removeClass('active');
        } 
    }
}
function clickTextShowMenu(){
    if ( $('.clicktext_show_submenu').length > 0 ){
         $('.clicktext_show_submenu li.has-sub').each(function() {
            $(this).find('a').first().on('click', function(e){
                if ($(window).width() <= 1024 ){
                    e.preventDefault();
                   var btnObj =  $(this).next('.arrow');
                    var wrapper =  btnObj.next();
                   if(btnObj.hasClass('closed'))
                    {                        
                        btnObj.removeClass('closed');
                        btnObj.addClass('opened');
                        wrapper.stop(true,true).addClass('active');
                    } else {
                        btnObj.removeClass('opened');
                        btnObj.addClass('closed');      
                        wrapper.stop(true,true).removeClass('active');
                    } 
               } 
            });
            
        });
        
    }
    if ( $('.clicktext_show_submenu').length > 0 ){
        $('.clicktext_show_submenu li.has-sub').each(function() {
            $(this).find('a').first().on('click', function(e){
                if ( $('.ets_mm_megamenu').hasClass('changestatus') && $(window).width() > 1024 ){
                   e.preventDefault();
                   var btnObj =  $(this).next('.arrow');
                    var wrapper =  btnObj.next();
                   if(btnObj.hasClass('closed'))
                    {                        
                        btnObj.removeClass('closed');
                        btnObj.addClass('opened');
                        wrapper.stop(true,true).addClass('active');
                    } else {
                        btnObj.removeClass('opened');
                        btnObj.addClass('closed');      
                        wrapper.stop(true,true).removeClass('active');
                    } 
                }
            });
        });
    }
    
    if ( $('.clicktext_show_submenu').length > 0 ){
         $('.clicktext_show_submenu li.mm_tabs_has_content > div').each(function() {
            $(this).find('a').first().on('click', function(e){
                if ($(window).width() <= 1024 ){
                    e.preventDefault();
                    itemClickMenu(this);
               } 
            });
        });
    }
    if ( $('.clicktext_show_submenu').length > 0 ){
        $('.clicktext_show_submenu li.mm_tabs_has_content > div').each(function() {
            $(this).find('a').first().on('click', function(e){
                if ( $('.ets_mm_megamenu').hasClass('changestatus') && $(window).width() > 1024 ){
                   e.preventDefault();
                   itemClickMenu(this);
                }
            });
        });
    }
    if ( $('.clicktext_show_submenu').length > 0 ){
         $('.clicktext_show_submenu li.mm_has_sub > a').each(function() {
            $(this).on('click', function(e){
                if ($(window).width() <= 1024 ){
                    e.preventDefault();
                   itemClickMenu(this);
               } 
            });
        });
    }
    if ( $('.clicktext_show_submenu').length > 0 ){
        $('.clicktext_show_submenu li.mm_has_sub > a').each(function() {
            $(this).on('click', function(e){
                if ( $('.ets_mm_megamenu').hasClass('changestatus') && $(window).width() > 1024 ){
                   e.preventDefault();
                   itemClickMenu(this);
                }
            });
        });
    }
    if ( $('.clicktext_show_submenu').length > 0 ){
        $('.mm_tab_has_child > .mm_tab_toggle_title').on('click', function(e){
            if ( $(this).find('a').length <= 0 ){
                if ( $('.ets_mm_megamenu').hasClass('changestatus') || $(window).width() > 1024 ){
                   var btnObj = $(this).parents('.mm_tab_li_content'), wrapper = $(this).parents('.mm_tab_li_content').next();

                    if(btnObj.hasClass('closed'))
                    {
                        $('.mm_tab_li_content').removeClass('opened');
                        $('.mm_tab_li_content').addClass('closed');
                        $('.mm_tab_li_content + .mm_columns_contents_ul').removeClass('active');

                        btnObj.removeClass('closed');
                        btnObj.addClass('opened');
                        wrapper.stop(true,true).addClass('active');
                    } else {
                        btnObj.removeClass('opened');
                        btnObj.addClass('closed');
                        wrapper.stop(true,true).removeClass('active');
                    }
                }
            }
        });
    }
}


$(document).on('click','.ets_mm_categories .has-sub .arrow',function(e){
        e.stopPropagation();
        var wrapper = $(this).next('.ets_mm_categories');
        if($(this).hasClass('closed'))
        {
            var btnObj = $(this); 
            btnObj.removeClass('closed');
            btnObj.addClass('opened');
            wrapper.stop(true,true).addClass('active');
        } else {
            var btnObj = $(this); 
            btnObj.removeClass('opened');
            btnObj.addClass('closed');
            wrapper.stop(true,true).removeClass('active');
        }
        
});

function displayHeightTab()
{
    if($('.mm_tabs_li.open .mm_columns_contents_ul').length)
    {
        $('.mm_tabs_li.open .mm_columns_contents_ul').each(function(){
           $(this).closest('.mm_columns_ul_tab').css('height', $(this).height('px')); 
        });
    }
}
$(document).ready(function(){

    var ETS_MM_ACTIVE_BG_GRAY = $('.ets_mm_megamenu').attr('data-bggray');
    $('.ets_mm_megamenu').removeClass('bg_submenu');
    if (typeof ETS_MM_ACTIVE_BG_GRAY !== "undefined" && ETS_MM_ACTIVE_BG_GRAY ) {
        $('.ets_mm_megamenu .mm_menus_ul > li.mm_has_sub.hover').mouseenter(function() {
            $('.ets_mm_megamenu').addClass('bg_submenu');
        })
        .mouseleave(function() {
            $('.ets_mm_megamenu').removeClass('bg_submenu');
        });
    }
    $(".mm_menus_li.click_open_submenu > a.ets_mm_url").click(function(e){
        e.preventDefault();
    });
    $(".mm_menus_li.click_open_submenu").click(function(){
        if ( $(this).hasClass('open_li') ){
            $(".mm_menus_li").removeClass("open_li");
            $(".mm_menus_li .mm_columns_ul").removeClass("open");
        } else {
            $(".mm_menus_li").removeClass("open_li");
            $(".mm_menus_li .mm_columns_ul").removeClass("open");
            $(this).addClass('open_li');
            $(this).find('.mm_columns_ul').addClass('open');
        }
    });
    $(document).on("click", function(event){
        if(!$(event.target).closest(".mm_menus_li").length){
            $('.transition_slide.click_open_submenu').find(".mm_columns_ul.open").slideUp(100);
            $(".mm_columns_ul").removeClass("open");
            $(".mm_menus_li").removeClass("open_li");
        }
    });
    $(document).on('click','.mm_tab_li_content',function(){
        $('.mm_tabs_li').not($(this).parent()).removeClass('open');
        $(this).parent('.mm_tabs_li').toggleClass('open');
    });
    $(document).on("click", function(event){
        if(!$(event.target).closest(".mm_tabs_li").length){
            $(".mm_tabs_li").removeClass("open");
            $(".mm_tabs_li.menu_ver_alway_open_first").addClass("open");
        }
    });
    $(document).on('click','.ets_mm_megamenu a',function(){
       if($(this).attr('href')=='' || $(this).attr('href')=='#')
       {
            return false;
       } 
    });
});
$(document).ready(function(){
    autoChangeStatus();
    clickTextShowMenu();
    
    $(window).resize(function(){
        autoChangeStatus();
    });

    $('.mm_menus_li iframe').hover(function () {
        $(this).parents('.mm_menus_li').addClass('menu_hover');
    }, function () {
        $(this).parents('.mm_menus_li').removeClass('menu_hover');
    });
});