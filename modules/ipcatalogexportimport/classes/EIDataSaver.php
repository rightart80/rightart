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

class EIDataSaver
{

    public $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function saveConfig()
    {
        $config = pSQL(Tools::getValue('config'));
        $datatables = Tools::getValue('datatables');
        $ieType = pSQL(Tools::getValue('ieType'));
//        print_r($datatables);
//        die;
        $name = pSQL(Tools::getValue('name'));
        if (!$name) {
            die(json_encode(array('type' => 'danger', 'message' => $this->module->l('Enter a name.', 'EIDataSaver'))));
        }
//        $config = pSQL(urldecode($config));
//        $config = pSQL($config);
        $sql = 'SELECT
                    `id_ipcatalog' . $ieType . '` 
                FROM `' . _DB_PREFIX_ . 'ipcatalog' . $ieType . "` 
                WHERE `name` = '" . $name . "';";
        $result = DB::getInstance()->executeS($sql);
        if (!empty($result)) {
            die(json_encode(array('type' => 'danger', 'message' => $this->module->l('A template with this name already exists.', 'EIDataSaver'))));
        } else {
            if (DB::getInstance()->insert(
                'ipcatalog' . $ieType,
                array('name' => $name, 'configuration' => $config, 'datatables' => str_replace('&quuot;', '\\\"', $datatables))
            )) {
                die(json_encode(array(
                        'type' => 'success',
                        'message' => $this->module->l('Changes were saved.', 'EIDataSaver'),
                        'configs' => Context::getContext()->controller->getConfigs(),
                )));
            } else {
                die(json_encode(array(
                        'type' => 'danger',
                        'message' => $this->module->l('Changes couldn\'t be saved.', 'EIDataSaver')
                )));
            }
        }
    }

    public function deleteConfig()
    {
        $id = (int) Tools::getValue('id');
        $ieType = pSQL(Tools::getValue('ieType'));
        if (DB::getInstance()->delete('ipcatalog' . $ieType, 'id_ipcatalog' . $ieType . " = $id AND `name` <> 'catalog_default'")) {
            die(json_encode(array(
                    'type' => 'success',
                    'message' => $this->module->l('Template was deleted.', 'EIDataSaver'),
                    'configs' => Context::getContext()->controller->getConfigs(),
            )));
        } else {
            die(json_encode(array(
                    'type' => 'danger',
                    'message' => $this->module->l('Template couldn\'t be deleted.', 'EIDataSaver'),
            )));
        }
    }

    public function saveScheduleEmail()
    {
        $id = (int) Tools::getValue('id');
        $data = Tools::getValue('data');
        $ieType = pSQL(Tools::getValue('ieType'));

        if (!Validate::isEmail($data['address'])) {
            die(json_encode(array(
                    'type' => 'error',
                    'message' => $this->module->l('The email is not valid.', 'EIDataSaver')
            )));
        }

        if ($id) {
            if (DB::getInstance()->update('ipcatalog' . $ieType . '_email', $data, 'id_ipcatalog' . $ieType . '_email = ' . $id)) {
                die(json_encode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully updated.', 'EIDataSaver')
                )));
            } else {
                die(json_encode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be updated.', 'EIDataSaver')
                )));
            }
        } else {
            if (DB::getInstance()->insert('ipcatalog' . $ieType . '_email', $data)) {
                die(json_encode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully added.', 'EIDataSaver')
                )));
            } else {
                die(json_encode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be added.', 'EIDataSaver')
                )));
            }
        }
    }

    public function deleteScheduleEmail()
    {
        $id = (int) Tools::getValue('id');
        $ieType = pSQL(Tools::getValue('ieType'));
        if ($id) {
            if (DB::getInstance()->delete('ipcatalog' . $ieType . '_email', 'id_ipcatalog' . $ieType . '_email = ' . $id)) {
                die(json_encode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully deleted.', 'EIDataSaver')
                )));
            } else {
                die(json_encode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be deleted.', 'EIDataSaver')
                )));
            }
        } else {
            die(json_encode(array('type' => 'error',
                    'message' => $this->module->l('Invalid arguments.', 'EIDataSaver')
            )));
        }
    }

    public function saveScheduleURL()
    {
        $id = (int) Tools::getValue('id');
        $data = Tools::getValue('data');
        $ieType = pSQL(Tools::getValue('ieType'));

        if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
            die(json_encode(array(
                    'type' => 'error',
                    'message' => $this->module->l('The URL is not valid.', 'EIDataSaver')
            )));
        }

        if ($id) {
            if (DB::getInstance()->update('ipcatalog' . $ieType . '_url', $data, 'id_ipcatalog' . $ieType . '_url = ' . $id)) {
                die(json_encode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully updated.', 'EIDataSaver')
                )));
            } else {
                die(json_encode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be updated.', 'EIDataSaver')
                )));
            }
        } else {
            if (DB::getInstance()->insert('ipcatalog' . $ieType . '_url', $data)) {
                die(json_encode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully added.', 'EIDataSaver')
                )));
            } else {
                die(json_encode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be added.', 'EIDataSaver')
                )));
            }
        }
    }
    
    public function deleteScheduleURL()
    {
        $id = (int) Tools::getValue('id');
        $ieType = pSQL(Tools::getValue('ieType'));
        if ($id) {
            if (DB::getInstance()->delete('ipcatalog' . $ieType . '_url', 'id_ipcatalog' . $ieType . '_url = ' . $id)) {
                die(json_encode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully deleted.', 'EIDataSaver')
                )));
            } else {
                die(json_encode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be deleted.', 'EIDataSaver')
                )));
            }
        } else {
            die(json_encode(array('type' => 'error',
                    'message' => $this->module->l('Invalid arguments.', 'EIDataSaver')
            )));
        }
    }
    
    public function saveScheduleFTP()
    {
        $id = (int) Tools::getValue('id');
        $data = Tools::getValue('data');
        $ieType = pSQL(Tools::getValue('ieType'));
        if (!is_numeric($data['port'])) {
            $data['port'] = '';
        }

        if ($id) {
            if (DB::getInstance()->update('ipcatalog' . $ieType . '_ftp', $data, 'id_ipcatalog' . $ieType . '_ftp = ' . $id)) {
                die(json_encode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully updated.', 'EIDataSaver')
                )));
            } else {
                die(json_encode(array(
                        'type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be updated.', 'EIDataSaver')
                )));
            }
        } else {
            if (DB::getInstance()->insert('ipcatalog' . $ieType . '_ftp', $data)) {
                die(json_encode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully added.', 'EIDataSaver')
                )));
            } else {
                die(json_encode(array(
                        'type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be added.', 'EIDataSaver')
                )));
            }
        }
    }

    public function deleteScheduleFTP()
    {
        $id = (int) Tools::getValue('id');
        $ieType = pSQL(Tools::getValue('ieType'));
        if ($id) {
            if (DB::getInstance()->delete('ipcatalog' . $ieType . '_ftp', 'id_ipcatalog' . $ieType . '_ftp = ' . $id)) {
                die(json_encode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully deleted.', 'EIDataSaver')
                )));
            } else {
                die(json_encode(array(
                        'type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be deleted.', 'EIDataSaver')
                )));
            }
        } else {
            die(json_encode(array(
                    'type' => 'error',
                    'message' => $this->module->l('Invalid arguments.', 'EIDataSaver')
            )));
        }
    }

    public function updateSchedule()
    {
        $key = pSQL(Tools::getValue('key'));
        $value = pSQL(Tools::getValue('value'));

        if (Configuration::updateGlobalValue($key, $value)) {
            die(json_encode(array(
                    'type' => 'success',
                    'message' => $this->module->l('Successfully updated.', 'EIDataSaver'),
            )));
        } else {
            die(json_encode(array(
                    'type' => 'error',
                    'message' => $this->module->l('Couldn\'t update.', 'EIDataSaver'),
            )));
        }
    }

    public function setExportSetting($datas = array())
    {
        $res = true;
        foreach ($datas as $data) {
            $res &= Db::getInstance()->insert('ipcatalogexport', $data, false, true, Db::INSERT_IGNORE);
        }

        return $res;
    }

    public function setImportSetting($datas = array())
    {
        $res = true;
        foreach ($datas as $data) {
            $res &= Db::getInstance()->insert('ipcatalogimport', $data, false, true, Db::INSERT_IGNORE);
        }

        return $res;
    }
}
