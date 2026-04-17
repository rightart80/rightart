{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<div id="catalogEI-tab-content-Product" class="catalogEI-tab-content">
    <form id="product_export_form" class="form-horizontal catalogEI_form" action="{$ajax_url}" method="post" enctype="multipart/form-data" name="product_export" novalidate="novalidate">
        <input type="hidden" name="export" value="product" />
        <div id="catalogEI-product" class="panel catalogEI-tab">
            <h3 class="tab"><i class="icon-cubes"></i> {l s='Products' mod='ipcatalogexportimport'}</h3>
            <div class="form-wrapper">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#product_configurations">
                            <i class="icon-wrench"></i>
                            {l s='Configurations' mod='ipcatalogexportimport'}</a></li>
                    <li><a data-toggle="tab" href="#product_filters">
                            <i class="icon-filter"></i>
                            {l s='Filters' mod='ipcatalogexportimport'}</a></li>
                    <li><a data-toggle="tab" href="#product_fields">
                            <i class="icon-sliders"></i>
                            {l s='Fields' mod='ipcatalogexportimport'}</a></li>
                </ul>
                <div class="tab-content">
                    <div id="product_configurations" class="tab-pane in active">
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
                                       id="product_doc_name"
                                       value="{l s='Products' mod='ipcatalogexportimport'}"
                                       class="fixed-width-xxl"
                                       size="33"	
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
                                            id="product_csv_separator">
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
                            id="product_csv_enclosure">
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
                                       id="product_multivalue_separator"
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
                                        id="product_language">
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
                                <select id="product_sort" name="sort" {*multiple*} class="fixed-width-xxl">
                                    <option value="product.id_product">{l s='ID' mod='ipcatalogexportimport'}</option>
                                    <option value="product.reference">{l s='Reference' mod='ipcatalogexportimport'}</option>
                                    <option value="product_lang.name">{l s='Name' mod='ipcatalogexportimport'}</option>
                                    <option value="product.ean13">{l s='UPC' mod='ipcatalogexportimport'}</option>
                                    {if $show_mpn}
                                        <option value="product.mpn">{l s='MPN' mod='ipcatalogexportimport'}</option>
                                    {/if}
                                    {if $show_isbn}
                                        <option value="product.isbn">{l s='ISBN' mod='ipcatalogexportimport'}</option>
                                    {/if}
                                    <option value="product_shop.visibility">{l s='Visibility' mod='ipcatalogexportimport'}</option>
                                    <option value="product_shop.condition">{l s='Condition' mod='ipcatalogexportimport'}</option>
                                    <option value="product_shop.available_date">{l s='Availability Date' mod='ipcatalogexportimport'}</option>
                                    <option value="product_shop.date_add">{l s='Product Creation Date' mod='ipcatalogexportimport'}</option>
                                    <option value="product_shop.price">{l s='Price (tax excluded)' mod='ipcatalogexportimport'}</option>
                                    <option value="product_shop.wholesale_price">{l s='Cost (Wholesale) Price' mod='ipcatalogexportimport'}</option>
                                    <option value="product_shop.ecotax">{l s='Ecotax' mod='ipcatalogexportimport'}</option>
                                    <option value="default_category.id">{l s='Default Category ID' mod='ipcatalogexportimport'}</option>
                                    <option value="default_category.name">{l s='Default Category Name' mod='ipcatalogexportimport'}</option>
                                    <option value="product.id_manufacturer">{l s='Brand ID' mod='ipcatalogexportimport'}</option>
                                    <option value="manufacturer.name">{l s='Brand Name' mod='ipcatalogexportimport'}</option>
                                    <option value="product.width">{l s='Width' mod='ipcatalogexportimport'}</option>
                                    <option value="product.height">{l s='Height' mod='ipcatalogexportimport'}</option>
                                    <option value="product.depth">{l s='Depth' mod='ipcatalogexportimport'}</option>
                                    <option value="product.weight">{l s='Weight' mod='ipcatalogexportimport'}</option>
                                    <option value="product_sale.quantity">{l s='Sold Quantity' mod='ipcatalogexportimport'}</option>
                                    <option value="product_sale.sale_nbr">{l s='Number of Sales' mod='ipcatalogexportimport'}</option>
                                    <option value="stock_available.quantity">{l s='Quantity' mod='ipcatalogexportimport'}</option>
                                    <option value="product.quantity">{l s='Quantity (old versions)' mod='ipcatalogexportimport'}</option>
                                    <option value="product_shop.minimal_quantity">{l s='Minimal Quantity' mod='ipcatalogexportimport'}</option>
                                    <option value="product.location">{l s='Location' mod='ipcatalogexportimport'}</option>
                                    <option value="product_shop.low_stock_threshold">{l s='Low Stock Level' mod='ipcatalogexportimport'}</option>
                                    <option value="product_lang.available_now">{l s='Label when in stock' mod='ipcatalogexportimport'}</option>
                                    <option value="product_lang.available_later">{l s='Label when backorder allowed' mod='ipcatalogexportimport'}</option>
                                    <option value="product_shop.advanced_stock_management">{l s='Advanced Stock Management' mod='ipcatalogexportimport'}</option>
                                    <option value="stock_available.depends_on_stock">{l s='Depends on stock' mod='ipcatalogexportimport'}</option>
                                    {*                <option value="stock.id_warehouse">{l s='Warehouse ID' mod='ipcatalogexportimport'}</option>*}
                                    <option value="product.id_supplier">{l s='Default Supplier ID' mod='ipcatalogexportimport'}</option>
                                    <option value="default_supplier.name">{l s='Default Supplier Name' mod='ipcatalogexportimport'}</option>
                                    <option value="product.supplier_reference">{l s='Default Supplier Reference' mod='ipcatalogexportimport'}</option>
                                </select>
                                <p class="help-block">
                                </p>
                            </div>
                            <div class="col-lg-5 col-lg-offset-1">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="sort_way" id="product_sort_asc" value="1" checked="checked" />
                                    <label for="product_sort_asc">{l s='ASC' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="sort_way" id="product_sort_desc" value="0" />
                                    <label for="product_sort_desc">{l s='DESC' mod='ipcatalogexportimport'}</label>
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
                                        id="product_decimals">
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
                                    <input id="product_speed" name="speed" type="range" min="1" max="7" step="1" value="4"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="product_filters" class="tab-pane catalogEI_filters">
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
                                            id="product_shop">
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
                                <select id="product_date" name="date" class="fixed-width-xl">
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
                                            id="product_from_date"
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
                                            id="product_to_date"
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
                                        id="product_availability">
                                    <option selected value="all">{l s='All' mod='ipcatalogexportimport'}</option>
                                    <option value="active">{l s='Active' mod='ipcatalogexportimport'}</option>
                                    <option value="inactive">{l s='Inactive' mod='ipcatalogexportimport'}</option>
                                </select>
                            </div> 
                        </div>
                        <br>
                        <br>

                        <hr>
                        <h4 class="filter">{l s='Filter By Price' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Price' mod='ipcatalogexportimport'}
                            </label>
                            <div class="col-lg-6">
                                <select name="price_operator"
                                        class="fixed-width-lg inline"
                                        id="product_price_operator">
                                    <option selected value="none">-- {l s='Not selected' mod='ipcatalogexportimport'} --</option>
                                    <option value="gt">></option>
                                    <option value="lt"><</option>
                                    <option value="eq">=</option>
                                </select>
                                &nbsp;&nbsp;
                                <input type="text"
                                       name="price"
                                       id="product_price"
                                       value=""
                                       placeholder="20"
                                       class="fixed-width-lg inline"
                                       size="33"	
                                       required="required" />
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
                                        id="product_quantity_operator">
                                    <option selected value="none">-- {l s='Not selected' mod='ipcatalogexportimport'} --</option>
                                    <option value="gt">></option>
                                    <option value="lt"><</option>
                                    <option value="eq">=</option>
                                </select>
                                &nbsp;&nbsp;
                                <input type="text"
                                       name="quantity"
                                       id="product_quantity"
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
                        <h4 class="filter">{l s='Filter By Discount' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Discount' mod='ipcatalogexportimport'}
                            </label>
                            <div class="col-lg-9">
                                <select name="discount"
                                        class="fixed-width-xl"
                                        id="product_discount">
                                    <option selected value="all">{l s='All' mod='ipcatalogexportimport'}</option>
                                    <option value="discounted">{l s='With discount' mod='ipcatalogexportimport'}</option>
                                    <option value="nondiscounted">{l s='Without discount' mod='ipcatalogexportimport'}</option>
                                </select>
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
                                    <input type="radio" name="category_whether_filter" id="product_category_yes_whether_filter" value="1" />
                                    <label for="product_category_yes_whether_filter">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="category_whether_filter" id="product_category_no_whether_filter" value="0" checked="checked" />
                                    <label for="product_category_no_whether_filter">{l s='No' mod='ipcatalogexportimport'}</label>
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
                                    <input type="radio" name="category_without" id="product_category_without_yes" value="1" checked="checked"/>
                                    <label for="product_category_without_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="category_without" id="product_category_without_no" value="0"/>
                                    <label for="product_category_without_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                                <p class="help-block">
                                    {l s='Products without category (if any)' mod='ipcatalogexportimport'}
                                </p>
                            </div>
                        </div>
                        {$category_tree}
                        <br>
                        <br>

                        <hr>
                        <h4 class="filter">{l s='Filter By Condition' mod='ipcatalogexportimport'}</h4>
                        <div class="scrollable">
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='New' mod='ipcatalogexportimport'}
                                </label>
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="conditions[new]" id="product_condition_yes_new" value="1" checked="checked"/>
                                        <label for="product_condition_yes_new">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                        <input type="radio" name="conditions[new]" id="product_condition_no_new" value="0"/>
                                        <label for="product_condition_no_new">{l s='No' mod='ipcatalogexportimport'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                    <p class="help-block">
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Used' mod='ipcatalogexportimport'}
                                </label>
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="conditions[used]" id="product_condition_yes_used" value="1" checked="checked"/>
                                        <label for="product_condition_yes_used">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                        <input type="radio" name="conditions[used]" id="product_condition_no_used" value="0"/>
                                        <label for="product_condition_no_used">{l s='No' mod='ipcatalogexportimport'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                    <p class="help-block">
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Refurbished' mod='ipcatalogexportimport'}
                                </label>
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="conditions[refurbished]" id="product_condition_yes_refurbished" value="1" checked="checked"/>
                                        <label for="product_condition_yes_refurbished">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                        <input type="radio" name="conditions[refurbished]" id="product_condition_no_refurbished" value="0"/>
                                        <label for="product_condition_no_refurbished">{l s='No' mod='ipcatalogexportimport'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                    <p class="help-block">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <br>
                        <br>

                        <hr>
                        <h4 class="filter">{l s='Filter By Visibility' mod='ipcatalogexportimport'}</h4>
                        <div class="scrollable">
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Everywhere' mod='ipcatalogexportimport'}
                                </label>
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="visibility[both]" id="product_visibility_yes_both" value="1" checked="checked"/>
                                        <label for="product_visibility_yes_both">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                        <input type="radio" name="visibility[both]" id="product_visibility_no_both" value="0"/>
                                        <label for="product_visibility_no_both">{l s='No' mod='ipcatalogexportimport'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                    <p class="help-block">
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Catalog only' mod='ipcatalogexportimport'}
                                </label>
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="visibility[catalog]" id="product_visibility_yes_catalog" value="1" checked="checked"/>
                                        <label for="product_visibility_yes_catalog">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                        <input type="radio" name="visibility[catalog]" id="product_visibility_no_catalog" value="0"/>
                                        <label for="product_visibility_no_catalog">{l s='No' mod='ipcatalogexportimport'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                    <p class="help-block">
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Search only' mod='ipcatalogexportimport'}
                                </label>
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="visibility[search]" id="product_visibility_yes_search" value="1" checked="checked"/>
                                        <label for="product_visibility_yes_search">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                        <input type="radio" name="visibility[search]" id="product_visibility_no_search" value="0"/>
                                        <label for="product_visibility_no_search">{l s='No' mod='ipcatalogexportimport'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                    <p class="help-block">
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Nowhere' mod='ipcatalogexportimport'}
                                </label>
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="visibility[none]" id="product_visibility_yes_none" value="1" checked="checked"/>
                                        <label for="product_visibility_yes_none">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                        <input type="radio" name="visibility[none]" id="product_visibility_no_none" value="0"/>
                                        <label for="product_visibility_no_none">{l s='No' mod='ipcatalogexportimport'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                    <p class="help-block">
                                    </p>
                                </div>
                            </div>
                        </div>
                        <br>
                        <br>


                        <hr>
                        <h4 class="filter">{l s='Filter Products' mod='ipcatalogexportimport'}</h4>
                        <select id="ctrl-show-selected-products" name="ctrl-show-selected-products" class="show_selected pull-left">
                            <option value="all" selected>{l s='Show all' mod='ipcatalogexportimport'}</option>
                            <option value="selected">{l s='Show selected' mod='ipcatalogexportimport'}</option>
                            <option value="not-selected">{l s='Show deselected' mod='ipcatalogexportimport'}</option>
                        </select>
                        <button id="refresh_products" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ipcatalogexportimport'}
                        </button>
                        <br>
                        <table id="products_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{l s='ID' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Image' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Name' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Reference' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Category' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Base Price' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Final Price' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Quantity' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Status' mod='ipcatalogexportimport'}</th>
                                </tr>
                            </thead>
                        </table>
                        <br>
                        <br>


                        <hr>
                        <h4 class="filter">{l s='Filter By Feature' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <i>{l s='Without Feature' mod='ipcatalogexportimport'}</i>
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="feature_without" id="product_feature_without_yes" value="1" checked="checked"/>
                                    <label for="product_feature_without_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="feature_without" id="product_feature_without_no" value="0"/>
                                    <label for="product_feature_without_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                                <p class="help-block">
                                    {l s='Products without feature (if any)' mod='ipcatalogexportimport'}
                                </p>
                            </div>
                        </div>
                        <select id="ctrl-show-selected-features" name="ctrl-show-selected-features" class="show_selected pull-left">
                            <option value="all" selected>{l s='Show all' mod='ipcatalogexportimport'}</option>
                            <option value="selected">{l s='Show selected' mod='ipcatalogexportimport'}</option>
                            <option value="not-selected">{l s='Show deselected' mod='ipcatalogexportimport'}</option>
                        </select>
                        <button id="refresh_features" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ipcatalogexportimport'}
                        </button>
                        <br>
                        <table id="features_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{l s='Name' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Value' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Custom' mod='ipcatalogexportimport'}</th>
                                </tr>
                            </thead>
                        </table>
                        <br>
                        <br>

                        <hr>
                        <h4 class="filter">{l s='Filter By Brand' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <i>{l s='Without Brand' mod='ipcatalogexportimport'}</i>
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="manufacturer_without" id="product_manufacturer_without_yes" value="1" checked="checked"/>
                                    <label for="product_manufacturer_without_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="manufacturer_without" id="product_manufacturer_without_no" value="0"/>
                                    <label for="product_manufacturer_without_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                                <p class="help-block">
                                    {l s='Products without brand (if any)' mod='ipcatalogexportimport'}
                                </p>
                            </div>
                        </div>
                        <select id="ctrl-show-selected-manufacturers" name="ctrl-show-selected-manufacturers" class="show_selected pull-left">
                            <option value="all" selected>{l s='Show all' mod='ipcatalogexportimport'}</option>
                            <option value="selected">{l s='Show selected' mod='ipcatalogexportimport'}</option>
                            <option value="not-selected">{l s='Show deselected' mod='ipcatalogexportimport'}</option>
                        </select>
                        <button id="refresh_manufacturers" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ipcatalogexportimport'}
                        </button>
                        <br>
                        <table id="manufacturers_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{l s='ID' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Logo' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Name' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Addresses' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Products' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Status' mod='ipcatalogexportimport'}</th>
                                </tr>
                            </thead>
                        </table>
                        <br>
                        <br>


                        <hr>
                        <h4 class="filter">{l s='Filter By Supplier' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <i>{l s='Without Supplier' mod='ipcatalogexportimport'}</i>
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="supplier_without" id="product_supplier_without_yes" value="1" checked="checked"/>
                                    <label for="product_supplier_without_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="supplier_without" id="product_supplier_without_no" value="0"/>
                                    <label for="product_supplier_without_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                                <p class="help-block">
                                    {l s='Products without supplier (if any)' mod='ipcatalogexportimport'}
                                </p>
                            </div>
                        </div>
                        <select id="ctrl-show-selected-suppliers" name="ctrl-show-selected-suppliers" class="show_selected pull-left">
                            <option value="all" selected>{l s='Show all' mod='ipcatalogexportimport'}</option>
                            <option value="selected">{l s='Show selected' mod='ipcatalogexportimport'}</option>
                            <option value="not-selected">{l s='Show deselected' mod='ipcatalogexportimport'}</option>
                        </select>
                        <button id="refresh_suppliers" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ipcatalogexportimport'}
                        </button>
                        <br>
                        <table id="suppliers_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{l s='ID' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Logo' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Name' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Products' mod='ipcatalogexportimport'}</th>
                                    <th>{l s='Status' mod='ipcatalogexportimport'}</th>
                                </tr>
                            </thead>
                        </table>
                        <br>
                        <br>


                        <hr>
                        <h4 class="filter">{l s='Filter By Carrier' mod='ipcatalogexportimport'}</h4>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                <i>{l s='Without Carrier' mod='ipcatalogexportimport'}</i>
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="carrier_without" id="product_carrier_without_yes" value="1" checked="checked"/>
                                    <label for="product_carrier_without_yes">{l s='Yes' mod='ipcatalogexportimport'}</label>
                                    <input type="radio" name="carrier_without" id="product_carrier_without_no" value="0"/>
                                    <label for="product_carrier_without_no">{l s='No' mod='ipcatalogexportimport'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                                <p class="help-block">
                                    {l s='Products without carrier (if any)' mod='ipcatalogexportimport'}
                                </p>
                            </div>
                        </div>
                        <select id="ctrl-show-selected-carriers" name="ctrl-show-selected-carriers" class="show_selected pull-left">
                            <option value="all" selected>{l s='Show all' mod='ipcatalogexportimport'}</option>
                            <option value="selected">{l s='Show selected' mod='ipcatalogexportimport'}</option>
                            <option value="not-selected">{l s='Show deselected' mod='ipcatalogexportimport'}</option>
                        </select>
                        <button id="refresh_carriers" class="btn btn-default refresh_button"><i class="icon-refresh"></i>
                            {l s='Reload table' mod='ipcatalogexportimport'}
                        </button>
                        <br>
                        <table id="carriers_table" class="table table-striped table-bordered" style="width:100%;table-layout: fixed;">
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
                    <div id="product_fields" class="tab-pane catalogEI_fields catalogEI_product_fields">
                        <div class="alert alert-warning alert-dismissible">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <span>{l s='It is important not to select or deselect any field if you import the export file to Prestashop with this module!' mod='ipcatalogexportimport'}</span>
                        </div>
                        <div class="row">
                            <div class="list-group col-lg-2 col-md-3">
                                <h4 class="group_header">{l s='Select a group to see its fields' mod='ipcatalogexportimport'}</h4>
                                <div id="product_columns_group" class="columns_group">
                                    <a href="#" id="product_fields_all" class="list-group-item list-group-item-action active"><strong>{l s='All' mod='ipcatalogexportimport'}</strong></a>
                                    <a href="#" id="product_fields_information" class="list-group-item list-group-item-action">{l s='Information' mod='ipcatalogexportimport'}</a>
                                    <a href="#" id="product_fields_prices" class="list-group-item list-group-item-action">{l s='Prices' mod='ipcatalogexportimport'}</a>
                                    <a href="#" id="product_fields_seo" class="list-group-item list-group-item-action">{l s='SEO' mod='ipcatalogexportimport'}</a>
                                    <a href="#" id="product_fields_associations" class="list-group-item list-group-item-action">{l s='Associations' mod='ipcatalogexportimport'}</a>
                                    <a href="#" id="product_fields_quantities" class="list-group-item list-group-item-action">{l s='Quantities' mod='ipcatalogexportimport'}</a>
                                    <a href="#" id="product_fields_images" class="list-group-item list-group-item-action">{l s='Images' mod='ipcatalogexportimport'}</a>
                                    <a href="#" id="product_fields_features" class="list-group-item list-group-item-action">{l s='Features' mod='ipcatalogexportimport'}</a>
                                    <a href="#" id="product_fields_customization" class="list-group-item list-group-item-action">{l s='Customization' mod='ipcatalogexportimport'}</a>
                                    <a href="#" id="product_fields_attachments" class="list-group-item list-group-item-action">{l s='Attachments' mod='ipcatalogexportimport'}</a>
                                    <a href="#" id="product_fields_suppliers" class="list-group-item list-group-item-action">{l s='Suppliers' mod='ipcatalogexportimport'}</a>
                                </div>
                            </div>
                            <div class="scrollable col-md-6 pull-right">
                                <div class="row fields_header">
                                    <button class="btn btn-default select_all_columns"><i class="icon-check-square-o"></i>{l s='Select all' mod='ipcatalogexportimport'}</button>&nbsp;&nbsp;
                                    <button class="btn btn-default reset_columns">{l s='Reset selection' mod='ipcatalogexportimport'}</button>&nbsp;&nbsp;
                                    <span class="clearable">
                                        <input class="fields_search" type="search" placeholder="{l s='Search...' mod='ipcatalogexportimport'}" />
                                        <i class="clearable_clear">&times;</i>
                                    </span>
                                </div>
                                <ul class="list-group item-list">
                                    <li class="list-group-item information selected" data-value="product.id_product"><i class="icon-check-square-o"></i>{l s='Product ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product.reference" title="{l s='Product Reference' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Reference' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_shop.active" title="{l s='Whether the product is enabled or not' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Active (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_lang.name" title="{l s='Product Name' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information additional" data-value="product_link" title="{l s='Product Link' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Link' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product.ean13" title="{l s='This type of product code is specific to Europe and Japan, but is widely used internationally. It is a superset of the UPC code: all products marked with an EAN will be accepted in North America.' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='EAN13' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product.upc" title="{l s='This type of product code is widely used in the United States, Canada, the United Kingdom, Australia, New Zealand and in other countries.' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='UPC' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {if $show_isbn}*}
                                    <li class="list-group-item information selected" data-value="product.isbn" title="{l s='The International Standard Book Number (ISBN) is used to identify books and other publications.' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='ISBN' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {/if}*}
                                            {*                        {if $show_mpn}*}
                                    <li class="list-group-item information selected" data-value="product.mpn" title="{l s='MPN is used internationally to identify the Manufacturer Part Number.' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='MPN' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {/if}*}
                                    <li class="list-group-item information selected" data-value="product_shop.visibility" title="{l s='Shows if the product is visible in catalog or in search or in both or nowhere' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Visibility' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_shop.condition" title="{l s='Shows if the product is new or used or refurbished' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Condition' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {if $show_show_condition}*}
                                    <li class="list-group-item information selected" data-value="product_shop.show_condition" title="{l s='The product condition is shown to customer (visitor) or not' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Show Condition (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {/if}*}
                                    <li class="list-group-item information selected" data-value="product.cache_is_pack" title="{l s='Shows if the item is a package of products or just a product' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Is Pack (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_lang.description_short" title="{l s='Short Description' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Summary' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_lang.description" title="{l s='Long Description' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Description' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_tag.tags" title="{l s='Product keyword to find the product in search easily' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Tags (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_shop.available_for_order"><i class="icon-check-square-o"></i>{l s='Available for Order (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_shop.available_date" title="{l s='Until when the product is available' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Availability Date' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_shop.show_price" title="{l s='Show the product price or not' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Show Price (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_shop.date_add" title="{l s='When the product was created' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Creation Date' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information additional" data-value="product_shop.date_upd" title="{l s='When the product was updated' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Update Date' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_shop.online_only"><i class="icon-check-square-o"></i>{l s='Available Online Only (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product.is_virtual" title="{l s='Is the product is virtual or standard' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Is Virtual Product (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="file_url" title="{l s='The URL of the virtual file' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Virtual File URL' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_download.display_filename" title="{l s='The displayed name of the virtual file to an employee' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Virtual File Displayed Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information additional" data-value="product_download.filename" title="{l s='This is a unique name in database. Not displayed to an employee' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Virtual File Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_download.nb_downloadable" title="{l s='This is related to virtual file' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Number of Allowed Downloads' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_download.date_expiration" title="{l s='This is related to virtual file' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Expiration Date of Download' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_download.nb_days_accessible" title="{l s='This is related to virtual file' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Number of Days Accessible' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information selected" data-value="product_shop.pack_stock_type" title="{l s='How to behave when a pack of product is sold. 0 = Decrement pack only, 1 = Decrement products in pack only, 2 = Decrement both, 3 = Default: Decrement pack only' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Pack Quantity Type' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information additional" data-value="shop.id_shop" title="{l s='Displays shop ID in which the product is' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Shop ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information additional" data-value="shop.name" title="{l s='Displays shop name in which the product is' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Shop Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information additional" data-value="shop_group.id_shop_group" title="{l s='Displays shop group ID which the product shop belongs to' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Shop Group ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item information additional" data-value="shop_group.name" title="{l s='Displays shop group name which the product shop belongs to' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Shop Group Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item prices selected" data-value="product_shop.price" title="{l s='Product price without tax' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Price (Tax Excluded)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item prices selected" data-value="product_price_tax_incl" title="{l s='Product price with tax' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Price (Tax Included)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item prices selected" data-value="product_shop.wholesale_price" title="{l s='The price that you have paid to supplier to buy the product' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Cost (Wholesale) Price' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item prices selected" data-value="product_shop.on_sale" title="{l s='Whether the product is on sale badge or not' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='On Sale Badge (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item prices selected" data-value="specific_price_priority.priority" title="{l s='In which priority the discount should be applied' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Priority Management' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item prices additional" data-value="product_shop.id_tax_rules_group" title="{l s='The ID of the taxt rule which is applied to product' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Tax Rule ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item prices selected" data-value="tax_rules_group.name" title="{l s='The name of the tax rule' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Tax Rule Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li> 
                                    <li class="list-group-item prices selected" data-value="product_shop.ecotax" title="{l s='A tax levied on activities which are considered to be harmful to the environment' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Ecotax' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item prices selected" data-value="product_shop.unity" title="{l s='a bottle, a pack,  a set, a kilo, a litre, etc.' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Unity' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item prices selected" data-value="product_shop.unit_price"><i class="icon-check-square-o"></i>{l s='Unit Price' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item seo selected" data-value="product_lang.meta_title" title="{l s='Meta title helps search engines to find your product easily' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Meta Title' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item seo selected" data-value="product_lang.meta_keywords" title="{l s='Meta keywords helps search engines to find your product easily' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Meta Keywords' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item seo selected" data-value="product_lang.meta_description" title="{l s='Meta description helps search engines to find your product easily' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Meta Description' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item seo selected" data-value="product_lang.link_rewrite" title="{l s='User-friendly URL' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Rewritten URL' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item seo selected" data-value="product_shop.redirect_type" title="{l s='What HTTP header to redirect with' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Redirect Type' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {if $id_type_redirected}
                                        <li class="list-group-item seo selected" data-value="product_shop.id_type_redirected" title="{l s='What ID to redirect to' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Target Redirect ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item seo selected" data-value="target_type.name" title="{l s='What name to redirect to' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Target Redirect Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {else}
                                        <li class="list-group-item seo selected" data-value="product_shop.id_product_redirected" title="{l s='What ID to redirect to' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Target Redirect ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                        <li class="list-group-item seo selected" data-value="target_product.name" title="{l s='What name to redirect to' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Target Redirect Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {/if}
                                    <li class="list-group-item associations selected" data-value="default_category.id_category" title="{l s='Main category ID' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Default Category ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item associations selected" data-value="default_category.name" title="{l s='Main category name' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Default Category Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item associations selected" data-value="category.ids" title="{l s='All the associated category IDs' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Category IDs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item associations selected" data-value="category.names" title="{l s='All the associated category names' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Category Names (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item associations additional" data-value="product.id_manufacturer" title="{l s='Manufacturer ID' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Brand ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item associations selected" data-value="manufacturer.name" title="{l s='Manufacturer name' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Brand Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item associations selected" data-value="related_products.ids" title="{l s='All the associated product IDs (Accessory IDs)' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Related Product IDs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item associations selected" data-value="related_products.names" title="{l s='Accessory names' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Related Product Names (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item associations additional" data-value="pack.product_ids" title="{l s='All the product IDs in the package if this is a package' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Products IDs in Pack (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item associations selected" data-value="pack.product_names" title="{l s='All the product names in this package if this is a package' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Product Names in Pack (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item associations selected" data-value="pack.quantity" title="{l s='All the product quantities in this package if this is a package' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Product Quantities in Pack (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item shipping additional" data-value="carrier.ids" title="{l s='All the associated carrier IDs' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Carrier IDs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item shipping selected" data-value="carrier.names" title="{l s='All the associated carrier names' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Carrier Names (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item shipping selected" data-value="product.width" title="{l s='The width of the product' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Width' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item shipping selected" data-value="product.height" title="{l s='The height of the product' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Height' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item shipping selected" data-value="product.depth" title="{l s='The depth of the product' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Depth' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item shipping selected" data-value="product.weight" title="{l s='The weight of the product' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Weight' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {if $show_delivery_date}*}
                                    <li class="list-group-item shipping selected" data-value="product_lang.delivery_in_stock"><i class="icon-check-square-o"></i>{l s='Delivery Time of In-stock Products' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item shipping selected" data-value="product_lang.delivery_out_stock"><i class="icon-check-square-o"></i>{l s='Delivery Time of Out-of-stock Products' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {/if}*}
                                    <li class="list-group-item shipping selected" data-value="product_shop.additional_shipping_cost"><i class="icon-check-square-o"></i>{l s='Additional Shipping Fees' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {if $show_add_delivery_time}*}
                                    <li class="list-group-item shipping selected" data-value="product.additional_delivery_times" title="{l s='0 = None, 1 = Default delivery time, 2 = Specific delivery time to this product' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Delivery Time' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {/if}*}
                                    <li class="list-group-item quantities selected" data-value="product_sale.quantity" title="{l s='Total sold quantity of the product' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Sold Quantity' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item quantities selected" data-value="product_sale.sale_nbr" title="{l s='How many times it was sold' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Number of Sales' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item quantities selected" data-value="stock_available.quantity" title="{l s='How many of this product are available in stock' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Quantity' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item quantities additional" data-value="product.quantity" title="{l s='How many of this product are available' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Quantity (old versions)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item quantities selected" data-value="product_shop.minimal_quantity" title="{l s='Minumum quantity for sale' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Minimal Quantity' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item quantities selected" data-value="product.location" title="{l s='Product location' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Location' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {if $show_low_stock}*}
                                    <li class="list-group-item quantities selected" data-value="product_shop.low_stock_threshold" title="{l s='Shows the stock level which you consider is low' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Low Stock Level' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item quantities selected" data-value="product_shop.low_stock_alert" title="{l s='Send me an email when the quantity is under this level' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Low Stock Alert (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                        {/if}*}
                                    <li class="list-group-item quantities selected" data-value="product_lang.available_now" title="{l s='Text when in stock' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Label When in Stock' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item quantities selected" data-value="product_lang.available_later" title="{l s='Text when out of stock' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Label When Backorder Allowed' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item quantities selected" data-value="stock_available.out_of_stock" title="{l s='0 = Deny orders, 1 = Allow orders, 2 = Use default behavior' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Action When out of Stock' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {if $old_ps}
{*                                        <li class="list-group-item quantities selected" data-value="product_shop.advanced_stock_management"><i class="icon-check-square-o"></i>{l s='Advanced Stock Management (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
{*                                        <li class="list-group-item quantities selected" data-value="stock_available.depends_on_stock" title="{l s='0 = I want to specify available quantities manually, 1 = The available quantities for the current product and its combinations are based on the stock in your warehouse (using the advanced stock management system)' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Depends on Stock' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                            {/if}
                                    <li class="list-group-item quantities selected" data-value="warehouse.name_ref_loc"><i class="icon-check-square-o"></i>{l s='Warehouses (Reference:Name:Location) (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item quantities selected" data-value="product.quantity_discount"><i class="icon-check-square-o"></i>{l s='Quantity Discount (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                <li class="list-group-item quantities selected" data-value="stock.id_warehouse"><i class="icon-check-square-o"></i>{l s='Warehouse ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                    <li class="list-group-item images additional" data-value="cover_image" title="{l s='Product Image' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Cover Image' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item images additional" data-value="image.cover_id" title="{l s='Product\'s Cover Image ID' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Cover Image ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item images additional" data-value="image.ids" title="{l s='Product\'s Image IDs' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Image IDs' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item images selected" data-value="cover_image_url" title="{l s='The URL of the product\'s cover image' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Cover Image URL' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*{if $shop_feature}
                                            <li class="list-group-item images selected" data-value="urls_in_shop" title="{l s='All image URLs belonging to this shop' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Image URLs in this shop (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            <li class="list-group-item images additional" data-value="image_urls" title="{l s='All image URLs' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Image URLs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {else}*}
                                    <li class="list-group-item images selected" data-value="image_urls" title="{l s='All image URLs' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Image URLs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*{/if}*}
                                            {*                                    <li class="list-group-item images selected" data-value="image.positions"><i class="icon-check-square-o"></i>{l s='Image Positions (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                    <li class="list-group-item images selected" data-value="image.texts" title="{l s='Alternate text of images when they are not available (e.g. due to Internet connection problem)' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Image Alt Texts (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                <li class="list-group-item images selected" data-value="delete_images" title="{l s='Whether to delete the current images on the target shop' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Delete existing images (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                    <li class="list-group-item features selected" data-value="feature.name_value_custom"><i class="icon-check-square-o"></i>{l s='Features (Name~Value~Custom) (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                <li class="list-group-item features selected" data-value="feature.name_value"><i class="icon-check-square-o"></i>{l s='Feature Name:Value (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                            {*                <li class="list-group-item features selected" data-value="feature.position" title="{l s='Feature position defines which feature is displayed first, i.e. their sequence' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Feature Position (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                            {*                <li class="list-group-item features selected" data-value="feature.custom" title="{l s='Whether the feature has custom or pre-defined values' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Feature Is Custom (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                    <li class="list-group-item customizations selected" data-value="product_shop.customizable" title="{l s='Whether the product is customizable or not' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Is Customizable (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item customizations selected" data-value="product_shop.text_fields" title="{l s='How many this product has text fields for the customization' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Number of Customization Text Fields' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item customizations selected" data-value="product_shop.uploadable_files" title="{l s='How many this product has file fields for the customization' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Number of Customization File Fields' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item customizations selected" data-value="customization_field.name_type_req" title="{l s='Type: 0 = file field, 1 = text field' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Customization Fields (Label:Type:Required) (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                <li class="list-group-item customizations selected" data-value="customization_field.name"><i class="icon-check-square-o"></i>{l s='Customization Label (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                            {*                <li class="list-group-item customizations selected" data-value="customization_field.type"><i class="icon-check-square-o"></i>{l s='Customization Type (0 = File, 1 = Text)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                            {*                <li class="list-group-item customizations selected" data-value="customization_field.required" title="{l s='Whether the customization for this product is required or not' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Customization Required (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                    <li class="list-group-item attachments selected" data-value="product.cache_has_attachments" title="{l s='Shows whether the product has attachments or not' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Has Attachments (0 = No, 1 = Yes)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item attachments selected" data-value="attachment.id_attachment"><i class="icon-check-square-o"></i>{l s='Attachment IDs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item attachments selected" data-value="attachment_url"><i class="icon-check-square-o"></i>{l s='Attachment URLs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                <li class="list-group-item attachments selected" data-value="attachment.file" title="{l s='A unique attachment name in database' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Attachment File' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                            {*                <li class="list-group-item attachments selected" data-value="attachment.file_name" title="{l s='Displayed name of the attachment' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Attachment File Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                            {*                <li class="list-group-item attachments selected" data-value="attachment.file_size" title="{l s='Size of the attachment in bytes' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Attachment File Size' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                            {*                <li class="list-group-item attachments selected" data-value="attachment.mime" title="{l s='The file type that is used on the Internet' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Attachment Mime Type' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                    <li class="list-group-item attachments selected" data-value="attachment.name"><i class="icon-check-square-o"></i>{l s='Attachment Names (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item attachments selected" data-value="attachment.description"><i class="icon-check-square-o"></i>{l s='Attachment Descriptions (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item suppliers additional" data-value="product.id_supplier"><i class="icon-square-o"></i>{l s='Default Supplier ID' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item suppliers selected" data-value="default_supplier.name"><i class="icon-check-square-o"></i>{l s='Default Supplier Name' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item suppliers selected" data-value="product.supplier_reference"><i class="icon-check-square-o"></i>{l s='Default Supplier Reference' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item suppliers additional" data-value="supplier.id_supplier" title="{l s='All the associated supplier IDs' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Supplier IDs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item suppliers selected" data-value="supplier.name" title="{l s='All the associated supplier names' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Supplier Names (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item suppliers selected" data-value="supplier.product_supplier_reference" title="{l s='All the associated supplier references' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Supplier References (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item suppliers selected" data-value="supplier.product_supplier_price_te" title="{l s='All the associated supplier prices (tax excluded)' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Supplier Prices (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item suppliers additional" data-value="supplier.id_currency" title="{l s='Currency IDs that were used with suppliers' mod='ipcatalogexportimport'}"><i class="icon-square-o"></i>{l s='Supplier Currency IDs (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                    <li class="list-group-item suppliers selected" data-value="supplier.currency_iso_code" title="{l s='Currency ISO codes that were used with suppliers' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Supplier Currency ISO Codes (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>
                                            {*                <li class="list-group-item suppliers selected" data-value="supplier.name_ref_price_iso" title="{l s='Currency ISO codes that were used with suppliers' mod='ipcatalogexportimport'}"><i class="icon-check-square-o"></i>{l s='Suppliers (Name:Ref:Price:ISO) (x,y,z...)' mod='ipcatalogexportimport'}<span class="pull-right ui-icon ui-icon-arrowthick-2-n-s"></span></li>*}
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="panel-footer">
                <div class="text-center">
                    <button type="submit" value="1" name="product_export" class="btn btn-default">
                        <i class="process-icon-export"></i>{l s='Export Products' mod='ipcatalogexportimport'}
                    </button>
                </div>
            </div>
        </div>
        {include file='./../our_modules.tpl'}
    </form>
</div>


