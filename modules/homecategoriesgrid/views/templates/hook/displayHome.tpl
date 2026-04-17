{if isset($homecategoriesgrid_items) && $homecategoriesgrid_items|@count > 0}
  <div class="home-categories-grid-wrapper">

    <h2 class="products-section-title text-uppercas">
      Spark Your Imagination</h2>
    <div class="home-categories-grid">
      {foreach from=$homecategoriesgrid_items item=item}
        <a href="{$item.link}" class="home-categories-grid__item" title="{$item.name|escape:'html':'UTF-8'}">
          <img src="{$item.image}" alt="{$item.name|escape:'html':'UTF-8'}" loading="lazy"
            class="home-categories-grid__image" />
        </a>
      {/foreach}
    </div>
  </div>
{/if}