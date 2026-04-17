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
/**
 * Class AdminMailchimpProTagsController
 *
 * @property Mailchimppro $module
 */

use PrestaChamps\MailchimpPro\Controllers\BaseMCObjectController;

class AdminMailchimpProTagsController extends BaseMCObjectController
{
	public $entityPlural   = 'tags';
    public $entitySingular = 'tag';
	protected $fields_form;
	protected $listId;
	
	/**
     * AdminMailchimpProTagsController constructor.
     *
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $multistore_on_store = false;

        if((\Shop::isFeatureActive() && \Shop::getContextShopID(true) != null) || !\Shop::isFeatureActive()){
            $multistore_on_store = true;
        }elseif(\Shop::getContextShopID(true) == null){
            $multistore_on_store = false;
        }

        if (!Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY) || !\Configuration::get(\MailchimpProConfig::MAILCHIMP_LIST_ID) || !\Configuration::get(\MailchimpProConfig::MAILCHIMP_STORE_SYNCED) || !$multistore_on_store) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminMailchimpProConfiguration'));
        }
        
        /* if (!\Configuration::get(\MailchimpProConfig::MAILCHIMP_API_KEY) || !\Configuration::get(\MailchimpProConfig::MAILCHIMP_LIST_ID) || !\Configuration::get(\MailchimpProConfig::MAILCHIMP_STORE_SYNCED)) {
			\Tools::redirectAdmin($this->context->link->getAdminLink('AdminMailchimpProConfiguration'));
		} */
        if ($this->mailchimp) {
            /* $this->queryParameters['key'] = \Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY); */
            $this->listId = $this->getListIdFromStore();
            $this->fields_form = [
                'tinymce' => false,
                'legend' => [
                    'title' => $this->trans('Create new tag', [], 'Modules.Mailchimppro.Adminmailchimpprotags'),
                    'icon' => 'icon-pencil'
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'name' => 'tagOperator',
                        'label' => $this->trans('Operator', [], 'Modules.Mailchimppro.Adminmailchimpprotags'),
                        'desc' => $this->trans('Customer total order value', [], 'Modules.Mailchimppro.Adminmailchimpprotags'),
                        'default_value' => '=',
                        'options' => [
                            'query' => [
                                ['id' => '=', 'name' => '='],
                                ['id' => '<', 'name' => '<'],
                                ['id' => '>', 'name' => '>'],							
                                ['id' => '>=', 'name' => '>='],
                                ['id' => '<=', 'name' => '<=']
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'col' => 12,
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Value', [], 'Modules.Mailchimppro.Adminmailchimpprotags'),
                        'desc' => $this->trans('as', [], 'Modules.Mailchimppro.Adminmailchimpprotags'),
                        'name' => 'orderTotalValue',
                        'lang' => false,
                        'col' => 12,
                        'required' => true,
                    ],				
                    [
                        'type' => 'text',
                        'label' => $this->trans('Tag name', [], 'Modules.Mailchimppro.Adminmailchimpprotags'),
                        'desc' => $this->trans('add tag', [], 'Modules.Mailchimppro.Adminmailchimpprotags'),
                        'name' => 'tagName',
                        'lang' => false,
                        'col' => 12,
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save and send to Mailchimp', [], 'Modules.Mailchimppro.Adminmailchimpprotags'),
                    'name' => 'createTag'
                ],
            ];
        }
    }
	
	protected function getListApiEndpointUrl()
    {
        //return '/tags/list';
		return "/lists/" . $this->listId . "/segments";
    }

    protected function getSingleApiEndpointUrl($entityId)
    {
        return "/tags/info";
    }
	
	/**
     * @return mixed
     * @throws \PrestaChamps\MailchimpPro\Exceptions\MailChimpException
     * @throws \Exception
     */
	/* protected function getEntities()
    {
        if (!$this->mailchimp) {
            return [];
        }

		$this->queryParameters['type'] = 'static';
		
		$result = $this->mailchimp->get(
            $this->getListApiEndpointUrl(),
            $this->queryParameters
        );
		
		if ($this->mailchimp->success()) {
            return $result['segments'];
        }

        throw new \PrestaChamps\MailchimpPro\Exceptions\MailChimpException($this->mailchimp->getLastError());
		
        /* $result = $this->mailchimp->post(
            $this->getListApiEndpointUrl(),
            $this->queryParameters
        ); */

//dump($result);die();

        /* if ($this->mailchimp->success()) {
			
            return $result;
        }


        throw new \PrestaChamps\MailchimpPro\Exceptions\MailChimpException($this->mailchimp->getLastError()); */
    /*} */
	
	protected function getEntities()
    {
        if (!$this->mailchimp) {
            return [];
        }
		
		$this->queryParameters['type'] = 'static';
		$this->queryParameters['count'] = $this->entitiesPerPage;
		$this->queryParameters['offset'] = ($this->currentPage - 1) * $this->entitiesPerPage;

		
        $result = $this->mailchimp->get(
            $this->getListApiEndpointUrl(),
            $this->queryParameters,
            999
        );
		
		unset($this->queryParameters['count']);
		unset($this->queryParameters['offset']);
        unset($this->queryParameters['type']);

        if ($this->mailchimp->success()) {
            $this->totalEntities = $result['total_items'];

            $this->totalPageNumber = ceil($this->totalEntities / $this->entitiesPerPage);
			
			foreach ($result['segments'] as &$segment) {				
				$segment['editable'] = true;
			}

            return $result['segments'];
        }

        throw new \PrestaChamps\MailchimpPro\Exceptions\MailChimpException($this->mailchimp->getLastError());
    }
	
	/* public function processEntityDelete()
    {
        $entityName = \Tools::getValue('entity_name', false);

        if ($entityName) {
            if ($this->deleteEntity($entityName)) {
                $this->confirmations[] = $this->trans('Entity deleted', [], 'Modules.Mailchimppro.Adminmailchimpprotags');
            } else {
                $this->errors[] = $this->trans('Could not delete the entity', [], 'Modules.Mailchimppro.Adminmailchimpprotags');
            }
        }
    } */
	
	/* protected function deleteEntity($name)
    {
		$this->queryParameters['tag'] = $name;
        $this->mailchimp->post("/tags/delete",$this->queryParameters);

        if ($this->mailchimp->success()) {
            return true;
        }

        return false;
    } */
	
	protected function deleteEntity($id)
    {
        $this->mailchimp->delete("/lists/" . $this->listId . "/segments/{$id}");

        if ($this->mailchimp->success()) {
            return true;
        }

        return false;
    }	
	
	/**
     * @throws \SmartyException
     */
    /* public function processEntityAdd()
    {
		//dump($this->renderForm());
        // $this->content .= $this->renderForm();
		//$result = $this->mailchimp->post('/templates', ['name'=>'import', 'html'=>'']);
    } */
	
	/**
     * Object creation.
     *
     * @return ObjectModel|false
     *
     * @throws PrestaShopException
     */
    public function processAdd()
    {
		$tagOperator = \Tools::getValue('tagOperator');
		if (\Tools::strlen($tagOperator) == 0) {
			$this->errors[] = $this->trans('The tag operator is required', [], 'Modules.Mailchimppro.Adminmailchimpprotags');
		}
		
		$orderTotalValue = \Tools::getValue('orderTotalValue');
		if (\Tools::strlen($orderTotalValue) == 0) {
			$this->errors[] = $this->trans('The tag order total value is required', [], 'Modules.Mailchimppro.Adminmailchimpprotags');
		}
		
		$tagName = \Tools::getValue('tagName');
		if (\Tools::strlen($tagName) == 0) {
			$this->errors[] = $this->trans('The tag name is required', [], 'Modules.Mailchimppro.Adminmailchimpprotags');
		}		
		
		//dump($orderTotalValue);die();
		
        if ($tagOperator && $orderTotalValue >=0 && $tagName) {			
			if ($customerIds = $this->getCustomersByOrderValue($tagOperator, $orderTotalValue)) {
				if ($response = $this->createTag($customerIds, $tagName)) {
					$this->confirmations[] = $this->trans('Tag created successfully', [], 'Modules.Mailchimppro.Adminmailchimpprotags');
					$this->redirect_after = self::$currentIndex . '&conf=3&token=' . $this->token;
				} else {
					$this->errors[] = $this->trans('Oups! Failed to create tag', [], 'Modules.Mailchimppro.Adminmailchimpprotags');
				}
			}
            else {
				$this->errors[] = $this->trans('No customer match for the selected criteria.', [], 'Modules.Mailchimppro.Adminmailchimpprotags');
			}

        }
		
		$this->action = '';

        return false;
	}
	
	private function getCustomersByOrderValue($tagOperator, $orderTotalValue)
    {
        // Define allowed operators
        $allowedOperators = ['=', '>', '<', '>=', '<='];

        // Validate the operator
        if (!in_array($tagOperator, $allowedOperators, true)) {
            throw new \InvalidArgumentException('Invalid operator.');
        }

        // Validate the order total value
        if (!is_numeric($orderTotalValue)) {
            throw new \InvalidArgumentException('Invalid order total value.');
        }

        
        $dbquery = new \DbQuery();
        $dbquery->select('c.`id_customer`, sum(o.`total_paid_tax_incl`) AS `ordertotal`');
        $dbquery->from('customer', 'c');
        $dbquery->leftJoin('orders', 'o', 'o.`id_customer` = c.`id_customer`');
        if ($orderTotalValue === 0 || $orderTotalValue === '0') {
            $dbquery->where('o.`total_paid_tax_incl` IS NULL');
        }
        else {
            $dbquery->where('o.`total_paid_tax_incl` '. pSQL($tagOperator) .' '. (float) $orderTotalValue);
        }       
        $dbquery->where('c.active IN (' . implode(',', \MailchimpProConfig::getConfigurationValues()[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED]) . ')');
        $dbquery->where('c.newsletter IN (' . implode(',', \MailchimpProConfig::getConfigurationValues()[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER]) . ')');
        $dbquery->groupBy('c.`id_customer`');
        
        return array_column(\Db::getInstance()->executeS($dbquery->build()), 'id_customer');
    }
	
	/**
     * @param $templateName
	 * @param $templateContent
     *
     * @return array|false
     * @throws Exception
     */
    private function createTag($customerIds, $tagName)
    {

		foreach ($customerIds as $customerId) {
			$customer = new Customer($customerId);

			$tags = [[
                'name' => $tagName,
                'status' => \PrestaChamps\MailchimpPro\Formatters\ListMemberTagFormatter::STATUS_ACTIVE
            ]];

            $listMemberTagFormatter = new \PrestaChamps\MailchimpPro\Formatters\ListMemberTagFormatter($tags);
            
            $hash = $this->mailchimp->subscriberHash($customer->email);
			$this->mailchimp->post("/lists/{$this->listId}/members/{$hash}/tags", $listMemberTagFormatter->format());
		}
		
		return true;
    }
	
	protected function renderEntityList()
    {
		if ($this->fields_form && is_array($this->fields_form)) {			
			$this->context->smarty->assign(['add_form' => $this->renderForm()]);			
		}
		
		parent::renderEntityList();
		
		/* $this->context->smarty->assign([$this->entityPlural => $this->getEntities()]);
		
        $this->content .= $this->context->smarty->fetch(
            $this->module->getLocalPath() . "views/templates/admin/entity_list/{$this->entityPlural}.tpl"
        ); */
    }
    
}