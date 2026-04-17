// /* File: themes/yourtheme/assets/js/dev/subcategories-featured.js */
// (function () {
//   "use strict";

//   function initFeaturedSlider(root, items) {
//     if (!items.length) return;

//     // Hide old expander / load more if Smarty rendered them
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
//       return 3;                 // desktop: 3 per slide
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

//     // respond to resize so pageSize updates correctly
//     let resizeTimer = null;
//     window.addEventListener("resize", function () {
//       clearTimeout(resizeTimer);
//       resizeTimer = setTimeout(updateVisible, 150);
//     });

//     // initial state
//     currentStart = 0;
//     updateVisible();
//   }

//   function initFeaturedSubcategories() {
//     // Grab ALL blocks that match your existing selectors
//     const roots = document.querySelectorAll(
//       "#subcategories.subcategories-container-parent.feature-category, " +
//       "#subcategories.card.card-block.feature-category, " +
//       "#subcategories.feature-category"
//     );

//     if (!roots.length) return;

//     roots.forEach(function (root) {
//       const list = root.querySelector("#subcategories-list");
//       if (!list) return;

//       const items = Array.from(list.querySelectorAll("li.subcategory-item"));
//       if (!items.length) return;

//       initFeaturedSlider(root, items);
//     });
//   }

//   function init() {
//     initFeaturedSubcategories();
//   }

//   if (document.readyState === "loading") {
//     document.addEventListener("DOMContentLoaded", init);
//   } else {
//     init();
//   }
// })();



/* modules/ps_featuredsubcategories/views/js/subcategories-featured.js */
(function () {
  "use strict";

  function initFeaturedSlider(root, items) {
    if (!items.length) return;

    // Hide old expander / load more if Smarty rendered them inside this block
    const oldExpander = root.querySelector("#subcategories-expander");
    const oldLoadMoreWrap = root.querySelector(".subcategories-load-more-wrap");
    if (oldExpander) oldExpander.style.display = "none";
    if (oldLoadMoreWrap) oldLoadMoreWrap.style.display = "none";

    const navWrapClass = "featured-subcategories-slider-nav";
    const prevBtnClass = "featured-subcategories-prev";
    const nextBtnClass = "featured-subcategories-next";

    let currentStart = 0; // index of first visible item
    let navWrap = root.querySelector("." + navWrapClass);
    let prevBtn = root.querySelector("." + prevBtnClass);
    let nextBtn = root.querySelector("." + nextBtnClass);

    // --- helpers ---

    function getPageSize() {
      const w = window.innerWidth || document.documentElement.clientWidth;
      if (w <= 576) return 1;   // mobile: 1 per slide
      if (w <= 992) return 2;   // tablet: 2 per slide
      return 3;                 // desktop: 3 per slide
    }

    function clampStart(pageSize) {
      const total = items.length;
      const maxStart = Math.max(0, total - pageSize);
      if (currentStart > maxStart) currentStart = maxStart;
      if (currentStart < 0) currentStart = 0;
      return maxStart;
    }

    function updateVisible() {
      const pageSize = getPageSize();
      const total = items.length;

      const maxStart = clampStart(pageSize);

      items.forEach(function (li, idx) {
        if (idx >= currentStart && idx < currentStart + pageSize) {
          li.classList.remove("is-hidden");
        } else {
          li.classList.add("is-hidden");
        }
      });

      // show / hide nav based on total vs pageSize
      if (navWrap) {
        navWrap.style.display = total > pageSize ? "" : "none";
      }

      // update disabled state
      if (prevBtn) {
        prevBtn.disabled = currentStart === 0;
      }
      if (nextBtn) {
        nextBtn.disabled = currentStart >= maxStart;
      }
    }

    function goNext() {
      const pageSize = getPageSize();
      const total = items.length;
      const maxStart = Math.max(0, total - pageSize);

      currentStart = Math.min(currentStart + pageSize, maxStart);
      updateVisible();
    }

    function goPrev() {
      const pageSize = getPageSize();
      currentStart = Math.max(currentStart - pageSize, 0);
      updateVisible();
    }

    // --- ensure nav buttons exist (per block) ---

    if (!navWrap) {
      navWrap = document.createElement("div");
      navWrap.className = navWrapClass;
      root.appendChild(navWrap);
    }

    if (!prevBtn) {
      prevBtn = document.createElement("button");
      prevBtn.type = "button";
      prevBtn.className = "btn btn-secondary " + prevBtnClass;
      prevBtn.textContent = "‹";
      navWrap.appendChild(prevBtn);
    }

    if (!nextBtn) {
      nextBtn = document.createElement("button");
      nextBtn.type = "button";
      nextBtn.className = "btn btn-secondary " + nextBtnClass;
      nextBtn.textContent = "›";
      navWrap.appendChild(nextBtn);
    }

    if (!prevBtn._bound) {
      prevBtn.addEventListener("click", goPrev);
      prevBtn._bound = true;
    }
    if (!nextBtn._bound) {
      nextBtn.addEventListener("click", goNext);
      nextBtn._bound = true;
    }

    // respond to resize so pageSize updates correctly
    let resizeTimer = null;
    window.addEventListener("resize", function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(updateVisible, 150);
    });

    // initial state
    currentStart = 0;
    updateVisible();
  }

  function initFeaturedSubcategories() {
    // 1) hide any global "load more" wrappers so they never show
    document.querySelectorAll("#subcategories-expander, .subcategories-load-more-wrap")
      .forEach(function (el) {
        el.style.display = "none";
      });

    // 2) Initialize slider for ALL blocks rendered by the module
    const roots = document.querySelectorAll(
      "section.subcategories-container-parent.feature-category"
    );

    if (!roots.length) return;

    roots.forEach(function (root) {
      const list = root.querySelector("#subcategories-list");
      if (!list) return;

      const items = Array.from(list.querySelectorAll("li.subcategory-item"));
      if (!items.length) return;

      initFeaturedSlider(root, items);
    });
  }

  function init() {
    initFeaturedSubcategories();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
