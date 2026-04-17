<?php
/**
 * PrestaChamps
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

namespace PrestaChamps\PrestaShop\Traits;
if (!defined('_PS_VERSION_')) {
    exit;
}
trait ShopIdTrait
{
    public function getShopId($idStore = null)
    {
        if(!$idStore){
            $shop = \Context::getContext()->shop;
        }else{
            $shop = new \Shop($idStore);
        }
        
        return \Mailchimppro::shopIdTransformer($shop);
    }
}
