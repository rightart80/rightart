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

class CustomerService
{
    /**
     * @param array $filter
     * @param int $limit
     * @param int $offset
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     */
    public function getCustomers($filter, $limit, $offset)
    {
        $sqlAnd = '';
        if (is_array($filter) && !empty($filter)) {
            foreach ($filter as $field => $value) {
                // Exclude unsubscribe_type from generic filter processing
                if ($field === 'unsubscribe_type') {
                    continue;
                }
                $sqlAnd .= ' AND c.`' . bqSQL($field) . '` = "' . pSQL($value) . '"';
            }
        }
        // Handle unsubscribe_type logic
        // unsubscribe_type = 0 → Not subscribed (never opted in)
        // unsubscribe_type = 1 → Unsubscribed (previously opted in, then opted out)
        $newsletterType = isset($filter['newsletter']) ? (int) $filter['newsletter'] : null;
        if ($newsletterType === 0) {
            $unsubscribeType = isset($filter['unsubscribe_type']) ? (int) $filter['unsubscribe_type'] : null;
            if ($unsubscribeType === 0) {
                // Not subscribed: newsletter = 0 and newsletter_date_add is '0000-00-00 00:00:00'
                $sqlAnd .= ' AND c.`newsletter` = 0 AND c.`newsletter_date_add` = "0000-00-00 00:00:00"';
            } elseif ($unsubscribeType === 1) {
                // Unsubscribed: newsletter = 0 and newsletter_date_add is a valid timestamp
                $sqlAnd .= ' AND c.`newsletter` = 0 AND c.`newsletter_date_add` > "1000-01-01 00:00:00"';
            }
        }

        $shop_id = \Shop::getContextShopID(true);
        if (!empty($shop_id)) {
            $sqlAnd .= ' AND c.`id_shop` = ' . $shop_id;
            $shop_group = \Shop::getContextShopGroupID();
            if (!empty($shop_group)) {
                $sqlAnd .= ' AND c.`id_shop_group` = ' . $shop_group;
            }
        }

        $data = ['customers' => \Db::getInstance()->executeS('
            SELECT DISTINCT 
                c.`id_customer` AS id, 
                LOWER(c.`email`) AS email, 
                c.`firstname`, 
                c.`lastname`, 
                c.`id_default_group`, 
                c.`id_default_group`,
                GROUP_CONCAT(DISTINCT cg.`id_group`) AS id_group, 
                c.`id_gender`, 
                c.`newsletter`, 
                DATE(c.`newsletter_date_add`) AS newsletter_date_add, 
                c.`birthday`, 
                cl.`name` AS country, 
                s.`name` AS state, 
                a.`company`, 
                a.`city`, 
                a.`address1`, 
                a.`address2`, 
                a.`postcode`, 
                a.`vat_number`, 
                DATE(c.`date_add`) AS date_add, 
                DATE(c.`date_upd`) AS date_upd, 
                l.`iso_code` AS id_lang, 
                a.phone AS phone
            FROM `' . _DB_PREFIX_ . 'customer` c
            LEFT JOIN `' . _DB_PREFIX_ . 'customer_group` cg ON (c.`id_customer` = cg.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (c.`id_customer` = a.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'lang` l ON (c.`id_lang` = l.`id_lang`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (a.`id_country` = cl.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` s ON (a.`id_state` = s.`id_state`)
            WHERE c.`active` = 1 ' . $sqlAnd . '
            GROUP BY c.`id_customer`
            ORDER BY c.`id_customer` ASC
            LIMIT ' . (int) $limit . '
            OFFSET ' . (int) $offset . '
        ')];

        $all_groups = \Group::getGroups(\ConfigurationCore::get('PS_LANG_DEFAULT'));

        foreach ($data['customers'] as $key => $value) {
            $data['customers'][$key]['id_group'] = $this->getAllGroupsOfCustomer(explode(',', $value['id_group']), $all_groups);
            $data['customers'][$key]['id_shop'] = $shop_id;
        }

        return $data;
    }

    public function getCustomersCount($filter)
    {
        $customersSubscribedCount = 0;
        $customersBlackslistedCount = 0;
        $sqlAnd = '';
        if (is_array($filter) && !empty($filter)) {
            foreach ($filter as $field => $value) {
                $sqlAnd .= ' AND c.`' . bqSQL($field) . '` = "' . pSQL($value) . '"';
            }
        }

        $shop_id = \Shop::getContextShopID(true);
        if (!empty($shop_id)) {
            $sqlAnd .= ' AND c.`id_shop` = ' . $shop_id;
            $shop_group = \Shop::getContextShopGroupID();
            if (!empty($shop_group)) {
                $sqlAnd .= ' AND c.`id_shop_group` = ' . $shop_group;
            }
        }

        $data = ['customers' => \Db::getInstance()->executeS('
            SELECT DISTINCT c.`id_customer` AS id, c.`newsletter`,
            GROUP_CONCAT(DISTINCT cg.`id_group`) AS id_group
            FROM `' . _DB_PREFIX_ . 'customer` c
            LEFT JOIN `' . _DB_PREFIX_ . 'customer_group` cg ON (c.`id_customer` = cg.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (c.`id_customer` = a.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'lang` l ON (c.`id_lang` = l.`id_lang`)
            LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (a.`id_country` = cl.`id_country`)
            LEFT JOIN `' . _DB_PREFIX_ . 'state` s ON (a.`id_state` = s.`id_state`)
            WHERE c.`active` = 1 ' . $sqlAnd . '
            GROUP BY c.`id_customer`
        ')];

        foreach ($data['customers'] as $key => $value) {
            if ($value['newsletter'] == 1) {
                ++$customersSubscribedCount;
            } elseif ($value['newsletter'] == 0) {
                ++$customersBlackslistedCount;
            }
        }

        return [
            'subscribed' => $customersSubscribedCount,
            'blacklisted' => $customersBlackslistedCount,
        ];
    }

    private function getAllGroupsOfCustomer($customer_groups, $all_groups)
    {
        array_walk($customer_groups, function (&$customer_groups, $key, $all_groups) {
            foreach ($all_groups as $group) {
                if ($customer_groups == $group['id_group']) {
                    $customer_groups = $group['name'];
                }
            }
        }, $all_groups);
        $customer_groups = !empty($customer_groups) ? implode(', ', $customer_groups) : '';

        return $customer_groups;
    }
}
