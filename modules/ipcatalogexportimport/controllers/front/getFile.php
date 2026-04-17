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

class IpCatalogExportImportGetFileModuleFrontController extends ModuleFrontController
{

    protected $display_header = false;
    protected $display_footer = false;

    public function init()
    {
        $filename = Tools::getValue('file');
        if (!Validate::isSha1($filename)) {
            die(Tools::displayError());
        }
        $file = _PS_DOWNLOAD_DIR_ .  preg_replace('/\.{2,}/', '.', $filename);
        $filename = ProductDownload::getFilenameFromFilename($filename);
        if (empty($filename)) {
            $filename = Tools::getValue('filename');
            if (empty($filename)) {
                $filename = 'file';
            }
        }

        if (!file_exists($file)) {
            die(Tools::displayError('Fatal error'));
        }

        /* Detect mime content type */
        $mimeType = false;
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME);
            $mimeType = @finfo_file($finfo, $file);
            @finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mimeType = @mime_content_type($file);
        }
//        elseif (function_exists('exec')) {
//            $mimeType = trim(@exec('file -b --mime-type ' . escapeshellarg($file)));
//            if (!$mimeType) {
//                $mimeType = trim(@exec('file --mime ' . escapeshellarg($file)));
//            }
//            if (!$mimeType) {
//                $mimeType = trim(@exec('file -bi ' . escapeshellarg($file)));
//            }
//        }

        if (empty($mimeType)) {
            $bName = basename($filename);
            $bName = explode('.', $bName);
            $bName = Tools::strtolower($bName[count($bName) - 1]);

            if (!($mimeType = EIHelper::getMimeFromType($bName))) {
                $mimeType = 'application/octet-stream';
            }
        }

        if (ob_get_level() && ob_get_length() > 0) {
            ob_end_clean();
        }

        /* Set headers for download */
        header('Content-Transfer-Encoding: binary');
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($file));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        //prevents max execution timeout, when reading large files
        @set_time_limit(0);
        $fp = fopen($file, 'rb');

        if ($fp && is_resource($fp)) {
            while (!feof($fp)) {
                echo fgets($fp, 16384);
            }
        }

        exit;
    }
}
