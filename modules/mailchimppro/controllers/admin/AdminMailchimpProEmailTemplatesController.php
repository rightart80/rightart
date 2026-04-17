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
 *
 * Class AdminMailchimpProEmailTemplatesController
 *
 * @property Mailchimppro $module
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AdminMailchimpProEmailTemplatesController extends \PrestaChamps\MailchimpPro\Controllers\BaseMCObjectController
{
	public $entityPlural   = 'email_templates';
    public $entitySingular = 'email_template';
	protected $fields_form;
	
	/**
     * AdminMailchimpProEmailTemplatesController constructor.
     *
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
		$this->queryParameters['type'] = "user";
		$this->queryParameters['sort_dir'] = "DESC";
        $this->fields_form = [
			'tinymce' => true,
			'legend' => [
				'title' => $this->trans('Create new email template', [], 'Modules.Mailchimppro.Adminmailchimpproemailtemplates'),
				'icon' => 'icon-envelope'
			],
			'input' => [
				[
					'type' => 'hidden',
					'name' => 'id_info',
				],
				[
					'type' => 'text',
					'label' => $this->trans('Title', [], 'Modules.Mailchimppro.Adminmailchimpproemailtemplates'),
					'name' => 'templateName',
					'lang' => false,
					'col' => 12,
					'required' => true,
                ],
				[
					'type' => 'textarea',
					'label' => $this->trans('Email content', [], 'Modules.Mailchimppro.Adminmailchimpproemailtemplates'),
					'lang' => false,
					'name' => 'templateContent',
					'cols' => 40,
					'rows' => 10,
					'class' => 'rte',
					'autoload_rte' => true,
					'col' => 12,
					'required' => true,
				],
			],
			'submit' => [
				'title' => $this->trans('Save and send to Mailchimp', [], 'Modules.Mailchimppro.Adminmailchimpproemailtemplates'),
				'name' => 'createTemplate'
			],
		];        
    }
	
	protected function getListApiEndpointUrl()
    {
        return '/templates';
    }

    protected function getSingleApiEndpointUrl($entityId)
    {
        return "/templates/{$entityId}";
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
		
		$this->queryParameters['count'] = $this->entitiesPerPage;
		$this->queryParameters['offset'] = ($this->currentPage - 1) * $this->entitiesPerPage;

		
        $result = $this->mailchimp->get(
            $this->getListApiEndpointUrl(),
            $this->queryParameters,
            999
        );

        if ($this->mailchimp->success()) {
            $this->totalEntities = $result['total_items'];

            $this->totalPageNumber = ceil($this->totalEntities / $this->entitiesPerPage);
			
			foreach ($result['templates'] as &$template) {
				if (file_exists($this->module->getLocalPath() . "views/templates/admin/entity_list/email_templates/{$template['id']}.html")) {
					$template['editable'] = true;
				}
				else {
					$template['editable'] = false;
				}
			}
			
            return $result['templates'];
        }

        throw new \PrestaChamps\MailchimpPro\Exceptions\MailChimpException($this->mailchimp->getLastError());
    }

    protected function deleteEntity($id)
	{
	    $this->mailchimp->delete($this->getSingleApiEndpointUrl($id));

	    if ($this->mailchimp->success()) {
	        // Define the base directory for email templates
	        $templateBaseDir = $this->module->getLocalPath() . "views/templates/admin/entity_list/email_templates/";

	        // Resolve the full path of the template file
	        $templateFile = realpath($templateBaseDir . basename($id) . '.html');

	        // Ensure the file exists and is within the expected directory
	        if ($templateFile && strpos($templateFile, realpath($templateBaseDir)) === 0 && file_exists($templateFile)) {
	            unlink($templateFile);
	        }
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
		$templateName = \Tools::getValue('templateName');
		if (\Tools::strlen($templateName) == 0) {
			$this->errors[] = $this->trans('The template name is required', [], 'Modules.Mailchimppro.Adminmailchimpproemailtemplates');
		}
		
		$templateContent = \Tools::getValue('templateContent');
		if (\Tools::strlen($templateContent) == 0) {
			$this->errors[] = $this->trans('The template content is required', [], 'Modules.Mailchimppro.Adminmailchimpproemailtemplates');
		}
		
		$editing = \Tools::getValue('editing');
		$templateId = $editing;
		
        if ($templateName && $templateContent) {
            if ($response = $this->createMailchimpTemplate($templateId, $templateName, $templateContent, $editing)) {
				try {
					$template = fopen($this->module->getLocalPath() . "views/templates/admin/entity_list/email_templates/{$response['id']}.html", "w");
					fwrite($template, $templateContent);
					fclose($template);

					if ($editing) {
						$this->confirmations[] = $this->trans('Template updated successfully', [], 'Modules.Mailchimppro.Adminmailchimpproemailtemplates');
						$this->redirect_after = self::$currentIndex . '&conf=4&token=' . $this->token;
					}
					else {
						$this->confirmations[] = $this->trans('Template created successfully', [], 'Modules.Mailchimppro.Adminmailchimpproemailtemplates');
						$this->redirect_after = self::$currentIndex . '&conf=3&token=' . $this->token;
					}					
				} catch (\Exception $exception) {
					$this->errors[] = $exception->getMessage();
				}
            } else {
                $this->errors[] = $this->trans('Oups! Failed to create template', [], 'Modules.Mailchimppro.Adminmailchimpproemailtemplates');
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
    private function createMailchimpTemplate($templateId, $templateName, $templateContent, $editing)
    {
        return \PrestaChamps\MailchimpPro\Factories\EmailTemplateFactory::make(
            $templateName,
			$templateContent,
			$editing,
			$editing ? $this->getSingleApiEndpointUrl($templateId) : $this->getListApiEndpointUrl(),
            $this->module->getApiClient(),
            $this->context
        );
    }
	
	protected function renderEntityList()
    {
		if (is_array($this->fields_form)) {			
			$this->context->smarty->assign(['add_form' => $this->renderForm()]);			
			// $this->context->smarty->assign(['add_form_builder' => $this->renderForm()]);			
		}
		
        parent::renderEntityList();
		
		Media::addJsDef([
			'emailTemplatesPath' => $this->module->getPathUri() . "views/templates/admin/entity_list/email_templates/"
		]);
		
		try {
			$template = fopen($this->module->getLocalPath() . "views/templates/admin/entity_list/email_templates/defaults/1.html", "r");			
			$defaultTemplateContent = fread($template,filesize($this->module->getLocalPath() . "views/templates/admin/entity_list/email_templates/defaults/1.html"));
			fclose($template);
			
			Media::addJsDef([
				'defaultTemplateContent' => $defaultTemplateContent
			]);
		} catch (\Exception $exception) {
			$this->errors[] = $exception->getMessage();
		}
    }
}