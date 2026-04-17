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
    <table class="table table-bordered report-table">
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
        </tr>
        </thead>
        <tbody>
			<tr>
				<td class="id">{$entity.id|escape:'htmlall':'UTF-8'}</td>
				<td class="campaign-info">
					<div><b>{l s='Title:' mod='mailchimppro'}</b> {$entity.campaign_title|escape:'htmlall':'UTF-8'}</div>
					<div><b>{l s='Subject:' mod='mailchimppro'}</b> {$entity.subject_line|escape:'htmlall':'UTF-8'}</div>						
				</td>
				<td class="type">{$entity.type|escape:'htmlall':'UTF-8'}</td>
				<td class="list status-{if isset($entity.list_is_active) && $entity.list_is_active}active{else}inactive{/if}" data-list-id="{$entity.list_id|escape:'htmlall':'UTF-8'}">
					<b>{$entity.list_name|escape:'htmlall':'UTF-8'}</b>				
				</td>
				<td>{$entity.emails_sent|escape:'htmlall':'UTF-8'}</td>					
				<td>{$entity.send_time|escape:'htmlall':'UTF-8'}</td>					
				<td>{$entity.abuse_reports|escape:'htmlall':'UTF-8'}</td>					
				<td>{$entity.unsubscribed|escape:'htmlall':'UTF-8'}</td>
				<td>
					{if isset($entity.forwards) && $entity.forwards}
						<div class="fw-count">{l s='Count:' mod='mailchimppro'} {$entity.forwards['forwards_count']|escape:'htmlall':'UTF-8'}</div>
						<div class="fw-count">{l s='Opens:' mod='mailchimppro'} {$entity.forwards['forwards_opens']|escape:'htmlall':'UTF-8'}</div>
					{/if}
				</td>
				<td>
					{if isset($entity.bounces) && $entity.bounces}
						<div class="fw-count">{l s='Hard:' mod='mailchimppro'} {$entity.bounces['hard_bounces']|escape:'htmlall':'UTF-8'}</div>
						<div class="fw-count">{l s='Soft:' mod='mailchimppro'} {$entity.bounces['soft_bounces']|escape:'htmlall':'UTF-8'}</div>
					{/if}
				</td>
				<td>
					{if isset($entity.opens) && $entity.opens}
						<b>{$entity.opens['unique_opens']|escape:'htmlall':'UTF-8'} ({($entity.opens['open_rate']|escape:'htmlall':'UTF-8') * 100}%)</b>
					{/if}
				</td>
				<td class="text-center">
					{if isset($entity.clicks) && $entity.clicks}
						<b>{$entity.clicks['clicks_total']|escape:'htmlall':'UTF-8'} ({($entity.clicks['click_rate']|escape:'htmlall':'UTF-8') * 100}%)</b>
					{/if}
				</td>
				<td>
					{if isset($entity.ecommerce) && $entity.ecommerce}
						<b>{$entity.ecommerce['total_revenue']|escape:'htmlall':'UTF-8'} {$entity.ecommerce['currency_code']|escape:'htmlall':'UTF-8'}</b>
					{/if}
				</td>
			</tr>
        </tbody>
    </table>
	
	<div class="card-group">
		{if isset($entity.opens) && $entity.opens}
			<div class="card {* bg-success *}">
				<div class="card-body text-center">
					<div>
						<span class="h2">{$entity.opens['unique_opens']|escape:'htmlall':'UTF-8'}</span>
						<div>{l s='Opened' mod='mailchimppro'}</div>
					</div>
				</div>
			</div>
		{/if}
		{if isset($entity.clicks) && $entity.clicks}
			<div class="card {* bg-primary *}">
				<div class="card-body text-center">
					<div>
						<span class="h2">{$entity.clicks['clicks_total']|escape:'htmlall':'UTF-8'}</span>
						<div>{l s='Clicked' mod='mailchimppro'}</div>
					</div>
				</div>
			</div>
		{/if}
		{if isset($entity.bounces) && $entity.bounces}
			<div class="card {* bg-warning *}">
				<div class="card-body text-center">
					<div>
						<span class="h2">{$entity.bounces['hard_bounces']|escape:'htmlall':'UTF-8'}</span>
						<div>{l s='Bounced' mod='mailchimppro'}</div>
					</div>
				</div>
			</div>
		{/if}
		<div class="card {* bg-danger *}">
			<div class="card-body text-center">
				<div>
					<span class="h2">{$entity.clicks['clicks_total']|escape:'htmlall':'UTF-8'}</span>
					<div>{l s='Unsubscribed' mod='mailchimppro'}</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row-statistics">
		<div>
			<div class="d-flex flex-column">
				<div class="d-flex justify-content-between">
					<span>{l s='Successful deliveries' mod='mailchimppro'}</span>
					<span>{$entity.emails_sent|escape:'htmlall':'UTF-8'}</span>
				</div>
				<div class="d-flex justify-content-between">
					<span>{l s='Total opens' mod='mailchimppro'}</span>
					<span>{$entity.opens['opens_total']|escape:'htmlall':'UTF-8'}</span>
				</div>
				<div class="d-flex justify-content-between">
					<span>{l s='Last opened' mod='mailchimppro'}</span>
					<span>{if $entity.opens['last_open']}{$entity.opens['last_open']|escape:'htmlall':'UTF-8'}{else}N/A{/if}</span>
				</div>
				<div class="d-flex justify-content-between">
					<span>{l s='Forwarded ' mod='mailchimppro'}</span>
					<span>{$entity.forwards['forwards_count']|escape:'htmlall':'UTF-8'}</span>
				</div>
			</div>
		</div>
		<div>
			<div class="d-flex flex-column">
				<div class="d-flex justify-content-between">
					<span>{l s='Clicks per unique opens' mod='mailchimppro'}</span>
					<span>{($entity.clicks['click_rate']|escape:'htmlall':'UTF-8') * 100}%</span>
				</div>
				<div class="d-flex justify-content-between">
					<span>{l s='Total clicks' mod='mailchimppro'}</span>
					<span>{$entity.clicks['clicks_total']|escape:'htmlall':'UTF-8'}</span>
				</div>
				<div class="d-flex justify-content-between">
					<span>{l s='Last clicked' mod='mailchimppro'}</span>
					<span>{if $entity.clicks['last_click']}{$entity.clicks['last_click']|escape:'htmlall':'UTF-8'}{else}N/A{/if}</span>
				</div>
				<div class="d-flex justify-content-between">
					<span>{l s='Abuse reports ' mod='mailchimppro'}</span>
					<span>{$entity.abuse_reports|escape:'htmlall':'UTF-8'}</span>
				</div>
			</div>
		</div>
	</div>
	
	<div class="card-group">
		{if isset($entity.opens) && $entity.opens}
			<div class="card {* bg-success *}">
				<div class="card-body text-center">
					<div>
						<span class="h2">{$entity.ecommerce['total_orders']|escape:'htmlall':'UTF-8'}</span>
						<div>{l s='Orders' mod='mailchimppro'}</div>
					</div>
				</div>
			</div>
		{/if}
		{if isset($entity.clicks) && $entity.clicks}
			<div class="card {* bg-primary *}">
				<div class="card-body text-center">
					<div>
						<span class="h2">
							{if $entity.ecommerce['total_orders']}
								{($entity.ecommerce['total_revenue']|escape:'htmlall':'UTF-8') / ($entity.ecommerce['total_orders'])}
							{else}
								0
							{/if}
							{$entity.ecommerce['currency_code']|escape:'htmlall':'UTF-8'}</span>
						<div>{l s='Average order revenue' mod='mailchimppro'}</div>
					</div>
				</div>
			</div>
		{/if}
		{if isset($entity.bounces) && $entity.bounces}
			<div class="card {* bg-warning *}">
				<div class="card-body text-center">
					<div>
						<span class="h2">{$entity.ecommerce['total_revenue']|escape:'htmlall':'UTF-8'} {$entity.ecommerce['currency_code']|escape:'htmlall':'UTF-8'}</span>
						<div>{l s='Total revenue' mod='mailchimppro'}</div>
					</div>
				</div>
			</div>
		{/if}
	</div>
	
	<div class="text-center">
		<div class="d-inline-block">
			<img class="img-responsive" src="../modules/mailchimppro/views/img/24h-report.png" height="389" width="1277">
		</div>	
	</div>
</div>

{literal}
	<style type="text/css">
		.report-table,
		.row-statistics {
		    margin-bottom: 50px !important;
		}
		.row-statistics {
			font-size: 1rem;
			gap: 50px;
			display: flex;
		}
		.row-statistics > div {
			flex: 1 0 0;
		}
		.row-statistics .d-flex {
			display: flex;
		}
		.row-statistics .flex-column {
			margin: auto;
			gap: 10px 15px;
			flex-direction: column;
		}
		.row-statistics .justify-content-between {
		    justify-content: space-between;
			background: url(../modules/mailchimppro/views/img/bg-leaders.png) transparent 0 1rem repeat-x;
		}
		.row-statistics .justify-content-between span {
		    background: white;			
		}
		.row-statistics .justify-content-between span:first-child {
			padding-right: 5px;
		}
		.row-statistics .justify-content-between span:last-child {
			padding-left: 5px;
		}		
		@media (min-width: 576px) {
			.card {
				position: relative;
				display: flex;
				flex-direction: column;
				min-width: 0;
				word-wrap: break-word;
				background-color: #fff;
				background-clip: border-box;
				border: 1px solid rgba(0,0,0,.125);
				border-radius: 0.25rem;
			}
			.card-group {
				display: flex;
				flex-flow: row wrap;
			    margin-bottom: 50px;
			}
			.card-group>.card {
				flex: 1 0 0%;
				margin-bottom: 0;
				
				padding: 2rem;
			}
			.card-group>.card:not(:last-child) {
				border-top-right-radius: 0;
				border-bottom-right-radius: 0;
			}
			.card-group>.card:not(:first-child) {
				border-top-left-radius: 0;
				border-bottom-left-radius: 0;
			}
		}
	</style>
{/literal}