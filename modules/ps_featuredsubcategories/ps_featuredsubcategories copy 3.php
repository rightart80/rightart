<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_FeaturedSubcategories extends Module
{
    // One config key storing ALL blocks as JSON
    const CFG_BLOCKS = 'PS_FSC_BLOCKS';

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
        $this->description = $this->l('Displays selected subcategories of one or more chosen parent categories.');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:ps_featuredsubcategories/views/templates/hook/ps_featuredsubcategories.tpl';
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayHome')
            || !$this->registerHook('actionObjectCategoryUpdateAfter')
            || !$this->registerHook('actionObjectCategoryDeleteAfter')
        ) {
            return false;
        }

        // Default: one block, parent = home category, no specific subcats
        $defaultParent = (int)Configuration::get('PS_HOME_CATEGORY');
        $blocks = array(
            array(
                'id'        => 1,
                'parent_id' => $defaultParent,
                'subcats'   => '',
            ),
        );
        Configuration::updateValue(self::CFG_BLOCKS, json_encode($blocks));

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName(self::CFG_BLOCKS);
        return parent::uninstall();
    }

    /* =========================================================
     *  HELPERS FOR BLOCK STORAGE
     * ======================================================= */

    protected function getBlocks()
    {
        $json = Configuration::get(self::CFG_BLOCKS);
        if (!$json) {
            return array();
        }

        $blocks = json_decode($json, true);
        if (!is_array($blocks)) {
            return array();
        }

        return $blocks;
    }

    protected function saveBlocks(array $blocks)
    {
        // Reindex the array but keep order
        $blocks = array_values($blocks);
        Configuration::updateValue(self::CFG_BLOCKS, json_encode($blocks));
    }

    protected function getNextBlockId(array $blocks)
    {
        $max = 0;
        foreach ($blocks as $b) {
            if (isset($b['id']) && $b['id'] > $max) {
                $max = (int)$b['id'];
            }
        }
        return $max + 1;
    }

    /* =========================================================
     *  CONFIGURATION
     * ======================================================= */

    public function getContent()
    {
        $output = '';

        // Delete
        if (Tools::isSubmit('deleteFscBlock')) {
            $output .= $this->processDeleteBlock();
        }

        // Add/Edit
        if (Tools::isSubmit('submitFscBlock')) {
            $output .= $this->processSaveBlock();
        }

        return $output . $this->renderForm();
    }

    protected function processDeleteBlock()
    {
        $id_block = (int)Tools::getValue('id_block');
        if (!$id_block) {
            return $this->displayError($this->l('Invalid block ID.'));
        }

        $blocks = $this->getBlocks();
        $new = array();
        $found = false;

        foreach ($blocks as $b) {
            if ((int)$b['id'] === $id_block) {
                $found = true;
                continue;
            }
            $new[] = $b;
        }

        if (!$found) {
            return $this->displayError($this->l('Block not found.'));
        }

        $this->saveBlocks($new);
        $this->_clearCache($this->templateFile);

        return $this->displayConfirmation($this->l('Block deleted.'));
    }

    protected function processSaveBlock()
    {
        $errors = array();
        $idLang = (int)$this->context->language->id;

        $blocks = $this->getBlocks();

        $id_block     = (int)Tools::getValue('fsc_id_block'); // 0 for new
        $id_parent    = (int)Tools::getValue('fsc_parent_cat');
        $subcats_input = Tools::getValue('fsc_subcats');

        // Validate parent
        if (!$id_parent || !Validate::isUnsignedId($id_parent)) {
            $errors[] = $this->l('The parent category ID is invalid.');
        } else {
            $parent = new Category($id_parent, $idLang);
            if (!Validate::isLoadedObject($parent)) {
                $errors[] = $this->l('The parent category does not exist.');
            }
        }

        // Validate subcat list
        $subcat_ids = array();
        if (!empty($subcats_input)) {
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

        $subcats_str = implode(',', $subcat_ids);

        if ($id_block > 0) {
            // Edit existing
            $updated = false;
            foreach ($blocks as &$b) {
                if ((int)$b['id'] === $id_block) {
                    $b['parent_id'] = $id_parent;
                    $b['subcats']   = $subcats_str;
                    $updated = true;
                    break;
                }
            }
            unset($b);

            if (!$updated) {
                $errors[] = $this->l('Block not found for editing.');
            }
        } else {
            // Add new
            $newId = $this->getNextBlockId($blocks);
            $blocks[] = array(
                'id'        => $newId,
                'parent_id' => $id_parent,
                'subcats'   => $subcats_str,
            );
        }

        if (!empty($errors)) {
            $out = '';
            foreach ($errors as $e) {
                $out .= $this->displayError($e);
            }
            return $out;
        }

        $this->saveBlocks($blocks);
        $this->_clearCache($this->templateFile);

        return $this->displayConfirmation($this->l('Block saved.'));
    }

    protected function renderForm()
    {
        $defaultLang = (int)$this->context->language->id;
        $blocks = $this->getBlocks();

        // Determine if we are editing a block
        $editId = (int)Tools::getValue('editBlock');
        $editBlock = null;

        if ($editId > 0) {
            foreach ($blocks as $b) {
                if ((int)$b['id'] === $editId) {
                    $editBlock = $b;
                    break;
                }
            }
        }

        $id_parent_value = $editBlock ? (int)$editBlock['parent_id'] : (int)Configuration::get('PS_HOME_CATEGORY');
        $subcats_value   = $editBlock ? $editBlock['subcats'] : '';

        // Attempt to resolve parent name for description
        $parent_name = '';
        if ($id_parent_value) {
            $parent_cat = new Category($id_parent_value, $defaultLang);
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
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'fsc_id_block',
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Parent category ID'),
                        'name'  => 'fsc_parent_cat',
                        'class' => 'fixed-width-sm',
                        'desc'  => $this->l('This category name will be used as the block title (current: ')
                                   . pSQL($parent_name) . ').',
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Subcategory IDs (comma-separated)'),
                        'name'  => 'fsc_subcats',
                        'class' => 'fixed-width-xxl',
                        'desc'  => $this->l('Comma-separated list of direct subcategory IDs to display. Leave empty to show all direct children of the parent.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save block'),
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
        $helper->submit_action = 'submitFscBlock';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['fsc_id_block']   = $editBlock ? (int)$editBlock['id'] : 0;
        $helper->fields_value['fsc_parent_cat'] = $id_parent_value;
        $helper->fields_value['fsc_subcats']    = $subcats_value;

        $formHtml = $helper->generateForm(array($fieldsForm));

        // Append table listing existing blocks
        $formHtml .= $this->renderBlocksTable($blocks);

        return $formHtml;
    }

    protected function renderBlocksTable(array $blocks)
    {
        $html = '<div class="panel">
            <div class="panel-heading">' . $this->l('Existing blocks') . '</div>';

        if (!count($blocks)) {
            $html .= '<div class="alert alert-info">' . $this->l('No blocks defined yet.') . '</div></div>';
            return $html;
        }

        $baseLink = $this->context->link->getAdminLink('AdminModules', false);
        $token = Tools::getAdminTokenLite('AdminModules');

        $html .= '<div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>' . $this->l('ID') . '</th>
                  <th>' . $this->l('Parent category ID') . '</th>
                  <th>' . $this->l('Subcategory IDs') . '</th>
                  <th>' . $this->l('Actions') . '</th>
                </tr>
              </thead>
              <tbody>';

        foreach ($blocks as $b) {
            $id = (int)$b['id'];
            $editUrl = $baseLink
                . '&configure=' . $this->name
                . '&tab_module=' . $this->tab
                . '&module_name=' . $this->name
                . '&token=' . $token
                . '&editBlock=' . $id;

            $deleteUrl = $baseLink
                . '&configure=' . $this->name
                . '&tab_module=' . $this->tab
                . '&module_name=' . $this->name
                . '&token=' . $token
                . '&deleteFscBlock=1&id_block=' . $id;

            $html .= '<tr>
                <td>' . (int)$b['id'] . '</td>
                <td>' . (int)$b['parent_id'] . '</td>
                <td>' . htmlspecialchars($b['subcats'], ENT_QUOTES, 'UTF-8') . '</td>
                <td>
                  <a href="' . $editUrl . '" class="btn btn-default btn-xs">
                    <i class="icon-pencil"></i> ' . $this->l('Edit') . '
                  </a>
                  <a href="' . $deleteUrl . '" class="btn btn-default btn-xs"
                     onclick="return confirm(\'' . $this->l('Are you sure you want to delete this block?') . '\');">
                    <i class="icon-trash"></i> ' . $this->l('Delete') . '
                  </a>
                </td>
              </tr>';
        }

        $html .= '</tbody></table></div></div>';

        return $html;
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

        $blocksData = $this->getBlocks();
        $blocks = array();

        foreach ($blocksData as $b) {
            $id_parent = (int)$b['parent_id'];
            if (!$id_parent) {
                continue;
            }

            $parent = new Category($id_parent, $idLang, $idShop);
            if (!Validate::isLoadedObject($parent)) {
                continue;
            }

            $subcategories = $parent->getSubCategories($idLang, true); // active = true
            if (!is_array($subcategories) || !count($subcategories)) {
                continue;
            }

            $selected = trim((string)$b['subcats']);
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
