<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

if(!class_exists('ModelVideoPlayer'));
    require_once _PS_MODULE_DIR_.'videoPlayer/classes/ModelVideoPlayer.php';

class VideoPlayer extends Module
{
    protected $config_form = false;

    //Appelé à chaque réinitialisation du module
    public function __construct(Context $context = null)
    {
        //Sytème permettant de choisir quel vidéo on place à quel endroit dans le menu "Positions"
        $this->multiContent = true;
        $this->name = 'videoPlayer';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'ptipabo';
        //$this->need_instance = 0;

        //Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
        $this->bootstrap = true;

        parent::__construct();

        $this->newTab = array(
            'name' => array(
                'fr' => 'Vidéos produits',
                'en' => 'Product Videos'),
            'class_name' => 'AdminVideoPlayer',
            'parent' => 'AdminCatalog');

        $this->displayName = $this->l('Video Player');
        $this->description = $this->l('This module allows the user to add video clips to product pages (individually) to better promotes each product.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    //Don't forget to create update methods if needed:
    //http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
    
    //Permet d'installer le module via le Back Office
     public function install()
    {
        require _PS_MODULE_DIR_.'videoPlayer/sql/install.php';

        return parent::install() &&
            $this->installTab() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader');
    }

    //Permet de désinstaller le module via le Back Office
    public function uninstall()
    {
        require _PS_MODULE_DIR_.'videoPlayer/sql/uninstall.php';

        return $this->installTab(false) && parent::uninstall();
    }

    //Permet d'installer l'onglet dans le menu du Back Office
    public function installTab($install = true){
        //Si on souhaite installer l'onglet...
        if($install){
            //On crée un nouvel onglet
            $tab = new Tab();
            //On lui attibue un nom
            $tab->module = $this->name;
            //On lui donne une classe unique
            $tab->class_name = $this->newTab['class_name'];
            //On lui indique dans quel menu il va devoir être créé
            $tab->id_parent = Tab::getIdFromClassName($this->newTab['parent']);

            //On récupère toutes les langues de notre webshop
            $languages = Language::getLanguages();
            //On définit le nom de l'onglet dans toutes les langues du webshop
            foreach($languages as $lang){
                if(array_key_exists($lang['language_code'], $this->newTab['name'])){
                    $tab->name[$lang['id_lang']] = $this->newTab['name'][$lang['language_code']];
                }
                else{
                    $tab->name[$lang['id_lang']] = $this->newTab['name']['en'];
                }
            }
            //On enregistre ce nouvel onglet
            try {
                $tab->save();
            } catch (Exception $e) {
                echo $e->getMessage();
                return false;
            }

            return true;
        }else{//Si on souhaite le désinstaller...
            //On récupère l'id de l'onglet à supprimer
            $id = Tab::getIdFromClassName('AdminVideoPlayer');
            //Si l'onglet existe bien, on récupère toutes ses infos et on le supprime
            if($id){
                $tab = new Tab($id);
                return $tab->delete();
            }
            return true;
        }
    }

    //Load the configuration form
    public function getContent(){
        //If values have been submitted in the form, process.
        
        if (((bool)Tools::isSubmit('submitVideoPlayerModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    //Create the form that will be displayed in the configuration of your module.
    protected function renderForm(){
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitVideoPlayerModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), //Add values for your inputs
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    //Create the structure of your form.
    protected function getConfigForm(){
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'VIDEOPLAYER_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'VIDEOPLAYER_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'VIDEOPLAYER_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    //Set values for the inputs.
    protected function getConfigFormValues(){
        return array(
            'VIDEOPLAYER_LIVE_MODE' => Configuration::get('VIDEOPLAYER_LIVE_MODE', true),
            'VIDEOPLAYER_ACCOUNT_EMAIL' => Configuration::get('VIDEOPLAYER_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'VIDEOPLAYER_ACCOUNT_PASSWORD' => Configuration::get('VIDEOPLAYER_ACCOUNT_PASSWORD', null),
        );
    }

    //Save form data.
    protected function postProcess(){
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    //Add the CSS & JavaScript files you want to be loaded in the BO.
    public function hookBackOfficeHeader(){
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    //Add the CSS & JavaScript files you want to be added on the FO.
    public function hookHeader(){
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayHome(){
        $videoPlayer = ModelVideoPlayer::getVideoPlayer(true);
        $this->context->smarty->assign(array(
            'videoPlayer' => $videoPlayer
        ));
        return $this->display(__FILE__, 'videoPlayer_home.tpl');
    }
}
