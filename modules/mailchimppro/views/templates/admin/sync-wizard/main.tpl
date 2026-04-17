{*
 * PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 *}
<script type="importmap">
  {
    "imports": {
      "vue": "https://cdnjs.cloudflare.com/ajax/libs/vue/3.2.37/vue.esm-browser.min.js",
      "ConcurrencyManager": "https://pdev.loc/modules/mailchimppro/views/js/sync-wizard/main.js"
    }
  }
</script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="{$JsLybraryPath|escape:'htmlall':'UTF-8'}axios.min.js"></script>
<script src="{$mainJsPath|escape:'htmlall':'UTF-8'}" type="module"></script> {* HTML comment, no escape necessary *}

<div id="app" v-cloak>
    <fieldset class="row" ref="appContainer">
        <div class="col-xs-12">
            {include file="./_step-1.tpl"}
            {include file="./_step-2.tpl"}
            {include file="./_step-3.tpl"}
            {include file="./_step-4.tpl"}
            {include file="./_step-5.tpl"}
            {include file="./_step-6.tpl"}
            {include file="./_step-7.tpl"}
            {include file="./_step-8.tpl"}
            {include file="./_finish.tpl"}
        </div>
    </fieldset>
</div>
<style>
    [v-cloak] > * {
        display: none;
    }

    [v-cloak]::before {
        content: " ";
        display: block;
        position: absolute;
        width: 80px;
        height: 80px;
        background-image: url(/modules/mailchimppro/views/img/loader.svg);
        background-size: cover;
        left: 50%;
        top: 50%;
    }
</style>