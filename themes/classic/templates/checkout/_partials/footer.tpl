{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}


{* ////////////////////
  *}


<div class="container footer-before">
  <div class="row">
    {block name='hook_footer_before'}
      {hook h='displayFooterBefore'}
    {/block}
  </div>
</div>



<div class="footer  footer-container">
  <div class="container ra-container link-container">

    <div class="company-info">

      <a id="footer-logo" href="{$urls.base_url}" title="{l s='Back to home' d='Shop.Theme.Global'}">
        <img src="https://rh.rightart.co.uk/images/mini-logo.png" alt="Logo" id="footer-logo-img">
      </a>

      </a>
      <a class="footer-item-1" href="mailto:support@rightart.co.uk">
        support@rightart.co.uk
      </a>

      <span class="footer-item-2">
        © 2026 RightArt.
        <br>All rights reserved.
      </span>


    </div>

    {block name='hook_footer'}
      {hook h='displayFooter'}
    {/block}

    <a href="/login" class="signup-link">
    <div class="sign-up">
    <div class="footer-email-box">
      <p class="subscribe-header"><strong>Sign Up!</strong></p>
      <p class="email-info">Claim your Welcome Bonus now and save 10% On First Order instantly!</p>
    </div>
    </div>
    </a>

  </div>

  <div class="container">
    {block name='hook_footer_after'}
      {hook h='displayFooterAfter'}
    {/block}
  </div>
  {* <div class="row">
      <div class="col-md-12">
        <p class="text-sm-center">
          {block name='copyright_link'}
            <a href="https://www.prestashop-project.org/" target="_blank" rel="noopener noreferrer nofollow">
              {l s='%copyright% %year% - Ecommerce software by %prestashop%' sprintf=['%prestashop%' => 'PrestaShop™', '%year%' => 'Y'|date, '%copyright%' => '©'] d='Shop.Theme.Global'}
            </a>
          {/block}
        </p>
      </div>
    </div> *}


</div>
<div class="social-container">

  {include file='_partials/social-links.tpl'}


</div>