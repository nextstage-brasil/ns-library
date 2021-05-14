<?php

class Form {

    //private $name;
    private $head;
    private $form;

    public function __construct($name = '', $legenda = '', $action = '', $method = "post", $enctype = "multipart/form-data", $onSubmit = "return false;") {
        //$this->name = 'form_'.$name;
        $name = (($name !== '') ? $name : md5(time()));
        $this->head = '
<form name="form_' . $name . '" id="' . $name . '" method="' . $method . '" action="' . $action . '" enctype="' . $enctype . '" onSubmit="' . $onSubmit . '">';

        $this->head .= '<fieldset>';
        $this->head .= (($legenda != '') ? '<legend>' . $legenda . '</legend>' : '');
        $this->form = '<div class="row">';
    }

    public function addElement($element, $class = '') {
        $this->form .= '<div class="' . $class . '">'
                . $element
                . '</div>';
        return $this;
    }

    public function printForm() {
        $this->form .= '</div>'; // fecha row
        return $this->head . $this->form . '
    </fieldset></form>';
    }

    public static function getModel($content, $colSm = 'col-sm-6') {
        return ['content' => $content, 'class' => ' mt-1 '. $colSm];
    }

    /**
     * @param type $json Arquivo em formato JSON para gerar o formulario
     * @param type $objectJson Nome da variavel que comporta o JSON. Ex: 'variavel[JSON_OBJECT]'
     * @param type $objectRoot
     */
    public static function fromJson($json, $objectJson) {
        $j = json_decode($json);
        $out = [];
        foreach ($j as $key => $val) {
            $out[] = Form::getModel(Html::input(
                    ['ng-model' => $objectJson."['$key']"], $key));
        }
        return $out;
    }

}
