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
use Tools;

class PromoCode extends BaseModel
{
    public $id;
    public $id_promo_main;
    public $code;
    public $status;
    public $sent_to_mc;
    public $email;
    public $date_reddeem;
    public $id_cart;
    public $date_add;
    public $date_upd;

    private $campaign;

    public static $definition = [
        'table'   => 'mailchimppro_promocodes',
        'primary' => 'id',
        'fields'  => [
            'id_promo_main' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'code'          => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'status'        => ['type' => self::TYPE_INT, 'validate' => 'isBool', 'required' => true],
            'sent_to_mc'    => ['type' => self::TYPE_INT, 'validate' => 'isBool', 'required' => false],
            'email'         => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => false, 'size' => 255],
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'date_reddeem' => ['type' => self::TYPE_DATE, 'validate' => 'isDateOrNull'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public static function getByCode($code)
    {
        $sql = new DbQuery();
        $sql->select('*')
            ->from('mailchimppro_promocodes')
            ->where('`code` = "' . $code . '"');

        return Db::getInstance()->getRow($sql);
    }

    public static function getInstanceByCode($code)
    {
        $id = (int) Db::getInstance()->getValue(
            'SELECT id FROM ' . _DB_PREFIX_ . 'mailchimppro_promocodes WHERE code = \'' . pSQL(trim($code)) . '\''
        );

        return new self($id);
    }

    public static function getInstanceByEmailAndCampaign($email, $campaign_id)
    {
        $id = (int) Db::getInstance()->getValue(
            'SELECT id FROM ' . _DB_PREFIX_ . 'mailchimppro_promocodes WHERE id_promo_main = \'' . (int) $campaign_id . '\'' . ' AND  email = \'' . pSQL(trim($email)) . '\''
        );

        return new self($id);
    }

    public static function getInstanceByCartId($idCart)
    {
        $id = (int) Db::getInstance()->getValue(
            'SELECT id FROM ' . _DB_PREFIX_ . 'mailchimppro_promocodes WHERE id_cart = \'' . (int) $idCart . '\''
        );

        return new self($id);
    }

    public static function getPromoCodes($id)
    {
        $sql = new DbQuery();

        $sql->select('id, code, email, status')
            ->from('mailchimppro_promocodes')
            ->where('`id_promo_main` = "' . (int)$id . '"');

        return Db::getInstance()->executeS($sql);
    }

    public static function setStatusUsed($code)
    {
        return Db::getInstance()->update(
            'mailchimppro_promocodes',
            ['status' => 1],
            'code = \'' . $code . '\''
        );
    }

    public function isUsedForOrder()
    {
        return $this->id_cart && \Order::getIdByCartId($this->id_cart);
    }

    public function checkCustomer($customer)
    {
        return $customer && $customer->email == $this->email;
    }

    public function checkCampaign()
    {
        return $this->getCampaign()->status == 1;
    }

    public function getCampaign()
    {
        if (!is_null($this->campaign)) {
            return $this->campaign;
        }

        return $this->campaign = new Campaign($this->id_promo_main);
    }

    public static function getCartRuleId()
    {
        return (int) \Configuration::get(\MailchimpProConfig::MAILCHIMP_CART_RULE_ID);
    }

    public static function setCartRuleId($id)
    {
        return \Configuration::updateValue(\MailchimpProConfig::MAILCHIMP_CART_RULE_ID, $id);
    }

    public function getReductionType()
    {
        return (int) $this->getCampaign()->reduction_type;
    }

    public function getReductionPercent()
    {
        return $this->getReductionType() == Campaign::REDUCTION_TYPE_PERCENT
            ? (float) $this->getCampaign()->reduction
            : 0.0;
    }

    public function getReductionAmount()
    {
        return $this->getReductionType() == Campaign::REDUCTION_TYPE_AMOUNT
            ? (float) $this->getCampaign()->reduction
            : 0.0;
    }

    public static function generateCode($prefix, $suffix = '', $length = 10)
    {
        do {
            $reference = $prefix . strtoupper(Tools::passwdGen($length)) . $suffix;

            $exists = Db::getInstance()->getValue('
            SELECT COUNT(*)
            FROM ' . _DB_PREFIX_ . 'mailchimppro_promocodes
            WHERE code = "' . pSQL($reference) . '"
        ');
        } while ($exists > 0);

        return $reference;
    }
}
