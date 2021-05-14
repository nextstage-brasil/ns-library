<?php
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}
require_once (Config::getData('path') . '/library/util/dompdf/autoload.inc.php');

use Dompdf\Dompdf;

class Html2Pdf {

    private $template;
    private $pathDestino;
    private $paper;
    private $orientacao;
    private $filename;
    private $result;

    public function __construct($pathDestino, $filename = false, $paper = 'A4', $orientacao = 'portrait') {
        $this->paper = $paper;
        $this->orientacao = $orientacao;
        $this->pathDestino = $pathDestino;
        $this->filename = (($filename) ? $filename : Helper::upper(substr(md5(microtime()), 0, 12) . '.pdf'));
        $this->result = false;
    }

    public function loadFromFile($filename) {
        if (file_exists($filename)) {
            $this->template = file_get_contents($filename);
        } else {
            return ['error' => 'File not exists'];
        }
    }

    public function loadFromHTML($html) {
        $this->template = $html;
    }

    public function save() {
        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_base_path(Config::getData('path') . '/sistema/css');
        $dompdf->load_html($this->template);
        $dompdf->set_paper($this->paper, $this->orientacao);
        $dompdf->render();
        $pdf = $dompdf->output();
        // garantir que o diretório ira existir
        Helper::saveFile($this->pathDestino);
        file_put_contents($this->pathDestino . DIRECTORY_SEPARATOR . $this->filename, $pdf);
        if (file_exists($this->pathDestino . DIRECTORY_SEPARATOR . $this->filename)) {
            $this->result = true;
        }
    }

    function getFilename() {
        return $this->filename;
    }

    function getResult() {
        return $this->result;
    }

}
