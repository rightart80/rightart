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
{* {if !empty($subcategories)}
  {if (isset($display_subcategories) && $display_subcategories eq 1) || !isset($display_subcategories) }
    <div id="subcategories" class="card card-block">
      <h2 class="subcategory-heading">{l s='Subcategories' d='Shop.Theme.Category'}</h2>

      <ul class="subcategories-list">
        {foreach from=$subcategories item=subcategory}
          <li>
            <div class="subcategory-image">
              <a href="{$subcategory.url}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="img">
                {if !empty($subcategory.image.large.url)}
                  <picture>
                    {if !empty($subcategory.image.large.sources.avif)}<source srcset="{$subcategory.image.large.sources.avif}" type="image/avif">{/if}
                    {if !empty($subcategory.image.large.sources.webp)}<source srcset="{$subcategory.image.large.sources.webp}" type="image/webp">{/if}
                    <img
                      class="img-fluid"
                      src="{$subcategory.image.large.url}"
                      alt="{$subcategory.name|escape:'html':'UTF-8'}"
                      loading="lazy"
                      width="{$subcategory.image.large.width}"
                      height="{$subcategory.image.large.height}"/>
                  </picture>
                {/if}
              </a>
            </div>

            <h5>
              <a class="subcategory-name" href="{$subcategory.url}">
                {$subcategory.name|truncate:25:'...'|escape:'html':'UTF-8'}
              </a>
            </h5>
            {if $subcategory.description}
              <div class="cat_desc">{$subcategory.description|unescape:'html' nofilter}</div>
            {/if}
          </li>
        {/foreach}
      </ul>
    </div>
  {/if}
{/if} *}




{* 
{if !empty($subcategories)}
  {if (isset($display_subcategories) && $display_subcategories eq 1) || !isset($display_subcategories)}
    <div id="subcategories" class="card card-block">
      <h2 class="subcategory-heading">
        {l s='Subcategories' d='Shop.Theme.Category'}
      </h2>

      <ul class="subcategories-list" id="subcategories-list">
        {foreach from=$subcategories item=subcategory name=subloop}
          <li class="subcategory-item{if $smarty.foreach.subloop.index >= 8} is-hidden{/if}">
            <div class="subcategory-image">
              <a href="{$subcategory.url}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="img">
                {if !empty($subcategory.image.large.url)}
                  <picture>
                    {if !empty($subcategory.image.large.sources.avif)}
                      <source srcset="{$subcategory.image.large.sources.avif}" type="image/avif">
                    {/if}
                    {if !empty($subcategory.image.large.sources.webp)}
                      <source srcset="{$subcategory.image.large.sources.webp}" type="image/webp">
                    {/if}
                    <img
                      class="img-fluid"
                      src="{$subcategory.image.large.url}"
                      alt="{$subcategory.name|escape:'html':'UTF-8'}"
                      loading="lazy"
                      width="{$subcategory.image.large.width}"
                      height="{$subcategory.image.large.height}"
                    />
                  </picture>
                {/if}
              </a>
            </div>

            <h5>
              <a class="subcategory-name" href="{$subcategory.url}">
                {$subcategory.name|truncate:25:'...'|escape:'html':'UTF-8'}
              </a>
            </h5>

            {if $subcategory.description}
              <div class="cat_desc">
                {$subcategory.description|unescape:'html' nofilter}
              </div>
            {/if}
          </li>
        {/foreach}
      </ul>

      {if count($subcategories) > 8}
        <div class="subcategories-load-more-wrap">
          <button
            type="button"
            id="load-more-subcategories"
            class="btn btn-secondary"
          >
            {l s='Load more categories' d='Shop.Theme.Category'}
          </button>
        </div>
      {/if}
    </div>
  {/if}
{/if} *}


{* 
{if !empty($subcategories)}
  {if (isset($display_subcategories) && $display_subcategories eq 1) || !isset($display_subcategories)}
    <div id="subcategories" class="card card-block">
      <h2 class="subcategory-heading">
        <span> Discover More
        </span>
        {$category.name|escape:'html':'UTF-8'}
        <span> Categories
        </span>
      </h2>

      <ul class="subcategories-list" id="subcategories-list">
        {foreach from=$subcategories item=subcategory name=subloop}
          {assign var=idx value=$smarty.foreach.subloop.iteration} 
          <li class="subcategory-item{if $idx > 8} is-hidden{/if}">
            <div class="subcategory-image">
              <a href="{$subcategory.url}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="img">
                {if !empty($subcategory.image.large.url)}
                  <picture>
                    {if !empty($subcategory.image.large.sources.avif)}
                      <source srcset="{$subcategory.image.large.sources.avif}" type="image/avif">
                    {/if}
                    {if !empty($subcategory.image.large.sources.webp)}
                      <source srcset="{$subcategory.image.large.sources.webp}" type="image/webp">
                    {/if}
                    <img class="img-fluid" src="{$subcategory.image.large.url}" alt="{$subcategory.name|escape:'html':'UTF-8'}"
                      loading="lazy" width="{$subcategory.image.large.width}" height="{$subcategory.image.large.height}" />
                  </picture>
                {/if}
              </a>
            </div>

            <h5>
              <a class="subcategory-name" href="{$subcategory.url}">
                {$subcategory.name|truncate:25:'...'|escape:'html':'UTF-8'}
              </a>
            </h5>

            {if $subcategory.description}
              <div class="cat_desc">
                {$subcategory.description|unescape:'html' nofilter}
              </div>
            {/if}
          </li>
        {/foreach}
      </ul>

      {if count($subcategories) > 8}
        <div class="subcategories-load-more-wrap">
          <button type="button" id="load-more-subcategories" class="btn btn-secondary">
            {l s='Load more categories' d='Shop.Theme.Category'}
          </button>
        </div>
      {/if}
    </div>
  {/if}
{/if}
*}
{* 

{if !empty($subcategories)}
  {if (isset($display_subcategories) && $display_subcategories eq 1) || !isset($display_subcategories)}
    <div id="subcategories" class="card card-block">

      <div class="subcategory-heading-wrap">
        <h2 class="subcategory-heading">
          <span>Discover More </span>
          {$category.name|escape:'html':'UTF-8'}
          <span> Categories</span>
        </h2>

        {if count($subcategories) > 0}
          <button
            type="button"
            id="toggle-subcategories"
            class="subcategory-toggle-btn"
            data-label-show="{l s='Show categories' d='Shop.Theme.Category'}"
            data-label-hide="{l s='Hide categories' d='Shop.Theme.Category'}"
            aria-expanded="true"
          >
            {l s='Hide categories' d='Shop.Theme.Category'}
          </button>
        {/if}
      </div>

      <ul class="subcategories-list" id="subcategories-list">
        {foreach from=$subcategories item=subcategory name=subloop}
          {assign var=idx value=$smarty.foreach.subloop.iteration} 
          <li class="subcategory-item{if $idx > 8} is-hidden{/if}">
            <div class="subcategory-image">
              <a href="{$subcategory.url}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="img">
                {if !empty($subcategory.image.large.url)}
                  <picture>
                    {if !empty($subcategory.image.large.sources.avif)}
                      <source srcset="{$subcategory.image.large.sources.avif}" type="image/avif">
                    {/if}
                    {if !empty($subcategory.image.large.sources.webp)}
                      <source srcset="{$subcategory.image.large.sources.webp}" type="image/webp">
                    {/if}
                    <img
                      class="img-fluid"
                      src="{$subcategory.image.large.url}"
                      alt="{$subcategory.name|escape:'html':'UTF-8'}"
                      loading="lazy"
                      width="{$subcategory.image.large.width}"
                      height="{$subcategory.image.large.height}"
                    />
                  </picture>
                {/if}
              </a>
            </div>

            <h5>
              <a class="subcategory-name" href="{$subcategory.url}">
                {$subcategory.name|truncate:25:'...'|escape:'html':'UTF-8'}
              </a>
            </h5>

            {if $subcategory.description}
              <div class="cat_desc">
                {$subcategory.description|unescape:'html' nofilter}
              </div>
            {/if}
          </li>
        {/foreach}
      </ul>

      {if count($subcategories) > 8}
        <div class="subcategories-load-more-wrap">
          <button
            type="button"
            id="load-more-subcategories"
            class="btn btn-secondary"
          >
            {l s='Load more categories' d='Shop.Theme.Category'}
          </button>
        </div>
      {/if}
    </div>
  {/if}
{/if} *}

{*
{if !empty($subcategories)}
  {if (isset($display_subcategories) && $display_subcategories eq 1) || !isset($display_subcategories)}
    <div id="subcategories" class="card card-block subcategories-container-parent">

      <div class="subcategory-heading-wrap">
        <h2 class="subcategory-heading">
          <span>Discover More </span>
          {$category.name|escape:'html':'UTF-8'}
          <span> Categories</span>
          <span id="subcategories-expander" class="subcategories-expander opened"
            title="{l s='Toggle categories' d='Shop.Theme.Category'}" aria-expanded="true" role="button"></span>
        </h2>



      </div>

      <ul class="subcategories-list" id="subcategories-list">
        {foreach from=$subcategories item=subcategory name=subloop}
          {assign var=idx value=$smarty.foreach.subloop.iteration} 
          <li class="subcategory-item{if $idx > 4} is-hidden{/if}">
            <div class="subcategory-image">
              <a href="{$subcategory.url}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="subcategory-url">
                {if !empty($subcategory.image.large.url)}
                  {if !empty($subcategory.image.large.sources.avif)}
                    <source srcset="{$subcategory.image.large.sources.avif}" type="image/avif">
                  {/if}
                  {if !empty($subcategory.image.large.sources.webp)}
                    <source srcset="{$subcategory.image.large.sources.webp}" type="image/webp">
                  {/if}
                  <img class="img-fluid" src="{$subcategory.image.large.url}" alt="{$subcategory.name|escape:'html':'UTF-8'}"
                    loading="lazy" width="{$subcategory.image.large.width}" height="{$subcategory.image.large.height}" />
                {/if}
                <span class="subcategory-name">
                  {$subcategory.name|truncate:25:'...'|escape:'html':'UTF-8'}

                </span>
              </a>
            </div>



            {if $subcategory.description}
              <div class="cat_desc">
                {$subcategory.description|unescape:'html' nofilter}
              </div>
            {/if}
          </li>
        {/foreach}
      </ul>

      {if count($subcategories) > 4}
        <div class="subcategories-load-more-wrap">
          <button type="button" id="load-more-subcategories" class="btn btn-secondary">
            {l s='Load more' d='Shop.Theme.Category'}
          </button>
        </div>
      {/if}
    </div>
  {/if}
{/if}
*}


{* 
{if !empty($subcategories)}
  {if (isset($display_subcategories) && $display_subcategories eq 1) || !isset($display_subcategories)}
    <div id="subcategories" class="card card-block subcategories-container-parent">
      <div class="subcategory-heading-wrap">
        <h2 class="subcategory-heading">
          <span>Discover More </span>
          {$category.name|escape:'html':'UTF-8'}
          <span> Categories</span>
          <span id="subcategories-expander"
                class="subcategories-expander opened"
                title="{l s='Toggle categories' d='Shop.Theme.Category'}"
                aria-expanded="true"
                role="button">
          </span>
        </h2>
      </div>

      <ul class="subcategories-list" id="subcategories-list">
        {foreach from=$subcategories item=subcategory name=subloop}
          {assign var=idx value=$smarty.foreach.subloop.iteration}
          <li class="subcategory-item{if $idx > 4} is-hidden{/if}">
            <div class="subcategory-image">
              <a href="{$subcategory.url}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="subcategory-url">
                {if !empty($subcategory.image.large.url)}
                  <img class="img-fluid"
                       src="{$subcategory.image.large.url}"
                       alt="{$subcategory.name|escape:'html':'UTF-8'}"
                       loading="lazy"
                       width="{$subcategory.image.large.width}"
                       height="{$subcategory.image.large.height}" />
                {/if}
                <span class="subcategory-name">
                  {$subcategory.name|truncate:25:'...'|escape:'html':'UTF-8'}
                </span>
              </a>
            </div>
            {if $subcategory.description}
              <div class="cat_desc">
                {$subcategory.description|unescape:'html' nofilter}
              </div>
            {/if}
          </li>
        {/foreach}
      </ul>

      {if count($subcategories) > 4}
        <div class="subcategories-load-more-wrap">
          <button type="button" id="load-more-subcategories" class="btn btn-secondary">
            {l s='Load more' d='Shop.Theme.Category'}
          </button>
        </div>
      {/if}
    </div>
  {/if}
{/if} *}



{if !empty($subcategories)}
  {if (isset($display_subcategories) && $display_subcategories eq 1) || !isset($display_subcategories)}
    <section id="subcategories" class="card card-block subcategories-container-parent">
      <div class="subcategory-heading-wrap">
        <h2 class="subcategory-heading">
          <span>Discover More </span>
          {$category.name|escape:'html':'UTF-8'}
          <span> Categories</span>

          <span id="subcategories-expander" class="subcategories-expander opened"
            title="{l s='Toggle categories' d='Shop.Theme.Category'}" aria-controls="subcategories-list"
            aria-expanded="true" role="button">
          </span>
        </h2>
      </div>
      

      <ul class="subcategories-list" id="subcategories-list">
        {foreach from=$subcategories item=subcategory name=subloop}
          {assign var=idx value=$smarty.foreach.subloop.iteration}
          <li class="subcategory-item{if $idx > 4} is-hidden{/if}">
            <div class="subcategory-image">
              <a href="{$subcategory.url}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="subcategory-url">
                {if !empty($subcategory.image.large.url)}
                  <img class="img-fluid" src="{$subcategory.image.bySize.category_default_new.url}" alt="{$subcategory.name|escape:'html':'UTF-8'}"
                    loading="lazy" width="{$subcategory.image.large.width}" height="{$subcategory.image.large.height}" />
                {/if}
                <span class="subcategory-name">
                  {$subcategory.name|truncate:25:'...'|escape:'html':'UTF-8'}
                </span>
              </a>
            </div>
            {if $subcategory.description}
              <div class="cat_desc">
                {$subcategory.description|unescape:'html' nofilter}
              </div>
            {/if}
          </li>
        {/foreach}
      </ul>
      {if count($subcategories) > 4}
        <div class="subcategories-load-more-wrap">
          <button type="button" id="load-more-subcategories" class="btn btn-secondary" aria-controls="subcategories-list">
            {l s='Load more' d='Shop.Theme.Category'}
          </button>
        </div>
      {/if}
      </section>

      {debug}
    {/if}
{/if}


{if !empty($related_categories)}
  <section id="subcategories" class="card card-block subcategories-container-parent">
    <div class="subcategory-heading-wrap">
      <h2 class="subcategory-heading">
        <span>Related </span>
        {$category.name|escape:'html':'UTF-8'}
        <span> Categories</span>

        <span id="subcategories-expander" class="subcategories-expander opened"
          title="{l s='Toggle categories' d='Shop.Theme.Category'}"
          aria-controls="subcategories-list"
          aria-expanded="true"
          role="button">
        </span>
      </h2>
    </div>

    <ul class="subcategories-list" id="subcategories-list">
      {foreach from=$related_categories item=subcategory name=subloop}
        {assign var=idx value=$smarty.foreach.subloop.iteration}
        <li class="subcategory-item{if $idx > 4} is-hidden{/if}">
          <div class="subcategory-image">
            <a href="{$subcategory.link}" title="{$subcategory.name|escape:'html':'UTF-8'}" class="subcategory-url">
              <span class="subcategory-name">
                {$subcategory.name|truncate:25:'...'|escape:'html':'UTF-8'}
              </span>
            </a>
          </div>
        </li>
      {/foreach}
    </ul>

    {if count($related_categories) > 4}
      <div class="subcategories-load-more-wrap">
        <button type="button" id="load-more-subcategories" class="btn btn-secondary" aria-controls="subcategories-list">
          {l s='Load more' d='Shop.Theme.Category'}
        </button>
      </div>
    {/if}
  </section>
{/if}