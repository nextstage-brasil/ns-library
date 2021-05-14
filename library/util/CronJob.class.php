<?php

//https://crontab-generator.org/

class CronJob {

    private $ef, $title;
    private $fileExclusive; // nome do arquivo que mantem exclusividade de execucao apenas uma vez

    public function __construct($title, $limits = [2, 20]) {
        Log::log('cron', 'CRONJOB-Start: ' . $title);
        $this->title = $title;
        $this->ef = new Eficiencia($title);
        $this->ef->setLimits($limits[0], $limits[1]);
        $this->fileExclusive = Config::getData('path') . '/app/_ctex/' . md5($title);
        if (!file_exists($this->fileExclusive)) {
            Helper::saveFile($this->fileExclusive, false, '2' . $title, 'SOBREPOR');
        }
    }

    public function __destruct() {
        $this->endExclusive();
        $t = $this->ef->end()->text;
        Log::log('cron', 'CRONJOB-End - ' . $t);
    }

    /**
     *  controle para apenas uma execução por vez. Caso um crontab passe do limite de 1min, pula pro próximo
     * 2: parado
     * 1: em atividade
     */
    public function setExclusive() {
        $content = (int) file_get_contents($this->fileExclusive);
        if ($content !== 2) {
            Log::error(__METHOD__ . ' Processos concorrentes chocando. Title:' . $title);
            die('Existe outro processo em execução');
            exit;
        }

        Helper::saveFile($this->fileExclusive, false, 1, 'SOBREPOR'); // iniciar exclusividade desta operação
        return $this;
    }

    public function endExclusive() {
        Helper::saveFile($this->fileExclusive, false, 2, 'SOBREPOR');
        return $this;
    }
    
   

}
