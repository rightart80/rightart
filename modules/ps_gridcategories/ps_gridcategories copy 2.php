<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_GridCategories extends Module
{
    // Separate config key so this module has its own blocks
    const CFG_GRID_BLOCKS = 'PS_GC3_BLOCKS';

    protected $templateFile;

    public function __construct()
    {
        // Technical name (must match folder name)
        $this->name = 'ps_gridcategories';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Custom';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        // Visible name in BO
        $this->displayName = $this->l('Grid categories');
        $this->description = $this->l('Displays selected categories in a responsive grid layout.');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);

        // Use grid template with new module name
        $this->templateFile = 'module:ps_gridcategories/views/templates/hook/ps_gridcategories.tpl';
    }

    public function install()
    {
        if (
            !parent::install()
            || !$this->installDb()
            || !$this->registerHook('displayHome')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('actionObjectCategoryUpdateAfter')
            || !$this->registerHook('actionObjectCategoryDeleteAfter')
            // NEW: hooks for Symfony category form
            || !$this->registerHook('actionCategoryFormBuilderModifier')
            || !$this->registerHook('actionAfterCreateCategoryFormHandler')
            || !$this->registerHook('actionAfterUpdateCategoryFormHandler')
        ) {
            return false;
        }

        // Default: one block, parent = home category, no specific categories
        $defaultParent = (int) Configuration::get('PS_HOME_CATEGORY');
        $gridBlocks = array(
            array(
                'id'           => 1,
                'parent_id'    => $defaultParent,
                'category_ids' => '',
            ),
        );
        Configuration::updateValue(self::CFG_GRID_BLOCKS, json_encode($gridBlocks));

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName(self::CFG_GRID_BLOCKS);
        $this->uninstallDb();

        return parent::uninstall();
    }

    /**
     * Create table that links categories to our extra image filename
     */
    protected function installDb()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'gc_category_image` (
                    `id_category` INT(10) UNSIGNED NOT NULL,
                    `image` VARCHAR(255) NOT NULL,
                    PRIMARY KEY (`id_category`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Drop the table on uninstall
     */
    protected function uninstallDb()
    {
        $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'gc_category_image`';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Get extra image filename for a category (or empty string)
     */
    protected function getExtraCategoryImageName($idCategory)
    {
        static $cache = array();

        $idCategory = (int) $idCategory;

        if (isset($cache[$idCategory])) {
            return $cache[$idCategory];
        }

        $sql = 'SELECT `image`
                FROM `'._DB_PREFIX_.'gc_category_image`
                WHERE `id_category` = '.(int) $idCategory;

        $fileName = Db::getInstance()->getValue($sql);
        $cache[$idCategory] = $fileName ? (string) $fileName : '';

        return $cache[$idCategory];
    }

    /* =========================================================
     *  CONFIGURATION (BO)
     * ======================================================= */

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('deleteGcBlock')) {
            $output .= $this->processDeleteGridBlock();
        }

        if (Tools::isSubmit('submitGcBlock')) {
            $output .= $this->processSaveGridBlock();
        }

        return $output . $this->renderGridForm();
    }

    protected function getGridBlocks()
    {
        $json = Configuration::get(self::CFG_GRID_BLOCKS);
        if (!$json) {
            return array();
        }

        $gridBlocks = json_decode($json, true);
        if (!is_array($gridBlocks)) {
            return array();
        }

        return $gridBlocks;
    }

    protected function saveGridBlocks(array $gridBlocks)
    {
        $gridBlocks = array_values($gridBlocks);
        Configuration::updateValue(self::CFG_GRID_BLOCKS, json_encode($gridBlocks));
    }

    protected function getNextGridBlockId(array $gridBlocks)
    {
        $max = 0;
        foreach ($gridBlocks as $block) {
            if (isset($block['id']) && $block['id'] > $max) {
                $max = (int) $block['id'];
            }
        }
        return $max + 1;
    }

    protected function processDeleteGridBlock()
    {
        $id_block = (int) Tools::getValue('gc_id_block');
        if (!$id_block) {
            return $this->displayError($this->l('Invalid grid block ID.'));
        }

        $gridBlocks = $this->getGridBlocks();
        $newBlocks = array();
        $found = false;

        foreach ($gridBlocks as $block) {
            if ((int) $block['id'] === $id_block) {
                $found = true;
                continue;
            }
            $newBlocks[] = $block;
        }

        if (!$found) {
            return $this->displayError($this->l('Grid block not found.'));
        }

        $this->saveGridBlocks($newBlocks);
        $this->_clearCache($this->templateFile);

        return $this->displayConfirmation($this->l('Grid block deleted.'));
    }

    protected function processSaveGridBlock()
    {
        $errors = array();
        $idLang = (int) $this->context->language->id;

        $gridBlocks = $this->getGridBlocks();

        $id_block   = (int) Tools::getValue('gc_id_block');
        $id_parent  = (int) Tools::getValue('gc_parent_category');
        $cats_input = Tools::getValue('gc_category_ids');

        // Validate parent (used as title category)
        if (!$id_parent || !Validate::isUnsignedId($id_parent)) {
            $errors[] = $this->l('The parent category ID is invalid.');
        } else {
            $parent = new Category($id_parent, $idLang);
            if (!Validate::isLoadedObject($parent)) {
                $errors[] = $this->l('The parent category does not exist.');
            }
        }

        // Validate category list
        $categoryIds = array();
        if (!empty($cats_input)) {
            $tmp = array_filter(array_map('trim', explode(',', $cats_input)));
            foreach ($tmp as $id) {
                if (!Validate::isUnsignedId($id)) {
                    $errors[] = $this->l('One of the category IDs is invalid.');
                    break;
                }
                $categoryIds[] = (int) $id;
            }
        }

        if (!empty($errors)) {
            $out = '';
            foreach ($errors as $e) {
                $out .= $this->displayError($e);
            }
            return $out;
        }

        $cats_str = implode(',', $categoryIds);

        if ($id_block > 0) {
            // Edit
            $updated = false;
            foreach ($gridBlocks as &$block) {
                if ((int) $block['id'] === $id_block) {
                    $block['parent_id']    = $id_parent;
                    $block['category_ids'] = $cats_str;
                    $updated = true;
                    break;
                }
            }
            unset($block);

            if (!$updated) {
                $errors[] = $this->l('Grid block not found for editing.');
            }
        } else {
            // New
            $newId = $this->getNextGridBlockId($gridBlocks);
            $gridBlocks[] = array(
                'id'           => $newId,
                'parent_id'    => $id_parent,
                'category_ids' => $cats_str,
            );
        }

        if (!empty($errors)) {
            $out = '';
            foreach ($errors as $e) {
                $out .= $this->displayError($e);
            }
            return $out;
        }

        $this->saveGridBlocks($gridBlocks);
        $this->_clearCache($this->templateFile);

        return $this->displayConfirmation($this->l('Grid block saved.'));
    }

    protected function renderGridForm()
    {
        $defaultLang = (int) $this->context->language->id;
        $gridBlocks  = $this->getGridBlocks();

        $editId = (int) Tools::getValue('editGcBlock');
        $editBlock = null;

        if ($editId > 0) {
            foreach ($gridBlocks as $block) {
                if ((int) $block['id'] === $editId) {
                    $editBlock = $block;
                    break;
                }
            }
        }

        $id_parent_value = $editBlock ? (int) $editBlock['parent_id'] : (int) Configuration::get('PS_HOME_CATEGORY');
        $cats_value      = $editBlock ? $editBlock['category_ids'] : '';

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
                    'title' => $this->l('Grid categories settings'),
                    'icon'  => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'gc_id_block',
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Title category ID'),
                        'name'  => 'gc_parent_category',
                        'class' => 'fixed-width-sm',
                        'desc'  => $this->l('This category name will be used as the grid title (current: ')
                            . pSQL($parent_name) . ').',
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Category IDs to display (comma-separated)'),
                        'name'  => 'gc_category_ids',
                        'class' => 'fixed-width-xxl',
                        'desc'  => $this->l('Comma-separated list of category IDs to display in this grid. Leave empty to show all direct children of the title category.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save grid block'),
                    'class' => 'btn btn-primary pull-right',
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitGcBlock';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['gc_id_block']        = $editBlock ? (int) $editBlock['id'] : 0;
        $helper->fields_value['gc_parent_category'] = $id_parent_value;
        $helper->fields_value['gc_category_ids']    = $cats_value;

        $formHtml = $helper->generateForm(array($fieldsForm));
        $formHtml .= $this->renderGridBlocksTable($gridBlocks);

        return $formHtml;
    }

    protected function renderGridBlocksTable(array $gridBlocks)
    {
        $html = '<div class="panel">
            <div class="panel-heading">' . $this->l('Existing grid blocks') . '</div>';

        if (!count($gridBlocks)) {
            $html .= '<div class="alert alert-info">' . $this->l('No grid blocks defined yet.') . '</div></div>';
            return $html;
        }

        $baseLink = $this->context->link->getAdminLink('AdminModules', false);
        $token = Tools::getAdminTokenLite('AdminModules');

        $html .= '<div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>' . $this->l('ID') . '</th>
                  <th>' . $this->l('Title category ID') . '</th>
                  <th>' . $this->l('Category IDs to display') . '</th>
                  <th>' . $this->l('Actions') . '</th>
                </tr>
              </thead>
              <tbody>';

        foreach ($gridBlocks as $block) {
            $id = (int) $block['id'];
            $editUrl = $baseLink
                . '&configure=' . $this->name
                . '&tab_module=' . $this->tab
                . '&module_name=' . $this->name
                . '&token=' . $token
                . '&editGcBlock=' . $id;

            $deleteUrl = $baseLink
                . '&configure=' . $this->name
                . '&tab_module=' . $this->tab
                . '&module_name=' . $this->name
                . '&token=' . $token
                . '&deleteGcBlock=1&gc_id_block=' . $id;

            $html .= '<tr>
                <td>' . (int) $block['id'] . '</td>
                <td>' . (int) $block['parent_id'] . '</td>
                <td>' . htmlspecialchars($block['category_ids'], ENT_QUOTES, 'UTF-8') . '</td>
                <td>
                  <a href="' . $editUrl . '" class="btn btn-default btn-xs">
                    <i class="icon-pencil"></i> ' . $this->l('Edit') . '
                  </a>
                  <a href="' . $deleteUrl . '" class="btn btn-default btn-xs"
                     onclick="return confirm(\'' . $this->l('Are you sure you want to delete this grid block?') . '\');">
                    <i class="icon-trash"></i> ' . $this->l('Delete') . '
                  </a>
                </td>
              </tr>';
        }

        $html .= '</tbody></table></div></div>';

        return $html;
    }

    /* =========================================================
     *  CATEGORY FORM HOOKS (EXTRA IMAGE)
     * ======================================================= */

    /**
     * Add an extra image upload field on the category form
     */
    public function hookActionCategoryFormBuilderModifier(array $params)
    {
        if (!isset($params['form_builder'])) {
            return;
        }

        /** @var \Symfony\Component\Form\FormBuilderInterface $formBuilder */
        $formBuilder = $params['form_builder'];

        // Add our extra image field (unmapped, handled only by hooks)
        $formBuilder->add(
            'gc_extra_image',
            \Symfony\Component\Form\Extension\Core\Type\FileType::class,
            array(
                'label'    => $this->l('Grid categories extra image'),
                'required' => false,
            )
        );

        if (isset($params['data'])) {
            $formBuilder->setData($params['data']);
        }
    }

    /**
     * After category creation, handle file upload
     */
    public function hookActionAfterCreateCategoryFormHandler(array $params)
    {
        $this->handleExtraCategoryImage($params);
    }

    /**
     * After category update, handle file upload
     */
    public function hookActionAfterUpdateCategoryFormHandler(array $params)
    {
        $this->handleExtraCategoryImage($params);
    }

    /**
     * Save uploaded extra category image into img/c/ and DB
     *
     * @param array $params Hook params from actionAfter*CategoryFormHandler
     */
    protected function handleExtraCategoryImage(array $params)
    {
        if (empty($params['id']) || empty($params['form_data']) || !is_array($params['form_data'])) {
            return;
        }

        $idCategory = (int) $params['id'];

        if (!isset($params['form_data']['gc_extra_image'])) {
            return;
        }

        $uploadedFile = $params['form_data']['gc_extra_image'];

        // No new file selected -> keep existing one
        if (!$uploadedFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            return;
        }

        if (0 !== $uploadedFile->getError()) {
            return;
        }

        // Guess extension (jpg/png/…) – fallback to jpg
        $extension = $uploadedFile->guessExtension();
        if (!$extension) {
            $extension = 'jpg';
        }

        // File name stored physically in img/c/ (same place as cover images)
        // e.g. 12_grid.jpg
        $fileName = (int) $idCategory . '_grid.' . $extension;

        try {
            // Delete old file if it exists
            if (file_exists(_PS_CAT_IMG_DIR_ . $fileName)) {
                @unlink(_PS_CAT_IMG_DIR_ . $fileName);
            }

            // Move uploaded file into category image directory
            $uploadedFile->move(_PS_CAT_IMG_DIR_, $fileName);
        } catch (\Exception $e) {
            return;
        }

        // Store/Update filename in our table
        $sql = "INSERT INTO `"._DB_PREFIX_."gc_category_image` (`id_category`, `image`)
                VALUES (".(int) $idCategory.", '".pSQL($fileName)."')
                ON DUPLICATE KEY UPDATE `image` = VALUES(`image`)";

        Db::getInstance()->execute($sql);

        // Clear FO cache so new image shows up
        $this->_clearCache($this->templateFile);
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
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;

        $blocksData = $this->getGridBlocks();
        $blocks = array();

        foreach ($blocksData as $block) {
            $id_parent = (int) $block['parent_id'];
            if (!$id_parent) {
                continue;
            }

            // Title category
            $parent = new Category($id_parent, $idLang, $idShop);
            if (!Validate::isLoadedObject($parent)) {
                continue;
            }

            $gridCategories = array();
            $selected = trim((string) $block['category_ids']);
            $iso = Language::getIsoById($idLang);

            if ($selected !== '') {
                // Explicit IDs
                $ids = array_map('intval', array_filter(array_map('trim', explode(',', $selected))));
                if (!empty($ids)) {
                    foreach ($ids as $idCat) {
                        if (!$idCat) {
                            continue;
                        }
                        $cat = new Category($idCat, $idLang, $idShop);
                        if (!Validate::isLoadedObject($cat) || !(int) $cat->active) {
                            continue;
                        }

                        $linkRewrite = is_array($cat->link_rewrite) ? $cat->link_rewrite[$idLang] : $cat->link_rewrite;
                        $description = is_array($cat->description) ? $cat->description[$idLang] : $cat->description;
                        $name        = is_array($cat->name) ? $cat->name[$idLang] : $cat->name;

                        // Default category image (id_category or language default)
                        $idImage = Tools::file_exists_cache(_PS_CAT_IMG_DIR_ . (int) $cat->id . '.jpg')
                            ? (int) $cat->id
                            : $iso . '-default';

                        // NEW: extra image filename (if any)
                        $extraImage = $this->getExtraCategoryImageName((int) $cat->id);

                        $gridCategories[] = array(
                            'id_category'  => (int) $cat->id,
                            'id_image'     => $idImage,
                            'name'         => $name,
                            'link_rewrite' => $linkRewrite,
                            'description'  => $description,
                            'extra_image'  => $extraImage,
                        );
                    }
                }
            } else {
                // Children of title category
                $children = $parent->getSubCategories($idLang, true);

                // Ensure each child has id_image set (own image or default)
                if (is_array($children) && !empty($children)) {
                    foreach ($children as &$child) {
                        if (empty($child['id_image'])) {
                            $child['id_image'] = Tools::file_exists_cache(_PS_CAT_IMG_DIR_ . (int) $child['id_category'] . '.jpg')
                                ? (int) $child['id_category']
                                : $iso . '-default';
                        }

                        // NEW: attach extra image filename if any
                        $extraImage = $this->getExtraCategoryImageName((int) $child['id_category']);
                        if ($extraImage) {
                            $child['extra_image'] = $extraImage;
                        }
                    }
                    unset($child);
                }

                $gridCategories = $children;
            }

            if (!is_array($gridCategories) || !count($gridCategories)) {
                continue;
            }

            $blocks[] = array(
                'parent'     => $parent,
                'categories' => $gridCategories,
            );
        }

        if (!count($blocks)) {
            return false;
        }

        return array(
            'gc_blocks' => $blocks,
            'gc_link'   => $this->context->link,
        );
    }

    /* =========================================================
     *  ASSETS (CSS / JS)
     * ======================================================= */

    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS(
            $this->_path . 'views/assets/css/front.css',
            'all'
        );

        $this->context->controller->addJS(
            $this->_path . 'views/assets/js/gc-gridcategories.js'
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

    public function _clearCache($template = null, $cache_id = null, $compile_id = null)
    {
        parent::_clearCache($this->templateFile);
    }
}
