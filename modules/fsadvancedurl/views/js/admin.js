/**
 * Copyright 2022 ModuleFactory
 *
 * @author    ModuleFactory
 * @copyright ModuleFactory all rights reserved.
 * @license   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

var FSAU = FSAU || {};

$(document).ready(function(){

    var fsau_menu_html = '';
    if ($("div#header ul#menu").html())
    {
        //1.5
        fsau_menu_html = '<li id="maintabFSAU" class="maintab submenu_size"><a class="title" href="'+FSAU.menu_button_url+'"><img alt="" src="../img/t/AdminWebservice.gif">'+FSAU.menu_button_text+'</a></li>';
        $('div#header ul#menu').append(fsau_menu_html);
    }
    else if ($("nav#nav-sidebar ul.menu").html())
    {
        //1.6 side
        fsau_menu_html = '<li id="maintabFSAU" class="maintab"><a class="title" href="'+FSAU.menu_button_url+'"><i class="icon-AdminShopUrl"></i> <span>'+FSAU.menu_button_text+'</span></a></li>';
        $('nav#nav-sidebar ul.menu').append(fsau_menu_html);
    }
    else if ($("nav#nav-topbar ul.menu").html())
    {
        //1.6 top
        fsau_menu_html = '<li id="maintabFSAU" class="maintab"><a class="title" href="'+FSAU.menu_button_url+'"><i class="icon-AdminShopUrl"></i> <span>'+FSAU.menu_button_text+'</span></a></li>';
        $('nav#nav-topbar ul.menu').append(fsau_menu_html);
    }
    else if ($("nav.nav-bar ul.main-menu").html())
    {
        //1.7 symfony
        fsau_menu_html = '<li id="maintabFSAU" class="link-levelone"><a class="link" href="'+FSAU.menu_button_url+'"><i class="material-icons">link</i> <span>'+FSAU.menu_button_text+'</span></a></li>';
        $('nav.nav-bar ul.main-menu').append(fsau_menu_html);
    }
});
