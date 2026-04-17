{*
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
*}

{if $have_li}
<li data-id-column="{$column.id_column|intval}" class="mm_columns_li item{$column.id_column|intval} column_size_{$column.column_size|intval} {if $column.is_breaker}mm_breaker{/if}" data-obj="column">
{/if}
<div class="mm_buttons">
    <span class="mm_column_delete" title="{l s='Delete column' mod='ets_megamenu'}">{l s='Delete' mod='ets_megamenu'}</span>
    <span class="mm_duplicate" title="{l s='Duplicate column' mod='ets_megamenu'}">{l s='Duplicate' mod='ets_megamenu'}</span>
    <span class="mm_column_edit" title="{l s='Edit column' mod='ets_megamenu'}">{l s='Edit' mod='ets_megamenu'}</span>
    <div class="mm_add_block btn btn-default" data-id-column="{$column.id_column|intval}" title="{l s='Add block' mod='ets_megamenu'}">{l s='Add block' mod='ets_megamenu'}</div>
</div>
<ul class="mm_blocks_ul">
    {if isset($column.blocks) && $column.blocks}
        {foreach from=$column.blocks item='block'}
            <li data-id-block="{$block.id_block|intval}" class="mm_blocks_li {if !$block.enabled}mm_disabled{/if} item{$block.id_block|intval}" data-obj="block">
                {Module::getInstanceByName('ets_megamenu')->hookDisplayMMItemBlock(['block'=>$block]) nofilter}
            </li>
        {/foreach}
    {/if}
</ul>
{if $have_li}
    </li>
{/if}