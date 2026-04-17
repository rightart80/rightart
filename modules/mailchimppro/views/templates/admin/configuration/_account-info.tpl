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

<div v-cloak v-if="!validApiKey" class="d-flex gap-2 p-1 no-valid-api-key account-info-content-container">
    <div class="panel panel-account-info mb-0 panel-account-log-in text-center d-flex align-items-center justify-content-center">
        <div class="panel-body d-inline-block text-left">
            <p class="title text-center h2 mt-0 mb-4 fw-700">{l s='Already Have an Account? Sign In!' mod='mailchimppro'}</p>
            <p class="headline text-center fw-700">{l s='Already Have an Account?' mod='mailchimppro'}</p>
            <p class="sub-headline">{l s='You are just one step away from managing your campaigns and audience.' mod='mailchimppro'}</p>
            <p class="sub-headline">{l s='Sign in to access all your tools and keep your marketing on track.' mod='mailchimppro'}</p>
            <br>
            <br>
            <div class="account-box text-center">
                <button v-on:click="oauthStart" class="btn button-mc btn-default btn-login">
                    {l s='Sign In to Mailchimp' mod='mailchimppro'}
                </button>
                <div class="mt-2 line-height-1">
                    <small>{l s='Access your account to manage your campaigns, audience, and more' mod='mailchimppro'}</small>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-account-info mb-0 panel-account-sign-up text-center d-flex align-items-center justify-content-center">
        <div class="panel-body d-inline-block text-left">
            <p class="title text-center h2 mt-0 mb-4 fw-700">New to Mailchimp? Get Started for <span class="highlight-underline">Free!</span></p> {* HTML comment, no escape necessary *}
            <p class="headline text-center fw-700">{l s='Try Our Standard Plan – Free for One Month!' mod='mailchimppro'}</p>
            <p class="sub-headline">{l s='Send thousands of emails monthly and unlock powerful tools like AI-driven features, segmentation, and custom templates.' mod='mailchimppro'}</p>
            <br>
            <p class="headline text-center fw-700">{l s='Key Features Include:' mod='mailchimppro'}</p>
            <br>
            <div class="text-center">
                <ul class="sign-up-features text-left d-inline-block p-0">
                    <li class="d-flex align-items-center">
                        <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                        {l s='Generative AI Tools: Create content faster.' mod='mailchimppro'}
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                        {l s='Advanced Segmentation & Reporting: Target and track with precision.' mod='mailchimppro'}
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                        {l s='Enhanced Automations: Streamline your marketing workflows.' mod='mailchimppro'}
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                        {l s='Custom-Coded Templates: Design unique email experiences.' mod='mailchimppro'}
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                        {l s='Data-Driven Optimization: Improve campaigns with actionable insights.' mod='mailchimppro'}
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                        {l s='Personalized Onboarding: Get expert guidance from the start.' mod='mailchimppro'}
                    </li>
                </ul>
            </div>
            <br>
            <br>
            <div class="account-box text-center">
                <button v-on:click="oauthStart" class="btn button-mc btn-default btn-sign-up">
                    {l s='Start Your Free Trial Today!' mod='mailchimppro'}
                </button>
                <div class="mt-2 line-height-1">
                    <small>Enjoy the first month <strong><span class="highlight-underline">free</span></strong>, then continue for a low monthly cost.</small> {* HTML comment, no escape necessary *}
                </div>
            </div>
        </div>
    </div>
</div>

<div v-cloak v-else class="panel panel-account-info" v-show="currentPage === 'accountInfo'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-user-cog la-2x"></i>
            {l s='Account info' mod='mailchimppro'}
        </span>
        <div class="panel-heading-action">
            <button id="desc-attribute_group-new"
                    v-on:click="disconnect"
                    title="{l s='Log out from your Mailchimp account' mod='mailchimppro'}"
                    class="list-toolbar-btn btn btn-logout {* btn-link *}">                 
                <i class="las la-sign-out-alt la-2x"></i>
                <span class="hidden-xs">{l s='Log out' mod='mailchimppro'}</span>
            </button>
        </div>
    </h3>
    {literal}
        <div class="panel-body">
            <div class="d-flex align-items-center">
                <img :src="accountInfo.avatar_url" alt="avatar" style="max-height: 64px" class="d-inline-block">
                <h2 class="d-inline-block mb-0" style="margin-left: 15px">
                    {{accountInfo.first_name}} {{accountInfo.last_name}} - {{accountInfo.account_name}}
                </h2>
            </div>
            <table class="table table-responsive" style="margin-top: 15px">
                <tr>
                    <td>
                        {/literal}{l s='Account ID' mod='mailchimppro'}{literal}
                    </td>
                    <td class="text-break">
                        {{accountInfo.account_id}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {/literal}{l s='Login ID' mod='mailchimppro'}{literal}
                    </td>
                    <td>
                        {{accountInfo.login_id}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {/literal}{l s='Account name' mod='mailchimppro'}{literal}
                    </td>
                    <td>
                        {{accountInfo.account_name}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {/literal}{l s='Email' mod='mailchimppro'}{literal}
                    </td>
                    <td>
                        {{accountInfo.email}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {/literal}{l s='Name' mod='mailchimppro'}{literal}
                    </td>
                    <td>
                        {{accountInfo.first_name}} {{accountInfo.last_name}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {/literal}{l s='Username' mod='mailchimppro'}{literal}
                    </td>
                    <td>
                        {{accountInfo.username}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {/literal}{l s='Role' mod='mailchimppro'}{literal}
                    </td>
                    <td>
                        {{accountInfo.role}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {/literal}{l s='Member since' mod='mailchimppro'}{literal}
                    </td>
                    <td>
                        {{accountInfo.member_since}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {/literal}{l s='Pricing plan type' mod='mailchimppro'}{literal}
                    </td>
                    <td>
                        {{accountInfo.pricing_plan_type}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {/literal}{l s='Account timezone' mod='mailchimppro'}{literal}
                    </td>
                    <td>
                        {{accountInfo.account_timezone}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {/literal}{l s='Pro enabled' mod='mailchimppro'}{literal}
                    </td>
                    <td>
                        <span v-if="accountInfo.pro_enabled">✔️</span>
                        <span v-else>❌️</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        {/literal}{l s='Contact' mod='mailchimppro'}{literal}
                    </td>
                    <td>
                        {{accountInfo.contact.addr1}} {{accountInfo.contact.addr2}},
                        {{accountInfo.contact.city}},
                        {{accountInfo.contact.company}},
                        {{accountInfo.contact.country}},
                        {{accountInfo.contact.state}},
                        {{accountInfo.contact.zip}}
                    </td>
                </tr>
                <tr>
                    <td>
                        {/literal}{l s='Total subscribers' mod='mailchimppro'}{literal}
                    </td>
                    <td>
                        {{accountInfo.total_subscribers}}
                    </td>
                </tr>
            </table>
        </div>
    {/literal}
</div>
