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
<script src="{$JsLybraryPath|escape:'htmlall':'UTF-8'}vue.global.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="{$JsLybraryPath|escape:'htmlall':'UTF-8'}axios.min.js"></script>
<script src="{$mainJsPath|escape:'htmlall':'UTF-8'}" type="module"></script> {* HTML comment, no escape necessary *}


<div id="app" data-v-app="" class="mailchimp-pro-content-container">
	{include file="../config/navbar.tpl"}
{literal}	
    <div class="row">
        <div class="col-xs-12">
            <div class="panel">
                <div class="panel-heading">
                    <span class="panel-heading-icon-container">
                        <i class="material-icons icon-done">&#xe164;</i>
                        {/literal}{l s='Queue worker' mod='mailchimppro'}{literal}
                    </span>
                </div>
				<div id="queue-jobs">
					<p :class="(initialProductsToSync > 0 && !remainingProductsToSync) ? 'sync-completed alert alert-success' : (syncCompleted && remainingProductsToSync ? 'sync-completed alert alert-warning' : '')">
						{/literal}{l s='Products:' mod='mailchimppro'}{literal}
						<span v-cloak v-if="!jobsCleared">
							<strong v-text="initialProductsToSync"></strong> / <strong v-text="initialProductsToSync-remainingProductsToSync"></strong>
						</span>
						<span v-else>
							<strong>0 / 0</strong>
						</span>
					</p>
					<p :class="(initialCustomersToSync > 0 && !remainingCustomersToSync) ? 'sync-completed alert alert-success' : (syncCompleted && remainingCustomersToSync ? 'sync-completed alert alert-warning' : '')">
						{/literal}{l s='Customers:' mod='mailchimppro'}{literal}
						<span v-cloak v-if="!jobsCleared">
							<strong v-text="initialCustomersToSync"></strong> / <strong v-text="initialCustomersToSync-remainingCustomersToSync"></strong>
						</span>
						<span v-else>
							<strong>0 / 0</strong>
						</span>
					</p>
					<p :class="(initialCartRulesToSync > 0 && !remainingCartRulesToSync) ? 'sync-completed alert alert-success' : (syncCompleted && remainingCartRulesToSync ? 'sync-completed alert alert-warning' : '')">
						{/literal}{l s='Cart rules:' mod='mailchimppro'}{literal}
						<span v-cloak v-if="!jobsCleared">
							<strong v-text="initialCartRulesToSync"></strong> / <strong v-text="initialCartRulesToSync-remainingCartRulesToSync"></strong>
						</span>
						<span v-else>
							<strong>0 / 0</strong>
						</span>
					</p>
					<p :class="(initialOrdersToSync > 0 && !remainingOrdersToSync) ? 'sync-completed alert alert-success' : (syncCompleted && remainingOrdersToSync ? 'sync-completed alert alert-warning' : '')">
						{/literal}{l s='Orders:' mod='mailchimppro'}{literal}
						<span v-cloak v-if="!jobsCleared">
							<strong v-text="initialOrdersToSync"></strong> / <strong v-text="initialOrdersToSync-remainingOrdersToSync"></strong>
						</span>
						<span v-else>
							<strong>0 / 0</strong>
						</span>
					</p>
                    <p :class="(initialCartsToSync > 0 && !remainingCartsToSync) ? 'sync-completed alert alert-success' : (syncCompleted && remainingCartsToSync ? 'sync-completed alert alert-warning' : '')">
						{/literal}{l s='Carts:' mod='mailchimppro'}{literal}
						<span v-cloak v-if="!jobsCleared">
							<strong v-text="initialCartsToSync"></strong> / <strong v-text="initialCartsToSync-remainingCartsToSync"></strong>
						</span>
						<span v-else>
							<strong>0 / 0</strong>
						</span>
					</p>
					<p :class="(initialNewsletterSubscribersToSync > 0 && !remainingNewsletterSubscribersToSync) ? 'sync-completed alert alert-success' : (syncCompleted && remainingNewsletterSubscribersToSync ? 'sync-completed alert alert-warning' : '')">
						{/literal}{l s='Newsletter subscribers:' mod='mailchimppro'}{literal}
						<span v-cloak v-if="!jobsCleared">
							<strong v-text="initialNewsletterSubscribersToSync"></strong> / <strong v-text="initialNewsletterSubscribersToSync-remainingNewsletterSubscribersToSync"></strong>
						</span>
						<span v-else>
							<strong>0 / 0</strong>
						</span>
					</p>

                    <p :class="(initialMergeTagPromoCodeToSync > 0 && !remainingMergeTagPromoCodeToSync) ? 'sync-completed alert alert-success' : (syncCompleted && remainingMergeTagPromoCodeToSync ? 'sync-completed alert alert-warning' : '')">
                        {/literal}{l s='Merge field values:' mod='mailchimppro'}{literal}
                        <span v-cloak v-if="!jobsCleared">
                            <strong v-text="initialMergeTagPromoCodeToSync"></strong> / <strong v-text="initialMergeTagPromoCodeToSync-remainingMergeTagPromoCodeToSync"></strong>
                        </span>
                        <span v-else>
                            <strong>0 / 0</strong>
                        </span>
                    </p>

				</div>
				
				<hr>
				
                <p>
                    {/literal}{l s='Number of jobs available:' mod='mailchimppro'}{literal}
                    <strong v-cloak>{{numberOfJobsAvailable}}</strong>
                </p>
                <p v-if="numberOfJobsAvailable > 0">
                    {/literal}{l s='Estimated remaining:' mod='mailchimppro'}{literal}
                    <strong v-cloak>{{convertMsToMinutesSeconds(avgResponseTime*(numberOfJobsAvailable))}}</strong>
                </p>
                <p>
                    {/literal}{l s='Number of jobs in flight:' mod='mailchimppro'}{literal}
                    <strong v-cloak>{{numberOfJobsInFlight}}</strong>
                </p>

                <div class="progress" v-if="runWorker">
                    <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" :style="{ width: ( jobsCompleted *100)/originalNumberOfJobs + '%' }">
                    </div>
                </div>
				
				<hr>
				
                <div class="queue-actions-container">
                    <div v-cloak v-if="!originalNumberOfJobs" class="alert alert-warning">
                        <strong>{/literal}{l s='There is no queue to sync with the Mailchimp!' mod='mailchimppro'}{literal}</strong>
                    </div>
                    
                    <div v-cloak v-if="syncCompleted" class="alert alert-success">
                        <strong>{/literal}{l s='Sync with the Mailchimp completed!' mod='mailchimppro'}{literal}</strong>
                    </div>
                    
                    <div v-cloak v-if="jobsCleared" class="alert alert-success">
                        <strong>{/literal}{l s='Jobs cleared successfully!' mod='mailchimppro'}{literal}</strong>
                    </div>
                    
                    <div v-if="numberOfJobsAvailable" class="queue-action-buttons-container">
                        <button v-on:click="runWorker = !runWorker" class="btn btn-primary">
                            <span v-if="!runWorker">
                                <i class="icon icon-play" aria-hidden="true"></i>
                                {/literal}{l s='Start sync' mod='mailchimppro'}{literal}
                            </span>
                            <span v-cloak v-if="runWorker">
                                <i class="icon icon-pause" aria-hidden="true"></i>
                                {/literal}{l s='Stop sync' mod='mailchimppro'}{literal}
                            </span>
                        </button>
                        <button v-on:click="clearJobs" class="btn btn-default" :class="{ disabled: runWorker }">
                            <i class="icon icon-trash" aria-hidden="true"></i>
                            {/literal}{l s='Clear jobs' mod='mailchimppro'}{literal}
                        </button>
                    </div>
                </div>
                
                <hr>
                
                {/literal}
                <div v-cloak class="last-synced-object-ids-container">
                    <h4 class="last-synced-object-ids">{l s='Last synced object ID\'s:' mod='mailchimppro'}</h4>
                    <ul>
                        <li>{l s='Product id:' mod='mailchimppro'} <b v-text="lastSyncedProductId ?? '–'"></b></li>
                        <li>{l s='Customer id:' mod='mailchimppro'} <b v-text="lastSyncedCustomerId ?? '–'"></b></li>
                        <li>{l s='Cart rule id:' mod='mailchimppro'} <b v-text="lastSyncedCartRuleId ?? '–'"></b></li>
                        <li>{l s='Order id:' mod='mailchimppro'} <b v-text="lastSyncedOrderId ?? '–'"></b></li>
                        <li>{l s='Cart id:' mod='mailchimppro'} <b v-text="lastSyncedCartId ?? '–'"></b></li>
                        <li>{l s='Newsletter subscriber id:' mod='mailchimppro'} <b v-text="lastSyncedNewsletterSubscriberId ?? '–'"></b></li>
                    </ul>
                </div>
                {literal}
            </div>
        </div>
    </div>
{/literal}	
</div>


<style>
    [v-cloak],
	[v-cloak] > * {
        display: none;
    }

    [v-cloak]::before {
        content: " ";
        display: block;
        position: absolute;
        width: 80px;
        height: 80px;
        background-image: url(/modules/mailchimppro/views/img/loader.svg);
        background-size: cover;
        left: 50%;
        top: 50%;
    }
</style>