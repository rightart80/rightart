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
    <table class="table table-bordered campaign-table">
        <thead>
        <tr>
            <th>{l s='Id' mod='mailchimppro'}</th>
			<th>{l s='Type' mod='mailchimppro'}</th>
			<th>{l s='Create time' mod='mailchimppro'}</th>
            <th>{l s='Status' mod='mailchimppro'}</th>
            <th>{l s='Emails sent' mod='mailchimppro'}</th> 
			<th>{l s='Send time' mod='mailchimppro'}</th>
			<th>{l s='Content type' mod='mailchimppro'}</th>
			<th>{l s='Resendable' mod='mailchimppro'}</th>
			<th>{l s='Recipients' mod='mailchimppro'}</th>
			<th>{l s='Settings' mod='mailchimppro'}</th>
            {if isset($add_form)}
				<th>
					{*
						<a href="{LinkHelper::getAdminLink('AdminMailchimpProEmailTemplates', true, [], ['action' => 'entityadd'])|escape:'htmlall':'UTF-8'}" title="{l s='Add new template' mod='mailchimppro'}" class="list-toolbar-btn btn btn-success">
							<i class="icon icon-plus" aria-hidden="true"></i>
							<span>{l s='Add new' mod='mailchimppro'}</span>
						</a>
					*}
					<div class="list-toolbar-btn btn btn-success" title="{l s='Add new campaign' mod='mailchimppro'}" data-toggle="modal" data-target="#new-campaign-modal">
						<i class="icon icon-plus" aria-hidden="true"></i>
						<span>{l s='Add new' mod='mailchimppro'}</span>
					</div>
				</th>
			{else}
				<th>{l s='Actions' mod='mailchimppro'}</th>
			{/if}
        </tr>
        </thead>
        <tbody>
		{if isset($campaigns) && $campaigns}
			{foreach $campaigns as $campaign}
				<tr>
					<td class="id">{$campaign.id|escape:'htmlall':'UTF-8'}</td>
					<td class="type">{$campaign.type|escape:'htmlall':'UTF-8'}</td>
					<td>{$campaign.create_time|escape:'htmlall':'UTF-8'}</td>
					<td class="status {$campaign.status|escape:'htmlall':'UTF-8'}">
						<span>{$campaign.status|escape:'htmlall':'UTF-8'}</span>
					</td>
					<td>{$campaign.emails_sent|escape:'htmlall':'UTF-8'}</td>
					<td>{$campaign.send_time|escape:'htmlall':'UTF-8'}</td>					
					<td>{$campaign.content_type|escape:'htmlall':'UTF-8'}</td>
					<td>{$campaign.resendable|escape:'htmlall':'UTF-8'}</td>
					<td>
						
						{if isset($campaign.recipients['recipient_count'])}{l s='Recipient count:' mod='mailchimppro'} {$campaign.recipients['recipient_count']|escape:'htmlall':'UTF-8'}<br>{/if}
						{if isset($campaign.recipients['list_name'])}{l s='List name:' mod='mailchimppro'} {$campaign.recipients['list_name']|escape:'htmlall':'UTF-8'}{/if}

					</td>
					<td>

							{if isset($campaign.settings['subject_line'])}<b>{l s='Subject:' mod='mailchimppro'}</b> {$campaign.settings['subject_line']|escape:'htmlall':'UTF-8'}<br>{/if}
							{if isset($campaign.settings['title'])}<b>{l s='Title:' mod='mailchimppro'}</b> {$campaign.settings['title']|escape:'htmlall':'UTF-8'}<br>{/if}
							{if isset($campaign.settings['from_name']) && isset($campaign.settings['reply_to'])}<b>{l s='From:' mod='mailchimppro'}</b> {$campaign.settings['from_name']|escape:'htmlall':'UTF-8'} ({$campaign.settings['reply_to']|escape:'htmlall':'UTF-8'}){/if}

					</td>
					<td>
						<div class="btn-group btn-group-xs">
							<a class="btn btn-default btn-delete" href="{LinkHelper::getAdminLink('AdminMailchimpProCampaigns', true, [], ['action' => 'entitydelete', 'entity_id' => $campaign.id])|escape:'htmlall':'UTF-8'}" title="{l s='Delete' mod='mailchimppro'}">
								<i class="icon icon-trash" aria-hidden="true"></i>
								<span>{l s='Delete' mod='mailchimppro'}</span>
							</a>
							<a href="#" class="btn btn-default btn-edit-campaign btn-edit" data-id="{$campaign.id|escape:'htmlall':'UTF-8'}" data-name="{$campaign.settings['title']|escape:'htmlall':'UTF-8'}" title="{l s='Edit' mod='mailchimppro'}">
								<i class="icon icon-pencil" aria-hidden="true"></i>
								<span>{l s='Edit' mod='mailchimppro'}</span>
							</a>
							{* if $campaign.resendable *}
								<a class="btn btn-default btn-delete" href="{LinkHelper::getAdminLink('AdminMailchimpProCampaigns', true, [], ['action' => 'entitysend', 'entity_id' => $campaign.id])|escape:'htmlall':'UTF-8'}" title="{l s='Send campaign' mod='mailchimppro'}">
									<i class="icon icon-send" aria-hidden="true"></i>
									<span>{l s='Send' mod='mailchimppro'}</span>
								</a>
							{* /if *}
						</div>
					</td>
				</tr>
			{/foreach}
		{/if}
        </tbody>
    </table>
</div>
{if isset($add_form)}
<div id="new-campaign-modal" class="modal fade add-new-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
	  <div class="panel-heading panel-heading-editing">
		<i class="icon-pencil"></i>{l s='Edit campaign' mod='mailchimppro'}
	  </div>
	  {$add_form|escape:'htmlall':'UTF-8'}
	  <button type="button" class="btn btn-default close-modal-button" data-dismiss="modal">{l s='Close' mod='mailchimppro'}</button>
    </div>
  </div>
</div>
{/if}

{literal}
	<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function (event) {
				
			$(document).on('click', '.btn-edit-campaign', function(e) {
				var campaignId = $(this).data('id');
				var campaignName = $(this).data('name');
				
				$('#new-campaign-modal').find('#campaignName').val(campaignName);
				$('#new-campaign-modal').modal('show').addClass('editing').find('#configuration_form').append('<input type="hidden" name="editing" value="' + campaigntId + '" />');		
			});
			
			$('#new-campaign-modal').on('hide.bs.modal', function (e) {
				if ($(this).hasClass('editing')) {
					$(this).removeClass('editing');
					$(this).find('#campaignName').val('');
					$(this).find('input[name="editing"]').remove();
				}
			});
			
			$('#new-campaign-modal form .panel-heading').addClass('panel-heading-new');
			$('#new-campaign-modal .panel-heading-editing').insertAfter('#new-campaign-modal form .panel-heading-new'); 
		});
	</script>
	
	<style type="text/css">
		.campaign-table td.status span {
			padding: 3px 9px;			
			border-radius: 5px;			
		}
		.campaign-table td.status.sent span {
			background: #008b31 !important;
			color: white !important;
		}
		.campaign-table td.status.save span {
		    background: #3fb9d5 !important;
		    color: white !important;
		}
		#new-segment-modal .form-wrapper {
		    display: flex;
			flex-wrap: wrap;			
			gap: 15px;
			font-size: 16px;
		}
		#new-segment-modal .form-group:not(:last-child) {
			width: 33.3333%;
		}
		#new-segment-modal .form-group:last-child {
	        width: calc(100% + 10px);
		}
		#new-segment-modal .form-group select {
			width: 100%!important;
			/* text-align: center; */
			/* font-size: inherit; */
		}
		#new-tag-modal .form-group:last-child {
			width: 40%;
		}
		#new-tag-modal .form-group label {
		    width: 100%;
			padding: 4px 5px;
			text-align: center;
		}
		#new-tag-modal .form-group:first-child label {
			text-align: right;
		}
		#new-tag-modal .form-group label + div {
			display: flex;
			flex-direction: row-reverse;
			gap: 15px;
		}		
		#new-tag-modal .form-group .help-block {
			margin: 0;
			display: flex;
			align-items: center;
			flex-grow: 1;
			line-height: 1;
			flex-shrink: 0;
			color: #343434;
			font-weight: 600;
		}
	</style>
{/literal}