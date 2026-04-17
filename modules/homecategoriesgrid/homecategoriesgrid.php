<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class HomeCategoriesGrid extends Module
{
    const CONFIG_CAT_ID_PREFIX  = 'HCG_CATEGORY_%d_ID';
    const CONFIG_CAT_IMG_PREFIX = 'HCG_CATEGORY_%d_IMG';

    public function __construct()
    {
        $this->name = 'homecategoriesgrid';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'ChatGPT';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Home Categories Grid');
        $this->description = $this->l('Displays 6 categories with custom images on the home page.');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        // Create uploads directory
        if (!is_dir($this->getUploadDir())) {
            if (!@mkdir($this->getUploadDir(), 0755, true)) {
                return false;
            }
        }

        if (!is_writable($this->getUploadDir())) {
            @chmod($this->getUploadDir(), 0755);
        }

        // Default config (empty)
        for ($i = 1; $i <= 6; $i++) {
            if (!Configuration::updateValue(sprintf(self::CONFIG_CAT_ID_PREFIX, $i), 0)) {
                return false;
            }
            if (!Configuration::updateValue(sprintf(self::CONFIG_CAT_IMG_PREFIX, $i), '')) {
                return false;
            }
        }

        return
            $this->registerHook('displayHome') &&
            $this->registerHook('header');
    }

    public function uninstall()
    {
        // Remove config values and uploaded images
        for ($i = 1; $i <= 6; $i++) {
            $idKey = sprintf(self::CONFIG_CAT_ID_PREFIX, $i);
            $imgKey = sprintf(self::CONFIG_CAT_IMG_PREFIX, $i);

            Configuration::deleteByName($idKey);

            $imgName = Configuration::get($imgKey);
            if ($imgName && file_exists($this->getUploadDir() . $imgName)) {
                @unlink($this->getUploadDir() . $imgName);
            }
            Configuration::deleteByName($imgKey);
        }

        return parent::uninstall();
    }

    /**
     * Path on disk to the uploads folder
     */
    protected function getUploadDir()
    {
        return _PS_MODULE_DIR_ . $this->name . '/uploads/';
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitHomeCategoriesGrid')) {
            $output .= $this->processForm();
        }

        $output .= $this->renderForm();

        return $output;
    }

    protected function processForm()
    {
        $output = '';

        // Ensure upload directory exists and is writable
        if (!is_dir($this->getUploadDir())) {
            @mkdir($this->getUploadDir(), 0755, true);
        }

        if (!is_writable($this->getUploadDir())) {
            return $this->displayError(
                sprintf(
                    $this->l('Upload directory %s is not writable. Please fix permissions.'),
                    $this->getUploadDir()
                )
            );
        }

        for ($i = 1; $i <= 6; $i++) {
            // Save category ID
            $catIdKey = sprintf(self::CONFIG_CAT_ID_PREFIX, $i);
            $catIdVal = (int)Tools::getValue($catIdKey);
            Configuration::updateValue($catIdKey, $catIdVal);

            // Handle image upload
            $fileInputName = sprintf(self::CONFIG_CAT_IMG_PREFIX, $i);

            if (
                isset($_FILES[$fileInputName]) &&
                isset($_FILES[$fileInputName]['tmp_name']) &&
                $_FILES[$fileInputName]['tmp_name'] !== ''
            ) {
                // Check PHP upload error first
                if ((int)$_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
                    $output .= $this->displayError(
                        sprintf(
                            $this->l('Upload error for image %d (error code: %s).'),
                            $i,
                            (int)$_FILES[$fileInputName]['error']
                        )
                    );
                    continue;
                }

                // Validate image and allowed extensions
                $error = ImageManager::validateUpload(
                    $_FILES[$fileInputName],
                    0,
                    array('jpg', 'jpeg', 'png', 'gif', 'webp')
                );

                if ($error) {
                    $output .= $this->displayError(
                        sprintf(
                            $this->l('Error with image %d: %s'),
                            $i,
                            $error
                        )
                    );
                    continue;
                }

                $ext = Tools::strtolower(pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'))) {
                    $output .= $this->displayError(
                        sprintf(
                            $this->l('Invalid image format for image %d. Allowed: jpg, jpeg, png, gif, webp.'),
                            $i
                        )
                    );
                    continue;
                }

                $newFileName = 'category_' . $i . '.' . $ext;
                $destination = $this->getUploadDir() . $newFileName;

                // Remove old image if exists and is different
                $oldName = Configuration::get($fileInputName);
                if ($oldName && $oldName !== $newFileName && file_exists($this->getUploadDir() . $oldName)) {
                    @unlink($this->getUploadDir() . $oldName);
                }

                if (!@move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $destination)) {
                    $output .= $this->displayError(
                        sprintf(
                            $this->l('Failed to upload image %d. Check write permissions for %s'),
                            $i,
                            $this->getUploadDir()
                        )
                    );
                    continue;
                }

                @chmod($destination, 0644);

                Configuration::updateValue($fileInputName, $newFileName);
            }
        }

        if ($output === '') {
            $output = $this->displayConfirmation($this->l('Settings updated.'));
        }

        return $output;
    }

    protected function renderForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Home Categories Grid settings'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        // 6 category ID + file fields
        for ($i = 1; $i <= 6; $i++) {
            $idKey = sprintf(self::CONFIG_CAT_ID_PREFIX, $i);
            $imgKey = sprintf(self::CONFIG_CAT_IMG_PREFIX, $i);

            $currentImg = Configuration::get($imgKey);
            $imgUrl = '';
            if ($currentImg) {
                $imgUrl = $this->_path . 'uploads/' . $currentImg;
            }

            $fields_form['form']['input'][] = array(
                'type'  => 'text',
                'label' => sprintf($this->l('Category ID %d'), $i),
                'name'  => $idKey,
                'class' => 'fixed-width-xs',
            );

            $desc = '';
            if ($imgUrl) {
                $desc = '<img src="' . $imgUrl . '" alt="" style="max-width:150px;display:block;margin-top:5px;" />';
            }

            $fields_form['form']['input'][] = array(
                'type'  => 'file',
                'label' => sprintf($this->l('Image %d'), $i),
                'name'  => $imgKey,
                'desc'  => $desc,
            );
        }

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->submit_action = 'submitHomeCategoriesGrid';

        $helper->fields_value = $this->getConfigFormValues();

        return $helper->generateForm(array($fields_form));
    }

    protected function getConfigFormValues()
    {
        $values = array();

        for ($i = 1; $i <= 6; $i++) {
            $idKey = sprintf(self::CONFIG_CAT_ID_PREFIX, $i);
            $imgKey = sprintf(self::CONFIG_CAT_IMG_PREFIX, $i);

            $values[$idKey] = Configuration::get($idKey);
            $values[$imgKey] = Configuration::get($imgKey);
        }

        return $values;
    }

    public function hookHeader($params)
    {
        // Front-office CSS
        $this->context->controller->registerStylesheet(
            'module-' . $this->name,
            'modules/' . $this->name . '/views/css/front.css',
            array(
                'media' => 'all',
                'priority' => 150,
            )
        );
    }

    public function hookDisplayHome($params)
    {
        $items = array();

        for ($i = 1; $i <= 6; $i++) {
            $idKey = sprintf(self::CONFIG_CAT_ID_PREFIX, $i);
            $imgKey = sprintf(self::CONFIG_CAT_IMG_PREFIX, $i);

            $id_category = (int)Configuration::get($idKey);
            $imgName = Configuration::get($imgKey);

            if ($id_category <= 0 || !$imgName) {
                continue;
            }

            $imgPath = $this->getUploadDir() . $imgName;
            if (!file_exists($imgPath)) {
                continue;
            }

            $link = $this->context->link->getCategoryLink($id_category);
            $name = 'Category ' . $id_category;

            try {
                $category = new Category($id_category, $this->context->language->id);
                if (Validate::isLoadedObject($category)) {
                    $name = $category->name;
                }
            } catch (Exception $e) {
                // silently ignore
            }

            $items[] = array(
                'id_category' => $id_category,
                'name'        => $name,
                'image'       => $this->_path . 'uploads/' . $imgName,
                'link'        => $link,
            );
        }

        $this->context->smarty->assign(array(
            'homecategoriesgrid_items' => $items,
        ));

        return $this->display(__FILE__, 'views/templates/hook/displayHome.tpl');
    }
}
