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

use Sendinblue\Services\BackInStockService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DisplayProductButtonsHook extends AbstractHook
{
    /**
     * @var BackInStockService
     */
    private $backInStockService;

    public function __construct()
    {
        $this->backInStockService = new BackInStockService();
    }

    /**
     *
     * @param array $params
     * @return string
     */
    public function handleEvent($params)
    {
        if (!isset($params['product'])) {
            return '';
        }

        $product = $params['product'];
        
        if ($this->isProductInStock($product)) {
            return '';
        }

        $html = '<div class="sendinblue-back-in-stock-container" data-product-id="' . $product->id . '" data-backinstock-url="' . $this->getContext()->link->getModuleLink('sendinblue', 'backinstock') . '" style="display: inline-block; margin-top: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">';
        $html .= '<h4 style="margin: 0 0 10px 0; color: #333; font-size: 16px;">Enter your email address and we will notify you when this product is back in stock.</h4>';
        $html .= '<div id="sendinblue-message-' . $product->id . '" style="display: none; margin-bottom: 10px; padding: 8px; border-radius: 3px;"></div>';
        $html .= '<div id="sendinblue-form-' . $product->id . '" class="sendinblue-form" style="display: flex; gap: 10px; align-items: center; margin-top: 20px;">';
        $html .= '<input type="hidden" name="product_id" value="' . $product->id . '">';
        $html .= '<input type="hidden" name="action" value="subscribe">';
        $html .= '<input type="email" name="email" id="sib_bis_email" placeholder="Enter your email address" required style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">';
        $html .= '<button type="button" class="brevo-stock-notification-submit" id="brevo-stock-notification-submit-' . $product->id . '" style="padding: 8px 15px; background-color: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer;">Notify me</button>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @param \Product $product
     * @return bool
     */
    private function isProductInStock($product)
    {
        $product = new \Product($product->id);
        $stockQuantity = \StockAvailable::getQuantityAvailableByProduct($product->id);

        return $product->active && 
               $product->available_for_order && 
               $stockQuantity > 0;
    }
}
