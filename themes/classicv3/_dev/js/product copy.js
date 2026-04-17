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
import $ from "jquery";
import prestashop from "prestashop";
import ProductSelect from "./components/product-select";
import updateSources from "./components/update-sources";


// ---------- keep only this dynamic-classes updater ----------

// which classes belong to the dynamic set (from Smarty)
function isDynamicClass(cls) {
  if (!cls) return false;
  return (
    cls === 'product-images-box' ||
    cls === 'pc1' || cls === 'pc3' ||
    cls.startsWith('shape-') ||
    cls.startsWith('type-')  ||
    cls.startsWith('size-')  ||
    cls.startsWith('frame-') ||
    /^[a-z0-9-]+-[a-z0-9-]+-[a-z0-9-]+-[a-z0-9-]+$/.test(cls) // art code
  );
}

// read the selected option names straight from DOM (groups: 5=Shape, 6=Type, 7=Size, 8=Frame)
function readSelectedFromDOM() {
  const read = (gid) => {
    const el = document.querySelector(`#group_${gid} input:checked`);
    // PrestaShop puts the readable name in title or aria-label; fall back to value
    return el ? (el.getAttribute('title') || el.getAttribute('aria-label') || el.value) : undefined;
  };
  const shape = read(5);
  const type  = read(6);
  const size  = read(7);
  const frame = read(8);
  const piece = (size === '3-PIECE') ? 'pc3' : 'pc1';
  return { shape, type, size, frame, piece };
}

// normalize + compose only the dynamic classes (same as Smarty)
function buildDynamicClassList({ shape, type, size, frame, piece }) {
  const norm = v => (v || '').toString().trim().toLowerCase().replace(/\s+/g, '-');
  const _shape = norm(shape);
  const _type  = norm(type);
  const _size  = norm(size);
  const _frame = norm(frame);
  const _piece = norm(piece) || 'pc1';

  const list = [
    'product-images-box',
    _piece,
    _shape ? `shape-${_shape}` : '',
    _type  ? `type-${_type}`   : '',
    _size  ? `size-${_size}`   : '',
    _frame ? `frame-${_frame}` : '',
  ];

  const artCode = [_shape, _type, _size, _frame].filter(Boolean).join('-');
  if (artCode) list.push(artCode);

  return Array.from(new Set(list.filter(Boolean)));
}

// MAIN: keep non-dynamic classes (e.g. 'square room position-03'), replace only dynamic ones
function updateProductImagesClassesFromEvent(/*event not needed now*/) {
  const section = document.getElementById('product-images');
  if (!section) return;

  // preserve anything that's NOT dynamic (your click-state like square/room/position-03)
  const current = (section.className || '').split(/\s+/).filter(Boolean);
  const preserved = current.filter(c => !isDynamicClass(c));

  // read the current selections from DOM
  const values = readSelectedFromDOM();
  const dynamic = buildDynamicClassList(values);

  // set new className = preserved + dynamic
  section.className = [...preserved, ...dynamic].join(' ');
}



$(document).ready(() => {
  function coverImage() {
    const productCover = $(prestashop.themeSelectors.product.cover);
    const modalProductCover = $(
      prestashop.themeSelectors.product.modalProductCover
    );

    let thumbSelected = $(prestashop.themeSelectors.product.selected);

    const swipe = (selectedThumb, thumbParent) => {
      const newSelectedThumb = thumbParent.find(
        prestashop.themeSelectors.product.thumb
      );

      // Swap active classes on thumbnail
      selectedThumb.removeClass("selected");
      newSelectedThumb.addClass("selected");

      // Update sources of both cover and modal cover
      modalProductCover.prop("src", newSelectedThumb.data("image-large-src"));
      productCover.prop("src", newSelectedThumb.data("image-medium-src"));

      // Get data from thumbnail and update cover src, alt and title
      productCover.attr("title", newSelectedThumb.attr("title"));
      modalProductCover.attr("title", newSelectedThumb.attr("title"));
      productCover.attr("alt", newSelectedThumb.attr("alt"));
      modalProductCover.attr("alt", newSelectedThumb.attr("alt"));

      // Get data from thumbnail and update cover sources
      updateSources(
        productCover,
        newSelectedThumb.data("image-medium-sources")
      );
      updateSources(
        modalProductCover,
        newSelectedThumb.data("image-large-sources")
      );
    };

    $(prestashop.themeSelectors.product.thumb).on("click", (event) => {
      thumbSelected = $(prestashop.themeSelectors.product.selected);
      swipe(
        thumbSelected,
        $(event.target).closest(
          prestashop.themeSelectors.product.thumbContainer
        )
      );
    });

    const swipe2 = (boximage, selectedthumb) => {
      // const newimagebox = thumbParent.find(
      //   prestashop.themeSelectors.product.thumb
      // )

      var newSelectedThumb = selectedthumb; // This is the clicked element.

      var thumballclasses = newSelectedThumb.attr("class");
      // Swap active classes on thumbnail

      const classesToRemove = [
        "room",
        "canvas",
        "position-00",
        "position-01",
        "position-02",
        "position-03",
        "position-04",
      ];

      classesToRemove.forEach((className) => {
        if (boximage.hasClass(className)) {
          boximage.removeClass(className); // Only remove if the class exists
        }
      });

      boximage.addClass(thumballclasses);

      // newimagebox.addClass("selected");
    };

    $(prestashop.themeSelectors.product.mockup).on("click", (event) => {
      let imagebox = $(prestashop.themeSelectors.product.imagebox);
      swipe2(
        imagebox,
        $(event.target)
        // .closest(
        //   prestashop.themeSelectors.product.thumbContainer
        // )
      );
    });

        

// // Click handler for option filter boxes
// $(document).on("click", prestashop.themeSelectors.product.filterbox, function (event) {
//   const boxfilter = $(prestashop.themeSelectors.product.filterbox);
//   const clickedElement = $(event.target);

//   // Find the closest ancestor .option-box
//   const boxclose = clickedElement.closest(".option-box");

//   // Only proceed if we actually found a valid option box
//   if (boxclose.length > 0) {
//     swipe3(boxfilter, boxclose);
//   }
// });

// const swipe3 = (boxfilter, selectedBox) => {
//   boxfilter.find(".option-box").removeClass("selected");
//   selectedBox.addClass("selected");
// };



    productCover.swipe({
      swipe: (event, direction) => {
        thumbSelected = $(prestashop.themeSelectors.product.selected);
        const parentThumb = thumbSelected.closest(
          prestashop.themeSelectors.product.thumbContainer
        );

        if (direction === "right") {
          if (parentThumb.prev().length > 0) {
            swipe(thumbSelected, parentThumb.prev());
          } else if (parentThumb.next().length > 0) {
            swipe(thumbSelected, parentThumb.next());
          }
        } else if (direction === "left") {
          if (parentThumb.next().length > 0) {
            swipe(thumbSelected, parentThumb.next());
          } else if (parentThumb.prev().length > 0) {
            swipe(thumbSelected, parentThumb.prev());
          }
        }
      },
      allowPageScroll: "vertical",
    });
  }

  function imageScrollBox() {
    if ($("#main .js-qv-product-images li").length > 2) {
      $("#main .js-qv-mask").addClass("scroll");
      $(".scroll-box-arrows").addClass("scroll");
      $("#main .js-qv-mask").scrollbox({
        direction: "h",
        distance: 113,
        autoPlay: false,
      });
      $(".scroll-box-arrows .left").click(() => {
        $("#main .js-qv-mask").trigger("backward");
      });
      $(".scroll-box-arrows .right").click(() => {
        $("#main .js-qv-mask").trigger("forward");
      });
    } else {
      $("#main .js-qv-mask").removeClass("scroll");
      $(".scroll-box-arrows").removeClass("scroll");
    }
  }

  function createInputFile() {
    $(prestashop.themeSelectors.fileInput).on("change", (event) => {
      const target = $(event.currentTarget)[0];
      const file = target ? target.files[0] : null;

      if (target && file) {
        $(target).prev().text(file.name);
      }
    });
  }

  function createProductSpin() {
    const $quantityInput = $(prestashop.selectors.quantityWanted);

    $quantityInput.TouchSpin({
      verticalbuttons: true,
      verticalupclass: "material-icons touchspin-up",
      verticaldownclass: "material-icons touchspin-down",
      buttondown_class: "btn btn-touchspin js-touchspin",
      buttonup_class: "btn btn-touchspin js-touchspin",
      min: parseInt($quantityInput.attr("min"), 10),
      max: 1000000,
    });

    $(prestashop.themeSelectors.touchspin).off("touchstart.touchspin");

    $quantityInput.on("focusout", () => {
      if (
        $quantityInput.val() === "" ||
        $quantityInput.val() < $quantityInput.attr("min")
      ) {
        $quantityInput.val($quantityInput.attr("min"));
        $quantityInput.trigger("change");
      }
    });

    $("body").on("change keyup", prestashop.selectors.quantityWanted, (e) => {
      if ($quantityInput.val() !== "") {
        $(e.currentTarget).trigger("touchspin.stopspin");
        prestashop.emit("updateProduct", {
          eventType: "updatedProductQuantity",
          event: e,
        });
      }
    });
  }

  function addJsProductTabActiveSelector() {
    const nav = $(prestashop.themeSelectors.product.tabs);
    nav.on("show.bs.tab", (e) => {
      const target = $(e.target);
      target.addClass(prestashop.themeSelectors.product.activeNavClass);
      $(target.attr("href")).addClass(
        prestashop.themeSelectors.product.activeTabClass
      );
    });
    nav.on("hide.bs.tab", (e) => {
      const target = $(e.target);
      target.removeClass(prestashop.themeSelectors.product.activeNavClass);
      $(target.attr("href")).removeClass(
        prestashop.themeSelectors.product.activeTabClass
      );
    });
  }

  createProductSpin();
  createInputFile();
  coverImage();
  imageScrollBox();
  addJsProductTabActiveSelector();

  prestashop.on("updatedProduct", (event) => {
    createInputFile();
    coverImage();

      setTimeout(() => updateProductImagesClassesFromEvent(event), 0);

    if (event && event.product_minimal_quantity) {
      const minimalProductQuantity = parseInt(
        event.product_minimal_quantity,
        10
      );
      const quantityInputSelector = prestashop.selectors.quantityWanted;
      const quantityInput = $(quantityInputSelector);

      // @see http://www.virtuosoft.eu/code/bootstrap-touchspin/ about Bootstrap TouchSpin
      quantityInput.trigger("touchspin.updatesettings", {
        min: minimalProductQuantity,
      });
    }
    imageScrollBox();
    $($(prestashop.themeSelectors.product.activeTabs).attr("href"))
      .addClass("active")
      .removeClass("fade");
    $(prestashop.themeSelectors.product.imagesModal).replaceWith(
      event.product_images_modal
    );

    const productSelect = new ProductSelect();
    productSelect.init();
  });
});


