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


namespace Sendinblue\Services;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderService
{
    private $idShop;
    private $psVersion;

    public function __construct()
    {
        $this->idShop = \ContextCore::getContext()->shop->id;
        $this->psVersion = version_compare(_PS_VERSION_, '1.7.7') <= 0 ? 'old' : 'new';
    }

    public function getTotalOrderCount()
    {
        $query = $this->buildOrderCountQuery();

        try {
            $result = \DbCore::getInstance()->getValue($query);
            return (int) $result;
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog($e->getMessage(), 3);
            return 0;
        }
    }

    public function getOrders($limit, $offset)
    {
        $baseQuery = $this->buildBaseQuery();
        $query = sprintf('%s LIMIT %d, %d', $baseQuery, $offset, $limit);

        try {
            $orders = \DbCore::getInstance()->executeS($query);

            if (empty($orders)) {
                return ['orders' => []];
            }

            return ['orders' => array_map([$this, 'processOrder'], $orders)];
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog($e->getMessage(), 3);
            return ['orders' => []];
        }
    }

    private function buildOrderCountQuery()
    {
        return sprintf(
            'SELECT COUNT(*) AS `count` FROM `%sorders` WHERE `id_shop` = %d',
            _DB_PREFIX_,
            $this->idShop
        );
    }

    private function buildBaseQuery()
    {
        $refundedField = $this->getRefundedField();
        $orderDetailJoin = $this->getOrderDetailJoin();
        $productFields = $this->getJsonOrConcatSQL(\Db::getInstance()->getVersion());

        return sprintf(
            "SELECT 
                o.`id_order`, o.`reference`, o.`total_paid_tax_incl` AS total_paid, 
                o.`date_add`, o.`date_upd`, o.`current_state`, %s,
                c.`firstname`, c.`lastname`, c.`email`, 
                CONCAT(a.`address1`, ' ', a.`address2`) AS address,
                a.`city`, a.`postcode`, a.`phone`,
                co.`iso_code` AS countryCode, s.`name` AS region,
                b.`name` AS order_status, o.`payment` AS paymentMethod,
                o.`conversion_rate` AS conversion_rate,
                GROUP_CONCAT(DISTINCT cr_lang.`name` SEPARATOR ', ') AS distinct_coupons,
                GROUP_CONCAT(
                    $productFields
                ) AS products_json
            FROM `%sorders` o
            LEFT JOIN `%scustomer` c ON o.`id_customer` = c.`id_customer`
            LEFT JOIN `%saddress` a ON o.`id_address_invoice` = a.`id_address`
            LEFT JOIN `%sorder_state_lang` b ON b.`id_order_state` = o.`current_state` AND b.`id_lang` = 1
            LEFT JOIN `%scountry` co ON co.`id_country` = a.`id_country`
            LEFT JOIN `%sstate` s ON s.`id_state` = a.`id_state`
            LEFT JOIN `%sorder_cart_rule` ocr ON ocr.`id_order` = o.`id_order`
            LEFT JOIN `%scart_rule_lang` cr_lang ON ocr.`id_cart_rule` = cr_lang.`id_cart_rule` AND cr_lang.`id_lang` = 1
            LEFT JOIN `%sorder_detail` od ON od.`id_order` = o.`id_order`
            $orderDetailJoin    
            LEFT JOIN `%sproduct` p ON p.`id_product` = od.`product_id`
            LEFT JOIN `%sproduct_lang` p_lang ON p.`id_product` = p_lang.`id_product` AND p_lang.`id_lang` = 1 AND p_lang.`id_shop` = %d
            WHERE o.`id_shop` = %d AND o.`current_state` != 0
            GROUP BY o.`id_order`",
            $refundedField,
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            $this->idShop,
            $this->idShop
        );
    }

    private function getRefundedField()
    {
        if ($this->psVersion === 'old') {
            return 'SUM(osd.`amount_tax_incl`) as refunded';
        }
        return 'SUM(od.`total_refunded_tax_incl`) as refunded';
    }

    private function getOrderDetailJoin()
    {
        if ($this->psVersion === 'old') {
            return sprintf('LEFT JOIN `%sorder_slip_detail` osd ON (od.`id_order_detail` = osd.`id_order_detail`)', _DB_PREFIX_);
        }
        return '';
    }

    private function getJsonOrConcatSQL($dbVersion)
    {
        $functionName = 'JSON_OBJECT';
        if (stripos($dbVersion, 'MariaDB') !== false && version_compare($dbVersion, '10.2.3', '<')) {
            $functionName = 'CONCAT';
        }

        return "$functionName(
                'id_product', od.`product_id`,
                'id_variant', od.`product_attribute_id`,
                'name', p_lang.`name`,
                'price', od.`product_price`,
                'quantity', od.`product_quantity`
            )";
    }

    private function processOrder($order)
    {
        $conversionRate = (isset($order['conversion_rate']) && $order['conversion_rate'] != 0) 
                            ?  $order['conversion_rate'] : 1 ;
        $order['date_add'] = gmdate('Y-m-d\TH:i:s', strtotime($order['date_add']));
        $order['date_upd'] = gmdate('Y-m-d\TH:i:s', strtotime($order['date_upd']));

        // Prepare billing info
        $order['billing'] = $this->prepareBillingInfo($order);

        // Process products
        $order['products'] = $this->processProducts($order['products_json'], $conversionRate);
        unset($order['products_json']);

        // Process coupons
        $order['coupons'] = !empty($order['distinct_coupons']) ? explode(', ', $order['distinct_coupons']) : [];
        unset($order['distinct_coupons']);

        // Adjust total paid
        $order['total_paid'] = $this->adjustTotalPaid($order['total_paid'], $order['refunded'], $conversionRate, $order['current_state']);
        unset($order['refunded']);

        // Remove redundant fields from the root level
        unset($order['address'], $order['city'], $order['countryCode'], $order['paymentMethod'], $order['phone'], $order['postcode'], $order['region']);

        return $order;
    }

    private function prepareBillingInfo($order)
    {
        return [
            'address' => $order['address'],
            'city' => $order['city'],
            'postCode' => $order['postcode'],
            'phone' => $order['phone'],
            'countryCode' => $order['countryCode'],
            'paymentMethod' => $order['paymentMethod'],
            'region' => $order['region'],
        ];
    }

    private function processProducts($productsJson, $conversionRate)
    {
        $products = json_decode('[' . $productsJson . ']', true);
        if (!empty($products)) {
            foreach ($products as &$product) {
                $product['price'] = round($product['price'] / $conversionRate, 2);
            }
        }
        return $products ?: [];
    }

    private function adjustTotalPaid($totalPaid, $refunded, $conversionRate, $currentState)
    {
        return ($currentState == 8 || $currentState == 7 || $currentState == 6)
            ? '0'
            : (string)round(($totalPaid - $refunded) / $conversionRate, 2);
    }
}
