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

class ConflictChecker
{
    /**
     * Module instance
     * @var \Module
     */
    protected $module;
    
    /**
     * Path to the pending overrides directory
     * @var string
     */
    protected $pendingOverridesDir;
    
    /**
     * Path to the PrestaShop overrides directory
     * @var string
     */
    protected $psOverridesDir;
    
    /**
     * Marker comment to identify our module's overrides
     * @var string
     */
    protected $moduleMarker;

    /**
     * Overrides array to identify our module's overrides
     * @var string
     */
    protected $overridesToCheck;
    
    /**
     * Constructor
     * 
     * @param \Module $module The module instance
     * @param string $pendingOverridesDir Path to pending overrides directory
     */
    public function __construct(\Module $module, $pendingOverridesDir = null)
    {
        $this->module = $module;
        $this->pendingOverridesDir = $pendingOverridesDir ?: _PS_MODULE_DIR_ . $this->module->name . '/overrides_pending/';
        $this->psOverridesDir = _PS_OVERRIDE_DIR_;
        $this->moduleMarker = '/* ' . $this->module->name . ' */';
        $this->overridesToCheck = [
            'classes/Cart.php',
            'classes/CartRule.php',
        ];

    }
    
    /**
     * Check if an override file exists in PrestaShop override directory
     * 
     * @param string $relativePath Relative path to the override file (e.g., 'classes/Cart.php')
     * @return bool True if override exists, false otherwise
     */
    public function overrideExists($relativePath)
    {
        return file_exists($this->psOverridesDir . $relativePath);
    }
    
    /**
     * Check if our custom code/marker is already installed in an override
     * 
     * @param string $relativePath Relative path to the override file
     * @return bool True if marker exists, false otherwise
     */
    public function hasCustomCode($relativePath)
    {
        if (!$this->overrideExists($relativePath)) {
            return false;
        }
        
        $content = file_get_contents($this->psOverridesDir . $relativePath);
        return strpos($content, $this->moduleMarker) !== false;
    }
    
    /**
     * Detect if there's a real conflict and return detailed information
     * 
     * @param string $relativePath Relative path to the override file
     * @return array Returns conflict details or success response
     */
    public function hasConflict($relativePath)
    {
        // If the override file doesn't exist in PrestaShop, no conflict
        $existingFile = $this->psOverridesDir . $relativePath;
        if (!file_exists($existingFile)) {
            return [
                'status' => 'success',
                'message' => 'No conflict detected'
            ];
        }
        
        // If our pending override doesn't exist, no conflict
        $pendingFile = $this->pendingOverridesDir . $relativePath;
        if (!file_exists($pendingFile)) {
            return [
                'status' => 'success',
                'message' => 'No pending override to install'
            ];
        }
        
        // Load both files as strings
        $existingFileContent = file($existingFile);
        $pendingFileContent = file($pendingFile);
        
        // Extract class name from existing file
        $existingClassName = $this->extractClassName(file_get_contents($existingFile));
        $pendingClassName = $this->extractClassName(file_get_contents($pendingFile));
        
        if (!$existingClassName || !$pendingClassName) {
            return [
                'status' => 'conflict',
                'message' => 'Unable to determine class names',
                'file' => $relativePath
            ];
        }
        
        // Create unique class names to avoid redeclaration
        $uniqueId = uniqid();
        $existingTempClassName = $existingClassName . 'Original' . $uniqueId;
        $pendingTempClassName = $pendingClassName . 'Override' . $uniqueId;
        
        try {
            // Evaluate the existing override file with a temporary class name
            eval(
                preg_replace(
                    ['#^\s*<\?(?:php)?#', '#class\s+' . $existingClassName . '\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'],
                    [' ', 'class ' . $existingTempClassName . ' extends \stdClass'],
                    implode('', $existingFileContent)
                )
            );
            
            // Evaluate the pending override file with a temporary class name
            eval(
                preg_replace(
                    ['#^\s*<\?(?:php)?#', '#class\s+' . $pendingClassName . '(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'],
                    [' ', 'class ' . $pendingTempClassName . ' extends \stdClass'],
                    implode('', $pendingFileContent)
                )
            );
            
            // Create reflection classes
            $existingReflection = new \ReflectionClass($existingTempClassName);
            $pendingReflection = new \ReflectionClass($pendingTempClassName);
            
            $conflictingMethods = [];
            
            // Check if any method in the pending override already exists in the existing override
            foreach ($pendingReflection->getMethods() as $method) {
                // Skip constructor and magic methods
                if (in_array($method->getName(), ['__construct', '__destruct', '__call', '__get', '__set', '__isset', '__unset', '__toString', '__invoke', '__clone'])) {
                    continue;
                }
                
                if ($existingReflection->hasMethod($method->getName())) {
                    $conflictingMethods[] = $method->getName();
                }
            }
            
            if (!empty($conflictingMethods)) {
                return [
                    'status' => 'conflict',
                    'message' => 'Method conflicts detected',
                    'file' => $relativePath,
                    'class' => $existingClassName,
                    'methods' => $conflictingMethods
                ];
            }
            
            return [
                'status' => 'success',
                'message' => 'No conflicts detected',
                'file' => $relativePath
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error analyzing override files: ' . $e->getMessage(),
                'file' => $relativePath
            ];
        }
    }

    /**
     * Extract class name from PHP code
     * 
     * @param string $code PHP code
     * @return string|null Class name or null if not found
     */
    protected function extractClassName($code)
    {
        if (preg_match('/class\s+([a-zA-Z0-9_]+)(?:\s+extends|\s+implements|\s*\{)/i', $code, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * Get the module marker
     * 
     * @return string
     */
    public function getModuleMarker()
    {
        return $this->moduleMarker;
    }
    
    /**
     * Get list of all pending overrides
     * 
     * @return array List of relative paths to pending overrides
     */
    public function getPendingOverrides()
    {
        $overrides = [];

        foreach ($this->overridesToCheck as $relativePath) {
            $fullPath = $this->pendingOverridesDir . $relativePath;
            if (file_exists($fullPath)) {
                $overrides[] = $relativePath;
            }
        }

        return $overrides;
    }
    
    /**
     * Install a single override file
     * 
     * @param string $relativePath Relative path to the override file
     * @return bool True if installation was successful
     */
    protected function installSingleOverride($relativePath)
    {
        $sourceFile = $this->pendingOverridesDir . $relativePath;
        $targetFile = $this->psOverridesDir . $relativePath;
        
        // Create directory if it doesn't exist
        $targetDir = dirname($targetFile);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Add our marker to the file content if it doesn't already have it
        $content = file_get_contents($sourceFile);
        if (strpos($content, $this->moduleMarker) === false) {
            $content = $this->moduleMarker . "\n" . $content;
        }
        
        // Write the file
        return file_put_contents($targetFile, $content) !== false;
    }
}
