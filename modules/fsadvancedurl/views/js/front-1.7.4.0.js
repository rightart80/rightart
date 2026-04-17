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

$(document).ready(function(){
    if (window.location.hash) {
        var refresh_params = false;
        for (var anchor in FSAU.product_urls) {
            if (anchor === window.location.hash) {
                console.log(anchor);
                if (!FSAU.product_urls.hasOwnProperty(anchor)) {
                    continue;
                }
                refresh_params = FSAU.product_urls[anchor];
            }
        }

        if (refresh_params) {
            for (var i in refresh_params) {
                if (!refresh_params.hasOwnProperty(i)) {
                    continue;
                }
                var param_group = refresh_params[i].group;
                var param_value = refresh_params[i].value;
                var name_selector = 'group['+param_group+']';

                var input_type = $('input[name=\''+name_selector+'\']').attr('type');
                if (input_type === undefined) {
                    input_type = 'select';
                }

                if (input_type === 'select') {
                    $('#group_'+param_group).val(param_value);
                }

                if (input_type === 'radio') {
                    $('input[name=\''+name_selector+'\']').prop('checked', '');
                    $('input[name=\''+name_selector+'\'][value=\''+param_value+'\']').prop('checked', 'checked');
                }
            }

            prestashop.emit('updateProduct', { eventType: 'updatedProductCombination', event: null });
        }
    }
});
