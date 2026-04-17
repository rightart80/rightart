{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *}

{assign var="selected_shape" value=""}
{assign var="selected_type" value=""}
{assign var="selected_size" value=""}
{assign var="selected_frame" value=""}

{foreach from=$groups item=group}
  {if $group.name|lower == 'shape'}
    {foreach from=$group.attributes item=attribute}
      {if isset($attribute.selected) && $attribute.selected}
        {assign var="selected_shape" value=$attribute.name}
      {/if}
    {/foreach}
  {/if}
{/foreach}

{foreach from=$groups item=group}
  {if $group.name|lower == 'type'}
    {foreach from=$group.attributes item=attribute}
      {if isset($attribute.selected) && $attribute.selected}
        {assign var="selected_type" value=$attribute.name}
      {/if}
    {/foreach}
  {/if}
{/foreach}

{foreach from=$groups item=group}
  {if $group.name|lower == 'size'}
    {foreach from=$group.attributes item=attribute}
      {if isset($attribute.selected) && $attribute.selected}
        {assign var="selected_size" value=$attribute.name}
      {/if}
    {/foreach}
  {/if}
{/foreach}

{foreach from=$groups item=group}
  {if $group.name|lower == 'frame'}
    {foreach from=$group.attributes item=attribute}
      {if isset($attribute.selected) && $attribute.selected}
        {assign var="selected_frame" value=$attribute.name}
      {/if}
    {/foreach}
  {/if}
{/foreach}

{if $selected_shape != ""}
{assign var="class_01" value="shape-"|cat:$selected_shape|lower|replace:" ":"-"}
{else}
{assign var="class_01" value=""}
{/if}

{if $selected_type != ""}
{assign var="class_02" value="type-"|cat:$selected_type|lower|replace:" ":"-"}
{else}
{assign var="class_02" value=""}
{/if}

{if $selected_size != ""}
{assign var="class_03" value="size-"|cat:$selected_size|lower|replace:" ":"-"}
{else}
{assign var="class_03" value=""}
{/if}

{if $selected_frame != ""}
{assign var="class_04" value="frame-"|cat:$selected_frame|lower|replace:" ":"-"}
{else}
{assign var="class_04" value=""}
{/if}

{assign var="final_classes" value=""}
{if $class_01 != ""}{assign var="final_classes" value=$final_classes|cat:$class_01|cat:" "}{/if}
{if $class_02 != ""}{assign var="final_classes" value=$final_classes|cat:$class_02|cat:" "}{/if}
{if $class_03 != ""}{assign var="final_classes" value=$final_classes|cat:$class_03|cat:" "}{/if}
{if $class_04 != ""}{assign var="final_classes" value=$final_classes|cat:$class_04}{/if}

{assign var="final_classes" value=$final_classes|trim}

{assign var="art_code" value=$selected_shape|lower|replace:" ":"-"|cat:"-"|cat:$selected_type|lower|replace:" ":"-"|cat:"-"|cat:$selected_size|lower|replace:" ":"-"|cat:"-"|cat:$selected_frame|lower|replace:" ":"-"}

{assign var="art_shape" value=$selected_shape|lower|replace:" ":"-"}

{if $selected_size == "3-PIECE"}
{assign var="piece" value="pc3"}
{else}
{assign var="piece" value="pc1"}
{/if}

<section id="product-images" class="product-images-box {$piece} {$final_classes} {$art_code}">

<div id="product-large-image" class="large-image">

<section class="canvas square p-01">
<div class="image-1">
<img class="big-canvas" src="{$product.default_image.bySize.large_default_new.url}">
</div>

<div class="image-2">
<img class="big-canvas" src="{$product.images[1].bySize.large_default_new.url|default:$product.default_image.bySize.large_default_new.url}">
</div>
</section>

<section class="room">
<div class="room-art-position">
<div class="room-art">

<div class="art-frame"></div>

<div class="art-image">
<img class="artwork"
src="{$product.images[1].bySize.large_default_new.url|default:$product.default_image.bySize.large_default_new.url|escape:'htmlall':'UTF-8'}">
</div>

</div>
</div>
</section>

</div>

<div id="product-thumbnails" class="thumbnails">

<section class="canvas {$art_shape} position-00">

<div class="image-1">
<img class="small-canvas" src="{$product.default_image.bySize.large_default_new.url}">
</div>

<div class="image-2">
<img class="small-canvas"
src="{$product.images[1].bySize.large_default_new.url|default:$product.default_image.bySize.large_default_new.url}">
</div>

</section>

{for $i=1 to 4}

<section class="room {$art_shape} position-0{$i}">

<div class="room-art-position">

<div class="room-art">

<div class="art-frame"></div>

<div class="art-image">
<img class="artwork"
src="{$product.images[1].bySize.large_default_new.url|default:$product.default_image.bySize.large_default_new.url|escape:'htmlall':'UTF-8'}">
</div>

</div>

</div>

</section>

{/for}

</div>

</section>