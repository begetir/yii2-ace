<?php
namespace begetir\ace;


use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * Class AceEditor
 * @package kak\widgets\aceeditor
 */
class AceEditor extends InputWidget
{

    /**
     * @var string Ace CDN base URL
     */
    public static $cdnBaseUrl = 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.1/';

    /** @var string */
    public $varNameAceEditor = 'aceEditor';

    /** @var bool Static syntax highlight */
    public $editable = true;

    /** @var bool */
    public $autocompletion = false;

    /**
     * @var string Programming Language Mode
     */
    public $mode = 'html';

    /**
     * @var string Editor theme
     * $see Themes List
     * @link https://github.com/ajaxorg/ace/tree/master/lib/ace/theme
     */
    public $theme = 'github';

    /** @var array */
    public $extensions = [];

    /**
     * @var array Ace options
     * @see https://github.com/ajaxorg/ace/wiki/Configuring-Ace
     */
    public $clientOptions = [
        "maxLines" => 100,
        "minLines" => 5,
    ];

    /**
     * @var array Container options
     */
    public $containerOptions = [
        'style' => 'width: 100%; min-height: 400px'
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!isset($this->containerOptions['id'])) {
            $this->containerOptions['id'] = $this->options['id'] . '-ace';
        }
        $this->initOptions();
    }

    public function initOptions()
    {
        $id = $this->getId();


        $editor_var = 'aceeditor_' . $id;
        $textarea_var = 'acetextarea_' . $id;

        $code = "
            var {$editor_var} = ace.edit('{$id}')
            {$editor_var}.setTheme('ace/theme/{$this->theme}')
            {$editor_var}.getSession().setMode('ace/mode/{$this->mode}')
            
            var {$textarea_var} = $('#{$this->options['id']}').hide();
            {$editor_var}.getSession().setValue({$textarea_var}.val());
            {$editor_var}.getSession().on('change', function(){
                {$textarea_var}.val({$editor_var}.getSession().getValue());
            });
        ";

        $this->getView()->registerJs($code);

        Html::addCssStyle($this->options, 'display: none');
        $this->containerOptions['id'] = $id;
        $this->getView()->registerCss("#{$id}{position:relative}");

    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerPlugin();
    }

    /**
     * Registers Ace plugin
     */
    protected function registerPlugin()
    {
        $view = $this->getView();
        if (!$this->editable) {
            $this->extensions[] = 'static_highlight';
        }

        if ($this->autocompletion) {
            $this->extensions[] = 'language_tools';
        }

        WidgetAsset::register($view, $this->extensions);

        return $this->editable ? $this->editable() : $this->readable();
    }

    protected function editable()
    {
        $id = $this->id;
        $autocompletion = $this->autocompletion ? 'true' : 'false';

        if ($this->autocompletion) {
            $this->clientOptions['enableBasicAutocompletion'] = true;
            $this->clientOptions['enableSnippets'] = true;
            $this->clientOptions['enableLiveAutocompletion'] = false;
        }

        $clientOptions = Json::encode($this->clientOptions);
        $js = <<<JS
        (function(){
            var aceEditorAutocompletion = {$autocompletion};
            if (aceEditorAutocompletion) {
                ace.require("ace/ext/language_tools");
            }
            {$this->varNameAceEditor} = ace.edit("{$id}");
            {$this->varNameAceEditor}.setTheme("ace/theme/{$this->theme}");
            {$this->varNameAceEditor}.getSession().setMode("ace/mode/{$this->mode}");
            {$this->varNameAceEditor}.setOptions({$clientOptions});
        })();
JS;
        $view = $this->getView();
        $view->registerJs("\nvar {$this->varNameAceEditor} = {};\n", $view::POS_HEAD);
        $view->registerJs($js);


        $content = Html::tag('div', '', $this->containerOptions);
        $this->options['style'] = 'display:none';
        if ($this->hasModel()) {
            $content .= Html::activeTextarea($this->model, $this->attribute, $this->options);
        } else {
            $content .= Html::textarea($this->name, $this->value, $this->options);
        }
        return $content;
    }

    /**
     * @return string
     */
    protected function readable()
    {
        $this->options['id'] = $this->id;
        $this->view->registerJs(
            <<<JS
            (function(){
                var _highlight = ace.require("ace/ext/static_highlight");
                _highlight(\$('#{$this->id}')[0], {
                    mode: "ace/mode/{$this->mode}",
                    theme: "ace/theme/{$this->theme}",
                    startLineNumber: 1,
                    showGutter: true,
                    trim: true
                });
            })();
JS
        );

        return Html::tag('pre', htmlspecialchars($this->value), $this->options);
    }

}