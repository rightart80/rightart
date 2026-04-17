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
use PrestaChamps\Queue\Queue;

class AdminMailchimpProQueueController extends ModuleAdminController
{
    public $bootstrap = true;
    /**
     * @var Queue
     */
    protected $queue;

    public function init()
    {
        parent::init();
        $this->queue = new Queue();
    }

    public function initContent()
    {
		$this->context->controller->addCSS($this->module->getLocalPath() . 'views/css/configuration.css');
		$this->context->controller->addCSS($this->module->getLocalPath() . 'views/css/queue.css');
        $multistore_on_store = false;

        if((\Shop::isFeatureActive() && \Shop::getContextShopID(true) != null) || !\Shop::isFeatureActive()){
            $multistore_on_store = true;
        }elseif(\Shop::getContextShopID(true) == null){
            $multistore_on_store = false;
        }

        if (!Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY) || !$multistore_on_store) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminMailchimpProConfiguration'));
        }
        else {
            $configValues = MailchimpProConfig::getConfigurationValues();
            
            Media::addJsDef([
                'queueUrl' => $this->context->link->getAdminLink($this->controller_name),
                'numberOfJobsAvailable' => $this->queue->getNumberOfAvailableJobs(),
                'numberOfJobsInFlight' => $this->queue->getNumberOfJobsInFlight(),
                'numberOfJobsAvailablePerType' => $this->queue->getNumberOfAvailableJobsPerType(),
                'lastSyncedProductId' => $configValues[MailchimpProConfig::LAST_SYNCED_PRODUCT_ID],
                'lastSyncedCustomerId' => $configValues[MailchimpProConfig::LAST_SYNCED_CUSTOMER_ID],
                'lastSyncedCartRuleId' => $configValues[MailchimpProConfig::LAST_SYNCED_PROMO_ID],
                'lastSyncedOrderId' => $configValues[MailchimpProConfig::LAST_SYNCED_ORDER_ID],
                'lastSyncedCartId' => $configValues[MailchimpProConfig::LAST_SYNCED_CART_ID],
                'lastSyncedNewsletterSubscriberId' => $configValues[MailchimpProConfig::LAST_SYNCED_NEWSLETTER_SUBSCRIBER_ID],
            ]);
            $this->context->smarty->assign([
                'mainJsPath' =>
                    Media::getJSPath(
                        $this->module->getLocalPath() . 'views/js/queue/main.js'
                    ),
                'JsLybraryPath' =>
                    Media::getJSPath(
                        $this->module->getLocalPath() . 'views/js/'
                    ),
            ]);
            $this->content = $this->context->smarty->fetch(
                $this->module->getLocalPath() . 'views/templates/admin/queue/main.tpl'
            );
        }
        parent::initContent();
    }

    public function ajaxProcessGetQueueStats()
    {
        $configValues = MailchimpProConfig::getConfigurationValues();
        $this->ajaxDie([
            'numberOfJobsAvailable' => $this->queue->getNumberOfAvailableJobs(),
            'numberOfJobsInFlight' => $this->queue->getNumberOfJobsInFlight(),
            'perType' => $this->queue->getNumberOfAvailableJobsPerType(),
        ]);
    }

    public function ajaxProcessRunJob()
    {
        $configValues = MailchimpProConfig::getConfigurationValues();
        $this->ajaxDie([
            'message' => $this->queue->runJob(),
            'numberOfJobsAvailable' => $this->queue->getNumberOfAvailableJobs(),
            'numberOfJobsInFlight' => $this->queue->getNumberOfJobsInFlight(),
            'perType' => $this->queue->getNumberOfAvailableJobsPerType(),
        ]);
    }

    public function ajaxProcessClearJobs()
    {
        $configValues = MailchimpProConfig::getConfigurationValues();
        $this->ajaxDie([
            'message' => $this->queue->clearChannel(),
            'numberOfJobsAvailable' => $this->queue->getNumberOfAvailableJobs(),
            'numberOfJobsInFlight' => $this->queue->getNumberOfJobsInFlight(),
        ]);
    }

    /**
     * @param null $value
     * @param null $controller
     * @param null $method
     * @param int $statusCode
     * @throws PrestaShopException
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
