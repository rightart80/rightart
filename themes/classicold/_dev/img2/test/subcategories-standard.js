// /* File: themes/yourtheme/assets/js/dev/subcategories-standard.js */
// (function () {
//   "use strict";

//   const INITIAL_VISIBLE = 4;   // show first 4 when expanded
//   const CHUNK_SIZE = 8;        // reveal 8 per click

//   function initSubcategoriesBlock(root) {
//     if (!root) return;

//     // If this is a featured container, bail out (handled by subcategories-featured.js)
//     if (root.classList.contains("feature-category")) return;

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

//     if (container) {
//       initSubcategoriesBlock(container);
//     }
//   }

//   if (document.readyState === "loading") {
//     document.addEventListener("DOMContentLoaded", init);
//   } else {
//     init();
//   }
// })();



// /* File: themes/yourtheme/assets/js/dev/subcategories-standard.js */
// (function () {
//   "use strict";

//   const INITIAL_VISIBLE = 4;   // show first 4 when expanded
//   const CHUNK_SIZE = 8;        // reveal 8 per click

//   function initSubcategoriesBlock(root) {
//     if (!root) return;

//     // If this is a featured container, bail out (handled by subcategories-featured.js)
//     if (root.classList.contains("feature-category")) return;

//     const list = root.querySelector("#subcategories-list");
//     if (!list) return;

//     const items = Array.from(list.querySelectorAll("li.subcategory-item"));
//     if (!items.length) return;

//     const expander = root.querySelector("#subcategories-expander");
//     const heading = root.querySelector(".subcategory-heading"); // <h2 class="subcategory-heading">
//     const loadMoreWrapSel = ".subcategories-load-more-wrap";
//     const loadMoreBtnId = "load-more-subcategories";

//     // ----- helpers -----
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
//     // Collapsed: show 0 (kept as utility in case you need it elsewhere)
//     function collapseAll() {
//       items.forEach(function (li) {
//         li.classList.add("is-hidden");
//       });
//       setExpanderState(false);
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

//     // ----- click behavior -----
//     // 1) Expander click -> ALWAYS reset to first 4
//     if (expander && !expander._bound) {
//       expander.addEventListener("click", function (evt) {
//         // prevent heading's click handler from firing twice for same click
//         evt.stopPropagation();
//         expandToInitial();
//       });
//       expander._bound = true;
//     }

//     // 2) Clicking anywhere inside the <h2 class="subcategory-heading"> also resets to first 4
//     if (heading && !heading._bound) {
//       heading.addEventListener("click", function () {
//         expandToInitial();
//       });
//       heading._bound = true;
//     }

//     // ----- initial page state -----
//     // Start in the "initial" state: first 4 visible, load more ready
//     expandToInitial();
//   }

//   function init() {
//     const container =
//       document.querySelector("#subcategories.subcategories-container-parent") ||
//       document.querySelector("#subcategories.card.card-block") ||
//       document.querySelector("#subcategories");

//     if (container) {
//       initSubcategoriesBlock(container);
//     }
//   }

//   if (document.readyState === "loading") {
//     document.addEventListener("DOMContentLoaded", init);
//   } else {
//     init();
//   }
// })();


/* File: themes/yourtheme/assets/js/dev/subcategories-standard.js */
(function () {
  "use strict";

  const INITIAL_VISIBLE = 4;   // show first 4 when in "initial" mode
  const CHUNK_SIZE = 8;        // reveal 8 per click on "Load more"

  function initSubcategoriesBlock(root) {
    if (!root) return;

    // If this is a featured container, bail out (handled by subcategories-featured.js)
    if (root.classList.contains("feature-category")) return;

    const list = root.querySelector("#subcategories-list");
    if (!list) return;

    const items = Array.from(list.querySelectorAll("li.subcategory-item"));
    if (!items.length) return;

    const expander = root.querySelector("#subcategories-expander");
    // You can switch this to ".subcategory-heading-wrap" if you prefer wrapping div
    const heading = root.querySelector(".subcategory-heading");
    const loadMoreWrapSel = ".subcategories-load-more-wrap";
    const loadMoreBtnId = "load-more-subcategories";

    // ----- helpers -----
    // expanded=true means "FULLY expanded" (all visible)
    function setExpanderState(expanded) {
      if (!expander) return;
      expander.setAttribute("aria-expanded", expanded ? "true" : "false");
      expander.classList.toggle("opened", !!expanded);
    }

    function hiddenCount() {
      return items.reduce(function (n, li) {
        return n + (li.classList.contains("is-hidden") ? 1 : 0);
      }, 0);
    }

    // Show exactly first N, hide the rest
    function showFirstN(n) {
      items.forEach(function (li, i) {
        if (i < n) li.classList.remove("is-hidden");
        else li.classList.add("is-hidden");
      });
    }

    function showAll() {
      items.forEach(function (li) {
        li.classList.remove("is-hidden");
      });
    }

    // ----- states -----
    // "Initial" state: first 4 visible + load-more (if needed)
    function expandToInitial() {
      showFirstN(INITIAL_VISIBLE);
      setExpanderState(false); // not fully expanded
      ensureLoadMoreButton();
    }

    // "Full" state: all visible, no load more
    function expandToFull() {
      showAll();
      setExpanderState(true); // fully expanded
      removeOrHideLoadMore(true);
    }

    // Reveal next chunk of 8 (used by load more)
    function revealNextChunk() {
      const hidden = items.filter(function (li) {
        return li.classList.contains("is-hidden");
      });
      hidden.slice(0, CHUNK_SIZE).forEach(function (li) {
        li.classList.remove("is-hidden");
      });

      if (hiddenCount() === 0) {
        // all visible, hide the button
        removeOrHideLoadMore(true);
      } else {
        // still some hidden, keep button visible
        removeOrHideLoadMore(false);
      }
    }

    // ----- load more management -----
    function ensureLoadMoreButton() {
      // Only relevant if we have more than initial
      if (items.length <= INITIAL_VISIBLE) {
        removeOrHideLoadMore(true);
        return;
      }

      let wrap = root.querySelector(loadMoreWrapSel);
      let btn = root.querySelector("#" + loadMoreBtnId);

      // If Smarty didn’t render it, create it
      if (!btn) {
        if (!wrap) {
          wrap = document.createElement("div");
          wrap.className = "subcategories-load-more-wrap";
          root.appendChild(wrap);
        }
        btn = document.createElement("button");
        btn.type = "button";
        btn.id = loadMoreBtnId;
        btn.className = "btn btn-secondary";
        btn.textContent = "Load more";
        wrap.appendChild(btn);
      }

      // Show only if there are hidden items remaining
      const anyHidden = hiddenCount() > 0;
      wrap.style.display = anyHidden ? "" : "none";

      if (!btn._bound) {
        btn.addEventListener("click", revealNextChunk);
        btn._bound = true;
      }
    }

    function removeOrHideLoadMore(hide) {
      if (hide === void 0) hide = true;
      const wrap = root.querySelector(loadMoreWrapSel);
      if (wrap) wrap.style.display = hide ? "none" : "";
    }

    // ----- toggle behavior for expander + heading -----
    function toggleExpand() {
      const anyHidden = hiddenCount() > 0;

      if (anyHidden) {
        // Some are hidden -> go FULL (show all)
        expandToFull();
      } else {
        // All visible -> go back to INITIAL (first 4 + load more)
        expandToInitial();
      }
    }

    // 1) Expander click -> toggle initial <-> full
    if (expander && !expander._bound) {
      expander.addEventListener("click", function (evt) {
        evt.stopPropagation(); // so it doesn’t double-trigger if inside heading
        toggleExpand();
      });
      expander._bound = true;
    }

    // 2) Clicking the heading text also toggles initial <-> full
    if (heading && !heading._bound) {
      heading.addEventListener("click", function () {
        toggleExpand();
      });
      heading._bound = true;
    }

    // ----- initial page state -----
    // Start in the "initial" state: first 4 visible
    expandToInitial();
  }

  function init() {
    const container =
      document.querySelector("#subcategories.subcategories-container-parent") ||
      document.querySelector("#subcategories.card.card-block") ||
      document.querySelector("#subcategories");

    if (container) {
      initSubcategoriesBlock(container);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
