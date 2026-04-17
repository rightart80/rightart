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
            <th>{l s='Name' mod='mailchimppro'}</th>
            <th>{l s='Active' mod='mailchimppro'}</th>
            <th>{l s='Thumbnail' mod='mailchimppro'}</th>
            <th>{l s='Date created' mod='mailchimppro'}</th>
			<th>{l s='Date edited' mod='mailchimppro'}</th>
            {if isset($add_form)}
				<th>
					{*
						<a href="{LinkHelper::getAdminLink('AdminMailchimpProEmailTemplates', true, [], ['action' => 'entityadd'])|escape:'htmlall':'UTF-8'}" title="{l s='Add new template' mod='mailchimppro'}" class="list-toolbar-btn btn btn-success">
							<i class="icon icon-plus" aria-hidden="true"></i>
							<span>{l s='Add new' mod='mailchimppro'}</span>
						</a>
					*}
					<div class="list-toolbar-btn btn btn-success" title="{l s='Add new template' mod='mailchimppro'}" data-toggle="modal" data-target="#new-email-template-modal">
						<i class="icon icon-plus" aria-hidden="true"></i>
						<span>{l s='Add new' mod='mailchimppro'}</span>
					</div>
					<div class="list-toolbar-btn btn btn-success" title="{l s='Email Template Builder' mod='mailchimppro'}" data-toggle="modal" data-target="#new-email-template-builder-modal">
						<i class="icon icon-plus" aria-hidden="true"></i>
						<span>{l s='Open Email Template Builder' mod='mailchimppro'}</span>
					</div>
				</th>
			{else}
				<th>{l s='Actions' mod='mailchimppro'}</th>
			{/if}
        </tr>
        </thead>
        <tbody>
        {foreach $email_templates as $email_template}
            <tr>
                <td class="id">{$email_template.id|escape:'htmlall':'UTF-8'}</td>
                <td class="name">{$email_template.name|escape:'htmlall':'UTF-8'}</td>
                <td>{$email_template.active|escape:'htmlall':'UTF-8'}</td>
                <td><img src="{if $email_template.thumbnail}../modules/mailchimppro/views/img/email_templates/sample.png{else}../modules/mailchimppro/views/img/email_templates/sample.png{/if}" class="img-responsive img-thumbnail"></td>
				{* <td><img src="{if $email_template.thumbnail}{$email_template.thumbnail|escape:'htmlall':'UTF-8'}{else}../modules/mailchimppro/views/img/email_templates/sample.png{/if}" class="img-responsive img-thumbnail"></td> *}
                <td>{$email_template.date_created|escape:'htmlall':'UTF-8'}</td>
				<td>{$email_template.date_edited|escape:'htmlall':'UTF-8'}</td>
                <td>
					<div class="btn-group btn-group-xs">
						<a class="btn btn-default btn-delete" href="{LinkHelper::getAdminLink('AdminMailchimpProEmailTemplates', true, [], ['action' => 'entitydelete', 'entity_id' => $email_template.id])|escape:'htmlall':'UTF-8'}" title="{l s='Delete' mod='mailchimppro'}">
							<i class="icon icon-trash" aria-hidden="true"></i>
							<span>{l s='Delete' mod='mailchimppro'}</span>
						</a>
						{if isset($email_template.editable) && $email_template.editable}
							{*
							<a class="btn btn-default btn-edit" href="{LinkHelper::getAdminLink('AdminMailchimpProEmailTemplates', true, [], ['action' => 'entityedit', 'entity_id' => $email_template.id])|escape:'htmlall':'UTF-8'}" title="{l s='Edit' mod='mailchimppro'}">
								<i class="icon icon-pencil" aria-hidden="true"></i>
								<span>{l s='Edit' mod='mailchimppro'}</span>
							</a>
							*}
							<a href="#" class="btn btn-default btn-edit-template btn-edit" data-id="{$email_template.id|escape:'htmlall':'UTF-8'}" data-name="{$email_template.name|escape:'htmlall':'UTF-8'}" title="{l s='Edit' mod='mailchimppro'}"> {* HTML comment, no escape necessary *}
								<i class="icon icon-pencil" aria-hidden="true"></i>
								<span>{l s='Edit' mod='mailchimppro'}</span>
							</a>
						{else}
							<a href="#" class="btn btn-default btn-edit-template btn-edit disabled" data-id="{$email_template.id|escape:'htmlall':'UTF-8'}" data-name="{$email_template.name|escape:'htmlall':'UTF-8'}" title="{l s='Edit' mod='mailchimppro'}"> {* HTML comment, no escape necessary *}
								<i class="icon icon-pencil" aria-hidden="true"></i>
								<span>{l s='Edit' mod='mailchimppro'}</span>
							</a>
						{/if}
					</div>
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
{if isset($add_form)}
<div id="new-email-template-modal" class="modal fade add-new-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">		
	  <div class="div-default-templates text-center">
		<span id="load-default-template" class="btn btn-primary">{l s='Load default template' mod='mailchimppro'}</span>
	  </div>
	  <div class="panel-heading panel-heading-editing">
		<i class="icon-envelope"></i>{l s='Edit template' mod='mailchimppro'}
	  </div>
	  {$add_form|escape:'htmlall':'UTF-8'} {* HTML comment, no escape necessary *}
	  <button type="button" class="btn btn-default close-modal-button" data-dismiss="modal">{l s='Close' mod='mailchimppro'}</button>
    </div>
  </div>
</div>
<div id="new-email-template-builder-modal" class="modal fade add-new-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document" style="width: 1200px!important;    margin-top: 20px;">
    <div class="modal-content">		
	  <h4 class="text-center" >{l s='Email template Builder' mod='mailchimppro'}</h4>
	  <iframe width="1180"heigh="600" border=0 src="https://emailbuilder.prestaforce.com/index.php?loadtemplate=1" style=" margin: 0 auto; display: block; border: 0 none;    height: 735px;"></iframe>

	  <button type="button" class="btn btn-default close-modal-button" data-dismiss="modal">{l s='Close' mod='mailchimppro'}</button>
    </div>
  </div>
</div>
{/if}


{literal}
	<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function (event) {
			$(document).on('focusin', function(e) {
				if ($(e.target).closest(".mce-window").length) {
					e.stopImmediatePropagation();
				}
			});
			
			$(document).on('click', '#load-default-template', function(e) {
				tinyMCE.get('templateContent').execCommand('mceSetContent', false, defaultTemplateContent);
			});
			
			$(document).on('click', '.btn-edit-template', function(e) {
				var templateId = $(this).data('id');
				var templateName = $(this).data('name');
				
				$.get(emailTemplatesPath + templateId + ".html", function (result) {
					//setTimeout(function(){
						tinyMCE.get('templateContent').execCommand('mceSetContent', false, result);
					//}, 200, result);
					$('#new-email-template-modal').find('#templateName').val(templateName);
					$('#new-email-template-modal').modal('show').addClass('editing').find('#configuration_form').append('<input type="hidden" name="editing" value="' + templateId + '" />');					
				});				
			});
			
			$('#new-email-template-modal').on('shown.bs.modal', function (e) {
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
			$('#new-email-template-modal .panel-heading-editing').insertAfter('#new-email-template-modal form .panel-heading-new');
		});
	</script>
{/literal}