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

 {* Iterate over the groups and their attributes *}


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
 {if $selected_shape != ""}
   {assign var="class_01" value="shape-"|cat:$selected_shape|lower|replace:" ":"-"}
 {else}
   {assign var="class_01" value=""}
 {/if}



 {* Set class_02 with "type-" prefix if selected_type is not empty *}
 {if $selected_type != ""}
   {assign var="class_02" value="type-"|cat:$selected_type|default:""|lower|replace:" ":"-"}
 {else}
   {assign var="class_02" value=""}
 {/if}


 {* Set class_03 with "size-" prefix if selected_size is not empty *}
 {if $selected_size != ""}
   {assign var="class_03" value="size-"|cat:$selected_size|default:""|lower|replace:" ":"-"}
 {else}
   {assign var="class_03" value=""}
 {/if}



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

 {assign var="art_code" value=$selected_shape|lower|replace:" ":"-"|cat:"-"|cat:$selected_type|default:""|lower|replace:" ":"-"|cat:"-"|cat:$selected_size|default:""|lower|replace:" ":"-"|cat:"-"|cat:$selected_frame|lower|replace:" ":"-"}

 {assign var="art_shape" value=""|cat:$selected_shape|lower|replace:" ":"-"}

 {if $selected_size == "3-PIECE"}
   {assign var="piece" value="pc3"}
 {else}
   {assign var="piece" value="pc1"}
 {/if}




 {* Output the product image section with the correctly formatted classes *}
 <section id="product-images" class="product-images-box {$piece} {$final_classes} {$art_code}">

   {* {block name='product_flags'}

     {include file='catalog/_partials/product-flags.tpl'}

   {/block} *}

   {* product large image *}
   <div id="product-large-image" class="large-image">
     <section class="canvas square p-01">
       <div class="image-1">
         <img class="big-canvas" src="{$product.default_image.bySize.large_default_new.url}">
       </div>
       <div class="image-2">
         <img class="big-canvas" src="{$product.images[1].bySize.large_default_new.url}" alt="Second product image">
       </div>
     </section>
     <section class="room">
       <div class="room-art-position">
         <div class="room-art">
           <div class="art-frame">

           </div>
           <div class="art-image">
             <img class="artwork" src="{$product.images[1].bySize.large_default_new.url|escape:'htmlall':'UTF-8'}"
               alt="Product artwork">
           </div>



         </div>
       </div>
     </section>
   </div>

   {* product thumbnails. *}
   <div id="product-thumbnails" class="thumbnails">
     {* first large-image thumbnails *}
     <section class="canvas {$art_shape} position-00">
       <div class="image-1">
         <img class="small-canvas" src="{$product.default_image.bySize.large_default_new.url}">
       </div>
       <div class="image-2">
         <img class="small-canvas" src="{$product.images[1].bySize.large_default_new.url}" alt="Second product image">
       </div>



       {* <img class="small-canvas" src="{$product.default_image.bySize.large_default_new.url}"> *}
     </section>
     <section class="room {$art_shape} position-01 ">
       <div class="room-art-position">


         <div class="room-art">
           <div class="art-frame">
           </div>

           <div class="art-image">
             <img class="artwork" src="{$product.images[1].bySize.large_default_new.url|escape:'htmlall':'UTF-8'}"
               alt="Product artwork">
           </div>
         </div>


       </div>
     </section>
     <section class="room {$art_shape} position-02">
       <div class="room-art-position">


         <div class="room-art">
           <div class="art-frame">
           </div>

           <div class="art-image">
             <img class="artwork" src="{$product.images[1].bySize.large_default_new.url|escape:'htmlall':'UTF-8'}"
               alt="Product artwork">
           </div>
         </div>
       </div>
     </section>
     <section class="room {$art_shape} position-03">
       <div class="room-art-position">


         <div class="room-art">
           <div class="art-frame">
           </div>

           <div class="art-image">
             <img class="artwork" src="{$product.images[1].bySize.large_default_new.url|escape:'htmlall':'UTF-8'}"
               alt="Product artwork">
           </div>
         </div>
       </div>
     </section>
     <section class="room {$art_shape} position-04">
       <div class="room-art-position">


         <div class="room-art">
           <div class="art-frame">
           </div>

           <div class="art-image">
             <img class="artwork" src="{$product.images[1].bySize.large_default_new.url|escape:'htmlall':'UTF-8'}"
               alt="Product artwork">
           </div>
         </div>
       </div>
     </section>
   </div>






 </section>



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



         {* 
         {block name='product_pack'}
           this is product pack
           {if $packItems}
             <section class="product-pack">
               <p class="h4">{l s='This pack contains' d='Shop.Theme.Catalog'}</p>
               {foreach from=$packItems item="product_pack"}
                 {block name='product_miniature'}
                   {include file='catalog/_partials/miniatures/pack-product.tpl' product=$product_pack showPackProductsPrice=$product.show_price}
                 {/block}
               {/foreach}
             </section>
           {/if}
         {/block} *}
         {* 
         {block name='product_discounts'}
           this is product discounts
           {include file='catalog/_partials/product-discounts.tpl'}
         {/block} *}

         {block name='product_custom_options'}


           <div class="PreviewOptions">
             <h3>Preview</h3>
             <div class="Preview-container {$class_04}">

               <div>
                 <span>
                   Classic Dark Wood Floating Frame
                   <br>[2" Thick] {$class_02}</span>
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

         {* Input to refresh product HTML removed, block kept for compatibility with themes *}
         {block name='product_refresh'}{/block}
       </form>
     {/block}

   </div>
   <div class="product-information">
     {* {block name='product_description_short'}
       <div id="product-description-short-{$product.id}" class="product-description">
         {$product.description_short nofilter}</div>
     {/block} *}

     {* {if $product.is_customizable && count($product.customizations.fields)}
       {block name='product_customization'}
         this is product customization
         {include file="catalog/_partials/product-customization.tpl" customizations=$product.customizations}
       {/block}
     {/if} *}


     {block name='hook_display_reassurance'}
       {hook h='displayReassurance'}
     {/block}

     {* {block name='product_tabs'}
       <div class="tabs">
         <ul class="nav nav-tabs" role="tablist">
           {if $product.description}
             <li class="nav-item">
               <a class="nav-link{if $product.description} active js-product-nav-active{/if}" data-toggle="tab"
                 href="#description" role="tab" aria-controls="description" {if $product.description} aria-selected="true"
                 {/if}>{l s='Description' d='Shop.Theme.Catalog'}</a>
             </li>
           {/if}
           <li class="nav-item">
             <a class="nav-link{if !$product.description} active js-product-nav-active{/if}" data-toggle="tab"
               href="#product-details" role="tab" aria-controls="product-details" {if !$product.description}
               aria-selected="true" {/if}>{l s='Product Details' d='Shop.Theme.Catalog'}</a>
           </li>
           {if $product.attachments}
             <li class="nav-item">
               <a class="nav-link" data-toggle="tab" href="#attachments" role="tab"
                 aria-controls="attachments">{l s='Attachments' d='Shop.Theme.Catalog'}</a>
             </li>
           {/if}
           {foreach from=$product.extraContent item=extra key=extraKey}
             <li class="nav-item">
               <a class="nav-link" data-toggle="tab" href="#extra-{$extraKey}" role="tab"
                 aria-controls="extra-{$extraKey}">{$extra.title}</a>
             </li>
           {/foreach}
         </ul>

         <div class="tab-content" id="tab-content">
           <div class="tab-pane fade in{if $product.description} active js-product-tab-active{/if}" id="description-text"
             role="tabpanel">
             {block name='product_description'}
               <div class="product-description">{$product.description nofilter}</div>
             {/block}
           </div>

           {block name='product_details'}
             {include file='catalog/_partials/product-details.tpl'}
           {/block}

           {block name='product_attachments'}
             {if $product.attachments}
               <div class="tab-pane fade in" id="attachments" role="tabpanel">
                 <section class="product-attachments">
                   <p class="h5 text-uppercase">{l s='Download' d='Shop.Theme.Actions'}</p>
                   {foreach from=$product.attachments item=attachment}
                     <div class="attachment">
                       <h4><a
                           href="{url entity='attachment' params=['id_attachment' => $attachment.id_attachment]}">{$attachment.name}</a>
                       </h4>
                       <p>{$attachment.description}</p>
                       <a href="{url entity='attachment' params=['id_attachment' => $attachment.id_attachment]}">
                         {l s='Download' d='Shop.Theme.Actions'} ({$attachment.file_size_formatted})
                       </a>
                     </div>
                   {/foreach}
                 </section>
               </div>
             {/if}
           {/block}

           {foreach from=$product.extraContent item=extra key=extraKey}
             <div class="tab-pane fade in {$extra.attr.class}" id="extra-{$extraKey}" role="tabpanel"
               {foreach $extra.attr as $key => $val} {$key}="{$val}" {/foreach}>
               {$extra.content nofilter}
             </div>
           {/foreach}
         </div>
       </div>
     {/block} *}
   </div>



 </section>

 <section id="description" class="product-description-box {$piece} {$final_classes} {$art_code}">
 <h2>
 <span id=discription-title>
 Fine Art Print</span>
 </h2>

<div id="description-canvas">
  <strong>{$product.name}</strong>
  {hook h='artistlink' product=$product}
  In How Far Is A Light Year? by Alexander Grahovsky paper art print is printed on a heavyweight, textured fine art paper and includes a white border to surround the art, which allows a nice touch when using your own frame. If you are selecting a frame for your fine art paper print, a premium Acrylite clear-coat is applied to the plexiglass to reduce glare and still provide a crystal clear view of the artwork. Your choice of hardwood frame (black or white with matte lacquer finish, natural wood, or mottled gold with textured metallic finish) completes the framed paper print - now ready to beautify your home for years to come.
</div>


<div id="description-fine-art-paper">
  <strong>{$product.name}</strong>
  {hook h='artistlink' product=$product}
  In How Far Is A Light Year? by Alexander Grahovsky paper art print is printed on a heavyweight, textured fine art paper and includes a white border to surround the art, which allows a nice touch when using your own frame. If you are selecting a frame for your fine art paper print, a premium Acrylite clear-coat is applied to the plexiglass to reduce glare and still provide a crystal clear view of the artwork. Your choice of hardwood frame (black or white with matte lacquer finish, natural wood, or mottled gold with textured metallic finish) completes the framed paper print - now ready to beautify your home for years to come.

</div>
<!-- Read More Section -->
<div id="read-more-container">
  <div id="read-more-content">
  {$product.description nofilter}
  </div>
  <button id="read-more-toggle" class="btn btn-link">Read more</button>
</div>
 </section>



{* ///OLD CODE *}