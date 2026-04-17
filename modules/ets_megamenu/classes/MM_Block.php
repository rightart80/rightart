<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }
class MM_Block extends MM_Obj
{
    public $context;
    public $id_block;
    public $title;    
    public $title_link;
    public $content;
    public $enabled;
    public $sort_order;
    public $id_categories;
    public $order_by_category;
    public $id_manufacturers;
    public $order_by_manufacturers;
    public $display_mnu_img;
    public $display_mnu_name;
    public $display_mnu_inline;
    public $id_suppliers;
    public $order_by_suppliers;
    public $display_suppliers_img;
    public $display_suppliers_name;
    public $display_suppliers_inline;
    public $id_cmss;
    public $block_type;
    public $image;
    public $custom_class;
    public $display_title;
    public $customer_groups;
    public $display_content_mobile;
    public $id_column;
    public $image_link;
	public $product_type;
	public $product_with_category;
    public $id_products;
    public $product_count;
    public $combination_enabled;
    public $show_description;
    public $show_clock;
    public $hidden_mobile;
    /**
     * @var array
     */
    public $fields_form = [];
    public static $definition = array(
		'table' => 'ets_mm_block',
		'primary' => 'id_block',
		'multilang' => true,
		'fields' => array(
			'sort_order' => array('type' => self::TYPE_INT),
            'id_column' => array('type' => self::TYPE_INT), 
            'id_categories' => array('type' => self::TYPE_STRING),  
            'order_by_category' => array('type' => self::TYPE_STRING), 
            'id_manufacturers' => array('type' => self::TYPE_STRING),
            'order_by_manufacturers' => array('type' => self::TYPE_STRING), 
            'display_mnu_img' => array('type' => self::TYPE_INT),
            'display_mnu_name' => array('type' => self::TYPE_INT),
            'display_mnu_inline' => array('type' => self::TYPE_STRING),
            'id_suppliers' => array('type' => self::TYPE_STRING),
            'order_by_suppliers' => array('type' => self::TYPE_STRING), 
            'display_suppliers_img' => array('type' => self::TYPE_INT),
            'display_suppliers_name' => array('type' => self::TYPE_INT),
            'display_suppliers_inline' => array('type' => self::TYPE_STRING),
            'id_cmss' => array('type' => self::TYPE_STRING),
			'product_type' => array('type' => self::TYPE_STRING),
            'id_products' => array('type' => self::TYPE_STRING),
			'product_count' => array('type' => self::TYPE_INT),
            'enabled' => array('type' => self::TYPE_INT),
            'block_type' => array('type' => self::TYPE_STRING),
            'product_with_category' => array('type' => self::TYPE_INT),
            'display_title' => array('type' => self::TYPE_INT),
            'customer_groups' => array('type' => self::TYPE_STRING),
            'display_content_mobile' => array('type' => self::TYPE_INT),
            'show_description' => array('type' => self::TYPE_INT),
            'show_clock' => array('type' => self::TYPE_INT),
            'hidden_mobile' => array('type' => self::TYPE_INT),
            'image' => array('type' => self::TYPE_STRING,'lang' => true),
            'title' => array('type' => self::TYPE_STRING, 'lang' => true),			
            'title_link' => array('type' => self::TYPE_STRING, 'lang' => true), 
            'image_link' => array('type' => self::TYPE_STRING, 'lang' => true),   
            'content' => array('type' => self::TYPE_HTML, 'lang' => true),                
        )
	);
    public function l($string, $fileName="")
    {
        return Translate::getModuleTranslation('ets_megamenu', $string, $fileName ?: pathinfo(__FILE__, PATHINFO_FILENAME));
    }
    protected static $formFields;
    public function getFormField()
    {
        /** @var Ets_megamenu $megamenu */
        $megamenu = Module::getInstanceByName('ets_megamenu');
        if(!self::$formFields)
            self::$formFields =  array(
            'form' => array(
                'legend' => array(
                    'title' => (int)$this->id ? $this->l('Edit block') : $this->l('Add block'),
                ),
                'input' => array(),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
                'name' => 'block',
                'parent' => 'column',
            ),
            'configs' => array(
                'block_type' => array(
                    'type' => 'select',
                    'label' => $this->l('Block type'),
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'HTML',
                                'name' => $this->l('Text/Html')
                            ),
                            array(
                                'id_option' => 'IMAGE',
                                'name' => $this->l('Image')
                            ),
                            array(
                                'id_option' => 'CATEGORY',
                                'name' => $this->l('Category')
                            ),
                            array(
                                'id_option' => 'CMS',
                                'name' => $this->l('CMS page')
                            ),
                            array(
                                'id_option' => 'MNFT',
                                'name' => $this->l('Manufacturer')
                            ),
                            array(
                                'id_option' => 'MNSP',
                                'name' => $this->l('Supplier')
                            ),
                            array(
                                'id_option' => 'PRODUCT',
                                'name' => $this->l('Products')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'default' => 'HTML',
                ),
                'title' => array(
                    'label' => $this->l('Title'),
                    'type' => 'text',
                    'required' => true,
                    'lang' => true,
                ),
                'id_column' => array(
                    'label' => $this->l('Column'),
                    'type' => 'hidden',
                    'default' => ($id_column = (int)Tools::isSubmit('id_column')) ? $id_column : 0,
                    'required' => true,
                ),
                'title_link' => array(
                    'label' => $this->l('Title link'),
                    'type' => 'text',
                    'lang' => true,
                    'desc' => $this->l('Leave blank if you do not want to add a link to block title'),
                ),
                'id_manufacturers' => array(
                    'label' => $this->l('Manufacturers'),
                    'type' => 'checkbox',
                    'values' => array(
                        'query' => $megamenu->getManufacturers(),
                        'id' => 'id',
                        'name' => 'label'
                    ),
                    'showRequired' => true,
                ),
                'order_by_manufacturers' => array(
                    'type' => 'select',
                    'label' => $this->l('Order by'),
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'name ASC,m.id_manufacturer ASC',
                                'name' => $this->l('Name A-Z')
                            ),
                            array(
                                'id_option' => 'name DESC,m.id_manufacturer ASC',
                                'name' => $this->l('Name Z-A')
                            ),
                            array(
                                'id_option' => 'm.id_manufacturer DESC',
                                'name' => $this->l('Newest manufacturer first')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'default' => 'nam ASC,m.id_manufacturer ASC',
                ),
                'display_mnu_img' => array(
                    'label' => $this->l('Display manufacturers image'),
                    'type' => 'switch',
                    'default' => 0,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'menu_mnu_img_1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'menu_mnu_img_0',
                            'value' => 0,
                        )
                    ),
                ),
                'display_mnu_name' => array(
                    'label' => $this->l('Display manufacturers name'),
                    'type' => 'switch',
                    'default' => 1,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'menu_mnu_name_1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'menu_mnu_name_0',
                            'value' => 0,
                        )
                    ),
                ),
                'display_mnu_inline' => array(
                    'label' => $this->l('Manufacturers per row'),
                    'type' => 'select',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => '1',
                                'name' => $this->l('1')
                            ),
                            array(
                                'id_option' => '2',
                                'name' => $this->l('2')
                            ),
                            array(
                                'id_option' => '3',
                                'name' => $this->l('3')
                            ),
                            array(
                                'id_option' => '4',
                                'name' => $this->l('4')
                            ),
                            array(
                                'id_option' => '5',
                                'name' => $this->l('5')
                            ),
                            array(
                                'id_option' => '6',
                                'name' => $this->l('6')
                            ),
                            array(
                                'id_option' => '7',
                                'name' => $this->l('7')
                            ),
                            array(
                                'id_option' => '8',
                                'name' => $this->l('8')
                            ),
                            array(
                                'id_option' => '9',
                                'name' => $this->l('9')
                            ), array(
                                'id_option' => '10',
                                'name' => $this->l('10')
                            ),
                            array(
                                'id_option' => '11',
                                'name' => $this->l('11')
                            ),
                            array(
                                'id_option' => '12',
                                'name' => $this->l('12')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                ),
                'id_suppliers' => array(
                    'label' => $this->l('Suppliers'),
                    'type' => 'checkbox',
                    'values' => array(
                        'query' => $megamenu->getSuppliers(),
                        'id' => 'id',
                        'name' => 'label'
                    ),
                    'showRequired' => true,
                ),
                'order_by_suppliers' => array(
                    'type' => 'select',
                    'label' => $this->l('Order by'),
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'name ASC,s.id_supplier ASC',
                                'name' => $this->l('Name A-Z')
                            ),
                            array(
                                'id_option' => 'name DESC,s.id_supplier ASC',
                                'name' => $this->l('Name Z-A')
                            ),
                            array(
                                'id_option' => 's.id_supplier DESC',
                                'name' => $this->l('Newest supplier first')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'default' => 'nam ASC,s.id_supplier ASC',
                ),
                'display_suppliers_img' => array(
                    'label' => $this->l('Display suppliers image'),
                    'type' => 'switch',
                    'default' => 0,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'menu_suppliers_img_1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'menu_suppliers_img_0',
                            'value' => 0,
                        )
                    ),
                ),
                'display_suppliers_name' => array(
                    'label' => $this->l('Display suppliers name'),
                    'type' => 'switch',
                    'default' => 1,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'menu_suppliers_name_1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'menu_suppliers_name_0',
                            'value' => 0,
                        )
                    ),
                ),
                'display_suppliers_inline' => array(
                    'label' => $this->l('Suppliers per row'),
                    'type' => 'select',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => '1',
                                'name' => $this->l('1')
                            ),
                            array(
                                'id_option' => '2',
                                'name' => $this->l('2')
                            ),
                            array(
                                'id_option' => '3',
                                'name' => $this->l('3')
                            ),
                            array(
                                'id_option' => '4',
                                'name' => $this->l('4')
                            ),
                            array(
                                'id_option' => '5',
                                'name' => $this->l('5')
                            ),
                            array(
                                'id_option' => '6',
                                'name' => $this->l('6')
                            ),
                            array(
                                'id_option' => '7',
                                'name' => $this->l('7')
                            ),
                            array(
                                'id_option' => '8',
                                'name' => $this->l('8')
                            ),
                            array(
                                'id_option' => '9',
                                'name' => $this->l('9')
                            ), array(
                                'id_option' => '10',
                                'name' => $this->l('10')
                            ),
                            array(
                                'id_option' => '11',
                                'name' => $this->l('11')
                            ),
                            array(
                                'id_option' => '12',
                                'name' => $this->l('12')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                ),
                'product_type' => array(
                    'label' => $this->l('Product type'),
                    'type' => 'radios',
                    'default' => 'specific',
                    'values' => array(
                        array(
                            'label' => $this->l('New products'),
                            'value' => 'new',
                        ),
                        array(
                            'label' => $this->l('Popular products'),
                            'value' => 'popular',
                        ),
                        array(
                            'label' => $this->l('Special products'),
                            'value' => 'special',
                        ),
                        array(
                            'label' => $this->l('Best sellers'),
                            'value' => 'best',
                        ),
                        array(
                            'label' => $this->l('Specific products '),
                            'value' => 'specific',
                        ),
                    ),
                ),
                'product_with_category' => array(
                    'label' => $this->l('Choose products from category'),
                    'type' => 'switch',
                    'default' => 0,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'product_with_category_1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'product_with_category_0',
                            'value' => 0,
                        )
                    ),
                ),
                'id_categories' => array(
                    'type' => 'categories',
                    'label' => $this->l('Categories'),
                    'name' => 'id_parent',
                    'tree' => array(
                        'id' => 'categories-tree',
                        'selected_categories' => array(),
                        'disabled_categories' => array(),
                        'use_checkbox' => true,
                        'root_category' => (int)Category::getRootCategory()->id,
                    ),
                    'showRequired' => true,
                ),

                'order_by_category' => array(
                    'type' => 'select',
                    'label' => $this->l('Order by'),
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'cl.name ASC,c.id_category ASC',
                                'name' => $this->l('Name A-Z')
                            ),
                            array(
                                'id_option' => 'cl.name DESC,c.id_category ASC',
                                'name' => $this->l('Name Z-A')
                            ),
                            array(
                                'id_option' => 'c.position ASC,c.id_category ASC',
                                'name' => $this->l('Default order ')
                            ),
                            array(
                                'id_option' => 'c.id_category DESC',
                                'name' => $this->l('Newest category first')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'default' => 'cl.name ASC,c.id_category ASC',
                ),
                'id_cmss' => array(
                    'label' => $this->l('CMS pages'),
                    'type' => 'checkbox',
                    'values' => array(
                        'query' => $megamenu->getCMSs(),
                        'id' => 'id',
                        'name' => 'label'
                    ),
                    'showRequired' => true,
                ),
                'content' => array(
                    'label' => $this->l('HTML/Text content'),
                    'type' => 'textarea',
                    'lang' => true,
                    'showRequired' => true,
                ),
                'image' => array(
                    'label' => $this->l('Image'),
                    'type' => 'file_lang',
                    'hide_delete' => true,
                    'showRequired' => true,
                    'lang' => true,
                    'desc' => sprintf($this->l('Accepted formats: jpg, jpeg, png, gif. Limit: %sMb.'), Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'))
                ),
                'image_link' => array(
                    'label' => $this->l('Image link'),
                    'type' => 'text',
                    'lang' => true,
                    'desc' => $this->l('Leave blank if you do not want to add a link to image'),
                ),
                'id_products' => array(
                    'label' => $this->l('Search products'),
                    'type' => 'search',
                    'placeholder' => $this->l('Search product by ID, name or reference'),
                    'showRequired' => true,
                ),
                'product_count' => array(
                    'label' => $this->l('Product count'),
                    'type' => 'text',
                    'default' => '2',
                    'showRequired' => true,
                    'suffix' => $this->l('item(s)')
                ),
                'sort_order' => array(
                    'label' => $this->l('Sort order'),
                    'type' => 'sort_order',
                    'required' => true,
                    'default' => 1,
                    'order_group' => array(
                        'column' => 'id_column',
                    ),
                ),
                'show_description' => array(
                    'label' => $this->l('Enable product description'),
                    'type' => 'switch',
                    'default' => 0,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'show_description_1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'show_description_0',
                            'value' => 0,
                        )
                    ),
                ),
                'show_clock' => array(
                    'label' => $this->l('Enable count down clock'),
                    'type' => 'switch',
                    'default' => 0,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'show_clock_1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'show_clock_0',
                            'value' => 0,
                        )
                    ),
                ),
                'display_title' => array(
                    'label' => $this->l('Display title'),
                    'type' => 'switch',
                    'default' => 1,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'menu_enabled_1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'menu_enabled_0',
                            'value' => 0,
                        )
                    ),
                ),
                'hidden_mobile' => array(
                    'label' => $this->l('Hide block when display on mobile devices'),
                    'type' => 'switch',
                    'default' => 0,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'hidden_mobile_1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'hidden_mobile_0',
                            'value' => 0,
                        )
                    ),
                ),
                'customer_groups' => array(
                    'label' => $this->l('Apply for customer groups'),
                    'type' => 'checkbox',
                    'values' => array(
                        'query' => $megamenu->getCustomerGroups(),
                        'id' => 'id',
                        'name' => 'label'
                    ),
                    'showRequired' => false,
                    'desc' => $this->l('Leave empty to display this block for all customer groups'),
                    'form_group_class' => 'customer_groups',
                ),
                'enabled' => array(
                    'label' => $this->l('Enabled'),
                    'type' => 'switch',
                    'default' => 1,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'menu_enabled_1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'menu_enabled_0',
                            'value' => 0,
                        )
                    ),
                ),
            ),
        );
        return self::$formFields;
    }
    public static function getBlocks($id_lang, $id_column = false, $activeOnly = true, $id_block = false)
    {
        $blocks = Db::getInstance()->executeS("
            SELECT b.*,bl.title,bl.title_link,bl.content,bl.image_link,bl.image
            FROM `" . _DB_PREFIX_ . "ets_mm_block` b
            LEFT JOIN `" . _DB_PREFIX_ . "ets_mm_block_lang` bl
            ON b.id_block=bl.id_block AND bl.id_lang=" . (int)$id_lang . "
            WHERE 1 " . ($activeOnly ? "AND b.enabled=1 " : "") . ($id_column ? " AND b.id_column=" . (int)$id_column . " " : "") . ($id_block ? " AND b.id_block=" . (int)$id_block : "") . "
            ORDER BY b.sort_order asc
        ");
        return $id_block && $blocks ? $blocks[0] : $blocks;
    }
    public static function getBlockById($id_block, $id_lang)
    {
        return Db::getInstance()->getRow("
            SELECT b.*,bl.title,bl.title_link,bl.content,bl.image_link,bl.image
            FROM `" . _DB_PREFIX_ . "ets_mm_block` b
            LEFT JOIN `" . _DB_PREFIX_ . "ets_mm_block_lang` bl
            ON b.id_block=bl.id_block AND bl.id_lang=" . (int)$id_lang . "
            WHERE b.id_block=" . (int)$id_block . "
        ");
    }
    public function _clearCache()
    {
        /** @var Ets_megamenu $megamenu */
        $megamenu = Module::getInstanceByName('ets_megamenu');
        $cacheID = $megamenu->_getCacheId(array_merge(array('block'),str_split((string)$this->id)),false);
        if($cacheID)
            $megamenu->_clearCache('*',$cacheID);
    }
    public function update($null_value = false)
    {
        if(parent::update($null_value))
        {
            $this->_clearCache();
            return true;
        }
        return false;
    }
    public function delete()
    {
        if(parent::delete())
        {
            $this->_clearCache();
            return true;
        }
        return false;
    }
    public static function checkBlockClockCountDown()
    {
        return Db::getInstance()->getValue('SELECT id_block FROM `'._DB_PREFIX_.'ets_mm_block` WHERE enabled=1 AND show_clock=1 AND block_type="PRODUCT"');
    }
}
