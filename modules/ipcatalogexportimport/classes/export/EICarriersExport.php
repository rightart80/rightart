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

class EICarriersExport
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

    private function getImageLink($id_image)
    {
        $sup_img_path = realpath(_PS_SHIP_IMG_DIR_ . $id_image . '.jpg');
        if (file_exists($sup_img_path)) {
            return $this->context->link->getBaseLink() . 'img/s/' . $id_image . '.jpg';
        } else {
            return '';
        }
    }

    private function getCarriers($getTotal = false)
    {
        // Columns that do not exist directly in the DB
        $absentColumns = array(
            'carrier_logo' => 'carrier.id_carrier',
            'logo_url' => 'carrier.id_carrier'
        );

        if ($this->fileType === 'csv' && isset($this->selectedColumns['carrier_logo'])) {
            unset($this->selectedColumns['carrier_logo']);
        }

        if ($this->auto) {
            $availability = $this->inputs['availability'];

            $carriers = pSQL(implode(',', $this->datatables['carriers2']['data']));
            $carriers_type = $this->datatables['carriers2']['type'];

            $this->langId = $langId = (int) $this->inputs['language'];
            $shopId = (int) $this->inputs['shop'] ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $this->sortWay = (int) $this->inputs['sort_way'] === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL($this->inputs['sort']);
            $this->multivalueSeparator = pSQL($this->inputs['multivalue_separator']) ?: ',';
        } else {
            $availability = Tools::getValue('availability');

            $carriers = pSQL(Tools::getValue('carriers2_data'));
            $carriers_type = Tools::getValue('carriers2_type');

            $this->langId = $langId = (int) Tools::getValue('language');
            $shopId = (int) Tools::getValue('shop') ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $this->sortWay = (int) Tools::getValue('sort_way') === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL(Tools::getValue('sort'));
            $this->multivalueSeparator = pSQL(Tools::getValue('multivalue_separator')) ?: ',';
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

//        $feature_active = ShopCore::isFeatureActive();

        $this->sql = rtrim($this->sql, ', ');

        $this->sql .= '
            FROM 
            ' . _DB_PREFIX_ . 'carrier carrier
                LEFT JOIN
            ' . _DB_PREFIX_ . 'carrier_lang carrier_lang ON carrier.id_carrier = carrier_lang.id_carrier
                    AND carrier_lang.id_shop = ' . $shopId . ' AND carrier_lang.id_lang = ' . $langId . (version_compare(_PS_VERSION_, 8, '<') ? '
                LEFT JOIN
            ' . _DB_PREFIX_ . 'tax_rules_group trg ON carrier.id_tax_rules_group = trg.id_tax_rules_group' : '') . '
                LEFT JOIN (
                    SELECT cg.id_carrier, GROUP_CONCAT(cg.id_group SEPARATOR "' . $this->multivalueSeparator . '") ids, GROUP_CONCAT(gl.name SEPARATOR "' . $this->multivalueSeparator . '") names
                    FROM ' . _DB_PREFIX_ . 'carrier_group cg
                    JOIN ' . _DB_PREFIX_ . 'group_lang gl ON cg.id_group = gl.id_group AND gl.id_lang = ' . $langId . '
                    GROUP BY cg.id_carrier
                ) carrier_group ON carrier.id_carrier = carrier_group.id_carrier
                LEFT JOIN (
                    SELECT cz.id_carrier, GROUP_CONCAT(cz.id_zone SEPARATOR "' . $this->multivalueSeparator . '") ids, GROUP_CONCAT(CONCAT(zone.name, ":", zone.active) SEPARATOR "' . $this->multivalueSeparator . '") names
                    FROM ' . _DB_PREFIX_ . 'carrier_zone cz
                    LEFT JOIN ' . _DB_PREFIX_ . 'zone zone ON cz.id_zone = zone.id_zone
                    GROUP BY cz.id_carrier
                ) carrier_zone ON carrier.id_carrier = carrier_zone.id_carrier
                LEFT JOIN (
                    SELECT 
                        rp.id_carrier,
                        GROUP_CONCAT(CONCAT(TRIM(rp.delimiter1) + 0, " - ", TRIM(rp.delimiter2) + 0) ORDER BY rp.id_range_price SEPARATOR "' . $this->multivalueSeparator . '") ranges,
                        GROUP_CONCAT(price.price ORDER BY rp.id_range_price SEPARATOR "' . $this->multivalueSeparator . '") price
                    FROM ' . _DB_PREFIX_ . 'range_price rp
                    LEFT JOIN (
                        SELECT
                            d.id_range_price,
                            d.id_carrier,
                            CONCAT_WS(" > ", CONCAT(TRIM(rp.delimiter1) + 0, " - ", TRIM(rp.delimiter2) + 0), GROUP_CONCAT(CONCAT_WS(":", z.name, TRIM(d.price) + 0) SEPARATOR "!")) price
                        FROM ' . _DB_PREFIX_ . 'delivery d
                        LEFT JOIN ' . _DB_PREFIX_ . 'zone z ON d.id_zone = z.id_zone
                        LEFT JOIN ' . _DB_PREFIX_ . 'range_price rp ON d.id_range_price = rp.id_range_price
                        WHERE d.id_range_price > 0 AND d.id_shop IS NULL
                            AND d.id_shop_group IS NULL
                        GROUP BY d.id_range_price, d.id_carrier
                    ) price ON rp.id_range_price = price.id_range_price AND rp.id_carrier = price.id_carrier
                    GROUP BY rp.id_carrier
                ) range_price ON carrier.id_carrier = range_price.id_carrier
                LEFT JOIN (
                    SELECT
                        rw.id_carrier,
                        GROUP_CONCAT(CONCAT(TRIM(rw.delimiter1) + 0, " - ", TRIM(rw.delimiter2) + 0) ORDER BY rw.id_range_weight SEPARATOR "' . $this->multivalueSeparator . '") ranges,
                        GROUP_CONCAT(price.price ORDER BY rw.id_range_weight SEPARATOR "' . $this->multivalueSeparator . '") price
                    FROM ' . _DB_PREFIX_ . 'range_weight rw
                    LEFT JOIN (
                        SELECT
                            d.id_range_weight,
                            d.id_carrier,
                            CONCAT_WS(" > ", CONCAT(TRIM(rw.delimiter1) + 0, " - ", TRIM(rw.delimiter2) + 0), GROUP_CONCAT(CONCAT_WS(":", z.name, TRIM(d.price) + 0) SEPARATOR "!")) price
                        FROM ' . _DB_PREFIX_ . 'delivery d
                        LEFT JOIN ' . _DB_PREFIX_ . 'zone z ON d.id_zone = z.id_zone
                        LEFT JOIN ' . _DB_PREFIX_ . 'range_weight rw ON d.id_range_weight = rw.id_range_weight
                        WHERE d.id_range_weight > 0 AND d.id_shop IS NULL
                            AND d.id_shop_group IS NULL
                        GROUP BY d.id_range_weight, d.id_carrier
                    ) price ON rw.id_range_weight = price.id_range_weight AND rw.id_carrier = price.id_carrier
                    GROUP BY rw.id_carrier
                ) range_weight ON carrier.id_carrier = range_weight.id_carrier
';

        $this->sql .= '
                WHERE carrier.`deleted` = 0
            ';

        // Filter By Availability
        $availabilityCond = '';
        if ($availability === 'active') {
            $availabilityCond = '
                AND carrier.active = 1 
                ';
        } elseif ($availability === 'inactive') {
            $availabilityCond = '
                AND carrier.active = 0 
                ';
        }
        $this->sql .= $availabilityCond;

        // Filter By Carrier
        $carrierCond = '';
        if ($carriers) {
            if ($carriers_type === 'unselected') {
                $carrierCond = 'carrier.id_carrier NOT IN (' . $carriers . ')';
            } else {
                $carrierCond = 'carrier.id_carrier IN (' . $carriers . ')';
            }
        }
        if ($carrierCond) {
            $this->sql .= ' 
                        AND (' . $carrierCond . ') ';
        }

        // Sort By ...
        $this->sql .= ' ORDER BY ' . $this->sort . $this->sortWay;
        if ($this->sort !== 'carrier.id_carrier') {
            $this->sql .= ', carrier.id_carrier ASC';
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
            $docName = $this->inputs['doc_name'] ?: $this->module->l('Carriers', 'EICarriersExport');

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

            $this->carriers = $this->getCarriers();

            if (!$this->carriers && Configuration::getGlobalValue('IPE_SCHDL_DNSEM')) {
                return;
            }
        } else {
            $this->fileType = Tools::getValue('as');
            $docName = Tools::getValue('doc_name') ?: $this->module->l('Carriers', 'EICarriersExport');

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

            $this->carriers = $this->getCarriers();
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
        $doneCount += $this->carriers ? count($this->carriers) : 0;

        $results['isFinished'] = $doneCount < $this->limit;

        $results['doneCount'] = $this->offset + $doneCount;
        if ($this->offset === 0) {
            $total = $this->getCarriers(true);
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
                if ($this->carriers) {
                    fputcsv($handle, array_keys($this->carriers[0]), $this->dlm, $this->encl);
                } else {
                    fputcsv($handle, [$this->module->l('No Data', 'EICarriersExport')], $this->dlm, $this->encl);
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
        foreach ($this->carriers as $value) {
            if (!empty($value[$this->selectedColumns['logo_url']])) {
                $value[$this->selectedColumns['logo_url']] = $this->getImageLink($value[$this->selectedColumns['logo_url']]);
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
            $excelColumns = EIHelper::createColumnsArray(count($this->carriers[0]));
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()->setCreator('Tehran Alishov')
                    ->setLastModifiedBy('Tehran Alishov')
                    ->setTitle('Office 2007 XLSX Carriers Document')
                    ->setSubject('Office 2007 XLSX Carriers Document')
                    ->setDescription('Carriers document for Office 2007 XLSX, generated using PHP classes.')
                    ->setKeywords('office 2007 openxml php')
                    ->setCategory('Carriers result file');
            $spreadsheet->setActiveSheetIndex(0);

            if (empty($this->carriers)) {
                $sheet->setCellValue('A1', $this->module->l('No Data', 'EICarriersExport'));
            } else {
                $excelColumns = EIHelper::createColumnsArray(count($this->carriers[0]));
                $sheet->getDefaultColumnDimension()->setWidth(21);
                if (isset($this->selectedColumns['carrier_logo'])) {
                    $sheet->getDefaultRowDimension()->setRowHeight(42);
                } else {
                    $sheet->getDefaultRowDimension()->setRowHeight(30);
                }

                $spreadsheet->getDefaultStyle()->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A1:' . end($excelColumns) . (count($this->carriers)))
                        ->getAlignment()->setWrapText(true);

                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                        ->getFont()->setBold(true);
                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                        ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFDCF0FF');
                $sheet->getStyle('A1:' . end($excelColumns) . '1')->getBorders()
                        ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Rename worksheet
                $sheet->setTitle($this->module->l('Carriers', 'EICarriersExport'));

                $headers = array_keys($this->carriers[0]);
                foreach ($headers as $key => $header) {
                    $sheet->setCellValue($excelColumns[$key] . '1', $header);
                }

                if (isset($this->selectedColumns['logo_url'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['logo_url'], $headers)])->setWidth(40);
                }
            }
        }

        $font = $sheet->getStyle('A1')->getFont();
        foreach ($this->carriers as $key => $value) {
            $i = 0;
            foreach ($value as $k => $val) {
                if ($k === $this->selectedColumns['carrier_logo'] && $val) {
                    $image_path = realpath(_PS_SHIP_IMG_DIR_ . $val . '.jpg');
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
                } elseif ($k === $this->selectedColumns['logo_url'] && $val) {
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
