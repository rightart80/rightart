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

class Cart extends CartCore
{
    public function newCalculator($products, $cartRules, $id_carrier, $computePrecision = null, bool $keepOrderPrices = false)
    {
        $orderId = null;
        if ($keepOrderPrices) {
            $orderId = Order::getIdByCartId($this->id);
            $orderId = (int) $orderId ?: null;
        }
        $calculator = new PrestaChamps\Cart\Calculator($this, $id_carrier, $computePrecision, $orderId);
        /** @var PriceCalculator $priceCalculator */
        $priceCalculator = PrestaShop\PrestaShop\Adapter\ServiceLocator::get(PrestaShop\PrestaShop\Adapter\Product\PriceCalculator::class);

        // set cart rows (products)
        $useEcotax = $this->configuration->get('PS_USE_ECOTAX');
        $precision = method_exists(Context::getContext(), 'getComputingPrecision') ? Context::getContext()->getComputingPrecision() : 6;
        $configRoundType = $this->configuration->get('PS_ROUND_TYPE');
        $roundTypes = [
            Order::ROUND_TOTAL => PrestaShop\PrestaShop\Core\Cart\CartRow::ROUND_MODE_TOTAL,
            Order::ROUND_LINE => PrestaShop\PrestaShop\Core\Cart\CartRow::ROUND_MODE_LINE,
            Order::ROUND_ITEM => PrestaShop\PrestaShop\Core\Cart\CartRow::ROUND_MODE_ITEM,
        ];
        if (isset($roundTypes[$configRoundType])) {
            $roundType = $roundTypes[$configRoundType];
        } else {
            $roundType = PrestaShop\PrestaShop\Core\Cart\CartRow::ROUND_MODE_ITEM;
        }

        foreach ($products as $product) {
            $cartRow = new PrestaShop\PrestaShop\Core\Cart\CartRow(
                $product,
                $priceCalculator,
                new PrestaShop\PrestaShop\Adapter\AddressFactory(),
                new PrestaShop\PrestaShop\Adapter\Customer\CustomerDataProvider(),
                new PrestaShop\PrestaShop\Adapter\Cache\CacheAdapter(),
                new PrestaShop\PrestaShop\Adapter\Group\GroupDataProvider(),
                new PrestaShop\PrestaShop\Adapter\Database(),
                $useEcotax,
                $precision,
                $roundType,
                $orderId
            );
            $calculator->addCartRow($cartRow);
        }

        // set cart rules
        foreach ($cartRules as $cartRule) {
            $calculator->addCartRule(new PrestaShop\PrestaShop\Core\Cart\CartRuleData($cartRule));
        }

        return $calculator;
    }

    public function addCartRule($id_cart_rule, bool $useOrderPrices = false)
    {
        $result = parent::addCartRule($id_cart_rule, $useOrderPrices);
        if ($result
            && Tools::getIsset('addDiscount')
            && $id_cart_rule == \PrestaChamps\MailchimpPro\Models\PromoCode::getCartRuleId()
        ) {
            $mcPromoCode = \PrestaChamps\MailchimpPro\Models\PromoCode::getInstanceByCode(Tools::getValue('discount_name'));
            if ($mcPromoCode->id) {
                $mcPromoCode->id_cart = (int) $this->id; // set id cart where the code was used
                $mcPromoCode->status = 1; // set used status
                if (!$mcPromoCode->date_reddeem || $mcPromoCode->date_reddeem == '0000-00-00 00:00:00') {
                    $mcPromoCode->date_reddeem = date('Y-m-d H:i:s');
                }
                $mcPromoCode->update();
            }
        }

        return $result;
    }

    public function removeCartRule($id_cart_rule, bool $useOrderPrices = false)
    {
        $result = parent::removeCartRule($id_cart_rule, $useOrderPrices);

        if ($result
            && $id_cart_rule == \PrestaChamps\MailchimpPro\Models\PromoCode::getCartRuleId()
        ) {
            $mcPromoCode = \PrestaChamps\MailchimpPro\Models\PromoCode::getInstanceByCartId($this->id);
            if ($mcPromoCode->id) {
                // mark the promo code as unused
                $mcPromoCode->id_cart = null; // set id cart where the code was used
                $mcPromoCode->status = 0; // set unused status
                $mcPromoCode->date_reddeem = null;
                $mcPromoCode->update();
            }
        }

        return $result;
    }

    public function getCartRules($filter = CartRule::FILTER_ACTION_ALL, $autoAdd = true, $useOrderPrices = false)
    {
        $cartRules = parent::getCartRules($filter, $autoAdd, $useOrderPrices);
        foreach ($cartRules as &$item) {
            if ($item['id_cart_rule'] != \PrestaChamps\MailchimpPro\Models\PromoCode::getCartRuleId()) {
                continue;
            }

            $promoCode = \PrestaChamps\MailchimpPro\Models\PromoCode::getInstanceByCartId($this->id);
            if (!$promoCode->id) {
                break;
            }

            if ($promoCode->getReductionType() == \PrestaChamps\MailchimpPro\Models\Campaign::REDUCTION_TYPE_PERCENT) {
                $item['obj']->reduction_percent = $promoCode->getReductionPercent();
                $item['reduction_percent'] = $item['obj']->reduction_percent;
            } else {
                $item['obj']->reduction_tax = true;
                $item['obj']->reduction_amount = $promoCode->getReductionAmount();
                $item['reduction_amount'] = $item['obj']->reduction_amount;
                $item['reduction_tax'] = 1;
            }

            // Define virtual context
            $virtual_context = Context::getContext()->cloneContext();
            $virtual_context->cart = $this;
            $virtual_context->virtualTotalTaxExcluded = $virtual_context->cart->getOrderTotal(false, self::ONLY_PRODUCTS);
            if (Tax::excludeTaxeOption()) {
                $virtual_context->virtualTotalTaxIncluded = $virtual_context->virtualTotalTaxExcluded;
            } else {
                $virtual_context->virtualTotalTaxIncluded = $virtual_context->cart->getOrderTotal(true, self::ONLY_PRODUCTS);
            }

            // clean cache
            Cache::clean('getContextualValue_' . $item['obj']->id . '_1_' . $this->id . '_*');
            Cache::clean('getContextualValue_' . $item['obj']->id . '_0_' . $this->id . '_*');

            $item['value_real'] = $item['obj']->getContextualValue(true, $virtual_context, $filter);
            $item['value_tax_exc'] = $item['obj']->getContextualValue(false, $virtual_context, $filter);
        }
        unset($item);

        return $cartRules;
    }

}
