<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomCodeBlock extends Module
{
    const CONFIG_CONTENT = 'CUSTOMCODEBLOCK_CONTENT';

    public function __construct()
    {
        $this->name = 'customcodeblock';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Custom';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Custom Code Block');
        $this->description = $this->l('Shows custom HTML/CSS/JS on the home page.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHome')
            && Configuration::updateValue(self::CONFIG_CONTENT, '');
    }

    public function uninstall()
    {
        return Configuration::deleteByName(self::CONFIG_CONTENT)
            && parent::uninstall();
    }

    /**
     * Simple config page: one textarea.
     */
    public function getContent()
    {
        if (Tools::isSubmit('submitCustomCodeBlock')) {
            $content = Tools::getValue(self::CONFIG_CONTENT);
            Configuration::updateValue(self::CONFIG_CONTENT, $content);
            $this->context->controller->confirmations[] = $this->l('Settings saved.');
        }

        $content = Configuration::get(self::CONFIG_CONTENT);

        // Simple HTML form (no HelperForm, minimal).
        $html = '
        <form method="post" action="">
            <div class="panel">
                <div class="panel-heading">
                    '.$this->l('Custom Code').'
                </div>
                <div class="form-wrapper">
                    <div class="form-group">
                        <label>'.$this->l('HTML / CSS / JS (paste full code)').'</label>
                        <textarea name="'.self::CONFIG_CONTENT.'" rows="12" cols="80" class="textarea-large">'
                            .htmlspecialchars($content, ENT_QUOTES, 'UTF-8').'</textarea>
                        <p class="help-block">
                            '.$this->l('You can paste any HTML here, including <style> and <script> tags.').'
                        </p>
                    </div>
                </div>
                <div class="panel-footer">
                    <button type="submit" name="submitCustomCodeBlock" class="btn btn-default pull-right">
                        '.$this->l('Save').'
                    </button>
                    <div class="clearfix"></div>
                </div>
            </div>
        </form>';

        return $html;
    }

    /**
     * Hook into displayHome (front office).
     * If empty, output nothing.
     */
    public function hookDisplayHome($params)
    {
        $content = Configuration::get(self::CONFIG_CONTENT);

        // If nothing is set, DO NOT output anything.
        if (!trim($content)) {
            return '';
        }

        $this->context->smarty->assign([
            'custom_content' => $content,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/customcodeblock.tpl');
    }
}
