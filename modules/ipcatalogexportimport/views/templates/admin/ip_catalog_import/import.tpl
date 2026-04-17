{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<script type="text/javascript">
    var controller_link = "{$ajax_url}";
    var files = {json_encode($files)};

    var initialData = {$initial_data};

    var setProductsTable = true;
    var setAttributesTable = true;
    var setFeaturesTable = true;
    var setManufacturersTable = true;
    // For Brands separately
    var setBrandsTable = true;
    //
    var setSuppliersTable = true;
    var setSuppliers2Table = true;
    var setCarriersTable = true;
    var setCustomersTable = true;
    var setGroupsTable = true;
    var setGroups2Table = true;

    var errorEmpty = "{l s='Please name your data matching configuration in order to save it.' mod='ipcatalogexportimport'}";

    var default_template = "-- {l s='Default' mod='ipcatalogexportimport'} --";
    var apply = "{l s='Apply' mod='ipcatalogexportimport'}";
    var use = "{l s='Use' mod='ipcatalogexportimport'}";
    var invalid_template_name = "{l s='Enter a valid template name' mod='ipcatalogexportimport'}";
    var template_loaded = "{l s='Template loaded.' mod='ipcatalogexportimport'}";
    var sure_to_delete = "{l s='Are you sure to delete?' mod='ipcatalogexportimport'}";
    var fill_required_fields = "{l s='Fill in the required fields!' mod='ipcatalogexportimport'}";
    var edit = "{l s='Edit' mod='ipcatalogexportimport'}";
    var delet = "{l s='Delete' mod='ipcatalogexportimport'}";
    var add_url = "{l s='Add a new file URL and choose what to import' mod='ipcatalogexportimport'}";
    var edit_url = "{l s='Edit the file URL and choose what to import' mod='ipcatalogexportimport'}";
    var add_ftp = "{l s='Add a new FTP address and choose what to import' mod='ipcatalogexportimport'}";
    var edit_ftp = "{l s='Edit the FTP address and choose what to import' mod='ipcatalogexportimport'}";
    var view_your_date = "{l s='View your data' mod='ipcatalogexportimport'}";
    var columns_match = "{l s='Please match each column of your source file to one of the destination columns.' mod='ipcatalogexportimport'}";
    var load = "{l s='Load' mod='ipcatalogexportimport'}";
    var all_fields_mapped = "{l s='All fields mapped' mod='ipcatalogexportimport' js=1}";
    var one_field_not_found = "{l s='1 field not found' mod='ipcatalogexportimport' js=1}";
    var some_fields_not_found = "{l s='#%d fields not found' mod='ipcatalogexportimport' js=1}";
    var all_fields_ignored = "{l s='All fields ignored' mod='ipcatalogexportimport' js=1}";

    var paginator = {$paginator|json_encode};
</script>

<div id="fader"></div>
<img id='spinner' src='{$module_path}views/img/spinner.svg' style="display:none;" />
<div class="catalogEI catalogEI-import row">
    <div class="col-lg-12">
        <div class="row">
            <div class="catalogEITabs col-lg-2 col-md-3">
                <div class="list-group">
                    <a class="list-group-item active" id="link-Product" href="{$ajax_url}&import=products">
                        <i class="icon-cubes"></i>
                        {l s='Products' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Feature" href="{$ajax_url}&import=features">
                        <i class="icon-th"></i>
                        {l s='Features' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Attribute" href="{$ajax_url}&import=attributes">
                        <i class="icon-table"></i>
                        {l s='Attributes' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Combination" href="{$ajax_url}&import=combinations">
                        <i class="icon-list-ul"></i>
                        {l s='Combinations' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Pack" href="{$ajax_url}&import=packs">
                        <i class="icon-briefcase"></i>
                        {l s='Packs' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Discount" href="{$ajax_url}&import=discounts">
                        <i class="icon-money"></i>
                        {l s='Discounts' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Category" href="{$ajax_url}&import=categories">
                        <i class="icon-sitemap"></i>
                        {l s='Categories' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Brand" href="{$ajax_url}&import=brands">
                        <i class="icon-bank"></i>
                        {l s='Manufacturers' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Supplier" href="{$ajax_url}&import=suppliers">
                        <i class="icon-credit-card"></i>
                        {l s='Suppliers' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Carrier" href="{$ajax_url}&import=carriers">
                        <i class="icon-truck"></i>
                        {l s='Carriers' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Group" href="{$ajax_url}&import=groups">
                        <i class="icon-user-secret"></i>
                        {l s='Groups' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Customer" href="{$ajax_url}&import=customers">
                        <i class="icon-group"></i>
                        {l s='Customers' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Address" href="{$ajax_url}&import=addresses">
                        <i class="icon-map-marker" style="font-size: 16px;"></i>
                        {l s='Addresses' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Store" href="{$ajax_url}&import=stores">
                        <i class="icon-newspaper-o"></i>
                        {l s='Store contacts' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Alias" href="{$ajax_url}&import=aliases">
                        <i class="icon-clipboard"></i>
                        {l s='Aliases' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Warehouse" href="{$ajax_url}&import=warehouses">
                        <i class="icon-building"></i>
                        {l s='Warehouses' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item invisible"></a>
                    <a class="list-group-item" id="link-Save" href="{$ajax_url}&import=save">
                        <i class="icon-save"></i>
                        {l s='Save' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Schedule" href="{$ajax_url}&import=schedule">
                        <i class="icon-clock-o"></i>
                        {l s='Cron' mod='ipcatalogexportimport'}
                    </a>
                    <a class="list-group-item" id="link-Support" href="{$ajax_url}&import=support">
                        <i class="icon-support"></i>
                        {l s='Support' mod='ipcatalogexportimport'}
                    </a>
                </div>
            </div>
            <div class="col-lg-10 col-md-9">
                {include file='./product.tpl'}
                {include file='./feature.tpl'}
                {include file='./attribute.tpl'}
                {include file='./combination.tpl'}
                {include file='./pack.tpl'}
                {include file='./discount.tpl'}
                {include file='./category.tpl'}
                {include file='./brand.tpl'}
                {include file='./supplier.tpl'}
                {include file='./carrier.tpl'}
                {include file='./group.tpl'}
                {include file='./customer.tpl'}
                {include file='./address.tpl'}
                {include file='./store.tpl'}
                {include file='./alias.tpl'}
                {include file='./warehouse.tpl'}
                {include file='./save.tpl'}
                {include file='./schedule.tpl'}
                {include file='./../support.tpl'}
            </div>
        </div>
    </div>
</div>
{include file='./modals.tpl'}
