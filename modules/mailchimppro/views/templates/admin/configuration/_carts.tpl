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
 <div v-cloak v-if="validApiKey" class="panel panel-carts" v-show="currentPage === 'carts'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-cart-arrow-down la-2x"></i>
            {l s='Abandoned carts' mod='mailchimppro'}
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
        <h2>{l s='Sync abandoned carts' mod='mailchimppro'}</h2>
        <div class="form-group">
            <div class="clearfix"></div>
            <div class="btn-group large" role="group">
                <button type="button" class="btn " v-on:click="syncCarts = true"
                        :class="syncCarts == true ? 'btn-primary' : 'btn-default'">
                    {l s='Yes' mod='mailchimppro'}
                </button>
                <button type="button" class="btn " v-on:click="syncCarts = false"
                        :class="syncCarts == false ? 'btn-primary' : 'btn-default'">
                    {l s='No' mod='mailchimppro'}
                </button>
            </div>
        </div>
        <hr>
        <div :class="syncCarts == true ? 'sync-settings-container' : 'sync-settings-container no-sync-type'">
            <h2>{l s='Cart recovery URL' mod='mailchimppro'}</h2>
            <div class="alert alert-info">
                <p>{l s='Passwordless Cart Recovery URL Explained' mod='mailchimppro'}</p>

                <p>{l s='When a customer abandons their shopping cart, there are two ways they can recover it:' mod='mailchimppro'}</p>

                <p>{l s='Passwordless Cart Recovery URL: With this method, the customer receives a special link (URL) via email. They can open this link from any browser or device, even if it is different from the one where they originally added items to the cart.' mod='mailchimppro'}</p>

                <p>{l s='Logged-In Account or Same Device/Browser: Alternatively, the customer can recover their cart by logging in to their account on the same device and browser where they initially added products. They can also open the abandoned cart email directly on that same device. In summary, the passwordless URL provides more flexibility, allowing customers to access their cart from anywhere, while the other method requires them to use the same device or log in to their account.' mod='mailchimppro'}</p>

            </div>
            <div class="form-group">
                <label>{l s='Passwordless URL' mod='mailchimppro'}</label>
                <div class="clearfix"></div>
                <div class="btn-group large" role="group">
                    <button type="button" class="btn " v-on:click="syncCartsPassw = true"
                            :class="syncCartsPassw == true ? 'btn-primary' : 'btn-default'">
                        {l s='Yes' mod='mailchimppro'}
                    </button>
                    <button type="button" class="btn " v-on:click="syncCartsPassw = false"
                            :class="syncCartsPassw == false ? 'btn-primary' : 'btn-default'">
                        {l s='No' mod='mailchimppro'}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>