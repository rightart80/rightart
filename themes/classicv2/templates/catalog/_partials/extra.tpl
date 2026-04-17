  <section class="select-filter-type">
    {foreach from=$groups key=id_attribute_group item=group}
      {if $group.name|lower == 'type'}
        <h3 class="filter-type-title">Choose Product Type</h3>
        <ul id="filter-type">

          {if $group.group_type == 'radio'}
            {foreach from=$group.attributes key=id_attribute item=group_attribute}
              <li class="input-container float-xs-left">
                <div class="frame-ext">
                  <div class="frame-int">
                    <img src="{$product.images[1].bySize.large_default_new.url}" alt="Second product image">

                  </div>
                  <span>{$group_attribute.name}</span>

                  {* <label>
                    <input class="input-radio" type="radio" data-product-attribute="{$id_attribute_group}"
                      name="group[{$id_attribute_group}]" value="{$id_attribute}" title="{$group_attribute.name}"
                      {if $group_attribute.selected} checked="checked" {/if}> *}

                  </label>
              </li>
            {/foreach}
          {/if}


        </ul>
      {/if}
    {/foreach}

  </section>



   
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
