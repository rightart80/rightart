<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    SmartPresta <tehran.alishov@gmail.com>
 *  @copyright 2023 SmartPresta
 *  @license   Commercial License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../classes/EIHelper.php';

class IpCatalogExportImportImportByGroupsModuleFrontController extends ModuleFrontController
{
    public $eIHelper;

    public function __construct()
    {
        // Call of the parent constructor method
        parent::__construct();
        
        $this->eIHelper = new EIHelper($this->module);
    }

    public function init()
    {
        if (Configuration::getGlobalValue('IPI_SCHDL_ENABLE')) {
            if (Tools::getValue('token') === md5(Configuration::getGlobalValue('IPEI_SECURE_KEY'))) {
                error_reporting(E_ERROR | E_PARSE);
                ini_set('max_execution_time', 0);
//                ini_set('memory_limit', '-1');
                require_once dirname(__FILE__) . '/../../classes/import/EIImport.php';
                $import = new EIImport($this);
                $import->import();
            } else {
                http_response_code(403);
                die($this->module->l('Token is incorrect.'));
            }
        } else {
            http_response_code(401);
            die($this->module->l('No schedule is enabled in the module.'));
        }
    }
}
