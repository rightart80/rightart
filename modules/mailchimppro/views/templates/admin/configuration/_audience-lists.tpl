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

<div class="form-group">
    <h2>{l s='Choose Mailchimp list' mod='mailchimppro'}</h2>
    <div class="listid-select-container">
        {literal}
            <Multiselect
                placeholder="{/literal}{l s='Please select audience list for the current prestashop store' mod='mailchimppro'}{literal}"
                v-model="listId"
                :can-deselect="false"
                :can-clear="false"
                :mode="'single'"
                :options="lists"
                :disabled="(storeSynced == true || storeAlreadySynced == true) ? true : false"
            >
            </Multiselect>
        {/literal}
        <button v-cloak v-if="storeSynced == false" type="button" :class="listId ? 'btn btn-primary' : 'btn btn-primary disabled'" v-on:click="syncStore">
            {l s='Initialize connection' mod='mailchimppro'}
        </button>
    </div>
    <p class="text-muted">
        {l s='The list for a specific store cannot be changed once it has been synchronized with Mailchimp, unless you delete the store from Mailchimp with all the e-commerce data.' mod='mailchimppro'}
    </p>
    <br>
    <div v-cloak v-if="listId && storeSynced == false" class="alert alert-info">
        <p>{l s='Please click on the Initialize connection button.' mod='mailchimppro'}</p>
    </div>
</div>
