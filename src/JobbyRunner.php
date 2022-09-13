<?php

namespace NsLibrary;

use Closure;
use Jobby\Jobby;
use NsUtil\Helper;
use NsUtil\UniqueExecution;

class JobbyRunner {

    private $jobby, $logError, $config;

    public function __construct(string $pathToLog) {
        // logError
        $this->logError = $pathToLog;
        Helper::directorySeparator($this->logError);
        Helper::mkdir($this->logError);
        $this->logError .= DIRECTORY_SEPARATOR . 'NS_JobbyRunner.log';

        // Jobby
        $this->jobby = new Jobby($this->getJobbyConfig());
    }

    public function run(): array {
        $onlyOne = new UniqueExecution(md5(__FILE__));
        if ($onlyOne->isRunning(5)) {
            $this->response(['error' => 'Its Running. Started at ' . date('c', $onlyOne->getStartedAt())]);
        }

        /*
          // Carregar jobs
          $name = 'ns-unique-teste';
          $description = 'Basic testes execution to log';
          $maxTimeExecution = 1;
          $schedule = '* * * * *';
          $isEnable = true;
          $cmd = '';
          $closure = function () {
          echo date('c') . ' - jobby_test' . PHP_EOL;
          return true;
          };
          $this->add($name, $description, $maxTimeExecution, $schedule, $isEnable, $cmd, $closure);
         */

        // Executar os arquivos conforme regras
        $this->jobby->run();

        // Retorno
        $ja = [];
        foreach ($this->jobby->getJobs() as $jobs) {
            $ja[] = $jobs[0] . ' | ' . $jobs[1]['schedule'] . ' | enabled: ' . (($jobs[1]['enabled']) ? 'true' : 'false');
        }


        (new UniqueExecution(md5(__FILE__)))->end();
        return ['run' => date('c'), 'jobs' => count($this->jobby->getJobs()), 'j' => $ja];
    }

    /**
     * 
     * @param type $maxRuntime Minutes
     * @return array
     */
    private function getJobbyConfig($maxRuntime = 10): array {
        $configJobby = ['output' => $this->logError];
        if (Helper::getSO() !== 'windows') {
            $configJobby['maxRuntime'] = (60 * $maxRuntime);
        }
        return $configJobby;
    }

    /**
     * 
     * @param string $name Referencia a ser localizada no arquivo config, caso necessario
     * @param string $description Descrição da operação
     * @param int $maxTimeExecution Tempo máximo de execução deste job
     * @param string $schedule '* * * * *' Expressão crontab para controle de execução
     * @param bool $isEnable Definição de esta habilitado ou não
     * @param string $cmd Comando para execução, caso não seja uma função
     * @param Closure $closure Função para execução
     * @return void
     */
    public function add(
            string $name,
            string $description,
            int $maxTimeExecution,
            string $schedule,
            bool $isEnable,
            string $cmd = '',
            Closure $closure = null
    ): JobbyRunner {
        $extras = [
            'schedule' => $schedule,
            'enabled' => $isEnable,
            'cmd' => $cmd
        ];
        if ($closure instanceof Closure) {
            unset($extras['cmd']);
            $extras['closure'] = $closure;
        }
        $this->jobby->add($description, array_merge($this->getJobbyConfig($maxTimeExecution), $extras));
        return $this;
    }

}
