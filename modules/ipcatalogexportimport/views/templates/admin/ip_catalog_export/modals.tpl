{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<div class="modal fade" id="errorModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="icon-exclamation-circle" style="color:orange;" aria-hidden="true"></i> {l s='Warning' mod='ipcatalogexportimport'}</h4>
            </div>
            <div class="modal-body">
                <p class="error">{l s='An error occurred.' mod='ipcatalogexportimport'}</p>
                <br>
                <a class="btn btn-default" target="_blank" href="testHref">
                    <i class="icon-external-link"></i> {l s='Contact us' mod='ipcatalogexportimport'}
                </a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='ipcatalogexportimport'}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exportProgress" tabindex="-1" data-keyboard="false" data-backdrop="static" aria-hidden="false">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">{l s='Exporting your data...' mod='ipcatalogexportimport'}</h4>
            </div>

            <div class="modal-body">
                <div class="alert alert-warning" id="export_details_stop" style="display:none;">
                    {l s='Aborting, please wait...' mod='ipcatalogexportimport'}
                </div>
                <p id="export_details_progressing" style="display: none;">
                    {l s='Exporting your data...' mod='ipcatalogexportimport'}
                </p>
                <div class="alert alert-success" id="export_details_finished" style="">
                    {l s='Data exported' mod='ipcatalogexportimport'}
                </div>
                <div id="export_messages_div" style="max-height:250px; overflow:auto;">
                    <div class="alert alert-danger" id="export_details_error" style="display:none;">
                        {l s='Errors occurred' mod='ipcatalogexportimport'}:<br><ul></ul>
                    </div>
                    <div class="alert alert-warning" id="export_details_post_limit" style="display:none;">
                        {l s='Warning, the current export may require a PHP setting update, to allow more data to be transferred. If the current export stops before the end, you should increase your PHP "post_max_size" setting to' mod='ipcatalogexportimport'} <span id="export_details_post_limit_value">16MB</span>{l s='MB at least, and try again.' mod='ipcatalogexportimport'}
                    </div>
                    <div class="alert alert-warning" id="export_details_warning" style="">
                        {l s='Some errors were detected. Please check the details' mod='ipcatalogexportimport'}:<br><ul><li>{l s='The confirmation email couldn\'t be sent, but the export is successful. Yay!' mod='ipcatalogexportimport'}</li></ul>
                    </div>
                    <div class="alert alert-info" id="export_details_info" style="display:none;">
                        {l s='We made the following adjustments' mod='ipcatalogexportimport'}:<br><ul></ul>
                    </div>
                </div>

                <div id="export_progress_div" style="">
                    <div class="pull-right" id="export_progression_details" default-value="{l s='Exporting your data...' mod='ipcatalogexportimport'}">100/100</div>
                    <div class="progress" style="display: block; width: 100%">
                        <div class="progress-bar progress-bar-info" role="progressbar" style="width: 0%" id="export_progressbar_done2">
                            <span>{l s='Linking related products...' mod='ipcatalogexportimport'}</span>
                        </div>
                        <div class="progress-bar progress-bar-success" role="progressbar" style="width: 100%;" id="export_progressbar_done">
                            <span><span id="export_progression_done">100</span>% {l s='exported' mod='ipcatalogexportimport'}
                            </span>
                        </div>
                        <div class="progress-bar progress-bar-success progress-bar-stripes active" role="progressbar" id="export_progressbar_next" style="opacity: 0.5; width: 0%;">
                            <span class="sr-only">{l s='Processing next page...' mod='ipcatalogexportimport'}</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="input-group pull-right">
                        <button type="button" class="btn btn-primary" tabindex="-1" id="export_continue_button" style="display: none;">
                            {l s='Ignore warnings and continue?' mod='ipcatalogexportimport'}
                        </button>
                        &nbsp;
                        <button type="button" class="btn btn-default" tabindex="-1" id="export_stop_button" style="display: none;">
                            {l s='Abort export' mod='ipcatalogexportimport'}
                        </button>
                        &nbsp;
                        <button type="button" class="btn btn-success" data-dismiss="modal" tabindex="-1" id="export_close_button" style="">
                            {l s='Close' mod='ipcatalogexportimport'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="schedule_email_modal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{l s='Add a new Email address' mod='ipcatalogexportimport'}</h4>
            </div>
            <div class="modal-body form-horizontal">
                <input type="hidden" name="schedule_email_id" id="schedule_email_id" value="" />
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Enabled' mod='ipcatalogexportimport'}
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="schedule_email_active" id="schedule_email_active_yes" value="1" checked="checked"/>
                            <label for="schedule_email_active_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                            <input type="radio" name="schedule_email_active" id="schedule_email_active_no" value="0" />
                            <label for="schedule_email_active_no">{l s='No' mod='ipcatalogexportimport'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <br>
                <div class="form-group">
                    <label class="control-label col-lg-3 required">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Domain name or IP address' mod='ipcatalogexportimport'}">
                            {l s='Email Address' mod='ipcatalogexportimport'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-at"></i>
                            </span>
                            <input type="text"
                                   name="schedule_email_address"
                                   id="schedule_email_address"
                                   value=""
                                   class=""
                                   size="33"	
                                   required="required" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Adds timestamp in "-YmdHis" format to file name to avoid overwriting. e.g. 20200114152852-Products.xlsx' mod='ipcatalogexportimport'}">
                            {l s='Add Timestamp' mod='ipcatalogexportimport'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="schedule_email_add_ts" id="schedule_email_add_ts_yes" value="1" checked="checked"/>
                            <label for="schedule_email_add_ts_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                            <input type="radio" name="schedule_email_add_ts" id="schedule_email_add_ts_no" value="0" />
                            <label for="schedule_email_add_ts_no">{l s='No' mod='ipcatalogexportimport'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Select a template on which exports should be created.' mod='ipcatalogexportimport'}">
                            {l s='Template' mod='ipcatalogexportimport'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <select id="schedule_email_template" class="fixed-width-xxl">
                            {foreach from=$configs item=config}
                                {if $config.name == 'catalog_default'}
                                    <option selected value="{$config.name}">-- {l s='Default' mod='ipcatalogexportimport'} --</option>
                                {else}
                                    <option value="{$config.name}">{$config.name}</option>
                                {/if}
                            {/foreach}
                        </select>
                        <p class="help-block">
                        </p>
                    </div>
                </div>
                <hr>
                <div id="schedule_email_entities">
                    {foreach from=$entities key=k item=entity}
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {$entity.plural}
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="schedule_email_{$k}" id="schedule_email_{$k}_yes" value="1" checked="checked"/>
                                    <label for="schedule_email_{$k}_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="schedule_email_{$k}" id="schedule_email_{$k}_no" value="0" />
                                    <label for="schedule_email_{$k}_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="modal-footer">
                <button id="schedule_email_save" class="pull-right btn btn-default">
                    <i class="process-icon-save"></i> {l s='Save' mod='ipcatalogexportimport'}
                </button>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="schedule_ftp_modal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{l s='Add a new FTP address' mod='ipcatalogexportimport'}</h4>
            </div>
            <div class="modal-body form-horizontal">
                <input type="hidden" name="schedule_ftp_id" id="schedule_ftp_id" value="" />
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Enabled' mod='ipcatalogexportimport'}
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="schedule_ftp_active" id="schedule_ftp_active_yes" value="1" checked="checked"/>
                            <label for="schedule_ftp_active_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                            <input type="radio" name="schedule_ftp_active" id="schedule_ftp_active_no" value="0" />
                            <label for="schedule_ftp_active_no">{l s='No' mod='ipcatalogexportimport'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <br>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='FTP Type' mod='ipcatalogexportimport'}
                    </label>
                    <div class="col-lg-9">
                        <select class="fixed-width-xxl" id="schedule_ftp_type" name="schedule_ftp_type">
                            <option value="ftp">{l s='FTP' mod='ipcatalogexportimport'}</option>
                            <option value="ftps">{l s='FTPS (Explicit)' mod='ipcatalogexportimport'}</option>
                            <option value="sftp">{l s='SFTP' mod='ipcatalogexportimport'}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3 required">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Domain name or IP address' mod='ipcatalogexportimport'}">
                            {l s='FTP URL' mod='ipcatalogexportimport'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-link"></i>
                            </span>
                            <input type="text"
                                   name="schedule_ftp_url"
                                   id="schedule_ftp_url"
                                   value=""
                                   class=""
                                   size="33"	
                                   required="required" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Default FTP port is 21 for FTP, and 22 for SFTP, if no port specified.' mod='ipcatalogexportimport'}">
                            {l s='FTP Port' mod='ipcatalogexportimport'}
                        </span>
                    </label>
                    <div class="col-lg-3">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-circle-thin"></i>
                            </span>
                            <input type="text"
                                   name="schedule_ftp_port"
                                   id="schedule_ftp_port"
                                   value=""
                                   class=""
                                   size="33" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3 required">
                        {l s='FTP Username' mod='ipcatalogexportimport'}
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-user"></i>
                            </span>
                            <input type="text"
                                   name="schedule_ftp_username"
                                   id="schedule_ftp_username"
                                   value=""
                                   class=""
                                   size="33"	
                                   required="required" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3 required">
                        {l s='FTP Password' mod='ipcatalogexportimport'}
                    </label>
                    <div class="col-lg-9">
                        <div class="input-group fixed-width-xxl">
                            <span class="input-group-addon">
                                <i class="icon-lock"></i>
                            </span>
                            <input type="text"
                                   name="schedule_ftp_password"
                                   id="schedule_ftp_password"
                                   value=""
                                   class=""
                                   size="33" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Adds timestamp in "-YmdHis" format to file name to avoid overwriting. e.g. 20200114152852-Products.xlsx' mod='ipcatalogexportimport'}">
                            {l s='Add Timestamp' mod='ipcatalogexportimport'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="schedule_ftp_add_ts" id="schedule_ftp_add_ts_yes" value="1" checked="checked"/>
                            <label for="schedule_ftp_add_ts_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                            <input type="radio" name="schedule_ftp_add_ts" id="schedule_ftp_add_ts_no" value="0" />
                            <label for="schedule_ftp_add_ts_no">{l s='No' mod='ipcatalogexportimport'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Select a template on which exports should be created.' mod='ipcatalogexportimport'}">
                            {l s='Template' mod='ipcatalogexportimport'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <select id="schedule_ftp_template" class="fixed-width-xxl">
                            {foreach from=$configs item=config}
                                {if $config.name == 'catalog_default'}
                                    <option selected value="{$config.name}">-- {l s='Default' mod='ipcatalogexportimport'} --</option>
                                {else}
                                    <option value="{$config.name}">{$config.name}</option>
                                {/if}
                            {/foreach}
                        </select>
                        <p class="help-block">
                        </p>
                    </div>
                </div>
                <hr>
                <div id="schedule_ftp_entities">
                    {foreach from=$entities key=k item=entity}
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="form-group">
                                    <label class="control-label col-lg-4">
                                        {$entity.plural}
                                    </label>
                                    <div class="col-lg-8">
                                        <span class="switch prestashop-switch fixed-width-lg">
                                            <input type="radio" name="schedule_ftp_{$k}" id="schedule_ftp_{$k}_yes" value="1" checked="checked"/>
                                            <label for="schedule_ftp_{$k}_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                            <input type="radio" name="schedule_ftp_{$k}" id="schedule_ftp_{$k}_no" value="0" />
                                            <label for="schedule_ftp_{$k}_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="form-group">
                                    <label class="control-label col-lg-1 required">
                                    </label>
                                    <div class="col-lg-11">
                                        <div class="input-group fixed-width-xl">
                                            <input type="text"
                                                   name="schedule_ftp_folder_{$k}"
                                                   id="schedule_ftp_folder_{$k}"
                                                   placeholder="{l s='Folder to export to' mod='ipcatalogexportimport'}"
                                                   value=""
                                                   class="folder_to_export_to"
                                                   size="33"	
                                                   required="required" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="modal-footer">
                <button id="schedule_ftp_save" class="pull-right btn btn-default">
                    <i class="process-icon-save"></i> {l s='Save' mod='ipcatalogexportimport'}
                </button>
            </div>
        </div>

    </div>
</div>