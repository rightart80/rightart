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
    <table class="table table-bordered reports-table">
        <thead>
        <tr>
            <th>{l s='Id' mod='mailchimppro'}</th>
			<th>{l s='Campaign' mod='mailchimppro'}</th>
			<th>{l s='Type' mod='mailchimppro'}</th>			
            <th>{l s='Audience' mod='mailchimppro'}</th>
            <th>{l s='Emails sent' mod='mailchimppro'}</th> 
			<th>{l s='Send time' mod='mailchimppro'}</th>
			<th>{l s='Abuse reports' mod='mailchimppro'}</th>
			<th>{l s='Unsubscribed' mod='mailchimppro'}</th>
			<th>{l s='Forwards' mod='mailchimppro'}</th>
			<th>{l s='Bounces' mod='mailchimppro'}</th>
			<th>{l s='Opens' mod='mailchimppro'}</th>
			<th>{l s='Clicks' mod='mailchimppro'}</th>
			<th>{l s='Revenue' mod='mailchimppro'}</th>
			<th>{l s='Actions' mod='mailchimppro'}</th>
        </tr>
        </thead>
        <tbody>
		{if isset($reports) && $reports}
			{foreach $reports as $report}
				<tr class="clickable-row" data-href="{LinkHelper::getAdminLink('AdminMailchimpProReports', true, [], ['action' => 'single', 'entity_id' => $report.id])|escape:'htmlall':'UTF-8'}">
					<td class="id">{$report.id|escape:'htmlall':'UTF-8'}</td>
					<td class="campaign-info">
						<div><b>{l s='Title:' mod='mailchimppro'}</b> {$report.campaign_title|escape:'htmlall':'UTF-8'}</div>
						<div><b>{l s='Subject:' mod='mailchimppro'}</b> {$report.subject_line|escape:'htmlall':'UTF-8'}</div>						
					</td>
					<td class="type">{$report.type|escape:'htmlall':'UTF-8'}</td>
					<td class="list status-{if isset($report.list_is_active) && $report.list_is_active}active{else}inactive{/if}" data-list-id="{$report.list_id|escape:'htmlall':'UTF-8'}">
						<b>{$report.list_name|escape:'htmlall':'UTF-8'}</b>				
					</td>
					<td>{$report.emails_sent|escape:'htmlall':'UTF-8'}</td>					
					<td>{$report.send_time|escape:'htmlall':'UTF-8'}</td>					
					<td>{$report.abuse_reports|escape:'htmlall':'UTF-8'}</td>					
					<td>{$report.unsubscribed|escape:'htmlall':'UTF-8'}</td>
					<td>
						{if isset($report.forwards) && $report.forwards}
							<div class="fw-count">{l s='Count:' mod='mailchimppro'} {$report.forwards['forwards_count']|escape:'htmlall':'UTF-8'}</div>
							<div class="fw-count">{l s='Opens:' mod='mailchimppro'} {$report.forwards['forwards_opens']|escape:'htmlall':'UTF-8'}</div>
						{/if}
					</td>
					<td>
						{if isset($report.bounces) && $report.bounces}
							<div class="fw-count">{l s='Hard:' mod='mailchimppro'} {$report.bounces['hard_bounces']|escape:'htmlall':'UTF-8'}</div>
							<div class="fw-count">{l s='Soft:' mod='mailchimppro'} {$report.bounces['soft_bounces']|escape:'htmlall':'UTF-8'}</div>
						{/if}
					</td>
					<td>
						{if isset($report.opens) && $report.opens}
							<b>{$report.opens['unique_opens']|escape:'htmlall':'UTF-8'} ({($report.opens['open_rate']|escape:'htmlall':'UTF-8') * 100}%)</b>
						{/if}
					</td>
					<td class="text-center">
						{if isset($report.clicks) && $report.clicks}
							<b>{$report.clicks['clicks_total']|escape:'htmlall':'UTF-8'} ({($report.clicks['click_rate']|escape:'htmlall':'UTF-8') * 100}%)</b>
						{/if}
					</td>
					<td>
						{if isset($report.ecommerce) && $report.ecommerce}
							<b>{$report.ecommerce['total_revenue']|escape:'htmlall':'UTF-8'} {$report.ecommerce['currency_code']|escape:'htmlall':'UTF-8'}</b>
						{/if}
					</td>
					
					<td>
						<div class="btn-group btn-group-xs">
							<a class="btn btn-default btn-delete" href="{LinkHelper::getAdminLink('AdminMailchimpProReports', true, [], ['action' => 'single', 'entity_id' => $report.id])|escape:'htmlall':'UTF-8'}" title="{l s='View' mod='mailchimppro'}">
								<i class="icon icon-eye" aria-hidden="true"></i>
								<span>{l s='View' mod='mailchimppro'}</span>
							</a>
						</div>
					</td>
				</tr>
			{/foreach}
		{/if}
        </tbody>
    </table>
</div>

{literal}
	<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function (event) {						
			$(".clickable-row").click(function() {
				window.location = $(this).data("href");
			});
		});
	</script>
	
	<style type="text/css">
		.clickable-row {
			cursor: pointer;
		}
	</style>
{/literal}