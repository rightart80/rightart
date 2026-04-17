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
<div class="panel" v-if="currentStep == 3">
    <div class="panel-heading">
        <i class="material-icons" style="width: 20px">
            compare_arrows
        </i>
        {/literal}{l s='Order status mapping' mod='mailchimppro'}{literal}
    </div>


    <div class="form-group">
        <label class="control-label col-lg-3" for="module-mailchimpproconfig-statuses-for-pending">
            {/literal}{l s='Status for pending' mod='mailchimppro'}{literal}
        </label>

        <div class="col-lg-9">
            <select name="module-mailchimpproconfig-statuses-for-pending[]"
                    :size="orderStates.length"
                    v-model="pendingStates"
                    id="module-mailchimpproconfig-statuses-for-pending" multiple="multiple">
                <option v-for="(state, id) in orderStates"
                        :key="id"
                        v-bind:value="id">{{state.text}}</option>
            </select>
        </div>
    </div>
    <div class="clearfix"></div>
    <hr>
    <div class="form-group">
        <label class="control-label col-lg-3" for="module-mailchimpproconfig-statuses-for-refunded">
            {/literal}{l s='Status for refunded' mod='mailchimppro'}{literal}
        </label>
        <div class="col-lg-9">
            <select name="module-mailchimpproconfig-statuses-for-refunded[]"
                    :size="orderStates.length"
                    v-model="refundedStates"
                    id="module-mailchimpproconfig-statuses-for-refunded" multiple="multiple">
                <option v-for="(state, id) in orderStates"
                        :key="id"
                        v-bind:value="id">{{state.text}}</option>
            </select>
        </div>
    </div>
    <div class="clearfix"></div>
    <hr>
    <div class="form-group">
        <label class="control-label col-lg-3" for="module-mailchimpproconfig-statuses-for-cancelled">
            {/literal}{l s='Status for cancelled' mod='mailchimppro'}{literal}
        </label>
        <div class="col-lg-9">
            <select name="module-mailchimpproconfig-statuses-for-cancelled[]"
                    v-model="cancelledStates"
                    :size="orderStates.length"
                    id="module-mailchimpproconfig-statuses-for-cancelled" multiple="multiple">
                <option v-for="(state, id) in orderStates"
                        :key="id"
                        v-bind:value="id">{{state.text}}</option>
            </select>
        </div>
    </div>
    <div class="clearfix"></div>
    <hr>
    <div class="form-group">
        <label class="control-label col-lg-3" for="module-mailchimpproconfig-statuses-for-shipped">
            {/literal}{l s='Status for shipped' mod='mailchimppro'}{literal}
        </label>
        <div class="col-lg-9">
            <select name="module-mailchimpproconfig-statuses-for-shipped[]"
                    v-model="shippedStates"
                    :size="orderStates.length"
                    id="module-mailchimpproconfig-statuses-for-shipped" multiple="multiple">
                <option v-for="(state, id) in orderStates"
                        :key="id"
                        v-bind:value="id">{{state.text}}</option>
            </select>
        </div>
    </div>
    <div class="clearfix"></div>
    <hr>
    <div class="form-group">
        <label class="control-label col-lg-3" for="module-mailchimpproconfig-statuses-for-paid">
            {/literal}{l s='Status for paid' mod='mailchimppro'}{literal}
        </label>
        <div class="col-lg-9">
            <select name="module-mailchimpproconfig-statuses-for-paid[]"
                    v-model="paidStates"
                    :size="orderStates.length"
                    id="module-mailchimpproconfig-statuses-for-paid" multiple="multiple">
                <option v-for="(state, id) in orderStates"
                        :key="id"
                        v-bind:value="id">{{state.text}}</option>
            </select>
        </div>
    </div>
    <div class="clearfix"></div>
    <hr>


    <div class="panel-footer">
        <button class="btn btn-default btn-xs pull-right" v-on:click="proceedToStepFour">
            {/literal}{l s='Next' mod='mailchimppro'}{literal}
        </button>
    </div>
</div>
{/literal}