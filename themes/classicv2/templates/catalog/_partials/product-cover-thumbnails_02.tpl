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

 {$image.bySize.large_default.url}
 {$image.bySize.large_default.url}
 {$image.bySize.large_default.url}
 {$image.bySize.large_default.url}
 {$image.bySize.large_default.url}
 {$image.bySize.large_default.url}
 {$image.bySize.large_default.url}
 {$image.bySize.large_default.url}
 {$image.bySize.large_default.url}

 {foreach from=$groups[5].attributes item=attribute key=attribute_id}
   {if $attribute.selected == true}
     {assign var="selected_shape" value=$attribute.name}
   {/if}
 {/foreach}

 {foreach from=$groups[6].attributes item=attribute key=attribute_id}
   {if $attribute.selected == true}
     {assign var="selected_type" value=$attribute.name}
   {/if}
 {/foreach}

 {foreach from=$groups[7].attributes item=attribute key=attribute_id}
   {if $attribute.selected == true}
     {assign var="selected_size" value=$attribute.name}
   {/if}
 {/foreach}

 {foreach from=$groups[8].attributes item=attribute key=attribute_id}
   {if $attribute.selected == true}
     {assign var="selected_frame" value=$attribute.name}
   {/if}
 {/foreach}

 {* Set class_01 with "shape-" prefix if selected_shape is not empty, otherwise empty *}
 {* Set class_01 with "shape-" prefix if selected_shape is not empty, otherwise empty *}
 {if $selected_shape != ""}
   {assign var="class_01" value="shape-"|cat:$selected_shape|lower|replace:" ":"-"}
 {else}
   {assign var="class_01" value=""}
 {/if}
 {* Set class_02 with "type-" prefix if selected_type is not empty *}
 {assign var="class_02" value="type-"|cat:$selected_type|default:""|lower|replace:" ":"-"}

 {* Set class_03 with "size-" prefix if selected_size is not empty *}
 {assign var="class_03" value="size-"|cat:$selected_size|default:""|lower|replace:" ":"-"}

 {* Set class_04 with "frame-" prefix if selected_frame is not empty; otherwise, don't set *}
 {if $selected_frame != ""}
   {assign var="class_04" value="frame-"|cat:$selected_frame|lower|replace:" ":"-"}
 {else}
   {assign var="class_04" value=""}
 {/if}




 {* Concatenate the valid classes into one string, ensuring no extra spaces *}
 {assign var="final_classes" value=""}
 {if $class_01 != ""}{assign var="final_classes" value=$final_classes|cat:$class_01|cat:" "}{/if}
 {if $class_02 != ""}{assign var="final_classes" value=$final_classes|cat:$class_02|cat:" "}{/if}
 {if $class_03 != ""}{assign var="final_classes" value=$final_classes|cat:$class_03|cat:" "}{/if}
 {if $class_04 != ""}{assign var="final_classes" value=$final_classes|cat:$class_04}{/if}

 {* Trim the final_classes to remove leading/trailing spaces *}
 {assign var="final_classes" value=$final_classes|trim}



 <div class="images-container-check js-images-container {$final_classes}">
   {block name='product_cover'}
     <div class="product-cover js-thumb-selected">
       {if $product.default_image}
         <picture>
           {if !empty($product.default_image.bySize.large_default.sources.avif)}
           <source srcset="{$product.default_image.bySize.large_default.sources.avif}" type="image/avif">{/if}
           {if !empty($product.default_image.bySize.large_default.sources.webp)}
           <source srcset="{$product.default_image.bySize.large_default.sources.webp}" type="image/webp">{/if}
           <img class="js-qv-product-cover img-fluid" src="{$product.default_image.bySize.large_default.url}"
             {if !empty($product.default_image.legend)} alt="{$product.default_image.legend}"
             title="{$product.default_image.legend}" {else} alt="{$product.name}" 
             {/if} loading="lazy"
             width="{$product.default_image.bySize.large_default.width}"
             height="{$product.default_image.bySize.large_default.height}">
         </picture>
         <div class="layer hidden-sm-down" data-toggle="modal" data-target="#product-modal">
           <i class="material-icons zoom-in">search</i>
         </div>
       {else}
         <picture>
           {if !empty($urls.no_picture_image.bySize.large_default.sources.avif)}
           <source srcset="{$urls.no_picture_image.bySize.large_default.sources.avif}" type="image/avif">{/if}
           {if !empty($urls.no_picture_image.bySize.large_default.sources.webp)}
           <source srcset="{$urls.no_picture_image.bySize.large_default.sources.webp}" type="image/webp">{/if}
           <img class="img-fluid" src="{$urls.no_picture_image.bySize.large_default.url}" loading="lazy"
             width="{$urls.no_picture_image.bySize.large_default.width}"
             height="{$urls.no_picture_image.bySize.large_default.height}">
         </picture>
       {/if}
     </div>
   {/block}

   {block name='product_images'}

     <div class="js-qv-mask mask">
       <ul class="product-images js-qv-product-images">
         {foreach from=$product.images item=image}
           <li class="thumb-container js-thumb-container">
             <picture>
               {if !empty($image.bySize.small_default.sources.avif)}
               <source srcset="{$image.bySize.small_default.sources.avif}" type="image/avif">{/if}
               {if !empty($image.bySize.small_default.sources.webp)}
               <source srcset="{$image.bySize.small_default.sources.webp}" type="image/webp">{/if}
               <img
                 class="thumb js-thumb {if $image.id_image == $product.default_image.id_image} selected js-thumb-selected {/if}"
                 data-image-medium-src="{$image.bySize.medium_default.url}"
                 {if !empty($image.bySize.medium_default.sources)}data-image-medium-sources="{$image.bySize.medium_default.sources|@json_encode}"
                 {/if} data-image-large-src="{$image.bySize.large_default.url}"
                 {if !empty($image.bySize.large_default.sources)}data-image-large-sources="{$image.bySize.large_default.sources|@json_encode}"
                   {/if} src="{$image.bySize.small_default.url}" {if !empty($image.legend)} alt="{$image.legend}"
                 title="{$image.legend}" {else} alt="{$product.name}" 
                 {/if} loading="lazy"
                 width="{$product.default_image.bySize.small_default.width}"
                 height="{$product.default_image.bySize.small_default.height}">
             </picture>
           </li>
         {/foreach}
       </ul>
     </div>
   {/block}
   {hook h='displayAfterProductThumbs' product=$product}
</div>