<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_FeaturedSubcategories extends Module
{
    const CFG_PARENT_CAT = 'PS_FSC_PARENT_CAT';
    const CFG_SUBCATS    = 'PS_FSC_SUBCATS'; // stored as comma-separated IDs

    protected $templateFile;

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
        $this->description = $this->l('Displays selected subcategories of a chosen parent category.');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:ps_featuredsubcategories/views/templates/hook/ps_featuredsubcategories.tpl';
    }

    public function install()
    {
        // Default parent category = Home category
        $defaultParent = (int)Configuration::get('PS_HOME_CATEGORY');

        return parent::install()
            && Configuration::updateValue(self::CFG_PARENT_CAT, $defaultParent)
            && Configuration::updateValue(self::CFG_SUBCATS, '')
            // Hook wherever you like; we copy ps_featuredproducts and use displayHome by default
            && $this->registerHook('displayHome')
            // clear cache on category changes
            && $this->registerHook('actionObjectCategoryUpdateAfter')
            && $this->registerHook('actionObjectCategoryDeleteAfter');
    }

    public function uninstall()
    {
        return Configuration::deleteByName(self::CFG_PARENT_CAT)
            && Configuration::deleteByName(self::CFG_SUBCATS)
            && parent::uninstall();
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

        $id_parent = (int)Tools::getValue(self::CFG_PARENT_CAT);
        $subcats_input = Tools::getValue(self::CFG_SUBCATS);

        if (!$id_parent || !Validate::isUnsignedId($id_parent)) {
            $errors[] = $this->l('The parent category ID is invalid.');
        } else {
            $parent = new Category($id_parent, (int)$this->context->language->id);
            if (!Validate::isLoadedObject($parent)) {
                $errors[] = $this->l('The parent category does not exist.');
            }
        }

        $subcat_ids = array();
        if (!empty($subcats_input)) {
            // We allow either comma-separated list: "3,5,10"
            $tmp = array_filter(array_map('trim', explode(',', $subcats_input)));
            foreach ($tmp as $id) {
                if (!Validate::isUnsignedId($id)) {
                    $errors[] = $this->l('One of the subcategory IDs is invalid.');
                    break;
                }
                $subcat_ids[] = (int)$id;
            }
        }

        if (!empty($errors)) {
            $out = '';
            foreach ($errors as $e) {
                $out .= $this->displayError($e);
            }
            return $out;
        }

        // Save configuration
        Configuration::updateValue(self::CFG_PARENT_CAT, $id_parent);
        Configuration::updateValue(self::CFG_SUBCATS, implode(',', $subcat_ids));

        // Clear cache
        $this->_clearCache($this->templateFile);

        return $this->displayConfirmation($this->l('Settings updated.'));
    }

    protected function renderForm()
    {
        $defaultLang = (int)$this->context->language->id;

        // We will show the parent category name as helper text
        $id_parent = (int)Configuration::get(self::CFG_PARENT_CAT);
        $parent_name = '';
        if ($id_parent) {
            $parent_cat = new Category($id_parent, $defaultLang);
            if (Validate::isLoadedObject($parent_cat)) {
                $parent_name = $parent_cat->name;
            }
        }

        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Featured subcategories settings'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Parent category ID'),
                        'name'  => self::CFG_PARENT_CAT,
                        'class' => 'fixed-width-sm',
                        'desc'  => $this->l('This category name will be used as the block title (current: ')
                                   . pSQL($parent_name) . ').',
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Subcategory IDs (comma-separated)'),
                        'name'  => self::CFG_SUBCATS,
                        'class' => 'fixed-width-xxl',
                        'desc'  => $this->l('Comma-separated list of direct subcategory IDs to display. Leave empty to show all direct children of the parent.'),
                    ),
                ),
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

        $helper->fields_value[self::CFG_PARENT_CAT] = $id_parent ? $id_parent : (int)Configuration::get('PS_HOME_CATEGORY');
        $helper->fields_value[self::CFG_SUBCATS] = Configuration::get(self::CFG_SUBCATS);

        return $helper->generateForm(array($fieldsForm));
    }

    /* =========================================================
     *  FRONT OFFICE
     * ======================================================= */

    public function hookDisplayHome($params)
    {
        return $this->renderWidget(null, array());
    }

    // If you want it in more hooks, implement renderWidget & getWidgetVariables
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

        $id_parent = (int)Configuration::get(self::CFG_PARENT_CAT);
        if (!$id_parent) {
            return false;
        }

        $parent = new Category($id_parent, $idLang, $idShop);
        if (!Validate::isLoadedObject($parent)) {
            return false;
        }

        // Get direct subcategories
        $subcategories = $parent->getSubCategories($idLang, true); // active = true
        if (!is_array($subcategories) || !count($subcategories)) {
            return false;
        }

        // Filter by selected IDs if any
        $selected = trim(Configuration::get(self::CFG_SUBCATS));
        if ($selected !== '') {
            $allowed_ids = array_map('intval', array_filter(array_map('trim', explode(',', $selected))));
            $subcategories = array_filter($subcategories, function ($subcat) use ($allowed_ids) {
                return in_array((int)$subcat['id_category'], $allowed_ids);
            });
        }

        if (!count($subcategories)) {
            return false;
        }

        // Optional: sort by position or name; here we leave the default order from getSubCategories

        return array(
            'fsc_parent_category' => $parent,
            'fsc_subcategories'   => $subcategories,
            'link'                => $this->context->link,
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
