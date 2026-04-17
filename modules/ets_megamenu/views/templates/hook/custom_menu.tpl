{*
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
*}
{if $ETS_MM_DISPLAY_SHOPPING_CART || $ETS_MM_DISPLAY_SEARCH || $ETS_MM_DISPLAY_CUSTOMER_INFO || $ETS_MM_CUSTOM_HTML_TEXT}
    <div class="mm_extra_item{if $ETS_MM_SEARCH_DISPLAY_DEFAULT} mm_display_search_default{/if}">
        {if $ETS_MM_CUSTOM_HTML_TEXT}
            <div class="mm_custom_text">
                {$ETS_MM_CUSTOM_HTML_TEXT nofilter}
            </div>
        {/if}
        {if $ETS_MM_DISPLAY_SEARCH}
            {hook h='displaySearch'}
        {/if}
        {if $ETS_MM_DISPLAY_CUSTOMER_INFO}
            {hook h='displayCustomerInforTop'}
        {/if}
        {if $ETS_MM_DISPLAY_SHOPPING_CART }
            {hook h='displayCartTop'}
        {/if}
    </div>
{/if}