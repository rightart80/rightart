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

<div class="panel">
    <div class="panel-body" style="padding: 0;">
        <hr>
        <div class="btn-group" role="group" style="padding: 15px;">
            <a href="#" class="btn btn-primary img-start"
               onclick='regenerateProductsExtra({$productId|escape:'htmlall':'UTF-8'}); return false;'> {* HTML comment, no escape necessary *}
                <i class="icon icon-retweet" aria-hidden="true"></i>
                {l s='Sync product' mod='mailchimppro'}
            </a>
        </div>
        <hr>
    </div>
</div>
<style>
    #product_extra_modules-tab .module-logo-thumb-grid {
        object-fit: contain;    
    }
    .module-mailchimppro .top-logo {
        max-width: 60px;
    }
</style>
{literal}
<script>
    function regenerateProductsExtra(productId) {
        $.ajax({
            type: "POST",
            url: '{/literal}{$regenerateLink nofilter} {* HTML comment, no escape necessary *}{literal}',
            data: {
                action: 'syncProduct',
                productId: productId,
            }, success: function (response) {
                alert(response.result);
                // alert('Product synced');
            },
        });
    }
</script>{/literal}
