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

namespace PrestaChamps\MailchimpPro\Models;

if (!defined('_PS_VERSION_')) {
    exit;
}

use DbQuery;
use Db;

class Campaign extends BaseModel
{
    const REDUCTION_TYPE_AMOUNT = 0;
    const REDUCTION_TYPE_PERCENT = 1;

    public $id;
    public $name;
    public $prefix;
    public $suffix;
    public $status;
    public $campaign_id;
    public $id_merge_field_mc;
    public $tag_merge_field_mc;
    public $reduction_type = 0;
    public $reduction;
    public $end_date;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table'   => 'mailchimppro_campaign',
        'primary' => 'id',
        'fields'  => [
            'name'         => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'prefix'       => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'suffix'       => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
            'status'       => ['type' => self::TYPE_INT, 'validate' => 'isBool', 'required' => true],
            'campaign_id'   => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
            'id_merge_field_mc' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'tag_merge_field_mc'    => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
            'reduction'         => ['type' => self::TYPE_STRING, 'validate' => 'isFloat', 'required' => true, 'size' => 255],
            'reduction_type'       => ['type' => self::TYPE_INT, 'validate' => 'isBool', 'required' => true],
            'end_date'     => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public static function isDuplicate($name, $prefix)
    {
        $sql = new DbQuery();
        $sql->select('COUNT(*)')
            ->from('mailchimppro_campaign')
            ->where('`name` = "' . pSQL($name) . '" OR `prefix` = "' . pSQL($prefix) . '"');
        return (bool) Db::getInstance()->getValue($sql);
    }

    public static function getPromoCodes()
    {
        $sql = new DbQuery();
        $sql->select('*')
            ->from('mailchimppro_campaign')
            ->orderBy('id DESC');

        return Db::getInstance()->executeS($sql);
    }

    public static function getById($id)
    {
        $sql = new DbQuery();
        $sql->select('*')
            ->from('mailchimppro_campaign')
            ->where('`id` = "' . (int)$id . '"');

        return Db::getInstance()->getRow($sql);
    }

    public static function getCartRuleById($id)
    {
        $sql = new DbQuery();
        $sql->select('id_cart_rule')
            ->from('mailchimppro_campaign')
            ->where('`id` = "' . (int)$id . '"');

        return Db::getInstance()->getRow($sql);
    }

    public function getCodesCount()
    {
        $sql = new DbQuery();
        $sql->select('count(*)')->from(PromoCode::$definition['table'])->where('id_promo_main = ' . (int)$this->id);

        return (int)Db::getInstance()->getValue($sql);
    }

    public static function getExpectedCodesCount()
    {
        $emailTable = _DB_PREFIX_ . 'emailsubscription';
        $customerTable = _DB_PREFIX_ . 'customer';

        $tableExists = Db::getInstance()->getValue("
            SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() 
            AND table_name = '" . pSQL($emailTable) . "'
        ");

        $totalQuery = "
            SELECT COUNT(*) FROM (
                SELECT DISTINCT email FROM " . pSQL($customerTable) . " WHERE email != ''
                " . ($tableExists ? "
                UNION 
                SELECT DISTINCT email FROM " . pSQL($emailTable) . " WHERE email != ''
                " : "") . "
            ) combined
        ";

        return (int)Db::getInstance()->getValue($totalQuery);
    }

    public function getUsedCodeCount()
    {
        $sql = new DbQuery();
        $sql->select('count(*)')->from(PromoCode::$definition['table'])->where('id_promo_main = ' . (int)$this->id . ' AND `status` = 1');

        return (int)Db::getInstance()->getValue($sql);
    }

    public function getUsedCodeInOrderCount()
    {
        $sql = new DbQuery();
        $sql->select('count(o.id_order)')->from(PromoCode::$definition['table'], 'c')
        ->leftJoin('cart', 'ct', 'c.id_cart = ct.id_cart')
        ->leftJoin('orders', 'o', 'o.id_cart = ct.id_cart')
        ->where('id_promo_main = ' . (int)$this->id . ' AND o.id_order IS NOT NULL');

        return (int)Db::getInstance()->getValue($sql);
    }

    public function getUsedByDay($date_start, $date_end)
    {
        $sql = new DbQuery();
        $sql->select('COUNT(*) as count, DATE(c.date_reddeem) as date_red')
        ->from(PromoCode::$definition['table'], 'c')
        ->where('id_promo_main = ' . (int)$this->id . ' AND `status` = 1 AND date_reddeem >= \'' . pSQL($date_start) . '\' AND date_reddeem <= \'' . pSQL($date_end) . '\'')
        ->groupBy('date_red');

        return Db::getInstance()->executeS($sql);
    }

    public function getConversionByDay($date_start, $date_end)
    {
        $sql = new DbQuery();
        $sql->select('DATE(c.date_reddeem) as date_red, SUM(IF(ct.id_cart is not null, 1, 0)) as used_in_cart, SUM(IF(o.id_order is not null, 1, 0)) as used_in_order, COUNT(*) as all_count')
        ->from(PromoCode::$definition['table'], 'c')
        ->leftJoin('cart', 'ct', 'c.id_cart = ct.id_cart')
        ->leftJoin('orders', 'o', 'o.id_cart = ct.id_cart')
        ->where('id_promo_main = ' . (int)$this->id . ' AND `status` = 1 AND date_reddeem >= \'' . pSQL($date_start) . '\' AND date_reddeem <= \'' . pSQL($date_end) . '\'')
        ->groupBy('date_red');

        return Db::getInstance()->executeS($sql);
    }

    public function isExpired()
    {
        return !$this->end_date
            || $this->end_date == '0000-00-00 00:00:00'
            || strtotime($this->end_date) <= time()
        ;
    }

    public function deleteCodes()
    {
        return Db::getInstance()->delete('mailchimppro_promocodes', 'id_promo_main = ' . (int)$this->id);
    }

    // Method to fetch all active campaigns
    public static function getActiveCampaigns()
    {
        $sql = new DbQuery();
        $sql->select('*')
            ->from('mailchimppro_campaign')
            ->where('status = 1 AND end_date > NOW()')
            ->orderBy('id DESC');

        return Db::getInstance()->executeS($sql);        
    }

    // Method to fetch all active campaigns and the promo code from the campaign by email
    public static function getActiveCampaignsWithPromoCodes($email)
    {
        $currentDate = date('Y-m-d H:i:s');

        // Fetch active campaigns
        $activeCampaigns = self::getActiveCampaigns();
        
        $result = [];

        foreach ($activeCampaigns as $campaign) {
            // Check if promo code exists for this email and campaign
            $promoCode = PromoCode::getInstanceByEmailAndCampaign($email, $campaign['id']);

            if (!\Validate::isLoadedObject($promoCode)) {
                // Generate new promo code
                $newCode = PromoCode::generateCode($campaign['prefix'], $campaign['suffix']);

                $promoCode = new PromoCode();

                $promoCode->id_promo_main = (int)$campaign['id'];
                $promoCode->code = pSQL($newCode);
                $promoCode->status = 0;
                $promoCode->email = pSQL($email);
                $promoCode->date_add = pSQL($currentDate);
                $promoCode->date_upd = pSQL($currentDate);

                $promoCode->add();

            }

            $result[] = [
                'campaign' => $campaign,
                'promo_code' => $promoCode
            ];
        }

        return $result;
    }

    // Method to fetch all syncronized and expired campaigns
    public static function getExpiredSyncedCampaigns()
    {
        $sql = new DbQuery();
        $sql->select('*')
            ->from('mailchimppro_campaign')
            ->where('end_date < NOW() AND id_merge_field_mc > 0 AND tag_merge_field_mc != ""')
            ->orderBy('id DESC');

        return Db::getInstance()->executeS($sql);        
    }
}
