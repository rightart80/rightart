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
 <div v-cloak v-if="validApiKey" class="panel panel-faq" v-show="currentPage === 'faq'">
    <h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-question-circle la-2x"></i>
            {l s='FAQs' mod='mailchimppro'}
        </span>
    </h3>
    <div class="panel-body">
		<div class="accordion" id="faq-accordions-container">
            <div class="accordion-item">
                <div class="accordion-header" id="accordion-heading1" data-toggle="collapse" data-target="#accordion-body-container1" aria-expanded="false" aria-controls="accordion-body-container1">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='Should I Synchronize every item in my store?' mod='mailchimppro'}
                        </span>
                        <div>
							<i class="material-icons icon-down">&#xe313;</i>
						</div>
                    </div>
                </div>
                <div id="accordion-body-container1" class="accordion-collapse collapse in" aria-labelledby="accordion-heading1">
                    <div class="accordion-body">
                        <p>{l s='Mailchimp uses customer data as the primary payment determinant. You will be charged per client email account. Even while it is not required, we encourage that you do so in order to maximize Mailchimp features such as Customer Journey and sales-based segmentation.'  mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header" id="accordion-heading2" data-toggle="collapse" data-target="#accordion-body-container2" aria-expanded="false" aria-controls="accordion-body-container2">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='What does Hook Based mean within Advanced Settings?' mod='mailchimppro'}
                        </span>
                        <div>
							<i class="material-icons icon-down">&#xe313;</i>
						</div>
                    </div>
                </div>
                <div id="accordion-body-container2" class="accordion-collapse collapse in" aria-labelledby="accordion-heading2">
                    <div class="accordion-body">
                        <p>{l s='Hook-based synchronization will utilize the Prestashop modular hook system to synchronize an instance\'s data; however, in the case of larger stores, this may result in checkout lag. This option is recommended for customers with enough servers or limited data.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header" id="accordion-heading3" data-toggle="collapse" data-target="#accordion-body-container3" aria-expanded="false" aria-controls="accordion-body-container3">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='How can I put up the required cronjob?' mod='mailchimppro'}
                        </span>
                        <div>
                            <i class="material-icons icon-down">&#xe313;</i>
                        </div>
                    </div>
                </div>
                <div id="accordion-body-container3" class="accordion-collapse collapse in" aria-labelledby="accordion-heading3">
                    <div class="accordion-body">
                        <p>{l s='WE developed the module in the same manner as any other official Prestashop module that employs cronjobs, such as Search or Faceted Search. Follow the description under the "Cron Job" Tab if you are unfamiliar with the cronjob settings and provide all information to the server maintenance or technical staff.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header" id="accordion-heading4" data-toggle="collapse" data-target="#accordion-body-container4" aria-expanded="false" aria-controls="accordion-body-container4">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='What does Multi-instance mode entail?' mod='mailchimppro'}
                        </span>
                        <div>
                            <i class="material-icons icon-down">&#xe313;</i>
                        </div>
                    </div>
                </div>
                <div id="accordion-body-container4" class="accordion-collapse collapse in" aria-labelledby="accordion-heading4">
                    <div class="accordion-body">
                        <p>{l s='As suggested by its name, it should not be confused with Prestashop Multistore Option. The module\'s main functionality includes both the core Prestashop Multistore as well as the Multilanguage option. Multi-instance mode refers to two distinct Prestashop instances that operate independently and are synchronized to the same Mailchimp account.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="accordion-item">
                <div class="accordion-header" id="accordion-heading5" data-toggle="collapse" data-target="#accordion-body-container5" aria-expanded="false" aria-controls="accordion-body-container5">
                    <div class="accordion-title h3">
                        <span>
                            <i class="material-icons icon-arrow-forward">&#xe5c8;</i>
                            {l s='Will Mailchimp\'s Unsubscribed accounts be linked with Prestashop?' mod='mailchimppro'}
                        </span>
                        <div>
                            <i class="material-icons icon-down">&#xe313;</i>
                        </div>
                    </div>
                </div>
                <div id="accordion-body-container5" class="accordion-collapse collapse in" aria-labelledby="accordion-heading5">
                    <div class="accordion-body">
                        <p>{l s='Not at present. Due to the fact that Prestashop allows the opportunity to place an order using a Guest account, each order can and will have a unique subscription choice, putting the Mailchimp Unsubscribe option at risk. We suggest manually adding unsubscribed accounts, or using Mailchimp for all marketing emails.' mod='mailchimppro'}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>