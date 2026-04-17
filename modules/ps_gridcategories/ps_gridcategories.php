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
            || !$this->registerHook('displayHome')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('actionObjectCategoryUpdateAfter')
            || !$this->registerHook('actionObjectCategoryDeleteAfter')
        ) {
            return false;
        }

        // Ensure base grid-image folder exists at shop root
        $this->ensureGridImageRootDir();

        // Default: one block, parent = home category, no specific categories
        $defaultParent = (int) Configuration::get('PS_HOME_CATEGORY');
        $gridBlocks = array(
            array(
                'id'           => 1,
                'parent_id'    => $defaultParent,
                'category_ids' => '',
                // new structure to hold per-category settings + images
                'categories'   => array(),
            ),
        );
        Configuration::updateValue(self::CFG_GRID_BLOCKS, json_encode($gridBlocks));

        return true;
    }

    public function uninstall()
    {
        // NOTE: We do NOT delete /grid-image or its contents so that
        // images remain until manually removed, as requested.
        Configuration::deleteByName(self::CFG_GRID_BLOCKS);
        return parent::uninstall();
    }

    /* =========================================================
     *  IMAGE STORAGE HELPERS
     * ======================================================= */

    /**
     * Root directory for all grid images (must be web-accessible)
     * Example: /var/www/html/shop/grid-image/
     */
    protected function getGridImageRootDir()
    {
        // Folder at the root of the shop (same level as /modules, /img, etc.)
        return rtrim(_PS_ROOT_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'grid-image' . DIRECTORY_SEPARATOR;
    }

    /**
     * Ensure the root /grid-image directory exists
     */
    protected function ensureGridImageRootDir()
    {
        $dir = $this->getGridImageRootDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    /**
     * Ensure the subfolder for a given block exists
     * Example: /grid-image/3/
     */
    protected function ensureBlockImageDir($id_block)
    {
        $this->ensureGridImageRootDir();
        $dir = $this->getGridImageRootDir() . (int) $id_block . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Public web URL for an image stored for a block
     * Example: /shop/grid-image/3/my-category-grid-image.jpg
     */
    protected function getGridImageWebPath($id_block, $filename)
    {
        // __PS_BASE_URI__ = / or /shop/ etc.
        return __PS_BASE_URI__ . 'grid-image/' . (int) $id_block . '/' . ltrim($filename, '/');
    }

    /**
     * Handle upload for a single category for a given block.
     *
     * @param int      $id_block
     * @param Category $category
     * @param int      $idCategory
     *
     * @return false|string  false if no upload, or filename, or error string
     */
    protected function uploadGridImage($id_block, Category $category, $idCategory)
    {
        if (!isset($_FILES['gc_category_image'])
            || !isset($_FILES['gc_category_image']['tmp_name'][$idCategory])
        ) {
            return false;
        }

        $tmpName = $_FILES['gc_category_image']['tmp_name'][$idCategory];
        if (empty($tmpName) || !file_exists($tmpName)) {
            return false;
        }

        $file = array(
            'name'     => $_FILES['gc_category_image']['name'][$idCategory],
            'type'     => $_FILES['gc_category_image']['type'][$idCategory],
            'tmp_name' => $_FILES['gc_category_image']['tmp_name'][$idCategory],
            'error'    => $_FILES['gc_category_image']['error'][$idCategory],
            'size'     => $_FILES['gc_category_image']['size'][$idCategory],
        );

        $error = ImageManager::validateUpload($file, 0);
        if ($error) {
            // Return the error message so it can be displayed
            return $error;
        }

        $this->ensureBlockImageDir($id_block);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nameLangId = (int) $this->context->language->id;
        $catName = is_array($category->name) ? $category->name[$nameLangId] : $category->name;

        // Filename based on category name + 'grid image'
        $baseName = Tools::link_rewrite($catName . ' grid image');
        if (!$baseName) {
            $baseName = 'category-' . (int) $category->id . '-grid-image';
        }

        $filename = $baseName . ($ext ? '.' . $ext : '');
        $dest = $this->getGridImageRootDir() . (int) $id_block . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return $this->l('Unable to move uploaded image for category ID ') . (int) $category->id;
        }

        return $filename; // store only filename, not full path
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

        // We intentionally do NOT delete the block's image directory
        // so images remain until manually removed.

        return $this->displayConfirmation($this->l('Grid block deleted.'));
    }

    /**
     * Save a block and its per-category settings & images.
     */
    protected function processSaveGridBlock()
    {
        $errors = array();
        $idLang = (int) $this->context->language->id;

        $gridBlocks = $this->getGridBlocks();

        $id_block  = (int) Tools::getValue('gc_id_block');
        $id_parent = (int) Tools::getValue('gc_parent_category');

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

        // Determine which block ID will be used (important for folder name)
        if ($id_block > 0) {
            $currentBlockId = $id_block;
        } else {
            $currentBlockId = $this->getNextGridBlockId($gridBlocks);
        }

        // Get subcategories of the parent category so we can ask individually
        $subcategories = $parent->getSubCategories($idLang, true);

        $enabledInput        = (array) Tools::getValue('gc_category_enable', array());
        $existingImagesInput = (array) Tools::getValue('gc_category_existing_image', array());

        $categoriesData = array();
        $selectedIds = array();

        if (is_array($subcategories)) {
            foreach ($subcategories as $subcat) {
                if (!isset($subcat['id_category'])) {
                    continue;
                }

                $idCat = (int) $subcat['id_category'];
                $isEnabled = isset($enabledInput[$idCat]) && (int) $enabledInput[$idCat] == 1;

                $imageName = isset($existingImagesInput[$idCat])
                    ? (string) $existingImagesInput[$idCat]
                    : '';

                // If a new file is uploaded for this category, handle upload
                if (isset($_FILES['gc_category_image']['tmp_name'][$idCat])
                    && $_FILES['gc_category_image']['tmp_name'][$idCat]
                ) {
                    $catObj = new Category($idCat, $idLang);
                    if (Validate::isLoadedObject($catObj)) {
                        $uploadResult = $this->uploadGridImage($currentBlockId, $catObj, $idCat);
                        if (is_string($uploadResult)) {
                            // Error string from ImageManager
                            $errors[] = $uploadResult;
                        } elseif ($uploadResult !== false) {
                            // Filename
                            $imageName = $uploadResult;
                        }
                    }
                }

                if ($isEnabled) {
                    $selectedIds[] = $idCat;
                }

                // Store per-category configuration even if disabled
                $categoriesData[$idCat] = array(
                    'enabled' => $isEnabled ? 1 : 0,
                    'image'   => $imageName,
                );
            }
        }

        if (!empty($errors)) {
            $out = '';
            foreach ($errors as $e) {
                $out .= $this->displayError($e);
            }
            return $out;
        }

        // Keep string of enabled IDs for compatibility / list overview
        $cats_str = implode(',', $selectedIds);

        if ($id_block > 0) {
            // Edit existing block
            $updated = false;
            foreach ($gridBlocks as &$block) {
                if ((int) $block['id'] === $id_block) {
                    $block['parent_id']    = $id_parent;
                    $block['category_ids'] = $cats_str;
                    $block['categories']   = $categoriesData;
                    $updated = true;
                    break;
                }
            }
            unset($block);

            if (!$updated) {
                $errors[] = $this->l('Grid block not found for editing.');
            }
        } else {
            // New block
            $gridBlocks[] = array(
                'id'           => $currentBlockId,
                'parent_id'    => $id_parent,
                'category_ids' => $cats_str,
                'categories'   => $categoriesData,
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

        $parent_name = '';
        $parent_cat = null;
        if ($id_parent_value) {
            $parent_cat = new Category($id_parent_value, $defaultLang);
            if (Validate::isLoadedObject($parent_cat)) {
                $parent_name = $parent_cat->name;
            }
        }

        // Build HTML for per-category selection and image upload
        $childCategories = array();
        if ($parent_cat && Validate::isLoadedObject($parent_cat)) {
            $childCategories = $parent_cat->getSubCategories($defaultLang, true);
        }

        $blockCategories = ($editBlock && isset($editBlock['categories']) && is_array($editBlock['categories']))
            ? $editBlock['categories']
            : array();

        $categoriesHtml = '<div class="panel">
            <div class="panel-heading">' . $this->l('Grid categories for this block') . '</div>';

        if (!is_array($childCategories) || !count($childCategories)) {
            $categoriesHtml .= '<div class="alert alert-info">'
                . $this->l('The selected title category has no direct subcategories.')
                . '</div>';
        } else {
            $categoriesHtml .= '<p>'
                . $this->l('For each subcategory, choose whether it appears in the grid and optionally upload a specific grid image.')
                . '</p>';
            $categoriesHtml .= '<div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>' . $this->l('Use in grid') . '</th>
                            <th>' . $this->l('Category ID') . '</th>
                            <th>' . $this->l('Category name') . '</th>
                            <th>' . $this->l('Grid image') . '</th>
                        </tr>
                    </thead>
                    <tbody>';

            foreach ($childCategories as $cat) {
                if (!isset($cat['id_category'])) {
                    continue;
                }

                $idCat = (int) $cat['id_category'];
                $name  = isset($cat['name']) ? $cat['name'] : '';

                $enabled = isset($blockCategories[$idCat]['enabled']) && (int) $blockCategories[$idCat]['enabled'] === 1;
                $checked = $enabled ? ' checked="checked"' : '';

                $currentImage = isset($blockCategories[$idCat]['image']) ? $blockCategories[$idCat]['image'] : '';
                $currentImageSafe = htmlspecialchars($currentImage, ENT_QUOTES, 'UTF-8');

                $imagePreview = '';
                if ($editBlock && $currentImage) {
                    $imagePreviewUrl = $this->getGridImageWebPath((int) $editBlock['id'], $currentImage);
                    $imagePreview = '<div>'
                        . '<img src="' . htmlspecialchars($imagePreviewUrl, ENT_QUOTES, 'UTF-8') . '" alt="" style="max-height:60px;" />'
                        . '</div>';
                }

                $categoriesHtml .= '
                    <tr>
                        <td>
                            <input type="checkbox" name="gc_category_enable[' . $idCat . ']" value="1"' . $checked . ' />
                        </td>
                        <td>' . $idCat . '</td>
                        <td>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</td>
                        <td>
                            ' . $imagePreview . '
                            <input type="file" name="gc_category_image[' . $idCat . ']" />
                            <input type="hidden" name="gc_category_existing_image[' . $idCat . ']" value="' . $currentImageSafe . '" />
                        </td>
                    </tr>';
            }

            $categoriesHtml .= '</tbody></table></div>';
        }

        $categoriesHtml .= '</div>';

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
                    // Custom HTML block to configure each subcategory individually
                    array(
                        'type'  => 'free',
                        'label' => $this->l('Grid categories'),
                        'name'  => 'gc_categories_html',
                        'desc'  => $this->l('Select which subcategories should appear in this grid and upload an image for each one if needed.'),
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

        // Important: make sure form accepts file uploads
        $helper->tpl_vars = array(
            'fields_value' => array(
                'gc_id_block'        => $editBlock ? (int) $editBlock['id'] : 0,
                'gc_parent_category' => $id_parent_value,
                'gc_categories_html' => $categoriesHtml,
            ),
            'languages'   => $this->context->controller->getLanguages(),
            'id_language' => $defaultLang,
        );

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
                  <th>' . $this->l('Category IDs enabled in grid') . '</th>
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

            $categoryIds = isset($block['category_ids']) ? $block['category_ids'] : '';

            $html .= '<tr>
                <td>' . (int) $block['id'] . '</td>
                <td>' . (int) $block['parent_id'] . '</td>
                <td>' . htmlspecialchars($categoryIds, ENT_QUOTES, 'UTF-8') . '</td>
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
            $id_block  = (int) $block['id'];

            if (!$id_parent) {
                continue;
            }

            // Title category
            $parent = new Category($id_parent, $idLang, $idShop);
            if (!Validate::isLoadedObject($parent)) {
                continue;
            }

            $gridCategories = array();
            $categoriesMeta = (isset($block['categories']) && is_array($block['categories']))
                ? $block['categories']
                : array();

            // New behavior: per-category configuration with optional custom image
            if (!empty($categoriesMeta)) {
                foreach ($categoriesMeta as $idCat => $meta) {
                    $idCat = (int) $idCat;
                    if (empty($meta['enabled'])) {
                        continue;
                    }

                    $cat = new Category($idCat, $idLang, $idShop);
                    if (!Validate::isLoadedObject($cat) || !(int) $cat->active) {
                        continue;
                    }

                    $linkRewrite = is_array($cat->link_rewrite) ? $cat->link_rewrite[$idLang] : $cat->link_rewrite;
                    $description = is_array($cat->description) ? $cat->description[$idLang] : $cat->description;
                    $name        = is_array($cat->name) ? $cat->name[$idLang] : $cat->name;

                    $imageUrl = '';
                    if (!empty($meta['image'])) {
                        // meta['image'] is filename; build full web URL
                        $imageUrl = $this->getGridImageWebPath($id_block, $meta['image']);
                    }

                    $gridCategories[] = array(
                        'id_category'  => (int) $cat->id,
                        'name'         => $name,
                        'link_rewrite' => $linkRewrite,
                        'description'  => $description,
                        'grid_image'   => $imageUrl,
                    );
                }
            } else {
                // Backwards compatible behavior:
                // If no per-category meta, use category_ids or all children.
                $selected = trim((string) (isset($block['category_ids']) ? $block['category_ids'] : ''));

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
                                'grid_image'   => '', // no images in legacy mode
                            );
                        }
                    }
                } else {
                    // Children of title category
                    $children = $parent->getSubCategories($idLang, true);
                    if (is_array($children)) {
                        foreach ($children as $child) {
                            if (!isset($child['id_category'])) {
                                continue;
                            }
                            $gridCategories[] = array(
                                'id_category'  => (int) $child['id_category'],
                                'name'         => $child['name'],
                                'link_rewrite' => $child['link_rewrite'],
                                'description'  => $child['description'],
                                'grid_image'   => '', // no images in legacy mode
                            );
                        }
                    }
                }
            }

            if (!is_array($gridCategories) || !count($gridCategories)) {
                continue;
            }

            $blocks[] = array(
                'id_block'   => $id_block,
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
