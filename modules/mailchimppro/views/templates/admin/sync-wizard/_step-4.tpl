{*
 * PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 *}
{literal}
<div class="panel" v-if="currentStep == 4">
    <div class="panel-heading">
        <i class="material-icons" style="width: 20px">
            compare_arrows
        </i>
        {/literal}{l s='Syncing shops' mod='mailchimppro'}{literal}
    </div>
    <div class="row">
        <div class="col-12 text-center">
            <img alt="loading spinner" src="/modules/mailchimppro/views/img/loader.svg" style="max-width: 75px">
            <p>{/literal}{l s='Syncing shops to Mailchimp' mod='mailchimppro'}{literal}</p>
        </div>
    </div>
</div>
{/literal}