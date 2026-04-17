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
<div class="panel" v-if="currentStep == 1">
    <div class="panel-heading">
        <i class="material-icons">
            account_circle
        </i>
        {/literal}{l s='Authentication' mod='mailchimppro'}{literal}
    </div>
    {/literal}
    <label for="api-key" class="hidden">{l s='API key' mod='mailchimppro'}</label>
    <p id="logged-in-as-container" {if empty($apiKey) && empty($mcEmail)}class="hidden"{/if}>
        {l s='Logged in as:' mod='mailchimppro'} <b id="logged-in-as">{$mcEmail|escape:'htmlall':'UTF-8'}</b> {* HTML comment, no escape necessary *}
    </p>
    <input type="hidden" class="form-control" name="api-key" id="api-key"
           placeholder="{l s='API key' mod='mailchimppro'}" required="" value="{$apiKey|escape:'htmlall':'UTF-8'}"> {* HTML comment, no escape necessary *}
    <a class="btn btn-default" id="oauth2-start" v-on:click="oauthStart">
        Log in to Mailchimp
    </a>
    {literal}
    <div class="panel-footer">
        <button class="btn btn-default btn-xs pull-right" v-on:click="proceedToStepTwo">
            {/literal}{l s='Next' mod='mailchimppro'}{literal}
        </button>
    </div>
</div>
{/literal}