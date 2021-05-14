<?php

/**
 * Update at 19/11/2018
 */
require_once (Config::getData('path') . '/library/_lib/wideimage/WideImage.php');

class Upload {

    private $arquivo;
    private $tipo;
    private $mime;
    private $nome;
    private $nomeOriginal;
    private $extensao;
    // mimes definidos para evitar upload de arquivo malicioso, ou executavel
    private static $_MIMES = array(
        'application/pdf' => 'pdf',
        'application/rtf' => 'rtf',
        'audio/x-wav' => 'wav',
        'audio/wav' => 'wav',
        'audio/mpeg' => 'mp3',
        'audio/mp3' => 'mp3',
        'audio/vnd.dlna.adts' => 'aac',
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/tiff' => 'tiff',
        'image/x-portable-bitmap' => 'bpm',
        'multipart/x-zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
        'text/html' => 'html',
        'text/plain' => 'txt',
        'text/richtext' => 'rtx',
        'video/mpeg' => 'mpeg',
        'video/quicktime' => 'mov',
        'video/msvideo' => 'avi',
        'video/x-sgi-movie' => 'movie',
        'video/mp4' => 'mp4',
        'application/vnd.ms-excel' => 'csv',
        'application/octet-stream' => 'ofx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/msword' => 'doc',
    );

    public function __construct($arquivo, $dir, $novaLargura = false, $thumbs = false) {
        $this->arquivo = false;
        if ($arquivo) {
            $arquivo['dirSalvar'] = Helper::directorySeparator($dir . DIRECTORY_SEPARATOR);

            if (!is_dir($arquivo['dirSalvar'])) {
                Helper::saveFile($arquivo['dirSalvar'] . '/index.php');
            }
            //$arquivo['novoNome'] = (($novoNome) ? $novoNome : $arquivo['name']);
            //$arquivo['novoNome'] = $arquivo['name'];
            $arquivo['novaLargura'] = $novaLargura;
            $arquivo['thumbs'] = (boolean) $thumbs;
            $this->setArquivo($arquivo);
        }
    }

    private function setArquivo($arquivo) {
        $this->arquivo = $arquivo;
        $this->arquivo['maxFilesize'] = (int) $this->arquivo['maxFilesize'];
        $this->arquivo['name'] = trim($this->arquivo['name']);
        // retirar extensão do arquivo
        //$t = explode('.', preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(trim($this->arquivo['name']))));
        $t = explode('.', $this->arquivo['name']);
        $this->extensao = array_pop($t); //unset($t[count($t) - 1]);
        $this->nomeOriginal = implode(' ', $t); // será usado com oreferencia de nome para o usuario
        //$this->arquivo['name'] = trim($this->arquivo['name']);
        //$this->tipo = self::$_MIMES[$this->getMimeType()];
        //$this->arquivo['type'] = $this->getMimeType();
        $this->mime = str_replace(['/vnd.dlna.adts'], ['/aac'], $this->arquivo['type']);
        $this->tipo = self::$_MIMES[$this->arquivo['type']];
        if (!$this->tipo || $this->tipo === '') { // arquivo enviado nao aceito na tabela de MIME
            $this->arquivo = false;
            Log::logTxt('mime_nao_definido', 'UPLOADFILE - MIME não definido: ' . var_export($arquivo, true));
            return 'Tipo de arquivo não permitido';
        }
        $namefile = $_SESSION['upload_prefix'] . $this->arquivo['name'];
        $this->nome = md5($namefile) . "." . $this->tipo;
        if (!file_exists($this->arquivo['dirSalvar'])) {
            mkdir($this->arquivo['dirSalvar'], 0777);
        }
        if (!file_exists($this->arquivo['dirSalvar'] . '/thumbs')) {
            mkdir($this->arquivo['dirSalvar'] . '/thumbs', 0777);
        }
    }

    public function getNome() {
        return $this->nome;
    }

    public function execute() {
        $t = (new Eficiencia(__METHOD__))->setLimits(1, 50);
        if (!$this->tipo || !$this->arquivo) {
            return "Tipo de arquivo não suportado ou MIME não definido (" . $this->tipo . ")";
        }
        if ($this->arquivo['maxFilesize'] > 0 && filesize($this->arquivo['tmp_name']) > $this->arquivo['maxFilesize']) {
            return "Arquivo '$this->nomeOriginal.$this->extensao' é superior ao tamanho máximo permitido (" . ($this->arquivo['maxFilesize'] / 1024 / 1024) . "MB)";
        }

        switch ($this->tipo) {

            case "png":
            case "jpeg":
            case "jpg":
            case "pjpeg":
            case "gif":

                $qualidade = (($this->tipo === 'png') ? 8 : 80);
                try {
                    $wide = WideImage::load($this->arquivo["tmp_name"]);
                } catch (WideImage_InvalidImageSourceException $exc) {
                    return $exc->getMessage();
                }
                if ($this->arquivo['thumbs']) {
                    $r = $wide->resize($this->arquivo['novaLargura'], $this->arquivo['novaLargura'], 'outside');
                    $resized = $r->crop('center', 'center', $this->arquivo['novaLargura'], $this->arquivo['novaLargura']);
                    $resized->saveToFile($this->arquivo['dirSalvar'] . $this->getNome(), $qualidade);
                }

                
                
                //thumbs
                $r = $wide
                        ->resize(500, 500, 'outside')
                        ->crop('center', 'center', 500, 500)
                        //->merge($watermark, 'center', 'bottom-5', 30)
                        ->saveToFile($this->arquivo['dirSalvar'] . '/thumbs/' . $this->getNome(), $qualidade);
                
                

                /*
                // marcadagua
                $watermark = WideImage::load(Config::getData('pathViewUser') . '/images/watermark.jpg');
                $img = WideImage::load($this->arquivo['dirSalvar'] . '/thumbs/' . $this->getNome());
                // or use alignment labels, it's prettier
                $new = $img->merge($watermark, 'center', 'bottom', 30);
                $new->saveToFile($this->arquivo['dirSalvar'] . '/thumbs/' . $this->getNome(), $qualidade);
                */

                // normal
                move_uploaded_file($this->arquivo['tmp_name'], $this->arquivo['dirSalvar'] . $this->getNome());

                return true; //$this->getNome();
                break;


            default:
                $ret = move_uploaded_file($this->arquivo['tmp_name'], $this->arquivo['dirSalvar'] . $this->getNome());
                if ($ret !== true) {
                    return 'Erro ao mover arquivo temporário';
                } else {
                    return true;
                }
        }
    }

    function getTipo() {
        return $this->tipo;
    }

    function getMime() {
        return $this->mime;
    }

    function getNomeOriginal() {
        return $this->nomeOriginal;
    }

    /**
     * Recupera o Mime-Type de um arquivo
     * @param string $file Caminho para o arquivo
     * @param boolean $encoding Define se também será retornado a codificação do arquivo
     * @return string
     */
    public function getMimeType($encoding = true, $filename = false) {
        $file = $this->arquivo['tmp_name'];
        if (function_exists('finfo_open')) {
            if (is_file($file) && is_readable($file)) {
                $finfo = new finfo($encoding ? FILEINFO_MIME : FILEINFO_MIME_TYPE);
                $out = explode(';', $finfo->file($file))[0];
            } else {
                $out = $this->arquivo['type'];
                //return 'O arquivo não existe ou não temos permissões de leitura';
            }
        } else {
            $out = $this->arquivo['type'];
        }
        return str_replace('.', '-', $out);
    }

    public static function getMimeTypeStatic($filename) {
        $t = new Upload(false, false);
        $t->arquivo['tmp_name'] = $filename;
        return $t->getMimeType();
    }

    public static function geraThumbs($filepath, $width = 500) {
        if (!file_exists($filepath)) {
            return false;
        }
        $qualidade = ((stripos($filepath, 'png') > 0) ? 8 : 80);
        try {
            $wide = WideImage::load($filepath);
        } catch (WideImage_InvalidImageSourceException $exc) {
            return $exc->getMessage();
        }

        //thumbs
        $r = $wide->resize($width, $width, 'outside');
        $resized = $r->crop('center', 'center', $width, $width);
        return $resized->saveToFile(Helper::thumbsOnName($filepath), $qualidade);
    }

}

// fecha classe
