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
<div class="product-variants js-product-variants">
  {foreach from=$groups key=id_attribute_group item=group}
    <section id="filter-box" class="{if $group.name|lower == 'shape'}shape{/if}">

      {if $group.name|lower == 'shape'}
      {elseif  $group.name|lower == 'type'}
        <h3 class="filter-title">Choose Product Type</h3>
        <ul id="filter-type">

          {if $group.group_type == 'radio'}
            {foreach from=$group.attributes key=id_attribute item=group_attribute}
              <li id="option-box" class="input-container float-xs-left {if $group_attribute.selected} selected{/if}">
                <input class="input-radio" type="radio" data-product-attribute="{$id_attribute_group}"
                  name="group[{$id_attribute_group}]" value="{$id_attribute}" title="{$group_attribute.name}"
                  {if $group_attribute.selected} checked="checked" {/if}>
                <div class="option-cover">
                  {* {if strtolower($group_attribute.name) == 'canvas'}
                    <img src="{$product.images[0].bySize.product_thumb_vertical.url}" alt="">
                  {else}
                    <img src="{$product.images[1].bySize.product_thumb_vertical.url}" alt="">
                  {/if} *}
                  {* Determine the selected shape from Group 5 (Shape) *}
                  {assign var='shape' value='vertical'} {* default fallback *}
                  {foreach from=$groups[5].attributes key=id_attribute item=attribute}
                    {if $attribute.selected}
                      {assign var='shape' value=$attribute.name}
                    {/if}
                  {/foreach}

                  {* Determine thumb key based on shape *}
                  {if $shape == 'square'}
                    {assign var='thumb_key' value='product_thumb_square'}
                  {elseif $shape == 'horizontal'}
                    {assign var='thumb_key' value='product_thumb_horizontal'}
                  {else}
                    {assign var='thumb_key' value='product_thumb_vertical'}

                  {/if}


                  {* Determine image index: 0 = canvas, 1 = fine-art-paper *}
                  {assign var='img_index' value=1}
                  {if strtolower($group_attribute.name) == 'canvas'}
                    {assign var='img_index' value=0}
                  {/if}

                  {* Safe fallback for URL *}
                  {assign var='thumb_url' value=$product.images[$img_index].bySize[$thumb_key].url|default:''}

                  {if $thumb_url}
                    <img src="{$thumb_url}" alt="">
                  {else}
                    {* Ultimate fallback: use main cover image *}
                    <img src="{$product.cover.bySize.product_thumb.url}" alt="">
                  {/if}


                  {* Determine image index: 0 = canvas, 1 = fine-art-paper
                  {assign var='img_index' value=0}
                  {assign var='thumb_url' value=$product.images[$img_index].bySize[$thumb_key].url|default:''}

                  {if strtolower($group_attribute.name) == 'canvas'}
                    <img src="{$product.cover.bySize.[$thumb_key].url}" alt="">
                    {else}
                    {if $thumb_url}
                      <img src="{$thumb_url}" alt="">
                    {else}


                    {/if}
<img src="{$product.cover.bySize.product_thumb.url}" alt="">

                  
                  {/if} *}


                  {* <img src="{$product.images[1].bySize.product_thumb_vertical.url}" alt=""> *}

                  <span>
                    {$group_attribute.name|replace:'_':' '|replace:'-':' '|capitalize}
                  </span>

                </div>
              </li>
            {/foreach}
          {/if}
        </ul>

      {elseif  $group.name|lower == 'size'}

        <h3 class="filter-title">Choose a Size (inches)</h3>
        <ul id="filter-size">

          {if $group.group_type == 'color'}
            {foreach from=$group.attributes key=id_attribute item=group_attribute}
              <li id="option-box" class="input-container float-xs-left {if $group_attribute.selected} selected{/if}">
                <input class="input-radio" type="radio" data-product-attribute="{$id_attribute_group}"
                  name="group[{$id_attribute_group}]" value="{$id_attribute}" title="{$group_attribute.name}"
                  {if $group_attribute.selected} checked="checked" {/if}>
                <div class="option-cover">

                  <span class="two-line">
                    {$group_attribute.name|regex_replace:'/^(\S+)\s+(.+)$/':'$1<br>$2' nofilter}
                  </span>
                </div>
                {* <span>{$group_attribute.name}</span> *}
              </li>
            {/foreach}
          {/if}
        </ul>


      {elseif  $group.name|lower == 'frame'}
        <h3 class="filter-title">Choose Frame</h3>
        <ul id="filter-frame">

          {if $group.group_type == 'color'}
            {foreach from=$group.attributes key=id_attribute item=group_attribute}
              <li id="option-box" class="input-container float-xs-left {if $group_attribute.selected} selected{/if}">
                <input class="input-color" type="radio" data-product-attribute="{$id_attribute_group}"
                  name="group[{$id_attribute_group}]" value="{$id_attribute}" title="{$group_attribute.name}"
                  {if $group_attribute.selected} checked="checked" {/if}>
                <div
                  class="option-cover {if $group_attribute.texture}color texture{elseif $group_attribute.html_color_code}color{/if}"
                  {if $group_attribute.texture} style="background-image: url({$group_attribute.texture})"
                  {elseif $group_attribute.html_color_code} style="background-color: {$group_attribute.html_color_code}" 
                  {/if}>
                </div>
                <div class="frame-label">

                  <span>{$group_attribute.name}</span>
                </div>
              </li>
            {/foreach}
          {/if}
        </ul>

      {/if}
    </section>
  {/foreach}



</div>