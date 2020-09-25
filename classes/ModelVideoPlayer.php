<?php

class ModelVideoPlayer extends ObjectModel
{
    public $name;
    public $active = true;
    public $url;

    public static $definition = array(
        'table' => 'videoPlayer',
        'primary' => 'id_videoPlayer',
        'multilang' => true,
        'fields' => array(
            // Champs Standards
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
            'active' => array('type' => self::TYPE_BOOL),
            'url' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'lang' => true, 'size' => 255),
        )
    );

    public static function getVideoPlayer($active = true, $id_lang = null){
        $id_lang = $id_lang ? $id_lang : Context::getContext()->language->id;
        $q = new DbQuery();
        $q->select('v.*, vl.url')
            ->from('videoPlayer', 'v')
            ->innerJoin('videoPlayer_lang', 'vl', 'vl.id_videoPlayer=v.id_videoPlayer and vl.id_lang='.$id_lang)
            ->where('v.active='.(int)$active);

        return Db::getInstance()->executeS($q);
    }
}