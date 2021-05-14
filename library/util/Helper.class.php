<?php

if (!defined('SISTEMA_LIBRARY')) {
    die('No direct script access allowed');
}
/*
  $domPDF = Config::getData('path') . '/library/util/dompdf/autoload.inc.php';
  require_once ($domPDF);
  use Dompdf\Dompdf;
 */

/**
 * Helper functions
 */
class Helper {

    private static $publicKey = 'awevas14525!@#$nmkmcHNHATGokmg83bnck,!@';
    private static $feriados;
    private static $jsonConfigDefault = [
        'grid' => 'col-sm-6',
        'type' => 'text',
        'class' => '',
        'ro' => 'false',
        'tip' => '',
        'label' => '',
        'list' => '',
    ];
    private static $controller;

    public function __construct() {
        
    }

    static function getJsonConfigDefault() {
        return self::$jsonConfigDefault;
    }

    public static function formatDate($data, $escolha = 'arrumar', $datahora = false, $alterarTimeZone = false) {
        //2017-05-04T02:59:59.000Z
        //$data = '2017-05-08T19:20:34-00:00';
        /*
         * TimeZone:
         * No banco de dados, esta sendo salvo horario de brasilia. Se vier do banco, precisa acrescentar 3 horas
         * 
         *          
         */

        if ($data !== 'NOW') {
            if (strlen($data) < 6) {
                return false;
            }
            $data = str_replace('"', '', $data);
            $t = explode('.', $data);
            $data = str_replace("T", " ", $t[0]);
            $hora = '12:00:00';
            $t = explode(' ', $data);
            if (count($t) > 1) {
                $data = $t[0];
                $hora = $t[1];
            }
            $c = (string) substr($data, 2, 1);
            if (!is_numeric($c)) {
                $data = substr($data, 6, 4) . '-' . substr($data, 3, 2) . '-' . substr($data, 0, 2);
            }
            $data = $data . 'T' . $hora . '-00:00';
            //Log::logTxt('debug', $data);
        }

        try {
            $date = new DateTime($data);
            if ($alterarTimeZone) {
                $date->setTimezone(new DateTimeZone('America/Sao_Paulo'));
            } else {
                //$date->setTimezone(new DateTimeZone('+0300'));
                //$date->setTimezone(new DateTimeZone());
            }
        } catch (Exception $e) {
            $backtrace = debug_backtrace();
            $origem = $backtrace[0]['file'] . ' [' . $backtrace[1]['class'] . '::' . $backtrace[1]['function'] . ' (' . $backtrace[0]['line'] . ')]';
            Log::logTxt('debug', 'ERROR DATE: ' . $e->getMessage() . '||' . $origem . __METHOD__ . __LINE__);
            return false;
        }
        if ($escolha === 'arrumar') { // Consertar o que vem form para inserir no BD
            if ($datahora) {
                return $date->format('Y-m-d H:i:s');
                //return date('Y-m-d h:i:s', strtotime($data));
            } else {
                return $date->format('Y-m-d');
                //return date('Y-m-d', strtotime($data));
            }
        } elseif ($escolha === "mostrar") { // Arrumar o que vem do Banco para imprimir na data pt-BR
            if ($datahora) {
                return $date->format('d/m/Y H:i:s');
                //return date('d/m/Y h:i:s', strtotime($data));
            } else {
                return $date->format('d/m/Y');
                //return date('d/m/Y', strtotime($data));
            }
        } elseif ($escolha === 'extenso') {
            return strftime('%d de %B de %Y', $date->getTimestamp());
        } else {
            return $date->format('Y-m-d');
            //return date('Y-m-d', strtotime($data));
        }
    }

    public static function formatFone($fone) {
        $fone = self::parseInt($fone);
        $ddd = '(' . substr($fone, 0, 2) . ') ';
        $fone = substr($fone, 2, strlen($fone) - 2);
        $out = $ddd . substr($fone, 0, 4) . substr($fone, 4, 8);
        if (strlen($fone) === 9) { // nono digito
            $out = $ddd . substr($fone, 0, 5) . substr($fone, 5, 9);
        }
        return $out;
    }

    public static function formatCep($cep) {
        $cep = self::parseInt($cep);
        return substr($cep, 0, 5) . '-' . substr($cep, 5, 8);
    }

    public static function decimalFormat($var) {
        if (stripos($var, ',') > -1) { // se achar virgula, veio da view, com formato. da base, nao vem virgula
            $var = self::parseInt($var);
            $var = substr($var, 0, strlen($var) - 2) . "." . substr($var, strlen($var) - 2, 2);
        }
        return $var;
    }

    public static function parseInt($var) {
        return preg_replace("/[^0-9]/", "", $var);
    }

    public static function dateToMktime($date = false) {
        if (!$date) {
            $date = time();
            return $date;
        }
        $date = Helper::formatDate($date, 'arrumar', true);
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        $timestamp = $dt->getTimestamp();
        return $timestamp;
    }

    public static function dateMoreDays($date, $days, $operation = '+') {
        $d = self::formatDate($date);
        $d = date_parse($d);
        $d = mktime($d['hour'], $d['minute'], $d['second'], $d['month'], (($operation === '+') ? $d['day'] + $days : $d['day'] - $days), $d['year']);
        $date = date('Y-m-d', $d);
        return $date;
    }

    public static function print_rr($var, $dump = null) {
        if (is_array($var) || is_object($var)) {
            echo "<pre>";
            if ($dump) {
                var_dump($var);
            } else {
                print_r($var);
            }
            echo "</pre>";
        }
    }

    public static function _objectToArray($object, $detalhes = false) {
        return AppController::arrayToObject($object, $detalhes);
    }

    public static function name2CamelCase($string, $prefixo = false) {
        $prefixo = array('mem_', 'sis_', 'anz_', 'aux_', 'app_');
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $out[self::name2CamelCase($key)] = $value;
            }
            return $out;
        }
        if (is_array($prefixo)) {
            foreach ($prefixo as $val) {
                $string = str_replace($val, "", $string);
            }
        }
        // new 26/02/2018
        $string = str_replace('_', ' ', $string);
        $out = str_replace(' ', '', ucwords($string));
        $out{0} = mb_strtolower($out{0});
        return $out;
    }

    public static function reverteName2CamelCase($string) {
        $out = '';
        for ($i = 0; $i < strlen($string); $i++) {
            if ($string[$i] === mb_strtoupper($string[$i]) && $string[$i] !== '.') {
                $out .= (($i > 0) ? '_' : '');
                $string[$i] = mb_strtolower($string[$i]);
            }
            $out .= $string[$i];
        }
        return $out;
    }

    public static function saveFileBuild($filename, $template = 'empty', $mode = "w+") {
        return self::saveFile($filename, false, $template, $mode);
    }

    private static function createTreeDir($filename) {
        $dir = str_replace('library', '', SistemaLibrary::getPath());
        $path = str_replace('/', DIRECTORY_SEPARATOR, $filename);
        $path = str_replace($dir, '', $path);
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $file = array_pop($parts);

        foreach ($parts as $part) {
            if (!is_dir($dir .= "/$part")) {
                mkdir(str_replace('/', DIRECTORY_SEPARATOR, $dir), 0777) or die('Can\'t create dir: ' . $dir);
            }
        }
        return (object) ['path' => $dir, 'name' => $file];
    }

    public static function saveFile($filename, $name = false, $template = '<?=php Header("Location:/")', $mode = "w+") {
        $filename = $filename . (($name) ? '/' . $name : '');
        $file = self::createTreeDir($filename);
        if (file_exists($filename) && $mode !== 'SOBREPOR') {
            $file->name = 'ZZ_XPTO_' . $file->name;
        }
        $save = str_replace('/', DIRECTORY_SEPARATOR, $file->path . DIRECTORY_SEPARATOR . $file->name);
        unset($filename);
        file_put_contents($save, $template);
        return file_exists($save);
    }

    public static function moveUploadFile($nomearquivo, $dirDestino, $thumbs = false, $rewrite = true) {
        $nomearquivo = str_replace('/', DIRECTORY_SEPARATOR, $nomearquivo);
        $dirDestino = str_replace('/', DIRECTORY_SEPARATOR, $dirDestino);
        $dirDestino = str_replace(Config::getData('path') . DIRECTORY_SEPARATOR, '', $dirDestino);
        $dirDestino = Config::getData('path') . DIRECTORY_SEPARATOR . $dirDestino;
        $oldname = Config::getData('pathView') . DIRECTORY_SEPARATOR . 'uploadFiles' . DIRECTORY_SEPARATOR . $nomearquivo;
        $newname = $dirDestino . DIRECTORY_SEPARATOR . $nomearquivo;
        $thumbsOldName = Config::getData('pathView') . DIRECTORY_SEPARATOR . 'uploadFiles' . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $nomearquivo;
        $thumbsNewName = $dirDestino . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $nomearquivo;
        //Log::logTxt('debug', __METHOD__.' NewName: ' . $newname);
        // verifica se arquivo ja existe no destino
        if (file_exists($newname)) {
            if ($rewrite) {
                self::deleteFile($newname);
                self::deleteFile($thumbsNewName);
            } else {
                // só retornar pois arquivo já existe
                return true;
            }
        }

        // criar diretórios caso não exista
        self::saveFile($dirDestino, 'index.html', '<html>O que procura?</html>');
        self::saveFile($dirDestino . '/thumbs', 'index.html', '<html>O que procura?</html>');

        // mover arquivo
        if (file_exists($oldname)) {
            rename($oldname, $newname);
            if (file_exists($thumbsOldName)) {
                if ($thumbs) {
                    rename($thumbsOldName, $thumbsNewName);
                }
            }
        } else {
            return "File $oldname não existe";
        }

        // retorna true se file foi copiado ou false se não
        if (file_exists($newname)) {
            return true;
        } else {
            return 'Fiel não foi registrado';
        }
    }

    /**
     * 
     * @param type $filename
     * @param type $apagarDiretorio
     * @param type $trash
     * @return boolean
     */
    public static function deleteFile($filename, $apagarDiretorio = false, $trash = true) {
        $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);

        $t = explode(DIRECTORY_SEPARATOR, $filename);
        $file = $t[count($t) - 1];

        if (is_dir($filename)) {
            $dir = dir($filename);
            while ($arquivo = $dir->read()) {
                if ($arquivo != '.' && $arquivo != '..') {
                    self::deleteFile($filename . '/' . $arquivo, false, $trash);
                }
            }
            $dir->close();
            if ($apagarDiretorio) {
                echo 'to del: ' . $filename;
                echo rmdir($filename);
            }
        } else {
            if (file_exists($filename)) {
                if ($trash) { // decidir se é para apagar ou mover para lixeira
                    $prefixo = substr(md5(time()), 6) . '_';
                    $logName = ' movido para lixeira. Prefixo: ' . $prefixo;
                    Helper::saveFile(Config::getData('path') . '/.trash/index.php', false, '', 'SOBREPOR');
                    $newname = Config::getData('path') . '/.trash/' . $prefixo . $file;
                    rename($filename, $newname);
                    sleep(0.1); // concorrencia de disco
                    // alterar data de modificação do arquivo para hoje, e assim manter nalixeira
                    touch($newname);
                } else {
                    $logName = ' removido definitivamente';
                    unlink($filename);
                }
                $log = $filename . $logName;
            }
        }
        if (!file_exists($filename)) {
            //Log::log(__METHOD__, $log);
            return false;
        } else {
            Log::error('Não foi possivel remover arquivo: ' . $filename);
        }
    }

    public static function deleteDir($dir) {
        try {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? self::deleteDir("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        } catch (Exception $ex) {
            Log::error('Erro ao remover diretorio:' . $ex->getMessage());
        }
        /*

          try {
          if (!is_dir($dirPath)) {
          throw new InvalidArgumentException("$dirPath must be a directory");
          }
          if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
          $dirPath .= '/';
          }
          $files = glob($dirPath . '*', GLOB_MARK);
          foreach ($files as $file) {
          if (is_dir($file)) {
          self::deleteDir($file);
          } else {
          unlink($file);
          }
          }
          rmdir($dirPath);
          } catch (Exception $exc) {

          }
         * 
         */
    }

    public static function crypto($action, $string, $chave = false) {
        $output = false;

        $encrypt_method = "AES-256-CBC";
        if ($chave) {
            $secret_key = md5(Config::getData('token') . $chave);
        } else {
            $secret_key = Config::getData('token');
        }
        $secret_iv = $secret_key . '_IV';

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }

    /**
     * Método para criptografia autenticada. Garante que nenhum dos bits pode ser invertido
     * @date 2019-09-02
     * @param type $action
     * @param type $string
     * @param type $chave
     * @return string
     */
    public static function crypto72($action, $string, $chave) {
        if (strlen($string) < 5) {
            return '';
        }
        $Chave = pack('H*', hash('sha256', Config::getData('token') . $chave));
        if ($action === 'encrypt') {
            $IV = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES);
            $output = base64_encode($IV . sodium_crypto_aead_chacha20poly1305_ietf_encrypt($string, '', $IV, $Chave));
        } else if ($action === 'decrypt') {
            $Resultado = base64_decode($string);
            $t2 = mb_substr($Resultado, SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES, null, '8bit');
            $IV2 = mb_substr($Resultado, 0, SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES, '8bit');
            $output = sodium_crypto_aead_chacha20poly1305_ietf_decrypt($t2, '', $IV2, $Chave);
        } else {
            $output = '';
        }
        return $output;
    }

    public static function codifica($texto, $chave = false) {
        return self::crypto('encrypt', $texto, $chave);
    }

    public static function decodifica($texto, $chave = false) {
        return self::crypto('decrypt', $texto, $chave);
    }

    public static function codifica72($texto, $chave = false) {
        return self::crypto72('encrypt', $texto, $chave);
    }

    public static function decodifica72($texto, $chave = false) {
        return self::crypto72('decrypt', $texto, $chave);
    }

    public static function escreveTemplate($template, $array) {
        foreach ($array as $key => $value) {
            $template = str_replace('%' . $key . '%', $value, $template);
        }
        return $template;
    }

    public static function getDiaSemana($data) {
        $diaSemana = (int) date('N', strtotime($data));
        $nomesSemana = array("", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado", 'Domingo');
        return $nomesSemana[$diaSemana];
    }

    // Define uma função que poderá ser usada para validar e-mails usando regexp
    public static function validaEmail($email) {

        //return filter_var($email, FILTER_VALIDATE_EMAIL);

        //return
        $er = "/^(([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}){0,1}$/";
        if (preg_match($er, $email) && strlen($email) > 4) {
            return true;
        } else {
            return false;
        }
    }

    public static function sanitize($str) {
        return str_replace(" ", "_", preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(trim($str))));
    }

    public static function filterSanitize($var) {
        if (is_array($var)) {
            foreach ($var as $key => $value) {
                if (is_array($value)) {
                    $var[$key] = Helper::filterSanitize($value);
                } else {
                    if (substr($key, 0, 2) === 'id') {
                        $var[$key] = filter_var($value, FILTER_VALIDATE_INT);
                    }
                    if (stripos($key, 'email') > -1) {
                        $var[$key] = filter_var($value, FILTER_VALIDATE_EMAIL);
                    } else {
                        $var[$key] = filter_var($value, FILTER_SANITIZE_STRING);
                    }
                }
            }
            return $var;
        } else {
            return filter_var($var, FILTER_SANITIZE_STRING);
        }
    }

    /**
     * 
     * @param type $number
     * @param type $sinalNoFim
     * @param type $prefixo
     * @param type $color
     * @return string
     */
    public static function formatNumber($number, $sinalNoFim = false, $prefixo = 'R$', $color = true) {
        $out = $prefixo . number_format($number, 2, ',', '.');
        if ($sinalNoFim) {
            $out = $prefixo . number_format(abs($number), 2, ',', '.') . (($number < 0) ? '-' : '');
        }
        if ($color && $number < 0) {
            $out = '<span class="text-red">' . $out . '</span>';
        }

        return $out;
    }

    public static function formatCpfCnpj($var) {
        $var = self::parseInt($var);
        if (strlen($var) === 11) { // cpf
            $out = substr($var, 0, 3) . '.' . substr($var, 3, 3) . '.' . substr($var, 6, 3) . '-' . substr($var, 9, 2);
        } else if (strlen($var) === 14) { // cnpj
            $out = substr($var, 0, 2) . '.' . substr($var, 2, 3) . '.' . substr($var, 5, 3) . '/' . substr($var, 8, 4) . '-' . substr($var, 12, 2);
        } else {
            $out = $var;
        }
        return $out;
    }

    public static function upperByReference(&$var) {
        $var = mb_strtoupper((string) $var, 'UTF-8');
    }

    public static function upper($dados) {
        if (is_array($dados)) {
            foreach ($dados as $key => $value) {
                if (is_array($value)) {
                    continue;
                } else {
                    $dados[$key] = mb_strtoupper($value, 'UTF-8');
                }
            }
        } else {
            $dados = mb_strtoupper($dados, 'UTF-8');
        }
        return $dados;
    }

    public static function lower(&$dados) {
        if (is_array($dados)) {
            foreach ($dados as $key => $value) {
                if (is_array($value)) {
                    continue;
                } else {
                    $dados[$key] = mb_strtolower($value, 'UTF-8');
                }
            }
        } else {
            $dados = mb_strtolower($dados, 'UTF-8');
        }
        //return $dados;
    }

    public static function thumbsOnName($filename) {
        $fileOriginal = $filename;
        $filename = str_replace(DIRECTORY_SEPARATOR, '/', $filename);
        $t = explode('/', $filename);
        if (count($t) > 1) {
            $filename = $t[count($t) - 1];
            unset($t[count($t) - 1]);
            return implode('/', $t) . '/thumbs/' . $filename;
        } else {
            return 'thumbs/' . $fileOriginal;
        }
    }

    public static function compareString($str1, $str2, $case = false) {
        if (!$case) {
            self::upperByReference($str1);
            self::upperByReference($str2);
        }
        return ($str1 === $str2);
    }

    public static function validaCpfCnpj($val) {
        $val = (string) self::parseInt($val);
        if (strlen($val) === 11) {
            return self::validaCPF($val);
        }
        if (strlen($val) === 14) {
            return self::validaCnpj($val);
        }
        return 'Preencha corretamente CPF/CNPJ';
    }

    private static function validaCPF($cpf = null) {
        // Verifica se um número foi informado
        if (empty($cpf) || $cpf === '') {
            return 'CPF Inválido: Vazio';
        }
        // Elimina possivel mascara
        $cpf = self::parseInt($cpf);
        // Verifica se o numero de digitos informados é igual a 11 
        if (strlen($cpf) != 11) {
            return 'CPF Inválido: Menor que 11 digitos';
        }
        // Verifica se nenhuma das sequências invalidas abaixo 
        // foi digitada. Caso afirmativo, retorna falso
        else if ($cpf == '00000000000' ||
                $cpf == '11111111111' ||
                $cpf == '22222222222' ||
                $cpf == '33333333333' ||
                $cpf == '44444444444' ||
                $cpf == '55555555555' ||
                $cpf == '66666666666' ||
                $cpf == '77777777777' ||
                $cpf == '88888888888' ||
                $cpf == '99999999999') {
            return 'CPF Inválido: Número Sequencial';
            // Calcula os digitos verificadores para verificar se o
            // CPF é válido
        } else {

            for ($t = 9; $t < 11; $t++) {

                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return 'CPF Inválido: Digito verificador não é válido';
                }
            }
            return true;
        }
    }

    private static function validaCnpj($cnpj = null) {
        $cnpj = self::parseInt($cnpj);
        if (empty($cnpj) || $cnpj === '') {
            return 'CNPJ Inválido: Vazio';
        }
        if (strlen($cnpj) != 14) {
            return 'CNPJ Inválido: Menor que 14 digitos';
        }
        if ($cnpj === '00000000000000') {
            return 'CNPJ Inválido: Número sequencial';
        }
        $cnpj = (string) $cnpj;
        $cnpj_original = $cnpj;
        $primeiros_numeros_cnpj = substr($cnpj, 0, 12);
        if (!function_exists('multiplica_cnpj')) {

            function multiplica_cnpj($cnpj, $posicao = 5) {
                // Variável para o cálculo
                $calculo = 0;
                // Laço para percorrer os item do cnpj
                for ($i = 0; $i < strlen($cnpj); $i++) {
                    // Cálculo mais posição do CNPJ * a posição
                    $calculo = $calculo + ( $cnpj[$i] * $posicao );
                    // Decrementa a posição a cada volta do laço
                    $posicao--;
                    // Se a posição for menor que 2, ela se torna 9
                    if ($posicao < 2) {
                        $posicao = 9;
                    }
                }
                // Retorna o cálculo
                return $calculo;
            }

        }

        // Faz o primeiro cálculo
        $primeiro_calculo = multiplica_cnpj($primeiros_numeros_cnpj);

        // Se o resto da divisão entre o primeiro cálculo e 11 for menor que 2, o primeiro
        // Dígito é zero (0), caso contrário é 11 - o resto da divisão entre o cálculo e 11
        $primeiro_digito = ( $primeiro_calculo % 11 ) < 2 ? 0 : 11 - ( $primeiro_calculo % 11 );

        // Concatena o primeiro dígito nos 12 primeiros números do CNPJ
        // Agora temos 13 números aqui
        $primeiros_numeros_cnpj .= $primeiro_digito;

        // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
        $segundo_calculo = multiplica_cnpj($primeiros_numeros_cnpj, 6);
        $segundo_digito = ( $segundo_calculo % 11 ) < 2 ? 0 : 11 - ( $segundo_calculo % 11 );

        // Concatena o segundo dígito ao CNPJ
        $cnpj = $primeiros_numeros_cnpj . $segundo_digito;

        // Verifica se o CNPJ gerado é idêntico ao enviado
        if ($cnpj === $cnpj_original) {
            return true;
        } else {
            return 'CNPJ Inválido: Cálculo do dígito verificador inválido';
        }
    }

    public static function getThumbsByFilename($filename) {
        $t = explode('.', $filename);
        $extensao = Helper::upper($t[count($t) - 1]);
        switch ($extensao) {
            case 'XLSX':
            case 'XLS':
                $out = 'file-excel-o';
                break;
            case 'PDF':
                $out = 'file-pdf-o';
                break;
            case 'PNG':
            case 'JPG':
            case 'GIF':
            case 'JPEG':
                $out = 'file-image-o';
                break;
            case 'ZIP':
                $out = 'file-archive-o';
                break;
            case 'MP3':
            case 'AAC':
                $out = 'file-audio-o';
                break;
            case 'AVI':
            case 'MP4':
                $out = 'file-video-o';
                break;
            default:
                $out = 'file';
        }
        return $out;
    }

    /**
     * @update 29/04/2019 foreach para zerar 'config' em segundo nivel de json
     * @param type $dados
     * @param type $campoJson
     */
    public static function jsonRecebeFromView(&$dados, &$campoJson) {
        if (is_array($dados)) {
            foreach ($campoJson as $cpo) {
                if (!$dados[$cpo]) {
                    $dados[$cpo] = '{}';
                }
                $dados[$cpo] = json_decode(str_replace('&#34;', '"', $dados[$cpo]), true);
                foreach ($dados[$cpo] as $key => $val) {
                    if (isset($dados[$cpo][$key]['config'])) {
                        unset($dados[$cpo][$key]['config']);
                        unset($dados[$cpo][$key]['$$hashkey']);
                    }
                }
            }
            unset($dados[$cpo]['$$hashkey']);
            unset($dados[$cpo]['config']);
        }
    }

    /**
     * V.03_2020
     * @param type $entities
     * @param type $camposDate
     * @param type $camposDouble
     * @param type $camposJson
     * @param type $prefixo
     * @return type
     */
    public static function parseDateToDatePTBR($entities, $camposDate, $camposDouble = [], $camposJson = [], $prefixo = 'R$') {
        if (is_array($entities)) {
            foreach ($entities as $value) {
                $out[] = self::parseDateToDatePTBR($value, $camposDate, $camposDouble, $camposJson, $prefixo);
            }
            return $out;
        }

        self::$controller = ((!self::$controller) ? new AppController() : self::$controller);
        $dd = ((gettype($entities) === 'object') ? self::$controller->objectToArray($entities) : $entities);
        $dd['ext_a'] = $entities->selectExtra;
        $dd['ext_b'] = $entities->selectExtraB;


        foreach ($camposDate as $campo) {
            $dt = self::formatDate($dd[$campo], 'mostrar', strlen($dd[$campo]) > 10);
            $dd[$campo] = $dt ? $dt : '';
        }
        foreach ($camposDouble as $campo) {
            $dd[$campo] = self::formatNumber($dd[$campo], false, $prefixo);
        }
        foreach ($camposJson as $item) {
            $dd[$item] = Helper::extrasJson(Config::getModelJson($item), $dd[$item]);
        }
        return $dd;
    }

    public static function plural($param) {
        return $param;
    }

    /**
     * Método que encapsula uma chamada GET a uma url
     * @param string $url
     * @param array $params
     * @param string $method
     * @return Array
     */
    public static function curlCall($url, $params = [], $method = 'GET', $header = ['Content-Type:application/json']) {
        $time = new Eficiencia('[curlCall]' . $url);
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0', //set user agent
            CURLOPT_COOKIEFILE => "cookie.txt", //set cookie file
            CURLOPT_COOKIEJAR => "cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING => "", // handle all encodings
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 30, // timeout on connect
            CURLOPT_TIMEOUT => 15, // timeout on response
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => false
        ];
        $options[CURLOPT_HTTPHEADER] = $header;
        $options[CURLOPT_VERBOSE] = false;
        switch ($method) {
            case 'POST':
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = json_encode($params);
                break;
            default:
                if (count($params) > 0) {
                    $url = sprintf("%s?%s", $url, http_build_query($params));
                }
                $options[CURLOPT_URL] = $url;

            //$options[CURLOPT_POSTFIELDS] = json_encode($params);
            /*
              if (count($params) > 0) {
              $url = sprintf("%s?%s", $url, http_build_query($params));
              }
              $options[CURLOPT_URL] = $url;
             * 
             */
        }
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        Log::logTxt('curl-call', curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
        Log::logTxt('curl-call', $content);
        //echo curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $ret = (object) [
                    'content' => $content,
                    'errorCode' => curl_errno($ch),
                    'error' => ((curl_error($ch)) ? curl_error($ch) : false),
                    'status' => curl_getinfo($ch)['http_code']
        ];
        curl_close($ch);
        $time->end(2);
        return $ret;
    }

    public static function curlTrataRetornoApi($json) {
        $dd = json_decode(json_encode(json_decode($json->content, true)));
        //return $dd;
        $dd->content = (object) self::name2CamelCase((array) $dd->content);
        return $dd;
    }

    /**
     * Método especifico para consumir serviços dentro da apipark
     * @param type $app Nome do App a ser acessado, ou, diretorio logo após o url do apipark
     * @param type $ent Entidade que ira atender o chamado, ou type
     * @param type $fn Método que será chamado
     * @param type $params Array contendo os dados do body
     * @return type
     */
    public static function consumeApi($app, $route, $params = []) {
        $method = 'POST';
        $header = ['Content-Type:application/json', 'Api-Key: ' . Config::getData('apiPark', 'key')];
        $params = (object) $params;
        if ($params->token) {
            $header[] = 'Token: ' . $params->token;
        }
        $url = Config::getData('apiPark', 'url') . "/$app/$route";
        $ret = self::curlCall($url, $params, $method, $header);
        //if ($ret->status === 200) {
        return self::curlTrataRetornoApi($ret);
        /* } else {
          return (object) $ret;
          }
         * 
         */
    }

    /**
     * Aplica o separador correto de diretório conforme o ambiente
     * @param type $var
     */
    public static function directorySeparator($var) {
        return str_replace('/', DIRECTORY_SEPARATOR, $var);
    }

    /**
     * Método que retorna um array com as diferenças entre dois arrays
     * @param array $arrayNew
     * @param array $arrayOld
     * @return array
     */
    public static function arrayDiff($arrayNew, $arrayOld) {
        $out = [];
        $alteradosNovo = array_diff_assoc($arrayNew, $arrayOld);
        $alteradosAntigo = array_diff_assoc($arrayOld, $arrayNew);
        unset($alteradosNovo['error']);
        //include Config::getData('path') . '/src/config/aliases_fields.php';
        if (count($alteradosNovo) > 0) {
            foreach ($alteradosNovo as $key => $value) {
                $out[] = [
                    'field' => $key,
                    'campo' => Config::getAliasesField($key),
                    'old' => $alteradosAntigo[$key],
                    'new' => $value
                ];
            }
        }
        return $out;
    }

    /**
     * Cria uma máscara para o email, com asteriscos para não exibir o email completo
     * @param type $email
     * @return type
     */
    public static function emailMask($email) {
        $m = explode('@', $email);
        for ($i = 4; $i < strlen($m[0]); $i++) {
            $m[0][$i] = '*';
        }
        for ($i = 4; $i < strlen($m[0]); $i++) {
            $m[1][$i] = '*';
        }
        return implode('@', $m);
    }

    /**
     * Método para remover os 'undefined' que o javascript insere em valores null, ou entre aspas
     */
    public static function removeUndefinedFromJavascript($dados) {
        $out = [];
        $dados = ((is_array($dados)) ? $dados : []);
        foreach ($dados as $key => $value) {
            if ($value === 'undefined' || $value === 'null') {
                continue;
            }
            $out[$key] = $value;
            $out[$key] = str_replace('NS21', '&', $out[$key]);
        }
        return $out;
    }

    public static function jsonToArrayFromView($json) {
        return json_decode(str_replace('&#34;', '"', $json), true);
    }

    public static function vencimentoMais30dias($data) {
        $d = explode('-', Helper::formatDate($data));
        $ano = $d[0];
        $mes = $d[1];
        $dia = $d[2];
        $ultimoDiaMes = date("t", mktime(0, 0, 0, $mes + 1, '01', $ano));
        if ($dia <= $ultimoDiaMes) { // para meses onde a data realmente existe
            return date('Y-m-d', mktime(0, 0, 0, $mes + 1, $dia, $ano));
        } else {
            return date('Y-m-d', mktime(0, 0, 0, $mes + 1, $ultimoDiaMes, $ano));
        }
    }

    public static function ultimoDiaMes($mes, $ano) {
        $ultdia = date("t", mktime(0, 0, 0, $mes, '01', $ano));
        return date('Y-m-d', mktime(0, 0, 0, $mes, $ultdia, $ano));
    }

    public static function trataPeriodo($periodo = false) {
        if ($periodo) {
            $_GET['periodo'] = $periodo;
        }
        // relação de periodos disponíveis
        $periodos = array();
        for ($index = 0; $index < 15; $index++) {
            $date = date('m/Y', mktime(0, 0, 0, date('m') - $index, date(15), date('Y')));
            $datekey = str_replace("/", "_", $date);
            $periodos[$datekey] = $date;
        }

        // peridoo
        if ($_GET['periodo']) {
            $t = explode('_', $_GET['periodo']);
            $ultimo_dia = date("t", mktime(0, 0, 0, $t[0], '01', $t[1]));
            $dataInicial = date('Y-m-d', mktime(0, 0, 0, $t[0], 1, $t[1]));
            $dataFinal = date('Y-m-d', mktime(0, 0, 0, $t[0], $ultimo_dia, $t[1]));
        } else {
            if ($_GET['dataInicial']) {
                $dataInicial = Helper::formatDate($_GET['dataInicial']);
                if ($_GET['dataFinal'] != '') {
                    $dataFinal = Helper::formatDate($_GET['dataFinal']);
                } else {
                    $ultimo_dia = date("t", Helper::dateToMktime($dataInicial));
                    $dataFinal = date('Y-m-d', mktime(0, 0, 0, date('m', Helper::dateToMktime($dataInicial)), $ultimo_dia, date('Y', Helper::dateToMktime($dataInicial))));
                }
            } else {
                $_GET['periodo'] = date('m') . '_' . date('Y');
                $t = explode('_', $_GET['periodo']);
                $ultimo_dia = date("t", mktime(0, 0, 0, $t[0], '01', $t[1]));
                $dataInicial = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
                $dataFinal = date('Y-m-d', mktime(0, 0, 0, date('m'), $ultimo_dia, date('Y')));
            }
        }
        return array(
            'ultimodia' => $ultimo_dia,
            'dataInicial' => $dataInicial,
            'dataFinal' => $dataFinal,
            'dataInicialF' => Helper::formatDate($dataInicial, 'mostrar'),
            'dataFinalF' => Helper::formatDate($dataFinal, 'mostrar'),
            'periodo' => $_GET['periodo'],
            'periodos' => $periodos,
        );
    }

    /**
     * Método para popuplar os campos extrasJSON com algum array padrão
     * @param type array $arrayFonte contendo o default
     * @param type array $arrayExtras os dados já configurados
     * @return type
     */
    public static function extrasJson($arrayFonte, $arrayExtras) {
        $configDefault = self::getJsonConfigDefault();

        if (!is_array($arrayExtras)) {
            $arrayExtras = json_decode($arrayExtras, true);
        }
        if (!is_array($arrayFonte)) {
            $arrayFonte = json_decode($arrayFonte, true);
        }

        $out = $arrayFonte;

        foreach ($arrayFonte as $key => $value) {
            // configurações
            $config[$key] = $configDefault;
            if (is_array($value)) { // se vier config, setar valores enviados
                //Log::logTxt('debug', $key);
                foreach ($configDefault as $k => $v) {
                    //Log::logTxt('debug', $k);
                    $config[$key][$k] = $value[$k]; // valor de value na chave de config
                }
            }

            // valores
            $out[$key] = $value['default'];
            if (isset($arrayExtras[$key])) {
                $out[$key] = $arrayExtras[$key];
            }
        }
        $out['config'] = $config;
        return $out;
    }

    public static function pdfFromHtmlCreate($html, $filename, $paper = ['a4', 'portrait']) {
        $h = Minify::html($html);
        $p = implode(', ', $paper);
        $dompdf = new Dompdf();
        $dompdf->setBasePath(Config::getData('pathView') . '/css');
        $dompdf->loadHtml($h);
        $dompdf->setPaper($p);
        $dompdf->render();
        $pdf = $dompdf->output();
        Helper::saveFile($filename, false, $pdf, 'SOBREPOR');

        // nome do arquivo
        $t = explode('/', $filename);
        $name = $t[count($t) - 1];
        sleep(0.1); // concorrencia disco
        if (file_exists($filename)) {
            return [
                'tmp_name' => $filename,
                'type' => 'application/pdf',
                'name' => $name
            ];
        } else {
            return false;
        }
    }

    /** Valida se a data em questão é um dia útil. Faz leitura de fim de semana, e a tabela de feriados de locale
     * 
     * @param type $data
     */
    public static function isDiaUtil($date) {
        $d = self::formatDate($date);
        $w = date('w', self::dateToMktime($d));
        if ($w === '0' || $w === '6') { // se for sabado ou domingo, já foi... é não util
            return false;
        } else {
            return !self::isFeriado($d); // validar a tabela de feriados
        }
    }

    public static function isFeriado($date) {
        $year = explode('-', $date)[0];
        $d = Helper::formatDate($date, 'mostrar');
        $link = 'https://api.calendario.com.br/?json=true&ano=' . $year . '&token=Y3Jpc3RvZmVyLmJhdHNjaGF1ZXJAZ21haWwuY29tJmhhc2g9MTMzODY3NTU2';
        if (!self::$feriados) {
            self::$feriados = json_decode(self::curlCall($link)->content, true);
            Log::logTxt('feriados', self::$feriados);
        }
        foreach (self::$feriados as $item) {
            if (self::compareString($item['date'], $d)) {
                return true;
            }
        }
        return false;
    }

    public static function getProximoDiaUtil($date, $passado = false) {
        $switch = (($passado) ? '-' : '+');
        $amanha = Helper::dateMoreDays($date, 1, $switch);
        while (!self::isDiaUtil($amanha)) {
            $amanha = Helper::dateMoreDays($amanha, 1, $switch);
        }
        return $amanha;
    }

    /**
     * Método para configurar a tabela conforme o schema do usuario logado.
     * @param type $table
     * @return type
     */
    public static function setTable($table) {
        return $table;
        /*
          $t = Config::getEntidadeName(str_replace('public.', '', self::name2CamelCase($table)));
          $table = (new $t())->getTable();

          if (stripos($table, 'public.') > -1) {
          return $table;
          } else {
          return 'agencia_' . $_SESSION['user']['idAgencia'] . '.' . $table;
          }
         * 
         */
    }

    /**
     * Retorna um valor baseado em um tipo
     * @param type $string
     * @param type $type
     */
    public static function getValByType($string, $type) {
        switch ($type) {
            case 'int':
                $out = filter_var($string, FILTER_VALIDATE_INT);
                if (!$out) {
                    $out = null;
                }
                break;
            case 'double':
                $out = filter_var($string, FILTER_VALIDATE_FLOAT);
                if (!$out) {
                    $out = null;
                }
                break;
            default:
                $out = filter_var($string, FILTER_SANITIZE_STRING);
                break;
        }
        return $out;
    }

    public static function dateDiff($date) {
        $datatime1 = new DateTime($date);
        $datatime2 = new DateTime();

        //$data1 = $datatime1->format('Y-m-d H:i:s');
        //$data2 = $datatime2->format('Y-m-d H:i:s');

        $diff = $datatime1->diff($datatime2);
        $horas = $diff->i . ' min'; // + ($diff->days * 24);
        if ($diff->h > 0) {
            $horas = $diff->h . 'h';
        }
        if ($diff->days > 0) {
            $horas = $diff->days . ' dia' . (($diff->days > 1) ? 's' : '');
        }
        if ($diff->m > 0) {
            $horas = $diff->m . ' mes' . (($diff->m > 1) ? 'es' : '');
        }
        if ($diff->y > 0) {
            $horas = $diff->y . ' ano' . (($diff->y > 1) ? 'es' : '');
        }

        return $horas;
    }

    public static function httpsForce() {
        if (!Config::getData('dev')) {
            if ($_SERVER["HTTPS"] != "on") {
                header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
                exit();
                die();
            }
        }
    }

    public static function zipDir($dir, $zipname) {
        //// ZIPAR PASTA INTEIRA
        $rootPath = realpath($dir);

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    }

    /**
     * Deletara os arquivos criados em /app/file para exibição, com mais de 15 minutos de criado
     */
    public static function deleteFilesTemp() {

        $dirs = [
            ['time' => 55, 'dir' => Config::getData('path') . '/app/d/'],
            ['time' => 15, 'dir' => Config::getData('path') . '/app/zip/'],
        ];

        foreach ($dirs as $item) {
            echo $item['dir'] . PHP_EOL;
            // Arquivos temporarios para download
            $dir = $item['dir'];
            $toRemove = time() - (60 * $item['time']); // 15minutos
            if ($handle = opendir($dir)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file !== '..' && $file !== '.' && $file !== 'index.php' && filectime($dir . $file) < $toRemove) {
                        Log::logTxt('unlink-file', $dir . $file);
                        unlink($dir . $file);
                    }
                }
            }
        }

        // Criar arquivo de labels caso não exista
        $f = Config::getData('path') . '/app/_cfg/_labels.php';
        Helper::saveFile($f, false, '{}'); // criar caso nao exista pra evitar erros
    }

    public static function getExtrasConfigDefault($grid = 'col-sm-6', $type = 'text', $class = '', $ro = 'false', $tip = '', $list = '', $label = false, $default = false) {
        $out = [
            'grid' => $grid,
            'type' => $type,
            'class' => $class,
            'ro' => (string) $ro,
            'tip' => $tip,
            'list' => $list,
            'label' => $label,
        ];
        if ($default) {
            $out['default'] = $default;
        }
        return $out;
    }

    /**
     * 
     * @param type $label
     * @param type $grid
     * @param type $type
     * @param type $tip
     * @param type $list
     * @param type $ro
     * @param type $class
     * @param type $default
     * @return type
     */
    public static function getExtrasConfig($label = false, $grid = 'col-sm-6', $type = 'text', $tip = '', $list = '', $ro = 'false', $class = '', $default = '') {
        return self::getExtrasConfigDefault($grid, $type, $class, $ro, $tip, $list, $label, $default);
    }

}
