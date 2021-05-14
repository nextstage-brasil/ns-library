<?php

/**
 * @date 20/03/2019
 * @author NS
 */
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

class Wiki {

    private $itens;
    private $template = '';
    private $head;
    private $css;

    public function __construct($title, $small='', $descricao='') {
        $this->css = 'text-dark';
        $this->head = '<h1 class="text-left pb-0 mb-0 {text-css}">' . $title . '</h1>
        <div class="row mt-0 pt-0">
            <div class="col-12 {text-css}">' . $small . '</div>
        </div>
        <p class="text-small mt-2 mb-5 {text-css}">' . $descricao . '</9>';
        $this->itens = [];
    }

    public function add($title, $content) {
        $this->itens[] = ['title' => $title, 'content' => $content];
    }
    public function setClassText($class)   {
        $this->css = $class;
    }

    public function printWiki() {
        $out = [];
        $out[] = '<div class="wiki">';
        foreach ($this->itens as $item) {
            $out[] = (($item['title']) ? '<h5 class="border-bottom text-left mt-1 mb-0 {text-css}">'.$item['title'].'</h5>' : '')
                    . '<p class="mb-1 {text-css}">'.$item['content'].'</p>';
        }
        $out[] = '</div>';
        $html = str_replace('{text-css}', $this->css, $this->head . implode(' ', $out));
        return Minify::html($html);
    }
    
    public function render() {
        return $this->printWiki();
    }

}
