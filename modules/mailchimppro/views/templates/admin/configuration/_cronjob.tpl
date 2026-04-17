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
 <div v-cloak v-if="validApiKey" class="panel panel-cronjob" v-show="currentPage === 'cronjob'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-clock la-2x"></i>
            {l s='Cronjob' mod='mailchimppro'}
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
		<div class="form-group cronjob-information">
			{if $multistore_php_command}
				<div class="alert alert-danger">
					<p>{l s='Until now you have used the PHP cron command, and your Prestashop is working in multi-store configuration. Because of multi-store compatibility, the PHP cron command is disabled. Each stores cron url is different. You need to configure the wget cron URL of every separate store where you want to use the cronjob synchronization.' mod='mailchimppro'}</p>
				</div>
			{/if}
			<div class="alert alert-info">
				<p>{l s='Set up a cronjob to automatically synchronize the data from your shop with Mailchimp, based on the previously defined choices.' mod='mailchimppro'}</p>
			</div>
			<h3 class="modal-title text-info">What's a Cron Job?</h3>
			<p>{l s='Cron jobs are scheduled tasks that the system executes at specific periods or times. An ordinary cron job consists of several straightforward activities that the system does from a script file.' mod='mailchimppro'}</p>
			<div class="cronjob-notes-container alert alert-warning">
				<h4>{l s='Notes you should keep in mind when setting the cronjob:' mod='mailchimppro'}</h4>
				<ul>
					<li>{l s='The recommended frequency for syncing data with Mailchimp is' mod='mailchimppro'} <b>{l s='once per minute' mod='mailchimppro'}</b>.</li>
					<li>
						<div>{l s='Depending on your host, there are many cronjob configuration options; check the links below to learn more about setting a cronjob:' mod='mailchimppro'}</div>
						<ul>
							<li>
								cPanel:
								<a href="https://docs.cpanel.net/cpanel/advanced/cron-jobs/" target="_blank" rel="noreferrer noopener">https://docs.cpanel.net/cpanel/advanced/cron-jobs/</a>
							</li>
							<li>
								Plesk:
								<a href="https://docs.plesk.com/en-US/obsidian/customer-guide/scheduling-tasks.65207/" target="_blank" rel="noreferrer noopener">https://docs.plesk.com/en-US/obsidian/customer-guide/scheduling-tasks.65207/</a>
							</li>
							<li>
								Ubuntu:
								<a href="https://www.digitalocean.com/community/tutorials/how-to-use-cron-to-automate-tasks-ubuntu-1804" target="_blank" rel="noreferrer noopener">https://www.digitalocean.com/community/tutorials/how-to-use-cron-to-automate-tasks-ubuntu-1804</a>
							</li>
							<li>
								Centos:
								<a href="https://www.digitalocean.com/community/tutorials/how-to-use-cron-to-automate-tasks-centos-8" target="_blank" rel="noreferrer noopener">https://www.digitalocean.com/community/tutorials/how-to-use-cron-to-automate-tasks-centos-8</a>
							</li>
						</ul>
					</li>
				</ul>
				<br>
				<p>{l s='You may also get in touch with your hosting company and ask them for assistance if you are having trouble setting up a cronjob.' mod='mailchimppro'}</p>
			</div>
		</div>
		<br><br>
		<div class="form-horizontal cronjob-settings-container">
			<div class="panel-heading">{l s='Settings' mod='mailchimppro'}</div>
			
			{* ****
			<div class="form-group cronjob-log-queue">
				<label class="control-label col-lg-2">{l s='Enable queue log' mod='mailchimppro'}</label>
				<div class="col-lg-8">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="CRONJOB_LOG_QUEUE" id="CRONJOB_LOG_QUEUE_on" value="1" v-model="logQueue">
						<label for="CRONJOB_LOG_QUEUE_on">{l s='Enabled' mod='mailchimppro'}</label>
						<input type="radio" name="CRONJOB_LOG_QUEUE" id="CRONJOB_LOG_QUEUE_off" value="0" v-model="logQueue">
						<label for="CRONJOB_LOG_QUEUE_off">{l s='Disabled' mod='mailchimppro'}</label>
						<a class="slide-button btn"></a>
					</span>
					<p class="help-block">{l s='Recommended to enable this option for testing purposes only!' mod='mailchimppro'}</p>
				</div>
			</div>
			**** *}
			
			<div class="form-group cronjob-queue-step">
				<label class="control-label col-lg-2 required">
					<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Maximum number of requests sent to Mailchimp every time cronjob file run' mod='mailchimppro'}">{l s='Data queue step' mod='mailchimppro'}</span>
				</label>
				<div class="col-lg-5">
					<div class="input-group">
						<input type="text" name="CRONJOB_QUEUE_STEP" id="CRONJOB_QUEUE_STEP" {* value="5" *} class="" required="required" v-model="queueStepRaw">
						<span class="input-group-addon">{l s='request(s)' mod='mailchimppro'}</span>
					</div>
					<p class="help-block">{l s='The requests to be submitted to Mailchimp are checked for in the data queue each time a cronjob is executed. If your server has a limited timeout value, lower this number.' mod='mailchimppro'}</p>
				</div>
			</div>
			
			<div class="form-group cronjob-queue-attempt">
				<label class="control-label col-lg-2 required">{l s='Data queue max-trying times' mod='mailchimppro'}</label>
				<div class="col-lg-5">
					<div class="input-group">
						<input type="text" name="CRONJOB_QUEUE_ATTEMPT" id="CRONJOB_QUEUE_ATTEMPT" {* value="5" *} class="" required="required" v-model="queueAttemptRaw">
						<span class="input-group-addon">{l s='attempt(s)' mod='mailchimppro'}</span>
					</div>
					<p class="help-block">{l s='The times to try to send a request again if it was failed! After that, the data will be deleted from queue.' mod='mailchimppro'}</p>
				</div>
			</div>
			
			<div class="form-group cronjob-url">
				<label class="control-label col-lg-2"></label>
				<div class="col-lg-10">
					<label class="control-label">
						<span class="required">* </span>{l s='Set up a cronjob as below on your server to synchronize the data from your shop with Mailchimp.' mod='mailchimppro'}
					</label>
					
					{if $cronjob_multiStore}

						<div class="alert alert-warning">
		                    <p>{l s='You are using multi-store configuration. The cron URL of each store are different. You need to configure each stores cron URL where you want to use the cronjob synchronization.' mod='mailchimppro'}</p>
		                </div>

						<div><span class="required"></span>{l s='Wget cron URL:' mod='mailchimppro'}</div>

						<em>
							<span class="cronjob-url-path wget">{$cronjobUrlLinkWget|escape:'htmlall':'UTF-8'}</span> {* HTML comment, no escape necessary *}
						</em>
						
					{else}
						
						<div class="php-cron-option"><span class="required">1. </span>{l s='First option with PHP:' mod='mailchimppro'}</div>
						
						<em>
							<span class="cronjob-url-path php">{$cronjobUrlPath|escape:'htmlall':'UTF-8'}</span> {* HTML comment, no escape necessary *}
						</em>
						
						<div><span class="required">2. </span>{l s='Second option with Wget:' mod='mailchimppro'}</div>

						<em>
							<span class="cronjob-url-path wget">{$cronjobUrlLinkWget|escape:'htmlall':'UTF-8'}</span> {* HTML comment, no escape necessary *}
						</em>
						
					{/if}

					<p class="help-block">{l s='In case you don\'t have the possibility to configure the cron job on your server, you can use any third party cronjob service with the wget cron URL.' mod='mailchimppro'}</p>

					<label class="control-label">
						<span class="required">* </span>{l s='Click the button below to manually run the cronjob.' mod='mailchimppro'}
					</label>
					<div class="cronjob-link-container">
						<a id="cronjob-link" class="btn btn-default" href="{$cronjobUrlLink|escape:'quotes':'UTF-8'}" target="_blank" v-on:click="executeCronjob">{l s='Execute cronjob manually' mod='mailchimppro'}</a>
					</div>
				</div>
			</div>
			
			<div class="form-group cronjob-log">
				<label class="control-label col-lg-2">{l s='Save cronjob log' mod='mailchimppro'}</label>
				<div class="col-lg-8">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="CRONJOB_LOG" id="CRONJOB_LOG_on" value="1" v-model="logCronjob">
						<label for="CRONJOB_LOG_on">{l s='Enabled' mod='mailchimppro'}</label>
						<input type="radio" name="CRONJOB_LOG" id="CRONJOB_LOG_off" value="0" v-model="logCronjob">
						<label for="CRONJOB_LOG_off">{l s='Disabled' mod='mailchimppro'}</label>
						<a class="slide-button btn"></a>
					</span>
					<p class="help-block">{l s='Recommended to have this option enabled!' mod='mailchimppro'}</p>
				</div>
			</div>
			
			<div class="form-group cronjob-log-textarea-container">
				<label class="control-label col-lg-2">{l s='Cronjob log' mod='mailchimppro'}</label>
				<div class="col-lg-10">
					<textarea readonly id="CRONJOB_LOG_AREA" name="CRONJOB_LOG_AREA" rows="10" v-model="cronjobLogContent">{if isset($cronjobLog) && $cronjobLog}{$cronjobLog|escape:'htmlall':'UTF-8'}{/if}</textarea> {* HTML comment, no escape necessary *}
					<br>
					<button class="clear-cronjob-log btn btn-default" :class="(cronjobLogContent == '') ? 'disabled' : ''" name="clear-cronjob-log" type="button" v-on:click="clearCronjobLog">
						<i class="icon-trash"></i> {l s='Clear cronjob log' mod='mailchimppro'}
					</button>
                    <br>
				</div>
			</div>
		</div>
		<div class="form-horizontal cronjob-status-container">
			<div class="panel-heading">{l s='Cronjob status' mod='mailchimppro'}</div>
			<div class="alert alert-info">
				<ul>
					<li><p>{l s='The last time Cronjob was executed:' mod='mailchimppro'} <b v-text="lastCronjob ?? '–'"></b></p></li>
					<li><p>{l s='Last Cronjob execution time:' mod='mailchimppro'} <b v-text="lastCronjobExecutionTime ?? '–'"></b></p></li>
					<li><p>{l s='Total remaining data in queue:' mod='mailchimppro'} <b v-text="totalJobs ?? '–'"></b></p></li>
				</ul>
			</div>
			<hr>
            <div class="last-synced-object-ids-container">
                <h4 class="last-synced-object-ids">{l s='Last synced object ID\'s:' mod='mailchimppro'}</h4>
                <ul>
                    <li :class="syncProducts == true ? 'sync-settings-container' : 'sync-settings-container no-sync-type'">{l s='Product id:' mod='mailchimppro'} <b v-text="lastSyncedProductId ?? '–'"></b></li>
                    <li :class="syncCustomers == true ? 'sync-settings-container' : 'sync-settings-container no-sync-type'">{l s='Customer id:' mod='mailchimppro'} <b v-text="lastSyncedCustomerId ?? '–'"></b></li>
                    <li :class="syncCartRules == true ? 'sync-settings-container' : 'sync-settings-container no-sync-type'">{l s='Cart rule id:' mod='mailchimppro'} <b v-text="lastSyncedCartRuleId ?? '–'"></b></li>
                    <li :class="syncOrders == true ? 'sync-settings-container' : 'sync-settings-container no-sync-type'">{l s='Order id:' mod='mailchimppro'} <b v-text="lastSyncedOrderId ?? '–'"></b></li>
                    <li :class="syncCarts == true ? 'sync-settings-container' : 'sync-settings-container no-sync-type'">{l s='Cart id:' mod='mailchimppro'} <b v-text="lastSyncedCartId ?? '–'"></b></li>
                    <li :class="syncNewsletterSubscribers == true ? 'sync-settings-container' : 'sync-settings-container no-sync-type'">{l s='Newsletter subscriber id:' mod='mailchimppro'} <b v-text="lastSyncedNewsletterSubscriberId ?? '–'"></b></li>
                </ul>
            </div>
		</div>	
    </div>
	{* *********
    <div class="panel-footer">
...
    </div>
	********* *}
</div>