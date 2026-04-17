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

class EISuppliersExport
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

    private function getImageType($type)
    {
        if (method_exists('ImageType', 'getFormattedName')) {
            $iType = ImageType::getFormattedName($type);
        } else {
            $iType = ImageType::getFormatedName($type);
        }
        return $iType;
    }

    private function getImageLink($id_image)
    {
        $sup_img_path = realpath(_PS_SUPP_IMG_DIR_ . $id_image . '.jpg');
        if (file_exists($sup_img_path)) {
            return $this->context->link->getBaseLink() . 'img/su/' . $id_image . '.jpg';
        } else {
            return '';
        }
    }

    private function getSuppliers($getTotal = false)
    {
        // Columns that do not exist directly in the DB
        $absentColumns = array(
            'supplier_link' => 'supplier.id_supplier',
            'supplier_image' => 'supplier.id_supplier',
            'image_url' => 'supplier.id_supplier'
        );

        if ($this->fileType === 'csv' && isset($this->selectedColumns['supplier_image'])) {
            unset($this->selectedColumns['supplier_image']);
        }

        if ($this->auto) {
            $availability = $this->inputs['availability'];

            $suppliers = pSQL(implode(',', $this->datatables['suppliers2']['data']));
            $suppliersType = $this->datatables['suppliers2']['type'];

            $this->langId = $langId = (int) $this->inputs['language'];
            $shopId = (int) $this->inputs['shop'] ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $suppliersDate = $this->inputs['date'];
            $fromDate = pSQL($this->inputs['from_date']);
            $toDate = pSQL($this->inputs['to_date']);
            $this->sortWay = (int) $this->inputs['sort_way'] === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL($this->inputs['sort']);
        } else {
            $availability = Tools::getValue('availability');

            $suppliers = pSQL(Tools::getValue('suppliers2_data'));
            $suppliersType = Tools::getValue('suppliers2_type');

            $this->langId = $langId = (int) Tools::getValue('language');
            $shopId = (int) Tools::getValue('shop') ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $suppliersDate = Tools::getValue('date');
            $fromDate = pSQL(Tools::getValue('from_date'));
            $toDate = pSQL(Tools::getValue('to_date'));
            $this->sortWay = (int) Tools::getValue('sort_way') === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL(Tools::getValue('sort'));
        }

        $this->sql = '
            SELECT ';
        foreach ($this->selectedColumns as $k => $col) {
            if (array_key_exists($k, $absentColumns)) {
                $this->sql .= "
                    {$absentColumns[$k]} `$col`, ";
            } else {
                $this->sql .= "
                        $k `$col`, ";
            }
        }

        $this->sql = rtrim($this->sql, ', ');

        $this->sql .= '
            FROM 
            ' . _DB_PREFIX_ . 'supplier supplier
                LEFT JOIN
            ' . _DB_PREFIX_ . 'supplier_shop supplier_shop ON supplier.id_supplier = supplier_shop.id_supplier
                    AND supplier_shop.id_shop = ' . $shopId . '
                LEFT JOIN
            ' . _DB_PREFIX_ . 'supplier_lang supplier_lang ON supplier.id_supplier = supplier_lang.id_supplier
                    AND supplier_lang.id_lang = ' . $langId;

        $this->sql .= '
                WHERE 1
            ';

        // Filter By Date
        $date = 'date_add';
        if ($suppliersDate === 'this_month') {
            $this->sql .= " 
                AND supplier.$date >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            $this->sql .= " 
                AND supplier.$date < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
        } elseif ($suppliersDate === 'last_month') {
            $this->sql .= " 
                AND supplier.$date >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
            $this->sql .= " 
                AND supplier.$date < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
        } elseif ($suppliersDate === 'this_week') {
            $start = date('N') == 1 ? date('Y-m-d') : date('Y-m-d', strtotime('last monday'));
            $end = date('Y-m-d', strtotime('this sunday'));
            $this->sql .= " 
                AND supplier.$date >= '" . $start . "'";
            $this->sql .= " 
                AND supplier.$date <= '" . $end . "'";
        } elseif ($suppliersDate === 'last_week') {
            $start = date('N') == 1 ? date('Y-m-d', strtotime('last monday')) : date('Y-m-d', strtotime('last week monday'));
            $end = date('Y-m-d', strtotime('last sunday'));
            $this->sql .= " 
                AND supplier.$date >= '" . $start . "'";
            $this->sql .= " 
                AND supplier.$date <= '" . $end . "'";
        } elseif ($suppliersDate === 'today') {
            $this->sql .= " 
                AND supplier.$date >= '" . date("Y-m-d", strtotime("today")) . "'";
            $this->sql .= " 
                AND supplier.$date < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
        } elseif ($suppliersDate === 'last_24_hours') {
            $this->sql .= " 
                AND supplier.$date >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
            $this->sql .= " 
                AND supplier.$date < '" . date('Y-m-d H:i:s') . "'";
        } elseif ($suppliersDate === 'yesterday') {
            $this->sql .= " 
                AND supplier.$date >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
            $this->sql .= " 
                AND supplier.$date < '" . date("Y-m-d", strtotime("today")) . "'";
        } elseif ($suppliersDate === 'select_date') {
            if ($fromDate) {
                $this->sql .= " 
                    AND supplier.$date >= '" . $fromDate . "'";
            }
            if ($toDate) {
                $this->sql .= " 
                    AND supplier.$date < '" . $toDate . "'";
            }
        }

        // Filter By Availability
        $availabilityCond = '';
        if ($availability === 'active') {
            $availabilityCond = '
                AND supplier.active = 1 
                ';
        } elseif ($availability === 'inactive') {
            $availabilityCond = '
                AND supplier.active = 0 
                ';
        }
        $this->sql .= $availabilityCond;

        // Filter By Supplier
        $supplierCond = '';
        if ($suppliers) {
            if ($suppliersType === 'unselected') {
                $supplierCond = 'supplier.id_supplier NOT IN (' . $suppliers . ')';
            } else {
                $supplierCond = 'supplier.id_supplier IN (' . $suppliers . ')';
            }
        }
        if ($supplierCond) {
            $this->sql .= ' 
                        AND (' . $supplierCond . ') ';
        }


        // Sort By ...
        $this->sql .= ' ORDER BY ' . $this->sort . $this->sortWay;
        if ($this->sort !== 'supplier.id_supplier') {
            $this->sql .= ', supplier.id_supplier ASC';
        }

        if (!$getTotal) {
            $this->sql .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
        }

//        d($this->sql);
        return Db::getInstance()->executeS($this->sql);
    }

    public function run($auto = null)
    {
        $this->auto = $auto;
        $this->encl = '"';

        if ($auto) {
            $this->inputs = $auto['config']['inputs'];

            $this->fileType = $this->inputs['as'];
            $docName = $this->inputs['doc_name'] ?: $this->module->l('Suppliers', 'EISuppliersExport');

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

            $this->suppliers = $this->getSuppliers();

            if (!$this->suppliers && Configuration::getGlobalValue('IPE_SCHDL_DNSEM')) {
                return;
            }
        } else {
            $this->fileType = Tools::getValue('as');
            $docName = Tools::getValue('doc_name') ?: $this->module->l('Suppliers', 'EISuppliersExport');

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

            $this->suppliers = $this->getSuppliers();
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
        $doneCount += $this->suppliers ? count($this->suppliers) : 0;

        $results['isFinished'] = $doneCount < $this->limit;

        $results['doneCount'] = $this->offset + $doneCount;
        if ($this->offset === 0) {
            $total = $this->getSuppliers(true);
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
                if ($this->suppliers) {
                    fputcsv($handle, array_keys($this->suppliers[0]), $this->dlm, $this->encl);
                } else {
                    fputcsv($handle, [$this->module->l('No Data', 'EISuppliersExport')], $this->dlm, $this->encl);
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
        foreach ($this->suppliers as $value) {
            if (!empty($value[$this->selectedColumns['supplier_link']])) {
                $value[$this->selectedColumns['supplier_link']] = $this->context->link->getSupplierLink($value[$this->selectedColumns['supplier_link']], null, $this->langId);
            }
            if (!empty($value[$this->selectedColumns['image_url']])) {
                $value[$this->selectedColumns['image_url']] = $this->getImageLink($value[$this->selectedColumns['image_url']]);
            }
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
            $excelColumns = EIHelper::createColumnsArray(count($this->suppliers[0]));
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()->setCreator('Tehran Alishov')
                ->setLastModifiedBy('Tehran Alishov')
                ->setTitle('Office 2007 XLSX Suppliers Document')
                ->setSubject('Office 2007 XLSX Suppliers Document')
                ->setDescription('Suppliers document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('Suppliers result file');
            $spreadsheet->setActiveSheetIndex(0);

            if (empty($this->suppliers)) {
                $sheet->setCellValue('A1', $this->module->l('No Data', 'EISuppliersExport'));
            } else {
                $excelColumns = EIHelper::createColumnsArray(count($this->suppliers[0]));
                $sheet->getDefaultColumnDimension()->setWidth(21);
                if (isset($this->selectedColumns['supplier_image'])) {
                    $sheet->getDefaultRowDimension()->setRowHeight(42);
                } else {
                    $sheet->getDefaultRowDimension()->setRowHeight(30);
                }

                $spreadsheet->getDefaultStyle()->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A1:' . end($excelColumns) . (count($this->suppliers)))
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFont()->setBold(true);
                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCF0FF');
                $sheet->getStyle('A1:' . end($excelColumns) . '1')->getBorders()
                    ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Rename worksheet
                $sheet->setTitle($this->module->l('Suppliers', 'EISuppliersExport'));

                $headers = array_keys($this->suppliers[0]);
                foreach ($headers as $key => $header) {
                    $sheet->setCellValue($excelColumns[$key] . '1', $header);
                }

                if (isset($this->selectedColumns['supplier_link'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['supplier_link'], $headers)])->setWidth(30);
                }
                if (isset($this->selectedColumns['image_url'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['image_url'], $headers)])->setWidth(40);
                }
            }
        }

        $small_image = $this->getImageType('small');
        $font = $sheet->getStyle('A1')->getFont();
        foreach ($this->suppliers as $key => $value) {
            $i = 0;
            foreach ($value as $k => $val) {
                if ($k === $this->selectedColumns['supplier_link']) {
                    $link = $this->context->link->getSupplierLink($val, null, $this->langId);
                    $cell = $excelColumns[$i] . ($this->offset + $key + 2);
                    $sheet->setCellValue($cell, $link);
                    if ($link) {
                        $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                    }
                } elseif ($k === $this->selectedColumns['supplier_image'] && $val) {
                    $image_path = realpath(_PS_SUPP_IMG_DIR_ . $val . '-' . $small_image . '.jpg');
                    if (file_exists($image_path)) {
                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setPath(realpath($image_path));
                        $height = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pointsToPixels(42, $font);
                        $drawing->setHeight($height);
                        $width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::pixelsToCellDimension($drawing->getWidth(), $font);
                        $sheet->getColumnDimension($excelColumns[$i])->setWidth($width);
                        $drawing->setCoordinates($excelColumns[$i] . ($this->offset + $key + 2));
                        $drawing->setWorksheet($sheet);
                        $drawing->getShadow()->setVisible(true);
                    }
                } elseif ($k === $this->selectedColumns['image_url'] && $val) {
                    $link = $this->getImageLink($val);
                    $cell = $excelColumns[$i] . ($this->offset + $key + 2);
                    $sheet->setCellValue($cell, $link);
                    if ($link) {
                        $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                    }
                } else {
                    $sheet->setCellValue($excelColumns[$i] . ($this->offset + $key + 2), $val);
                }
                $i++;
            }
        }

        $sheet->setSelectedCell('A1');

        // Write to file
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($this->file);
    }
}
