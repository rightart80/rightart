<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_FeaturedSubcategories extends Module
{
    // Used as prefixes, real keys will be PS_FSC_PARENT_CAT_1, PS_FSC_PARENT_CAT_2, ...
    const CFG_PARENT_CAT = 'PS_FSC_PARENT_CAT_';
    const CFG_SUBCATS    = 'PS_FSC_SUBCATS_';

    protected $templateFile;

    // How many blocks you want to configure/display
    protected $blockCount = 3;

    public function __construct()
    {
        $this->name = 'ps_featuredsubcategories';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Custom';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Featured subcategories');
        $this->description = $this->l('Displays selected subcategories of one or more chosen parent categories.');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:ps_featuredsubcategories/views/templates/hook/ps_featuredsubcategories.tpl';
    }

    public function install()
    {
        // Default parent category = Home category
        $defaultParent = (int)Configuration::get('PS_HOME_CATEGORY');

        if (!parent::install()
            || !$this->registerHook('displayHome')
            || !$this->registerHook('actionObjectCategoryUpdateAfter')
            || !$this->registerHook('actionObjectCategoryDeleteAfter')
        ) {
            return false;
        }

        // Initialize each block
        for ($i = 1; $i <= $this->blockCount; $i++) {
            Configuration::updateValue(self::CFG_PARENT_CAT.$i, $defaultParent);
            Configuration::updateValue(self::CFG_SUBCATS.$i, '');
        }

        return true;
    }

    public function uninstall()
    {
        // Remove configuration for all blocks
        for ($i = 1; $i <= $this->blockCount; $i++) {
            Configuration::deleteByName(self::CFG_PARENT_CAT.$i);
            Configuration::deleteByName(self::CFG_SUBCATS.$i);
        }

        return parent::uninstall();
    }

    /* =========================================================
     *  CONFIGURATION
     * ======================================================= */

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitPsFeaturedSubcategories')) {
            $output .= $this->processForm();
        }

        return $output . $this->renderForm();
    }

    protected function processForm()
    {
        $errors = array();
        $idLang = (int)$this->context->language->id;

        for ($i = 1; $i <= $this->blockCount; $i++) {
            $parentKey  = self::CFG_PARENT_CAT.$i;
            $subcatsKey = self::CFG_SUBCATS.$i;

            $id_parent     = (int)Tools::getValue($parentKey);
            $subcats_input = Tools::getValue($subcatsKey);

            // If both empty => disable this block
            if (!$id_parent && !strlen(trim($subcats_input))) {
                Configuration::updateValue($parentKey, 0);
                Configuration::updateValue($subcatsKey, '');
                continue;
            }

            // Validate parent category
            if (!$id_parent || !Validate::isUnsignedId($id_parent)) {
                $errors[] = sprintf($this->l('The parent category ID is invalid for block #%d.'), $i);
                continue;
            }

            $parent = new Category($id_parent, $idLang);
            if (!Validate::isLoadedObject($parent)) {
                $errors[] = sprintf($this->l('The parent category does not exist for block #%d.'), $i);
                continue;
            }

            // Validate subcategory list
            $subcat_ids = array();
            if (!empty($subcats_input)) {
                $tmp = array_filter(array_map('trim', explode(',', $subcats_input)));
                foreach ($tmp as $id) {
                    if (!Validate::isUnsignedId($id)) {
                        $errors[] = sprintf($this->l('One of the subcategory IDs is invalid in block #%d.'), $i);
                        break;
                    }
                    $subcat_ids[] = (int)$id;
                }
            }

            if (!empty($errors)) {
                // If errors were added, do not save this block
                continue;
            }

            // Save configuration for this block
            Configuration::updateValue($parentKey, $id_parent);
            Configuration::updateValue($subcatsKey, implode(',', $subcat_ids));
        }

        if (!empty($errors)) {
            $out = '';
            foreach ($errors as $e) {
                $out .= $this->displayError($e);
            }
            return $out;
        }

        // Clear cache
        $this->_clearCache($this->templateFile);

        return $this->displayConfirmation($this->l('Settings updated.'));
    }

    protected function renderForm()
    {
        $defaultLang = (int)$this->context->language->id;

        $inputs = array();

        for ($i = 1; $i <= $this->blockCount; $i++) {
            $parentKey  = self::CFG_PARENT_CAT.$i;
            $subcatsKey = self::CFG_SUBCATS.$i;

            $id_parent = (int)Configuration::get($parentKey);
            $parent_name = '';
            if ($id_parent) {
                $parent_cat = new Category($id_parent, $defaultLang);
                if (Validate::isLoadedObject($parent_cat)) {
                    $parent_name = $parent_cat->name;
                }
            }

            $inputs[] = array(
                'type'  => 'text',
                'label' => sprintf($this->l('Parent category ID (block %d)'), $i),
                'name'  => $parentKey,
                'class' => 'fixed-width-sm',
                'desc'  => $this->l('This category name will be used as the block title (current: ')
                           . pSQL($parent_name) . '). '
                           . $this->l('Leave empty (and subcategories empty) to disable this block.'),
            );

            $inputs[] = array(
                'type'  => 'text',
                'label' => sprintf($this->l('Subcategory IDs (comma-separated, block %d)'), $i),
                'name'  => $subcatsKey,
                'class' => 'fixed-width-xxl',
                'desc'  => $this->l('Comma-separated list of direct subcategory IDs to display. Leave empty to show all direct children of the parent.'),
            );
        }

        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Featured subcategories settings'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-primary pull-right',
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPsFeaturedSubcategories';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        for ($i = 1; $i <= $this->blockCount; $i++) {
            $parentKey  = self::CFG_PARENT_CAT.$i;
            $subcatsKey = self::CFG_SUBCATS.$i;

            $helper->fields_value[$parentKey]  = (int)Configuration::get($parentKey);
            $helper->fields_value[$subcatsKey] = Configuration::get($subcatsKey);
        }

        return $helper->generateForm(array($fieldsForm));
    }

    /* =========================================================
     *  FRONT OFFICE
     * ======================================================= */

    public function hookDisplayHome($params)
    {
        return $this->renderWidget(null, array());
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId())) {
            $vars = $this->getWidgetVariables($hookName, $configuration);
            if ($vars === false) {
                return ''; // nothing to display
            }
            $this->context->smarty->assign($vars);
        }

        return $this->fetch($this->templateFile, $this->getCacheId());
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $idLang = (int)$this->context->language->id;
        $idShop = (int)$this->context->shop->id;

        $blocks = array();

        for ($i = 1; $i <= $this->blockCount; $i++) {
            $parentKey  = self::CFG_PARENT_CAT.$i;
            $subcatsKey = self::CFG_SUBCATS.$i;

            $id_parent = (int)Configuration::get($parentKey);
            if (!$id_parent) {
                continue; // block disabled
            }

            $parent = new Category($id_parent, $idLang, $idShop);
            if (!Validate::isLoadedObject($parent)) {
                continue;
            }

            // Get direct subcategories
            $subcategories = $parent->getSubCategories($idLang, true); // active = true
            if (!is_array($subcategories) || !count($subcategories)) {
                continue;
            }

            // Filter by selected IDs if any
            $selected = trim(Configuration::get($subcatsKey));
            if ($selected !== '') {
                $allowed_ids = array_map('intval', array_filter(array_map('trim', explode(',', $selected))));
                $subcategories = array_filter($subcategories, function ($subcat) use ($allowed_ids) {
                    return in_array((int)$subcat['id_category'], $allowed_ids);
                });
            }

            if (!count($subcategories)) {
                continue;
            }

            $blocks[] = array(
                'parent'        => $parent,
                'subcategories' => $subcategories,
            );
        }

        if (!count($blocks)) {
            return false;
        }

        return array(
            'fsc_blocks' => $blocks,
            'link'       => $this->context->link,
        );
    }

    /* =========================================================
     *  CACHE CLEARING
     * ======================================================= */

    public function hookActionObjectCategoryUpdateAfter($params)
    {
        $this->_clearCache($this->templateFile);
    }

    public function hookActionObjectCategoryDeleteAfter($params)
    {
        $this->_clearCache($this->templateFile);
    }

    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        parent::_clearCache($this->templateFile);
    }
}
