{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<button type="button" class="btn btn-primary compact-toggle type-{$af_btn.type} {if $af_btn.external}external af{else}sticky{/if}">
    {if $af_btn.type != 2}<span class="{$af_btn.icon|escape:'html':'UTF-8'} compact-toggle-icon"></span>{/if}
    {if $af_btn.type != 3}{l s='Filter' mod='amazzingfilter'}{/if}
</button>
{* since 3.3.2 *}
