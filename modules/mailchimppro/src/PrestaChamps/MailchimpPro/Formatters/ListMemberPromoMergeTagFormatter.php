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
 * @author    PrestaChamps <leo@prestachamps.com>
 * @copyright PrestaChamps
 * @license   commercial
 */

namespace PrestaChamps\MailchimpPro\Formatters;
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailchimpPro\Models\Campaign;
use PrestaChamps\MailchimpPro\Models\PromoCode;

class ListMemberPromoMergeTagFormatter
{

    public $context;
    public $campaign;
    public $idStore;
    public $promo_code;

    /**
     * ListMemberFormatter constructor.
     *
     * @param \Customer $customer
     * @param \Context $context
     * @param           $status
     * @param           $emailType
     * @param \Address $address
     * @param  bool     $updateSubscriptionStatus
     */
    public function __construct(PromoCode $promo_code, Campaign $campaign, \Context $context, $idStore = null)
    {
        $this->context = $context;
        $this->promo_code = $promo_code;
        $this->campaign = $campaign;
        $this->idStore = $idStore ? $idStore : $this->context->shop->id;
    }

    /**
     * @return array
     */
    public function format()
    {
        $data = [
            'email_address'    => $this->promo_code->email,
            'merge_fields'     => [
                $this->campaign->tag_merge_field_mc  => $this->promo_code->code,
            ],
        ];

        return $data;
    }

}
