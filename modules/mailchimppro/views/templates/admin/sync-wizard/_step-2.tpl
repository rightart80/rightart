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
<div class="panel" v-if="currentStep == 2">
    <div class="panel-heading">
        <i class="material-icons" style="width: 20px">
            list_alt
        </i>
        {/literal}{l s='List select' mod='mailchimppro'}{literal}
    </div>
    <select v-model="selectedList">
        <option v-for="option in availableLists" :value="option.id">
            {{ option.name }}
        </option>
    </select>
    <div class="panel-footer">
        <button class="btn btn-default btn-xs pull-right" v-on:click="proceedToStepThree">
            {/literal}{l s='Next' mod='mailchimppro'}{literal}
        </button>
    </div>
</div>
{/literal}