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

namespace Sendinblue\Hooks;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ActionCategoryHook extends AbstractHook
{
    const EVENT_CATEGORY_CREATED = '/events/%s/collection_sync?action=collection_created';

    const EVENT_CATEGORY_DELETED = '/events/%s/collection_sync?action=collection_deleted';

    const EVENT_CATEGORY_UPDATED = '/events/%s/collection_sync?action=collection_updated';

    public function handleEvent($data)
    {
        $category = [];
        if (empty($data->id)) {
            return $category;
        }

        $defaultLanguage = (int) \Context::getContext()->language->id;
        $data = new \Category($data->id, $defaultLanguage);
        $link = new \Link();

        $category['id'] = (int) $data->id;
        $category['name'] = $data->name;
        $category['description'] = $data->description;
        $category['slug'] = $data->link_rewrite;
        $category['parent'] = (int) $data->id_parent;
        $category['url'] = $link->getCategoryLink($data->id, $data->link_rewrite);

        return $category;
    }

    public function categoryAddEvent($category)
    {
        try {
            $category = $this->handleEvent($category['category']);
            if (!empty($category)) {
                $this->getApiClientService()->categoryEvents($category, self::EVENT_CATEGORY_CREATED);
            }
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    public function categoryUpdateEvent($category)
    {
        try {
            $category = $this->handleEvent($category['category']);
            if (!empty($category)) {
                $this->getApiClientService()->categoryEvents($category, self::EVENT_CATEGORY_UPDATED);
            }
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    public function categoryDeleteEvent($category)
    {
        try {
            if (!empty($category['category'])) {
                $data = [];
                $data['id'] = (int) $category['category']->id_category;
                $this->getApiClientService()->categoryEvents($data, self::EVENT_CATEGORY_DELETED);
            }
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }
}
