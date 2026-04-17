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
if (!defined('_PS_VERSION_')) {
    exit;
}

if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
    abstract class WebserviceSpecificManagementSendinblueAbstract implements WebserviceSpecificManagementInterface
    {
        use WebServiceSpecificManagementTrait;
        const RESULT = 'result';
        const COUNT = 'count';
        const COUNT_PER_PAGE = 'countPerPage';
        const SUCCESS = 'success';
        const ERROR = 'error';
        const PLUGIN_VERSION = 'plugin_version';
        const SHOP_CURRENCY = 'shop_currency';
        const SHOP_VERSION = 'shop_version';
        const VERSION = 'version';

        /**
         * @var array
         */
        protected $response;

        protected $objOutput;

        protected $wsObject;

        /**
         * @return $this
         */
        public function setObjectOutput(WebserviceOutputBuilder $obj)
        {
            $this->objOutput = $obj;
            $this->objOutput->setHeaderParams('Content-Type', 'application/json; text/html; charset=utf-8');

            return $this;
        }

        /**
         * @return $this
         *
         * @throws WebserviceException
         */
        public function setWsObject(WebserviceRequest $obj)
        {
            $this->wsObject = $obj;

            return $this;
        }
    }
} else {
    abstract class WebserviceSpecificManagementSendinblueAbstract implements WebserviceSpecificManagementInterface
    {
        use WebServiceSpecificManagementTrait;
        const RESULT = 'result';
        const COUNT = 'count';
        const COUNT_PER_PAGE = 'countPerPage';
        const SUCCESS = 'success';
        const ERROR = 'error';
        const PLUGIN_VERSION = 'plugin_version';
        const SHOP_CURRENCY = 'shop_currency';
        const SHOP_VERSION = 'shop_version';
        const VERSION = 'version';

        /**
         * @var array
         */
        protected $response;

        protected $objOutput;

        protected $wsObject;

        /**
         * @return $this
         */
        public function setObjectOutput(WebserviceOutputBuilderCore $obj)
        {
            $this->objOutput = $obj;
            $this->objOutput->setHeaderParams('Content-Type', 'application/json; text/html; charset=utf-8');

            return $this;
        }

        /**
         * @return $this
         *
         * @throws WebserviceException
         */
        public function setWsObject(WebserviceRequestCore $obj)
        {
            $this->wsObject = $obj;

            return $this;
        }
    }
}
