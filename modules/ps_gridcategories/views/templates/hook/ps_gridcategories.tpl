{* Grid Categories Blocks (multiple) *}
{if isset($gc_blocks) && $gc_blocks|@count > 0}
  {foreach from=$gc_blocks item=gc_block}
    {assign var=gc_parent_category value=$gc_block.parent}
    {assign var=gc_categories value=$gc_block.categories}

    <section id="gc-gridcategories" class="card card-block gc-gridcategories-wrapper">
      {if isset($gc_parent_category) && $gc_categories|@count > 0}
        <div id="gc-gridcategories-block-{$gc_parent_category->id}" class="gc-gridcategories-block block clearfix">
          <h2 class="h2 gc-gridcategories-title text-uppercase">
            {$gc_parent_category->name|escape:'html':'UTF-8'}
          </h2>

          <ul class="gc-gridcategories-list" id="gc-gridcategories-list-{$gc_parent_category->id}">
            {foreach from=$gc_categories item=gc_category name=gc_loop}
              <li class="gc-gridcategories-item">
                <div class="gc-gridcategories-image">
                  <a href="{$gc_link->getCategoryLink($gc_category.id_category)|escape:'html':'UTF-8'}"
                     title="{$gc_category.name|escape:'html':'UTF-8'}"
                     class="gc-gridcategories-link">

                    {if isset($gc_category.grid_image) && $gc_category.grid_image}
                      {* Use custom stored grid image if available (full URL from PHP) *}
                      <img
                        class="gc-gridcategories-img"
                        src="{$gc_category.grid_image|escape:'html':'UTF-8'}"
                        alt="{$gc_category.name|escape:'html':'UTF-8'}"
                        loading="lazy"
                      />
                    {elseif isset($gc_category.id_category)}
                      {* Fallback to default category image *}
                      <img
                        class="gc-gridcategories-img"
                        src="{$gc_link->getCatImageLink($gc_category.link_rewrite, $gc_category.id_category, 'product_thumb_vertical')|escape:'html':'UTF-8'}"
                        alt="{$gc_category.name|escape:'html':'UTF-8'}"
                        loading="lazy"
                      />
                    {/if}

                  </a>
                </div>

                {if isset($gc_category.description) && $gc_category.description}
                  <div class="gc-gridcategories-desc">
                    {$gc_category.description|unescape:'html' nofilter}
                  </div>
                {/if}
              </li>
            {/foreach}
          </ul>
        </div>
      {/if}
    </section>

  {/foreach}
{/if}
