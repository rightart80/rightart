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

class EventdataData extends AbstractModel
{
    public $affiliation = '';
    public $currency = '';
    public $discount = '';
    public $discount_parsed = '';
    public $discount_taxinc = '';
    public $discount_taxinc_parsed = '';
    public $shipping = '';
    public $shipping_parsed = '';
    public $shipping_taxinc = '';
    public $shipping_taxinc_parsed = '';
    public $subtotal = '';
    public $subtotal_parsed = '';
    public $subtotal_predisc = '';
    public $subtotal_predisc_parsed = '';
    public $subtotal_predisc_taxinc = '';
    public $subtotal_predisc_taxinc_parsed = '';
    public $subtotal_taxinc = '';
    public $subtotal_taxinc_parsed = '';
    public $tax = '';
    public $tax_parsed = '';
    public $total = '';
    public $total_parsed = '';
    public $total_before_tax = '';
    public $total_before_tax_parsed = '';
    public $url = '';
    public $items = '';
}
