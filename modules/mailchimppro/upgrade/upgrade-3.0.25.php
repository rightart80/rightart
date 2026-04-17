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

function upgrade_module_3_0_25(Mailchimppro $module)
{
    // Check if the promoOverridesEnabled setting is already set
    if (Configuration::hasKey('module-mailchimpproconfig-mailchimp-promo-override-enabled')) {
        // If it's already set, don't override the user's preference
        return true;
    }
    
    // Check if the overrides exist in the PrestaShop override directory
    $cartOverridePath = _PS_OVERRIDE_DIR_ . 'classes/Cart.php';
    $cartRuleOverridePath = _PS_OVERRIDE_DIR_ . 'classes/CartRule.php';
    
    // Check if the override files exist
    $overridesExist = file_exists($cartOverridePath) && file_exists($cartRuleOverridePath);
    
    // If the override files exist, check if they contain Mailchimp-related code
    $mailchimpOverridesInstalled = false;
    if ($overridesExist) {
        $cartOverrideContent = file_get_contents($cartOverridePath);
        $cartRuleOverrideContent = file_get_contents($cartRuleOverridePath);
        
        // Define markers that would indicate our module's overrides
        $markers = [
            'mailchimppro', // Module name
            'PrestaChamps', // Company name
            'MAILCHIMP_PROMO', // Specific string used in the overrides
            'getPromoCodeByEmail', // Method likely used in the overrides
            'getPromoCodeByCart', // Method likely used in the overrides
        ];
        
        // Check if any of the markers are present in the override files
        foreach ($markers as $marker) {
            if (
                (strpos($cartOverrideContent, $marker) !== false) || 
                (strpos($cartRuleOverrideContent, $marker) !== false)
            ) {
                $mailchimpOverridesInstalled = true;
                break;
            }
        }
    }
    
    // If the Mailchimp overrides are installed and the module is enabled,
    // then enable the promo overrides setting
    if ($mailchimpOverridesInstalled && Module::isEnabled('mailchimppro')) {
        // Enable the promo overrides setting
        Configuration::updateValue('module-mailchimpproconfig-mailchimp-promo-override-enabled', 1);
        Configuration::updateValue('module-mailchimpproconfig-mailchimp-promo-override-auto-installed', 1);
        
        // Log the action
        PrestaShopLogger::addLog(
            'Mailchimp promo overrides automatically enabled during upgrade because existing overrides were detected',
            1, // Info severity
            null,
            'Mailchimppro',
            null,
            true
        );
    } else {
        // For new installations or upgrades from below 3.0.23, set the default value to disabled
        Configuration::updateValue('module-mailchimpproconfig-mailchimp-promo-override-enabled', 0);
        Configuration::updateValue('module-mailchimpproconfig-mailchimp-promo-override-auto-installed', 0);
    }
    
    return true;
}
