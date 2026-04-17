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



 
<header id="js-product-list-header">

  {if isset($category.image) && !empty($category.image.large.url)}

    {if !empty($category.image.large.sources.avif)}
      <source srcset="{$category.image.large.sources.avif}" type="image/avif">
    {/if}
    {if !empty($category.image.large.sources.webp)}
      <source srcset="{$category.image.large.sources.webp}" type="image/webp">
    {/if}
    <img class="cover-image" src="{$category.image.bySize.category_default_new.url}"
      alt="{if !empty($category.image.legend)}{$category.image.legend}{else}{$category.name}{/if}" loading="lazy">
  {/if}

  <div id="category-header">
    <h1 class="category-title">
      {$category.name|escape:'html':'UTF-8'}
      <span> - ART Collection
      </span>
    </h1>
  </div>

</header>

<section id="description" class="product-description-box">
  {* <h2>
     <span id=discription-title>
       Fine Art Print</span>
   </h2> *}

  <div id="description-canvas">

    {$category.description nofilter}
  </div>
  <!-- Read More Section -->
  <div id="read-more-container">
    <div id="read-more-content">
      {* {$category.description nofilter} *}
      {* {$category.description nofilter} *}
    </div>
    <button id="read-more-toggle" class="btn btn-link">Read more</button>
  </div>
</section>