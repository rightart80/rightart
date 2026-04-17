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
if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaChamps\MailChimpAPI;
use PrestaChamps\MailchimpPro\Commands\OrderSyncCommand;
use PrestaChamps\Queue\Jobs\CartRuleSyncJob;
use PrestaChamps\Queue\Jobs\CustomerSyncJob;
use PrestaChamps\Queue\Jobs\ProductSyncJob;

/**
 * Class AdminMailchimpProWizardController
 *
 * @property Mailchimppro $module
 */
class AdminMailchimpProWizardController extends ModuleAdminController
{
    public $bootstrap = true;

    /**
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function initContent()
    {
        $this->addCSS($this->module->getLocalPath() . 'views/css/main.css');
        if (\Shop::getContext() !== \Shop::CONTEXT_SHOP) {
            $this->content = '';
            $this->warnings[] = $this->trans('Please select a shop', [], 'Modules.Mailchimppro.Adminmailchimpprowizard');
        } else {
            Media::addJsDef(['wizardUrl' => $this->context->link->getAdminLink($this->controller_name)]);
            $this->addCSS($this->module->getLocalPath() . 'views/css/smart_wizard.css');
            $orderStates = [];
            foreach (OrderState::getOrderStates($this->context->language->id) as $orderState) {
                $orderStates[] = [
                    'text' => $orderState['name'],
                    'value' => $orderState['id_order_state']
                ];
            }
            Media::addJsDef([
                'orderStates' => $orderStates,
                'statePending' => MailchimpProConfig::STATUSES_FOR_PENDING,
                'stateRefunded' => MailchimpProConfig::STATUSES_FOR_REFUNDED,
                'stateCancelled' => MailchimpProConfig::STATUSES_FOR_CANCELLED,
                'stateShipped' => MailchimpProConfig::STATUSES_FOR_SHIPPED,
                'statePaid' => MailchimpProConfig::STATUSES_FOR_PAID,
                'productIds' => array_column(
                    Product::getSimpleProducts(\Context::getContext()->language->id),
                    'id_product'
                ),
                'promoCodeIds' => $this->getCartRules(),
                'orderIds' => $this->getOrderIds(),
                'customerIds' => array_column(Customer::getCustomers(true), 'id_customer'),
                'syncUrl' => $this->context->link->getAdminLink($this->controller_name),
                'workerUrl' => $this->context->link->getAdminLink('AdminMailchimpProQueue'),
                'middlewareUrl' => Mailchimppro::MC_MIDDLEWARE,
                'itemsPerRequest' => 50,
                'token' =>
                    Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY),
                'userName' => $this->getMailchimpUserEmail(
                    Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY)
                ),
            ]);
            $this->context->smarty->assign([
                'mainJsPath' =>
                    Media::getJSPath(
                        $this->module->getLocalPath() . 'views/js/sync-wizard/main.js'
                    ),
                'JsLybraryPath' =>
                    Media::getJSPath(
                        $this->module->getLocalPath() . 'views/js/'
                    ),
                'apiKey' =>
                    Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY),
                'mcEmail' => $this->getMailchimpUserEmail(
                    Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY)
                ),
            ]);
            $this->content .= $this->context->smarty->fetch(
                $this->module->getLocalPath() . 'views/templates/admin/sync-wizard/main.tpl'
            );
            if (Shop::getContext() !== Shop::CONTEXT_SHOP) {
                $this->content = '';
                $this->context->controller->warnings[] = $this->trans('Please select a shop', [], 'Modules.Mailchimppro.Adminmailchimpprowizard');
            }

            if (!Tools::usingSecureMode()) {
                $this->content = '';
                $this->context->controller->warnings[] = $this->trans('Please use HTTPS for authenticating to Mailchimp', [], 'Modules.Mailchimppro.Adminmailchimpprowizard');
            }
            parent::initContent();
        }
    }

    protected function getMailchimpUserEmail($apiKey)
    {
        try {
            $mc = new MailChimpAPI($apiKey);
            $response = $mc->get('/');
        } catch (Exception $exception) {
            return null;
        }

        return (isset($response['email'])) ? $response['email'] : null;
    }

    protected function getCartRules()
    {
        $query = new DbQuery();
        $query->from('cart_rule');
        $query->select('id_cart_rule');
        $query->where('shop_restriction = 0');
        $ids = array_column(Db::getInstance()->executeS($query), 'id_cart_rule');

        $query = new DbQuery();
        $query->from('cart_rule_shop');
        $query->select('id_cart_rule');
        $query->where('id_shop = ' . pSQL($this->context->shop->id));
        $result = array_column(Db::getInstance()->executeS($query), 'id_cart_rule');
        $result = array_unique(array_merge($ids, $result));
        sort($result, SORT_NUMERIC);

        return $result;
    }

    protected function getOrderIds()
    {
        $shopId = Shop::getContextShopID();
        $query = new DbQuery();
        $query->from('orders');
        $query->select('id_order');
        if ($shopId) {
            $query->where("id_shop = " . (int)$shopId);
        }

        return array_column(Db::getInstance()->executeS($query), 'id_order');
    }

    protected function getProductIds()
    {
        $shopId = Shop::getContextShopID();
        $query = new DbQuery();
        $query->from('products');
        $query->select('id_product');
        if ($shopId) {
            $query->where("id_shop = " . (int)$shopId);
        }

        return array_column(Db::getInstance()->executeS($query), 'id_order');
    }

    public function getJsonPayloadValue($key, $defaultValue = null)
    {
        $body = json_decode(Tools::file_get_contents('php://input'), true);

        return isset($body[$key]) ? $body[$key] : $defaultValue;
    }

    public function ajaxProcessApiKey()
    {
        try {
            $apiKey = $this->getJsonPayloadValue('apiKey');
            $mc = new MailChimpAPI($apiKey);
            $mc->get('ping');
            if ($mc->success()) {

                Configuration::updateValue(MailchimpProConfig::MAILCHIMP_API_KEY, $apiKey);
                $this->ajaxDie(['hasError' => false, 'error' => null]);
            } else {
                $this->ajaxDie(
                    [
                        'hasError' => true,
                        'error' => $this->trans('Invlid api key', [], 'Modules.Mailchimppro.Adminmailchimpprowizard'),
                    ],
                    null,
                    null, 400
                );
            }
        } catch (Exception $exception) {
            $this->ajaxDie(
                [
                    'hasError' => true,
                    'error' => $exception->getMessage(),
                ],
                null,
                null,
                400
            );
        }
    }

    public function getStateMapping()
    {
        try {
            $configValues = MailchimpProConfig::getConfigurationValues();
            $this->ajaxDie(
                [
                    'hasError' => false,
                    'mapping' => [
                        MailchimpProConfig::STATUSES_FOR_PENDING =>
                            $configValues[MailchimpProConfig::STATUSES_FOR_PENDING],
                        MailchimpProConfig::STATUSES_FOR_REFUNDED =>
                            $configValues[MailchimpProConfig::STATUSES_FOR_REFUNDED],
                        MailchimpProConfig::STATUSES_FOR_CANCELLED =>
                            $configValues[MailchimpProConfig::STATUSES_FOR_CANCELLED],
                        MailchimpProConfig::STATUSES_FOR_SHIPPED =>
                            $configValues[MailchimpProConfig::STATUSES_FOR_SHIPPED],
                        MailchimpProConfig::STATUSES_FOR_PAID =>
                            $configValues[MailchimpProConfig::STATUSES_FOR_PAID],
                    ],
                ],
                null,
                null,
                400
            );
        } catch (Exception $exception) {
            $this->ajaxDie(
                [
                    'hasError' => true,
                    'error' => $exception->getMessage(),
                ],
                null,
                null,
                400
            );
        }
    }

    public function ajaxProcessStateMapping()
    {
        try {
            $statuses = $this->getJsonPayloadValue('states');
            if (isset($statuses[MailchimpProConfig::STATUSES_FOR_PENDING]) &&
                isset($statuses[MailchimpProConfig::STATUSES_FOR_REFUNDED]) &&
                isset($statuses[MailchimpProConfig::STATUSES_FOR_CANCELLED]) &&
                isset($statuses[MailchimpProConfig::STATUSES_FOR_SHIPPED]) &&
                isset($statuses[MailchimpProConfig::STATUSES_FOR_PAID]) &&
                is_array($statuses[MailchimpProConfig::STATUSES_FOR_PENDING]) &&
                is_array($statuses[MailchimpProConfig::STATUSES_FOR_REFUNDED]) &&
                is_array($statuses[MailchimpProConfig::STATUSES_FOR_CANCELLED]) &&
                is_array($statuses[MailchimpProConfig::STATUSES_FOR_SHIPPED]) &&
                is_array($statuses[MailchimpProConfig::STATUSES_FOR_PAID])
            ) {
                MailchimpProConfig::saveValue(
                    MailchimpProConfig::STATUSES_FOR_PENDING,
                    json_encode($statuses[MailchimpProConfig::STATUSES_FOR_PENDING])
                );
                MailchimpProConfig::saveValue(
                    MailchimpProConfig::STATUSES_FOR_REFUNDED,
                    json_encode($statuses[MailchimpProConfig::STATUSES_FOR_REFUNDED])
                );
                MailchimpProConfig::saveValue(
                    MailchimpProConfig::STATUSES_FOR_CANCELLED,
                    json_encode($statuses[MailchimpProConfig::STATUSES_FOR_CANCELLED])
                );
                MailchimpProConfig::saveValue(
                    MailchimpProConfig::STATUSES_FOR_SHIPPED,
                    json_encode($statuses[MailchimpProConfig::STATUSES_FOR_SHIPPED])
                );
                MailchimpProConfig::saveValue(
                    MailchimpProConfig::STATUSES_FOR_PAID,
                    json_encode($statuses[MailchimpProConfig::STATUSES_FOR_PAID])
                );
                $this->ajaxDie(['hasError' => false, 'error' => null]);
            }
            throw new Exception('Invalid data');
        } catch (Exception $exception) {
            $this->ajaxDie(
                [
                    'hasError' => true,
                    'error' => $exception->getMessage(),
                ],
                null,
                null,
                400
            );
        }
    }

    public function ajaxProcessGetStates()
    {
        try {
            $configValues = MailchimpProConfig::getConfigurationValues();

            $orderStates = OrderState::getOrderStates($this->context->language->id);
            $this->ajaxDie([
                'hasError' => false,
                'error' => null,
                'states' => $orderStates,
                'mapping' => [
                    MailchimpProConfig::STATUSES_FOR_PENDING =>
                        $configValues[MailchimpProConfig::STATUSES_FOR_PENDING],
                    MailchimpProConfig::STATUSES_FOR_REFUNDED =>
                        $configValues[MailchimpProConfig::STATUSES_FOR_REFUNDED],
                    MailchimpProConfig::STATUSES_FOR_CANCELLED =>
                        $configValues[MailchimpProConfig::STATUSES_FOR_CANCELLED],
                    MailchimpProConfig::STATUSES_FOR_SHIPPED =>
                        $configValues[MailchimpProConfig::STATUSES_FOR_SHIPPED],
                    MailchimpProConfig::STATUSES_FOR_PAID =>
                        $configValues[MailchimpProConfig::STATUSES_FOR_PAID],
                ],
            ]);
        } catch (Exception $exception) {
            $this->ajaxDie(
                [
                    'hasError' => true,
                    'error' => $exception->getMessage(),
                ],
                null,
                null,
                400
            );
        }
    }

    public function ajaxProcessSyncStores()
    {
        try {
            $command = new \PrestaChamps\MailchimpPro\Commands\StoreSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                [$this->context->shop->id]
            );
            $command->setSyncMode($command::SYNC_MODE_REGULAR);
            $command->setMethod($command::SYNC_METHOD_POST);
            $command->execute();
            $command->setMethod($command::SYNC_METHOD_PATCH);
            $this->ajaxDie([
                'hasError' => false,
                'error' => null,
                'result' => $command->execute(),
            ]);
        } catch (Exception $exception) {
            $this->ajaxDie(
                [
                    'hasError' => true,
                    'error' => $exception->getMessage(),
                ],
                null,
                null,
                400
            );
        }
    }

    public function ajaxProcessAddProductsToQueue()
    {
        $products = \ProductCore::getSimpleProducts(\Context::getContext()->language->id);
        $queue = new PrestaChamps\Queue\Queue();
        foreach ($products as $product) {
            $job = new ProductSyncJob();
            $job->productId = $product['id_product'];
            $queue->push($job, 'setup-wizard');
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessAddCustomersToQueue()
    {
        $customers = array_column(Customer::getCustomers(true), 'id_customer');
        $queue = new PrestaChamps\Queue\Queue();
        foreach ($customers as $customer) {
            $job = new CustomerSyncJob();
            $job->customerId = $customer;
            $queue->push($job, 'setup-wizard');
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessAddOrdersToQueue()
    {
        $orders = $this->getOrderIds();
        $queue = new PrestaChamps\Queue\Queue();
        foreach ($orders as $order) {
            $job = new \PrestaChamps\Queue\Jobs\OrderSyncJob();
            $job->orderId = $order;
            $queue->push($job, 'setup-wizard');
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessAddPromoCodesToQueue()
    {
        $cartRules = $this->getCartRules();
        $queue = new PrestaChamps\Queue\Queue();
        foreach ($cartRules as $cartRule) {
            $job = new CartRuleSyncJob();
            $job->cartRuleId = $cartRule;
            $queue->push($job, 'setup-wizard');
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessListSelect()
    {
        try {
            $listId = $this->getJsonPayloadValue('listId');
            Configuration::updateValue(MailchimpProConfig::MAILCHIMP_LIST_ID, $listId);
            $this->ajaxDie(['hasError' => false, 'error' => null]);
        } catch (Exception $exception) {
            $this->ajaxDie(
                [
                    'hasError' => true,
                    'error' => $exception->getMessage(),
                ],
                null,
                null,
                400
            );
        }
    }

    public function ajaxProcessGetLists()
    {
        try {
            $lists = $this->module->getApiClient()->get(
                'lists',
                ['fields' => 'lists.name,lists.id', 'count' => 999]
            );
            if (!$lists || empty($lists['lists'])) {
                \PrestaChamps\MailchimpPro\Factories\ListFactory::make(
                    $this->context->shop->name,
                    $this->module->getApiClient(),
                    $this->context
                );
                $lists = $this->module->getApiClient()->get(
                    'lists',
                    ['fields' => 'lists.name,lists.id', 'count' => 999]
                );
            }
            $this->ajaxDie(
                [
                    'hasError' => false,
                    'error' => null,
                    'lists' => $lists['lists'],
                    'selectedList' => Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID),
                ]
            );
        } catch (Exception $exception) {
            $this->ajaxDie(
                [
                    'hasError' => true,
                    'error' => $exception->getMessage(),
                ],
                null,
                null,
                400
            );
        }
    }

    /**
     * @param null $value
     * @param null $controller
     * @param null $method
     * @param int $statusCode
     */
    public function ajaxDie($value = null, $controller = null, $method = null, $statusCode = 200)
    {
        header('Content-Type: application/json');
        if (!is_scalar($value)) {
            $value = json_encode($value);
        }

        http_response_code($statusCode);
        if ((bool)version_compare(_PS_VERSION_, '1.7.5.0', '>=')) {
            parent::ajaxRender($value, $controller, $method); // from PS 1.7.5.0
            die();
        }else{
            parent::ajaxDie($value, $controller, $method);
        }
    }
}
