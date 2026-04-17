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

class EIAddressesExport
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

    private function getAddresses($getTotal = false)
    {
        if ($this->auto) {
            $availability = $this->inputs['availability'];

            $addresses = pSQL(implode(',', $this->datatables['addresses']['data']));
            $addressesType = $this->datatables['addresses']['type'];

            $this->langId = $langId = (int) $this->inputs['language'];
            $addressesDate = $this->inputs['date'];
            $fromDate = pSQL($this->inputs['from_date']);
            $toDate = pSQL($this->inputs['to_date']);
            $this->sortWay = (int) $this->inputs['sort_way'] === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL($this->inputs['sort']);
        } else {
            $availability = Tools::getValue('availability');

            $addresses = pSQL(Tools::getValue('addresses_data'));
            $addressesType = Tools::getValue('addresses_type');

            $this->langId = $langId = (int) Tools::getValue('language');
            $addressesDate = Tools::getValue('date');
            $fromDate = pSQL(Tools::getValue('from_date'));
            $toDate = pSQL(Tools::getValue('to_date'));
            $this->sortWay = (int) Tools::getValue('sort_way') === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL(Tools::getValue('sort'));
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
            ' . _DB_PREFIX_ . 'address address
                LEFT JOIN
            ' . _DB_PREFIX_ . 'country_lang country_lang ON address.id_country = country_lang.id_country
                    AND country_lang.id_lang = ' . $langId . '
                LEFT JOIN
            ' . _DB_PREFIX_ . 'state state ON address.id_state = state.id_state
                LEFT JOIN
            ' . _DB_PREFIX_ . 'customer customer ON address.id_customer = customer.id_customer
                LEFT JOIN
            ' . _DB_PREFIX_ . 'manufacturer manufacturer ON address.id_manufacturer = manufacturer.id_manufacturer
                LEFT JOIN
            ' . _DB_PREFIX_ . 'supplier supplier ON address.id_supplier = supplier.id_supplier
                ';

        $this->sql .= '
                WHERE address.deleted = 0 AND address.id_country <> 0
            ';

        // Filter By Date
        $date = 'date_add';
        if ($addressesDate === 'this_month') {
            $this->sql .= " 
                AND address.$date >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            $this->sql .= " 
                AND address.$date < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
        } elseif ($addressesDate === 'last_month') {
            $this->sql .= " 
                AND address.$date >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
            $this->sql .= " 
                AND address.$date < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
        } elseif ($addressesDate === 'this_week') {
            $start = date('N') == 1 ? date('Y-m-d') : date('Y-m-d', strtotime('last monday'));
            $end = date('Y-m-d', strtotime('this sunday'));
            $this->sql .= " 
                AND address.$date >= '" . $start. "'";
            $this->sql .= " 
                AND address.$date <= '" . $end . "'";
        } elseif ($addressesDate === 'last_week') {
            $start = date('N') == 1 ? date('Y-m-d', strtotime('last monday')) : date('Y-m-d', strtotime('last week monday'));
            $end = date('Y-m-d', strtotime('last sunday'));
            $this->sql .= " 
                AND address.$date >= '" . $start . "'";
            $this->sql .= " 
                AND address.$date <= '" . $end . "'";
        } elseif ($addressesDate === 'today') {
            $this->sql .= " 
                AND address.$date >= '" . date("Y-m-d", strtotime("today")) . "'";
            $this->sql .= " 
                AND address.$date < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
        } elseif ($addressesDate === 'last_24_hours') {
            $this->sql .= " 
                AND address.$date >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
            $this->sql .= " 
                AND address.$date < '" . date('Y-m-d H:i:s') . "'";
        } elseif ($addressesDate === 'yesterday') {
            $this->sql .= " 
                AND address.$date >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
            $this->sql .= " 
                AND address.$date < '" . date("Y-m-d", strtotime("today")) . "'";
        } elseif ($addressesDate === 'select_date') {
            if ($fromDate) {
                $this->sql .= " 
                    AND address.$date >= '" . $fromDate . "'";
            }
            if ($toDate) {
                $this->sql .= " 
                    AND address.$date < '" . $toDate . "'";
            }
        }

        // Filter By Availability
        $availabilityCond = '';
        if ($availability === 'active') {
            $availabilityCond = '
                AND address.active = 1 
                ';
        } elseif ($availability === 'inactive') {
            $availabilityCond = '
                AND address.active = 0 
                ';
        }
        $this->sql .= $availabilityCond;

        // Filter By Address
        $addressCond = '';
        if ($addresses) {
            if ($addressesType === 'unselected') {
                $addressCond = 'address.id_address NOT IN (' . $addresses . ')';
            } else {
                $addressCond = 'address.id_address IN (' . $addresses . ')';
            }
        }
        if ($addressCond) {
            $this->sql .= ' 
                        AND (' . $addressCond . ') ';
        }


        // Sort By ...
        $this->sql .= ' ORDER BY ' . $this->sort . $this->sortWay;
        if ($this->sort !== 'address.id_address') {
            $this->sql .= ', address.id_address ASC ';
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
            $docName = $this->inputs['doc_name'] ?: $this->module->l('Addresses', 'EIAddressesExport');

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

            $this->addresses = $this->getAddresses();

            if (!$this->addresses && Configuration::getGlobalValue('IPE_SCHDL_DNSEM')) {
                return;
            }
        } else {
            $this->fileType = Tools::getValue('as');
            $docName = Tools::getValue('doc_name') ?: $this->module->l('Addresses', 'EIAddressesExport');

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

            $this->addresses = $this->getAddresses();
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
        $doneCount += $this->addresses ? count($this->addresses) : 0;

        $results['isFinished'] = $doneCount < $this->limit;

        $results['doneCount'] = $this->offset + $doneCount;
        if ($this->offset === 0) {
            $total = $this->getAddresses(true);
            $results['totalCount'] = $total ? count($total) : 0;
        }
        if (!$results['isFinished']) {
            // Since we'll have to POST this array from ajax for the next call, we should care about it size.
            $results['nextPostSize'] = 1024 * 64; // 64KB more for the rest of the POST query.
            $results['postSizeLimit'] = Tools::getMaxUploadSize();
        }
    }

    protected function writeToCsv()
    {
        $handle = $this->openCsvFile();
        foreach ($this->addresses as $value) {
            fputcsv($handle, $value, $this->dlm, $this->encl);
        }
        $this->closeCsvFile($handle);
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
                if ($this->addresses) {
                    fputcsv($handle, array_keys($this->addresses[0]), $this->dlm, $this->encl);
                } else {
                    fputcsv($handle, [$this->module->l('No Data', 'EIAddressesExport')], $this->dlm, $this->encl);
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

    protected function writeToExcel()
    {
        require_once dirname(__FILE__) . '/../../vendor/autoload.php';

        if (file_exists($this->file)) {
            $spreadsheet = IOFactory::load($this->file);
            $sheet = $spreadsheet->getActiveSheet();
            $excelColumns = EIHelper::createColumnsArray(count($this->addresses[0]));
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()->setCreator('Tehran Alishov')
                ->setLastModifiedBy('Tehran Alishov')
                ->setTitle('Office 2007 XLSX Addresses Document')
                ->setSubject('Office 2007 XLSX Addresses Document')
                ->setDescription('Addresses document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('Addresses result file');
            $spreadsheet->setActiveSheetIndex(0);

            if ($this->addresses) {
                $excelColumns = EIHelper::createColumnsArray(count($this->addresses[0]));
                $sheet->getDefaultColumnDimension()->setWidth(21);
                $sheet->getDefaultRowDimension()->setRowHeight(30);

                $spreadsheet->getDefaultStyle()->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A1:' . end($excelColumns) . (count($this->addresses)))
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFont()->setBold(true);
                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCF0FF');
                $sheet->getStyle('A1:' . end($excelColumns) . '1')->getBorders()
                    ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Rename worksheet
                $sheet->setTitle($this->module->l('Addresses', 'EIAddressesExport'));

                foreach (array_keys($this->addresses[0]) as $key => $header) {
                    $sheet->setCellValue($excelColumns[$key] . '1', $header);
                }
            } else {
                $sheet->setCellValue('A1', $this->module->l('No Data', 'EIAddressesExport'));
            }
        }

        foreach ($this->addresses as $key => $value) {
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
