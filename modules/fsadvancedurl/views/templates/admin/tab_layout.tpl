{**
 *  2020 ModuleFactory.co
 *
 *  @author    ModuleFactory.co <info@modulefactory.co>
 *  @copyright 2020 ModuleFactory.co
 *  @license   ModuleFactory.co Commercial License
 *}

<div class="row">
    <div id="fsau_tabs" class="col-lg-2 col-md-3">
        <div class="list-group">
            {foreach from=$fsau_tab_layout item=fsau_tab}
                <a class="list-group-item{if $fsau_active_tab == $fsau_tab.id} active{/if}"
                   href="#{$fsau_tab.id|escape:'htmlall':'UTF-8'}"
                   aria-controls="{$fsau_tab.id|escape:'htmlall':'UTF-8'}" role="tab" data-toggle="tab">
                    {$fsau_tab.title|escape:'htmlall':'UTF-8'|fsauCorrectTheMess}
                </a>
            {/foreach}
        </div>
        <div class="fsau-side-menu-container">
            <div class="fsau-brand-container">
                <div class="fsau-brand-col c-1"></div>
                <div class="fsau-brand-col c-2"></div>
                <div class="fsau-brand-col c-3"></div>
                <div class="fsau-brand-col c-4"></div>
            </div>
            <div class="fsau-need-help-container">
                <i class="fsau-fa fsau-fa-question-circle fsau-need-help-question-mark" aria-hidden="true"></i>
                <a class="fsau-need-help-link" href="https://addons.prestashop.com/en/contact-us?id_product=19643" target="_blank">
                    Need help? <i class="fsau-fa fsau-fa-external-link" aria-hidden="true"></i>
                </a>
            </div>
            <div class="fsau-more-modules-container">
                <img src="{$module_base_url|escape:'html':'UTF-8'}views/img/modules-link-logo-40.jpg">
                <a class="fsau-more-modules-link" href="https://addons.prestashop.com/en/2_community-developer?contributor=271190" target="_blank">
                    Our modules! <i class="fsau-fa fsau-fa-external-link" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-10 col-md-9">
        <div class="tab-content">
            {foreach from=$fsau_tab_layout item=fsau_tab}
                <div role="tabpanel" class="tab-pane{if $fsau_active_tab == $fsau_tab.id} active{/if}" id="{$fsau_tab.id|escape:'htmlall':'UTF-8'}">
                    {$fsau_tab.content|escape:'html':'UTF-8'|fsauCorrectTheMess}
                </div>
            {/foreach}
        </div>
    </div>
</div>
<div class="clearfix"></div>