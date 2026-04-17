<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }
class MM_Config
{
    public $fields_form = [];
    public function l($string, $fileName="")
    {
        return Translate::getModuleTranslation('ets_megamenu', $string, $fileName ?: pathinfo(__FILE__, PATHINFO_FILENAME));
    }
    protected static $formFields;

    /**
     * @return array
     */
    private function getMenuAlignment()
    {
        return [
            'options' => [
                [
                    'id_option' => 'left',
                    'name' => $this->l('Left')
                ],
                [
                    'id_option' => 'center',
                    'name' => $this->l('Center')
                ],
                [
                    'id_option' => 'right',
                    'name' => $this->l('Right')
                ],
            ],
            'default' => 'left',
        ];
    }

    public function getFormField()
    {
        $opacityOptions = [
            'query' => array(
                ['id_option' => 0, 'name' => $this->l('Transparent')],
                ['id_option' => 0.025, 'name' => '2.5%'],
                ['id_option' => 0.05, 'name' => '5%'],
                ['id_option' => 0.075, 'name' => '7.5%'],
                ['id_option' => 0.1, 'name' => '10%'],
                ['id_option' => 0.125, 'name' => '12.5%'],
                ['id_option' => 0.15, 'name' => '15%'],
                ['id_option' => 0.175, 'name' => '17.5%'],
                ['id_option' => 0.2, 'name' => '20%'],
                ['id_option' => 0.225, 'name' => '22.5%'],
                ['id_option' => 0.25, 'name' => '25%'],
                ['id_option' => 0.275, 'name' => '27.5%'],
                ['id_option' => 0.3, 'name' => '30%'],
                ['id_option' => 0.325, 'name' => '32.5%'],
                ['id_option' => 0.35, 'name' => '35%'],
                ['id_option' => 0.375, 'name' => '37.5%'],
                ['id_option' => 0.4, 'name' => '40%'],
                ['id_option' => 0.425, 'name' => '42.5%'],
                ['id_option' => 0.45, 'name' => '45%'],
                ['id_option' => 0.475, 'name' => '47.5%'],
                ['id_option' => 0.5, 'name' => '50%'],
                ['id_option' => 0.525, 'name' => '52.5%'],
                ['id_option' => 0.55, 'name' => '55%'],
                ['id_option' => 0.575, 'name' => '57.5%'],
                ['id_option' => 0.6, 'name' => '60%'],
                ['id_option' => 0.625, 'name' => '62.5%'],
                ['id_option' => 0.65, 'name' => '65%'],
                ['id_option' => 0.675, 'name' => '67.5%'],
                ['id_option' => 0.7, 'name' => '70%'],
                ['id_option' => 0.725, 'name' => '72.5%'],
                ['id_option' => 0.75, 'name' => '75%'],
                ['id_option' => 0.775, 'name' => '77.5%'],
                ['id_option' => 0.8, 'name' => '80%'],
                ['id_option' => 0.825, 'name' => '82.5%'],
                ['id_option' => 0.85, 'name' => '85%'],
                ['id_option' => 0.875, 'name' => '87.5%'],
                ['id_option' => 0.9, 'name' => '90%'],
                ['id_option' => 0.925, 'name' => '92.5%'],
                ['id_option' => 0.95, 'name' => '95%'],
                ['id_option' => 0.975, 'name' => '97.5%'],
                ['id_option' => 1, 'name' => '100%']
            ),
            'id' => 'id_option',
            'name' => 'name',
        ];
        if(!self::$formFields)
        {
            /** @var Ets_megamenu $megamenu */
            $megamenu =Module::getInstanceByName('ets_megamenu');
            $imageTypes = $megamenu->imageTypes(true);
            $menuAlignment = $this->getMenuAlignment();
            self::$formFields = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Configuration'),
                        'icon' => 'icon-AdminAdmin'
                    ),
                    'input' => array(),
                    'submit' => array(
                        'title' => $this->l('Save'),
                    ),
                    'name' => 'config'
                ),
                'configs' => array(
                    'ETS_MM_HOOK_TO' => array(
                        'type' => 'select',
                        'label' => $this->l('Hook to'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_option' => 'default',
                                    'name' => $this->l('Default hook')
                                ),
                                array(
                                    'id_option' => 'customhook',
                                    'name' => $this->l('Custom hook')
                                ),
                            ),
                            'id' => 'id_option',
                            'name' => 'name',
                        ),
                        'default' => 'default',
                        'desc' => $this->l('Put {hook h=\'displayMegaMenu\'} on tpl file where you want to display the mega menu'),
                    ),
                    'ETS_MM_TRANSITION_EFFECT' => array(
                        'type' => 'select',
                        'label' => $this->l('Submenu transition effect'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_option' => 'fade',
                                    'name' => $this->l('Default')
                                ),
                                array(
                                    'id_option' => 'slide',
                                    'name' => $this->l('Slide down')
                                ),
                                array(
                                    'id_option' => 'scale_down',
                                    'name' => $this->l('Scale down')
                                ),
                                array(
                                    'id_option' => 'fadeInUp',
                                    'name' => $this->l('Fade in up')
                                ),
                                array(
                                    'id_option' => 'zoom',
                                    'name' => $this->l('Zoom In')
                                )
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                        'default' => 'fade',
                    ),
                    'ETS_MM_DIR' => array(
                        'type' => 'select',
                        'label' => $this->l('Language direction mode'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_option' => 'auto',
                                    'name' => $this->l('Auto detect LTR or RTL')
                                ),
                                array(
                                    'id_option' => 'ltr',
                                    'name' => $this->l('LTR')
                                ),
                                array(
                                    'id_option' => 'rtl',
                                    'name' => $this->l('RTL')
                                ),
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                        'default' => 'auto',
                    ),
                    'ETS_MOBILE_MM_TYPE' => array(
                        'type' => 'select',
                        'label' => $this->l('Mobile menu type'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_option' => 'floating',
                                    'name' => $this->l('Floating')
                                ),
                                array(
                                    'id_option' => 'default',
                                    'name' => $this->l('Bottom')
                                ),
                                array(
                                    'id_option' => 'full',
                                    'name' => $this->l('Full screen')
                                ),
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                        'default' => 'floating',
                    ),
                    'ETS_MM_IMAGE_TYPE' => array(
                        'type' => 'select',
                        'label' => $this->l('Thumbnail image type'),
                        'options' => array(
                            'query' => $imageTypes[0],
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                        'default' => $imageTypes[1],
                    ),
                    'ETS_MM_MENU_ALIGNMENT' => array(
                        'type' => 'select',
                        'label' => $this->l('Menu alignment'),
                        'options' => array(
                            'query' => $menuAlignment['options'],
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                        'default' => $menuAlignment['default'],
                    ),
                    'ETS_MM_DISPLAY_SUBMENU_BY_CLICK' => array(
                        'type' => 'switch',
                        'label' => $this->l('Display submenu by clicking'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'default' => 0,
                    ),
                    'ETS_MM_INCLUDE_SUB_CATEGORIES' => array(
                        'type' => 'switch',
                        'label' => $this->l('Include sub-categories'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'default' => 1,
                    ),
                    'ETS_MM_STICKY_ENABLED' => array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Sticky menu'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'default' => 1,
                    ),
                    'ETS_MM_STICKY_DISMOBILE' => array(
                        'type' => 'switch',
                        'label' => $this->l('Hide sticky menu on mobile'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'default' => 1,
                    ),
                    'ETS_MM_CLICK_TEXT_SHOW_SUB' => array(
                        'type' => 'switch',
                        'label' => $this->l('Click on menu text to open its submenu'),
                        'is_bool' => true,
                        'desc' => $this->l('Apply for mobile only'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'default' => 0,
                    ),
                    'ETS_MM_SHOW_ICON_VERTICAL' => array(
                        'type' => 'switch',
                        'label' => $this->l('Show vertical menu icon on mobile'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'default' => 1,
                    ),
                    'ETS_MM_ACTIVE_ENABLED' => array(
                        'type' => 'switch',
                        'label' => $this->l('Highlight the activated menu item'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'default' => 0,
                    ),
                    'ETS_MM_ACTIVE_BG_GRAY' => array(
                        'type' => 'switch',
                        'label' => $this->l('Enable grey overlay effect for submenu'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'default' => 1,
                    ),
                    'ETS_MM_CLEAR_CACHE_SPEED' => array(
                        'type' => 'switch',
                        'label' => $this->l('Automatically clear the cache when modifying the mega menu'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('When enabled, this feature works in conjunction with the \'Super Speed\' module. After modifying and saving the contents of your mega menu, the entire site\'s cache will be automatically cleared, ensuring an instant display of the updated menu data on the front office.'),
                        'default' => 1,
                    ),
                    'ETS_MM_CACHE_ENABLED' => array(
                        'type' => 'switch',
                        'label' => $this->l('Enable cache'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'default' => 0,
                        'desc' => $this->l('The module uses PrestaShop Smarty Cache, so please make sure that PrestaShop Smarty Cache is enabled to use this feature'),
                    ),
                    'ETS_MM_CACHE_LIFE_TIME' => array(
                        'type' => 'text',
                        'label' => $this->l('Cache lifetime'),
                        'default' => 24,
                        'suffix' => $this->l('Hours'),
                        'validate' => 'isUnsignedInt',
                    ),
                    'ETS_MM_LAYOUT' => array(
                        'type' => 'select',
                        'label' => $this->l('Layout type'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_option' => 'layout1',
                                    'name' => $this->l('Layout 1')
                                ),
                                array(
                                    'id_option' => 'layout2',
                                    'name' => $this->l('Layout 2')
                                ),
                                array(
                                    'id_option' => 'layout3',
                                    'name' => $this->l('Layout 3')
                                ),
                                array(
                                    'id_option' => 'layout4',
                                    'name' => $this->l('Layout 4')
                                ),
                                array(
                                    'id_option' => 'layout5',
                                    'name' => $this->l('Layout 5')
                                ),
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                        'default' => 'layout1',
                    ),
                    'ETS_MM_HEADING_FONT' => array(
                        'label' => $this->l('Heading font'),
                        'type' => 'select',
                        'options' => array(
                            'query' => $this->getGoogleFonts(),
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                        'desc' => $this->l('Use default font of your theme or select a Google font from the list'),
                        'default' => 'inherit',
                    ),
                    'ETS_MM_HEADING_FONT_SIZE' => array(
                        'label' => $this->l('Heading font size'),
                        'type' => 'text',
                        'default' => '16',
                        'suffix' => 'px',
                        'class' => 'col-lg-3',
                        'validate' => 'isUnsignedInt'
                    ),
                    'ETS_MM_TEXT_FONT' => array(
                        'label' => $this->l('General text font'),
                        'type' => 'select',
                        'options' => array(
                            'query' => $this->getGoogleFonts(),
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                        'desc' => $this->l('Use default font of your theme or select a Google font from the list'),
                        'default' => 'inherit',
                    ),
                    'ETS_MM_TEXTTITLE_FONT_SIZE' => array(
                        'label' => $this->l('Submenu title font size'),
                        'type' => 'text',
                        'default' => '16',
                        'suffix' => 'px',
                        'class' => 'col-lg-3',
                        'validate' => 'isUnsignedInt'
                    ),
                    'ETS_MM_TEXT_FONT_SIZE' => array(
                        'label' => $this->l('General text font size'),
                        'type' => 'text',
                        'default' => '14',
                        'suffix' => 'px',
                        'class' => 'col-lg-3',
                        'validate' => 'isUnsignedInt'
                    ),
                    'ETS_MM_COLOR1' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu background color'),
                        'validate' => 'isColor',
                        'default' => '',
                        'form_group_class' => 'custom_color layout1'
                    ),
                    'ETS_MM_COLOR2' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu text color'),
                        'validate' => 'isColor',
                        'default' => '#484848',
                        'form_group_class' => 'custom_color layout1'
                    ),
                    'ETS_MM_COLOR3' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu text hover color'),
                        'validate' => 'isColor',
                        'default' => '#ec4249',
                        'form_group_class' => 'custom_color layout1'
                    ),
                    'ETS_MM_COLOR4' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu background hover color'),
                        'validate' => 'isColor',
                        'default' => '#ffffff',
                        'form_group_class' => 'custom_color layout1'
                    ),
                    'ETS_MM_COLOR5' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu background color'),
                        'validate' => 'isColor',
                        'default' => '#ffffff',
                        'form_group_class' => 'custom_color layout1'
                    ),
                    'ETS_MM_COLOR_BACKDROP_1' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu overlay color'),
                        'validate' => 'isColor',
                        'default' => '#000000',
                        'form_group_class' => 'custom_color layout1'
                    ),
                    'ETS_MM_OPACITY_BACKDROP_1' => array(
                        'type' => 'select',
                        'label' => $this->l('Sub-menu overlay opacity'),
                        'options' => $opacityOptions,
                        'default' => 0.5,
                        'form_group_class' => 'custom_color layout1'
                    ),
                    'ETS_MM_COLOR_36' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu title color'),
                        'validate' => 'isColor',
                        'default' => '#414141',
                        'form_group_class' => 'custom_color layout1'
                    ),
                    'ETS_MM_COLOR6' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu text color'),
                        'validate' => 'isColor',
                        'default' => '#414141',
                        'form_group_class' => 'custom_color layout1'
                    ),
                    'ETS_MM_COLOR7' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu link hover color'),
                        'validate' => 'isColor',
                        'default' => '#ec4249',
                        'form_group_class' => 'custom_color layout1'
                    ),

                    'ETS_MM_COLOR8' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu background color'),
                        'validate' => 'isColor',
                        'default' => '#3cabdb',
                        'form_group_class' => 'custom_color layout2'
                    ),
                    'ETS_MM_COLOR9' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu text color'),
                        'validate' => 'isColor',
                        'default' => '#ffffff',
                        'form_group_class' => 'custom_color layout2'
                    ),
                    'ETS_MM_COLOR_10' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu text hover color'),
                        'validate' => 'isColor',
                        'default' => '#ffffff',
                        'form_group_class' => 'custom_color layout2'
                    ),
                    'ETS_MM_COLOR_11' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu background hover color'),
                        'validate' => 'isColor',
                        'default' => '#50b4df',
                        'form_group_class' => 'custom_color layout2'
                    ),
                    'ETS_MM_COLOR_12' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu background color'),
                        'validate' => 'isColor',
                        'default' => '#ffffff',
                        'form_group_class' => 'custom_color layout2'
                    ),
                    'ETS_MM_COLOR_BACKDROP_2' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu overlay color'),
                        'validate' => 'isColor',
                        'default' => '#000000',
                        'form_group_class' => 'custom_color layout2'
                    ),
                    'ETS_MM_OPACITY_BACKDROP_2' => array(
                        'type' => 'select',
                        'label' => $this->l('Sub-menu overlay opacity'),
                        'options' => $opacityOptions,
                        'default' => 0.5,
                        'form_group_class' => 'custom_color layout2'
                    ),
                    'ETS_MM_COLOR_37' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu title color'),
                        'validate' => 'isColor',
                        'default' => '#414141',
                        'form_group_class' => 'custom_color layout2'
                    ),
                    'ETS_MM_COLOR_13' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu text color'),
                        'validate' => 'isColor',
                        'default' => '#666666',
                        'form_group_class' => 'custom_color layout2'
                    ),
                    'ETS_MM_COLOR_14' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu link hover color'),
                        'validate' => 'isColor',
                        'default' => '#fc4444',
                        'form_group_class' => 'custom_color layout2'
                    ),

                    'ETS_MM_COLOR_15' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu background color'),
                        'validate' => 'isColor',
                        'default' => '#333333',
                        'form_group_class' => 'custom_color layout3'
                    ),
                    'ETS_MM_COLOR_16' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu text color'),
                        'validate' => 'isColor',
                        'default' => '#ffffff',
                        'form_group_class' => 'custom_color layout3'
                    ),
                    'ETS_MM_COLOR_17' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu text hover color'),
                        'validate' => 'isColor',
                        'default' => '#ffffff',
                        'form_group_class' => 'custom_color layout3'
                    ),
                    'ETS_MM_COLOR_18' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu background hover color'),
                        'validate' => 'isColor',
                        'default' => '#000000',
                        'form_group_class' => 'custom_color layout3'
                    ),
                    'ETS_MM_COLOR_19' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu background color'),
                        'validate' => 'isColor',
                        'default' => '#000000',
                        'form_group_class' => 'custom_color layout3'
                    ),
                    'ETS_MM_COLOR_BACKDROP_3' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu overlay color'),
                        'validate' => 'isColor',
                        'default' => '#000000',
                        'form_group_class' => 'custom_color layout3'
                    ),
                    'ETS_MM_OPACITY_BACKDROP_3' => array(
                        'type' => 'select',
                        'label' => $this->l('Sub-menu overlay opacity'),
                        'options' => $opacityOptions,
                        'default' => 0.5,
                        'form_group_class' => 'custom_color layout3'
                    ),
                    'ETS_MM_COLOR_38' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu title color'),
                        'validate' => 'isColor',
                        'default' => '#ec4249',
                        'form_group_class' => 'custom_color layout3'
                    ),
                    'ETS_MM_COLOR_20' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu text color'),
                        'validate' => 'isColor',
                        'default' => '#dcdcdc',
                        'form_group_class' => 'custom_color layout3'
                    ),
                    'ETS_MM_COLOR_21' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu link hover color'),
                        'validate' => 'isColor',
                        'default' => '#fc4444',
                        'form_group_class' => 'custom_color layout3'
                    ),

                    'ETS_MM_COLOR_22' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu background color'),
                        'validate' => 'isColor',
                        'default' => '#ffffff',
                        'form_group_class' => 'custom_color layout4'
                    ),
                    'ETS_MM_COLOR_23' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu text color'),
                        'validate' => 'isColor',
                        'default' => '#333333',
                        'form_group_class' => 'custom_color layout4'
                    ),
                    'ETS_MM_COLOR_24' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu text hover color'),
                        'validate' => 'isColor',
                        'default' => '#ffffff',
                        'form_group_class' => 'custom_color layout4'
                    ),
                    'ETS_MM_COLOR_25' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu background hover color'),
                        'validate' => 'isColor',
                        'default' => '#ec4249',
                        'form_group_class' => 'custom_color layout4'
                    ),
                    'ETS_MM_COLOR_26' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu background color'),
                        'validate' => 'isColor',
                        'default' => '#ffffff',
                        'form_group_class' => 'custom_color layout4'
                    ),
                    'ETS_MM_COLOR_BACKDROP_4' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu overlay color'),
                        'validate' => 'isColor',
                        'default' => '#000000',
                        'form_group_class' => 'custom_color layout4'
                    ),
                    'ETS_MM_OPACITY_BACKDROP_4' => array(
                        'type' => 'select',
                        'label' => $this->l('Sub-menu overlay opacity'),
                        'options' => $opacityOptions,
                        'default' => 0.5,
                        'form_group_class' => 'custom_color layout4'
                    ),
                    'ETS_MM_COLOR_39' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu title color'),
                        'validate' => 'isColor',
                        'default' => '#414141',
                        'form_group_class' => 'custom_color layout4'
                    ),
                    'ETS_MM_COLOR_27' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu text color'),
                        'validate' => 'isColor',
                        'default' => '#666666',
                        'form_group_class' => 'custom_color layout4'
                    ),
                    'ETS_MM_COLOR_28' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu link hover color'),
                        'validate' => 'isColor',
                        'default' => '#ec4249',
                        'form_group_class' => 'custom_color layout4'
                    ),

                    'ETS_MM_COLOR_29' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu background color'),
                        'validate' => 'isColor',
                        'default' => '#f6f6f6',
                        'form_group_class' => 'custom_color layout5'
                    ),
                    'ETS_MM_COLOR_30' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu text color'),
                        'validate' => 'isColor',
                        'default' => '#333333',
                        'form_group_class' => 'custom_color layout5'
                    ),
                    'ETS_MM_COLOR_31' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu text hover color'),
                        'validate' => 'isColor',
                        'default' => '#ec4249',
                        'form_group_class' => 'custom_color layout5'
                    ),
                    'ETS_MM_COLOR_32' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu background hover color'),
                        'validate' => 'isColor',
                        'default' => '',
                        'form_group_class' => 'custom_color layout5'
                    ),
                    'ETS_MM_COLOR_33' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu background color'),
                        'validate' => 'isColor',
                        'default' => '#ffffff',
                        'form_group_class' => 'custom_color layout5'
                    ),
                    'ETS_MM_COLOR_BACKDROP_5' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu overlay color'),
                        'validate' => 'isColor',
                        'default' => '#000000',
                        'form_group_class' => 'custom_color layout5'
                    ),
                    'ETS_MM_OPACITY_BACKDROP_5' => array(
                        'type' => 'select',
                        'label' => $this->l('Sub-menu overlay opacity'),
                        'options' => $opacityOptions,
                        'default' => 0.5,
                        'form_group_class' => 'custom_color layout5'
                    ),
                    'ETS_MM_COLOR_40' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu title color'),
                        'validate' => 'isColor',
                        'default' => '#414141',
                        'form_group_class' => 'custom_color layout5'
                    ),
                    'ETS_MM_COLOR_34' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu text color'),
                        'validate' => 'isColor',
                        'default' => '#333333',
                        'form_group_class' => 'custom_color layout5'
                    ),
                    'ETS_MM_COLOR_35' => array(
                        'type' => 'color',
                        'label' => $this->l('Sub-menu link hover color'),
                        'validate' => 'isColor',
                        'default' => '#ec4249',
                        'form_group_class' => 'custom_color layout5'
                    ),
                    'ETS_MM_MOBILE_BG_BAR' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu bar background on mobile color'),
                        'validate' => 'isColor',
                        'default' => '#000000',
                    ),
                    'ETS_MM_MOBILE_COLOR_BAR' => array(
                        'type' => 'color',
                        'label' => $this->l('Menu bar color on mobile color'),
                        'validate' => 'isColor',
                        'default' => '#ffffff',
                    ),
                    'ETS_MM_CUSTOM_CLASS' => array(
                        'type' => 'text',
                        'label' => $this->l('Custom class'),
                    ),
                    'ETS_MM_DISPLAY_SHOPPING_CART' => array(
                        'type' => 'switch',
                        'label' => $this->l('Display shopping cart'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Not recommended! For advanced users only. Menu can be broken when this option is enabled (especially on custom theme). You are required to have HTML/CSS knowledge to refine the issues. Please understand this is out of free support as it depends on your theme'),
                        'default' => 0,
                    ),
                    'ETS_MM_DISPLAY_SEARCH' => array(
                        'type' => 'switch',
                        'label' => $this->l('Display search box'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Not recommended! For default search module and advanced users only. Menu can be broken when this option is enabled (especially on custom theme). You are required to have HTML/CSS knowledge to refine the issues. Please understand this is out of free support as it depends on your theme'),
                        'default' => 0,
                    ),
                    'ETS_MM_SEARCH_DISPLAY_DEFAULT' => array(
                        'type' => 'switch',
                        'label' => $this->l('Open search box by default'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'default' => 0,
                        'form_group_class' => 'mm_form_display_search'
                    ),
                    'ETS_MM_DISPLAY_CUSTOMER_INFO' => array(
                        'type' => 'switch',
                        'label' => $this->l('Display user info links'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'desc' => $this->l('Not recommended! For advanced users only. Menu can be broken when this option is enabled (especially on custom theme). You are required to have HTML/CSS knowledge to refine the issues. Please understand this is out of free support as it depends on your theme'),
                        'default' => 0,
                    ),
                    'ETS_MM_CUSTOM_HTML_TEXT' => array(
                        'type' => 'textarea',
                        'label' => $this->l('Custom HTML text'),
                        'default' => '',
                        'lang' => true,
                    )

                )
            );
            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                if (!Module::getInstanceByName('ps_shoppingcart') || !Module::isEnabled('ps_shoppingcart'))
                    unset(self::$formFields['configs']['ETS_MM_DISPLAY_SHOPPING_CART']);
                if (!Module::getInstanceByName('ps_searchbar') || !Module::isEnabled('ps_searchbar')) {
                    unset(self::$formFields['configs']['ETS_MM_DISPLAY_SEARCH']);
                    unset(self::$formFields['configs']['ETS_MM_SEARCH_DISPLAY_DEFAULT']);
                }
                if (!Module::getInstanceByName('ps_customersignin') || !Module::isEnabled('ps_customersignin'))
                    unset(self::$formFields['configs']['ETS_MM_DISPLAY_CUSTOMER_INFO']);
            } else {
                if (!Module::getInstanceByName('blockcart') || !Module::isEnabled('blockcart'))
                    unset(self::$formFields['configs']['ETS_MM_DISPLAY_SHOPPING_CART']);
                if (!Module::getInstanceByName('blocksearch') || !Module::isEnabled('blocksearch')) {
                    unset(self::$formFields['configs']['ETS_MM_DISPLAY_SEARCH']);
                    unset(self::$formFields['configs']['ETS_MM_SEARCH_DISPLAY_DEFAULT']);
                }
                if (!Module::getInstanceByName('blockuserinfo') || !Module::isEnabled('blockuserinfo'))
                    unset(self::$formFields['configs']['ETS_MM_DISPLAY_CUSTOMER_INFO']);
            }
            if((!Module::getInstanceByName('ets_superspeed') || !Module::isEnabled('ets_superspeed')) && (!Module::getInstanceByName('ets_pagecache') || !Module::isEnabled('ets_pagecache')) )
            {
                unset(self::$formFields['configs']['ETS_MM_CLEAR_CACHE_SPEED']);
            }
        }
        return self::$formFields;
    }
    public function getGoogleFonts()
    {
        $googlefonts = json_decode(Tools::file_get_contents(dirname(__FILE__) . '/../data/google-fonts.json'), true);
        if (!$googlefonts) {
            $googlefonts = array(
                array(
                    'id_option' => 'inherit',
                    'name' => $this->l('THEME DEFAULT FONT'),
                ),
                array(
                    'id_option' => 'Arial',
                    'name' => 'Arial',
                ),
                array(
                    'id_option' => 'Times new roman',
                    'name' => 'Times new roman',
                ),
            );
        }
        return $googlefonts;
    }
    public function renderForm($context)
    {
        $formFields = $this->getFormField();
        $helper = new HelperForm();
        $helper->module = new Ets_megamenu();
        $configs = $formFields['configs'];
        $fields_form = array();
        $fields_form['form'] = $formFields['form'];
        if($configs)
        {
            foreach($configs as $key => $config)
            {
                $confFields = array(
                    'name' => $key,
                    'type' => $config['type'],
                    'label' => $config['label'],
                    'desc' => isset($config['desc']) ? $config['desc'] : false,
                    'required' => isset($config['required']) && $config['required'] ? true : false,
                    'autoload_rte' => isset($config['autoload_rte']) && $config['autoload_rte'] ? true : false,
                    'options' => isset($config['options']) && $config['options'] ? $config['options'] : array(),
                    'suffix' => isset($config['suffix']) && $config['suffix'] ? $config['suffix']  : false,
                    'values' => isset($config['values']) ? $config['values'] : false,
                    'lang' => isset($config['lang']) ? $config['lang'] : false,
                    'class' => isset($config['class']) ? $config['class'] : '',
                    'form_group_class' => isset($config['form_group_class']) ? $config['form_group_class'] : '',
                    'hide_delete' => isset($config['hide_delete']) ? $config['hide_delete'] : false,
                    'display_img' => isset($config['type']) && $config['type']=='file' && Configuration::get($key)!='' && @file_exists(_PS_ETS_MM_IMG_DIR_.Configuration::get($key)) ? _PS_ETS_MM_IMG_.Configuration::get($key) : false,
                    'img_del_link' => isset($config['type']) && $config['type']=='file' && Configuration::get($key)!='' && @file_exists(_PS_ETS_MM_IMG_DIR_.Configuration::get($key)) ? $helper->module->baseAdminUrl().'&deleteimage='.$key.'&itemId=0&mm_object=MM_'.Tools::ucfirst($fields_form['form']['name']) : false,                     
                );
                if(isset($config['tree']) && $config['tree'])
                {
                    $confFields['tree'] = $config['tree'];
                    if(isset($config['tree']['use_checkbox']) && $config['tree']['use_checkbox'])
                        $confFields['tree']['selected_categories'] = explode(',',Configuration::get($key));
                    else
                        $confFields['tree']['selected_categories'] = array(Configuration::get($key));
                }                    
                if(!$confFields['suffix'])
                    unset($confFields['suffix']);
                $fields_form['form']['input'][] = $confFields;
            }
        }
		$helper->show_toolbar = false;
		$helper->table = false;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();		
		$helper->identifier = 'mm_form_'.$formFields['form']['name'];
		$helper->submit_action = 'save_'.$formFields['form']['name'];
        $link = new Link();
		$helper->currentIndex = $link->getAdminLink('AdminModules', true).'&configure=ets_megamenu';
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $fields = array();        
        $languages = Language::getLanguages(false);
        $helper->override_folder = '/';
        if($configs)
        {
            foreach($configs as $key => $config)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    foreach($languages as $l)
                    {
                        $fields[$key][$l['id_lang']] = Configuration::get($key,$l['id_lang']);
                    }
                }
                else
                    $fields[$key] = Configuration::get($key);
            }
        }
        $helper->tpl_vars = array(
			'base_url' => $context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
			'fields_value' => $fields,
			'languages' => $context->controller->getLanguages(),
			'id_language' => $context->language->id,
            'mm_object' => 'MM_'.Tools::ucfirst($fields_form['form']['name']),
            'image_baseurl' => _PS_ETS_MM_IMG_,
            'image_module_baseurl' => $helper->module->modulePath().'views/img/',
            'mm_clear_cache_url' => $helper->module->baseAdminUrl(),
            'reset_default' => true,                
        );        
        return str_replace(array('id="ets_mm_menu_form"','id="fieldset_0"'),'',$helper->generateForm(array($fields_form)));	
    }
    protected static $configs;
    public function getConfig($id_lang)
    {
        if(!self::$configs)
        {
            $fields = $this->getFormField();
            $configs = $fields['configs'];
            $data = array();
            if($configs)
            {
                foreach($configs as $key => $config)
                {
                    $data[$key] = isset($config['lang']) && $config['lang'] ? Configuration::get($key,$id_lang) : Configuration::get($key);
                }
            }
            self::$configs = $data;
        }
        return self::$configs;
    }
    public function installConfigs($upgrade = false)
    {
        $fields = $this->getFormField();
        $configs = $fields['configs'];
        $languages = Language::getLanguages(false);
        if($configs)
        {
            foreach($configs as $key => $config)
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    if ($upgrade &&  !Configuration::hasKey($key) || !$upgrade)
                    {
                        Configuration::updateGlobalValue($key, $values, true);
                    }
                }
                elseif ($upgrade &&  !Configuration::hasKey($key) || !$upgrade)
                {
                    Configuration::updateGlobalValue($key, isset($config['default']) ? $config['default'] : '',true);
                }
            }
        }
        Configuration::updateGlobalValue('ETS_MM_CLEAR_CACHE_SPEED',1);
        return true;
    }
    public function unInstallConfigs()
    {
        $fields = $this->getFormField();
        if($fields['configs'])
        {
            foreach($fields['configs'] as $key => $config)
            {
                Configuration::deleteByName($key);
                unset($config);
            }
        } 
    }    
}