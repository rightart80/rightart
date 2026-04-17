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

use Sendinblue\Services\DataValidationService;
use Sendinblue\Services\LocationService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ActionNewsletterRegistrationAfterHook extends AbstractHook
{
    /* Newsletter Group Name */
    const GROUP_NAME = 'NEWSLETTER_RECIPIENTS';

    /* Newsletter Recipient Value */
    const NEWSLETTER_VALUE = 'true';

    /**
     * @param array $recipient
     */
    public function handleEvent($recipient)
    {
        try {
            $dVService = new DataValidationService();
            // sent empty fields to avoid the sib api exception with "attributes should be an object"
            $this->getApiClientService()->createContact([
                'email' => $recipient['email'],
                'firstname' => '',
                'lastname' => '',
                'id_default_group' => self::GROUP_NAME,
                'id_shop' => $this->getSendinblueConfigService()->shopId,
                'newsletter' => self::NEWSLETTER_VALUE,
                'id_lang' => !is_null($this->getContextlanguage()) ? $dVService->checkAndGiveMeString($this->getContextlanguage()->iso_code) : null,
                'subscription_location' => LocationService::LOCATION_FOOTER,
            ]);
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }
}
