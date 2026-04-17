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
require_once(dirname(__FILE__) . '/classes/MM_Obj.php');
require_once(dirname(__FILE__) . '/classes/MM_Menu.php');
require_once(dirname(__FILE__) . '/classes/MM_Column.php');
require_once(dirname(__FILE__) . '/classes/MM_Block.php');
require_once(dirname(__FILE__) . '/classes/MM_Config.php');
require_once(dirname(__FILE__) . '/classes/MM_Tab.php');
require_once(dirname(__FILE__) . '/classes/MM_Products.php');
require_once(dirname(__FILE__) . '/classes/Ets_megamenu_defines.php');
require_once(dirname(__FILE__) . '/classes/MM_ImportExport.php');
if (version_compare(_PS_VERSION_, '1.6.1.0', '<'))
    include_once(dirname(__FILE__) . '/classes/Uploader.php');
if (!defined('_PS_ETS_MM_IMG_DIR_')) {
    define('_PS_ETS_MM_IMG_DIR_', _PS_IMG_DIR_ . 'ets_megamenu/');
}
if (!defined('_PS_ETS_MM_IMG_')) {
    define('_PS_ETS_MM_IMG_', __PS_BASE_URI__ . 'img/ets_megamenu/');
}
if (!defined('_ETS_MEGAMENU_CACHE_DIR_'))
    define('_ETS_MEGAMENU_CACHE_DIR_', _PS_CACHE_DIR_ . 'ets_megamenu/');

class Ets_megamenu extends Module
{
    private $_html;
    public $alerts;
    public $is8 = false;
    public $is17 = false;
    public $errors = array();
    public $image_dir;
    public function __construct()
    {
        $this->name = 'ets_megamenu';
        $this->tab = 'front_office_features';
        $this->version = '2.6.5';
        $this->author = 'PrestaHero';
        $this->module_key = '';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Mega Menu PRO');
        $this->description = $this->l('Visual drag and drop mega menu builder');
        $this->refs = 'https://prestahero.com/';
        $this->ps_versions_compliancy = array('min' => '1.6.0.6', 'max' => _PS_VERSION_);
        $this->image_dir = _PS_CAT_IMG_DIR_;
        if (version_compare(_PS_VERSION_, '8.0', '>=')) {
            $this->is8 = true;
        }
        if (version_compare(_PS_VERSION_, '1.7', '>='))
            $this->is17 = true;
        $this->context->smarty->assign(
            array(
                'link' => $this->context->link,
                'MODULE_DIR' => _MODULE_DIR_,
            )
        );
    }
    public function imageTypes($setDefault)
    {
        $types = ImageType::getImagesTypes('products');
        if (!$types)
            return $setDefault ? array(false, false) : array();
        $result = array();
        if ($setDefault)
            $default = array();
        foreach ($types as $image_type) {
            $result[] = array(
                'id_option' => ($imgType = $this->imageType($image_type['name'])),
                'name' => Tools::ucfirst($imgType),
            );
            if (isset($default) && (trim($imgType) == 'home' || trim($imgType) == 'large' || trim($imgType) == 'medium')) {
                $default[$imgType] = $imgType;
            }
        }
        if (isset($default) && !$default && isset($result[0])) {
            $item = $result[0];
            $default[$item['id_option']] = trim($item['id_option']);
            return array($result, $default);
        }
        return isset($default) ? array($result, isset($default['home']) ? $default['home'] : (isset($default['large']) ? $default['large'] : $default['medium'])) : $result;
    }

    public function imageType($name, $ucFirst = false)
    {
        $name = str_replace('_default', '', $name);
        if ($ucFirst)
            $name = Tools::ucfirst($name);
        return $name;
    }

    /**
     * @see Module::disable()
     */
    public function disable($force_all = false)
    {
        return parent::disable($force_all) && $this->activeModuleExtra();
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        $config = new MM_Config();
        $config->installConfigs();
        self::clearAllCache();
        $this->rrmdir(_PS_ETS_MM_IMG_DIR_);
        if (!is_dir(_ETS_MEGAMENU_CACHE_DIR_)){
            @mkdir(_ETS_MEGAMENU_CACHE_DIR_, 0777, true);
            Tools::copy(dirname(__FILE__).'/index.php',_ETS_MEGAMENU_CACHE_DIR_.'index.php');
        }
        if ($this->is17 && Module::isInstalled('ps_mainmenu'))
            Module::disableByName('ps_mainmenu');
        elseif (!$this->is17 && Module::isInstalled('blocktopmenu'))
            Module::disableByName('blocktopmenu');
        Configuration::updateValue('PS_ALLOW_HTML_IFRAME', 1);

        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayTop')
            && $this->registerHook('displayBlock')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayMMItemMenu')
            && $this->registerHook('displayMMItemColumn')
            && $this->registerHook('displayMegaMenu')
            && $this->registerHook('displayMMItemBlock')
            && $this->registerHook('displayMMItemTab')
            && $this->registerHook('displayCustomMenu')
            && $this->registerHook('displayCustomerInforTop')
            && $this->registerHook('displaySearch')
            && $this->registerHook('displayCartTop')
            && $this->registerHook('displayMMProductList')
            && $this->registerHook('displayNavFullWidth')
            && $this->registerHook('actionObjectLanguageAddAfter')
            && $this->installDb()
            && $this->initMenu();
    }

    /**
     * @see Module::uninstall()
     */
    public function uninstall()
    {
            self::clearAllCache();
        $this->rrmdir(_PS_ETS_MM_IMG_DIR_);
        $this->rrmdir(_ETS_MEGAMENU_CACHE_DIR_);
        $config = new MM_Config();
        $config->unInstallConfigs();
        return parent::uninstall()
            && $this->unregisterHook('displayHeader')
            && $this->unregisterHook('displayTop')
            && $this->unregisterHook('displayBlock')
            && $this->unregisterHook('displayBackOfficeHeader')
            && $this->unregisterHook('displayMMItemMenu')
            && $this->unregisterHook('displayMMItemColumn')
            && $this->unregisterHook('displayMegaMenu')
            && $this->unregisterHook('displayMMItemBlock')
            && $this->unregisterHook('displayMMItemTab')
            && $this->unregisterHook('displayCustomMenu')
            && $this->unregisterHook('displayCustomerInforTop')
            && $this->unregisterHook('displaySearch')
            && $this->unregisterHook('displayCartTop')
            && $this->unregisterHook('displayMMProductList')
            && $this->unregisterHook('displayNavFullWidth')
            && $this->unregisterHook('actionObjectLanguageAddAfter')
            && Ets_megamenu_defines::deleteDb() && $this->activeModuleExtra();

    }

    public function initMenu()
    {
        $languages = Language::getLanguages(false);
        $menu = new MM_Menu();
        $menu->enabled_vertical = 0;
        $menu->menu_ver_text_color = '#ffffff';
        $menu->menu_ver_background_color = '#666666';
        $menu->menu_ver_alway_show = 0;
        $menu->menu_ver_alway_open_first = 1;
        $menu->menu_ver_hidden_border = 0;
        $menu->menu_item_width = '230px';
        $menu->tab_item_width = '230px';
        $menu->link_type = 'HOME';
        if ($languages) {
            $val = array();
            foreach ($languages as $lang)
                $val[$lang['id_lang']] = $this->l('Home');
            $menu->title = $val;
        }
        $menu->menu_icon = 'fa-home';
        $menu->sub_menu_type = 'FULL';
        $menu->display_tabs_in_full_width = 1;
        $menu->sub_menu_max_width = '100%';
        $menu->bubble_text_color = '#ffffff';
        $menu->bubble_background_color = '#FC4444';
        $menu->position_background = 'center';
        $menu->menu_open_new_tab = 0;
        $menu->enabled = 1;
        $shops = Shop::getShops(false);
        $res = $menu->validateFields(false);
        if (count($shops) > 1) {
            foreach ($shops as $shop) {
                if (!empty($shop['id_shop']))
                    $res &= $menu->add(true, false, (int)$shop['id_shop']);
            }
        } else
            $res &= $menu->add(true, false, $this->context->shop->id);
        return $res;
    }

    public function getContent()
    {
        $this->registerPlugins();
        if (!$this->active) {
            return  $this->displayWarning($this->l('You have to enable Mega menu PRO module to configure its features'));
        }
        $this->proccessPost();
        $this->requestForm();
        $this->context->controller->addJqueryUI('ui.sortable');
        $this->_html .= $this->renderForm();
        $this->_html .= $this->displayAdminJs();
        return $this->_html;
    }

    public function renderForm()
    {
        $menu = new MM_Menu();
        $tab = new MM_Tab();
        $column = new MM_Column();
        $block = new MM_Block();
        $config = new MM_Config();
        $this->smarty->assign(array(
            'menuForm' => $menu->renderForm($this->context),
            'columnForm' => $column->renderForm($this->context),
            'tabForm' => $tab->renderForm($this->context),
            'blockForm' => $block->renderForm($this->context),

            'configForm' => $config->renderForm($this->context),
            'menus' => MM_Menu::getMenus($this->context, false),
            'mmBaseAdminUrl' => $this->baseAdminUrl(),
            'layoutDirection' => $this->layoutDirection(),
            'multiLayout' => MM_Obj::multiLayoutExist(),
            'mm_img_dir' => $this->_path . 'views/img/',
            'mm_backend_layout' => $this->context->language->is_rtl ? 'rtl' : 'ltr',
            'iconForm' => $this->display(__FILE__, 'admin-icon.tpl'),
        ));
        return $this->display(__FILE__, 'admin-form.tpl');
    }
    public function baseAdminUrl()
    {
        return $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name;
    }
    public function getFormattedName($name)
    {
        $themeName = $this->context->shop->theme_name;
        $nameWithoutThemeName = str_replace(['_' . $themeName, $themeName . '_'], '', $name);

        //check if the theme name is already in $name if yes only return $name
        if ($themeName !== null && strstr($name, $themeName) && ImageType::getByNameNType($name)) {
            return $name;
        }

        if (ImageType::getByNameNType($nameWithoutThemeName . '_' . $themeName)) {
            return $nameWithoutThemeName . '_' . $themeName;
        }

        if (ImageType::getByNameNType($themeName . '_' . $nameWithoutThemeName)) {
            return $themeName . '_' . $nameWithoutThemeName;
        }

        return $nameWithoutThemeName . '_default';
    }
    public function getMmType($name = false)
    {
        $mmType = Configuration::get('ETS_MM_IMAGE_TYPE');
        if (!$mmType && ($imageTypes = $this->imageTypes(true)) && isset($imageTypes[1]) && $imageTypes[1])
            $mmType = $imageTypes[1];
        if ($name)
            $nameType = ImageType::typeAlreadyExists($name) ? $name : $mmType;
        if (!(isset($nameType)) || !$nameType)
            $nameType = $mmType;
        return $this->getFormattedName($nameType);
    }
    public function saveDataConfig($config)
    {
        /** @var MM_Config  $config */
        $errors = array();
        $success = array();
        $languages = Language::getLanguages(false);
        $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
        $fields = $config->getFormField();
        $configs = $fields['configs'];
        if($configs)
        {
            foreach($configs as $key => $config)
            {
                $value_key = Tools::getValue($key);
                if(isset($config['lang']) && $config['lang'])
                {
                    $value_key_lang_default = trim(Tools::getValue($key.'_'.$id_lang_default));
                    if(isset($config['required']) && $config['required'] && $config['type']!='switch' && $value_key_lang_default == '')
                    {
                        $errors[] = sprintf($this->l('%s is required'),$config['label']);
                    }
                    elseif($value_key_lang_default && !Validate::isCleanHtml($value_key_lang_default))
                        $errors[] = sprintf($this->l('%s is not valid'),$config['label']);
                }
                else
                {
                    if(isset($config['required']) && $config['required'] && isset($config['type']) && $config['type']=='file')
                    {
                        if(Configuration::get($key)=='' && (!isset($_FILES[$key]['size']) || isset($_FILES[$key]['size']) && !$_FILES[$key]['size']))
                            $errors[] = sprintf($this->l('%s is required'),$config['label']);
                        elseif(isset($_FILES[$key]['size']))
                        {
                            $fileSize = round((int)$_FILES[$key]['size'] / (1024 * 1024));
                            if($fileSize > 100)
                                $errors[] = sprintf($this->l('%s upload file cannot be larger than %sMB'), $config['label'], Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'));
                        }
                    }
                    else
                    {
                        if(isset($config['required']) && $config['required'] && $config['type']!='switch' && trim($value_key) == '')
                        {
                            $errors[] = sprintf($this->l('%s is required'),$config['label']);
                        }
                        elseif(!is_array($value_key) && isset($config['validate']) && method_exists('Validate',$config['validate']))
                        {
                            $validate = $config['validate'];
                            if(trim($value_key) && !Validate::$validate(trim($value_key)))
                                $errors[] = sprintf($this->l('%s is not valid'),$config['label']);
                            unset($validate);
                        }
                        elseif(!Validate::isCleanHtml(trim($value_key)))
                        {
                            $errors[] = sprintf($this->l('%s is not valid'),$config['label']);
                        }
                    }
                }
            }
        }
        if(!$errors)
        {
            if($configs)
            {
                foreach($configs as $key => $config)
                {
                    $value_key = Tools::getValue($key);
                    if(isset($config['lang']) && $config['lang'])
                    {
                        $valules = array();
                        $value_key_lang_default  = trim(Tools::getValue($key.'_'.$id_lang_default));
                        foreach($languages as $lang)
                        {
                            $value_key_lang = trim(Tools::getValue($key.'_'.$lang['id_lang']));
                            if($config['type']=='switch')
                                $valules[$lang['id_lang']] = (int)$value_key_lang ? 1 : 0;
                            else
                                $valules[$lang['id_lang']] = $value_key_lang ? : (Validate::isCleanHtml($value_key_lang_default) ? $value_key_lang_default:'');
                        }
                        Configuration::updateValue($key,$valules,true);
                    }
                    elseif($config['type']=='switch')
                    {
                        Configuration::updateValue($key,(int)$value_key ? 1 : 0);
                    }
                    elseif($config['type']=='file')
                    {
                        if(isset($_FILES[$key]['tmp_name']) && isset($_FILES[$key]['name']) && $_FILES[$key]['name'])
                        {
                            $_FILES[$key]['name'] = str_replace(array(' ','(',')','!','@','#','+'),'_',$_FILES[$key]['name']);
                            $salt = Tools::substr(sha1(microtime()),0,10);
                            $type = Tools::strtolower(Tools::substr(strrchr($_FILES[$key]['name'], '.'), 1));
                            $imageName = @file_exists(_PS_ETS_MM_IMG_DIR_.Tools::strtolower($_FILES[$key]['name'])) ? $salt.'-'.Tools::strtolower($_FILES[$key]['name']) : Tools::strtolower($_FILES[$key]['name']);
                            $fileName = _PS_ETS_MM_IMG_DIR_.$imageName;
                            $max_file_size = Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')*1024*1024;
                            if(!Validate::isFileName($_FILES[$key]['name']))
                                $errors[] = sprintf($this->l('%s is not valid'),$config['label']);
                            elseif($_FILES[$key]['size'] > $max_file_size)
                                $errors[] = sprintf($this->l('%s upload file cannot be larger than %sMB'),$config['label'],(int)Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'));
                            elseif(file_exists($fileName))
                            {
                                $errors[] = sprintf($this->l('%s file name already exists. Try to rename the file and upload again'),$config['label']);
                            }
                            else
                            {
                                $imagesize = @getimagesize($_FILES[$key]['tmp_name']);
                                if (!$errors && isset($_FILES[$key]) &&
                                    !empty($_FILES[$key]['tmp_name']) &&
                                    !empty($imagesize) &&
                                    in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
                                )
                                {
                                    $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                                    if ($error = ImageManager::validateUpload($_FILES[$key]))
                                        $errors[] = $error;
                                    elseif (!$temp_name || !move_uploaded_file($_FILES[$key]['tmp_name'], $temp_name))
                                        $errors[] = $this->l('Cannot upload file');
                                    elseif (!ImageManager::resize($temp_name, $fileName, null, null, $type))
                                        $errors[] = $this->l('An error occurred during the image upload process.');
                                    if (file_exists($temp_name))
                                        Ets_megamenu_defines::unlink($temp_name);
                                    if(!$errors)
                                    {
                                        if(Configuration::get($key)!='')
                                        {
                                            $oldImage = _PS_ETS_MM_IMG_DIR_.Configuration::get($key);
                                            if(file_exists($oldImage))
                                                Ets_megamenu_defines::unlink($oldImage);
                                        }
                                        Configuration::updateValue($key,$imageName);
                                    }
                                }
                            }
                        }
                    }
                    elseif($config['type']=='categories' && Ets_megamenu::validateArray($value_key) && isset($config['tree']['use_checkbox']) && $config['tree']['use_checkbox'] || $config['type']=='checkbox')
                        Configuration::updateValue($key,$value_key ? implode(',',$value_key):'');
                    elseif(Validate::isCleanHtml($value_key))
                        Configuration::updateValue($key,$value_key);
                }
            }
        }
        if(!$errors)
        {
            $success[] = $this->l('Saved');
            Ets_megamenu::clearAllCache();
        }
        return array('errors' => $errors, 'success' => $success);
    }
    public function saveDataObj($obj)
    {
        /** @var MM_Menu  $obj */
        $formFields = $obj->getFormField();
        $errors = array();
        $success = array();
        $languages = Language::getLanguages(false);
        $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
        $parent= isset($formFields['form']['parent']) ? $formFields['form']['parent']:'1';
        $configs = $formFields['configs'];
        if($configs)
        {
            foreach($configs as $key => $config)
            {
                $value_key = Tools::getValue($key);
                if($config['type']=='sort_order')
                    continue;
                if(isset($config['lang']) && $config['lang'])
                {
                    $key_value_lang_default = trim(Tools::getValue($key.'_'.$id_lang_default));
                    if(isset($config['required']) && $config['required'] && $config['type']!='switch' && $key_value_lang_default == '')
                    {
                        $errors[] = sprintf($this->l('%s is required'),$config['label']);
                    }
                    elseif(!Validate::isCleanHtml($key_value_lang_default,true))
                        $errors[] = sprintf($this->l('%s is not valid'),$config['label']);
                }
                else
                {
                    if(isset($config['required']) && $config['required'] && isset($config['type']) && $config['type']=='file')
                    {
                        if($obj->$key=='' && !isset($_FILES[$key]['size']))
                            $errors[] = sprintf($this->l('%s is required'),$config['label']);
                        elseif(isset($_FILES[$key]['size']))
                        {
                            $fileSize = round((int)$_FILES[$key]['size'] / (1024 * 1024));
                            if($fileSize > 100)
                                $errors[] = sprintf($this->l('%s upload file cannot be larger than %sMB'), $config['label'],Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'));
                        }
                    }
                    elseif(isset($config['required']) && $config['required'] && isset($config['type']) && $config['type']=='file_lang')
                    {
                        $key_lang_default = $key.'_'.$id_lang_default;
                        if($obj->$key=='' && !isset($_FILES[$key_lang_default]['size']))
                            $errors[] = sprintf($this->l('%s is required'),$config['label']);
                        elseif(isset($_FILES[$key_lang_default]['size']))
                        {
                            $fileSize = round((int)$_FILES[$key_lang_default]['size'] / (1024 * 1024));
                            if($fileSize > 100)
                                $errors[] = sprintf($this->l('%s upload file cannot be larger than %sMB'), $config['label'],Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'));
                        }
                    }
                    else
                    {
                        if(isset($config['required']) && $config['required'] && $config['type']!='switch' && !is_array($value_key) && trim($value_key) == '')
                        {
                            $errors[] = sprintf($this->l('%s is required'),$config['label']);
                        }
                        elseif(!is_array($value_key) && isset($config['validate']) && method_exists('Validate',$config['validate']))
                        {
                            $validate = $config['validate'];
                            if(!Validate::$validate(trim($value_key)))
                                $errors[] = sprintf($this->l('%s is not valid'),$config['label']);
                            unset($validate);
                        }
                        elseif(!is_array($value_key)  && !Validate::isCleanHtml(trim($value_key)))
                        {
                            $errors[] = sprintf($this->l('%s is not valid'),$config['label']);
                        }
                    }
                }
            }
        }

        if($formFields['form']['name']=='menu')
        {
            $link_type = Tools::getValue('link_type');
            switch($link_type)
            {
                case 'CUSTOM':
                    $link = trim(Tools::getValue('link_'.$id_lang_default));
                    if($link=='')
                        $errors[] = $this->l('Custom link is required');
                    elseif($link && $link!='#' && !Validate::isCleanHtml($link))
                        $errors[] = $this->l('Custom link is not valid');
                    break;
                case 'CMS':
                    $id_cms = (int)Tools::getValue('id_cms');
                    if(!$id_cms)
                        $errors[] = $this->l('CMS page is required');
                    break;
                case 'CATEGORY':
                    $id_category = (int)Tools::getValue('id_category');
                    if(!$id_category)
                        $errors[] = $this->l('Please select a category');
                    break;
                case 'MNFT':
                    $id_manufacturer = (int)Tools::getValue('id_manufacturer');
                    if(!$id_manufacturer)
                        $errors[] = $this->l('Please select a manufacturer');
                    break;
                case 'MNSP':
                    $id_supplier = (int)Tools::getValue('id_supplier');
                    if(!$id_supplier)
                        $errors[] = $this->l('Please select a supplier');
                    break;
                case 'CONTACT':
                    break;
                case 'HOME':
                    break;
                default:
                    $errors[] = $this->l('Link type is not valid');
                    break;
            }
            $sub_menu_max_width = Tools::getValue('sub_menu_max_width');
            if(Tools::strlen($sub_menu_max_width) <1 || Tools::strlen($sub_menu_max_width) > 50 || !Validate::isCleanHtml($sub_menu_max_width))
                $errors[] = $this->l('Sub menu width must be between 10 and 100');
            foreach($languages as $lang)
            {
                if($bubble_text = Tools::getValue('bubble_text_'.$lang['id_lang']))
                {
                    if(Tools::strlen($bubble_text) > 50 || !Validate::isCleanHtml($bubble_text))
                    {
                        $errors[] = $this->l('Bubble text cannot be longer than 50 characters');
                    }
                    $bubble_text_entered = true;
                }

            }
            if(!empty($bubble_text_entered))
            {
                $bubble_background_color = Tools::getValue('bubble_background_color');
                $bubble_text_color = Tools::getValue('bubble_text_color');
                if(!$bubble_text_color)
                    $errors[] = $this->l('Bubble alert text color is required');
                elseif(!Validate::isColor($bubble_text_color))
                    $errors[] = $this->l('Bubble alert text color is not valid');
                if(!$bubble_background_color)
                    $errors[] = $this->l('Bubble alert background color is required');
                elseif(!Validate::isColor($bubble_background_color))
                    $errors[] = $this->l('Bubble alert background color is not valid');
            }

        }
        if($formFields['form']['name']=='tab')
        {
            $link_type = Tools::getValue('link_type');
            switch($link_type)
            {
                case 'CUSTOM':
                    $url = trim(Tools::getValue('url_'.$id_lang_default));
                    if($url=='')
                        $errors[] = $this->l('Custom link is required');
                    elseif($url && $url!='#' && !Validate::isCleanHtml($url))
                        $errors[] = $this->l('Custom link is not valid');
                    break;
                case 'CMS':
                    $id_cms = (int)Tools::getValue('id_cms');
                    if(!$id_cms)
                        $errors[] = $this->l('CMS page is required');
                    break;
                case 'CATEGORY':
                    $id_category = (int)Tools::getValue('id_category');
                    if(!$id_category)
                        $errors[] = $this->l('Please select a category');
                    break;
                case 'MNFT':
                    $id_manufacturer = (int)Tools::getValue('id_manufacturer');
                    if(!$id_manufacturer)
                        $errors[] = $this->l('Please select a manufacturer');
                    break;
                case 'MNSP':
                    $id_supplier = (int)Tools::getValue('id_supplier');
                    if(!$id_supplier)
                        $errors[] = $this->l('Please select a supplier');
                    break;
                case 'CONTACT':
                    break;
                case 'HOME':
                    break;
                default:
                    $errors[] = $this->l('Link type is not valid');
                    break;
            }
        }
        if($formFields['form']['name']=='block')
        {
            $block_type = Tools::getValue('block_type');
            switch($block_type)
            {
                case 'HTML':
                    $content = trim(Tools::getValue('content_'.$id_lang_default));
                    if($content=='' || !Validate::isCleanHtml($content,true))
                        $errors[] = $this->l('HTML/Text is required');
                    break;
                case 'CMS':
                    $id_cmss = Tools::getValue('id_cmss');
                    if(!$id_cmss || !Ets_megamenu::validateArray($id_cmss))
                        $errors[] = $this->l('CMS pages is required');
                    break;
                case 'CATEGORY':
                    $id_categories = Tools::getValue('id_categories');
                    if(!$id_categories || !Ets_megamenu::validateArray($id_categories))
                        $errors[] = $this->l('Categories are required');
                    break;
                case 'MNFT':
                    $id_manufacturers = Tools::getValue('id_manufacturers');
                    if(!$id_manufacturers || !Ets_megamenu::validateArray($id_manufacturers))
                        $errors[] = $this->l('Manufacturers are required');
                    break;
                case 'MNSP':
                    $id_suppliers = Tools::getValue('id_suppliers');
                    if(!$id_suppliers || !Ets_megamenu::validateArray($id_suppliers))
                        $errors[] = $this->l('Suppliers are required');
                    break;
                case 'PRODUCT':
                    $product_type = Tools::getValue('product_type',false);
                    if ($product_type == 'specific')
                    {
                        $id_products = Tools::getValue('id_products',false);
                        if(!$id_products || !Ets_megamenu::validateArray($id_products))
                            $errors[] = $this->l('Please enter product IDs');
                    }
                    else
                    {
                        $product_count = Tools::getValue('product_count',false);
                        if(!$product_count)
                            $errors[] = $this->l('Product count required');
                        elseif(!Validate::isUnsignedId($product_count))
                            $errors[] = $this->l('Product count is not valid');
                    }
                    break;
                case 'IMAGE':
                    if((!$obj->image || !(isset($obj->image[$id_lang_default]) && $obj->image[$id_lang_default])) && (!isset($_FILES['image_'.$id_lang_default]['size']) || isset($_FILES['image_'.$id_lang_default]['size']) && !$_FILES['image_'.$id_lang_default]['size']))
                    {
                        $errors[] = $this->l('Image is required');
                    }
                    break;
                default:
                    $errors[] = $this->l('Block type is not valid');
                    break;
            }
        }
        $new_files = array();
        $old_files = array();
        if(!$errors)
        {
            if($configs)
            {
                foreach($configs as $key => $config)
                {
                    $value_key = Tools::getValue($key);
                    if(isset($config['type']) && $config['type']=='sort_order')
                    {
                        if(!$obj->id)
                        {
                            if(!isset($config['order_group'][$parent]) || isset($config['order_group'][$parent]) && !$config['order_group'][$parent])
                                $obj->$key = $obj->maxVal($key)+1;
                            else
                            {
                                $orderGroup = $config['order_group'][$parent];
                                $obj->$key = $obj->maxVal($key,$orderGroup,(int)$obj->$orderGroup)+1;
                            }
                        }
                    }
                    elseif(isset($config['lang']) && $config['lang'] && $config['type']!='file_lang')
                    {
                        $valules = array();
                        $key_value_lang_default = trim(Tools::getValue($key.'_'.$id_lang_default));
                        foreach($languages as $lang)
                        {
                            $key_value_lang = trim(Tools::getValue($key.'_'.$lang['id_lang']));
                            if($config['type']=='switch')
                                $valules[$lang['id_lang']] = (int)$key_value_lang ? 1 : 0;
                            elseif(Validate::isCleanHtml($key_value_lang,true))
                                $valules[$lang['id_lang']] = $key_value_lang ? : (Validate::isCleanHtml($key_value_lang_default,true) ? $key_value_lang_default:'');
                        }
                        $obj->$key = $valules;
                    }
                    elseif($config['type']=='switch')
                    {
                        $obj->$key = (int)$value_key ? 1 : 0;
                    }
                    elseif($config['type']=='file')
                    {
                        if(isset($_FILES[$key]['tmp_name']) && isset($_FILES[$key]['name']) && $_FILES[$key]['name'])
                        {
                            $_FILES[$key]['name'] = str_replace(array(' ','(',')','!','@','#','+'),'_',$_FILES[$key]['name']);
                            $salt = Tools::substr(sha1(microtime()),0,10);
                            $type = Tools::strtolower(Tools::substr(strrchr($_FILES[$key]['name'], '.'), 1));
                            $imageName = @file_exists(_PS_ETS_MM_IMG_DIR_.Tools::strtolower($_FILES[$key]['name']))|| Tools::strtolower($_FILES[$key]['name'])== $obj->$key ? $salt.'-'.Tools::strtolower($_FILES[$key]['name']) : Tools::strtolower($_FILES[$key]['name']);
                            $fileName = _PS_ETS_MM_IMG_DIR_.$imageName;
                            $max_file_size = Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')*1024*1024;
                            if(!Validate::isFileName($_FILES[$key]['name']))
                                $errors[] = sprintf($this->l('%s is not valid'),$config['label']);
                            elseif($_FILES[$key]['size'] > $max_file_size)
                                $errors[] = sprintf($this->l('%s upload file cannot be larger than %sMB'),$config['label'], Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'));
                            elseif(file_exists($fileName))
                            {
                                $errors[] = sprintf($this->l('%s file name already exists. Try to rename the file and upload again'),$config['label']);
                            }
                            else
                            {
                                $imagesize = @getimagesize($_FILES[$key]['tmp_name']);
                                if (!$errors && isset($_FILES[$key]) &&
                                    !empty($_FILES[$key]['tmp_name']) &&
                                    !empty($imagesize) &&
                                    in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
                                )
                                {
                                    $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                                    if ($error = ImageManager::validateUpload($_FILES[$key]))
                                        $errors[] = $error;
                                    elseif (!$temp_name || !move_uploaded_file($_FILES[$key]['tmp_name'], $temp_name))
                                        $errors[] = $this->l('Cannot upload file');
                                    elseif (!ImageManager::resize($temp_name, $fileName, null, null, $type))
                                        $errors[] = $this->l('An error occurred during the image upload process.');
                                    if (file_exists($temp_name))
                                        Ets_megamenu_defines::unlink($temp_name);
                                    if(!$errors)
                                    {
                                        if($obj->$key!='')
                                        {
                                            $oldImage = _PS_ETS_MM_IMG_DIR_.$obj->$key;
                                            if(file_exists($oldImage) && !MM_Obj::imageExists($obj->$key,$obj->id))
                                                Ets_megamenu_defines::unlink($oldImage);
                                        }
                                        $obj->$key = $imageName;
                                    }
                                }
                                else
                                    $errors[] = sprintf($this->l('%s file is not in the correct format, accepted formats: jpg, gif, jpeg, png.'),$config['label']);
                            }
                        }
                    }
                    elseif($config['type']=='file_lang')
                    {
                        foreach($languages as $l)
                        {
                            $key_lang = $key.'_'.$l['id_lang'];
                            if(isset($_FILES[$key_lang]['tmp_name']) && isset($_FILES[$key_lang]['name']) && $_FILES[$key_lang]['name'])
                            {
                                $_FILES[$key_lang]['name'] = str_replace(array(' ','(',')','!','@','#','+'),'_',$_FILES[$key_lang]['name']);
                                $file_name = $_FILES[$key_lang]['name'];
                                $salt = Tools::substr(sha1(microtime()),0,10);
                                $type = Tools::strtolower(Tools::substr(strrchr($file_name, '.'), 1));
                                $imageName = @file_exists(_PS_ETS_MM_IMG_DIR_.Tools::strtolower($file_name)) || ($obj->$key && in_array($file_name,$obj->$key)) ? $salt.'-'.Tools::strtolower($file_name) : Tools::strtolower($file_name);
                                $fileDir = _PS_ETS_MM_IMG_DIR_.'/'.$imageName;
                                $max_file_size = Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')*1024*1024;
                                if(!Validate::isFileName($file_name))
                                    $errors[] =sprintf($this->l('%s is not valid'),$config['label']);
                                elseif($_FILES[$key_lang]['size'] > $max_file_size)
                                    $errors[] = sprintf($this->l('%s file is too large'),$config['label']);
                                elseif(file_exists($fileDir))
                                {
                                    $errors[] = sprintf($this->l('%s file existed'),$config['label']);
                                }
                                else
                                {
                                    $imagesize = @getimagesize($_FILES[$key_lang]['tmp_name']);
                                    if (!$errors && isset($_FILES[$key_lang]) &&
                                        !empty($_FILES[$key_lang]['tmp_name']) &&
                                        !empty($imagesize) &&
                                        in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
                                    )
                                    {
                                        $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                                        if ($error = ImageManager::validateUpload($_FILES[$key_lang]))
                                            $errors[] = $error;
                                        elseif (!$temp_name || !move_uploaded_file($_FILES[$key_lang]['tmp_name'], $temp_name))
                                            $errors[] = sprintf($this->l('%s cannot upload file','Ets_blog_obj'),$config['label']);
                                        elseif (!ImageManager::resize($temp_name, $fileDir, null, null, $type))
                                            $errors[] = sprintf($this->l('%s an error occurred during the image upload process'),$config['label']);
                                        else
                                        {
                                            if(isset($obj->{$key}[$l['id_lang']]) && $obj->{$key}[$l['id_lang']]!='')
                                            {
                                                $old_file = $obj->{$key}[$l['id_lang']];
                                            }
                                            else
                                                $old_file = '';
                                            $obj->{$key}[$l['id_lang']] = $imageName;
                                            $new_files[] = $imageName;
                                            if($old_file && !in_array($old_file,$obj->{$key}))
                                                $old_files[] = $old_file;
                                        }
                                        if (file_exists($temp_name))
                                            Ets_megamenu_defines::unlink($temp_name);

                                    }
                                    else
                                        $errors[] = sprintf($this->l('%s file is not in the correct format, accepted formats: jpg, gif, jpeg, png','Ets_blog_obj'),$config['label']);
                                }
                            }
                        }
                        foreach($languages as $l)
                        {
                            if(!isset($obj->{$key}[$l['id_lang']]) || !$obj->{$key}[$l['id_lang']])
                                $obj->{$key}[$l['id_lang']] = isset($obj->{$key}[$id_lang_default]) ? $obj->{$key}[$id_lang_default]:'';
                        }
                    }
                    elseif($config['type']=='categories' && Ets_megamenu::validateArray($value_key) && isset($config['tree']['use_checkbox']) && $config['tree']['use_checkbox'] || $config['type']=='checkbox')
                        $obj->{$key} = $value_key ? implode(',',$value_key):'';
                    elseif(Validate::isCleanHtml($value_key))
                        $obj->{$key} = trim($value_key);
                }
            }
        }
        if (!count($errors))
        {

            if($obj->id && $obj->update() || !$obj->id && $obj->add(true, false, $this->context->shop->id))
            {
                Ets_megamenu::clearAllCache();
                if($old_files)
                {
                    foreach($old_files as $old_file)
                        Ets_megamenu_defines::unlink(_PS_ETS_MM_IMG_DIR_.$old_file);
                }
                $success[] = $this->l('Saved');
            }
            else
            {
                if($new_files)
                {
                    foreach($new_files as $new_file)
                    {
                        Ets_megamenu_defines::unlink(_PS_ETS_MM_IMG_DIR_.$new_file);
                    }
                }
                $errors[] = $this->l('Unknown error happened');
            }
        }
        return array('errors' => $errors, 'success' => $success);
    }
    public function proccessPost()
    {
        $this->alerts = array();
        if (($query = Tools::getValue('q', false)) && Validate::isCleanHtml($query)) {
            $imageType = $this->getMmType('cart');
            $excludeIds = Tools::getValue('excludeIds', false);
            $excludedProductIds = array();
            if ($excludeIds && $excludeIds != 'NaN' && Validate::isCleanHtml($excludeIds)) {
                $excludeIds = implode(',', array_map(array($this, 'isValidIds'), explode(',', $excludeIds)));
                $ids = explode(',', $excludeIds);
                foreach ($ids as $id) {
                    $id = explode('-', $id);
                    if (isset($id[0]) && isset($id[1]) && !$id[1]) {
                        $excludedProductIds[] = (int)$id[0];
                    }
                }
            } else {
                $excludeIds = false;
            }
            $excludeVirtuals = (bool)Tools::getValue('excludeVirtuals', false);
            $exclude_packs = (bool)Tools::getValue('exclude_packs', false);
            Ets_megamenu_defines::searchProduct($this->context, $query,$excludedProductIds,$excludeVirtuals,$exclude_packs,$excludeIds,$imageType);
        }
        $product_type = Tools::getValue('product_type', false);
        if ($product_type && Validate::isCleanHtml($product_type) && ($IDs = Tools::getValue('ids', false)) && Ets_megamenu::validateArray($IDs)) {
            die(json_encode(array(
                'html' => $this->hookDisplayMMProductList(array('ids' => $IDs)),
            )));
        }
        $time = time();
        $mm_object = Tools::getValue('mm_object');
        if (Tools::isSubmit('mm_form_submitted') && ($mmObj = $mm_object) && in_array($mmObj, array('MM_Menu', 'MM_Column', 'MM_Block', 'MM_Tab'))) {
            /** @var MM_Menu $obj */
            $obj = ($itemId = (int)Tools::getValue('itemId')) && $itemId > 0 ? new $mmObj($itemId) : new $mmObj();

            $this->alerts = $this->saveDataObj($obj);
            $vals = $obj->getFieldVals($this->context);

            $params = array();
            switch ($mmObj) {
                case 'MM_Menu':
                    $params['menu'] = MM_Menu::getMenus($this->context, false, false, $obj->id);
                    $vals['html_content'] = $this->hookDisplayMMItemMenu($params);
                    break;
                case 'MM_Tab':
                    $params['tab'] = MM_Tab::getTabs($this->context, false, $obj->id);
                    $vals['html_content'] = $this->hookDisplayMMItemTab($params);
                    break;
                case 'MM_Column':
                    $params['column'] = MM_Column::getColumns(false, $obj->id, $this->context->language->id);
                    $vals['html_content'] = $this->hookDisplayMMItemColumn($params);
                    break;
                case 'MM_Block':
                    $params['block'] = MM_Block::getBlocks($this->context->language->id, false, $obj->id);
                    $vals['html_content'] = $this->hookDisplayMMItemColumn($params);
                    break;
            }
            if ($obj->id && $mmObj == 'MM_Block')
                $vals['blockHtml'] = $this->hookDisplayBlock(array('block' => MM_Block::getBlockById($obj->id, $this->context->language->id)));
            $formField = $obj->getFormField();
            die(json_encode(array(
                'alert' => $this->displayAlerts($time),
                'itemId' => (int)$obj->id,
                'title' => property_exists($obj, 'title') && isset($obj->title[(int)$this->context->language->id]) ? $obj->title[(int)$this->context->language->id] : false,
                'images' =>  property_exists($obj, 'image') && $obj->image && isset($obj->image[$this->context->language->id]) ? array(array(
                    'name' => 'image',
                    'url' => _PS_ETS_MM_IMG_ . $obj->image[$this->context->language->id],
                )) : false,
                'img_dir' => _PS_ETS_MM_IMG_,
                'menu_icon' => $obj->id && property_exists($obj, 'menu_icon') && $obj->menu_icon ? $obj->menu_icon : '',
                'itemKey' => 'id_' . $formField['form']['name'],
                'time' => $time,
                'id_menu' => ($id_menu = (int)Tools::getValue('id_menu')) ? $id_menu : false,
                'mm_object' => $mmObj,
                'vals' => $vals,
                'success' => isset($this->alerts['success']) && $this->alerts['success'],
            )));
        }

        if (($image = Tools::getValue('deleteimage')) && Validate::isCleanHtml($image) && ($mmObj = $mm_object) && in_array($mmObj, array('MM_Menu', 'MM_Column', 'MM_Block', 'MM_Tab'))) {
            if (($itemId = (int)Tools::getValue('itemId')) && $itemId > 0) {
                /** @var MM_Menu  $obj */
                $obj = new $mmObj($itemId);
                $this->alerts = $obj->clearImage($image);
                die(json_encode(array(
                    'alert' => $this->displayAlerts($time),
                    'itemId' => (int)$obj->id,
                    'itemKey' => 'image',
                    'time' => $time,
                    'success' => isset($this->alerts['success']) && $this->alerts['success'],
                )));
            } else
                die(json_encode(array(
                    'alert' => true,
                    'itemId' => 0,
                    'itemKey' => 'image',
                    'time' => true,
                    'success' => true,
                )));
        } elseif (($image = Tools::getValue('deleteimage')) && Validate::isCleanHtml($image) && isset($mmObj) && $mmObj == 'MM_Config') {
            if (file_exists(_PS_ETS_MM_IMG_DIR_ . Configuration::get($image)))
                Ets_megamenu_defines::unlink(_PS_ETS_MM_IMG_DIR_ . Configuration::get($image));
            Configuration::updateValue($image, '');
            $this->alerts = array(
                'errors' => false,
                'success' => array
                (
                    $this->l('Delete image successfully')
                ),
            );
            die(json_encode(array(
                'alert' => $this->displayAlerts($time),
                'itemId' => 1,
                'itemKey' => 'image',
                'time' => $time,
                'success' =>$this->alerts['success'],
            )));
        }
        if ((Tools::isSubmit('deleteobject')) && ($mmObj = $mm_object) && in_array($mmObj, array('MM_Menu', 'MM_Column', 'MM_Block', 'MM_Tab')) && ($itemId = (int)Tools::getValue('itemId')) && $itemId > 0) {
            /** @var MM_Menu $obj */
            $obj = new $mmObj($itemId);
            $this->alerts = $obj->deleteObj();
            die(json_encode(array(
                'alert' => $this->displayAlerts($time),
                'time' => $time,
                'itemId' => $itemId,
                'success' => isset($this->alerts['success']) && $this->alerts['success'],
                'successMsg' => isset($this->alerts['success']) && $this->alerts['success'] ? $this->l('Item deleted') : false,
            )));
        }
        $mmObj = 'MM_' . Tools::ucfirst(Tools::strtolower($mm_object));
        if ((Tools::isSubmit('duplicateItem')) && in_array($mmObj, array('MM_Menu', 'MM_Column', 'MM_Block', 'MM_Tab')) && ($itemId = (int)Tools::getValue('itemId')) && $itemId > 0) {
            /** @var MM_Menu $obj */
            $obj = new $mmObj($itemId);
            if ($newObj = $obj->duplicateItem(false, false, $this->context->shop->id)) {
                switch ($mmObj) {
                    case 'MM_Menu':
                        $menu = MM_Menu::getMenus($this->context, false, false, $newObj->id);
                        $html = $this->hookDisplayMMItemMenu(array('menu' => $menu, 'have_li' => true));
                        break;
                    case 'MM_Tab':
                        $tab = MM_Tab::getTabs($this->context, false, $newObj->id);
                        $html = $this->hookDisplayMMItemTab(array('tab' => $tab, 'have_li' => true));
                        break;
                    case 'MM_Column':
                        $column = MM_Column::getColumns(false, $newObj->id, $this->context->language->id);
                        $html = $this->hookDisplayMMItemColumn(array('column' => $column, 'have_li' => true));
                        break;
                    case 'MM_Block':
                        $block = MM_Block::getBlocks($this->context->language->id, false, false, $newObj->id);
                        $html = $this->hookDisplayMMItemBlock(array('block' => $block, 'have_li' => true));
                        break;
                    default:
                        break;
                }
            }
            die(json_encode(array(
                'alerts' => $newObj ? array('success' => $this->l('Item duplicated')) : array('errors' => $this->l('Cannot duplicate item. An unknown problem happened')),
                'time' => $time,
                'itemId' => $itemId,
                'newItemId' => $newObj->id,
                'mm_object' => $mm_object,
                'html' => isset($html) ? $html : '',
            )));
        }
        if (Tools::isSubmit('mm_config_submitted')) {
            $this->configExtra();
        }
        if (Tools::isSubmit('updateOrder')) {
            $itemId = (int)Tools::getValue('itemId');
            $objName = 'MM_' . Tools::ucfirst(Tools::strtolower(trim(Tools::getValue('obj'))));
            $parentId = (int)Tools::getValue('parentId') > 0 ? (int)Tools::getValue('parentId') : 0;
            $previousId = (int)Tools::getValue('previousId');
            $parentObj = Tools::getValue('parentObj');
            $result = false;
            if (in_array($objName, array('MM_Menu', 'MM_Column', 'MM_Block', 'MM_Tab')) && $itemId > 0 && (Validate::isCleanHtml($parentObj) || !$parentObj)) {
                /** @var MM_Menu $obj */
                $obj = new $objName($itemId);
                $result = $obj->updateOrder($previousId, $parentId, $parentObj);
            }
            die(json_encode(array(
                'success' => $result
            )));
        }
        if (Tools::isSubmit('clearMenuCache')) {
            $this->clearAllCache(true);
            die(json_encode(array(
                'success' => $this->l('Cache cleared'),
            )));
        }
        if (Tools::isSubmit('exportMenu')) {
            $this->generateArchive();
        }
        if (Tools::isSubmit('importMenu')) {
            $errors = $this->processImport();
            die(json_encode(array(
                'success' => !$errors ? $this->l('Menu was successfully imported. This page will be reloaded in 3 seconds') : false,
                'error' => $errors ? implode('; ', $errors) : false,
            )));
        }
        if (Tools::isSubmit('reset_config')) {
            $configuration = new MM_Config();
            $configuration->installConfigs();
            die(json_encode(array(
                'success' => $this->l('Configuration was successfully restored. This page will be reloaded in 3 seconds'),
            )));
        }
    }

    public function enable($force_all = false)
    {
        return parent::enable($force_all) && $this->configExtra(true);
    }

    public function configExtra($reConfig = false)
    {
        if (!$reConfig) {
            $time = time();
            $config = new MM_Config();
        }
        $ETS_MM_DISPLAY_CUSTOMER_INFO = Configuration::get('ETS_MM_DISPLAY_CUSTOMER_INFO');
        $ETS_MM_DISPLAY_SEARCH = Configuration::get('ETS_MM_DISPLAY_SEARCH');
        $ETS_MM_DISPLAY_SHOPPING_CART = Configuration::get('ETS_MM_DISPLAY_SHOPPING_CART');
        if (!$reConfig) {
            $this->alerts = $this->saveDataConfig($config);
        }
        if ($this->is17) {
            if ($reConfig || $ETS_MM_DISPLAY_CUSTOMER_INFO != Configuration::get('ETS_MM_DISPLAY_CUSTOMER_INFO')) {
                $ps_customersignin = Module::getInstanceByName('ps_customersignin');
                if (Configuration::get('ETS_MM_DISPLAY_CUSTOMER_INFO') && $ps_customersignin) {
                    $id_hook = Hook::getIdByName('displayNav2');
                    Configuration::updateValue('ETS_MM_POSITION_USERINFOR', $ps_customersignin->getPosition($id_hook));
                    $ps_customersignin->unregisterHook('displayNav2');
                    $ps_customersignin->registerHook('displayCustomerInforTop');
                } elseif ($ps_customersignin) {
                    $ps_customersignin->registerHook('displayNav2');
                    $id_hook = Hook::getIdByName('displayNav2');
                    if ($position = (int)Configuration::get('ETS_MM_POSITION_USERINFOR'))
                        $ps_customersignin->updatePosition($id_hook, false, $position);
                    $ps_customersignin->unregisterHook('displayCustomerInforTop');
                }
            }
            if ($reConfig || $ETS_MM_DISPLAY_SEARCH != Configuration::get('ETS_MM_DISPLAY_SEARCH')) {
                $ps_searchbar = Module::getInstanceByName('ps_searchbar');
                if (Configuration::get('ETS_MM_DISPLAY_SEARCH') && $ps_searchbar) {
                    $id_hook = Hook::getIdByName('displayTop');
                    Configuration::updateValue('ETS_MM_POSITION_BLOCK_SEARCH', $ps_searchbar->getPosition($id_hook));
                    $ps_searchbar->unregisterHook('displayTop');
                } elseif ($ps_searchbar) {
                    $ps_searchbar->registerHook('displayTop');
                    $id_hook = Hook::getIdByName('displayTop');
                    if ($position = (int)Configuration::get('ETS_MM_POSITION_BLOCK_SEARCH'))
                        $ps_searchbar->updatePosition($id_hook, false, $position);
                }
            }

            if ($reConfig || $ETS_MM_DISPLAY_SHOPPING_CART != Configuration::get('ETS_MM_DISPLAY_SHOPPING_CART')) {
                $ps_shoppingcart = Module::getInstanceByName('ps_shoppingcart');
                if ($ps_shoppingcart && Configuration::get('ETS_MM_DISPLAY_SHOPPING_CART')) {
                    $id_hook = Hook::getIdByName('displayNav2');
                    Configuration::updateValue('ETS_MM_POSITION_BLOCKCART', $ps_shoppingcart->getPosition($id_hook));
                    $ps_shoppingcart->unregisterHook('displayNav2');
                    $ps_shoppingcart->registerHook('displayCartTop');
                } elseif ($ps_shoppingcart) {
                    $ps_shoppingcart->registerHook('displayNav2');
                    $id_hook = Hook::getIdByName('displayNav2');
                    if ($position = Configuration::get('ETS_MM_POSITION_BLOCKCART'))
                        $ps_shoppingcart->updatePosition($id_hook, false, (int)$position);
                    $ps_shoppingcart->unregisterHook('displayCartTop');
                }
            }
        } else {
            if ($reConfig || $ETS_MM_DISPLAY_SHOPPING_CART != Configuration::get('ETS_MM_DISPLAY_SHOPPING_CART')) {
                $blockcart = Module::getInstanceByName('blockcart');
                if (Configuration::get('ETS_MM_DISPLAY_SHOPPING_CART') && $blockcart) {
                    if ($blockcart->isRegisteredInHook('top')) {
                        Configuration::updateValue('ETS_MM_HOOK_BLOCKCART', 'top');
                        $id_hook = Hook::getIdByName('top');
                        Configuration::updateValue('ETS_MM_POSITION_BLOCKCART', $blockcart->getPosition($id_hook));
                        $blockcart->unregisterHook('top');
                    } elseif ($blockcart->isRegisteredInHook('displayTop')) {
                        Configuration::updateValue('ETS_MM_HOOK_BLOCKCART', 'displayTop');
                        $id_hook = Hook::getIdByName('displayTop');
                        Configuration::updateValue('ETS_MM_POSITION_BLOCKCART', $blockcart->getPosition($id_hook));
                        $blockcart->unregisterHook('displayTop');
                    } elseif ($blockcart->isRegisteredInHook('displayNav')) {
                        Configuration::updateValue('ETS_MM_HOOK_BLOCKCART', 'displayNav');
                        $id_hook = Hook::getIdByName('displayNav');
                        Configuration::updateValue('ETS_MM_POSITION_BLOCKCART', $blockcart->getPosition($id_hook));
                        $blockcart->unregisterHook('displayNav');
                    }

                } elseif ($blockcart) {
                    $hook = Configuration::get('ETS_MM_HOOK_BLOCKCART') ? Configuration::get('ETS_MM_HOOK_BLOCKCART') : 'top';
                    $blockcart->registerHook($hook);
                    $id_hook = Hook::getIdByName($hook);
                    if ($position = (int)Configuration::get('ETS_MM_POSITION_BLOCKCART'))
                        $blockcart->updatePosition($id_hook, false, $position);
                }
            }
            if ($reConfig || $ETS_MM_DISPLAY_SEARCH != Configuration::get('ETS_MM_DISPLAY_SEARCH')) {
                $blocksearch = Module::getInstanceByName('blocksearch');
                if (Configuration::get('ETS_MM_DISPLAY_SEARCH') && $blocksearch) {
                    if ($blocksearch->isRegisteredInHook('top')) {
                        Configuration::updateValue('ETS_MM_HOOK_BLOCK_SEARCH', 'top');
                        $id_hook = Hook::getIdByName('top');
                        Configuration::updateValue('ETS_MM_POSITION_BLOCK_SEARCH', $blocksearch->getPosition($id_hook));
                        $blocksearch->unregisterHook('top');
                    } elseif ($blocksearch->isRegisteredInHook('displayTop')) {
                        Configuration::updateValue('ETS_MM_HOOK_BLOCK_SEARCH', 'displayTop');
                        $id_hook = Hook::getIdByName('displayTop');
                        Configuration::updateValue('ETS_MM_POSITION_BLOCK_SEARCH', $blocksearch->getPosition($id_hook));
                        $blocksearch->unregisterHook('displayTop');
                    } elseif ($blocksearch->isRegisteredInHook('displayNav')) {
                        Configuration::updateValue('ETS_MM_HOOK_BLOCK_SEARCH', 'displayNav');
                        $id_hook = Hook::getIdByName('displayNav');
                        Configuration::updateValue('ETS_MM_POSITION_BLOCK_SEARCH', $blocksearch->getPosition($id_hook));
                        $blocksearch->unregisterHook('displayNav');
                    }
                } elseif ($blocksearch) {
                    $hook = Configuration::get('ETS_MM_HOOK_BLOCK_SEARCH') ? Configuration::get('ETS_MM_HOOK_BLOCK_SEARCH') : 'top';
                    $id_hook = Hook::getIdByName($hook);
                    $blocksearch->registerHook($hook);
                    if ($position = (int)Configuration::get('ETS_MM_POSITION_BLOCK_SEARCH'))
                        $blocksearch->updatePosition($id_hook, false, $position);
                }
            }
            if ($reConfig || $ETS_MM_DISPLAY_CUSTOMER_INFO != Configuration::get('ETS_MM_DISPLAY_CUSTOMER_INFO')) {
                $blockuserinfo = Module::getInstanceByName('blockuserinfo');
                if (Configuration::get('ETS_MM_DISPLAY_CUSTOMER_INFO') && $blockuserinfo) {
                    if ($blockuserinfo->isRegisteredInHook('displayNav')) {
                        Configuration::updateValue('ETS_MM_HOOK_USERINFOR', 'displayNav');
                        $id_hook = Hook::getIdByName('displayNav');
                        Configuration::updateValue('ETS_MM_POSITION_USERINFOR', $blockuserinfo->getPosition($id_hook));
                        $blockuserinfo->unregisterHook('displayNav');
                    } elseif ($blockuserinfo->isRegisteredInHook('displayTop')) {
                        Configuration::updateValue('ETS_MM_HOOK_USERINFOR', 'displayTop');
                        $id_hook = Hook::getIdByName('displayTop');
                        Configuration::updateValue('ETS_MM_POSITION_USERINFOR', $blockuserinfo->getPosition($id_hook));
                        $blockuserinfo->unregisterHook('displayTop');
                    } elseif ($blockuserinfo->isRegisteredInHook('top')) {
                        Configuration::updateValue('ETS_MM_HOOK_USERINFOR', 'top');
                        $id_hook = Hook::getIdByName('top');
                        Configuration::updateValue('ETS_MM_POSITION_USERINFOR', $blockuserinfo->getPosition($id_hook));
                        $blockuserinfo->unregisterHook('top');
                    }
                } elseif ($blockuserinfo) {
                    $hook = Configuration::get('ETS_MM_HOOK_USERINFOR') ? Configuration::get('ETS_MM_HOOK_USERINFOR') : 'displayNav';
                    $id_hook = Hook::getIdByName($hook);
                    $blockuserinfo->registerHook($hook);
                    if ($position = (int)Configuration::get('ETS_MM_POSITION_USERINFOR'))
                        $blockuserinfo->updatePosition($id_hook, false, $position);
                }
            }
        }
        if (!$reConfig) {
            die(json_encode(array(
                'alert' => $this->displayAlerts($time),
                'time' => $time,
                'layout_direction' => $this->layoutDirection(),
                'success' => isset($this->alerts['success']) && $this->alerts['success'],
            )));
        } else
            return true;
    }

    public function requestForm()
    {
        if (Tools::isSubmit('request_form') && ($mmObj = Tools::getValue('mm_object')) && in_array($mmObj, array('MM_Menu', 'MM_Column', 'MM_Block', 'MM_Tab'))) {
            $obj = ($itemId = (int)Tools::getValue('itemId')) && $itemId > 0 ? new $mmObj($itemId) : new $mmObj();
            die(json_encode(array(
                'form' => $obj->renderForm($this->context),
                'itemId' => $itemId,
            )));
        }
    }

    public function displayAdminJs()
    {
        $this->smarty->assign(array(
            'js_dir_path' => $this->_path . 'views/js/',
        ));
        return $this->display(__FILE__, 'admin-js.tpl');
    }

    public function displayAlerts($time)
    {
        $this->smarty->assign(array(
            'alerts' => $this->alerts,
            'time' => $time,
        ));
        return $this->display(__FILE__, 'admin-alerts.tpl');
    }

    public function hookDisplayBlock($params)
    {
        $isBackOffice = false;
        if (isset($this->context->controller)){
            $controller = $this->context->controller;
            $isBackOffice = ($controller instanceof AdminController) ||
                (property_exists($controller, 'controller_type') &&
                    $controller->controller_type == 'admin');
        }
        if (isset($params['block']) && $params['block']) {
            if (isset($this->context->customer) && $this->context->customer->id) {
                $customer_groups = $this->context->customer->getGroups();
            } else {
                $customer_groups = array((int)Configuration::get('PS_UNIDENTIFIED_GROUP'));
            }
            $blockGroups = $params['block']['customer_groups'];
            $hasAccess = true;
            if (!empty($blockGroups) && $blockGroups != '0') {
                $blockGroupsArray = explode(',', $blockGroups);
                $hasAccess = count(array_intersect($customer_groups, $blockGroupsArray)) > 0;
            }
            $cacheID = $this->_getCacheId(array_merge(
                array('block'),
                str_split($params['block']['id_block']),
                $customer_groups,
                array((int)$hasAccess)
            ));
            if(!$cacheID || !$this->isCached('block.tpl', $cacheID)) {
                $this->smarty->assign(array(
                    'block' => $this->convertBlockProperties($params['block']),
                    'customer_groups' => $customer_groups,
                    'is_logged' => isset($this->context->customer->id) ? true : false,
                    'has_access' => $hasAccess,
                    'is_backoffice' => $isBackOffice ? : false
                ));
            }
            return $this->display(__FILE__, 'block.tpl', $cacheID);
        }
        return '';
    }
    public function convertBlockProperties($block)
    {
        if (isset($block['id_manufacturers']) && $block['id_manufacturers'] && ($ids = $this->strToIds($block['id_manufacturers']))) {
            if ($manufacturers = $this->getManufacturers($block['order_by_manufacturers'], ' AND m.id_manufacturer IN(' . implode(',', $ids) . ')')) {
                foreach ($manufacturers as &$manufacturer) {
                    if ((int)Configuration::get('PS_REWRITING_SETTINGS'))
                        $link_rewrite = Tools::str2url($manufacturer['label']);
                    else
                        $link_rewrite = 0;
                    $manufacturer['link'] = $this->context->link->getManufacturerLink((int)$manufacturer['value'], $link_rewrite);
                    if (file_exists(_PS_MANU_IMG_DIR_ . $manufacturer['value'] . '.jpg'))
                        $manufacturer['image'] = trim($this->getBaseLink(), '/') . '/img/m/' . $manufacturer['value'] . '.jpg';
                    else
                        $manufacturer['image'] = $this->_path . 'views/img/2.jpg';

                }
                $block['manufacturers'] = $manufacturers;
            }
        }
        if (isset($block['id_suppliers']) && $block['id_suppliers'] && ($ids = $this->strToIds($block['id_suppliers']))) {
            if ($suppliers = $this->getSuppliers($block['order_by_suppliers'], ' AND s.id_supplier IN(' . implode(',', $ids) . ')')) {
                foreach ($suppliers as &$supplier) {
                    $supplier['link'] = $this->context->link->getSupplierLink((int)$supplier['value']);
                    if (file_exists(_PS_SUPP_IMG_DIR_ . $supplier['value'] . '.jpg'))
                        $supplier['image'] = trim($this->getBaseLink(), '/') . '/img/su/' . $supplier['value'] . '.jpg';
                    else
                        $supplier['image'] = $this->_path . 'views/img/2.jpg';
                }
                $block['suppliers'] = $suppliers;
            }
        }
        if (isset($block['id_cmss']) && $block['id_cmss'] && ($ids = $this->strToIds($block['id_cmss']))) {
            if ($cmss = $this->getCMSs(false, ' AND c.id_cms IN(' . implode(',', $ids) . ')')) {
                foreach ($cmss as &$c) {
                    $c['link'] = $this->context->link->getCMSLink((int)$c['value']);
                }
                $block['cmss'] = $cmss;
            }
        }
        if (isset($block['id_categories']) && $block['id_categories'] && ($ids = $this->strToIds($block['id_categories']))) {
            $block['categoriesHtml'] = $this->displayCategories(Ets_megamenu_defines::getCategoryById($this->context, $ids, $block['order_by_category']), $block['order_by_category']);
        }
        if (isset($block['image']) && $block['image']) {
            $block['image'] = $this->context->link->getMediaLink(_PS_ETS_MM_IMG_ . $block['image']);
        }
        if (isset($block['product_type']) && $block['product_type']) {
            if ($block['product_type'] != 'specific') {
                $block['productsHtml'] = $this->displayProducts(false, $block);
            } elseif (isset($block['id_products'])) {
                $block['productsHtml'] = $this->displayProducts($block['id_products'], $block);
            }
        }
        return $block;
    }
    public function displayProducts($ids, $block)
    {
        $products = $ids ? $this->getBlockProducts($ids) : $this->getProductFeatured($block);
        $this->smarty->assign(array(
            'products' => $products,
            'PS_CATALOG_MODE' => (bool)Configuration::get('PS_CATALOG_MODE') || (Group::isFeatureActive() && !(bool)Group::getCurrent()->show_prices),
            'comparator_max_item' => (int)Configuration::get('PS_COMPARATOR_MAX_ITEM'),
            'compared_products' => array(),
            'protocol_link' => (Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://',
            'link' => $this->context->link,
            'block' => $block,
            'imageType' => $this->getMmType('home'),
        ));
        $configure = Tools::getValue('configure');
        return $this->display(__FILE__, 'product-list' . ($configure == 'ets_megamenu' ? '-mini' : ($this->is17 ? '-17' : '')) . '.tpl');
    }

    public function getProductFeatured($block)
    {
        if (!(isset($block['product_type'])))
            return false;
        $mmProduct = new MM_Products();
        $perPage = isset($block['product_count']) && ($nb = $block['product_count']) ? $nb : 2;
        if ( isset($block['product_with_category']) && $block['product_with_category'] == 1 ){
            $listIdCategory = $block['id_categories'];
        }else{
            $listIdCategory = null;
        }
        $mmProduct->setPage(1)
            ->setPerPage($perPage)
            ->setOrderBy(null)
            ->setOrderWay(null);
        $products = array();
        switch ($block['product_type']) {
            case 'new':
                $products = $mmProduct->getNewProducts($this->context, $listIdCategory);
                break;
            case 'popular':
                $id_category = ($catID = Configuration::get('HOME_FEATURED_CAT')) ? $catID : (int)Category::getRootCategory()->id;
                $products = $mmProduct->setIdCategory($id_category)->getHomeFeatured($this->context, $listIdCategory);
                break;
            case 'special':
                $products = $mmProduct->getSpecialProducts($this->context, $listIdCategory);
                break;
            case 'best':
                $products = $mmProduct->getBestSellers($this->context, $listIdCategory);
                break;
        }
        if ($this->is17 && $this->context->controller->controller_type != 'admin') {
            $products = $this->productsForTemplate($products);
        }
        if ($products)
            foreach ($products as &$product) {
                if (isset($product['specific_prices']) && $product['specific_prices'] && $product['specific_prices']['to'] != '0000-00-00 00:00:00') {
                    $product['specific_prices_to'] = $product['specific_prices']['to'];
                }
                if ($this->is17 || $this->context->controller->controller_type == 'admin') {
                    $image = ($product['id_product_attribute'] && ($image = Ets_megamenu_defines::getCombinationImageById($product['id_product_attribute'], $this->context->language->id))) ? $image : Product::getCover($product['id_product']);
                    $product['image_id'] = isset($image['id_image']) ? $image['id_image'] : 0;
                }
            }
        return $products;
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws ReflectionException
     */
    public function productsForTemplate($products)
    {
        if (!$products || !is_array($products))
            return array();
        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter(
            new PrestaShop\PrestaShop\Adapter\Image\ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter(),
            new PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever(),
            $this->context->getTranslator()
        );
        $products_for_template = array();
        foreach ($products as $item) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($item),
                $this->context->language
            );
        }
        return $products_for_template;
    }

    public function hookDisplayBackOfficeHeader()
    {
        $configure = trim(Tools::strtolower(Tools::getValue('configure')));
        if ($configure == 'ets_megamenu') {
            $this->context->controller->addCSS($this->_path . 'views/css/font-awesome.css');
            if ($this->is8) {
                if (Module::isEnabled('ps_edition_basic')) {
                    $this->context->controller->addCSS($this->_path . 'views/css/megamenu-admin8e.css');
                }
            }
            $this->context->controller->addCSS($this->_path . 'views/css/megamenu-admin.css');
        }
    }

    public function hookDisplayHeader()
    {
        $this->addGoogleFonts();
        $this->context->controller->addCSS($this->_path . 'views/css/font-awesome.css');
        if ($this->is17) {
            $this->addCss17('megamenu', 'main');
            $this->addCss17('fix17', 'fix17');
        } else {
            $this->context->controller->addCSS($this->_path . 'views/css/megamenu.css');
            $this->context->controller->addCSS($this->_path . 'views/css/fix16.css');
        }
        $this->context->controller->addJS($this->_path . 'views/js/megamenu.js');
        if(MM_Block::checkBlockClockCountDown())
        {
            $this->context->controller->addJS($this->_path . 'views/js/jquery.countdown.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/clock.js');
        }

        $config = new MM_Config();
        $this->context->smarty->assign(array(
            'mm_config' => $config->getConfig($this->context->language->id),
            'ETS_MM_ACTIVE_BG_GRAY' => Configuration::get('ETS_MM_ACTIVE_BG_GRAY')
        ));
        if( Configuration::get('ETS_MM_DIR') == 'rtl'){
            $this->context->controller->addCSS($this->_path . 'views/css/rtl.css');
        }
        if( Configuration::get('ETS_MM_LAYOUT') == 'layout1'){
            $this->context->controller->addCSS($this->_path . 'views/css/layout1.css');
        }
        if( Configuration::get('ETS_MM_LAYOUT') == 'layout2'){
            $this->context->controller->addCSS($this->_path . 'views/css/layout2.css');
            if( Configuration::get('ETS_MM_DIR') == 'rtl'){
                $this->context->controller->addCSS($this->_path . 'views/css/layout2_rtl.css');
            }
        }
        if( Configuration::get('ETS_MM_LAYOUT') == 'layout3'){
            $this->context->controller->addCSS($this->_path . 'views/css/layout3.css');
            if( Configuration::get('ETS_MM_DIR') == 'rtl'){
                $this->context->controller->addCSS($this->_path . 'views/css/layout3_rtl.css');
            }
        }
        if( Configuration::get('ETS_MM_LAYOUT') == 'layout4'){
            $this->context->controller->addCSS($this->_path . 'views/css/layout4.css');
        }
        if( Configuration::get('ETS_MM_LAYOUT') == 'layout5'){
            $this->context->controller->addCSS($this->_path . 'views/css/layout5.css');
        }
        if( Configuration::get('ETS_MOBILE_MM_TYPE') == 'floating'){
            $this->context->controller->addCSS($this->_path . 'views/css/mobile_floating.css');
        }
        if( Configuration::get('ETS_MOBILE_MM_TYPE') == 'full'){
            $this->context->controller->addCSS($this->_path . 'views/css/mobile_full.css');
        }
        if (Configuration::get('ETS_MM_CACHE_ENABLED')) {
            if (@file_exists(dirname(__FILE__) . '/views/css/cache.css') || !@file_exists(dirname(__FILE__) . '/views/css/cache.css') && @file_put_contents(dirname(__FILE__) . '/views/css/cache.css', $this->getCSS())) {
                if ($this->is17)
                    $this->addCss17('cache', 'cache');
                else
                    $this->context->controller->addCSS($this->_path . 'views/css/cache.css');
                return $this->displayDynamicCss(true);
            } else
                return $this->displayDynamicCss();
        } else
            return $this->displayDynamicCss();
    }

    public function addGoogleFonts($frontend = false)
    {
        $font1 = Configuration::get('ETS_MM_HEADING_FONT');
        $font2 = Configuration::get('ETS_MM_TEXT_FONT');
        if ($font1 != 'Times new roman' && $font1 != 'Arial' && $font1 != 'inherit') {
            if ($this->is17) {
                $this->addCss17('https://fonts.googleapis.com/css?family=' . urlencode($font1), 'mm_gfont_1', 'remote');
            } else
                $this->context->controller->addCSS('https://fonts.googleapis.com/css?family=' . urlencode($font1));
        }
        if ($font2 != $font1 && $font2 != 'Times new roman' && $font2 != 'Arial' && $font2 != 'inherit') {
            if ($this->is17) {
                $this->addCss17('https://fonts.googleapis.com/css?family=' . urlencode($font2), 'mm_gfont_2', 'remote');
            } else
                $this->context->controller->addCSS('https://fonts.googleapis.com/css?family=' . urlencode($font2));
        }
        unset($frontend);
    }

    public function addCss17($cssFile, $id = false, $server = 'local')
    {
        $this->context->controller->registerStylesheet('modules-ets_megamenu' . ($id ? '_' . $id : ''), $server == 'remote' ? $cssFile : 'modules/' . $this->name . '/views/css/' . $cssFile . '.css', array('media' => 'all', 'priority' => 150, 'server' => $server));
    }
    public function displayDynamicCss($cache_css = false)
    {
        if(!$cache_css)
        {
            $mm_css = $this->getCss();
            $this->smarty->assign(array(
                'mm_css' => $mm_css !== null ? $mm_css : '',
            ));
        }
        return $this->display(__FILE__, 'header.tpl');
    }

    public function getCSS()
    {
        $colors = array(
            Configuration::get('ETS_MM_TEXT_FONT_SIZE'),
            Configuration::get('ETS_MM_COLOR1'),
            Configuration::get('ETS_MM_COLOR2'),
            Configuration::get('ETS_MM_COLOR3'),
            Configuration::get('ETS_MM_COLOR4'),
            Configuration::get('ETS_MM_COLOR5'),
            Configuration::get('ETS_MM_COLOR6'),
            Configuration::get('ETS_MM_COLOR7'),
            Configuration::get('ETS_MM_COLOR_BACKDROP_1'),
            Configuration::get('ETS_MM_OPACITY_BACKDROP_1'),
            Configuration::get('ETS_MM_COLOR8'),
            Configuration::get('ETS_MM_COLOR9'),
            Configuration::get('ETS_MM_COLOR_10'),
            Configuration::get('ETS_MM_COLOR_11'),
            Configuration::get('ETS_MM_COLOR_12'),
            Configuration::get('ETS_MM_COLOR_13'),
            Configuration::get('ETS_MM_COLOR_14'),
            Configuration::get('ETS_MM_COLOR_BACKDROP_2'),
            Configuration::get('ETS_MM_OPACITY_BACKDROP_2'),
            Configuration::get('ETS_MM_COLOR_15'),
            Configuration::get('ETS_MM_COLOR_16'),
            Configuration::get('ETS_MM_COLOR_17'),
            Configuration::get('ETS_MM_COLOR_18'),
            Configuration::get('ETS_MM_COLOR_19'),
            Configuration::get('ETS_MM_COLOR_20'),
            Configuration::get('ETS_MM_COLOR_21'),
            Configuration::get('ETS_MM_COLOR_BACKDROP_3'),
            Configuration::get('ETS_MM_OPACITY_BACKDROP_3'),
            Configuration::get('ETS_MM_COLOR_22'),
            Configuration::get('ETS_MM_COLOR_23'),
            Configuration::get('ETS_MM_COLOR_24'),
            Configuration::get('ETS_MM_COLOR_25'),
            Configuration::get('ETS_MM_COLOR_26'),
            Configuration::get('ETS_MM_COLOR_27'),
            Configuration::get('ETS_MM_COLOR_28'),
            Configuration::get('ETS_MM_COLOR_BACKDROP_4'),
            Configuration::get('ETS_MM_OPACITY_BACKDROP_4'),
            Configuration::get('ETS_MM_COLOR_29'),
            Configuration::get('ETS_MM_COLOR_30'),
            Configuration::get('ETS_MM_COLOR_31'),
            Configuration::get('ETS_MM_COLOR_32'),
            Configuration::get('ETS_MM_COLOR_33'),
            Configuration::get('ETS_MM_COLOR_34'),
            Configuration::get('ETS_MM_COLOR_35'),
            Configuration::get('ETS_MM_COLOR_BACKDROP_5'),
            Configuration::get('ETS_MM_OPACITY_BACKDROP_5'),
            Configuration::get('ETS_MM_COLOR_36'),
            Configuration::get('ETS_MM_COLOR_37'),
            Configuration::get('ETS_MM_COLOR_38'),
            Configuration::get('ETS_MM_COLOR_39'),
            Configuration::get('ETS_MM_COLOR_40'),
            Configuration::get('ETS_MM_MOBILE_BG_BAR'),
            Configuration::get('ETS_MM_MOBILE_COLOR_BAR'),
        );
        $colors[] = Configuration::get('ETS_MM_HEADING_FONT') != 'inherit' ? "'" . Configuration::get('ETS_MM_HEADING_FONT') . "'" : 'inherit';
        $colors[] = Configuration::get('ETS_MM_TEXT_FONT') != 'inherit' ? "'" . Configuration::get('ETS_MM_TEXT_FONT') . "'" : 'inherit';
        $dynamicCSS = @file_exists(dirname(__FILE__) . '/views/css/dynamic.css') && @is_readable(dirname(__FILE__) . '/views/css/dynamic.css') ? Tools::file_get_contents(dirname(__FILE__) . '/views/css/dynamic.css') : '';
        $css = ($dynamicCSS)
            ? str_replace(
                [
                    'text_fontsize',
                    'l1_color1', 'l1_color2', 'l1_color3', 'l1_color4', 'l1_color5', 'l1_color6', 'l1_color7', 'l1_color_backdrop', 'l1_opacity_backdrop',
                    'l2_color1', 'l2_color2', 'l2_color3', 'l2_color4', 'l2_color5', 'l2_color6', 'l2_color7', 'l2_color_backdrop', 'l2_opacity_backdrop',
                    'l3_color1', 'l3_color2', 'l3_color3', 'l3_color4', 'l3_color5', 'l3_color6', 'l3_color7', 'l3_color_backdrop', 'l3_opacity_backdrop',
                    'l4_color1', 'l4_color2', 'l4_color3', 'l4_color4', 'l4_color5', 'l4_color6', 'l4_color7', 'l4_color_backdrop', 'l4_opacity_backdrop',
                    'l5_color1', 'l5_color2', 'l5_color3', 'l5_color4', 'l5_color5', 'l5_color6', 'l5_color7', 'l5_color_backdrop', 'l5_opacity_backdrop',
                    'l1_color8', 'l2_color8', 'l3_color8', 'l4_color8', 'l5_color8',
                    'm_bar_bg', 'm_bar_color',
                    'font1', 'font2'
                ],
                $colors,
                $dynamicCSS . "\n"
            )
            : '';
        return $css;
    }

    public function strToIds($str)
    {
        $ids = array();
        if ($str) {
            $arg = explode(',', $str);
            foreach ($arg as $id)
                if (!in_array((int)$id, $ids))
                    $ids[] = (int)$id;
        }
        return $ids;
    }
    public function displayCategories($categories, $order_by = 'c.nleft ASC')
    {
        if ($categories) {
            if (Configuration::get('ETS_MM_INCLUDE_SUB_CATEGORIES')) {
                foreach ($categories as &$category) {
                    $category['sub'] = ($subcategories = Ets_megamenu_defines::getChildCategories($this->context, (int)$category['id_category'], $order_by)) ? $this->displayCategories($subcategories, $order_by) : false;
                }
            }
            foreach ($categories as &$category) {
                $category['url_image'] = $this->context->link->getCatImageLink($category['link_rewrite'], (int)$category['id_category'], $this->getFormattedName('category'));
            }
            $this->smarty->assign(array(
                'categories' => $categories,
                'link' => $this->context->link,
            ));
            return $this->display(__FILE__, 'categories-tree.tpl');
        }
        return '';
    }
    public function displayCategoriesOfProduct($categories, $order_by = 'c.nleft ASC')
    {
        if ($categories) {
            foreach ($categories as &$category) {
                $category['url_image'] = $this->context->link->getCatImageLink($category['link_rewrite'], (int)$category['id_category'], $this->getFormattedName('category'));
            }
            $this->smarty->assign(array(
                'categories' => $categories,
                'link' => $this->context->link,
            ));
            return $this->display(__FILE__, 'categories-tree.tpl');
        }
        return '';
    }
    public static function clearAllCache($clear_smarty = false)
    {
        if (@file_exists(dirname(__FILE__) . '/views/css/cache.css'))
            Ets_megamenu_defines::unlink(dirname(__FILE__) . '/views/css/cache.css');
        /** @var Ets_megamenu $megamenu */
        $megamenu = Module::getInstanceByName('ets_megamenu');
        if($clear_smarty)
            $megamenu->_clearCache('*');
        else
        {
            $megamenu->_clearCache('*');
        }
        if(Configuration::get('ETS_MM_CLEAR_CACHE_SPEED'))
        {
            if (Configuration::get('ETS_MM_HOOK_TO') == 'customhook')
            {
                Hook::exec('actionDeleteAllCache',array('hook_name' => 'customhook','module_name' => 'ets_megamenu', 'id_module' => $megamenu->id,'all'=>true));
            }
            else
            {
                if($megamenu->is17)
                    Hook::exec('actionDeleteAllCache',array('hook_name' => 'displayNavFullWidth','module_name' => 'ets_megamenu','id_module' => $megamenu->id,'all'=>true));
                else
                    Hook::exec('actionDeleteAllCache',array('hook_name' => 'displayTop','module_name' => 'ets_megamenu','id_module' => $megamenu->id,'all'=>true));
            }
        }
    }
    public function modulePath()
    {
        return $this->_path;
    }

    public function layoutDirection()
    {
        if (Configuration::get('ETS_MM_DIR') == 'auto')
            return $this->context->language->is_rtl ? 'ets-dir-rtl' : 'ets-dir-ltr';
        else
            return 'ets-dir-' . (Configuration::get('ETS_MM_DIR') == 'rtl' ? 'rtl' : 'ltr');
    }
    public function _getCacheId($params = null,$parentID = true)
    {
        if(Configuration::get('ETS_MM_CACHE_ENABLED'))
        {
            $cacheId = $this->getCacheId($this->name);
            $cacheId = str_replace($this->name, '', $cacheId);
            $suffix ='';
            if($params)
            {
                if(is_array($params))
                    $suffix .= '|'.implode('|',$params);
                else
                    $suffix .= '|'.$params;
            }
            return $this->name . $suffix .($parentID ? $cacheId:'');
        }
        return null;
    }
    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        if($cache_id===null)
            $cache_id = $this->name;
        if($template=='*')
        {
            return Tools::clearCache($this->context->smarty, false, null, $compile_id);
        }
        else
        {
            return Tools::clearCache($this->context->smarty, $this->getTemplatePath($template), $cache_id, $compile_id);
        }
    }
    public function displayMenuFrontend()
    {
        $this->smarty->assign(array(
            'menusHTML' => $this->displayMegaMenu().$this->hookDisplayCustomMenu(),
            'mm_layout_direction' => $this->layoutDirection(),
            'mm_multiLayout' => MM_Obj::multiLayoutExist(),
        ));
        return $this->display(__FILE__, 'megamenu.tpl');
    }

    public function hookDisplayTop()
    {
        if (!$this->is17 && Configuration::get('ETS_MM_HOOK_TO') != 'customhook')
            return $this->displayMenuFrontend();
    }

    public function hookDisplayNavFullWidth()
    {
        if (Configuration::get('ETS_MM_HOOK_TO') != 'customhook')
            return $this->displayMenuFrontend();
    }

    public function hookDisplayMegaMenu()
    {
        if (Configuration::get('ETS_MM_HOOK_TO') == 'customhook')
            return $this->displayMenuFrontend();
    }
    public function displayMegaMenu($id_lang = false)
    {
        $cacheId = $this->_getCacheId('megamenu');
        if($cacheId && ($clearTime = Configuration::get('ETS_MM_CACHE_TIME_CLEAR')) && strtotime($clearTime) < time())
            $this->_clearCache('*');
        if(!$cacheId || !$this->isCached('menu-html.tpl',$cacheId))
        {
            $cacheLifeTime = (int)Configuration::get('ETS_MM_CACHE_LIFE_TIME') ? :24;
            Configuration::updateValue('ETS_MM_CACHE_TIME_CLEAR',date('Y-m-d H:i:s',strtotime('+'.(int)$cacheLifeTime.' hour')));
            $menus = $id_lang ? MM_Menu::getMenus($this->context, true, $id_lang) : MM_Menu::getMenus($this->context, true);
            $this->smarty->assign(array(
                'menus' => $menus,
                'mm_img_dir' => $this->_path . 'views/img/',
            ));
        }
        return $this->display(__FILE__, 'menu-html.tpl',$cacheId);
    }

    public function hookDisplayMMItemMenu($params)
    {
        $this->smarty->assign(array(
            'menu' => isset($params['menu']) ? $params['menu'] : false,
            'have_li' => isset($params['have_li']) ? $params['have_li'] : false,
        ));
        return $this->display(__FILE__, 'item-menu.tpl');
    }

    public function hookDisplayMMItemColumn($params)
    {
        if(isset($params['column']) && $params['column'])
        {
            $this->smarty->assign(array(
                'column' => $params['column'],
                'have_li' => isset($params['have_li']) ? $params['have_li'] : false,
            ));
            return $this->display(__FILE__, 'item-column.tpl');
        }
        return '';
    }

    public function hookDisplayMMItemTab($params)
    {
        $this->smarty->assign(array(
            'tab' => isset($params['tab']) ? $params['tab'] : false,
            'have_li' => isset($params['have_li']) ? $params['have_li'] : false,
        ));
        return $this->display(__FILE__, 'item-tab.tpl');
    }

    public function hookDisplayMMItemBlock($params)
    {
        $this->smarty->assign(array(
            'block' => isset($params['block']) ? $params['block'] : false,
            'have_li' => isset($params['have_li']) ? $params['have_li'] : false,
        ));
        return $this->display(__FILE__, 'item-block.tpl');
    }

    public function installDb()
    {
        if (!is_dir(_PS_ETS_MM_IMG_DIR_)) {
            @mkdir(_PS_ETS_MM_IMG_DIR_, 0755, true);
            @copy(dirname(__FILE__) . '/index.php', _PS_ETS_MM_IMG_DIR_ . 'index.php');
        }
        return Ets_megamenu_defines::createDb();
    }
    private function processImport($zipfile = false)
    {
        $errors = array();
        if (!$zipfile) {
            if (!is_dir(_ETS_MEGAMENU_CACHE_DIR_))
                @mkdir(_ETS_MEGAMENU_CACHE_DIR_, 0777, true);
            if (!is_dir(_ETS_MEGAMENU_CACHE_DIR_.'views/'))
                @mkdir(_ETS_MEGAMENU_CACHE_DIR_.'views/', 0777, true);
            $savePath = _ETS_MEGAMENU_CACHE_DIR_;
            if (@file_exists($savePath . 'megamenu.data.zip'))
                Ets_megamenu_defines::unlink($savePath . 'megamenu.data.zip');
            $uploader = new Uploader('sliderdata');
            $uploader->setCheckFileSize(false);
            $uploader->setAcceptTypes(array('zip'));
            $uploader->setSavePath($savePath);
            $file = $uploader->process();
            if ($file[0]['error'] === 0) {
                if (!Tools::ZipTest($savePath . 'megamenu.data.zip'))
                    $errors[] = $this->l('Zip file seems to be broken');
            } elseif($file[0]['error']) {
                $errors[] = $file[0]['error'];
            }
            $extractUrl = $savePath . 'megamenu.data.zip';
        } else
            $extractUrl = $zipfile;
        if (!@file_exists($extractUrl))
            $errors[] = $this->l('Zip file doesn\'t exist');
        if (!$errors) {
            $zip = new ZipArchive();
            if ($zip->open($extractUrl) === true) {
                if ($zip->locateName('Menu-Info.xml') === false) {
                    $errors[] = $this->l('Menu-Info.xml doesn\'t exist');
                    if ( file_exists($extractUrl) && !$zipfile && $zip->close())
                        Ets_megamenu_defines::unlink($extractUrl);
                }
            } else
                $errors[] = $this->l('Cannot open zip file. It might be broken or damaged');
        }
        if (!$errors && Tools::isSubmit('importoverride') && isset($zip) && $zip->locateName('Data.xml') !== false) {
            MM_Menu::deleteAllMenu($this->context->shop->id);
        }
        if (!$errors) {
            if (!is_dir(_ETS_MEGAMENU_CACHE_DIR_)){
                @mkdir(_ETS_MEGAMENU_CACHE_DIR_, 0777, true);
                Tools::copy(dirname(__FILE__).'/index.php',_ETS_MEGAMENU_CACHE_DIR_.'index.php');
            }
            if (!is_dir(_ETS_MEGAMENU_CACHE_DIR_.'views/')){
                @mkdir(_ETS_MEGAMENU_CACHE_DIR_.'views/', 0777, true);
                Tools::copy(dirname(__FILE__).'/index.php',_ETS_MEGAMENU_CACHE_DIR_.'views/index.php');
            }
            if (!Tools::ZipExtract($extractUrl, _ETS_MEGAMENU_CACHE_DIR_ . 'views/'))
                $errors[] = $this->l('Cannot extract data from zip file');
            if (!@file_exists(_ETS_MEGAMENU_CACHE_DIR_ . 'views/Data.xml') && !@file_exists(_ETS_MEGAMENU_CACHE_DIR_ . 'views/Config.xml'))
                $errors[] = $this->l('Neither Data.xml nor Config.xml exists');
        }
        if (!$errors) {
            self::clearAllCache(true);
            $this->copy_directory(_ETS_MEGAMENU_CACHE_DIR_ . 'views/img/upload', _PS_ETS_MM_IMG_DIR_);
            if (@file_exists(_ETS_MEGAMENU_CACHE_DIR_. 'views/Data.xml')) {
                MM_ImportExport::importXmlTbl($this->context, @simplexml_load_file(_ETS_MEGAMENU_CACHE_DIR_ . 'views/Data.xml'));
                Ets_megamenu_defines::unlink(_ETS_MEGAMENU_CACHE_DIR_ . 'views/Data.xml');
            }
            if (@file_exists(_ETS_MEGAMENU_CACHE_DIR_ . 'views/Config.xml')) {
                MM_ImportExport::importXmlConfig(@simplexml_load_file(_ETS_MEGAMENU_CACHE_DIR_ . 'views/Config.xml'));
                Ets_megamenu_defines::unlink(_ETS_MEGAMENU_CACHE_DIR_. 'views/Config.xml');
            }
            if (@file_exists(_ETS_MEGAMENU_CACHE_DIR_ . 'views/Menu-Info.xml')) {
                Ets_megamenu_defines::unlink(_ETS_MEGAMENU_CACHE_DIR_ .'views/Menu-Info.xml');
            }
            if ( file_exists($extractUrl) && !$zipfile && isset($zip) && $zip->close())
                Ets_megamenu_defines::unlink($extractUrl);
            if (is_dir(_ETS_MEGAMENU_CACHE_DIR_.'views/')) {
                $this->rrmdir(_ETS_MEGAMENU_CACHE_DIR_.'views/');
            }
        }
        return $errors;
    }
    private function archiveThisFile($obj, $file, $server_path, $archive_path)
    {
        if (is_dir($server_path . $file)) {
            $dir = scandir($server_path . $file);
            foreach ($dir as $row) {
                if ($row[0] != '.') {
                    $this->archiveThisFile($obj, $row, $server_path . $file . '/', $archive_path . $file . '/');
                }
            }
        } else $obj->addFile($server_path . $file, $archive_path . $file);
    }

    public function renderConfigXml()
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><!-- Copyright PrestaHero --><config></config>');
        if ($configs = $this->getConfigs(true)) {
            foreach ($configs as $key => $val) {
                $config = $xml->addChild($key);
                $config->addAttribute('configValue', Configuration::get($key, isset($val['lang']) && $val['lang'] ? (int)Configuration::get('PS_LANG_DEFAULT') : null));
            }
        }
        return $xml->asXML();
    }

    public function renderInfoXml()
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><!-- Copyright PrestaHero --><info></info>');
        $xml->addAttribute('export_time', date('l jS \of F Y h:i:s A'));
        $xml->addAttribute('export_source', $this->context->link->getPageLink('index', Configuration::get('PS_SSL_ENABLED')));
        $xml->addAttribute('module_version', $this->version);
        return $xml->asXML();
    }
    private function generateArchive()
    {
        $zip = new ZipArchive();
        if (!is_dir(_ETS_MEGAMENU_CACHE_DIR_))
            @mkdir(_ETS_MEGAMENU_CACHE_DIR_, 0777, true);
        $cacheDir = _ETS_MEGAMENU_CACHE_DIR_;
        $zip_file_name = 'megamenu_' . date('dmYHis') . '.zip';
        if ($zip->open($cacheDir . $zip_file_name, ZipArchive::OVERWRITE | ZipArchive::CREATE) === true) {
            if (!$zip->addFromString('Config.xml', $this->renderConfigXml())) {
                $this->errors[] = $this->l('Cannot create config.xml file.');
            }
            if (!$zip->addFromString('Data.xml', MM_ImportExport::renderMenuDataXml($this->context->shop->id))) {
                $this->errors[] = $this->l('Cannot create data.xml file.');
            }
            if (!$zip->addFromString('Menu-Info.xml', $this->renderInfoXml())) {
                $this->errors[] = $this->l('Cannot create Menu-Info.xml file');
            }
            $this->archiveThisFile($zip, '', _PS_ETS_MM_IMG_DIR_, 'img/upload/');
            $zip->close();

            if (!is_file($cacheDir . $zip_file_name)) {
                $this->errors[] = $this->l(sprintf('Could not create %1s', $cacheDir . $zip_file_name));
            }

            if (!$this->errors) {
                if (ob_get_length() > 0) {
                    ob_end_clean();
                }

                ob_start();
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Cache-Control: public');
                header('Content-Description: File Transfer');
                header('Content-type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $zip_file_name . '"');
                header('Content-Transfer-Encoding: binary');
                ob_end_flush();
                if(file_exists($cacheDir . $zip_file_name))
                {
                    Ets_megamenu_defines::getInstance()->readFile($cacheDir . $zip_file_name);
                    Ets_megamenu_defines::unlink($cacheDir . $zip_file_name);
                }

                exit;
            }
        }
        {
            echo $this->l('An error occurred during the archive generation');
            die;
        }
    }

    public function getConfigs($id_lang = false)
    {
        $configs = array();
        $mm_config = new MM_Config();
        $formField = $mm_config->getFormField();
        foreach ($formField['configs'] as $key => $val) {
            $configs[$key] = Tools::strtolower(Configuration::get($key, isset($val['lang']) && $val['lang'] ? ($id_lang ? $id_lang : (int)$this->context->language->id) : null));
        }
        return $configs;
    }
    public function hookDisplayCustomMenu()
    {
        $this->context->smarty->assign(
            array(
                'ETS_MM_DISPLAY_SHOPPING_CART' => (int)Configuration::get('ETS_MM_DISPLAY_SHOPPING_CART'),
                'ETS_MM_DISPLAY_SEARCH' => (int)Configuration::get('ETS_MM_DISPLAY_SEARCH'),
                'ETS_MM_DISPLAY_CUSTOMER_INFO' => (int)Configuration::get('ETS_MM_DISPLAY_CUSTOMER_INFO'),
                'ETS_MM_CUSTOM_HTML_TEXT' => Configuration::get('ETS_MM_CUSTOM_HTML_TEXT', $this->context->language->id),
                'ETS_MM_SEARCH_DISPLAY_DEFAULT' => (int)Configuration::get('ETS_MM_SEARCH_DISPLAY_DEFAULT'),
            )
        );
        return $this->display(__FILE__, 'custom_menu.tpl');
    }

    public function hookDisplaySearch()
    {
        if ($this->is17)
            return '';
        $blocksearch = Module::getInstanceByName('blocksearch');
        if ($blocksearch->id) {
            $blocksearch->unregisterHook('displaySearch');
            return Hook::exec('top', [], $blocksearch->id);
        }
        return '';
    }

    public function hookDisplayCartTop()
    {
        if ($this->is17)
            return '';
        $blockcart = Module::getInstanceByName('blockcart');
        if ($blockcart->id) {
            $params = array(
                'cart' => $this->context->cart,
            );
            return Hook::exec('Top',$params, $blockcart->id);
        }
        return '';
    }

    public function hookDisplayCustomerInforTop()
    {
        if ($this->is17)
            return '';
        $blockuserinfo = Module::getInstanceByName('blockuserinfo');
        if ($blockuserinfo->id) {
            return Hook::exec('DisplayNav',[], $blockuserinfo->id);
        }
        return '';
    }
    public function getBaseLink()
    {
        return (Configuration::get('PS_SSL_ENABLED_EVERYWHERE') ? 'https://' : 'http://') . $this->context->shop->domain . $this->context->shop->getBaseURI();
    }

    public function activeModuleExtra()
    {
        if ($this->is17) {
            $ps_customersignin = Module::getInstanceByName('ps_customersignin');
            if ($ps_customersignin && !$ps_customersignin->isRegisteredInHook('displayNav2') && Configuration::get('ETS_MM_POSITION_USERINFOR')) {
                $ps_customersignin->registerHook('displayNav2');
                $id_hook = Hook::getIdByName('displayNav2');
                if ($position = (int)Configuration::get('ETS_MM_POSITION_USERINFOR'))
                    $ps_customersignin->updatePosition($id_hook, false, (int)$position);
                $ps_customersignin->unregisterHook('displayCustomerInforTop');
            }
            $ps_searchbar = Module::getInstanceByName('ps_searchbar');
            if ($ps_searchbar && !$ps_searchbar->isRegisteredInHook('top') && Configuration::get('ETS_MM_POSITION_BLOCK_SEARCH')) {
                $ps_searchbar->registerHook('top');
                $id_hook = Hook::getIdByName('top');
                if ($position = (int)Configuration::get('ETS_MM_POSITION_BLOCK_SEARCH'))
                    $ps_searchbar->updatePosition($id_hook, false, (int)$position);
            }
            /** @var Ps_Shoppingcart $ps_shoppingcart */
            $ps_shoppingcart = Module::getInstanceByName('ps_shoppingcart');
            if ($ps_shoppingcart->id && !$ps_shoppingcart->isRegisteredInHook('displayNav2') && ($position = Configuration::get('ETS_MM_POSITION_BLOCKCART'))) {
                $ps_shoppingcart->registerHook('displayNav2');
                $id_hook = Hook::getIdByName('displayNav2');
                $ps_shoppingcart->updatePosition($id_hook, false, (int)$position);
                $ps_shoppingcart->unregisterHook('displayCartTop');
            }
        } else {
            $blockcart = Module::getInstanceByName('blockcart');
            $hook = Configuration::get('ETS_MM_HOOK_BLOCKCART');
            if ($blockcart && $hook && !$blockcart->isRegisteredInHook($hook)) {
                $blockcart->registerHook($hook);
                $id_hook = Hook::getIdByName($hook);
                if ($position = (int)Configuration::get('ETS_MM_POSITION_BLOCKCART'))
                    $blockcart->updatePosition($id_hook, false, (int)$position);
            }
            $blocksearch = Module::getInstanceByName('blocksearch');
            $hook = Configuration::get('ETS_MM_HOOK_BLOCK_SEARCH');
            if ($blocksearch && $hook && !$blocksearch->isRegisteredInHook($hook)) {
                $id_hook = Hook::getIdByName($hook);
                $blocksearch->registerHook($hook);
                if ($position = (int)Configuration::get('ETS_MM_POSITION_BLOCK_SEARCH'))
                    $blocksearch->updatePosition($id_hook, false, (int)$position);
            }
            $blockuserinfo = Module::getInstanceByName('blockuserinfo');
            $hook = Configuration::get('ETS_MM_HOOK_USERINFOR');
            if ($blockuserinfo && $hook && !$blockuserinfo->isRegisteredInHook($hook)) {
                $id_hook = Hook::getIdByName($hook);
                $blockuserinfo->registerHook($hook);
                if ($position = (int)Configuration::get('ETS_MM_POSITION_USERINFOR'))
                    $blockuserinfo->updatePosition($id_hook, false, (int)$position);
            }
        }
        return true;
    }

    public function hookDisplayMMProductList($params)
    {
        if (isset($params['ids']) && ($productIds = $params['ids'])) {
            $IDs = explode(',', $productIds);
            $products = array();
            foreach ($IDs as $ID) {
                if ($ID) {
                    $tmpIDs = explode('-', $ID);
                    $products[] = array(
                        'id_product' => $tmpIDs[0],
                        'id_product_attribute' => !empty($tmpIDs[1]) ? $tmpIDs[1] : 0,
                    );
                }
            }
            if ($products) {
                $products = $this->getBlockProducts($products);
            }
            $this->smarty->assign('products', $products);
            return $this->display(__FILE__, 'block-product-item.tpl');
        }
    }

    public function isValidIds($excludeId)
    {
        if ($excludeId != '') {
            $ids = explode('-', $excludeId);
            if (!isset($ids[1]))
                $ids[1] = 0;
            if (Validate::isInt($ids[0]) && Validate::isInt($ids[1]))
                return (int)$ids[0] . '-' . (int)$ids[1];
            return false;
        }
        return false;
    }

    public function getBlockProducts($products)
    {
        if (!$products)
            return false;
        if (!is_array($products)) {
            $IDs = explode(',', $products);
            $products = array();
            foreach ($IDs as $ID) {
                if ($ID) {
                    $tmpIDs = explode('-', $ID );
                    if(!empty($tmpIDs[1]))
                        $combination = new Combination((int)$tmpIDs[1]);
                    else
                        $combination = false;
                    $products[] = array(
                        'id_product' => $tmpIDs[0],
                        'id_product_attribute' => $combination && Validate::isLoadedObject($combination) && $combination->id_product==$tmpIDs[0] ? $tmpIDs[1] : 0,
                    );
                }
            }
        }
        if ($products) {
            $context = $this->context;
            $id_group = isset($context->customer->id) && $context->customer->id ? Customer::getDefaultGroupId((int)$context->customer->id) : (int)Group::getCurrent()->id;
            $group = new Group($id_group);
            $useTax = $group->price_display_method ? false : true;
            foreach ($products as $key=> &$product) {
                $p = new Product($product['id_product'], true, $this->context->language->id, $this->context->shop->id);
                if(!Validate::isLoadedObject($p) || !$p->active)
                {
                    unset($products[$key]);
                    continue;
                }
                $product['link_rewrite'] = $p->link_rewrite;
                $product['price'] = Tools::displayPriceSmarty(['price'=> $p->getPrice($useTax, $product['id_product_attribute'] ? : null), 'currency'=> $this->context->currency], $this->context->smarty);
                if (($oldPrice = $p->getPriceWithoutReduct(!$useTax, $product['id_product_attribute'] ? : null)) && $oldPrice != $product['price']) {
                    $product['price_without_reduction'] = Tools::convertPrice($oldPrice);
                }
                if (isset($product['price_without_reduction']) && $product['price_without_reduction'] != $product['price']) {
                    $product['specific_prices'] = $p->specificPrice;
                }
                if (isset($product['specific_prices']) && $product['specific_prices'] && $product['specific_prices']['to'] != '0000-00-00 00:00:00') {
                    $product['specific_prices_to'] = $product['specific_prices']['to'];
                }
                $product['name'] = $p->name;
                $product['description_short'] = $p->description_short;
                $image = ($product['id_product_attribute'] && ($image = Ets_megamenu_defines::getCombinationImageById($product['id_product_attribute'], $context->language->id))) ? $image : Product::getCover($product['id_product']);
                $product['link'] = $context->link->getProductLink($product, null, null, null, null, null, $product['id_product_attribute'] ? : 0);
                if (!$this->is17 || $this->context->controller->controller_type == 'admin') {
                    $product['add_to_cart_url'] = isset($context->customer) && $this->is17 ? $context->link->getAddToCartURL((int)$product['id_product'], (int)$product['id_product_attribute']) : '';
                    $imageType = $this->getMmType();
                    $product['image'] = $context->link->getImageLink($p->link_rewrite, isset($image['id_image']) ? $image['id_image'] : 0, $imageType);
                    $product['price_tax_exc'] = Product::getPriceStatic((int)$product['id_product'], false, (int)$product['id_product_attribute'], (!$useTax ? 2 : 6), null, false, true, $p->minimal_quantity);
                    $product['available_for_order'] = $p->available_for_order;
                    if ($product['id_product_attribute']) {
                        $product['attributes'] = $p->getAttributeCombinationsById((int)$product['id_product_attribute'], $context->language->id);
                    }
                }
                $product['id_image'] = isset($image['id_image']) ? $image['id_image'] : 0;
                if ($this->is17 && $this->context->controller->controller_type != 'admin') {
                    $product['image_id'] = $product['id_image'];
                }
                $product['is_available'] = $p->checkQty(1);
                $product['allow_oosp'] = Product::isAvailableWhenOutOfStock($p->out_of_stock);
                $product['show_price'] = $p->show_price;
                if (!$this->is17) {
                    $product['out_of_stock'] = $p->out_of_stock;
                    $product['id_category_default'] = $p->id_category_default;
                    $product['ean13'] = $p->ean13;
                }
            }
            unset($context);
        }
        if ($products && $this->context->controller->controller_type != 'admin') {
            return $this->is17 ? $this->productsForTemplate($products) : Product::getProductsProperties($this->context->language->id, $products);
        }
        return $products;
    }
    public static function validateArray($array, $validate = 'isCleanHtml')
    {
        if (!is_array($array)) {
            if (method_exists('Validate', $validate)) {
                return Validate::$validate($array);
            } else
                return true;
        }
        if (method_exists('Validate', $validate)) {
            if ($array && is_array($array)) {
                $ok = true;
                foreach ($array as $val) {
                    if (!is_array($val)) {
                        if ($val && !Validate::$validate($val)) {
                            $ok = false;
                            break;
                        }
                    } else
                        $ok = self::validateArray($val, $validate);
                }
                return $ok;
            }
        }
        return true;
    }

    public function copy_directory($src, $dst,$typeImage = true)
    {
        if (is_dir($src)) {
            $dir = opendir($src);
            if (!file_exists($dst))
                @mkdir($dst);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src . '/' . $file)) {
                        $this->copy_directory($src . '/' . $file, $dst . '/' . $file);
                    } elseif (!file_exists($dst . '/' . $file)) {
                        $type = Tools::strtolower(Tools::substr(strrchr($file, '.'), 1));
                        if(!$typeImage || in_array($type,array('jpg', 'gif', 'jpeg', 'png')))
                        {
                            copy($src . '/' . $file, $dst . '/' . $file);
                        }
                    }
                }
            }
            closedir($dir);
        }
    }
    public function upgrade_2_1_9(){
        $config = new MM_Config();
        return $config->installConfigs(true);
    }
    public function rrmdir($dir)
    {
        $dir = rtrim($dir, '/');
        if ($dir && is_dir($dir)) {
            if ($objects = scandir($dir)) {
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (is_dir($dir . "/" . $object) && !is_link($dir . "/" . $object))
                            $this->rrmdir($dir . "/" . $object);
                        elseif(file_exists($dir . "/" . $object))
                            Ets_megamenu_defines::unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
    public static function isUrl($link){
        $link_validation = '/(http|https)\:\/\/[a-zA-Z0-9\.\/\?\:@\-_=#]+\.([a-zA-Z0-9\&\.\/\?\:@\-_=#])*/';
        if($link =='#' || preg_match($link_validation, $link)){
            return  true;
        }
        return false;
    }
    public function hookActionObjectLanguageAddAfter()
    {
        Ets_megamenu_defines::duplicateRowsFromDefaultShopLang('ets_mm_block_lang',$this->context->shop->id,'id_block');
        Ets_megamenu_defines::duplicateRowsFromDefaultShopLang('ets_mm_menu_lang',$this->context->shop->id,'id_menu');
        Ets_megamenu_defines::duplicateRowsFromDefaultShopLang('ets_mm_tab_lang',$this->context->shop->id,'id_tab');
    }
    public function display($file, $template, $cache_id = null, $compile_id = null)
    {
        if (($overloaded = Module::_isTemplateOverloadedStatic(basename($file, '.php'), $template)) === null) {
            return $this->l('No template was found for the module').' '.basename($file, '.php').(_PS_MODE_DEV_ ? ' (' . $template . ')' : '');
        } else {
            $this->smarty->assign([
                'module_dir' => __PS_BASE_URI__ . 'modules/' . basename($file, '.php') . '/',
                'module_template_dir' => ($overloaded ? _THEME_DIR_ : __PS_BASE_URI__) . 'modules/' . basename($file, '.php') . '/',
                'allow_push' => isset($this->allow_push) ? $this->allow_push : false,
            ]);
            if ($cache_id !== null) {
                Tools::enableCache();
            }
            if ($compile_id === null && method_exists($this,'getDefaultCompileId')) {
                $compile_id = $this->getDefaultCompileId();
            }
            $result = $this->getCurrentSubTemplate($template, $cache_id, $compile_id);
            if ($cache_id !== null) {
                Tools::restoreCacheSettings();
            }
            $result = $result->fetch();
            $this->resetCurrentSubTemplate($template, $cache_id, $compile_id);
            return $result;
        }
    }
    public function registerPlugins(){
        if(version_compare(_PS_VERSION_, '8.0.4', '>='))
        {
            $smarty = $this->context->smarty->_getSmartyObj();
            if(!isset($smarty->registered_plugins[ 'modifier' ][ 'implode' ]))
                $this->context->smarty->registerPlugin('modifier', 'implode', 'implode');
            if(!isset($smarty->registered_plugins[ 'modifier' ][ 'strpos' ]))
                $this->context->smarty->registerPlugin('modifier', 'strpos', 'strpos');
        }
    }
    public function getManufacturers($orderBy = 'name asc', $addWhere = false)
    {
        return MM_Obj::getManufacturers($this->context->shop->id, $orderBy, $addWhere);
    }
    public function getSuppliers($orderBy = 'name asc', $addWhere = false)
    {
        return MM_Obj::getSuppliers($this->context->shop->id, $orderBy, $addWhere);
    }
    public function getCustomerGroups($orderBy = 'name asc', $addWhere = false)
    {
        return MM_Obj::getCustomerGroups($this->context->language->id, $orderBy, $addWhere);
    }
    public function getCMSs($orderBy = 'cl.meta_title asc', $addWhere = false)
    {
        return MM_Obj::getCMSs($this->context,$orderBy, $addWhere);
    }
}