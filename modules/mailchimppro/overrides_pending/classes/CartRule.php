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

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartRule extends CartRuleCore
{
    public static function getIdByCode($code)
    {
        if (!Validate::isCleanHtml($code)) {
            return false;
        }

        // Check for custom promo codes
        $mcPromoCode = \PrestaChamps\MailchimpPro\Models\PromoCode::getInstanceByCode($code);
        if (Validate::isLoadedObject($mcPromoCode)) {
            return \PrestaChamps\MailchimpPro\Models\PromoCode::getCartRuleId();
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT `id_cart_rule` FROM `' . _DB_PREFIX_ . 'cart_rule` WHERE `code` = \'' . pSQL($code) . '\''
        );
    }

    public function checkValidity(Context $context, $alreadyInCart = false, $display_error = true, $check_carrier = true, $useOrderPrices = false)
    {
        if (Tools::getIsset('addDiscount')) {
            $mcPromoCode = \PrestaChamps\MailchimpPro\Models\PromoCode::getInstanceByCode(Tools::getValue('discount_name'));

            if (Validate::isLoadedObject($mcPromoCode)) {
                if ($mcPromoCode->isUsedForOrder()) {
                    return (!$display_error) ? false : 'This voucher already used for order';
                } elseif ($mcPromoCode->checkCustomer($context->customer) == false
                    || $mcPromoCode->checkCampaign() == false
                ) {
                    return (!$display_error) ? false : 'This voucher does not exist.';
                } elseif ($mcPromoCode->getCampaign()->isExpired()) {
                    return (!$display_error) ? false : 'This voucher has expired';
                }
            }
        }

        return parent::checkValidity($context, $alreadyInCart, $display_error, $check_carrier, $useOrderPrices);
    }
}
