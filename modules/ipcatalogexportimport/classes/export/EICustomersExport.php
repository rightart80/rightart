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

class EICustomersExport
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

    private function getCustomers($getTotal = false)
    {
        // Columns that do not exist directly in the DB
        $absentColumns = array(
            'new_passwd' => '""'
        );

        if ($this->auto) {
            $availability = $this->inputs['availability'];

            $customers = pSQL(implode(',', $this->datatables['customers']['data']));
            $customersType = $this->datatables['customers']['type'];

            $this->langId = $langId = (int) $this->inputs['language'];
            $customersDate = $this->inputs['date'];
            $fromDate = pSQL($this->inputs['from_date']);
            $toDate = pSQL($this->inputs['to_date']);
            $this->sortWay = (int) $this->inputs['sort_way'] === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL($this->inputs['sort']);
            $this->multivalueSeparator = pSQL($this->inputs['multivalue_separator']) ?: ',';

            $groups = pSQL(implode(',', $this->datatables['groups']['data']));
            $groups_type = $this->datatables['groups']['type'];
            $groups_without = $this->inputs['group_without'];
        } else {
            $availability = Tools::getValue('availability');

            $customers = pSQL(Tools::getValue('customers_data'));
            $customersType = Tools::getValue('customers_type');

            $this->langId = $langId = (int) Tools::getValue('language');
            $customersDate = Tools::getValue('date');
            $fromDate = pSQL(Tools::getValue('from_date'));
            $toDate = pSQL(Tools::getValue('to_date'));
            $this->sortWay = (int) Tools::getValue('sort_way') === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL(Tools::getValue('sort'));
            $this->multivalueSeparator = pSQL(Tools::getValue('multivalue_separator')) ?: ',';

            $groups = pSQL(Tools::getValue('groups_data'));
            $groups_type = Tools::getValue('groups_type');
            $groups_without = Tools::getValue('group_without');
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

        // Filter By Group
        $groupsCond = $groupsCond2 = '';
        if ($groups) {
            if ($groups_type === 'unselected') {
                $groupsCond = ' AND id_group NOT IN (' . $groups . ')';
            } else {
                $groupsCond = ' AND id_group IN (' . $groups . ')';
            }
            $groupsCond2 .= 'groupp.id_customer IS NOT NULL';
            if ($groups_without !== '0') {
                $groupsCond2 .= ' OR for_null_group.id_customer IS NULL';
            }
            $groupsCond2 = ' 
                AND (' . $groupsCond2 . ')';
        } else {
            if ($groups_without !== '0' && $groups_type === 'selected') {
                $groupsCond2 .= ' 
                    AND (for_null_group.id_customer IS NULL)';
            } elseif ($groups_without === '0' && $groups_type === 'unselected') {
                $groupsCond2 .= ' 
                    AND (for_null_group.id_customer IS NOT NULL)';
            }
        }

        $this->sql .= '
            FROM 
            ' . _DB_PREFIX_ . 'customer customer
                LEFT JOIN
            ' . _DB_PREFIX_ . 'gender gender ON customer.id_gender = gender.id_gender
                LEFT JOIN
            ' . _DB_PREFIX_ . 'group_lang default_group_lang ON customer.id_default_group = default_group_lang.id_group
                    AND default_group_lang.id_lang = ' . $langId . '
                LEFT JOIN ' . _DB_PREFIX_ . 'lang lang ON customer.id_lang = lang.id_lang
                LEFT JOIN (
                    SELECT 
                        cg.id_customer,
                        GROUP_CONCAT(cg.id_group SEPARATOR "' . $this->multivalueSeparator . '") ids,
                        GROUP_CONCAT(gl.name SEPARATOR "' . $this->multivalueSeparator . '") names
                    FROM (
                        SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_ . 'customer_group WHERE 1' . $groupsCond . '
                    ) sub_group
                    JOIN ' . _DB_PREFIX_ . 'customer_group cg ON sub_group.id_customer = cg.id_customer
                    LEFT JOIN ' . _DB_PREFIX_ . 'group_lang gl ON cg.id_group = gl.id_group AND gl.id_lang = ' . $langId . '
                    GROUP BY cg.id_customer
                ) groupp ON customer.id_customer = groupp.id_customer
                LEFT JOIN (
                    SELECT DISTINCT id_customer FROM ' . _DB_PREFIX_ . 'customer_group
                ) for_null_group ON customer.id_customer = for_null_group.id_customer
                LEFT JOIN ' . _DB_PREFIX_ . 'risk risk ON customer.id_risk = risk.id_risk
                LEFT JOIN ' . _DB_PREFIX_ . 'risk_lang risk_lang ON risk.id_risk = risk_lang.id_risk AND risk_lang.id_lang = ' . $langId . '
                ';

        $this->sql .= '
                WHERE customer.deleted = 0
            ';

        // Filter By Date
        $date = 'date_add';
        if ($customersDate === 'this_month') {
            $this->sql .= " 
                AND customer.$date >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            $this->sql .= " 
                AND customer.$date < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
        } elseif ($customersDate === 'last_month') {
            $this->sql .= " 
                AND customer.$date >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
            $this->sql .= " 
                AND customer.$date < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
        } elseif ($customersDate === 'this_week') {
            $start = date('N') == 1 ? date('Y-m-d') : date('Y-m-d', strtotime('last monday'));
            $end = date('Y-m-d', strtotime('this sunday'));
            $this->sql .= " 
                AND customer.$date >= '" . $start . "'";
            $this->sql .= " 
                AND customer.$date <= '" . $end . "'";
        } elseif ($customersDate === 'last_week') {
            $start = date('N') == 1 ? date('Y-m-d', strtotime('last monday')) : date('Y-m-d', strtotime('last week monday'));
            $end = date('Y-m-d', strtotime('last sunday'));
            $this->sql .= " 
                AND customer.$date >= '" . $start . "'";
            $this->sql .= " 
                AND customer.$date <= '" . $end . "'";
        } elseif ($customersDate === 'today') {
            $this->sql .= " 
                AND customer.$date >= '" . date("Y-m-d", strtotime("today")) . "'";
            $this->sql .= " 
                AND customer.$date < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
        } elseif ($customersDate === 'last_24_hours') {
            $this->sql .= " 
                AND customer.$date >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
            $this->sql .= " 
                AND customer.$date < '" . date('Y-m-d H:i:s') . "'";
        } elseif ($customersDate === 'yesterday') {
            $this->sql .= " 
                AND customer.$date >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
            $this->sql .= " 
                AND customer.$date < '" . date("Y-m-d", strtotime("today")) . "'";
        } elseif ($customersDate === 'select_date') {
            if ($fromDate) {
                $this->sql .= " 
                    AND customer.$date >= '" . $fromDate . "'";
            }
            if ($toDate) {
                $this->sql .= " 
                    AND customer.$date < '" . $toDate . "'";
            }
        }

        // Filter By Availability
        $availabilityCond = '';
        if ($availability === 'active') {
            $availabilityCond = '
                AND customer.active = 1 
                ';
        } elseif ($availability === 'inactive') {
            $availabilityCond = '
                AND customer.active = 0 
                ';
        }
        $this->sql .= $availabilityCond;

        // Filter By Customer
        $customerCond = '';
        if ($customers) {
            if ($customersType === 'unselected') {
                $customerCond = 'customer.id_customer NOT IN (' . $customers . ')';
            } else {
                $customerCond = 'customer.id_customer IN (' . $customers . ')';
            }
        }
        if ($customerCond) {
            $this->sql .= ' 
                        AND (' . $customerCond . ') ';
        }

        // Filter By Group
        $this->sql .= $groupsCond2;

        // Sort By ...
        $this->sql .= ' ORDER BY ' . $this->sort . $this->sortWay;
        if ($this->sort !== 'customer.id_customer') {
            $this->sql .= ', customer.id_customer ASC';
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
            $docName = $this->inputs['doc_name'] ?: $this->module->l('Customers', 'EICustomersExport');

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

            $this->customers = $this->getCustomers();

            if (!$this->customers && Configuration::getGlobalValue('IPE_SCHDL_DNSEM')) {
                return;
            }
        } else {
            $this->fileType = Tools::getValue('as');
            $docName = Tools::getValue('doc_name') ?: $this->module->l('Customers', 'EICustomersExport');

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

            $this->customers = $this->getCustomers();
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
        $doneCount += $this->customers ? count($this->customers) : 0;

        $results['isFinished'] = $doneCount < $this->limit;

        $results['doneCount'] = $this->offset + $doneCount;
        if ($this->offset === 0) {
            $total = $this->getCustomers(true);
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
                if ($this->customers) {
                    fputcsv($handle, array_keys($this->customers[0]), $this->dlm, $this->encl);
                } else {
                    fputcsv($handle, [$this->module->l('No Data', 'EICustomersExport')], $this->dlm, $this->encl);
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
        foreach ($this->customers as $value) {
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
            $excelColumns = EIHelper::createColumnsArray(count($this->customers[0]));
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()->setCreator('Tehran Alishov')
                ->setLastModifiedBy('Tehran Alishov')
                ->setTitle('Office 2007 XLSX Customers Document')
                ->setSubject('Office 2007 XLSX Customers Document')
                ->setDescription('Customers document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('Customers result file');
            $spreadsheet->setActiveSheetIndex(0);

            if (empty($this->customers)) {
                $sheet->setCellValue('A1', $this->module->l('No Data', 'EICustomersExport'));
            } else {
                $excelColumns = EIHelper::createColumnsArray(count($this->customers[0]));
                $sheet->getDefaultColumnDimension()->setWidth(21);
                $sheet->getDefaultRowDimension()->setRowHeight(30);

                $spreadsheet->getDefaultStyle()->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A1:' . end($excelColumns) . (count($this->customers)))
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFont()->setBold(true);
                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCF0FF');
                $sheet->getStyle('A1:' . end($excelColumns) . '1')->getBorders()
                    ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Rename worksheet
                $sheet->setTitle($this->module->l('Customers', 'EICustomersExport'));

                $headers = array_keys($this->customers[0]);
                foreach ($headers as $key => $header) {
                    $sheet->setCellValue($excelColumns[$key] . '1', $header);
                }
            }
        }

        foreach ($this->customers as $key => $value) {
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
