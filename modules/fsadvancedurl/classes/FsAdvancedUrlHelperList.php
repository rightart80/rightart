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
class FsAdvancedUrlHelperList extends HelperList
{
    /** @var Fsadvancedurl|null */
    public $module;

    public function displayEditobjectLink($token, $id, $name = null)
    {
        $context = Context::getContext();
        list($type, $id) = explode('_', $id);

        $url = sha1(FsAdvancedUrlModule::jsonEncodeStatic($token) . FsAdvancedUrlModule::jsonEncodeStatic($name));
        switch ($type) {
            case 'product':
                $url = $context->link->getAdminLink('AdminProducts');
                $url .= '&id_product=' . $id . '&updateproduct&key_tab=Seo';
                if (FsAdvancedUrlModule::isPsMin17Static()) {
                    $url = $context->link->getAdminLink('AdminProducts', true, [
                        'id_product' => $id,
                    ]) . '#tab-step5';
                }
                break;
            case 'category':
                $url = $context->link->getAdminLink('AdminCategories');
                $url .= '&id_category=' . $id . '&updatecategory';
                break;
            case 'manufacturer':
                $url = $context->link->getAdminLink('AdminManufacturers');
                $url .= '&id_manufacturer=' . $id . '&updatemanufacturer';
                break;
            case 'supplier':
                $url = $context->link->getAdminLink('AdminSuppliers');
                $url .= '&id_supplier=' . $id . '&updatesupplier';
                break;
            case 'cms':
                $url = $context->link->getAdminLink('AdminCmsContent');
                $url .= '&id_cms=' . $id . '&updatecms';
                break;
            case 'cmscategory':
                $url = $context->link->getAdminLink('AdminCmsContent');
                $url .= '&id_cms_category=' . $id . '&updatecms_category';
                break;
        }

        $this->module->smartyAssign([
            'button_url' => $url,
            'button_name' => $this->module->l('Edit'),
        ]);

        return $this->module->smartyFetch('admin/edit_object_link.tpl');
    }
}
