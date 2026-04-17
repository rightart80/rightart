(function () {
  function ready(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else {
      fn();
    }
  }

  ready(function () {
    var data = window.PROMOBAR_DATA || {};
    var promos = Array.isArray(data.promos) ? data.promos : [];
    var height = Number(data.height || 40);

    if (!promos.length) return;

    // set CSS variable (bar height + body padding)
    document.documentElement.style.setProperty("--promo-bar-height", height + "px");

    var index = 0;
    var lastFocus = null;

    var bar = document.getElementById("promoBar");
    var textEl = document.getElementById("promoBarText");
    var detailsBtn = document.getElementById("promoBarDetailsBtn");
    var headerContainer = document.getElementById("header-container");

    var modal = document.getElementById("promoModal");
    var modalTitle = document.getElementById("promoModalTitle");
    var modalBody = document.getElementById("promoModalBody");
    var modalCta = document.getElementById("promoModalCta");

    if (!bar || !textEl || !detailsBtn || !modal || !modalBody || !modalTitle || !modalCta) return;

    var arrows = bar.querySelectorAll("[data-dir]");

    function syncPromoBarWithStickyHeader() {
      if (!headerContainer || !document.body) return;
      if (headerContainer.classList.contains("is-sticky")) {
        document.body.classList.add("promo-bar-hidden-on-sticky");
      } else {
        document.body.classList.remove("promo-bar-hidden-on-sticky");
      }
    }

    if (headerContainer && typeof MutationObserver !== "undefined") {
      var stickyObserver = new MutationObserver(syncPromoBarWithStickyHeader);
      stickyObserver.observe(headerContainer, {
        attributes: true,
        attributeFilter: ["class"],
      });
      window.addEventListener("resize", syncPromoBarWithStickyHeader);
      syncPromoBarWithStickyHeader();
    }

    function renderPromo() {
      var p = promos[index] || {};

      textEl.textContent = p.text || "";

      modalTitle.textContent = p.title || "Promotion Details";
      modalBody.innerHTML = p.detailsHtml || "";

      modalCta.href = p.ctaUrl || "/";
      modalCta.textContent = p.ctaLabel || "SHOP NOW";

      detailsBtn.setAttribute("aria-label", "Details: " + (p.title || p.text || "Promotion"));
    }

    function openModal() {
      lastFocus = document.activeElement;
      modal.classList.add("is-open");
      modal.setAttribute("aria-hidden", "false");
      document.documentElement.style.overflow = "hidden";

      var closeBtn = modal.querySelector("[data-close]");
      if (closeBtn && closeBtn.focus) closeBtn.focus();
    }

    function closeModal() {
      modal.classList.remove("is-open");
      modal.setAttribute("aria-hidden", "true");
      document.documentElement.style.overflow = "";

      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    // Hide arrows if only one promo
    if (promos.length < 2) {
      arrows.forEach(function (b) { b.style.display = "none"; });
    }

    // Arrows: rotate promos
    arrows.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var dir = Number(btn.getAttribute("data-dir"));
        index = (index + dir + promos.length) % promos.length;
        renderPromo();
      });
    });

    // Details opens modal (for current promo)
    detailsBtn.addEventListener("click", openModal);

    // Close modal (backdrop + close button) - uses closest() so SVG clicks work
    modal.addEventListener("click", function (e) {
      var t = e.target;
      if (t && t.closest && t.closest("[data-close]")) closeModal();
    });

    // ESC closes modal
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && modal.classList.contains("is-open")) closeModal();
    });

    renderPromo();
  });
})();
