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

class IpCatalogExportImportExportModuleFrontController extends ModuleFrontController
{

    private $now;
    private $exportDir;
    private $url;
    private $fileType;
    private $docName;

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

//        $this->cleanDir($this->exportDir);
        $dt = new DateTime();
        $this->now = $dt->format('YmdHis');
        $this->url = $this->context->link->getModuleLink('ipcatalogexportimport', 'exportByGroups', array('token' => md5(Configuration::getGlobalValue('IPEI_SECURE_KEY'))));
        $speeds = [50, 100, 200, 500, 1000, 2000, 5000, 10000];
        $allEntities = EIHelper::allEntities($this->module);
        $files = [];
        if (Configuration::getGlobalValue('IPE_SCHDL_USE_EMAIL') == '1') {
            $emails_limit = '';
            if (Tools::isSubmit('email_ids')) {
                $emails_limit = ' AND id_ipcatalogexport_email IN (' . pSQL(Tools::getValue('email_ids')) . ') ';
            }

            $sql = 'SELECT 
                        i.id_ipcatalogexport id,
                        address,
                        timestamp,
                        entities,
                        IFNULL(configuration,
                        (
                            SELECT configuration
                            FROM ' . _DB_PREFIX_ . 'ipcatalogexport
                            WHERE `name` = "catalog_default")) configuration, 
                        datatables
                    FROM ' . _DB_PREFIX_ . 'ipcatalogexport_email ie
                    LEFT JOIN ' . _DB_PREFIX_ . 'ipcatalogexport i ON ie.template = i.`name`
                    WHERE ie.active = 1 ' . $emails_limit . '
                ';
            $result = Db::getInstance()->executeS($sql);

            foreach ($result as $res) {
                $config = json_decode($res['configuration'], true);
                $attachments = [];
                $files[$res['id']] = [];
                foreach (explode(',', $res['entities']) as $val) {
                    $conf = $config[$val];
                    parse_str($conf['inputs'], $conf['inputs']);

                    if (isset($files[$res['id']][$val]) && file_exists($files[$res['id']][$val])) {
                        $file = $files[$res['id']][$val];
                    } else {
                        $id = mt_rand() . uniqid();
                        $data = $this->exportNow(null, 0, $speeds[$conf['inputs']['speed']], -1, $val, $id, $res['id']);
                        $files[$res['id']][$val] = $file = $this->exportDir . '/' . $data['fileId'] . '.' . $data['type'];
                    }

                    $this->fileType = $conf['inputs']['as'];
                    $this->docName = $conf['inputs']['doc_name'] ?: $allEntities[$val]['plural'];

                    $file_attachement = array();
                    $file_attachement['content'] = Tools::file_get_contents($file);
                    $file_attachement['name'] = ($res['timestamp'] ? $this->now . '-' : '') . $this->docName;
                    if ($this->fileType === 'xlsx') {
                        $file_attachement['mime'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                        $file_attachement['name'] .= '.xlsx';
                    } elseif ($this->fileType === 'csv') {
                        $file_attachement['mime'] = 'text/csv';
                        $file_attachement['name'] .= '.csv';
                    }
                    $attachments[] = $file_attachement;
                }
                $this->sendPSEmail($res['address'], $this->module->l('Your exports'), $attachments);
            }
        }

        if (Configuration::getGlobalValue('IPE_SCHDL_USE_FTP') == '1') {
            $ftps_limit = '';
            if (Tools::isSubmit('ftp_ids')) {
                $ftps_limit = ' AND id_ipcatalogexport_ftp IN (' . pSQL(Tools::getValue('ftp_ids')) . ') ';
            }

            $sql = 'SELECT 
                        i.id_ipcatalogexport id,
                        ie.`template`,
                        type,
                        url,
                        port,
                        username,
                        password,
                        timestamp,
                        entities,
                        IFNULL(configuration,
                        (
                            SELECT configuration
                            FROM ' . _DB_PREFIX_ . 'ipcatalogexport
                            WHERE `name` = "catalog_default")) configuration, 
                        datatables
                    FROM ' . _DB_PREFIX_ . 'ipcatalogexport_ftp ie
                    LEFT JOIN ' . _DB_PREFIX_ . 'ipcatalogexport i ON ie.template = i.`name`
                    WHERE ie.active = 1 ' . $ftps_limit;
            $result = Db::getInstance()->executeS($sql);
            foreach ($result as $res) {
                $config = json_decode($res['configuration'], true);
                foreach (explode(',', $res['entities']) as $val) {
                    $val = explode(':', $val);
                    if ($val[1] == '1') {
                        $conf = $config[$val[0]];
                        parse_str($conf['inputs'], $conf['inputs']);

                        if (isset($files[$res['id']][$val[0]]) && file_exists($files[$res['id']][$val[0]])) {
                            $file = $files[$res['id']][$val[0]];
                        } else {
                            $id = mt_rand() . uniqid();
                            $data = $this->exportNow(null, 0, $speeds[$conf['inputs']['speed']], -1, $val[0], $id, $res['id']);
                            $files[$res['id']][$val[0]] = $file = $this->exportDir . '/' . $data['fileId'] . '.' . $data['type'];
                        }

                        $this->fileType = $conf['inputs']['as'];
                        $this->docName = $conf['inputs']['doc_name'] ?: $allEntities[$val[0]]['plural'];

                        $this->uploadToFTP(
                            $file,
                            $res['type'],
                            $res['url'],
                            $res['port'],
                            $res['username'],
                            $res['password'],
                            $val[2],
                            $res['timestamp']
                        );
                    }
                }
            }
        }
        // Remove all contents of "export" folder in the end
        $this->cleanDir($this->exportDir);
    }

    public function sendPSEmail($to, $subject, $attachments)
    {
        $dir_mail = false;
        $mail_iso = $this->context->language->iso_code;
        if (!file_exists(dirname(__FILE__) . '/../../mails/' . $mail_iso . '/export.txt') ||
            !file_exists(dirname(__FILE__) . '/../../mails/' . $mail_iso . '/export.html')) {
            $this->copyDir(dirname(__FILE__) . '/../../mails/en', dirname(__FILE__) . '/../mails/' . $mail_iso);
        }
        $dir_mail = dirname(__FILE__) . '/../../mails/';

        $configuration = Configuration::getMultiple(array(
                'PS_SHOP_EMAIL',
                'PS_SHOP_NAME',
        ));

        $template_vars = array('{shop_name}' => $configuration['PS_SHOP_NAME']);

        if (Mail::Send(
            $this->context->language->id,
            'export',
            $subject,
            $template_vars,
            $to,
            null,
            $configuration['PS_SHOP_EMAIL'],
            $configuration['PS_SHOP_NAME'],
            $attachments,
            null,
            $dir_mail,
            null,
            $this->context->shop->id
        )) {
            echo $this->module->l("Email successfully sent.") . '<br />';
            return true;
        } else {
            echo $this->module->l("Email could not sent.") . '<br />';
            return false;
        }
    }

    public function uploadToFTP($file, $type, $url, $port, $username, $password, $remote_folder, $file_add_ts)
    {
        if (!$remote_folder) {
            return false;
//            $remote_folder = 'public_ftp';
        }
        $password = html_entity_decode($password);
        $ext = '.' . $this->fileType;
        $file_path = $file_add_ts ? $remote_folder . '/' . $this->now . '-' . $this->docName . $ext : $remote_folder . '/' . $this->docName . $ext;

        if ($type === 'sftp') {
            set_include_path(dirname(__FILE__) . '/../../vendor/phpseclib');
            include_once('Net/SFTP.php');
            if (!$port) {
                $port = 22;
            }
            $sftp = new Net_SFTP($url, $port);
            if ($sftp->login($username, $password)) {
                if ($sftp->put($file_path, $file, NET_SFTP_LOCAL_FILE)) {
                    echo $this->module->l('File was successfully transferred using SFTP.') . '<br />';
//                    $this->saveScheduleExport('ftp');
                    return true;
                } else {
                    echo $this->module->l('File was unable to be transferred using SFTP.') . '<br />';
                    return false;
                }
            } else {
                echo $this->module->l('Cannot log in using SFTP.') . '<br />';
                return false;
            }
        } else {
            if (!$port) {
                $port = 21;
            }
            // open an FTP/FTPS connection
            if ($type === 'ftps') {
                $connId = ftp_ssl_connect($url, (int) $port);
            } else {
                $connId = ftp_connect($url, (int) $port);
            }
            if ($connId) {
                // login to FTP server
                if (!@ftp_login($connId, $username, $password)) {
                    echo $this->module->l('Could not log in using FTP. Make sure login and/or password is correct.') . '<br />';
                    return false;
                }

                ftp_pasv($connId, true);

                if (ftp_put($connId, $file_path, $file, FTP_BINARY)) {
                    ftp_close($connId);
                    echo $this->module->l('File was successfully transferred using FTP.') . '<br />';
                    return true;
                } else {
                    ftp_pasv($connId, false);
                    if (ftp_put($connId, $file_path, $file, FTP_BINARY)) {
                        ftp_close($connId);
                        echo $this->module->l('File was successfully transferred using FTP (active mode).') . '<br />';
                        return true;
                    }
                    ftp_close($connId);
                    echo $this->module->l('File was unable to be transferred using FTP.') . '<br />';
                    return false;
                }
            } else {
                echo $this->module->l('Could not connect using FTP.') . '<br />';
                return false;
            }
        }
    }

    private function copyDir($src, $dst)
    {
        // open the source directory
        $dir = opendir($src);

        // Make the destination directory if not exist
        @mkdir($dst);

        // Loop through the files in source directory
        while ($file = readdir($dir)) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    // Recursively calling custom copy function
                    // for sub directory
                    $this->copyDir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }

    private function cleanDir($str)
    {
        if (is_file($str)) {
            //Attempt to delete it.
            return unlink($str);
        } elseif (is_dir($str)) {
            //Get a list of the files in this directory.
            $scan = glob(rtrim($str, '/') . '/*');
            //Loop through the list of files.
            foreach ($scan as $path) {
                //Call our recursive function.
                $this->cleanDir($path);
            }
            //Remove the directory itself.
            if ($str !== $this->exportDir) {
                return @rmdir($str);
            } else {
                return 1;
            }
        }
    }

    public function exportNow($ch, $offset, $limit, $total, $entity, $fileId, $templateId)
    {
        if ($ch === null) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $query = http_build_query([
            'action' => 'export',
            'offset' => $offset,
            'limit' => $limit,
            'entity' => $entity,
            'fileId' => $fileId,
            'templateId' => $templateId,
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: XDEBUG_SESSION=netbeans-xdebug"));
        $server_output = curl_exec($ch);
        if (curl_errno($ch)) {
            echo curl_error($ch) . "<br> \n";
        }
        $server_output = json_decode($server_output, true);

        if ($server_output['totalCount']) {
            $total = $server_output['totalCount'];
        }

        if (isset($server_output['isFinished']) && !$server_output['isFinished']) {
            $newOffset = $offset + $limit;
            return $this->exportNow($ch, $newOffset, $limit, $total, $entity, $fileId, $templateId);
        } else {
            curl_close($ch);
            return $server_output;
        }
    }
}