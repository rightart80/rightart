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

<div v-cloak v-if="validApiKey" class="panel panel-promo" v-show="currentPage === 'promo'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-percent la-2x"></i>
            {l s='Promo codes' mod='mailchimppro'}
        </span>
        <div class="panel-heading-action">
            <button id="desc-attribute_group-new"
                    v-on:click="setCurrentPage('addNewPromo');populatePromoForm('new')"
                    v-if="validApiKey"
                    title="{l s='Add new' mod='mailchimppro'}"
                    class="list-toolbar-btn btn btn-success">
                <i class="las la-plus-square la-2x"></i>
                <span>{l s='Add new' mod='mailchimppro'}</span>
            </button>
        </div>
    </h3>


    <div class="panel-body">
        <h2>{l s='Promo Code Feature - Override Installation Required' mod='mailchimppro'}</h2>
        <div class="alert alert-info">
            <p>{l s='To enable the Promo Code feature, you need to install the module\'s class overrides. These overrides extend the default PrestaShop Cart and CartRule functionalities in order to support this feature.' mod='mailchimppro'}</p>

            <p>{l s='If the override installation detects a conflict (for example, if another module already overrides the same files), the installation cannot be completed automatically. In this case, the overrides must be merged and installed manually.' mod='mailchimppro'}</p>

            <p>{l s='You can contact our team for assistance, or ask your developer to handle the manual integration. Instructions and code snippets are provided to help with the process.' mod='mailchimppro'}</p>

        </div>
        <div class="form-group">
            <label>{l s='Enable and install overrides:' mod='mailchimppro'}</label>
            <div class="clearfix"></div>
            <div class="btn-group large" role="group">                
                <button type="button" class="btn " v-on:click="promoOverridesEnabled = false"
                        :class="promoOverridesEnabled == false ? 'btn-primary' : 'btn-default'">
                    {l s='No' mod='mailchimppro'}
                </button>
                <button type="button" class="btn " v-on:click="promoOverridesEnabled = true"
                        :class="promoOverridesEnabled == true ? 'btn-primary' : 'btn-default'">
                    {l s='Yes' mod='mailchimppro'}
                </button>
            </div>
        </div>
        
        <!-- Manual installation dialog -->
        <div class="manual-installation-dialog" v-if="showManualInstallationDialog">
            <div class="alert alert-warning">
                <h4>{l s='Manual Override Installation Required' mod='mailchimppro'}</h4>
                <p>{l s='The following override files have conflicts:' mod='mailchimppro'}</p>
                <div v-html="conflictDetailsHtml"></div>
                <p>{l s='Please copy the override files from the module\'s overrides_pending directory to your PrestaShop override directory.' mod='mailchimppro'}</p>
                <div class="alert alert-info">
                    <p>{l s='Instructions:' mod='mailchimppro'}</p>
                    <ol>
                        <li>{l s='Copy the files from:' mod='mailchimppro'} <code>modules/mailchimppro/overrides_pending/</code></li>
                        <li>{l s='Paste them to:' mod='mailchimppro'} <code>override/</code></li>
                        <li>{l s='If the files already exist, you need to manually merge the code.' mod='mailchimppro'}</li>
                        <li>{l s='After installation, clear the PrestaShop cache.' mod='mailchimppro'}</li>
                    </ol>
                </div>
                <p>{l s='Once you have manually installed the overrides, click the button below:' mod='mailchimppro'}</p>
                <button class="btn btn-primary" v-on:click="markOverridesManuallyInstalled">
                    <i class="icon-check"></i> {l s='I have manually installed the overrides' mod='mailchimppro'}
                </button>
            </div>
        </div>
        
        <!-- Success message when overrides are installed -->
        <div class="alert alert-info" v-if="promoOverridesEnabled && promoOverridesAutoInstalled">
            <p><i class="icon-info-circle"></i> {l s='Overrides have been installed successfully.' mod='mailchimppro'}</p>
        </div>
        
        <!-- Information about manually installed overrides -->
        <div class="alert alert-info" v-if="manuallyInstalledOverrides">
            <h4>{l s='Manually Installed Overrides' mod='mailchimppro'}</h4>
            <p><i class="icon-info-circle"></i> {l s='The overrides have been manually installed. If you need to remove them, you will need to do so manually by:' mod='mailchimppro'}</p>
            <ol>
                <li>{l s='Removing or reverting the changes in:' mod='mailchimppro'} <code>override/classes/Cart.php</code> {l s='and' mod='mailchimppro'} <code>override/classes/CartRule.php</code></li>
                <li>{l s='Clearing the PrestaShop cache after removal.' mod='mailchimppro'}</li>
            </ol>
            <p>{l s='Note: Simply disabling the overrides here will not remove the manually installed files.' mod='mailchimppro'}</p>
        </div>


        <div :class="promoOverridesEnabled == true ? 'sync-settings-container panel-body' : 'sync-settings-container no-sync-type panel-body'">
            <table id="promoTable" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>{l s='#Id' mod='mailchimppro'}</th>
                    <th>{l s='Name' mod='mailchimppro'}</th>
                    <th>{l s='Mailchimp MergeTag' mod='mailchimppro'}</th>
                    {*<th>{l s='Campaign id' mod='mailchimppro'}</th>*}
                    <th>{l s='Codes prefix' mod='mailchimppro'}</th>
                    <th>{l s='Codes suffix' mod='mailchimppro'}</th>
                    <th>{l s='Reduction' mod='mailchimppro'}</th>
                    <th>{l s='Status' mod='mailchimppro'}</th>
                    <th>{l s='Expiration' mod='mailchimppro'}</th>
                    <th>{l s='Actions' mod='mailchimppro'}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$promos item=promo}
                    <tr data-id="{$promo.id|escape:'htmlall':'UTF-8'}">
                        <td>{$promo.id|escape:'htmlall':'UTF-8'}</td>
                        <td>{$promo.name|escape:'htmlall':'UTF-8'}</td>
                        <td>{$promo.tag_merge_field_mc|escape:'htmlall':'UTF-8'}</td>
                        {*<td>{$promo.campaign_id|escape:'htmlall':'UTF-8'}</td>*}
                        <td>{$promo.prefix|escape:'htmlall':'UTF-8'}</td>
                        <td>{$promo.suffix|escape:'htmlall':'UTF-8'}</td>
                        <td>{if $promo.reduction}{$promo.reduction|escape:'htmlall':'UTF-8'} {if $promo.reduction_type == 1}%{else}{$currency->sign}{/if}{/if}</td>
                        <td>{if $promo.status == 1}Active{else}Inactive{/if}</td>
                        <td>{$promo.end_date|escape:'htmlall':'UTF-8'}</td>
                        <td>
                            <button class="btn btn-xs btn-default view-promo-group"
                                    v-on:click="viewPromo({$promo.id})" title="View">
                                <i class="icon icon-zoom-in" aria-hidden="true"></i>
                            </button>
                            <button class="btn btn-xs btn-default edit-promo-group"
                                    v-on:click="editPromo({$promo.id})" title="Edit">
                                <i class="icon icon-edit" aria-hidden="true"></i>
                            </button>
                            <button class="btn btn-xs btn-default edit-promo-group"
                                    v-on:click="viewPromoStats({$promo.id})" title="Stats">
                                <i class="icon icon-pie-chart"></i>
                            </button>
                            <button class="btn btn-xs btn-danger delete-promo-group"
                                    v-on:click="deletePromo({$promo.id})" title="Delete">
                                <i class="icon icon-trash" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>

<div v-cloak v-if="validApiKey" class="panel panel-promo panel-add-edit-promo" :class="currentPromoId ? 'panel-edit-promo' : 'panel-add-new-promo'" v-show="currentPage === 'addNewPromo'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-percent la-2x"></i>
            {l s='Promo codes' mod='mailchimppro'}
        </span>
    </h3>

    <div class="panel-body">
        <div class="form-horizontal new-promo-container">
            <div class="panel-heading">
                <span v-show="currentPage === 'addNewPromo' && !currentPromoId">{l s='Add new promo code' mod='mailchimppro'}</span>
                <span v-show="currentPromoId">{l s='Edit promo code' mod='mailchimppro'}</span>
            </div>

            <div class="form-group promo-name-step">
                <label class="control-label col-lg-2 required">{l s='Promo name' mod='mailchimppro'}</label>

                <div class="col-lg-5">
                    <input type="text" name="PROMO_NAME" id="PROMO_NAME" class="" required="required" v-on:blur='validatePromoData($event)'>

                    <p class="help-block">{l s='The name of the promo code.' mod='mailchimppro'}</p>
                </div>
            </div>

            <div class="form-group promo-prefix-step">
                <label class="control-label col-lg-2 required">{l s='Promo prefix' mod='mailchimppro'}</label>

                <div class="col-lg-5">
                    <input type="text" name="PROMO_PREFIX" id="PROMO_PREFIX" class="" required="required" v-on:blur='validatePromoData($event)'>

                    <p class="help-block">{l s='The prefix of the promo code. Will be used as the prefix of the generated promo codes.' mod='mailchimppro'}</p>
                </div>
            </div>

            <div class="form-group promo-suffix-step">
                <label class="control-label col-lg-2">{l s='Promo suffix' mod='mailchimppro'}</label>

                <div class="col-lg-5">
                    <input type="text" name="PROMO_SUFFIX" id="PROMO_SUFFIX" class="">

                    <p class="help-block">{l s='The suffix of the promo code. Will be used as the suffix of the generated promo codes.' mod='mailchimppro'}</p>
                </div>
            </div>

            <div class="form-group promo-expiration-step">
                <label class="control-label col-lg-2 required">{l s='Promo expiration date' mod='mailchimppro'}</label>

                <div class="col-lg-5">
                    <input type="text" class="input-medium" name="PROMO_EXPIRATION" id="PROMO_EXPIRATION" required="required" value="{$defaultDate}">

                    <p class="help-block">{l s='The expiration date of the promo code.' mod='mailchimppro'}</p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2">{l s='Apply a discount' mod='mailchimppro'}</label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="PROMO_REDUCTION_TYPE" id="PROMO_REDUCTION_TYPE_on" value="1">
                        <label for="PROMO_REDUCTION_TYPE_on">{l s='Percent' mod='mailchimppro'}</label>
                        <input type="radio" name="PROMO_REDUCTION_TYPE" id="PROMO_REDUCTION_TYPE_off" value="0" checked>
                        <label for="PROMO_REDUCTION_TYPE_off">{l s='Amount' mod='mailchimppro'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>

            <div class="form-group promo-promo-step">
                <label class="control-label col-lg-2 required">{l s='Promo reduction' mod='mailchimppro'}</label>

                <div class="col-lg-5">
                    <input type="text" name="PROMO_REDUCTION" id="PROMO_REDUCTION" class="" required="required" v-on:blur='validatePromoData($event)'>
                </div>
            </div>

            {*<div class="form-group promo-campain-step">
                <label class="control-label col-lg-2 required">{l s='Mailchimp campain id' mod='mailchimppro'}</label>

                <div class="col-lg-5">
                    <input type="text" name="PROMO_CAMPAIN" id="PROMO_CAMPAIN" class="form-control" required="required">

                    <p class="help-block">{l s='The Mailchimp campain id for the promo code.' mod='mailchimppro'}</p>
                </div>
            </div>*}

            {*<div class="form-group promo-campain-step">
                <label class="control-label col-lg-2 required">{l s='Mailchimp campain id' mod='mailchimppro'}</label>

                <div class="col-lg-5">
                    <input type="text" name="PROMO_CAMPAIN" id="PROMO_CAMPAIN" class="form-control" required="required">

                    <p class="help-block">{l s='The Mailchimp Merge Tag to use in email templates.' mod='mailchimppro'}</p>
                </div>
            </div>*}

            <div class="form-group">
                <label class="control-label col-lg-2">{l s='Promo status' mod='mailchimppro'}</label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="PROMO_STATUS" id="PROMO_STATUS_on" value="1">
                        <label for="PROMO_STATUS_on">{l s='Enabled' mod='mailchimppro'}</label>
                        <input type="radio" name="PROMO_STATUS" id="PROMO_STATUS_off" value="0" checked>
                        <label for="PROMO_STATUS_off">{l s='Disabled' mod='mailchimppro'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>

            <hr class="generate-promo-codes-hr" style="display: none;">
            <div class="generate-promo-codes form-group" style="display: none;">
                <h2 class="">{l s='Generate promo codes' mod='mailchimppro'}</h2>

                {* Commented for future features *}
                {* <div class="form-group">
                    <label for="codeCount" class="control-label col-lg-2">{l s='Number of Codes' mod='mailchimppro'}</label>
                    <div class="col-lg-10">
                        <div class="col-lg-6">
                            <input type="number" id="codeCount" class="form-control" min="1" required>
                        </div>
                    </div>
                </div> *}

                <div class="col-lg-10">
                    <div class="col-lg-12 col-generate-codes" style="display: none;">
                        <button class="btn btn-primary" v-on:click="generateCodes(currentPromoId)">
                            {l s='Generate Codes' mod='mailchimppro'}
                        </button>
                        <p class="help-block">{l s='This button will generate promo codes for each client.' mod='mailchimppro'}</p>
                    </div>
                    <div class="col-lg-10">
                        <div class="col-lg-12">
                            {l s='Expected total number of promo codes:' mod='mailchimppro'} <span class="" id="totalCodes">{$totalCodes}</span>
                        </div>
                        <div class="col-lg-12">
                            {l s='Total generated codes:' mod='mailchimppro'} <span class="" id="totalCount">0</span>
                        </div>
                        <div class="col-lg-12">
                            <div class="progress" style="margin-top: 10px;">
                                <div class="progress-bar"
                                     role="progressbar"
                                     style="width: 0%;"
                                     aria-valuenow="0"
                                     aria-valuemin="0"
                                     aria-valuemax="100"
                                     id="progressBar">
                                    0%
                                </div>
                            </div>
                        </div>
                        <p class="help-block">{l s='This show the progress of the code generation.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>


            {* <hr> *}
            <div class="send-promo-codes form-group hidden">
                <h2 class="">{l s='Send promo codes to Mailchimp' mod='mailchimppro'}</h2>

                <div class="col-lg-10">
                    <div class="col-lg-12">
                        <button class="btn btn-primary" v-on:click="syncPromo(currentPromoId)">
                            {l s='Send Merge Tag' mod='mailchimppro'}
                        </button>
                        <p class="help-block">{l s='This button will create the merge field in which the promo codes will be stored at Mailchimp for each client.' mod='mailchimppro'}</p>
                    </div>                    
                </div>
            </div>


        </div>
    </div>
    <div class="panel-footer">
        <button id="desc-attribute_group-new"
                v-on:click="setCurrentPage('promo');populatePromoForm()"
                v-if="validApiKey"
                title="{l s='Cancel' mod='mailchimppro'}"
                class="btn btn-danger">
            <i class="las la-window-close la-2x"></i>
            <span>{l s='Cancel' mod='mailchimppro'}</span>
        </button>
        <button id="desc-attribute_group-new1"
                v-on:click="currentPromoId ? updatePromo() : addNewPromo()"
                v-if="validApiKey"
                title="{l s='Save' mod='mailchimppro'}"
                class="btn btn-primary pull-right">
            <i class="las la-save la-2x"></i>
            <span>{l s='Save' mod='mailchimppro'}</span>
        </button>
    </div>
</div>

<div v-cloak v-if="validApiKey" class="panel panel-promo panel-promocodes" v-show="currentPage === 'promocodes'" >
    <h2>Promo Codes</h2>
    <button
            class="btn btn-danger"
            style="margin-bottom:10px"
            v-on:click="deleteSelectedPromocodes"
            title="Delete Selected"
            :disabled="selectedPromoCodes.length === 0">
        <i class="icon icon-trash" aria-hidden="true"></i> Delete Selected
    </button>
    <table id="promocodesTable" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>
                    <input type="checkbox" v-on:change="toggleSelectAll" v-model="selectAll">
                </th>
                <th>{l s='#id' mod='mailchimppro'}</th>
                <th>{l s='Promocode' mod='mailchimppro'}</th>
                <th>{l s='Email' mod='mailchimppro'}</th>
                <th>{l s='Status' mod='mailchimppro'}</th>
                <th>{l s='Actions' mod='mailchimppro'}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(code, index) in promoCodes" :key="index">
                <td>
                    <input
                            type="checkbox"
                            v-model="selectedPromoCodes"
                            :value="code.id">
                </td>
                <td v-html="code.id"></td>
                <td v-html="code.code"></td>
                <td v-html="code.email"></td>
                <td v-html="code.status == 1 ? 'Used' : 'Not Used'"></td>
                <td>
                    <button class="btn btn-xs btn-danger delete-promo-group"
                            v-on:click="deletePromocode(code.id)" title="Delete">
                        <i class="icon icon-trash" aria-hidden="true"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
    <button
            v-on:click="setCurrentPage('promo');populatePromoForm()"
            title="{l s='Back' mod='mailchimppro'}"
            class="btn btn-danger">
        <span>{l s='Back' mod='mailchimppro'}</span>
    </button>
</div>

<div v-cloak v-if="validApiKey" class="panel panel-promo panel-promo-stats" v-show="currentPage === 'promoStats'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-percent la-2x"></i>
            {l s='Stats' mod='mailchimppro'}
        </span>
        <div class="panel-heading-action">
            <button id="desc-attribute_group-new" v-on:click="setCurrentPage('promo');"
                title="{l s='Back' mod='mailchimppro'}" class="list-toolbar-btn btn btn-success">
                <i class="las la-undo la-2x"></i>
                <span>{l s='Back' mod='mailchimppro'}</span>
            </button>
        </div>
    </h3>

    <div class="panel-body">
        <h2>{literal}
                {{statsName}}
            {/literal}</h2>

        <div class="stats row">
            <div class="stats_card with-icon chart-block">

                <div id="pieChart">

                </div>

            </div>
            <div class="stats_card with-icon stat-metric-1">


               
                <div class="dlx">
                    
                    <i class="las la-tag"></i>
                    <div>
                        <p class="dlx-text">{l s='All' mod='mailchimppro'}</p>
                        <h3 class="stat-number">{literal}{{statAllCodes}}{/literal}</h3>
                    </div>

                </div>
            </div>
            <div class="stats_card with-icon stat-metric-2">
                
                <div class="dlx">
                    
                    <i class="las la-shopping-cart"></i>
                    <div>
                        <p class="dlx-text">{l s='Used' mod='mailchimppro'}</p>
                        <h3 class="stat-number">{literal}{{statUsedCodes}}{/literal}</h3>

                    </div>
                </div>
            </div>

            <div class="stats_card with-icon stat-metric-3">
                
                <div class="dlx">
                
                    <i class="las la-percent"></i>
                    <div>
                        <p class="dlx-text">{l s='Conversion' mod='mailchimppro'}</p>
                        <h3 class="stat-number">{literal}{{statConversion}}{/literal}</h3>
                    </div>
                </div>
            </div>

            <div class="stats_card with-icon stat-metric-4">
                
                <div class="dlx">
                
                    <i class="las la-wave-square"></i>
                    <div>
                        <p class="dlx-text">{l s='Usage frequency' mod='mailchimppro'}</p>
                        <h3 class="stat-number">{literal}{{statFrequency}}{/literal}</h3>
                    </div>
                    </div>
            </div>
        </div>

        <hr>

        <div class="stats-filter">
            <div class="input-group">
                <input type="text" class="datepicker input-medium" name="statsStart" id="statsStart" required="required"
                    placeholder="{l s='Start date' mod='mailchimppro'}">
            </div>
            <div class="input-group">
                <input type="text" class="datepicker input-medium" name="statsEnd" id="statsEnd" required="required"
                    placeholder="{l s='End date' mod='mailchimppro'}">
            </div>
            <div class="input-group">
                <button id="getStats" v-on:click="getStats();" title="{l s='Apply filter' mod='mailchimppro'}"
                    class="list-toolbar-btn btn btn-success">
                    <i class="las la-chart-line la-2x"></i>
                    <span>{l s='Apply filter' mod='mailchimppro'}</span>
                </button>
            </div>

        </div>

        <div class="chart-wrapepr">
            <div id="barChart">

            </div>
        </div>

        <div class="chart-wrapepr">
            <div id="conversionChart"></div>
        </div>
    </div>
</div>
