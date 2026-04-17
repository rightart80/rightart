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
            <th>{l s='Id' mod='mailchimppro'}</th>
			<th>{l s='Name' mod='mailchimppro'}</th>
			<th>{l s='Member count' mod='mailchimppro'}</th>
            <th>{l s='Type' mod='mailchimppro'}</th>
            <th>{l s='Created at' mod='mailchimppro'}</th> 
			<th>{l s='Updated at' mod='mailchimppro'}</th>
            {if isset($add_form)}
				<th>
					{*
						<a href="{LinkHelper::getAdminLink('AdminMailchimpProEmailTemplates', true, [], ['action' => 'entityadd'])|escape:'htmlall':'UTF-8'}" title="{l s='Add new template' mod='mailchimppro'}" class="list-toolbar-btn btn btn-success">
							<i class="icon icon-plus" aria-hidden="true"></i>
							<span>{l s='Add new' mod='mailchimppro'}</span>
						</a>
					*}
					<div class="list-toolbar-btn btn btn-success" title="{l s='Add new segment' mod='mailchimppro'}" data-toggle="modal" data-target="#new-segment-modal">
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
		{if isset($segments) && $segments}
			{foreach $segments as $segment}
				<tr>
					<td class="id">{$segment.id|escape:'htmlall':'UTF-8'}</td>
					<td class="name">{$segment.name|escape:'htmlall':'UTF-8'}</td>
					<td>{$segment.member_count|escape:'htmlall':'UTF-8'}</td>
					<td>{$segment.type|escape:'htmlall':'UTF-8'}</td>
					<td>{$segment.created_at|escape:'htmlall':'UTF-8'}</td>
					<td>{$segment.updated_at|escape:'htmlall':'UTF-8'}</td>
					<td>
						<div class="btn-group btn-group-xs">
							<a class="btn btn-default btn-delete" href="{LinkHelper::getAdminLink('AdminMailchimpProSegments', true, [], ['action' => 'entitydelete', 'entity_id' => $segment.id])|escape:'htmlall':'UTF-8'}" title="{l s='Delete' mod='mailchimppro'}">
								<i class="icon icon-trash" aria-hidden="true"></i>
								<span>{l s='Delete' mod='mailchimppro'}</span>
							</a>
							<a href="#" class="btn btn-default btn-edit-segment btn-edit" data-id="{$segment.id|escape:'htmlall':'UTF-8'}" data-name="{$segment.name|escape:'htmlall':'UTF-8'}" title="{l s='Edit' mod='mailchimppro'}"> {* HTML comment, no escape necessary *}
								<i class="icon icon-pencil" aria-hidden="true"></i>
								<span>{l s='Edit' mod='mailchimppro'}</span>
							</a>
						</div>
					</td>
				</tr>
			{/foreach}
		{/if}
        </tbody>
    </table>
</div>
{if isset($add_form)}
<div id="new-segment-modal" class="modal fade add-new-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
	  <div class="panel-heading panel-heading-editing">
		<i class="icon-pencil"></i>{l s='Edit segment' mod='mailchimppro'}
	  </div>
	  {$add_form|escape:'htmlall':'UTF-8'} {* HTML comment, no escape necessary *}
	  <button type="button" class="btn btn-default close-modal-button" data-dismiss="modal">{l s='Close' mod='mailchimppro'}</button>
    </div>
  </div>
</div>
{/if}

{literal}
	<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function (event) {
				
			$(document).on('click', '.btn-edit-segment', function(e) {
				var segmentId = $(this).data('id');
				var segmentName = $(this).data('name');
				
				$('#new-segment-modal').find('#segmentName').val(segmentName);
				$('#new-segment-modal').modal('show').addClass('editing').find('#configuration_form').append('<input type="hidden" name="editing" value="' + segmentId + '" />');		
			});
			
			$('#new-segment-modal').on('hide.bs.modal', function (e) {
				if ($(this).hasClass('editing')) {
					$(this).removeClass('editing');
					$(this).find('#segmentName').val('');
					$(this).find('input[name="editing"]').remove();
				}
			});
			
			$('#new-segment-modal form .panel-heading').addClass('panel-heading-new');
			$('#new-segment-modal .panel-heading-editing').insertAfter('#new-segment-modal form .panel-heading-new'); 
		});
	</script>
	
	<style type="text/css">
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