<?php

namespace NsLibrary\Builder;

use NsLibrary\Config;
use NsLibrary\Connection;
use NsUtil\Helper;
use NsUtil\StatusLoader;
use function mb_strtolower;

class Create
{

    private $querys;
    private $rotas;
    private $data;
    private $onlyGetData = false;
    private $quiet = true;
    private array $prefixos;
    public array $tablesToIgnore = ['_execution_lock', 'spatial_ref_sys'];

    /**
     * Método para criação das entidades ORM conforme constam no banco de dados Postgresql
     * @param array $schemasLoad
     */
    public function __construct(array $schemasLoad = ['public'], array $prefixos = ['mem_', 'sis_', 'anz_', 'aux_', 'app_'])
    {
        $database = Config::getData('database')['dbname'];

        $this->prefixos = $prefixos;

        // Schemas a ler
        $schemasLoad = array_map(function ($val) {
            return "'$val'";
        }, $schemasLoad);
        $schemas = implode(',', $schemasLoad);

        $this->querys = [
            'listTables' => "SELECT schemaname, tablename FROM pg_catalog.pg_tables WHERE tablename not in (" . implode(',', array_map(fn ($item) => "'$item'", $this->tablesToIgnore)) . ") and  schemaname in (" . $schemas . ") ORDER BY tablename",
            'getEstruturaTable' => "select * from information_schema.columns WHERE table_name= '%s' and table_schema in (" . $schemas . ")",
            'getComents' => 'SELECT pg_catalog.col_description(c.oid, a.attnum) AS column_comment FROM pg_class c LEFT JOIN pg_attribute a ON a.attrelid = c.oid LEFT JOIN information_schema.columns ws ON ws.column_name = a.attname AND ws.table_name= c.relname '
                . 'WHERE c.relname = \'%s\' AND a.attname= \'%s\' and c.relnamespace in (select oid from pg_catalog.pg_namespace where nspname in (' . $schemas . ')) ',
            'relacionamentos' => "SELECT nf.nspname as referenced_schema_name, a.attname AS column_name, clf.relname AS referenced_table_name, af.attname AS referenced_column_name   
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
        cl.relname = '%s' and n.oid in (select oid from pg_catalog.pg_namespace where nspname in (" . $schemas . "))
        group by 1,2,3,4",
            'getPrimaryKey' => "SELECT               
                                    pg_attribute.attname, 
                                    format_type(pg_attribute.atttypid, pg_attribute.atttypmod) 
                                  FROM pg_index, pg_class, pg_attribute, pg_namespace 
                                  WHERE 
                                    pg_class.oid = '%s'::regclass AND 
                                    indrelid = pg_class.oid AND 
                                    nspname in (" . $schemas . ") AND 
                                    pg_class.relnamespace = pg_namespace.oid AND 
                                    pg_attribute.attrelid = pg_class.oid AND 
                                    pg_attribute.attnum = any(pg_index.indkey)
                                   AND indisprimary
                                   ;"
        ];
    }

    /**
     * Irá criar as entidades do sistema (models) e os controllers (RestFULL) para o sistema, sempre reescrevendo o diretório total.
     * Use como base para criar seus demais controller
     * @param string $tokenCrypto
     * @param string $appName
     * @param string $htmlTitle
     * @param string $adminName
     * @param string $adminEmail
     * @param array $controllerIgnoreEntites
     */
    public function run(
        string $tokenCrypto = '',
        string $appName = '',
        string $htmlTitle = '',
        string $adminName = '',
        string $adminEmail = '',
        array $controllerIgnoreEntites = []
    ) {
        $con = Connection::getConnection();
        $this->entidadesInit();
        $this->controllerInit();
        $this->data = [];

        // Config default
        $CONFIG = [
            'identificador' => $tokenCrypto, //ATENÇÃO: NÃO ALTERAR APÓS INSTALAÇÃO, POIS OS ARQUIVO SERÃO CRIPTOGRAFADOS UTILIZADO ESTE CAMPO
            'name' => $appName,
            'title' => $htmlTitle,
            'nomeAdmin' => $adminName,
            'emailAdmin' => $adminEmail,
            'timeShowError' => 30, // tempo de exibição de erro na tela da view, antes de mudar a pagina
            'validaIdEmpresa' => false,
            'keyGoogle' => '', // para uso em mapas
            'url' => 'URL IS NOT DEFINED',
            'sendMail' => [
                'host' => 'HOST',
                'email' => 'EMAIL',
                'username' => 'USERNAME',
                'password' => 'PASSWORD',
                'port' => 465,
                'smtpSecure' => 'ssl',
                'SMTPAuth' => true
            ],
            'local' => false,
            'dev' => false,
            'producao' => false,
            'menuPrincipal' => 'nav_default',
            'menuUser' => [
                ['link' => 'logout', 'label' => 'Sair', 'icon' => 'sign-out']
            ],
            'rota' => '',
            'params' => [],
            'path' => '',
            'fileserver' => [
                'StoragePrivate' => 'Local', // define em qua storage deve ser armazenado os arquivos privados. Opções: Local | FileRun | S3 | GCP
                'StoragePublic' => 'Local', // define em qua storage deve ser armazenado os arquivos publicos (thumbs)
                'FileRun' => [ // dados do servidor de armazenamento de arquivos, para uso na API
                    'url' => '',
                    'client_id' => '',
                    'client_secret' => '',
                    'username' => '',
                    'password' => '',
                ],
                'S3' => [
                    // Credenciais:  https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_profiles.html
                    'profile' => '',
                    'region' => '',
                ],
                'GCP' => [
                    'projectId' => '',
                    'keyFilePath' => '',
                ],
            ],
            'onlyDev' => [], // rotas de acesso exclusivo para login developer
            'onlyAdm' => [], // rotas de acesso exclusivo para login adminsitrativo
            'integracao' => [],
            'menu' => []
        ];

        $tipos = [
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
            'text' => 'string',
            'USER-DEFINED' => 'string',
            'citext' => 'string',
        ];

        $rota = [
            "['prefix' => '/', 'archive' => 'App/index.php']",
            "['prefix' => '/home', 'archive' => 'App/index.php']",
            "['prefix' => '/index.php', 'archive' => 'App/index.php']",
            "['prefix' => '/login', 'archive' => 'appLogin/index.php']",
            "['prefix' => '/usuariogrupo', 'archive' => 'fmwUsuariogrupo/index.php']",
            "['prefix' => '/file', 'archive' => 'file.php']",
            "['prefix' => '/logout', 'archive' => 'logout.php']",
            "['prefix' => '/reset', 'archive' => 'reset.php']",
            "['prefix' => '/about', 'archive' => 'about.php']",
            "['prefix' => '/versao', 'archive' => 'versao.php']",
            "['prefix' => '/recovery', 'archive' => 'appRecovery/index.php']"
        ];

        // rotas
        $CONFIG['router'] = [
            ['prefix' => '/', 'archive' => 'App/index.php'],
            ['prefix' => '/home', 'archive' => 'App/index.php'],
            ['prefix' => '/index.php', 'archive' => 'App/index.php'],
            ['prefix' => '/login', 'archive' => 'appLogin/index.php'],
            ['prefix' => '/usuariogrupo', 'archive' => 'fmwUsuariogrupo/index.php'],
            ['prefix' => '/file', 'archive' => 'file.php'],
            ['prefix' => '/logout', 'archive' => 'logout.php'],
            ['prefix' => '/reset', 'archive' => 'reset.php'],
            ['prefix' => '/about', 'archive' => 'about.php'],
            ['prefix' => '/versao', 'archive' => 'versao.php'],
            ['prefix' => '/Teste', 'archive' => 'Teste/index.php'],
            ['prefix' => '/recovery', 'archive' => 'appRecovery/index.php'],
        ];

        $defaults = [
            '::integer' => '',
            '\'{}\'::jsonb' => "'{}'",
            '\'[]\'::jsonb' => "'{}'",
            'CURRENT_TIMESTAMP' => '',
            'now()' => "date('Y-m-d H:i:s')",
            'nextval' => '',
            '::text' => '',
            '::character varying' => '',
            '::bpchar' => '',
        ];

        $prefixos = $this->prefixos;

        $query = $this->querys;

        // Obter tabelas
        $tabelas = [];
        $list = $con->execQueryAndReturn($query['listTables']);
        foreach ($list as $item) {
            $tabelas[$item['schemaname'] . '.' . $item['tablename']] = [
                'tabela' => $item['tablename'],
                'schema' => $item['schemaname']
            ];
        }


        $libraryEntities = $hints = $libraryFields = []; // armazenara um arquivo com as etiquetas para os nomes dos campos
        $aliases = $camposDouble = [];
        if (count($tabelas) === 0) {
            echo '<div class="alert alert-danger text-center">'
                . 'ERROR!<br/>Nenhuma tabela na base de dados'
                . '</div>';
            die();
        }

        $totalRegistros = count($tabelas);
        $done = 0;
        if (!$this->quiet) {
            $loader = new StatusLoader($totalRegistros, 'Lendo database');
        }



        foreach ($tabelas as $schemaTable => $tab) {
            $tabela = $tab['tabela'];
            $entidade = $table = $temp = $declaracao = $out = $relacionamentos = false;
            $camposJson = $camposDouble = $atributos = $camposDate = [];

            // obter nome da entidade
            $myent = $entidade = Helper::name2CamelCase($tabela, $prefixos);
            if (is_numeric($entidade[0])) {
                $entidade = '_' . $entidade;
            }
            $entidade[0] = strtoupper($entidade[0]);
            $menu[] = "['label' => Config::getData('titlePagesAliases', '$entidade'), 'link' => Config::getData('url') .'/$entidade', 'icon' => 'angle-right', 'dropdown' => false]";

            //$rota[] = "['prefix' => '/$myent', 'archive' => '$myent.php']";
            $rota[] = "['prefix' => '/$entidade', 'archive' => '$entidade/index.php']";
            $CONFIG['router'][] = ['prefix' => '/' . $entidade, 'archive' => $entidade . '/index.php', 'router' => str_replace('_', '-', $tabela)];

            //$libraryEntities
            // Obter atributos da tabela
            $con->executeQuery(sprintf($query['getEstruturaTable'], $tabela));
            $estrutura = [];
            while ($dd = $con->next()) {
                foreach ($dd as $key => $value) {
                    $dd[strtolower($key)] = $value;
                }
                $estrutura[] = $dd;
            }
            if (count($estrutura) === 0) {
                echo '<br/>TABELA ' . $tabela . ' NÃO POSSUI ATRIBUTOS. ENTIDADE NÃO CRIADA';
                continue;
            }

            // Obter o nome do campo ID da tabela
            $ret = $con->execQueryAndReturn(sprintf($query['getPrimaryKey'], $schemaTable));
            $cpoID = ((isset($ret[0]['attname'])) ? $ret[0]['attname'] : '');

            // Aliases Table com base no comentario da tabela
            $CONFIG['titlePagesAliasesByComents'][mb_strtolower($entidade)] = $con->execQueryAndReturn("
                select obj_description((table_schema||'.'||quote_ident(table_name))::regclass) as nametable
                from information_schema.tables where table_schema <> 'pg_catalog' and table_name= '$tabela'
            ")[0]['nametable'];


            // obter nome dos atributos
            $encontrouPrimaryKey = false;
            $aliaseTableByCpoID = str_replace('_', ' ', $tabela);
            foreach ($estrutura as $key => $detalhes) {
                // Campo ID:
                //                if ($detalhes['ordinal_position'] === 1 || $detalhes['column_key'] === 'PRI') {
                //                    $cpoID = $detalhes['column_name'];
                //                }

                $isKey = $detalhes['column_name'] === $cpoID;

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
                    $detalhes['column_default'] = str_replace($key, $value, (string) $detalhes['column_default']);
                }
                if (stripos($detalhes['column_default'], '::') !== false) {
                    $temp = explode('::', $detalhes['column_default']);
                    $detalhes['column_default'] = $temp[0];
                }

                // comentários do campo
                if (isset($query['getComents'])) {
                    $con->executeQuery(sprintf($query['getComents'], $tabela, $detalhes['column_name']));
                    $extras = $con->next();
                    $c = explode('|', (string) $extras['column_comment']);
                    $detalhes['column_comment'] = $c[0];
                    $detalhes['hint'] = ((isset($c[1]) && strlen((string) $c[1]) > 1) ? $c[1] : false);

                    // Aliases table by cpoId
                    $aliaseTableByCpoID = '';
                    if ($isKey && !$encontrouPrimaryKey) {
                        $encontrouPrimaryKey = true;
                        $aliaseTableByCpoID = strlen((string) $detalhes['column_comment']) > 0
                            ? $detalhes['column_comment']
                            : str_replace('_', ' ', $tabela);
                        $CONFIG['titlePagesAliases'][mb_strtolower($entidade)] = $aliaseTableByCpoID;
                    }
                }


                /*                 * *********************************************
                 *         //Campo name
                  $CONFIG['libraryEntities']['NOTIFYTOOL']; // Ferramenta de notificação
                  $CONFIG['aliasesField'][$entidade . "." . $field['column_name']]; // Nome da ferramenta
                  $CONFIG['hint'][$entidade . "." . $field['column_name']]; // Digite aqui o nome da ferramenta

                 */

                if (strlen((string) $detalhes['column_comment']) === 0) {
                    $detalhes['column_comment'] = str_replace('_' . strtolower($entidade), '', $detalhes['column_name']);
                    $detalhes['column_comment'] = str_replace('_', ' ', $detalhes['column_comment']);
                }

                $chaveField = mb_strtolower(Helper::name2CamelCase($detalhes['column_name']));
                $chaveHint = mb_strtolower($entidade . '_' . $chaveField);

                $libraryFields[] = "'" . $chaveField . "' => '" . $detalhes['column_comment'] . "'";
                if (strlen((string) $detalhes['hint']) > 1) { // se hint existir, salve
                    $hints[] = "'" . $chaveHint . "' => '" . $detalhes['hint'] . "'";
                    $CONFIG['hints'][$entidade . "_" . Helper::name2CamelCase($detalhes['column_name'])] = $detalhes['hint'];
                    $CONFIG['hints'][$chaveHint] = $detalhes['hint'];
                }
                $CONFIG['aliasesField'][$entidade . "_" . Helper::name2CamelCase($detalhes['column_name'])] = $detalhes['column_comment'];
                $CONFIG['aliasesField'][$chaveField] = $detalhes['column_comment'];
                $CONFIG['aliasesField'][Helper::name2CamelCase($detalhes['column_name'])] = $detalhes['column_comment'];

                // Criação do atributo
                $atributos[] = [
                    'entidade' => $entidade,
                    'key' => $isKey,
                    'nome' => Helper::name2CamelCase($detalhes['column_name']),
                    'column_name' => $detalhes['column_name'],
                    'tipo' => $detalhes['data_type'],
                    'maxsize' => (($detalhes['character_maximum_length']) ? $detalhes['character_maximum_length'] : 1000000000),
                    'valorPadrao' => (($detalhes['column_default'] != '' && !$isKey) ? $detalhes['column_default'] : "null"),
                    'coments' => (($detalhes['column_comment']) ? $detalhes['column_comment'] : Helper::name2CamelCase($detalhes['column_name'])),
                    'notnull' => (($detalhes['is_nullable'] === 'NO') ? true : false),
                    'hint' => $detalhes['hint'],
                    'relationship' => false
                ];
            }

            // Se não encontrou a chave, o primeiro camp passa a ser
            if (strlen((string) $cpoID) === 0) {
                $cpoID = $atributos[0]['column_name'];
                $atributos[0]['valorPadrao'] = "''";
                $atributos[0]['key'] = true;
            }

            // aliases para tabela
            $CONFIG['libraryEntities'][mb_strtoupper($entidade)] = $entidade;
            $aliases[] = "
            '" . mb_strtolower($entidade) . "' => '" . $aliaseTableByCpoID . "'";
            $libraryEntities[] = "
            '" . mb_strtolower($entidade) . "' => '" . $entidade . "'";

            //Relacionamentos
            $relacoes = null;
            $relacoesToJson = [];
            $con->executeQuery(sprintf($query['relacionamentos'], $tabela));
            while ($dd = $con->next()) {
                foreach ($dd as $key => $value) {
                    $dd[strtolower($key)] = $value;
                }
                // obter nome da entidade
                $entidadeRef = Helper::name2CamelCase($dd['referenced_table_name'], $prefixos);

                // != $tabela: evitara o autorelacionamento, pois gera exaustao de memória
                if ($dd['referenced_table_name'] != $tabela) {
                    $relacoesToJson[] = [
                        'entidade' => ucwords(Helper::name2CamelCase($dd['referenced_table_name'])),
                        'table' => $dd['referenced_table_name'],
                        'schema' => $dd['referenced_schema_name'],
                        'field_origin' => $dd['column_name'],
                        'fieldOrigin' => Helper::name2CamelCase($dd['column_name']),
                        'field_relation' => $dd['referenced_column_name'],
                        'fieldRelation' => Helper::name2CamelCase($dd['referenced_column_name']),
                    ];
                    $relacoes[] = "['schema' => '" . $dd['referenced_schema_name'] . "',  'tabela'=>'" . $dd['referenced_table_name'] . "', 'cpoOrigem'=>'" . $dd['column_name'] . "', 'cpoRelacao'=>'" . $dd['referenced_column_name'] . "']";
                    $atributos[] = [
                        'key' => false,
                        'nome' => $entidadeRef,
                        'column_name' => $detalhes['column_name'],
                        'tipo' => (((Helper::compareString(substr((string) $dd['referenced_table_name'], 0, 3), 'ce_'))) ? 'EXTERNA' : 'OBJECT'),
                        'valorPadrao' => 'new ' . ucwords($entidadeRef) . '()',
                        'coments' => 'Relação com entidade ' . $dd['referenced_table_name'] . ' @JoinColumn(name=\'' . $dd['referenced_column_name'] . '\')',
                        'notnull' => 'false',
                        'relationship' => true
                    ];
                }
            }

            $CONFIG['entitieConfig'][$entidade] = [
                'camposDate' => str_replace("'", '', $camposDate),
                'camposDouble' => str_replace("'", '', $camposDouble),
                'camposJson' => str_replace("'", '', $camposJson),
            ];
            $dados = [
                'aliaseTableByID' => $CONFIG['titlePagesAliases'][mb_strtolower($entidade)] ?? $entidade,
                'aliaseTableByComents' => $CONFIG['titlePagesAliasesByComents'][mb_strtolower($entidade)] ?? $entidade,
                'schema' => $tab['schema'],
                'schemaTable' => $schemaTable,
                'tabela' => $tabela,
                'cpoID' => Helper::name2CamelCase($cpoID),
                'entidade' => $entidade,
                'estrutura' => $estrutura,
                'atributos' => $atributos,
                'relacionamentos' => $relacoes,
                'relations' => $relacoesToJson,
                'camposDate' => implode(', ', $camposDate),
                'camposDouble' => implode(', ', $camposDouble),
                'camposJson' => implode(', ', $camposJson),
                'arrayCamposJson' => $camposJson,
                'routeBackend' => Helper::name2CamelCase($tabela),
                'routeFrontend' => str_replace('_', '-', mb_strtolower($tabela))
            ];

            $this->data['itens'][] = $dados;

            $out = [];

            if (!$this->onlyGetData) {

                // Criação de entidade
                EntidadesCreate::save($dados, $entidade);

                // Criação de controller
                RestControllerCreate::save($dados, $entidade, $controllerIgnoreEntites);
            }



            if (isset($loader)) {
                $done++;
                $loader->done($done);
            }
        } // FECHA FOREACH TABELAS
        // Setando dados final
        $this->data = [
            'rotas' => $rota,
            'menu' => $menu,
            'aliases' => $aliases,
            'libraryEntities' => $libraryEntities,
            'aliasesFields' => $libraryFields,
            'hints' => $hints,
            'config' => $CONFIG,
            'itens' => $this->data['itens']
        ];
    }

    public function readData()
    {
        return $this->data;
    }

    private function entidadesInit()
    {
        Config::setData('pathEntidades', Config::getData('path') . '/src/NsLibrary/Entities');
        // Remover diretório de entidades, caso exista
        Helper::deleteDir(Config::getData('pathEntidades'));
        sleep(1);
        Helper::mkdir(Config::getData('pathEntidades'));
    }

    private function controllerInit()
    {
        // REST
        Config::setData('pathRestControllers', Config::getData('path') . '/src/NsLibrary/Routers');
        Helper::mkdir(Config::getData('pathRestControllers'));

        // CONTROLLER
        //        Config::setData('pathControllers', Config::getData('path') . '/src/NsLibrary/Controllers');
        //        Helper::mkdir(Config::getData('pathControllers'));
    }

    private function viewInit()
    {
        Config::setData('pathViewSource', Config::getData('path') . '/src/NsLibrary/ViewSource');
        Helper::mkdir(Config::getData('pathViewSource'));
        sleep(1);
    }

    public function getData($quiet = false, $tokenCrypto = '', $appName = '', $htmlTitle = '', $adminName = '', $adminEmail = '')
    {
        $this->onlyGetData = true;
        $this->quiet = $quiet;
        $this->run($tokenCrypto, $appName, $htmlTitle, $adminName, $adminEmail);
        $this->onlyGetData = false;
        $this->quiet = true;
        return $this->data;
    }
}
