<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_GridCategories extends Module
{
    // Separate config key so this module has its own blocks
    const CFG_GRID_BLOCKS = 'PS_GC3_BLOCKS';

    /** @var string */
    protected $templateFile;

    /** @var string directory for per-subcategory images */
    protected $imageDir;

    public function __construct()
    {
        // Technical name (must match folder name)
        $this->name = 'ps_gridcategories';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0'; // bumped
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

        // Directory where we will store per-subcategory images
        $this->imageDir = _PS_MODULE_DIR_.$this->name.'/views/img/';
    }

    public function install()
    {
        if (
            !parent::install()
            || !$this->registerHook('displayHome')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('actionObjectCategoryUpdateAfter')
            || !$this->registerHook('actionObjectCategoryDeleteAfter')
        ) {
            return false;
        }

        // NEW: create folder for subcategory images
        if (!$this->createImagesDir()) {
            return false;
        }

        // Default: one block, parent = home category, no specific categories yet
        $defaultParent = (int) Configuration::get('PS_HOME_CATEGORY');
        $gridBlocks = array(
            array(
                'id'           => 1,
                'parent_id'    => $defaultParent,
                // New structure: list of subcategories, each with an optional image file
                // array(array('id_category' => 2, 'image' => 'my-file.jpg'), ...)
                'categories'   => array(),
                // Legacy key kept for BC; no longer actively used for new saves
                'category_ids' => '',
            ),
        );
        Configuration::updateValue(self::CFG_GRID_BLOCKS, json_encode($gridBlocks));

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName(self::CFG_GRID_BLOCKS);
        return parent::uninstall();
    }

    /**
     * Create the directory used to store uploaded per-subcategory images.
     *
     * @return bool
     */
    protected function createImagesDir()
    {
        if (!is_dir($this->imageDir)) {
            if (!@mkdir($this->imageDir, 0755, true)) {
                return false;
            }
            // Avoid indexes listing
            @file_put_contents($this->imageDir.'index.php', "<?php\n// Silence is golden\n");
        }

        return true;
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
                // NEW: clear associated images for this block
                if (!empty($block['categories']) && is_array($block['categories'])) {
                    foreach ($block['categories'] as $catConf) {
                        if (!empty($catConf['image'])) {
                            $file = $this->imageDir.$catConf['image'];
                            if (is_file($file)) {
                                @unlink($file);
                            }
                        }
                    }
                }
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

    /**
     * Handle saving (create/edit) of a grid block, including per-subcategory options.
     */
    protected function processSaveGridBlock()
    {
        $errors = array();
        $idLang = (int) $this->context->language->id;

        $gridBlocks = $this->getGridBlocks();

        $id_block   = (int) Tools::getValue('gc_id_block');
        $id_parent  = (int) Tools::getValue('gc_parent_category');

        // Validate parent (used as title category)
        if (!$id_parent || !Validate::isUnsignedId($id_parent)) {
            $errors[] = $this->l('The parent category ID is invalid.');
        } else {
            $parent = new Category($id_parent, $idLang);
            if (!Validate::isLoadedObject($parent)) {
                $errors[] = $this->l('The parent category does not exist.');
            }
        }

        if (!empty($errors)) {
            $out = '';
            foreach ($errors as $e) {
                $out .= $this->displayError($e);
            }
            return $out;
        }

        // NEW: build the list of subcategories chosen for this block, with their optional images
        $categoriesData = $this->buildCategoriesDataFromForm($id_parent, $errors);

        if (!empty($errors)) {
            $out = '';
            foreach ($errors as $e) {
                $out .= $this->displayError($e);
            }
            return $out;
        }

        if ($id_block > 0) {
            // Edit
            $updated = false;
            foreach ($gridBlocks as &$block) {
                if ((int) $block['id'] === $id_block) {
                    $block['parent_id']    = $id_parent;
                    $block['categories']   = $categoriesData;
                    // Keep legacy key in sync (for backward compat or debugging)
                    $block['category_ids'] = implode(',', array_map(function ($c) {
                        return (int) $c['id_category'];
                    }, $categoriesData));
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
                'categories'   => $categoriesData,
                'category_ids' => implode(',', array_map(function ($c) {
                    return (int) $c['id_category'];
                }, $categoriesData)),
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

    /**
     * Build the list of subcategories chosen in the configuration form
     * and upload/store their images.
     *
     * @param int   $idParent
     * @param array $errors passed by reference
     *
     * @return array
     */
    protected function buildCategoriesDataFromForm($idParent, array &$errors)
    {
        $result = array();

        $enabled = Tools::getValue('gc_cat_enabled', array());
        $existing = Tools::getValue('gc_cat_image_existing', array());

        if (!is_array($enabled) || !count($enabled)) {
            // No subcategory was explicitly selected; front office will simply not display this block
            return $result;
        }

        foreach ($enabled as $idCatStr => $on) {
            $idCat = (int) $idCatStr;
            if (!$idCat || !Validate::isUnsignedId($idCat)) {
                $errors[] = $this->l('One of the subcategory IDs is invalid.');
                continue;
            }

            // Start from existing image if any
            $imageName = '';
            if (is_array($existing) && isset($existing[$idCat])) {
                $imageName = (string) $existing[$idCat];
            }

            // Try to handle new upload for this subcategory
            $fieldName = 'gc_cat_image_'.$idCat;
            $newImage = $this->uploadCategoryImage($fieldName, $errors);

            if ($newImage) {
                // Remove previous file if we successfully stored a new one
                if ($imageName && is_file($this->imageDir.$imageName)) {
                    @unlink($this->imageDir.$imageName);
                }
                $imageName = $newImage;
            }

            $result[] = array(
                'id_category' => $idCat,
                'image'       => $imageName,
            );
        }

        return $result;
    }

    /**
     * Handle upload/validation/move of one category image.
     *
     * @param string $fieldName
     * @param array  $errors   passed by reference
     *
     * @return string image filename (relative to $this->imageDir) or empty string on failure/no file
     */
    protected function uploadCategoryImage($fieldName, array &$errors)
    {
        if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['tmp_name'])) {
            return '';
        }

        $file = $_FILES[$fieldName];
        if (!isset($file['error']) || $file['error'] == UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = $this->l('An error occurred while uploading one of the images.');
            return '';
        }

        $maxSize = (int) Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE');
        $error = ImageManager::validateUpload($file, $maxSize);
        if ($error) {
            $errors[] = $error;
            return '';
        }

        $ext = Tools::strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
            $errors[] = $this->l('Invalid image format (allowed: jpg, jpeg, gif, png).');
            return '';
        }

        if (!file_exists($this->imageDir)) {
            if (!$this->createImagesDir()) {
                $errors[] = $this->l('Unable to create the directory for images.');
                return '';
            }
        }

        $newName = sha1($file['name'].microtime()).'.'.$ext;
        $target = $this->imageDir.$newName;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            $errors[] = $this->l('Unable to save the uploaded image.');
            return '';
        }

        return $newName;
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
                    // NEW: per-subcategory selection + image upload
                    array(
                        'type'         => 'html',
                        'name'         => 'gc_categories_html',
                        'label'        => $this->l('Subcategories of the title category'),
                        'html_content' => $this->renderSubcategoriesFields($id_parent_value, $editBlock),
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

        $formHtml = $helper->generateForm(array($fieldsForm));
        $formHtml .= $this->renderGridBlocksTable($gridBlocks);

        return $formHtml;
    }

    /**
     * Render the HTML snippet that lists every direct subcategory
     * with a checkbox and image upload field.
     *
     * @param int        $idParent
     * @param array|null $editBlock
     *
     * @return string
     */
    protected function renderSubcategoriesFields($idParent, $editBlock)
    {
        $idLang = (int) $this->context->language->id;

        if (!$idParent) {
            return '<p class="alert alert-info">'.$this->l('Enter a valid title category ID and save to configure its subcategories.').'</p>';
        }

        $parent = new Category($idParent, $idLang);
        if (!Validate::isLoadedObject($parent)) {
            return '<p class="alert alert-warning">'.$this->l('The selected parent category could not be loaded.').'</p>';
        }

        $subcats = $parent->getSubCategories($idLang, true);
        if (!is_array($subcats) || !count($subcats)) {
            return '<p class="alert alert-info">'.$this->l('This category has no direct subcategories.').'</p>';
        }

        // Map of category_id => [id_category, image]
        $existing = array();
        if ($editBlock && !empty($editBlock['categories']) && is_array($editBlock['categories'])) {
            foreach ($editBlock['categories'] as $catConf) {
                $existing[(int) $catConf['id_category']] = $catConf;
            }
        } elseif ($editBlock && !empty($editBlock['category_ids'])) {
            // Legacy config, keep compatibility: mark those IDs as selected, without images
            $ids = array_map('intval', array_filter(array_map('trim', explode(',', $editBlock['category_ids']))));
            foreach ($ids as $idCat) {
                $existing[$idCat] = array(
                    'id_category' => $idCat,
                    'image'       => '',
                );
            }
        }

        $html = '
        <div class="alert alert-info">
            '.$this->l('For each direct subcategory, choose whether to display it and optionally upload an image that will be available in the front office.').'
        </div>';

        $html .= '<div class="panel">
            <div class="panel-heading">'.$this->l('Subcategories of this parent').'</div>
            <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>'.$this->l('Use in grid').'</th>
                  <th>'.$this->l('ID').'</th>
                  <th>'.$this->l('Name').'</th>
                  <th>'.$this->l('Image').'</th>
                </tr>
              </thead>
              <tbody>';

        foreach ($subcats as $sc) {
            $idCat = (int) $sc['id_category'];
            $name  = is_array($sc['name']) ? reset($sc['name']) : $sc['name'];

            $isSelected = isset($existing[$idCat]);
            $imageName = $isSelected && !empty($existing[$idCat]['image']) ? $existing[$idCat]['image'] : '';

            $html .= '<tr>';
            $html .= '<td>
                <input type="checkbox" name="gc_cat_enabled['.$idCat.']" value="1" '.($isSelected ? 'checked="checked"' : '').' />
            </td>';
            $html .= '<td>'.$idCat.'</td>';
            $html .= '<td>'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'</td>';
            $html .= '<td>';

            if ($imageName) {
                $url = $this->_path.'views/img/'.rawurlencode($imageName);
                $html .= '<p class="current-image">'
                       . $this->l('Current: ')
                       . '<a href="'.$url.'" target="_blank">'
                       . htmlspecialchars($imageName, ENT_QUOTES, 'UTF-8')
                       . '</a></p>';
            }

            $html .= '
                <input type="file" name="gc_cat_image_'.$idCat.'" />
                <input type="hidden" name="gc_cat_image_existing['.$idCat.']"
                       value="'.htmlspecialchars($imageName, ENT_QUOTES, 'UTF-8').'" />
            ';

            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div></div>';

        return $html;
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
                  <th>' . $this->l('Subcategories in grid') . '</th>
                  <th>' . $this->l('Actions') . '</th>
                </tr>
              </thead>
              <tbody>';

        foreach ($gridBlocks as $block) {
            $id = (int) $block['id'];

            // Display the list of subcategory IDs for quick reference
            $catsLabel = '';
            if (!empty($block['categories']) && is_array($block['categories'])) {
                $ids = array();
                foreach ($block['categories'] as $catConf) {
                    $ids[] = (int) $catConf['id_category'];
                }
                $catsLabel = implode(', ', $ids);
            } elseif (!empty($block['category_ids'])) {
                $catsLabel = htmlspecialchars($block['category_ids'], ENT_QUOTES, 'UTF-8');
            }

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
                <td>' . $catsLabel . '</td>
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

            // NEW preferred: use the "categories" structure (per subcategory, with optional image)
            if (!empty($block['categories']) && is_array($block['categories'])) {
                foreach ($block['categories'] as $catConf) {
                    $idCat = (int) $catConf['id_category'];
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

                    $gridCategories[] = array(
                        'id_category'  => (int) $cat->id,
                        'name'         => $name,
                        'link_rewrite' => $linkRewrite,
                        'description'  => $description,
                        // Expose image URL so the template can use it if needed
                        'image'        => !empty($catConf['image']) ? $this->_path.'views/img/'.$catConf['image'] : '',
                    );
                }
            } else {
                // Legacy behaviour: use "category_ids" as a comma-separated list
                $selected = isset($block['category_ids']) ? trim((string) $block['category_ids']) : '';

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

                            $gridCategories[] = array(
                                'id_category'  => (int) $cat->id,
                                'name'         => $name,
                                'link_rewrite' => $linkRewrite,
                                'description'  => $description,
                                'image'        => '',
                            );
                        }
                    }
                } else {
                    // Children of title category (legacy behaviour)
                    $children = $parent->getSubCategories($idLang, true);
                    if (is_array($children)) {
                        foreach ($children as $child) {
                            $gridCategories[] = array(
                                'id_category'  => (int) $child['id_category'],
                                'name'         => $child['name'],
                                'link_rewrite' => $child['link_rewrite'],
                                'description'  => isset($child['description']) ? $child['description'] : '',
                                'image'        => '',
                            );
                        }
                    }
                }
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
