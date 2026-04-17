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

use Sendinblue\Services\LocationService;
use Sendinblue\Services\DataValidationService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ActionObjectCustomerAddressUpdateHook extends AbstractHook
{
    /**
     * @param \AddressCore $data
     */
    public function handleEvent($data)
    {
        try {
            if (!$data['object']->id_customer || $data['object']->alias === 'ESC_ADDRESS') {
                return;
            }
            
            $unifiedEnabled = $this->getSendinblueConfigService()->isUnifiedEnabled();
            $orderAutoSyncEnabled = $this->getSendinblueConfigService()->isOrderAutoSyncEnabled();
            if (!is_null($this->getContextCustomer()) && ($this->getContextCustomer()->newsletter == 1 || $unifiedEnabled || $orderAutoSyncEnabled)) {
                $id_lang = $this->getContext()->language->id;
                $customerCountry = "";
                if (!empty($data['object']->id_country)){
                     $psCountry = \Db::getInstance()->getRow(
                        'SELECT name FROM `' . _DB_PREFIX_ . 'country_lang` WHERE id_country = ' . (int)$data['object']->id_country . ' AND id_lang = ' . (int)$id_lang
                    );
                    
                    if ($psCountry && isset($psCountry['name'])) {
                        $customerCountry = \Tools::ucfirst($psCountry['name']);
                    }
                }
                
                $customerState = "";
                if (!empty($data['object']->id_state)) {
                    $psState = \Db::getInstance()->getRow('SELECT name FROM `' . _DB_PREFIX_ . 'state` WHERE id_state = ' . (int)$data['object']->id_state);
                    if ($psState && isset($psState['name'])) {
                        $customerState = \Tools::ucfirst($psState['name']);
                    }
                }

                $customer_groups = $this->getContextCustomer()->getGroups();
                $all_groups = \Group::getGroups(\ConfigurationCore::get('PS_LANG_DEFAULT'));
                array_walk($customer_groups, function (&$customer_groups, $key, $all_groups) {
                    foreach ($all_groups as $group) {
                        if ($customer_groups == $group['id_group']) {
                            $customer_groups = $group['name'];
                        }
                    }
                }, $all_groups);
                $customer_groups = !empty($customer_groups) ? implode(', ', $customer_groups) : '';

                $dVService = new DataValidationService();

                $contactPayload = [
                    'id' => $data['object']->id_customer,
                    'id_default_group' => !is_null($this->getContextCustomer()) ?
                        $this->getContextCustomer()->id_default_group : null,
                    'id_lang' => !is_null($this->getContextlanguage()) ? $dVService->checkAndGiveMeString($this->getContextlanguage()->iso_code) : null,
                    'email' => !is_null($this->getContextCustomer()) ? $dVService->checkAndGiveMeString($this->getContextCustomer()->email) : null,
                    'country' => $dVService->checkAndGiveMeString($customerCountry),
                    'id_group' => $customer_groups,
                    'id_shop' => $this->getSendinblueConfigService()->shopId,
                    'state' => $dVService->checkAndGiveMeString($customerState),
                    'company' => $dVService->checkAndGiveMeString($data['object']->company),
                    'firstname' => $dVService->checkAndGiveMeString($data['object']->firstname),
                    'lastname' => $dVService->checkAndGiveMeString($data['object']->lastname),
                    'address1' => $dVService->checkAndGiveMeString($data['object']->address1),
                    'address2' => $dVService->checkAndGiveMeString($data['object']->address2),
                    'postcode' => $data['object']->postcode,
                    'city' => $dVService->checkAndGiveMeString($data['object']->city),
                    'phone' => $dVService->checkAndGiveMeString($data['object']->phone),
                    'vat_number' => $data['object']->vat_number,
                    'date_add' => $dVService->checkAndGiveMeString(date('Y-m-d', strtotime($data['object']->date_add))),
                    'date_upd' => $dVService->checkAndGiveMeString(date('Y-m-d', strtotime($data['object']->date_upd))),
                    'subscription_location' => LocationService::detectSubscriptionSource(),
                ];
                if ($unifiedEnabled){
                    $contactPayload['newsletter'] = $this->getContextCustomer()->newsletter == 1 ? 'true' : 'false';
                }
                $this->getApiClientService()->updateContact($contactPayload);
            } else {
                return;
            }
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }
}
