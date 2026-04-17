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

use Sendinblue\Services\BackInStockService;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class SendinblueBackinstockModuleFrontController extends ModuleFrontController
{
    /**
     * @var BackInStockService
     */
    private $backInStockService;

    public function __construct()
    {
        parent::__construct();
        $this->backInStockService = new BackInStockService();
    }

    public function init()
    {
        parent::init();
        header('Content-Type: application/json; charset=utf-8');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('action') && Tools::getValue('action') === 'subscribe') {
            $this->handleSubscription();
        }
    }

    /**
     * Handle subscription request
     */
    private function handleSubscription()
    {
        try {
            if (!$this->validateRequest()) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Invalid request']);
                return;
            }

            $productId = (int) \Tools::getValue('product_id');
            $email = \Tools::getValue('email');

            $result = $this->backInStockService->subscribeToBackInStock($productId, $email);

            if ($result) {
                $this->sendJsonResponse([
                    'success' => true, 
                    'message' => $this->module->l('Thank you! You will be notified when this product is back in stock.')
                ]);
            } else {
                $this->sendJsonResponse([
                    'success' => false, 
                    'message' => $this->module->l('Failed to subscribe. Please try again.')
                ]);
            }
        } catch (Exception $e) {
            \PrestaShopLogger::addLog('BackInStock controller error: ' . $e->getMessage(), 3);
            $this->sendJsonResponse([
                'success' => false, 
                'message' => $this->module->l('An error occurred. Please try again.')
            ]);
        }
    }

    /**
     * Validate the request
     *
     * @return bool
     */
    private function validateRequest()
    {
        // Validate product ID
        $productId = \Tools::getValue('product_id');
        if (!$productId || !\Validate::isUnsignedId($productId)) {
            return false;
        }

        // Validate email
        $email = \Tools::getValue('email');
        if (!$email || !\Validate::isEmail($email)) {
            return false;
        }

        // Check if product exists
        $product = new \Product($productId);
        if (!\Validate::isLoadedObject($product)) {
            return false;
        }

        return true;
    }

    /**
     * Send JSON response
     *
     * @param array $data
     */
    private function sendJsonResponse($data)
    {
        echo json_encode($data);
        exit;
    }

    public function display(): void
    {
        // This method should not be called for AJAX requests
        $this->sendJsonResponse(['success' => false, 'message' => 'Invalid request method']);
    }
}
