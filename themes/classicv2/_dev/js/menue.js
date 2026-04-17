// seach icone clicked destop


  document.addEventListener('DOMContentLoaded', function () {
    const icon   = document.getElementById('header-search-icon');
    const widget = document.getElementById('search_widget');

    if (!icon || !widget) return;

    icon.addEventListener('click', function (e) {
      e.preventDefault();

      const input = widget.querySelector('.ui-autocomplete-input');
      if (!input) return;

      // Scroll to top
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });

      // Focus after scrolling
      setTimeout(function () {
        input.focus();
        input.click(); // helps some autocomplete widgets

        // Put cursor at the end of existing text (optional)
        if (input.setSelectionRange) {
          const len = input.value.length;
          input.setSelectionRange(len, len);
        }
      }, 320); // adjust if needed
    });
  });



// document.addEventListener('DOMContentLoaded', function () {
//     const icon = document.getElementById('header-search-icon');
//     const widget = document.getElementById('search_widget');

//     if (!icon || !widget) return;

//     icon.addEventListener('click', function (e) {
//       e.preventDefault();

//       // Find the actual input/textarea inside the widget
//       let input = widget;
//       if (!input.matches('input, textarea')) {
//         input = widget.querySelector('input, textarea');
//       }
//       if (!input) return; // no focusable field found

//       // Scroll to top (or to the widget if you prefer)
//       window.scrollTo({
//         top: 0,
//         behavior: 'smooth'
//       });

//       // Focus after scrolling
//       setTimeout(function () {
//         input.focus();

//         // Put cursor at the end of any existing text
//         if (input.setSelectionRange) {
//           const len = input.value.length;
//           input.setSelectionRange(len, len);
//         }
//       }, 400); // you can tweak this delay
//     });
//   });

// document.addEventListener('DOMContentLoaded', function () {
//     const icon = document.getElementById('header-search-icon');
//     const searchInput = document.getElementById('search_widget');

//     if (!icon || !searchInput) return;

//     icon.addEventListener('click', function (e) {
//       e.preventDefault();

//       // Scroll to the top smoothly
//       window.scrollTo({
//         top: 0,
//         behavior: 'smooth'
//       });

//       // After a short delay, focus the search input
//       setTimeout(function () {
//         searchInput.focus();

//         // Put the cursor at the end of existing text (optional)
//         const value = searchInput.value;
//         searchInput.value = '';
//         searchInput.value = value;
//       }, 300); // adjust delay if needed
//     });
//   });




/////////
(function () {
  // Mobile breakpoint
  const mobileQuery = window.matchMedia('(max-width: 1024px)');

  let mobileInitialized = false;
  let searchIcon, headerTop, widget, closeBtn;
  let onSearchIconClick, onDocumentClick;

  function initMobileSearch() {
    if (mobileInitialized) return; // avoid double init

    console.log('Init mobile search (<= 767px)');

    searchIcon = document.getElementById('header-search-icon');
    headerTop = document.querySelector('.header-top');
    widget    = document.getElementById('search_widget');

    if (!searchIcon || !headerTop || !widget) {
      console.warn('Search widget elements missing', { searchIcon, headerTop, widget });
      return;
    }

    // Open on search icon click
    onSearchIconClick = function () {
      // extra guard: do nothing if not mobile anymore
      if (!mobileQuery.matches) return;

      console.log('Search icon clicked (mobile)');
      headerTop.classList.add('search-opened');
      widget.classList.add('search-opened');
      widget.classList.remove('hidden');
    };

    searchIcon.addEventListener('click', onSearchIconClick);

    // Create / ensure close button inside widget
    closeBtn = document.getElementById('search_widget_close');
    if (!closeBtn) {
      closeBtn = document.createElement('span');
      closeBtn.id = 'search_widget_close';
      closeBtn.className = 'close-search-widget';
      closeBtn.textContent = '×';
      widget.appendChild(closeBtn);
    }

    // Close on clicking the close button
    onDocumentClick = function (e) {
      if (!mobileQuery.matches) return; // only act on mobile
      if (e.target.id === 'search_widget_close') {
        console.log('Close search widget (mobile)');
        widget.classList.remove('search-opened');
        widget.classList.add('hidden');
        headerTop.classList.remove('search-opened');
      }
    };

    document.addEventListener('click', onDocumentClick);

    mobileInitialized = true;
  }

  function destroyMobileSearch() {
    if (!mobileInitialized) return;

    console.log('Destroy mobile search (> 767px), reset everything');

    // Remove listeners
    if (searchIcon && onSearchIconClick) {
      searchIcon.removeEventListener('click', onSearchIconClick);
    }
    if (onDocumentClick) {
      document.removeEventListener('click', onDocumentClick);
    }

    // Reset classes so desktop logic starts from a clean state
    if (widget) {
      widget.classList.remove('search-opened', 'hidden');
    }
    if (headerTop) {
      headerTop.classList.remove('search-opened');
    }

    // Remove the mobile close button (optional, but keeps DOM clean)
    if (closeBtn && closeBtn.parentNode) {
      closeBtn.parentNode.removeChild(closeBtn);
    }

    mobileInitialized = false;
    searchIcon = headerTop = widget = closeBtn = null;
    onSearchIconClick = onDocumentClick = null;
  }

  function handleBreakpointChange(e) {
    // e.matches === true => <= 767px (mobile)
    if (e.matches) {
      initMobileSearch();
    } else {
      destroyMobileSearch();
    }
  }

  // Initial setup when DOM is ready
  document.addEventListener('DOMContentLoaded', function () {
    handleBreakpointChange(mobileQuery); // run once for initial size
  });

  // React to resize / orientation changes
  if (mobileQuery.addEventListener) {
    mobileQuery.addEventListener('change', handleBreakpointChange);
  } else if (mobileQuery.addListener) {
    // older browsers
    mobileQuery.addListener(handleBreakpointChange);
  }
})();





// // Search button activation
// document.addEventListener('DOMContentLoaded', function () {
//   console.log("DOM loaded");

//   const searchIcon = document.getElementById('header-search-icon');
//   const headerTop  = document.querySelector('.header-top'); // <-- class, not id
//   const widget     = document.getElementById('search_widget');

//   console.log("searchIcon:", searchIcon);
//   console.log("headerTop:", headerTop);
//   console.log("widget:", widget);

//   if (!searchIcon || !headerTop || !widget) {
//     console.warn("One of the elements was NOT found!", { searchIcon, headerTop, widget });
//     return;
//   }

//   searchIcon.addEventListener('click', function () {
//     console.log("Search icon clicked!");

//     headerTop.classList.add('search-opened');
//     widget.classList.add('search-opened');
//     widget.classList.remove('hidden');

//     console.log("Classes added.");
//   });
// });





// //////


// document.addEventListener('DOMContentLoaded', function () {
//   const widget = document.getElementById('search_widget');
//   if (!widget) return;

//   // Create the span element that will act as a close button
//   const closeBtn = document.createElement('span');
//   closeBtn.id = "search_widget_close";
//   closeBtn.className = "close-search-widget";
//   closeBtn.textContent = "×";

//   // INSERT INSIDE the widget
//   widget.appendChild(closeBtn);
// });




// // search wiget hide
// document.addEventListener('click', function (e) {
//   // Check if the thing clicked is our close button
//   if (e.target.id === 'search_widget_close') {
    
//     // Remove `search-opened` from the search widget
//     const widget = document.getElementById('search_widget');
//     if (widget) {
//       widget.classList.remove('search-opened');
//       widget.classList.add('hidden');   // Optional, delete if you don't want hiding
//     }

//     // Remove `search-opened` from .header-top
//     const headerTop = document.querySelector('.header-top');
//     if (headerTop) {
//       headerTop.classList.remove('search-opened');
//     }
//   }
// });




// updated sticky logic size change before sticky

document.addEventListener('DOMContentLoaded', function () {
  var headerContainer = document.getElementById('header-container');      // your sticky header
  var headerRight     = document.querySelector('#header-container .header-right-col');
  var stickyIcon      = document.querySelector('#header-container .Sticky-header-icone');
  var mainContent     = document.getElementById('wrapper');               // content under header

  if (!headerContainer || !headerRight || !mainContent) {
    return;
  }

  // Checkout/order pages have a different flow; sticky collapsing here hides/pushes content.
  var bodyId = document.body ? document.body.id : '';
  if (bodyId === 'checkout' || bodyId === 'order-confirmation') {
    headerContainer.classList.remove('is-sticky', 'width-change');
    headerRight.classList.remove('col-md-8');
    headerRight.classList.add('col-md-10');
    if (stickyIcon) {
      stickyIcon.classList.add('d-none');
    }
    mainContent.style.paddingTop = '';
    return;
  }

  var headerHeight = headerContainer.offsetHeight;
  var stickyStart  = headerContainer.offsetTop + headerHeight;

  // 🔹 how many pixels BEFORE sticky to shrink columns
  var shiftOffsetBefore = 40;  // col-md-10 -> col-md-8 happens 40px before sticky
  // 🔹 how many pixels BEFORE sticky to grow back when scrolling up
  var shiftOffsetAfter  = 80;  // col-md-8 -> col-md-10 happens 80px before sticky (closer to top)

  var colShrinkAt = stickyStart - shiftOffsetBefore;
  var colExpandAt = stickyStart - shiftOffsetAfter;

  function handleScroll () {
    var width = window.innerWidth || document.documentElement.clientWidth;

    // 🔹 MOBILE / TABLET (< 1025px) → no sticky logic, no padding
    if (width < 1025) {
      // clean desktop sticky state if any
      headerContainer.classList.remove('is-sticky');
      mainContent.style.paddingTop = '';

      headerRight.classList.remove('col-md-8');
      headerRight.classList.add('col-md-10');

      if (stickyIcon) {
        stickyIcon.classList.add('d-none');
      }
      return; // stop here for small screens
    }

    // 🔹 DESKTOP (≥ 768px) → normal sticky behavior
    var y = window.pageYOffset || document.documentElement.scrollTop;

    
    /* ------------------------------------------
   1) COLUMN WIDTH LOGIC + WIDTH-CHANGE CLASS
------------------------------------------- */

if (y > colShrinkAt) {
  // make it 8 BEFORE sticky
  headerRight.classList.remove('col-md-10');
  headerRight.classList.add('col-md-8');

  headerContainer.classList.add('width-change');  // add class
} 
else if (y < colExpandAt) {
  // make it 10 again AFTER going further up
  headerRight.classList.remove('col-md-8');
  headerRight.classList.add('col-md-10');

  headerContainer.classList.remove('width-change');  // remove class
}

    
    
    /* ------------------------------------------
       1) COLUMN WIDTH LOGIC (pre/post sticky)
       - shrink a bit BEFORE sticky
       - grow back a bit AFTER sticky is gone
    ------------------------------------------- */

    // if (y > colShrinkAt) {
    //   // make it 8 BEFORE sticky kicks in
    //   headerRight.classList.remove('col-md-10');
    //   headerRight.classList.add('col-md-8');
    // } else if (y < colExpandAt) {
    //   // make it 10 again only after scrolling further up
    //   headerRight.classList.remove('col-md-8');
    //   headerRight.classList.add('col-md-10');
    // }
    // between colExpandAt and colShrinkAt we keep the last state (nice hysteresis)

    /* ------------------------------------------
       2) STICKY logic (unchanged except col classes removed)
    ------------------------------------------- */
    if (y > stickyStart) {
      if (!headerContainer.classList.contains('is-sticky')) {
        headerContainer.classList.add('is-sticky');

        // add padding so content doesn’t slide under header

        var extraOffset = 60;
mainContent.style.paddingTop = (headerHeight + extraOffset) + 'px';
        // mainContent.style.paddingTop = headerHeight + 'px';

        if (stickyIcon) {
          stickyIcon.classList.remove('d-none');
        }
      }
    } else {
      if (headerContainer.classList.contains('is-sticky')) {
        headerContainer.classList.remove('is-sticky');

        mainContent.style.paddingTop = '';

        if (stickyIcon) {
          stickyIcon.classList.add('d-none');
        }
      }
    }
  }

  window.addEventListener('scroll', handleScroll);

  window.addEventListener('resize', function () {
    headerHeight = headerContainer.offsetHeight;
    stickyStart  = headerContainer.offsetTop + headerHeight;

    // 🔁 recompute thresholds on resize
    colShrinkAt = stickyStart - shiftOffsetBefore;
    colExpandAt = stickyStart - shiftOffsetAfter;

    handleScroll();
  });

  // run once on load
  handleScroll();
});



////update mobile padding on scroll for 768 sz only 


// document.addEventListener('DOMContentLoaded', function () {
//   var headerContainer = document.getElementById('header-container');      // your sticky header
//   var headerRight = document.querySelector('#header-container .header-right-col');
//   var stickyIcon = document.querySelector('#header-container .Sticky-header-icone');
//   var mainContent = document.getElementById('wrapper');               // content under header

//   if (!headerContainer || !headerRight || !mainContent) {
//     return;
//   }

//   var headerHeight = headerContainer.offsetHeight;
//   var stickyStart = headerContainer.offsetTop + headerHeight;

//   function handleScroll() {
//     var width = window.innerWidth || document.documentElement.clientWidth;

//     // 🔹 MOBILE / TABLET (< 768px) → no sticky logic, no padding
//     if (width < 768) {
//       // clean desktop sticky state if any
//       headerContainer.classList.remove('is-sticky');
//       mainContent.style.paddingTop = '';

//       headerRight.classList.remove('col-md-8');
//       headerRight.classList.add('col-md-10');

//       if (stickyIcon) {
//         stickyIcon.classList.add('d-none');
//       }
//       return; // stop here for small screens
//     }

//     // 🔹 DESKTOP (≥ 768px) → normal sticky behavior
//     var y = window.pageYOffset || document.documentElement.scrollTop;

//     if (y > stickyStart) {
//       if (!headerContainer.classList.contains('is-sticky')) {
//         headerContainer.classList.add('is-sticky');

//         // add padding so content doesn’t slide under header
//         mainContent.style.paddingTop = headerHeight + 'px';

//         headerRight.classList.remove('col-md-10');
//         headerRight.classList.add('col-md-8');

//         if (stickyIcon) {
//           stickyIcon.classList.remove('d-none');
//         }
//       }
//     } else {
//       if (headerContainer.classList.contains('is-sticky')) {
//         headerContainer.classList.remove('is-sticky');

//         mainContent.style.paddingTop = '';

//         headerRight.classList.remove('col-md-8');
//         headerRight.classList.add('col-md-10');

//         if (stickyIcon) {
//           stickyIcon.classList.add('d-none');
//         }
//       }
//     }
//   }

//   window.addEventListener('scroll', handleScroll);

//   window.addEventListener('resize', function () {
//     headerHeight = headerContainer.offsetHeight;
//     stickyStart = headerContainer.offsetTop + headerHeight;
//     handleScroll();
//   });

//   // run once on load
//   handleScroll();
// });





/////mobile padding 160 on scroll /////

// document.addEventListener('DOMContentLoaded', function () {
//   var headerContainer = document.getElementById('header-container');      // your sticky header
//   var headerRight     = document.querySelector('#header-container .header-right-col');
//   var stickyIcon      = document.querySelector('#header-container .Sticky-header-icone');
//   var mainContent     = document.getElementById('wrapper');               // content under header

//   if (!headerContainer || !headerRight || !mainContent) {
//     return;
//   }

//   var headerHeight = headerContainer.offsetHeight;
//   // start sticky when the BOTTOM of header hits the top of the viewport
//   var stickyStart  = headerContainer.offsetTop + headerHeight;

//   function handleScroll () {
//     var y = window.pageYOffset || document.documentElement.scrollTop;

//     if (y > stickyStart) {
//       if (!headerContainer.classList.contains('is-sticky')) {
//         headerContainer.classList.add('is-sticky');

//         // 👇 this is where we add padding so your product title doesn't hide
//         mainContent.style.paddingTop = headerHeight + 'px';

//         headerRight.classList.remove('col-md-10');
//         headerRight.classList.add('col-md-8');

//         if (stickyIcon) {
//           stickyIcon.classList.remove('d-none');
//         }
//       }
//     } else {
//       if (headerContainer.classList.contains('is-sticky')) {
//         headerContainer.classList.remove('is-sticky');

//         // 👇 remove padding when not sticky
//         mainContent.style.paddingTop = '';

//         headerRight.classList.remove('col-md-8');
//         headerRight.classList.add('col-md-10');
//       }
//     }
//   }

//   window.addEventListener('scroll', handleScroll);

//   window.addEventListener('resize', function () {
//     headerHeight = headerContainer.offsetHeight;
//     stickyStart  = headerContainer.offsetTop + headerHeight;
//     handleScroll();
//   });

//   // run once on load
//   handleScroll();
// });



////////class mobile header js///////


document.addEventListener('DOMContentLoaded', function () {

  var headerContainer = document.getElementById('header-container');

  function updateHeaderClass() {
    var width = window.innerWidth;

    if (width >= 1025) {
      headerContainer.classList.add('desktop-header');
      headerContainer.classList.remove('mobile-header');
    } else {
      headerContainer.classList.add('mobile-header');
      headerContainer.classList.remove('desktop-header');
    }
  }

  // Run on load
  updateHeaderClass();

  // Run on resize
  window.addEventListener('resize', updateHeaderClass);
});









// document.addEventListener('DOMContentLoaded', function () {
//   var headerContainer = document.getElementById('header-container');
//   var headerRight     = document.querySelector('.header-right-col');
//   var stickyIcon      = document.querySelector('.Sticky-header-icone');

//   if (!headerContainer || !headerRight || !stickyIcon) {
//     return;
//   }

//   // "Slight scroll" before activating sticky
//   var stickyStart = headerContainer.offsetTop + 10; // 10px after top

//   function handleScroll () {
//     if (window.pageYOffset > stickyStart) {
//       if (!headerContainer.classList.contains('is-sticky')) {
//         headerContainer.classList.add('is-sticky');

//         // change col-md-10 -> col-md-8
//         headerRight.classList.remove('col-md-10');
//         headerRight.classList.add('col-md-8');

//         // // show the right icon column (2/12)
//         // stickyIcon.classList.remove('d-none');
//         // if (!stickyIcon.classList.contains('col-md-2')) {
//         //   stickyIcon.classList.add('col-md-2');
//         // }
//       }
//     } else {
//       if (headerContainer.classList.contains('is-sticky')) {
//         headerContainer.classList.remove('is-sticky');

//         // back to col-md-10 when not sticky
//         headerRight.classList.remove('col-md-8');
//         headerRight.classList.add('col-md-10');

//         // hide icon column again
//         // stickyIcon.classList.add('d-none');
//       }
//     }
//   }

//   window.addEventListener('scroll', handleScroll);
//   window.addEventListener('resize', function () {
//     // recalc offset if layout changes
//     stickyStart = headerContainer.offsetTop + 10;
//     handleScroll();
//   });
// });



////////////////new-menue///////

// document.addEventListener('DOMContentLoaded', function () {
//   var main = document.querySelector('main');

//   if (!main) return;

//   var triggerPoint = 50; // px after scrolling

//   function handleScroll() {
//     if (window.pageYOffset > triggerPoint) {
//       main.classList.add('collapsed');
//     } else {
//       main.classList.remove('collapsed');
//     }
//   }

//   window.addEventListener('scroll', handleScroll);
//   handleScroll();
// });



// document.addEventListener('DOMContentLoaded', function () {
//   var header = document.getElementById('header');

//   if (!header) return;

//   var triggerPoint = 50; // px after scrolling

//   function handleScroll() {
//     if (window.pageYOffset > triggerPoint) {
//       header.classList.add('collapsed');
//     } else {
//       header.classList.remove('collapsed');
//     }
//   }

//   window.addEventListener('scroll', handleScroll);
//   handleScroll();
// });
