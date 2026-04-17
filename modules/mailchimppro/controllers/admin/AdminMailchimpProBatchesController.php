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
 * Class AdminMailchimpProBatchesController
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AdminMailchimpProBatchesController extends \PrestaChamps\MailchimpPro\Controllers\BaseMCObjectController
{
    public $entityPlural   = 'batches';
    public $entitySingular = 'batch';

    protected function getListApiEndpointUrl()
    {
        return '/batches';
    }

    protected function getSingleApiEndpointUrl($entityId)
    {
        return "batches/{$entityId}";
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
     * @throws Exception
     */
    public function processSingle()
    {
        parent::processSingle();
        try {
            // Create a temporary filename with a safe, randomized prefix
            $tempFilename = tempnam(sys_get_temp_dir(), 'TMP_');
            $destinationPath = $tempFilename . '_extracted';
            $zipName = $tempFilename . '.zip';

            // Safely copy the remote file
            copy($this->entity['response_body_url'], $tempFilename . '.tar.gz');

            // Safely handle the tar.gz file
            $p = new PharData($tempFilename . '.tar.gz');
            $p->convertToData(Phar::ZIP);

            // Extract the ZIP archive safely
            $zip = new ZipArchive;
            $res = $zip->open($zipName);
            if ($res === true) {
                // Validate and ensure extraction is confined to the destination path
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $zip->extractTo($destinationPath);
                $zip->close();
            } else {
                throw new Exception('Failed to open the ZIP archive');
            }

            // Process the extracted files safely
            $it = new RecursiveDirectoryIterator($destinationPath, RecursiveDirectoryIterator::SKIP_DOTS);
            $responses = [];
            foreach (new RecursiveIteratorIterator($it) as $file) {
                // Validate file path to prevent path traversal
                if ($file->getExtension() === 'json' && strpos(realpath($file), realpath($destinationPath)) === 0) {
                    $items = json_decode(Tools::file_get_contents($file), true);
                    foreach ($items as $item) {
                        $responses[] = \PrestaChamps\MailchimpPro\Models\BatchResponse::fromArray($item);
                    }
                }
            }

            // Assign responses to the Smarty template
            $this->context->smarty->assign([
                'responses' => $responses,
            ]);
            $this->content .= $this->context->smarty->fetch(
                $this->module->getLocalPath() . 'views/templates/admin/entity_single/batch-responses.tpl'
            );

            // Clean up temporary files and directories safely
            @Tools::deleteDirectory($destinationPath, true);
            @Tools::deleteFile($zipName);
            @Tools::deleteFile($tempFilename . '.tar.gz');
            @Tools::deleteFile($tempFilename . '.tar');
            @Tools::deleteFile($tempFilename);

        } catch (Exception $exception) {
            $this->errors[] = $this->trans("Can't decode response" , [], 'Modules.Mailchimppro.Adminmailchimpprobatches');
        }
    }

}
