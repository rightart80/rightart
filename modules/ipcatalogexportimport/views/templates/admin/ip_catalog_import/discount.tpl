{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<div id="catalogEI-tab-content-Discount" class="catalogEI-tab-content" style="display: none;">
    <form id="import_form_discounts" class="form-horizontal catalogEI_form" action="{$ajax_url}" method="post" enctype="multipart/form-data" name="discounts_import" novalidate="novalidate">
        <input type="hidden" name="entity" value="discounts" />
        <input type="hidden" name="filename" value="" />
        <input type="hidden" name="id_employee" value="{$id_employee}" />
        <div id="catalogEI-discount" class="panel catalogEI-tab">
            <h3 class="tab"><i class="icon-money"></i> {l s='Discounts (Specific Prices)' mod='ipcatalogexportimport'}</h3>
            <div class="form-wrapper">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#discount_configurations">
                            <i class="icon-wrench"></i>
                            {l s='Configurations' mod='ipcatalogexportimport'}</a>
                    </li>
                    <li><a data-toggle="tab" href="#discount_fields">
                            <i class="icon-sliders"></i>
                            {l s='Fields' mod='ipcatalogexportimport'}</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div id="discount_configurations" class="tab-pane in active catalogEI_configurations">
                        <div class="alert alert-info alert-dismissible fade in">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <span><strong>{l s='Tips before import!' mod='ipcatalogexportimport'}</strong></span>
                            <ul>
                                <li>{l s='Do a backup of your shop and database' mod='ipcatalogexportimport'}</li>
                                <li>{l s='Install the locale of the selected language under the "Localization" menu' mod='ipcatalogexportimport'}</li>
                                <li>{l s='If you import to a new empty shop, the import order must be: Manufacturers > Suppliers > Groups > Customers > Addresses > Carriers > Warehouses > Categories > Features > Attributes > Products > Combinations > Packs > Discounts' mod='ipcatalogexportimport'}</li>
                            </ul>
                        </div>
                        <div id="after_upload_success_alert_discounts" class="row after_upload_success_alert hide">
                            <div class="alert alert-success col-xs-12">
                                <span></span>
                                <button type="button" class="btn btn-default pull-right change_file_btn">
                                    <i class="icon-pencil"></i> {l s='Change' mod='ipcatalogexportimport'}
                                </button>
                            </div>
                        </div>
                        <div id="after_upload_danger_alert_discounts" class="row after_upload_danger_alert hide">
                            <div class="alert alert-danger alert-dismissible col-xs-12">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <span>{l s='The file type is incorrect.' mod='ipcatalogexportimport'}</span>
                            </div>
                        </div>
                        <div id="import_source_discounts" class="form-group import_source">
                            <br>
                            <label class="control-label col-lg-3">
                                <span class="control-label">
                                    {l s='Import source' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-9">
                                <select name="import_source"
                                        class="fixed-width-xxl"
                                        id="discount_import_source">
                                    <option value="file_upload">{l s='File upload' mod='ipcatalogexportimport'}</option>
                                    <option value="from_url">{l s='URL' mod='ipcatalogexportimport'}</option>
                                    <option value="from_ftp">{l s='FTP' mod='ipcatalogexportimport'}</option>
                                    <option value="from_history">{l s='History' mod='ipcatalogexportimport'}</option>
                                </select>
                            </div>
                        </div>
                        <div id="import_type_discounts">
                            <br>
                            <div class="file_upload import_type">
                                <div id="file_uploader_discounts" class="form-group file_uploader">
                                    <label class="control-label col-lg-3 required">
                                        {l s='Select a file to import' mod='ipcatalogexportimport'}
                                    </label>
                                    <div class="col-lg-4">
                                        <input type="file" name="file" accept=".csv,.txt,.xls,.xlsx,.ods" data-entity="discounts" class="entity_file hide">
                                        <div class="dummyfile input-group">
                                            <input class="entity_file-name uploader_input" value="{l s='Choose file' mod='ipcatalogexportimport'}" type="text" name="discounts" readonly>
                                            <span class="input-group-btn">
                                                <button type="button" name="submitAddAttachments" class="entity_file-selectbutton btn btn-default">
                                                    <i class="icon-folder-open"></i> {l s='Upload file' mod='ipcatalogexportimport'}
                                                </button>
                                            </span>
                                        </div>
                                        <p class="help-block">
                                            {l s='Allowed formats: .csv, .txt, .xls, .xlsx, .ods' mod='ipcatalogexportimport'}
                                        </p>
                                    </div>
                                    {*<div class="col-lg-3">
                                    {l s='or' mod='ipcatalogexportimport'}
                                    <button type="button" class="btn btn-default show_upload_history" {if $files.discounts.data|count == 0}disabled{/if}>
                                    <span class="badge badge-info">{$files.discounts.data|count}</span> {l s='Choose from history' mod='ipcatalogexportimport'}
                                    </button>
                                    </div>*}
                                </div>
                            </div>
                            <div id="upload_history_discounts" class="panel upload_history import_type hide">
                                <div>
                                    <div class="panel-heading">
                                        {l s='History of uploaded files' mod='ipcatalogexportimport'}
                                        <span class="file_history_nb badge">{$files.discounts.data|count}</span>
                                        {*<button type="button" class="btn btn-link pull-right close_history_btn">
                                        <i class="icon-remove"></i>
                                        </button>*}
                                    </div>
                                    {*        <div class="panel-body">*}
                                    <table id="upload_history_table_discounts" class="table table-striped table-bordered upload_history_table" data-table="discounts">
                                        <thead>
                                            <tr>
                                                <th>{l s='File' mod='ipcatalogexportimport'}</th>
                                                <th>{l s='Size' mod='ipcatalogexportimport'}</th>
                                                <th>{l s='Action' mod='ipcatalogexportimport'}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="from_url import_type hide">
                                <div class="form-group">
                                    <label class="control-label col-lg-3 required">
                                        {l s='File URL' mod='ipcatalogexportimport'}
                                    </label>
                                    <div class="col-lg-5">
                                        <input type="text"
                                               id="file_url_discounts"
                                               name="file_url"
                                               value=""
                                               class="file_url_input"
                                               size="33"	
                                               required="required" />
                                    </div>
                                    <div class="col-lg-1">
                                        <button id="file_download_from_url_discounts" class="file_download_from_url_btn btn btn-default" data-entity="discounts">
                                            <i class="process-icon-download"></i> {l s='Download' mod='ipcatalogexportimport'}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="from_ftp import_type hide">
                                <div class="form-group">
                                    <label class="control-label col-lg-3">
                                        {l s='FTP Type' mod='ipcatalogexportimport'}
                                    </label>
                                    <div class="col-lg-9">
                                        <select class="fixed-width-xxl" id="ftp_type_discounts" name="ftp_type">
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
                                                   name="ftp_url"
                                                   id="ftp_url_discounts"
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
                                                   name="ftp_port"
                                                   id="ftp_port_discounts"
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
                                                   name="ftp_username"
                                                   id="ftp_username_discounts"
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
                                                   name="ftp_password"
                                                   id="ftp_password_discounts"
                                                   value=""
                                                   class=""
                                                   size="33" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3 required">
                                        {l s='FTP File Path' mod='ipcatalogexportimport'}
                                    </label>
                                    <div class="col-lg-9">
                                        <div class="input-group fixed-width-xxl">
                                            <span class="input-group-addon">
                                                <i class="icon-file-text"></i>
                                            </span>
                                            <input type="text"
                                                   name="ftp_filepath"
                                                   id="ftp_filepath_discounts"
                                                   value=""
                                                   class=""
                                                   size="33" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">

                                    </label>
                                    <div class="col-lg-9">
                                        <div class="input-group fixed-width-xxl">
                                            <button id="file_download_from_ftp_discounts" class="file_download_from_ftp_btn btn btn-default pull-right" data-entity="discounts">
                                                <i class="process-icon-download"></i> {l s='Download' mod='ipcatalogexportimport'}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='The language of data (Headers are excluded)' mod='ipcatalogexportimport'}">
                                    {l s='Language' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-3">
                                <select name="language"
                                        class="fixed-width-xxl"
                                        id="discount_language">
                                    {foreach from=$languages item=language}
                                        {if $lang_id neq $language.id_lang}
                                            <option value="{$language.id_lang}">{$language.name}</option>
                                        {else}
                                            <option selected value="{$language.id_lang}">{$language.name}</option>
                                        {/if}
                                    {/foreach}
                                </select>
                                <p class="help-block">
                                    {l s='Locale must be installed' mod='ipcatalogexportimport'}
                                </p>
                            </div>
                        </div>
                        <div class="csv_options">
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='e.g. a,b,c or a;b;c or a   b   c' mod='ipcatalogexportimport'}">
                                        {l s='Value separator' mod='ipcatalogexportimport'}
                                    </span>
                                </label>
                                <div class="col-lg-9">
                                    <select name="csv_separator"
                                            class="fixed-width-xxl"
                                            id="discount_csv_separator">
                                        <option value=";">; {l s='(semicolon)' mod='ipcatalogexportimport'}</option>
                                        <option value=",">, {l s='(comma)' mod='ipcatalogexportimport'}</option>
                                        <option value="t">\t {l s='(tab)' mod='ipcatalogexportimport'}</option>
                                    </select>
                                </div>
                            </div>
                            <br>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='If there are several values in a cell, by what symbol they should be separated. By default, it is a comma.' mod='ipcatalogexportimport'}">
                                    {l s='Multiple Value separator' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-9">
                                <input type="text"
                                       name="multivalue_separator"
                                       id="discount_multivalue_separator"
                                       value=","
                                       class="fixed-width-xxl"
                                       size="33"	
                                       required="required" />
                                <p class="help-block">
                                    {l s='e.g. Blouse; red.jpg, blue.jpg, green.jpg; 129.90' mod='ipcatalogexportimport'}
                                </p>
                            </div>
                        </div>
                        <div class="form-group action">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip" style="font-weight: 600;" data-toggle="tooltip" title="" data-original-title="{l s='1) "Insert": There will be only insertions if the insert conditions are met and no update. 2) "Update": There will be only updates if the update conditions are met, no insertion. 3) "Update or Insert": First the update conditions will be checked. If the conditions are met, there will be updates. Otherwise, there will be insertions if the insert conditions are met.' mod='ipcatalogexportimport'}">
                                    {l s='Action' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-3">
                                <select name="target_action"
                                        class="fixed-width-xxl"
                                        id="discounts_target_action">
                                    <option value="insert">{l s='Insert' mod='ipcatalogexportimport'}</option>
                                    <option value="update">{l s='Update' mod='ipcatalogexportimport'}</option>
                                    <option value="update_insert">{l s='Update or Insert' mod='ipcatalogexportimport'}</option>
                                </select>
                                <p class="help-block">
                                </p>
                            </div>
                        </div>
                        <div class="update_options hide">
                            <h4 class="col-lg-pull-3">{l s='Update options' mod='ipcatalogexportimport'}</h4>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='' mod='ipcatalogexportimport'}">
                                        {l s='Update by' mod='ipcatalogexportimport'}
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <select name="update_by"
                                            class="fixed-width-xxl"
                                            id="discount_update_by">
                                        <option value="id">{l s='Specific Price (Rule) ID' mod='ipcatalogexportimport'}</option>
                                        <option value="name">{l s='Specific Price Rule Name' mod='ipcatalogexportimport'}</option>
                                    </select>
                                    <p class="help-block">
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='If enabled, when the updated new value is empty, the old value will not be updated. Otherwise, it will be updated to empty value if the new value is empty.' mod='ipcatalogexportimport'}">
                                        {l s='Keep the old value if the new value is empty' mod='ipcatalogexportimport'}
                                    </span>
                                </label>
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="keep_old_value" id="discount_keep_old_value_yes" value="1" checked="checked" />
                                        <label for="discount_keep_old_value_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                        <input type="radio" name="keep_old_value" id="discount_keep_old_value_no" value="0" />
                                        <label for="discount_keep_old_value_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                    <p class="help-block">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="insert_options">
                            <h4 class="col-lg-pull-3">{l s='Insert options' mod='ipcatalogexportimport'}</h4>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip delete_entities" data-toggle="tooltip" title="" data-original-title="{l s='If enabled, all the existing products will be deleted before import.' mod='ipcatalogexportimport'}">
                                        {l s='Delete all existing specific prices before import' mod='ipcatalogexportimport'}
                                    </span>
                                </label>
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="truncate" id="discount_truncate_yes" value="1" />
                                        <label for="discount_truncate_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                        <input type="radio" name="truncate" id="discount_truncate_no" value="0" checked="checked" />
                                        <label for="discount_truncate_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                    <p class="help-block">
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Enable this option to keep your imported products’ ID number as is. Otherwise, PrestaShop will ignore them and create auto-incremented ID numbers. If this option is enabled and there is a product in your database with this ID, then this product will not be inserted.' mod='ipcatalogexportimport'}"> 
                                        {l s='Keep ID numbers of inserted specific prices' mod='ipcatalogexportimport'}
                                    </span>
                                </label>
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="keep_id" id="discount_keep_id_yes" value="1" checked="checked" />
                                        <label for="discount_keep_id_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                        <input type="radio" name="keep_id" id="discount_keep_id_no" value="0" />
                                        <label for="discount_keep_id_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                    <p class="help-block">
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='If a product exists with the imported specific price rule name, you can: 1) Ignore the imported row, 2) Create a new specific price rule with the existing name.' mod='ipcatalogexportimport'}">
                                        {l s='If a specific price rule exists with the specific price rule name you import' mod='ipcatalogexportimport'}
                                    </span>
                                </label>
                                <div class="col-lg-3">
                                    <select name="name_exists"
                                            class="fixed-width-xxl"
                                            id="discount_name_exists">
                                        <option value="ignore">{l s='Do not insert' mod='ipcatalogexportimport'}</option>
                                        <option value="insert">{l s='Insert' mod='ipcatalogexportimport'}</option>
                                    </select>
                                    <p class="help-block">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='If the imported file is a ISO 8859-1 encoded file, it is necessary to convert its ecoding to UTF-8.' mod='ipcatalogexportimport'}"> 
                                    {l s='Convert to UTF-8' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="convert" id="discount_convert_yes" value="1" />
                                    <label for="discount_convert_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="convert" id="discount_convert_no" value="0" checked="checked" />
                                    <label for="discount_convert_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                                <p class="help-block">
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Sends an email to let you know your import is complete. It can be useful when handling large files, as the import may take some time.' mod='ipcatalogexportimport'}"> 
                                    {l s='Send notification email' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="send_email" id="discount_send_email_yes" value="1" />
                                    <label for="discount_send_email_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="send_email" id="discount_send_email_no" value="0" checked="checked" />
                                    <label for="discount_send_email_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                                <p class="help-block">
                                </p>
                            </div>
                        </div>
                        {if $shop_feature}   
                            <hr>
                            <h4 class="filter">{l s='Select shop into which you want to import' mod='ipcatalogexportimport'}</h4>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Shop' mod='ipcatalogexportimport'}
                                </label>
                                <div class="col-lg-9">
                                    <select name="shop"
                                            class="{if $shops|@count gt 10} chosen {/if} fixed-width-xxl"
                                            id="discount_shop">
                                        <option selected value="all">{l s='All shops' mod='ipcatalogexportimport'}</option>
                                        {foreach from=$shops item=shop}
                                            {if $shop.id_shop eq $context_shop}
                                                <option value="{$shop.id_shop}">{$shop.name} ({l s='This shop' mod='ipcatalogexportimport'})</option>
                                            {elseif $shop.id_shop eq $default_shop}
                                                <option value="{$shop.id_shop}">{$shop.name} ({l s='Default shop' mod='ipcatalogexportimport'})</option>
                                            {else}
                                                <option value="{$shop.id_shop}">{$shop.name}</option>
                                            {/if}
                                        {/foreach}
                                    </select>
                                    <p class="help-block">
                                        {l s='The shop of the products' mod='ipcatalogexportimport'}
                                    </p>
                                </div>
                            </div>
                        {/if}

                        <div class="panel-footer">
                            <div class="text-right">
                                <button class="btn btn-default next" data-value="discount">
                                    <i class="process-icon-next"></i>{l s='Next' mod='ipcatalogexportimport'}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="discount_fields" class="tab-pane catalogEI_fields catalogEI_discount_fields">
                        <div id="container-customer" class="panel">
                            <h3><i class="icon-list-alt"></i> <span class="panel_header_text">{l s='Columns Mapping' mod='ipcatalogexportimport'}</span></h3>
                            <div class="alert alert-info">
                                <p>{l s='This a the preview of the columns to import. The preview is used so that you can save the sequence of the columns that you will import even before uploading any file. You can select or deselect, or change the orders of the columns.' mod='ipcatalogexportimport'}</p>
                            </div>
                            <div id="error_duplicate_type_discounts" class="alert alert-danger" style="display:none;">
                                {l s='Two columns cannot have the same value' mod='ipcatalogexportimport'}:
                                <strong></strong>
                            </div>
                            <div id="required_column_discounts" class="alert alert-danger" style="display:none;">
                                {l s='This column must be set:' mod='ipcatalogexportimport'} <span id="missing_column_discounts">&nbsp;</span>
                            </div>
                            {*        <form action="{$current|escape:'html':'UTF-8'}&amp;token={$token|escape:'html':'UTF-8'}" method="post" id="import_form" name="import_form" class="form-horizontal">*}
                            <div class="row">
                                <div class="form-group col-lg-5">
                                    <label class="control-label" for="discount_skip">
                                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This number indicates how many of the first lines of your file should be skipped when importing the data. For instance, set it to 1 if the first row of your file contains headers.' mod='ipcatalogexportimport'}">
                                            {l s='Rows to skip' mod='ipcatalogexportimport'}
                                        </span>
                                    </label>
                                    &nbsp;
                                    <input class="fixed-width-md" type="text" name="skip" id="discount_skip" value="1" />
                                </div>
                                <div class="form-group col-lg-7 pull-right">
                                    <button type="button" class="btn btn-default pull-right map-fields"><i class="icon-arrows"></i> {l s='Map all fields' mod='ipcatalogexportimport'}</button>
                                    <button type="button" class="btn btn-default pull-right ignore-fields"><i class="icon-minus"></i> {l s='Ignore all fields' mod='ipcatalogexportimport'}</button>
                                    <button type="button" class="btn btn-default pull-right save-apply-fields label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='You will be sent to the "Save" section to save your current changes or apply one of your previous changes.' mod='ipcatalogexportimport'}"><i class="icon-external-link-square"></i> {l s='Save or apply your changes' mod='ipcatalogexportimport'}</button>
                                </div>
                            </div>
                            <div id="view_data_discounts" class="form-group catalogEI_view_data">
                                <div class="col-lg-12">
                                    {*{section name=nb_i start=0 loop=$nb_table step=1}
                                    {assign var=i value=$smarty.section.nb_i.index}
                                    {$data.$i}
                                    {/section}*}
                                    <div id="view_data_table_discounts" class="catalogEI_view_data_table">
                                        {$columns.discounts}
                                    </div>
                                    <div class="text-center paginate discounts">
                                        <button id="btn_left_discounts" type="button" class="btn btn-default go_left" disabled="disabled" data-entity="discounts">
                                            <i class="icon-angle-left"></i>
                                        </button>
                                        <button id="btn_discounts_page_1" type="button" class="btn btn-default activated btn_page" data-entity="discounts">1</button>
                                        <span class="dynamic_buttons">
                                            {if $paginator.discounts.nbTable > 7}
                                                <span id="btn_discounts_left_dots" class="hide">...</span>
                                                {for $foo=2 to $paginator.discounts.nbTable - 1}
                                                    <button id="btn_discounts_page_{$foo}" type="button" class="btn btn-default btn_hideable btn_page{if $foo > 5} hide{/if}" data-entity="discounts">{$foo}</button>
                                                {/for}
                                                <span id="btn_discounts_right_dots">...</span>
                                                <button id="btn_discounts_page_{$paginator.discounts.nbTable}" type="button" class="btn btn-default btn_page" data-entity="discounts">{$paginator.discounts.nbTable}</button>
                                            {else}
                                                {for $foo=2 to $paginator.discounts.nbTable}
                                                    <button id="btn_discounts_page_{$foo}" type="button" class="btn btn-default btn_page" data-entity="discounts">{$foo}</button>
                                                {/for}
                                            {/if}
                                        </span>
                                        <button id="btn_right_discounts" type="button" class="btn btn-default go_right"{if $paginator.discounts.nbTable == 1} disabled="disabled"{/if} data-entity="discounts">
                                            <i class="icon-angle-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            {*<div class="panel-footer">
                            <button type="button" class="btn btn-default" onclick="window.history.back();">
                            <i class="process-icon-cancel text-danger"></i>
                            {l s='Cancel' mod='ipcatalogexportimport'}
                            </button>
                            <button id="import" name="import" type="submit" onclick="return (validateImport(new Array({$res})));"  class="btn btn-default pull-right">
                            <i class="process-icon-ok text-success"></i>
                            {l s='Import .CSV data' mod='ipcatalogexportimport'}
                            </button>
                            </div>*}
                            {*        </form>*}
                        </div>




                        <div class="panel-footer">
                            <button class="btn btn-default back" data-value="discount">
                                <i class="process-icon-back"></i>{l s='Back' mod='ipcatalogexportimport'}
                            </button>
                            <button class="btn btn-default pull-right" type="submit" value="1" name="import_discounts" disabled>
                                <i class="process-icon-upload"></i>{l s='Import' mod='ipcatalogexportimport'}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>
