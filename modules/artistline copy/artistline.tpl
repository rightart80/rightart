{if isset($artistline_artists) && $artistline_artists|@count}

  {*
    First pass: count valid artists (not empty, not 'unknown')
  *}
  {assign var=valid_count value=0}
  {foreach from=$artistline_artists item=a}
    {assign var=clean_name value=$a.name|trim}
    {if $clean_name ne '' && $clean_name|lower ne 'unknown'}
      {assign var=valid_count value=$valid_count+1}
    {/if}
  {/foreach}

  <span id="artist-name">
    {if $valid_count > 0}
      by
      {assign var=i value=0}
      {foreach from=$artistline_artists item=a}
        {assign var=clean_name value=$a.name|trim}
        {if $clean_name ne '' && $clean_name|lower ne 'unknown'}
          {if $i > 0}, {/if}
          <span class="artist-link">
            <a href="{$link->getCategoryLink($a.id_category)|escape:'html':'UTF-8'}">
              {$clean_name|replace:'-':' '|lower|capitalize}
            </a>
          </span>

          {assign var=i value=$i+1}
        {/if}
      {/foreach}
    {/if}

    {if isset($product.reference) && $product.reference|trim != ''}
      <span class="artist-ref">&nbsp;&nbsp;&nbsp;Art Print # {$product.reference|escape:'html':'UTF-8'}</span>
    {/if}
  </span>
{/if}

{* {if isset($artistline_artists) && $artistline_artists|@count}
  <p id="artist-name">
    by
    {foreach from=$artistline_artists item=a name=alist}
      <span id="artist-link">
        <a href="{$link->getCategoryLink($a.id_category)|escape:'html':'UTF-8'}">
          {$a.name|escape:'html':'UTF-8'}
        </a>
        {debug}
      </span>{if not $smarty.foreach.alist.last}, {/if}
    {/foreach}

    {if isset($product.reference) && $product.reference|trim != ''}
  <span class="artist-ref">&nbsp;&nbsp;&nbsp;Art Print # {$product.reference|escape:'html':'UTF-8'}</span>
{/if}

  </p>
{/if} *}