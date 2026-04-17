{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<div id="catalogEI-tab-content-Combination" class="catalogEI-tab-content" style="display: none;">
    <form id="combination_export_form" class="form-horizontal catalogEI_form" action="{$ajax_url}" method="post" enctype="multipart/form-data" name="combination_export" novalidate="novalidate">
        <input type="hidden" name="export" value="combination" />
        <div id="catalogEI-combination" class="panel catalogEI-tab">
            <h3 class="tab"><i class="icon-list-ul"></i> {l s='Combinations' mod='ipcatalogexportimport'}</h3>
            <div class="form-wrapper">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#combination_configurations">
                            <i class="icon-wrench"></i>
                            {l s='Configurations' mod='ipcatalogexportimport'}</a></li>
                    <li><a data-toggle="tab" href="#combination_filters">
                            <i class="icon-filter"></i>
                            {l s='Filters' mod='ipcatalogexportimport'}</a></li>
                    <li><a data-toggle="tab" href="#combination_fields">
                            <i class="icon-sliders"></i>
                            {l s='Fields' mod='ipcatalogexportimport'}</a></li>
                </ul>
                <div class="tab-content">
                    <div id="combination_configurations" class="tab-pane in active">
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
                                       id="combination_doc_name"
                                       value="{l s='Combinations' mod='ipcatalogexportimport'}"
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
                                            id="combination_csv_separator">
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
                            id="combination_csv_enclosure">
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
                                       id="combination_multivalue_separator"
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
                                        id="combination_language">
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
                                <select id="combination_sort" name="sort" {*multiple*} class="fixed-width-xxl">
                                    <option value="combination.id_product_attribute">{l s='ID' mod='ipcatalogexportimport'}</option>
                                    <option value="combination.reference">{l s='Reference' mod='ipcatalogexportimport'}</option>
                                    <option value="product.reference">{l s='Product Reference' mod='ipcatalogexportimport'}</option>
                                    <option value="product_lang.name">{l s='Product Name' mod='ipcatalogexportimport'}</option>
                                    <option value="combination.supplier_reference">{l s='Supplier Reference' mod='ipcatalogexportimport'}</option>
                                    <option value="combination.ean13">{l s='EAN13' mod='ipcatalogexportimport'}</option>
                                    <option value="combination.upc">{l s='UPC' mod='ipcatalogexportimport'}</option>
                                    <option value="combination_shop.wholesale_price">{l s='Wholesale Price' mod='ipcatalogexportimport'}</option>
                                    <option value="combination_shop.price">{l s='Impact On Price' mod='ipcatalogexportimport'}</option>
                                    <option value="combination_shop.unit_price_impact">{l s='Impact On Unit Price' mod='ipcatalogexportimport'}</option>
                                    <option value="stock_available.quantity">{l s='Quantity' mod='ipcatalogexportimport'}</option>
                                    <option value="combination.quantity">{l s='Quantity (old versions)' mod='ipcatalogexportimport'}</option>
                                    <option value="combination_shop.minimal_quantity">{l s='Minimal Quantity' mod='ipcatalogexportimport'}</option>
                                    <option value="combination_shop.weight">{l s='Impact On Weight' mod='ipcatalogexportimport'}</option>
                                    <option value="combination_shop.available_date">{l s='Availability Date' mod='ipcatalogexportimport'}</option>
                                </select>
                                <p class="help-block">
                                </p>
                            </div>
                            <div class="col-lg-5 col-lg-offset-1">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="sort_way" id="combination_sort_asc" value="1" checked="checked" />
                                    <label for="combination_sort_asc">{l s='ASC' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="sort_way" id="combination_sort_desc" value="0" />
                                    <label for="combination_sort_desc">{l s='DESC' mod='ipcatalogexportimport'}</label>
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
                                        id="combination_decimals">
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
                                    <input id="combination_speed" name="speed" type="range" min="1" max="7" step="1" value="4"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="combination_filters" class="tab-pane catalogEI_filters">
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
                                            id="combination_shop">
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
                        <h4 class="filter">{l s='Filter By Date' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group date_collapser">
                            <label class="control-label col-lg-3">
                                {l s='Product Creation Date' mod='ipcatalogexportimport'}
                            </label>
                            <div class="col-lg-9">
                                <select id="combination_date" name="date" class="fixed-width-xl">
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
                                            id="combination_from_date"
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
                                            id="combination_to_date"
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
                        <h4 class="filter">{l s='Filter By Discount' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Discount' mod='ipcatalogexportimport'}
                            </label>
                            <div class="col-lg-9">
                                <select name="discount"
                                        class="fixed-width-xl"
                                        id="combination_discount">
                                    <option selected value="all">{l s='All' mod='ipcatalogexportimport'}</option>
                                    <option value="discounted">{l s='With discount' mod='ipcatalogexportimport'}</option>
                                    <option value="nondiscounted">{l s='Without discount' mod='ipcatalogexportimport'}</option>
                                </select>
                            </div> 
                        </div>
                        <br>
                        <br>

                        <hr>
                        <h4 class="filter">{l s='Filter By Quantity' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Quantity' mod='ipcatalogexportimport'}
                            </label>
                            <div class="col-lg-9">
                                <select name="quantity_operator"
                                        class="fixed-width-lg inline"
                                        id="combination_quantity_operator">
                                    <option selected value="none">-- {l s='Not selected' mod='ipcatalogexportimport'} --</option>
                                    <option value="gt">></option>
                                    <option value="lt"><</option>
                                    <option value="eq">=</option>
                                </select>
                                &nbsp;&nbsp;
                                <input type="text"
                                       name="quantity"
                                       id="combination_quantity"
                                       value=""
                                       placeholder="50"
                                       class="fixed-width-lg inline"
                                       size="33"
                                       required="required" />
                            </div>
                        </div>
                        <br>
                        <br>

                        <hr>
                        <h4 class="filter">{l s='Filter By Category' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Since the category filter takes all the selected categories into account, if you create a new template, then add a new category, the products that have this category as default will not be exported with that template. In this case, you should disable the category filter.' mod='ipcatalogexportimport'}">
                                    {l s='Enabled' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="category_whether_filter" id="combination_category_yes_whether_filter" value="1" />
                                    <label for="combination_category_yes_whether_filter">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="category_whether_filter" id="combination_category_no_whether_filter" value="0" checked="checked" />
                                    <label for="combination_category_no_whether_filter">{l s='No' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                                <p class="help-block">
                                    <i>{l s='If disabled, the category filter will not be applied!' mod='ipcatalogexportimport'}</i>
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <i>{l s='Without Category' mod='ipcatalogexportimport'}</i>
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="category_without" id="combination_category_without_yes" value="1" checked="checked"/>
                                    <label for="combination_category_without_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="category_without" id="combination_category_without_no" value="0"/>
                                    <label for="combination_category_without_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                                <p class="help-block">
                                    {l s='Products without category (if any)' mod='ipcatalogexportimport'}
                                </p>
                            </div>
                        </div>
                        {$category_tree_3}
                        <br>
                        <br>

                        <hr>
                        <h4 class="filter">{l s='Filter Combinations' mod='ipcatalogexportimport'}</h4>
                        <select id="ctrl-show-selected-combinations" name="ctrl-show-selected-combinations" class="show_selected pull-left">
                            <option value="all" selected>{l s='Show all' mod='ipcatalogexportimport'}</option>
                            <option value="selected">{l s='Show selected' mod='ipcatalogexportimport'}</option>
                            <option value="not-selected">{l s='Show deselected' mod='ipcatalogexportimport'}</option>
                        </select>
                        <button id="refresh_combinations" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ipcatalogexportimport'}
                        </button>
                        <br>
                        <table id="combinations_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{l s='Combination ID' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Combination Reference' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Combination' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Combination Quantity' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Product ID' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Product Image' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Product Name' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Product Reference' mod='ipcatalogexportimport'}</th>
                                </tr>
                            </thead>
                        </table>
                        <br>
                        <br>

                        <hr>
                        <h4 class="filter">{l s='Filter By Attribute' mod='ipcatalogexportimport'}</h4>
                        <select id="ctrl-show-selected-attributes" name="ctrl-show-selected-attributes" class="show_selected pull-left">
                            <option value="all" selected>{l s='Show all' mod='ipcatalogexportimport'}</option>
                            <option value="selected">{l s='Show selected' mod='ipcatalogexportimport'}</option>
                            <option value="not-selected">{l s='Show deselected' mod='ipcatalogexportimport'}</option>
                        </select>
                        <button id="refresh_attributes" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ipcatalogexportimport'}
                        </button>
                        <br>
                        <table id="attributes_table" class="table table-striped table-bordered" style="width:100%;table-layout:fixed;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{l s='ID' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Group Name' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Name' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Group Type' mod='ipcatalogexportimport'}</th>
                                </tr>
                            </thead>
                        </table>
                        <br>
                        <br>

                    </div>
                    <div id="combination_fields" class="tab-pane catalogEI_fields catalogEI_combination_fields">
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
                                    <li class="list-group-item selected" data-value="combination.id_product_attribute"><i class="icon-check-square-o"></i>{l s='Combination ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination.reference"><i class="icon-check-square-o"></i>{l s='Combination Reference' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination.id_product"><i class="icon-check-square-o"></i>{l s='Product ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="product.reference"><i class="icon-check-square-o"></i>{l s='Product Reference' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="product_lang.name"><i class="icon-check-square-o"></i>{l s='Product Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="attributes.name_values"><i class="icon-square-o"></i>{l s='Combination' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="attributes.types"><i class="icon-check-square-o"></i>{l s='Attribute Type (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="attributes.groups"><i class="icon-check-square-o"></i>{l s='Attribute Name (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="attributes.public_name"><i class="icon-check-square-o"></i>{l s='Attribute Public Name (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="attributes.values"><i class="icon-check-square-o"></i>{l s='Value:Color / Value (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination.supplier_reference"><i class="icon-check-square-o"></i>{l s='Supplier Reference' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="{if version_compare(_PS_VERSION_, 8, '<') }combination{else}product{/if}.location"><i class="icon-check-square-o"></i>{l s='Location' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination.ean13"><i class="icon-check-square-o"></i>{l s='EAN13' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination.upc"><i class="icon-check-square-o"></i>{l s='UPC' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination.isbn"><i class="icon-check-square-o"></i>{l s='ISBN' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination.mpn"><i class="icon-check-square-o"></i>{l s='MPN' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination_shop.wholesale_price"><i class="icon-check-square-o"></i>{l s='Cost (Wholesale) Price' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination_shop.price"><i class="icon-check-square-o"></i>{l s='Impact on Price' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination_shop.unit_price_impact"><i class="icon-check-square-o"></i>{l s='Impact on Unit Price' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination_shop.ecotax"><i class="icon-check-square-o"></i>{l s='Ecotax' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="stock_available.quantity"><i class="icon-check-square-o"></i>{l s='Quantity' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="combination.quantity"><i class="icon-square-o"></i>{l s='Quantity (old versions)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination_shop.minimal_quantity"><i class="icon-check-square-o"></i>{l s='Minimal Quantity' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {if $show_low_stock}*}
                                    <li class="list-group-item quantities selected" data-value="combination_shop.low_stock_threshold" title="{l s='Shows the stock level which you consider is low' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Low Stock Level' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item quantities selected" data-value="combination_shop.low_stock_alert" title="{l s='Send me an email when the quantity is under this level' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Low Stock Alert (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {/if}*}
                                    <li class="list-group-item selected" data-value="combination_shop.weight"><i class="icon-check-square-o"></i>{l s='Impact on Weight' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination_shop.default_on"><i class="icon-check-square-o"></i>{l s='Is Default (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="combination_shop.available_date"><i class="icon-check-square-o"></i>{l s='Availability Date' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="image_urls"><i class="icon-check-square-o"></i>{l s='Image URLs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                                    <li class="list-group-item selected" data-value="image_position"><i class="icon-check-square-o"></i>{l s='Image Positions (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                    <li class="list-group-item selected" data-value="image.texts"><i class="icon-check-square-o"></i>{l s='Image Alt Texts (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item quantities selected" data-value="stock_available.out_of_stock" title="{l s='0 = Deny orders, 1 = Allow orders, 2 = Use default behavior' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Action When out of Stock' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
{*                                            {if $old_ps}*}
{*                                        <li class="list-group-item quantities selected" data-value="stock_available.depends_on_stock" title="{l s='0 = I want to specify available quantities manually, 1 = The available quantities for the current product and its combinations are based on the stock in your warehouse (using the advanced stock management system)' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Depends on Stock' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
{*                                            {/if}*}
                                    <li class="list-group-item quantities selected" data-value="warehouse.name_ref_loc"><i class="icon-check-square-o"></i>{l s='Warehouses (Reference:Name:Location) (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="panel-footer">
                <div class="text-center">
                    <button type="submit" value="1" name="combination_export" class="btn btn-default">
                        <i class="process-icon-export"></i>{l s='Export Combinations' mod='ipcatalogexportimport'}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>