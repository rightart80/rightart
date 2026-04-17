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
class FrontController extends FrontControllerCore
{
    protected function canonicalRedirection($canonical_url = '')
    {
        $fsau_params = [];
        if (Module::isEnabled('fsadvancedurl')) {
            $fsau = Module::getInstanceByName('fsadvancedurl');
            $info = $fsau->overrideCanonicalUrl($canonical_url);
            $canonical_url = $info['canonical_url'];
            if (isset($info['params']) && is_array($info['params']) && $info['params']) {
                $fsau_params = $info['params'];
            }
        }

        parent::canonicalRedirection($canonical_url);

        if ($fsau_params) {
            $_GET = array_merge($_GET, $fsau_params);
        }
    }

    protected function updateQueryString(array $extraParams = null)
    {
        $uriWithoutParams = explode('?', $_SERVER['REQUEST_URI']);
        if (isset($uriWithoutParams[0])) {
            $uriWithoutParams = $uriWithoutParams[0];
        }

        $url = Tools::getCurrentUrlProtocolPrefix() . $_SERVER['HTTP_HOST'] . $uriWithoutParams;

        if (Module::isEnabled('fsadvancedurl')) {
            $fsau = Module::getInstanceByName('fsadvancedurl');
            $url = $fsau->overrideUpdateQueryStringBaseUrl($url, $extraParams);
        }

        $params = [];
        parse_str($_SERVER['QUERY_STRING'], $params);

        if (null !== $extraParams) {
            foreach ($extraParams as $key => $value) {
                if (null === $value) {
                    unset($params[$key]);
                } else {
                    $params[$key] = $value;
                }
            }
        }

        ksort($params);

        if (null !== $extraParams) {
            foreach ($params as $key => $param) {
                if (null === $param || '' === $param) {
                    unset($params[$key]);
                }
            }
        } else {
            $params = [];
        }

        $queryString = str_replace('%2F', '/', http_build_query($params));

        return $url . ($queryString ? "?$queryString" : '');
    }
}
