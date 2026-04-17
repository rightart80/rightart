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
 <div v-cloak v-if="validApiKey" class="panel panel-cart-rules" v-show="currentPage === 'vouchers'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-ruler-combined la-2x"></i>
            {l s='Cart rules' mod='mailchimppro'}
        </span>
        <div class="panel-heading-action">
            <button id="desc-attribute_group-new"
                    v-on:click="saveSettings"
                    v-if="validApiKey"
                    title="{l s='Save settings' mod='mailchimppro'}"
                    class="list-toolbar-btn btn btn-success">
                <i class="las la-save la-2x"></i>
				<span>{l s='Save' mod='mailchimppro'}</span>
            </button>
        </div>
    </h3>
    <div class="panel-body">
        <h2>{l s='Sync cart rules' mod='mailchimppro'}</h2>
        <div class="form-group">
            <div class="clearfix"></div>
            <div class="btn-group large" role="group">
                <button type="button" class="btn " v-on:click="syncCartRules = true"
                        :class="syncCartRules == true ? 'btn-primary' : 'btn-default'">
                    {l s='Yes' mod='mailchimppro'}
                </button>
                <button type="button" class="btn " v-on:click="syncCartRules = false"
                        :class="syncCartRules == false ? 'btn-primary' : 'btn-default'">
                    {l s='No' mod='mailchimppro'}
                </button>
            </div>
        </div>
        <hr>
        <div :class="syncCartRules == true ? 'sync-settings-container' : 'sync-settings-container no-sync-type'">
            <h2>{l s='Filter cart rules' mod='mailchimppro'}</h2>
            <div class="form-group">
                <label>{l s='By status' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn "
                            v-on:click="cartRuleSyncFilterStatus = [0,1]"
                            :class="[arrayEquals(cartRuleSyncFilterStatus, [0,1]) ? 'btn-primary' : 'btn-default']">
                        {l s='All' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn "
                            v-on:click="cartRuleSyncFilterStatus = [1]"
                            :class="[arrayEquals(cartRuleSyncFilterStatus, [1]) ? 'btn-primary' : 'btn-default']">
                        {l s='Only active with available quantity' mod='mailchimppro'}
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label>{l s='By expiration' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn "
                            v-on:click="cartRuleSyncFilterExpiration = [0,1]"
                            :class="[arrayEquals(cartRuleSyncFilterExpiration, [0,1]) ? 'btn-primary' : 'btn-default']">
                        {l s='All' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn "
                            v-on:click="cartRuleSyncFilterExpiration = [1]"
                            :class="[arrayEquals(cartRuleSyncFilterExpiration, [1]) ? 'btn-primary' : 'btn-default']">
                        {l s='Only active' mod='mailchimppro'}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>