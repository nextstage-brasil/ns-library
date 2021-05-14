<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

// Classe utilizada para medir o tempo de execução e consumo de recursos de um determinado bloco de programas
class Eficiencia {

    private $start;
    private $memoryStart;
    private $end;
    private $result;
    private $nomeTeste;
    private $registrar;
    public $limiteTIME = 2;
    public $limiteMEMORY = 20;

    public function __construct($name = false, $registrar = false) {
        $this->end = 0;
        $this->result = new stdClass();
        $this->start();
        $b = debug_backtrace();
        krsort($b);
        $this->registrar = $registrar;
        $t = [];
        foreach ($b as $value) {
            if (!Helper::compareString($value['class'], 'Eficiencia')) {
                $t[] = "$value[class]::$value[function]";
            }
        }
        $this->nomeTeste = (($name) ? '{' . $name . '}' : '') . '%s ' . implode(' > ', $t) . ' %s';

        //register_shutdown_function(array($this, "__shutdown_check"));
    }

    public function __shutdown_check() {
        if ($this->start !== false) {
            $this->end();
        }
    }

    public function setLimits($time, $memory) {
        $this->limiteTIME = $time;
        $this->limiteMEMORY = $memory;
        return $this;
    }

    function start() {
        list($usec, $sec) = explode(' ', microtime());
        $this->start = (float) $sec + (float) $usec;
        $this->memoryStart = round(((memory_get_usage() / 1024) / 1024), 2);
    }

    function end($limit = false, $memoryLimit = false) {
        if (!$this->start)   {
            $std = new stdClass();
            $std->text = 'EFICIENCIA: START NÃO REGISTRADO';
            $b = debug_backtrace();
            Log::logTxt('ERROR-EFICIENCIA', var_export($b, true));
            return $std;
        }
        if (!$limit) {
            $limit = $this->limiteTIME;
        }
        if (!$memoryLimit) {
            $memoryLimit = $this->limiteMEMORY;
        }
        $extras = [];
        
        

        list($usec, $sec) = explode(' ', microtime());
        $this->end = (float) $sec + (float) $usec;
        $this->result->time = number_format(round($this->end - $this->start, 5), 2);
        //echo "[$this->start - $this->end ? " . $this->result->time . "  ||  ";
        $this->result->memory = round(round(((memory_get_usage() / 1024) / 1024), 2) - $this->memoryStart, 2);
        $limit = ((Config::getData('dev')) ? $limit + 1 : $limit);
        $this->result->text = ''
                . 'Tempo de execução: ' . $this->result->time . ' segundos (Limite: ' . $limit . 's). '
                . 'Memória utilizada: ' . round($this->result->memory, 2) . 'MB. (Limite: ' . $memoryLimit . 'MB)';
        // controle em logs para execuções lentas

        if (($this->result->time > ($limit * 1.0)) || $this->registrar || ($this->result->memory > $memoryLimit)) {
            $b = debug_backtrace();
            krsort($b);
            $log[] = '';
            global $DATA_SENDER;
            $extras['DATA'] = $DATA_SENDER;
            unset($extras['DATA']['password']);
            unset($extras['DATA']['senha']);
            $extras['LIMITES'] = ['Tempo' => $limit, 'Memória' => $memoryLimit];
            $extras['BACKTRACE'] = $b;

            $log = implode("<br/>", $log);
            $pre = (($this->result->time > $limit) ? '[TIME] ' : '');
            $pre .= (($this->result->memory > $memoryLimit) ? '[MEMORY] ' : '');
            Log::log('lentidao', sprintf($pre . $this->nomeTeste, $this->result->text, $log), 0, '', $extras);
        }
        $this->start = false;
        return $this->result;
    }

    function getResult() {
        return $this->result;
    }

}
