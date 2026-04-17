<?php
/**
 * MailChimp
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

namespace PrestaChamps\Cart;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Cart;
use Currency;
use PrestaShop\PrestaShop\Core\Localization\CLDR\ComputingPrecision;
use Tools;

/**
 * provides methods to process cart calculation.
 */
class Calculator extends \PrestaShop\PrestaShop\Core\Cart\Calculator
{
    public function __construct(Cart $cart, $carrierId, ?int $computePrecision = null, ?int $orderId = null)
    {
        parent::__construct($cart, $carrierId, $computePrecision, $orderId);
        $this->cartRuleCalculator = new CartRuleCalculator();
    }
}
