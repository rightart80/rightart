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
<script src="https://cdn.jsdelivr.net/npm/@vueform/multiselect@2.5.2/dist/multiselect.global.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@vueform/multiselect@2.5.2/themes/default.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="{$JsLybraryPath|escape:'htmlall':'UTF-8'}axios.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script src="{$mainJsPath|escape:'htmlall':'UTF-8'}" type="module"></script> {* HTML comment, no escape necessary *}

<link rel="stylesheet"
      href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
<style>
    #side-menu * {
        cursor: pointer;
    }

    .multiselect-tags .form-control,
    .multiselect-tags input[type="text"],
    .multiselect-tags input[type="search"],
    .multiselect-tags input[type="password"],
    .multiselect-tags textarea,
    .multiselect-tags select {
        height: unset;
        display: unset;
        width: unset;
        padding: unset;
        font-size: unset;
        line-height: unset;
        color: unset;
        background-color: unset;
        background-image: unset;
        border: unset;
        border-radius: unset;
        -webkit-transition: unset;
        transition: unset;
    }

    .btn-primary {
        text-transform: unset !important;
    }

    body {
        --ms-tag-bg: #1e94ab;
        --ms-option-bg-selected: #1e94ab;
    }
    #app h2 {
        margin-top: 0;
    }
</style>
<div id="app" data-v-app="" class="mailchimp-pro-content-container">
    <div id="loader-container" :class="(isSaving == true || showLoader == true) ? 'visible' : ''">
        <div class="loader"></div>
    </div>
    {include file="../config/navbar.tpl"}
    <div v-cloak class="alert alert-info" v-show="isSaving">
        {l s='Saving settings...' mod='mailchimppro'}
    </div>

    {if isset($jobs_deleted_message_show) && $jobs_deleted_message_show != false}
        <div class="alert alert-danger">
            {l s='PrestaShop has introduced new requirements, prompting us to change how jobs are saved in the module table. As we upgrade, it is important to delete old jobs from the database. You can mark this message as read and prevent it from appearing again by using the button below:' mod='mailchimppro'}
            <br><br>
            <button id="desc-attribute_group-new"
                    v-on:click="markReadJsonJobs"
                    title="{l s='Mark as read' mod='mailchimppro'}"
                    class="list-toolbar-btn btn btn-success">
                <span>{l s='Mark as read' mod='mailchimppro'}</span>
            </button>
        </div>
    {/if}

    <div v-cloak v-if="(validApiKey && listId && storeSynced && !selectedPreset)" class="alert alert-info alert-info-select-preset">{l s='To start using the module, select a preset option. This will automatically adjust all the related settings for you. You can make changes to these settings anytime afterward as needed.' mod='mailchimppro'}</div>
    <div v-cloak v-if="(validApiKey && selectedPreset && !listId)" class="alert alert-info alert-info-select-audinece-list">
        {{l s='In order to link the current shop with Mailchimp, please select an audience list for it from the drop-down menu on the [b][link]Store sync[/link][/b] tab and click the [b]Initialize connection[/b] button.' 
            sprintf=[
                '[link]' => "<a href=\"#sync\">",
                '[/link]' => '</a>',
                '[b]' => '<b>',
                '[/b]' => '</b>'
            ] 
            mod='mailchimppro'
        }|unescape:'html'}
    </div>
    <div v-cloak v-if="(validApiKey && selectedPreset && listId && !storeSynced)" class="alert alert-info alert-info-select-audinece-list">
        {{l s='In order to link the current shop with Mailchimp, please click the [b]Initialize connection[/b] button on the [b][link-preset]Preset configuration[/link][/b] or [b][link-store]Store sync[/link][/b] tab.' 
            sprintf=[
                '[b]' => '<b>',
                '[/b]' => '</b>',
                '[link-preset]' => "<a href=\"#presets\">",
                '[link-store]' => "<a href=\"#sync\">",
                '[/link]' => '</a>'
            ] 
            mod='mailchimppro'
        }|unescape:'html'}
    </div>

    {if isset($autoSyncPopup) && $autoSyncPopup != false}
        <div v-cloak v-if="(validApiKey && selectedPreset)" class="alert alert-message alert-auto-audience-sync">
            {l s='Your PrestaShop store has been automatically synchronized with the following audience list from your Mailchimp account: ' mod='mailchimppro'}
            <b> {$autoSyncPopupListName|escape:'htmlall':'UTF-8'}</b> {* HTML comment, no escape necessary *}
            <br>
            {{l s='In case you want to change the connection to another list, please go to the [b][link-advanced-settings]Advanced options[/link][/b] tab.'
                sprintf=[
                    '[b]' => '<b>',
                    '[/b]' => '</b>',
                    '[link-advanced-settings]' => "<a href=\"#advanced-settings\">",
                    '[/link]' => '</a>'
                ]
                mod='mailchimppro'
            }|unescape:'html'}
            <br>
            {{l s='Press the [b]Delete[/b] button at the [b]Delete all e-commerce data[/b] section.'
                sprintf=[
                        '[b]' => '<b>',
                        '[/b]' => '</b>'
                    ]
                mod='mailchimppro'
            }|unescape:'html'}
            <br>
            {{l s='After that you can select another audience list on the [b][link-preset]Preset configuration[/link][/b] or [b][link-store]Store sync[/link][/b] tab.'
                sprintf=[
                    '[b]' => '<b>',
                    '[/b]' => '</b>',
                    '[link-preset]' => "<a href=\"#presets\">",
                    '[link-store]' => "<a href=\"#sync\">",
                    '[/link]' => '</a>'
                ] 
                mod='mailchimppro'
            }|unescape:'html'}
            <br><br>
            <button id="desc-attribute_group-new"
                    v-on:click="markReadAutoList"
                    title="{l s='Mark as read' mod='mailchimppro'}"
                    class="list-toolbar-btn btn btn-success">
                <span>{l s='Mark as read' mod='mailchimppro'}</span>
            </button>
        </div>
    {/if}

    <div class="row mailchimp-pro-content-row d-flex flex-wrap">
        {if $multistore_on_store}
            <div v-cloak class="col-md-4 side-col mw-100" :class="!selectedPreset ? 'w-0 p-0 flex-grow-0' : 'flex-grow-1'" v-if="validApiKey">
                <ul class="list-group" id="side-menu" v-show="selectedPreset">
                    <li class="list-group-item d-flex align-items-center"
                        v-show="validApiKey"
                        :class="currentPage === 'presets' ?  'active' :''"
                        v-on:click="setCurrentPage('presets')">
                        <i class="las la-tasks la-2x"></i>
                        <span class="list-group-item-title flex-grow-1 d-flex flex-wrap align-items-center">
                            <span class="flex-grow-1">{l s='Preset configuration' mod='mailchimppro'}</span>
                            <span class="current-preset-label small fw-600 line-height-1" v-html="selectedPreset ? definedPresets[selectedPreset].title : ''"></span>
                        </span>
                    </li>
                    <li class="list-group-item d-flex align-items-center"
                        v-show="validApiKey"
                        :class="currentPage === 'accountInfo' ?  'active' :''"
                        v-on:click="setCurrentPage('accountInfo')">
                        <i class="las la-user-cog la-2x"></i>
                        {l s='Account info' mod='mailchimppro'}
                    </li>
                    <li class="list-group-item-dropdown list-group-advanced-settings"
                        :aria-expanded="(['advanced-settings', 'products', 'customers', 'vouchers', 'orders', 'carts', 'newsletter-subscribers', 'promo', 'addNewPromo', 'promocodes', 'promoStats'].includes(currentPage) || (selectedPreset && selectedPreset === 'custom') ) ?  'true' : 'false'"
                        v-show="validApiKey">
                        <span class="list-group-item d-flex align-items-center rounded-0"
                            :class="currentPage === 'advanced-settings' ?  'active' :''"
                            v-on:click="setCurrentPage('advanced-settings')">
                            <i class="las la-cog la-2x"></i>
                            <span class="list-group-item-title flex-grow-1">{l s='Advanced settings' mod='mailchimppro'}</span>
                            <div class="dropdown-toggle" role="button" v-on:click="dropDownToggle">
                                <i class="las la-angle-down"></i>
                            </div>
                        </span>
                        <ul class="list-group-sub w-100 p-0">
                            <li class="list-group-item d-flex align-items-center list-group-products list-group-sub-item rounded-0"
                                v-show="validApiKey"
                                :class="currentPage === 'products' ?  'active' :''"
                                v-on:click="setCurrentPage('products')">
                                <i class="las la-boxes la-2x"></i>
                                {l s='Products' mod='mailchimppro'}
                            </li>
                            <li class="list-group-item d-flex align-items-center list-group-customers list-group-sub-item"
                                v-show="validApiKey"
                                :class="currentPage === 'customers' ?  'active' :''"
                                v-on:click="setCurrentPage('customers')">
                                <i class="las la-users  la-2x"></i>
                                {l s='Customers' mod='mailchimppro'}
                            </li>
                            <li class="list-group-item d-flex align-items-center list-group-cart-rules list-group-sub-item"
                                v-show="validApiKey"
                                :class="currentPage === 'vouchers' ?  'active' :''"
                                v-on:click="setCurrentPage('vouchers')">
                                <i class="las la-ruler-combined la-2x"></i>
                                {l s='Cart rules' mod='mailchimppro'}
                            </li>                            
                            <li class="list-group-item d-flex align-items-center list-group-cart-rules list-group-sub-item"
                                v-show="validApiKey"
                                :class="((currentPage === 'promo' || currentPage === 'addNewPromo' || currentPage === 'promocodes' || currentPage === 'promoStats') ?  'active' :'')"
                                v-on:click="setCurrentPage('promo')">
                                <i class="las la-percent la-2x"></i>
                                {l s='Promo codes' mod='mailchimppro'}
                            </li>
                            <li class="list-group-item d-flex align-items-center list-group-orders list-group-sub-item"
                                v-show="validApiKey"
                                :class="currentPage === 'orders' ?  'active' :''"
                                v-on:click="setCurrentPage('orders')">
                                <i class="las la-shopping-cart la-2x"></i>
                                {l s='Orders' mod='mailchimppro'}
                            </li>
                            <li class="list-group-item d-flex align-items-center list-group-carts list-group-sub-item rounded-0"
                                v-show="validApiKey"
                                :class="currentPage === 'carts' ?  'active' :''"
                                v-on:click="setCurrentPage('carts')">
                                <i class="las la-cart-arrow-down la-2x"></i>
                                {l s='Abandoned carts' mod='mailchimppro'}
                            </li>
                            <li class="list-group-item d-flex align-items-center list-group-carts list-group-sub-item rounded-0"
                                v-show="validApiKey"
                                :class="currentPage === 'newsletter-subscribers' ?  'active' :''"
                                v-on:click="setCurrentPage('newsletter-subscribers')">
                                <i class="las la-envelope-open la-2x"></i>
                                {l s='Newsletter subscribers' mod='mailchimppro'}
                            </li>
                        </ul>
                    </li>
                    <li class="list-group-item d-flex align-items-center"
                        v-show="validApiKey"
                        :class="currentPage === 'sync' ?  'active' :''"
                        v-on:click="setCurrentPage('sync')">
                        <i class="las la-save la-2x"></i>
                        {l s='Store sync' mod='mailchimppro'}
                    </li>
                    <li class="list-group-item d-flex align-items-center"
                        v-show="validApiKey"
                        :class="currentPage === 'cronjob' ?  'active' :''"
                        v-on:click="setCurrentPage('cronjob')">
                        <i class="las la-clock la-2x"></i>
                        {l s='Cronjob' mod='mailchimppro'}
                    </li>
                    <li class="list-group-item d-flex align-items-center"
                        v-show="validApiKey"
                        :class="currentPage === 'faq' ?  'active' :''"
                        v-on:click="setCurrentPage('faq')">
                        <i class="las la-question-circle la-2x"></i>
                        {l s='FAQs' mod='mailchimppro'}
                    </li>
                    <li class="list-group-item d-flex align-items-center"
                        v-show="validApiKey"
                        :class="currentPage === 'log' ? 'active' : ''"
                        v-on:click="setCurrentPage('log')">
                        <i class="las la-server la-2x"></i>
                        {l s='Logs' mod='mailchimppro'}
                    </li>
                    <li class="list-group-item d-flex align-items-center"
                        v-show="validApiKey"
                        :class="currentPage === 'statistics' ? 'active' : ''"
                        v-on:click="setCurrentPage('statistics')">
                        <i class="las la-chart-bar la-2x"></i>
                        {l s='Statistics' mod='mailchimppro'}
                    </li>
                </ul>
            </div>
            <div class="col-md-8 main-col flex-grow-1 mw-100">
                <div class="row m-0">
                    {include file="./_account-info.tpl"}
                    {if isset($validApiKey) && $validApiKey}
                        {include file="./_presets.tpl"}
                        {include file="./_products.tpl"}
                        {include file="./_customers.tpl"}
                        {include file="./_vouchers.tpl"}
                        {include file="./_promo.tpl"}
                        {include file="./_orders.tpl"}
                        {include file="./_carts.tpl"}
                        {include file="./_newsletter-subscribers.tpl"}
                        {include file="./_advanced.tpl"}
                        {include file="./_sync.tpl"}
                        {include file="./_cronjob.tpl"}
                        {include file="./_faq.tpl"}
                        {include file="./_logs.tpl"}
                        {include file="./_statistics.tpl"}
                    {/if}
                </div>
            </div>
        {else}
            <div class="col-md-12 col-form-group w-100">
                <div class="form-group mb-0">
                    <div class="alert alert-warning">
                        <p>{l s='You are using Prestashop in multi-store configuration. Please pay attention to choose one of the stores when you use the module.' mod='mailchimppro'}</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <p>{l s='In order to get started, please select one shop in your back office and log in to your Mailchimp account.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        const promoExpiration = $('#PROMO_EXPIRATION');

        $('#logsTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "order": [[0, 'desc']],
            "columnDefs": [
                { "orderable": false, "targets": [3] }
            ]
        });
        
        promoExpiration.datepicker({
            dateFormat: 'yy-mm-dd',
            onSelect: function(datetext) {
                var d = new Date();

                var h = d.getHours();
                h = (h < 10) ? ("0" + h) : h ;

                var m = d.getMinutes();
                m = (m < 10) ? ("0" + m) : m ;

                var s = d.getSeconds();
                s = (s < 10) ? ("0" + s) : s ;

                datetext = datetext + " " + h + ":" + m + ":" + s;

                promoExpiration.val(datetext);
            }
        });

        $('#statsStart').datepicker({
            dateFormat: 'yy-mm-dd',
            onSelect: function(datetext) {
                $('#statsStart').val(datetext);
            }
        });
        $('#statsEnd').datepicker({
            dateFormat: 'yy-mm-dd',
            onSelect: function(datetext) {
                $('#statsEnd').val(datetext);
            }
        });
    });
</script>
</script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">