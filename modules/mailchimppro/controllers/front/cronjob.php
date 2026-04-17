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

use PrestaChamps\Queue\Queue;

class MailchimpproCronjobModuleFrontController extends ModuleFrontController
{
    public $auth = false;

    /** @var bool */
    public $ajax;

    public function display()
    {
        if(Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)){

            if(Shop::isFeatureActive() && isset($_GET['php_cron_call']) && $_GET['php_cron_call'] == '1'){

                Configuration::updateGlobalValue(MailchimpProConfig::CRONJOB_BASED_SYNC_FOR_MULTI_STORE,true);

                die("Until now you have used the PHP cron command, and your Prestashop is working in multi-store configuration. Because of multi-store compatibility, the PHP cron command is disabled. Each stores cron url is different. You need to configure the wget cron URL of every separate store where you want to use the cronjob synchronization.");
            }

            $this->ajax = 1;
            $queue = new Queue();
            $queue->runCronjob();
        
        }else{
            // echo "Hook based configuration!";
        }

        return true;

    }
}