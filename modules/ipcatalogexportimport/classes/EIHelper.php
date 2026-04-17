<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    SmartPresta <tehran.alishov@gmail.com>
 *  @copyright 2023 SmartPresta
 *  @license   Commercial License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

@ini_set('max_execution_time', 0);
if (!defined('MAX_LINE_SIZE')) {
    define('MAX_LINE_SIZE', 0);
}
if (!defined('MAX_COLUMNS')) {
    define('MAX_COLUMNS', 6);
}
@ini_set('auto_detect_line_endings', '1');

class EIHelper
{
    public $entity;
    public $fields;
    public $available_fields = [];
    public $required_fields = [];
    
    public function __construct($module)
    {
        $this->entity = Tools::getValue('entity');
        
        $this->fields = [
            'categories' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id' => ['label' => $module->l('Category ID', 'EIHelper')],
                'active' => ['label' => $module->l('Active (0 = No, 1 = Yes)', 'EIHelper')],
                'name' => ['label' => $module->l('Name', 'EIHelper')],
                'id_parent' => ['label' => $module->l('Parent Category ID', 'EIHelper')],
                'parent' => ['label' => $module->l('Parent Category Name', 'EIHelper')],
                'description' => ['label' => $module->l('Description', 'EIHelper')],
                'link_rewrite' => ['label' => $module->l('Rewritten URL', 'EIHelper')],
                'meta_title' => ['label' => $module->l('Meta Title', 'EIHelper')],
                'meta_keywords' => ['label' => $module->l('Meta Keywords', 'EIHelper')],
                'meta_description' => ['label' => $module->l('Meta Description', 'EIHelper')],
//                    'is_root_category' => [
//                        'label' => $module->l('Root category (0/1)', 'EIHelper'),
//                        'help' => $module->l('A category root is where a category tree can begin. This is used with multistore.', 'EIHelper'),
//                    ],
                'position' => ['label' => $module->l('Position', 'EIHelper')],
                'image' => ['label' => $module->l('Image URL', 'EIHelper')],
                'date_add' => ['label' => $module->l('Creation Date', 'EIHelper')],
                'groups' => ['label' => $module->l('Group Names (x,y,z...)', 'EIHelper')],
                'reductions' => ['label' => $module->l('Group Reductions (x,y,z...)', 'EIHelper')],
            ],
            'products' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id' => ['label' => $module->l('Product ID', 'EIHelper')],
                'reference' => ['label' => $module->l('Reference', 'EIHelper')],
                'active' => ['label' => $module->l('Active (0 = No, 1 = Yes)', 'EIHelper')],
                'name' => ['label' => $module->l('Name', 'EIHelper')],
                'ean13' => ['label' => $module->l('EAN13', 'EIHelper')],
                'upc' => ['label' => $module->l('UPC', 'EIHelper')],
                'isbn' => ['label' => $module->l('ISBN', 'EIHelper')],
                'mpn' => ['label' => $module->l('MPN', 'EIHelper')],
                'visibility' => ['label' => $module->l('Visibility', 'EIHelper')],
                'condition' => ['label' => $module->l('Condition', 'EIHelper')],
                'show_condition' => ['label' => $module->l('Show Condition (0 = No, 1 = Yes)', 'EIHelper')],
                'cache_is_pack' => ['label' => $module->l('Is Pack (0 = No, 1 = Yes)', 'EIHelper')],
                'description_short' => ['label' => $module->l('Summary', 'EIHelper')],
                'description' => ['label' => $module->l('Description', 'EIHelper')],
                'tags' => ['label' => $module->l('Tags (x,y,z...)', 'EIHelper')],
                'available_for_order' => ['label' => $module->l('Available for Order (0 = No, 1 = Yes)', 'EIHelper')],
                'available_date' => ['label' => $module->l('Availability Date', 'EIHelper')],
                'show_price' => ['label' => $module->l('Show Price (0 = No, 1 = Yes)', 'EIHelper')],
                'date_add' => ['label' => $module->l('Creation Date', 'EIHelper')],
                'online_only' => ['label' => $module->l('Available Online Only (0 = No, 1 = Yes)', 'EIHelper')],
                'is_virtual' => ['label' => $module->l('Is Virtual Product (0 = No, 1 = Yes)', 'EIHelper')],
                'file_url' => ['label' => $module->l('Virtual File URL', 'EIHelper')],
                'display_filename' => ['label' => $module->l('Virtual File Displayed Name', 'EIHelper')],
                'nb_downloadable' => [
                    'label' => $module->l('Number of Allowed Downloads', 'EIHelper'),
                    'help' => $module->l('Number of days this file can be accessed by customers. Set to zero for unlimited access.', 'EIHelper'),
                ],
                'date_expiration' => ['label' => $module->l('Expiration Date of Download', 'EIHelper')],
                'nb_days_accessible' => [
                    'label' => $module->l('Number of Days Accessible', 'EIHelper'),
                    'help' => $module->l('Number of days this file can be accessed by customers. Set to zero for unlimited access.', 'EIHelper'),
                ],
                'pack_stock_type' => ['label' => $module->l('Pack Quantity Type', 'EIHelper')],
                'price_tex' => ['label' => $module->l('Price (Tax Excluded)', 'EIHelper')],
                'price_tin' => ['label' => $module->l('Price (Tax Included)', 'EIHelper')],
                'wholesale_price' => ['label' => $module->l('Cost (Wholesale) Price', 'EIHelper')],
                'on_sale' => ['label' => $module->l('On Sale Badge (0 = No, 1 = Yes)', 'EIHelper')],
//                'reduction_amount' => ['label' => $module->l('Discount Amount', 'EIHelper')],
//                'reduction_type' => ['label' => $module->l('Discount Type', 'EIHelper')],
//                'reduction_from' => ['label' => $module->l('Discount from (yyyy-mm-dd)', 'EIHelper')],
//                'reduction_to' => ['label' => $module->l('Discount to (yyyy-mm-dd)', 'EIHelper')],
//                'reduction_price' => ['label' => $module->l('Discount Base Price', 'EIHelper')],
//                'reduction_from_quantity' => ['label' => $module->l('Discount From Quantity', 'EIHelper')],
//                'reduction_customer_email' => ['label' => $module->l('Discount Customer Email', 'EIHelper')],
//                'reduction_group_name' => ['label' => $module->l('Discount Group', 'EIHelper')],
//                'reduction_id_country' => ['label' => $module->l('Discount Country', 'EIHelper')],
//                'reduction_currency' => ['label' => $module->l('Discount Currency (ISO)', 'EIHelper')],
//                'reduction_priority' => ['label' => $module->l('Discount Priority', 'EIHelper')],
                'specific_price_priority' => ['label' => $module->l('Priority Management', 'EIHelper')],
                'id_tax_rules_group' => ['label' => $module->l('Tax Rule Name', 'EIHelper')],
                'ecotax' => ['label' => $module->l('Ecotax', 'EIHelper')],
                'unity' => ['label' => $module->l('Unity', 'EIHelper')],
                'unit_price' => ['label' => $module->l('Unit Price', 'EIHelper')],
                'meta_title' => ['label' => $module->l('Meta Title', 'EIHelper')],
                'meta_keywords' => ['label' => $module->l('Meta Keywords', 'EIHelper')],
                'meta_description' => ['label' => $module->l('Meta Description', 'EIHelper')],
                'link_rewrite' => ['label' => $module->l('Rewritten URL', 'EIHelper')],
                'redirect_type' => ['label' => $module->l('Redirect Type', 'EIHelper')],
                'id_type_redirected' => ['label' => $module->l('Target Redirect ID', 'EIHelper')],
                'type_redirected_name' => ['label' => $module->l('Target Redirect Name', 'EIHelper')],
                'id_category_default' => ['label' => $module->l('Default Category ID', 'EIHelper')],
                'category_default' => ['label' => $module->l('Default Category Name', 'EIHelper')],
                'id_category' => ['label' => $module->l('Category IDs (x,y,z...)', 'EIHelper')],
                'category' => ['label' => $module->l('Category Names (x,y,z...)', 'EIHelper')],
                'manufacturer' => ['label' => $module->l('Brand Name', 'EIHelper')],
                'id_accessories' => ['label' => $module->l('Related Product IDs (x,y,z...)', 'EIHelper')],
                'accessories' => ['label' => $module->l('Related Product Names (x,y,z...)', 'EIHelper')],
                'pack_products' => ['label' => $module->l('Product Names in Pack (x,y,z...)', 'EIHelper')],
                'pack_product_quantity' => ['label' => $module->l('Product Quantities in Pack (x,y,z...)', 'EIHelper')],
                'carriers' => ['label' => $module->l('Carrier Names (x,y,z...)', 'EIHelper')],
                'width' => ['label' => $module->l('Width', 'EIHelper')],
                'height' => ['label' => $module->l('Height', 'EIHelper')],
                'depth' => ['label' => $module->l('Depth', 'EIHelper')],
                'weight' => ['label' => $module->l('Weight', 'EIHelper')],
                'delivery_in_stock' => ['label' => $module->l('Delivery Time of In-stock Products', 'EIHelper')],
                'delivery_out_stock' => ['label' => $module->l('Delivery Time of Out-of-stock Products', 'EIHelper')],
                'additional_shipping_cost' => ['label' => $module->l('Additional Shipping Fees', 'EIHelper')],
                'additional_delivery_times' => ['label' => $module->l('Delivery Time', 'EIHelper')],
                'sold_quantity' => ['label' => $module->l('Sold Quantity', 'EIHelper')],
                'sale_nbr' => ['label' => $module->l('Number of Sales', 'EIHelper')],
                'quantity' => ['label' => $module->l('Quantity', 'EIHelper')],
                'minimal_quantity' => ['label' => $module->l('Minimal Quantity', 'EIHelper')],
                'location' => ['label' => $module->l('Location', 'EIHelper')],
                'low_stock_threshold' => ['label' => $module->l('Low Stock Level', 'EIHelper')],
                'low_stock_alert' => ['label' => $module->l('Low Stock Alert (0 = No, 1 = Yes)', 'EIHelper')],
                'available_now' => ['label' => $module->l('Label When in Stock', 'EIHelper')],
                'available_later' => ['label' => $module->l('Label When Backorder Allowed', 'EIHelper')],
                'out_of_stock' => ['label' => $module->l('Action When out of Stock', 'EIHelper')],
//                'advanced_stock_management' => [
//                    'label' => $module->l('Advanced Stock Management (0 = No, 1 = Yes)', 'EIHelper'),
//                    'help' => $module->l('Enable Advanced Stock Management on product (0 = No, 1 = Yes).', 'EIHelper'),
//                ],
//                'depends_on_stock' => [
//                    'label' => $module->l('Depends on Stock', 'EIHelper'),
//                    'help' => $module->l('0 = Use quantity set in product, 1 = Use quantity from warehouse.', 'EIHelper'),
//                ],
                'warehouse' => ['label' => $module->l('Warehouses (Reference:Name:Location) (x,y,z...)', 'EIHelper')],
                'quantity_discount' => ['label' => $module->l('Quantity Discount (0 = No, 1 = Yes)', 'EIHelper')],
                'cover_image' => ['label' => $module->l('Cover Image URL', 'EIHelper')],
                'image' => ['label' => $module->l('Image URLs (x,y,z...)', 'EIHelper')],
//                'image_position' => ['label' => $module->l('Image positions (x,y,z...)', 'EIHelper')],
                'image_alt' => ['label' => $module->l('Image Alt Texts (x,y,z...)', 'EIHelper')],
//                    'delete_existing_images' => ['label' => $module->l('Delete existing images (0 = No, 1 = Yes)', 'EIHelper')],
                'features' => ['label' => $module->l('Features (Name~Value~Custom) (x,y,z...)', 'EIHelper')],
                'customizable' => ['label' => $module->l('Is Customizable (0 = No, 1 = Yes)', 'EIHelper')],
                'text_fields' => ['label' => $module->l('Number of Customization Text Fields', 'EIHelper')],
                'uploadable_files' => ['label' => $module->l('Number of Customization File Fields', 'EIHelper')],
                'customization_fields' => ['label' => $module->l('Customization Fields (Label:Type:Required) (x,y,z...)', 'EIHelper')],
                'cache_has_attachments' => ['label' => $module->l('Has Attachments (0 = No, 1 = Yes)', 'EIHelper')],
                'attachment_id' => ['label' => $module->l('Attachment IDs (x,y,z...)', 'EIHelper')],
                'attachment_url' => ['label' => $module->l('Attachment URLs (x,y,z...)', 'EIHelper')],
                'attachment_name' => ['label' => $module->l('Attachment Names (x,y,z...)', 'EIHelper')],
                'attachment_desc' => ['label' => $module->l('Attachment Descriptions (x,y,z...)', 'EIHelper')],
                'supplier' => ['label' => $module->l('Default Supplier Name', 'EIHelper')],
                'supplier_reference' => ['label' => $module->l('Default Supplier Reference', 'EIHelper')],
                'supplier_names' => ['label' => $module->l('Supplier Names (x,y,z...)', 'EIHelper')],
                'supplier_references' => ['label' => $module->l('Supplier References (x,y,z...)', 'EIHelper')],
                'supplier_prices' => ['label' => $module->l('Supplier Prices (x,y,z...)', 'EIHelper')],
                'supplier_currencies' => ['label' => $module->l('Supplier Currency ISO Codes (x,y,z...)', 'EIHelper')],
//                    'shop' => [
//                        'label' => $module->l('ID / Name of shop', 'EIHelper'),
//                        'help' => $module->l('Ignore this field if you don\'t use the Multistore tool. If you leave this field empty, the default shop will be used.', 'EIHelper'),
//                    ],
            ],
            'combinations' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id' => ['label' => $module->l('Combination ID', 'EIHelper')],
                'reference' => ['label' => $module->l('Combination Reference', 'EIHelper')],
                'id_product' => ['label' => $module->l('Product ID', 'EIHelper')],
                'product_reference' => ['label' => $module->l('Product Reference', 'EIHelper')],
                'product_name' => ['label' => $module->l('Product Name', 'EIHelper')],
                'group_type' => ['label' => $module->l('Attribute Type (x,y,z...)', 'EIHelper') . ' *'],
                'group_name' => ['label' => $module->l('Attribute Name (x,y,z...)', 'EIHelper') . ' *'],
                'group_public_name' => ['label' => $module->l('Attribute Public Name (x,y,z...)', 'EIHelper') . ' *'],
                'attribute' => ['label' => $module->l('Value:Color / Value (x,y,z...)', 'EIHelper') . ' *'],
                'supplier_reference' => ['label' => $module->l('Supplier Reference', 'EIHelper')],
                'location' => ['label' => $module->l('Location', 'EIHelper')],
                'ean13' => ['label' => $module->l('EAN13', 'EIHelper')],
                'upc' => ['label' => $module->l('UPC', 'EIHelper')],
                'isbn' => ['label' => $module->l('ISBN', 'EIHelper')],
                'mpn' => ['label' => $module->l('MPN', 'EIHelper')],
                'wholesale_price' => ['label' => $module->l('Cost (Wholesale) Price', 'EIHelper')],
                'price' => ['label' => $module->l('Impact on Price', 'EIHelper')],
                'unit_price_impact' => ['label' => $module->l('Impact on Unit Price', 'EIHelper')],
                'ecotax' => ['label' => $module->l('Ecotax', 'EIHelper')],
                'quantity' => ['label' => $module->l('Quantity', 'EIHelper')],
                'minimal_quantity' => ['label' => $module->l('Minimal Quantity', 'EIHelper')],
                'low_stock_threshold' => ['label' => $module->l('Low Stock Level', 'EIHelper')],
                'low_stock_alert' => ['label' => $module->l('Low Stock Alert (0 = No, 1 = Yes)', 'EIHelper')],
                'weight' => ['label' => $module->l('Impact on Weight', 'EIHelper')],
                'default_on' => ['label' => $module->l('Is Default (0 = No, 1 = Yes)', 'EIHelper')],
                'available_date' => ['label' => $module->l('Availability Date', 'EIHelper')],
                'image_url' => ['label' => $module->l('Image URLs (x,y,z...)', 'EIHelper')],
//                'image_position' => ['label' => $module->l('Image Positions (x,y,z...)', 'EIHelper')],
                'image_alt' => ['label' => $module->l('Image Alt Texts (x,y,z...)', 'EIHelper')],
//                    'shop' => [
//                        'label' => $module->l('ID / Name of shop', 'EIHelper'),
//                        'help' => $module->l('Ignore this field if you don\'t use the Multistore tool. If you leave this field empty, the default shop will be used.', 'EIHelper'),
//                    ],
                'out_of_stock' => ['label' => $module->l('Action When out of Stock', 'EIHelper')],
//                'depends_on_stock' => [
//                    'label' => $module->l('Depends on Stock', 'EIHelper'),
//                    'help' => $module->l('0 = Use quantity set in product, 1 = Use quantity from warehouse.', 'EIHelper'),
//                ],
//                    'advanced_stock_management' => [
//                        'label' => $module->l('Advanced Stock Management', 'EIHelper'),
//                        'help' => $module->l('Enable Advanced Stock Management on product (0 = No, 1 = Yes)', 'EIHelper'),
//                    ],
                'warehouse' => ['label' => $module->l('Warehouses (Reference:Name:Location) (x,y,z...)', 'EIHelper')],
            ],
            'features' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id' => ['label' => $module->l('Feature ID', 'EIHelper')],
                'name' => ['label' => $module->l('Feature Name', 'EIHelper')],
                'position' => ['label' => $module->l('Feature Position', 'EIHelper')],
                'feature_value_id' => ['label' => $module->l('Feature Value ID', 'EIHelper')],
                'feature_value' => ['label' => $module->l('Feature Value', 'EIHelper')],
                'feature_value_custom' => ['label' => $module->l('Feature Value Is Custom', 'EIHelper')],
            ],
            'attributes' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id' => ['label' => $module->l('Attribute ID', 'EIHelper')],
                'name' => ['label' => $module->l('Attribute Name', 'EIHelper')],
                'public_name' => ['label' => $module->l('Attribute Public Name', 'EIHelper')],
                'group_type' => ['label' => $module->l('Attribute Group Type', 'EIHelper')],
                'position' => ['label' => $module->l('Attribute Position', 'EIHelper')],
                'attribute_id' => ['label' => $module->l('Attribute Value ID', 'EIHelper')],
                'attribute_name' => ['label' => $module->l('Attribute Value', 'EIHelper')],
                'attribute_color' => ['label' => $module->l('Attribute Value Color', 'EIHelper')],
                'attribute_position' => ['label' => $module->l('Attribute Value Position', 'EIHelper')],
            ],
            'packs' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id' => ['label' => $module->l('Pack ID', 'EIHelper')],
                'name' => ['label' => $module->l('Pack Name', 'EIHelper')],
                'reference' => ['label' => $module->l('Pack Reference', 'EIHelper')],
                'id_product' => ['label' => $module->l('Product ID', 'EIHelper')],
                'product_name' => ['label' => $module->l('Product Name', 'EIHelper')],
                'product_reference' => ['label' => $module->l('Product Reference', 'EIHelper')],
                'id_product_attribute' => ['label' => $module->l('Combination ID', 'EIHelper')],
                'product_attribute_reference' => ['label' => $module->l('Combination Reference', 'EIHelper')],
                'attribute' => ['label' => $module->l('Attributes (x,y,z...)', 'EIHelper')],
                'quantity' => ['label' => $module->l('Quantity', 'EIHelper')],
            ],
            'discounts' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id_specific_price' => ['label' => $module->l('Specific Price ID', 'EIHelper')],
                'id_specific_price_rule' => ['label' => $module->l('Catalog Price Rule ID', 'EIHelper')],
                'name' => ['label' => $module->l('Catalog Price Rule Name', 'EIHelper')],
                'conds' => ['label' => $module->l('Catalog Price Rule Conditions', 'EIHelper')],
                'id_product' => ['label' => $module->l('Product ID', 'EIHelper')],
                'product_reference' => ['label' => $module->l('Product Reference', 'EIHelper')],
                'product_name' => ['label' => $module->l('Product Name', 'EIHelper')],
                'id_product_attribute' => ['label' => $module->l('Combination ID', 'EIHelper')],
                'product_attribute_reference' => ['label' => $module->l('Combination Reference', 'EIHelper')],
                'attribute' => ['label' => $module->l('Combination Attributes', 'EIHelper')],
                'reduction' => ['label' => $module->l('Reduction', 'EIHelper')],
                'reduction_type' => ['label' => $module->l('Reduction Type', 'EIHelper')],
                'reduction_tax' => ['label' => $module->l('Reduction Tax (0 = No, 1 = Yes)', 'EIHelper')],
                'from' => ['label' => $module->l('From Date', 'EIHelper')],
                'to' => ['label' => $module->l('To Date', 'EIHelper')],
                'price' => ['label' => $module->l('Price', 'EIHelper')],
                'from_quantity' => ['label' => $module->l('From Quantity', 'EIHelper')],
                'id_customer' => ['label' => $module->l('Customer Email', 'EIHelper')],
                'id_group' => ['label' => $module->l('Group Name', 'EIHelper')],
                'id_country' => ['label' => $module->l('Country Name', 'EIHelper')],
                'id_currency' => ['label' => $module->l('Currency ISO Code', 'EIHelper')],
                'is_rule' => ['label' => $module->l('Is Rule', 'EIHelper')],
            ],
            'carriers' => array(
                'no' => array('label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --', 'EIHelper'),
                'id' => array('label' => $module->l('Carrier ID', 'EIHelper')),
                'id_reference' => array('label' => $module->l('Reference ID', 'EIHelper')),
                'active' => array('label' => $module->l('Active (0 = No, 1 = Yes)', 'EIHelper')),
                'name' => array('label' => $module->l('Name', 'EIHelper') . ' *', 'EIHelper'),
                'delay' => array('label' => $module->l('Transit Time', 'EIHelper') . ' *', 'EIHelper'),
                'id_tax_rules_group' => array('label' => $module->l('Tax Rule Name', 'EIHelper')),
                'url' => array('label' => $module->l('Tracking URL', 'EIHelper')),
                'logo_url' => array('label' => $module->l('Logo URL', 'EIHelper')),
                'shipping_handling' => array('label' => $module->l('Shipping Handling (0 = No, 1 = Yes)', 'EIHelper')),
                'range_behavior' => array('label' => $module->l('Range Behavior (0/1)', 'EIHelper')),
                'is_module' => array('label' => $module->l('Is Module (0 = No, 1 = Yes)', 'EIHelper')),
                'is_free' => array('label' => $module->l('Is Free (0 = No, 1 = Yes)', 'EIHelper')),
                'shipping_external' => array('label' => $module->l('External Shipping (0 = No, 1 = Yes)', 'EIHelper')),
                'external_module_name' => array('label' => $module->l('External Module Name', 'EIHelper')),
                'shipping_method' => array('label' => $module->l('Shipping Method (0/1/2)', 'EIHelper')),
                'position' => array('label' => $module->l('Position', 'EIHelper')),
                'max_width' => array('label' => $module->l('Max Width', 'EIHelper')),
                'max_height' => array('label' => $module->l('Max Height', 'EIHelper')),
                'max_depth' => array('label' => $module->l('Max Depth', 'EIHelper')),
                'max_weight' => array('label' => $module->l('Max Weight', 'EIHelper')),
                'grade' => array('label' => $module->l('Speed Grade', 'EIHelper')),
                'groups' => ['label' => $module->l('Group Names (x,y,z...)', 'EIHelper')],
                'zones' => ['label' => $module->l('Zone Names (Name:Active) (x,y,z...)', 'EIHelper')],
                'price_range' => ['label' => $module->l('Price Ranges (Min - Max) (x,y,z...)', 'EIHelper')],
                'price_range_price' => ['label' => $module->l('Zone Prices by Total Price (x,y,z...)', 'EIHelper')],
                'weight_range' => ['label' => $module->l('Weight Ranges (Min - Max) (x,y,z...)', 'EIHelper')],
                'weight_range_price' => ['label' => $module->l('Zone Prices by Total Weight (x,y,z...)', 'EIHelper')],
            ),
            'brands' => array(
                'no' => array('label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --', 'EIHelper'),
                'id' => array('label' => $module->l('Brand ID', 'EIHelper')),
                'active' => array('label' => $module->l('Active (0 = No, 1 = Yes)', 'EIHelper')),
                'name' => array('label' => $module->l('Name', 'EIHelper') . ' *', 'EIHelper'),
                'description' => array('label' => $module->l('Description', 'EIHelper')),
                'short_description' => array('label' => $module->l('Short Description', 'EIHelper')),
                'meta_title' => array('label' => $module->l('Meta Title', 'EIHelper')),
                'meta_keywords' => array('label' => $module->l('Meta Keywords', 'EIHelper')),
                'meta_description' => array('label' => $module->l('Meta Description', 'EIHelper')),
                'image' => array('label' => $module->l('Image URL', 'EIHelper')),
                'date_add' => array('label' => $module->l('Creation Date', 'EIHelper')),
            ),
            'suppliers' => array(
                'no' => array('label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --', 'EIHelper'),
                'id' => array('label' => $module->l('Supplier ID', 'EIHelper')),
                'active' => array('label' => $module->l('Active (0 = No, 1 = Yes)', 'EIHelper')),
                'name' => array('label' => $module->l('Name', 'EIHelper') . ' *', 'EIHelper'),
                'description' => array('label' => $module->l('Description', 'EIHelper')),
                'meta_title' => array('label' => $module->l('Meta Title', 'EIHelper')),
                'meta_keywords' => array('label' => $module->l('Meta Keywords', 'EIHelper')),
                'meta_description' => array('label' => $module->l('Meta Description', 'EIHelper')),
                'image' => array('label' => $module->l('Image URL', 'EIHelper')),
                'date_add' => array('label' => $module->l('Creation Date', 'EIHelper')),
            ),
            'customers' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id' => ['label' => $module->l('Customer ID', 'EIHelper')],
                'active' => ['label' => $module->l('Active (0 = No, 1 = Yes)', 'EIHelper')],
                'id_gender' => ['label' => $module->l('Title ID (1 = Mr, 2 = Mrs, 0 = else)', 'EIHelper')],
                'email' => ['label' => $module->l('Email', 'EIHelper') . ' *'],
                'passwd' => ['label' => $module->l('Current Password (Encrypted)', 'EIHelper') . ' **'],
                'new_passwd' => ['label' => $module->l('New Password', 'EIHelper') . ' **'],
                'firstname' => ['label' => $module->l('First Name', 'EIHelper') . ' *'],
                'lastname' => ['label' => $module->l('Last Name', 'EIHelper') . ' *'],
                'birthday' => ['label' => $module->l('Birthday', 'EIHelper')],
                'date_add' => ['label' => $module->l('Registration Date', 'EIHelper')],
                'is_guest' => ['label' => $module->l('Is Guest (0 = No, 1 = Yes)', 'EIHelper')],
                'id_lang' => ['label' => $module->l('Customer Language (ISO)', 'EIHelper')],
                'newsletter' => ['label' => $module->l('Newsletter (0 = No, 1 = Yes)', 'EIHelper')],
                'newsletter_date_add' => ['label' => $module->l('Newsletter Subscription Date', 'EIHelper')],
                'optin' => ['label' => $module->l('Opt-in (0 = No, 1 = Yes)', 'EIHelper')],
                'note' => ['label' => $module->l('Private Note', 'EIHelper')],
                'id_default_group' => ['label' => $module->l('Default Group Name', 'EIHelper')],
                'groups' => ['label' => $module->l('Group Names (x,y,z...)', 'EIHelper')],
                'company' => ['label' => $module->l('Company')],
                'siret' => ['label' => $module->l('SIRET')],
                'ape' => ['label' => $module->l('APE')],
                'website' => ['label' => $module->l('Website')],
                'outstanding_allow_amount' => ['label' => $module->l('Allowed Outstanding Amount')],
                'max_payment_days' => ['label' => $module->l('Maximum Number of Payment Days')],
                'id_risk' => ['label' => $module->l('Risk ID')],
            ],
            'groups' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id' => ['label' => $module->l('Group ID', 'EIHelper')],
                'name' => ['label' => $module->l('Name', 'EIHelper') . ' *'],
                'reduction' => ['label' => $module->l('Reduction', 'EIHelper')],
                'price_display_method' => ['label' => $module->l('Price Display Method (0 = Tax Included, 1 = Tax Excluded)', 'EIHelper')],
                'show_prices' => ['label' => $module->l('Show Prices (0 = No, 1 = Yes)', 'EIHelper')],
                'date_add' => ['label' => $module->l('Creation Date', 'EIHelper')],
            ],
            'addresses' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id' => ['label' => $module->l('Address ID', 'EIHelper')],
                'active' => ['label' => $module->l('Active (0 = No, 1 = Yes)', 'EIHelper')],
                'address1' => ['label' => $module->l('Address', 'EIHelper') . ' *'],
                'address2' => ['label' => $module->l('Address 2', 'EIHelper')],
                'alias' => ['label' => $module->l('Alias', 'EIHelper') . ' *'],
                'company' => ['label' => $module->l('Company', 'EIHelper')],
                'firstname' => ['label' => $module->l('First Name', 'EIHelper') . ' *'],
                'lastname' => ['label' => $module->l('Last Name', 'EIHelper') . ' *'],
                'customer_email' => ['label' => $module->l('Customer Email', 'EIHelper') . ' *'],
                'manufacturer' => ['label' => $module->l('Brand Name', 'EIHelper')],
                'supplier' => ['label' => $module->l('Supplier Name', 'EIHelper')],
                'postcode' => ['label' => $module->l('Zip/Postal code', 'EIHelper') . ' *'],
                'city' => ['label' => $module->l('City', 'EIHelper') . ' *'],
                'country' => ['label' => $module->l('Country Name', 'EIHelper') . ' *'],
                'state' => ['label' => $module->l('State Name', 'EIHelper')],
                'other' => ['label' => $module->l('Other', 'EIHelper')],
                'phone' => ['label' => $module->l('Phone', 'EIHelper')],
                'phone_mobile' => ['label' => $module->l('Mobile Phone', 'EIHelper')],
                'vat_number' => ['label' => $module->l('VAT Number', 'EIHelper')],
                'dni' => ['label' => $module->l('DNI (Identification Number)', 'EIHelper')],
                'date_add' => ['label' => $module->l('Creation Date', 'EIHelper')],
            ],
            'aliases' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id' => ['label' => $module->l('Alias ID', 'EIHelper')],
                'active' => ['label' => $module->l('Active (0 = No, 1 = Yes)', 'EIHelper')],
                'alias' => ['label' => $module->l('Alias', 'EIHelper') . ' *'],
                'search' => ['label' => $module->l('Search', 'EIHelper') . ' *'],
            ],
            'stores' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id' => ['label' => $module->l('Store ID', 'EIHelper')],
                'active' => ['label' => $module->l('Active (0 = No, 1 = Yes)', 'EIHelper')],
                'name' => ['label' => $module->l('Name', 'EIHelper')],
                'address1' => ['label' => $module->l('Address', 'EIHelper') . ' *'],
                'address2' => ['label' => $module->l('Address 2', 'EIHelper')],
                'city' => ['label' => $module->l('City', 'EIHelper') . ' *'],
                'country' => ['label' => $module->l('Country Name', 'EIHelper') . ' *'],
                'state' => ['label' => $module->l('State Name', 'EIHelper')],
                'latitude' => ['label' => $module->l('Latitude', 'EIHelper') . ' *'],
                'longitude' => ['label' => $module->l('Longitude', 'EIHelper') . ' *'],
                'postcode' => ['label' => $module->l('Zip/Postal Code', 'EIHelper')],
                'phone' => ['label' => $module->l('Phone', 'EIHelper')],
                'fax' => ['label' => $module->l('Fax', 'EIHelper')],
                'email' => ['label' => $module->l('Email', 'EIHelper')],
                'note' => ['label' => $module->l('Note', 'EIHelper')],
                'hours' => ['label' => $module->l('Hours', 'EIHelper')],
                'image' => ['label' => $module->l('Image URL', 'EIHelper')],
                'date_add' => ['label' => $module->l('Creation Date', 'EIHelper')],
//                    'shop' => [
//                        'label' => $module->l('ID / Name of shop', 'EIHelper'),
//                        'help' => $module->l('Ignore this field if you don\'t use the Multistore tool. If you leave this field empty, the default shop will be used.', 'EIHelper'),
//                    ],
            ],
            'warehouses' => [
                'no' => ['label' => '-- ' . $module->l('Ignore this column', 'EIHelper') . ' --'],
                'id' => ['label' => $module->l('Warehouse ID', 'EIHelper')],
                'reference' => ['label' => $module->l('Reference', 'EIHelper') . ' *'],
                'name' => ['label' => $module->l('Name', 'EIHelper') . ' *'],
                'management_type' => ['label' => $module->l('Management Type', 'EIHelper')],
                'id_currency' => ['label' => $module->l('Currency ISO', 'EIHelper') . ' *'],
                'phone' => ['label' => $module->l('Phone', 'EIHelper')],
                'phone_mobile' => ['label' => $module->l('Mobile Phone', 'EIHelper')],
                'address1' => ['label' => $module->l('Address', 'EIHelper') . ' *'],
                'address2' => ['label' => $module->l('Address 2', 'EIHelper')],
                'postcode' => ['label' => $module->l('ZIP/Postal Code', 'EIHelper')],
                'city' => ['label' => $module->l('City', 'EIHelper') . ' *'],
                'country' => ['label' => $module->l('Country Name', 'EIHelper') . ' *'],
                'state' => ['label' => $module->l('State Name', 'EIHelper')],
                'id_employee' => ['label' => $module->l('Manager Email', 'EIHelper') . ' *'],
                'carriers' => ['label' => $module->l('Carrier Names (x,y,z...)', 'EIHelper')],
            ],
        ];
        
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            unset($this->fields['products']['advanced_stock_management']);
            unset($this->fields['products']['depends_on_stock']);
            unset($this->fields['combinations']['depends_on_stock']);
        }
        
        if (version_compare(_PS_VERSION_, 8, '>=')) {
            unset($this->fields['carriers']['id_tax_rules_group']);
        }

        switch ($this->entity) {
            case 'categories':
                $this->available_fields = $this->fields['categories'];

                break;
            case 'products':
                $this->available_fields = $this->fields['products'];

                break;
            case 'combinations':
                $this->required_fields = [
                    'group_name',
                    'attribute',
                ];

                $this->available_fields = $this->fields['combinations'];

                break;
            case 'packs':
                $this->required_fields = [
                    'product_name',
                    'quantity'
                ];

                $this->available_fields = $this->fields['packs'];

                break;
            case 'features':
                $this->available_fields = $this->fields['features'];
                $this->required_fields = [
                    'name',
                    'feature_value'
                ];
                break;
            case 'attributes':
                $this->available_fields = $this->fields['attributes'];
                $this->required_fields = [
                    'name',
                    'attribute_name'
                ];
                break;
            case 'discounts':
                $this->available_fields = $this->fields['discounts'];

                break;
            case 'carriers':
                $this->required_fields = ['name', 'delay'];

                $this->available_fields = $this->fields['carriers'];

                break;
            case 'brands':
                $this->required_fields = array('name');

                $this->available_fields = $this->fields['brands'];

                break;
            case 'suppliers':
                $this->required_fields = array('name');

                $this->available_fields = $this->fields['suppliers'];

                break;
            case 'customers':
                //Overwrite required_fields AS only email is required whereas other entities
                $this->required_fields = ['email', 'passwd', 'lastname', 'firstname'];

                $this->available_fields = $this->fields['customers'];

                break;
            case 'warehouses':
                //Overwrite required_fields AS only email is required whereas other entities
                $this->required_fields = [
                    'reference',
                    'name',
                    'address1',
                    'city',
                    'country',
                    'id_employee',
                    'management_type',
                    'id_currency',
                ];

                $this->available_fields = $this->fields['warehouses'];

                break;
            case 'groups':
                //Overwrite required_fields AS only email is required whereas other entities
                $this->required_fields = ['name'];

                $this->available_fields = $this->fields['groups'];

                break;
            case 'addresses':
                //Overwrite required_fields
                $this->required_fields = [
                    'alias',
                    'lastname',
                    'firstname',
                    'address1',
                    'postcode',
                    'country',
                    'customer_email',
                    'city',
                ];

                $this->available_fields = $this->fields['addresses'];

                break;
            case 'aliases':
                //Overwrite required_fields
                $this->required_fields = [
                    'alias',
                    'search',
                ];

                $this->available_fields = $this->fields['aliases'];

                break;
            case 'stores':
                $this->required_fields = [
                    'address1',
                    'city',
                    'country',
                    'latitude',
                    'longitude',
                ];

                $this->available_fields = $this->fields['stores'];

                break;
        }
    }

    public static $formatArray = array(
        'Y-m-d' => '%Y-%m-%d',
        'd/m/Y' => '%d/%m/%Y',
        'Y/m/d' => '%Y/%m/%d',
        'm/d/e' => '%m/%d/%e',
        'd.m.Y' => '%d.%m.%Y',
        'e/c/Y' => '%e/%c/%Y',
        'c/e/Y' => '%c/%e/%Y',
        'e.c.Y' => '%e.%c.%Y',
        'e/c/y' => '%e/%c/%y',
        'c/e/Y' => '%c/%e/%Y',
        'e.c.y' => '%e.%c.%y',
        'd b Y' => '%d %b %Y',
        'e b Y' => '%e %b %Y',
        'e b y' => '%e %b %y',
        'd M Y' => '%d %M %Y',
        'e M Y' => '%e %M %Y',
        'e M y' => '%e %M %y',
        'Ymd' => '%Y%m%d',
        'H:i:s' => ' %H:%i:%s',
        'k:i:s' => ' %k:%i:%s',
        'h:i:s p' => ' %h:%i:%s %p',
        'l:i:s p' => ' %l:%i:%s %p',
        'His' => ' %H%i%s',
        'no_time' => '',
    );
    private static $operators = array(
        'gt' => '>',
        'lt' => '<',
        'eq' => '=',
    );
    public static $mimeTypes = array(
        'ez' => 'application/andrew-inset',
        'hqx' => 'application/mac-binhex40',
        'cpt' => 'application/mac-compactpro',
        'doc' => 'application/msword',
        'oda' => 'application/oda',
        'pdf' => 'application/pdf',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        'smi' => 'application/smil',
        'smil' => 'application/smil',
        'wbxml' => 'application/vnd.wap.wbxml',
        'wmlc' => 'application/vnd.wap.wmlc',
        'wmlsc' => 'application/vnd.wap.wmlscriptc',
        'bcpio' => 'application/x-bcpio',
        'vcd' => 'application/x-cdlink',
        'pgn' => 'application/x-chess-pgn',
        'cpio' => 'application/x-cpio',
        'csh' => 'application/x-csh',
        'dcr' => 'application/x-director',
        'dir' => 'application/x-director',
        'dxr' => 'application/x-director',
        'dvi' => 'application/x-dvi',
        'spl' => 'application/x-futuresplash',
        'gtar' => 'application/x-gtar',
        'hdf' => 'application/x-hdf',
        'js' => 'application/x-javascript',
        'skp' => 'application/x-koan',
        'skd' => 'application/x-koan',
        'skt' => 'application/x-koan',
        'skm' => 'application/x-koan',
        'latex' => 'application/x-latex',
        'nc' => 'application/x-netcdf',
        'cdf' => 'application/x-netcdf',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'swf' => 'application/x-shockwave-flash',
        'sit' => 'application/x-stuffit',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'tar' => 'application/x-tar',
        'tcl' => 'application/x-tcl',
        'tex' => 'application/x-tex',
        'texinfo' => 'application/x-texinfo',
        'texi' => 'application/x-texinfo',
        't' => 'application/x-troff',
        'tr' => 'application/x-troff',
        'roff' => 'application/x-troff',
        'man' => 'application/x-troff-man',
        'me' => 'application/x-troff-me',
        'ms' => 'application/x-troff-ms',
        'ustar' => 'application/x-ustar',
        'src' => 'application/x-wais-source',
        'xhtml' => 'application/xhtml+xml',
        'xht' => 'application/xhtml+xml',
        'zip' => 'application/zip',
        'au' => 'audio/basic',
        'snd' => 'audio/basic',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'kar' => 'audio/midi',
        'mpga' => 'audio/mpeg',
        'mp2' => 'audio/mpeg',
        'mp3' => 'audio/mpeg',
        'aif' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'm3u' => 'audio/x-mpegurl',
        'ram' => 'audio/x-pn-realaudio',
        'rm' => 'audio/x-pn-realaudio',
        'rpm' => 'audio/x-pn-realaudio-plugin',
        'ra' => 'audio/x-realaudio',
        'wav' => 'audio/x-wav',
        'pdb' => 'chemical/x-pdb',
        'xyz' => 'chemical/x-xyz',
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'ief' => 'image/ief',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'png' => 'image/png',
        'tiff' => 'image/tiff',
        'tif' => 'image/tif',
        'djvu' => 'image/vnd.djvu',
        'djv' => 'image/vnd.djvu',
        'wbmp' => 'image/vnd.wap.wbmp',
        'ras' => 'image/x-cmu-raster',
        'pnm' => 'image/x-portable-anymap',
        'pbm' => 'image/x-portable-bitmap',
        'pgm' => 'image/x-portable-graymap',
        'ppm' => 'image/x-portable-pixmap',
        'rgb' => 'image/x-rgb',
        'xbm' => 'image/x-xbitmap',
        'xpm' => 'image/x-xpixmap',
        'xwd' => 'image/x-windowdump',
        'igs' => 'model/iges',
        'iges' => 'model/iges',
        'msh' => 'model/mesh',
        'mesh' => 'model/mesh',
        'silo' => 'model/mesh',
        'wrl' => 'model/vrml',
        'vrml' => 'model/vrml',
        'css' => 'text/css',
        'html' => 'text/html',
        'htm' => 'text/html',
        'asc' => 'text/plain',
        'txt' => 'text/plain',
        'rtx' => 'text/richtext',
        'rtf' => 'text/rtf',
        'sgml' => 'text/sgml',
        'sgm' => 'text/sgml',
        'tsv' => 'text/tab-seperated-values',
        'wml' => 'text/vnd.wap.wml',
        'wmls' => 'text/vnd.wap.wmlscript',
        'etx' => 'text/x-setext',
        'xml' => 'text/xml',
        'xsl' => 'text/xml',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'mxu' => 'video/vnd.mpegurl',
        'avi' => 'video/x-msvideo',
        'movie' => 'video/x-sgi-movie',
        'ice' => 'x-conference-xcooltalk');

    public static function allEntities($module)
    {
        return array(
            'product' => array(
                'pEntity' => 'products',
                'plural' => $module->l('Products', 'EIHelper'),
                'class' => 'EIProductsExport',
                'position' => 10,
            ),
            'feature' => array(
                'pEntity' => 'features',
                'plural' => $module->l('Features', 'EIHelper'),
                'class' => 'EIFeaturesExport',
                'position' => 8,
            ),
            'attribute' => array(
                'pEntity' => 'attributes',
                'plural' => $module->l('Attributes', 'EIHelper'),
                'class' => 'EIAttributesExport',
                'position' => 9,
            ),
            'combination' => array(
                'pEntity' => 'combinations',
                'plural' => $module->l('Combinations', 'EIHelper'),
                'class' => 'EICombinationsExport',
                'position' => 11,
            ),
            'pack' => array(
                'pEntity' => 'packs',
                'plural' => $module->l('Packs', 'EIHelper'),
                'class' => 'EIPacksExport',
                'position' => 12,
            ),
            'discount' => array(
                'pEntity' => 'discounts',
                'plural' => $module->l('Discounts', 'EIHelper'),
                'class' => 'EIDiscountsExport',
                'position' => 13,
            ),
            'category' => array(
                'pEntity' => 'categories',
                'plural' => $module->l('Categories', 'EIHelper'),
                'class' => 'EICategoriesExport',
                'position' => 7,
            ),
            'brand' => array(
                'pEntity' => 'brands',
                'plural' => $module->l('Brands', 'EIHelper'),
                'class' => 'EIBrandsExport',
                'position' => 0,
            ),
            'supplier' => array(
                'pEntity' => 'suppliers',
                'plural' => $module->l('Suppliers', 'EIHelper'),
                'class' => 'EISuppliersExport',
                'position' => 1,
            ),
            'carrier' => array(
                'pEntity' => 'carriers',
                'plural' => $module->l('Carriers', 'EIHelper'),
                'class' => 'EICarriersExport',
                'position' => 5,
            ),
            'group' => array(
                'pEntity' => 'groups',
                'plural' => $module->l('Groups', 'EIHelper'),
                'class' => 'EIGroupsExport',
                'position' => 2,
            ),
            'customer' => array(
                'pEntity' => 'customers',
                'plural' => $module->l('Customers', 'EIHelper'),
                'class' => 'EICustomersExport',
                'position' => 3,
            ),
            'address' => array(
                'pEntity' => 'addresses',
                'plural' => $module->l('Addresses', 'EIHelper'),
                'class' => 'EIAddressesExport',
                'position' => 4,
            ),
            'store' => array(
                'pEntity' => 'stores',
                'plural' => $module->l('Stores', 'EIHelper'),
                'class' => 'EIStoresExport',
                'position' => 14,
            ),
            'alias' => array(
                'pEntity' => 'aliases',
                'plural' => $module->l('Aliases', 'EIHelper'),
                'class' => 'EIAliasesExport',
                'position' => 15,
            ),
            'warehouse' => array(
                'pEntity' => 'warehouses',
                'plural' => $module->l('Warehouses', 'EIHelper'),
                'class' => 'EIWarehousesExport',
                'position' => 6,
            ),
        );
    }
    
    public static function allEntitiesUnordered($module)
    {
        return array(
            'products' => array(
                'plural' => $module->l('Products', 'EIHelper'),
                'class' => 'EIProductsExport'
            ),
            'features' => array(
                'plural' => $module->l('Features', 'EIHelper'),
                'class' => 'EIFeaturesExport'
            ),
            'attributes' => array(
                'plural' => $module->l('Attributes', 'EIHelper'),
                'class' => 'EIAttributesExport'
            ),
            'combinations' => array(
                'plural' => $module->l('Combinations', 'EIHelper'),
                'class' => 'EICombinationsExport'
            ),
            'packs' => array(
                'plural' => $module->l('Packs', 'EIHelper'),
                'class' => 'EIPacksExport'
            ),
            'discounts' => array(
                'plural' => $module->l('Discounts', 'EIHelper'),
                'class' => 'EIDiscountsExport'
            ),
            'categories' => array(
                'plural' => $module->l('Categories', 'EIHelper'),
                'class' => 'EICategoriesExport'
            ),
            'brands' => array(
                'plural' => $module->l('Brands', 'EIHelper'),
                'class' => 'EIBrandsExport'
            ),
            'suppliers' => array(
                'plural' => $module->l('Suppliers', 'EIHelper'),
                'class' => 'EISuppliersExport'
            ),
            'carriers' => array(
                'plural' => $module->l('Carriers', 'EIHelper'),
                'class' => 'EICarriersExport'
            ),
            'groups' => array(
                'plural' => $module->l('Groups', 'EIHelper'),
                'class' => 'EIGroupsExport'
            ),
            'customers' => array(
                'plural' => $module->l('Customers', 'EIHelper'),
                'class' => 'EICustomersExport'
            ),
            'addresses' => array(
                'plural' => $module->l('Addresses', 'EIHelper'),
                'class' => 'EIAddressesExport'
            ),
            'stores' => array(
                'plural' => $module->l('Stores', 'EIHelper'),
                'class' => 'EIStoresExport'
            ),
            'aliases' => array(
                'plural' => $module->l('Aliases', 'EIHelper'),
                'class' => 'EIAliasesExport'
            ),
            'warehouses' => array(
                'plural' => $module->l('Warehouses', 'EIHelper'),
                'class' => 'EIWarehousesExport'
            ),
        );
    }

    public static function getOperator($op)
    {
        if (isset(static::$operators[$op])) {
            return static::$operators[$op];
        } else {
            throw new PrestaShopException('No such an operator!');
        }
    }

    public static function createColumnsArray($x)
    {
        $letters = range('A', 'Z');
        $arr = array();
        $i = 1;
        foreach ($letters as $val) {
            $arr[] = $val;
            if ($i === $x) {
                return $arr;
            }
            if ($i === 26) {
                break;
            }
            $i++;
        }

        foreach ($letters as $outerVal) {
            foreach ($letters as $innerVal) {
                $i++;
                $arr[] = $outerVal . $innerVal;
                if ($i === $x) {
                    return $arr;
                }
            }
        }
    }

    public static function getTypeFromMime($mime)
    {
        $types = array_flip(static::$mimeTypes);
        if (isset($types[$mime])) {
            return $types[$mime];
        } else {
            return false;
        }
    }

    public static function getMimeFromType($type)
    {
        if (isset(static::$mimeTypes[$type])) {
            return static::$mimeTypes[$type];
        } else {
            return false;
        }
    }
}
