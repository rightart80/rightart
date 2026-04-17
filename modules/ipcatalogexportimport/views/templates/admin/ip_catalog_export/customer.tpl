{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<div id="catalogEI-tab-content-Customer" class="catalogEI-tab-content" style="display: none;">
    <form id="customer_export_form" class="form-horizontal catalogEI_form" action="{$ajax_url}" method="post" enctype="multipart/form-data" name="customer_export" novalidate="novalidate">
        <input type="hidden" name="export" value="customer" />
        <div id="catalogEI-customer" class="panel catalogEI-tab">
            <h3 class="tab"><i class="icon-group"></i> {l s='Customers' mod='ipcatalogexportimport'}</h3>
            <div class="form-wrapper">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#customer_configurations">
                            <i class="icon-wrench"></i>
                            {l s='Configurations' mod='ipcatalogexportimport'}</a></li>
                    <li><a data-toggle="tab" href="#customer_filters">
                            <i class="icon-filter"></i>
                            {l s='Filters' mod='ipcatalogexportimport'}</a></li>
                    <li><a data-toggle="tab" href="#customer_fields">
                            <i class="icon-sliders"></i>
                            {l s='Fields' mod='ipcatalogexportimport'}</a></li>
                </ul>
                <div class="tab-content">
                    <div id="customer_configurations" class="tab-pane in active">
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
                                       id="customer_doc_name"
                                       value="{l s='Customers' mod='ipcatalogexportimport'}"
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
                                            id="customer_csv_separator">
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
                                            id="customer_csv_enclosure">
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
                                       id="customer_multivalue_separator"
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
                                        id="customer_language">
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
                                <select id="customer_sort" name="sort" {*multiple*} class="fixed-width-xxl">
                                    <option value="customer.id_customer">{l s='ID' mod='ipcatalogexportimport'}</option>
                                    <option value="customer.email">{l s='Email' mod='ipcatalogexportimport'}</option>
                                    <option value="customer.firstname">{l s='First Name' mod='ipcatalogexportimport'}</option>
                                    <option value="customer.lastname">{l s='Last Name' mod='ipcatalogexportimport'}</option>
                                    <option value="default_group_lang.name">{l s='Default Group Name' mod='ipcatalogexportimport'}</option>
                                    <option value="customer.date_add">{l s='Registration Date' mod='ipcatalogexportimport'}</option>
                                </select>
                                <p class="help-block">
                                </p>
                            </div>
                            <div class="col-lg-5 col-lg-offset-1">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="sort_way" id="customer_sort_asc" value="1" checked="checked" />
                                    <label for="customer_sort_asc">{l s='ASC' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="sort_way" id="customer_sort_desc" value="0" />
                                    <label for="customer_sort_desc">{l s='DESC' mod='ipcatalogexportimport'}</label>
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
                                        id="customer_decimals">
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
                                    <input id="customer_speed" name="speed" type="range" min="1" max="7" step="1" value="4"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="customer_filters" class="tab-pane catalogEI_filters">
                        <div class="alert alert-info alert-dismissible fade in">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <span>{l s='You can filter the products here. If all items of a table (e.g. Feature) are selected or deselected, they are not included in the filter.' mod='ipcatalogexportimport'}</span>
                        </div>
                        <hr>
                        <h4 class="filter">{l s='Filter By Date' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group date_collapser">
                            <label class="control-label col-lg-3">
                                {l s='Customer Registration Date' mod='ipcatalogexportimport'}
                            </label>
                            <div class="col-lg-9">
                                <select id="customer_date" name="date" class="fixed-width-xl">
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
                                            id="customer_from_date"
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
                                            id="customer_to_date"
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
                                        id="customer_availability">
                                    <option selected value="all">{l s='All' mod='ipcatalogexportimport'}</option>
                                    <option value="active">{l s='Active' mod='ipcatalogexportimport'}</option>
                                    <option value="inactive">{l s='Inactive' mod='ipcatalogexportimport'}</option>
                                </select>
                            </div> 
                        </div>
                        <br>
                        <br>


                        <hr>
                        <h4 class="filter">{l s='Filter By Customer' mod='ipcatalogexportimport'}</h4>
                        <select id="ctrl-show-selected-customers" name="ctrl-show-selected-customers" class="show_selected pull-left">
                            <option value="all" selected>{l s='Show all' mod='ipcatalogexportimport'}</option>
                            <option value="selected">{l s='Show selected' mod='ipcatalogexportimport'}</option>
                            <option value="not-selected">{l s='Show deselected' mod='ipcatalogexportimport'}</option>
                        </select>
                        <button id="refresh_customers" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ipcatalogexportimport'}
                        </button>
                        <br>
                        <table id="customers_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{l s='ID' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Social Title' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='First Name' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Last Name' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Email' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Group' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Status' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Newsletter' mod='ipcatalogexportimport'}</th>
                                        {*                <th>{l s='Deleted' mod='ipcatalogexportimport'}</th>*}
                                </tr>
                            </thead>
                        </table>
                        <br>
                        <br>
                        <br>


                        <hr>
                        <h4 class="filter">{l s='Filter By Customer Group' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <i>{l s='Without Group' mod='ipcatalogexportimport'}</i>
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="group_without" id="customer_group_without_yes" value="1" checked="checked"/>
                                    <label for="customer_group_without_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="group_without" id="customer_group_without_no" value="0"/>
                                    <label for="customer_group_without_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                                <p class="help-block">
                                    {l s='Customers without group (if any)' mod='ipcatalogexportimport'}
                                </p>
                            </div>
                        </div>
                        <select id="ctrl-show-selected-groups" name="ctrl-show-selected-groups" class="show_selected pull-left">
                            <option value="all" selected>{l s='Show all' mod='ipcatalogexportimport'}</option>
                            <option value="selected">{l s='Show selected' mod='ipcatalogexportimport'}</option>
                            <option value="not-selected">{l s='Show deselected' mod='ipcatalogexportimport'}</option>
                        </select>
                        <button id="refresh_groups" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ipcatalogexportimport'}
                        </button>
                        <br>
                        <table id="groups_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{l s='ID' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Name' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Discount' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Members' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Show Prices' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Creation Date' mod='ipcatalogexportimport'}</th>
                                </tr>
                            </thead>
                        </table>
                        <br>
                        <br>
                        <br>

                    </div>
                    <div id="customer_fields" class="tab-pane catalogEI_fields catalogEI_customer_fields">
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
                                    <li class="list-group-item selected" data-value="customer.id_customer"><i class="icon-check-square-o"></i>{l s='Customer ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.active"><i class="icon-check-square-o"></i>{l s='Active (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.id_gender"><i class="icon-check-square-o"></i>{l s='Title ID (1 = Mr, 2 = Mrs, 0 = else)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.email"><i class="icon-check-square-o"></i>{l s='Email' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.passwd"><i class="icon-check-square-o"></i>{l s='Current Password (Encrypted)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="new_passwd"><i class="icon-check-square-o"></i>{l s='New Password' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.firstname"><i class="icon-check-square-o"></i>{l s='First Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.lastname"><i class="icon-check-square-o"></i>{l s='Last Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.birthday"><i class="icon-check-square-o"></i>{l s='Birthday' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.date_add"><i class="icon-check-square-o"></i>{l s='Registration Date' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="customer.date_upd"><i class="icon-square-o"></i>{l s='Update Date' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.is_guest"><i class="icon-check-square-o"></i>{l s='Is Guest (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="customer.id_lang"><i class="icon-square-o"></i>{l s='Language ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="lang.name"><i class="icon-square-o"></i>{l s='Language Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="lang.iso_code"><i class="icon-check-square-o"></i>{l s='Customer Language (ISO)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.newsletter"><i class="icon-check-square-o"></i>{l s='Newsletter (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.newsletter_date_add"><i class="icon-check-square-o"></i>{l s='Newsletter Subscription Date' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.optin"><i class="icon-check-square-o"></i>{l s='Opt-in (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="customer.secure_key"><i class="icon-square-o"></i>{l s='Secure Key' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.note"><i class="icon-check-square-o"></i>{l s='Private Note' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="customer.id_default_group" title="{l s='Default Group ID' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Default Group ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="default_group_lang.name" title="{l s='Default Group Name' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Default Group Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="groupp.ids"><i class="icon-square-o"></i>{l s='Group IDs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="groupp.names"><i class="icon-check-square-o"></i>{l s='Group Names (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.company"><i class="icon-check-square-o"></i>{l s='Company' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.siret"><i class="icon-check-square-o"></i>{l s='SIRET' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.ape"><i class="icon-check-square-o"></i>{l s='APE' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.website"><i class="icon-check-square-o"></i>{l s='Website' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.outstanding_allow_amount"><i class="icon-check-square-o"></i>{l s='Allowed Outstanding Amount' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.max_payment_days"><i class="icon-check-square-o"></i>{l s='Maximum Number of Payment Days' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="customer.id_risk"><i class="icon-check-square-o"></i>{l s='Risk ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="risk_lang.name"><i class="icon-square-o"></i>{l s='Risk Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="panel-footer">
                <div class="text-center">
                    <button type="submit" value="1" name="customer_export" class="btn btn-default">
                        <i class="process-icon-export"></i>{l s='Export Customers' mod='ipcatalogexportimport'}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>