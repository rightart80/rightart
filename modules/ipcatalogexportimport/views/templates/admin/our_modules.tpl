{*
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2022 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<hr>
<div class="panel">
    <div class="row">
        <div class="col-lg-3 company-logo">
            <div class="block-company">
                <span>{l s='Developed by' mod='ipcatalogexportimport'}</span>
                <img src="{$path_uri}views/img/smart_presta.jpg">
            </div>
            <div class="block-partner">
                <img src="{$path_uri}views/img/official_prestashop_partner.jpg">
            </div>
            <div class="block-creator">
                <img src="{$path_uri}views/img/partner_module_creator.jpg">
            </div>
            <span class="partner">{l s='PrestaShop Premium Partner' mod='ipcatalogexportimport'}</span>
        </div>
        <div class="col-lg-7">
            {foreach from=$adModules item=module}
                <div class="col-lg-4">
                    <div class="module-block text-center">
                        <a href="{$module.url}" target="_blank">
                            <div class="title">
                                <img src="{$module.pico}">
                                <p class="module-name">
                                    {$module.name}
                                </p>
                            </div>
                            <button class="btn btn-default">
                                <span>{l s='More information' mod='ipcatalogexportimport'}</span>
                            </button>
                        </a>
                    </div>
                </div>
            {/foreach}
        </div>
        <div class="col-lg-2 text-center">
            <a class="btn btn-default our-modules" href="https://addons.prestashop.com/{$lang_iso}/252_smart-presta" target="_blank">
                <i class="icon-external-link"></i>
                {l s='Discover all our modules' mod='ipcatalogexportimport'}
            </a>
        </div>
    </div>
</div>