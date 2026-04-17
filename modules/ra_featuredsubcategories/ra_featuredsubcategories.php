<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ra_FeaturedSubcategories extends Module
{
    const CFG_BLOCKS = 'RA_FSC_BLOCKS';

    /** @var string */
    protected $templateFile;

    public function __construct()
    {
        $this->name = 'ra_featuredsubcategories';
        $this->tab = 'front_office_features';
        $this->version = '1.2.0';
        $this->author = 'Right Art';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Right Art Featured Subcategories');
        $this->description = $this->l('Displays selected categories or subcategories in configurable blocks.');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
        $this->templateFile = 'module:ra_featuredsubcategories/views/templates/hook/ra_featuredsubcategories.tpl';
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayHome')
            || !$this->registerHook('actionFrontControllerSetMedia')
            || !$this->registerHook('actionObjectCategoryUpdateAfter')
            || !$this->registerHook('actionObjectCategoryDeleteAfter')
        ) {
            return false;
        }

        $defaultParent = (int) Configuration::get('PS_HOME_CATEGORY');
        $blocks = array(
            array(
                'id' => 1,
                'parent_id' => $defaultParent,
                'subcats' => '',
                'position' => 0,
            ),
        );

        $this->saveBlocks($blocks);

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName(self::CFG_BLOCKS);

        return parent::uninstall();
    }

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

        foreach ($blocks as $index => $block) {
            if (!isset($blocks[$index]['id'])) {
                $blocks[$index]['id'] = $index + 1;
            }
            if (!isset($blocks[$index]['parent_id'])) {
                $blocks[$index]['parent_id'] = 0;
            }
            if (!isset($blocks[$index]['subcats'])) {
                $blocks[$index]['subcats'] = '';
            }
            if (!isset($blocks[$index]['position'])) {
                $blocks[$index]['position'] = $index;
            }
        }

        usort($blocks, function ($a, $b) {
            return (int) $a['position'] - (int) $b['position'];
        });

        return $blocks;
    }

    protected function saveBlocks(array $blocks)
    {
        $cleanBlocks = array_values($blocks);

        foreach ($cleanBlocks as $index => $block) {
            $cleanBlocks[$index]['id'] = isset($block['id']) ? (int) $block['id'] : ($index + 1);
            $cleanBlocks[$index]['parent_id'] = isset($block['parent_id']) ? (int) $block['parent_id'] : 0;
            $cleanBlocks[$index]['subcats'] = isset($block['subcats']) ? trim((string) $block['subcats']) : '';
            $cleanBlocks[$index]['position'] = $index;
        }

        Configuration::updateValue(self::CFG_BLOCKS, json_encode($cleanBlocks));
    }

    protected function getNextBlockId(array $blocks)
    {
        $max = 0;

        foreach ($blocks as $block) {
            if (isset($block['id']) && (int) $block['id'] > $max) {
                $max = (int) $block['id'];
            }
        }

        return $max + 1;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('deleteFscBlock')) {
            $output .= $this->processDeleteBlock();
        }

        if (Tools::isSubmit('submitFscBlock')) {
            $output .= $this->processSaveBlock();
        }

        if (Tools::isSubmit('submitFscSortBlocks')) {
            $output .= $this->processSortBlocks();
        }

        return $output . $this->renderForm();
    }

    protected function processDeleteBlock()
    {
        $idBlock = (int) Tools::getValue('id_block');
        if (!$idBlock) {
            return $this->displayError($this->l('Invalid block ID.'));
        }

        $blocks = $this->getBlocks();
        $newBlocks = array();
        $found = false;

        foreach ($blocks as $block) {
            if ((int) $block['id'] === $idBlock) {
                $found = true;
                continue;
            }

            $newBlocks[] = $block;
        }

        if (!$found) {
            return $this->displayError($this->l('Block not found.'));
        }

        $this->saveBlocks($newBlocks);
        $this->_clearCache($this->templateFile);

        return $this->displayConfirmation($this->l('Block deleted.'));
    }

    protected function processSaveBlock()
    {
        $errors = array();
        $idLang = (int) $this->context->language->id;
        $blocks = $this->getBlocks();

        $idBlock = (int) Tools::getValue('fsc_id_block');
        $idParent = (int) Tools::getValue('fsc_parent_cat');
        $subcatsInput = Tools::getValue('fsc_subcats');

        if (!$idParent || !Validate::isUnsignedId($idParent)) {
            $errors[] = $this->l('The parent category ID is invalid.');
        } else {
            $parent = new Category($idParent, $idLang);
            if (!Validate::isLoadedObject($parent)) {
                $errors[] = $this->l('The parent category does not exist.');
            }
        }

        $subcatIds = array();
        if (!empty($subcatsInput)) {
            $tmp = array_filter(array_map('trim', explode(',', $subcatsInput)));
            foreach ($tmp as $id) {
                if (!Validate::isUnsignedId($id)) {
                    $errors[] = $this->l('One of the category IDs is invalid.');
                    break;
                }
                $subcatIds[] = (int) $id;
            }
        }

        if (!empty($errors)) {
            $out = '';
            foreach ($errors as $error) {
                $out .= $this->displayError($error);
            }

            return $out;
        }

        $subcatsStr = implode(',', $subcatIds);

        if ($idBlock > 0) {
            $updated = false;
            foreach ($blocks as &$block) {
                if ((int) $block['id'] === $idBlock) {
                    $block['parent_id'] = $idParent;
                    $block['subcats'] = $subcatsStr;
                    if (!isset($block['position'])) {
                        $block['position'] = 0;
                    }
                    $updated = true;
                    break;
                }
            }
            unset($block);

            if (!$updated) {
                $errors[] = $this->l('Block not found for editing.');
            }
        } else {
            $blocks[] = array(
                'id' => $this->getNextBlockId($blocks),
                'parent_id' => $idParent,
                'subcats' => $subcatsStr,
                'position' => count($blocks),
            );
        }

        if (!empty($errors)) {
            $out = '';
            foreach ($errors as $error) {
                $out .= $this->displayError($error);
            }

            return $out;
        }

        $this->saveBlocks($blocks);
        $this->_clearCache($this->templateFile);

        return $this->displayConfirmation($this->l('Block saved.'));
    }

    protected function processSortBlocks()
    {
        $order = trim((string) Tools::getValue('fsc_block_order'));
        if ($order === '') {
            return $this->displayError($this->l('No block order received.'));
        }

        $ids = array();
        foreach (explode(',', $order) as $value) {
            $id = (int) trim($value);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        if (empty($ids)) {
            return $this->displayError($this->l('Invalid block order.'));
        }

        $blocks = $this->getBlocks();
        $indexed = array();

        foreach ($blocks as $block) {
            $indexed[(int) $block['id']] = $block;
        }

        $orderedBlocks = array();
        foreach ($ids as $id) {
            if (isset($indexed[$id])) {
                $orderedBlocks[] = $indexed[$id];
                unset($indexed[$id]);
            }
        }

        foreach ($indexed as $block) {
            $orderedBlocks[] = $block;
        }

        $this->saveBlocks($orderedBlocks);
        $this->_clearCache($this->templateFile);

        return $this->displayConfirmation($this->l('Block order updated.'));
    }

    protected function renderForm()
    {
        $defaultLang = (int) $this->context->language->id;
        $blocks = $this->getBlocks();
        $editId = (int) Tools::getValue('editBlock');
        $editBlock = null;

        if ($editId > 0) {
            foreach ($blocks as $block) {
                if ((int) $block['id'] === $editId) {
                    $editBlock = $block;
                    break;
                }
            }
        }

        $idParentValue = $editBlock ? (int) $editBlock['parent_id'] : (int) Configuration::get('PS_HOME_CATEGORY');
        $subcatsValue = $editBlock ? $editBlock['subcats'] : '';

        $parentName = '';
        if ($idParentValue) {
            $parentCat = new Category($idParentValue, $defaultLang);
            if (Validate::isLoadedObject($parentCat)) {
                $parentName = $parentCat->name;
            }
        }

        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Featured categories settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'fsc_id_block',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title category ID'),
                        'name' => 'fsc_parent_cat',
                        'class' => 'fixed-width-sm',
                        'desc' => $this->l('This category name will be used as the block title (current: ') . htmlspecialchars($parentName, ENT_QUOTES, 'UTF-8') . ').',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Category IDs to display (comma-separated)'),
                        'name' => 'fsc_subcats',
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Comma-separated list of category IDs to display in this block. Leave empty to show all direct children of the title category.'),
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
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitFscBlock';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['fsc_id_block'] = $editBlock ? (int) $editBlock['id'] : 0;
        $helper->fields_value['fsc_parent_cat'] = $idParentValue;
        $helper->fields_value['fsc_subcats'] = $subcatsValue;

        return $helper->generateForm(array($fieldsForm)) . $this->renderBlocksTable($blocks);
    }

    protected function renderBlocksTable(array $blocks)
    {
        $html = '<div class="panel"><div class="panel-heading">' . $this->l('Existing blocks') . '</div>';

        if (!count($blocks)) {
            return $html . '<div class="alert alert-info">' . $this->l('No blocks defined yet.') . '</div></div>';
        }

        $baseLink = $this->context->link->getAdminLink('AdminModules', false);
        $token = Tools::getAdminTokenLite('AdminModules');
        $actionUrl = $baseLink
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name
            . '&token=' . $token;

        $html .= '
        <style>
            #fsc-blocks-table tbody tr.fsc-sort-row { cursor: move; }
            #fsc-blocks-table tbody tr.fsc-sort-row.fsc-dragging { opacity: .45; }
            #fsc-blocks-table .fsc-drag-handle { width: 42px; text-align: center; font-size: 18px; }
            #fsc-blocks-table .fsc-drag-handle span { display: inline-block; padding: 4px 6px; border: 1px solid #ddd; border-radius: 3px; background: #fafafa; }
        </style>
        <form method="post" action="' . $actionUrl . '" id="fsc-sort-form">
            <input type="hidden" name="submitFscSortBlocks" value="1">
            <input type="hidden" name="fsc_block_order" id="fsc_block_order" value="">
            <div class="alert alert-info">' . $this->l('Drag blocks up or down using the handle, then click Save order.') . '</div>
            <div class="table-responsive">
                <table class="table" id="fsc-blocks-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>' . $this->l('ID') . '</th>
                      <th>' . $this->l('Title category ID') . '</th>
                      <th>' . $this->l('Category IDs to display') . '</th>
                      <th>' . $this->l('Actions') . '</th>
                    </tr>
                  </thead>
                  <tbody>';

        foreach ($blocks as $block) {
            $id = (int) $block['id'];
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

            $html .= '<tr class="fsc-sort-row" data-id="' . $id . '" draggable="true">
                <td class="fsc-drag-handle"><span>&#9776;</span></td>
                <td>' . $id . '</td>
                <td>' . (int) $block['parent_id'] . '</td>
                <td>' . htmlspecialchars($block['subcats'], ENT_QUOTES, 'UTF-8') . '</td>
                <td>
                  <a href="' . $editUrl . '" class="btn btn-default btn-xs">
                    <i class="icon-pencil"></i> ' . $this->l('Edit') . '
                  </a>
                  <a href="' . $deleteUrl . '" class="btn btn-default btn-xs" onclick="return confirm(\'' . $this->l('Are you sure you want to delete this block?') . '\');">
                    <i class="icon-trash"></i> ' . $this->l('Delete') . '
                  </a>
                </td>
              </tr>';
        }

        $html .= '
                  </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary pull-right">
                <i class="icon-save"></i> ' . $this->l('Save order') . '
            </button>
            <div class="clearfix"></div>
        </form>
        <script>
            (function () {
                var tableBody = document.querySelector("#fsc-blocks-table tbody");
                var orderInput = document.getElementById("fsc_block_order");
                var draggedRow = null;

                function updateOrderInput() {
                    if (!tableBody || !orderInput) {
                        return;
                    }

                    var ids = [];
                    var rows = tableBody.querySelectorAll("tr.fsc-sort-row");
                    for (var i = 0; i < rows.length; i++) {
                        ids.push(rows[i].getAttribute("data-id"));
                    }
                    orderInput.value = ids.join(",");
                }

                function getDragAfterElement(container, y) {
                    var rows = container.querySelectorAll("tr.fsc-sort-row:not(.fsc-dragging)");
                    var closest = null;
                    var closestOffset = Number.NEGATIVE_INFINITY;

                    for (var i = 0; i < rows.length; i++) {
                        var box = rows[i].getBoundingClientRect();
                        var offset = y - box.top - (box.height / 2);
                        if (offset < 0 && offset > closestOffset) {
                            closestOffset = offset;
                            closest = rows[i];
                        }
                    }

                    return closest;
                }

                if (!tableBody) {
                    return;
                }

                tableBody.addEventListener("dragstart", function (event) {
                    var row = event.target.closest("tr.fsc-sort-row");
                    if (!row) {
                        return;
                    }

                    draggedRow = row;
                    row.classList.add("fsc-dragging");
                    if (event.dataTransfer) {
                        event.dataTransfer.effectAllowed = "move";
                        event.dataTransfer.setData("text/plain", row.getAttribute("data-id"));
                    }
                });

                tableBody.addEventListener("dragend", function () {
                    if (draggedRow) {
                        draggedRow.classList.remove("fsc-dragging");
                    }
                    draggedRow = null;
                    updateOrderInput();
                });

                tableBody.addEventListener("dragover", function (event) {
                    event.preventDefault();
                    if (!draggedRow) {
                        return;
                    }

                    var afterElement = getDragAfterElement(tableBody, event.clientY);
                    if (afterElement === null) {
                        tableBody.appendChild(draggedRow);
                    } else if (afterElement !== draggedRow) {
                        tableBody.insertBefore(draggedRow, afterElement);
                    }
                });

                tableBody.addEventListener("drop", function (event) {
                    event.preventDefault();
                    updateOrderInput();
                });

                updateOrderInput();
            })();
        </script>
        </div>';

        return $html;
    }

    public function hookActionFrontControllerSetMedia()
    {
        if (!isset($this->context->controller) || $this->context->controller->php_self !== 'index') {
            return;
        }

        $this->context->controller->registerStylesheet(
            'module-' . $this->name . '-front',
            'modules/' . $this->name . '/views/css/front.css',
            array(
                'media' => 'all',
                'priority' => 150,
            )
        );
    }

    public function hookDisplayHome($params)
    {
        return $this->renderWidget(null, array());
    }

    public function renderWidget($hookName = null, array $configuration = array())
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId())) {
            $vars = $this->getWidgetVariables($hookName, $configuration);
            if ($vars === false) {
                return '';
            }
            $this->context->smarty->assign($vars);
        }

        return $this->fetch($this->templateFile, $this->getCacheId());
    }

    public function getWidgetVariables($hookName = null, array $configuration = array())
    {
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;
        $blocksData = $this->getBlocks();
        $blocks = array();

        foreach ($blocksData as $blockData) {
            $idParent = (int) $blockData['parent_id'];
            if (!$idParent) {
                continue;
            }

            $parent = new Category($idParent, $idLang, $idShop);
            if (!Validate::isLoadedObject($parent)) {
                continue;
            }

            $subcategories = array();
            $selected = trim((string) $blockData['subcats']);

            if ($selected !== '') {
                $ids = array_map('intval', array_filter(array_map('trim', explode(',', $selected))));
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
                    $name = is_array($cat->name) ? $cat->name[$idLang] : $cat->name;

                    $subcategories[] = array(
                        'id_category' => (int) $cat->id,
                        'name' => $name,
                        'link_rewrite' => $linkRewrite,
                        'description' => $description,
                    );
                }
            } else {
                $subcategories = $parent->getSubCategories($idLang, true);
            }

            if (!is_array($subcategories) || !count($subcategories)) {
                continue;
            }

            $blocks[] = array(
                'parent' => $parent,
                'subcategories' => $subcategories,
            );
        }

        if (!count($blocks)) {
            return false;
        }

        return array(
            'fsc_blocks' => $blocks,
            'link' => $this->context->link,
        );
    }

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
