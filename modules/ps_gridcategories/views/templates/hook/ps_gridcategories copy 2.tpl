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

                    {if isset($gc_category.id_category)}
                      {if isset($gc_category.extra_image) && $gc_category.extra_image}
                        {* Use custom extra image if available *}
                        <img
                          class="gc-gridcategories-img"
                          src="{$gc_link->getMediaLink("{$smarty.const._THEME_CAT_DIR_}{$gc_category.extra_image}")|escape:'html':'UTF-8'}"
                          alt="{$gc_category.name|escape:'html':'UTF-8'}"
                          loading="lazy"
                        />
                      {else}
                        {* Fallback: standard category image type *}
                        <img
                          class="gc-gridcategories-img"
                          src="{$gc_link->getCatImageLink($gc_category.link_rewrite, $gc_category.id_category, 'product_thumb_square')|escape:'html':'UTF-8'}"
                          alt="{$gc_category.name|escape:'html':'UTF-8'}"
                          loading="lazy"
                        />
                      {/if}
                    {/if}

                    {* <span class="gc-gridcategories-name">
                      {$gc_category.name|truncate:25:'...'|escape:'html':'UTF-8'}
                    </span> *}
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
