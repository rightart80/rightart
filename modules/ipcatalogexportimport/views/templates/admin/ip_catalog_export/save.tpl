{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<div id="catalogEI-tab-content-Save" class="catalogEI-tab-content" style="display: none;">
    <div id="catalogEI-save" class="panel catalogEI-tab form-horizontal catalogEI_save">
        <h3 class="tab"><i class="icon-save"></i> {l s='Save' mod='ipcatalogexportimport'}</h3>
        <div class="alert alert-info alert-dismissible fade in">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <span>{l s='Here you can save the changes you made for the catalog.' mod='ipcatalogexportimport'}</span>
        </div>
        <h3 class="save_header">{l s='Save changes' mod='ipcatalogexportimport'}</h3>
        <div class="row">
            <div class="form-group">
                <label class="control-label col-lg-3 required">
                    {l s='Name of the template' mod='ipcatalogexportimport'}
                </label>
                <div class="col-lg-3">
                    <input type="text"
                           id="template_name"
                           value=""
                           class=""
                           size="33"	
                           required="required" />
                </div>
                <div class="col-lg-2">
                    <button id="save_template_btn" class="catalogEI_save_template_btn btn btn-default">
                        <i class="icon-save"></i> {l s='Save' mod='ipcatalogexportimport'}
                    </button>
                </div>
            </div>
            <hr class="open_hr">
            <br>
            <h3 class="save_header">{l s='Apply from saved templates' mod='ipcatalogexportimport'}</h3>
            <div class="col-lg-6 templates">
                <ul id="configs" class="list-group">
                    {foreach from=$configs item=config}
                        {if $config.name == 'catalog_default'}
                            <li data-id="{$config.id_ipcatalogexport}" data-config='{$config.configuration}' class="list-group-item"><b>-- {l s='Default' mod='ipcatalogexportimport'} --</b>
                                <span class="pull-right apply_span default_config">
                                    <button type="button" class="btn btn-default apply_config">
                                        <i class="icon-check"></i> {l s='Apply' mod='ipcatalogexportimport'}</button> |
                                    <a class="btn btn-default" href="{$ajax_url}&action=getSetting&st_id={$config.id_ipcatalogexport}">
                                        <i class="icon-download"></i></a>
                                </span>
                            </li>
                        {else}
                            <li data-id="{$config.id_ipcatalogexport}" data-config='{$config.configuration}' class="list-group-item"><b>{$config.name}</b>
                                <span class="pull-right apply_span">
                                    <button type="button" class="btn btn-default apply_config">
                                        <i class="icon-check"></i> {l s='Apply' mod='ipcatalogexportimport'}</button> |
                                    <a class="btn btn-default" href="{$ajax_url}&action=getSetting&st_id={$config.id_ipcatalogexport}">
                                        <i class="icon-download"></i></a> |
                                    <button type="button" class="btn btn-default delete_file">
                                        <i title="{$config.title}" class="icon-trash" style="color:#c50000" aria-hidden="true"></i>
                                    </button>
                                </span>
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            </div>
            <div id="txt_file_uploader" class="col-lg-6">
                <div class="btn-toolbar">
                    <button id="import_button"
                            class="btn btn-default pull-right">
                        <i class="icon-upload"></i>
                        {l s='Import' mod='ipcatalogexportimport'}
                    </button>
                    <input type="file" id="file" accept=".txt" style="display:none" multiple>
                    <a class="btn btn-default pull-right" href="{$ajax_url}&action=getSetting">
                        <i class="icon-download"></i>
                        {l s='Export All' mod='ipcatalogexportimport'}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>