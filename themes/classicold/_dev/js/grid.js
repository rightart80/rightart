/* modules/ps_gridcategories3/views/js/gc-gridcategories.js */
(function () {
  "use strict";

  /**
   * Initialize slider behaviour for a single grid block.
   *
   * @param {HTMLElement} root
   * @param {HTMLElement[]} items
   */
  function initGridCategoriesSlider(root, items) {
    if (!items.length) return;

    // Hide any old / legacy expanders or load-more wrappers
    root
      .querySelectorAll(
        "#subcategories-expander, .subcategories-load-more-wrap, .gc-gridcategories-expander, .gc-gridcategories-load-more-wrap"
      )
      .forEach(function (el) {
        el.style.display = "none";
      });

    const navWrapClass = "gc-gridcategories-slider-nav";
    const prevBtnClass = "gc-gridcategories-prev";
    const nextBtnClass = "gc-gridcategories-next";

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
          li.style.display = ""; // default (list-item)
        } else {
          li.style.display = "none";
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

    if (!prevBtn._gcBound) {
      prevBtn.addEventListener("click", goPrev);
      prevBtn._gcBound = true;
    }
    if (!nextBtn._gcBound) {
      nextBtn.addEventListener("click", goNext);
      nextBtn._gcBound = true;
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

  /**
   * Initialize all grid categories blocks rendered by the module.
   */
  function initGridCategoriesBlocks() {
    // 1) hide any global "load more" wrappers so they never show (old + new)
    document
      .querySelectorAll(
        "#subcategories-expander, .subcategories-load-more-wrap, .gc-gridcategories-expander, .gc-gridcategories-load-more-wrap"
      )
      .forEach(function (el) {
        el.style.display = "none";
      });

    // 2) Initialize slider for ALL blocks rendered by the ps_gridcategories3 module
    const roots = document.querySelectorAll(".gc-gridcategories-wrapper");

    if (!roots.length) return;

    roots.forEach(function (root) {
      const list = root.querySelector(".gc-gridcategories-list");
      if (!list) return;

      const items = Array.from(list.querySelectorAll("li.gc-gridcategories-item"));
      if (!items.length) return;

      initGridCategoriesSlider(root, items);
    });
  }

  function init() {
    initGridCategoriesBlocks();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
