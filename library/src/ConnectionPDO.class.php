<?php

if (!defined('SISTEMA_LIBRARY')) {
    die('Acesso direto não permitido');
}

class ConnectionPDO implements ConnectionInterface {

    private $con; // garantir o singleton
    public $query;
    public $result;
    public $numRows;
    public $error;
    public $dd;
    public $lastInsertId;
    private $type;
    private static $transaction_in_progress;
    private static $QUERY_GET_PRIMARY = 'SELECT a.attname AS chave_pk
                FROM pg_class c
                  INNER JOIN pg_attribute a ON (c.oid = a.attrelid)
                  INNER JOIN pg_index i ON (c.oid = i.indrelid)
                WHERE
                  i.indkey[0] = a.attnum AND
                  i.indisprimary = "t" AND
                  c.relname = "%s"';
    private static $QUERY_GET_RELACIONAMENTOS = 'SELECT a.attname AS atributo, clf.relname AS tabela_ref,   
                af.attname AS atributo_ref   
              FROM pg_catalog.pg_attribute a   
                JOIN pg_catalog.pg_class cl ON (a.attrelid = cl.oid AND cl.relkind = "r")
                JOIN pg_catalog.pg_namespace n ON (n.oid = cl.relnamespace)   
                JOIN pg_catalog.pg_constraint ct ON (a.attrelid = ct.conrelid AND   
                     ct.confrelid != 0 AND ct.conkey[1] = a.attnum)   
                JOIN pg_catalog.pg_class clf ON (ct.confrelid = clf.oid AND clf.relkind = "r")
                JOIN pg_catalog.pg_namespace nf ON (nf.oid = clf.relnamespace)   
                JOIN pg_catalog.pg_attribute af ON (af.attrelid = ct.confrelid AND   
                     af.attnum = ct.confkey[1])   
              WHERE   
                cl.relname = "%s"';

    public function __construct($type) {
        $this->type = $type;
        $this->open();
        //$this->executeQuery("SET character_set_results=utf8");
        //$this->executeQuery("SET character_set_client=utf8");
    }

    /**
     * Ira chamar os método não atualizados dos adapters
     * @param type $name
     * @param type $arguments
     * @return type
     */
    public function __call($name, $arguments) {
        $this->open();
        Log::logTxt('__CALL', "$name $arguments[0]");
        return $this->con->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
    }

    // gravar o conector em session, vai garantir uma unica conexão no sistema todo, exceto se solicitado explicitamente new
    public function open() {
        if (!$this->con) {
            try {
                //Log::logTxt('query-debug', 'open connection');
                switch ($this->type) {
                    case 'postgres':
                        $stringConnection = "pgsql:host=" . Config::getData('database', 'host') . ";port=" . Config::getData('database', 'port') . ";dbname=" . Config::getData('database', 'database') . ";user=" . Config::getData('database', 'user') . ";password=" . Config::getData('database', 'pwd');
                        $this->con = new PDO($stringConnection);
                        $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $this->con->exec("set schema '" . Config::getData('database', 'schema') . "'");
                        //$this->con->exec("create extension if not exists unaccent");
                        //$this->con->exec("SET application.name TO '" . $_SESSION['user']['nomeUsuario'] . "'");
                        //$this->con->exec("SET application.usuario TO '" . $_SESSION['user']['idUsuario'] . "'");
                        break;
                    case 'mysql':
                        $stringConnection = 'mysql:dbname=' . Config::getData('database', 'database') . ';host=' . Config::getData('database', 'host');
                        $this->con = new PDO($stringConnection, Config::getData('database', 'user'), Config::getData('database', 'pwd'));
                        // Para manter compatiblidade utf-8. Se tirar causa erro de acentuação no mysql
                        $this->con->exec("SET character_set_results=utf8");
                        $this->con->exec("SET character_set_client=utf8");
                        $this->con->exec("DROP FUNCTION IF EXISTS 'unaccent';

DELIMITER //
CREATE FUNCTION 'unaccent'('str' TEXT)
    RETURNS text
    LANGUAGE SQL
    DETERMINISTIC
    NO SQL
    SQL SECURITY INVOKER
    COMMENT ''

BEGIN

    SET str = REPLACE(str,'Š','S');
    SET str = REPLACE(str,'š','s');
    SET str = REPLACE(str,'Ð','Dj');
    SET str = REPLACE(str,'Ž','Z');
    SET str = REPLACE(str,'ž','z');
    SET str = REPLACE(str,'À','A');
    SET str = REPLACE(str,'Á','A');
    SET str = REPLACE(str,'Â','A');
    SET str = REPLACE(str,'Ã','A');
    SET str = REPLACE(str,'Ä','A');
    SET str = REPLACE(str,'Å','A');
    SET str = REPLACE(str,'Æ','A');
    SET str = REPLACE(str,'Ç','C');
    SET str = REPLACE(str,'È','E');
    SET str = REPLACE(str,'É','E');
    SET str = REPLACE(str,'Ê','E');
    SET str = REPLACE(str,'Ë','E');
    SET str = REPLACE(str,'Ì','I');
    SET str = REPLACE(str,'Í','I');
    SET str = REPLACE(str,'Î','I');
    SET str = REPLACE(str,'Ï','I');
    SET str = REPLACE(str,'Ñ','N');
    SET str = REPLACE(str,'Ò','O');
    SET str = REPLACE(str,'Ó','O');
    SET str = REPLACE(str,'Ô','O');
    SET str = REPLACE(str,'Õ','O');
    SET str = REPLACE(str,'Ö','O');
    SET str = REPLACE(str,'Ø','O');
    SET str = REPLACE(str,'Ù','U');
    SET str = REPLACE(str,'Ú','U');
    SET str = REPLACE(str,'Û','U');
    SET str = REPLACE(str,'Ü','U');
    SET str = REPLACE(str,'Ý','Y');
    SET str = REPLACE(str,'Þ','B');
    SET str = REPLACE(str,'ß','Ss');
    SET str = REPLACE(str,'à','a');
    SET str = REPLACE(str,'á','a');
    SET str = REPLACE(str,'â','a');
    SET str = REPLACE(str,'ã','a');
    SET str = REPLACE(str,'ä','a');
    SET str = REPLACE(str,'å','a');
    SET str = REPLACE(str,'æ','a');
    SET str = REPLACE(str,'ç','c');
    SET str = REPLACE(str,'è','e');
    SET str = REPLACE(str,'é','e');
    SET str = REPLACE(str,'ê','e');
    SET str = REPLACE(str,'ë','e');
    SET str = REPLACE(str,'ì','i');
    SET str = REPLACE(str,'í','i');
    SET str = REPLACE(str,'î','i');
    SET str = REPLACE(str,'ï','i');
    SET str = REPLACE(str,'ð','o');
    SET str = REPLACE(str,'ñ','n');
    SET str = REPLACE(str,'ò','o');
    SET str = REPLACE(str,'ó','o');
    SET str = REPLACE(str,'ô','o');
    SET str = REPLACE(str,'õ','o');
    SET str = REPLACE(str,'ö','o');
    SET str = REPLACE(str,'ø','o');
    SET str = REPLACE(str,'ù','u');
    SET str = REPLACE(str,'ú','u');
    SET str = REPLACE(str,'û','u');
    SET str = REPLACE(str,'ý','y');
    SET str = REPLACE(str,'ý','y');
    SET str = REPLACE(str,'þ','b');
    SET str = REPLACE(str,'ÿ','y');
    SET str = REPLACE(str,'ƒ','f');


    RETURN str;
END
//
DELIMITER ;");
                        break;
                }
            } catch (PDOException $e) {
                Log::logTxt('error', __METHOD__ . ' ERROR: Connection failed: ' . $e->getTraceAsString());
                //echo '<br/>ERROR: Connection failed:<br/> ' . $e->getMessage();
                echo '<p class="alert alert-error text-center">'
                . 'ERROR: Connection Failed (CPD-167)<br/>' . $e->getMessage()
                . '</p>';
                die();
            }
        }
        $this->con->exec("SET audit.username TO '" . $_SESSION['user']['nomeUsuario'] . "'");
        $this->con->exec("SET audit.userid TO '" . $_SESSION['user']['idUsuario'] . "'");
    }

    public function close() {
        pg_close($this->con);
    }

    public function begin_transaction() {
        if (!self::$transaction_in_progress) {
            $this->executeQuery('START TRANSACTION');
            self::$transaction_in_progress = true;
            register_shutdown_function(array($this, "__shutdown_check"));
        }
    }

    public function __shutdown_check() {
        if (self::$transaction_in_progress) {
            $this->rollback();
        }
    }

    public function commit() {
        $this->executeQuery("COMMIT");
        self::$transaction_in_progress = false;
    }

    public function rollback() {
        $this->executeQuery("ROLLBACK");
        self::$transaction_in_progress = false;
    }

    public function autocommit($boolean) {
        $this->con->autocommit($boolean);
    }

    /**
     * Executara um update na tabela com prepared. Os nomes do campos já devem estar no formato da tabela, sem camelcase
     * @param type $table
     * @param type $array
     * @param type $cpoWhere
     * @return boolean
     * @throws SistemaException
     */
    public function insert($table, $array, $nomeCpoId, $onConflict = '') {
        $preValues = $update = $valores = [];
        foreach ($array as $key => $value) {
            $keys[] = $key;
            $preValues[] = '?';
            $valores[] = $value;
        }
        $query = "INSERT INTO $table (" . implode(',', $keys) . ") VALUES (" . implode(',', $preValues) . ")"
                . " $onConflict "
                . " returning $nomeCpoId as nsnovoid";
        //Log::logTxt('query-insert', $query);
        //Log::logTxt('query-insert', $valores);
        $this->open();
        $res = false;
        $this->numRows = 0;
        $this->result = false;
        $this->error = false;
        try {
            $this->result = $this->con->prepare($query);
            if (!$this->result->execute($valores)) {
                $this->error = $this->result->errorInfo()[2];
                Log::logTxt('query-insert-error', 'ERROR: ' . $this->error);
                throw new SistemaException($this->result->errorInfo()[0] . $this->result->errorInfo()[2], 0);
            }
            return $res;
        } catch (Exception $exc) {
            $this->result = false;
            Log::log('ERROR-SQL', 'Erro ao executar insert', '', Helper::name2CamelCase($table), [
                'data' => $array,
                'backtrace' => $exc->getTraceAsString(),
                'error' => $exc->getMessage()
            ]);
            throw new SistemaException($exc->getMessage() . $query);
        }
    }

    /**
     * Executara um update na tabela com prepared. Os nomes do campos já devem estar no formato da tabela, sem camelcase
     * @param type $table
     * @param type $array
     * @param type $cpoWhere
     * @return boolean
     * @throws SistemaException
     */
    public function update($table, $array, $cpoWhere) {
        $update = $valores = [];
        $idWhere = $array[$cpoWhere];
        unset($array[$cpoWhere]);
        foreach ($array as $key => $value) {
            $valores[] = $value;
            $update[] = "$key=?";
        }
        // where
        $valores[] = $idWhere;
        $query = "update $table set " . implode(',', $update) . " where $cpoWhere=?";
        Log::log('log-update', $message);
        $this->open();
        $res = false;
        $this->numRows = 0;
        $this->result = false;
        $this->error = false;
        try {
            $this->result = $this->con->prepare($query);
            if (!$this->result->execute($valores)) {
                $this->error = $this->result->errorInfo()[2];
                Log::logTxt('query-update', 'ERROR: ' . $this->error);
                throw new SistemaException($this->result->errorInfo()[0] . $this->result->errorInfo()[2], 0);
            }
            return $res;
        } catch (Exception $exc) {
            $this->result = false;
            Log::log('ERROR', 'Erro ao executar update', $idWhere, Helper::name2CamelCase($table), [
                'data' => $array,
                'backtrace' => $exc->getTraceAsString(),
                'error' => $exc->getMessage()
            ]);
            throw new SistemaException($exc->getMessage() . $query);
        }
    }

    public function executeQuery($query, $gravarLog = true) {
        $time = new Eficiencia(__METHOD__);
        $time->setLimits(1, 2);

        $this->open();
        $res = false;
        $this->numRows = 0;
        $this->result = false;
        $this->error = false;
        $this->query = $query;
        $queryLog = $query; //Helper::codifica($query);
        $tipo = explode(" ", $query);

        if (strtolower($tipo[0]) !== 'set' && $gravarLog && stripos($query, 'from app_mensagem') === false) {// && Config::getData('dev')) {
            Log::logTxt('query-debug', $queryLog);
        }
        try {
            $this->result = $this->con->prepare($query);
            if (!$this->result->execute()) {
                $this->error = $this->result->errorInfo()[2];
                Log::logTxt('query-debug', $this->error);
                throw new SistemaException($this->result->errorInfo()[0] . $this->result->errorInfo()[2], 0);
            }
            $this->numRows = $this->result->rowCount();
            if (strtolower($tipo[0]) !== "select") {
                //$this->lastInsertId = $this->con->lastInsertId();
            }
            //$time->end(1);
        } catch (Exception $exc) {
            $this->result = false;
            $this->result = false;
            Log::logTxt('query-error', 'QUERY-ERROR:' . $query);
            Log::log('ERROR', 'Erro ao executar query', '-1', 'Conforme Query', [
                'data' => $query,
                'backtrace' => $exc->getTraceAsString(),
                'error' => $exc->getMessage()
            ]);
            if (stripos($exc->getMessage(), 'ERROR:  function unaccent') > -1) {
                Api::result(403, ['error' => 'DEV:  A EXTESÃO UNACCENT NÃO FOI INSTALADA']);
            }
            throw new SistemaException($exc->getMessage());
        }
        $time->end();
        return $res;
    }

    public function next() {
        try {
            if ($this->result) {
                //$dados = pg_fetch_array($this->result);
                $dados = $this->result->fetch(PDO::FETCH_ASSOC);
                if (is_array($dados)) {
                    return $dados;
                } else {
                    $this->result = false;
                    return false;
                }
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function getRelacionamento($table) {
        $query = sprintf(self::$QUERY_GET_RELACIONAMENTOS, $table);
        $this->executeQuery($query);
        while ($dd = $this->next()) {
            //if ($dd['REFERENCED_TABLE_NAME'] != $table) { // evitar autorelacionamento
            $out[] = array(
                'tabela' => $dd['tabela_ref'],
                'cpoOrigem' => $dd['atributo'],
                'cpoRelacao' => $dd['atributo_ref']
            );
            //}
        }
        return $out;
    }

}
