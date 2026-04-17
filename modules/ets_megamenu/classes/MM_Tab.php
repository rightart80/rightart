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
class MM_Tab extends MM_Obj
{
    public $id_tab;
    public $id_menu;   
    public $tab_img_link;
    public $tab_sub_width;
    public $menu_ver_hidden_border;
    public $tab_sub_content_pos;
    public $tab_icon; 
    public $title;    
    public $enabled;
    public $sort_order;    
    public $bubble_background_color;
    public $bubble_text_color;
    public $bubble_text;
    public $background_image;
    public $position_background;
    public $link_type;
    public $id_cms;
    public $id_category;
    public $id_manufacturer;
    public $id_supplier;
    public $url;
    /**
     * @var array
     */
    public $fields_form = [];
    public static $definition = array(
		'table' => 'ets_mm_tab',
		'primary' => 'id_tab',
		'multilang' => true,
		'fields' => array(
			'id_menu' => array('type' => self::TYPE_INT), 
            'tab_img_link'=> array('type'=>self::TYPE_STRING),
            'tab_sub_width'=> array('type'=>self::TYPE_STRING),
            'tab_icon'=> array('type'=>self::TYPE_STRING),
            'bubble_text_color'=> array('type'=>self::TYPE_STRING),
            'bubble_background_color'=> array('type'=>self::TYPE_STRING),
            'tab_sub_content_pos'=>array('type'=>self::TYPE_INT),
            'enabled'=>array('type'=>self::TYPE_INT),
            'background_image' => array('type' => self::TYPE_STRING),
            'position_background' => array('type' => self::TYPE_STRING),
            'title' => array('type' => self::TYPE_STRING,'lang' => true),
            'url' => array('type'=>self::TYPE_STRING,'lang'=>true),
            'bubble_text' => array('type' => self::TYPE_STRING,'lang' => true),   
            'sort_order' => array('type' => self::TYPE_INT),  
            'link_type' => array('type' => self::TYPE_STRING),  
            'id_category' => array('type' => self::TYPE_INT),   
            'id_manufacturer' => array('type' => self::TYPE_INT),
            'id_supplier'=>array('type'=>self::TYPE_INT),
            'id_cms' => array('type' => self::TYPE_INT),         
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
                    'title' => (int)$this->id ? $this->l('Edit tab') : $this->l('Add tab'),
                ),
                'input' => array(),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
                'name' => 'tab',
                'connect_to' => 'column',
                'parent' => 'menu',
            ),
            'configs' => array(
                'title' => array(
                    'label' => $this->l('Title'),
                    'type' => 'text',
                    'required' => true,
                    'lang' => true,
                ),
                'link_type' => array(
                    'type' => 'select',
                    'label' => $this->l('Tab link type'),
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
                'url' => array(
                    'label' => 'Custom link',
                    'type' => 'text',
                    'lang' => true,
                    'showRequired' => true,
                    'validate'=> 'isCleanHtml'
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
                'tab_icon' => array(
                    'label' => $this->l('Tab icon font'),
                    'type' => 'text',
                    'class' => 'mm_browse_icon',
                    'desc' => $this->l('Use font awesome class. Ex: fa-bars, fa-plus, ...'),
                ),
                'tab_img_link' => array(
                    'label' => $this->l('Tab icon image'),
                    'type' => 'file',
                    'desc' => sprintf($this->l('Accepted formats: jpg, jpeg, png, gif. Limit %dMb. Recommended size:20 x 20'),Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')),
                ),
                'tab_sub_width' => array(
                    'label' => $this->l('Tab content width'),
                    'type' => 'text',
                    'desc' => $this->l('Use "px" or "%" or "vw". Eg: "20%" or "230px" or "80vw"'),
                ),
                'tab_sub_content_pos' => array(
                    'label' => $this->l('Display tab content from top'),
                    'type' => 'switch',
                    'default' => 1,
                    'values' => array(
                        array(
                            'label' => $this->l('Yes'),
                            'id' => 'tab_sub_content_pos_1',
                            'value' => 1,
                        ),
                        array(
                            'label' => $this->l('No'),
                            'id' => 'tab_sub_content_pos_0',
                            'value' => 0,
                        )
                    ),
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
                'id_menu' => array(
                    'label' => $this->l('Menu'),
                    'type' => 'hidden',
                    'default' => ($id_menu = (int)Tools::isSubmit('id_menu')) ? $id_menu : 0,
                    'required' => true,
                ),
                'sort_order' => array(
                    'label' => $this->l('Sort order'),
                    'type' => 'sort_order',
                    'required' => true,
                    'default' => 1,
                    'order_group' => array(
                        'menu' => 'id_menu',
                    ),
                ),
            ),
        );
        return self::$formFields;
    }
    public static function getTabs($context, $id_menu = false, $id_tab = false, $id_lang = false)
    {
        $tabs = Db::getInstance()->executeS("
            SELECT *
            FROM `" . _DB_PREFIX_ . "ets_mm_tab` t
            LEFT JOIN `" . _DB_PREFIX_ . "ets_mm_tab_lang` tl on (t.id_tab=tl.id_tab AND tl.id_lang=" . ($id_lang ? (int)$id_lang : (int)$context->language->id) . ")
            WHERE 1 " . ($id_menu ? " AND id_menu=" . (int)$id_menu : "") . ($id_tab ? " AND t.id_tab=" . (int)$id_tab : "") . "
            ORDER BY sort_order asc
        ");
        if ($tabs)
        {
            foreach ($tabs as &$tab) {
                $tab['columns'] = MM_Column:: getColumnsByTab($tab['id_tab'], false, $id_lang);
                if ($tab['tab_img_link'])
                    $tab['tab_img_link'] = $context->link->getMediaLink(_PS_ETS_MM_IMG_ . $tab['tab_img_link']);
                if ($tab['background_image'])
                    $tab['background_image'] = $context->link->getMediaLink(_PS_ETS_MM_IMG_ . $tab['background_image']);
                $tab['url'] = self::getTabLink($context, $tab);
                if ($context->language->is_rtl) {
                    $tab['position_background'] = str_replace(array('right'), array('_right'), $tab['position_background']);
                    $tab['position_background'] = str_replace(array('left'), array('_left'), $tab['position_background']);
                    $tab['position_background'] = str_replace(array('_left'), array('right'), $tab['position_background']);
                    $tab['position_background'] = str_replace(array('_right'), array('left'), $tab['position_background']);
                }
            }
        }

        return $id_tab && $tabs ? $tabs[0] : $tabs;
    }
    public static function getTabLink($context, $tab)
    {
        if (isset($tab['link_type'])) {
            switch ($tab['link_type']) {
                case 'CUSTOM':
                    return $tab['url'];
                case 'CMS':
                    return $context->link->getCMSLink((int)$tab['id_cms']);
                case 'CATEGORY':
                    return $context->link->getCategoryLink((int)$tab['id_category']);
                case 'MNFT':
                    $manufacturer = new Manufacturer((int)$tab['id_manufacturer'], (int)$context->language->id);
                    if (Validate::isLoadedObject($manufacturer)) {
                        return $context->link->getManufacturerLink((int)$tab['id_manufacturer']);
                    }
                    return '#';
                case 'MNSP':
                    $supplier = new Supplier((int)$tab['id_supplier'], (int)$context->language->id);
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
}
