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

class EICategoriesExport
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

    private function getImageLink($id_cat)
    {
        if (file_exists(_PS_CAT_IMG_DIR_ . $id_cat . '.jpg')) {
            $uri_path = _THEME_CAT_DIR_ . $id_cat . '.jpg';
            return $this->context->link->protocol_content . Tools::getMediaServer($uri_path) . $uri_path;
        }
        return '';
    }

    private function getCategories($getTotal = false)
    {
        // Columns that do not exist directly in the DB
        $absentColumns = array(
            'category_link' => 'category.id_category',
            'category_image' => 'category.id_category',
            'image_url' => 'category.id_category',
        );

        if ($this->fileType === 'csv' && isset($this->selectedColumns['category_image'])) {
            unset($this->selectedColumns['category_image']);
        }

        if ($this->auto) {
            $this->langId = $langId = (int) $this->inputs['language'];
            $shopId = (int) $this->inputs['shop'] ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $categoriesDate = $this->inputs['date'];
            $fromDate = pSQL($this->inputs['from_date']);
            $toDate = pSQL($this->inputs['to_date']);
            $this->sortWay = (int) $this->inputs['sort_way'] === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL($this->inputs['sort']);
            $this->multivalueSeparator = pSQL($this->inputs['multivalue_separator']) ?: ',';

            $categories = $this->inputs['categories'];
            $filter_categories = $this->inputs['category_whether_filter'];
            $availability = $this->inputs['availability'];
        } else {
            $this->langId = $langId = (int) Tools::getValue('language');
            $shopId = (int) Tools::getValue('shop') ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $categoriesDate = Tools::getValue('date');
            $fromDate = pSQL(Tools::getValue('from_date'));
            $toDate = pSQL(Tools::getValue('to_date'));
            $this->sortWay = (int) Tools::getValue('sort_way') === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL(Tools::getValue('sort'));
            $this->multivalueSeparator = pSQL(Tools::getValue('multivalue_separator')) ?: ',';

            $categories = Tools::getValue('categories');
            $filter_categories = Tools::getValue('category_whether_filter');
            $availability = Tools::getValue('availability');
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
            ' . _DB_PREFIX_ . 'category category
                LEFT JOIN
            ' . _DB_PREFIX_ . 'category_shop category_shop ON category.id_category = category_shop.id_category
                    AND category_shop.id_shop = ' . $shopId . '
                LEFT JOIN
            ' . _DB_PREFIX_ . 'category_lang category_lang ON category.id_category = category_lang.id_category
                    AND category_lang.id_shop = ' . $shopId . '
                    AND category_lang.id_lang = ' . $langId . '
                LEFT JOIN 
            ' . _DB_PREFIX_ . 'category_lang parent ON category.id_parent = parent.id_category
                    AND parent.id_shop = ' . $shopId . '
                    AND parent.id_lang = ' . $langId . '
                LEFT JOIN
            ' . _DB_PREFIX_ . 'shop default_shop ON category.id_shop_default = default_shop.id_shop
                LEFT JOIN
            ' . _DB_PREFIX_ . 'shop_group default_shop_group ON default_shop.id_shop_group = default_shop_group.id_shop_group
                LEFT JOIN (
                    SELECT cg.id_category, GROUP_CONCAT(cg.id_group SEPARATOR "' . $this->multivalueSeparator . '") ids, GROUP_CONCAT(gl.name SEPARATOR "' . $this->multivalueSeparator . '") names
                    FROM ' . _DB_PREFIX_ . 'category_group cg
                    JOIN ' . _DB_PREFIX_ . 'group_lang gl ON cg.id_group = gl.id_group AND gl.id_lang = ' . $langId . '
                    GROUP BY cg.id_category
                ) category_group ON category.id_category = category_group.id_category
                LEFT JOIN (
                    SELECT gr.id_category, GROUP_CONCAT(CONCAT(gl.name, ":", gr.reduction) SEPARATOR "' . $this->multivalueSeparator . '") reductions
                    FROM ' . _DB_PREFIX_ . 'group_reduction gr
                    JOIN ' . _DB_PREFIX_ . 'group_lang gl ON gr.id_group = gl.id_group AND gl.id_lang = ' . $langId . '
                    GROUP BY gr.id_category
                ) group_reduction ON group_reduction.id_category = category.id_category
            ';

        $this->sql .= '
                WHERE 1
            ';

        // Filter By Category
        $categoriesCond = '';
        if ($filter_categories === '1' && $categories) {
            $categoriesCond = ' category.id_category IN (' . implode(',', $categories) . ')';
            $categoriesCond = ' 
                    AND (' . $categoriesCond . ')';
        }
        $this->sql .= $categoriesCond;

        // Filter By Date
        $date = 'date_add';
        if ($categoriesDate === 'this_month') {
            $this->sql .= " 
                AND category.$date >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            $this->sql .= " 
                AND category.$date < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
        } elseif ($categoriesDate === 'last_month') {
            $this->sql .= " 
                AND category.$date >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
            $this->sql .= " 
                AND category.$date < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
        } elseif ($categoriesDate === 'this_week') {
            $start = date('N') == 1 ? date('Y-m-d') : date('Y-m-d', strtotime('last monday'));
            $end = date('Y-m-d', strtotime('this sunday'));
            $this->sql .= " 
                AND category.$date >= '" . $start . "'";
            $this->sql .= " 
                AND category.$date <= '" . $end . "'";
        } elseif ($categoriesDate === 'last_week') {
            $start = date('N') == 1 ? date('Y-m-d', strtotime('last monday')) : date('Y-m-d', strtotime('last week monday'));
            $end = date('Y-m-d', strtotime('last sunday'));
            $this->sql .= " 
                AND category.$date >= '" . $start . "'";
            $this->sql .= " 
                AND category.$date <= '" . $end . "'";
        } elseif ($categoriesDate === 'today') {
            $this->sql .= " 
                AND category.$date >= '" . date("Y-m-d", strtotime("today")) . "'";
            $this->sql .= " 
                AND category.$date < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
        } elseif ($categoriesDate === 'last_24_hours') {
            $this->sql .= " 
                AND category.$date >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
            $this->sql .= " 
                AND category.$date < '" . date('Y-m-d H:i:s') . "'";
        } elseif ($categoriesDate === 'yesterday') {
            $this->sql .= " 
                AND category.$date >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
            $this->sql .= " 
                AND category.$date < '" . date("Y-m-d", strtotime("today")) . "'";
        } elseif ($categoriesDate === 'select_date') {
            if ($fromDate) {
                $this->sql .= " 
                    AND category.$date >= '" . $fromDate . "'";
            }
            if ($toDate) {
                $this->sql .= " 
                    AND category.$date < '" . $toDate . "'";
            }
        }

        // Filter By Availability
        $availabilityCond = '';
        if ($availability === 'active') {
            $availabilityCond = '
                AND category.active = 1 
                ';
        } elseif ($availability === 'inactive') {
            $availabilityCond = '
                AND category.active = 0 
                ';
        }
        $this->sql .= $availabilityCond;

        // Sort By ...
        $this->sql .= ' ORDER BY ' . $this->sort . $this->sortWay;
        if ($this->sort !== 'category.id_category') {
            $this->sql .= ', category.id_category ASC';
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
            $docName = $this->inputs['doc_name'] ?: $this->module->l('Categories', 'EICategoriesExport');

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

            $this->categories = $this->getCategories();

            if (!$this->categories && Configuration::getGlobalValue('IPE_SCHDL_DNSEM')) {
                return;
            }
        } else {
            $this->fileType = Tools::getValue('as');
            $docName = Tools::getValue('doc_name') ?: $this->module->l('Categories', 'EICategoriesExport');

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

            $this->categories = $this->getCategories();
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
        $doneCount += $this->categories ? count($this->categories) : 0;

        $results['isFinished'] = $doneCount < $this->limit;

        $results['doneCount'] = $this->offset + $doneCount;
        if ($this->offset === 0) {
            $total = $this->getCategories(true);
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
                if ($this->categories) {
                    fputcsv($handle, array_keys($this->categories[0]), $this->dlm, $this->encl);
                } else {
                    fputcsv($handle, [$this->module->l('No Data', 'EICategoriesExport')], $this->dlm, $this->encl);
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
        foreach ($this->categories as $value) {
            if (!empty($value[$this->selectedColumns['category_link']])) {
                $value[$this->selectedColumns['category_link']] = $this->context->link->getCategoryLink($value[$this->selectedColumns['category_link']], null, $this->langId);
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
            $excelColumns = EIHelper::createColumnsArray(count($this->categories[0]));
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()->setCreator('Tehran Alishov')
                ->setLastModifiedBy('Tehran Alishov')
                ->setTitle('Office 2007 XLSX Categories Document')
                ->setSubject('Office 2007 XLSX Categories Document')
                ->setDescription('Categories document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('Categories result file');
            $spreadsheet->setActiveSheetIndex(0);

            if (empty($this->categories)) {
                $sheet->setCellValue('A1', $this->module->l('No Data', 'EICategoriesExport'));
            } else {
                $excelColumns = EIHelper::createColumnsArray(count($this->categories[0]));
                $sheet->getDefaultColumnDimension()->setWidth(21);
                if (isset($this->selectedColumns['category_image'])) {
                    $sheet->getDefaultRowDimension()->setRowHeight(42);
                } else {
                    $sheet->getDefaultRowDimension()->setRowHeight(30);
                }

                $spreadsheet->getDefaultStyle()->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A1:' . end($excelColumns) . (count($this->categories)))
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFont()->setBold(true);
                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCF0FF');
                $sheet->getStyle('A1:' . end($excelColumns) . '1')->getBorders()
                    ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Rename worksheet
                $sheet->setTitle($this->module->l('Categories', 'EICategoriesExport'));

                $headers = array_keys($this->categories[0]);

                foreach ($headers as $key => $header) {
                    $sheet->setCellValue($excelColumns[$key] . '1', $header);
                }

                if (isset($this->selectedColumns['category_link'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['category_link'], $headers)])->setWidth(30);
                }
                if (isset($this->selectedColumns['image_url'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['image_url'], $headers)])->setWidth(30);
                }
                if (isset($this->selectedColumns['category_lang.description'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['category_lang.description'], $headers)])->setWidth(50);
                }
            }
        }

        $image_type = $this->getImageType('medium');
        $font = $sheet->getStyle('A1')->getFont();
        foreach ($this->categories as $key => $value) {
            $i = 0;
            foreach ($value as $k => $val) {
                if ($k === $this->selectedColumns['category_link']) {
                    $link = $this->context->link->getCategoryLink($val, null, $this->langId);
                    $cell = $excelColumns[$i] . ($this->offset + $key + 2);
                    $sheet->setCellValue($cell, $link);
                    if ($link) {
                        $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                    }
                } elseif ($k === $this->selectedColumns['category_image'] && $val) {
                    $image_path = realpath(_PS_CAT_IMG_DIR_ . $val . '-' . $image_type . '.jpg');
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
