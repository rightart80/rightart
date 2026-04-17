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

$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['fc'] = 'module';
$_GET['module'] = 'mailchimppro';
$_GET['controller'] = 'cronjob';
$_GET['php_cron_call'] = '1';

require_once dirname(__FILE__) . '/../../index.php';

if (!defined('_PS_VERSION_')) {
    exit;
}