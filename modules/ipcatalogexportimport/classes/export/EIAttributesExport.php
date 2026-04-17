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

class EIAttributesExport
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

    private function getAttributes($getTotal = false)
    {
        if ($this->auto) {
            $this->langId = $langId = (int) $this->inputs['language'];
            $shopId = (int) $this->inputs['shop'] ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $this->sortWay = (int) $this->inputs['sort_way'] === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL($this->inputs['sort']);
            $this->multivalueSeparator = pSQL($this->inputs['multivalue_separator']) ?: ',';

            $attributeGroups = pSQL(implode(',', $this->datatables['attributeGroups']['data']));
            $attributeGroupsType = $this->datatables['attributeGroups']['type'];
            
            $attributeValues = pSQL(implode(',', $this->datatables['attributeValues']['data']));
            $attributeValuesType = $this->datatables['attributeValues']['type'];
        } else {
            $this->langId = $langId = (int) Tools::getValue('language');
            $shopId = (int) Tools::getValue('shop') ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $this->sortWay = (int) Tools::getValue('sort_way') === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL(Tools::getValue('sort'));
            $this->multivalueSeparator = pSQL(Tools::getValue('multivalue_separator')) ?: ',';

            $attributeGroups = pSQL(Tools::getValue('attributeGroups_data'));
            $attributeGroupsType = Tools::getValue('attributeGroups_type');
            
            $attributeValues = pSQL(Tools::getValue('attributeValues_data'));
            $attributeValuesType = Tools::getValue('attributeValues_type');
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
            ' . _DB_PREFIX_ . 'attribute_group attribute_group
                LEFT JOIN
            ' . _DB_PREFIX_ . 'attribute_group_shop attribute_group_shop ON attribute_group.id_attribute_group = attribute_group_shop.id_attribute_group
                    AND attribute_group_shop.id_shop = ' . $shopId . '
                LEFT JOIN
            ' . _DB_PREFIX_ . 'attribute_group_lang attribute_group_lang ON attribute_group.id_attribute_group = attribute_group_lang.id_attribute_group
                    AND attribute_group_lang.id_lang = ' . $langId . '
                LEFT JOIN
            ' . _DB_PREFIX_ . 'attribute attribute ON attribute_group.id_attribute_group = attribute.id_attribute_group
                LEFT JOIN
            ' . _DB_PREFIX_ . 'attribute_lang attribute_lang ON attribute.id_attribute = attribute_lang.id_attribute
                    AND attribute_lang.id_lang = ' . $langId . '
                ';

        $this->sql .= '
                WHERE 1
            ';

        // Filter By Attribute Group
        $attributeGroupCond = '';
        if ($attributeGroups) {
            if ($attributeGroupsType === 'unselected') {
                $attributeGroupCond = 'attribute_group.id_attribute_group NOT IN (' . $attributeGroups . ')';
            } else {
                $attributeGroupCond = 'attribute_group.id_attribute_group IN (' . $attributeGroups . ')';
            }
        }
        if ($attributeGroupCond) {
            $this->sql .= ' 
                        AND (' . $attributeGroupCond . ') ';
        }

        // Filter By Attribute
        $attributeCond = '';
        if ($attributeValues) {
            if ($attributeValuesType === 'unselected') {
                $attributeCond = 'attribute.id_attribute NOT IN (' . $attributeValues . ')';
            } else {
                $attributeCond = 'attribute.id_attribute IN (' . $attributeValues . ')';
            }
        }
        if ($attributeCond) {
            $this->sql .= ' 
                        AND (' . $attributeCond . ') ';
        }

        // Sort By ...
        $this->sql .= ' ORDER BY ' . $this->sort . $this->sortWay;
        if ($this->sort !== 'attribute_group.id_attribute_group') {
            $this->sql .= ', attribute_group.id_attribute_group ASC';
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
            $docName = $this->inputs['doc_name'] ?: $this->module->l('Attributes', 'EIAttributesExport');

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

            $this->attributes = $this->getAttributes();

            if (!$this->attributes && Configuration::getGlobalValue('IPE_SCHDL_DNSEM')) {
                return;
            }
        } else {
            $this->fileType = Tools::getValue('as');
            $docName = Tools::getValue('doc_name') ?: $this->module->l('Attributes', 'EIAttributesExport');

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

            $this->attributes = $this->getAttributes();
        }

        $results = ['fileId' => $fileId];
        $this->exportByAttributes($results);

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

    public function exportByAttributes(&$results)
    {
        $doneCount = 0;

        if ($this->fileType === 'csv') {
            $this->writeToCsv();
        } elseif ($this->fileType === 'xlsx') {
            $this->writeToExcel();
        }
        $doneCount += $this->attributes ? count($this->attributes) : 0;

        $results['isFinished'] = $doneCount < $this->limit;

        $results['doneCount'] = $this->offset + $doneCount;
        if ($this->offset === 0) {
            $total = $this->getAttributes(true);
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
                if ($this->attributes) {
                    fputcsv($handle, array_keys($this->attributes[0]), $this->dlm, $this->encl);
                } else {
                    fputcsv($handle, [$this->module->l('No Data', 'EIAttributesExport')], $this->dlm, $this->encl);
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
        foreach ($this->attributes as $value) {
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
            $excelColumns = EIHelper::createColumnsArray(count($this->attributes[0]));
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()->setCreator('Tehran Alishov')
                ->setLastModifiedBy('Tehran Alishov')
                ->setTitle('Office 2007 XLSX Attributes Document')
                ->setSubject('Office 2007 XLSX Attributes Document')
                ->setDescription('Attributes document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('Attributes result file');
            $spreadsheet->setActiveSheetIndex(0);

            if (empty($this->attributes)) {
                $sheet->setCellValue('A1', $this->module->l('No Data', 'EIAttributesExport'));
            } else {
                $excelColumns = EIHelper::createColumnsArray(count($this->attributes[0]));
                $sheet->getDefaultColumnDimension()->setWidth(21);
                $sheet->getDefaultRowDimension()->setRowHeight(30);

                $spreadsheet->getDefaultStyle()->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A1:' . end($excelColumns) . (count($this->attributes)))
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFont()->setBold(true);
                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCF0FF');
                $sheet->getStyle('A1:' . end($excelColumns) . '1')->getBorders()
                    ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Rename worksheet
                $sheet->setTitle($this->module->l('Attributes', 'EIAttributesExport'));

                $headers = array_keys($this->attributes[0]);
                foreach ($headers as $key => $header) {
                    $sheet->setCellValue($excelColumns[$key] . '1', $header);
                }
            }
        }

        foreach ($this->attributes as $key => $value) {
            $i = 0;
            foreach ($value as $val) {
                $sheet->setCellValue($excelColumns[$i++] . ($this->offset + $key + 2), $val);
            }
        }

        $sheet->setSelectedCell('A1');

        // Write to file
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($this->file);
    }
}
