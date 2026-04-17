{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
  {block name='header_banner'}
    <div class="header-banner">

      {hook h='displayBanner'}
    </div>
  {/block}

  <div class="container ra-container" id="header-container">

    <div class="col-md-2 header-logo-col">
      <div class="logo-holder" id="_desktop_logo">
        {if $shop.logo_details}
          {if $page.page_name == 'index'}
            <h1>{renderLogo}</h1>
          {else}
            {renderLogo}
          {/if}
        {else}
          {* simple placeholder if no logo is set *}
          <span>LOGO</span>
        {/if}
      </div>

      <div class="logo-holder" id="sticky-logo">
        {* {if $shop.logo_details}
        {if $page.page_name == 'index'}
          <h1>{renderLogo}</h1>
        {else}
          {renderLogo}
        {/if}
        {else}        
        {/if} *}
      </div>
    </div>


    <div class="col-md-10 header-right-col">

      {block name='header_nav'}
        <nav class="header-nav">
          <div class="container">
            <div class="hidden-sm-down">


              <div class="col-md-5 col-xs-12">
                {hook h='displayNav1'}

                <a href="/faqs" class="contact-link header-nav-link" title="{l s='Help' d='Shop.Theme.Global'}">
                  {l s='Help' d='Shop.Theme.Global'}
                </a>



              </div>
              <div class="col-md-7 right-nav">
                {hook h='displayNav2'}

                {if $customer.is_logged}
                  <a href="{$urls.pages.history}" class="guest-tracking header-nav-link"
                    title="{l s='Order history' d='Shop.Theme.Customeraccount'}">
                    {l s='Order history' d='Shop.Theme.Customeraccount'}
                  </a>
                {else}
                  <a href="{$urls.pages.guest_tracking}" class="guest-tracking header-nav-link"
                    title="{l s='Guest Tracking' d='Shop.Theme.Customeraccount'}">
                    {l s='Guest Tracking' d='Shop.Theme.Customeraccount'}
                  </a>
                {/if}


              </div>
            </div>
            <div class="hidden-md-up text-sm-center mobile">
              {* <div class="float-xs-left" id="menu-icon">
                <i class="material-icons d-inline">&#xE5D2;</i>
              </div> *}
              <div class="float-xs-right" id="_mobile_cart"></div>
              <div class="float-xs-right" id="_mobile_user_info"></div>
              <div class="top-logo" id="_mobile_logo"></div>
              <div class="clearfix"></div>
            </div>

          </div>
        </nav>
      {/block}

      {block name='header_top'}
        <div class="header-top">
          <div id="search-row" class="container">


            {* 
            <div class="col-md-2 hidden-sm-down" id="_desktop_logo">
              {if $shop.logo_details}
                {if $page.page_name == 'index'}
                  <h1>
                    {renderLogo}
                  </h1>
                {else}
                  {renderLogo}
                {/if}
              {/if}
            </div> *}



            <div class="header-top-right">
              {hook h='displayTop'}



              <div class="header-free-shipping">

                {assign var='legal_notice_url' value=$link->getCMSLink(2, 'shipping-policy')}
                <a class="shipping-notice" href="{$legal_notice_url}">FREE SHIPPING £80+ UK. ONLY</a>

              </div>


              <div id="mobile_top_menu_wrapper" class="row hidden-md-up" style="display:none;">
                <div class="js-top-menu mobile" id="_mobile_top_menu"></div>
                <div class="js-top-menu-bottom">
                  <div id="_mobile_currency_selector"></div>
                  <div id="_mobile_language_selector"></div>
                  <div id="_mobile_contact_link"></div>
                </div>
              </div>
            </div>
          </div>
          <div id="menue-row" class="container">
            {hook h='displayNavFullWidth'}
          </div>

        {/block}

      </div>

    </div>
    <div class="col-md-2 Sticky-header-icone">

    </div>

    <div id="mobile-header-block">



    </div>


    <div id="header-favorites-icon" class="{if $customer.is_logged}customer-logged-in{else}customer-guest{/if}">

      <a href="/module/blockwishlist/lists" {* href="https://rh.rightart.co.uk/module/blockwishlist/lists" *}
        target="{if $customer.is_logged}_self{else}_blank{/if}" style="
     position:absolute;
     top:0;
     left:0;
     width:100%;
     height:100%;
     display:block;
   ">
      </a>



    </div>

    <div id="header-search-icon">
    </div>

    <div id="header-cart-icon">
      <a class="cart-icon-link" href="https://rh.rightart.co.uk/en/cart?action=show"></a>
    </div>

    <a id="header-mini-logo" href="/">
      <img src="/images/right_art_mini_logo.png" alt="Right Art">
    </a>


  </div>

</div>

{* <div class="container desktop-header" id="header-container"> *}

