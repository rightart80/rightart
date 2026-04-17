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
class MM_Column extends MM_Obj
{
    public $id_column;
    public $id_menu;    
    public $id_tab;
    public $column_size;    
    public $sort_order;
    public $is_breaker;
    /**
     * @var array
     */
    public $fields_form = [];
    public static $definition = array(
		'table' => 'ets_mm_column',
		'primary' => 'id_column',
		'multilang' => false,
		'fields' => array(
			'id_menu' => array('type' => self::TYPE_INT),
            'id_tab' => array('type' => self::TYPE_INT),  
            'column_size' => array('type' => self::TYPE_STRING),   
            'sort_order' => array('type' => self::TYPE_INT), 
            'is_breaker' => array('type' => self::TYPE_INT),             
        )
	);
    public function l($string, $fileName="")
    {
        return Translate::getModuleTranslation('ets_megamenu', $string, $fileName ?: pathinfo(__FILE__, PATHINFO_FILENAME));
    }
    protected static $formFields;
    public function getFormField()
    {
        if(!self::$formFields)
            self::$formFields =  array(
            'form' => array(
                'legend' => array(
                    'title' => (int)$this->id ? $this->l('Edit column') : $this->l('Add column'),
                ),
                'input' => array(),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
                'name' => 'column',
                'connect_to' => 'block',
                'parent' => 'menu',
                'parent2' => 'tab'
            ),
            'configs' => array(
                'column_size' => array(
                    'type' => 'select',
                    'label' => $this->l('Column width size'),
                    'name' => 'menu_type',
                    'options' => array(
                        'query' => $this->getColumnSizes(),
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'default' => '3',
                ),
                'is_breaker' => array(
                    'label' => $this->l('Break'),
                    'type' => 'switch',
                    'default' => 0,
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
                    'desc' => $this->l('Break from this column to new line'),
                ),
                'id_menu' => array(
                    'label' => $this->l('Menu'),
                    'type' => 'hidden',
                    'default' => ($id_menu = (int)Tools::isSubmit('id_menu')) ? $id_menu : 0,
                    'required' => true,
                ),
                'id_tab' => array(
                    'label' => $this->l('Tab'),
                    'type' => 'hidden',
                    'default' => ($id_tab = (int)Tools::isSubmit('id_tab')) ? $id_tab : 0,
                    'required' => true,
                ),
                'sort_order' => array(
                    'label' => $this->l('Sort order'),
                    'type' => 'sort_order',
                    'required' => true,
                    'default' => 1,
                    'order_group' => array(
                        'menu' => 'id_menu',
                        'tab' => 'id_tab',
                    ),
                ),
            ),
        );
        return self::$formFields;
    }
    public static function getColumns($id_menu = false, $id_column = false, $id_lang = false)
    {
        $columns = Db::getInstance()->executeS("
            SELECT *
            FROM `" . _DB_PREFIX_ . "ets_mm_column`
            WHERE 1 " . ($id_menu ? " AND id_menu=" . (int)$id_menu : "") . ($id_column ? " AND id_column=" . (int)$id_column : "") . "
            ORDER BY sort_order asc
        ");
        if ($columns)
            foreach ($columns as &$column)
                $column['blocks'] = MM_Block::getBlocks($id_lang,$column['id_column'], false );
        return $id_column && $columns ? $columns[0] : $columns;
    }
    public static function getColumnsByTab($id_tab = false, $id_column = false, $id_lang = false)
    {
        $columns = Db::getInstance()->executeS("
            SELECT *
            FROM `" . _DB_PREFIX_ . "ets_mm_column`
            WHERE 1 " . ($id_tab ? " AND id_tab=" . (int)$id_tab : "") . ($id_column ? " AND id_column=" . (int)$id_column : "") . "
            ORDER BY sort_order asc
        ");
        if ($columns)
            foreach ($columns as &$column)
                $column['blocks'] = MM_Block::getBlocks($id_lang,$column['id_column'], false);
        return $id_column && $columns ? $columns[0] : $columns;
    }
}
