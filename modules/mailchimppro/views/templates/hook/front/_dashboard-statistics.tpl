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

<section id="mailchimp-dashboard-section" class="panel p-0">
    <div class="panel-heading d-flex align-items-center gap-0-5 m-0">
        <i class="icon-bar-chart m-0"></i>
        <span class="flex-grow-1">{l s='Mailchimp campaign reports' d='mailchimppro'}</span>
        <span class="panel-heading-action flex-shrink-0 position-initial">
            <a class="list-toolbar-btn" href="{$configurationLink|escape:'htmlall':'UTF-8'}" title="{l s='Configure Mailchimp' d='Admin.Actions'}">
                <i class="process-icon-configure"></i>
            </a>
            <a class="list-toolbar-btn" href="{$dashboardLink|escape:'htmlall':'UTF-8'}" title="{l s='Refresh' d='Admin.Actions'}">
                <i class="process-icon-refresh"></i>
            </a>
        </span>
    </div>
    <div class="mailchimp-dashboard-section-body">
        <div class="mailchimp-logo-container text-center mb-3">
            <img class="img-responsive mx-auto" src="/modules/mailchimppro/views/img/_logo-horizontal.png" width="160">
        </div>
        <div class="statistics-content-container">{include file="module:mailchimppro/views/templates/admin/configuration/_statistics-data.tpl"}</div>
    </div>
</section>
