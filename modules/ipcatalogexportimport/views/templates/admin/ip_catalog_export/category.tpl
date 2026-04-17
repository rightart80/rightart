{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<div id="catalogEI-tab-content-Category" class="catalogEI-tab-content" style="display: none;">
    <form id="category_export_form" class="form-horizontal catalogEI_form" action="{$ajax_url}" method="post" enctype="multipart/form-data" name="category_export" novalidate="novalidate">
        <input type="hidden" name="export" value="category" />
        <div id="catalogEI-category" class="panel catalogEI-tab">
            <h3 class="tab"><i class="icon-sitemap"></i> {l s='Categories' mod='ipcatalogexportimport'}</h3>
            <div class="form-wrapper">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#category_configurations">
                            <i class="icon-wrench"></i>
                            {l s='Configurations' mod='ipcatalogexportimport'}</a></li>
                    <li><a data-toggle="tab" href="#category_filters">
                            <i class="icon-filter"></i>
                            {l s='Filters' mod='ipcatalogexportimport'}</a></li>
                    <li><a data-toggle="tab" href="#category_fields">
                            <i class="icon-sliders"></i>
                            {l s='Fields' mod='ipcatalogexportimport'}</a></li>
                </ul>
                <div class="tab-content">
                    <div id="category_configurations" class="tab-pane in active">
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
                                       id="category_doc_name"
                                       value="{l s='Categories' mod='ipcatalogexportimport'}"
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
                                            id="category_csv_separator">
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
                                            id="category_csv_enclosure">
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
                            <div class="col-lg-3">
                                <input type="text"
                                       name="multivalue_separator"
                                       id="category_multivalue_separator"
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
                                        id="category_language">
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
                                <select id="category_sort" name="sort" {*multiple*} class="fixed-width-xxl">
                                    <option value="category.id_category">{l s='ID' mod='ipcatalogexportimport'}</option>
                                    <option value="category_lang.name">{l s='Name' mod='ipcatalogexportimport'}</option>
                                    <option value="category_lang.link_rewrite">{l s='Rewritten URL' mod='ipcatalogexportimport'}</option>
                                    <option value="category.id_parent">{l s='Parent ID' mod='ipcatalogexportimport'}</option>
                                    <option value="parent.name">{l s='Parent Name' mod='ipcatalogexportimport'}</option>
                                    <option value="category.level_depth">{l s='Depth Level' mod='ipcatalogexportimport'}</option>
                                    <option value="category.nleft">{l s='Nested Left' mod='ipcatalogexportimport'}</option>
                                    <option value="category.nright">{l s='Nested Right' mod='ipcatalogexportimport'}</option>
                                    <option value="category.date_add">{l s='Creation Date' mod='ipcatalogexportimport'}</option>
                                </select>
                                <p class="help-block">
                                </p>
                            </div>
                            <div class="col-lg-5 col-lg-offset-1">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="sort_way" id="category_sort_asc" value="1" checked="checked" />
                                    <label for="category_sort_asc">{l s='ASC' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="sort_way" id="category_sort_desc" value="0" />
                                    <label for="category_sort_desc">{l s='DESC' mod='ipcatalogexportimport'}</label>
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
                                        id="category_decimals">
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
                                    <input id="category_speed" name="speed" type="range" min="1" max="7" step="1" value="4"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="category_filters" class="tab-pane catalogEI_filters">
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
                                            id="category_shop">
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
                                {l s='Category Creation Date' mod='ipcatalogexportimport'}
                            </label>
                            <div class="col-lg-9">
                                <select id="category_date" name="date" class="fixed-width-xl">
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
                                            id="category_from_date"
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
                                            id="category_to_date"
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
                                        id="category_availability">
                                    <option selected value="all">{l s='All' mod='ipcatalogexportimport'}</option>
                                    <option value="active">{l s='Active' mod='ipcatalogexportimport'}</option>
                                    <option value="inactive">{l s='Inactive' mod='ipcatalogexportimport'}</option>
                                </select>
                            </div> 
                        </div>
                        <br>
                        <br>


                        <hr>
                        <h4 class="filter">{l s='Filter By Category' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Since the category filter takes all the selected categories into account, if you create a new template, then add a new category, it will not be exported with that template. In this case, you should disable the category filter.' mod='ipcatalogexportimport'}">
                                    {l s='Enabled' mod='ipcatalogexportimport'}
                                </span>
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="category_whether_filter" id="category_category_yes_whether_filter" value="1" />
                                    <label for="category_category_yes_whether_filter">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="category_whether_filter" id="category_category_no_whether_filter" value="0" checked="checked" />
                                    <label for="category_category_no_whether_filter">{l s='No' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                                <p class="help-block">
                                    <i>{l s='If disabled, the category filter will not be applied!' mod='ipcatalogexportimport'}</i>
                                </p>
                            </div>
                        </div>
                        {$category_tree_2}
                        <br>
                        <br>

                    </div>
                    <div id="category_fields" class="tab-pane catalogEI_fields catalogEI_category_fields">
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
                                    <li class="list-group-item selected" data-value="category.id_category"><i class="icon-check-square-o"></i>{l s='Category ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="category.active"><i class="icon-check-square-o"></i>{l s='Active (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="category_lang.name"><i class="icon-check-square-o"></i>{l s='Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="category_link"><i class="icon-square-o"></i>{l s='Link' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="category.id_parent" title="{l s='Parent category ID' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Parent Category ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="parent.name" title="{l s='Parent category name' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Parent Category Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="category_lang.description"><i class="icon-check-square-o"></i>{l s='Description' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="category_lang.link_rewrite"><i class="icon-check-square-o"></i>{l s='Rewritten URL' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="category_lang.meta_title"><i class="icon-check-square-o"></i>{l s='Meta Title' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="category_lang.meta_keywords"><i class="icon-check-square-o"></i>{l s='Meta Keywords' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="category_lang.meta_description"><i class="icon-check-square-o"></i>{l s='Meta Description' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                <li class="list-group-item additional" data-value="category.id_shop_default"><i class="icon-square-o"></i>{l s='Default Shop ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                            {*                <li class="list-group-item selected" data-value="default_shop.name"><i class="icon-check-square-o"></i>{l s='Default Shop Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                            {*                <li class="list-group-item selected" data-value="category.level_depth" title="{l s='Number of parents' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Depth Level' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                            {*                <li class="list-group-item selected" data-value="category.nleft" title="{l s='Nested tree model "left" value' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Nested Left' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                            {*                <li class="list-group-item selected" data-value="category.nright" title="{l s='Nested tree model "right" value' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Nested Right' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                    <li class="list-group-item selected" data-value="category.position" title="{l s='The lower position comes first' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Position' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="category_image"><i class="icon-square-o"></i>{l s='Image' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="image_url"><i class="icon-check-square-o"></i>{l s='Image URL' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="category.date_add" title="{l s='When the category was created' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Creation Date' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="category.date_upd" title="{l s='When the category was updated' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Update Date' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="category.is_root_category" title="{l s='Is Root (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Is Root (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item additional" data-value="category_group.ids" title="{l s='Associated group IDs' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Group IDs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="category_group.names" title="{l s='Associated group names' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Group Names (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item selected" data-value="group_reduction.reductions"><i class="icon-check-square-o"></i>{l s='Group Reductions (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                <li class="list-group-item selected" data-value="category_shop.position"><i class="icon-check-square-o"></i>{l s='Position in Shop' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="panel-footer">
                <div class="text-center">
                    <button type="submit" value="1" name="category_export" class="btn btn-default">
                        <i class="process-icon-export"></i>{l s='Export Categories' mod='ipcatalogexportimport'}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>