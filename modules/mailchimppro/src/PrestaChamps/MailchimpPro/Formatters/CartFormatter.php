<?php
/**
 * PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */

namespace PrestaChamps\MailchimpPro\Formatters;
if (!defined('_PS_VERSION_')) {
    exit;
}

class CartFormatter
{
    public $cart;
    public $customer;
    public $context;
    protected $campaignId = null;

    protected $idStore;

    /**
     * CartFormatter constructor.
     *
     * @param \Cart     $cart
     * @param \Customer $customer
     * @param \Context  $context
     * @param  string   $campaignId
     */
    public function __construct(\Cart $cart, \Customer $customer, \Context $context, $campaignId = null, $idStore = null)
    {

        $this->cart = $cart;
        $this->customer = $customer;
        $this->context = $context;
        $this->campaignId = $campaignId;
        $this->idStore = $idStore ? $idStore : $this->context->shop->id;
    }

    /**
     * Set campaignId feature
     *
     * @param string $campaign_id
     */
    public function setCampaignId($campaign_id = null)
    {
        $this->campaignId = $campaign_id;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function format()
    {
        $products = $this->cart->getProducts(true);

        if ($this->cart->id_currency) {
            $cart_currency = new \Currency($this->cart->id_currency);
            $store_currency_format = \Tools::strtoupper($cart_currency->iso_code);
        } else {
            $store_currency_format = \Tools::strtoupper(\Currency::getDefaultCurrency()->iso_code);
        }
        
        $data = [
            'id' => (string)$this->cart->id,
            'customer' => ['id' => (string)$this->customer->id],
            'order_total' => (float)$this->cart->getOrderTotal(true, \Cart::BOTH, $products),
            'checkout_url' => $this->context->link->getPageLink('order', null, null, null, false, $this->idStore),
            'currency_code' => $store_currency_format,
            'lines' => [],
        ];

        if(\Configuration::get(\MailchimpProConfig::SYNC_CARTS_PASSW, null, null, $this->idStore) == true){
            $cart_link_passw = $this->context->link->getPageLink(
                    'order',
                    null,
                    (int) $this->cart->id_lang,
                    'step=3&recover_cart=' . $this->cart->id . '&token_cart=' . md5(_COOKIE_KEY_ . 'recover_cart_' . $this->cart->id),
                    false,
                    $this->idStore
                );

            $data['checkout_url'] = $cart_link_passw;
        }

        

        /**
         * Addons Validator note
         *
         * Unfortunately the encrypted cookie functionality can't be used here, because it can't be implemented
         * in both Mailchimp and PrestaShop side
         */

        if ($this->campaignId) {
            $data['campaign_id'] = $this->campaignId;
        }
        else if (isset($_COOKIE['mc_cid']) && !empty($_COOKIE['mc_cid']) && !is_a($this->context->controller, 'AdminController') && !is_subclass_of($this->context->controller, 'AdminController') && !is_a($this->context->controller, 'MailchimpproCronjobModuleFrontController')) {
            $data['campaign_id'] = $_COOKIE['mc_cid'];
        }

        foreach ($products as $prod) {
            $p = new \Product($prod['id_product'], true, $this->context->language->id);
            $price_with_tax = \Product::getPriceStatic(
                $p->id,
                true,
                null,
                2,
                null,
                false,
                true,
                1,
                false,
                null,
                $this->cart->id
            );
            $total_with_tax = $prod['cart_quantity'] * $price_with_tax;

            $data['lines'][] = [
                'id' => (string)$prod['unique_id'],
                'product_id' => (string)$prod['id_product'],
                'product_variant_id' => (string)($prod['id_product_attribute'] == 0
                    ? $prod['id_product'] : $prod['id_product_attribute']),
                'quantity' => (int)$prod['cart_quantity'],
                'price' => $total_with_tax,
            ];
        }

        return $data;
    }
}
