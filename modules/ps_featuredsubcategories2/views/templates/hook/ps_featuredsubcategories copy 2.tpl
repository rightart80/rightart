<section id="subcategories" class="card card-block feature-category subcategories-container-parent">
{* Featured Subcategories Block *}
{if isset($fsc_parent_category) && $fsc_subcategories|@count > 0}
<div id="featured-subcategories" class="featured-subcategories block clearfix">
  <h2 class="h2 products-section-title text-uppercase">
    {$fsc_parent_category->name|escape:'html':'UTF-8'}
  </h2>

  <ul class="subcategories-list" id="subcategories-list">
    {foreach from=$fsc_subcategories item=subcat name=subloop}
      {assign var=idx value=$smarty.foreach.subloop.iteration}
      <li class="subcategory-item{if $idx > 4} is-hidden{/if}">
        <div class="subcategory-image">
          <a href="{$link->getCategoryLink($subcat.id_category)|escape:'html':'UTF-8'}"
             title="{$subcat.name|escape:'html':'UTF-8'}"
             class="subcategory-url">

            {if isset($subcat.id_category)}
              <img
                class="img-fluid"
                src="{$link->getCatImageLink($subcat.link_rewrite, $subcat.id_category, 'category_default_new')|escape:'html':'UTF-8'}"
                alt="{$subcat.name|escape:'html':'UTF-8'}"
                loading="lazy"
              />
            {/if}

            <span class="subcategory-name">
              {$subcat.name|truncate:25:'...'|escape:'html':'UTF-8'}
            </span>
          </a>
        </div>

        {if isset($subcat.description) && $subcat.description}
          <div class="cat_desc">
            {$subcat.description|unescape:'html' nofilter}
          </div>
        {/if}
      </li>
    {/foreach}
  </ul>
</div>
{/if}

</section>