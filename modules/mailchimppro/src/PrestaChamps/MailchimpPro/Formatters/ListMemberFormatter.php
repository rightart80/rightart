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

/**
 * Class ListMemberFormatter
 *
 * @package PrestaChamps\MailchimpPro\Formatters
 */
class ListMemberFormatter
{
    const EMAIL_TYPE_HTML = 'html';
    const EMAIL_TYPE_TEXT = 'text';

    const STATUS_SUBSCRIBED = 'subscribed';
    const STATUS_TRANSACTIONAL = 'transactional';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    const STATUS_CLEANED = 'cleaned';
    const STATUS_PENDING = 'pending';

    public $customer;
    public $address;
    public $context;
    public $status;
    public $emailType;
    protected $updateSubscriptionStatus = true;

    protected $idStore;

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
    public function __construct(\Customer $customer, \Context $context, $status, $emailType, \Address $address = null, $updateSubscriptionStatus = true, $idStore = null)
    {
        $this->customer = $customer;
        $this->address = $address;
        $this->context = $context;
        $this->status = $status;
        $this->emailType = $emailType;
        $this->updateSubscriptionStatus = $updateSubscriptionStatus;
        $this->idStore = $idStore ? $idStore : $this->context->shop->id;
    }

    /**
     * Set updateSubscriptionStatus feature
     *
     * @param bool $update
     */
    public function setUpdateSubscriptionStatus($update = true)
    {
        $this->updateSubscriptionStatus = (bool)$update;
    }

    /**
     * @return array
     */
    public function format()
    {
        /* $customer = $this->customer;
        $addresses = $customer->getAddresses(\Configuration::get('PS_LANG_DEFAULT'));
        if (!empty($addresses)) {
            $address = reset($addresses);
            $address = new \Address($address['id_address'], \Configuration::get('PS_LANG_DEFAULT'));

        } else {
            $address = null;
        } */
		
		if (!$this->address) {
            $this->clearCustomerAddressesCache();
            $addresses = $this->customer->getAddresses(\Configuration::get('PS_LANG_DEFAULT'));
			if (!empty($addresses)) {
				$address = reset($addresses);
				$this->address = new \Address($address['id_address'], \Configuration::get('PS_LANG_DEFAULT'));
			}
        }

		$language = new \Language($this->customer->id_lang);
        $customer_iso_code = \Mailchimppro::getCustomerLanguageIsoCode($language->iso_code);
        
        if ($this->address) {

            $state = ($this->address && $this->address->id_state) ? \State::getNameById($this->address->id_state) : '';
            $country_iso = ($this->address && $this->address->id_country) ? \Country::getIsoById($this->address->id_country) : '';

            if(!$this->address->id_country){
                $shop_country = (new \Country(\Configuration::get('PS_COUNTRY_DEFAULT')))->iso_code;
                
                if($country_iso == ''){
                    $country_iso = $shop_country;
                }
                // need to check on multi store
            }

            $customer_address = [
                    "addr1"   => ($this->address && $this->address->address1) ? $this->address->address1 : '-',
                    "addr2"   => ($this->address && $this->address->address2) ? $this->address->address2 : '',
                    "city"    => ($this->address && $this->address->city) ? $this->address->city : '-',
                    "state"   => ($this->address && $this->address->id_state && $state) ? $state : '-',
                    "zip"     => ($this->address && $this->address->postcode) ? $this->address->postcode : '-',
                    "country" => ($this->address && $this->address->id_country && $country_iso) ? $country_iso : '',
                ];
        }else{
            $customer_address = '';
        }

        if($this->customer->birthday && $this->customer->birthday != "0000-00-00"){
            $customer_birthday = date("m/d", strtotime($this->customer->birthday));
        }else{
            $customer_birthday = "";
        }

        $promo_campaigns = Campaign::getActiveCampaignsWithPromoCodes($this->customer->email);

        $extra_fields = [];
        if(count($promo_campaigns) > 0){
            foreach($promo_campaigns as $promo_campaign){

                if(!(is_null($promo_campaign['campaign']['id_merge_field_mc']) || $promo_campaign['campaign']['id_merge_field_mc'] == 0)){
                    $extra_fields[$promo_campaign['campaign']['tag_merge_field_mc']] = $promo_campaign['promo_code']->code;
                }
            }
        }

        $data = [
            'email_address'    => $this->customer->email,
            'email_type'       => $this->emailType,
            'language'         => $customer_iso_code,
            'timestamp_signup' => gmdate('Y-m-d H:i:s', time()),
            'merge_fields'     => [
                'FNAME'    => $this->customer->firstname,
                'LNAME'    => $this->customer->lastname,
                'BIRTHDAY' => $customer_birthday,
                'ADDRESS'  => $customer_address,
                /* 'GROUP' => (\Group::isFeatureActive())
                    ? (new \Group($this->customer->id_default_group, \Configuration::get('PS_LANG_DEFAULT')))->name
                    : 'Default group',
                'CITY'     => ($this->address) ? $this->address->city : '',
                'STATE'    => ($this->address) ? \State::getNameById($this->address->id_state) : '',
                'ZIP'      => ($this->address) ? $this->address->postcode : '',
                'COUNTRY'  => ($this->address) ? $this->address->country : '',
                'COMPANY'  => $this->customer->company ? $this->customer->company : '', */
            ],
        ];

        if (count($extra_fields) > 0) {
            $data['merge_fields'] = array_merge($data['merge_fields'], $extra_fields);
        }

        if ($this->address && !empty($this->address->phone)) {
            $data['merge_fields']['PHONE'] = $this->address->phone;
        }

        if ($this->address && !empty($this->address->phone_mobile)) {
            $data['merge_fields']['PHONE'] = $this->address->phone_mobile;
        }

        elseif (!isset($data['merge_fields']['PHONE'])) {
            $data['merge_fields']['PHONE'] = '';
        }

        if ($this->updateSubscriptionStatus) {
            $data['status'] = $this->status;
        }

        return $data;
    }

    /**
     * Clears the customer addresses cache
     */
    protected function clearCustomerAddressesCache() {
        $group = $this->context->shop->getGroup();
        $shareOrder = isset($group->share_order) ? (bool) $group->share_order : false;
        $cacheId = 'Customer::getAddresses'
            . '-' . (int) $this->customer->id
            . '-' . (int) \Configuration::get('PS_LANG_DEFAULT')
            . '-' . ($shareOrder ? 1 : 0);

        \Cache::clean($cacheId);
    }
}
