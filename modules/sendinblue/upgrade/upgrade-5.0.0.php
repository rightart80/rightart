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

use Sendinblue\Services\ConfigService;
use Sendinblue\Services\WebserviceService;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @throws Exception
 */
function upgrade_module_5_0_0($module)
{
    $allResources = [
        'sendinblueorders',
        'sendinblueordercount',
        'sendinbluecategorycount',
        'sendinblueproductcount',
        'sendinbluecategories',
        'sendinblueproductsfullsync',
    ];

    try {
        $configService = new ConfigService();
        $accountId = $configService->getSibConfig(WebserviceService::SENDINBLUE_API_ACCOUNT);

        $values = [];
        if ($accountId) {
            foreach ($allResources as $resource) {
                $values[] = [
                    'resource' => $resource,
                    'method' => 'GET',
                    'id_webservice_account' => $accountId,
                ];
            }
        }

        $dbInstance = Db::getInstance();
        $dbInstance->insert('webservice_permission', $values);

        $newSendinblueHooks = [
            'actionProductAdd',
            'actionProductUpdate',
            'actionProductDelete',
            'actionCategoryAdd',
            'actionCategoryUpdate',
            'actionCategoryDelete',
            'actionOrderEdited',
            'actionOrderSlipAdd',
        ];

        foreach ($newSendinblueHooks as $newSendinblueHook) {
            $module->registerHook($newSendinblueHook);
        }

        return true;
    } catch (Exception $e) {
        throw new Exception('Something went wrong.');
    }
}
