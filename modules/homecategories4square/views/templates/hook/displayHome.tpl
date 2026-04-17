{if isset($homecategories4square_items) && $homecategories4square_items|@count > 0}
<div class="home-categories4square-wrapper">
<div class="grid-block-title">
<h2 class="products-section-title text-uppercas">
Find Your Inspiration</h2>
  <div class="home-categories4square-grid">
    {foreach from=$homecategories4square_items item=item}
      <a href="{$item.link}" class="home-categories4square-item" title="{$item.name|escape:'html':'UTF-8'}">
        <img
          src="{$item.image}"
          alt="{$item.name|escape:'html':'UTF-8'}"
          loading="lazy"
          class="home-categories4square-image"
        />
      </a>
    {/foreach}
  </div>
</div>
{/if}
