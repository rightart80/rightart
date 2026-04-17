<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RelatedCategories extends Module
{
    public function __construct()
    {
        $this->name = 'relatedcategories';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'You';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Related Categories');
        $this->description = $this->l('Adds a related_categories field to categories and exposes it on category pages.');
    }

    public function install()
    {
        return parent::install()
            && $this->createTables()
            && $this->registerHook('actionCategoryFormBuilderModifier')
            && $this->registerHook('actionAfterCreateCategoryFormHandler')
            && $this->registerHook('actionAfterUpdateCategoryFormHandler')
            && $this->registerHook('displayHeader'); // inject variable on FO category pages
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->dropTables();
    }

    /**
     * Create module table: {prefix}related_category
     */
    protected function createTables()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'related_category` (
                    `id_category` INT(10) UNSIGNED NOT NULL,
                    `id_related_category` INT(10) UNSIGNED NOT NULL,
                    PRIMARY KEY (`id_category`, `id_related_category`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Drop module table
     */
    protected function dropTables()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'related_category`';
        return Db::getInstance()->execute($sql);
    }

    /* -----------------------------
     *  BACK OFFICE – CATEGORY FORM
     * ----------------------------- */

    /**
     * Add "Related categories" checklist to category form
     */
    public function hookActionCategoryFormBuilderModifier($params)
    {
        if (!isset($params['form_builder'])) {
            return;
        }

        /** @var \Symfony\Component\Form\FormBuilderInterface $formBuilder */
        $formBuilder = $params['form_builder'];

        // Base form data
        $data = isset($params['data']) && is_array($params['data']) ? $params['data'] : [];

        // Current category ID (0 on create)
        $idCategory = isset($params['id']) ? (int) $params['id'] : 0;

        // Get list of all categories as choices, excluding the current category itself
        $choices = $this->getCategoryChoices($idCategory);

        // Already-related categories for this category
        $selected = [];
        if ($idCategory) {
            $selected = $this->getRelatedCategoryIds($idCategory);
        }

        // Make sure our field is in the data array
        $data['related_categories'] = $selected;

        // Build a help text listing the currently related categories
        $helpText = '';
        if (!empty($selected)) {
            $idLang = (int) $this->context->language->id;
            $names  = [];

            foreach ($selected as $idRel) {
                $cat = new Category($idRel, $idLang);
                if (Validate::isLoadedObject($cat)) {
                    $names[] = sprintf('%s (ID: %d)', $cat->name, (int) $cat->id);
                }
            }

            if (!empty($names)) {
                $helpText = $this->l('Currently related categories: ') . implode(', ', $names);
            }
        }

        /**
         * IMPORTANT:
         * - multiple => true : field holds an array of selected IDs
         * - expanded => true : show as a checklist (checkboxes) instead of a dropdown
         */
        $formBuilder->add('related_categories', ChoiceType::class, [
            'label'    => $this->l('Related categories'),
            'required' => false,
            'multiple' => true,    // multiple values
            'expanded' => true,    // rendered as checkboxes
            'choices'  => $choices, // ['Category name (ID: x)' => id_category]
            'data'     => $selected, // pre-checked boxes
            'translation_domain' => false,
            'help'     => $helpText,
        ]);

        // Re-bind updated data to the form
        $params['data'] = $data;
        $formBuilder->setData($data);
    }

    /**
     * After creating a category, save related_categories
     */
    public function hookActionAfterCreateCategoryFormHandler($params)
    {
        $idCategory = (int) $params['id'];
        $formData = isset($params['form_data']) ? $params['form_data'] : [];

        $related = isset($formData['related_categories']) ? (array) $formData['related_categories'] : [];
        $this->saveRelatedCategories($idCategory, $related);
    }

    /**
     * After updating a category, save related_categories
     */
    public function hookActionAfterUpdateCategoryFormHandler($params)
    {
        $idCategory = (int) $params['id'];
        $formData = isset($params['form_data']) ? $params['form_data'] : [];

        $related = isset($formData['related_categories']) ? (array) $formData['related_categories'] : [];
        $this->saveRelatedCategories($idCategory, $related);
    }

    /**
     * Save related categories in our table (replace old values)
     */
    protected function saveRelatedCategories($idCategory, array $relatedIds)
    {
        $idCategory = (int) $idCategory;
        $table = 'related_category';

        // Remove all old relations for this category
        Db::getInstance()->delete($table, 'id_category = ' . $idCategory);

        if (!empty($relatedIds)) {
            $values = [];
            foreach ($relatedIds as $idRelated) {
                $idRelated = (int) $idRelated;
                if ($idRelated > 0 && $idRelated !== $idCategory) {
                    $values[] = [
                        'id_category'        => $idCategory,
                        'id_related_category' => $idRelated,
                    ];
                }
            }

            if (!empty($values)) {
                Db::getInstance()->insert($table, $values);
            }
        }
    }

    /**
     * Return array of ['Category name (ID: x)' => id_category]
     * Optionally exclude a category (for avoiding self-relation).
     *
     * @param int $excludeIdCategory
     *
     * @return array
     */
    protected function getCategoryChoices($excludeIdCategory = 0)
    {
        $idLang = (int) $this->context->language->id;
        $categories = Category::getSimpleCategories($idLang);
        $choices = [];

        if (is_array($categories)) {
            foreach ($categories as $cat) {
                $idCat = (int) $cat['id_category'];

                // Avoid letting the category relate to itself
                if ($excludeIdCategory && $idCat === (int) $excludeIdCategory) {
                    continue;
                }

                $name = sprintf('%s (ID: %d)', $cat['name'], $idCat);
                $choices[$name] = $idCat;
            }
        }

        return $choices;
    }

    /**
     * Get related category IDs for a given category
     *
     * @param int $idCategory
     *
     * @return int[]
     */
    protected function getRelatedCategoryIds($idCategory)
    {
        $sql = 'SELECT id_related_category 
                FROM `' . _DB_PREFIX_ . 'related_category`
                WHERE id_category = ' . (int) $idCategory;

        $rows = Db::getInstance()->executeS($sql);
        if (!$rows) {
            return [];
        }

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) $row['id_related_category'];
        }
        return $ids;
    }

    /* -----------------------------
     *  FRONT OFFICE – INJECT VARIABLE
     * ----------------------------- */

    /**
     * On category pages, load related categories and assign $related_categories for Smarty.
     */
    public function hookDisplayHeader($params)
    {
        if (!isset($this->context->controller)) {
            return;
        }

        // Only on category pages
        if (!($this->context->controller instanceof CategoryControllerCore)) {
            return;
        }

        if (!method_exists($this->context->controller, 'getCategory')) {
            return;
        }

        $category = $this->context->controller->getCategory();
        if (!$category || !Validate::isLoadedObject($category)) {
            return;
        }

        $idLang = (int) $this->context->language->id;
        $relatedIds = $this->getRelatedCategoryIds((int) $category->id);

        if (empty($relatedIds)) {
            return;
        }

        $relatedCategories = [];
        foreach ($relatedIds as $idRel) {
            $cat = new Category($idRel, $idLang);
            if (!Validate::isLoadedObject($cat) || !$cat->active) {
                continue;
            }

            $relatedCategories[] = [
                'id'   => (int) $cat->id,
                'name' => $cat->name,
                'link' => $this->context->link->getCategoryLink($cat),
            ];
        }

        if (!empty($relatedCategories)) {
            // This is the Smarty variable you will use in category.tpl
            $this->context->smarty->assign('related_categories', $relatedCategories);
        }
    }
}
