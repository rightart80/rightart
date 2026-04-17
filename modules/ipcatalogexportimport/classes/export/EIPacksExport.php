<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    SmartPresta <tehran.alishov@gmail.com>
 *  @copyright 2023 SmartPresta
 *  @license   http://opensource.org/licenses/afl-3.0.php Commercial License!
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class EIPacksExport
{

    public $module;
    protected $path;
    private $langId;
    private $sql;
    private $selectedColumns;
    private $exportDir;
    private $auto;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->exportDir = dirname(__FILE__) . '/../../export/';
    }

    private function getPacks($getTotal = false)
    {
        if ($this->auto) {
            $this->langId = $langId = (int) $this->inputs['language'];
            $shopId = (int) $this->inputs['shop'] ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $this->sortWay = (int) $this->inputs['sort_way'] === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL($this->inputs['sort']);
            $this->multivalueSeparator = pSQL($this->inputs['multivalue_separator']) ?: ',';

            $packs = '"' . pSQL(implode('","', $this->datatables['packs']['data'])) . '"';
            $packsType = $this->datatables['packs']['type'];
        } else {
            $this->langId = $langId = (int) Tools::getValue('language');
            $shopId = (int) Tools::getValue('shop') ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $this->sortWay = (int) Tools::getValue('sort_way') === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL(Tools::getValue('sort'));
            $this->multivalueSeparator = pSQL(Tools::getValue('multivalue_separator')) ?: ',';

            $packs = pSQL(Tools::getValue('packs_data'));
            $packs = '"' . implode('","', explode(',', $packs)) . '"';
            $packsType = Tools::getValue('packs_type');
        }
        
        $this->sql = '
            SELECT ';
        foreach ($this->selectedColumns as $k => $col) {
            $this->sql .= "
                    $k `$col`, ";
        }

        $this->sql = rtrim($this->sql, ', ');

        $this->sql .= '
            FROM 
            ' . _DB_PREFIX_ . 'pack pack
            JOIN (SELECT product.id_product, product.reference, product_lang.name FROM ' . _DB_PREFIX_ . 'product product
                JOIN ' . _DB_PREFIX_ . 'product_lang product_lang ON product.id_product = product_lang.id_product
                    AND product_lang.id_shop = ' . $shopId . '
                    AND product_lang.id_lang = ' . $langId . ') pack_name ON pack.id_product_pack = pack_name.id_product
            JOIN ' . _DB_PREFIX_ . 'product product ON pack.id_product_item = product.id_product
            JOIN ' . _DB_PREFIX_ . 'product_lang product_lang ON product.id_product = product_lang.id_product
                AND product_lang.id_shop = ' . $shopId . '
                AND product_lang.id_lang = ' . $langId . '
            LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute combination ON pack.id_product_attribute_item = combination.id_product_attribute
            LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_shop product_attribute_shop ON combination.id_product_attribute = product_attribute_shop.id_product_attribute
                AND product_attribute_shop.id_shop = ' . $shopId . '
            LEFT JOIN (
                SELECT                        
                    pac.id_product_attribute,
                    GROUP_CONCAT(CONCAT_WS(":", IFNULL(agl.name, ""), al.name) ORDER BY agl.name, al.name SEPARATOR ", ") name_values,
                    GROUP_CONCAT(CONCAT_WS(":", agl.name, agl.public_name, ag.group_type) ORDER BY agl.name, al.name SEPARATOR ", ") `groups`,
                    GROUP_CONCAT(CONCAT_WS(":", agl.name, al.name) ORDER BY agl.name, al.name SEPARATOR ", ") `values`
                FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                JOIN ' . _DB_PREFIX_ . 'attribute_shop ash ON a.id_attribute = ash.id_attribute AND ash.id_shop = ' . $shopId . '
                JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $langId . '
                JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                JOIN ' . _DB_PREFIX_ . 'attribute_group_shop agsh ON a.id_attribute_group = agsh.id_attribute_group AND agsh.id_shop = ' . $shopId . '
                JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $langId . '
                GROUP BY pac.id_product_attribute
            ) attributes ON combination.id_product_attribute = attributes.id_product_attribute
            ';

        $this->sql .= '
                WHERE 1
            ';
        
        // Filter By Pack
        $packCond = '';
        if ($packs) {
            if ($packsType === 'unselected') {
                $packCond = 'CONCAT_WS("-", pack.id_product_pack, pack.id_product_item, pack.id_product_attribute_item) NOT IN (' . $packs . ')';
            } else {
                $packCond = 'CONCAT_WS("-", pack.id_product_pack, pack.id_product_item, pack.id_product_attribute_item) IN (' . $packs . ')';
            }
        }
        if ($packCond) {
            $this->sql .= ' 
                        AND (' . $packCond . ') ';
        }

        // Sort By ...
        $this->sql .= ' ORDER BY ' . $this->sort . $this->sortWay;
        if ($this->sort !== 'pack.id_product_pack') {
            $this->sql .= ', pack.id_product_pack ASC';
        }

        if (!$getTotal) {
            $this->sql .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
        }

//        die($this->sql);
        return Db::getInstance()->executeS($this->sql);
    }

    public function run($auto = null)
    {
        $this->auto = $auto;
        $this->encl = '"';

        if ($auto) {
            $this->inputs = $auto['config']['inputs'];

            $this->fileType = $this->inputs['as'];
            $docName = $this->inputs['doc_name'] ?: $this->module->l('Packs', 'EIPacksExport');

            $this->selectedColumns = $this->module->convertToUsableColumns($auto['config']['columns']);

            if ($this->fileType === 'csv') {
                $this->dlm = $this->inputs['csv_separator'];
//                $this->encl = $this->inputs['csv_enclosure'];

                if ($this->dlm === 't') {
                    $this->dlm = "\t";
                }

//                if ($this->encl === 'none') {
//                    $this->encl = '';
//                } elseif ($this->encl === 'quot') {
//                    $this->encl = '"';
//                }
            }

            $this->datatables = json_decode($auto['datatables'], true);

            $fileId = $auto['fileId'];
            $this->file = $this->exportDir . $fileId . '.' . $this->fileType;

            $this->offset = $auto['offset'];
            $this->limit = $auto['limit'];

            $this->packs = $this->getPacks();

            if (!$this->packs && Configuration::getGlobalValue('IPE_SCHDL_DNSEM')) {
                return;
            }
        } else {
            $this->fileType = Tools::getValue('as');
            $docName = Tools::getValue('doc_name') ?: $this->module->l('Packs', 'EIPacksExport');

            $this->selectedColumns = json_decode(Tools::getValue('selectedColumns'), true);

            if ($this->fileType === 'csv') {
                $this->dlm = Tools::getValue('csv_separator');
//                $this->encl = Tools::getValue('csv_enclosure');

                if ($this->dlm === 't') {
                    $this->dlm = "\t";
                }

//                if ($this->encl === 'none') {
//                    $this->encl = '';
//                } elseif ($this->encl === 'quot') {
//                    $this->encl = '"';
//                }
            }

            $fileId = Tools::getValue('fileId') ?: mt_rand() . uniqid();

            $this->file = $this->exportDir . $fileId . '.' . $this->fileType;

            $this->offset = (int) Tools::getValue('offset');
            $this->limit = (int) Tools::getValue('limit');
            $this->moreStep = (int) Tools::getValue('moreStep');

            $this->packs = $this->getPacks();
        }

        $results = ['fileId' => $fileId];
        $this->exportByGroups($results);

        // Retrieve errors/warnings if any
        if (isset($this->errors) && count($this->errors) > 0) {
            $results['errors'] = $this->errors;
        }
        if (isset($this->warnings) && count($this->warnings) > 0) {
            $results['warnings'] = $this->warnings;
        }
        if (isset($this->informations) && count($this->informations) > 0) {
            $results['informations'] = $this->informations;
        }

        if ($results['isFinished']) {
            $results['type'] = $this->fileType;
            $results['name'] = $docName;
        }
        die(json_encode($results));
    }

    public function exportByGroups(&$results)
    {
        $doneCount = 0;

        if ($this->fileType === 'csv') {
            $this->writeToCsv();
        } elseif ($this->fileType === 'xlsx') {
            $this->writeToExcel();
        }
        $doneCount += $this->packs ? count($this->packs) : 0;

        $results['isFinished'] = $doneCount < $this->limit;

        $results['doneCount'] = $this->offset + $doneCount;
        if ($this->offset === 0) {
            $total = $this->getPacks(true);
            $results['totalCount'] = $total ? count($total) : 0;
        }
        if (!$results['isFinished']) {
            // Since we'll have to POST this array from ajax for the next call, we should care about it size.
            $results['nextPostSize'] = 1024 * 64; // 64KB more for the rest of the POST query.
            $results['postSizeLimit'] = Tools::getMaxUploadSize();
        }
    }

    protected function openCsvFile()
    {
        $handle = false;
        if (file_exists($this->file)) {
            $handle = fopen($this->file, 'a');
        } else {
            $handle = fopen($this->file, 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // NEW LINE
            if ($handle) {
                if ($this->packs) {
                    fputcsv($handle, array_keys($this->packs[0]), $this->dlm, $this->encl);
                } else {
                    fputcsv($handle, [$this->module->l('No Data', 'EIPacksExport')], $this->dlm, $this->encl);
                }
            }
        }

        if (!$handle) {
            $this->errors[] = Tools::displayError('Cannot read the file');
        }

        return $handle;
    }

    protected function closeCsvFile($handle)
    {
        fclose($handle);
    }

    protected function writeToCsv()
    {
        $handle = $this->openCsvFile();
        foreach ($this->packs as $value) {
            fputcsv($handle, $value, $this->dlm, $this->encl);
        }
        $this->closeCsvFile($handle);
    }

    private function writeToExcel()
    {
        require_once dirname(__FILE__) . '/../../vendor/autoload.php';

        if (file_exists($this->file)) {
            $spreadsheet = IOFactory::load($this->file);
            $sheet = $spreadsheet->getActiveSheet();
            $excelColumns = EIHelper::createColumnsArray(count($this->packs[0]));
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()->setCreator('Tehran Alishov')
                ->setLastModifiedBy('Tehran Alishov')
                ->setTitle('Office 2007 XLSX Packs Document')
                ->setSubject('Office 2007 XLSX Packs Document')
                ->setDescription('Packs document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('Packs result file');
            $spreadsheet->setActiveSheetIndex(0);

            if (empty($this->packs)) {
                $sheet->setCellValue('A1', $this->module->l('No Data', 'EIPacksExport'));
            } else {
                $excelColumns = EIHelper::createColumnsArray(count($this->packs[0]));
                $sheet->getDefaultColumnDimension()->setWidth(21);
                $sheet->getDefaultRowDimension()->setRowHeight(30);

                $spreadsheet->getDefaultStyle()->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A1:' . end($excelColumns) . (count($this->packs)))
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFont()->setBold(true);
                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCF0FF');
                $sheet->getStyle('A1:' . end($excelColumns) . '1')->getBorders()
                    ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Rename worksheet
                $sheet->setTitle($this->module->l('Packs', 'EIPacksExport'));

                $headers = array_keys($this->packs[0]);
                foreach ($headers as $key => $header) {
                    $sheet->setCellValue($excelColumns[$key] . '1', $header);
                }

                if (isset($this->selectedColumns['image_urls'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['image_urls'], $headers)])->setWidth(40);
                }
            }
        }

        foreach ($this->packs as $key => $value) {
            $i = 0;
            foreach ($value as $val) {
                $sheet->setCellValue($excelColumns[$i] . ($this->offset + $key + 2), $val);
                $i++;
            }
        }

        $sheet->setSelectedCell('A1');

        // Write to file
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($this->file);
    }
}
