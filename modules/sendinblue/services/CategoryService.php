<?php
/**
 * 2007-2025 Sendinblue
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@sendinblue.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Sendinblue <contact@sendinblue.com>
 * @copyright 2007-2025 Sendinblue
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of Sendinblue
 */

namespace Sendinblue\Services;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CategoryService
{
    const DEFAULT_LIMIT = 100;
    const DEFAULT_PAGE = 1;

    /**
     * @var int|null
     */
    private $idShop;

    /**
     * @var false|string
     */
    private $defaultLanguage;

    /**
     * @var false|int|string
     */
    private $idLang;

    public function __construct()
    {
        $this->idShop = \ContextCore::getContext()->shop->id;
        $this->defaultLanguage = \ConfigurationCore::get('PS_LANG_DEFAULT');
        $this->idLang = !empty($this->defaultLanguage) ? $this->defaultLanguage : \ContextCore::getContext()->language->id;
    }

    public function getTotalCategoryCount()
    {
        try {
            $sql = sprintf(
                    'SELECT count(DISTINCT(c.`id_category`)) AS `count` FROM  `%scategory_shop` c LEFT JOIN `%scategory_lang` cl ON (cl.`id_category` = c.`id_category`) WHERE c.`id_shop` = %d AND cl.`id_lang` = %d AND cl.`id_shop` = %d AND c.id_category != "1"',
                    _DB_PREFIX_,
                    _DB_PREFIX_,
                    $this->idShop,
                    $this->idLang,
                    $this->idShop
            );
            $result = \DbCore::getInstance()->executeS(
                $sql
            );
            return isset($result[0]['count']) ? (int) $result[0]['count'] : 0;
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog($e->getMessage(), 3);
        }
    }

    /**
     * @param int $page
     * @param int $itemsPerPage
     * @return array|null
     */
    public function getCategories($offset, $itemsPerPage)
    {
        $sql = sprintf(
            'SELECT  DISTINCT(c.`id_category`), c.`id_shop`, cl.`name`, cl.`description` , cl.`id_lang` FROM  `%scategory_shop` c LEFT JOIN `%scategory_lang` cl ON (cl.`id_category` = c.`id_category`) WHERE c.`id_shop` = %d AND cl.`id_lang` = %d AND cl.`id_shop` = %d  AND c.id_category != "1" LIMIT %d, %d',
            _DB_PREFIX_,
            _DB_PREFIX_,
            $this->idShop,
            $this->idLang,
            $this->idShop,
            $offset,
            $itemsPerPage
        );

        try {
            $result = \DbCore::getInstance()->executeS($sql);

            $categories = [];

            if (empty($result)) {
                return $categories;
            }

            foreach ($result as $key => $category) {
                $categories[$key]['id'] = (int) $category['id_category'];
                $categories[$key]['name'] = $category['name'];
                $categories[$key]['description'] = $category['description'];
                $categories[$key]['url'] = \Context::getContext()->link->getCategoryLink($category['id_category']);
            }

            return $categories;
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog($e->getMessage(), 3);
            return null;
        }
    }

    /**
     * @param int $page
     * @return int
     */
    public function fixPage($page)
    {
        return empty($page) ? self::DEFAULT_PAGE : $page;
    }
}
