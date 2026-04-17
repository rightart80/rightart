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
class MM_Menu extends MM_Obj
{
    public $id_menu;
    public $title;    
    public $link;
    public $enabled;
    public $menu_open_new_tab;
    public $menu_ver_hidden_border;
    public $menu_ver_alway_show;
    public $menu_ver_alway_open_first;
    public $sort_order;
    public $id_category;
    public $id_manufacturer;
    public $id_supplier;
    public $id_cms;
    public $link_type;
    public $sub_menu_type;
    public $sub_menu_max_width;
    public $custom_class;
    public $menu_icon;
    public $menu_img_link;
    public $bubble_text;
    public $bubble_text_color;
    public $bubble_background_color;
    public $menu_ver_text_color;
    public $menu_ver_background_color;
    public $enabled_vertical;
    public $menu_item_width;
    public $tab_item_width;
    public $background_image;
    public $position_background;
    public $display_tabs_in_full_width;
    /**
     * @var array
     */
    public $fields_form = [];
    public static $definition = array(
		'table' => 'ets_mm_menu',
		'primary' => 'id_menu',
		'multilang' => true,
		'fields' => array(
			'sort_order' => array('type' => self::TYPE_INT), 
            'id_category' => array('type' => self::TYPE_INT),   
            'id_manufacturer' => array('type' => self::TYPE_INT),
            'id_supplier'=>array('type'=>self::TYPE_INT),
            'id_cms' => array('type' => self::TYPE_INT),
            'sub_menu_type' => array('type' => self::TYPE_STRING),
            'link_type' => array('type' => self::TYPE_STRING),
            'sub_menu_max_width' => array('type' => self::TYPE_STRING),
            'custom_class' => array('type' => self::TYPE_STRING),
            'bubble_text_color' => array('type' => self::TYPE_STRING),
            'bubble_background_color' => array('type' => self::TYPE_STRING),
            'menu_ver_text_color' => array('type' => self::TYPE_STRING),
            'menu_item_width' => array('type' => self::TYPE_STRING),
            'tab_item_width'=> array('type'=>self::TYPE_STRING),
            'menu_ver_background_color' => array('type' => self::TYPE_STRING),
            'menu_ver_hidden_border'=>array('type'=>self::TYPE_INT),
            'menu_ver_alway_show'=>array('type'=>self::TYPE_INT),
            'menu_ver_alway_open_first'=>array('type'=>self::TYPE_INT),
            'enabled' => array('type' => self::TYPE_INT),
            'menu_open_new_tab' => array('type' => self::TYPE_INT),
            'menu_icon' => array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isCleanHtml'),
            'menu_img_link' => array('type' => self::TYPE_STRING, 'lang' => false),
            'enabled_vertical' => array('type'=>self::TYPE_INT),
            'background_image' => array('type' => self::TYPE_STRING),
            'position_background' => array('type' => self::TYPE_STRING),
            'display_tabs_in_full_width'=>   array('type' => self::TYPE_INT),
            'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true),			
            'link' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'),            
            'bubble_text' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'),           
        )
	);
    /**
     * @var array
     */
    public $image = [];

    public function l($string, $fileName="")
    {
        return Translate::getModuleTranslation('ets_megamenu', $string, $fileName ?: pathinfo(__FILE__, PATHINFO_FILENAME));
    }
    public function add($autodate = true, $null_values = false, $id_shop = null)
	{
		if(parent::add($autodate, $null_values))
        {
            if($id_shop)
            {
                 Db::getInstance()->execute('
                    INSERT INTO `'._DB_PREFIX_.'ets_mm_menu_shop` (`id_shop`, `id_menu`)
                    VALUES('.(int)$id_shop.', '.(int)$this->id.')'
                );
            }
            return true;
        }
		return false;
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
                    'title' => (int)$this->id ? $this->l('Edit menu') : $this->l('Add menu'),
                ),
                'input' => array(),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
                'name' => 'menu',
                'connect_to' => 'column',
                'connect_to2' => 'tab',
            ),
            'configs' => array(
                'enabled_vertical' => array(
                    'type' => 'select',
                    'label' => $this->l('Direction'),
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => '0',
                                'name' => $this->l('Horizontal')
                            ),
                            array(
                                'id_option' => '1',
                                'name' => $this->l('Vertical')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                ),
                'menu_ver_text_color' => array(
                    'label' => $this->l('Vertical menu text color'),
                    'type' => 'color',
                    'default' => '#ffffff',
                    'validate' => 'isColor',
                    'class' => 'color mColorPickerInput'
                ),
                'menu_ver_background_color' => array(
                    'label' => $this->l('Vertical menu background color'),
                    'type' => 'color',
                    'default' => '#666666',
                    'validate' => 'isColor',
                    'class' => 'color mColorPickerInput'
                ),
                'menu_ver_alway_show' => array(
                    'label' => $this->l('Always open vertical menu'),
                    'type' => 'switch',
                    'default' => 0,
                    'desc' => $this->l('Only apply for desktop'),
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'yes',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'no',
                            'value' => 0,
                        )
                    ),
                ),
                'menu_ver_alway_open_first' => array(
                    'label' => $this->l('Always open the content of the first tab on a vertical menu'),
                    'type' => 'switch',
                    'default' => 1,
                    'desc' => $this->l('The content of the first tab of a vertical menu will be displayed when a user hovers mouse pointer over the vertical menu\'s title. Only apply to desktop devices.'),
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'yes',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'no',
                            'value' => 0,
                        )
                    ),
                ),
                'menu_ver_hidden_border' => array(
                    'label' => $this->l('Remove border'),
                    'type' => 'switch',
                    'default' => 0,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'menu_ver_hidden_border1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'menu_ver_hidden_border0',
                            'value' => 0,
                        )
                    ),
                ),
                'menu_item_width' => array(
                    'label' => $this->l('Menu item width'),
                    'type' => 'text',
                    'default' => '230px',
                    'desc' => $this->l('Use "px" or "%" or "vw". Eg: "20%" or "230px" or "80vw"'),
                ),
                'tab_item_width' => array(
                    'label' => $this->l('Tab item width'),
                    'type' => 'text',
                    'default' => '230px',
                    'desc' => $this->l('Use "px" or "%" or "vw". Eg: "20%" or "230px" or "80vw"'),
                ),
                'link_type' => array(
                    'type' => 'select',
                    'label' => $this->l('Menu link type'),
                    'name' => 'menu_type',
                    'class' => 'ybc_menu_type',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'CUSTOM',
                                'name' => $this->l('Custom link')
                            ),
                            array(
                                'id_option' => 'CMS',
                                'name' => $this->l('CMS page')
                            ),
                            array(
                                'id_option' => 'CONTACT',
                                'name' => $this->l('Contact')
                            ),
                            array(
                                'id_option' => 'CATEGORY',
                                'name' => $this->l('Category')
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
                                'id_option' => 'HOME',
                                'name' => $this->l('Home')
                            )
                        ),
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'default' => 'CUSTOM',
                ),
                'title' => array(
                    'label' => $this->l('Title'),
                    'type' => 'text',
                    'required' => true,
                    'lang' => true,
                ),
                'link' => array(
                    'label' => $this->l('Custom link'),
                    'type' => 'text',
                    'lang' => true,
                    'showRequired' => true,
                    'validate'=> 'isCleanHtml',
                ),
                'id_manufacturer' => array(
                    'label' => $this->l('Manufacturer'),
                    'type' => 'radio',
                    'values' => $megamenu->getManufacturers(),
                    'showRequired' => true,
                ),
                'id_supplier' => array(
                    'label' => $this->l('Supplier'),
                    'type' => 'radio',
                    'values' => $megamenu->getSuppliers(),
                    'showRequired' => true,
                ),
                'id_category' => array(
                    'type' => 'categories',
                    'label' => $this->l('Category'),
                    'name' => 'id_parent',
                    'tree' => array(
                        'id' => 'categories-tree',
                        'selected_categories' => array(),
                        'disabled_categories' => array(),
                        'use_checkbox' => false,
                        'root_category' => (int)Category::getRootCategory()->id,
                    ),
                    'showRequired' => true,
                ),
                'id_cms' => array(
                    'label' => $this->l('CMS page'),
                    'type' => 'radio',
                    'values' => $megamenu->getCMSs(),
                    'showRequired' => true,
                ),
                'menu_icon' => array(
                    'label' => $this->l('Menu icon font'),
                    'type' => 'text',
                    'class' => 'mm_browse_icon',
                    'desc' => $this->l('Use font awesome class. Eg: fa-bars, fa-plus, ...'),
                ),
                'menu_img_link' => array(
                    'label' => $this->l('Menu icon image'),
                    'type' => 'file',
                    'desc' => sprintf($this->l('Accepted formats: jpg, jpeg, png, gif. Limit %dMb. Recommended size:20 x 20'),Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')),
                ),
                'sub_menu_type' => array(
                    'type' => 'select',
                    'label' => $this->l('Submenu alignment') . (self::multiLayoutExist() ? ' ' . $this->l('(LTR layout)') : ''),
                    'name' => 'menu_type',
                    'class' => 'ybc_menu_type',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'FULL',
                                'name' => $this->l('Auto')
                            ),
                            array(
                                'id_option' => 'LEFT',
                                'name' => $this->l('Left')
                            ),
                            array(
                                'id_option' => 'RIGHT',
                                'name' => $this->l('Right')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'default' => 'FULL',
                    'desc' => self::multiLayoutExist() ? $this->l('Submenu alignment is reversed on RTL layout automatically') : '',
                ),
                'display_tabs_in_full_width' => array(
                    'label' => $this->l('Display tabs in full width'),
                    'type' => 'switch',
                    'default' => 0,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'display_tabs_in_full_width_1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'display_tabs_in_full_width_0',
                            'value' => 0,
                        )
                    ),
                ),
                'sub_menu_max_width' => array(
                    'label' => $this->l('Sub menu width'),
                    'type' => 'text',
                    'required' => true,
                    'default' => '100%',
                    'desc' => $this->l('Use "px" or "%" or "vw". Eg: "100%" or "100px" or "80vw"'),
                ),
                'custom_class' => array(
                    'label' => $this->l('Custom class'),
                    'type' => 'text',
                ),
                'bubble_text' => array(
                    'label' => $this->l('Bubble alert text'),
                    'type' => 'text',
                    'lang' => true,
                    'desc' => $this->l('New, Sale, Hot... Leave blank if you do not want to have a bubble alert for this menu')
                ),
                'bubble_text_color' => array(
                    'label' => $this->l('Bubble alert text color'),
                    'type' => 'color',
                    'default' => '#ffffff',
                    'validate' => 'isColor',
                    'class' => 'color mColorPickerInput'
                ),
                'bubble_background_color' => array(
                    'label' => $this->l('Bubble alert background color'),
                    'type' => 'color',
                    'default' => '#FC4444',
                    'validate' => 'isColor',
                    'class' => 'color mColorPickerInput'
                ),
                'background_image' => array(
                    'label' => $this->l('Background image'),
                    'type' => 'file',
                    'desc' => sprintf($this->l('Accepted formats: jpg, jpeg, png, gif. Limit %dMb.'),Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')),
                ),
                'position_background' => array(
                    'label' => $this->l('Background position'),
                    'type' => 'select',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'center',
                                'name' => $this->l('Center')
                            ),
                            array(
                                'id_option' => 'top',
                                'name' => $this->l('Top')
                            ),
                            array(
                                'id_option' => 'top right',
                                'name' => $this->l('Top right')
                            ),
                            array(
                                'id_option' => 'top left',
                                'name' => $this->l('Top left')
                            ),
                            array(
                                'id_option' => 'left',
                                'name' => $this->l('Left')
                            ),
                            array(
                                'id_option' => 'bottom',
                                'name' => $this->l('Bottom')
                            ),
                            array(
                                'id_option' => 'bottom left',
                                'name' => $this->l('Bottom left')
                            ),
                            array(
                                'id_option' => 'bottom right',
                                'name' => $this->l('Bottom right')
                            ),
                            array(
                                'id_option' => 'right',
                                'name' => $this->l('Right')
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                ),
                'menu_open_new_tab' => array(
                    'label' => $this->l('Open link in new tab'),
                    'type' => 'switch',
                    'default' => 0,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'menu_open_new_tab1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'menu_open_new_tab0',
                            'value' => 0,
                        )
                    ),
                ),
                'sort_order' => array(
                    'label' => $this->l('Sort order'),
                    'type' => 'sort_order',
                    'required' => true,
                    'default' => 1,
                    'order_group' => false,
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
    public static function getMenus($context, $activeOnly = true, $id_lang = false, $id_menu = false)
    {
        $menus = Db::getInstance()->executeS("
            SELECT m.*,ml.title,ml.link,ml.bubble_text
            FROM `" . _DB_PREFIX_ . "ets_mm_menu` m
            INNER JOIN `" . _DB_PREFIX_ . "ets_mm_menu_shop` ms ON (m.id_menu =ms.id_menu AND ms.id_shop='" . (int)$context->shop->id . "')            
            LEFT JOIN `" . _DB_PREFIX_ . "ets_mm_menu_lang` ml
            ON m.id_menu=ml.id_menu AND ml.id_lang=" . ((int)$id_lang ? : (int)$context->language->id) . "
            WHERE 1 " . ($activeOnly ? " AND m.enabled=1" : "") . ($id_menu ? " AND m.id_menu=" . (int)$id_menu : "") . " 
            ORDER BY m.sort_order asc
        ");
        if ($menus)
            foreach ($menus as &$menu) {
                if ($menu['enabled_vertical']) {
                    $menu['tabs'] = MM_Tab::getTabs($context, $menu['id_menu']);
                    $menu['columns'] = array();
                }
                else
                {
                    $menu['columns'] = MM_Column::getColumns($menu['id_menu'], false, $context->language->id);
                }
                $menu['menu_link'] = self::getMenuLink($context, $menu);
                if ($menu['menu_img_link'])
                    $menu['menu_img_link'] = $context->link->getMediaLink(_PS_ETS_MM_IMG_ . $menu['menu_img_link']);
                if ($menu['background_image'])
                    $menu['background_image'] = $context->link->getMediaLink(_PS_ETS_MM_IMG_ . $menu['background_image']);
                if ($context->language->is_rtl) {

                    $menu['position_background'] = str_replace(array('right'), array('_right'), $menu['position_background']);
                    $menu['position_background'] = str_replace(array('left'), array('_left'), $menu['position_background']);
                    $menu['position_background'] = str_replace(array('_left'), array('right'), $menu['position_background']);
                    $menu['position_background'] = str_replace(array('_right'), array('left'), $menu['position_background']);
                }
            }
        return $id_menu && $menus ? $menus[0] : $menus;
    }
    public static function getMenuLink($context, $menu)
    {
        if (isset($menu['link_type'])) {
            switch ($menu['link_type']) {
                case 'CUSTOM':
                    return $menu['link'];
                case 'CMS':
                    return $context->link->getCMSLink((int)$menu['id_cms']);
                case 'CUSTOM':
                    return $menu['link'];
                case 'CATEGORY':
                    return $context->link->getCategoryLink((int)$menu['id_category']);
                case 'MNFT':
                    $manufacturer = new Manufacturer((int)$menu['id_manufacturer'], (int)$context->language->id);
                    if (Validate::isLoadedObject($manufacturer)) {
                        return $context->link->getManufacturerLink((int)$menu['id_manufacturer']);
                    }
                    return '#';
                case 'MNSP':
                    $supplier = new Supplier((int)$menu['id_supplier'], (int)$context->language->id);
                    if (Validate::isLoadedObject($supplier)) {
                        return $context->link->getSupplierLink($supplier->id);
                    }
                    return '#';
                case 'HOME':
                    return $context->link->getPageLink('index', true);
                case 'CONTACT':
                    return $context->link->getPageLink('contact', true);
            }
        }
        return '#';
    }
    public static function deleteAllMenu($id_shop)
    {
        Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_mm_menu` WHERE id_menu IN (SELECT id_menu FROM `" . _DB_PREFIX_ . "ets_mm_menu_shop` WHERE id_shop=" . (int)$id_shop . ")");
        Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_mm_column` WHERE id_menu NOT IN (SELECT id_menu FROM `" . _DB_PREFIX_ . "ets_mm_menu` )");
        Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_mm_menu_lang` WHERE id_menu NOT IN (SELECT id_menu FROM `" . _DB_PREFIX_ . "ets_mm_menu` )");
        Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_mm_block` WHERE id_column NOT IN (SELECT id_column FROM `" . _DB_PREFIX_ . "ets_mm_column` )");
        Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "ets_mm_block_lang` WHERE id_block NOT IN (SELECT id_block FROM `" . _DB_PREFIX_ . "ets_mm_block` )");
        if (!Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ets_mm_menu` '))
            self::clearUploadedImages();
    }
    public static function clearUploadedImages()
    {
        if (@file_exists(_PS_ETS_MM_IMG_DIR_) && ($files = glob(_PS_ETS_MM_IMG_DIR_ . '*'))) {
            foreach ($files as $file)
                if (@file_exists($file) && strpos($file, 'index.php') === false)
                  Ets_megamenu_defines::unlink($file);
        }
    }
}
