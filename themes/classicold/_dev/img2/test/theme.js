/**
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
 */
/* eslint-disable */
import "expose-loader?exposes=Tether!tether";
import "bootstrap/dist/js/bootstrap.min";
import "flexibility";
import "bootstrap-touchspin";
import "jquery-touchswipe";
import "./selectors";

import "./responsive";
import "./checkout";
import "./customer";
import "./listing";
import "./product";
import "./mosaic";
import "./cart";
import "./menue";
import "./codexai";
import "./subcategories-featured";
import "./subcategories-standard";

import prestashop from "prestashop";
import EventEmitter from "events";
import DropDown from "./components/drop-down";
import Form from "./components/form";
import usePasswordPolicy from "./components/usePasswordPolicy";
import ProductMinitature from "./components/product-miniature";
import ProductSelect from "./components/product-select";
import TopMenu from "./components/top-menu";

import "./lib/bootstrap-filestyle.min";
import "./lib/jquery.scrollbox.min";

import "./components/block-cart";
import $ from "jquery";
/* eslint-enable */

// "inherit" EventEmitter
// eslint-disable-next-line
for (const i in EventEmitter.prototype) {
  prestashop[i] = EventEmitter.prototype[i];
}

$(document).ready(() => {
  const dropDownEl = $(".js-dropdown");
  const form = new Form();
  const topMenuEl = $('.js-top-menu ul[data-depth="0"]');
  const dropDown = new DropDown(dropDownEl);
  const topMenu = new TopMenu(topMenuEl);
  const productMinitature = new ProductMinitature();
  const productSelect = new ProductSelect();
  dropDown.init();
  form.init();
  topMenu.init();
  productMinitature.init();
  productSelect.init();
  usePasswordPolicy(".field-password-policy");

  $('.carousel[data-touch="true"]').swipe({
    swipe(event, direction) {
      if (direction === "left") {
        $(this).carousel("next");
      }
      if (direction === "right") {
        $(this).carousel("prev");
      }
    },
    allowPageScroll: "vertical",
  });
});

//////////////filter fixed and bottom


document.addEventListener('DOMContentLoaded', function () {
  const sidebar = document.getElementById('left-col');
  if (!sidebar) return;

  const footer =
    document.getElementById('js-product-list-footer') ||
    document.querySelector('footer');

  const header =
    document.querySelector('.header-nav') ||
    document.querySelector('.header-top') ||
    document.querySelector('header');

  const headerHeight = header ? header.offsetHeight : 0;
  const BOTTOM_MARGIN = 16;   // gap above footer
  const HYSTERESIS = 12;      // band to stop flickering near footer

  const startRect = sidebar.getBoundingClientRect();
  const startY = startRect.top + window.scrollY;

  let lastState = ''; // '', 'fixed', 'bottom-fixed'
  let ticking = false;

  function setSidebarHeightForState(state) {
    if (state === 'fixed') {
      sidebar.style.height = window.innerHeight + 'px';
    } else {
      sidebar.style.height = ''; // reset to CSS/auto
    }
  }

  function applyState(nextState) {
    if (nextState === lastState) return;

    sidebar.classList.remove('fixed', 'bottom-fixed');

    if (nextState === 'fixed') {
      sidebar.classList.add('fixed');
    } else if (nextState === 'bottom-fixed') {
      sidebar.classList.add('bottom-fixed');
    }

    setSidebarHeightForState(nextState);
    lastState = nextState;
  }

  function update() {
    ticking = false;
    const scrollY = window.scrollY;

    // No footer: classic sticky under header
    if (!footer) {
      if (scrollY + headerHeight >= startY) {
        applyState('fixed');
      } else {
        applyState('');
      }
      return;
    }

    const footerRect = footer.getBoundingClientRect();
    const footerTopPage = footerRect.top + window.scrollY;

    // Use a *stable* virtual fixed height for logic
    // since visually we force full viewport height when fixed
    const fixedHeight = window.innerHeight;
    const fixedTop = scrollY + headerHeight;
    const fixedBottom = fixedTop + fixedHeight;

    const limitForFixed = footerTopPage - BOTTOM_MARGIN;

    let nextState = lastState;

    // --- Bottom hysteresis zone ---
    if (fixedBottom >= limitForFixed + HYSTERESIS) {
      // clearly overlapping → go/stay bottom-fixed
      nextState = 'bottom-fixed';
    } else if (fixedBottom <= limitForFixed - HYSTERESIS) {
      // clearly away from footer → can be normal or fixed
      if (fixedTop <= startY) {
        nextState = '';
      } else {
        nextState = 'fixed';
      }
    } else {
      // inside hysteresis band → keep current state (no flip)
      nextState = lastState;
    }

    applyState(nextState);
  }

  function onScrollOrResize() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(update);
  }

  window.addEventListener('scroll', onScrollOrResize, { passive: true });
  window.addEventListener('resize', function () {
    if (lastState === 'fixed') {
      sidebar.style.height = window.innerHeight + 'px';
    }
    onScrollOrResize();
  });

  // Initial run
  update();
});


//////
document.addEventListener("DOMContentLoaded", function () {
  const desc = document.getElementById("description-canvas");
  const btn = document.getElementById("read-more-toggle");
  if (!desc || !btn) return;

  const originalHeight = desc.scrollHeight;
  desc.classList.add("collapsed");

  setTimeout(() => {
    const collapsedHeight = desc.clientHeight;
    if (originalHeight > collapsedHeight + 10) {
      btn.style.display = "inline-block";
    }
  }, 100);

  btn.addEventListener("click", function () {
    desc.classList.toggle("collapsed");
    btn.textContent = desc.classList.contains("collapsed")
      ? "Read more"
      : "Read less";
  });
});


/////////// load more subcategories and expander

// /* File: themes/yourtheme/assets/js/dev/subcategories.js */
// (function () {
//   "use strict";

//   const INITIAL_VISIBLE = 4;   // default: show first 4 when expanded (non-featured)
//   const CHUNK_SIZE = 8;        // reveal 8 per click

//   /**
//    * FEATURED CATEGORY: responsive behavior
//    * - Desktop: load more + expander, initial 6 items
//    * - Tablet/mobile: slider (2 or 1 item per page) with prev/next
//    */
//   function initFeaturedResponsiveBlock(root, items, expander, loadMoreWrapSel, loadMoreBtnId) {
//     if (!items.length) return;

//     const DESKTOP_INITIAL_VISIBLE = 3;

//     const sliderNavClass = "featured-subcategories-slider-nav";
//     const prevBtnId = "featured-subcategories-prev";
//     const nextBtnId = "featured-subcategories-next";

//     let sliderNavWrap = root.querySelector("." + sliderNavClass);
//     let sliderPrevBtn = root.querySelector("#" + prevBtnId);
//     let sliderNextBtn = root.querySelector("#" + nextBtnId);

//     let currentMode = null; // "desktop" | "tablet" | "mobile"
//     let currentStart = 0;

//     // ----- shared helpers -----
//     function getMode() {
//       const w = window.innerWidth || document.documentElement.clientWidth;
//       if (w <= 576) return "mobile";   // 1 per page
//       if (w <= 992) return "tablet";   // 2 per page
//       return "desktop";                // load more, 6 initial
//     }

//     function setExpanderState(expanded) {
//       if (!expander) return;
//       expander.setAttribute("aria-expanded", expanded ? "true" : "false");
//       expander.classList.toggle("opened", !!expanded);
//     }

//     function hiddenCount() {
//       return items.reduce((n, li) => n + (li.classList.contains("is-hidden") ? 1 : 0), 0);
//     }

//     function showFirstN(n) {
//       items.forEach((li, i) => {
//         if (i < n) li.classList.remove("is-hidden");
//         else li.classList.add("is-hidden");
//       });
//     }

//     function removeOrHideLoadMore(hide = true) {
//       const wrap = root.querySelector(loadMoreWrapSel);
//       if (wrap) wrap.style.display = hide ? "none" : "";
//     }

//     // ----- DESKTOP: load-more logic -----
//     function ensureLoadMoreButtonDesktop() {
//       // Only relevant if we have more than DESKTOP_INITIAL_VISIBLE
//       if (items.length <= DESKTOP_INITIAL_VISIBLE) {
//         removeOrHideLoadMore(true);
//         return;
//       }

//       let wrap = root.querySelector(loadMoreWrapSel);
//       let btn = root.querySelector("#" + loadMoreBtnId);

//       // If Smarty didn’t render it, create it
//       if (!btn) {
//         if (!wrap) {
//           wrap = document.createElement("div");
//           wrap.className = "subcategories-load-more-wrap";
//           root.appendChild(wrap);
//         }
//         btn = document.createElement("button");
//         btn.type = "button";
//         btn.id = loadMoreBtnId;
//         btn.className = "btn btn-secondary";
//         btn.textContent = "Load more";
//         wrap.appendChild(btn);
//       }

//       // Show only if there are hidden items remaining
//       const anyHidden = hiddenCount() > 0;
//       wrap.style.display = anyHidden ? "" : "none";

//       if (!btn._bound) {
//         btn.addEventListener("click", revealNextChunkDesktop);
//         btn._bound = true;
//       }
//     }

//     function collapseAllDesktop() {
//       items.forEach(li => li.classList.add("is-hidden"));
//       setExpanderState(false);
//       removeOrHideLoadMore(true);
//     }

//     function expandToInitialDesktop() {
//       showFirstN(DESKTOP_INITIAL_VISIBLE);
//       setExpanderState(true);
//       ensureLoadMoreButtonDesktop();
//     }

//     function revealNextChunkDesktop() {
//       const hidden = items.filter(li => li.classList.contains("is-hidden"));
//       hidden.slice(0, CHUNK_SIZE).forEach(li => li.classList.remove("is-hidden"));

//       if (hiddenCount() === 0) {
//         removeOrHideLoadMore(true);
//       } else {
//         removeOrHideLoadMore(false);
//       }
//     }

//     // ----- SLIDER: tablet/mobile -----
//     function getPageSizeForMode(mode) {
//       if (mode === "mobile") return 1;   // ≤576px: 1 per page
//       if (mode === "tablet") return 2;   // 577–992px: 2 per page
//       return DESKTOP_INITIAL_VISIBLE;    // not really used in slider
//     }

//     function clampStart(pageSize) {
//       const total = items.length;
//       const maxStart = Math.max(0, total - pageSize);
//       if (currentStart > maxStart) currentStart = maxStart;
//       if (currentStart < 0) currentStart = 0;
//       return maxStart;
//     }

//     function ensureSliderNav() {
//       if (!sliderNavWrap) {
//         sliderNavWrap = document.createElement("div");
//         sliderNavWrap.className = sliderNavClass;
//         root.appendChild(sliderNavWrap);
//       }

//       if (!sliderPrevBtn) {
//         sliderPrevBtn = document.createElement("button");
//         sliderPrevBtn.type = "button";
//         sliderPrevBtn.id = prevBtnId;
//         sliderPrevBtn.className = "btn btn-secondary featured-subcategories-prev";
//         sliderPrevBtn.textContent = "‹";
//         sliderNavWrap.appendChild(sliderPrevBtn);
//       }

//       if (!sliderNextBtn) {
//         sliderNextBtn = document.createElement("button");
//         sliderNextBtn.type = "button";
//         sliderNextBtn.id = nextBtnId;
//         sliderNextBtn.className = "btn btn-secondary featured-subcategories-next";
//         sliderNextBtn.textContent = "›";
//         sliderNavWrap.appendChild(sliderNextBtn);
//       }

//       if (!sliderPrevBtn._bound) {
//         sliderPrevBtn.addEventListener("click", goPrev);
//         sliderPrevBtn._bound = true;
//       }
//       if (!sliderNextBtn._bound) {
//         sliderNextBtn.addEventListener("click", goNext);
//         sliderNextBtn._bound = true;
//       }
//     }

//     function updateSliderVisible() {
//       const mode = currentMode || getMode();
//       const pageSize = getPageSizeForMode(mode);
//       const total = items.length;

//       const maxStart = clampStart(pageSize);

//       items.forEach((li, idx) => {
//         if (idx >= currentStart && idx < currentStart + pageSize) {
//           li.classList.remove("is-hidden");
//         } else {
//           li.classList.add("is-hidden");
//         }
//       });

//       if (sliderNavWrap) {
//         sliderNavWrap.style.display = total > pageSize ? "" : "none";
//       }
//       if (sliderPrevBtn) {
//         sliderPrevBtn.disabled = currentStart === 0;
//       }
//       if (sliderNextBtn) {
//         sliderNextBtn.disabled = currentStart >= maxStart;
//       }
//     }

//     function goNext() {
//       const pageSize = getPageSizeForMode(currentMode || getMode());
//       const total = items.length;
//       const maxStart = Math.max(0, total - pageSize);

//       currentStart = Math.min(currentStart + pageSize, maxStart);
//       updateSliderVisible();
//     }

//     function goPrev() {
//       const pageSize = getPageSizeForMode(currentMode || getMode());
//       currentStart = Math.max(currentStart - pageSize, 0);
//       updateSliderVisible();
//     }

//     // ----- mode switching -----
//     function applyMode(newMode) {
//       currentMode = newMode;

//       if (newMode === "desktop") {
//         // show desktop elements
//         if (expander) {
//           expander.style.display = "";
//         }
//         const loadMoreWrap = root.querySelector(loadMoreWrapSel);
//         if (loadMoreWrap) {
//           loadMoreWrap.style.display = "";
//         }

//         // hide slider nav
//         if (sliderNavWrap) {
//           sliderNavWrap.style.display = "none";
//         }

//         // bind expander only once
//         if (expander && !expander._featuredBound) {
//           expander.addEventListener("click", function () {
//             const expanded = expander.getAttribute("aria-expanded") === "true";
//             if (expanded) {
//               collapseAllDesktop();
//             } else {
//               expandToInitialDesktop();
//             }
//           });
//           expander._featuredBound = true;
//         }

//         // initial desktop state
//         expandToInitialDesktop();
//       } else {
//         // tablet/mobile: slider
//         // hide desktop-only UI
//         if (expander) {
//           expander.style.display = "none";
//         }
//         const loadMoreWrap = root.querySelector(loadMoreWrapSel);
//         if (loadMoreWrap) {
//           loadMoreWrap.style.display = "none";
//         }

//         // ensure slider UI and show it
//         ensureSliderNav();
//         if (sliderNavWrap) {
//           sliderNavWrap.style.display = "";
//         }

//         currentStart = 0;
//         updateSliderVisible();
//       }
//     }

//     function handleResize() {
//       const newMode = getMode();
//       if (newMode !== currentMode) {
//         applyMode(newMode);
//       } else if (currentMode !== "desktop") {
//         // still in slider mode, just re-apply visibility in case size changed a bit
//         updateSliderVisible();
//       }
//     }

//     // initial mode
//     applyMode(getMode());

//     // respond to window resizing
//     let resizeTimer = null;
//     window.addEventListener("resize", function () {
//       clearTimeout(resizeTimer);
//       resizeTimer = setTimeout(handleResize, 150);
//     });
//   }

//   function initSubcategoriesBlock(root) {
//     if (!root) return;

//     const list = root.querySelector("#subcategories-list");
//     if (!list) return;

//     const items = Array.from(list.querySelectorAll("li.subcategory-item"));
//     if (!items.length) return;

//     const expander = root.querySelector("#subcategories-expander");
//     const loadMoreWrapSel = ".subcategories-load-more-wrap";
//     const loadMoreBtnId = "load-more-subcategories";

//     const isFeatured =
//       root.id === "subcategories" && root.classList.contains("feature-category");

//     // ----- FEATURED CATEGORY: special responsive behavior -----
//     if (isFeatured) {
//       initFeaturedResponsiveBlock(root, items, expander, loadMoreWrapSel, loadMoreBtnId);
//       return;
//     }

//     // ----- ORIGINAL LOGIC for non-featured -----

//     function setExpanderState(expanded) {
//       if (!expander) return;
//       expander.setAttribute("aria-expanded", expanded ? "true" : "false");
//       expander.classList.toggle("opened", !!expanded);
//     }

//     function hiddenCount() {
//       return items.reduce((n, li) => n + (li.classList.contains("is-hidden") ? 1 : 0), 0);
//     }

//     // Show exactly first N, hide the rest
//     function showFirstN(n) {
//       items.forEach((li, i) => {
//         if (i < n) li.classList.remove("is-hidden");
//         else li.classList.add("is-hidden");
//       });
//     }

//     // Collapsed: show 0
//     function collapseAll() {
//       items.forEach(li => li.classList.add("is-hidden"));
//       setExpanderState(false);
//       // When fully collapsed, hide Load more (nothing visible to extend)
//       removeOrHideLoadMore(true);
//     }

//     // Expanded (reset): show first 4 and prepare load-more
//     function expandToInitial() {
//       showFirstN(INITIAL_VISIBLE);
//       setExpanderState(true);
//       ensureLoadMoreButton(); // will show only if there are >4
//     }

//     // Reveal next chunk of 8
//     function revealNextChunk() {
//       const hidden = items.filter(li => li.classList.contains("is-hidden"));
//       hidden.slice(0, CHUNK_SIZE).forEach(li => li.classList.remove("is-hidden"));

//       if (hiddenCount() === 0) {
//         // all visible, hide the button
//         removeOrHideLoadMore(true);
//       } else {
//         // still some hidden, keep button visible
//         removeOrHideLoadMore(false);
//       }
//     }

//     // ----- load more management -----
//     function ensureLoadMoreButton() {
//       // Only relevant if we have more than initial
//       if (items.length <= INITIAL_VISIBLE) {
//         removeOrHideLoadMore(true);
//         return;
//       }

//       let wrap = root.querySelector(loadMoreWrapSel);
//       let btn = root.querySelector("#" + loadMoreBtnId);

//       // If Smarty didn’t render it, create it
//       if (!btn) {
//         if (!wrap) {
//           wrap = document.createElement("div");
//           wrap.className = "subcategories-load-more-wrap";
//           root.appendChild(wrap);
//         }
//         btn = document.createElement("button");
//         btn.type = "button";
//         btn.id = loadMoreBtnId;
//         btn.className = "btn btn-secondary";
//         btn.textContent = "Load more";
//         wrap.appendChild(btn);
//       }

//       // Show only if there are hidden items remaining
//       const anyHidden = hiddenCount() > 0;
//       wrap.style.display = anyHidden ? "" : "none";

//       if (!btn._bound) {
//         btn.addEventListener("click", revealNextChunk);
//         btn._bound = true;
//       }
//     }

//     function removeOrHideLoadMore(hide = true) {
//       const wrap = root.querySelector(loadMoreWrapSel);
//       if (wrap) wrap.style.display = hide ? "none" : "";
//     }

//     // ----- expander behavior (toggle collapse ↔ initial 4) -----
//     if (expander && !expander._bound) {
//       expander.addEventListener("click", function () {
//         const expanded = expander.getAttribute("aria-expanded") === "true";
//         if (expanded) {
//           // from any expanded state (even after many "load more" clicks) -> collapse to 0
//           collapseAll();
//         } else {
//           // from collapsed -> expand back to INITIAL_VISIBLE (4)
//           expandToInitial();
//         }
//       });
//       expander._bound = true;
//     }

//     // ----- initial page state -----
//     // Start expanded to first 4
//     expandToInitial();
//   }

//   function init() {
//     const container =
//       document.querySelector("#subcategories.subcategories-container-parent") ||
//       document.querySelector("#subcategories.card.card-block") ||
//       document.querySelector("#subcategories");
//     if (container) initSubcategoriesBlock(container);
//   }

//   if (document.readyState === "loading") {
//     document.addEventListener("DOMContentLoaded", init);
//   } else {
//     init();
//   }
// })();




// orignal

// /* File: themes/yourtheme/assets/js/dev/subcategories.js */
// (function () {
//   "use strict";

//   const INITIAL_VISIBLE = 4;   // show first 4 when expanded
//   const CHUNK_SIZE = 8;        // reveal 8 per click

//   function initSubcategoriesBlock(root) {
//     if (!root) return;

//     const list = root.querySelector("#subcategories-list");
//     if (!list) return;

//     const items = Array.from(list.querySelectorAll("li.subcategory-item"));
//     if (!items.length) return;

//     const expander = root.querySelector("#subcategories-expander");
//     const loadMoreWrapSel = ".subcategories-load-more-wrap";
//     const loadMoreBtnId = "load-more-subcategories";

//     // ----- helpers -----
//     function setExpanderState(expanded) {
//       if (!expander) return;
//       expander.setAttribute("aria-expanded", expanded ? "true" : "false");
//       expander.classList.toggle("opened", !!expanded);
//     }

//     function hiddenCount() {
//       return items.reduce((n, li) => n + (li.classList.contains("is-hidden") ? 1 : 0), 0);
//     }

//     // Show exactly first N, hide the rest
//     function showFirstN(n) {
//       items.forEach((li, i) => {
//         if (i < n) li.classList.remove("is-hidden");
//         else li.classList.add("is-hidden");
//       });
//     }

//     // ----- states -----
//     // Collapsed: show 0
//     function collapseAll() {
//       items.forEach(li => li.classList.add("is-hidden"));
//       setExpanderState(false);
//       // When fully collapsed, hide Load more (nothing visible to extend)
//       removeOrHideLoadMore(true);
//     }

//     // Expanded (reset): show first 4 and prepare load-more
//     function expandToInitial() {
//       showFirstN(INITIAL_VISIBLE);
//       setExpanderState(true);
//       ensureLoadMoreButton(); // will show only if there are >4
//     }

//     // Reveal next chunk of 8
//     function revealNextChunk() {
//       const hidden = items.filter(li => li.classList.contains("is-hidden"));
//       hidden.slice(0, CHUNK_SIZE).forEach(li => li.classList.remove("is-hidden"));

//       if (hiddenCount() === 0) {
//         // all visible, hide the button
//         removeOrHideLoadMore(true);
//       } else {
//         // still some hidden, keep button visible
//         removeOrHideLoadMore(false);
//       }
//     }

//     // ----- load more management -----
//     function ensureLoadMoreButton() {
//       // Only relevant if we have more than initial
//       if (items.length <= INITIAL_VISIBLE) {
//         removeOrHideLoadMore(true);
//         return;
//       }

//       let wrap = root.querySelector(loadMoreWrapSel);
//       let btn = root.querySelector("#" + loadMoreBtnId);

//       // If Smarty didn’t render it, create it
//       if (!btn) {
//         if (!wrap) {
//           wrap = document.createElement("div");
//           wrap.className = "subcategories-load-more-wrap";
//           root.appendChild(wrap);
//         }
//         btn = document.createElement("button");
//         btn.type = "button";
//         btn.id = loadMoreBtnId;
//         btn.className = "btn btn-secondary";
//         btn.textContent = "Load more";
//         wrap.appendChild(btn);
//       }

//       // Show only if there are hidden items remaining
//       const anyHidden = hiddenCount() > 0;
//       wrap.style.display = anyHidden ? "" : "none";

//       if (!btn._bound) {
//         btn.addEventListener("click", revealNextChunk);
//         btn._bound = true;
//       }
//     }

//     function removeOrHideLoadMore(hide = true) {
//       const wrap = root.querySelector(loadMoreWrapSel);
//       if (wrap) wrap.style.display = hide ? "none" : "";
//     }

//     // ----- expander behavior (toggle collapse ↔ initial 4) -----
//     if (expander && !expander._bound) {
//       expander.addEventListener("click", function () {
//         const expanded = expander.getAttribute("aria-expanded") === "true";
//         if (expanded) {
//           // from any expanded state (even after many "load more" clicks) -> collapse to 0
//           collapseAll();
//         } else {
//           // from collapsed -> expand back to INITIAL_VISIBLE (4)
//           expandToInitial();
//         }
//       });
//       expander._bound = true;
//     }

//     // ----- initial page state -----
//     // Start expanded to first 4 (you can switch to collapseAll() if you want default closed)
//     expandToInitial();
//   }

//   function init() {
//     const container =
//       document.querySelector("#subcategories.subcategories-container-parent") ||
//       document.querySelector("#subcategories.card.card-block") ||
//       document.querySelector("#subcategories");
//     if (container) initSubcategoriesBlock(container);
//   }

//   if (document.readyState === "loading") {
//     document.addEventListener("DOMContentLoaded", init);
//   } else {
//     init();
//   }
// })();



// slider for featured subcategories
// working
// /* File: themes/yourtheme/assets/js/dev/subcategories.js */
// (function () {
//   "use strict";

//   const INITIAL_VISIBLE = 4;   // show first 4 when expanded (non-featured)
//   const CHUNK_SIZE = 8;        // reveal 8 per click (non-featured)

//   function initFeaturedSlider(root, items) {
//     if (!items.length) return;

//     // optional: hide existing expander / load more if Smarty rendered them
//     const oldExpander = root.querySelector("#subcategories-expander");
//     const oldLoadMoreWrap = root.querySelector(".subcategories-load-more-wrap");
//     if (oldExpander) oldExpander.style.display = "none";
//     if (oldLoadMoreWrap) oldLoadMoreWrap.style.display = "none";

//     const navWrapClass = "featured-subcategories-slider-nav";
//     const prevBtnId = "featured-subcategories-prev";
//     const nextBtnId = "featured-subcategories-next";

//     let currentStart = 0; // index of first visible item
//     let navWrap = root.querySelector("." + navWrapClass);
//     let prevBtn = root.querySelector("#" + prevBtnId);
//     let nextBtn = root.querySelector("#" + nextBtnId);

//     // --- helpers ---

//     function getPageSize() {
//       const w = window.innerWidth || document.documentElement.clientWidth;
//       if (w <= 576) return 1;   // mobile: 1 per slide
//       if (w <= 992) return 2;   // tablet: 2 per slide
//       return 3;                 // desktop: 6 per slide
//     }

//     function clampStart(pageSize) {
//       const total = items.length;
//       const maxStart = Math.max(0, total - pageSize);
//       if (currentStart > maxStart) currentStart = maxStart;
//       if (currentStart < 0) currentStart = 0;
//       return maxStart;
//     }

//     function updateVisible() {
//       const pageSize = getPageSize();
//       const total = items.length;

//       const maxStart = clampStart(pageSize);

//       items.forEach(function (li, idx) {
//         if (idx >= currentStart && idx < currentStart + pageSize) {
//           li.classList.remove("is-hidden");
//         } else {
//           li.classList.add("is-hidden");
//         }
//       });

//       // show / hide nav based on total vs pageSize
//       if (navWrap) {
//         navWrap.style.display = total > pageSize ? "" : "none";
//       }

//       // update disabled state
//       if (prevBtn) {
//         prevBtn.disabled = currentStart === 0;
//       }
//       if (nextBtn) {
//         nextBtn.disabled = currentStart >= maxStart;
//       }
//     }

//     function goNext() {
//       const pageSize = getPageSize();
//       const total = items.length;
//       const maxStart = Math.max(0, total - pageSize);

//       currentStart = Math.min(currentStart + pageSize, maxStart);
//       updateVisible();
//     }

//     function goPrev() {
//       const pageSize = getPageSize();
//       currentStart = Math.max(currentStart - pageSize, 0);
//       updateVisible();
//     }

//     // --- ensure nav buttons exist ---

//     if (!navWrap) {
//       navWrap = document.createElement("div");
//       navWrap.className = navWrapClass;
//       root.appendChild(navWrap);
//     }

//     if (!prevBtn) {
//       prevBtn = document.createElement("button");
//       prevBtn.type = "button";
//       prevBtn.id = prevBtnId;
//       prevBtn.className = "btn btn-secondary featured-subcategories-prev";
//       prevBtn.textContent = "‹";
//       navWrap.appendChild(prevBtn);
//     }

//     if (!nextBtn) {
//       nextBtn = document.createElement("button");
//       nextBtn.type = "button";
//       nextBtn.id = nextBtnId;
//       nextBtn.className = "btn btn-secondary featured-subcategories-next";
//       nextBtn.textContent = "›";
//       navWrap.appendChild(nextBtn);
//     }

//     if (!prevBtn._bound) {
//       prevBtn.addEventListener("click", goPrev);
//       prevBtn._bound = true;
//     }
//     if (!nextBtn._bound) {
//       nextBtn.addEventListener("click", goNext);
//       nextBtn._bound = true;
//     }

//     // respond to resize so pageSize (6/2/1) updates correctly
//     let resizeTimer = null;
//     window.addEventListener("resize", function () {
//       clearTimeout(resizeTimer);
//       resizeTimer = setTimeout(updateVisible, 150);
//     });

//     // initial state
//     currentStart = 0;
//     updateVisible();
//   }

//   function initSubcategoriesBlock(root) {
//     if (!root) return;

//     const list = root.querySelector("#subcategories-list");
//     if (!list) return;

//     const items = Array.from(list.querySelectorAll("li.subcategory-item"));
//     if (!items.length) return;

//     const expander = root.querySelector("#subcategories-expander");
//     const loadMoreWrapSel = ".subcategories-load-more-wrap";
//     const loadMoreBtnId = "load-more-subcategories";

//     const isFeatured = root.id === "subcategories" && root.classList.contains("feature-category");

//     // ----- SPECIAL CASE: featured slider -----
//     if (isFeatured) {
//       initFeaturedSlider(root, items);
//       return; // skip normal expand/load-more logic
//     }

//     // ----- helpers for normal block -----
//     function setExpanderState(expanded) {
//       if (!expander) return;
//       expander.setAttribute("aria-expanded", expanded ? "true" : "false");
//       expander.classList.toggle("opened", !!expanded);
//     }

//     function hiddenCount() {
//       return items.reduce(function (n, li) {
//         return n + (li.classList.contains("is-hidden") ? 1 : 0);
//       }, 0);
//     }

//     // Show exactly first N, hide the rest
//     function showFirstN(n) {
//       items.forEach(function (li, i) {
//         if (i < n) li.classList.remove("is-hidden");
//         else li.classList.add("is-hidden");
//       });
//     }

//     // ----- states -----
//     // Collapsed: show 0
//     function collapseAll() {
//       items.forEach(function (li) {
//         li.classList.add("is-hidden");
//       });
//       setExpanderState(false);
//       // When fully collapsed, hide Load more (nothing visible to extend)
//       removeOrHideLoadMore(true);
//     }

//     // Expanded (reset): show first 4 and prepare load-more
//     function expandToInitial() {
//       showFirstN(INITIAL_VISIBLE);
//       setExpanderState(true);
//       ensureLoadMoreButton(); // will show only if there are >4
//     }

//     // Reveal next chunk of 8
//     function revealNextChunk() {
//       const hidden = items.filter(function (li) {
//         return li.classList.contains("is-hidden");
//       });
//       hidden.slice(0, CHUNK_SIZE).forEach(function (li) {
//         li.classList.remove("is-hidden");
//       });

//       if (hiddenCount() === 0) {
//         // all visible, hide the button
//         removeOrHideLoadMore(true);
//       } else {
//         // still some hidden, keep button visible
//         removeOrHideLoadMore(false);
//       }
//     }

//     // ----- load more management -----
//     function ensureLoadMoreButton() {
//       // Only relevant if we have more than initial
//       if (items.length <= INITIAL_VISIBLE) {
//         removeOrHideLoadMore(true);
//         return;
//       }

//       let wrap = root.querySelector(loadMoreWrapSel);
//       let btn = root.querySelector("#" + loadMoreBtnId);

//       // If Smarty didn’t render it, create it
//       if (!btn) {
//         if (!wrap) {
//           wrap = document.createElement("div");
//           wrap.className = "subcategories-load-more-wrap";
//           root.appendChild(wrap);
//         }
//         btn = document.createElement("button");
//         btn.type = "button";
//         btn.id = loadMoreBtnId;
//         btn.className = "btn btn-secondary";
//         btn.textContent = "Load more";
//         wrap.appendChild(btn);
//       }

//       // Show only if there are hidden items remaining
//       const anyHidden = hiddenCount() > 0;
//       wrap.style.display = anyHidden ? "" : "none";

//       if (!btn._bound) {
//         btn.addEventListener("click", revealNextChunk);
//         btn._bound = true;
//       }
//     }

//     function removeOrHideLoadMore(hide) {
//       if (hide === void 0) hide = true;
//       const wrap = root.querySelector(loadMoreWrapSel);
//       if (wrap) wrap.style.display = hide ? "none" : "";
//     }

//     // ----- expander behavior (toggle collapse ↔ initial 4) -----
//     if (expander && !expander._bound) {
//       expander.addEventListener("click", function () {
//         const expanded = expander.getAttribute("aria-expanded") === "true";
//         if (expanded) {
//           // from any expanded state (even after many "load more" clicks) -> collapse to 0
//           collapseAll();
//         } else {
//           // from collapsed -> expand back to INITIAL_VISIBLE (4)
//           expandToInitial();
//         }
//       });
//       expander._bound = true;
//     }

//     // ----- initial page state -----
//     // Start expanded to first 4 (you can switch to collapseAll() if you want default closed)
//     expandToInitial();
//   }

//   function init() {
//     const container =
//       document.querySelector("#subcategories.subcategories-container-parent") ||
//       document.querySelector("#subcategories.card.card-block") ||
//       document.querySelector("#subcategories");
//     if (container) initSubcategoriesBlock(container);
//   }

//   if (document.readyState === "loading") {
//     document.addEventListener("DOMContentLoaded", init);
//   } else {
//     init();
//   }
// })();
