<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Promobar extends Module
{
    const CONF_ENABLED   = 'PROMOBAR_ENABLED';
    const CONF_HEIGHT    = 'PROMOBAR_HEIGHT';

    const P1_TITLE       = 'PROMOBAR_P1_TITLE';
    const P1_TEXT        = 'PROMOBAR_P1_TEXT';
    const P1_DETAILS     = 'PROMOBAR_P1_DETAILS';
    const P1_CTA_URL     = 'PROMOBAR_P1_CTA_URL';
    const P1_CTA_LABEL   = 'PROMOBAR_P1_CTA_LABEL';

    const P2_TITLE       = 'PROMOBAR_P2_TITLE';
    const P2_TEXT        = 'PROMOBAR_P2_TEXT';
    const P2_DETAILS     = 'PROMOBAR_P2_DETAILS';
    const P2_CTA_URL     = 'PROMOBAR_P2_CTA_URL';
    const P2_CTA_LABEL   = 'PROMOBAR_P2_CTA_LABEL';

    public function __construct()
    {
        $this->name = 'promobar';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'Custom';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Promo Bar (DisplayBanner)');
        $this->description = $this->l('Fixed promo bar with rotating promos + details modal, configurable from Back Office.');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayBanner')
            && $this->registerHook('displayHeader')
            && $this->setDefaults();
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->deleteConfig();
    }

    private function setDefaults()
    {
        Configuration::updateValue(self::CONF_ENABLED, 1);
        Configuration::updateValue(self::CONF_HEIGHT, 40);

        Configuration::updateValue(self::P1_TITLE, 'Trending Artists Sale');
        Configuration::updateValue(self::P1_TEXT, 'Trending Artists Sale | 45% Off Sitewide | Extra 10% Off When You Buy 2+');
        Configuration::updateValue(
            self::P1_DETAILS,
            '<p><strong>Trending Artists Sale</strong></p><p>45% Off Sitewide (discounted prices are shown as marked).</p><p>Extra 10% Off will be shown at checkout once minimum order requirement of 2 units or more is met.</p><p>eGift Cards are excluded from the offer.</p>',
            true
        );
        Configuration::updateValue(self::P1_CTA_URL, '/');
        Configuration::updateValue(self::P1_CTA_LABEL, 'SHOP NOW');

        Configuration::updateValue(self::P2_TITLE, 'Free Shipping');
        Configuration::updateValue(self::P2_TEXT, 'Free Shipping on $100+ Orders');
        Configuration::updateValue(
            self::P2_DETAILS,
            '<p>Discounted prices are shown as marked.</p><p>Additional 10% off will be shown at checkout once minimum order requirement of 2 units or more is met.</p><p>eGift Cards are excluded from the offer.</p><p>Free Shipping on $100+ orders applies to the 48 contiguous United States only.</p>',
            true
        );
        Configuration::updateValue(self::P2_CTA_URL, '/');
        Configuration::updateValue(self::P2_CTA_LABEL, 'SHOP NOW');

        return true;
    }

    private function deleteConfig()
    {
        $keys = [
            self::CONF_ENABLED, self::CONF_HEIGHT,
            self::P1_TITLE, self::P1_TEXT, self::P1_DETAILS, self::P1_CTA_URL, self::P1_CTA_LABEL,
            self::P2_TITLE, self::P2_TEXT, self::P2_DETAILS, self::P2_CTA_URL, self::P2_CTA_LABEL,
        ];

        foreach ($keys as $k) {
            Configuration::deleteByName($k);
        }
        return true;
    }

    private function getPromosData()
    {
        $promos = [];

        $p1Text = trim((string) Configuration::get(self::P1_TEXT));
        if ($p1Text !== '') {
            $promos[] = [
                'title' => (string) Configuration::get(self::P1_TITLE),
                'text' => $p1Text,
                'detailsHtml' => (string) Configuration::get(self::P1_DETAILS),
                'ctaUrl' => (string) Configuration::get(self::P1_CTA_URL),
                'ctaLabel' => (string) Configuration::get(self::P1_CTA_LABEL),
            ];
        }

        $p2Text = trim((string) Configuration::get(self::P2_TEXT));
        if ($p2Text !== '') {
            $promos[] = [
                'title' => (string) Configuration::get(self::P2_TITLE),
                'text' => $p2Text,
                'detailsHtml' => (string) Configuration::get(self::P2_DETAILS),
                'ctaUrl' => (string) Configuration::get(self::P2_CTA_URL),
                'ctaLabel' => (string) Configuration::get(self::P2_CTA_LABEL),
            ];
        }

        return $promos;
    }

    public function getContent()
    {
        $out = '';

        if (Tools::isSubmit('submitPromobar')) {
            $enabled = (int) Tools::getValue(self::CONF_ENABLED);
            $height  = (int) Tools::getValue(self::CONF_HEIGHT);

            $height = max(28, min(80, $height)); // keep sensible

            Configuration::updateValue(self::CONF_ENABLED, $enabled);
            Configuration::updateValue(self::CONF_HEIGHT, $height);

            Configuration::updateValue(self::P1_TITLE, Tools::getValue(self::P1_TITLE));
            Configuration::updateValue(self::P1_TEXT, Tools::getValue(self::P1_TEXT));
            Configuration::updateValue(self::P1_DETAILS, Tools::getValue(self::P1_DETAILS), true);
            Configuration::updateValue(self::P1_CTA_URL, Tools::getValue(self::P1_CTA_URL));
            Configuration::updateValue(self::P1_CTA_LABEL, Tools::getValue(self::P1_CTA_LABEL));

            Configuration::updateValue(self::P2_TITLE, Tools::getValue(self::P2_TITLE));
            Configuration::updateValue(self::P2_TEXT, Tools::getValue(self::P2_TEXT));
            Configuration::updateValue(self::P2_DETAILS, Tools::getValue(self::P2_DETAILS), true);
            Configuration::updateValue(self::P2_CTA_URL, Tools::getValue(self::P2_CTA_URL));
            Configuration::updateValue(self::P2_CTA_LABEL, Tools::getValue(self::P2_CTA_LABEL));

            $out .= $this->displayConfirmation($this->l('Settings updated.'));
        }

        return $out . $this->renderForm();
    }

    private function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Promo Bar Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable promo bar'),
                        'name' => self::CONF_ENABLED,
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')],
                            ['id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled')],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Bar height (px)'),
                        'name' => self::CONF_HEIGHT,
                        'class' => 'fixed-width-sm',
                        'desc' => $this->l('Recommended: 34–40.'),
                    ],

                    ['type' => 'html', 'name' => 'sep1', 'html_content' => '<hr><h3>Promo 1</h3>'],
                    ['type' => 'text', 'label' => $this->l('Title'), 'name' => self::P1_TITLE],
                    ['type' => 'text', 'label' => $this->l('Bar text'), 'name' => self::P1_TEXT],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Details HTML'),
                        'name' => self::P1_DETAILS,
                        'rows' => 6,
                        'desc' => $this->l('HTML allowed. Keep it simple: <p>...</p>'),
                    ],
                    ['type' => 'text', 'label' => $this->l('CTA URL'), 'name' => self::P1_CTA_URL],
                    ['type' => 'text', 'label' => $this->l('CTA Label'), 'name' => self::P1_CTA_LABEL],

                    ['type' => 'html', 'name' => 'sep2', 'html_content' => '<hr><h3>Promo 2</h3><p>Tip: leave Bar text empty to disable Promo 2.</p>'],
                    ['type' => 'text', 'label' => $this->l('Title'), 'name' => self::P2_TITLE],
                    ['type' => 'text', 'label' => $this->l('Bar text'), 'name' => self::P2_TEXT],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Details HTML'),
                        'name' => self::P2_DETAILS,
                        'rows' => 6,
                        'desc' => $this->l('HTML allowed. Keep it simple: <p>...</p>'),
                    ],
                    ['type' => 'text', 'label' => $this->l('CTA URL'), 'name' => self::P2_CTA_URL],
                    ['type' => 'text', 'label' => $this->l('CTA Label'), 'name' => self::P2_CTA_LABEL],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPromobar';
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value = $this->getConfigValues();

        return $helper->generateForm([$fields_form]);
    }

    private function getConfigValues()
    {
        return [
            self::CONF_ENABLED => (int) Configuration::get(self::CONF_ENABLED),
            self::CONF_HEIGHT  => (int) Configuration::get(self::CONF_HEIGHT),

            self::P1_TITLE     => Configuration::get(self::P1_TITLE),
            self::P1_TEXT      => Configuration::get(self::P1_TEXT),
            self::P1_DETAILS   => Configuration::get(self::P1_DETAILS),
            self::P1_CTA_URL   => Configuration::get(self::P1_CTA_URL),
            self::P1_CTA_LABEL => Configuration::get(self::P1_CTA_LABEL),

            self::P2_TITLE     => Configuration::get(self::P2_TITLE),
            self::P2_TEXT      => Configuration::get(self::P2_TEXT),
            self::P2_DETAILS   => Configuration::get(self::P2_DETAILS),
            self::P2_CTA_URL   => Configuration::get(self::P2_CTA_URL),
            self::P2_CTA_LABEL => Configuration::get(self::P2_CTA_LABEL),
        ];
    }

    /**
     * PrestaShop 8+ front hook for assets.
     */
    public function hookDisplayHeader($params)
    {
        if (!(int) Configuration::get(self::CONF_ENABLED)) {
            return '';
        }

        $promos = $this->getPromosData();
        if (!count($promos)) {
            return '';
        }

        // Register assets (PS 1.7/8)
        if (isset($this->context) && isset($this->context->controller) && $this->context->controller) {
            $css = 'modules/' . $this->name . '/views/css/promobar.css';
            $js  = 'modules/' . $this->name . '/views/js/promobar.js';

            if (method_exists($this->context->controller, 'registerStylesheet')) {
                $this->context->controller->registerStylesheet(
                    'module-' . $this->name,
                    $css,
                    ['media' => 'all', 'priority' => 150]
                );
            } else {
                $this->context->controller->addCSS($css);
            }

            if (method_exists($this->context->controller, 'registerJavascript')) {
                $this->context->controller->registerJavascript(
                    'module-' . $this->name,
                    $js,
                    ['position' => 'bottom', 'priority' => 200]
                );
            } else {
                $this->context->controller->addJS($js);
            }
        }

        $height = (int) Configuration::get(self::CONF_HEIGHT);

        if (class_exists('Media')) {
            Media::addJsDef([
                'PROMOBAR_DATA' => [
                    'height' => $height,
                    'promos' => $promos,
                ],
            ]);
        }

        return '';
    }

    public function hookDisplayBanner($params)
    {
        if (!(int) Configuration::get(self::CONF_ENABLED)) {
            return '';
        }

        $promos = $this->getPromosData();
        if (!count($promos)) {
            return '';
        }

        // Use display() for maximum compatibility (Smarty)
        return $this->display(__FILE__, 'displayBanner.tpl');
    }
}
