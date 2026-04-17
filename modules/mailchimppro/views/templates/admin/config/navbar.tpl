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
<div class="row navbar-container d-flex gap-1">
    <div class="col-sm-6 flex-grow-1">
        <img class="img-responsive mailchimp-logo" src="../modules/mailchimppro/views/img/logo-horizontal.png" height="440" width="2000">
    </div>
    <div class="col-sm-6">
        <div class="btn-group pull-right" role="group" style="height:100%; vertical-align:center;line-height : 100%;">
            <a class="btn btn-default btn-configuration" href="{LinkHelper::getAdminLink('AdminMailchimpProConfiguration')|escape:'htmlall':'UTF-8'}">
                <i class="icon icon-gear" aria-hidden="true"></i>
                {l s='Setup wizard' mod='mailchimppro'}
            </a>
            <a class="btn btn-default btn-syncronization hidden" href="{LinkHelper::getAdminLink('AdminMailchimpProSync')|escape:'htmlall':'UTF-8'}">
                <i class="icon icon-retweet" aria-hidden="true"></i>
                {l s='Sync' mod='mailchimppro'}
            </a>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default btn-mc dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                    <i class="icon icon-folder-open-o" aria-hidden="true"></i>
                    <span class="btn-text hidden-xs">{l s='Mailchimp Objects' mod='mailchimppro'}</span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-mc-objects">
                    <li>
                        <a href="{LinkHelper::getAdminLink('AdminMailchimpProBatches')|escape:'htmlall':'UTF-8'}">
                            {l s='Batches' mod='mailchimppro'}
                        </a>
                    </li>
                    <li>
                        <a href="{LinkHelper::getAdminLink('AdminMailchimpProCarts')|escape:'htmlall':'UTF-8'}">
                            {l s='Carts' mod='mailchimppro'}
                        </a>
                    </li>
                    <li>
                        <a href="{LinkHelper::getAdminLink('AdminMailchimpProCustomers')|escape:'htmlall':'UTF-8'}">
                            {l s='Customers' mod='mailchimppro'}
                        </a>
                    </li>
                    <li>
                        <a href="{LinkHelper::getAdminLink('AdminMailchimpProLists')|escape:'htmlall':'UTF-8'}">
                            {l s='Lists' mod='mailchimppro'}
                        </a>
                    </li>
                    <li>
                        <a href="{LinkHelper::getAdminLink('AdminMailchimpProOrders')|escape:'htmlall':'UTF-8'}">
                            {l s='Orders' mod='mailchimppro'}
                        </a>
                    </li>
                    <li>
                        <a href="{LinkHelper::getAdminLink('AdminMailchimpProProducts')|escape:'htmlall':'UTF-8'}">
                            {l s='Products' mod='mailchimppro'}
                        </a>
                    </li>
                    <li>
                        <a href="{LinkHelper::getAdminLink('AdminMailchimpProStores')|escape:'htmlall':'UTF-8'}">
                            {l s='Stores' mod='mailchimppro'}
                        </a>
                    </li>

                    <li>
                        <a href="{LinkHelper::getAdminLink('AdminMailchimpProSites')|escape:'htmlall':'UTF-8'}">
                            {l s='Sites' mod='mailchimppro'}
                        </a>
                    </li>
                    <li>
                        <a href="{LinkHelper::getAdminLink('AdminMailchimpProAutomations')|escape:'htmlall':'UTF-8'}">
                            {l s='Automations' mod='mailchimppro'}
                        </a>
                    </li>
                    <li>
                        <a href="{LinkHelper::getAdminLink('AdminMailchimpProPromoRules')|escape:'htmlall':'UTF-8'}">
                            {l s='Promo rules' mod='mailchimppro'}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<hr>
