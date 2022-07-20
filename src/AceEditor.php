<?php
namespace begetir\ace;

/
 * Copyright  (C) 7/20/22, 3:32 AM , Inc - All Rights Reserved
 * Author: Beget
 * Programmer: Mohamad Kazem Lotfi
 * Phone : +989130012987
 * WebSite : https://beget.ir
 * Powered By : Yii2  (https://www.yiiframework.com)
 *
 */

use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/
 * ACE Widget
 */
class AceEditor extends InputWidget
{
    /* @var boolean Read-only mode on/off (false = off - default) */
    public $readOnly = false;

    / @var string By default, the editor supports plain text mode */
    public $mode = 'php';

    / @var string Themes are loaded on demand; all you have to do is pass the string name:
     * See all themes https://github.com/ajaxorg/ace/tree/master/lib/ace/theme
     */
    public $theme = 'github';

    / @var bool Static syntax highlight */
    public $editable = true;

    / @var bool
     * Auto completion snippets default is true
     */
    public $autocompletion = true;

    / @var array */
    public $extensions = [];

    / @var array */
    public $clientOptions = [
        "maxLines" => 100,
        "minLines" => 5,
    ];

    /
     * @var array Div options
     */
    public $containerOptions = [
        'style' => 'width: 100%; min-height: 400px'
    ];

    public function init()
    {
        parent::init();
//        WidgetAsset::register($this->getView());


    }

    /
     * @return string
     */
    public function run()
    {
        $this->registerPlugin();
        $content = Html::tag('div', '', $this->containerOptions);
        $this->options['style'] = 'display:none';
        if ($this->hasModel()) {
            $content .= Html::activeTextarea($this->model, $this->attribute, $this->options);
        } else {
            $content .= Html::textarea($this->name, $this->value, $this->options);
        }
        return $content;
    }


    protected function registerPlugin()
    {
        // Set ids
        $editor_id = $this->getId();
        $editor_var = 'aceeditor_' . $editor_id;
        $textarea_var = 'acetextarea_' . $editor_id;

        if (!$this->editable) {
            $this->extensions[] = 'static_highlight';
        }

        // Enable auto completion
        $autocompletion = $this->autocompletion ? 'true' : 'false';

        if ($this->autocompletion) {
            $this->extensions[] = 'language_tools';
        }

        if ($this->autocompletion) {
            $this->clientOptions['enableBasicAutocompletion'] = true;
            $this->clientOptions['enableSnippets'] = true;
            $this->clientOptions['enableLiveAutocompletion'] = false;
        }

        WidgetAsset::register($this->getView(), $this->extensions);

        // Convert client options to json
        $clientOptions = Json::encode($this->clientOptions);

        $js = <<<JS
        (function(){
            var aceEditorAutocompletion = {$autocompletion};
            if (aceEditorAutocompletion) {
                ace.require("ace/ext/language_tools");
            }
            {$editor_var} = ace.edit("{$editor_id}");
            {$editor_var}.setTheme("ace/theme/{$this->theme}");
            {$editor_var}.setHighlightActiveLine(true);
            {$editor_var}.setShowPrintMargin(false);
            {$editor_var}.getSession().setMode("ace/mode/{$this->mode}");
            {$editor_var}.setReadOnly({$this->readOnly});
            {$editor_var}.setOptions({$clientOptions});
        })();
JS;
        $view = $this->getView();
        $view->registerJs("\nvar {$editor_var} = {};\n", $view::POS_HEAD);
        $view->registerJs($js);
		$this->getView()->registerJs("
            var {$textarea_var} = $('#{$this->options['id']}').hide();
            {$editor_var}.getSession().setValue({$textarea_var}.val());
            {$editor_var}.getSession().on('change', function(){
                {$textarea_var}.val({$editor_var}.getSession().getValue());
            });
        ");
        Html::addCssStyle($this->options, 'display: none');
        $this->containerOptions['id'] = $editor_id;
        $this->getView()->registerCss("#{$editor_id}{position:relative}");
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