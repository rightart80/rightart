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

namespace PrestaChamps\Queue;
if (!defined('_PS_VERSION_')) {
    exit;
}
use Db;
use DbQuery;
use PrestaChamps\Queue\Exceptions\InvalidJobException;

use PrestaChamps\Queue\Jobs\CartRuleSyncJob;
use PrestaChamps\Queue\Jobs\CartSyncJob;
use PrestaChamps\Queue\Jobs\CustomerSyncJob;
use PrestaChamps\Queue\Jobs\NewsletterSubscriberSyncJob;
use PrestaChamps\Queue\Jobs\OrderSyncJob;
use PrestaChamps\Queue\Jobs\ProductSyncJob;
use PrestaChamps\Queue\Jobs\MergeTagPromoCodeSyncJob;
use PrestaChamps\MailchimpPro\Models\Campaign;
use PrestaChamps\MailchimpPro\Commands\PromoMergeTagSyncCommand;

class Queue
{
    //protected $table = "ps_mailchimppro_jobs";
	protected $table = _DB_PREFIX_ . "mailchimppro_jobs";
	protected $max_try;
	protected $max_jobs;

    const STATUS_AVAILABLE = 0;
    const STATUS_PROCESSED = 100;
    const STATUS_PROCESSING = 10;
    const STATUS_FAILED = -1;
    const PRIORITIES_BY_TYPE = array (
        "product"                => 1,
        "customer"               => 2,
        "cartRule"               => 3,
        "order"                  => 4,
        "cart"                   => 5,
        "newsletterSubscriber"   => 6,
        "mergeTagPromoCode"		 => 7,
	);
	
	public function __construct()
	{
		$this->max_try = ($max = (int)\Configuration::get(\MailchimpProConfig::QUEUE_ATTEMPT)) && $max > 0 && \Validate::isUnsignedInt($max) ? $max : 2;
		$this->max_jobs = ($limit = (int)\Configuration::get(\MailchimpProConfig::QUEUE_STEP)) && $limit > 0 && \Validate::isUnsignedInt($limit) ? $limit : 10;
	}
	
    public function push(JobInterface $job, $channel, $id_shop)
    {
        $class = $this->escape($job->getJobType());

        $bodyJson = $job->convertToArrayJson();

        if (!$entityId = $job->getJobId()) {
            $entityId = 0;
        }
		if (!$priority = self::PRIORITIES_BY_TYPE[$class]) {
			$priority = 1;
		}

	    // Special handling for product jobs only
	    if ($class === 'product') {
	        $newJobData = json_decode($bodyJson, true);
	        $newMethod = isset($newJobData['method']) ? $newJobData['method'] : null;

            $query = new DbQuery();
            $query->select('id_job, body');
            $query->from('mailchimppro_jobs');
            $query->where('id_entity = ' . (int)$entityId);
            $query->where('type = \'' . pSQL($class) . '\'');
            $query->where('id_shop = ' . (int)$id_shop);
            
            $existingJob = Db::getInstance()->getRow($query);
            
            if ($existingJob) {
                $existingJobData = json_decode($existingJob['body'], true);
                $existingMethod = isset($existingJobData['method']) ? $existingJobData['method'] : null;
                if ($existingMethod !== $newMethod) {
                    $where = 'id_job = ' . (int)$existingJob['id_job'];
                    Db::getInstance()->delete('mailchimppro_jobs', $where);
                }
            }
	    }

	    // Use PrestaShop's insert method with INSERT_IGNORE option
		Db::getInstance()->insert('mailchimppro_jobs', [
							    'channel'   => pSQL($channel),
							    'body'      => Db::getInstance()->escape($bodyJson),
							    'type'      => pSQL($class),
							    'id_entity' => (int)$entityId,
							    'priority'  => (int)$priority,
							    'id_shop'   => (int)$id_shop
							], 
							false, 
							true, 
							Db::INSERT_IGNORE
						);
    }

    protected function escape($value)
    {
        $search = array("\\", "\x00", "\n", "\r", "'", '"', "\x1a");
        $replace = array("\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z");

        return str_replace($search, $replace, $value);
    }

    public function getNumberOfAvailableJobs($channel = "%%")
    {
    	$idShop = \Context::getContext()->shop->id;

		// Create a new DbQuery object
		$query = new DbQuery();
		$query->select('COUNT(*)');
		$query->from('mailchimppro_jobs');
		$query->where('`status` = ' . (int)self::STATUS_AVAILABLE);
		$query->where('`id_shop` = ' . (int)$idShop);

		// Execute the query and get the count
		return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);;
    }
	
	public function getNumberOfTotalJobs($channel = "%%")
    {
    	$idShop = \Context::getContext()->shop->id;

    	// Create a new DbQuery object
		$query = new DbQuery();
		$query->select('COUNT(*)');
		$query->from('mailchimppro_jobs');
		$query->where('`id_shop` = ' . (int)$idShop);

		// Execute the query and get the count
		return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public function getNumberOfAvailableJobsPerType($channel = "%%")
    {
    	$idShop = \Context::getContext()->shop->id;

    	$query = new DbQuery();
		$query->select('`type`, COUNT(*) as count');
		$query->from('mailchimppro_jobs');
		$query->where('`status` = ' . (int)self::STATUS_AVAILABLE);
		$query->where('`id_shop` = ' . (int)$idShop);
		$query->groupBy('`type`');

		// Execute the query and fetch the results
		$results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		// Convert the results into a key-value pair array
		$keyValuePair = [];
		foreach ($results as $row) {
		    $keyValuePair[$row['type']] = $row['count'];
		}

		return $keyValuePair;
	}

	////////////////////////
	private function countAvailableProductJobsForBatch($channel = "%%") {
	    $idShop = \Context::getContext()->shop->id;

	    $query = new DbQuery();
	    $query->select('COUNT(*)');
	    $query->from('mailchimppro_jobs');
	    $query->where('`status` = ' . (int)self::STATUS_AVAILABLE);
	    $query->where('`id_shop` = ' . (int)$idShop);
	    $query->where('`type` = \'' . pSQL(\PrestaChamps\Queue\Jobs\ProductSyncJob::JOB_TYPE) . '\'');
	    $query->where('`channel` LIKE \'' . pSQL($channel) . '\'');
	    
	    return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}
	///////////////////////////

    public function getNumberOfJobsInFlight($channel = "%%")
    {
    	$idShop = \Context::getContext()->shop->id;

    	$query = new DbQuery();
		$query->select('COUNT(*)');
		$query->from('mailchimppro_jobs');
		$query->where('`status` = ' . (int)self::STATUS_PROCESSING);
		$query->where('`id_shop` = ' . (int)$idShop);

		return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}

    protected function reserveJob($jobId)
    {
    	// Prepare the data to be updated
		$data = [
		    'status'    => (int)self::STATUS_PROCESSING,
		    'locked_at' => ['type' => 'sql', 'value' => 'CURRENT_TIMESTAMP']  // Using SQL function directly
		];

		// Define the where condition
		$where = 'id_job = ' . (int)$jobId;

		// Execute the update query
		return Db::getInstance()->update('mailchimppro_jobs', $data, $where);
    }

    protected function unReserveJob($jobId, $message = "Unknown error")
    {
    	// Prepare the data to be updated
		$data = [
		    'status' => (int)self::STATUS_AVAILABLE,
		    'error'  => pSQL($message)
		];

		// Define the where condition
		$where = 'id_job = ' . (int)$jobId;

		// Execute the update query
		return Db::getInstance()->update('mailchimppro_jobs', $data, $where);
    }

    protected function deleteJob($jobId)
    {
    	// Define the where condition
		$where = 'id_job = ' . (int)$jobId;

		// Execute the delete query
		return Db::getInstance()->delete('mailchimppro_jobs', $where);
    }

    protected function deleteUnsuccessfulJobs()
    {
    	// Define the where condition
		$where = 'attempts >= ' . (int)$this->max_try;

		// Execute the delete query
		return Db::getInstance()->delete('mailchimppro_jobs', $where);
    }

    public function clearChannel($channel = "%%")
    {
    	$idShop = \Context::getContext()->shop->id;

		// Prepare the where condition
		$where = "`id_shop` = " . (int)$idShop;

		// Execute the delete query
		return Db::getInstance()->delete('mailchimppro_jobs', $where);
    }

    protected function increaseAttemptsCounter($jobId)
    {
        // Prepare the data to be updated
		$data = [
		    'attempts' => ['type' => 'sql', 'value' => '`attempts` + 1']  // SQL expression to increment attempts
		];

		// Define the where condition
		$where = 'id_job = ' . (int)$jobId;

		// Execute the update query
		return Db::getInstance()->update('mailchimppro_jobs', $data, $where);
    }
	
	protected function getAttemptsCounter($jobId)
    {
        $query = new DbQuery();
		$query->select('`attempts`');
		$query->from('mailchimppro_jobs');
		$query->where('`id_job` = ' . (int)$jobId);

		// Execute the query and get the result
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public function pop($channel = "%%", $limit = false, $jobType = null)
    {
    	$idShop = \Context::getContext()->shop->id;
		$max_try = pSQL($this->max_try);

		if(!$limit) {
			$query = new DbQuery();
			$query->select('*');
			$query->from('mailchimppro_jobs');
			$query->where('(`status` = ' . (int)self::STATUS_AVAILABLE . ' AND `attempts` < ' . (int)Db::getInstance()->escape(pSQL($max_try)) . ') 
			              OR (`status` = ' . (int)self::STATUS_PROCESSING . ' AND TIMESTAMPDIFF(SECOND, `locked_at`, CURRENT_TIMESTAMP) > 60)');
			$query->where('`id_shop` = ' . (int)$idShop);

			$query->where('`channel` LIKE \'' . pSQL($channel) . '\'');

			// Add job type filter if specified
	        if ($jobType !== null) {
	            $query->where('`type` = \'' . pSQL($jobType) . '\'');
	        }

			$query->orderBy('`priority` ASC, `id_entity` ASC');

			// Execute the query and fetch the result
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
		}
		else {
			$query = new DbQuery();
			$query->select('*');
			$query->from('mailchimppro_jobs');
			$query->where('(`status` = ' . (int)self::STATUS_AVAILABLE . ' AND `attempts` < ' . (int)Db::getInstance()->escape(pSQL($max_try)) . ') 
			              OR (`status` = ' . (int)self::STATUS_PROCESSING . ' AND TIMESTAMPDIFF(SECOND, `locked_at`, CURRENT_TIMESTAMP) > 60)');
			$query->where('`id_shop` = ' . (int)$idShop);

			$query->where('`channel` LIKE \'' . pSQL($channel) . '\'');

			// Add job type filter if specified
	        if ($jobType !== null) {
	            $query->where('`type` = \'' . pSQL($jobType) . '\'');
	        }

			$query->orderBy('`priority` ASC, `id_entity` ASC');
			$query->limit((int)Db::getInstance()->escape(pSQL($limit)));

			

			// Execute the query and fetch all results
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
		}

        if (empty($result)) {
            return null;
        }
		
		if (!$limit) {
			$this->increaseAttemptsCounter($result['id_job']);
		}
		else {
			foreach ($result as $queue) {
				$this->increaseAttemptsCounter($queue['id_job']);
			}
		}

        return $result;
    }

    /**
     * @throws InvalidJobException
     */
    public function runJob($channel = "%%")
    {
        $errorMessage = '';
        $requestSuccess = true;
		$jobType = '';

        $jobData = $this->pop($channel);

        if (empty($jobData)) {
            $this->deleteUnsuccessfulJobs();
			return [
				'success' => true,
				'message' => 'No job to sync!',
				'type' => null,
				'data' => null
			];
        }

        try {

        	$job = $this->getJobObject($jobData["type"], $jobData["body"]);

            $this->reserveJob($jobData['id_job']);

            if (!($job instanceof JobInterface)) {
                $resp = "Deserialized object is not an instance of " . JobInterface::class;
                $this->unReserveJob($jobData['id_job'], $resp);
                throw new InvalidJobException($resp);
            }

            $response = $job->execute($jobData['id_shop']);
            
            if (isset($response['requestSuccess']) && $response['requestSuccess'] == true) {
                $this->deleteJob($jobData['id_job']);
                $requestSuccess = true;
				$jobType = $job->getJobType();
            }
            else {
                $requestSuccess = false;
				if (isset($response['requestLastErrors'])) {
                    if (is_array($response['requestLastErrors'])) {
                        $errorMessage = implode(";", array_values($response['requestLastErrors']));
                    }
                    else {
                        $errorMessage = $response['requestLastErrors'];
                    }
					if ($attempsCount = $this->getAttemptsCounter($jobData['id_job'])) {
						$errorMessage .= ' (' . $attempsCount . '. attemp(s))';
					}
					$this->unReserveJob($jobData['id_job'], $errorMessage);
                }
				else {
					$this->unReserveJob($jobData['id_job']);
				}
			}
			$this->deleteUnsuccessfulJobs();
        } catch (\Exception $exception) {
            $this->unReserveJob($jobData['id_job'], $exception->getMessage());
            return [
                'success' => false,
                'message' => $exception->getMessage(),
                'type' => null,
                'data' => null,
            ];
        }

        return [
            'success' => $requestSuccess,
            'message' => $errorMessage,
            'type' => get_class($job),
            'data' => $jobData,
			'jobType' => $jobType
        ];
    }
	
	public function addSpecificPriceProductJobs(){

		$current_date = new \DateTime('now', new \DateTimeZone(@date_default_timezone_get()));

		$query = new DbQuery();
		$query->select('*');
		$query->from('mailchimppro_specific_price');
		$query->where('(`needToRun` = 2 AND `start_date` < \'' . Db::getInstance()->escape(pSQL($current_date->format('Y-m-d H:i:s'))) . '\') 
		              OR (`needToRun` = 1 AND `end_date` < \'' . Db::getInstance()->escape(pSQL($current_date->format('Y-m-d H:i:s'))) . '\')');

		// Execute the query and fetch the results
		$specific_prices = Db::getInstance()->executeS($query);

		if(count($specific_prices) > 0){
			foreach($specific_prices as $specific_price){

				$queue = new Queue();
                $job = new Jobs\ProductSyncJob();
                $job->productId = $specific_price["id_product"];
                $queue->push($job, 'setup-wizard', $specific_price["id_shop"]);

				if ($specific_price["needToRun"] == 1) {
                    // Define the where condition
					$where = 'id_specific_price = ' . (int)$specific_price['id_specific_price'];

					// Execute the delete query
					$delete = Db::getInstance()->delete('mailchimppro_specific_price', $where);
                }

				if ($specific_price["needToRun"] == 2) {
					// Prepare the data to be updated
					$data = [
					    'needToRun' => 1
					];

					// Define the where condition
					$where = 'id_specific_price = ' . (int)$specific_price['id_specific_price'];

					// Execute the update query
					$update = Db::getInstance()->update('mailchimppro_specific_price', $data, $where);
                }

			}
		}

	}

	public function checkAndDeleteExpiredPromoMergeFields() 
	{
		$campaigns = Campaign::getExpiredSyncedCampaigns();

		foreach ($campaigns as $campaign) {
			$campaign = new Campaign($campaign['id']);

	        if($campaign->id_merge_field_mc > 0){

	            $promoCommand = new PromoMergeTagSyncCommand(
	                \Context::getContext(),
	                \Module::getInstanceByName('mailchimppro')->getApiClient(),
	                $campaign,
	                [],
	                \Context::getContext()->shop->id
	            );

	            $delete_response = $promoCommand->deleteMergeFieldFromMailchimp();

	            $campaign->id_merge_field_mc = null;
	            $campaign->tag_merge_field_mc = null;

	            $campaign->update();         
	        }
		}
	}

	public function runCronjob($channel = "%%")
    {
		\Configuration::updateValue(\MailchimpProConfig::LAST_CRONJOB, date('Y-m-d H:i:s'), true);
		\Configuration::updateGlobalValue('MAILCHIMPPRO_IS_CRONJOB_RUNNING', true, true);
		
		$startTime = new \DateTime();

		if (!($token = trim(\Tools::getValue('secure'))) || !\Validate::isCleanHtml($token) || $token != \Configuration::get(\MailchimpProConfig::CRONJOB_SECURE_TOKEN)) {
			if (\Tools::isSubmit('ajax')) {
				die(json_encode(array(
					'errors' => 'Access denied',
					'result' => ''
				)));
			}
			die('Access denied');
		}

		$this->addSpecificPriceProductJobs();

		$this->checkAndDeleteExpiredPromoMergeFields();
		
		$count = 0;
		$errors = array();
		
		$max_try = $this->max_try ? $this->max_try : 2;
		$max_jobs = $this->max_jobs? $this->max_jobs : 10;		
		$status = self::STATUS_AVAILABLE;

		////////////////////////////////////////////////////////////////
	    // Product batch processing
	    ////////////////////////////////////////////////////////////////

	    $max_batch_jobs = 50;
	    $start_batch = new \DateTime();
	    
	    $channel = 'setup-wizard'; // for product batch jobs 
	    $totalProductJobs = $this->countAvailableProductJobsForBatch($channel);
	    $maxProductJobsPerMinute = 50 * $max_batch_jobs; // 2500 jobs max
	    
	    $processedProductJobs = 0;
	    $batchNum = 0; // Fixed: Declare outside the loop
	    $shouldProcessNonProductJobs = ($totalProductJobs < $maxProductJobsPerMinute);
	    
	    // Determine how many product jobs to process
	    $targetProductJobs = $shouldProcessNonProductJobs ? $totalProductJobs : $maxProductJobsPerMinute;
	    $batchesNeeded = ceil($targetProductJobs / 50);
	    
	    // Single batch processing loop
	    for ($batchNum = 0; $batchNum < $batchesNeeded; $batchNum++) {
	        $remainingJobs = $targetProductJobs - $processedProductJobs;
	        $batchSize = min(50, $remainingJobs);
	        
	        if ($productJobs = $this->pop($channel, $batchSize, \PrestaChamps\Queue\Jobs\ProductSyncJob::JOB_TYPE)) {
	            // Reserve jobs
	            foreach ($productJobs as $job) {
	                $this->reserveJob($job['id_job']);
	            }
	            
	            // Process batch
	            $response = \PrestaChamps\Queue\Jobs\ProductSyncJob::handleBatch($productJobs, \Context::getContext()->shop->id);
	            
	            if (isset($response['requestSuccess']) && $response['requestSuccess'] == true) {
	                foreach ($productJobs as $job) {
	                    $this->deleteJob($job['id_job']);
	                    $processedProductJobs++;
	                }
	            } else {
	                $errors[] = "Batch $batchNum product sync failed.";
	                foreach ($productJobs as $job) {
	                    $this->unReserveJob($job['id_job'], "Batch processing failed");
	                }
	            }
	        } else {
	            break; // No more product jobs available
	        }
	    }
	    
	    $end_batch = new \DateTime();
	    $batchProcessingTime = $start_batch->diff($end_batch)->format('%H:%i:%s');

	    ////////////////////////////////////////////////////////////////
	    // Process non-product jobs if needed and we have capacity
	    ////////////////////////////////////////////////////////////////


	    // Fixed: Use actual processed jobs count instead of batch number
    	if ($shouldProcessNonProductJobs && $batchesNeeded < $max_jobs) {
	        // Calculate how many non-product jobs we can process
			$remainingJobSlots = $max_jobs - $batchesNeeded;

	        if ($remainingJobSlots > 0) {
				$channel = "%%";
	            if ($queues = $this->pop($channel, $remainingJobSlots)) {
	                // reserve all the selected jobs
	                foreach ($queues as $queue) {
	                    $this->reserveJob($queue['id_job']);
	                }
	                
	                foreach ($queues as $queue) {
	                    $errorMessage = '';
	                    $requestSuccess = true;

	                    $job = $this->getJobObject($queue["type"], $queue["body"]);

	                    if (!($job instanceof JobInterface)) {
	                        $resp = "Deserialized object is not an instance of " . JobInterface::class;
	                        $this->unReserveJob($queue['id_job'], $resp);
	                        continue; // Fixed: Add missing continue statement
	                    }
	                    
	                    $response = $job->execute($queue['id_shop']); // parameter is store ID from PS multi store configuration
	                    
	                    if (isset($response['requestSuccess']) && $response['requestSuccess'] == true) {
	                        $count++;
	                        $this->deleteJob($queue['id_job']);
	                    } else {
	                        $requestSuccess = false;
	                        if (isset($response['requestLastErrors'])) {
	                            if (is_array($response['requestLastErrors'])) {
	                                $errorMessage = implode(";", array_values($response['requestLastErrors']));
	                            } else {
	                                $errorMessage = $response['requestLastErrors'];
	                            }
	                            if ($attempsCount = $this->getAttemptsCounter($queue['id_job'])) {
	                                $errorMessage .= ' (' . $attempsCount . '. attempt(s))';
	                            }
	                            
	                            if (!empty($response['requestLastResponse']['body'])) {
	                                $responseBody = json_decode($response['requestLastResponse']['body'], true);
	                                if (!empty($responseBody['errors']) && is_array($responseBody['errors'])) {
	                                    $additionalErrors = array();
	                                    foreach ($responseBody['errors'] as $bodyErrors) {
	                                        if (!empty($bodyErrors['message'])) {
	                                            $additionalErrors[] = $bodyErrors['message'];
	                                        }
	                                    }
	                                    if (!empty($additionalErrors)) {
	                                        $errorMessage .= "\n**********\n • " . implode("\n • ", $additionalErrors) . "\n**********\n";
	                                    }
	                                }
	                            }
	                            
	                            $this->unReserveJob($queue['id_job'], $errorMessage);
	                            $errors[] = '− Job type: ' . $job->getJobType() . ' | Id: ' . $queue['id_entity'] . ' | Message: ' . $errorMessage;
	                        } else {
	                            $this->unReserveJob($queue['id_job']);
	                        }
	                    }
	                }
	            }
	        }
	    }

		$this->deleteUnsuccessfulJobs();
		$totalJobsProcessed = 0;
		if ((int)\Configuration::get(\MailchimpProConfig::LOG_CRONJOB)) {
			$log = "================================================================\n";
			$log .= date(\Context::getContext()->language->date_format_full);
			
			// Track total jobs processed
			$totalJobsProcessed = $processedProductJobs + $count;
			
			// Build the main message
			if ($totalJobsProcessed > 0) {
				$log .= '  ' . sprintf('There were %s job(s) successfully synced to the MailChimp!', $totalJobsProcessed);
				

				// Add breakdown details
				$breakdown = array();
				if ($processedProductJobs > 0) {
					$breakdown[] = sprintf('%s product sync job(s)', $processedProductJobs);
				
					if ($count > 0) {
						$breakdown[] = sprintf('%s other job(s)', $count);
					}
					
					if (count($breakdown) > 0) {
						$log .= "\n  Breakdown: " . implode(', ', $breakdown);
					}
				}
				
				// Add batch processing details if product jobs were processed
				if ($processedProductJobs > 0) {
					$log .= "\n  Product batch processing: {$batchesNeeded} batch(es) in {$batchProcessingTime}";

				}
			} else {
				$log .= '  ' . 'No job has been synced';
			}

			if ($errors) {
				$log .= "\n";
				$log .= 'Errors: ';
				foreach ($errors as $error) {
					$log .= " \n " . $error;
				}
			}
			$log .= "\n";

			// Define the destination directory
		    $dest = _PS_MODULE_DIR_ . 'mailchimppro/logs';

		    // Resolve the destination path
		    $resolvedDest = realpath($dest) ?: $dest; // Use the original path if realpath fails

		    // Ensure the directory exists, create it if it doesn't
		    if (!is_dir($resolvedDest)) {
		        if (!mkdir($resolvedDest, 0755, true)) {
		            // Handle error if directory creation fails
		            throw new \Exception('Failed to create log directory.');
		        }

		        $resolvedDest = realpath($resolvedDest) ?: $dest; // Use the original path if realpath fails
		    }

		    // Ensure the resolved path is within the intended directory
		    if (strpos(realpath($resolvedDest), realpath(_PS_MODULE_DIR_ . 'mailchimppro')) === 0) {
		        if ($count || $processedProductJobs || $errors) {
		            // Safely append the log to the file
		            $logFilePath = $resolvedDest . '/cronjob.log';
		            if (file_put_contents($logFilePath, $log . PHP_EOL, FILE_APPEND) === false) {
		                // Handle error if writing to the log file fails
		                throw new \Exception('Failed to write to log file.');
		            }
		        }
		    } else {
		        // Handle error if the path is not valid
		        throw new \Exception('Invalid log directory path.');
		    }
		}
	
		\Configuration::updateGlobalValue('MAILCHIMPPRO_IS_CRONJOB_RUNNING', false, true);
		
		$endTime = new \DateTime();
		\Configuration::updateValue(\MailchimpProConfig::LAST_CRONJOB_EXECUTION_TIME, $startTime->diff($endTime)->format('%Y-%m-%d %H:%i:%s'), true);
		
		$jsonArr = array(
			'result' => 'Cronjob ran successfully:' . ' ' . ($totalJobsProcessed <= 0 ? 'No job has been synced!' : sprintf('%s job(s) was synced to the MailChimp!', $totalJobsProcessed)),
		);

        die(json_encode($jsonArr));
    }

    public function getJobObject($type,$body)
    {
    	$job = null;

    	switch($type){
			case CartRuleSyncJob::JOB_TYPE:{
				$job = new CartRuleSyncJob($body);
				break;
			}
			case CartSyncJob::JOB_TYPE:{
				$job = new CartSyncJob($body);
				break;
			}
			case CustomerSyncJob::JOB_TYPE:{
				$job = new CustomerSyncJob($body);
				break;
			}
			case NewsletterSubscriberSyncJob::JOB_TYPE:{
				$job = new NewsletterSubscriberSyncJob($body);
				break;
			}
			case OrderSyncJob::JOB_TYPE:{
				$job = new OrderSyncJob($body);
				break;
			}
			case ProductSyncJob::JOB_TYPE:{
				$job = new ProductSyncJob($body);
				break;
			}
			case MergeTagPromoCodeSyncJob::JOB_TYPE:{
				$job = new MergeTagPromoCodeSyncJob($body);
				break;
			}
		}

		return $job;
    }
}
