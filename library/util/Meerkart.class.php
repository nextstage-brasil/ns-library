<?php

require_once __DIR__ . '/Helper.class.php';
require_once Config::getData('path') . '/library/_lib/wideimage/WideImage.php';

class Meerkart {

    private static $recognition;
    private static $host;
    private static $header;
    private static $headerJson;
    private static $method;

    public function __construct() {
        self::$host = Config::getData('meerkartHost');
        self::$method = 'POST';
        self::$headerJson = [
            'Content-Type:application/json',
            'api_key:' . Config::getData('meerkartKey')
        ];
        self::$header = [
            'Content-Type:multipart/form-data',
            'api_key:' . Config::getData('meerkartKey')
        ];
    }

    public static function init() {
        if (self::$recognition == null) {
            self::$recognition = new Meerkart();
        }
    }

    /**
     * Método para upload de imagem para treinamento do servidor
     * @param string $filename path do filename a ser enviado
     * @param type $label Nome de referência a ser ensinado
     * @return type
     */
    public static function train($filename, $label) {
        self::init();
        $out = [];
        if (!file_exists($filename)) {
            return ['error' => 'Filename not exists (RCG-31)'];
        }

        // fazer um train para cada image (ver como melhorar essa eficiencia)
        $params = [
            'imageB64' => base64_encode(file_get_contents($filename)),
            'label' => $label
        ];
        $ret = Helper::getWebPage(self::$host . '/train/person', $params, self::$method, self::$header);
        $out['content'] = json_decode($ret->content, true);
        $out['httpStatus'] = $ret->http_code;
        Log::logTxt('Meerkart', __METHOD__ . ' '.json_encode($out));
        return $out;
    }

    /**
     * Busca pessoas baseada em uma imagem
     * @param string $filename Path da imagem a ser analisada
     * @return stdClass:: count: Total de pessoas identificadas, httpStatus, peoples[] Nome das pessoas 
     */
    public static function recognize($filename, $newFilename = false) {
        self::init();
        if (!file_exists($filename)) {
            return ['error' => 'File not exists (RCG-41)'];
        }
        $params = ['imageB64' => base64_encode(file_get_contents($filename))];
        $dados = Helper::getWebPage(self::$host . '/recognize/people', $params, self::$method, self::$header);
        $ret = json_decode($dados->content, true);
        $out['count'] = count($ret['people']);
        $out['httpStatus'] = $dados->http_code;
        foreach ($ret['people'] as $people) {
            $p = json_decode(json_encode($people));
            $out['peoples'][] = $p->recognition->predictedLabel;
            self::setFace($p, $filename, $newFilename);
        }
        Log::logTxt('Meerkart', __METHOD__ . ' '.json_encode($out));
        return $out;
    }

    public static function remove($traindId) {
        $out = ['content' => [], 'httpStatus' => '500'];
        if (strlen($traindId) > 5) {
            self::init();
            $ret = Helper::getWebPage(self::$host . '/train/point', ['trainId' => $traindId], 'DELETE', self::$headerJson);
            $out['content'] = json_decode($ret->content, true);
            $out['httpStatus'] = $ret->http_code;
            Log::logTxt('Meerkart', __METHOD__ . ' - traindId: '.$traindId.', Result: '.$ret->http_code);
        }
        return $out;
    }

    /**
     * Método que escreve um quadro ao redor do rosto identificado, e o nome identificado abaixo
     * @param type $p
     * @param type $filename
     * @param type $newFilename
     */
    private static function setFace($p, $filename, $newFilename = false) {
        // marcar rosto
        $img = WideImage::load($filename);
        $canvas = $img->getCanvas();
        // contorno do rosto
        $canvas->rectangle($p->top_left->x, $p->top_left->y, $p->bottom_right->x, $p->bottom_right->y, $img->allocateColor(102, 0, 255));
        // base para nome
        $canvas->filledRectangle($p->top_left->x, $p->bottom_right->y, $p->top_left->x + 18 + strlen($p->recognition->predictedLabel) * 8, $p->bottom_right->y + 25, $img->allocateColor(102, 0, 255));
        // name text
        $canvas->useFont(Config::getData('pathView') . '/fonts/arial.ttf', 12, $img->allocateColor(242, 242, 242));
        $canvas->writeText($p->top_left->x + 7, $p->bottom_right->y + 7, $p->recognition->predictedLabel);
        // save to some file, or new
        // update image
        $img->saveToFile((($newFilename) ? $newFilename : $filename));
    }

}
