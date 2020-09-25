<?php

if(!class_exists('ModelVideoPlayer'));
    require_once _PS_MODULE_DIR_.'videoPlayer/classes/ModelVideoPlayer.php';

class AdminVideoPlayerController extends ModuleAdminController{

    public function __construct(){
        $this->table = 'videoPlayer';
        $this->className = 'ModelVideoPlayer';
        $this->lang = true;
        $this->bootstrap = true;

        //Peremt d'afficher le tableau des vidéos existantes
        $this->fields_list = array(
            'id_videoPlayer' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto',
            ),
            'url' => array(
                'title' => $this->l('URL'),
                'width' => 'auto',
            ),
            /*'autoplay' => array(
                'title' => $this->l('Autoplay'),
                'autoplay' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'orderby' => true
            ),*/
            'active' => array(
                'title' => $this->l('Enabled'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'orderby' => true
            )
        );
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();
    }

    //Permet d'afficher le formulaire d'ajout d'une nouvelle vidéo
    public function renderForm()
    {
        if (!($videoPlayer = $this->loadObject(true))) {
            return;
        }

        //Ici on définit tous les champs de notre formulaire
        $this->fields_form = array(
            'tinymce' => true,//On active le plugin tinymce qui permet d'ajouter un éditeur de texte "TinyMCE" dans un textarea
            'legend' => array(
                'title' => $this->l('Add/Edit a video'),//Titre du formulaire qui apparaitra en haut à gauche de celui-ci
                'icon' => 'icon-certificate',
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'col' => 4,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:') . ' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('URL'),
                    'name' => 'url',
                    'lang' => true,
                    'col' => 4,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:') . ' &lt;&gt;;=#{}'
                ),
                /*array(
                    'type' => 'switch',
                    'label' => $this->l('Autoplay'),
                    'name' => 'autoplay',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ),*/
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    )
                )
            )
        );

        if (!($videoPlayer = $this->loadObject(true))) {
            return;
        }

        /*if(Shop::isFeatureActive()){
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso',
            );
        }*/

        $this->fields_form['submit'] = array(
            'title' => $this->l('Save'),
        );

        foreach ($this->_languages as $language) {
            $this->fields_value['url_' . $language['id_lang']] = htmlentities(stripslashes($this->getFieldValue(
                $videoPlayer,
                'url',
                $language['id_lang']
            )), ENT_COMPAT, 'UTF-8');
        }

        return parent::renderForm();
    }

    /**
     * Surcharge de la fonction de traduction sur PS 1.7 et supérieur.
     * La fonction globale ne fonctionne pas
     * @param type $string
     * @param type $class
     * @param type $addslashes
     * @param type $htmlentities
     * @return type
    */

    //En redéclarant cette fonction ici, on écrase l'ancienne mais on utilise quand même cette dernière si la version de Prestashop est antérieure à la version 1.7 
    public function l($string, $class = null, $addslashes = false, $htmlentities = true){
        //Si la version de Prestashop est plus grande ou égale à 1.7...
        if(_PS_VERSION_ >= '1.7'){
            //On utilise la nouvelle méthode de traduction
            return Context::getContext()->getTranslator()->trans($string);
        }else{//Sinon...
            //On utilise l'ancienne méthode pour les traductions
            return parent::l($string, $class, $addslashes, $htmlentities);
        }
    }

}
