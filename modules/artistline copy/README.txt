Artist Line (PrestaShop 8.x) v1.0.1
=================================

This module exposes the artist category (child of parent category ID=10) to your product page.

Fixes
-----
- Replaced deprecated/undefined Category::getProductCategoriesFull()
  with Product::getProductCategoriesFull() and a safe fallback to
  Product::getProductCategories() + hydrating Category objects.

Install
-------
1) Upload `artistline_fixed.zip` in Back Office → Modules → Upload a module, then Install.
   OR unzip and copy `artistline` folder to /modules/ and install from Module Manager.

2) Ensure your product page template calls the hook where you want the line to appear.
   Example (under the H1 in product.tpl):
      {hook h='displayProductAdditionalInfo' product=$product}

3) Ensure your products are assigned to an artist subcategory whose parent category ID is 10.
   If your "Artist" parent has a different ID, edit modules/artistline/artistline.php:
      $artistParentId = 10;

4) Clear cache while testing: Back Office → Advanced Parameters → Performance
   - Force compile: Yes
   - Cache: No
   Then click "Clear cache".
