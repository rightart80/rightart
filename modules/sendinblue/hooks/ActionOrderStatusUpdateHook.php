<?php
/**
 * 2007-2025 Sendinblue
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@sendinblue.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Sendinblue <contact@sendinblue.com>
 * @copyright 2007-2025 Sendinblue
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of Sendinblue
 */

namespace Sendinblue\Hooks;

use Sendinblue\Services\ApiClientService;
use Sendinblue\Services\ConfigService;
use Sendinblue\Services\SmsService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ActionOrderStatusUpdateHook extends AbstractHook
{
    const ORDER_AWAITING_CHECK_PAYMENT = 1; // order paid
    const ORDER_PAYMENT_ACCEPTED = 2; // order paid
    const ORDER_PROCESSING_STATUS = 3; // order create
    const ORDER_SHIPPED_STATUS = 4; // order paid
    const ORDER_DELIVERED_STATUS = 5; // order paid
    const ORDER_CANCELLED_STATUS = 6;  // order cancelled
    const ORDER_REFUNDED_STATUS = 7;  // order refunded
    const ORDER_PAYMENT_ERROR_STATUS = 8;  // order create
    const ORDER_BACKORDER_PAID_STATUS = 9; // order paid
    const ORDER_AWAITING_BANKWIRE_STATUS = 10; // order create
    const ORDER_REMOTE_PAYMENT_ACCEPTED_STATUS = 11; // order paid
    const ORDER_BACKORDER_UNPAID_STATUS = 12;  // order create
    const ORDER_COD_VALIDATION_STATUS = 13;  // order create

    const PS_EN_LANG_ID = 1;

    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var SmsService
     */
    private $smsService;

    public function __construct()
    {
        $this->idShop = \ContextCore::getContext()->shop->id;
    }

    /**
     * @param \OrderCore|\Shipping $order
     */
    public function handleEvent($order)
    {
        try {
            $shopGroup = \Shop::getContextShopGroupID(true);
            $idShop = \Shop::getContextShopID(true);
            $smsShippingStatus = $this->getSibConfigService()->getSibConfig(
                ConfigService::CONFIG_SMS_SHIPPING_CONFIRMATION,
                $shopGroup,
                $idShop
            );
            $apiKey = $this->getSibConfigService()->getSibConfig(ConfigService::API_KEY_V3, $shopGroup, $idShop);

            if (!empty($apiKey) && (int) $smsShippingStatus === 1) {
                $this->sendShipmentConfirmationSms($order);
            }

            $configService = $this->getSendinblueConfigService();
            if ($configService->isOrderAutoSyncEnabled()
            ) {
                $this->handleOrderEvents($order);
            }
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    protected function handleOrderEvents($order)
    {
        $idOrderState = !empty($order['newOrderStatus']->id)
            ? $order['newOrderStatus']->id
            : \Tools::getValue('id_order_state');

        $orderStatus = !empty($order['newOrderStatus'])
            ? $order['newOrderStatus'] : 'New';

        $orderEventURI = '';
        $orderCreateStatuses = [
            self::ORDER_AWAITING_CHECK_PAYMENT,
            self::ORDER_PROCESSING_STATUS,
            self::ORDER_PAYMENT_ERROR_STATUS,
            self::ORDER_BACKORDER_UNPAID_STATUS,
            self::ORDER_COD_VALIDATION_STATUS,
            self::ORDER_AWAITING_BANKWIRE_STATUS,
        ];

        $orderPaidStatuses = [
            self::ORDER_PAYMENT_ACCEPTED,
            self::ORDER_SHIPPED_STATUS,
            self::ORDER_DELIVERED_STATUS,
            self::ORDER_BACKORDER_PAID_STATUS,
            self::ORDER_REMOTE_PAYMENT_ACCEPTED_STATUS,
        ];

        if (in_array($idOrderState, $orderCreateStatuses)) {
            $orderEventURI = ApiClientService::EVENT_ORDER_CREATED_URI;
        } elseif (in_array($idOrderState, $orderPaidStatuses)) {
            $orderEventURI = ApiClientService::EVENT_ORDER_PAID_URI;
        } elseif ($idOrderState == self::ORDER_CANCELLED_STATUS) {
            $orderEventURI = ApiClientService::EVENT_ORDER_CANCELLED_URI;
        } elseif ($idOrderState == self::ORDER_REFUNDED_STATUS) {
            $orderEventURI = ApiClientService::EVENT_ORDER_REFUND_URI;
        }
        $orderPayload = $this->prepareOrderPayload($order, $idOrderState);

        $this->getApiClientService()->sendOrderData($orderPayload, $orderEventURI);
    }

    protected function prepareOrderPayload($params, $idOrderState)
    {
        $idOrder = !empty($params['id_order']) ? $params['id_order'] : \Tools::getValue('id_order');
        $order = new \Order($idOrder);
        $customer = new \Customer($order->id_customer);
        $address = new \Address($order->id_address_delivery);
        $state = new \State($address->id_state);
        $country = new \Country($address->id_country);
        $conversion_rate = $order->conversion_rate;
        $orderPayload = [
            'id_order' => $idOrder,
            'order_status' => $params['newOrderStatus']->name,
            'discount_total' => round($order->total_discounts / $conversion_rate, 2),
            'discount_tax' => round(($order->total_discounts_tax_incl - $order->total_discounts_tax_excl) / $conversion_rate, 2),
            'shipping_total' => round($order->total_shipping / $conversion_rate, 2),
            'shipping_tax' => round(($order->total_shipping_tax_incl - $order->total_shipping_tax_excl) / $conversion_rate, 2),
            'total_paid' => (string) round($order->total_paid_tax_incl / $conversion_rate, 2),
            'total_tax' => round(($order->total_paid_tax_incl - $order->total_paid_tax_excl) / $conversion_rate, 2),
            'final_amount' => (string) round($order->total_paid_tax_incl / $conversion_rate, 2),
            'customer_note' => property_exists($order, 'note') ? $order->note : '',
            'date_add' => gmdate('Y-m-d\TH:i:s', strtotime($order->date_add)),
            'date_upd' => gmdate('Y-m-d\TH:i:s', strtotime($order->date_upd)),
            'payment_method' => $order->payment,
            'email' => $customer->email,
            'phone' => $address->phone,
        ];

        // Payment Error or Cancelled or Refunded
        if ($idOrderState == 8 || $idOrderState == 7 || $idOrderState == 6) {
            $orderPayload['total_paid'] = (string) 0;
            $orderPayload['total_tax'] = (string) 0;
            $orderPayload['final_amount'] = (string) 0;
        }

        $orderPayload['shipping']['country_code'] = $country->iso_code;
        $orderPayload['shipping']['country'] = $address->country;
        $orderPayload['billing']['countryCode'] = $country->iso_code;
        $orderPayload['billing']['country'] = $address->country;
        $orderPayload['billing']['city'] = $address->city;
        $orderPayload['billing']['postCode'] = $address->postcode;
        $orderPayload['billing']['region'] = $state->name;
        $orderPayload['billing']['address'] = $address->address1 . ' ' . $address->address2;
        $orderPayload['billing']['phone'] = $address->phone;
        $orderPayload['billing']['paymentMethod'] = $order->payment;

        $orderPayload['products'] = [];
        $productDetails = \OrderDetail::getList((int) $idOrder);

        foreach ($productDetails as $k => $v) {
            $quantity = $v['product_quantity'];
            if (isset($v['product_quantity_refunded']) && $v['product_quantity_refunded'] > 0) {
                $quantity = $v['product_quantity'] - $v['product_quantity_refunded'];
            }

            $items = [
                'name' => $v['product_name'],
                'id_product' => (float) $v['product_id'],
                'quantity' => (float) $v['product_quantity'],
                'price' => (float) round($v['unit_price_tax_incl'] / $conversion_rate, 2),
            ];
            array_push($orderPayload['products'], $items);
        }

        if (!empty($order->getCartRules())) {
            $orderPayload['coupons'] = [];
            foreach ($order->getCartRules() as $k => $v) {
                array_push($orderPayload['coupons'], $v['name']);
            }
        }
        return $orderPayload;
    }

    /**
     * @param \Shipping|\OrderCore $order
     */
    private function sendShipmentConfirmationSms($params)
    {
        $idOrderState = !empty($params['newOrderStatus']->id)
            ? $params['newOrderStatus']->id
            : \Tools::getValue('id_order_state');

        $idOrder = !empty($params['id_order']) ? $params['id_order'] : \Tools::getValue('id_order');

        $shopGroup = \Shop::getContextShopGroupID(true);
        $idShop = \Shop::getContextShopID(true);
        $sender = $this->getSibConfigService()->getSibConfig(
            ConfigService::CONFIG_SMS_SHIPPING_CONFIRMATION_SENDER,
            $shopGroup,
            $idShop
        );
        $message = $this->getSibConfigService()->getSibConfig(
            ConfigService::CONFIG_SMS_SHIPPING_CONFIRMATION_MSG,
            $shopGroup,
            $idShop
        );

        if ($idOrderState == self::ORDER_SHIPPED_STATUS && $message != '' && is_numeric($idOrder) == true) {
            $order = new \Order($idOrder);
            $deliveryAddress = new \AddressCore($order->id_address_delivery);
            $customer = new \Customer($deliveryAddress->id_customer);
            $countryData = new \CountryCore($deliveryAddress->id_country);
            $currency = new \CurrencyCore($order->id_currency);
            $referenceNum = (isset($order->reference)) ? $order->reference : 0;
            $totalPay = (isset($order->total_paid)) ? round($order->total_paid, 2) : 0;
            $totalPay = $totalPay . ' ' . $currency->iso_code;
            $orderDate = (isset($order->date_upd)) ? $order->date_upd : 0;

            if ((int) $order->id_lang === self::PS_EN_LANG_ID) {
                $ordDate = date('m/d/Y', strtotime($orderDate));
            } else {
                $ordDate = date('d/m/Y', strtotime($orderDate));
            }

            if (!empty($customer->id_gender) && !empty($order->id_lang)) {
                $genderName = \Db::getInstance()->getRow('
                    SELECT `name` FROM ' . _DB_PREFIX_ . 'gender_lang 
                    WHERE  `id_lang` = \'' . pSQL($order->id_lang) . '\' 
                    AND `id_gender` = \'' . pSQL($customer->id_gender) . '\'');

                $civility = !empty($genderName['name']) ? $genderName['name'] : '';
            } else {
                $civility = '';
            }

            $firstName = $deliveryAddress->firstname ?: $deliveryAddress->firstname;
            $lastName = $deliveryAddress->lastname ?: $deliveryAddress->lastname;
            if ($deliveryAddress) {
                $phoneSms = $deliveryAddress->phone_mobile ?: $deliveryAddress->phone;

                if (!empty($phoneSms) && !empty($sender) && !empty($message)) {
                    $number = $this->getSibSmsService()->checkMobileNumber($phoneSms, $countryData->call_prefix);

                    $msgBody = $this->getSibSmsService()->renderSmsMessage(
                        $civility,
                        $firstName,
                        $lastName,
                        $totalPay,
                        $ordDate,
                        $referenceNum,
                        $message
                    );

                    $this->getApiClientService()->sibApiRequest(
                        SmsService::API_POST_METHOD,
                        SmsService::SIB_SEND_SMS_URI,
                        [
                            'recipient' => $number,
                            'sender' => $sender,
                            'content' => $msgBody,
                            'type' => SmsService::SENDINBLUE_SMS_TYPE,
                        ]
                    );
                }
            }
        }
    }

    /**
     * @return ConfigService
     */
    protected function getSibConfigService()
    {
        if (!$this->configService) {
            $this->configService = new ConfigService();
        }
        return $this->configService;
    }

    /**
     * @return SmsService
     */
    protected function getSibSmsService()
    {
        if (!$this->smsService) {
            $this->smsService = new SmsService();
        }
        return $this->smsService;
    }
}
