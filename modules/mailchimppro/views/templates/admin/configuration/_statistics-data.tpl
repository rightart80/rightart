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

<div id="mailchimp-reports">
    {if !empty($statistics.total_items) && $statistics.total_items && !empty($statistics.reports)}
        <div class="mailchimp-reports-content-container">
            <div class="mailchimp-reports-email-performance">
                <label>{l s='Email performance' mod='mailchimppro'}</label>
                <div class="mailchimp-reports-top-cards-container d-flex flex-wrap justify-content-center">
                    {if isset($statistics.total_emails_sent)}
                        <div class="mailchimp-reports-top-card d-flex flex-column align-items-center card-total-emails-sent">
                            <div class="card-title text-center fw-600 d-flex align-items-center cursor-pointer help-tooltip"
                                data-toggle="tooltip"
                                data-placement="top"
                                data-html="true"
                                title="<p>
                                    {{l s='The total number of emails sent in the last [strong][variable][/strong] campaigns.' 
                                        sprintf=[
                                            '[strong]' => '<strong>',
                                            '[/strong]' => '</strong>',
                                            '[variable]' => count($statistics.reports)
                                        ]
                                        mod='mailchimppro'
                                    }|unescape:'html'}
                                </p>"
                            >
                                <img class="chimp-badge img-responsive rounded-circle flex-shrink-0" src="/modules/mailchimppro/views/img/chimp.png" width="25" height="25" alt="{l s='Total emails sent' mod='mailchimppro'}">
                                <span class="card-title-label">{l s='Emails sent' mod='mailchimppro'}</span>
                            </div>
                            <div class="card-body d-flex align-items-center flex-grow-1">
                                <strong>{$statistics.total_emails_sent|escape:'htmlall':'UTF-8'}</strong>
                            </div>
                        </div>
                    {/if}
                    {if isset($statistics.avg_emails_bounce_rate)}
                        <div class="mailchimp-reports-top-card d-flex flex-column align-items-center card-average-bounce-rate">
                            <div class="card-title text-center fw-600 d-flex align-items-center cursor-pointer help-tooltip"
                                data-toggle="tooltip"
                                data-placement="top"
                                data-html="true"
                                title="<p>
                                    {{l s='The percentage of emails that were blocked and returned to the sender in the last [strong][variable][/strong] campaigns.' 
                                        sprintf=[
                                            '[strong]' => '<strong>',
                                            '[/strong]' => '</strong>',
                                            '[variable]' => count($statistics.reports)
                                        ]
                                        mod='mailchimppro'
                                    }|unescape:'html'}
                                </p>"
                            >
                                <img class="chimp-badge img-responsive rounded-circle flex-shrink-0" src="/modules/mailchimppro/views/img/chimp.png" width="25" height="25" alt="{l s='Average bounce rate' mod='mailchimppro'}">
                                <span class="card-title-label">{l s='Bounce rate' mod='mailchimppro'}</span>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center flex-grow-1">
                                <strong>{$statistics.avg_emails_bounce_rate.text|escape:'htmlall':'UTF-8'}</strong>
                                {if isset($statistics.total_emails_bounced)}
                                    <small class="text-muted">{l s='Total bounced:' mod='mailchimppro'} <span class="fw-600">{$statistics.total_emails_bounced|escape:'htmlall':'UTF-8'}</span></small>
                                {/if}
                            </div>
                        </div>
                    {/if}
                    {if isset($statistics.avg_email_unique_open_rate)}
                        <div class="mailchimp-reports-top-card d-flex flex-column align-items-center card-average-open-rate">
                            <div class="card-title text-center fw-600 d-flex align-items-center cursor-pointer help-tooltip"
                                data-toggle="tooltip"
                                data-placement="top"
                                data-html="true"
                                title="<p>
                                    {{l s='The percentage of successfully delivered emails that were opened in the last [strong][variable][/strong] campaigns.' 
                                        sprintf=[
                                            '[strong]' => '<strong>',
                                            '[/strong]' => '</strong>',
                                            '[variable]' => count($statistics.reports)
                                        ]
                                        mod='mailchimppro'
                                    }|unescape:'html'}
                                </p>"
                            >
                                <img class="chimp-badge img-responsive rounded-circle flex-shrink-0" src="/modules/mailchimppro/views/img/chimp.png" width="25" height="25" alt="{l s='Average open rate' mod='mailchimppro'}">
                                <span class="card-title-label">{l s='Open rate' mod='mailchimppro'}</span>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center flex-grow-1">
                                <strong>{$statistics.avg_email_unique_open_rate.text|escape:'htmlall':'UTF-8'}</strong>
                                {if isset($statistics.total_emails_unique_opens)}
                                    <small class="text-muted">{l s='Total opened:' mod='mailchimppro'} <span class="fw-600">{$statistics.total_emails_unique_opens|escape:'htmlall':'UTF-8'}</span></small>
                                {/if}
                            </div>
                        </div>
                    {/if}
                    {if isset($statistics.avg_email_unique_click_rate)}
                        <div class="mailchimp-reports-top-card d-flex flex-column align-items-center card-average-click-rate">
                            <div class="card-title text-center fw-600 d-flex align-items-center cursor-pointer help-tooltip"
                                data-toggle="tooltip"
                                data-placement="top"
                                data-html="true"
                                title="<p>
                                    {{l s='The percentage of successfully delivered messages that registered a click in the last [strong][variable][/strong] campaigns.' 
                                        sprintf=[
                                            '[strong]' => '<strong>',
                                            '[/strong]' => '</strong>',
                                            '[variable]' => count($statistics.reports)
                                        ]
                                        mod='mailchimppro'
                                    }|unescape:'html'}
                                </p>"
                            >
                                <img class="chimp-badge img-responsive rounded-circle flex-shrink-0" src="/modules/mailchimppro/views/img/chimp.png" width="25" height="25" alt="{l s='Average click rate' mod='mailchimppro'}">
                                <span class="card-title-label">{l s='Click rate' mod='mailchimppro'}</span>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center flex-grow-1">
                                <strong>{$statistics.avg_email_unique_click_rate.text|escape:'htmlall':'UTF-8'}</strong>
                                {if isset($statistics.total_emails_unique_clicks)}
                                    <small class="text-muted">{l s='Total clicked:' mod='mailchimppro'} <span class="fw-600">{$statistics.total_emails_unique_clicks|escape:'htmlall':'UTF-8'}</span></small>
                                {/if}
                            </div>
                        </div>
                    {/if}
                    {if isset($statistics.avg_unsubscribe_rate)}
                        <div class="mailchimp-reports-top-card d-flex flex-column align-items-center card-average-unsubscribe-rate">
                            <div class="card-title text-center fw-600 d-flex align-items-center cursor-pointer help-tooltip"
                                data-toggle="tooltip"
                                data-placement="top"
                                data-html="true"
                                title="<p>
                                    {{l s='The percentage of subscribers who unsubscribed from a successfully delivered email in the last [strong][variable][/strong] campaigns.' 
                                        sprintf=[
                                            '[strong]' => '<strong>',
                                            '[/strong]' => '</strong>',
                                            '[variable]' => count($statistics.reports)
                                        ]
                                        mod='mailchimppro'
                                    }|unescape:'html'}
                                </p>"
                            >
                                <img class="chimp-badge img-responsive rounded-circle flex-shrink-0" src="/modules/mailchimppro/views/img/chimp.png" width="25" height="25" alt="{l s='Average unsubscribe rate' mod='mailchimppro'}">
                                <span class="card-title-label">{l s='Unsubscribe rate' mod='mailchimppro'}</span>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center flex-grow-1">
                                <strong>{$statistics.avg_unsubscribe_rate.text|escape:'htmlall':'UTF-8'}</strong>
                                {if isset($statistics.total_unsubscribed)}
                                    <small class="text-muted">{l s='Total unsubscribed:' mod='mailchimppro'} <span class="fw-600">{$statistics.total_unsubscribed|escape:'htmlall':'UTF-8'}</span></small>
                                {/if}
                            </div>
                        </div>
                    {/if}
                </div>
                <small class="email-performance-note text-muted d-block text-right mt-3">
                    {{l s='Based on the last [strong][variable][/strong] out of your total of [strong][variable2][/strong] campaigns.' 
                        sprintf=[
                            '[strong]' => '<strong>',
                            '[/strong]' => '</strong>',
                            '[variable]' => count($statistics.reports),
                            '[variable2]' => $statistics.total_items
                        ]
                        mod='mailchimppro'
                    }|unescape:'html'}
                </small>
            </div>
            <hr>
            <div class="mailchimp-reports-campaigns">
                <label>{l s='Campaigns' mod='mailchimppro'}</label>
                {if !empty($configurationLink)}
                    <div class="campaign-email-performance-info-labels-container d-flex flex-wrap flex-grow-1">
                        {if !empty($statistics.reports[0].bounces)}
                            <div class="performance-title fw-600 bounces">{l s='Bounces' mod='mailchimppro'}</div>
                        {/if}
                        {if !empty($statistics.reports[0].opens)}
                            <div class="performance-title fw-600 opens">{l s='Opens' mod='mailchimppro'}</div>
                        {/if}
                        {if !empty($statistics.reports[0].clicks)}
                            <div class="performance-title fw-600 clicks">{l s='Clicks' mod='mailchimppro'}</div>
                        {/if}
                        {if !empty($statistics.reports[0].ecommerce)}
                            <div class="performance-title fw-600 orders">{l s='Orders' mod='mailchimppro'}</div>
                        {/if}
                        {if !empty($statistics.reports[0].ecommerce)}
                            <div class="performance-title fw-600 revenue">{l s='Revenue' mod='mailchimppro'}</div>
                        {/if}
                    </div>
                {/if}
                <div class="mailchimp-reports-details-container">
                    {foreach from=$statistics.reports item=report name=reports}
                        <div class="mailchimp-report-container">
                            <div class="mailchimp-report d-flex flex-wrap">
                                <i class="material-icons flex-shrink-0">mail_outline</i>
                                <div class="campaign-general-info">
                                    {if !empty($report.campaign_title)}
                                        <div class="campaign-title">{$report.campaign_title|escape:'htmlall':'UTF-8'}</div>
                                    {/if}
                                    {if !empty($report.subject_line)}
                                        <div class="campaign-subject-line {if !empty($report.campaign_title)}text-muted small{/if}">{$report.subject_line|escape:'htmlall':'UTF-8'}</div>
                                    {/if}
                                    {if !empty($report.list_name)}
                                        <div class="campaign-list-name small">
                                            <span>
                                                {l s='Audience: ' mod='mailchimppro'}
                                                {$report.list_name|escape:'htmlall':'UTF-8'}
                                            </span>
                                        </div>
                                    {/if}
                                    {if !empty($report.send_time)}
                                        <small class="campaign-send-time small">
                                            {l s='Sent' mod='mailchimppro'}
                                            <strong class="fw-600">{$report.send_time|escape:'htmlall':'UTF-8'}</strong>
                                            {if !empty($report.emails_sent)}
                                                {l s='to' mod='mailchimppro'}
                                                <strong>{$report.emails_sent|escape:'htmlall':'UTF-8'}</strong>
                                                {l s='recipients' mod='mailchimppro'}
                                            {/if}
                                        </small>
                                    {/if}
                                </div>
                                <div class="campaign-email-performance-info d-flex flex-wrap flex-grow-1">
                                    {if !empty($report.bounces)}
                                        <div class="campaign-email-performance-info-item d-flex flex-column align-items-center justify-content-center bounces">
                                            <div class="performance-title fw-600">{l s='Bounces' mod='mailchimppro'}</div>
                                            <strong class="performance-values fw-600">
                                                <div class="value">{$report.bounces.hard_bounces|escape:'htmlall':'UTF-8'}</div>
                                            </strong>
                                        </div>
                                    {/if}
                                    {if !empty($report.opens)}
                                        <div class="campaign-email-performance-info-item d-flex flex-column align-items-center justify-content-center opens">
                                            <div class="performance-title fw-600">{l s='Opens' mod='mailchimppro'}</div>
                                            <strong class="performance-values fw-600">
                                                <div class="percentage">{round($report.opens.open_rate * 100, 2)|escape:'htmlall':'UTF-8'} %</div>
                                                <div class="value">{$report.opens.unique_opens|escape:'htmlall':'UTF-8'}</div>
                                            </strong>
                                        </div>
                                    {/if}
                                    {if !empty($report.clicks)}
                                        <div class="campaign-email-performance-info-item d-flex flex-column align-items-center justify-content-center clicks">
                                            <div class="performance-title fw-600">{l s='Clicks' mod='mailchimppro'}</div>
                                            <strong class="performance-values fw-600">
                                                <div class="percentage">{round($report.clicks.click_rate * 100, 2)|escape:'htmlall':'UTF-8'} %</div>
                                                <div class="value">{$report.clicks.unique_clicks|escape:'htmlall':'UTF-8'}</div>
                                            </strong>
                                        </div>
                                    {/if}
                                    {if !empty($report.ecommerce)}
                                        <div class="campaign-email-performance-info-item d-flex flex-column align-items-center justify-content-center total-orders">
                                            <div class="performance-title fw-600">{l s='Orders' mod='mailchimppro'}</div>
                                            <strong class="performance-values fw-600">
                                                <div class="value">{$report.ecommerce.total_orders|escape:'htmlall':'UTF-8'}</div>
                                            </strong>
                                        </div>
                                    {/if}
                                    {if !empty($report.ecommerce)}
                                        <div class="campaign-email-performance-info-item d-flex flex-column align-items-center justify-content-center total-revenue">
                                            <div class="performance-title fw-600">{l s='Revenue' mod='mailchimppro'}</div>
                                            <strong class="performance-values fw-600">
                                                <div class="value">
                                                    {$report.ecommerce.total_revenue|escape:'htmlall':'UTF-8'}
                                                    {if !empty($report.ecommerce.currency_code)}
                                                        {$report.ecommerce.currency_code|escape:'htmlall':'UTF-8'}
                                                    {/if}
                                                </div>
                                            </strong>
                                        </div>
                                    {/if}
                                </div>
                            </div>
                            {if !$smarty.foreach.reports.last}<hr>{/if}
                        </div>
                    {/foreach}
                </div>
            </div>
            {if !empty($configurationLink)}
                <div class="text-center mt-3">
                    <a class="configuration-link link-mc2 bg-white" href="{$configurationLink|escape:'htmlall':'UTF-8'}" title="{l s='See more' d='Admin.Actions'}">
                        {l s='See more' mod='mailchimppro'}
                    </a>
                </div>
            {/if}
        </div>
    {else}
        <div class="create-journey-container d-flex flex-wrap align-items-center justify-content-center">
            <div class="create-journey-content">
                <div class="create-journey-content-title title h3 fw-700">{l s='Create your first journey!' d='Admin.Actions'}</div>
                <p class="create-journey-content-text">
                    {{l s='Automate your marketing with [a]Customer Journey Builder[/a] to deliver better emails, drive repeat business, enhance customer retention, and achieve up to 4x more orders through effective marketing automation.' 
                    sprintf=[
                        '[a]' => "<a class=\"link-mc fw-600 text-decoration-underline\" href=\"https://mailchimp.com/features/automations/customer-journey-builder/\" target=\"_blank\">",
                        '[/a]' => "</a>"
                    ]
                    mod='mailchimppro'}|unescape:'html'} 
                </p>
            </div>
            <a class="campaign-image-link d-inline-flex justify-content-end" href="https://mailchimp.com/features/automations/customer-journey-builder/" target="_blank">
                <img class="img-responsive contrast-on-hover" src="/modules/mailchimppro/views/img/paths_builder.jpg">
            </a>
        </div>
    {/if}
    {if !empty($statistics.campaign_error) && $statistics.campaign_error}
        <div class="alert alert-danger">{$statistics.campaign_error|escape:'htmlall':'UTF-8'}</div>
    {/if}
    {if !empty($statistics.total_items) && $statistics.total_items && !empty($statistics.reports) && !empty($statistics.last_update)}
        <div class="last-updated-label text-muted text-center">
            <hr>
            <small>*{l s='Last updated on: ' d='mailchimppro'}<strong>{$statistics.last_update|escape:'htmlall':'UTF-8'}</strong></small>
        </div>
    {/if}
</div>
