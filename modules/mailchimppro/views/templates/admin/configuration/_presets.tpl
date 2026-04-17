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

<div v-cloak v-if="validApiKey" class="panel panel-presets" v-show="currentPage === 'presets'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-tasks la-2x"></i>
            {l s='Preset configuration' mod='mailchimppro'}
        </span>
        <div class="panel-heading-action"></div>
    </h3>
    <div class="panel-body panel-steps p-0">
        <div class="steps-container d-flex justify-content-center mt-2 mb-4">
            <div v-on:click="selectPanelStep" data-step-target="step-sync-store-content" class="panel-step step-sync-store d-flex flex-column align-items-center cursor-pointer available" :class="[isPanelStepActive('sync-store') ? 'active' : '', isPanelStepDone('sync-store') ? 'done' : '']">
                <span class="step-number rounded-circle d-flex align-items-center justify-content-center fw-600">1</span>
                <span class="step-name">{l s='Sync store' mod='mailchimppro'}</span>
            </div>
            <div class="panel-step-separator align-self-baseline position-relative"></div>
            <div v-on:click="selectPanelStep" data-step-target="step-select-preset-content" class="panel-step step-select-preset d-flex flex-column align-items-center cursor-pointer" :class="[isPanelStepAvailable('select-preset') ? 'available' : '', isPanelStepActive('select-preset') ? 'active' : '', isPanelStepDone('select-preset') ? 'done' : '']">
                <span class="step-number rounded-circle d-flex align-items-center justify-content-center fw-600">2</span>
                <span class="step-name">{l s='Select preset' mod='mailchimppro'}</span>
            </div>
            <div v-show="cronjobBasedSync" class="panel-step-separator align-self-baseline position-relative"></div>
            <div v-show="cronjobBasedSync" v-on:click="selectPanelStep" data-step-target="step-setup-cronjob-content" class="panel-step step-setup-cronjob d-flex flex-column align-items-center cursor-pointer" :class="[isPanelStepAvailable('setup-cronjob') ? 'available' : '', isPanelStepActive('setup-cronjob') ? 'active' : '', isPanelStepDone('setup-cronjob') ? 'done' : '']">
                <span class="step-number rounded-circle d-flex align-items-center justify-content-center fw-600">3</span>
                <span class="step-name">{l s='Set up cronjob' mod='mailchimppro'}</span>
            </div>
        </div>
        <hr>
        <div class="step-content step-sync-store-content" :class="isPanelStepActive('sync-store') ? 'active' : ''">
            {if isset($autoSyncPopup) && $autoSyncPopup != false}
                <div class="alert alert-success alert-auto-audience-sync">
                    {l s='Your PrestaShop store has been automatically synchronized with the following audience list from your Mailchimp account: ' mod='mailchimppro'}
                    <b> {$autoSyncPopupListName|escape:'htmlall':'UTF-8'}</b> {* HTML comment, no escape necessary *}
                </div>
            {else}
                {include file="./_audience-lists.tpl"}
            {/if}
        </div>
        <div class="step-content step-select-preset-content" :class="isPanelStepActive('select-preset') ? 'active' : ''">
            <h4 class="modal-title mb-4 fw-600 fs-4 text-center">{l s='Choose the most suitable preset option for your business!' mod='mailchimppro'}</h4>
            <div class="d-grid flex-wrap gap-2 presets-content-container">
                <template v-for="(value, key, index) in definedPresets">
                    <div :data-preset="key" class="panel mb-0 flex-column" :class="[(!selectedPreset && index === 0) ? 'highlighted' : '', selectedPreset == key ? 'selected' : '', index === 0 ? 'w-100-' : '', 'panel-' + key, (key == 'free' && selectedPreset !== 'free' && !isFreePresetAvailable()) ? 'd-none' : 'd-flex']">
                        <div class="title text-center h3 m-0 fw-700 d-flex align-items-center justify-content-center" v-html="value.title"></div>
                        <div v-if="(index === 0)" class="recommended-label-container text-center">
                            <span class="recommended-label d-inline-block small fw-600">{l s='Recommended' mod='mailchimppro'}</span>
                        </div>
                        <div class="panel-body d-inline-flex flex-column text-left flex-grow-1 hide-pseudo">
                            <div class="panel-body-top flex-grow-1">
                                <p class="preset-description {* fs-6 *} mb-4" v-html="value.description"></p>
                                <div class="preset-sync-type mb-4">
                                    <p class="preset-sync-label fw-700 text-decoration-underline">{l s='Sync type:' mod='mailchimppro'}</p>
                                    <ul class="preset-sync-type-list d-grid p-0">
                                        <li class="d-flex align-items-center">
                                            <i class="material-icons icon-arrow-forward fs-0 rounded-circle">&#xe5c8;</i>
                                            <span v-html="value['sync-type']"></span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="preset-data-sync">
                                    <p class="preset-data-sync-label fw-700 mb-4 text-decoration-underline">{l s='Data sync:' mod='mailchimppro'}</p>
                                    <ul class="preset-data-sync-list d-grid p-0">
                                        <template v-for="(dataSync, key) in value['data-sync']">
                                            <li class="d-flex align-items-center">
                                                <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                                                <span v-html="dataSync"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                            <div class="account-box text-center">
                                <div v-if="selectedPreset == key" class="label-selected bg-light-blue fw-600">{l s='Selected' mod='mailchimppro'}</div>
                                <button v-else v-on:click="selectPreset" class="btn button-mc btn-default btn-select" :class="(index !== 0 || (index === 0 && selectedPreset)) ? 'bg-white' : ''">
                                    {l s='Select preset' mod='mailchimppro'}
                                </button>
                                <p class="use-case mb-0">
                                    <i v-html="value['use-case']"></i>
                                </p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div class="step-content step-setup-cronjob-content" :class="isPanelStepActive('setup-cronjob') ? 'active' : ''">
            <div class="alert alert-warning alert-cronjob mb-0">
                {{l s='To make everything work, you\'ll need to set up something called a [strong]"Cronjob"[/strong] on your server. Don\'t worry—it\'s just a small task that keeps things running smoothly. [br] You can find the details in the [a]"Cronjob"[/a] tab.' 
                    sprintf=[
                        '[strong]' => "<strong>",
                        '[/strong]' => "</strong>",
                        '[br]' => "<br>",
                        '[a]' => "<a class=\"link-mc fw-600 text-decoration-underline\" href=\"#cronjob\">",
                        '[/a]' => "</a>"
                    ]
                    mod='mailchimppro'}|unescape:'html'}        
            </div>
        </div>
    </div>
</div>
