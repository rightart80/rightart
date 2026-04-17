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
use PrestaShop\PrestaShop\Core\Cart\AmountImmutable;
use PrestaShop\PrestaShop\Core\Cart\CartRowCollection;
use PrestaShop\PrestaShop\Core\Cart\CartRuleData;
use PrestaChamps\MailchimpPro\Models\Campaign;
use PrestaChamps\MailchimpPro\Models\PromoCode;

class CartRuleCalculator extends \PrestaShop\PrestaShop\Core\Cart\CartRuleCalculator
{
    protected function applyCartRule(CartRuleData $cartRuleData, $withFreeShipping = true)
    {
        if (!\CartRule::isFeatureActive()) {
            return;
        }

        $cartRule = $cartRuleData->getCartRule();
        if ($cartRule->id != PromoCode::getCartRuleId()) {
            return parent::applyCartRule($cartRuleData, $withFreeShipping);
        }

        // get mc promocode by cart id
        $promoCode = PromoCode::getInstanceByCartId($this->calculator->getCart()->id);
        if (!$promoCode->id) {
            return;
        }

        if ($promoCode->getReductionType() == Campaign::REDUCTION_TYPE_PERCENT) {
            foreach ($this->cartRows as $cartRow) {
                $product = $cartRow->getRowData();
                if (
                    array_key_exists('product_quantity', $product) &&
                    0 === (int) $product['product_quantity']
                ) {
                    $cartRuleData->addDiscountApplied(new AmountImmutable(0.0, 0.0));
                } else {
                    $amount = $cartRow->applyPercentageDiscount($promoCode->getReductionPercent());
                    $cartRuleData->addDiscountApplied($amount);
                }
            }
            return;
        }

        if ($promoCode->getReductionType() == Campaign::REDUCTION_TYPE_AMOUNT) {
            $concernedRows = new CartRowCollection();
            if ($cartRule->reduction_product > 0) {
                // discount on single product
                foreach ($this->cartRows as $cartRow) {
                    if ($cartRow->getRowData()['id_product'] == $cartRule->reduction_product) {
                        $concernedRows->addCartRow($cartRow);
                    }
                }
            } elseif ($cartRule->reduction_product == 0) {
                // Discount (¤) on the whole order
                $concernedRows = $this->cartRows;
            }

            // currency conversion
            $discountConverted = $this->convertAmountBetweenCurrencies(
                $promoCode->getReductionAmount(),
                new \Currency($cartRule->reduction_currency),
                new \Currency($this->calculator->getCart()->id_currency)
            );

            // get total of concerned rows
            $totalTaxIncl = $totalTaxExcl = 0;
            foreach ($concernedRows as $concernedRow) {
                $totalTaxIncl += $concernedRow->getFinalTotalPrice()->getTaxIncluded();
                $totalTaxExcl += $concernedRow->getFinalTotalPrice()->getTaxExcluded();
            }

            // The reduction cannot exceed the products total, except when we do not want it to be limited (for the partial use calculation)
            $discountConverted = min($discountConverted, $cartRule->reduction_tax ? $totalTaxIncl : $totalTaxExcl);

            // apply weighted discount :
            // on each line we apply a part of the discount corresponding to discount*rowWeight/total
            foreach ($concernedRows as $concernedRow) {
                // get current line tax rate
                $taxRate = 0;
                if ($concernedRow->getFinalTotalPrice()->getTaxExcluded() != 0) {
                    $taxRate = ($concernedRow->getFinalTotalPrice()->getTaxIncluded()
                                - $concernedRow->getFinalTotalPrice()->getTaxExcluded())
                               / $concernedRow->getFinalTotalPrice()->getTaxExcluded();
                }
                $weightFactor = 0;
                if ($cartRule->reduction_tax) {
                    // if cart rule amount is set tax included : calculate weight tax included
                    if ($totalTaxIncl != 0) {
                        $weightFactor = $concernedRow->getFinalTotalPrice()->getTaxIncluded() / $totalTaxIncl;
                    }
                    $discountAmountTaxIncl = $discountConverted * $weightFactor;
                    // recalculate tax included
                    $discountAmountTaxExcl = $discountAmountTaxIncl / (1 + $taxRate);
                } else {
                    // if cart rule amount is set tax excluded : calculate weight tax excluded
                    if ($totalTaxExcl != 0) {
                        $weightFactor = $concernedRow->getFinalTotalPrice()->getTaxExcluded() / $totalTaxExcl;
                    }
                    $discountAmountTaxExcl = $discountConverted * $weightFactor;
                    // recalculate tax excluded
                    $discountAmountTaxIncl = $discountAmountTaxExcl * (1 + $taxRate);
                }
                $amount = new AmountImmutable($discountAmountTaxIncl, $discountAmountTaxExcl);
                $concernedRow->applyFlatDiscount($amount);
                $cartRuleData->addDiscountApplied($amount);
            }

            return;
        }
    }
}
