<?php

// Classe para inserção de components

class Component {

    public static function init($name, $print = true) {
        if (is_array($name)) {
            foreach ($name as $value) {
                $ret .= self::init($value, $print);
                if (!$ret) { // retornar false caso algum componente nao exusta
                    return false;
                }
            }
            if ($print) {
                return true;
            } else {
                return $ret;
            }
        }
        $ret = self::getContent($name);
        if (strlen($ret) < 5) {
            $out = [
                'Nome enviado' => $name,
                'Nome identificado' => self::formatName($name)
            ];
            \Log::error('Conteudo de component não localizado: ' . var_export($out, true));
            return false;
        }
        $out = "<script>" . $ret . "</script>";
        if ($print) {
            echo $out;
        }
        return $out;
    }

    private static function getContent($n) {
        $name = self::formatName($n);
        if (!$_SESSION['app_cache_js'][$name] || Config::getData('dev')) {
            $file = realpath(Config::getData('dirComponents') . "/$name");
            $_SESSION['app_cache_js'][$name] = file_get_contents($file);
        }
        return $_SESSION['app_cache_js'][$name];
    }

    /**
     * @update 03/04/2020
     * @param type $filename
     * @return string
     */
    public static function formatName($filename) {
        $f = mb_strtolower(realpath($filename));
        $f = str_replace(['.js', '.html'], '', $filename);
        $t = explode(DIRECTORY_SEPARATOR, $f);
        $name = md5(array_pop($t)) . '.js';
        //echo "$name\r\n";
        return $name;
    }

    /**
     * @update 03/04/2020
     * Compila e salva na pasta adequada da aplicação
     * @param type $filename
     */
    public static function compileAndSaveJS($filename) {
        $name = self::formatName($filename);
        $js = file_get_contents($filename);
        self::removeConsole($js);
        $js_compile = (new Packer($js, 'Normal', true, false, true))->pack();
        Helper::saveFile(Config::getData('dirComponents') . '/' . $name, '', $js_compile, 'SOBREPOR');
    }

    static function saveComponent($filename) {
        $filename = realpath($filename);
        $name = self::formatName($filename);
        $dir = explode(DIRECTORY_SEPARATOR, $filename);
        $name_fonte = str_replace(['.js', '.html'], '', array_pop($dir));
        $diretorio_fonte = implode(DIRECTORY_SEPARATOR, $dir);
        $html = file_get_contents($diretorio_fonte . DIRECTORY_SEPARATOR . $name_fonte . '.html');
        $js = file_get_contents($diretorio_fonte . DIRECTORY_SEPARATOR . $name_fonte . '.js');

        // tratamentos HTML
        $html = Minify::html($html);
        Helper::saveFile(Config::getData('dirComponents') . '/' . str_replace('.js', '.html', $name), '', $html, 'SOBREPOR');

        // tratamento JS
        $js = str_replace($name_fonte . '.html', str_replace('.js', '.html', $name), $js);
        self::removeConsole($js);
        $js = Minify::js($js);
        $js = (new Packer($js, 'Normal', true, false, true))->pack();
        Helper::saveFile(Config::getData('dirComponents') . '/' . $name, '', $js, 'SOBREPOR');
    }

    static function removeConsole(&$input) {
        if ($_GET['compileToBuild'] === 'YES') {
            $t = str_replace('console', '//console', $input);
            $input = $t;
        }
        $input = str_replace("alert(", "Swal.fire(", $input);
        $input = str_replace("alert_JS(", "alert(", $input);
    }

    public static function packAndPrint($js) {
        $packer = new Packer($js, 'Normal', true, false, true);
        $packed_js = $packer->pack();
        echo "<script>$packed_js</script>";
    }

}
