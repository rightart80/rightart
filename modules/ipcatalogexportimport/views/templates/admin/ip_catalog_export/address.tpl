{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<div id="catalogEI-tab-content-Address" class="catalogEI-tab-content" style="display: none;">
    <form id="address_export_form" class="form-horizontal catalogEI_form" action="{$ajax_url}" method="post" enctype="multipart/form-data" name="address_export" novalidate="novalidate">
        <input type="hidden" name="export" value="address" />
        <div id="catalogEI-address" class="panel catalogEI-tab">
            <h3 class="tab"><i class="icon-map-marker" style="font-size: 16px;"></i> {l s='Addresses' mod='ipcatalogexportimport'}</h3>
            <div class="form-wrapper">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#address_configurations">
                            <i class="icon-wrench"></i>
                            {l s='Configurations' mod='ipcatalogexportimport'}</a></li>
                    <li><a data-toggle="tab" href="#address_filters">
                            <i class="icon-filter"></i>
                            {l s='Filters' mod='ipcatalogexportimport'}</a></li>
                    <li><a data-toggle="tab" href="#address_fields">
                            <i class="icon-sliders"></i>
                            {l s='Fields' mod='ipcatalogexportimport'}</a></li>
                </ul>
                <div class="tab-content">
                    <div id="address_configurations" class="tab-pane in active">
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
                                       id="address_doc_name"
                                       value="{l s='Addresses' mod='ipcatalogexportimport'}"
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
                                            id="address_csv_separator">
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
                                            id="address_csv_enclosure">
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
                                       id="address_multivalue_separator"
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
                                        id="address_language">
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
                                <select id="address_sort" name="sort" {*multiple*} class="fixed-width-xxl">
                                    <option value="address.id_address">{l s='ID' mod='ipcatalogexportimport'}</option>
                                    <option value="address.name">{l s='Name' mod='ipcatalogexportimport'}</option>
                                    <option value="address.date_add">{l s='Creation Date' mod='ipcatalogexportimport'}</option>
                                </select>
                                <p class="help-block">
                                </p>
                            </div>
                            <div class="col-lg-5 col-lg-offset-1">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="sort_way" id="address_sort_asc" value="1" checked="checked" />
                                    <label for="address_sort_asc">{l s='ASC' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="sort_way" id="address_sort_desc" value="0" />
                                    <label for="address_sort_desc">{l s='DESC' mod='ipcatalogexportimport'}</label>
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
                                        id="address_decimals">
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
                                    <input id="address_speed" name="speed" type="range" min="1" max="7" step="1" value="4"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="address_filters" class="tab-pane catalogEI_filters">
                        <div class="alert alert-info alert-dismissible fade in">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <span>{l s='You can filter the products here. If all items of a table (e.g. Feature) are selected or deselected, they are not included in the filter.' mod='ipcatalogexportimport'}</span>
                        </div>
                        <hr>
                        <h4 class="filter">{l s='Filter By Date' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group date_collapser">
                            <label class="control-label col-lg-3">
                                {l s='Address Creation Date' mod='ipcatalogexportimport'}
                            </label>
                            <div class="col-lg-9">
                                <select id="address_date" name="date" class="fixed-width-xl">
                                    <option selected="selected" value="no_date">-- {l s='All time' mod='ipcatalogexportimport'} --</option> 
                                    <option value="today">{l s='Today' mod='ipcatalogexportimport'}</option> 
                                    <option value="last_24_hours">{l s='Last 24 hours' mod='ipcatalogexportimport'}</option> 
                                    <option value="yesterday">{l s='Yesterday' mod='ipcatalogexportimport'}</option>
                                    <option value="this_week">{l s='This week' mod='ipcatalogexportimport'}</option>
                                    <option value="last_week">{l s='Last week' mod='ipcatalogexportimport'}</option>
                                    <option value="this_month">{l s='This month' mod='ipcatalogexportimport'}</option>
                                    <option value="last_month">{l s='Last month' mod='ipcatalogexportimport'}</option>
                                    <option value="select_date">{l s='Select date' mod='ipcatalogexportimport'}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group collapse">
                            <label class="control-label col-lg-3">
                                {l s='Select a period ' mod='ipcatalogexportimport'}
                            </label>
                            <div class="col-lg-9">
                                <div class="col-lg-4">
                                    <div class="input-group fixed-width-xl">
                                        <span class="input-group-addon">
                                            <i class="icon-calendar"></i>
                                            {l s='From' mod='ipcatalogexportimport'}
                                        </span>
                                        <input
                                            id="address_from_date"
                                            name="from_date"
                                            type="text"
                                            data-hex="true"
                                            class="datepicker"
                                            value="" />
                                    </div>
                                </div>
                                {*<div class="col-lg-2">
                                </div>*}
                                <div class="col-lg-4">
                                    <div class="input-group datepicker fixed-width-xl">
                                        <span class="input-group-addon">
                                            <i class="icon-calendar"></i>
                                            {l s='Till' mod='ipcatalogexportimport'}
                                        </span>
                                        <input
                                            id="address_to_date"
                                            name="to_date"
                                            type="text"
                                            data-hex="true"
                                            class=""
                                            value="" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <br>


                        <hr>
                        <h4 class="filter">{l s='Filter By Availability' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Availability' mod='ipcatalogexportimport'}
                            </label>
                            <div class="col-lg-9">
                                <select name="availability"
                                        class="fixed-width-xl"
                                        id="address_availability">
                                    <option selected value="all">{l s='All' mod='ipcatalogexportimport'}</option>
                                    <option value="active">{l s='Active' mod='ipcatalogexportimport'}</option>
                                    <option value="inactive">{l s='Inactive' mod='ipcatalogexportimport'}</option>
                                </select>
                            </div> 
                        </div>
                        <br>
                        <br>

                        <hr>
                        <h4 class="filter">{l s='Filter By Address' mod='ipcatalogexportimport'}</h4>
                        <select id="ctrl-show-selected-addresses" name="ctrl-show-selected-addresses" class="show_selected pull-left">
                            <option value="all" selected>{l s='Show all' mod='ipcatalogexportimport'}</option>
                            <option value="selected">{l s='Show selected' mod='ipcatalogexportimport'}</option>
                            <option value="not-selected">{l s='Show deselected' mod='ipcatalogexportimport'}</option>
                        </select>
                        <button id="refresh_addresses" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ipcatalogexportimport'}
                        </button>
                        <br>
                        <table id="addresses_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{l s='ID' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Address' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Country' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='City' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Firstname' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Lastname' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Customer Email' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Brand Name' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Supplier Name' mod='ipcatalogexportimport'}</th>
                                </tr>
                            </thead>
                        </table>
                        <br>
                        <br>
                    </div>
                    <div id="address_fields" class="tab-pane catalogEI_fields catalogEI_address_fields">
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
                                    <li class="list-group-item selected" data-value="address.id_address"><i class="icon-check-square-o"></i>{l s='Address ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.active"><i class="icon-check-square-o"></i>{l s='Active (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.address1"><i class="icon-check-square-o"></i>{l s='Address' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.address2"><i class="icon-check-square-o"></i>{l s='Address 2' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.alias"><i class="icon-check-square-o"></i>{l s='Alias' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.company"><i class="icon-check-square-o"></i>{l s='Company' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.firstname"><i class="icon-check-square-o"></i>{l s='First Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.lastname"><i class="icon-check-square-o"></i>{l s='Last Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.email"><i class="icon-check-square-o"></i>{l s='Customer Email' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="customer.firstname"><i class="icon-square-o"></i>{l s='Customer First Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="customer.lastname"><i class="icon-square-o"></i>{l s='Customer Last Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="address.id_manufacturer"><i class="icon-square-o"></i>{l s='Brand ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="manufacturer.name"><i class="icon-check-square-o"></i>{l s='Brand Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="address.id_supplier"><i class="icon-square-o"></i>{l s='Supplier ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="supplier.name"><i class="icon-check-square-o"></i>{l s='Supplier Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.postcode"><i class="icon-check-square-o"></i>{l s='Zip/Postal code' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.city"><i class="icon-check-square-o"></i>{l s='City' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="address.id_country"><i class="icon-square-o"></i>{l s='Country ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="country_lang.name"><i class="icon-check-square-o"></i>{l s='Country Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="state.id_state"><i class="icon-square-o"></i>{l s='State ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="state.name"><i class="icon-check-square-o"></i>{l s='State Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.other"><i class="icon-check-square-o"></i>{l s='Other' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.phone"><i class="icon-check-square-o"></i>{l s='Phone' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.phone_mobile"><i class="icon-check-square-o"></i>{l s='Mobile Phone' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.vat_number"><i class="icon-check-square-o"></i>{l s='VAT Number' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.dni"><i class="icon-check-square-o"></i>{l s='DNI (Identification Number)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="address.date_add" title="{l s='Address creation date' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Creation Date' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="address.date_upd" title="{l s='Address update date' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Update Date' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <div class="text-center">
                    <button type="submit" value="1" name="address_export" class="btn btn-default">
                        <i class="process-icon-export"></i>{l s='Export Addresses' mod='ipcatalogexportimport'}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>