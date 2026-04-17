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
 * If you need help please contact support@prestachamps.com
 *
 * @author    PrestaChamps <support@prestachamps.com>
 * @copyright PrestaChamps
 * @license   commercial
 */

namespace PrestaChamps\PrestaShop\Override;
if (!defined('_PS_VERSION_')) {
    exit;
}

class OverrideInstaller
{
    /**
     * Module instance
     * @var \Module
     */
    protected $module;
    
    /**
     * ConflictChecker instance
     * @var ConflictChecker
     */
    protected $conflictChecker;
    
    /**
     * Constructor
     * 
     * @param \Module $module The module instance
     * @param ConflictChecker $conflictChecker Optional ConflictChecker instance
     */
    public function __construct(\Module $module, ConflictChecker $conflictChecker = null)
    {
        $this->module = $module;
        $this->conflictChecker = $conflictChecker ?: new ConflictChecker($module);
    }
    
    /**
     * Install overrides safely with conflict checking
     * 
     * @return bool True if installation was successful
     */
    public function install()
    {
        try {            
            // Copy files from overrides_pending to overrides
            $this->copyPendingOverridesToModuleOverrides();
            
            // Then use PrestaShop's standard method to register the overrides
            return $this->module->installOverrides();
        } catch (\Exception $e) {
            // Log the conflict
            \PrestaShopLogger::addLog(
                'Override conflict detected in ' . $this->module->name . ': ' . $e->getMessage(),
                3, // Error level
                null,
                $this->module->name,
                null,
                true
            );
            
            throw $e;
        }
    }
    
    /**
     * Copy files from overrides_pending to the module's overrides directory
     */
    protected function copyPendingOverridesToModuleOverrides()
    {

        $pendingOverridesDir = _PS_MODULE_DIR_ . $this->module->name . '/overrides_pending/';
        $moduleOverridesDir = _PS_MODULE_DIR_ . $this->module->name . '/override';

        if (file_exists($moduleOverridesDir) && is_dir($moduleOverridesDir)) {
            $this->recursiveRmdir($moduleOverridesDir);
        }

        if (is_dir($pendingOverridesDir)) {
            $this->copyDir($pendingOverridesDir, $moduleOverridesDir);
        }

        return true;
    }

    protected function copyDir($src, $dst)
    {
        if (is_dir($src)) {
            $dir = opendir($src);
            @mkdir($dst);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src.'/'.$file)) {
                        $this->copyDir($src.'/'.$file, $dst.'/'.$file);
                    } else {
                        copy($src.'/'.$file, $dst.'/'.$file);
                    }
                }
            }
            closedir($dir);
        }
    }

    protected function recursiveRmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        $this->recursiveRmdir($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
    
    /**
     * Uninstall overrides
     * 
     * @return bool True if uninstallation was successful
     */
    public function uninstall()
    {
        return $this->module->uninstallOverrides();
    }
}
