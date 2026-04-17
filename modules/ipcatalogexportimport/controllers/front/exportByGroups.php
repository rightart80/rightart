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

class IpCatalogExportImportExportByGroupsModuleFrontController extends ModuleFrontController
{

    public function init()
    {
        if (Configuration::getGlobalValue('IPE_SCHDL_ENABLE') && (Configuration::getGlobalValue('IPE_SCHDL_USE_EMAIL') || Configuration::getGlobalValue('IPE_SCHDL_USE_FTP'))) {
            if (Tools::getValue('token') === md5(Configuration::getGlobalValue('IPEI_SECURE_KEY'))) {
                error_reporting(E_ERROR | E_PARSE);
                ini_set('max_execution_time', 0);
//                ini_set('memory_limit', '-1');
                $this->export();
                die;
            } else {
                http_response_code(403);
                die($this->module->l('Token is incorrect.'));
            }
        } else {
            http_response_code(401);
            die($this->module->l('No schedule is enabled in the module.'));
        }
    }

    public function export()
    {
        $this->exportDir = dirname(__FILE__) . '/../../export';

        $fileId = Tools::getValue('fileId');
        $templateId = Tools::getValue('templateId');
        $entity = Tools::getValue('entity');

        $sql = 'SELECT configuration, datatables
                    FROM ' . _DB_PREFIX_ . 'ipcatalogexport i
                    WHERE i.id_ipcatalogexport = ' . (int) $templateId;
        $result = Db::getInstance()->getRow($sql);

        $config = json_decode($result['configuration'], true)[$entity];

        parse_str($config['inputs'], $config['inputs']);

        require_once dirname(__FILE__) . '/../../classes/EIHelper.php';
        $class = EIHelper::allEntities($this->module)[$entity]['class'];
        require_once dirname(__FILE__) . '/../../classes/export/' . $class . '.php';
        $export = new $class($this->module);
        $export->run(array(
            'fileId' => $fileId,
            'offset' => (int) Tools::getValue('offset'),
            'limit' => (int) Tools::getValue('limit'),
            'config' => $config,
            'datatables' => $result['datatables'],
        ));
    }
}
