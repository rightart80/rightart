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
 <div v-cloak v-if="validApiKey" class="panel panel-log" v-show="currentPage === 'log'">
 	<h3 class="panel-heading">
        <span class="panel-heading-icon-container">
            <i class="las la-server la-2x"></i>
            {l s='Logs' mod='mailchimppro'}
        </span>
    </h3>
    <div class="panel-body">
		<div class="logs-table-container">
			<table id="logsTable" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>#Id</th>
						<th>Request type</th>
						<th>End point</th>
						<th>Is success</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$logs item=log}
						<tr>
							<td>{$log.id|escape:'htmlall':'UTF-8'}</td> {* HTML comment, no escape necessary *}
							<td>{$log.request_type|escape:'htmlall':'UTF-8'}</td> {* HTML comment, no escape necessary *}
							<td>{$log.end_point|escape:'htmlall':'UTF-8'}</td> {* HTML comment, no escape necessary *}
							<td>{$log.is_success|escape:'htmlall':'UTF-8'}</td> {* HTML comment, no escape necessary *}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
    </div>
 </div>
 