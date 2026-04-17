{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<div id="catalogEI-tab-content-Schedule" class="catalogEI-tab-content" style="display: none;">
    <div id="catalogEI-schedule" class="panel catalogEI-tab form-horizontal">
        <h3 class="tab"><i class="icon-clock-o"></i> {l s='Cron' mod='ipcatalogexportimport'}</h3>
        <div class="alert alert-info alert-dismissible fade in">
            <p>
                {l s='You can set cron jobs and export your catalog any time you want. To execute your cron tasks, please insert the following line in your cron tasks manager.' mod='ipcatalogexportimport'}<br>
            </p>
            <br>
            <ul class="list-unstyled">
                <li><code>0 0 * * * curl "{$schedule_url}"</code></li>
            </ul>
            <strong>{l s='Note: ' mod='ipcatalogexportimport'}</strong>
            {l s='If you want to send the export to specific emails among the enabled, append the needed IDs in form of "&email_ids=m,n,l" to the end of the URL, like ' mod='ipcatalogexportimport'}
            "{$schedule_url}&email_ids=1,3,4"{l s='. For FTP addresses append it like "&ftp_ids=2,3,5". This is helpful when you need all enabled addresses not to receive the export file at the same time.' mod='ipcatalogexportimport'}
            <br>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Enable Cron' mod='ipcatalogexportimport'}
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="schedule" id="schedule_yes" value="1" {if $schedule_enabled eq '1'} checked="checked"{/if} />
                    <label for="schedule_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                    <input type="radio" name="schedule" id="schedule_no" value="0" {if $schedule_enabled ne '1'} checked="checked"{/if}  />
                    <label for="schedule_no">{l s='No' mod='ipcatalogexportimport'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">
                </p>
                <br />
            </div>
        </div>
        <div class="schedule collapse {if $schedule_enabled eq '1'} in {/if}">
            <h3 class="save_header">{l s='Select export type' mod='ipcatalogexportimport'}</h3>
            <div class="vomiting">
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='By Email' mod='ipcatalogexportimport'}
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="schedule_use_email" id="schedule_use_email_yes" value="1" {if $schedule_email_enabled eq '1'} checked="checked"{/if} />
                            <label for="schedule_use_email_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                            <input type="radio" name="schedule_use_email" id="schedule_use_email_no" value="0" {if $schedule_email_enabled ne '1'} checked="checked"{/if} />
                            <label for="schedule_use_email_no">{l s='No' mod='ipcatalogexportimport'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <div class="schedule-email collapse {if $schedule_email_enabled eq '1'} in {/if}">
                    <div style="padding:0 30px 30px 30px;">
                        <div style="height:45px;">
                            <button id="refresh_scheduleEmails" class="btn btn-default refresh_button pull-left"><i class="icon-refresh"></i>
                                {l s='Reload table' mod='ipcatalogexportimport'}
                            </button>
                            <button id="schedule_add_new_email" data-toggle="modal" data-target="#schedule_email_modal" type="button" class="btn btn-success pull-right"><i class="icon-plus"></i> {l s=' Add' mod='ipcatalogexportimport'}</button>
                        </div>
                        <table id="scheduleEmails_table" class="table table-striped table-bordered" style="width: 100%; table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th style="width: 1px;"></th>
                                    <th>{l s='ID' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Email' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Timestamp' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Template' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Enabled' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Action' mod='ipcatalogexportimport'}</th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <br>
            <br>
            <div class="vomiting">
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='By FTP' mod='ipcatalogexportimport'}
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="schedule_use_ftp" id="schedule_use_ftp_yes" value="1" {if $schedule_ftp_enabled eq '1'} checked="checked" {/if} />
                            <label for="schedule_use_ftp_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                            <input type="radio" name="schedule_use_ftp" id="schedule_use_ftp_no" value="0" {if $schedule_ftp_enabled ne '1'} checked="checked" {/if} />
                            <label for="schedule_use_ftp_no">{l s='No' mod='ipcatalogexportimport'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <div class="schedule-ftp collapse {if $schedule_ftp_enabled eq '1'} in {/if}">
                    <div style="padding:0 30px 30px 30px;">
                        <div style="height:45px;">
                            <button id="refresh_scheduleFTPs" class="btn btn-default refresh_button pull-left"><i class="icon-refresh"></i>
                                {l s='Reload table' mod='ipcatalogexportimport'}
                            </button>
                            <button id="schedule_add_new_ftp" data-toggle="modal" data-target="#schedule_ftp_modal" type="button" class="btn btn-success pull-right"><i class="icon-plus"></i> {l s=' Add' mod='ipcatalogexportimport'}</button>
                        </div>
                        <table id="scheduleFTPs_table" class="table table-striped table-bordered" style="width: 100%; table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th style="width: 1px;"></th>
                                    <th>{l s='ID' mod='ipcatalogexportimport'}</th>
{*                                    <th>{l s='FTP Type' mod='ipcatalogexportimport'}</th>*}
                                    <th>{l s='FTP URL' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='FTP Username' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='FTP Password' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Timestamp' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Template' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Enabled' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Action' mod='ipcatalogexportimport'}</th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <br />
            <hr style="border-top-color: #d8d8d8;">
            <br />

            <div class="form-group">
                <label class="control-label col-lg-3">
                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='If the export file does not have any product, the empty file will not be sent to the receiver emails or FTP address.' mod='ipcatalogexportimport'}">
                        {l s='Do not send if export is empty' mod='ipcatalogexportimport'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="schedule_dont_send_empty" id="schedule_dont_send_empty_yes" value="1" {if $schedule_dont_send_empty eq '1'} checked="checked" {/if} />
                        <label for="schedule_dont_send_empty_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                        <input type="radio" name="schedule_dont_send_empty" id="schedule_dont_send_empty_no" value="0" {if $schedule_dont_send_empty ne '1'} checked="checked" {/if} />
                        <label for="schedule_dont_send_empty_no">{l s='No' mod='ipcatalogexportimport'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    {*<p class="help-block">
                    </p>*}
                </div>
            </div>
        </div>
    </div>
</div>







