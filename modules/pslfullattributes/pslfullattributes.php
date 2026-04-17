<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Pslfullattributes extends Module
{
    public function __construct()
    {
        $this->name = 'pslfullattributes';
        $this->tab = 'front_office_features';
        $this->version = '1.0.5';
        $this->author = 'Custom';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Shape attribute in product listing');
        $this->description = $this->l('Adds the Shape combination of the default product combination to the product listing array.');
        $this->ps_versions_compliancy = array('min' => '8.0.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionPresentProductListing');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Called for EACH product in a listing (category, search, etc.).
     * $params['presentedProduct'] is a ProductListingLazyArray.
     */
    public function hookActionPresentProductListing($params)
    {
        if (empty($params['presentedProduct'])) {
            return;
        }

        // Lazy array object implementing ArrayAccess
        $productLazy = $params['presentedProduct'];

        if (!isset($productLazy['id_product']) || (int) $productLazy['id_product'] <= 0) {
            $productLazy['ps_shape_label'] = '';
            $productLazy['ps_shape_value'] = '';
            return;
        }

        $idProduct = (int) $productLazy['id_product'];

        $context = \Context::getContext();
        $idLang = (int) $context->language->id;

        $productObj = new \Product($idProduct, false, $idLang);
        if (!\Validate::isLoadedObject($productObj)) {
            $productLazy['ps_shape_label'] = '';
            $productLazy['ps_shape_value'] = '';
            return;
        }

        $attrs = $productObj->getAttributesGroups($idLang);
        if (!is_array($attrs) || empty($attrs)) {
            $productLazy['ps_shape_label'] = '';
            $productLazy['ps_shape_value'] = '';
            return;
        }

        $shapeLabel = '';
        $shapeValue = '';

        // 1st: try to use Shape of DEFAULT combination
        foreach ($attrs as $row) {
            if (
                isset($row['group_name'], $row['attribute_name'], $row['default_on'])
                && \Tools::strtolower($row['group_name']) === 'shape'
                && (int) $row['default_on'] === 1
            ) {
                $shapeLabel = $row['attribute_name'];
                $shapeValue = \Tools::link_rewrite($row['attribute_name']); // slug
                break;
            }
        }

        // Fallback: first Shape attribute we find
        if ($shapeLabel === '') {
            foreach ($attrs as $row) {
                if (isset($row['group_name'], $row['attribute_name'])
                    && \Tools::strtolower($row['group_name']) === 'shape'
                ) {
                    $shapeLabel = $row['attribute_name'];
                    $shapeValue = \Tools::link_rewrite($row['attribute_name']);
                    break;
                }
            }
        }

        // Store safe custom keys (no conflict with LazyArray methods)
        $productLazy['ps_shape_label'] = $shapeLabel;
        $productLazy['ps_shape_value'] = $shapeValue;
    }
}
