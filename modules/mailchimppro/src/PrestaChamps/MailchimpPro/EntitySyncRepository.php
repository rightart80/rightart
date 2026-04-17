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

namespace PrestaChamps\MailchimpPro;
if (!defined('_PS_VERSION_')) {
    exit;
}
class EntitySyncRepository
{
    protected $db;
    /**
     * @var array
     */
    private $configuration;

    public function __construct()
    {
        $this->db = \Db::getInstance();
        $this->configuration = \MailchimpProConfig::getConfigurationValues();
    }

    protected function products()
    {
        $query = new \DbQuery();
        $query->from('product', 'p');
		
		$idShop = \Context::getContext()->shop->id;
		
		if ($idShop) {
			$query->innerJoin('product_shop', 'product_shop', 'product_shop.`id_product` = p.`id_product` AND product_shop.`id_shop` = ' . (int) $idShop);
		} else {
			$query->innerJoin('product_shop', 'product_shop', 'product_shop.`id_product` = p.`id_product`');
		}

        if (is_array($this->configuration[\MailchimpProConfig::PRODUCT_SYNC_FILTER_ACTIVE])) {
            $query->where('p.`active` IN (' . implode(',', $this->configuration[\MailchimpProConfig::PRODUCT_SYNC_FILTER_ACTIVE]) . ')');
        }

        if (is_array($this->configuration[\MailchimpProConfig::PRODUCT_SYNC_FILTER_VISIBILITY])) {
            $items = $this->configuration[\MailchimpProConfig::PRODUCT_SYNC_FILTER_VISIBILITY];
            array_walk($items, function (&$value) {
                $value = "'{$value}'";
            });
            $query->where('p.`visibility` IN (' . implode(',', $items) . ')');

        }

        return $query;
    }

    protected function orders()
    {
        $query = new \DbQuery();
        $query->from('orders', 'o');
		
		$idShop = \Context::getContext()->shop->id;
		
		if ($idShop) {
			$query->where('o.`id_shop` = ' . (int) $idShop);
		}
		
		/* $customers = array_column($this->getCustomers(), 'id_customer'); //email
		if ($customers) {
			$query->where('o.`id_customer` IN (' . implode(',', $customers) . ')');//email
		} */

        if (in_array($this->configuration[\MailchimpProConfig::EXISTING_ORDER_SYNC_STRATEGY], ["-1 months", "-3 months", "-6 months", "-1 year", "onlyNew"])) {
            if ($this->configuration[\MailchimpProConfig::EXISTING_ORDER_SYNC_STRATEGY] === 'onlyNew') {
                $query->where('o.`date_add` > "' . date('Y-m-d H:i:s', strtotime('-0 days')) . '"');
            } else {
                $query->where('o.`date_add` > "' . date('Y-m-d H:i:s', strtotime($this->configuration[\MailchimpProConfig::EXISTING_ORDER_SYNC_STRATEGY])) . '"');
            }
        }

        return $query;
    }

    protected function customers()
    {
        $query = new \DbQuery();
        $query->from('customer', 'c');
		
		$group = \Shop::getGroupFromShop(\Shop::getContextShopID(), false);
		
		if (\Shop::getContext() == \Shop::CONTEXT_SHOP && $group['share_customer']) {
			$query->where('c.`id_shop_group` = ' . (int) \Shop::getContextShopGroupID());
        } else {
            $query->where('c.`id_shop` IN (' . implode(', ', \Shop::getContextListShopID(\Shop::SHARE_CUSTOMER)) . ')');
        }

        if (in_array($this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_PERIOD], ["-1 months", "-3 months", "-6 months", "-1 year", "onlyNew"])) {
            if ($this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_PERIOD] === 'onlyNew') {
                $query->where('c.`date_add` > "' . date('Y-m-d H:i:s', strtotime('-0 days')) . '"');
            } else {
                $query->where('c.`date_add` > "' . date('Y-m-d H:i:s', strtotime($this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_PERIOD])) . '"');
            }
        }

        $query->where('c.`active` IN (' . implode(',', $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED]) . ')');
        $query->where('c.`newsletter` IN (' . implode(',', $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER]) . ')');

        return $query;
    }

    protected function cartRules()
    {
		$idShop = \Context::getContext()->shop->id;
		$restrictedCartRules = null;
		if ($idShop) {
			$dbQuery = new \DbQuery();
			$dbQuery->select('crs.`id_cart_rule`');
			$dbQuery->from('cart_rule_shop', 'crs');
			$dbQuery->where('crs.`id_shop` != ' . (int) $idShop);
			$restrictedCartRules = array_column($this->db->executeS($dbQuery), 'id_cart_rule');
		}
		
		$query = new \DbQuery();
        $query->from('cart_rule', 'c');
        $query->where('c.`active` IN (' . implode(',', $this->configuration[\MailchimpProConfig::CART_RULE_SYNC_FILTER_STATUS]) . ')');
		
		if (is_array($this->configuration[\MailchimpProConfig::CART_RULE_SYNC_FILTER_STATUS])) {
            if (!in_array('0', $this->configuration[\MailchimpProConfig::CART_RULE_SYNC_FILTER_STATUS])) {
                $query->where('c.`quantity` > 0');
            }
        }
		
		if (is_array($this->configuration[\MailchimpProConfig::CART_RULE_SYNC_FILTER_EXPIRATION])) {
            if (!in_array('0', $this->configuration[\MailchimpProConfig::CART_RULE_SYNC_FILTER_EXPIRATION])) {
                $query->where('c.`date_from` <= "' . date('Y-m-d H:i:s', strtotime('now')) . '"');
				$query->where('c.`date_to` >= "' . date('Y-m-d H:i:s', strtotime('now')) . '"');
            }
        }
		
		if ($restrictedCartRules) {
			$query->where('c.`id_cart_rule` NOT IN (' . implode(',', $restrictedCartRules) . ')');
		}

        return $query;
    }
	
	protected function newsletterSubscribers()
	{
		$query = new \DbQuery();
		
		if (\Module::isEnabled('Ps_Emailsubscription')) {
			$query->from('emailsubscription', 's');
		} elseif (\Module::isEnabled('blocknewsletter')) {
			$query->from('newsletter', 's');
		}
		
		$query->where('s.`active` = 1');

        if (in_array($this->configuration[\MailchimpProConfig::NEWSLETTER_SUBSCRIBER_SYNC_FILTER_PERIOD], ["-1 months", "-3 months", "-6 months", "-1 year", "onlyNew"])) {
            if ($this->configuration[\MailchimpProConfig::NEWSLETTER_SUBSCRIBER_SYNC_FILTER_PERIOD] === 'onlyNew') {
                $query->where('s.`newsletter_date_add` > "' . date('Y-m-d H:i:s', strtotime('-0 days')) . '"');
            } else {
                $query->where('s.`newsletter_date_add` > "' . date('Y-m-d H:i:s', strtotime($this->configuration[\MailchimpProConfig::NEWSLETTER_SUBSCRIBER_SYNC_FILTER_PERIOD])) . '"');
            }
        }
        
		$idShop = \Context::getContext()->shop->id;
		
		if ($idShop) {
			$query->where('s.`id_shop` = ' . (int) $idShop);
		}
		
		return $query;
	}

	protected function specificPrices()
    {
        $query = new \DbQuery();
        $query->from('specific_price', 'sp');

        $query->where('sp.`to` > NOW()');

        $idShop = \Context::getContext()->shop->id;

        if ($idShop) {
			$query->where('sp.`id_shop` = ' . (int) $idShop);
		}

        return $query;
    }

    public function getOrdersCount()
    {
        return (int)$this->db->getValue($this->orders()->select('count(*)'));
    }

    public function getOrders()
    {
        return $this->db->executeS($this->orders()->select('o.`id_order`'));
    }

    public function getProductsCount()
    {
        return (int)$this->db->getValue($this->products()->select('count(*)'));
    }

    public function getProducts()
    {
        return $this->db->executeS($this->products()->select('p.`id_product`'));
    }

    public function getCustomersCount()
    {
        return (int)$this->db->getValue($this->customers()->select('count(*)'));
    }

    public function getCustomers()
    {
        return $this->db->executeS($this->customers()->select('c.`id_customer`'));
    }

    public function getCartRulesCount()
    {
        return (int)$this->db->getValue($this->cartRules()->select('count(*)'));
    }

    public function getCartRules()
    {
        return $this->db->executeS($this->cartRules()->select('c.`id_cart_rule`'));
    }
	
	public function getNewsletterSubscribersCount()
    {
		if (\Module::isEnabled('Ps_Emailsubscription') || \Module::isEnabled('blocknewsletter')) {
			return (int)$this->db->getValue($this->newsletterSubscribers()->select('count(*)'));
		}
		
		return 0;
    }

    public function getNewsletterSubscribers()
    {
        if (\Module::isEnabled('Ps_Emailsubscription')) {
            $query = $this->newsletterSubscribers()->select('s.`id`, s.`email`');
            $mInstance = \Module::getInstanceByName('ps_emailsubscription');
            if ((bool)version_compare($mInstance->version, '2.4.0', '>=')) {
                $query = $this->newsletterSubscribers()->select('s.`id`, s.`email`, s.`id_lang`');
            }
            return $this->db->executeS($query);
        } elseif (\Module::isEnabled('blocknewsletter')) {
            return $this->db->executeS($this->newsletterSubscribers()->select('s.`id`, s.`email`'));
        }
        
        return array();
    }
    
    public function getSpecificPrices()
    {
        return $this->db->executeS($this->specificPrices()->select('sp.`id_specific_price`, sp.`id_product`, sp.`from`, sp.`to`, sp.`id_shop`'));
    }
}
