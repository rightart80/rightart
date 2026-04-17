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

namespace PrestaChamps\MailchimpPro\Controllers;
if (!defined('_PS_VERSION_')) {
    exit;
}
use JasonGrimes\Paginator;
use PrestaChamps\MailChimpAPI;
use PrestaChamps\PrestaShop\Helpers\LinkHelper;
use PrestaChamps\PrestaShop\Traits\ShopIdTrait;

/**
 * Class BaseMCObjectController
 *
 * @package PrestaChamps\MailchimpPro\Exceptions
 */
abstract class BaseMCObjectController extends \ModuleAdminController
{
    use ShopIdTrait;
    
    public $bootstrap = true;
    public $mailchimp;

    public $entitySingular;
    public $entityPlural;

    protected $entitiesPerPage = 20;
    protected $offset          = 0;
    protected $currentPage     = 1;
    protected $totalPageNumber = 1;
    protected $totalEntities   = 0;
    protected $entity;
    protected $queryParameters = [];
    

    /**
     * @var int Mailchimp store ID
     */
    public $mcStoreId = 1;

    /**
     * AdminMailchimpProProductsController constructor.
     *
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();

        if (!\Configuration::get(\MailchimpProConfig::MAILCHIMP_API_KEY) || !\Configuration::get(\MailchimpProConfig::MAILCHIMP_LIST_ID) || !\Configuration::get(\MailchimpProConfig::MAILCHIMP_STORE_SYNCED)) {
            \Tools::redirectAdmin($this->context->link->getAdminLink('AdminMailchimpProConfiguration'));
        }

        $this->mcStoreId = \Mailchimppro::shopIdTransformer($this->context->shop);
        try {

            $idStore = \Context::getContext()->shop->id;
            $this->mailchimp = new \PrestaChamps\MailChimpAPI(\Configuration::get(\MailchimpProConfig::MAILCHIMP_API_KEY,null,null,$idStore));

            $this->mailchimp->setUserAgent($this->module->version);
            
        } catch (\Exception $exception) {
            $this->errors[] = $exception->getMessage();
        }
    }

    protected function getListApiEndpointUrl()
    {
        return "ecommerce/stores/{$this->mcStoreId}/{$this->entityPlural}";
    }

    protected function getSingleApiEndpointUrl($entityId)
    {
        return "ecommerce/stores/{$this->mcStoreId}/{$this->entityPlural}/{$entityId}";
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
            return $result[$this->entityPlural];
        }

        throw new \PrestaChamps\MailchimpPro\Exceptions\MailChimpException($this->mailchimp->getLastError());
    }

    /**
     * @throws \SmartyException
     */
    public function initContent()
    {
        $this->addCSS([
            $this->module->getLocalPath() . 'views/css/main.css',
            $this->module->getLocalPath() . 'views/css/configuration.css'
        ]);
        $this->content = $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/config/navbar.tpl'
        ) . $this->content;
        try {

            if (empty($this->action) || $this->action === 'page' || $this->action === 'entitydelete') {
                $this->renderEntityList();
            }
            //parent::initContent();
        } catch (\Exception $exception) {
            $this->errors[] = $exception->getMessage();
        }
        
        $this->context->smarty->assign(['content' => $this->content]);
        $this->content = $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/config/content.tpl'
        );
        parent::initContent();
    }

    /**
     * @throws \PrestaChamps\MailchimpPro\Exceptions\MailChimpException
     * @throws \SmartyException
     */
    protected function renderEntityList()
    {
        $this->context->smarty->assign([$this->entityPlural => $this->getEntities()]);        
        $this->content .= $this->context->smarty->fetch(
            $this->module->getLocalPath() . "views/templates/admin/entity_list/{$this->entityPlural}.tpl"
        );
        $this->renderPagination();
    }

    /**
     * Generate the link template for the pagination buttons
     *
     * @return string
     * @throws \PrestaShopException
     */
    protected function getPaginationPageLinkTemplate()
    {
        return urldecode(
            LinkHelper::getAdminLink(
                $this->controller_name,
                true,
                [],
                [
                    'action' => 'page',
                    'page' => '(:num)',
                ]
            )
        );
    }

    /**
     * @throws \PrestaShopException
     */
    protected function renderPagination()
    {
        $pagination = new Paginator(
            $this->totalEntities,
            $this->entitiesPerPage,
            $this->currentPage,
            $this->getPaginationPageLinkTemplate()
        );

        $this->content .= "<div class='text-center'>{$pagination}</div>";
    }

    protected function deleteEntity($id)
    {
        $this->mailchimp->delete("/ecommerce/stores/{$this->mcStoreId}/{$this->entityPlural}/{$id}");

        if ($this->mailchimp->success()) {
            return true;
        }

        return false;
    }

    public function processEntityDelete()
    {
        $entityId = \Tools::getValue('entity_id', false);

        if ($entityId) {
            if ($this->deleteEntity($entityId)) {
                $this->confirmations[] = $this->trans('Entity deleted', [], 'Modules.Mailchimppro.Basemcobject');
                $this->redirect_after = self::$currentIndex . '&conf=1&token=' . $this->token;
            } else {
                $this->errors[] = $this->trans('Could not delete the entity', [], 'Modules.Mailchimppro.Basemcobject');
            }
        }
    }

    public function processPage()
    {
        $page = \Tools::getValue('page', false);
        if ($page && is_numeric($page) && $page > 0) {
            $this->currentPage = (int)$page;
        }
    }

    /**
     * @throws \SmartyException
     */
    public function processSingle()
    {
        $entityId = \Tools::getValue('entity_id');
        if (!$entityId) {
            $this->errors[] = $this->trans('Invalid entity id', [], 'Modules.Mailchimppro.Basemcobject');
            return;
        }
        $this->entity = $this->mailchimp->get($this->getSingleApiEndpointUrl($entityId));
        $this->context->smarty->assign(['entity' => $this->entity]);
        $this->content .= $this->context->smarty->fetch(
            $this->module->getLocalPath() . "views/templates/admin/entity_single/{$this->entitySingular}.tpl"
        );
    }

    /**
     * Get list ID from store
     *
     * @return string
     */
    protected function getListIdFromStore()
    {
        $idStore = $this->context->shop->id;

        if(\Configuration::get(\MailchimpProConfig::MAILCHIMP_LIST_ID, null, null, $idStore)) {
            return \Configuration::get(\MailchimpProConfig::MAILCHIMP_LIST_ID, null, null, $idStore);
        }

        throw new \UnexpectedValueException("Can't determine LIST id from store");
    }
}
