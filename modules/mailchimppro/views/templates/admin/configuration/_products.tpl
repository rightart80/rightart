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
 <div v-cloak v-if="validApiKey" class="panel panel-products" v-show="currentPage === 'products'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-boxes la-2x"></i>
            {l s='Products' mod='mailchimppro'}
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
        <h2>{l s='Sync products' mod='mailchimppro'}</h2>
        <div class="form-group">
            <div class="clearfix"></div>
            <div class="btn-group large" role="group">
                <button type="button" class="btn " v-on:click="syncProducts = true"
                        :class="syncProducts == true ? 'btn-primary' : 'btn-default'">
                    {l s='Yes' mod='mailchimppro'}
                </button>
                <button type="button" class="btn " v-on:click="syncProducts = false"
                        :class="syncProducts == false ? 'btn-primary' : 'btn-default'">
                    {l s='No' mod='mailchimppro'}
                </button>
            </div>
        </div>
        <hr>
        <div :class="syncProducts == true ? 'sync-settings-container' : 'sync-settings-container no-sync-type'">
            <h2>{l s='Product data' mod='mailchimppro'}</h2>
            <div class="form-group">
                <label>{l s='Description field' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                {literal}
                <Multiselect
                    :can-deselect="false"
                    :can-clear="false"
                    :mode="'single'"
                    v-model="productDescriptionField"
                    :options="[{'label': 'Description','value': 'description'},{'label': 'Short description','value': 'description_short'}]"
                >
                </Multiselect>
                {/literal}
            </div>
            <div class="form-group">
                <label>{l s='Product image size' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                <div class="btn-group" role="group">
                    {foreach $imageSizes as $imageSize}
                        <button type="button" class="btn " v-on:click="productImageSize = '{$imageSize.name|escape:'htmlall':'UTF-8'}'"
                                :class="[productImageSize === '{$imageSize.name|escape:'htmlall':'UTF-8'}' ? 'btn-primary' : 'btn-default']">
                            {$imageSize.name|escape:'htmlall':'UTF-8'} {* HTML comment, no escape necessary *}
                            <small>({$imageSize.width|escape:'htmlall':'UTF-8'}x{$imageSize.height|escape:'htmlall':'UTF-8'})</small>
                        </button>
                    {/foreach}
                </div>
            </div>
            <hr>
            <h2>{l s='Filter products' mod='mailchimppro'}</h2>
            <div class="form-group">
                <label>{l s='By status' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn " v-on:click="productSyncFilterActive = [0, 1]"
                            :class="[arrayEquals(productSyncFilterActive, [0, 1]) ? 'btn-primary' : 'btn-default']">
                        {l s='All' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn " v-on:click="productSyncFilterActive = [1]"
                            :class="[arrayEquals(productSyncFilterActive, [1]) ? 'btn-primary' : 'btn-default']">
                        {l s='Only active' mod='mailchimppro'}
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label>{l s='By visibility' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn "
                            v-on:click="productSyncFilterVisibility = ['both', 'catalog', 'search', 'none']"
                            :class="[arrayEquals(productSyncFilterVisibility, ['both', 'catalog', 'search', 'none']) ? 'btn-primary' : 'btn-default']">
                        {l s='All' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn "
                            v-on:click="productSyncFilterVisibility = ['both']"
                            :class="[arrayEquals(productSyncFilterVisibility, ['both']) ? 'btn-primary' : 'btn-default']">
                        {l s='Both' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn "
                            v-on:click="productSyncFilterVisibility = ['catalog']"
                            :class="[arrayEquals(productSyncFilterVisibility, ['catalog']) ? 'btn-primary' : 'btn-default']">
                        {l s='Catalog' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn "
                            v-on:click="productSyncFilterVisibility = ['search']"
                            :class="[arrayEquals(productSyncFilterVisibility, ['search']) ? 'btn-primary' : 'btn-default']">
                        {l s='Search' mod='mailchimppro'}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>