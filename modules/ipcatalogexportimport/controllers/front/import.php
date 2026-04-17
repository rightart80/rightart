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
require_once dirname(__FILE__) . '/../../classes/EIDataProvider.php';

class IpCatalogExportImportImportModuleFrontController extends ModuleFrontController
{
    private $now;
    private $importDir;
    private $url;
    private $cookies = array();

    public function init()
    {
        if (Configuration::getGlobalValue('IPI_SCHDL_ENABLE') && (Configuration::getGlobalValue('IPI_SCHDL_USE_URL') || Configuration::getGlobalValue('IPI_SCHDL_USE_FTP'))) {
            if (Tools::getValue('token') === md5(Configuration::getGlobalValue('IPEI_SECURE_KEY'))) {
                error_reporting(E_ERROR | E_PARSE);
                ini_set('max_execution_time', 0);
//                ini_set('memory_limit', '-1');
                $this->import();
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

    public function import()
    {
        $this->importDir = dirname(__FILE__) . '/../../import/';

        $dt = new DateTime();
        $this->now = $dt->format('YmdHis');
        $this->url = $this->context->link->getModuleLink('ipcatalogexportimport', 'importByGroups', array('token' => md5(Configuration::getGlobalValue('IPEI_SECURE_KEY'))));
        
        if (Configuration::getGlobalValue('IPI_SCHDL_USE_URL') == '1') {
            $urls_limit = '';
            if (Tools::isSubmit('url_ids')) {
                $urls_limit = ' AND id_ipcatalogimport_url IN (' . pSQL(Tools::getValue('url_ids')) . ') ';
            }

            $sql = 'SELECT 
                    i.id_ipcatalogimport id,
                    ie.`template`,
                    url,
                    entity,
                    IFNULL(configuration,
                        (
                            SELECT configuration
                            FROM ' . _DB_PREFIX_ . 'ipcatalogexport
                            WHERE `name` = "catalog_default")) configuration
                FROM ' . _DB_PREFIX_ . 'ipcatalogimport_url ie
                LEFT JOIN ' . _DB_PREFIX_ . 'ipcatalogimport i ON ie.template = i.`name`
                WHERE ie.active = 1 ' . $urls_limit;
            $result = Db::getInstance()->executeS($sql);
            foreach ($result as &$res) {
                $remote_file_url = $res['url'];
                if (filter_var($remote_file_url, FILTER_VALIDATE_URL) === false) {
                    continue;
                }
                $remote_file = pathinfo($remote_file_url);
                $local_file = $this->now . '-' . $remote_file['basename'];

                if (file_put_contents($this->importDir . $res['entity'] . '/' . $local_file, fopen($remote_file_url, "r"))) {
                    $conf = json_decode($res['configuration'], true)[$res['entity']];

                    $data = $this->importNow(null, 0, 5, -1, '{}',  $res['entity'], $local_file, $conf);
                    // Insert the imported file to the DB
                    if (!empty($data['isFinished'])) {
                        echo sprintf($this->module->l('"%s" imported.'), $remote_file_url) . '<br />';
                    } elseif (!empty($data['errors'])) {
                        foreach ($data['errors'] as $error) {
                            echo $error . '<br />';
                        }
                    } elseif (is_string($data)) {
                        echo $data . '<br />';
                    }
                }
            }
        }

        if (Configuration::getGlobalValue('IPI_SCHDL_USE_FTP') == '1') {
            $ftps_limit = '';
            if (Tools::isSubmit('ftp_ids')) {
                $ftps_limit = ' AND id_ipcatalogimport_ftp IN (' . pSQL(Tools::getValue('ftp_ids')) . ') ';
            }

            $sql = 'SELECT 
                    i.id_ipcatalogimport id,
                    ie.`template`,
                    type,
                    url,
                    port,
                    username,
                    password,
                    entities,
                    IFNULL(configuration,
                        (
                            SELECT configuration
                            FROM ' . _DB_PREFIX_ . 'ipcatalogexport
                            WHERE `name` = "catalog_default")) configuration
                FROM ' . _DB_PREFIX_ . 'ipcatalogimport_ftp ie
                LEFT JOIN ' . _DB_PREFIX_ . 'ipcatalogimport i ON ie.template = i.`name`
                WHERE ie.active = 1 ' . $ftps_limit;
            $result = Db::getInstance()->executeS($sql);
            foreach ($result as &$res) {
                foreach (explode(',', $res['entities']) as $val) {
                    $val = explode(':', $val);
                    if ($val[1] == '1') {
                        $files = $this->downloadFromFTP(
                            $res['type'],
                            $res['url'],
                            $res['port'],
                            $res['username'],
                            $res['password'],
                            $val[2],
                            $val[0]
                        );
                        if ($files && is_array($files)) {
                            $conf = json_decode($res['configuration'], true)[$val[0]];
                            foreach ($files as $file) {
                                $data = $this->importNow(null, 0, 5, -1, '{}', $val[0], $file, $conf);
                                // Insert the imported file to the DB
                                if (!empty($data['isFinished'])) {
                                    Db::getInstance()->execute('INSERT IGNORE INTO ' . _DB_PREFIX_ . 'ipcatalogimport_history
                                        VALUES (NULL, "' . $val[0] . '", "' . $file . '", "' . date('Y-m-d H:i:s') . '");');
                                    echo sprintf($this->module->l('%s -> "%s" imported.'), $val[0], $file) . '<br />';
                                } elseif (!empty($data['errors'])) {
                                    foreach ($data['errors'] as $error) {
                                        echo $error . '<br />';
                                    }
                                } elseif (is_string($data)) {
                                    echo $data . '<br />';
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function downloadFromFTP($type, $url, $port, $username, $password, $remote_folder, $entity)
    {
        if (!$remote_folder) {
            return false;
//            $remote_folder = 'public_ftp';
        }
        $password = html_entity_decode($password);

        if ($type === 'sftp') {
            set_include_path(dirname(__FILE__) . '/../../vendor/phpseclib');
            include_once('Net/SFTP.php');
            if (!$port) {
                $port = 22;
            }
            $sftp = new Net_SFTP($url, $port);
            if ($sftp->login($username, $password)) {
                $files = [];
                foreach ($sftp->nlist($remote_folder) as $content) {
                    $path_parts = pathinfo($content);
                    if ($path_parts['basename'] == '.' || $path_parts['basename'] == '..' || !$path_parts['extension']) {
                        continue;
                    }
                    if (!is_numeric(Tools::substr($path_parts['basename'], 0, 14))) {
                        $path_parts['basename'] = $this->now . '-' . $path_parts['basename'];
                    }
                    if (Db::getInstance()->getValue('SELECT id_ipcatalogimport_history id FROM ' . _DB_PREFIX_ . 'ipcatalogimport_history
                                        WHERE `entity` = "' . $entity . '" AND `file` = "' . $path_parts['basename'] . '"')) {
                        continue;
                    } elseif (file_exists($this->importDir . $entity . '/' . $path_parts['basename'])) {
                        $files[] = $path_parts['basename'];
                    } else {
                        if ((bool) $sftp->get($remote_folder . '/' . $content, $this->importDir . $entity . '/' . $path_parts['basename'])) {
                            $files[] = $path_parts['basename'];
                        } else {
                            echo sprintf($this->module->l('Could not download file "%s" via SFTP.'), $remote_folder . '/' . $content) . '<br />';
                        }
                    }
                }
                return $files;
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
                if (!($contents = ftp_nlist($connId, $remote_folder))) {
                    ftp_pasv($connId, false);
                    $contents = ftp_nlist($connId, $remote_folder);
                }
                if (!$contents) {
                    echo $this->module->l('Could not read the FTP directory.') . '<br />';
                    return false;
                }

                $files = [];
                foreach ($contents as $content) {
                    $path_parts = pathinfo($content);
                    if ($path_parts['basename'] == '.' || $path_parts['basename'] == '..' || !$path_parts['extension']) {
                        continue;
                    }
                    if (!is_numeric(Tools::substr($path_parts['basename'], 0, 14))) {
                        $path_parts['basename'] = $this->now . '-' . $path_parts['basename'];
                    }
                    if (Db::getInstance()->getValue('SELECT id_ipcatalogimport_history id FROM ' . _DB_PREFIX_ . 'ipcatalogimport_history
                                        WHERE `entity` = "' . $entity . '" AND `file` = "' . $path_parts['basename'] . '"')) {
                        continue;
                    } elseif (file_exists($this->importDir . $entity . '/' . $path_parts['basename'])) {
                        $files[] = $path_parts['basename'];
                    } else {
                        if (ftp_get($connId, $this->importDir . $entity . '/' . $path_parts['basename'], $content, FTP_BINARY)) {
                            $files[] = $path_parts['basename'];
                        } else {
                            echo sprintf($this->module->l('Could not download file "%s" via FTP.'), $content) . '<br />';
                        }
                    }
                }
                ftp_close($connId);
                return $files;
            } else {
                echo $this->module->l('Could not connect using FTP.') . '<br />';
                return false;
            }
        }
    }
    
    private function curlResponseHeaderCallback($ch, $headerLine)
    {
        if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $matches) == 1 && !in_array($matches[1], $this->cookies)) {
            $this->cookies[] = $matches[1];
        }
        return strlen($headerLine); // Needed by curl
    }

    public function importNow($ch, $offset, $limit, $total, $crossStepsVariables, $entity, $filename, $config, $moreStep = 0)
    {
        if ($ch === null) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, "curlResponseHeaderCallback"));
        }

        $startingTime = microtime(true);

        $query = $config . '&' . http_build_query([
                'ajax' => 1,
                'action' => 'import',
                'offset' => $offset,
                'limit' => $limit,
                'entity' => $entity,
                'filename' => $filename,
                'crossStepsVars' => $crossStepsVariables,
                'moreStep' => $moreStep
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie: ' . implode('; ', $this->cookies)));
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: XDEBUG_SESSION=netbeans-xdebug"));
        
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo curl_error($ch) . "<br> \n";
        }
        $server_output = json_decode($result, true);
        
        if (!$server_output) {
            curl_close($ch);
            return $result;
        }

        if ($server_output['totalCount']) {
            $total = $server_output['totalCount'];
        }

        if (isset($server_output['isFinished']) && !$server_output['isFinished'] || !empty($server_output['oneMoreStep'])) {
            $previousDelay = microtime(true) - $startingTime;
            $targetDelay = 5 * 1000 * 1000; // try to keep around 5 seconds by call
            // acceleration will be limited to 4 to avoid newLimit to increase too fast (NEVER reach 30 seconds by call!).
            $acceleration = min(4, ($targetDelay / $previousDelay));
            // keep between 5 to 100 elements to process in one call
            $newLimit = min(100, max(5, floor($limit * $acceleration)));
            $newOffset = !empty($server_output['isFinished']) ? 0 : $offset + $limit;
            return $this->importNow(
                            $ch,
                            $newOffset,
                            $newLimit,
                            $total,
                            json_encode($server_output['crossStepsVariables'], JSON_UNESCAPED_SLASHES), // or just $server_output['crossStepsVariables']?
                            $entity,
                            $filename,
                            $config,
                            (int) $server_output['oneMoreStep']
            );
        } else {
            curl_close($ch);
            return $server_output;
        }
    }
}
