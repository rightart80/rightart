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
{literal}
<div class="panel" v-if="currentStep == 7">
    <div class="panel-heading">
        <i class="material-icons" style="width: 20px">
            shopping_cart
        </i>
        {/literal}{l s='Syncing orders' mod='mailchimppro'}{literal}
    </div>
    <div class="row">
        <div class="col-12 text-center">
            <div class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" :style="{ width: (ordersSynced*100)/orderIds.length + '%' }">
                    <span class="sr-only">
                        {{(ordersSynced*100)/orderIds.length}} Complete
                    </span>
                </div>
            </div>
            <p class="text-center">
                {{((ordersSynced*100)/orderIds.length).toFixed(0)}}% Complete
            </p>
            <p class="text-center">
                {{ordersSynced}}/{{orderIds.length}}
            </p>
            <p class="text-center">
                Remaining time: {{convertMsToMinutesSeconds(avgResponseTime*(orderIds.length-ordersSynced))}}
            </p>
        </div>
    </div>
</div>
{/literal}