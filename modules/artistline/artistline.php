<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class ArtistLine extends Module
{
    public function __construct()
    {
        $this->name = 'artistline';
        $this->version = '1.0.4';
        $this->author = 'you';
        $this->tab = 'front_office_features';

        parent::__construct();

        $this->displayName = 'Artist Line';
        $this->description = 'Expose artist category (child of "Artist") on product page.';
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayProductAdditionalInfo')
            && $this->registerHook('artistlink');
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        return $this->renderArtist($params, 'AdditionalInfo');
    }

    public function hookartistlink($params)
    {
        return $this->renderArtist($params, 'ArtistLink');
    }

    /**
     * Find the parent category ID for "Artist" dynamically.
     * First tries exact category name = Artist
     * Then tries SEO slug = artist
     */
    private function getArtistParentId()
    {
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;

        $artistName = pSQL('Artist');
        $artistSlug = pSQL(Tools::link_rewrite('Artist'));

        $queries = [
            '
            SELECT c.id_category
            FROM ' . _DB_PREFIX_ . 'category c
            INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
                ON cl.id_category = c.id_category
            WHERE cl.name = "' . $artistName . '"
            ORDER BY (cl.id_lang = ' . $idLang . ') DESC,
                     (cl.id_shop = ' . $idShop . ') DESC,
                     c.id_category ASC
            ',
            '
            SELECT c.id_category
            FROM ' . _DB_PREFIX_ . 'category c
            INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
                ON cl.id_category = c.id_category
            WHERE cl.link_rewrite = "' . $artistSlug . '"
            ORDER BY (cl.id_lang = ' . $idLang . ') DESC,
                     (cl.id_shop = ' . $idShop . ') DESC,
                     c.id_category ASC
            ',
        ];

        foreach ($queries as $sql) {
            $idCategory = (int) $db->getValue($sql);
            if ($idCategory > 0) {
                return $idCategory;
            }
        }

        return 0;
    }

    private function renderArtist($params, $hookLabel)
    {
        $artistParentId = (int) $this->getArtistParentId();
        if ($artistParentId <= 0) {
            return $this->marker($hookLabel . ': Artist parent not found');
        }

        $product = isset($params['product']) ? $params['product'] : null;
        if (!$product) {
            return $this->marker($hookLabel . ': no product');
        }

        $idProduct = 0;

        if (is_array($product)) {
            $idProduct = (int) (isset($product['id_product']) ? $product['id_product'] : (isset($product['id']) ? $product['id'] : 0));
        } elseif (is_object($product)) {
            $idProduct = (int) (isset($product->id) ? $product->id : (isset($product->id_product) ? $product->id_product : 0));
        }

        if ($idProduct <= 0) {
            return $this->marker($hookLabel . ': no pid');
        }

        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;

        $sql = '
            SELECT c.id_category, cl.name
            FROM ' . _DB_PREFIX_ . 'category_product cp
            INNER JOIN ' . _DB_PREFIX_ . 'category c
                ON c.id_category = cp.id_category
            INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
                ON cl.id_category = c.id_category
               AND cl.id_lang = ' . $idLang . '
               AND cl.id_shop = ' . $idShop . '
            WHERE cp.id_product = ' . (int) $idProduct . '
              AND c.id_parent = ' . (int) $artistParentId . '
            ORDER BY cl.name ASC
        ';

        $artists = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!is_array($artists)) {
            $artists = [];
        }

        $this->context->smarty->assign([
            'artistline_artists' => $artists,
            'product' => $product,
        ]);

        return $this->display(__FILE__, 'artistline.tpl');
    }

    private function marker($text)
    {
        return '<div style="margin:8px 0;padding:6px 8px;border:1px solid #e88;background:#fee;color:#b00;font-size:13px">
            ArtistLine: ' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '
        </div>';
    }
}