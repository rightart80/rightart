{if isset($artistline_artists) && $artistline_artists|@count}

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
  <a href="{$link->getCategoryLink($a.id_category)|escape:'html':'UTF-8'}">{$clean_name|replace:'-':' '|lower|capitalize}</a>&nbsp;
</span>
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