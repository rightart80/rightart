/**
 * Copyright 2022 ModuleFactory
 *
 * @author    ModuleFactory
 * @copyright ModuleFactory all rights reserved.
 * @license   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

var FSAU = FSAU || { };
FSAU.hash = '';

$(document).ready(function(){
    FSAU.anchor = window.location.hash;
    if (FSAU.anchor) {
        var refresh_url = false;
        for (var anchor in FSAU.product_urls) {
            if (anchor == FSAU.anchor) {
                if (!FSAU.product_urls.hasOwnProperty(anchor)) {
                    continue;
                }
                refresh_url = FSAU.product_urls[anchor];
            }
        }

        if (refresh_url) {
            prestashop.emit('updateProduct', { reason: { productUrl: refresh_url }});
        }
    }
});
