{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<div class="modal fade in" id="errorModal" data-keyboard="false" data-backdrop="static" role="dialog">
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
<div class="modal fade" id="importProgress" tabindex="-1" data-keyboard="false" data-backdrop="static" aria-hidden="false">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">{l s='Importing your data...' mod='ipcatalogexportimport'}</h4>
            </div>

            <div class="modal-body">
                <div class="alert alert-warning" id="import_details_stop" style="display:none;">
                    {l s='Aborting, please wait...' mod='ipcatalogexportimport'}
                </div>
                <p id="import_details_progressing" style="display: none;">
                    {l s='Importing your data...' mod='ipcatalogexportimport'}
                </p>
                <div class="alert alert-success" id="import_details_finished" style="">
                    {l s='Data imported' mod='ipcatalogexportimport'}
                    <br>
                    {l s='Look at your listings to make sure it\'s all there as you wished.' mod='ipcatalogexportimport'}
                </div>
                <div id="import_messages_div" style="max-height:250px; overflow:auto;">
                    <div class="alert alert-danger" id="import_details_error" style="display:none;">
                        {l s='Errors occurred' mod='ipcatalogexportimport'}:<br><ul></ul>
                    </div>
                    <div class="alert alert-warning" id="import_details_post_limit" style="display:none;">
                        {l s='Warning, the current import may require a PHP setting update, to allow more data to be transferred. If the current import stops before the end, you should increase your PHP "post_max_size" setting to' mod='ipcatalogexportimport'} <span id="import_details_post_limit_value">16MB</span>{l s='MB at least, and try again.' mod='ipcatalogexportimport'}
                    </div>
                    <div class="alert alert-warning" id="import_details_warning" style="">
                        {l s='Some errors were detected. Please check the details' mod='ipcatalogexportimport'}:<br><ul><li>{l s='The confirmation email couldn\'t be sent, but the import is successful. Yay!' mod='ipcatalogexportimport'}</li></ul>
                    </div>
                    <div class="alert alert-info" id="import_details_info" style="display:none;">
                        {l s='We made the following adjustments' mod='ipcatalogexportimport'}:<br><ul></ul>
                    </div>
                </div>

                <div id="import_validate_div" style="margin-top:17px;">
                    <div class="pull-right" id="import_validation_details" default-value="{l s='Validating data...' mod='ipcatalogexportimport'}">100/100</div>
                    <div class="progress" style="display: block; width: 100%">
                        <div class="progress-bar progress-bar-info" role="progressbar" style="width: 100%;" id="validate_progressbar_done">
                            <span><span id="validate_progression_done">100</span>% {l s='validated' mod='ipcatalogexportimport'}
                            </span>
                        </div>
                        <div class="progress-bar progress-bar-info" role="progressbar" id="validate_progressbar_next" style="opacity: 0.5; width: 0%;">
                            <span class="sr-only">{l s='Processing next page...' mod='ipcatalogexportimport'}</span>
                        </div>
                    </div>
                </div>

                <div id="import_progress_div" style="">
                    <div class="pull-right" id="import_progression_details" default-value="{l s='Importing your data...' mod='ipcatalogexportimport'}">100/100</div>
                    <div class="progress" style="display: block; width: 100%">
                        <div class="progress-bar progress-bar-info" role="progressbar" style="width: 0%" id="import_progressbar_done2">
                            <span>{l s='Linking related products...' mod='ipcatalogexportimport'}</span>
                        </div>
                        <div class="progress-bar progress-bar-success" role="progressbar" style="width: 100%;" id="import_progressbar_done">
                            <span><span id="import_progression_done">100</span>% {l s='imported' mod='ipcatalogexportimport'}
                            </span>
                        </div>
                        <div class="progress-bar progress-bar-success progress-bar-stripes active" role="progressbar" id="import_progressbar_next" style="opacity: 0.5; width: 0%;">
                            <span class="sr-only">{l s='Processing next page...' mod='ipcatalogexportimport'}</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="input-group pull-right">
                        <button type="button" class="btn btn-primary" tabindex="-1" id="import_continue_button" style="display: none;">
                            {l s='Ignore warnings and continue?' mod='ipcatalogexportimport'}
                        </button>
                        &nbsp;
                        <button type="button" class="btn btn-default" tabindex="-1" id="import_stop_button" style="display: none;">
                            {l s='Abort import' mod='ipcatalogexportimport'}
                        </button>
                        &nbsp;
                        <button type="button" class="btn btn-success" data-dismiss="modal" tabindex="-1" id="import_close_button" style="">
                            {l s='Close' mod='ipcatalogexportimport'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="schedule_url_modal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{l s='Add a new file url' mod='ipcatalogexportimport'}</h4>
            </div>
            <div class="modal-body form-horizontal">
                <input type="hidden" name="schedule_url_id" id="schedule_url_id" value="" />
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Enabled' mod='ipcatalogexportimport'}
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="schedule_url_active" id="schedule_url_active_yes" value="1" checked="checked"/>
                            <label for="schedule_url_active_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                            <input type="radio" name="schedule_url_active" id="schedule_url_active_no" value="0" />
                            <label for="schedule_url_active_no">{l s='No' mod='ipcatalogexportimport'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        {*<p class="help-block">
                        </p>*}
                    </div>
                </div>
                <br>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Entities' mod='ipcatalogexportimport'}
                    </label>                          
                    <div class="col-lg-9">
                        <select class="fixed-width-xxl" id="schedule_url_entity" name="schedule_url_entity">
                            {foreach from=$entities_unordered key=k item=entity}
                                <option value="{$k}">{$entity.plural}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3 required">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Full file URL' mod='ipcatalogexportimport'}">
                            {l s='File URL' mod='ipcatalogexportimport'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <input type="text"
                               id="schedule_url_url"
                               name="schedule_url_url"
                               value=""
                               class="file_url_input"
                               size="33"	
                               required="required" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Select a template on which exports should be created.' mod='ipcatalogexportimport'}">
                            {l s='Template' mod='ipcatalogexportimport'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <select id="schedule_url_template" class="fixed-width-xxl">
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
            </div>
            <div class="modal-footer">
                <button id="schedule_url_save" class="pull-right btn btn-default">
                    <i class="process-icon-save"></i> {l s='Save' mod='ipcatalogexportimport'}
                </button>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="schedule_ftp_modal" role="dialog">
    <div class="modal-dialog import">
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
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Select a template on which imports should be created.' mod='ipcatalogexportimport'}">
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
                            <div class="col-lg-6">
                                <div class="form-group with-position" data-position="{$entity.position}">
                                    <label class="control-label col-lg-4">
                                        {$entity.plural}
                                    </label>
                                    <div class="col-lg-8">
                                        <span class="switch prestashop-switch fixed-width-lg">
                                            <input type="radio" name="schedule_ftp_{$entity.pEntity}" id="schedule_ftp_{$entity.pEntity}_yes" value="1" checked="checked"/>
                                            <label for="schedule_ftp_{$entity.pEntity}_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                            <input type="radio" name="schedule_ftp_{$entity.pEntity}" id="schedule_ftp_{$entity.pEntity}_no" value="0" />
                                            <label for="schedule_ftp_{$entity.pEntity}_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label col-lg-1 required">
                                    </label>
                                    <div class="col-lg-11">
                                        <div class="input-group fixed-width-xxl">
                                            <input type="text"
                                                   name="schedule_ftp_folder_{$entity.pEntity}"
                                                   id="schedule_ftp_folder_{$entity.pEntity}"
                                                   placeholder="{l s='Unique folder to import from' mod='ipcatalogexportimport'}"
                                                   value=""
                                                   class="folder_to_import_from"
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