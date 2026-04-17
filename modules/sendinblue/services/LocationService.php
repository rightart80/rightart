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

/**
 * Service class for detecting subscription locations and sources
 */
class LocationService
{
    /**
     * Subscription location constants
     */
    const LOCATION_CHECKOUT = 'checkout';
    const LOCATION_SIGNUP = 'signup';
    const LOCATION_FOOTER = 'footer';
    const LOCATION_DEFAULT = '';

    /**
     * Detect the source of subscription based on controller and referer
     * @return string Returns 'checkout', 'signup', or empty string
     */
    public static function detectSubscriptionSource()
    {
        $controller = strtolower(\Tools::getValue('controller') ?: '');
        $referer = '';
        $refererPath = '';

        if (isset($_SERVER['HTTP_REFERER']) && filter_var($_SERVER['HTTP_REFERER'], FILTER_VALIDATE_URL)) {
            $referer = strtolower($_SERVER['HTTP_REFERER']);
            $parsedUrl = parse_url($referer);

            if (isset($parsedUrl['path']) && !empty($parsedUrl['path'])) {
                $refererPath = trim($parsedUrl['path'], '/');
            }
        }

        if ($controller === 'order' || preg_match('#(^|/)order($|/)#', $refererPath)) {
            return self::LOCATION_CHECKOUT;
        }

        if ($controller === 'authentication' || $controller === 'registration' || preg_match('#(^|/)(authentication|registration)($|/)#', $refererPath)) {
            return self::LOCATION_SIGNUP;
        }

        return self::LOCATION_DEFAULT;
    }
}
