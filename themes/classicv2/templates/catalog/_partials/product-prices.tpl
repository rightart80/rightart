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
{if $product.show_price}
  <div class="product-prices js-product-prices">
    {block name='product_discount'}
      {if $product.has_discount}
        <div class="product-discount">
          {hook h='displayProductPriceBlock' product=$product type="old_price"}
          <span class="reg">REG</span>
          <span class="regular-price">{$product.regular_price}</span>

          {if $product.has_discount}
            {if $product.discount_type === 'percentage'}
              <span class="discount discount-amount">
                {assign var="clean_discount" value=$product.discount_amount_to_display|replace:"-":""}
                {l s='Save %amount%' d='Shop.Theme.Catalog' sprintf=['%amount%' => $clean_discount]}
              </span>


            {else}
              <span class="discount discount-amount">
                {l s='Save %amount%' d='Shop.Theme.Catalog' sprintf=['%amount%' => $product.discount_to_display]}
              </span>
            {/if}
          {/if}
        </div>
      {/if}
    {/block}

    {block name='product_price'}
      <div class="final-price h5 {if $product.has_discount}has-discount{/if}">

        <div class="current-price">

          {capture name='custom_price'}{hook h='displayProductPriceBlock' product=$product type='custom_price' hook_origin='product_sheet'}{/capture}
          {if '' !== $smarty.capture.custom_price}
            {$smarty.capture.custom_price nofilter}
          {else}
            {$product.price}
          {/if}


          <div class="tax-shipping-delivery-label">
            {if !$configuration.taxes_enabled}
              {l s='No tax' d='Shop.Theme.Catalog'}
            {elseif $configuration.display_taxes_label}
              {$product.labels.tax_long}
            {/if}
            {hook h='displayProductPriceBlock' product=$product type="price"}
            {* {hook h='displayProductPriceBlock' product=$product type="after_price"} *}
            {if $product.is_virtual	== 0}
              {if $product.additional_delivery_times == 1}
                {if $product.delivery_information}
                  <span class="delivery-information">{$product.delivery_information}</span>
                {/if}
              {elseif $product.additional_delivery_times == 2}
                {if $product.quantity >= $product.quantity_wanted}
                  <span class="delivery-information">{$product.delivery_in_stock}</span>
                  {* Out of stock message should not be displayed if customer can't order the product. *}
                {elseif $product.add_to_cart_url}
                  <span class="delivery-information">{$product.delivery_out_stock}</span>
                {/if}
              {/if}
            {/if}
          </div>
        </div>
                {hook h='displayProductActions' product=$product}

      </div>
    {/block}

  </div>
{/if}