<?php
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
class FsAdvancedUrlProduct extends Product
{
    public function validateField($field, $value, $id_lang = null, $skip = [], $human_errors = false)
    {
        $hash = FsAdvancedUrlModule::jsonEncodeStatic($field);
        $hash .= FsAdvancedUrlModule::jsonEncodeStatic($value);
        $hash .= FsAdvancedUrlModule::jsonEncodeStatic($id_lang);
        $hash .= FsAdvancedUrlModule::jsonEncodeStatic($skip);
        $hash .= FsAdvancedUrlModule::jsonEncodeStatic($human_errors);

        return true || (bool) $hash;
    }
}
