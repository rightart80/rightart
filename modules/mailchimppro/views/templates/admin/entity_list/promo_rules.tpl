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
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>{l s='ID' mod='mailchimppro'}</th>
            <th>{l s='Title' mod='mailchimppro'}</th>
            <th>{l s='Starts at' mod='mailchimppro'}
            <th>{l s='Ends at' mod='mailchimppro'}
            <th>{l s='Amount' mod='mailchimppro'}
            <th>{l s='Type' mod='mailchimppro'}
            <th>{l s='Target' mod='mailchimppro'}
            <th>{l s='Enabled' mod='mailchimppro'}
            <th>{l s='Actions' mod='mailchimppro'}</th>
        </tr>
        </thead>
        <tbody>
        {foreach $promo_rules as $promo_rule}
            <tr>
                <td>{$promo_rule.id|escape:'htmlall':'UTF-8'}</td>
                <td>{$promo_rule.title|escape:'htmlall':'UTF-8'}</td>
                <td>{$promo_rule.starts_at|escape:'htmlall':'UTF-8'}</td>
                <td>{$promo_rule.ends_at|escape:'htmlall':'UTF-8'}</td>
                <td>{$promo_rule.amount|escape:'htmlall':'UTF-8'}</td>
                <td>{$promo_rule.type|escape:'htmlall':'UTF-8'}</td>
                <td>{$promo_rule.target|escape:'htmlall':'UTF-8'}</td>
                <td class="{if $promo_rule.enabled}enabled{else}disabled{/if}" title="{if $promo_rule.enabled}{l s='Enabled' mod='mailchimppro'}{else}{l s='Disabled' mod='mailchimppro'}{/if}">
					{if $promo_rule.enabled}
						<i class="icon-check"></i>
					{else}
						<i class="icon-remove"></i>
					{/if}
				</td>
                <td>
                    <div class="btn-group  btn-group-xs" role="group" aria-label="...">
                        <a class="btn btn-default"
                           href="{LinkHelper::getAdminLink('AdminMailchimpProPromoCodes', true, [], ['action' => 'entitydelete', 'entity_id' => $promo_rule.id])|escape:'htmlall':'UTF-8'}" title="{l s='Delete' mod='mailchimppro'}">
                            <i class="icon icon-trash" aria-hidden="true"></i>
							<span>{l s='Delete' mod='mailchimppro'}</span>
                        </a>

                        <a class="btn btn-default"
                           href="{LinkHelper::getAdminLink('AdminMailchimpProPromoCodes', true, [], ['rule_id' => $promo_rule.id])|escape:'htmlall':'UTF-8'}" title="{l s='Promo codes' mod='mailchimppro'}">
                            <i class="icon icon-file-text" aria-hidden="true"></i>
							<span>{l s='Promo codes' mod='mailchimppro'}</span>
                        </a>
                    </div>

                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>