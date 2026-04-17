<!-- PROMO BAR -->
<div id="promoBar" class="promo-bar" role="region" aria-label="Store promotion">
  <button class="promo-bar__arrow" type="button" data-dir="-1" aria-label="Previous promotion">
    <svg viewBox="0 0 10 15" aria-hidden="true"><path d="M8.75.75 2.25 7.25 8.75 13.75" fill="none" stroke="currentColor" stroke-width="2"/></svg>
  </button>

  <div class="promo-bar__center">
    <span id="promoBarText" class="promo-bar__text">Loading promotion...</span>
    <button id="promoBarDetailsBtn" class="promo-bar__details" type="button" aria-haspopup="dialog" aria-controls="promoModal">
      Details
    </button>
  </div>

  <button class="promo-bar__arrow" type="button" data-dir="1" aria-label="Next promotion">
    <svg viewBox="0 0 10 15" aria-hidden="true"><path d="M1.25 13.75 7.75 7.25 1.25.75" fill="none" stroke="currentColor" stroke-width="2"/></svg>
  </button>
</div>

<!-- PROMO MODAL -->
<div id="promoModal" class="promo-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="promoModalTitle">
  <div class="promo-modal__backdrop" data-close></div>

  <div class="promo-modal__panel" role="document">
    <button class="promo-modal__close" type="button" data-close aria-label="Close">
      <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="m6 6 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </button>

    <h2 id="promoModalTitle" class="promo-modal__title">Promotion Details</h2>
    <div id="promoModalBody" class="promo-modal__body"></div>

    <div class="promo-modal__footer">
      <a id="promoModalCta" class="shop-button promo-modal__cta" href="/">
        SHOP NOW
      </a>
    </div>
  </div>
</div>
