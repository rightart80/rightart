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
		{*
            <th>{l s='Tag' mod='mailchimppro'}</th>
			<th>{l s='Reputation' mod='mailchimppro'}</th>
            <th>{l s='Sent' mod='mailchimppro'}</th>
			<th>{l s='Rejects' mod='mailchimppro'}</th>
            <th>{l s='Opens' mod='mailchimppro'}</th>
            <th>{l s='Clicks' mod='mailchimppro'}</th>            			
		*}
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
					{* **********
					<div class="list-toolbar-btn btn btn-success" title="{l s='Add new tag' mod='mailchimppro'}" data-toggle="modal" data-target="#new-tag-modal">
						<i class="icon icon-plus" aria-hidden="true"></i>
						<span>{l s='Add new' mod='mailchimppro'}</span>
					</div>
					********** *}
				</th>
			{else}
				<th>{l s='Actions' mod='mailchimppro'}</th>
			{/if}
        </tr>
        </thead>
        <tbody>
		{if isset($tags) && $tags}
			{foreach $tags as $tag}
				<tr>
				{*
					<td class="name">{$tag.tag|escape:'htmlall':'UTF-8'}</td>
					<td class="reputation">{$tag.reputation|escape:'htmlall':'UTF-8'}</td>
					<td>{$tag.sent|escape:'htmlall':'UTF-8'}</td>
					<td>{$tag.rejects|escape:'htmlall':'UTF-8'}</td>
					<td>{$tag.opens|escape:'htmlall':'UTF-8'}</td>
					<td>{$tag.clicks|escape:'htmlall':'UTF-8'}</td>
				*}
					<td class="id">{$tag.id|escape:'htmlall':'UTF-8'}</td>
					<td class="name">{$tag.name|escape:'htmlall':'UTF-8'}</td>
					<td>{$tag.member_count|escape:'htmlall':'UTF-8'}</td>
					<td>{$tag.type|escape:'htmlall':'UTF-8'}</td>
					<td>{$tag.created_at|escape:'htmlall':'UTF-8'}</td>
					<td>{$tag.updated_at|escape:'htmlall':'UTF-8'}</td>
					<td>
						<div class="btn-group btn-group-xs">
							<a class="btn btn-default btn-delete" href="{LinkHelper::getAdminLink('AdminMailchimpProTags', true, [], ['action' => 'entitydelete', 'entity_id' => $tag.id])|escape:'htmlall':'UTF-8'}" title="{l s='Delete' mod='mailchimppro'}">
								<i class="icon icon-trash" aria-hidden="true"></i>
								<span>{l s='Delete' mod='mailchimppro'}</span>
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
<div id="new-tag-modal" class="modal fade add-new-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
	  {$add_form|escape:'htmlall':'UTF-8'}
	  <button type="button" class="btn btn-default close-modal-button" data-dismiss="modal">{l s='Close' mod='mailchimppro'}</button>
    </div>
  </div>
</div>
{/if}

{literal}
	<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function (event) {
			
			/* $('#new-email-template-modal').on('shown.bs.modal', function (e) {
				tinyMCE.get('templateContent').execCommand('mceSetContent', false, tinyMCE.get('templateContent').getContent());
			});

			$('#new-email-template-modal').on('hide.bs.modal', function (e) {
				if ($(this).hasClass('editing')) {
					tinyMCE.get('templateContent').execCommand('mceSetContent', false, '');
					$(this).removeClass('editing');
					$(this).find('#templateName').val('');
					$(this).find('input[name="editing"]').remove();
				}
			});
			
			$('#new-email-template-modal form .panel-heading').addClass('panel-heading-new');
			$('#new-email-template-modal .panel-heading-editing').insertAfter('#new-email-template-modal form .panel-heading-new'); */
		});
	</script>
	
	<style type="text/css">
		#new-tag-modal .form-wrapper {
		    display: flex;    
			gap: 15px;
			font-size: 16px;
		}
		#new-tag-modal .form-group:first-child {
			width: 40%;
		}
		#new-tag-modal .form-group:nth-child(2) {
		    width: 20%;
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
		#new-tag-modal .form-group select {
			width: 75px!important;
			/* text-align: center; */
			font-size: inherit;
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