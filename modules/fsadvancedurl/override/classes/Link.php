<?php
/**
 * Copyright 2022 ModuleFactory
 *
 * @author    ModuleFactory
 * @copyright ModuleFactory all rights reserved.
 * @license   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class Link extends LinkCore
{
    protected function getLangLink($idLang = null, Context $context = null, $idShop = null)
    {
        if (Configuration::get('FSAU_REMOVE_DEFAULT_LANG', null, null, $idShop) &&
            Language::isMultiLanguageActivated()) {
            if (!$idLang) {
                if (is_null($context)) {
                    $context = Context::getContext();
                }

                $idLang = $context->language->id;
            }

            if ($idLang == Configuration::get('PS_LANG_DEFAULT', null, null, $idShop)) {
                return '';
            }
        }

        return parent::getLangLink($idLang, $context, $idShop);
    }

    public function getCategoryLink(
        $category,
        $alias = null,
        $idLang = null,
        $selectedFilters = null,
        $idShop = null,
        $relativeProtocol = false
    ) {
        $dispatcher = Dispatcher::getInstance();

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink($idShop, null, $relativeProtocol) . $this->getLangLink($idLang, null, $idShop);

        if (!is_object($category)) {
            if (is_array($category) && isset($category['id_category'])) {
                $category = new Category($category['id_category'], $idLang);
            } elseif ((int) $category) {
                $category = new Category((int) $category, $idLang);
            } else {
                return '';
            }
        }

        $params = [];
        $params['id'] = $category->id;

        $selectedFilters = null === $selectedFilters ? '' : $selectedFilters;
        if (empty($selectedFilters)) {
            $rule = 'category_rule';
        } else {
            $rule = 'layered_rule';
            $params['selected_filters'] = $selectedFilters;
        }

        $params['rewrite'] = (!$alias) ? $category->getFieldByLang('link_rewrite', $idLang) : $alias;

        if ($dispatcher->hasKeyword($rule, $idLang, 'meta_keywords', $idShop)) {
            $params['meta_keywords'] = Tools::str2url($category->getFieldByLang('meta_keywords', $idLang));
        }

        if ($dispatcher->hasKeyword($rule, $idLang, 'meta_title', $idShop)) {
            $params['meta_title'] = Tools::str2url($category->getFieldByLang('meta_title', $idLang));
        }

        if ($dispatcher->hasKeyword($rule, $idLang, 'categories', $idShop)) {
            $cats = [];
            foreach ($category->getParentsCategories($idLang) as $cat) {
                if (!in_array($cat['id_category'], Link::$category_disable_rewrite)) {
                    $cats[] = $cat['link_rewrite'];
                }
            }
            $cats = array_reverse($cats);
            array_pop($cats);
            $params['categories'] = implode('/', $cats);
        }

        return $url . Dispatcher::getInstance()->createUrl($rule, $idLang, $params, $this->allow, '', $idShop);
    }

    public function getProductLink(
        $product,
        $alias = null,
        $category = null,
        $ean13 = null,
        $idLang = null,
        $idShop = null,
        $idProductAttribute = null,
        $force_routes = false,
        $relativeProtocol = false,
        $withIdInAnchor = false,
        $extraParams = [],
        bool $addAnchor = true
    ) {
        $remove_anchor = false;
        if (Module::isEnabled('fsadvancedurl')) {
            $fsau = Module::getInstanceByName('fsadvancedurl');
            $remove_anchor = $fsau->isRemoveAnchor($product, $idProductAttribute);
        }

        if ($remove_anchor) {
            $addAnchor = false;
        }

        return parent::getProductLink(
            $product,
            $alias,
            $category,
            $ean13,
            $idLang,
            $idShop,
            $idProductAttribute,
            $force_routes,
            $relativeProtocol,
            $withIdInAnchor,
            $extraParams,
            $addAnchor
        );
    }

    public function getCMSCategoryLink(
        $cmsCategory,
        $alias = null,
        $idLang = null,
        $idShop = null,
        $relativeProtocol = false
    ) {
        $dispatcher = Dispatcher::getInstance();

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink($idShop, null, $relativeProtocol) . $this->getLangLink($idLang, null, $idShop);

        if (!is_object($cmsCategory)) {
            if (is_array($cmsCategory) && isset($cmsCategory['id_cms_category'])) {
                $cmsCategory = new CMSCategory($cmsCategory['id_cms_category'], $idLang);
            } elseif ((int) $cmsCategory) {
                $cmsCategory = new CMSCategory((int) $cmsCategory, $idLang);
            } else {
                return '';
            }
        }

        $params = [];
        $params['id'] = $cmsCategory->id;
        $params['rewrite'] = (!$alias) ? $cmsCategory->getFieldByLang('link_rewrite', $idLang) : $alias;

        if ($dispatcher->hasKeyword('cms_category_rule', $idLang, 'meta_keywords', $idShop)) {
            $params['meta_keywords'] = Tools::str2url($cmsCategory->getFieldByLang('meta_keywords', $idLang));
        }

        if ($dispatcher->hasKeyword('cms_category_rule', $idLang, 'meta_title', $idShop)) {
            $params['meta_title'] = Tools::str2url($cmsCategory->getFieldByLang('meta_title', $idLang));
        }

        if ($dispatcher->hasKeyword('cms_category_rule', $idLang, 'categories', $idShop)) {
            $cats = [];
            if (Module::isEnabled('fsadvancedurl')) {
                $fsau = Module::getInstanceByName('fsadvancedurl');
                $categories = $fsau->getCMSCategoryParentCategories($cmsCategory->id, $idLang);
                if ($categories) {
                    foreach ($categories as $cat) {
                        $cats[] = $cat['link_rewrite'];
                    }
                    $cats = array_reverse($cats);
                    array_pop($cats);
                }
            }
            $params['categories'] = implode('/', $cats);
        }

        return $url . $dispatcher->createUrl('cms_category_rule', $idLang, $params, $this->allow, '', $idShop);
    }

    public function getCMSLink(
        $cms,
        $alias = null,
        $ssl = null,
        $idLang = null,
        $idShop = null,
        $relativeProtocol = false
    ) {
        $dispatcher = Dispatcher::getInstance();

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink($idShop, $ssl, $relativeProtocol) . $this->getLangLink($idLang, null, $idShop);

        if (!is_object($cms)) {
            if (is_array($cms) && isset($cms['id_cms'])) {
                $cms = new CMS($cms['id_cms'], $idLang);
            } elseif ((int) $cms) {
                $cms = new CMS((int) $cms, $idLang);
            } else {
                return '';
            }
        }

        $params = [];
        $params['id'] = $cms->id;
        $params['rewrite'] = (!$alias) ? $cms->getFieldByLang('link_rewrite', $idLang) : $alias;

        if ($dispatcher->hasKeyword('cms_rule', $idLang, 'meta_keywords', $idShop)) {
            $params['meta_keywords'] = Tools::str2url($cms->getFieldByLang('meta_keywords', $idLang));
        }

        if ($dispatcher->hasKeyword('cms_rule', $idLang, 'meta_title', $idShop)) {
            $params['meta_title'] = Tools::str2url($cms->getFieldByLang('meta_title', $idLang));
        }

        if ($dispatcher->hasKeyword('cms_rule', $idLang, 'categories', $idShop)) {
            $cats = [];
            $cmsCategory = new CMSCategory($cms->id_cms_category, $idLang);
            if (Validate::isLoadedObject($cmsCategory)) {
                if (Module::isEnabled('fsadvancedurl')) {
                    $fsau = Module::getInstanceByName('fsadvancedurl');
                    $categories = $fsau->getCMSCategoryParentCategories($cmsCategory->id, $idLang);
                    if ($categories) {
                        foreach ($categories as $cat) {
                            $cats[] = $cat['link_rewrite'];
                        }
                        $cats = array_reverse($cats);
                    }
                }
            }
            $params['categories'] = implode('/', $cats);
        }

        return $url . $dispatcher->createUrl('cms_rule', $idLang, $params, $this->allow, '', $idShop);
    }
}
