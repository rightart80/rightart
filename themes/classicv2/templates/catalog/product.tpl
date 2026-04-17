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
 * If you did not receivcc
 #'
 e a copy of the license and are unable to
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
{extends file=$layout}
{block name='head' append}
  <meta property="og:type" content="product">
  {if $product.cover}
    <meta property="og:image" content="{$product.cover.large.url}">
  {/if}

  {if $product.show_price}
    <meta property="product:pretax_price:amount" content="{$product.price_tax_exc}">
    <meta property="product:pretax_price:currency" content="{$currency.iso_code}">
    <meta property="product:price:amount" content="{$product.price_amount}">
    <meta property="product:price:currency" content="{$currency.iso_code}">
  {/if}
  {if isset($product.weight) && ($product.weight != 0)}
    <meta property="product:weight:value" content="{$product.weight}">
    <meta property="product:weight:units" content="{$product.weight_unit}">
  {/if}
{/block}

{block name='head_microdata_special'}
  {include file='_partials/microdata/product-jsonld.tpl'}
{/block}

{block name='content'}

  <section id="main">
    <meta content="{$product.url}">


    {block name= 'custom_product_page'}
      <div id="product-header" class="container">



        {block name='page_header'}
          <div id="titlebar" class="product-titlebar">
            {* Wish icon BEFORE title *}
            <div class="wishicon">
              {hook h='displayProductActions' product=$product}
            </div>

            <h1 class="h1">
              {block name='page_title'}{$product.name}{/block}
              <span id="title-postfix"> - Art Print</span>
            </h1>
          </div>
        {/block}
        {*         
        {block name='page_header_container'}


          {block name='page_header'}
            <h1 class="h1">{block name='page_title'}{$product.name}{/block}<span id="title-postfix"> - Art Print</span></h1>
          {/block}
        {/block} *}
        {* Artist category should be here *}
        {* --- Artist line below title --- *}
        {* under the H1 *}

        {block name='art_details'}
          {hook h='artistlink' product=$product}

        {/block}


        {* this is breadcrumb code *}
        {block name='breadcrumb'}
          {include file='_partials/breadcrumb.tpl'}
        {/block}
        {* this is END of breadscrumb code *}

      </div>

      {* This is main custom product code *}
      <div id="product-main" class="product-container">

        {block name='product-images'}
          {block name='product_cover_thumbnails'}
            {include file='catalog/_partials/product-images.tpl'}
          {/block}
        {/block}


        {* {block name='product_options'}
          {include file='catalog/_partials/product-options.tpl'}
        {/block} *}

      </div>

      <div id="page-bottom" class="page-bottom-container">

        {block name='product_description'}
          {include file='catalog/_partials/page-bottom-all.tpl'}
        {/block}
      </div>

    {/block}

    {* main custom product code End Here *}








    {* ////orignal  *}


    {* BLOCK 1 *}

    {* BLOCK_1_END *}
    {* {block name='product_accessories'}
      {if $accessories}
        <section class="product-accessories clearfix">
          <p class="h5 text-uppercase">{l s='You might also like' d='Shop.Theme.Catalog'}</p>
          <div class="products row">
            {foreach from=$accessories item="product_accessory" key="position"}
              {block name='product_miniature'}
                {include file='catalog/_partials/miniatures/product.tpl' product=$product_accessory position=$position productClasses="col-xs-12 col-sm-6 col-lg-4 col-xl-3"}
              {/block}
            {/foreach}
          </div>
        </section>
      {/if}
    {/block} *}

    {block name='product_footer'}

      {hook h='displayFooterProduct' product=$product category=$category}
    {/block}


    {block name='product_images_modal'}
      {include file='catalog/_partials/product-images-modal.tpl'}
    {/block}

    {block name='page_footer_container'}
      <footer class="page-footer">
        {block name='page_footer'}
          <!-- Footer content -->
        {/block}
      </footer>
    {/block}
  </section>


{/block}