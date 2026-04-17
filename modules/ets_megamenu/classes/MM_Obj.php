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
class MM_Obj extends ObjectModel 
{
    public $tab_img_link;
    public $background_image;
    public $menu_img_link;
    public $image;
    public $id_tab;
    public $id_menu;
    public $sort_order;
    public function renderForm($context)
    {
        $formFields = $this->getFormField();
        $helper = new HelperForm();
        /** @var Ets_megamenu $megamenu */
        $megamenu = Module::getInstanceByName('ets_megamenu');
        $helper->module = $megamenu;
        $configs = isset($formFields['configs']) ? $formFields['configs'] : array();
        $fields_form = array();
        $fields_form['form'] = isset($formFields['form']) ? $formFields['form']:array();
        if($configs)
        {
            foreach($configs as $key => $config)
            {                
                if(isset($config['type']) && in_array($config['type'],array('sort_order')))
                    continue;
                $confFields = array(
                    'name' => $key,
                    'type' => $config['type'],
                    'class'=>isset($config['class'])?$config['class']:'',
                    'label' => $config['label'],
                    'desc' => isset($config['desc']) ? $config['desc'] : false,
                    'required' => isset($config['required']) && $config['required'] ? true : false,
                    'autoload_rte' => isset($config['autoload_rte']) && $config['autoload_rte'] ? true : false,
                    'options' => isset($config['options']) && $config['options'] ? $config['options'] : array(),
                    'suffix' => isset($config['suffix']) && $config['suffix'] ? $config['suffix']  : false,
                    'values' => isset($config['values']) ? $config['values'] : false,
                    'lang' => isset($config['lang']) ? $config['lang'] : false,
                    'showRequired' => isset($config['showRequired']) && $config['showRequired'],
                    'hide_delete' => isset($config['hide_delete']) ? $config['hide_delete'] : false,
                    'placeholder' => isset($config['placeholder']) ? $config['placeholder'] : false,
                    'display_img' => $this->id && isset($config['type']) && $config['type']=='file' && $this->$key!='' && @file_exists(_PS_ETS_MM_IMG_DIR_.$this->$key) ? _PS_ETS_MM_IMG_.$this->$key : false,
                    'img_del_link' => $this->id && isset($config['type']) && $config['type']=='file' && $this->$key!='' && @file_exists(_PS_ETS_MM_IMG_DIR_.$this->$key) ? $helper->module->baseAdminUrl().'&deleteimage='.$key.'&itemId='.(isset($this->id)?$this->id:'0').'&mm_object=MM_'.Tools::ucfirst($fields_form['form']['name']) : false, 
                );
                if(isset($config['tree']) && $config['tree'])
                {
                    $confFields['tree'] = $config['tree'];
                    if(isset($config['tree']['use_checkbox']) && $config['tree']['use_checkbox'])
                        $confFields['tree']['selected_categories'] = $this->$key ?  explode(',',$this->$key):array();
                    else
                        $confFields['tree']['selected_categories'] = array($this->$key);
                }                    
                if(!$confFields['suffix'])
                    unset($confFields['suffix']);                
                $fields_form['form']['input'][] = $confFields;
            }
        }        
        
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'save_'.$formFields['form']['name'];
        $link = new Link();
		$helper->currentIndex = $link->getAdminLink('AdminModules', true).'&configure=ets_megamenu';
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $fields = array();        
        $languages = Language::getLanguages(false);
        $helper->override_folder = '/';        
        if($configs)
        {
                foreach($configs as $key => $config)
                {
                    
                    if($config['type']=='checkbox')
                        $fields[$key] = $this->id ? explode(',',$this->$key) : (isset($config['default']) && $config['default'] ? $config['default'] : array());
                    elseif(isset($config['lang']) && $config['lang'])
                    {                    
                        foreach($languages as $l)
                        {
                            $temp = $this->$key;
                            $fields[$key][$l['id_lang']] = $this->id ? $temp[$l['id_lang']] : (isset($config['default']) && $config['default'] ? $config['default'] : null);
                        }
                    }
                    elseif(!isset($config['tree']))
                        $fields[$key] = $this->id ? $this->$key : (isset($config['default']) && $config['default'] ? $config['default'] : null);                            
                }
        }
           
        $helper->tpl_vars = array(
			'base_url' => $context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
			'fields_value' => $fields,
			'languages' => $context->controller->getLanguages(),
			'id_language' => $context->language->id,
            'key_name' => 'id_'.$fields_form['form']['name'],
            'item_id' => $this->id,  
            'mm_object' => 'MM_'.Tools::ucfirst($fields_form['form']['name']),
            'list_item' => true,
            'image_baseurl' => _PS_ETS_MM_IMG_,
            'image_module_baseurl' => $helper->module->modulePath().'views/img/',
        );        
        return str_replace(array('id="ets_mm_menu_form"','id="fieldset_0"'),'',$helper->generateForm(array($fields_form)));	
    }
    public function getFieldVals($context)
    {
        if(!$this->id)
            return array();
        $vals = array();
        $fields = $this->getFormField();
        foreach($fields['configs'] as $key => $config)
        {
            if(property_exists($this,$key))
            {
                if(isset($config['lang'])&&$config['lang'])
                {
                    $val_lang= $this->$key;
                    $vals[$key]=$val_lang[$context->language->id];

                }
                else
                    $vals[$key] = $this->$key;
                    
            }
                
        }
        $vals['id_'.$fields['form']['name']] = (int)$this->id;
        unset($config);
        return $vals;
    }
    public function clearImage($image)
    {
        $fields = $this->getFormField();
        $configs = $fields['configs'];
        $errors = array();
        $success = array();
        if(!$this->id)
            $errors[] = $this->l('Object is empty','MM_Obj');
        elseif(!isset($configs[$image]['type']) || isset($configs[$image]['type']) && $configs[$image]['type']!='file')
            $errors[] = $this->l('Field is not valid','MM_Obj');
        elseif(isset($configs[$image]) && !isset($configs[$image]['required']) || (isset($configs[$image]['required']) && !$configs[$image]['required']))
        {
            $imageName = $this->$image;
            $imagePath = _PS_ETS_MM_IMG_DIR_.$imageName;
            $this->$image = '';
            if($this->update())
            {
                if($imageName && file_exists($imagePath) && !self::imageExists($imageName,$this->id))
                {
                    Ets_megamenu_defines::unlink($imagePath);

                }
                $success[] = $this->l('Image deleted','MM_Obj');
                Ets_megamenu::clearAllCache();
            }
            else
                $errors[] = $this->l('Unknown error happened','MM_Obj');

        }
        else
            $errors[] = sprintf($this->l('%s is required','MM_Obj'),$configs[$image]['label']);
        return array('errors' => $errors,'success' => $success);
    }
    public function deleteObj()
    {
        $errors = array();
        $success = array();
        $fields = $this->getFormField();
        $configs = $fields['configs'];
        $parent = isset($fields['form']['parent']) ? $fields['form']['parent'] : '1';
        $images = array();

        foreach ($configs as $key => $config) {
            if ($config['type'] == 'file' && $this->$key && @file_exists(_PS_ETS_MM_IMG_DIR_ . $this->$key) && !self::imageExists($this->$key, $this->id)) {
                $images[] = _PS_ETS_MM_IMG_DIR_ . $this->$key;
            }
            if ($config['type'] == 'file_lang' && $this->$key) {
                foreach ($this->$key as $image) {
                    if (@file_exists(_PS_ETS_MM_IMG_DIR_ . $image) && !self::imageExists($image, $this->id)) {
                        $images[] = _PS_ETS_MM_IMG_DIR_ . $image;
                    }
                }
            }
        }

        if (!$this->delete()) {
            $errors[] = $this->l('Cannot delete the item due to an unknown technical problem', 'MM_Obj');
        } else {
            if ($images) {
                foreach ($images as $image) {
                    if (file_exists($image)) {
                        Ets_megamenu_defines::unlink($image);
                    }
                }
            }
            $success[] = $this->l('Item deleted', 'MM_Obj');
            Ets_megamenu::clearAllCache();

            if (isset($configs['sort_order']) && $configs['sort_order']) {
                $table = 'ets_mm_' . pSQL($fields['form']['name']);
                $condition = 'sort_order > ' . (int)$this->sort_order;

                if (isset($configs['sort_order']['order_group'][$parent]) && ($orderGroup = $configs['sort_order']['order_group'][$parent])) {
                    $condition .= ' AND ' . pSQL($orderGroup) . ' = ' . (int)$this->$orderGroup;
                }
                Db::getInstance()->update($table, array(
                    'sort_order' => array('type' => 'sql', 'value' => 'sort_order - 1')
                ), $condition);
            }

            if ($this->id) {
                if(isset($fields['form']['connect_to2']) && $fields['form']['connect_to2'])
                    $this->deleteConnectedItems($fields['form']['connect_to2'], $fields['form']['name']);
                if(isset($fields['form']['connect_to']) && $fields['form']['connect_to'])
                    $this->deleteConnectedItems($fields['form']['connect_to'], $fields['form']['name']);
            }
        }

        return array('errors' => $errors, 'success' => $success);
    }

    private function deleteConnectedItems($connectionField, $formName)
    {
        if (isset($connectionField) && $connectionField) {
            $query = new DbQuery();
            $query->select('id_' . pSQL($connectionField))
                ->from( 'ets_mm_' . pSQL($connectionField))
                ->where('id_' . pSQL($formName) . ' = ' . (int)$this->id);
            $subs = Db::getInstance()->executeS($query);

            foreach ($subs as $sub) {
                $className = 'MM_' . Tools::ucfirst(Tools::strtolower($connectionField));
                if (class_exists($className)) {
                    $obj = new $className((int)$sub['id_' . $connectionField]);
                    $obj->deleteObj();
                }
            }
        }
    }
    public function maxVal($key,$group = false, $groupval=0)
    {
        $fields = $this->getFormField();
        $key = bqSQL($key);
        $tableName = 'ets_mm_' . bqSQL($fields['form']['name']);
        $query = new DbQuery();
        $query->select('MAX(' . $key . ')');
        $query->from($tableName);
        if ($group && $groupval > 0) {
            $query->where(bqSQL($group) . ' = ' . (int)$groupval);
        }
        return ($max = Db::getInstance()->getValue($query)) ? (int)$max : 0;
    }   
    public function updateOrder($previousId = 0, $groupdId = 0,$parentObj='')
    {
        $fields = $this->getFormField();
        $group = isset($fields['configs']['sort_order']['order_group'][$parentObj]) && $fields['configs']['sort_order']['order_group'][$parentObj] ? $fields['configs']['sort_order']['order_group'][$parentObj] : false;
        if(!$groupdId && $group)
            $groupdId = $this->$group;
        $oldOrder = $this->sort_order;
        if($group && $groupdId && property_exists($this,$group) && $this->$group != $groupdId)
        {            
            Db::getInstance()->execute("
                    UPDATE "._DB_PREFIX_."ets_mm_".bqSQL($fields['form']['name'])."
                    SET sort_order=sort_order-1 
                    WHERE sort_order>".(int)$this->sort_order." AND id_".bqSQL($fields['form']['name'])."!=".(int)$this->id." AND ".bqSQL($group)."=".(int)$this->$group);
            $this->$group = $groupdId;
            if($parentObj=='tab')
            {
                $tab= new MM_Tab($groupdId);
                $this->id_menu = $tab->id_menu;
            }
            if($parentObj=='menu')
            {
                $this->id_tab=0;
            }
            $changeGroup = true;
        }
        else
            $changeGroup = false;                    
        if($previousId > 0)
        {
            $objName = 'MM_'.Tools::ucfirst($fields['form']['name']);
            $obj = new $objName($previousId);
            if($obj->sort_order > 0)
                $this->sort_order = $obj->sort_order+1;
            else
                $this->sort_order = 1;
        } else {
            $this->sort_order = 0;
        }

        if($this->update())
        {    
            
            Db::getInstance()->execute("
                    UPDATE "._DB_PREFIX_."ets_mm_".pSQL($fields['form']['name'])."
                    SET sort_order=sort_order+1 
                    WHERE sort_order>=".(int)$this->sort_order." AND id_".pSQL($fields['form']['name'])."!=".(int)$this->id."
                          ".($group && $groupdId ? " AND ".pSQL($group)."=".(int)$this->$group : ""));
            
            if(!$changeGroup && $this->sort_order!=$oldOrder)
            {                
                
                $rs = Db::getInstance()->execute("
                        UPDATE "._DB_PREFIX_."ets_mm_".pSQL($fields['form']['name'])."
                        SET sort_order=sort_order-1
                        WHERE sort_order>".($this->sort_order > $oldOrder ? (int)($oldOrder) : (int)($oldOrder+1)).($group && $groupdId ? " AND ".pSQL($group)."=".(int)$this->$group : ""));
                Ets_megamenu::clearAllCache();
                return $rs;
            }
            Ets_megamenu::clearAllCache();
            return true;
        }               
        return false;       
    }
    public static function imageExists($image, $id)
    {
        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            $res = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'ets_mm_tab` WHERE (tab_img_link="' . pSQL($image) . '" OR background_image="' . pSQL($image) . '") AND id_tab!="' . (int)$id . '"') || Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'ets_mm_block_lang` WHERE image ="' . pSQL($image) . '" AND id_block!="' . (int)$id . '"') || Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'ets_mm_menu` WHERE (background_image="' . pSQL($image) . '" OR menu_img_link="' . pSQL($image) . '") AND id_menu!="' . (int)$id . '"');
            return $res;
        }
        return false;
    }
    public function duplicateItem($id_parent = false,$id_parent2=false, $id_shop= 0)
    {
        $oldId = $this->id;
        $this->id = null;
        $formFields = $this->getFormField();
        if($id_parent && isset($formFields['form']['parent']))
        {
            $parent = 'id_'.$formFields['form']['parent'];
            if(property_exists($this,$parent))
            {
                $this->$parent = $id_parent;
            }
        }
        if($id_parent2 && isset($formFields['form']['parent2']))
        {
            $parent2 = 'id_'.$formFields['form']['parent2'];
            if(property_exists($this,$parent2))
                $this->$parent2 = $id_parent2;
        }

        if(property_exists($this,'sort_order'))
        {
            if(!isset($formFields['form']['parent'])|| !isset($formFields['configs']['sort_order']['order_group'][$formFields['form']['parent']]) || isset($formFields['configs']['sort_order']['order_group'][$formFields['form']['parent']]) && !$formFields['configs']['sort_order']['order_group'][$formFields['form']['parent']])
                $this->sort_order = $this->maxVal('sort_order')+1;
            else
            {
                $tempName = $formFields['configs']['sort_order']['order_group'][$formFields['form']['parent']];
                $this->sort_order = $this->maxVal('sort_order',$tempName,(int)$this->$tempName)+1;
                $groupId = $this->$tempName;
            }  
            $oldOrder = $this->sort_order;              
        }
        if(property_exists($this,'image') && $this->image)
        {
            $oldImages = array();
            if(is_array($this->image))
            {
                foreach($this->image as $id_lang => $image)
                {
                    if(file_exists(_PS_ETS_MM_IMG_DIR_.$image))
                    {
                        $salt = $this->maxVal('id_'.$formFields['form']['name'])+1;
                        $oldImages[$id_lang] = _PS_ETS_MM_IMG_DIR_.$image;
                        $this->image[$id_lang] = $salt.'_'.$image;
                    }
                }
            }
            else
            {
                if(file_exists(_PS_ETS_MM_IMG_DIR_.$this->image))
                {
                    $salt = $this->maxVal('id_'.$formFields['form']['name'])+1;
                    $oldImage = _PS_ETS_MM_IMG_DIR_.$this->image;
                    $this->image = $salt.'_'.$this->image;
                }
            }
        }
        if(property_exists($this,'menu_img_link') && $this->menu_img_link && file_exists(_PS_ETS_MM_IMG_DIR_.$this->menu_img_link))
        {
            $salt = $this->maxVal('id_'.$formFields['form']['name'])+1;
            $oldmenu_img_link = _PS_ETS_MM_IMG_DIR_.$this->menu_img_link;
            $this->menu_img_link = $salt.'_'.$this->menu_img_link;            
        }
        if(property_exists($this,'background_image') && $this->background_image && file_exists(_PS_ETS_MM_IMG_DIR_.$this->background_image))
        {
            $salt = $this->maxVal('id_'.$formFields['form']['name'])+1;
            $oldbackground_image = _PS_ETS_MM_IMG_DIR_.$this->background_image;
            $this->background_image = $salt.'_'.$this->background_image;            
        }
        if(property_exists($this,'tab_img_link') && $this->tab_img_link && file_exists(_PS_ETS_MM_IMG_DIR_.$this->tab_img_link))
        {
            $salt = $this->maxVal('id_'.$formFields['form']['name'])+1;
            $oldtab_img_link = _PS_ETS_MM_IMG_DIR_.$this->tab_img_link;
            $this->image = $salt.'_'.$this->tab_img_link;            
        }
        if($this->add(true, false, $id_shop))
        {
            if(isset($oldImage))
            {
                @copy($oldImage,_PS_ETS_MM_IMG_DIR_.$this->image);
            }
            if(isset($oldImages) && $oldImages)
            {
                foreach($oldImages as $id_lang=> $image)
                {
                    @copy($image,_PS_ETS_MM_IMG_DIR_.$this->image[$id_lang]);
                }
            }
            if(isset($oldmenu_img_link) )
            {
                @copy($oldmenu_img_link,_PS_ETS_MM_IMG_DIR_.$this->menu_img_link);
            }
            if(isset($oldbackground_image))
            {
                @copy($oldbackground_image,_PS_ETS_MM_IMG_DIR_.$this->background_image);
            }
            if(isset($oldtab_img_link))
            {
                @copy($oldtab_img_link,_PS_ETS_MM_IMG_DIR_.$this->tab_img_link);
            }
            if(isset($oldOrder) && $oldOrder)
                $this->updateOrder($oldId,isset($groupId) ? (int)$groupId : 0);
            if (get_class($this) == 'MM_Menu' && $this->enabled_vertical) {
                if (isset($formFields['form']['connect_to2']) && $formFields['form']['connect_to2']) {
                    $connectTo2 = bqSQL($formFields['form']['connect_to2']);
                    $formName = bqSQL($formFields['form']['name']);
                    $query = new DbQuery();
                    $query->select('id_' . $connectTo2);
                    $query->from( 'ets_mm_' . $connectTo2);
                    $query->where('id_' . $formName . ' = ' . (int)$oldId);
                    $subs = Db::getInstance()->executeS($query);
                    if ($subs) {
                        foreach ($subs as $sub) {
                            $className = 'MM_' . Tools::ucfirst(Tools::strtolower($connectTo2));
                            if (class_exists($className)) {
                                $obj = new $className((int)$sub['id_' . $connectTo2]);
                                $obj->duplicateItem($this->id, false, $id_shop);
                            }
                        }
                    }
                }
            } else {
                if (isset($formFields['form']['connect_to']) && $formFields['form']['connect_to']) {
                    $connectTo = bqSQL($formFields['form']['connect_to']);
                    $formName = bqSQL($formFields['form']['name']);
                    $query = new DbQuery();
                    $query->select('id_' . $connectTo);
                    $query->from( 'ets_mm_' . $connectTo);
                    $query->where('id_' . $formName . ' = ' . (int)$oldId);
                    $subs = Db::getInstance()->executeS($query);
                    if ($subs) {
                        foreach ($subs as $sub) {
                            $className = 'MM_' . Tools::ucfirst(Tools::strtolower($connectTo));
                            if (class_exists($className)) {
                                $obj = new $className((int)$sub['id_' . $connectTo]);
                                if (get_class($this) == 'MM_Tab') {
                                    $obj->duplicateItem($id_parent, $this->id, $id_shop);
                                } else {
                                    $obj->duplicateItem($this->id, false, $id_shop);
                                }
                            }
                        }
                    }
                }
            }
            return $this;
        }
        return false;
    }
    public function update($null_value=false)
    {
        $ok = parent::update($null_value);
        if(get_class($this)=='MM_Menu' && $this->enabled_vertical)
        {
            $columns= Db::getInstance()->executeS('SELECT id_column FROM `'._DB_PREFIX_.'ets_mm_column` WHERE id_menu='.(int)$this->id.' AND id_tab not in (SELECT id_tab FROM `'._DB_PREFIX_.'ets_mm_tab` where id_menu ='.(int)$this->id.')');
            if($columns)
            {
                $id_tab= Db::getInstance()->getValue('SELECT id_tab FROM `'._DB_PREFIX_.'ets_mm_tab` where id_menu='.(int)$this->id);
                if(!$id_tab)
                {
                    $tab=new MM_Tab();
                    $tab->id_menu=$this->id;
                    $tab->enabled=1;
                    $languages= Language::getLanguages(false);
                    foreach($languages as $language)
                    {
                        $tab->title[$language['id_lang']] ='Undefined title';
                    }
                    $tab->add();
                    $id_tab=$tab->id;
                }
                foreach($columns as $column)
                {
                    Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ets_mm_column` SET id_tab="'.(int)$id_tab.'" WHERE id_column='.(int)$column['id_column']);
                }
            }
        }
        return $ok;
    }
    public static function multiLayoutExist()
    {
        return Db::getInstance()->getRow("SELECT id_lang FROM `" . _DB_PREFIX_ . "lang` WHERE is_rtl=0 AND active=1") && Db::getInstance()->getRow("SELECT id_lang FROM `" . _DB_PREFIX_ . "lang` WHERE is_rtl=1 AND active=1");
    }
    public static function getManufacturers($id_shop, $orderBy = 'name asc', $addWhere = false)
    {
        return Db::getInstance()->executeS("
            SELECT m.id_manufacturer as value,CONCAT('mm_manufacturer_',m.id_manufacturer) as id, name as label
            FROM `" . _DB_PREFIX_ . "manufacturer` m
            INNER JOIN `" . _DB_PREFIX_ . "manufacturer_shop` ms ON (m.id_manufacturer=ms.id_manufacturer AND ms.id_shop=" . (int)$id_shop . ")            
            WHERE active=1 " . ($addWhere ? pSQL($addWhere) : "") . "
            ORDER BY " . ($orderBy ? $orderBy : 'name asc') . "
        ");
    }

    public static function getSuppliers($id_shop, $orderBy = 'name asc', $addWhere = false)
    {
        return Db::getInstance()->executeS("
            SELECT s.id_supplier as value,CONCAT('mm_supplier_',s.id_supplier) as id, name as label
            FROM `" . _DB_PREFIX_ . "supplier` s
            INNER JOIN `" . _DB_PREFIX_ . "supplier_shop` ss ON (s.id_supplier=ss.id_supplier AND ss.id_shop=" . (int)$id_shop . ")            
            WHERE active=1 " . ($addWhere ? pSQL($addWhere) : "") . "
            ORDER BY " . ($orderBy ? $orderBy : 'name asc') . "
        ");
    }

    public static function getCustomerGroups($id_lang, $orderBy = 'name asc', $addWhere = false)
    {
        return Db::getInstance()->executeS("
            SELECT g.id_group as value, CONCAT('mm_group_', g.id_group) as id, gl.name as label
            FROM `" . _DB_PREFIX_ . "group` g
            LEFT JOIN `" . _DB_PREFIX_ . "group_lang` gl ON (
                g.id_group = gl.id_group 
                AND gl.id_lang = " . (int)$id_lang . "
            )
            WHERE 1 " . ($addWhere ? " AND " . pSQL($addWhere) : "") . "
            ORDER BY " . ($orderBy ? pSQL($orderBy) : 'name asc') . "
        ");
    }
    public static function getCMSs($context, $orderBy = 'cl.meta_title asc', $addWhere = false)
    {
        return Db::getInstance()->executeS("
            SELECT c.id_cms as value,CONCAT('mm_cms_',c.id_cms) as id, cl.meta_title as label            
            FROM `" . _DB_PREFIX_ . "cms` c
            INNER JOIN `" . _DB_PREFIX_ . "cms_shop` cs ON (c.id_cms= cs.id_cms AND cs.id_shop=" . (int)$context->shop->id . ")
            LEFT JOIN `" . _DB_PREFIX_ . "cms_lang` cl ON c.id_cms=cl.id_cms AND cl.id_lang=" . (int)$context->language->id .((version_compare(_PS_VERSION_,'1.6.0.12','>=')) ? " AND cl.id_shop=".(int)$context->shop->id:""). "
            WHERE c.active=1 " . ($addWhere ? pSQL($addWhere) : "") . "
            ORDER BY " . ($orderBy ? : 'cl.meta_title asc') . "
        ");
    }
    public function getColumnSizes()
    {
        $sizes = array();
        for ($i = 1; $i <= 12; $i++) {
            $sizes[] = array(
                'id_option' => $i,
                'name' => $i != 12 ? $i . '/12' : $this->l('12/12 (Full)'),
            );
        }
        return $sizes;
    }
    public function getFormField(){
        return array();
    }
    public function l($string, $fileName="")
    {
        return Translate::getModuleTranslation('ets_megamenu', $string, $fileName ?: pathinfo(__FILE__, PATHINFO_FILENAME));
    }
    public function add($autodate = true, $null_values = false, $id_shop = null)
    {
        unset($id_shop);
        return parent::add($autodate, $null_values);
    }
}