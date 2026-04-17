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

namespace Sendinblue\Models;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartItem extends AbstractModel
{
    public $id = '';
    public $name = '';
    public $quantity = '';
    public $size = '';
    public $available_now = '';
    public $category = '';
    public $sku = '';
    public $image = '';
    public $description_short = '';
    public $price = '';
    public $price_parsed = '';
    public $price_predisc = '';
    public $price_predisc_parsed = '';
    public $price_predisc_taxinc = '';
    public $price_predisc_taxinc_parsed = '';
    public $price_taxinc = '';
    public $price_taxinc_parsed = '';
    public $disc_amount = '';
    public $disc_amount_parsed = '';
    public $disc_amount_taxinc = '';
    public $disc_amount_taxinc_parsed = '';
    public $disc_rate = '';
    public $disc_rate_parsed = '';
    public $tax_amount = '';
    public $tax_amount_parsed = '';
    public $tax_name = '';
    public $tax_rate = '';
    public $tax_rate_parsed = '';
    public $url = '';
    public $variant_id = '';
    public $variant_id_name = '';
    public $variant_name = '';
}
