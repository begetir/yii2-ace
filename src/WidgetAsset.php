<?php

namespace begetir\ace;

use yii\web\AssetBundle;
use yii\web\View;

class WidgetAsset extends  AssetBundle{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/ace-builds/src-noconflict';
    /**
     * @inheritdoc
     */
    public $js = [
        'ace.js'
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset'
    ];

    public function init()
    {
        if (!YII_DEBUG) {
            $this->sourcePath = str_replace("ace-builds/src", "ace-builds/src-min", $this->sourcePath);
        }
    }

    /**
     * @param View $view
     * @param array $extensions
     * @return static
     */

    public static function register($view, $extensions = [])
    {
        $bundle = parent::register($view);

        foreach ($extensions as $_ext) {
            $view->registerJsFile($bundle->baseUrl . "/ext-{$_ext}.js", ['depends' => [static::class]], "ACE_EXT_" . $_ext);
        }

        return $bundle;
    }


}