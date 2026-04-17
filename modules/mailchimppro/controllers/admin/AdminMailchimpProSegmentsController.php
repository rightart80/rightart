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
 * Class AdminMailchimpProSegmentsController
 *
 * @property Mailchimppro $module
 */
class AdminMailchimpProSegmentsController extends \PrestaChamps\MailchimpPro\Controllers\BaseMCObjectController
{
	public $entityPlural   = 'segments';
    public $entitySingular = 'segment';
	protected $fields_form;
	protected $listId;
	
	/**
     * AdminMailchimpProSegmentsController constructor.
     *
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
		/* $this->queryParameters['type'] = "user";
		$this->queryParameters['sort_dir'] = "DESC"; */
		$this->listId = $this->getListIdFromStore();
		$tagList = $this->getTagList();
		/* $tagListIds = array_column($tagList, 'id');
		$tagListNames = array_column($tagList, 'name');
		$tagList = array_combine($tagListIds, $tagListNames);
dump(array_intersect_key($this->getTagList()[0], array_flip(['id', 'name']))); */

        $this->fields_form = [
			'tinymce' => false,
			'legend' => [
				'title' => $this->trans('Create new segment', [], 'Modules.Mailchimppro.Adminmailchimpprosegments'),
				'icon' => 'icon-pencil'
			],
			'input' => [				
				[
					'type' => 'select',
					'name' => 'segmentType',
					'label' => $this->trans('By', [], 'Modules.Mailchimppro.Adminmailchimpprosegments'),					
					'default_value' => 'static_segment',
					'options' => [
						'query' => [
							['id' => 'static_segment', 'name' => 'Tags'],
							/* ['id' => 'email_client', 'name' => 'Email client'],
							['id' => 'language', 'name' => 'Language'],							
							['id' => 'gmonkey', 'name' => 'VIP status'] */
						],
						'id' => 'id',
						'name' => 'name',
					],
					'col' => 12,
					'required' => true,
				],
				[
					'type' => 'select',
					'name' => 'segmentOperator',
					'label' => $this->trans('Operator', [], 'Modules.Mailchimppro.Adminmailchimpprosegments'),					
					'default_value' => 'static_segment',
					'options' => [
						'query' => [
							['id' => 'static_is', 'name' => 'contact is tagged'],
							['id' => 'static_not', 'name' => 'contact is not tagged']
						],
						'id' => 'id',
						'name' => 'name',
					],
					'col' => 12,
					'required' => true,
				],
				[
					'type' => 'select',
					'name' => 'segmentValue',
					'label' => $this->trans('Value', [], 'Modules.Mailchimppro.Adminmailchimpprosegments'),					
					/* 'default_value' => 2, */
					'options' => [
						'query' => $tagList,
						'id' => 'id',
						'name' => 'name',
					],
					'col' => 12,
					'required' => true,
				],
				[
					'type' => 'text',
					'label' => $this->trans('Segment name', [], 'Modules.Mailchimppro.Adminmailchimpprosegments'),
					'name' => 'segmentName',
					'lang' => false,
					'col' => 12,
					'required' => true,
                ],
				
			],
			'submit' => [
				'title' => $this->trans('Save and send to Mailchimp', [], 'Modules.Mailchimppro.Adminmailchimpprosegments'),
				'name' => 'createSegment'
			],
		];			
    }
	
	protected function getListApiEndpointUrl()
    {
        return "/lists/" . $this->listId . "/segments";
    }

    protected function getSingleApiEndpointUrl($entityId)
    {
        return "/lists/" . $this->listId . "/segments/{$entityId}";
    }
	
	private function getTagList() {
		if (!$this->mailchimp) {
            return [];
        }
		
		/* $this->queryParameters['fields'] = 'segments.id'; */
		$this->queryParameters['type'] = 'static';
		$this->queryParameters['count'] = 1000;
		
		$result = $this->mailchimp->get(
            $this->getListApiEndpointUrl(),
            $this->queryParameters,
            999
        );
		
		unset($this->queryParameters['fields']);
		unset($this->queryParameters['type']);
		unset($this->queryParameters['count']);
		
		if ($this->mailchimp->success()) {
            //return $result;
			return $result['segments'];
        }

        throw new \PrestaChamps\MailchimpPro\Exceptions\MailChimpException($this->mailchimp->getLastError());
	}
	
	/**
     * @return mixed
     * @throws \PrestaChamps\MailchimpPro\Exceptions\MailChimpException
     * @throws \Exception
     */
	protected function getEntities()
    {
        if (!$this->mailchimp) {
            return [];
        }
		
		$this->queryParameters['type'] = 'saved';
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

//dump($result);die();

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
	
	protected function deleteEntity($id)
    {
        $this->mailchimp->delete($this->getSingleApiEndpointUrl($id));

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
		$segmentName = \Tools::getValue('segmentName');
		if (\Tools::strlen($segmentName) == 0) {
			$this->errors[] = $this->trans('The segment name is required', [], 'Modules.Mailchimppro.Adminmailchimpprosegments');
		}
		
		$segmentOperator = \Tools::getValue('segmentOperator');
		if (\Tools::strlen($segmentOperator) == 0) {
			$this->errors[] = $this->trans('The segment operator is required', [], 'Modules.Mailchimppro.Adminmailchimpprosegments');
		}
		
		$segmentValue = \Tools::getValue('segmentValue');
		if (\Tools::strlen($segmentValue) == 0) {
			$this->errors[] = $this->trans('The segment value is required', [], 'Modules.Mailchimppro.Adminmailchimpprosegments');
		}
		
		$editing = \Tools::getValue('editing');
		//$templateId = $editing;
		
        if ($segmentName && $segmentOperator && $segmentValue) {
            if ($response = $this->createSegment($segmentName, $segmentOperator, $segmentValue, $editing)) {

				if ($editing) {
					$this->confirmations[] = $this->trans('Segment updated successfully', [], 'Modules.Mailchimppro.Adminmailchimpprosegments');
					$this->redirect_after = self::$currentIndex . '&conf=4&token=' . $this->token;
				}
				else {
					$this->confirmations[] = $this->trans('Segment created successfully', [], 'Modules.Mailchimppro.Adminmailchimpprosegments');
					$this->redirect_after = self::$currentIndex . '&conf=3&token=' . $this->token;
				}					

            } else {
                $this->errors[] = $this->trans('Oups! Failed to create segment', [], 'Modules.Mailchimppro.Adminmailchimpprosegments');
            }

        }
		
		$this->action = '';

		return false;
	}
	
	/**
     * @throws \SmartyException
     */
    public function processEntityEdit()
    {
		//dump($this->renderForm());
        $this->content .= $this->renderForm();
		//$result = $this->mailchimp->post('/templates', ['name'=>'import', 'html'=>'']);
    }
	
	/**
     * @param $templateName
	 * @param $templateContent
     *
     * @return array|false
     * @throws Exception
     */
    private function createSegment($segmentName, $segmentOperator, $segmentValue, $editing)
    {
		$this->queryParameters['name'] = $segmentName;
		$this->queryParameters['options'] = (object)[
			'match' => 'any',
			'conditions' => [
				(object)[
					'field' => 'static_segment',
					'op' => $segmentOperator,
					'value' => $segmentValue
				]
			]
		];
		//dump($segmentValue);die();
		$result = $this->mailchimp->post(
			$this->getListApiEndpointUrl(),
            $this->queryParameters
		);
		
		unset($this->queryParameters['name']);
		unset($this->queryParameters['options']);
		
		//dump($result);die();
		
		if ($this->mailchimp->success()) {
            return $result;
        }

        throw new \PrestaChamps\MailchimpPro\Exceptions\MailChimpException($this->mailchimp->getLastError());
    }
	
	protected function renderEntityList()
    {
		if ($this->fields_form && is_array($this->fields_form)) {			
			$this->context->smarty->assign(['add_form' => $this->renderForm()]);			
		}
		
        parent::renderEntityList();
    }
    
}