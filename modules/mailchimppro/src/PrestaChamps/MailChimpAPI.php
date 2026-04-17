<?php
/**
 * MailChimp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */

namespace PrestaChamps;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Db;
use DbQuery;
use \DrewM\MailChimp\MailChimp as MailChimpClient;

/**
 * Class MailChimpAPI
 */
class MailChimpAPI extends MailChimpClient
{

    protected function makeRequest($http_verb, $method, $args = array(), $timeout = self::TIMEOUT)
    {
        $url = $this->api_endpoint . '/' . $method;

        $response = $this->prepareStateForRequest($http_verb, $method, $url, $timeout);

        $httpHeader = array(
            'Accept: application/vnd.api+json',
            'Content-Type: application/vnd.api+json',
            'Authorization: OAuth ' . $this->api_key
        );

        if (isset($args["language"])) {
            $httpHeader[] = "Accept-Language: " . $args["language"];
        }

        $ch = $this->prepareResource($url, $timeout, $httpHeader);

        switch ($http_verb) {
            case 'post':
                curl_setopt($ch, CURLOPT_POST, true);
                $this->attachRequestPayload($ch, $args);
                break;

            case 'get':
                $query = http_build_query($args, '', '&');
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $query);
                break;

            case 'delete':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;

            case 'patch':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                $this->attachRequestPayload($ch, $args);
                break;

            case 'put':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                $this->attachRequestPayload($ch, $args);
                break;
        }

        $responseContent = curl_exec($ch);
        $response['headers'] = curl_getinfo($ch);
        $response = $this->setResponseState($response, $responseContent, $ch);
        $formattedResponse = $this->formatResponse($response);

        curl_close($ch);

        $isSuccess = $this->determineSuccess($response, $formattedResponse, $timeout);

        // START OF LOG MC REQUESTS
        // get backtrace
        ob_start();
        debug_print_backtrace(2);
        $back_trace = ob_get_clean();

        // prepare log data to DB
        $insertData = array(
            'request_type' => pSQL($http_verb),
            'end_point' => pSQL($method),
            'is_success' => pSQL($isSuccess),
            'back_trace' => pSQL($back_trace),
            'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
        );

        $query = new DbQuery();
        $query->select('COUNT(*)');
        $query->from('mailchimppro_api_log'); // No need for _DB_PREFIX_ here, PrestaShop adds it automatically

        // Execute the query and get the count of rows
        $count = Db::getInstance()->getValue($query);

        $rows_to_keep = 10000; // Future implementation to get it from a variable

        if ($count > $rows_to_keep) {
            // Select the IDs of the oldest rows that need to be deleted (all except the newest $rows_to_keep rows)
            $subquery = new DbQuery();
            $subquery->select('id');
            $subquery->from('mailchimppro_api_log');
            $subquery->orderBy('id ASC');  // Order by ascending to get the oldest rows
            $subquery->limit($count - $rows_to_keep);  // Limit to the number of rows that need to be deleted

            // Fetch the IDs of the rows to delete
            $ids_to_delete = Db::getInstance()->executeS($subquery);

            if (!empty($ids_to_delete)) {
                // Extract the IDs to delete
                $ids = array_column($ids_to_delete, 'id');

                // Delete the rows with those IDs
                Db::getInstance()->delete('mailchimppro_api_log', 'id IN (' . implode(',', array_map('intval', $ids)) . ')');
            }
        }

        // insert request log data to DB
        \Db::getInstance()->insert('mailchimppro_api_log', $insertData);
        // END of LOG MC REQUESTS

        return is_array($formattedResponse) ? $formattedResponse : $isSuccess;
    }

    public function setUserAgent($moduleVersion = "")
    {
        $module_version = "mailchimppro_version: " . $moduleVersion;

        $presta_version = "prestashop_version: " . _PS_VERSION_;

        // Get the current shop context
        $context = \Context::getContext();

        // Get the shop name
        $shop_name = "shop_name: " . $context->shop->name;

        static::$USER_AGENT = 'MAILCHIMPPRO_PRESTASHOP' . ' |' . $shop_name . '|' . $presta_version .'|'. $module_version;
    }

}