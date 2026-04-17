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

namespace PrestaChamps\MailchimpPro\Formatters;
if (!defined('_PS_VERSION_')) {
    exit;
}

class AddressFormatter
{
    public $address;
    public $context;
    
    const DEFAULT_EMPTY_ADDRESS = [
        'name' => '',
        'address1' => '',
        'address2' => '',
        'city' => '',
        'province' => '',
        'postal_code' => '',
        'country' => '',
        'country_code' => '',
        'phone' => '',
        'company' => '',
    ];

    /**
     * AddressFormatter constructor.
     *
     * @param \Address $address
     * @param \Context $context
     */
    public function __construct(\Address $address, \Context $context)
    {
        $this->address = $address;
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function format($type = '')
    {
        $state = \State::getNameById($this->address->id_state);
        $country = \Country::getNameById($this->context->language->id, $this->address->id_country);
		$country_iso = \Country::getIsoById($this->address->id_country);

        $formatted_address = [
            'name'         => $this->address->alias ? $this->address->alias : '',
            'address1'     => $this->address->address1 ? $this->address->address1 : '',
            'address2'     => $this->address->address2 ? $this->address->address2 : '',
            'city'         => $this->address->city ? $this->address->city : '',
            'province'     => $state ? $state : '',
            'postal_code'  => $this->address->postcode ? $this->address->postcode : '',
            'country'      => $country_iso ? $country_iso : '',
            'country_code' => $country_iso ? $country_iso : '',
        ];

        if (!empty($this->address->phone)) {
            $formatted_address['phone'] = $this->address->phone;
        }
        if (!empty($this->address->phone_mobile)) {
            $formatted_address['phone'] = $this->address->phone_mobile;
        }
        if (!empty($this->address->company)) {
            $formatted_address['company'] = $this->address->company;
        }

        return $formatted_address;
    }
}
