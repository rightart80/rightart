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

class MailchimpproWorkerModuleFrontController extends ModuleFrontController
{
    public function display()
    {
        $queue = new Queue();

        $this->ajaxDie([
            'message' => $queue->runJob() ? 'ok' : 'no job',
            'a' => _PS_VERSION_
//            'numberOfJobsAvailable' => $queue->getNumberOfAvailableJobs(),
//            'numberOfJobsInFlight' => $queue->getNumberOfJobsInFlight(),
        ]);

        return true;
        
    }

    public function ajaxDie($value = null, $controller = null, $method = null)
    {

        header('Content-Type: application/json; charset=utf-8');
        if (!is_scalar($value)) {
            $value = json_encode($value);
        }
        if ((bool)version_compare(_PS_VERSION_, '1.7.5.0', '>=')) {
            parent::ajaxRender($value, $controller, $method); // from PS 1.7.5.0
            die();
        }else{
            parent::ajaxDie($value, $controller, $method);
        }
    }
}
