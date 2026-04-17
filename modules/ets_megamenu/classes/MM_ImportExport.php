<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }

class MM_ImportExport
{
    public static function importXmlTbl($context, $xml)
    {
        if (!$xml)
            return false;
        if (isset($xml->ets_mm_menu) && $xml->ets_mm_menu) {
            foreach ($xml->ets_mm_menu as $menu_data) {

                $id_menu = self::addObj($context,'menu', $menu_data);
                if ((int)$menu_data['enabled_vertical']) {
                    if ($id_menu && isset($menu_data->ets_mm_tab) && $menu_data->ets_mm_tab) {
                        foreach ($menu_data->ets_mm_tab as $tab_data) {
                            $foreign_key_tab = array(
                                'id_menu' => $id_menu
                            );
                            $id_tab = self::addObj($context,'tab', $tab_data, $foreign_key_tab);
                            if ($id_tab && isset($tab_data->ets_mm_column) && $tab_data->ets_mm_column) {
                                foreach ($tab_data->ets_mm_column as $column_data) {
                                    $foreign_key_column = array(
                                        'id_menu' => $id_menu,
                                        'id_tab' => $id_tab,
                                    );
                                    $id_column = self::addObj($context, 'column', $column_data, $foreign_key_column);
                                    if ($id_column && isset($column_data->ets_mm_block) && $column_data->ets_mm_block) {
                                        foreach ($column_data->ets_mm_block as $block_data) {
                                            $foreign_key_block = array(
                                                'id_column' => $id_column,
                                            );
                                            self::addObj($context,'block', $block_data, $foreign_key_block);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if ($id_menu && isset($menu_data->ets_mm_column) && $menu_data->ets_mm_column) {
                        foreach ($menu_data->ets_mm_column as $column_data) {
                            $foreign_key_column = array(
                                'id_menu' => $id_menu
                            );
                            $id_column = self::addObj($context, 'column', $column_data, $foreign_key_column);
                            if ($id_column && isset($column_data->ets_mm_block) && $column_data->ets_mm_block) {
                                foreach ($column_data->ets_mm_block as $block_data) {
                                    $foreign_key_block = array(
                                        'id_column' => $id_column,
                                    );
                                    self::addObj($context, 'block', $block_data, $foreign_key_block);
                                }
                            }
                        }
                    }
                }

            }
        }
        return true;
    }
    protected static function addObj($context, $obj, $data, $foreign_key = array())
    {
        $realOjbect = ($obj == 'menu' ? new MM_Menu() : ($obj == 'column' ? new MM_Column() : ($obj == 'tab' ? new MM_Tab : new MM_Block())));
        $languages = Language::getLanguages(false);
        /** @var MM_Menu  $realOjbect */
        $attrs = $realOjbect->getFormField();
        foreach ($attrs['configs'] as $key => $val) {
            if (!isset($val['lang']) || !$val['lang']) {
                if (isset($data[$key]) && $data[$key]) {
                    $realOjbect->$key =self::setVal($context, $key, (string)$data[$key]);
                } elseif (isset($val['default'])) {
                    $realOjbect->$key = $val['default'];
                } else
                    $realOjbect->$key = '';
            }
        }
        if (isset($data->datalanguage) && $data->datalanguage) {
            $language_xml_default = null;
            foreach ($data->datalanguage as $language_xml) {
                if (isset($language_xml['default']) && (int)$language_xml['default']) {
                    $language_xml_default = $language_xml;
                    break;
                }
            }
            $list_language_xml = array();

            foreach ($data->datalanguage as $language_xml) {
                $iso_code = (string)$language_xml['iso_code'];
                $id_lang = Language::getIdByIso($iso_code);
                $list_language_xml[] = $id_lang;

                if ($id_lang) {

                    foreach ($attrs['configs'] as $key => $val) {
                        if (isset($val['lang']) && $val['lang']) {
                            $temp = $realOjbect->$key;
                            $temp[$id_lang] = (string)$language_xml->$key;

                            if (!$temp[$id_lang]) {
                                if (isset($language_xml_default) && $language_xml_default && isset($language_xml_default->$key) && $language_xml_default->$key) {
                                    $temp[$id_lang] = (string)$language_xml_default->$key;
                                }
                            }
                            $realOjbect->$key = $temp;
                        }
                    }
                }
            }
            foreach ($languages as $language) {
                if (!in_array($language['id_lang'], $list_language_xml)) {
                    foreach ($attrs['configs'] as $key => $val) {
                        if (isset($val['lang']) && $val['lang']) {
                            $temp = $realOjbect->$key;
                            if (isset($language_xml_default) && $language_xml_default && isset($language_xml_default->$key) && $language_xml_default->$key) {
                                $temp[$language['id_lang']] = $language_xml_default->$key;
                            }
                            $realOjbect->$key = $temp;
                        }
                    }
                }
            }
        }
        if ($foreign_key) {
            foreach ($foreign_key as $key => $val) {
                $realOjbect->$key = $val;
            }
        }
        if ($realOjbect->add(true, false, $context->shop->id))
            return $realOjbect->id;
        return false;
    }
    protected static function setVal($context, $key, $val)
    {
        if ($key != 'id_products') {
            return $val;
        } elseif (!$val) {
            return '';
        } else {
            $ids = explode(',', $val);
            $retVal = array();
            foreach ($ids as $id) {
                if ($id) {
                    $tmpIDs = explode('-', $id);
                    $product = new Product((int)$tmpIDs[0]);
                    $id_combination = isset($tmpIDs[1]) && $tmpIDs[1] ? $tmpIDs[1] : 0;
                    if ($product->id && ($id_combination == 0 || (!Combination::isFeatureActive() || (($product->getAttributeCombinationsById((int)$id_combination, $context->language->id)))))) {
                        $retVal[] = $id;
                    }
                }
            }
            return $retVal ? implode(',', $retVal) : '';
        }
    }
    public static function importXmlConfig($xml)
    {
        if (!$xml)
            return false;
        $languages = Language::getLanguages(false);
        $MMConfig = new MM_Config();
        foreach ($MMConfig->getFormField() as $key => $config) {
            if (property_exists($xml, $key)) {
                if (isset($config['lang']) && $config['lang']) {
                    $temp = array();
                    foreach ($languages as $lang) {
                        $node = $xml->$key;
                        $temp[$lang['id_lang']] = isset($node['configValue']) ? (string)$node['configValue'] : (isset($config['default']) ? $config['default'] : '');
                    }
                    Configuration::updateValue($key, $temp);
                } else {
                    $node = $xml->$key;
                    Configuration::updateValue($key, isset($node['configValue']) ? (string)$node['configValue'] : (isset($config['default']) ? $config['default'] : ''));
                }
            }
        }
        /** @var Ets_megamenu $megamenu */
        $megamenu = Module::getInstanceByName('ets_megamenu');
        $megamenu->configExtra(true);
        return true;
    }
    public static function renderMenuDataXml($id_shop)
    {
        $xml_output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml_output .= '<entity_profile>' . "\n";
        $sql = "SELECT m.*,ms.id_shop FROM `" . _DB_PREFIX_ . 'ets_mm_menu` m
        INNER JOIN `' . _DB_PREFIX_ . 'ets_mm_menu_shop` ms ON (m.id_menu= ms.id_menu AND ms.id_shop="' . (int)$id_shop . '")';
        $menus = Db::getInstance()->executeS($sql);
        if ($menus) {
            $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
            foreach ($menus as &$menu) {
                $xml_output .= '<ets_mm_menu ';
                foreach ($menu as $key => $value) {
                    if (strpos($value, '"') === false)
                        $xml_output .= $key . '="' . str_replace('&', 'and', $value) . '" ';
                    else
                        $xml_output .= $key . "='" . str_replace('&', 'and', $value) . "' ";
                }
                $xml_output .= ' >' . "\n";
                $menu['datalanguages'] = Db::getInstance()->executeS('SELECT ml.*,l.iso_code FROM `' . _DB_PREFIX_ . 'ets_mm_menu_lang` ml,' . _DB_PREFIX_ . 'lang l WHERE ml.id_lang=l.id_lang AND ml.id_menu=' . (int)$menu['id_menu']);
                if (isset($menu['datalanguages']) && $menu['datalanguages']) {
                    foreach ($menu['datalanguages'] as $datalanguage) {
                        $xml_output .= '<datalanguage iso_code="' . $datalanguage['iso_code'] . '"' . ($datalanguage['id_lang'] == Configuration::get('PS_LANG_DEFAULT') ? ' default="1"' : '') . ' >' . "\n";
                        foreach ($datalanguage as $key => $value)
                            if ($key != 'id_menu' && $key != 'id_lang' && $key != 'iso_code')
                                $xml_output .= '<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>' . "\n";
                        $xml_output .= '</datalanguage>' . "\n";
                    }
                }
                if ($menu['enabled_vertical']) {
                    $menu['tabs'] = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ets_mm_tab` c WHERE c.id_menu=' . (int)$menu['id_menu']);
                    if ($menu['tabs']) {
                        foreach ($menu['tabs'] as &$tab) {
                            $xml_output .= '<ets_mm_tab ';
                            foreach ($tab as $key => $value) {
                                if (strpos($value, '"') === false)
                                    $xml_output .= $key . '="' . str_replace('&', 'and', $value) . '" ';
                                else
                                    $xml_output .= $key . "='" . str_replace('&', 'and', $value) . "' ";
                            }
                            $xml_output .= ' >' . "\n";
                            $tab['datalanguages'] = Db::getInstance()->executeS('SELECT tl.*,l.iso_code FROM `' . _DB_PREFIX_ . 'ets_mm_tab_lang` tl,`' . _DB_PREFIX_ . 'lang` l WHERE tl.id_lang=l.id_lang AND tl.id_tab=' . (int)$tab['id_tab']);
                            if (isset($tab['datalanguages']) && $tab['datalanguages']) {
                                foreach ($tab['datalanguages'] as $datalanguage) {
                                    $xml_output .= '<datalanguage iso_code="' . $datalanguage['iso_code'] . '"' . ($datalanguage['id_lang'] == $id_lang_default ? ' default="1"' : '') . ' >' . "\n";
                                    foreach ($datalanguage as $key => $value)
                                        if ($key != 'id_menu' && $key != 'id_lang' && $key != 'iso_code')
                                            $xml_output .= '<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>' . "\n";
                                    $xml_output .= '</datalanguage>' . "\n";
                                }
                            }
                            $tab['columns'] = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ets_mm_column` c WHERE c.id_tab=' . (int)$tab['id_tab']);
                            if ($tab['columns']) {
                                foreach ($tab['columns'] as &$column) {
                                    $xml_output .= '<ets_mm_column ';
                                    foreach ($column as $key => $value) {
                                        if (strpos($value, '"') === false)
                                            $xml_output .= $key . '="' . str_replace('&', 'and', $value) . '" ';
                                        else
                                            $xml_output .= $key . "='" . str_replace('&', 'and', $value) . "' ";
                                    }
                                    $xml_output .= ' >' . "\n";
                                    $column['blocks'] = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ets_mm_block` b WHERE b.id_column=' . (int)$column['id_column']);
                                    if ($column['blocks']) {
                                        foreach ($column['blocks'] as &$block) {
                                            $xml_output .= '<ets_mm_block ';
                                            foreach ($block as $key => $value) {
                                                if (strpos($value, '"') === false)
                                                    $xml_output .= $key . '="' . str_replace('&', 'and', $value) . '" ';
                                                else
                                                    $xml_output .= $key . "='" . str_replace('&', 'and', $value) . "' ";
                                            }
                                            $xml_output .= ' >' . "\n";
                                            $block['datalanguages'] = Db::getInstance()->executeS('SELECT bl.*,l.iso_code FROM `' . _DB_PREFIX_ . 'ets_mm_block_lang` bl,`' . _DB_PREFIX_ . 'lang` l WHERE bl.id_lang=l.id_lang AND bl.id_block=' . (int)$block['id_block']);
                                            if (isset($block['datalanguages']) && $block['datalanguages']) {
                                                foreach ($block['datalanguages'] as $datalanguage) {
                                                    $xml_output .= '<datalanguage iso_code="' . $datalanguage['iso_code'] . '"' . ($datalanguage['id_lang'] == $id_lang_default ? ' default="1"' : '') . ' >' . "\n";
                                                    foreach ($datalanguage as $key => $value)
                                                        if ($key != 'id_block' && $key != 'id_lang' && $key != 'iso_code')
                                                            $xml_output .= '<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>' . "\n";
                                                    $xml_output .= '</datalanguage>' . "\n";
                                                }
                                            }
                                            $xml_output .= '</ets_mm_block>' . "\n";
                                        }
                                    }
                                    $xml_output .= '</ets_mm_column>' . "\n";
                                }
                            }
                            $xml_output .= '</ets_mm_tab>' . "\n";
                        }
                    }
                } else {
                    $menu['columns'] = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ets_mm_column` c WHERE c.id_menu=' . (int)$menu['id_menu']);
                    if ($menu['columns']) {
                        foreach ($menu['columns'] as &$column) {
                            $xml_output .= '<ets_mm_column ';
                            foreach ($column as $key => $value) {
                                if (strpos($value, '"') === false)
                                    $xml_output .= $key . '="' . str_replace('&', 'and', $value) . '" ';
                                else
                                    $xml_output .= $key . "='" . str_replace('&', 'and', $value) . "' ";
                            }
                            $xml_output .= ' >' . "\n";
                            $column['blocks'] = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ets_mm_block` b WHERE b.id_column=' . (int)$column['id_column']);
                            if ($column['blocks']) {
                                foreach ($column['blocks'] as &$block) {
                                    $xml_output .= '<ets_mm_block ';
                                    foreach ($block as $key => $value) {
                                        if (strpos($value, '"') === false)
                                            $xml_output .= $key . '="' . str_replace('&', 'and', $value) . '" ';
                                        else
                                            $xml_output .= $key . "='" . str_replace('&', 'and', $value) . "' ";
                                    }
                                    $xml_output .= ' >' . "\n";
                                    $block['datalanguages'] = Db::getInstance()->executeS('SELECT bl.*,l.iso_code FROM `' . _DB_PREFIX_ . 'ets_mm_block_lang` bl,' . _DB_PREFIX_ . 'lang l WHERE bl.id_lang=l.id_lang AND bl.id_block=' . (int)$block['id_block']);
                                    if (isset($block['datalanguages']) && $block['datalanguages']) {
                                        foreach ($block['datalanguages'] as $datalanguage) {
                                            $xml_output .= '<datalanguage iso_code="' . $datalanguage['iso_code'] . '"' . ($datalanguage['id_lang'] == $id_lang_default ? ' default="1"' : '') . ' >' . "\n";
                                            foreach ($datalanguage as $key => $value)
                                                if ($key != 'id_block' && $key != 'id_lang' && $key != 'iso_code')
                                                    $xml_output .= '<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>' . "\n";
                                            $xml_output .= '</datalanguage>' . "\n";
                                        }
                                    }
                                    $xml_output .= '</ets_mm_block>' . "\n";
                                }
                            }
                            $xml_output .= '</ets_mm_column>' . "\n";
                        }
                    }
                }
                $xml_output .= '</ets_mm_menu>' . "\n";
            }

        }
        $xml_output .= '</entity_profile>' . "\n";
        return $xml_output;
    }
}