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

<div v-cloak v-if="validApiKey" class="panel panel-statistics" v-show="currentPage === 'statistics'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-la-chart-bar la-2x"></i>
            {l s='Statistics' mod='mailchimppro'}
        </span>
        <div class="panel-heading-action"></div>
    </h3>
    <div class="panel-body">
        <h2>{l s='Show Dashboard Stats' mod='mailchimppro'}</h2>
        <div class="form-group">
            <div class="clearfix"></div>
            <div class="btn-group large" role="group">
                <button type="button" class="btn " v-on:click="showDashboardStats = true"
                        :class="showDashboardStats == true ? 'btn-primary' : 'btn-default'">
                    {l s='Yes' mod='mailchimppro'}
                </button>
                <button type="button" class="btn " v-on:click="showDashboardStats = false"
                        :class="showDashboardStats == false ? 'btn-primary' : 'btn-default'">
                    {l s='No' mod='mailchimppro'}
                </button>
            </div>
        </div>
        <hr>
        {if !empty($statistics.total_items) && $statistics.total_items && !empty($statistics.reports)}
            <div class="panel-heading mb-4">
                <span class="panel-heading-icon-container">
                    {l s='Mailchimp campaign reports' mod='mailchimppro'}
                </span>
                <div class="panel-heading-action">
                    <button id="refresh-reports"
                            v-on:click="refreshReports"
                            v-if="validApiKey"
                            title="{l s='Refresh' mod='mailchimppro'}"
                            class="list-toolbar-btn btn btn-success">
                        <i class="las la-sync la-2x"></i>
                        <span>{l s='Refresh' mod='mailchimppro'}</span>
                    </button>
                </div>
            </div>
        {/if}
        <div class="statistics-content-container">{include file="./_statistics-data.tpl"}</div>
    </div>
</div>