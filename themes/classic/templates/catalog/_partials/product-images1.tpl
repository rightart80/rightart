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

{* -------------------------------------------------------- *}
{*  Collect selected attributes (with fallbacks / safe checks)
{* -------------------------------------------------------- *}

{assign var="selected_shape" value=""}
{assign var="selected_type" value=""}
{assign var="selected_size" value=""}
{assign var="selected_frame" value=""}

{if isset($groups[5]) && isset($groups[5].attributes)}
  {foreach from=$groups[5].attributes item=attribute}
    {if !empty($attribute.selected)}
      {assign var="selected_shape" value=$attribute.name}
    {/if}
  {/foreach}
{/if}

{if isset($groups[6]) && isset($groups[6].attributes)}
  {foreach from=$groups[6].attributes item=attribute}
    {if !empty($attribute.selected)}
      {assign var="selected_type" value=$attribute.name}
    {/if}
  {/foreach}
{/if}

{if isset($groups[7]) && isset($groups[7].attributes)}
  {foreach from=$groups[7].attributes item=attribute}
    {if !empty($attribute.selected)}
      {assign var="selected_size" value=$attribute.name}
    {/if}
  {/foreach}
{/if}

{if isset($groups[8]) && isset($groups[8].attributes)}
  {foreach from=$groups[8].attributes item=attribute}
    {if !empty($attribute.selected)}
      {assign var="selected_frame" value=$attribute.name}
    {/if}
  {/foreach}
{/if}

{* -------------------------------------------------------- *}
{*  Build CSS helper classes
{* -------------------------------------------------------- *}

{assign var="class_01" value=""}
{assign var="class_02" value=""}
{assign var="class_03" value=""}
{assign var="class_04" value=""}

{if $selected_shape|default:'' != ''}
  {assign var="class_01" value="shape-"|cat:$selected_shape|lower|replace:" ":"-"}
{/if}

{if $selected_type|default:'' != ''}
  {assign var="class_02" value="type-"|cat:$selected_type|lower|replace:" ":"-"}
{/if}

{if $selected_size|default:'' != ''}
  {assign var="class_03" value="size-"|cat:$selected_size|lower|replace:" ":"-"}
{/if}

{if $selected_frame|default:'' != ''}
  {assign var="class_04" value="frame-"|cat:$selected_frame|lower|replace:" ":"-"}
{/if}

{* Concatenate non-empty classes *}
{assign var="final_classes" value=""}
{if $class_01 != ""}{assign var="final_classes" value=$final_classes|cat:$class_01|cat:" "}{/if}
{if $class_02 != ""}{assign var="final_classes" value=$final_classes|cat:$class_02|cat:" "}{/if}
{if $class_03 != ""}{assign var="final_classes" value=$final_classes|cat:$class_03|cat:" "}{/if}
{if $class_04 != ""}{assign var="final_classes" value=$final_classes|cat:$class_04}{/if}
{assign var="final_classes" value=$final_classes|trim}

{* art_code built safely with capture (no broken pipes) *}
{capture name=art_code}
{if $selected_shape|default:'' != ''}{$selected_shape|lower|replace:" ":"-"}{/if}
{if $selected_type|default:''  != ''}-{$selected_type|lower|replace:" ":"-"}{/if}
{if $selected_size|default:''  != ''}-{$selected_size|lower|replace:" ":"-"}{/if}
{if $selected_frame|default:'' != ''}-{$selected_frame|lower|replace:" ":"-"}{/if}
{/capture}
{assign var="art_code" value=$smarty.capture.art_code|trim:"- "}

{* shape-only helper *}
{assign var="art_shape" value=$selected_shape|default:''|lower|replace:" ":"-"}

{* piece flag *}
{assign var="piece" value="pc1"}
{if $selected_size == "3-PIECE"}
  {assign var="piece" value="pc3"}
{/if}

{* -------------------------------------------------------- *}
{*  Image fallbacks (avoid undefined index notices)
{* -------------------------------------------------------- *}

{assign var="default_img" value=$product.default_image.bySize.large_default_new.url|default:''}
{assign var="second_img" value=$default_img}
{if isset($product.images[1].bySize.large_default_new.url) && $product.images[1].bySize.large_default_new.url != ''}
  {assign var="second_img" value=$product.images[1].bySize.large_default_new.url}
{/if}

{* -------------------------------------------------------- *}
{*  Product Images Section
{* -------------------------------------------------------- *}

<section id="product-images" class="product-images-box {$piece} {$final_classes} {$art_code}">

  {* {block name='product_flags'}
    {include file='catalog/_partials/product-flags.tpl'}
  {/block} *}

  {* Main large visuals *}
  <div id="product-large-image" class="large-image">
    <section class="canvas square p-01">
      <div class="image-1">
        <img class="big-canvas" src="{$default_img}" alt="Main product image">
      </div>
      <div class="image-2">
        <img class="big-canvas" src="{$second_img}" alt="Second product image">
      </div>
    </section>

    <section class="room">
      <div class="room-art-position">
        <div class="room-art">
          <div class="art-frame"></div>
          <div class="art-image">
            <img class="artwork"
                 src="{$second_img|escape:'htmlall':'UTF-8'}"
                 alt="Product artwork">
          </div>
        </div>
      </div>
    </section>
  </div>

  {* Thumbnails *}
  <div id="product-thumbnails" class="thumbnails">

    <section class="canvas {$art_shape} position-00">
      <div class="image-1">
        <img class="small-canvas" src="{$default_img}" alt="Thumbnail main">
      </div>
      <div class="image-2">
        <img class="small-canvas" src="{$second_img}" alt="Thumbnail second">
      </div>
    </section>

    <section class="room {$art_shape} position-01">
      <div class="room-art-position">
        <div class="room-art">
          <div class="art-frame"></div>
          <div class="art-image">
            <img class="artwork"
                 src="{$second_img|escape:'htmlall':'UTF-8'}"
                 alt="Product artwork">
          </div>
        </div>
      </div>
    </section>

    <section class="room {$art_shape} position-02">
      <div class="room-art-position">
        <div class="room-art">
          <div class="art-frame"></div>
          <div class="art-image">
            <img class="artwork"
                 src="{$second_img|escape:'htmlall':'UTF-8'}"
                 alt="Product artwork">
          </div>
        </div>
      </div>
    </section>

    <section class="room {$art_shape} position-03">
      <div class="room-art-position">
        <div class="room-art">
          <div class="art-frame"></div>
          <div class="art-image">
            <img class="artwork"
                 src="{$second_img|escape:'htmlall':'UTF-8'}"
                 alt="Product artwork">
          </div>
        </div>
      </div>
    </section>

    <section class="room {$art_shape} position-04">
      <div class="room-art-position">
        <div class="room-art">
          <div class="art-frame"></div>
          <div class="art-image">
            <img class="artwork"
                 src="{$second_img|escape:'htmlall':'UTF-8'}"
                 alt="Product artwork">
          </div>
        </div>
      </div>
    </section>

  </div>

</section>

{* -------------------------------------------------------- *}
{*  Product Options / Right column
{* -------------------------------------------------------- *}

<section id="product-Options" class="product-Options-box {$piece} {$final_classes} {$art_code}">

  <div class="product-actions js-product-actions">
    {block name='product_buy'}
      <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh">
        <input type="hidden" name="token" value="{$static_token}">
        <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id">
        <input type="hidden" name="id_customization" value="{$product.id_customization}" id="product_customization_id"
               class="js-product-customization-id">

        {block name='product_variants'}
          {include file='catalog/_partials/product-variants.tpl'}
        {/block}

        {block name='product_custom_options'}
          <div class="PreviewOptions">
            <h3>Preview</h3>
            <div class="Preview-container {$class_04}">
              <div>
                <span>
                  Classic Dark Wood Floating Frame
                  <br>[2" Thick] {$class_02}
                </span>
              </div>
            </div>
            this is option preview
          </div>
        {/block}

        {block name='product_prices'}
          {include file='catalog/_partials/product-prices.tpl'}
        {/block}

        {block name='product_add_to_cart'}
          {include file='catalog/_partials/product-add-to-cart.tpl'}
        {/block}

        {block name='sideway_sale_notice'}
          {include file='catalog/_partials/sideway_sale_notice.tpl'}
        {/block}

        {block name='product_extra_trust'}
          {include file='catalog/_partials/product_extra_trust.tpl'}
        {/block}

        {block name='product_additional_info'}
          {include file='catalog/_partials/product-additional-info.tpl'}
        {/block}

        {block name='product_refresh'}{/block}
      </form>
    {/block}
  </div>

  <div class="product-information">
    {block name='hook_display_reassurance'}
      {hook h='displayReassurance'}
    {/block}
  </div>

</section>

{* -------------------------------------------------------- *}
{*  Description box using the same classes
{* -------------------------------------------------------- *}

<section id="description" class="product-description-box {$piece} {$final_classes} {$art_code}">
  <h2>
    <span id="discription-title">Fine Art Print</span>
  </h2>

  <div id="description-canvas">
    <strong>{$product.name}</strong>
    {hook h='artistlink' product=$product}
    In How Far Is A Light Year? by Alexander Grahovsky paper art print is printed on a heavyweight,
    textured fine art paper and includes a white border to surround the art, which allows a nice touch when
    using your own frame. If you are selecting a frame for your fine art paper print, a premium Acrylite
    clear-coat is applied to the plexiglass to reduce glare and still provide a crystal clear view of the
    artwork. Your choice of hardwood frame (black or white with matte lacquer finish, natural wood, or mottled
    gold with textured metallic finish) completes the framed paper print - now ready to beautify your home for
    years to come.
  </div>

  <div id="description-fine-art-paper">
    <strong>{$product.name}</strong>
    {hook h='artistlink' product=$product}
    In How Far Is A Light Year? by Alexander Grahovsky paper art print is printed on a heavyweight,
    textured fine art paper and includes a white border to surround the art, which allows a nice touch when
    using your own frame. If you are selecting a frame for your fine art paper print, a premium Acrylite
    clear-coat is applied to the plexiglass to reduce glare and still provide a crystal clear view of the
    artwork. Your choice of hardwood frame (black or white with matte lacquer finish, natural wood, or mottled
    gold with textured metallic finish) completes the framed paper print - now ready to beautify your home for
    years to come.
  </div>

  <div id="read-more-container">
    <div id="read-more-content">
      {$product.description nofilter}
    </div>
    <button id="read-more-toggle" class="btn btn-link">Read more</button>
  </div>
</section>
