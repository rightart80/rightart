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

class EICombinationsExport
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
        $theme = Shop::isFeatureActive() && file_exists(_PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($id_image) . $id_image . '-' . (int) Context::getContext()->shop->id_theme . '.jpg') ? '-' . Context::getContext()->shop->id_theme : '';
        $uri_path = _THEME_PROD_DIR_ . Image::getImgFolderStatic($id_image) . $id_image . $theme . '.jpg';
        return $this->context->link->protocol_content . Tools::getMediaServer($uri_path) . $uri_path;
    }

    private function getCombinations($getTotal = false)
    {
        // Columns that do not exist directly in the DB
        $absentColumns = array(
            'image_urls' => 'image.ids'
        );

        $low = !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product_attribute_shop` LIKE 'low_stock_threshold'"));
        $newColumns = [
            'combination.isbn' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product_attribute` LIKE 'isbn'")),
            'combination.mpn' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product_attribute` LIKE 'mpn'")),
            'combination_shop.low_stock_threshold' => $low,
            'combination_shop.low_stock_alert' => $low,
        ];

        if ($this->auto) {
            $this->langId = $langId = (int) $this->inputs['language'];
            $shopId = (int) $this->inputs['shop'] ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $combinationsDate = $this->inputs['date'];
            $fromDate = pSQL($this->inputs['from_date']);
            $toDate = pSQL($this->inputs['to_date']);
            $this->sortWay = (int) $this->inputs['sort_way'] === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL($this->inputs['sort']);
            $this->multivalueSeparator = pSQL($this->inputs['multivalue_separator']) ?: ',';
            
            $combinations = pSQL(implode(',', $this->datatables['combinations']['data']));
            $combinationsType = $this->datatables['combinations']['type'];

            $quantityOperator = pSQL($this->inputs['quantity_operator']);
            $quantity = (int) $this->inputs['quantity'];
            
            $categories = $this->inputs['categories'];
            $filterCategories = $this->inputs['category_whether_filter'];
            $categories_without = $this->inputs['category_without'];

            $attributes = pSQL(implode(',', $this->datatables['attributes']['data']));
            $attributesType = $this->datatables['attributes']['type'];
            
            $discount = $this->inputs['discount'];
        } else {
            $this->langId = $langId = (int) Tools::getValue('language');
            $shopId = (int) Tools::getValue('shop') ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $combinationsDate = Tools::getValue('date');
            $fromDate = pSQL(Tools::getValue('from_date'));
            $toDate = pSQL(Tools::getValue('to_date'));
            $this->sortWay = (int) Tools::getValue('sort_way') === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL(Tools::getValue('sort'));
            $this->multivalueSeparator = pSQL(Tools::getValue('multivalue_separator')) ?: ',';
            
            $combinations = pSQL(Tools::getValue('combinations_data'));
            $combinationsType = Tools::getValue('combinations_type');

            $quantityOperator = pSQL(Tools::getValue('quantity_operator'));
            $quantity = (int) Tools::getValue('quantity');
            
            $categories = Tools::getValue('categories');
            $filterCategories = Tools::getValue('category_whether_filter');
            $categories_without = Tools::getValue('category_without');

            $attributes = pSQL(Tools::getValue('attributes_data'));
            $attributesType = Tools::getValue('attributes_type');
            
            $discount = Tools::getValue('discount');
        }

        $this->sql = '
            SELECT ';
        foreach ($this->selectedColumns as $k => $col) {
            if (array_key_exists($k, $absentColumns)) {
                $this->sql .= "
                    {$absentColumns[$k]} `$col`, ";
            } elseif (array_key_exists($k, $newColumns) && !$newColumns[$k]) {
                $this->sql .= "
                    '' `$col`, ";
            } else {
                $this->sql .= "
                        $k `$col`, ";
            }
        }

        $this->sql = rtrim($this->sql, ', ');

        // Filter By Category
        $categoriesCond = $categoriesCond2 = '';
        if ($filterCategories === '1') {
            if ($categories) {
                $categoriesCond = ' WHERE id_category IN (' . implode(',', $categories) . ')';
                $categoriesCond2 .= 'category.id_product IS NOT NULL';
                if ($categories_without !== '0') {
                    $categoriesCond2 .= ' OR for_null_category.id_product IS NULL';
                }
                $categoriesCond2 = ' 
                    AND (' . $categoriesCond2 . ')';
            } else {
                if ($categories_without !== '0') {
                    $categoriesCond2 .= ' 
                        AND (for_null_category.id_product IS NULL)';
                }
            }
        }

        // Filter By Attribute
        $attributesCond = $attributesCond2 = '';
        if ($attributes) {
            if ($attributesType === 'unselected') {
                $attributesCond = ' WHERE id_attribute NOT IN (' . $attributes . ')';
            } else {
                $attributesCond = ' WHERE id_attribute IN (' . $attributes . ')';
            }
            $attributesCond2 .= 'attributes.id_product_attribute IS NOT NULL';

            $attributesCond2 = ' 
                AND (' . $attributesCond2 . ')';
        }

        $this->sql .= '
            FROM 
            ' . _DB_PREFIX_ . 'product_attribute combination
                LEFT JOIN
            ' . _DB_PREFIX_ . 'product_attribute_shop combination_shop ON combination.id_product_attribute = combination_shop.id_product_attribute
                    AND combination_shop.id_shop = ' . $shopId . '
                LEFT JOIN
            ' . _DB_PREFIX_ . 'product product ON combination.id_product = product.id_product
                LEFT JOIN
            ' . _DB_PREFIX_ . 'product_lang product_lang ON combination.id_product = product_lang.id_product
                    AND product_lang.id_shop = ' . $shopId . '
                    AND product_lang.id_lang = ' . $langId . '
                LEFT JOIN (
                    SELECT
                        pac.id_product_attribute,
                        GROUP_CONCAT(CONCAT_WS(":", IFNULL(agl.name, ""), al.name) ORDER BY agl.name, al.name, a.color SEPARATOR "' . $this->multivalueSeparator . '") name_values,
                        GROUP_CONCAT(ag.group_type ORDER BY agl.name, al.name, a.color SEPARATOR "' . $this->multivalueSeparator . '") `types`,
                        GROUP_CONCAT(agl.name ORDER BY agl.name, al.name, a.color SEPARATOR "' . $this->multivalueSeparator . '") `groups`,
                        GROUP_CONCAT(agl.public_name ORDER BY agl.name, al.name, a.color SEPARATOR "' . $this->multivalueSeparator . '") `public_name`,
                        GROUP_CONCAT(IF(a.color = "", al.name, CONCAT_WS(":", al.name, a.color)) ORDER BY agl.name, al.name, a.color SEPARATOR "' . $this->multivalueSeparator . '") `values`
                    FROM (
                        SELECT DISTINCT id_product_attribute
                        FROM ' . _DB_PREFIX_ . 'product_attribute_combination' .
            $attributesCond . '
                    ) temp
                    JOIN ' . _DB_PREFIX_ . 'product_attribute_combination pac ON temp.id_product_attribute = pac.id_product_attribute
                    JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                    JOIN ' . _DB_PREFIX_ . 'attribute_shop ash ON a.id_attribute = ash.id_attribute AND ash.id_shop = ' . $shopId . '
                    JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $langId . '
                    JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                    JOIN ' . _DB_PREFIX_ . 'attribute_group_shop agsh ON a.id_attribute_group = agsh.id_attribute_group AND agsh.id_shop = ' . $shopId . '
                    JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $langId . '
                    GROUP BY pac.id_product_attribute
                ) attributes ON combination.id_product_attribute = attributes.id_product_attribute
                LEFT JOIN (
                    SELECT DISTINCT id_product
                    FROM ' . _DB_PREFIX_ . 'category_product ' . $categoriesCond . '
                ) category ON product.id_product = category.id_product
                LEFT JOIN (
                    SELECT DISTINCT id_product FROM ' . _DB_PREFIX_ . 'category_product
                ) for_null_category ON product.id_product = for_null_category.id_product
                LEFT JOIN (
                    SELECT 
                        pai.id_product_attribute,
                        GROUP_CONCAT(IF(pai.id_image = 0, "", pai.id_image) ORDER BY i.position SEPARATOR "' . $this->multivalueSeparator . '") ids,
                        GROUP_CONCAT(il.`legend` ORDER BY i.position SEPARATOR "' . $this->multivalueSeparator . '") `texts`,
                        GROUP_CONCAT(i.`position` ORDER BY i.position SEPARATOR "' . $this->multivalueSeparator . '") `positions`
                    FROM ' . _DB_PREFIX_ . 'product_attribute_image pai
                    LEFT JOIN ' . _DB_PREFIX_ . 'image i ON pai.id_image = i.id_image
                    LEFT JOIN ' . _DB_PREFIX_ . 'image_lang il ON pai.id_image = il.id_image AND il.id_lang = ' . $langId . '
                    GROUP BY pai.id_product_attribute
                ) image ON combination.id_product_attribute = image.id_product_attribute
                LEFT JOIN ' . _DB_PREFIX_ . 'stock_available stock_available ON combination.id_product = stock_available.id_product
                    AND combination.id_product_attribute = stock_available.id_product_attribute
                    ' . StockAvailable::addSqlShopRestriction(null, $shopId, 'stock_available') . '
                LEFT JOIN (
                    SELECT
                        wpl.id_product,
                        wpl.id_product_attribute,
                        GROUP_CONCAT(CONCAT_WS(":", w.`reference`, w.`name`, wpl.location) SEPARATOR "' . $this->multivalueSeparator . '") name_ref_loc
                    FROM ' . _DB_PREFIX_ . 'warehouse_product_location wpl
                    LEFT JOIN ' . _DB_PREFIX_ . 'warehouse w ON wpl.id_warehouse = w.id_warehouse 
                    WHERE wpl.id_product_attribute <> 0
                    GROUP BY wpl.id_product, wpl.id_product_attribute
                ) warehouse ON combination.id_product = warehouse.id_product AND combination.id_product_attribute = warehouse.id_product_attribute
            ';

        $this->sql .= '
                WHERE 1
            ';

        // Filter By Date
        $date = 'date_add';
        if ($combinationsDate === 'this_month') {
            $this->sql .= " 
                AND product.$date >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            $this->sql .= " 
                AND product.$date < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
        } elseif ($combinationsDate === 'last_month') {
            $this->sql .= " 
                AND product.$date >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
            $this->sql .= " 
                AND product.$date < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
        } elseif ($combinationsDate === 'this_week') {
            $start = date('N') == 1 ? date('Y-m-d') : date('Y-m-d', strtotime('last monday'));
            $end = date('Y-m-d', strtotime('this sunday'));
            $this->sql .= " 
                AND product.$date >= '" . $start . "'";
            $this->sql .= " 
                AND product.$date <= '" . $end . "'";
        } elseif ($combinationsDate === 'last_week') {
            $start = date('N') == 1 ? date('Y-m-d', strtotime('last monday')) : date('Y-m-d', strtotime('last week monday'));
            $end = date('Y-m-d', strtotime('last sunday'));
            $this->sql .= " 
                AND product.$date >= '" . $start . "'";
            $this->sql .= " 
                AND product.$date <= '" . $end . "'";
        } elseif ($combinationsDate === 'today') {
            $this->sql .= " 
                AND product.$date >= '" . date("Y-m-d", strtotime("today")) . "'";
            $this->sql .= " 
                AND product.$date < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
        } elseif ($combinationsDate === 'last_24_hours') {
            $this->sql .= " 
                AND product.$date >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
            $this->sql .= " 
                AND product.$date < '" . date('Y-m-d H:i:s') . "'";
        } elseif ($combinationsDate === 'yesterday') {
            $this->sql .= " 
                AND product.$date >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
            $this->sql .= " 
                AND product.$date < '" . date("Y-m-d", strtotime("today")) . "'";
        } elseif ($combinationsDate === 'select_date') {
            if ($fromDate) {
                $this->sql .= " 
                    AND product.$date >= '" . $fromDate . "'";
            }
            if ($toDate) {
                $this->sql .= " 
                    AND product.$date < '" . $toDate . "'";
            }
        }

        // Filter By Combination
        $combinationCond = '';
        if ($combinations) {
            if ($combinationsType === 'unselected') {
                $combinationCond = 'combination.id_product_attribute NOT IN (' . $combinations . ')';
            } else {
                $combinationCond = 'combination.id_product_attribute IN (' . $combinations . ')';
            }
        }
        if ($combinationCond) {
            $this->sql .= ' 
                        AND (' . $combinationCond . ') ';
        }
        
        // Filter By Category
        $this->sql .= $categoriesCond2;

        // Filter By Quantity
        if ($quantityOperator !== 'none' && ($quantity || $quantity == '0')) {
            $this->sql .= '
                AND stock_available.quantity ' . EIHelper::getOperator($quantityOperator) . $quantity;
        }

        // Filter By Attribute
        $this->sql .= $attributesCond2;
        
        // Filter By Discount
        $discountCond = '';
        if ($discount === 'discounted') {
            $discountCond = '
                AND EXISTS (SELECT *
                    FROM ' . _DB_PREFIX_ . 'specific_price
                    WHERE id_product_attribute = combination.id_product_attribute
                        AND id_shop = ' . $shopId . '
                        AND (`from` < NOW() OR `from` = "0000-00-00 00:00:00") 
                        AND (`to` > NOW() OR `to` = "0000-00-00 00:00:00"))
                ';
        } elseif ($discount === 'nondiscounted') {
            $discountCond = '
                AND NOT EXISTS (SELECT *
                    FROM ' . _DB_PREFIX_ . 'specific_price
                    WHERE id_product_attribute = combination.id_product_attribute
                        AND id_shop = ' . $shopId . '
                        AND (`from` < NOW() OR `from` = "0000-00-00 00:00:00") 
                        AND (`to` > NOW() OR `to` = "0000-00-00 00:00:00"))
                ';
        }
        $this->sql .= $discountCond;

        // Sort By ...
        $this->sql .= ' ORDER BY ' . $this->sort . $this->sortWay;
        if ($this->sort !== 'combination.id_product_attribute') {
            $this->sql .= ', combination.id_product_attribute ASC';
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
            $docName = $this->inputs['doc_name'] ?: $this->module->l('Combinations', 'EICombinationsExport');

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

            $this->combinations = $this->getCombinations();

            if (!$this->combinations && Configuration::getGlobalValue('IPE_SCHDL_DNSEM')) {
                return;
            }
        } else {
            $this->fileType = Tools::getValue('as');
            $docName = Tools::getValue('doc_name') ?: $this->module->l('Combinations', 'EICombinationsExport');

            if(!Tools::isSubmit('selectedColumns')) {
                throw new PrestaShopException($this->module->l('Not all inputs were accepted by your server. Please increase the \'max_input_vars\' variable in your PHP configuration.', 'EICombinationsExport'));
            }
            $this->selectedColumns = json_decode(Tools::getValue('selectedColumns'), true);
            if (!$this->selectedColumns) {
                throw new PrestaShopException($this->module->l('Please select at least one column to export.', 'EICombinationsExport'));
            }

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

            $this->combinations = $this->getCombinations();
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
        $doneCount += $this->combinations ? count($this->combinations) : 0;

        $results['isFinished'] = $doneCount < $this->limit;

        $results['doneCount'] = $this->offset + $doneCount;
        if ($this->offset === 0) {
            $total = $this->getCombinations(true);
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
                if ($this->combinations) {
                    fputcsv($handle, array_keys($this->combinations[0]), $this->dlm, $this->encl);
                } else {
                    fputcsv($handle, [$this->module->l('No Data', 'EICombinationsExport')], $this->dlm, $this->encl);
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
        foreach ($this->combinations as $value) {
            if (!empty($value[$this->selectedColumns['image_urls']])) {
                $link = '';
                foreach (explode($this->multivalueSeparator, $value[$this->selectedColumns['image_urls']]) as $v) {
                    $link .= $this->getImageLink($v) . $this->multivalueSeparator;
                }
                $value[$this->selectedColumns['image_urls']] = rtrim($link, $this->multivalueSeparator);
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
            $excelColumns = EIHelper::createColumnsArray(count($this->combinations[0]));
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()->setCreator('Tehran Alishov')
                ->setLastModifiedBy('Tehran Alishov')
                ->setTitle('Office 2007 XLSX Combinations Document')
                ->setSubject('Office 2007 XLSX Combinations Document')
                ->setDescription('Combinations document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('Combinations result file');
            $spreadsheet->setActiveSheetIndex(0);

            if (empty($this->combinations)) {
                $sheet->setCellValue('A1', $this->module->l('No Data', 'EICombinationsExport'));
            } else {
                $excelColumns = EIHelper::createColumnsArray(count($this->combinations[0]));
                $sheet->getDefaultColumnDimension()->setWidth(21);
                $sheet->getDefaultRowDimension()->setRowHeight(30);

                $spreadsheet->getDefaultStyle()->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A1:' . end($excelColumns) . (count($this->combinations)))
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFont()->setBold(true);
                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCF0FF');
                $sheet->getStyle('A1:' . end($excelColumns) . '1')->getBorders()
                    ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Rename worksheet
                $sheet->setTitle($this->module->l('Combinations', 'EICombinationsExport'));

                $headers = array_keys($this->combinations[0]);
                foreach ($headers as $key => $header) {
                    $sheet->setCellValue($excelColumns[$key] . '1', $header);
                }

                if (isset($this->selectedColumns['image_urls'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['image_urls'], $headers)])->setWidth(40);
                }
            }
        }

        foreach ($this->combinations as $key => $value) {
            $i = 0;
            foreach ($value as $k => $val) {
                if ($k === $this->selectedColumns['image_urls'] && $val) {
                    $link = '';
                    $val = explode($this->multivalueSeparator, $val);
                    foreach ($val as $v) {
                        $link .= $this->getImageLink($v) . $this->multivalueSeparator;
                    }
                    $link = rtrim($link, $this->multivalueSeparator);
                    $cell = $excelColumns[$i] . ($this->offset + $key + 2);
                    $sheet->setCellValue($cell, $link);
                    if (count($val) === 1 && $link) {
                        $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                    }
                } elseif ($k === $this->selectedColumns['combination.ean13'] && $val) {
                    $sheet->setCellValueExplicit($excelColumns[$i] . ($this->offset + $key + 2), $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
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
