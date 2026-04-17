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
            <th>{l s='Status' mod='mailchimppro'}</th>
            <th>{l s='Total operations' mod='mailchimppro'}</th>
            <th>{l s='Finished operations' mod='mailchimppro'}</th>
            <th>{l s='Failed operations' mod='mailchimppro'}</th>
            <th>{l s='Submitted at' mod='mailchimppro'}</th>
            <th>{l s='Completed at' mod='mailchimppro'}</th>
            <th>{l s='Actions' mod='mailchimppro'}</th>
        </tr>
        </thead>
        <tbody>
        {foreach $batches as $batch}
            <tr>
                <td>{$batch.id|escape:'htmlall':'UTF-8'}</td>
                <td>{$batch.status|escape:'htmlall':'UTF-8'}</td>
                <td>{$batch.total_operations|escape:'htmlall':'UTF-8'}</td>
                <td>{$batch.finished_operations|escape:'htmlall':'UTF-8'}</td>
                <td>{$batch.errored_operations|escape:'htmlall':'UTF-8'}</td>
                <td>{$batch.submitted_at|escape:'htmlall':'UTF-8'}</td>
                <td>{$batch.completed_at|escape:'htmlall':'UTF-8'}</td>
                <td>
					<div class="btn-group btn-group-xs">
						<a class="btn btn-default" href="{LinkHelper::getAdminLink('AdminMailchimpProBatches', true, [], ['action' => 'entitydelete', 'entity_id' => $batch.id])|escape:'htmlall':'UTF-8'}" title="{l s='Delete' mod='mailchimppro'}">
							<i class="icon icon-trash" aria-hidden="true"></i>
							<span>{l s='Delete' mod='mailchimppro'}</span>
						</a>
						
						<a class="btn btn-default" href="{LinkHelper::getAdminLink('AdminMailchimpProBatches', true, [], ['action' => 'single', 'entity_id' => $batch.id])|escape:'htmlall':'UTF-8'}" title="{l s='View' mod='mailchimppro'}">
							<i class="icon icon-search" aria-hidden="true"></i>
							<span>{l s='View' mod='mailchimppro'}</span>
						</a>
					</div>
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>