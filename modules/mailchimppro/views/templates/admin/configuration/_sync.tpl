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
 <div v-cloak v-if="validApiKey" class="panel panel-sync" v-show="currentPage === 'sync'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-save la-2x"></i>
            {l s='Store sync' mod='mailchimppro'}
        </span>
        <div class="panel-heading-action">
            <button id="desc-attribute_group-new"
                    {*v-on:click="saveSettings"*}
                    v-on:click="syncStore"
                    v-if="storeSynced == false"
                    title="{l s='Save settings' mod='mailchimppro'}"
                    {*class="list-toolbar-btn btn btn-success"*}
                    :class="listId ? 'list-toolbar-btn btn btn-success' : 'list-toolbar-btn btn btn-success disabled'"
                    >
                <i class="las la-save la-2x"></i>
                <span>{l s='Save' mod='mailchimppro'}</span>
            </button>
        </div>
    </h3>
    <div class="panel-body">

        {include file="./_audience-lists.tpl"}

        <hr>
        
        <div :class="storeSynced == true ? 'sync-settings-container' : 'sync-settings-container no-sync-type'">
            <div class="form-group">
                <h2>{l s='Objects to sync with Mailchimp' mod='mailchimppro'}</h2>
                <ul id="sync-list" class="list-unstyled">
                    <li class="sync-list-item">
                        <label>
                            <span class="custom-checkbox-container">
                                <input type="checkbox" v-model="syncProducts">
                                <span class="custom-checkbox">
                                    <i class="material-icons icon-done">&#xe876;</i>
                                </span>
                                <span class="custom-checkbox-text">{l s='Sync products' mod='mailchimppro'} <span>{literal}({{numberOfProductsToSync}}){/literal}</span></span>
                            </span>
                        </label>
                        <span class="sync-item-actions" v-if="storeSynced && numberOfProductsToSync > 0">
                            <button type="button"
                                    class="btn btn-sm sync-item-btn"
                                    :disabled="!syncProducts || queueLoadingProducts"
                                    v-on:click="pushSingleTypeToQueue('products')"
                                    v-if="!queuedTypes.products">
                                <i class="las la-spinner la-spin" v-if="queueLoadingProducts"></i>
                                <i class="las la-plus-circle" v-else></i>
                                {l s='Add to queue' mod='mailchimppro'}
                            </button>
                            <span class="sync-item-queued" v-if="queuedTypes.products">
                                <i class="las la-check-circle"></i> {l s='Queued' mod='mailchimppro'}
                            </span>
                        </span>
                    </li>
                    <li class="sync-list-item">
                        <label>
                            <span class="custom-checkbox-container">
                                <input type="checkbox" v-model="syncCustomers">
                                <span class="custom-checkbox">
                                    <i class="material-icons icon-done">&#xe876;</i>
                                </span>
                                <span class="custom-checkbox-text">{l s='Sync customers' mod='mailchimppro'} <span>{literal}({{numberOfCustomersToSync}}){/literal}</span></span>
                            </span>
                        </label>
                        <span class="sync-item-actions" v-if="storeSynced && numberOfCustomersToSync > 0">
                            <button type="button"
                                    class="btn btn-sm sync-item-btn"
                                    :disabled="!syncCustomers || queueLoadingCustomers"
                                    v-on:click="pushSingleTypeToQueue('customers')"
                                    v-if="!queuedTypes.customers">
                                <i class="las la-spinner la-spin" v-if="queueLoadingCustomers"></i>
                                <i class="las la-plus-circle" v-else></i>
                                {l s='Add to queue' mod='mailchimppro'}
                            </button>
                            <span class="sync-item-queued" v-if="queuedTypes.customers">
                                <i class="las la-check-circle"></i> {l s='Queued' mod='mailchimppro'}
                            </span>
                        </span>
                    </li>
                    <li class="sync-list-item">
                        <label>
                            <span class="custom-checkbox-container">
                                <input type="checkbox" v-model="syncCartRules">
                                <span class="custom-checkbox">
                                    <i class="material-icons icon-done">&#xe876;</i>
                                </span>
                                <span class="custom-checkbox-text">{l s='Sync cart rules' mod='mailchimppro'} <span>{literal}({{numberOfCartRulesToSync}}){/literal}</span></span>
                            </span>
                        </label>
                        <span class="sync-item-actions" v-if="storeSynced && numberOfCartRulesToSync > 0">
                            <button type="button"
                                    class="btn btn-sm sync-item-btn"
                                    :disabled="!syncCartRules || queueLoadingCartRules"
                                    v-on:click="pushSingleTypeToQueue('cartRules')"
                                    v-if="!queuedTypes.cartRules">
                                <i class="las la-spinner la-spin" v-if="queueLoadingCartRules"></i>
                                <i class="las la-plus-circle" v-else></i>
                                {l s='Add to queue' mod='mailchimppro'}
                            </button>
                            <span class="sync-item-queued" v-if="queuedTypes.cartRules">
                                <i class="las la-check-circle"></i> {l s='Queued' mod='mailchimppro'}
                            </span>
                        </span>
                    </li>
                    <li class="sync-list-item">
                        <label>
                            <span class="custom-checkbox-container">
                                <input type="checkbox" v-model="syncOrders">
                                <span class="custom-checkbox">
                                    <i class="material-icons icon-done">&#xe876;</i>
                                </span>
                                <span class="custom-checkbox-text">{l s='Sync orders' mod='mailchimppro'} <span>{literal}({{numberOfOrdersToSync}}){/literal}</span></span>
                            </span>
                        </label>
                        <span class="sync-item-actions" v-if="storeSynced && numberOfOrdersToSync > 0">
                            <button type="button"
                                    class="btn btn-sm sync-item-btn"
                                    :disabled="!syncOrders || queueLoadingOrders"
                                    v-on:click="pushSingleTypeToQueue('orders')"
                                    v-if="!queuedTypes.orders">
                                <i class="las la-spinner la-spin" v-if="queueLoadingOrders"></i>
                                <i class="las la-plus-circle" v-else></i>
                                {l s='Add to queue' mod='mailchimppro'}
                            </button>
                            <span class="sync-item-queued" v-if="queuedTypes.orders">
                                <i class="las la-check-circle"></i> {l s='Queued' mod='mailchimppro'}
                            </span>
                        </span>
                    </li>
                    <li class="sync-list-item">
                        <label>
                            <span class="custom-checkbox-container">
                                <input type="checkbox" v-model="syncNewsletterSubscribers">
                                <span class="custom-checkbox">
                                    <i class="material-icons icon-done">&#xe876;</i>
                                </span>
                                <span class="custom-checkbox-text">{l s='Sync newsletter subscribers' mod='mailchimppro'} <span>{literal}({{numberOfNewsletterSubscribersToSync}}){/literal}</span></span>
                            </span>
                        </label>
                        <span class="sync-item-actions" v-if="storeSynced && numberOfNewsletterSubscribersToSync > 0">
                            <button type="button"
                                    class="btn btn-sm sync-item-btn"
                                    :disabled="!syncNewsletterSubscribers || queueLoadingNewsletterSubscribers"
                                    v-on:click="pushSingleTypeToQueue('newsletterSubscribers')"
                                    v-if="!queuedTypes.newsletterSubscribers">
                                <i class="las la-spinner la-spin" v-if="queueLoadingNewsletterSubscribers"></i>
                                <i class="las la-plus-circle" v-else></i>
                                {l s='Add to queue' mod='mailchimppro'}
                            </button>
                            <span class="sync-item-queued" v-if="queuedTypes.newsletterSubscribers">
                                <i class="las la-check-circle"></i> {l s='Queued' mod='mailchimppro'}
                            </span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
		
		<div v-cloak v-if="jobsAddedToQueue" class="alert alert-success">
			<p>
                <strong>{l s='Jobs have been put to the queue; set up the cronjob now!' mod='mailchimppro'}</strong>
            </p>
		</div>
    </div>
    <div v-cloak v-if="storeSynced" class="panel-footer">
        <button type="button" :class="listId ? 'btn btn-primary pull-right' : 'btn btn-primary pull-right disabled'" v-on:click="pushSetupJobsToQueue">
            {l s='Add all to queue' mod='mailchimppro'}
        </button>
    </div>
</div>