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
if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * Class Mailchimppro
 */
class Mailchimppro extends Module
{
    const MC_MIDDLEWARE_NEW_TO_CONNECT = "https://prestashop.mailchimpapp.com/new-auth";     
    
    // original OLD url - posts response back with token/api-key
    const MC_MIDDLEWARE = "https://prestashop.mailchimpapp.com";
    /**
     * @var \DrewM\MailChimp\MailChimp MailChimp API client object
     *
     * @see https://github.com/drewm/mailchimp-api
     */
    protected $apiClient;

    protected $configuration;

    public $menus = [
        [
            'is_root' => true,
            'name' => 'Mailchimp Config',
            'class_name' => 'mailchimppro',
            'visible' => true,
            'parent_class_name' => 0,
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Config',
            'class_name' => 'AdminMailchimpProConfig',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Configuration',
            'class_name' => 'AdminMailchimpProConfiguration',
            'visible' => true,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Setup Wizard',
            'class_name' => 'AdminMailchimpProWizard',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Queue Work',
            'class_name' => 'AdminMailchimpProQueue',
            'visible' => true,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp List',
            'class_name' => 'AdminMailchimpProLists',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Batches',
            'class_name' => 'AdminMailchimpProBatches',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Carts',
            'class_name' => 'AdminMailchimpProCarts',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Customers',
            'class_name' => 'AdminMailchimpProCustomers',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Orders',
            'class_name' => 'AdminMailchimpProOrders',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Products',
            'class_name' => 'AdminMailchimpProProducts',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Stores',
            'class_name' => 'AdminMailchimpProStores',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Sync',
            'class_name' => 'AdminMailchimpProSync',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Sites',
            'class_name' => 'AdminMailchimpProSites',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Automations',
            'class_name' => 'AdminMailchimpProAutomations',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'List members',
            'class_name' => 'AdminMailchimpProListMembers',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Promo rules',
            'class_name' => 'AdminMailchimpProPromoRules',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Promo codes',
            'class_name' => 'AdminMailchimpProPromoCodes',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Email templates',
            'class_name' => 'AdminMailchimpProEmailTemplates',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Tags',
            'class_name' => 'AdminMailchimpProTags',
            'visible' => true,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Segments',
            'class_name' => 'AdminMailchimpProSegments',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Campaigns',
            'class_name' => 'AdminMailchimpProCampaigns',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Statistics',
            'class_name' => 'AdminMailchimpProReports',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'PromoStats',
            'class_name' => 'AdminMailchimpProStats',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
    ];


    public function __construct()
    {
        $this->name = 'mailchimppro';
        $this->tab = 'administration';
        $this->version = '3.0.34';
        $this->author = 'Mailchimp';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->module_key = '793ebc5f330220c7fb7b817fe0d63a92';

        parent::__construct();

        $this->displayName = 'Mailchimp';
        $this->description = 'Official Mailchimp integration for PrestaShop';
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];

        require_once $this->getLocalPath() . 'vendor/autoload.php';

        $this->configuration = \MailchimpProConfig::getConfigurationValues();
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * Install the required tabs, configs and stuff
     *
     * @return bool
     * @throws PrestaShopException
     *
     * @throws PrestaShopDatabaseException
     * @since 0.0.1
     *
     */
    public function install()
    {
        $tabRepository = new \PrestaChamps\PrestaShop\Tab\TabRepository($this->menus, 'mailchimppro');
        $tabRepository->install();

        return parent::install() &&
            // The moduleRoutes hook is necessary in order to load the autoloader
            $this->registerHook('moduleRoutes') &&
            $this->registerHook('displayFooter') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('actionObjectUpdateAfter') &&
            $this->registerHook('actionObjectDeleteAfter') &&
            $this->registerHook('actionOrderStatusUpdate') &&
            $this->registerHook('actionCartSave') &&
            $this->registerHook('actionObjectCustomerAddAfter') &&
            $this->registerHook('actionObjectCartRuleAddAfter') &&
            $this->registerHook('actionObjectCartRuleDeleteBefore') &&
            // $this->registerHook('displayAdminOrderContentOrder') &&
            // $this->registerHook('displayAdminOrderTabOrder') &&
            // $this->registerHook('displayBackOfficeTop') &&
            $this->registerHook('actionObjectCartRuleUpdateAfter') &&
            $this->registerHook('displayFooterBefore') &&
            $this->registerHook('actionNewsletterRegistrationAfter') &&
            $this->registerHook('actionCustomerAccountAdd') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('actionCustomerAccountUpdate') &&

            $this->registerHook('actionObjectSpecificPriceAddAfter') &&
            $this->registerHook('actionObjectSpecificPriceUpdateAfter') &&
            $this->registerHook('actionObjectSpecificPriceDeleteAfter') &&

            $this->registerHook('actionObjectAddressAddAfter') &&

            $this->registerHook('dashboardZoneOne') &&
            $this->installDb() && 
            $this->updatePosition(Hook::getIdByName('dashboardZoneOne'), 0, 1);
    }

    protected function installDb() {
        $pf = _DB_PREFIX_ . "{$this->name}_";
        return Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}jobs` (
                `id_job` INT NOT NULL AUTO_INCREMENT,
                `id_entity` INT NULL,
                `type` VARCHAR(50)  NOT NULL,
                `channel` TINYTEXT  NOT NULL,
                `status` TINYINT NOT NULL DEFAULT 0,
                `attempts` SMALLINT DEFAULT 0,
                `body` MEDIUMTEXT NOT NULL,
                `error` VARCHAR(255) NULL,
                `created_at` DATETIME NULL,
                `locked_at` DATETIME NULL,
                `priority` SMALLINT DEFAULT 1,
                `id_shop` INT NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_job`),
                CONSTRAINT entity_type_shop UNIQUE (id_entity,type,id_shop)
                ) ENGINE = InnoDB;
        ") &&
        Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}specific_price` (
                `id_specific_price` INT NOT NULL,
                `id_product` INT NOT NULL,
                `start_date` DATETIME NOT NULL,
                `end_date` DATETIME NOT NULL,
                `needToRun` SMALLINT NOT NULL DEFAULT 0,
                `id_shop` INT NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_specific_price`)
                ) ENGINE = InnoDB;
        ") && 
        Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}api_log` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `request_type` VARCHAR(50) NOT NULL,
                `end_point` VARCHAR(255) NOT NULL,
                `back_trace` LONGTEXT NULL,
                `is_success` BOOLEAN,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = InnoDB;
        ") &&
        Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}campaign` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `prefix` varchar(255) DEFAULT NULL,
                `suffix` varchar(255) DEFAULT NULL,
                `reduction` varchar(255) NOT NULL,
                `reduction_type` int(11) NOT NULL,
                `status` tinyint(4) NOT NULL,
                `campaign_id` varchar(255) DEFAULT NULL,
                `id_merge_field_mc` int(11) DEFAULT NULL,
                `tag_merge_field_mc` varchar(255) DEFAULT NULL,
                `end_date` datetime NOT NULL,
                `date_add` datetime DEFAULT NULL,
                `date_upd` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = InnoDB;
        ") &&
        Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}promocodes` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `id_promo_main` int(11) NOT NULL,
                `code` varchar(255) NOT NULL,
                `status` tinyint(4) NOT NULL,
                `sent_to_mc` tinyint(4) NOT NULL,
                `id_cart` int(10) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `date_reddeem` datetime DEFAULT NULL,
                `date_add` datetime DEFAULT NULL,
                `date_upd` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = InnoDB;
        ");
    }

    public function runUpgradeModule()
    {
        return parent::runUpgradeModule(); // TODO: Change the autogenerated stub
    }

    public function hookModuleRoutes()
    {
        return '';
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        $tabRepository = new \PrestaChamps\PrestaShop\Tab\TabRepository($this->menus, 'mailchimppro');
        $tabRepository->uninstall();

        $pf = _DB_PREFIX_ . "{$this->name}_";
        Db::getInstance()->execute("DROP TABLE IF EXISTS `{$pf}jobs`");
        Db::getInstance()->execute("DROP TABLE IF EXISTS `{$pf}specific_price`");
        Db::getInstance()->execute("DROP TABLE IF EXISTS `{$pf}api_log`");
        Db::getInstance()->execute("DROP TABLE IF EXISTS `{$pf}campaign`");
        Db::getInstance()->execute("DROP TABLE IF EXISTS `{$pf}promocodes`");

        return parent::uninstall();
    }


    /**
     * Check if the current PrestaShop installation is version 1.7 or below
     *
     * @return bool
     */
    public static function isPs17()
    {
        return (bool)version_compare(_PS_VERSION_, '1.7', '>=');
    }


    /**
     * Redirect to the custom config controller
     *
     * @throws PrestaShopException
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminMailchimpProConfiguration'));
    }

    /**
     * Place UTM tracking cookie when the user arrived via MailChimp
     *
     * @param $params
     */
    public function hookDisplayHeader($params)
    {
        if ((Tools::getValue('utm_source') === 'mailchimp' || !empty(Tools::getValue('mc_cid')))
            && $this->isApiKeySet()) {
            $this->context->cookie->landing_site = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $mc_cid = Tools::getValue('mc_cid', false);
            $utm_source = Tools::getValue('utm_source', false);
            if ($mc_cid) {
                setcookie('mc_cid', Tools::getValue('mc_cid'), 0, $this->context->shop->getBaseURI());
            }
            if ($utm_source) {
                setcookie('utm_source', urldecode(Tools::getValue('utm_source')), 0, $this->context->shop->getBaseURI());
            }
            $this->context->cookie->utm_source = Tools::getValue('utm_source');
            setcookie(
                'landing_site',
                (Tools::usingSecureMode() ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
                0,
                $this->context->shop->getBaseURI()
            );
        }

        $mc_js_payload = '';

        if ($this->isApiKeySet() && $this->isStoreSynced() && Configuration::hasKey(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED)) {
            $mcjsscript = Configuration::get(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED);

            if (strpos($mcjsscript, 'chimpstatic.com') !== false || strpos($mcjsscript, '!function(c,h,i,m,p)') !== false) {
                // Strip CDATA
                $mcjsscript = str_replace(['<![CDATA[', ']]>'], '', $mcjsscript);

                // Remove PS HTML comment wrappers (no literal </script> here)
                $endScript = '<' . '/script>'; // avoid literal in source
                $mcjsscript = str_replace(['<!--//-->//><!--', '//--><!' . $endScript], ['', $endScript], $mcjsscript);

                // Remove outer script tag if present (build regex without literal "<script")
                $pat = '#^\s*' . '<' . 'script' . '[^>]*>(.*)' . '<' . '/script>\s*$#si';
                $mcjsscript = preg_replace($pat, '$1', $mcjsscript);

                $mc_js_payload = trim($mcjsscript);

                $this->context->smarty->assign([
                    'mc_js_payload' => $mc_js_payload,
                ]);
            
                return $this->context->smarty->fetch(
                    $this->getLocalPath() . 'views/templates/hook/front/_displayHeader.tpl'
                );  
            }
        }
    }

    /**
     * Mailchimp API client factory
     *
     * @throws Exception
     */
    public function getApiClient($idStore = null)
    {
        if(!$idStore){
            $idStore = Context::getContext()->shop->id;
        }

        if ($this->apiClient instanceof \PrestaChamps\MailChimpAPI) {

            $reflectionClass = new ReflectionClass('\PrestaChamps\MailChimpAPI');
            $reflectionProperty = $reflectionClass->getProperty('api_key');
            $reflectionProperty->setAccessible(true);


            $this->apiClient->setUserAgent($this->version);

            if (Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY,null,null,$idStore) == $reflectionProperty->getValue($this->apiClient)) {
                return $this->apiClient;
            }
        }
        
        $this->apiClient = new \PrestaChamps\MailChimpAPI(Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY,null,null,$idStore));

        $this->apiClient->setUserAgent($this->version);

        return $this->apiClient;
    }

    /**
     * @param       $url
     * @param       $method
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    public function sendApiRequest($url, $method, $data = [])
    {
        if ($method === 'POST') {
            $this->getApiClient()->post($url, $data);
        } elseif ($method === 'PATCH') {
            $this->getApiClient()->patch($url, $data);
        } elseif ($method === 'PUT') {
            $this->getApiClient()->put($url, $data);
        } elseif ($method === 'DELETE') {
            $this->getApiClient()->delete($url, $data);
        } else {
            $this->getApiClient()->get($url, $data);
        }

        return $this->getApiClient()->getLastResponse();
    }

    /**
     * Display site MailChimp site verification
     *
     * @param $params
     *
     * @return string
     */
    public function hookDisplayFooter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {

            if (Configuration::get(MailchimpProConfig::SYNC_NEWSLETTER_SUBSCRIBERS)) {
                if((bool)version_compare(_PS_VERSION_, '1.7', '<')){

                    $subscriptionIsEnabled = Module::isEnabled('blocknewsletter');

                    if (Tools::isSubmit('submitNewsletter') && $subscriptionIsEnabled) {
                        try {
                        \PrestaChamps\MailchimpPro\Hooks\Display\FooterBefore::run(
                                $params,
                                $this->getApiClient(),
                                $this->context
                            )->newsletterBlockRegistration();
                        } catch (Exception $exception) {
                            PrestaShopLogger::addLog("[MAILCHIMP-NW16] :{$exception->getMessage()}");
                        }
                    }
                }
            }            
        }
        return '';
    }

    public function hookDashboardZoneOne($params)
    {
        if (MailchimpProConfig::getConfigurationValues()[MailchimpProConfig::SHOW_DASHBOARD_STATS]) {
            if ($this->isApiKeySet()) {
                $this->context->controller->addCSS($this->_path . 'views/css/backoffice_dashboard.css');

                $this->context->smarty->assign([
                    'configurationLink' => $this->context->link->getAdminLink('AdminMailchimpProConfiguration') . '#statistics',
                    'dashboardLink' => $this->context->link->getAdminLink('AdminDashboard', true, [], ['refreshMailchimpReports'=> 1]),
                ]);
                $this->assignStatisticsVariables();

                return $this->context->smarty->fetch($this->getLocalPath() . 'views/templates/hook/front/_dashboard-statistics.tpl');
            }
        }   
    }

    public function assignStatisticsVariables($params = [])
    {
        $this->context->smarty->assign(['statistics' => $this->getReportsVariables($params)]);
    }

    public function getReportsVariables($params = [])
    {
        if (Configuration::hasKey(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED) 
            && ($campaingReports = Configuration::get(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED)) 
            && Configuration::hasKey(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED_DATE) 
            && ($campaingReportsLastUpdate = Configuration::get(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED_DATE))
            && !Tools::getValue('refreshMailchimpReports')
            && !(isset($params['refresh']) && $params['refresh']))
        {
            $campaingReports = json_decode($campaingReports, true);
            $campaingReports['last_update'] = $campaingReportsLastUpdate . ' (' . date_default_timezone_get() . ')';

            // daily update the campaing reports
            if ((time() - strtotime($campaingReportsLastUpdate)) <= 86400) {
                return $campaingReports;
            }
        }

        $reportsEndpoint = "/reports?count=5";
        $campaingReports = [
            'reports' => [],
            'total_items' => 0,
        ];
        try {
            $response = $this->getApiClient()->get($reportsEndpoint);

            if ($this->getApiClient()->success()) {
                $campaingReports = $response;
                
                // Keys to unset from response campaingReports array
                $keysToUnset = ['_links', 'timeseries'];
                $this->unsetKeysRecursive($campaingReports, $keysToUnset);
                
                $totalEmailsSent = 0;
                $totalEmailUniqueOpens = 0;
                $avgEmailUniqueOpens = 0;
                $totalEmailUniqueClicks = 0;
                $avgEmailUniqueClicks = 0;
                $totalEmailsBounced = 0;
                $avgEmailsBounced = 0;
                //$totalOpenRate = 0;
                $totalUnsubscribed = 0;
                $avgUnsubscribed = 0;
                
                foreach ($campaingReports['reports'] as $key => &$report) {
                    $totalEmailsSent += $report['emails_sent'];
                    $totalEmailsBounced += array_sum(array_values($report['bounces']));
                    $totalEmailUniqueOpens += $report['opens']['unique_opens'];
                    $totalEmailUniqueClicks += $report['clicks']['unique_clicks'];
                    //$totalOpenRate += $report['opens']['open_rate'];
                    $totalUnsubscribed += $report['unsubscribed'];

                    if (!empty($report['send_time'])) {
                        $send_time = new DateTime($report['send_time']);
                        $report['send_time'] = $send_time->format('Y-m-d H:i:s');
                    }
                }
                if ($totalEmailsSent && $totalEmailsBounced) {
                    //$avgEmailsBounced = $totalEmailsBounced / count($campaingReports['reports']) * 100;
                    $avgEmailsBounced = $totalEmailsBounced / $totalEmailsSent * 100;
                }
                //if ($totalOpenRate) {
                //    $avgEmailUniqueOpens = $totalOpenRate / count($campaingReports['reports']) * 100;
                //}
                if ($totalEmailUniqueOpens && ($totalEmailsSent > $totalEmailsBounced)) {
                    $avgEmailUniqueOpens = $totalEmailUniqueOpens / ($totalEmailsSent - $totalEmailsBounced) * 100;
                }
                if ($totalEmailUniqueClicks && ($totalEmailsSent > $totalEmailsBounced)) {
                    $avgEmailUniqueClicks = $totalEmailUniqueClicks / ($totalEmailsSent - $totalEmailsBounced) * 100;
                }
                if ($totalUnsubscribed && ($totalEmailsSent > $totalEmailsBounced)) {
                    $avgUnsubscribed = $totalUnsubscribed / ($totalEmailsSent - $totalEmailsBounced) * 100;
                }

                $campaingReports['total_emails_sent'] = $totalEmailsSent;
                $campaingReports['total_emails_bounced'] = $totalEmailsBounced;
                $campaingReports['total_emails_unique_opens'] = $totalEmailUniqueOpens;
                $campaingReports['total_emails_unique_clicks'] = $totalEmailUniqueClicks;
                $campaingReports['total_unsubscribed'] = $totalUnsubscribed;
                $campaingReports['avg_emails_bounce_rate'] = [
                    'value' => $avgEmailsBounced,
                    'text' => round($avgEmailsBounced, 2) . '%',
                ];
                $campaingReports['avg_email_unique_open_rate'] = [
                    'value' => $avgEmailUniqueOpens,
                    'text' => round($avgEmailUniqueOpens, 2) . '%',
                ];
                $campaingReports['avg_email_unique_click_rate'] = [
                    'value' => $avgEmailUniqueClicks,
                    'text' => round($avgEmailUniqueClicks, 2) . '%',
                ];
                $campaingReports['avg_unsubscribe_rate'] = [
                    'value' => $avgUnsubscribed,
                    'text' => round($avgUnsubscribed, 2) . '%',
                ];
            } else {
                $campaingReports = [
                    'campaign_error' => 'Status: ' . $response['status'] . ' - ' . $response['title'] . ' (' . $response['detail'] . ')',
                ];
            }
        } catch (Exception $e) {
            // Handle API errors
            $campaingReports = [
                'campaign_error' => $e->getMessage(),
            ];
        }

        // Store the value in the configuration
        Configuration::updateValue(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED, json_encode($campaingReports));
        Configuration::updateValue(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED_DATE, date('Y-m-d H:i:s'));

        $campaingReports['last_update'] = date('Y-m-d H:i:s') . ' (' . date_default_timezone_get() . ')';

        return $campaingReports;
    }

    public static function unsetKeysRecursive(&$array, $keysToUnset) {
        foreach ($array as $key => &$value) {
            if (in_array($key, $keysToUnset)) {
                unset($array[$key]); // Unset the key
            } elseif (is_array($value)) {
                self::unsetKeysRecursive($value, $keysToUnset); // Recurse into subarrays
            }
        }
        unset($value); // Prevent reference issues
    }

    /**
     * Sync the newly created customer to MailChimp
     *
     * @param $params
     */
    public function hookActionObjectCustomerAddAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CUSTOMERS)) {
                try {
                    /**
                     * @var $customer Customer
                     */
                    $customer = $params['object'];
                    // check filter customers to sync
                    if(in_array($customer->active, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED]) && 
                        in_array($customer->newsletter, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])){
                        if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                            $command = new \PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand(
                                $this->context,
                                $this->getApiClient(),
                                [$customer->id]
                            );
                            $command->triggerDoubleOptIn(true);
                            $command->setSyncMode($command::SYNC_MODE_REGULAR);
                            $command->setMethod($command::SYNC_METHOD_PUT);
                            $command->execute();
                        } else {
                            $job = new \PrestaChamps\Queue\Jobs\CustomerSyncJob();
                            $job->customerId = $customer->id;
                            $job->triggerDoubleOptIn(true);
                            $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_MODE_REGULAR);
                            $job->setMethod(\PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_METHOD_PUT);
                            $queue = new \PrestaChamps\Queue\Queue();
                            $queue->push($job, 'hook-customer-add-after', $this->context->shop->id);
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = "[MAILCHIMP] :{$exception->getMessage()}";
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    /**
     * Sync the newly created address for customer to MailChimp
     *
     * @param $params
     */
    public function hookActionObjectAddressAddAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CUSTOMERS)) {
                try {
                    /**
                     * @var $address Address
                     */
                    $address = $params['object'];

                    $customer = new Customer($address->id_customer);
                    // check filter customers to sync
                    if(in_array($customer->active, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED]) && 
                        in_array($customer->newsletter, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])){
                        if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                            $command = new \PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand(
                                $this->context,
                                $this->getApiClient(),
                                [$address->id_customer]
                            );
                            $command->triggerDoubleOptIn(true);
                            if($this->context->controller->controller_type == 'admin'){
                                $command->setUpdateSubscriptionStatus(false);
                            }
                            $command->setSyncMode(\PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_MODE_REGULAR);
                            $command->setMethod(\PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_METHOD_PUT);
                            $command->execute();
                        } else {
                            $job = new \PrestaChamps\Queue\Jobs\CustomerSyncJob();
                            $job->customerId = $address->id_customer;
                            $job->triggerDoubleOptIn(true);
                            if($this->context->controller->controller_type == 'admin'){
                                $job->setUpdateSubscriptionStatus(false);
                            }
                            $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_MODE_REGULAR);
                            $job->setMethod(\PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_METHOD_PUT);
                            $queue = new \PrestaChamps\Queue\Queue();
                            $queue->push($job, 'hook-address-add-after', $this->context->shop->id);
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = "[MAILCHIMP] :{$exception->getMessage()}";
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    /**
     * @param $params
     *
     * @throws Exception
     * @todo Refactor code to use a service pattern
     *
     */
    public function hookActionObjectCartRuleAddAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CART_RULES)) {
                $object = new CartRule($params['object']->id, $this->context->language->id);
                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                    $command = new \PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand(
                        $this->context,
                        $this->getApiClient(),
                        [$object]
                    );
                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                    $command->setMethod($command::SYNC_METHOD_POST);
                    $command->execute();
                } else {
                    $job = new \PrestaChamps\Queue\Jobs\CartRuleSyncJob();
                    $job->cartRuleId = $object->id;
                    $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand::SYNC_MODE_REGULAR);
                    $job->setMethod(\PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand::SYNC_METHOD_POST);
                    $queue = new \PrestaChamps\Queue\Queue();
                    $queue->push($job, 'hook-cartrule-add-after', $this->context->shop->id);
                }
            }
        }
    }

    /**
     * @param $params
     *
     * @throws Exception
     * @todo Refactor code to use a service pattern
     *
     */
    public function hookActionObjectCartRuleUpdateAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CART_RULES)) {
                $object = new CartRule($params['object']->id, $this->context->language->id);
                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                    $command = new \PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand(
                        $this->context,
                        $this->getApiClient(),
                        [$object]
                    );
                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                    $command->setMethod($command::SYNC_METHOD_PATCH);
                    $command->execute();
                } else {
                    $job = new \PrestaChamps\Queue\Jobs\CartRuleSyncJob();
                    $job->cartRuleId = $object->id;
                    $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand::SYNC_MODE_REGULAR);
                    $job->setMethod(\PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand::SYNC_METHOD_PATCH);
                    $queue = new \PrestaChamps\Queue\Queue();
                    $queue->push($job, 'hook-cartrule-update-after', $this->context->shop->id);
                }
            }
        }
    }

    /**
     * @param $params
     *
     * @throws Exception
     * @todo Refactor code to use a service pattern
     *
     */
    public function hookActionObjectCartRuleDeleteBefore($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CART_RULES)) {
                $object = new CartRule($params['object']->id, $this->context->language->id);
                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                    $command = new \PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand(
                        $this->context,
                        $this->getApiClient(),
                        [$object]
                    );
                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                    $command->setMethod($command::SYNC_METHOD_DELETE);
                    $command->execute();
                } else {
                    $job = new \PrestaChamps\Queue\Jobs\CartRuleSyncJob();
                    $job->cartRuleId = $object->id;
                    $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand::SYNC_MODE_REGULAR);
                    $job->setMethod(\PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand::SYNC_METHOD_DELETE);
                    $queue = new \PrestaChamps\Queue\Queue();
                    $queue->push($job, 'hook-cartrule-delete-before', $this->context->shop->id);
                }
            }
        }
    }


    /**
     * Create or update the cart in Mailchimp
     *
     * @param $params
     *
     * @throws Exception
     * @todo Use command pattern instead
     *
     */
    public function hookActionCartSave($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced() && (Tools::getValue('controller') !== 'adminaddresses')) {
            if (Configuration::get(MailchimpProConfig::SYNC_CARTS)) {
                try {
                    \PrestaChamps\MailchimpPro\Hooks\Action\CartSave::run(
                        $this->context,
                        $this->getApiClient()
                    );
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = "[MAILCHIMP] :{$exception->getMessage()}";
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    /**
     * Sync the order status update to MailChimp
     *
     * @param $params
     */
    public function hookActionOrderStatusUpdate($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_ORDERS)) {
                try {
                    $orderId = null;
                    if (isset($params['id_order'])) {
                        $orderId = $params['id_order'];
                    }
                    if (isset($params['newOrderStatus']) && isset($params['newOrderStatus'], $params['newOrderStatus']->id_order)) {
                        $orderId = $params['newOrderStatus']->id_order;
                    }
                    if ($orderId) {
                        $order = new \Order($orderId, $this->context->language->id);

                        if(in_array($order->getCustomer()->active, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED]) &&
                            in_array($order->getCustomer()->newsletter, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])){
                            if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                $command = new \PrestaChamps\MailchimpPro\Commands\OrderSyncCommand(
                                    $this->context,
                                    $this->getApiClient(),
                                    [$orderId]
                                );
                                $command->setSyncMode($command::SYNC_MODE_REGULAR);
                                if (!empty($params['newOrderStatus']->id)) {
                                    // Provide the new idOrderState to the command
                                    $command->setIdOrderStates([$orderId => (int) $params['newOrderStatus']->id]);
                                }
                                $command->setMethod($command::SYNC_METHOD_PATCH); 

                                $result = $command->execute();

                                if (isset($result["requestSuccess"]) && $result["requestSuccess"] == false) {
                                    $command->setMethod($command::SYNC_METHOD_POST);
                                    $result = $command->execute();                
                                }
                            } else {
                                $job = new \PrestaChamps\Queue\Jobs\OrderSyncJob();
                                $job->orderId = $orderId;
                                $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\OrderSyncCommand::SYNC_MODE_REGULAR);
                                if (isset($_COOKIE['mc_cid']) && !empty($_COOKIE['mc_cid']) && !is_a($this->context->controller, 'AdminController') && !is_subclass_of($this->context->controller, 'AdminController')) {
                                    $job->setCampaignId($_COOKIE['mc_cid']);
                                }
                                $queue = new \PrestaChamps\Queue\Queue();
                                $queue->push($job, 'hook-order-status-update', $this->context->shop->id);
                            }
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = "[MAILCHIMP] :{$exception->getMessage()}";
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    public function hookActionValidateOrder($params)
    {
        if (isset($params['order']) && is_subclass_of($params['order'], 'OrderCore') && $this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_ORDERS)) {
                try {
                    $order = new Order($params['order']->id, $this->context->language->id);

                    if(in_array($order->getCustomer()->active, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED]) &&
                        in_array($order->getCustomer()->newsletter, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])){

                        if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                            $command = new \PrestaChamps\MailchimpPro\Commands\OrderSyncCommand(
                                $this->context,
                                $this->getApiClient(),
                                [$params['order']->id]
                            );
                            $command->setSyncMode($command::SYNC_MODE_REGULAR);
                            
                            $command->setMethod($command::SYNC_METHOD_PATCH); 

                            $result = $command->execute();

                            if (isset($result["requestSuccess"]) && $result["requestSuccess"] == false) {
                                $command->setMethod($command::SYNC_METHOD_POST);
                                $result = $command->execute();                
                            }

                            $command = new \PrestaChamps\MailchimpPro\Commands\CartSyncCommand(
                                $this->context,
                                $this->getApiClient(),
                                [$order->id_cart]
                            );
                            $command->setSyncMode($command::SYNC_MODE_REGULAR);
                            $command->setMethod($command::SYNC_METHOD_DELETE);
                            $command->execute();

                        } else {
                            $job = new \PrestaChamps\Queue\Jobs\OrderSyncJob();
                            $job->orderId = $params['order']->id;
                            $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\OrderSyncCommand::SYNC_MODE_REGULAR);
                            if (isset($_COOKIE['mc_cid']) && !empty($_COOKIE['mc_cid']) && !is_a($this->context->controller, 'AdminController') && !is_subclass_of($this->context->controller, 'AdminController')) {
                                $job->setCampaignId($_COOKIE['mc_cid']);
                            }
                            $queue = new \PrestaChamps\Queue\Queue();
                            $queue->push($job, 'hook-action-validate-order', $this->context->shop->id);

                            $job = new \PrestaChamps\Queue\Jobs\CartSyncJob();
                            $job->cartId = $order->id_cart;
                            $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\CartSyncCommand::SYNC_MODE_REGULAR);
                            $job->setMethod(\PrestaChamps\MailchimpPro\Commands\CartSyncCommand::SYNC_METHOD_DELETE);

                            $queue->push($job, 'hook-action-validate-order', $this->context->shop->id);
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = "[MAILCHIMP] :{$exception->getMessage()}";
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    /**
     * Delete the objects from the MailChimp account also
     *
     * @param $params
     */
    public function hookActionProductUpdate($params)
    {
        if (isset($params['product']) && $this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
                try {
                    $product = $params['product'];
                    if (is_a($product, 'ProductCore')) {
                        /**
                         * @var $product Product
                         */
                        if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                            $command = new \PrestaChamps\MailchimpPro\Commands\ProductSyncCommand(
                                $this->context,
                                $this->getApiClient(),
                                [$product->id]
                            );
                            $command->setSyncMode($command::SYNC_MODE_REGULAR);
                            if ($product->active) { // adding/updating the product to Mailchimp if active
                                $command->setMethod($command::SYNC_METHOD_PATCH); 
                                $result = $command->execute();

                                if (isset($result["requestSuccess"]) && $result["requestSuccess"] == false) {
                                    $command->setMethod($command::SYNC_METHOD_POST);
                                    $result = $command->execute();                                    
                                }
                            } else { // deactivation of product should remove the product from Mailchimp
                                $command->setMethod($command::SYNC_METHOD_DELETE);
                                $command->execute();
                            }
                            
                        } else {
                            $job = new \PrestaChamps\Queue\Jobs\ProductSyncJob();
                            $job->productId = $product->id;
                            $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_REGULAR);
                            if(!$product->active){ // deactivation of product should remove the product from Mailchimp
                                $job->setMethod(\PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_METHOD_DELETE);
                            }
                            $queue = new \PrestaChamps\Queue\Queue();
                            $queue->push($job, 'hook-product-update', $this->context->shop->id);
                        }                        
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = $exception->getMessage();
                    PrestaShopLogger::addLog(
                        "MAILCHIMP_ERROR: {$exception->getMessage()}",
                        1,
                        $exception->getCode(),
                        PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::class,
                        null,
                        true
                    );
                }
            }
        }
    }

    /**
     * Delete the objects from the MailChimp account also
     *
     * @param $object
     */
    public function hookActionObjectDeleteAfter($object)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (is_subclass_of($object['object'], 'ProductCore')) {
                if (Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
                    try {
                        $objectId = $object['object']->id;
                        if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                            $command = new \PrestaChamps\MailchimpPro\Commands\ProductSyncCommand(
                                $this->context,
                                $this->getApiClient(),
                                [$objectId]
                            );
                            $command->setSyncMode($command::SYNC_MODE_REGULAR);
                            $command->setMethod($command::SYNC_METHOD_DELETE);
                            $command->execute();
                        } else {
                            $job = new \PrestaChamps\Queue\Jobs\ProductSyncJob();
                            $job->productId = $objectId;
                            $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_REGULAR);
                            $job->setMethod(\PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_METHOD_DELETE);
                            $queue = new \PrestaChamps\Queue\Queue();
                            $queue->push($job, 'hook-object-delete-after', $this->context->shop->id);
                        }
                    } catch (Exception $e) {
                        $this->context->controller->errors[] = "[MAILCHIMP] :{$e->getMessage()}";
                        PrestaShopLogger::addLog("[MAILCHIMP] :{$e->getMessage()}");
                    }
                }
            }
            elseif (is_subclass_of($object['object'], 'AddressCore')) {
                if (Configuration::get(MailchimpProConfig::SYNC_CUSTOMERS)) {
                    try {
                        $customerId = $object['object']->id_customer;
                        if ($customerId) {
                            $customer = new Customer($customerId);
                            // check filter customers to sync
                            if(in_array($customer->active, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED]) && 
                                in_array($customer->newsletter, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])){
                                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                    $command = new \PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand(
                                        $this->context,
                                        $this->getApiClient(),
                                        [$customerId]
                                    );
                                    if($this->context->controller->controller_type == 'admin'){
                                        $command->setUpdateSubscriptionStatus(false);
                                    }
                                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                                    $command->setMethod($command::SYNC_METHOD_PUT);
                                    $command->execute();
                                } else {
                                    $job = new \PrestaChamps\Queue\Jobs\CustomerSyncJob();
                                    $job->customerId = $customerId;
                                    if($this->context->controller->controller_type == 'admin'){
                                        $job->setUpdateSubscriptionStatus(false);
                                    }
                                    $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_MODE_REGULAR);
                                    $job->setMethod(\PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_METHOD_PUT);
                                    $queue = new \PrestaChamps\Queue\Queue();
                                    $queue->push($job, 'hook-customer-add-after', $this->context->shop->id);
                                }
                            }
                        }
                    } catch (Exception $exception) {
                        $this->context->controller->errors[] = $exception->getMessage();
                        PrestaShopLogger::addLog(
                            "[MAILCHIMP]: {$exception->getMessage()}",
                            1,
                            $exception->getCode(),
                            PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::class,
                            null,
                            true
                        );
                    }
                }
            }
        }
    }

    /**
     * Sync the object updates to Mailchimp
     *
     * @param $object
     */
    public function hookActionObjectUpdateAfter($object)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CUSTOMERS)) {
                if (is_subclass_of($object['object'], 'CustomerCore') || is_subclass_of($object['object'], 'AddressCore')) {
                    try {
                        if (is_subclass_of($object['object'], 'CustomerCore')) {
                            $customerId = $object['object']->id;
                        } else if (is_subclass_of($object['object'], 'AddressCore')) {
                            $customerId = $object['object']->id_customer;
                        }
                        if (isset($customerId) && $customerId) {
                            $customer = new Customer($customerId);
                            // check filter customers to sync
                            if(in_array($customer->active, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED]) && 
                                in_array($customer->newsletter, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])){
                                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                    $command = new \PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand(
                                        $this->context,
                                        $this->getApiClient(),
                                        [$customerId]
                                    );
                                    if($this->context->controller->controller_type == 'admin'){
                                        $command->setUpdateSubscriptionStatus(false);
                                    }
                                    $command->setSyncMode(\PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_MODE_REGULAR);
                                    $command->setMethod(\PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_METHOD_PUT);
                                    $command->execute();
                                } else {
                                    $job = new \PrestaChamps\Queue\Jobs\CustomerSyncJob();
                                    $job->customerId = $customerId;
                                    if($this->context->controller->controller_type == 'admin'){
                                        $job->setUpdateSubscriptionStatus(false);
                                    }
                                    $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_MODE_REGULAR);
                                    $job->setMethod(\PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_METHOD_PUT);
                                    $queue = new \PrestaChamps\Queue\Queue();
                                    $queue->push($job, 'hook-object-update-after', $this->context->shop->id);
                                }
                            }
                        }
                    } catch (Exception $exception) {
                        $this->context->controller->errors[] = $exception->getMessage();
                        PrestaShopLogger::addLog(
                            "[MAILCHIMP]: {$exception->getMessage()}",
                            1,
                            $exception->getCode(),
                            PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::class,
                            null,
                            true
                        );
                    }
                }
            }

            if (is_subclass_of($object['object'], 'ShopCore')) {
                try {
                    $command = new \PrestaChamps\MailchimpPro\Commands\StoreSyncCommand(
                        $this->context,
                        $this->getApiClient(),
                        [$object['object']->id]
                    );
                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                    $command->setMethod($command::SYNC_METHOD_PATCH);
                    
                    $command->execute();
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = $exception->getMessage();
                    PrestaShopLogger::addLog(
                        "[MAILCHIMP]: {$exception->getMessage()}",
                        1,
                        $exception->getCode(),
                        \PrestaChamps\MailchimpPro\Commands\StoreSyncCommand::class,
                        null,
                        true
                    );
                }
            }
        }
    }

    /**
     * @param $params
     *
     * @return string
     */
    // public function hookDisplayAdminOrderContentOrder($params)
    // {
    //     if ($this->isApiKeySet() && $this->isStoreSynced()) {
    //         try {
    //             /**
    //              * @var $order Order
    //              */
    //             $order = $params['order'];
    //          $shop = new Shop($order->id_shop);
    //          $idShop = static::shopIdTransformer($shop);
    //          $response = $this->getApiClient()->get("ecommerce/stores/{$idShop}/orders/{$order->id}");
    //             if ($this->getApiClient()->success()) {
    //                 $this->context->smarty->assign([
    //                     'order' => $response,
    //                 ]);
    //                 return $this->context->smarty->fetch(
    //                     $this->getLocalPath() . 'views/templates/admin/mc-order-detail-tab-content.tpl'
    //                 );
    //             }

    //             return $this->context->smarty->fetch(
    //                 $this->getLocalPath() . 'views/templates/admin/mc-order-detail-tab-content-empty.tpl'
    //             );

    //         } catch (Exception $exception) {
    //             $this->context->controller->errors[] =
    //                 $this->trans("Unable to fetch MailChimp order: %error%", ['%error%' => $exception->getMessage()], 'Modules.Mailchimppro.Mailchimppro');
    //         }
    //     }
    //     return '';
    // }

    /**
     * @param $params
     *
     * @return string
     * @throws SmartyException
     */
    // public function hookDisplayAdminOrderTabOrder($params)
    // {
    //     return $this->context->smarty->fetch(
    //         $this->getLocalPath() . '/views/templates/admin/mc-order-detail-tab-title.tpl'
    //     );
    // }

    /**
     * @throws SmartyException
     */
    // public function hookDisplayBackOfficeTop()
    // {
    //     if ($this->context->controller->controller_name === 'AdminCarts' &&
    //         $this->isApiKeySet() &&
    //         $this->isStoreSynced()) {
    //         $cart = new Cart(Tools::getValue('id_cart'));
    //      $shop = new Shop($cart->id_shop);
    //      $idShop = static::shopIdTransformer($shop);
    //      $response = $this->getApiClient()->get("ecommerce/stores/{$idShop}/carts/{$cart->id}");

    //         if ($this->getApiClient()->success()) {
    //             $this->context->smarty->assign([
    //                 'cart' => $response,
    //             ]);
    //             $this->context->controller->content .=
    //                 $this->context->smarty->fetch(
    //                     $this->getLocalPath() . 'views/templates/admin/mc-cart-detail.tpl'
    //                 );
    //         }
    //     }
    // }

    public function hookDisplayFooterBefore($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_NEWSLETTER_SUBSCRIBERS)) {
                try {
                    \PrestaChamps\MailchimpPro\Hooks\Display\FooterBefore::run(
                        $params,
                        $this->getApiClient(),
                        $this->context
                    )->newsletterBlockRegistration();
                } catch (Exception $exception) {
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    public function hookActionNewsletterRegistrationAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_NEWSLETTER_SUBSCRIBERS)) {
                try {
                    $newsletterSubscriber['email'] = $params['email'];
                    if (isset($this->context->language->id) && $this->context->language->id) {
                        $newsletterSubscriber['id_lang'] = $this->context->language->id;
                    }
                    if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                        $command = new \PrestaChamps\MailchimpPro\Commands\NewsletterSubscriberSyncCommand(
                            $this->context,
                            $this->getApiClient(),
                            [$newsletterSubscriber]
                        );
                        $command->setSyncMode($command::SYNC_MODE_REGULAR);
                        $command->setMethod($command::SYNC_METHOD_PUT);
                        $command->execute();
                    } else {
                        $job = new \PrestaChamps\Queue\Jobs\NewsletterSubscriberSyncJob();
                        $job->newsletterSubscriber = $newsletterSubscriber;
                        $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\NewsletterSubscriberSyncCommand::SYNC_MODE_REGULAR);
                        $job->setMethod(\PrestaChamps\MailchimpPro\Commands\NewsletterSubscriberSyncCommand::SYNC_METHOD_PUT);
                        $queue = new \PrestaChamps\Queue\Queue();
                        $queue->push($job, 'hook-newsletter-registration-after', $this->context->shop->id);
                    }
                } catch (Exception $exception) {
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    protected function isApiKeySet()
    {
        return !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY));
    }

    protected function isStoreSynced()
    {
        return !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID)) && !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED)) && !empty(Configuration::get(MailchimpProConfig::SELECTED_PRESET));
    }

    public function hookActionCustomerAccountAdd($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CUSTOMERS)) {
                try {
                    $customer = $this->getCustomerFromHookParam($params);
                    // check filter customers to sync
                    if(in_array($customer->active, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED]) && 
                        in_array($customer->newsletter, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])){
                        \PrestaChamps\MailchimpPro\Hooks\Action\Customer\AccountAdd::run(
                            $this->context,
                            $this->getApiClient(),
                            $customer
                        );
                    }
                } catch (Exception $exception) {
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    public function hookActionCustomerAccountUpdate($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CUSTOMERS)) {
                try {
                    $customer = $this->getCustomerFromHookParam($params);
                    // check filter customers to sync
                    if(in_array($customer->active, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED]) && 
                        in_array($customer->newsletter, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])){
                        \PrestaChamps\MailchimpPro\Hooks\Action\Customer\AccountUpdate::run(
                            $this->context,
                            $this->getApiClient(),
                            $customer
                        );
                    }
                } catch (Exception $exception) {
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    /**
     * @param $hookParams
     *
     * @return Customer
     * @throws Exception
     */
    private function getCustomerFromHookParam($hookParams)
    {
        if (isset($hookParams['customer']) && $hookParams['customer'] instanceof CustomerCore) {
            return $hookParams['customer'];
        }

        if (isset($hookParams['newCustomer']) && $hookParams['newCustomer'] instanceof CustomerCore) {
            return $hookParams['newCustomer'];
        }

        throw new Exception("Can't get Customer from hook");
    }

    /**
     * @param $params
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
                $productId = isset($params['id_product']) ? $params['id_product'] : Tools::getValue('id_product');
                if (Validate::isLoadedObject(new Product($productId))) {
                    $this->context->smarty->assign([
                        'productId' => $productId,
                        'regenerateLink' => $this->context->link->getAdminLink('AdminMailchimpProConfiguration'),
                    ]);

                    return $this->display(__FILE__, 'views/templates/hook/admin/_products-extra.tpl');
                }
            }
        }

        return "";
    }

    public function hookActionObjectSpecificPriceAddAfter($params){
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
                try {
                    if (is_subclass_of($params['object'], 'SpecificPriceCore')) {
                        $product_id = $params['object']->id_product;
                        $specific_price_id = $params['object']->id;
                        $shopId = $params['object']->id_shop;
                        $from = $params['object']->from;
                        $to = $params['object']->to;

                        $current_date = new DateTime('now', new DateTimeZone(@date_default_timezone_get()));
                        $from_date = new DateTime($from, new DateTimeZone(@date_default_timezone_get()));
                        $to_date = new DateTime($to, new DateTimeZone(@date_default_timezone_get()));

                        $needToRun = 0;
                        $unlimited = 0;

                        if ($from_date > $current_date) {
                            $needToRun = 2;
                        } elseif ($to_date > $current_date) {
                            $needToRun = 1;
                        } elseif ($to == '0000-00-00 00:00:00') {
                            $unlimited = 1;
                        }

                        if ($needToRun > 0 && $unlimited == 0) {                            
                            // Insert the data using the insert method, with INSERT_IGNORE option
                            Db::getInstance()->insert('mailchimppro_specific_price', [
                                                    'id_specific_price' => (int)$specific_price_id,
                                                    'id_product'        => (int)$product_id,
                                                    'start_date'        => pSQL($from),
                                                    'end_date'          => pSQL($to),
                                                    'needToRun'         => pSQL($needToRun),  // Sanitized string input
                                                    'id_shop'           => (int)$shopId
                                                    ], 
                                                    false, 
                                                    true, 
                                                    Db::INSERT_IGNORE
                                                );

                        }

                        if ($needToRun == 1 || ($needToRun == 0 && $unlimited == 1)) {
                            if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                // sync live add sp price
                                $command = new \PrestaChamps\MailchimpPro\Commands\ProductSyncCommand(
                                    $this->context,
                                    $this->getApiClient(),
                                    [$product_id]
                                );
                                $command->setSyncMode($command::SYNC_MODE_REGULAR);
                                $command->setMethod($command::SYNC_METHOD_PATCH); 
                                $result = $command->execute();

                                if (isset($result["requestSuccess"]) && $result["requestSuccess"] == false) {
                                    $command->setMethod($command::SYNC_METHOD_POST);
                                    $result = $command->execute();                                    
                                }
                            } else {
                                // add job add sp price
                                $job = new \PrestaChamps\Queue\Jobs\ProductSyncJob();
                                $job->productId = $product_id;
                                $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_REGULAR);
                                $queue = new \PrestaChamps\Queue\Queue();
                                $queue->push($job, 'hook-specific-price-add-after', $shopId);
                            }
                        }


                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = $exception->getMessage();
                    PrestaShopLogger::addLog(
                        "MAILCHIMP_ERROR: {$exception->getMessage()}",
                        1,
                        $exception->getCode(),
                        PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::class,
                        null,
                        true
                    );
                }
            }
        }
    }

    public function hookActionObjectSpecificPriceUpdateAfter($params){
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
                try {
                    if (is_subclass_of($params['object'], 'SpecificPriceCore')) {

                        $product_id = $params['object']->id_product;
                        $specific_price_id = $params['object']->id;
                        $shopId = $params['object']->id_shop;
                        $from = $params['object']->from;
                        $to = $params['object']->to;

                        $current_date = new DateTime('now', new DateTimeZone(@date_default_timezone_get()));
                        $from_date = new DateTime($from, new DateTimeZone(@date_default_timezone_get()));
                        $to_date = new DateTime($to, new DateTimeZone(@date_default_timezone_get()));

                        $needToRun = 0;
                        $unlimited = 0;
                        $needToRunDB = 0;

                        if ($from_date > $current_date) {
                            $needToRun = 2;
                        } elseif ($to_date > $current_date) {
                            $needToRun = 1;
                        } elseif ($to == '0000-00-00 00:00:00') {
                            $unlimited = 1;
                        }

                        $query = new DbQuery();
                        $query->select('*');
                        $query->from('mailchimppro_specific_price');
                        $query->where('id_specific_price = ' . (int)$specific_price_id);

                        // Execute the query and fetch the result
                        $db_specific_price = Db::getInstance()->getRow($query);

                        if ($db_specific_price) {
                            $needToRunDB = $db_specific_price["needToRun"];
                            $fromDB = $db_specific_price["start_date"];
                            $toDB = $db_specific_price["end_date"];
                            
                            // Define the where condition
                            $where = 'id_specific_price = ' . (int)$specific_price_id;

                            // // Execute the delete query
                            $delete = Db::getInstance()->delete('mailchimppro_specific_price', $where);
                        }

                        if ($needToRun > 0 && $unlimited == 0) {                            
                            // Insert the data using the insert method, with INSERT_IGNORE option
                            Db::getInstance()->insert('mailchimppro_specific_price', [
                                                    'id_specific_price' => (int)$specific_price_id,
                                                    'id_product'        => (int)$product_id,
                                                    'start_date'        => pSQL($from),
                                                    'end_date'          => pSQL($to),
                                                    'needToRun'         => pSQL($needToRun),  // Sanitized string input
                                                    'id_shop'           => (int)$shopId
                                                    ], 
                                                    false, 
                                                    true, 
                                                    Db::INSERT_IGNORE
                                                );

                        }

                        if ($needToRun == 1 || ($needToRun == 0 && $unlimited == 1) || $db_specific_price) {
                            if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                // sync live edit sp price
                                $command = new \PrestaChamps\MailchimpPro\Commands\ProductSyncCommand(
                                    $this->context,
                                    $this->getApiClient(),
                                    [$product_id]
                                );
                                $command->setSyncMode($command::SYNC_MODE_REGULAR);
                                $command->setMethod($command::SYNC_METHOD_PATCH); 
                                $result = $command->execute();

                                if (isset($result["requestSuccess"]) && $result["requestSuccess"] == false) {
                                    $command->setMethod($command::SYNC_METHOD_POST);
                                    $result = $command->execute();                                    
                                }
                            } else {
                                // add job edit sp price
                                $job = new \PrestaChamps\Queue\Jobs\ProductSyncJob();
                                $job->productId = $product_id;
                                $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_REGULAR);
                                $queue = new \PrestaChamps\Queue\Queue();
                                $queue->push($job, 'hook-specific-price-update-after', $shopId);
                            }
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = $exception->getMessage();
                    PrestaShopLogger::addLog(
                        "MAILCHIMP_ERROR: {$exception->getMessage()}",
                        1,
                        $exception->getCode(),
                        PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::class,
                        null,
                        true
                    );
                }
            }
        }
    }

    public function hookActionObjectSpecificPriceDeleteAfter($params){
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
                try {
                    if (is_subclass_of($params['object'], 'SpecificPriceCore') && $this->isApiKeySet()) {

                        $product_id = $params['object']->id_product;
                        $specific_price_id = $params['object']->id;
                        $shopId = $params['object']->id_shop;

                        $query = new DbQuery();
                        $query->select('*');
                        $query->from('mailchimppro_specific_price');
                        $query->where('id_specific_price = ' . (int)$specific_price_id);

                        // Execute the query and fetch the result
                        $db_specific_price = Db::getInstance()->getRow($query);

                        $needToRunDB = 0;

                        if ($db_specific_price) {
                            $needToRunDB = $db_specific_price["needToRun"];

                            // Define the where condition
                            $where = 'id_specific_price = ' . (int)$specific_price_id;

                            // // Execute the delete query
                            $delete = Db::getInstance()->delete('mailchimppro_specific_price', $where);
                        }

                        if ($needToRunDB == 1 || !$db_specific_price) {
                            if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                // sync live delete sp price
                                $command = new \PrestaChamps\MailchimpPro\Commands\ProductSyncCommand(
                                    $this->context,
                                    $this->getApiClient(),
                                    [$product_id]
                                );
                                $command->setSyncMode($command::SYNC_MODE_REGULAR);
                                $command->setMethod($command::SYNC_METHOD_PATCH); 
                                $result = $command->execute();

                                if (isset($result["requestSuccess"]) && $result["requestSuccess"] == false) {
                                    $command->setMethod($command::SYNC_METHOD_POST);
                                    $result = $command->execute();                                    
                                }
                            } else {
                                // add job edit sp price
                                $job = new \PrestaChamps\Queue\Jobs\ProductSyncJob();
                                $job->productId = $product_id;
                                $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_REGULAR);
                                $queue = new \PrestaChamps\Queue\Queue();
                                $queue->push($job, 'hook-specific-price-delete-after', $shopId);
                            }
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = $exception->getMessage();
                    PrestaShopLogger::addLog(
                        "MAILCHIMP_ERROR: {$exception->getMessage()}",
                        1,
                        $exception->getCode(),
                        PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::class,
                        null,
                        true
                    );
                }
            }
        }
    }

    public static function shopIdTransformer(\Shop $shop)
    {
        if (Configuration::get(MailchimpProConfig::MULTI_INSTANCE_MODE) == 1) {
            return $shop->domain . "_" . $shop->id;
        }

        return $shop->id;
    }

    protected function getShopId()
    {
        return static::shopIdTransformer($this->context->shop);
    }

    public static function getCustomerLanguageIsoCode($isoCode){
        $iso_return = '';
        
        switch ((string)$isoCode){
            case 'fr': 
                $iso_return = 'fr';
                break;
            case 'qc': 
                $iso_return = 'fr_CA';
                break;
            case 'pt':
                $iso_return = 'pt_PT';
                break;
            case 'br':
                $iso_return = 'pt';
                break;
            case 'es': 
                $iso_return = 'es_ES';
                break;
            case 'mx':
                $iso_return = 'es';
                break;
            default:
                $iso_return = $isoCode; 
        }

        return $iso_return;
    }
}
