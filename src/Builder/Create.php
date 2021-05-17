<?php

namespace NsLibrary\Builder;

use ControllerCreate;
use NsLibrary\Config;
use NsLibrary\Connection;
use NsUtil\Helper;
use SistemaCreate;
use function mb_strtolower;

class Create {

    private $querys;
    private $rotas;

    /**
     * Método para criação das entidades ORM conforme constam no banco de dados Postgresql
     * @param array $schemasLoad
     */
    public function __construct(array $schemasLoad = ['public']) {
        $database = Config::getData('database')['dbname'];

        // Schemas a ler
        $schemasLoad = array_map(function($val) {
            return "'$val'";
        }, $schemasLoad);
        $schemas = implode(',', $schemasLoad);

        $this->querys = [
            'listTables' => "SELECT schemaname, tablename FROM pg_catalog.pg_tables WHERE schemaname in (" . $schemas . ") ORDER BY tablename",
            'getEstruturaTable' => "select * from information_schema.columns WHERE table_name= '%s' and table_schema in (" . $schemas . ")",
            'getComents' => 'SELECT pg_catalog.col_description(c.oid, a.attnum) AS column_comment FROM pg_class c LEFT JOIN pg_attribute a ON a.attrelid = c.oid LEFT JOIN information_schema.columns ws ON ws.column_name = a.attname AND ws.table_name= c.relname '
            . 'WHERE c.relname = \'%s\' AND a.attname= \'%s\' and c.relnamespace in (select oid from pg_catalog.pg_namespace where nspname in (' . $schemas . ')) ',
            'relacionamentos' => "SELECT a.attname AS column_name, clf.relname AS referenced_table_name, af.attname AS referenced_column_name   
        FROM pg_catalog.pg_attribute a   
        JOIN pg_catalog.pg_class cl ON (a.attrelid = cl.oid AND cl.relkind = 'r')
        JOIN pg_catalog.pg_namespace n ON (n.oid = cl.relnamespace)   
        JOIN pg_catalog.pg_constraint ct ON (a.attrelid = ct.conrelid AND   
           ct.confrelid != 0 AND ct.conkey[1] = a.attnum)   
        JOIN pg_catalog.pg_class clf ON (ct.confrelid = clf.oid AND clf.relkind = 'r')
        JOIN pg_catalog.pg_namespace nf ON (nf.oid = clf.relnamespace)   
        JOIN pg_catalog.pg_attribute af ON (af.attrelid = ct.confrelid AND   
           af.attnum = ct.confkey[1])   
        WHERE   
        cl.relname = '%s' and n.oid in (select oid from pg_catalog.pg_namespace where nspname in (" . $schemas . ")) group by a.attname, af.attname, clf.relname"
        ];
    }

    public function run() {
        $con = Connection::getConnection();
        $this->entidadesInit();

        $tipos = array(
            //numeros
            'bigint' => 'int',
            'integer' => 'int',
            'tinyint' => 'int',
            'smallint' => 'int',
            'mediumint' => 'int',
            'double' => 'double',
            'float' => 'double',
            'decimal' => 'double',
            'numeric' => 'double',
            //textos
            'varchar' => 'string',
            'char' => 'string',
            'blob' => 'string',
            'clob' => 'string',
            'bool1' => 'string',
            'character' => 'string',
            'longvarchar' => 'string',
            'character varying' => 'string',
            'timestamp without time zone' => 'timestamp',
            'time without time zone' => 'int',
            'enum' => 'string',
        );

        $rota = [
            "['prefix' => '/', 'archive' => 'App/index.php']",
            "['prefix' => '/home', 'archive' => 'App/index.php']",
            "['prefix' => '/index.php', 'archive' => 'App/index.php']",
            "['prefix' => '/login', 'archive' => 'login.php']",
            "['prefix' => '/logout', 'archive' => 'logout.php']",
            "['prefix' => '/reset', 'archive' => 'reset.php']",
            "['prefix' => '/about', 'archive' => 'about.php']",
            "['prefix' => '/versao', 'archive' => 'versao.php']",
            "['prefix' => '/Teste', 'archive' => 'Teste/index.php']",
            "['prefix' => '/recovery', 'archive' => 'App/passwordRecovery.php']"
        ];

        $defaults = array('CURRENT_TIMESTAMP' => '', '\'{}\'::jsonb' => "'{}'", '::text' => '', '::character varying' => '', '::bpchar' => '', 'now()' => "date('Y-m-d H:i:s')", 'nextval' => '');
        $prefixos = array('mem_', 'sis_', 'anz_', 'aux_', 'app_');
        $query = $this->querys;


        // Obter tabelas
        $tabelas = [];
        $list = $con->execQueryAndReturn($query['listTables']);
        foreach ($list as $item) {
            $tabelas[$item['schemaname'].'.'.$item['tablename']] = $item['tablename'];
        }


        $libraryEntities = $hints = $libraryFields = []; // armazenara um arquivo com as etiquetas para os nomes dos campos
        $aliases = $camposDouble = [];
        if (count($tabelas) === 0) {
            echo '<div class="alert alert-danger text-center">'
            . 'ERROR!<br/>Nenhuma tabela na base de dados'
            . '</div>';
            die();
        }
        foreach ($tabelas as $schemaTable => $tabela) {
            $estrutura = $entidade = $atributos = $table = $temp = $declaracao = $out = $relacionamentos = false;
            $camposDate = [];
            $camposDouble = [];
            $camposJson = [];

            // obter nome da entidade
            $myent = $entidade = Helper::name2CamelCase($tabela, $prefixos);
            if (is_numeric($entidade[0])) {
                $entidade = '_' . $entidade;
            }
            $entidade[0] = strtoupper($entidade[0]);
            $menu[] = "['label' => Config::getData('titlePagesAliases', '$entidade'), 'link' => Config::getData('url') .'/$entidade', 'icon' => 'angle-right', 'dropdown' => false]";

            $rota[] = "['prefix' => '/$myent', 'archive' => '$myent.php']";

            // aliases para tabela
            $aliases[] = "
            '" . mb_strtolower($entidade) . "' => '" . ucwords(str_replace('_', ' ', $tabela)) . "s'";
            $libraryEntities[] = "
            '" . mb_strtolower($entidade) . "' => '" . $entidade . "'";

            //$libraryEntities
            // Obter atributos da tabela
            $con->executeQuery(sprintf($query['getEstruturaTable'], $tabela));
            while ($dd = $con->next()) {
                foreach ($dd as $key => $value) {
                    $dd[strtolower($key)] = $value;
                }
                $estrutura[] = $dd;
            }
            if (!$estrutura) {
                echo '<br/>TABELA ' . $tabela . ' NÃO POSSUI ATRIBUTOS. ENTIDADE NÃO CRIADA';
                continue;
            }


            // obter nome dos atributos
            foreach ($estrutura as $key => $detalhes) {
                // Campo ID:
                if ($detalhes['ordinal_position'] === 1 || $detalhes['column_key'] === 'PRI') {
                    $cpoID = $detalhes['column_name'];
                }

                // corrigir tipo do atributo para php
                foreach ($tipos as $key => $val) {
                    if ($detalhes['data_type'] === $key) {
                        $detalhes['data_type'] = $val;
                    }
                }

                if ($detalhes['data_type'] === 'date' || $detalhes['data_type'] === 'timestamp') {
                    $camposDate[] = "'" . Helper::name2CamelCase($detalhes['column_name']) . "'";
                }
                if ($detalhes['data_type'] === 'double') {
                    $camposDouble[] = "'" . Helper::name2CamelCase($detalhes['column_name']) . "'";
                }
                if ($detalhes['data_type'] === 'json' || $detalhes['data_type'] === 'jsonb') {
                    $camposJson[] = "'" . Helper::name2CamelCase($detalhes['column_name']) . "'";
                }


                // corrigir valores padrões
                foreach ($defaults as $key => $value) {
                    $detalhes['column_default'] = str_replace($key, $value, $detalhes['column_default']);
                }

                // comentários do campo
                if (isset($query['getComents'])) {
                    $con->executeQuery(sprintf($query['getComents'], $tabela, $detalhes['column_name']));
                    $extras = $con->next();
                    $c = explode('|', $extras['column_comment']);
                    $detalhes['column_comment'] = $c[0];
                    $detalhes['hint'] = ((strlen($c[1]) > 1) ? $c[1] : false);
                }

                if (strlen($detalhes['column_comment']) === 0) {
                    $detalhes['column_comment'] = str_replace('_' . strtolower($entidade), '', $detalhes['column_name']);
                    $detalhes['column_comment'] = str_replace('_', ' ', $detalhes['column_comment']);
                }

                $chaveField = mb_strtolower(Helper::name2CamelCase($detalhes['column_name']));
                $chaveHint = mb_strtolower($entidade . '_' . $chaveField);

                $libraryFields[] = "'" . $chaveField . "' => '" . $detalhes['column_comment'] . "'";
                if (strlen($detalhes['hint']) > 1) { // se hint existir, salve
                    $hints[] = "'" . $chaveHint . "' => '" . $detalhes['hint'] . "'";
                }



                // Criação do atributo
                $atributos[] = array(
                    'entidade' => $entidade,
                    'key' => (($detalhes['ordinal_position'] === 1 || $detalhes['column_key'] === 'PRI') ? true : false),
                    'nome' => Helper::name2CamelCase($detalhes['column_name']),
                    'tipo' => $detalhes['data_type'],
                    'maxsize' => (($detalhes['character_maximum_length']) ? $detalhes['character_maximum_length'] : 1000000000),
                    'valorPadrao' => (($detalhes['column_default'] != '' && $detalhes['ordinal_position'] > 1) ? $detalhes['column_default'] : "''"),
                    'coments' => (($detalhes['column_comment']) ? $detalhes['column_comment'] : Helper::name2CamelCase($detalhes['column_name'])),
                    'notnull' => (($detalhes['is_nullable'] === 'NO') ? true : false),
                    'hint' => $detalhes['hint']
                );
            }

            //Relacionamentos
            unset($relacoes);
            $con->executeQuery(sprintf($query['relacionamentos'], $tabela));
            while ($dd = $con->next()) {
                foreach ($dd as $key => $value) {
                    $dd[strtolower($key)] = $value;
                }
                // obter nome da entidade
                $entidadeRef = Helper::name2CamelCase($dd['referenced_table_name'], $prefixos);

                // != $tabela: evitara o autorelacionamento, pois gera exaustao de memória
                if ($dd['referenced_table_name'] != $tabela) {
                    $relacoes[] = "array('tabela'=>'" . $dd['referenced_table_name'] . "', 'cpoOrigem'=>'" . $dd['column_name'] . "', 'cpoRelacao'=>'" . $dd['referenced_column_name'] . "')";
                    $atributos[] = array(
                        'key' => false,
                        'nome' => $entidadeRef,
                        'tipo' => (((Helper::compareString(substr($dd['referenced_table_name'], 0, 3), 'ce_'))) ? 'EXTERNA' : 'OBJECT'),
                        'valorPadrao' => 'new ' . ucwords($entidadeRef) . '()',
                        'coments' => 'Relação com entidade ' . $dd['referenced_table_name'] . ' @JoinColumn(name=\'' . $dd['referenced_column_name'] . '\')',
                        'notnull' => 'false'
                    );
                }
            }


            $dados = array(
                'schemaTable' => $schemaTable, 
                'tabela' => $tabela,
                'cpoID' => Helper::name2CamelCase($cpoID),
                'entidade' => $entidade,
                'estrutura' => $estrutura,
                'atributos' => $atributos,
                'relacionamentos' => $relacoes,
                'camposDate' => implode(', ', $camposDate),
                'camposDouble' => implode(', ', $camposDouble),
                'camposJson' => implode(', ', $camposJson),
                'arrayCamposJson' => $camposJson,
            );



            $out = array();

            ### Criação de entidade
            EntidadesCreate::save($dados, $entidade);
            
            continue;

            ### Criação de controller


                /*
            ### Criação do ambiente administração
            $template = SistemaCreate::getList($dados);
            $index = file_get_contents('./templates/template-index-component.php');
            $dados['AUX'] = $template['aux'];
            $dados['SETDATA'] = $template['setdata'];
            $dados['filtros'] = $template['filtros'];
            $dados['MODAL_DATA'] = $template['MODAL_DATA'];
            Helper::saveFileBuild(__DIR__ . "/_sourceView/$entidade/index.php", $template['div']);
            Helper::saveFileBuild(__DIR__ . "/_sourceView/$entidade/script.js", SistemaCreate::getJs($dados));
            Helper::saveFileBuild(__DIR__ . "/_sourceView/$entidade/view.php", '<?php include \'../../../library/SistemaLibrary.php\'; ?>');

            // Salvando na rota nova, com PHP no server
            Helper::saveFileBuild(Config::getData('path') . "/view/fonte/$myent.php", $template['div']);
            Helper::saveFileBuild(Config::getData('path') . "/_build/js_app/$entidade-script.js", SistemaCreate::getJs($dados));
            */


            ### Criação dos components
            ##  saidas
            $outGeral[] = "Arquivos criados para entidade $entidade";
        } // FECHA FOREACH TABELAS
    }

    private function loadData() {
        
    }

    private function entidadesInit() {
        Config::setData('pathEntidades', Config::getData('path') . '/src/NsLibrary/Entidades');
        // Remover diretório de entidades, caso exista
        Helper::deleteDir(Config::getData('pathEntidades'));
        sleep(0.2);
        Helper::mkdir(Config::getData('pathEntidades'));
    }

    private function controllerInit() {
        // Remover diretório de entidades, caso exista
        Helper::deleteDir(Config::getData('path') . '/src/NsLibrary/Controller');
        Helper::mkdir(Config::getData('path') . '/src/NsLibrary/Controller');
    }

    private function viewInit() {
        
    }

}
