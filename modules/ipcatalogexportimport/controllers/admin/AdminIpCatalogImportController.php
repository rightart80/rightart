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
require_once(dirname(__FILE__) . '/../../classes/EIDataSaver.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminIpCatalogImportController extends ModuleAdminController
{

    public static $column_mask;
    public $path;
    public $importDir;
    public $entity;
    public $file;
    public $separator;
    public $enclosure;
    public $convert;
    public $fileTypes;
    public $eIHelper;

    public function __construct()
    {
        // Enable bootstrap
        $this->bootstrap = true;

        // Call of the parent constructor method
        parent::__construct();

        // Save the module path in a variable
        $this->path = __PS_BASE_URI__ . 'modules/ipcatalogexportimport/';

        $this->importDir = dirname(__FILE__) . '/../../import/';
        
        $this->fileTypes = ['csv', 'txt', 'xls', 'xlsx', 'ods'];
        
        $this->eIHelper = new EIHelper($this->module);
    }

    public function init()
    {
        error_reporting(E_ERROR | E_PARSE);
        ini_set('max_execution_time', 0);
//        ini_set('memory_limit', '-1');

        /*
         * If values have been submitted in the form, process.
         */
        if ((bool) Tools::isSubmit('ajax_action')) {
            $action = Tools::getValue('ajax_action');
            $type = Tools::getValue('type');
            if ($type === 'dml') {
                $saver = new EIDataSaver($this->module);
                if (method_exists($saver, $action)) {
                    $saver->{$action}();
                }
            } else {
                if (method_exists('EIDataProvider', $action)) {
                    EIDataProvider::$action($this->module);
                }
            }
        } else {
            $actions_list = array(
                'downloadFile' => 'downloadFile',
                'deleteFile' => 'deleteFile',
                'getDoc' => 'getDoc',
                'getSetting' => 'getSetting',
                'importSetting' => 'importSetting'
            );
            $action = Tools::getValue('action');
            if (isset($actions_list[$action])) {
                $this->{$actions_list[$action]}();
            } else {
                foreach ($actions_list as $key => $action) {
                    if (Tools::getIsset($key)) {
                        $this->$action();
                    }
                }
            }
        }

        parent::init();
    }

    public function initContent()
    {
        parent::initContent();

        $columns = $files = $paginator = [];
        foreach ($this->eIHelper->fields as $entity => $fields) {
            $files[$entity] = ['data' => []];
            foreach (new DirectoryIterator($this->importDir . $entity) as $fileInfo) {
                if (in_array($fileName = $fileInfo->getFilename(), ['.', '..', 'index.php'])) {
                    continue;
                }
                $files[$entity]['data'][] = ['file' => $fileName, 'size' => $this->formatSizeUnits($fileInfo->getSize())];
            }
//            $files[$entity]['data'] = array_reverse($files[$entity]['data']);
            $files[$entity]['recordsTotal'] = $files[$entity]['recordsFiltered'] = count($files[$entity]['data']);

            $columns[$entity] = '';
            $nb_column = count($fields) - 1;
            $nb_table = ceil($nb_column / MAX_COLUMNS);

            $paginator[$entity] = [
                'currentViewTable' => 0,
                'nbTable' => $nb_table
            ];

            for ($i = 0; $i < $nb_table; $i++) {
                $columns[$entity] .= $this->generateColumnsPreview($i, $nb_column, $entity, $fields);
            }
        }

        $this->context->smarty->assign(array(
            'module_path' => $this->path,
            'ajax_url' => $this->context->link->getAdminLink('AdminIpCatalogImport'),
            'lang_iso_code' => $this->context->language->iso_code,
            'lang_id' => $this->context->language->id,
            'languages' => $this->context->controller->getLanguages(),
            'shop_feature' => Shop::isFeatureActive(),
            'shops' => Shop::getShops(),
            'context_shop' => $this->context->shop->id,
            'configs' => $this->getConfigs(),
            'entities' => EIHelper::allEntities($this->module),
            'entities_unordered' => EIHelper::allEntitiesUnordered($this->module),
            'files' => $files,
            'columns' => $columns,
            'paginator' => $paginator,
            'id_employee' => $this->context->employee->id,
            'initial_data' => json_encode([
                'urls' => EIDataProvider::getImportScheduleURLs($this->module, true),
                'ftps' => EIDataProvider::getImportScheduleFTPs($this->module, true),
            ]),
            'schedule_url' => $this->context->link->getModuleLink('ipcatalogexportimport', 'import', array('token' => md5(Configuration::getGlobalValue('IPEI_SECURE_KEY')))),
            'schedule_enabled' => Configuration::getGlobalValue('IPI_SCHDL_ENABLE'),
            'schedule_url_enabled' => Configuration::getGlobalValue('IPI_SCHDL_USE_URL'),
            'schedule_ftp_enabled' => Configuration::getGlobalValue('IPI_SCHDL_USE_FTP'),
            'show_isbn' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product` LIKE 'isbn'")),
            'show_mpn' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product` LIKE 'mpn'")),
            'show_delivery_date' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product_lang` LIKE 'delivery_in_stock'")),
            'show_add_delivery_time' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product` LIKE 'additional_delivery_times'")),
            'show_show_condition' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product_shop` LIKE 'show_condition'")),
            'show_low_stock' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product_shop` LIKE 'low_stock_threshold'")),
            'id_type_redirected' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product_shop` LIKE 'id_type_redirected'")),
            'adModules' => $this->module->getRandomModulesForAd(),
            'path_uri' => $this->path,
            'lang_iso' => $this->context->language->iso_code,
        ));
        $this->setTemplate('import.tpl');
    }
    
    public function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
    
    public function downloadFromFTP($type, $url, $port, $username, $password, $remote_file)
    {
        $file = pathinfo($remote_file);
        if (!in_array(Tools::strtolower($file['extension']), $this->fileTypes)) {
            die(json_encode([
                    'error' => $this->l('File type is incorrect. It must be one of these') . ': .' . implode(', .', $this->fileTypes)
            ]));
        }
        
        $filename_prefix = date('YmdHis') . '-';
        $this->file = $this->importDir . $this->eIHelper->entity . '/' . $filename_prefix . $file['basename'];

        if ($type === 'sftp') {
            set_include_path(dirname(__FILE__) . '/../../vendor/phpseclib');
            include_once('Net/SFTP.php');
            if (!$port) {
                $port = 22;
            }
            $sftp = new Net_SFTP($url, $port);
            if ($sftp->login($username, $password)) {
                if ((bool) $sftp->get($remote_file, $this->file)) {
                    die(json_encode([
                            'filename' => $filename_prefix . $file['basename'],
                            'error' => 0,
                            ] + $this->getFileData()));
                } else {
                    die(json_encode([
                            'error' => sprintf($this->l('Could not download file "%s" via SFTP.'), $remote_file)
                    ]));
                }
            } else {
                die(json_encode([
                        'error' => $this->l('Cannot log in using SFTP')
                ]));
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
                    die(json_encode([
                            'error' => $this->l('Could not log in using FTP. Make sure login and/or password is correct.')
                    ]));
                }
                ftp_pasv($connId, true);
                if (!ftp_get($connId, $this->file, $remote_file, FTP_BINARY)) {
                    ftp_pasv($connId, false);
                    if (ftp_get($connId, $this->file, $remote_file, FTP_BINARY)) {
                        ftp_close($connId);
                        die(json_encode([
                            'filename' => $filename_prefix . $file['basename'],
                            'error' => 0,
                            ] + $this->getFileData()));
                    } else {
                        ftp_close($connId);
                        die(json_encode([
                                'error' => sprintf($this->l('Could not download file "%s" via FTP.'), $remote_file)
                        ]));
                    }
                } else {
                    ftp_close($connId);
                    die(json_encode([
                            'filename' => $filename_prefix . $file['basename'],
                            'error' => 0,
                            ] + $this->getFileData()));
                }
            } else {
                die(json_encode([
                        'error' => $this->l('Could not connect using FTP.')
                ]));
            }
        }
    }

    public function ajaxProcessUpload()
    {
        if (($file_url = Tools::getValue('from_url'))) {
            if (filter_var($file_url, FILTER_VALIDATE_URL) === false) {
                die(json_encode([
                    'error' => $this->l('Please provide a valid URL')
                ]));
            }

            $content = array_change_key_case(get_headers($file_url, 1), CASE_LOWER);

            // by header
            $realfilename = 'import.csv';
            if ($content['content-disposition']) {
                $tmp_name = explode('=', $content['content-disposition']);
                if ($tmp_name[1]) {
                    $realfilename = trim($tmp_name[1], '";\'');
                }
            } else {
                // by URL Basename
                $stripped_url = preg_replace('/\\?.*/', '', $file_url);
                $realfilename = basename($stripped_url);
            }

//            $file = pathinfo($file_url);
//            if (!in_array(Tools::strtolower($file['extension']), $this->fileTypes)) {
//                die(json_encode([
//                        'error' => $this->l('File type is incorrect. It must be one of these') . ': .' . implode(', .', $this->fileTypes)
//                ]));
//            }

            $filename_prefix = date('YmdHis') . '-';
            $this->file = $this->importDir . $this->eIHelper->entity . '/' . $filename_prefix . $realfilename;
            if (file_put_contents($this->file, fopen($file_url, "r"))) {
                die(json_encode([
                        'filename' => $filename_prefix . $realfilename,
                        'error' => 0,
                        ] + $this->getFileData()));
            } else {
                die(json_encode([
                        'error' => $this->l('An error occurred while downloading / copying the file.')
                ]));
            }
        } elseif (Tools::getValue('from_ftp')) {
            $file_type = Tools::getValue('ftp_type');
            $ftp_url = Tools::getValue('ftp_url');
            $ftp_port = Tools::getValue('ftp_port');
            $ftp_username = Tools::getValue('ftp_username');
            $ftp_password = Tools::getValue('ftp_password');
            $ftp_filepath = Tools::getValue('ftp_filepath');
            if (!$file_type || !$ftp_url || !$ftp_username || !$ftp_password || !$ftp_filepath) {
                die(json_encode([
                    'error' => $this->l('Please fill in the required fields')
                ]));
            }
            $this->downloadFromFTP($file_type, $ftp_url, $ftp_port, $ftp_username, $ftp_password, $ftp_filepath);
        } elseif (($use = Tools::getValue('use'))) {
            $ext = pathinfo($use, PATHINFO_EXTENSION);
            if (!in_array(Tools::strtolower($ext), $this->fileTypes)) {
                die(json_encode([
                    'error'=> $this->l('File type is incorrect. It must be one of these') . ': .' . implode(', .', $this->fileTypes)
                ]));
            }
            $this->file = $this->importDir . $this->eIHelper->entity . '/' . $use;
            die(json_encode(array_merge(array('error' => 0, 'filename' => $use), $this->getFileData())));
        } elseif (isset($_FILES['file'])) {
            $filename = pathinfo($_FILES['file']['name']);
            if (!in_array(Tools::strtolower($filename['extension']), $this->fileTypes)) {
                $_FILES['file']['error'] = $this->l('File type is incorrect. It must be one of these') . ': .' . implode(', .', $this->fileTypes);
            }
            $filename_prefix = date('YmdHis') . '-';
            $this->file = $this->importDir . $this->eIHelper->entity . '/' . str_replace("\0", '', $filename_prefix . $filename['filename']) . '.' . $filename['extension'];

            if (!empty($_FILES['file']['error'])) {
                switch ($_FILES['file']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $_FILES['file']['error'] = $this->l('The uploaded file exceeds the upload_max_filesize directive in php.ini. If your server configuration allows it, you may add a directive in your .htaccess.');
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $_FILES['file']['error'] = $this->l('The uploaded file exceeds the post_max_size directive in php.ini.
                        If your server configuration allows it, you may add a directive in your .htaccess, for example:')
                            . '<br/><a href="' . $this->context->link->getAdminLink('AdminMeta') . '" >
                    <code>php_value post_max_size 20M</code> ' .
                            $this->l('(click to open "Generators" page)') . '</a>';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $_FILES['file']['error'] = $this->l('The uploaded file was only partially uploaded.');
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $_FILES['file']['error'] = $this->l('No file was uploaded.');
                        break;
                }
            } elseif (!preg_match('/.*\.(' . implode('|', $this->fileTypes) . ')$/i', $_FILES['file']['name'])) {
                $_FILES['file']['error'] = $this->l('The extension of your file should be one of these') . ': .' . implode(', .', $this->fileTypes);
            } elseif (@filemtime($_FILES['file']['tmp_name']) && @move_uploaded_file($_FILES['file']['tmp_name'], $this->file)) {
                @chmod($this->file, 0664);
                $_FILES['file']['filename'] = str_replace("\0", '', $filename_prefix . $filename['filename']) . '.' . $filename['extension'];
                $_FILES['file'] = array_merge($_FILES['file'], $this->getFileData());
            } else {
                $_FILES['file']['error'] = $this->l('An error occurred while uploading / copying the file.');
            }
            die(json_encode($_FILES['file']));
        } else {
            $_FILES['file']['error'] = $this->l('No file was uploaded.');
            die(json_encode($_FILES['file']));
        }
    }

    public function ajaxProcessImport()
    {
        require_once dirname(__FILE__) . '/../../classes/import/EIImport.php';
        $import = new EIImport($this);
        try {
            $import->import();
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
            if (_PS_MODE_DEV_) {
                $errMsg .= ' --- ' . $e->getTraceAsString();
            }
            $this->errors[] = $errMsg;
        }
    }

    public function setMedia($isNewTheme = false)
    {
        // We call the parent method
        parent::setMedia($isNewTheme);
        $this->bootstrap = true;
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->path . 'views/js/datatables.min.js');
        $this->context->controller->addJS($this->path . 'views/js/dataTables.checkboxes.min.js');
        $this->context->controller->addJQueryUI('ui.sortable');
        $this->context->controller->addCSS($this->path . 'views/css/datatables.min.css', 'all');
        $this->context->controller->addCSS($this->path . 'views/css/dataTables.checkboxes.css', 'all');
        
        // If the PS version supports including assets with any version, then include this way
        $ref = new ReflectionMethod($this->context->controller, 'addJs');
        if (count($ref->getParameters()) == 2) {
            $this->context->controller->addJS($this->path . 'views/js/back.js?v=' . $this->module->version, false);
            $this->context->controller->addJS($this->path . 'views/js/import.js?v=' . $this->module->version, false);
            $this->context->controller->addCSS($this->path . 'views/css/back.css?v=' . $this->module->version, 'all', null, false);
        } else {
            $this->context->controller->addJS($this->path . 'views/js/back.js');
            $this->context->controller->addJS($this->path . 'views/js/import.js');
            $this->context->controller->addCSS($this->path . 'views/css/back.css', 'all');
        }
        
        $this->context->controller->addJqueryPlugin('tagify');
    }

    public function getConfigs()
    {
        $confs = DB::getInstance()->executeS('SELECT 
                                                    `id_ipcatalogimport`, 
                                                    `name`,
                                                    `configuration`,
                                                    "' . $this->l('Delete') . '" `title`
                                                FROM `' . _DB_PREFIX_ . 'ipcatalogimport`
                                                ORDER BY `id_ipcatalogimport` DESC;');
        foreach ($confs as &$conf) {
            $conf['configuration'] = str_replace("'", '&apos;', $conf['configuration']);
        }
        return $confs;
    }

    private function getFileData()
    {
        $this->separator = Tools::getValue('csv_separator');
        $this->enclosure = '"';
        $this->convert = (int) Tools::getValue('convert');

        if ($this->separator === 't') {
            $this->separator = "\t";
        }

//        if ($this->enclosure === 'none') {
//            $this->enclosure = '';
//        } elseif ($this->enclosure === 'quot') {
//            $this->enclosure = '"';
//        }

        if (in_array(Tools::strtolower(pathinfo($this->file, PATHINFO_EXTENSION)), ['xls', 'xlsx', 'ods'])) {
            if (file_exists($this->file)) {
                if (file_exists($this->importDir . 'tmp/' . pathinfo($this->file, PATHINFO_FILENAME) . '.csv')) {
                    $this->file = $this->importDir . 'tmp/' . pathinfo($this->file, PATHINFO_FILENAME) . '.csv';
                } else {
                    require_once dirname(__FILE__) . '/../../vendor/autoload.php';
                    $spreadsheet = IOFactory::load($this->file);
                    $writer = IOFactory::createWriter($spreadsheet, 'Csv');
                    $writer->setDelimiter($this->separator);
                    $writer->setEnclosure($this->enclosure);
                    $this->file = $this->importDir . 'tmp/' . pathinfo($this->file, PATHINFO_FILENAME) . '.csv';
                    $writer->save($this->file);
                    unset($spreadsheet);
                    unset($writer);
                }
            } else {
                return ['errors' => [$this->l('File does not exist!')]];
            }
        }

        $handle = $this->openCsvFile();
        $nb_column = $this->getNbrColumn($handle);
        $nb_table = ceil($nb_column / MAX_COLUMNS);

//        $res = array();
//        foreach ($this->eIHelper->required_fields as $elem) {
//            $res[] = '\'' . $elem . '\'';
//        }

        $data = array();
        for ($i = 0; $i < $nb_table; $i++) {
            $data[$i] = $this->generateContentTable($i, $nb_column, $handle);
        }

        $this->closeCsvFile($handle);

        return array(
//            'import_matchs' => Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'import_match', true, false),
            'nb_table' => $nb_table,
            'nb_column' => $nb_column,
            'required_fields' => $this->eIHelper->required_fields,
            'max_columns' => MAX_COLUMNS,
//            'available_fields' => $this->eIHelper->available_fields,
            'data' => $data,
            'pages' => $this->generatePages($nb_table)
        );
    }
    
    protected function generatePages($nb_table)
    {
        $entity = Tools::getValue('entity');
        $pages = '';
        if ($nb_table > 7) {
            $pages .= '
                <span id="btn_' . $entity . '_left_dots" class="hide">...</span>';
            for ($i = 2; $i <= $nb_table - 1; $i++) {
                $pages .= '
                    <button id="btn_' . $entity . '_page_' . $i . '" type="button" class="btn btn-default btn_hideable btn_page';
                if ($i > 5) {
                    $pages .= ' hide';
                }
                $pages .= '" data-entity="' . $entity . '">' . $i . '</button>';
            }
            $pages .= '
                <span id="btn_' . $entity . '_right_dots">...</span>';
            $pages .= '
                <button id="btn_' . $entity . '_page_' . $nb_table . '" type="button" class="btn btn-default btn_page" data-entity="' . $entity . '">' . $nb_table . '</button>';
        } else {
            for ($i = 2; $i <= $nb_table; $i++) {
                $pages .= '
                    <button id="btn_' . $entity . '_page_' . $i . '" type="button" class="btn btn-default btn_page" data-entity="' . $entity . '">' . $i . '</button>';
            }
        }

        return $pages;
    }

    protected function getNbrColumn($handle)
    {
        if (!is_resource($handle)) {
            return false;
        }
        $tmp = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure);
        self::rewindBomAware($handle);

        return count($tmp);
    }

    protected function openCsvFile($offset = false)
    {
        self::setLocale();
        
        $handle = false;
        if (is_file($this->file) && is_readable($this->file)) {
//            if (!mb_check_encoding(file_get_contents($this->file), 'UTF-8')) {
//                $this->convert = true;
//            }
            $handle = fopen($this->file, 'rb');
        }

        if (!$handle) {
            $this->errors[] = Tools::displayError('Cannot read the file');
        }

        self::rewindBomAware($handle);

        $toSkip = (int) Tools::getValue('skip');
        if ($offset && $offset > 0) {
            $toSkip += $offset;
        }

        for ($i = 0; $i < $toSkip; $i++) {
            $line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure);
            if ($line === false) {
                return false; // reached end of file
            }
        }
        return $handle;
    }

    protected function closeCsvFile($handle)
    {
        fclose($handle);
    }

    protected function generateColumnsPreview($current_table, $nb_column, $entity, $fields)
    {
        $html = '<table id="table_' . $entity . $current_table . '" ' . ($current_table !== 0 ? 'style="display: none;"' : '') . ' class="table table-bordered"><thead><tr>';
        // Header
        for ($i = 0; $i < $nb_column; $i++) {
            if (MAX_COLUMNS * (int) $current_table <= $i && (int) $i < MAX_COLUMNS * ((int) $current_table + 1)) {
                $html .= '<th>
                            <select id="type_value_' . $entity . '[' . $i . ']"
                                name="type_value[' . $i . ']"
                                class="type_value">
                                ' . $this->getTypeValuesOptionsPreview($i, $fields) . '
                            </select>
                        </th>';
            }
        }
        $html .= '</tr></thead></table>';

        return $html;
    }

    protected function getTypeValuesOptionsPreview($nb_c, $fields)
    {
        $i = 0;

        $options = '';
        foreach ($fields as $k => $field) {
            $options .= '<option value="' . $k . '"';
            if ($i === $nb_c + 1) {
                $options .= ' selected="selected"';
            }
            $options .= '>' . $field['label'] . '</option>';
            $i++;
        }
        return $options;
    }

    protected function generateContentTable($current_table, $nb_column, $handle)
    {
        $html = '<table id="table_' . $this->eIHelper->entity . $current_table . '" style="display: none;" class="table table-bordered"><thead><tr>';
        // Header
        for ($i = 0; $i < $nb_column; $i++) {
            if (MAX_COLUMNS * (int) $current_table <= $i && (int) $i < MAX_COLUMNS * ((int) $current_table + 1)) {
                $html .= '<th>
                            <select id="type_value_' . $this->eIHelper->entity . '[' . $i . ']"
                                name="type_value[' . $i . ']"
                                class="type_value">
                                ' . $this->getTypeValuesOptions($i) . '
                            </select>
                        </th>';
            }
        }
        $html .= '</tr></thead><tbody>';
        
        for ($current_line = 0; $current_line < 10 && $line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure); $current_line++) {
            /* UTF-8 conversion */
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }
            $html .= '<tr id="table_' . $this->eIHelper->entity . $current_table . '_line_' . $current_line . '">';
            foreach ($line as $nb_c => $column) {
                if ((MAX_COLUMNS * (int) $current_table <= $nb_c) && ((int) $nb_c < MAX_COLUMNS * ((int) $current_table + 1))) {
                    $html .= '<td>' . htmlentities(Tools::substr($column, 0, 200), ENT_QUOTES, 'UTF-8') . '</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        self::rewindBomAware($handle);
        return $html;
    }

    protected function getTypeValuesOptions($nb_c)
    {
        $i = 0;

        $options = '';
        foreach ($this->eIHelper->available_fields as $k => $field) {
            $options .= '<option value="' . $k . '"';
            if ($i === $nb_c + 1) {
                $options .= ' selected="selected"';
            }
            $options .= '>' . $field['label'] . '</option>';
            $i++;
        }
        return $options;
    }

    public function utf8EncodeArray($array)
    {
        return (is_array($array) ? array_map('utf8_encode', $array) : utf8_encode($array));
    }

    protected static function rewindBomAware($handle)
    {
        // A rewind wrapper that skips BOM signature wrongly
        if (!is_resource($handle)) {
            return false;
        }
        rewind($handle);
        if (($bom = fread($handle, 3)) != "\xEF\xBB\xBF") {
            rewind($handle);
        }
    }

    public static function setLocale()
    {
        $iso_lang = Language::getIsoById(Tools::getValue('language'));
        setlocale(LC_COLLATE, Tools::strtolower($iso_lang) . '_' . Tools::strtoupper($iso_lang) . '.UTF-8');
        setlocale(LC_CTYPE, Tools::strtolower($iso_lang) . '_' . Tools::strtoupper($iso_lang) . '.UTF-8');
//        setlocale(LC_ALL, Tools::strtolower($iso_lang) . '_' . Tools::strtoupper($iso_lang) . '.UTF-8');
    }

    public function downloadFile()
    {
        $fileName = Tools::getValue('downloadFile');
        $entity = Tools::getValue('entity');
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $fileName . "\"");
        readfile($this->importDir . $entity . '/' . $fileName);
        exit;
    }

    public function deleteFile()
    {
        $f = Tools::getValue('deleteFile');
        $file = $this->importDir . Tools::getValue('entity') . '/' . $f;
        if (file_exists($file)) {
            if (@unlink($file)) {
                if (file_exists($f = $this->importDir . 'tmp/' . pathinfo($f, PATHINFO_FILENAME) . '.csv')) {
                    @unlink($f);
                }
                die(json_encode(array(
                        'state' => 1,
                        'msg' => $this->l('File deleted.')
                )));
            } else {
                die(json_encode(array(
                        'state' => 0,
                        'msg' => $this->l('File could not be deleted.')
                )));
            }
        } else {
            die(json_encode(array(
                    'state' => 0,
                    'msg' => $this->l('File does not exist.')
            )));
        }
    }

    private function getSetting()
    {
        $dP = new EIDataProvider();

        $txt_arr = $dP->getImportSetting(Tools::getValue('st_id'));
        $serialized_txt = json_encode($txt_arr);

        if (count($txt_arr) > 1) {
            header('Content-Disposition: attachment; filename="all_catalog_import_settings.txt"');
        } else {
            header('Content-Disposition: attachment;' . 'filename=' . $txt_arr[0]['name'] . '.txt');
        }
        header('Content-Type: text/plain'); # Don't use application/force-download - it's not a real MIME type, and the Content-Disposition header is sufficient
        header('Content-Length: ' . strlen($serialized_txt));
        header('Connection: close');

        die($serialized_txt);
    }

    private function importSetting()
    {
        try {
            $files = $_FILES['file']['tmp_name'];
            if ($files) {
                foreach ($files as $key => $value) {
                    $file_name = $_FILES['file']['name'][$key];
                    $target = dirname(__FILE__) . '/../../import/' . $file_name;
                    move_uploaded_file($files[$key], $target);

                    $file = fopen($target, 'r');
                    $data = fread($file, filesize($target));
                    $unserialized_data = json_decode($data, true);
                    fclose($file);
                    @unlink($target);

                    $eC = new EIDataSaver($this->module);
                    if (!$eC->setImportSetting($unserialized_data)) {
                        throw new Exception($this->l('Could not import some settings.'));
                    }
                }
                die(json_encode(array(
                    'type' => 'success',
                    'message' => $this->l('File was succesfully imported.'),
                    'configs' => $this->getConfigs(),
                )));
            }
            throw new Exception($this->l('No file to import!'));

        } catch (Exception $e) {
            die(json_encode(array(
                'type' => 'error',
                'message' => $e->getMessage()
            )));
        }
    }
}
