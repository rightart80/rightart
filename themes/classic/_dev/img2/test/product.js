// /**
//  * Copyright since 2007 PrestaShop SA and Contributors
//  * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
//  *
//  * NOTICE OF LICENSE.
//  *
//  * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
//  * that is bundled with this package in the file LICENSE.md.
//  * It is also available through the world-wide-web at this URL:
//  * https://opensource.org/licenses/AFL-3.0
//  * If you did not receive a copy of the license and are unable to
//  * obtain it through the world-wide-web, please send an email
//  * to license@prestashop.com so we can send you a copy immediately.
//  *
//  * DISCLAIMER
//  *
//  * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
//  * versions in the future. If you wish to customize PrestaShop for your
//  * needs please refer to https://devdocs.prestashop.com/ for more information.
//  *
//  * @author    PrestaShop SA and Contributors <contact@prestashop.com>
//  * @copyright Since 2007 PrestaShop SA and Contributors
//  * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
//  */
// import $ from "jquery";
// import prestashop from "prestashop";
// import ProductSelect from "./components/product-select";
// import updateSources from "./components/update-sources";




// // --- helpers to normalize + build the same classes your Smarty outputs ---
// function norm(v) {
//   return (v || '').toString().trim().toLowerCase().replace(/\s+/g, '-');
// }

// // capture the initial static classes so we don't lose them on updates
// let __PI_STATIC__ = null; // filled on first use

// function buildProductImageClasses({ shape, type, size, frame, piece }) {
//   const _shape = norm(shape);
//   const _type  = norm(type);
//   const _size  = norm(size);
//   const _frame = norm(frame);
//   const _piece = norm(piece) || 'pc1';

//   const class_01 = _shape ? `shape-${_shape}` : '';
//   const class_02 = _type  ? `type-${_type}`   : '';
//   const class_03 = _size  ? `size-${_size}`   : '';
//   const class_04 = _frame ? `frame-${_frame}` : '';

//   // matches your Smarty "{$art_code}" = shape-type-size-frame
//   const art_code = [_shape, _type, _size, _frame].filter(Boolean).join('-');

//   // always include the static base classes we found on first load
//   const base = __PI_STATIC__ || 'product-images-box';

//   return [base, _piece, class_01, class_02, class_03, class_04, art_code]
//     .filter(Boolean)
//     .join(' ');
// }

// // try to read attributes from the updatedProduct payload in different formats
// function extractAttributesFromEvent(event) {
//   // 1) direct structure (most themes)
//   if (event && event.product && event.product.attributes) {
//     return event.product.attributes; // keys: "5","6","7","8"
//   }

//   // 2) parse data-product JSON embedded in product_details HTML
//   if (event && typeof event.product_details === 'string') {
//     const m = event.product_details.match(/data-product="([^"]+)"/);
//     if (m) {
//       try {
//         const decoded = m[1].replace(/&quot;/g, '"').replace(/&amp;/g, '&');
//         const dp = JSON.parse(decoded);
//         if (dp && dp.attributes) return dp.attributes;
//       } catch (_) {}
//     }
//   }
//   return null;
// }

// // main: build classes and set them on #product-images
// function updateProductImagesClassesFromEvent(event, groupMap = { shape:'5', type:'6', size:'7', frame:'8' }) {
//   const section = document.getElementById('product-images');
//   if (!section) return;

//   // record the "static" classes once (everything except the dynamic pieces)
//   if (!__PI_STATIC__) {
//     const keep = section.className
//       .split(/\s+/)
//       .filter(c => !/^shape-/.test(c) && !/^type-/.test(c) && !/^size-/.test(c) && !/^frame-/.test(c) && c !== 'pc1' && c !== 'pc3' && !/^[a-z0-9-]+-[a-z0-9-]+-[a-z0-9-]+-[a-z0-9-]+$/.test(c));
//     __PI_STATIC__ = keep.join(' ') || 'product-images-box';
//   }

//   // Path A: attributes object
//   const attrs = extractAttributesFromEvent(event);
//   if (attrs) {
//     const values = {
//       shape: attrs[groupMap.shape] ? attrs[groupMap.shape].name : undefined,
//       type:  attrs[groupMap.type]  ? attrs[groupMap.type].name  : undefined,
//       size:  attrs[groupMap.size]  ? attrs[groupMap.size].name  : undefined,
//       frame: attrs[groupMap.frame] ? attrs[groupMap.frame].name : undefined,
//       piece: (attrs[groupMap.size] && attrs[groupMap.size].name === '3-PIECE') ? 'pc3' : 'pc1',
//     };
//     section.className = buildProductImageClasses(values);
//     return;
//   }

//   // Path B: copy classes from incoming HTML if present ("product-images" / "product_images")
//   const html = (event && (event['product-images'] || event['product_images'])) || null;
//   if (html) {
//     const tmp = document.createElement('div');
//     tmp.innerHTML = html;
//     const incoming = tmp.querySelector('#product-images');
//     if (incoming) {
//       // keep our static classes; replace dynamic ones
//       const incomingDyn = incoming.className;
//       const dynOnly = incomingDyn.replace(__PI_STATIC__, '').trim();
//       section.className = (__PI_STATIC__ + ' ' + dynOnly).trim();
//     }
//   }
// }


// $(document).ready(() => {
//   function coverImage() {
//     const productCover = $(prestashop.themeSelectors.product.cover);
//     const modalProductCover = $(
//       prestashop.themeSelectors.product.modalProductCover
//     );

//     let thumbSelected = $(prestashop.themeSelectors.product.selected);

//     const swipe = (selectedThumb, thumbParent) => {
//       const newSelectedThumb = thumbParent.find(
//         prestashop.themeSelectors.product.thumb
//       );

//       // Swap active classes on thumbnail
//       selectedThumb.removeClass("selected");
//       newSelectedThumb.addClass("selected");

//       // Update sources of both cover and modal cover
//       modalProductCover.prop("src", newSelectedThumb.data("image-large-src"));
//       productCover.prop("src", newSelectedThumb.data("image-medium-src"));

//       // Get data from thumbnail and update cover src, alt and title
//       productCover.attr("title", newSelectedThumb.attr("title"));
//       modalProductCover.attr("title", newSelectedThumb.attr("title"));
//       productCover.attr("alt", newSelectedThumb.attr("alt"));
//       modalProductCover.attr("alt", newSelectedThumb.attr("alt"));

//       // Get data from thumbnail and update cover sources
//       updateSources(
//         productCover,
//         newSelectedThumb.data("image-medium-sources")
//       );
//       updateSources(
//         modalProductCover,
//         newSelectedThumb.data("image-large-sources")
//       );
//     };

//     $(prestashop.themeSelectors.product.thumb).on("click", (event) => {
//       thumbSelected = $(prestashop.themeSelectors.product.selected);
//       swipe(
//         thumbSelected,
//         $(event.target).closest(
//           prestashop.themeSelectors.product.thumbContainer
//         )
//       );
//     });

//     const swipe2 = (boximage, selectedthumb) => {
//       // const newimagebox = thumbParent.find(
//       //   prestashop.themeSelectors.product.thumb
//       // )

//       var newSelectedThumb = selectedthumb; // This is the clicked element.

//       var thumballclasses = newSelectedThumb.attr("class");
//       // Swap active classes on thumbnail

//       const classesToRemove = [
//         "room",
//         "canvas",
//         "position-00",
//         "position-01",
//         "position-02",
//         "position-03",
//         "position-04",
//       ];

//       classesToRemove.forEach((className) => {
//         if (boximage.hasClass(className)) {
//           boximage.removeClass(className); // Only remove if the class exists
//         }
//       });

//       boximage.addClass(thumballclasses);

//       // newimagebox.addClass("selected");
//     };

//     $(prestashop.themeSelectors.product.mockup).on("click", (event) => {
//       let imagebox = $(prestashop.themeSelectors.product.imagebox);
//       swipe2(
//         imagebox,
//         $(event.target)
//         // .closest(
//         //   prestashop.themeSelectors.product.thumbContainer
//         // )
//       );
//     });

//     productCover.swipe({
//       swipe: (event, direction) => {
//         thumbSelected = $(prestashop.themeSelectors.product.selected);
//         const parentThumb = thumbSelected.closest(
//           prestashop.themeSelectors.product.thumbContainer
//         );

//         if (direction === "right") {
//           if (parentThumb.prev().length > 0) {
//             swipe(thumbSelected, parentThumb.prev());
//           } else if (parentThumb.next().length > 0) {
//             swipe(thumbSelected, parentThumb.next());
//           }
//         } else if (direction === "left") {
//           if (parentThumb.next().length > 0) {
//             swipe(thumbSelected, parentThumb.next());
//           } else if (parentThumb.prev().length > 0) {
//             swipe(thumbSelected, parentThumb.prev());
//           }
//         }
//       },
//       allowPageScroll: "vertical",
//     });
//   }

//   function imageScrollBox() {
//     if ($("#main .js-qv-product-images li").length > 2) {
//       $("#main .js-qv-mask").addClass("scroll");
//       $(".scroll-box-arrows").addClass("scroll");
//       $("#main .js-qv-mask").scrollbox({
//         direction: "h",
//         distance: 113,
//         autoPlay: false,
//       });
//       $(".scroll-box-arrows .left").click(() => {
//         $("#main .js-qv-mask").trigger("backward");
//       });
//       $(".scroll-box-arrows .right").click(() => {
//         $("#main .js-qv-mask").trigger("forward");
//       });
//     } else {
//       $("#main .js-qv-mask").removeClass("scroll");
//       $(".scroll-box-arrows").removeClass("scroll");
//     }
//   }

//   function createInputFile() {
//     $(prestashop.themeSelectors.fileInput).on("change", (event) => {
//       const target = $(event.currentTarget)[0];
//       const file = target ? target.files[0] : null;

//       if (target && file) {
//         $(target).prev().text(file.name);
//       }
//     });
//   }

//   function createProductSpin() {
//     const $quantityInput = $(prestashop.selectors.quantityWanted);

//     $quantityInput.TouchSpin({
//       verticalbuttons: true,
//       verticalupclass: "material-icons touchspin-up",
//       verticaldownclass: "material-icons touchspin-down",
//       buttondown_class: "btn btn-touchspin js-touchspin",
//       buttonup_class: "btn btn-touchspin js-touchspin",
//       min: parseInt($quantityInput.attr("min"), 10),
//       max: 1000000,
//     });

//     $(prestashop.themeSelectors.touchspin).off("touchstart.touchspin");

//     $quantityInput.on("focusout", () => {
//       if (
//         $quantityInput.val() === "" ||
//         $quantityInput.val() < $quantityInput.attr("min")
//       ) {
//         $quantityInput.val($quantityInput.attr("min"));
//         $quantityInput.trigger("change");
//       }
//     });

//     $("body").on("change keyup", prestashop.selectors.quantityWanted, (e) => {
//       if ($quantityInput.val() !== "") {
//         $(e.currentTarget).trigger("touchspin.stopspin");
//         prestashop.emit("updateProduct", {
//           eventType: "updatedProductQuantity",
//           event: e,
//         });
//       }
//     });
//   }

//   function addJsProductTabActiveSelector() {
//     const nav = $(prestashop.themeSelectors.product.tabs);
//     nav.on("show.bs.tab", (e) => {
//       const target = $(e.target);
//       target.addClass(prestashop.themeSelectors.product.activeNavClass);
//       $(target.attr("href")).addClass(
//         prestashop.themeSelectors.product.activeTabClass
//       );
//     });
//     nav.on("hide.bs.tab", (e) => {
//       const target = $(e.target);
//       target.removeClass(prestashop.themeSelectors.product.activeNavClass);
//       $(target.attr("href")).removeClass(
//         prestashop.themeSelectors.product.activeTabClass
//       );
//     });
//   }

//   createProductSpin();
//   createInputFile();
//   coverImage();
//   imageScrollBox();
//   addJsProductTabActiveSelector();

//   prestashop.on("updatedProduct", (event) => {
//     createInputFile();
//     coverImage();

//       setTimeout(() => updateProductImagesClassesFromEvent(event), 0);

//     if (event && event.product_minimal_quantity) {
//       const minimalProductQuantity = parseInt(
//         event.product_minimal_quantity,
//         10
//       );
//       const quantityInputSelector = prestashop.selectors.quantityWanted;
//       const quantityInput = $(quantityInputSelector);

//       // @see http://www.virtuosoft.eu/code/bootstrap-touchspin/ about Bootstrap TouchSpin
//       quantityInput.trigger("touchspin.updatesettings", {
//         min: minimalProductQuantity,
//       });
//     }
//     imageScrollBox();
//     $($(prestashop.themeSelectors.product.activeTabs).attr("href"))
//       .addClass("active")
//       .removeClass("fade");
//     $(prestashop.themeSelectors.product.imagesModal).replaceWith(
//       event.product_images_modal
//     );

//     const productSelect = new ProductSelect();
//     productSelect.init();
//   });
// });



/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE.
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
//  * @author    PrestaShop SA and Contributors <contact@prestashop.com>
//  * @copyright Since 2007 PrestaShop SA and Contributors
//  * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
//  */
// import $ from "jquery";
// import prestashop from "prestashop";
// import ProductSelect from "./components/product-select";
// import updateSources from "./components/update-sources";

// /* =========================
//    Helpers: class building
//    ========================= */

// // normalize to kebab-case
// function norm(v) {
//   return (v || "").toString().trim().toLowerCase().replace(/\s+/g, "-");
// }

// /**
//  * Recreate Smarty classes:
//  *   product-*-box <piece> shape-* type-* size-* frame-* <art_code>
//  * <art_code> = shape-type-size-frame
//  *
//  * NOTE: We return with default base "product-images-box".
//  * For other sections (e.g. product-Options), we replace that base later.
//  */
// function buildProductImageClasses({ shape, type, size, frame, piece }) {
//   const _shape = norm(shape);
//   const _type = norm(type);
//   const _size = norm(size);
//   const _frame = norm(frame);
//   const _piece = norm(piece) || "pc1";

//   const class_01 = _shape ? `shape-${_shape}` : "";
//   const class_02 = _type ? `type-${_type}` : "";
//   const class_03 = _size ? `size-${_size}` : "";
//   const class_04 = _frame ? `frame-${_frame}` : "";

//   const art_code = [_shape, _type, _size, _frame].filter(Boolean).join("-");

//   // default base; we will swap for target section
//   const base = "product-images-box";

//   return [base, _piece, class_01, class_02, class_03, class_04, art_code]
//     .filter(Boolean)
//     .join(" ");
// }

// // per-section static class snapshots so we don't erase custom classes
// const __PI_STATIC_BY_ID__ = {};

// /**
//  * Preserve static classes on a section and apply dynamic classes.
//  * @param {string} id   - element id (e.g. 'product-images' or 'product-Options')
//  * @param {string} base - base class for this section (e.g. 'product-images-box' or 'product-Options-box')
//  * @param {object} values - {shape,type,size,frame,piece}
//  */
// function setSectionClasses(id, base, values) {
//   const el = document.getElementById(id);
//   if (!el) return;

//   // capture static classes one time for this element
//   if (!__PI_STATIC_BY_ID__[id]) {
//     const keep = el.className
//       .split(/\s+/)
//       .filter(
//         (c) =>
//           c &&
//           c !== "pc1" &&
//           c !== "pc3" &&
//           !/^shape-/.test(c) &&
//           !/^type-/.test(c) &&
//           !/^size-/.test(c) &&
//           !/^frame-/.test(c) &&
//           // exclude art_code-like token (shape-type-size-frame)
//           !/^[a-z0-9-]+-[a-z0-9-]+-[a-z0-9-]+-[a-z0-9-]+$/.test(c)
//       );
//     __PI_STATIC_BY_ID__[id] = keep.length ? keep.join(" ") : base;
//   }

//   // build using images base, then replace with this section's base/static
//   const built = buildProductImageClasses(values);
//   const classString = built.replace(/^product-images-box/, __PI_STATIC_BY_ID__[id]);

//   el.className = classString;
// }

// /**
//  * Extract attributes from the updatedProduct payload in different formats.
//  * Returns object keyed by group IDs ("5","6","7","8"), or null.
//  */
// function extractAttributesFromEvent(event) {
//   // 1) direct structure
//   if (event && event.product && event.product.attributes) {
//     return event.product.attributes;
//   }

//   // 2) data-product JSON embedded in product_details HTML
//   if (event && typeof event.product_details === "string") {
//     const m = event.product_details.match(/data-product="([^"]+)"/);
//     if (m) {
//       try {
//         const decoded = m[1].replace(/&quot;/g, '"').replace(/&amp;/g, "&");
//         const dp = JSON.parse(decoded);
//         if (dp && dp.attributes) return dp.attributes;
//       } catch (_) {}
//     }
//   }
//   return null;
// }

// /**
//  * Build classes from event and apply to BOTH sections:
//  *   #product-images  (base: 'product-images-box')
//  *   #product-Options (base: 'product-Options-box')
//  *
//  * Adjust groupMap here if your group IDs differ.
//  */
// function updateProductSectionsFromEvent(event, groupMap = { shape: "5", type: "6", size: "7", frame: "8" }) {
//   const attrs = extractAttributesFromEvent(event);

//   if (attrs) {
//     const values = {
//       shape: attrs[groupMap.shape] ? attrs[groupMap.shape].name : undefined,
//       type: attrs[groupMap.type] ? attrs[groupMap.type].name : undefined,
//       size: attrs[groupMap.size] ? attrs[groupMap.size].name : undefined,
//       frame: attrs[groupMap.frame] ? attrs[groupMap.frame].name : undefined,
//       piece: attrs[groupMap.size] && attrs[groupMap.size].name === "3-PIECE" ? "pc3" : "pc1",
//     };

//     // apply to BOTH sections
//     setSectionClasses("product-images", "product-images-box", values);
//     setSectionClasses("product-Options", "product-Options-box", values);
//     return;
//   }

//   // Fallback: copy from incoming HTML if present ("product-images" / "product_images")
//   const html = (event && (event["product-images"] || event["product_images"])) || null;
//   if (html) {
//     const tmp = document.createElement("div");
//     tmp.innerHTML = html;

//     const incomingImages = tmp.querySelector("#product-images");
//     const incomingOptions = tmp.querySelector("#product-Options");

//     if (incomingImages) {
//       const base = __PI_STATIC_BY_ID__["product-images"] || "product-images-box";
//       const dyn = incomingImages.className.replace(base, "").trim();
//       const el = document.getElementById("product-images");
//       if (el) el.className = (base + " " + dyn).trim();
//     }
//     if (incomingOptions) {
//       const base = __PI_STATIC_BY_ID__["product-Options"] || "product-Options-box";
//       const dyn = incomingOptions.className.replace(base, "").trim();
//       const el = document.getElementById("product-Options");
//       if (el) el.className = (base + " " + dyn).trim();
//     }
//   }
// }

// /* =========================
//    Page behaviors
//    ========================= */

// $(document).ready(() => {
//   function coverImage() {
//     const productCover = $(prestashop.themeSelectors.product.cover);
//     const modalProductCover = $(prestashop.themeSelectors.product.modalProductCover);

//     let thumbSelected = $(prestashop.themeSelectors.product.selected);

//     const swipe = (selectedThumb, thumbParent) => {
//       const newSelectedThumb = thumbParent.find(prestashop.themeSelectors.product.thumb);

//       // Swap active classes on thumbnail
//       selectedThumb.removeClass("selected");
//       newSelectedThumb.addClass("selected");

//       // Update sources of both cover and modal cover
//       modalProductCover.prop("src", newSelectedThumb.data("image-large-src"));
//       productCover.prop("src", newSelectedThumb.data("image-medium-src"));

//       // Update alt/title
//       productCover.attr("title", newSelectedThumb.attr("title"));
//       modalProductCover.attr("title", newSelectedThumb.attr("title"));
//       productCover.attr("alt", newSelectedThumb.attr("alt"));
//       modalProductCover.attr("alt", newSelectedThumb.attr("alt"));

//       // Update <source> sets
//       updateSources(productCover, newSelectedThumb.data("image-medium-sources"));
//       updateSources(modalProductCover, newSelectedThumb.data("image-large-sources"));
//     };

//     $(prestashop.themeSelectors.product.thumb).on("click", (event) => {
//       thumbSelected = $(prestashop.themeSelectors.product.selected);
//       swipe(thumbSelected, $(event.target).closest(prestashop.themeSelectors.product.thumbContainer));
//     });

//     // Thumbnail-to-large mockup class sync
//     const swipe2 = (boximage, selectedthumb) => {
//       const thumbClasses = selectedthumb.attr("class");
//       const classesToRemove = ["room", "canvas", "position-00", "position-01", "position-02", "position-03", "position-04"];

//       classesToRemove.forEach((className) => {
//         if (boximage.hasClass(className)) boximage.removeClass(className);
//       });

//       boximage.addClass(thumbClasses);
//     };

//     $(prestashop.themeSelectors.product.mockup).on("click", (event) => {
//       let imagebox = $(prestashop.themeSelectors.product.imagebox);
//       swipe2(imagebox, $(event.target));
//     });

//     productCover.swipe({
//       swipe: (event, direction) => {
//         thumbSelected = $(prestashop.themeSelectors.product.selected);
//         const parentThumb = thumbSelected.closest(prestashop.themeSelectors.product.thumbContainer);

//         if (direction === "right") {
//           if (parentThumb.prev().length > 0) {
//             swipe(thumbSelected, parentThumb.prev());
//           } else if (parentThumb.next().length > 0) {
//             swipe(thumbSelected, parentThumb.next());
//           }
//         } else if (direction === "left") {
//           if (parentThumb.next().length > 0) {
//             swipe(thumbSelected, parentThumb.next());
//           } else if (parentThumb.prev().length > 0) {
//             swipe(thumbSelected, parentThumb.prev());
//           }
//         }
//       },
//       allowPageScroll: "vertical",
//     });
//   }

//   function imageScrollBox() {
//     if ($("#main .js-qv-product-images li").length > 2) {
//       $("#main .js-qv-mask").addClass("scroll");
//       $(".scroll-box-arrows").addClass("scroll");
//       $("#main .js-qv-mask").scrollbox({
//         direction: "h",
//         distance: 113,
//         autoPlay: false,
//       });
//       $(".scroll-box-arrows .left").click(() => {
//         $("#main .js-qv-mask").trigger("backward");
//       });
//       $(".scroll-box-arrows .right").click(() => {
//         $("#main .js-qv-mask").trigger("forward");
//       });
//     } else {
//       $("#main .js-qv-mask").removeClass("scroll");
//       $(".scroll-box-arrows").removeClass("scroll");
//     }
//   }

//   function createInputFile() {
//     $(prestashop.themeSelectors.fileInput).on("change", (event) => {
//       const target = $(event.currentTarget)[0];
//       const file = target ? target.files[0] : null;

//       if (target && file) {
//         $(target).prev().text(file.name);
//       }
//     });
//   }

//   function createProductSpin() {
//     const $quantityInput = $(prestashop.selectors.quantityWanted);

//     $quantityInput.TouchSpin({
//       verticalbuttons: true,
//       verticalupclass: "material-icons touchspin-up",
//       verticaldownclass: "material-icons touchspin-down",
//       buttondown_class: "btn btn-touchspin js-touchspin",
//       buttonup_class: "btn btn-touchspin js-touchspin",
//       min: parseInt($quantityInput.attr("min"), 10),
//       max: 1000000,
//     });

//     $(prestashop.themeSelectors.touchspin).off("touchstart.touchspin");

//     $quantityInput.on("focusout", () => {
//       if ($quantityInput.val() === "" || $quantityInput.val() < $quantityInput.attr("min")) {
//         $quantityInput.val($quantityInput.attr("min"));
//         $quantityInput.trigger("change");
//       }
//     });

//     $("body").on("change keyup", prestashop.selectors.quantityWanted, (e) => {
//       if ($quantityInput.val() !== "") {
//         $(e.currentTarget).trigger("touchspin.stopspin");
//         prestashop.emit("updateProduct", {
//           eventType: "updatedProductQuantity",
//           event: e,
//         });
//       }
//     });
//   }

//   function addJsProductTabActiveSelector() {
//     const nav = $(prestashop.themeSelectors.product.tabs);
//     nav.on("show.bs.tab", (e) => {
//       const target = $(e.target);
//       target.addClass(prestashop.themeSelectors.product.activeNavClass);
//       $(target.attr("href")).addClass(prestashop.themeSelectors.product.activeTabClass);
//     });
//     nav.on("hide.bs.tab", (e) => {
//       const target = $(e.target);
//       target.removeClass(prestashop.themeSelectors.product.activeNavClass);
//       $(target.attr("href")).removeClass(prestashop.themeSelectors.product.activeTabClass);
//     });
//   }

//   // initial boot
//   createProductSpin();
//   createInputFile();
//   coverImage();
//   imageScrollBox();
//   addJsProductTabActiveSelector();

//   // when the product JSON (variant) changes
//   prestashop.on("updatedProduct", (event) => {
//     createInputFile();
//     coverImage();

//     // update BOTH sections' classes from AJAX payload
//     setTimeout(() => updateProductSectionsFromEvent(event), 0);

//     if (event && event.product_minimal_quantity) {
//       const minimalProductQuantity = parseInt(event.product_minimal_quantity, 10);
//       const quantityInputSelector = prestashop.selectors.quantityWanted;
//       const quantityInput = $(quantityInputSelector);
//       quantityInput.trigger("touchspin.updatesettings", { min: minimalProductQuantity });
//     }

//     imageScrollBox();
//     $($(prestashop.themeSelectors.product.activeTabs).attr("href")).addClass("active").removeClass("fade");
//     $(prestashop.themeSelectors.product.imagesModal).replaceWith(event.product_images_modal);

//     const productSelect = new ProductSelect();
//     productSelect.init();
//   });
// });
// //////////////////////////////////


/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE.
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

/* =========================
   Helpers: class building
   ========================= */

function norm(v) {
  return (v || "").toString().trim().toLowerCase().replace(/\s+/g, "-");
}

/** build Smarty-like classes for images section */
function buildProductImageClasses({ shape, type, size, frame, piece }) {
  const _shape = norm(shape);
  const _type = norm(type);
  const _size = norm(size);
  const _frame = norm(frame);
  const _piece = norm(piece) || "pc1";

  const class_01 = _shape ? `shape-${_shape}` : "";
  const class_02 = _type ? `type-${_type}` : "";
  const class_03 = _size ? `size-${_size}` : "";
  const class_04 = _frame ? `frame-${_frame}` : "";

  const art_code = [_shape, _type, _size, _frame].filter(Boolean).join("-");

  // default base; replaced per target section later
  const base = "product-images-box";

  return [base, _piece, class_01, class_02, class_03, class_04, art_code]
    .filter(Boolean)
    .join(" ");
}

/** per-section static class snapshots so we don't erase custom classes */
const __PI_STATIC_BY_ID__ = {};

/** classes to ALWAYS preserve from the current element (mockup view classes) */
function extractViewClasses(el) {
  if (!el) return [];
  return el.className
    .split(/\s+/)
    .filter(
      (c) =>
        /^(room|canvas|square)$/.test(c) || /^position-\d+$/.test(c) // keep these
    );
}

/** apply dynamic classes while preserving static+view classes */
function setSectionClasses(id, base, values) {
  const el = document.getElementById(id);
  if (!el) return;

  // capture static classes once (anything not part of dynamic set)
  if (!__PI_STATIC_BY_ID__[id]) {
    const keep = el.className
      .split(/\s+/)
      .filter(
        (c) =>
          c &&
          c !== "pc1" &&
          c !== "pc3" &&
          !/^shape-/.test(c) &&
          !/^type-/.test(c) &&
          !/^size-/.test(c) &&
          !/^frame-/.test(c) &&
          !/^[a-z0-9-]+-[a-z0-9-]+-[a-z0-9-]+-[a-z0-9-]+$/.test(c) // art_code-like
      );
    __PI_STATIC_BY_ID__[id] = keep.length ? keep.join(" ") : base;
  }

  // preserve current view classes (room/canvas/square/position-XX)
  const viewKeep = extractViewClasses(el);

  // build images-class string, then swap base for this section's static base
  const built = buildProductImageClasses(values);
  const dynamicPart = built.replace(/^product-images-box/, __PI_STATIC_BY_ID__[id]);

  // combine: dynamic + preserved view classes (dedup)
  const finalClasses = Array.from(
    new Set(
      (dynamicPart + " " + viewKeep.join(" ")).trim().split(/\s+/)
    )
  ).join(" ");

  el.className = finalClasses;
}

/** extract attributes from updatedProduct payload */
function extractAttributesFromEvent(event) {
  if (event && event.product && event.product.attributes) {
    return event.product.attributes;
  }
  if (event && typeof event.product_details === "string") {
    const m = event.product_details.match(/data-product="([^"]+)"/);
    if (m) {
      try {
        const decoded = m[1].replace(/&quot;/g, '"').replace(/&amp;/g, "&");
        const dp = JSON.parse(decoded);
        if (dp && dp.attributes) return dp.attributes;
      } catch (_) {}
    }
  }
  return null;
}

/** update BOTH #product-images and #product-Options and #description*/
function updateProductSectionsFromEvent(
  event,
  groupMap = { shape: "5", type: "6", size: "7", frame: "8" }
) {
  const attrs = extractAttributesFromEvent(event);

  if (attrs) {
    const values = {
      shape: attrs[groupMap.shape] ? attrs[groupMap.shape].name : undefined,
      type: attrs[groupMap.type] ? attrs[groupMap.type].name : undefined,
      size: attrs[groupMap.size] ? attrs[groupMap.size].name : undefined,
      frame: attrs[groupMap.frame] ? attrs[groupMap.frame].name : undefined,
      piece:
        attrs[groupMap.size] && attrs[groupMap.size].name === "3-PIECE"
          ? "pc3"
          : "pc1",
    };

    setSectionClasses("product-images", "product-images-box", values);
    setSectionClasses("product-Options", "product-Options-box", values);
    setSectionClasses("description", "product-description-box", values);
    return;
  }

  // Fallback: copy from incoming HTML (rare)
  const html =
    (event && (event["product-images"] || event["product_images"])) || null;
  if (html) {
    const tmp = document.createElement("div");
    tmp.innerHTML = html;

    // product-images
    {
      const incoming = tmp.querySelector("#product-images");
      const el = document.getElementById("product-images");
      if (incoming && el) {
        const base = __PI_STATIC_BY_ID__["product-images"] || "product-images-box";
        const dyn = incoming.className.replace(base, "").trim();
        const viewKeep = extractViewClasses(el);
        el.className = [base, dyn, ...viewKeep].filter(Boolean).join(" ").trim();
      }
    }
    // product-Options
    {
      const incoming = tmp.querySelector("#product-Options");
      const el = document.getElementById("product-Options");
      if (incoming && el) {
        const base = __PI_STATIC_BY_ID__["product-Options"] || "product-Options-box";
        const dyn = incoming.className.replace(base, "").trim();
        const viewKeep = extractViewClasses(el);
        el.className = [base, dyn, ...viewKeep].filter(Boolean).join(" ").trim();
      }
    }
    // description
    {
      const incoming = tmp.querySelector("#description");
      const el = document.getElementById("description");
      if (incoming && el) {
        const base = __PI_STATIC_BY_ID__["description"] || "description-box";
        const dyn = incoming.className.replace(base, "").trim();
        const viewKeep = extractViewClasses(el);
        el.className = [base, dyn, ...viewKeep].filter(Boolean).join(" ").trim();
      }
    }
  }
}

/* =========================
   Page behaviors
   ========================= */

$(document).ready(() => {
  function coverImage() {
    const productCover = $(prestashop.themeSelectors.product.cover);
    const modalProductCover = $(prestashop.themeSelectors.product.modalProductCover);

    let thumbSelected = $(prestashop.themeSelectors.product.selected);

    const swipe = (selectedThumb, thumbParent) => {
      const newSelectedThumb = thumbParent.find(prestashop.themeSelectors.product.thumb);

      selectedThumb.removeClass("selected");
      newSelectedThumb.addClass("selected");

      modalProductCover.prop("src", newSelectedThumb.data("image-large-src"));
      productCover.prop("src", newSelectedThumb.data("image-medium-src"));

      productCover.attr("title", newSelectedThumb.attr("title"));
      modalProductCover.attr("title", newSelectedThumb.attr("title"));
      productCover.attr("alt", newSelectedThumb.attr("alt"));
      modalProductCover.attr("alt", newSelectedThumb.attr("alt"));

      updateSources(productCover, newSelectedThumb.data("image-medium-sources"));
      updateSources(modalProductCover, newSelectedThumb.data("image-large-sources"));
    };

    $(prestashop.themeSelectors.product.thumb).on("click", (event) => {
      thumbSelected = $(prestashop.themeSelectors.product.selected);
      swipe(thumbSelected, $(event.target).closest(prestashop.themeSelectors.product.thumbContainer));
    });

    // keep room/canvas/square/position-* on the big box when clicking mockups
    const swipe2 = (boximage, selectedthumb) => {
      const thumbClasses = selectedthumb.attr("class");
      const classesToRemove = ["room", "canvas", "position-00", "position-01", "position-02", "position-03", "position-04", "square"];

      classesToRemove.forEach((className) => {
        if (boximage.hasClass(className)) boximage.removeClass(className);
      });

      boximage.addClass(thumbClasses);
    };

    $(prestashop.themeSelectors.product.mockup).on("click", (event) => {
      let imagebox = $(prestashop.themeSelectors.product.imagebox);
      swipe2(imagebox, $(event.target));
    });

    productCover.swipe({
      swipe: (event, direction) => {
        thumbSelected = $(prestashop.themeSelectors.product.selected);
        const parentThumb = thumbSelected.closest(prestashop.themeSelectors.product.thumbContainer);

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
      if ($quantityInput.val() === "" || $quantityInput.val() < $quantityInput.attr("min")) {
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
      $(target.attr("href")).addClass(prestashop.themeSelectors.product.activeTabClass);
    });
    nav.on("hide.bs.tab", (e) => {
      const target = $(e.target);
      target.removeClass(prestashop.themeSelectors.product.activeNavClass);
      $(target.attr("href")).removeClass(prestashop.themeSelectors.product.activeTabClass);
    });
  }

  // initial boot
  createProductSpin();
  createInputFile();
  coverImage();
  imageScrollBox();
  addJsProductTabActiveSelector();

  // when the product JSON (variant) changes
  prestashop.on("updatedProduct", (event) => {
    createInputFile();
    coverImage();

    // update BOTH sections' classes from AJAX payload (preserving view classes)
    setTimeout(() => updateProductSectionsFromEvent(event), 0);

    if (event && event.product_minimal_quantity) {
      const minimalProductQuantity = parseInt(event.product_minimal_quantity, 10);
      const quantityInputSelector = prestashop.selectors.quantityWanted;
      const quantityInput = $(quantityInputSelector);
      quantityInput.trigger("touchspin.updatesettings", { min: minimalProductQuantity });
    }

    imageScrollBox();
    $($(prestashop.themeSelectors.product.activeTabs).attr("href")).addClass("active").removeClass("fade");
    $(prestashop.themeSelectors.product.imagesModal).replaceWith(event.product_images_modal);

    const productSelect = new ProductSelect();
    productSelect.init();
  });
});

document.addEventListener("DOMContentLoaded", function() {
  const container = document.getElementById("read-more-container");
  const button = document.getElementById("read-more-toggle");

  if (container && button) {
    button.addEventListener("click", function() {
      container.classList.toggle("expanded");
      button.textContent = container.classList.contains("expanded")
        ? "Read less"
        : "Read more";
    });
  }
});