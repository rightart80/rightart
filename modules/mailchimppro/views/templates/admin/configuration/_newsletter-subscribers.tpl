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
 <div v-cloak v-if="validApiKey" class="panel panel-newsletter-subscribers" v-show="currentPage === 'newsletter-subscribers'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-envelope-open  la-2x"></i>
            {l s='Newsletter subscribers' mod='mailchimppro'}
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
        <h2>{l s='Sync newsletter subscribers' mod='mailchimppro'}</h2>
        <div class="form-group">
            <div class="clearfix"></div>
            <div class="btn-group large" role="group">
                <button type="button" class="btn " v-on:click="syncNewsletterSubscribers = true"
                        :class="syncNewsletterSubscribers == true ? 'btn-primary' : 'btn-default'">
                    {l s='Yes' mod='mailchimppro'}
                </button>
                <button type="button" class="btn " v-on:click="syncNewsletterSubscribers = false"
                        :class="syncNewsletterSubscribers == false ? 'btn-primary' : 'btn-default'">
                    {l s='No' mod='mailchimppro'}
                </button>
            </div>
        </div>
        <hr>
        <div :class="syncNewsletterSubscribers == true ? 'sync-settings-container' : 'sync-settings-container no-sync-type'">
            <h2>{l s='Filter newsletter subscribers to sync' mod='mailchimppro'}</h2>
            <div class="form-group">
                <label>{l s='By subscribtion date' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                <span>{l s='Only (from):' mod='mailchimppro'} </span>
                <div class="btn-group" role="group">
                    <button type="button" class="btn" v-on:click="newsletterSubscriberSyncFilterPeriod = 'onlyNew'"
                            :class="[newsletterSubscriberSyncFilterPeriod === 'onlyNew' ? 'btn-primary' : 'btn-default']">
                        {l s='New subscribers' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn" v-on:click="newsletterSubscriberSyncFilterPeriod = '-1 months'"
                            :class="[newsletterSubscriberSyncFilterPeriod === '-1 months' ? 'btn-primary' : 'btn-default']">
                        {l s='Last 1 month' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn" v-on:click="newsletterSubscriberSyncFilterPeriod = '-3 months'"
                            :class="[newsletterSubscriberSyncFilterPeriod === '-3 months' ? 'btn-primary' : 'btn-default']">
                        {l s='Last 3 months' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn" v-on:click="newsletterSubscriberSyncFilterPeriod = '-6 months'"
                            :class="[newsletterSubscriberSyncFilterPeriod === '-6 months' ? 'btn-primary' : 'btn-default']">
                        {l s='Last 6 months' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn" v-on:click="newsletterSubscriberSyncFilterPeriod = '-1 year'"
                            :class="[newsletterSubscriberSyncFilterPeriod === '-1 year' ? 'btn-primary' : 'btn-default']">
                        {l s='Last 1 year' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn" v-on:click="newsletterSubscriberSyncFilterPeriod = 'all'"
                            :class="[newsletterSubscriberSyncFilterPeriod === 'all' ? 'btn-primary' : 'btn-default']">
                        {l s='All' mod='mailchimppro'}
                    </button>
                </div>
                <div class="clearfix"></div>
                <ul class="form-text text-muted list-unstyled" style="margin-top: 10px">
                    <li>
                        <b>{l s='Only new subscribers' mod='mailchimppro'}:</b>
                        {l s='Subscribers opted in afer the synchronization will be added to Mailchimp' mod='mailchimppro'}
                    </li>
                    <li>
                        <b>{l s='Only from last 1 month' mod='mailchimppro'}:</b>
                        {l s='Subscribers opted in from the last 1 months and the new newsletter subscribers will be added to Mailchimp' mod='mailchimppro'}
                    </li>
                    <li>
                        <b>{l s='Only from last 3 month' mod='mailchimppro'}:</b>
                        {l s='Subscribers opted in from the last 3 months and the new newsletter subscribers will be added to Mailchimp' mod='mailchimppro'}
                    </li>
                    <li>
                        <b>{l s='Only from last 6 month' mod='mailchimppro'}:</b>
                        {l s='Subscribers opted in from the last 6 months and the new newsletter subscribers will be added to Mailchimp' mod='mailchimppro'}
                    </li>
                    <li>
                        <b>{l s='Only from last 1 year' mod='mailchimppro'}:</b>
                        {l s='Subscribers opted in from the last 1 year and the new newsletter subscribers will be added to Mailchimp' mod='mailchimppro'}
                    </li>
                    <li>
                        <b>{l s='All' mod='mailchimppro'}:</b>
                        {l s='All newsletter subscribers will be added to Mailchimp' mod='mailchimppro'}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>