document.addEventListener('DOMContentLoaded', function () {
    function updateMasonryClass() {
        var grid = document.querySelector('.masonry.products');
        if (!grid) return;

        // Remove previous classes
        grid.classList.remove('c1', 'c2', 'c3');

        var w = window.innerWidth;

        if (w <= 575) {
            // Mobile
            grid.classList.add('c1');
        } else if (w >= 1200) {
            // Desktop large
            grid.classList.add('c3');
        } else {
            // Tablet / mid screens
            grid.classList.add('c2');
        }
    }

    updateMasonryClass();                // On load
    window.addEventListener('resize', updateMasonryClass); // On resize
});





/////////


document.addEventListener('DOMContentLoaded', function () {
    var grid = document.querySelector('.masonry.products');
    if (!grid) return;

    grid.style.position = 'relative';

    function updateMasonryClass() {
        // If you already added this earlier, keep only one version.
        var w = window.innerWidth;

        grid.classList.remove('c1', 'c2', 'c3');

        if (w <= 575) {
            grid.classList.add('c1');
        } else if (w >= 1200) {
            grid.classList.add('c3');
        } else {
            grid.classList.add('c2');
        }
    }

    function getColumnCount() {
        if (grid.classList.contains('c1')) return 1;
        if (grid.classList.contains('c2')) return 2;
        if (grid.classList.contains('c3')) return 3;
        return 1;
    }

    function getLeftPercent(colIndex, colCount) {
        if (colCount === 1) {
            return '0%';
        }

        if (colCount === 2) {
            return colIndex === 0 ? '0%' : '50%';
        }

        if (colCount === 3) {
            if (colIndex === 0) return '0%';
            if (colIndex === 1) return '33.3333%';
            return '66.6666%';
        }

        // fallback (not really needed here)
        return (100 / colCount) * colIndex + '%';
    }

    // function layoutMasonry() {
    //     var colCount = getColumnCount();
    //     if (colCount < 1) return;

    //     var items = grid.querySelectorAll('.list-product.js-product.product');
    //     if (!items.length) return;

    //     // Init column heights
    //     var colHeights = [];
    //     for (var i = 0; i < colCount; i++) {
    //         colHeights[i] = 0;
    //     }

    //     var itemWidth = (100 / colCount);

    //     items.forEach(function (item) {
    //         item.style.position = 'absolute';
    //         item.style.width = itemWidth + '%';

    //         // find column with smallest height
    //         var targetCol = 0;
    //         var minHeight = colHeights[0];

    //         for (var c = 1; c < colCount; c++) {
    //             if (colHeights[c] < minHeight) {
    //                 minHeight = colHeights[c];
    //                 targetCol = c;
    //             }
    //         }

    //         var left = getLeftPercent(targetCol, colCount);
    //         var top = colHeights[targetCol];

    //         item.style.left = left;
    //         item.style.top = top + 'px';

    //         // update column height with this item's height
    //         colHeights[targetCol] = top + item.offsetHeight;
    //     });

    //     // Set container height to tallest column so nothing overlaps footer
    //     var maxHeight = Math.max.apply(null, colHeights);
    //     grid.style.height = maxHeight + 'px';
    // }

    function layoutMasonry() {
    var colCount = getColumnCount();
    if (colCount < 1) return;

    var items = grid.querySelectorAll('.list-product.js-product.product');
    if (!items.length) return;

    var colHeights = [];
    for (var i = 0; i < colCount; i++) {
        colHeights[i] = 0;
    }

    var itemWidth = (100 / colCount);

    items.forEach(function (item) {
        item.style.position = 'absolute';
        item.style.width = itemWidth + '%';

        // choose column with smallest height
        var targetCol = 0;
        var minHeight = colHeights[0];
        for (var c = 1; c < colCount; c++) {
            if (colHeights[c] < minHeight) {
                minHeight = colHeights[c];
                targetCol = c;
            }
        }

        var left = getLeftPercent(targetCol, colCount);
        var top = colHeights[targetCol];

        item.style.left = left;
        item.style.top = top + 'px';

        // 🔴 IMPORTANT PART: add margin-bottom to the height
        var style = window.getComputedStyle(item);
        var marginBottom = parseFloat(style.marginBottom) || 0;

        colHeights[targetCol] = top + item.offsetHeight + marginBottom;
    });

    var maxHeight = Math.max.apply(null, colHeights);
    grid.style.height = maxHeight + 'px';
}


    function relayout() {
        window.requestAnimationFrame(function () {
            updateMasonryClass();
            layoutMasonry();
        });
    }

    // Initial layout (wait for images where possible)
    function initMasonry() {
        var images = grid.querySelectorAll('img');
        if (!images.length) {
            relayout();
            return;
        }

        var loaded = 0;
        var total = images.length;

        function checkDone() {
            loaded++;
            if (loaded >= total) {
                relayout();
            }
        }

        images.forEach(function (img) {
            if (img.complete) {
                checkDone();
            } else {
                img.addEventListener('load', checkDone);
                img.addEventListener('error', checkDone);
            }
        });
    }

    // Watch for infinite scroll (new products appended)
    // var observer = new MutationObserver(function (mutations) {
    //     var needsLayout = false;

    //     mutations.forEach(function (mutation) {
    //         if (mutation.addedNodes && mutation.addedNodes.length) {
    //             mutation.addedNodes.forEach(function (node) {
    //                 if (
    //                     node.nodeType === 1 &&
    //                     node.matches &&
    //                     node.matches('.list-product.js-product.product')
    //                 ) {
    //                     needsLayout = true;
    //                 }

    //                 // If wrapper markup is more nested:
    //                 if (
    //                     node.nodeType === 1 &&
    //                     node.querySelector &&
    //                     node.querySelector('.list-product.js-product.product')
    //                 ) {
    //                     needsLayout = true;
    //                 }
    //             });
    //         }
    //     });

    //     if (needsLayout) {
    //         relayout();
    //     }
    // });
var observer = new MutationObserver(function (mutations) {
    var newImages = [];

    mutations.forEach(function (mutation) {
        if (!mutation.addedNodes || !mutation.addedNodes.length) return;

        mutation.addedNodes.forEach(function (node) {
            if (node.nodeType !== 1) return;

            // If the node itself is a product
            if (node.matches && node.matches('.list-product.js-product.product')) {
                newImages = newImages.concat(Array.from(node.querySelectorAll('img')));
            } else if (node.querySelector) {
                // Or if it contains products inside
                newImages = newImages.concat(
                    Array.from(node.querySelectorAll('.list-product.js-product.product img'))
                );
            }
        });
    });

    // No images? just relayout
    if (!newImages.length) {
        relayout();
        return;
    }

    var loaded = 0;
    var total = newImages.length;

    function done() {
        loaded++;
        if (loaded >= total) {
            relayout();
        }
    }

    newImages.forEach(function (img) {
        if (img.complete) {
            done();
        } else {
            img.addEventListener('load', done, { once: true });
            img.addEventListener('error', done, { once: true });
        }
    });
});


    observer.observe(grid, {
        childList: true,
        subtree: true
    });

    // Relayout on resize (with small debounce)
    var resizeTimeout;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(relayout, 150);
    });

    // Run once
    initMasonry();
});




document.addEventListener('DOMContentLoaded', function () {
    var backToTop = document.getElementById('back-to-top');
    if (!backToTop) return;

    // How far down (in px) before showing the button
    var showOffset = 200;

    function toggleBackToTop() {
        // If we've scrolled more than showOffset, show the button
        if (window.scrollY > showOffset) {
            backToTop.classList.add('active');
        } else {
            // At/near the top: hide the button
            backToTop.classList.remove('active');
        }
    }

    // Keep button state in sync with scroll position
    window.addEventListener('scroll', toggleBackToTop, { passive: true });

    // Click: scroll smoothly to top
    backToTop.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
        // The scroll listener will remove .active once we reach the top
    });

    // Run once on load in case page loads scrolled down
    toggleBackToTop();
});
