{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<div id="catalogEI-tab-content-Carrier" class="catalogEI-tab-content" style="display: none;">
    <form id="carrier_export_form" class="form-horizontal catalogEI_form" action="{$ajax_url}" method="post" enctype="multipart/form-data" name="carrier_export" novalidate="novalidate">
        <input type="hidden" name="export" value="carrier" />
        <div id="catalogEI-carrier" class="panel catalogEI-tab">
            <h3 class="tab"><i class="icon-truck"></i> {l s='Carriers' mod='ipcatalogexportimport'}</h3>
            <div class="form-wrapper">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#carrier_configurations">
                            <i class="icon-wrench"></i>
                            {l s='Configurations' mod='ipcatalogexportimport'}</a></li>
                    <li><a data-toggle="tab" href="#carrier_filters">
                            <i class="icon-filter"></i>
                            {l s='Filters' mod='ipcatalogexportimport'}</a></li>
                    <li><a data-toggle="tab" href="#carrier_fields">
                            <i class="icon-sliders"></i>
                            {l s='Fields' mod='ipcatalogexportimport'}</a></li>
                </ul>
                <div class="tab-content">
                    <div id="carrier_configurations" class="tab-pane in active">
                        <div class="alert alert-info alert-dismissible fade in">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <span>{l s='Here you define main options of the document you will export.' mod='ipcatalogexportimport'}</span>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='CSV is faster.' mod='ipcatalogexportimport'}">
                                    <b>{l s='Export as ' mod='ipcatalogexportimport'}</b>
                                </span>
                            </label>
                            <div class="col-lg-9">
                                <div class="radio">
                                    <label><input type="radio" name="as" value="csv" checked>{l s='CSV' mod='ipcatalogexportimport'}</label>
                                </div>
                                <div class="radio">
                                    <label><input type="radio" name="as" value="xlsx">{l s='Excel' mod='ipcatalogexportimport'}</label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This is the name of the document you will download.' mod='ipcatalogexportimport'}">
                                    {l s='File name' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-3">
                                <input type="text"
                                       name="doc_name"
                                       id="carrier_doc_name"
                                       value="{l s='Carriers' mod='ipcatalogexportimport'}"
                                       class="fixed-width-xxl"
                                       size="33
                                       required="required" />
                            </div>
                        </div>
                        <br>
                        <div class="csv_options collapse in">
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='e.g. a,b,c or a;b;c or a   b   c' mod='ipcatalogexportimport'}">
                                        {l s='Value separator' mod='ipcatalogexportimport'}
                                    </span>
                                </label>
                                <div class="col-lg-9">
                                    <select name="csv_separator"
                                            class="fixed-width-xxl"
                                            id="carrier_csv_separator">
                                        <option value=";">; {l s='(semicolon)' mod='ipcatalogexportimport'}</option>
                                        <option value=",">, {l s='(comma)' mod='ipcatalogexportimport'}</option>
                                        <option value="t">\t {l s='(tab)' mod='ipcatalogexportimport'}</option>
                                    </select>
                                </div>
                            </div>
                            {*<div class="form-group">
                            <label class="control-label col-lg-3">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='e.g. a,b,c or "a","b","c"' mod='ipcatalogexportimport'}">
                            {l s='Value enclosure' mod='ipcatalogexportimport'}
                            </span>
                            </label>
                            <div class="col-lg-9">
                            <select name="csv_enclosure"
                            class="fixed-width-xxl"
                            id="carrier_csv_enclosure">
                            <option value="quot">"" {l s='(quotation marks)' mod='ipcatalogexportimport'}</option>
                            <option value="none">{l s=' (nothing)' mod='ipcatalogexportimport'}</option>
                            </select>
                            </div>
                            </div>*}
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
                                       id="carrier_multivalue_separator"
                                       value=","
                                       class="fixed-width-xxl"
                                       size="33"	
                                       required="required" />
                                <p class="help-block">
                                    {l s='e.g. Blouse; red.jpg, blue.jpg, green.jpg; 129.90' mod='ipcatalogexportimport'}
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='The language of data (Headers are excluded)' mod='ipcatalogexportimport'}">
                                    {l s='Language' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-3">
                                <select name="language"
                                        class="fixed-width-xxl"
                                        id="carrier_language">
                                    {foreach from=$languages item=language}
                                        {if $lang_id neq $language.id_lang}
                                            <option value="{$language.id_lang}">{$language.name}</option>
                                        {else}
                                            <option selected value="{$language.id_lang}">{$language.name}</option>
                                        {/if}
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Select a column' mod='ipcatalogexportimport'}">
                                    {l s='Sort By' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-3">
                                <select id="carrier_sort" name="sort" {*multiple*} class="fixed-width-xxl">
                                    <option value="carrier.id_carrier">{l s='ID' mod='ipcatalogexportimport'}</option>
                                    <option value="carrier.name">{l s='Name' mod='ipcatalogexportimport'}</option>
                                    <option value="carrier.date_add">{l s='Creation Date' mod='ipcatalogexportimport'}</option>
                                </select>
                                <p class="help-block">
                                </p>
                            </div>
                            <div class="col-lg-5 col-lg-offset-1">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="sort_way" id="carrier_sort_asc" value="1" checked="checked" />
                                    <label for="carrier_sort_asc">{l s='ASC' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="sort_way" id="carrier_sort_desc" value="0" />
                                    <label for="carrier_sort_desc">{l s='DESC' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                                <p class="help-block">
                                    {l s='Ascending or descending' mod='ipcatalogexportimport'}
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Specify how many digits you want to see after the decimal symbol.' mod='ipcatalogexportimport'}">
                                    {l s='Number of decimals' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-9">
                                <select name="decimals"
                                        class="fixed-width-xxl"
                                        id="carrier_decimals">
                                    <option selected value="-1">{l s='Keep as is' mod='ipcatalogexportimport'}</option>
                                    <option value="0">{l s='0 digit' mod='ipcatalogexportimport'}</option>
                                    <option value="1">{l s='1 digit' mod='ipcatalogexportimport'}</option>
                                    <option value="2">{l s='2 digits' mod='ipcatalogexportimport'}</option>
                                    <option value="3">{l s='3 digits' mod='ipcatalogexportimport'}</option>
                                    <option value="4">{l s='4 digits' mod='ipcatalogexportimport'}</option>
                                </select>
                                <br>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3 speed">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Number of rows per export request. For a weak server select a lower value, for a speedy server select a higher value.' mod='ipcatalogexportimport'}">
                                    {l s='Export speed' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-9">
                                <div class="fixed-width-xxl">
                                    <div class="range-labels">
                                        <span class="">100</span>
                                        <span class="">200</span>
                                        <span class="">500</span>
                                        <span class="">1k</span>
                                        <span class="">2k</span>
                                        <span class="">5k</span>
                                        <span class="">10k</span>
                                    </div>
                                    <input id="carrier_speed" name="speed" type="range" min="1" max="7" step="1" value="4"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="carrier_filters" class="tab-pane catalogEI_filters">
                        <div class="alert alert-info alert-dismissible fade in">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <span>{l s='You can filter the products here. If all items of a table (e.g. Feature) are selected or deselected, they are not included in the filter.' mod='ipcatalogexportimport'}</span>
                        </div>
                        {if $shop_feature}
                            <hr>
                            <h4 class="filter">{l s='Select Shop Context' mod='ipcatalogexportimport'}</h4>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Shop' mod='ipcatalogexportimport'}
                                </label>
                                <div class="col-lg-9">
                                    <select name="shop"
                                            class="{if $shops|@count gt 10} chosen {/if} fixed-width-xxl"
                                            id="carrier_shop">
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
                            <br>
                        {/if}


                        <hr>
                        <h4 class="filter">{l s='Filter By Availability' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Availability' mod='ipcatalogexportimport'}
                            </label>
                            <div class="col-lg-9">
                                <select name="availability"
                                        class="fixed-width-xl"
                                        id="carrier_availability">
                                    <option selected value="all">{l s='All' mod='ipcatalogexportimport'}</option>
                                    <option value="active">{l s='Active' mod='ipcatalogexportimport'}</option>
                                    <option value="inactive">{l s='Inactive' mod='ipcatalogexportimport'}</option>
                                </select>
                            </div> 
                        </div>
                        <br>
                        <br>


                        <hr>
                        <h4 class="filter">{l s='Filter By Supplier' mod='ipcatalogexportimport'}</h4>
                        <select id="ctrl-show-selected-carriers2" name="ctrl-show-selected-carriers2" class="show_selected pull-left">
                            <option value="all" selected>{l s='Show all' mod='ipcatalogexportimport'}</option>
                            <option value="selected">{l s='Show selected' mod='ipcatalogexportimport'}</option>
                            <option value="not-selected">{l s='Show deselected' mod='ipcatalogexportimport'}</option>
                        </select>
                        <button id="refresh_carriers2" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ipcatalogexportimport'}
                        </button>
                        <br>
                        <table id="carriers2_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{l s='ID' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Reference' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Name' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Logo' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Delay' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Status' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Free Shipping' mod='ipcatalogexportimport'}</th>
                                </tr>
                            </thead>
                        </table>
                        <br>
                        <br>

                    </div>
                    <div id="carrier_fields" class="tab-pane catalogEI_fields catalogEI_carrier_fields">
                        <div class="alert alert-warning alert-dismissible">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <span>{l s='It is important not to select or deselect any field if you import the export file to Prestashop with this module!' mod='ipcatalogexportimport'}</span>
                        </div>
                        <div class="row">
                            <div class="scrollable col-md-6">
                                <div class="row fields_header">
                                    <button class="btn btn-default select_all_columns"><i class="icon-check-square-o"></i>{l s='Select all' mod='ipcatalogexportimport'}</button>&nbsp;&nbsp;
                                    <button class="btn btn-default reset_columns">{l s='Reset selection' mod='ipcatalogexportimport'}</button>&nbsp;&nbsp;
                                    <span class="clearable pull-right">
                                        <input class="fields_search" type="search" placeholder="{l s='Search...' mod='ipcatalogexportimport'}" />
                                        <i class="clearable_clear">&times;</i>
                                    </span>
                                </div>
                                <ul class="list-group item-list">
                                    <li class="list-group-item selected" data-value="carrier.id_carrier"><i class="icon-check-square-o"></i>{l s='Carrier ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="carrier.id_reference"><i class="icon-check-square-o"></i>{l s='Reference ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="carrier.active"><i class="icon-check-square-o"></i>{l s='Active (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="carrier.name"><i class="icon-check-square-o"></i>{l s='Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="carrier_lang.delay"><i class="icon-check-square-o"></i>{l s='Transit Time' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    {if version_compare(_PS_VERSION_, 8, '<') }<li class="list-group-item additional" data-value="carrier.id_tax_rules_group"><i class="icon-square-o"></i>{l s='Tax Rule ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="trg.name"><i class="icon-check-square-o"></i>{l s='Tax Rule Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>{/if}
                                        <li class="list-group-item selected" data-value="carrier.url"><i class="icon-check-square-o"></i>{l s='Tracking URL' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item additional" data-value="carrier_logo"><i class="icon-square-o"></i>{l s='Logo' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="logo_url"><i class="icon-check-square-o"></i>{l s='Logo URL' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.shipping_handling"><i class="icon-check-square-o"></i>{l s='Shipping Handling (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.range_behavior" title="{l s='0 = Disable carrier, 1 = Apply the cost of the highest defined range' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Range Behavior (0/1)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.is_module"><i class="icon-check-square-o"></i>{l s='Is Module (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.is_free"><i class="icon-check-square-o"></i>{l s='Is Free (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.shipping_external"><i class="icon-check-square-o"></i>{l s='External Shipping (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.external_module_name"><i class="icon-check-square-o"></i>{l s='External Module Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.shipping_method" title="{l s='1 = According to total weight, 2 = According to total price' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Shipping Method (0/1/2)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.position"><i class="icon-check-square-o"></i>{l s='Position' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.max_width"><i class="icon-check-square-o"></i>{l s='Max Width' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.max_height"><i class="icon-check-square-o"></i>{l s='Max Height' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.max_depth"><i class="icon-check-square-o"></i>{l s='Max Depth' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.max_weight"><i class="icon-check-square-o"></i>{l s='Max Weight' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier.grade"><i class="icon-check-square-o"></i>{l s='Speed Grade' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item additional" data-value="carrier_group.ids" title="{l s='Associated group IDs' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Group IDs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier_group.names" title="{l s='Associated group names' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Group Names (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item additional" data-value="carrier_zone.ids" title="{l s='Associated zone IDs' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Zone IDs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="carrier_zone.names" title="{l s='Associated zone names' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Zone Names (Name:Active) (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="range_price.ranges"><i class="icon-check-square-o"></i>{l s='Price Ranges (Min - Max) (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="range_price.price"><i class="icon-check-square-o"></i>{l s='Zone Prices by Total Price (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="range_weight.ranges"><i class="icon-check-square-o"></i>{l s='Weight Ranges (Min - Max) (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item selected" data-value="range_weight.price"><i class="icon-check-square-o"></i>{l s='Zone Prices by Total Weight (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="panel-footer">
                    <div class="text-center">
                        <button type="submit" value="1" name="carrier_export" class="btn btn-default">
                            <i class="process-icon-export"></i>{l s='Export Carriers' mod='ipcatalogexportimport'}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>