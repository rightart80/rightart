<?php
/**
 * PrestaChamps
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
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailchimpPro\Models\Campaign;

/**
 * Class AdminMailchimpProListsController
 *
 * @property Mailchimppro $module
 */
class AdminMailchimpProStatsController extends \PrestaChamps\MailchimpPro\Controllers\BaseMCObjectController
{

    private $requestData;

    public function postProcess()
    {
        if ($this->ajax) {
            $jsonInput = file_get_contents('php://input');

            $this->requestData = json_decode($jsonInput, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->jsonReponse(400, ['success' => false, 'message' =>  $this->trans('Bad request', [], 'Modules.Mailchimppro.Adminmailchimpprostats')]);
            }
        }

        parent::postProcess();
    }

    public function ajaxProcessGetStats()
    {

        if (!isset($this->requestData['idPromo'])) {
            $this->jsonReponse(200, ['success' => false, 'message' =>  $this->trans('Missing promo', [], 'Modules.Mailchimppro.Adminmailchimpprostats')]);
        }
        
        $campaign = new Campaign($this->requestData['idPromo']);

        $allCodes = $campaign->getCodesCount();
        $usedCodes = $campaign->getUsedCodeCount();
        $unUsedCodes = $allCodes - $usedCodes;

        $usedInOrderCodes = $campaign->getUsedCodeInOrderCount();

        $conversion =  $allCodes ? round(($usedInOrderCodes / $allCodes) * 100, 2) : 0;

        $pieData = [
            'labels' => [$this->trans('Used', [], 'Modules.Mailchimppro.Adminmailchimpprostats'), $this->trans('Unused', [], 'Modules.Mailchimppro.Adminmailchimpprostats')],
            'series' => [$usedCodes, $unUsedCodes],
            'colors' => ['#ffe01b', '#241c15'],
        ];


        //prepare bar data
        $start = new DateTime($campaign->date_add);
        $end = new DateTime($campaign->end_date);
        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);

        $barData = $this->getBarData($start, $end, $campaign);
        $conversionData = $this->getConversionData($start, $end, $campaign);


        $response = [
            'success' => true,
            'campaign' => [
                'name' => $campaign->name,
                'start_date' => $campaign->date_add,
                'end_date' => $campaign->end_date,
            ],
            'stats' => [
                'all_codes' => $campaign->getCodesCount(),
                'used_codes' => $usedCodes,
                'unused_codes' => $unUsedCodes,
                'conversion' => $conversion,
                'frequency' => $this->getFrequency($campaign),
            ],
            'pieData' => $pieData,
            'barData' => $barData,
            'conversionData' => $conversionData,
        ];

        $this->jsonReponse(200, $response);
    }

    public function ajaxProcessGetStatsByDate()
    {
        if (!isset($this->requestData['idPromo'])) {
            $this->jsonReponse(200, ['success' => false, 'message' =>  $this->trans('Missing promo', [], 'Modules.Mailchimppro.Adminmailchimpprostats')]);
        }

        $campaign = new Campaign($this->requestData['idPromo']);

        if (!(isset($this->requestData['dateStart']) && !empty($this->requestData['dateStart']))) {
            $this->jsonReponse(200, ['success' => false, 'message' =>  $this->trans('Invalid date start', [], 'Modules.Mailchimppro.Adminmailchimpprostats')]);
        }

        if (!(isset($this->requestData['dateEnd']) && !empty($this->requestData['dateEnd']))) {
            $end = new DateTime($campaign->end_date);
            $end->setTime(23, 59, 59);
        }else{
            $end = new DateTime($this->requestData['dateEnd']);
        }

     

        $start = new DateTime($this->requestData['dateStart']);
       
        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);


        $barData = $this->getBarData($start, $end, $campaign);
        $conversionData = $this->getConversionData($start, $end, $campaign);

        $response = [
            'success' => true,
            'barData' => $barData,
            'conversionData' => $conversionData,
        ];

        $this->jsonReponse(200, $response);
    }

    private function jsonReponse(int $httpCode, $data)
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');

        $response = json_encode($data);

        $this->ajaxRender($response);
        exit;
    }

    private function getBarData($start, $end, $campaign)
    {

        $usageByDay = $campaign->getUsedByDay($start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));

        $dateInterval = new DateInterval('P1D'); // Interval of 1 day
        $period = new DatePeriod($start, $dateInterval,  $end);


        $barData = [];
        $barData['mainLabel'] = $this->trans('Code usage per day', [], 'Modules.Mailchimppro.Adminmailchimpprostats');
        $barData['seriesLabel'] = $this->trans('Used', [], 'Modules.Mailchimppro.Adminmailchimpprostats');
        foreach ($period as $key => $day) {
            $label = $day->format('Y-m-d');
            $barData['labels'][$key] = $label;
            $barData['series'][$key] = 0;
            foreach ($usageByDay as $usage) {
                if ($usage['date_red'] == $label) {
                    $barData['series'][$key] = (int)$usage['count'];
                    break;
                }
            }
          
        }
        return $barData;
    }

    private function getConversionData($start, $end, $campaign)
    {
        $conversion = $campaign->getConversionByDay($start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));

        $dateInterval = new DateInterval('P1D');
        $period = new DatePeriod($start, $dateInterval, $end);

        $barData = [];
        $barData['mainLabel'] = $this->trans('Conversion rate', [], 'Modules.Mailchimppro.Adminmailchimpprostats');
        $barData['seriesLabel'] = $this->trans('Conversion', [], 'Modules.Mailchimppro.Adminmailchimpprostats');
        foreach ($period as $key => $day) {
            $label = $day->format('Y-m-d');
            $barData['labels'][$key] = $label;
            $barData['seriesOrderCart'][$key] = 0;
            foreach ($conversion as $usage) {
                if ($usage['date_red'] == $label) {

                    $barData['seriesOrderCart'][$key] = (int)$usage['used_in_cart'] != 0 ? ((int)$usage['used_in_order'] / (int)$usage['used_in_cart']) * 100 : 0;
                    break;
                }
            }
          
        }
        return $barData;
    }

    private function getFrequency($campaign)
    {
        
        
        $start = new DateTime($campaign->date_add);
        $end = new DateTime($campaign->end_date);
        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);

        $now = (new DateTime())->setTime(23, 59, 59);

        $usedEndDate = $now > $end ? $end : $now;

        $usageForFrequency = $campaign->getUsedByDay($start->format('Y-m-d H:i:s'), $usedEndDate->format('Y-m-d H:i:s'));
        $count = 0;
        $entryCount = $start->diff($usedEndDate, true)->days + 1;
        

        foreach ($usageForFrequency as $usage) {
            $count += (int)$usage['count'];
        }

        $frequency = $entryCount ? round($count / $entryCount, 2) : 0;

        return $frequency;
    }
}
