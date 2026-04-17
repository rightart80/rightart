{**
*
* NOTICE OF LICENSE
*
*  @author    SmartPresta <tehran.alishov@gmail.com>
*  @copyright 2023 SmartPresta
*  @license   Commercial License
*
*}

<div id="catalogEI-tab-content-Support" class="catalogEI-tab-content" style="display: none;">
    <div id="catalogEI-support" class="panel catalogEI-tab">
        <h3 class="tab"><i class="icon-support"></i> {l s='Support' mod='ipcatalogexportimport'}</h3>
        {*----------------------*}
        <p><b>{l s='You can get a PDF documentation on how to use this module' mod='ipcatalogexportimport'}</b></p>
        <a class="btn btn-default" href="https://intellipresta.com/docs/ipcatalogexportimport/readme_{$lang_iso_code}.pdf" target="_blank"><i class="icon-file-pdf-o" aria-hidden="true"></i> {l s='Documentation' mod='ipcatalogexportimport'}</a>
        {*----------------------*}
        <hr class="open_hr">
        <p><b>{l s='Translate untranslated or mistranslated fields' mod='ipcatalogexportimport'}</b></p>
        <div class="input-group">
            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                <i class="icon-flag"></i>
                {l s='Manage translations' mod='ipcatalogexportimport'}
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                {assign var=translations_link value=$link->getAdminLink('AdminTranslations')}
                {foreach from=$languages item=language}
                    <li>
                        <a href="{$translations_link}&type=modules&module=ipcatalogexportimport&lang={$language.iso_code}#ipcatalogexportimport" target="_blank">
                            {$language.name}
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
        {*----------------------*}
        {*<hr class="open_hr">
        <p><b>{l s='Discover our other modules' mod='ipcatalogexportimport'}</b></p>
        <a class="btn btn-default" target="_blank" href="https://addons.prestashop.com/{$lang_iso_code}/252_smart-presta">
            <i class="icon-puzzle-piece"></i> {l s='Our modules' mod='ipcatalogexportimport'}
        </a>*}
        {*----------------------*}
        <hr class="open_hr">
        <p><b>{l s='Any questions or additional features?' mod='ipcatalogexportimport'}</b></p>
        <a class="btn btn-default" target="_blank" href="https://addons.prestashop.com/en/contact-us?id_product=87189">
            <i class="icon-external-link"></i> {l s='Contact us' mod='ipcatalogexportimport'}
        </a>
        {*----------------------*}
    </div>
</div>







