<?php
if (!defined('_PS_VERSION_')) { exit; }

class ArtistLine extends Module
{
    public function __construct()
    {
        $this->name = 'artistline';
        $this->version = '1.0.3';
        $this->author = 'you';
        $this->tab = 'front_office_features';
        parent::__construct();
        $this->displayName = 'Artist Line';
        $this->description = 'Expose artist category (child of "Artist") on product page.';
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install()
            // We hook to BOTH: one that you already see (AdditionalInfo) and
            // one you can place under the H1 (Buttons).
            && $this->registerHook('displayProductAdditionalInfo')
            && $this->registerHook('artistlink');
    }

    // Use same logic for both hooks
    public function hookDisplayProductAdditionalInfo($params) { return $this->renderArtist($params, 'AdditionalInfo'); }
    public function hookartistlink($params)        { return $this->renderArtist($params, 'Buttons'); }

    private function renderArtist($params, $hookLabel)
    {
        // CHANGE if your parent "Artist" category ID differs
        $artistParentId = 10;

        // 1) Robust product id detection
        $product = $params['product'] ?? null;
        if (!$product) {
            return $this->marker("{$hookLabel}: no product");
        }
        $idProduct = 0;
        if (is_array($product)) {
            $idProduct = (int)($product['id_product'] ?? $product['id'] ?? 0);
        } elseif (is_object($product)) {
            $idProduct = (int)($product->id ?? $product->id_product ?? 0);
        }
        if ($idProduct <= 0) {
            return $this->marker("{$hookLabel}: no pid");
        }

        $idLang = (int)$this->context->language->id;

        // 2) Fetch artist categories (children of parent=10)
        $sql = '
            SELECT c.id_category, cl.name
            FROM '._DB_PREFIX_.'category_product cp
            INNER JOIN '._DB_PREFIX_.'category c ON c.id_category = cp.id_category
            INNER JOIN '._DB_PREFIX_.'category_lang cl
                ON cl.id_category = c.id_category AND cl.id_lang = '.(int)$idLang.'
            WHERE cp.id_product = '.(int)$idProduct.'
              AND c.id_parent = '.(int)$artistParentId.'
            ORDER BY cl.name ASC
        ';
        $artists = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!is_array($artists)) { $artists = []; }

        // // 3) Always show a visible marker so you can SEE the hook fired
        // if (!$artists) {
        //     return $this->marker("{$hookLabel}: hook OK, no artist (pid={$idProduct})");
        // }

        // 4) We have artists → render template
        $this->context->smarty->assign(['artistline_artists' => $artists]);
        return $this->display(__FILE__, 'artistline.tpl');
    }

    private function marker($text)
    {
        // Prominent red box so you can't miss it. Remove after verification.
        return '<div style="margin:8px 0;padding:6px 8px;border:1px solid #e88;background:#fee;color:#b00;font-size:13px">
          ArtistLine: '.$text.'
        </div>';
    }
}
