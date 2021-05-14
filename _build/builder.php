<?php

mb_internal_encoding('UTF-8');
$dirApps = str_replace('library' . DIRECTORY_SEPARATOR . 'build', '', __DIR__);

require ('../library/SistemaLibrary.php');

require_once 'ControllerCreate.class.php';
require_once 'EntidadesCreate.class.php';
require_once 'SistemaCreate.class.php';

//$SOBREPOR_VIEW = (($_GET['sobrepor'] === '1') ? 'SOBREPOR' : 'w+');
//$SOBREPOR_CTR = (($_GET['sobrepor_ctr'] === '1') ? 'SOBREPOR' : 'w+');
// Arquivo de configuração não editado
$errorBuild[] = $create;
$errorBuild[] = ((strlen(Config::getData('identificador')) < 3) ? 'Identificador do sistema precisa ter mais de três letras' : '');
$errorBuild[] = ((Config::getData('name') === '') ? 'Definir nome do sistema' : '');
$errorBuild[] = ((Config::getData('database', 'type') === '') ? 'Definir tipo de database' : '');
$errorBuild[] = ((Config::getData('database', 'database') === '' ) ? 'Definir database name' : ' ');
$errorBuild[] = ((Config::getData('url') === '' ) ? 'Url deve não definida' : ' ');

if ($create || Config::getData('name') === '' || Config::getData('database', 'type') === '' || Config::getData('database', 'database') === '') {
    echo '<div class="alert alert-danger text-center">'
    . '<h1>Ops.. Faltam algumas configurações: <br/>Abra o arquivo em HOME/config.php e sete as variaveis de configuração.</h1>'
    . '<h3>Erros: ' . implode('<br/>', $errorBuild) . '</h3>'
    . '</div>';
    die();
}


$app = new AppLibraryController();

setlocale(LC_CTYPE, "pt_BR");
setlocale(LC_TIME, "pt_BR");
Log::log('entidades_create', 'Nova criação');
$database = Config::getData('database', 'database');
$con = Connection::getConnection();
//$con->executeQuery('create extension unaccent');
Helper::deleteDir(Config::getData('path') . '/auto/entidades');
Helper::deleteDir(Config::getData('dirComponents'));
sleep(0.2);
//Helper::deleteFile(Config::getData('path') . '/auto/entidades', true, false); // removido explicitament para forcar reet das entidade
//die();
##########################################################################
# NÃO ALTERAR PARA BAIXO
##########################################################################
// Criar pastas necessárias
$dirs = [
    Config::getData('path') . '/view/_' . Config::getData('identificador') . '/_template/index.php',
    Config::getData('path') . '/src/config/index.php',
        //Config::getData('pathView') . '/js/index.php',
        //Config::getData('pathView') . '/css/index.php',
        //Config::getData('pathView') . '/files/index.php',
        //Config::getData('pathView') . '/images/index.php',
        //Config::getData('pathView') . '/uploadFiles/index.php',
        //Config::getData('path') . '/src/logs/index.php',
        //Config::getData('path') . '/src/js/index.php',
];
foreach ($dirs as $filename) {
    Helper::saveFile($filename, false, '<?php header("Location:/");', 'SOBREPOR');
}
Helper::saveFile(Config::getData('fileModelJson'), false, '<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

// EXTRAS JSON
$MODEL_JSON = [
    // Exemplo
    \'slaConfig\' => [
        \'unidade\' => [\'default\' => \'D\', \'grid\' => \'col-sm-4\', \'type\' => \'select\', \'list\' => [
                [\'id\' => \'H\', \'label\' => \'Horas\'],
                [\'id\' => \'D\', \'label\' => \'Dias\'],
            ], \'class\' => \'\', \'ro\' => \'false\', \'tip\' => \'Definir o tipo de tempo\', \'label\' => \'Unidade de tempo\'],
        \'tempo\' => [\'default\' => 2, \'grid\' => \'col-sm-4\', \'type\' => \'number\', \'class\' => \'\', \'ro\' => \'false\', \'tip\' => \'Definir a quantidade de dias ou horas\', \'label\' => \'Tempo\'],
        \'tempo_util\' => [\'default\' => \'S\', \'grid\' => \'col-sm-4\', \'type\' => \'boolean\', \'class\' => \'\', \'ro\' => \'false\', \'tip\' => \'Se sim, o prazo levará em conta somente períodos úteis nacionais\', \'label\' => \'Somente dias úteis?\']
    ]
];');



$querys = array(
    'mysql' => array(
        'listTables' => "SELECT TABLE_NAME as tablename FROM information_schema.tables WHERE table_schema = '$database'",
        'getEstruturaTable' => "select * from information_schema.columns WHERE table_name= '%s' and TABLE_SCHEMA= '$database'",
        'relacionamentos' => "SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME = '%s' AND REFERENCED_TABLE_NAME IS NOT NULL;"
    ),
    'postgres' => array(
        'listTables' => "SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname= '" . Config::getData('database', 'schema') . "' ORDER BY tablename",
        'getEstruturaTable' => "select * from information_schema.columns WHERE table_name= '%s' and table_schema='" . Config::getData('database', 'schema') . "'",
        'getComents' => 'SELECT pg_catalog.col_description(c.oid, a.attnum) AS column_comment FROM pg_class c LEFT JOIN pg_attribute a ON a.attrelid = c.oid LEFT JOIN information_schema.columns ws ON ws.column_name = a.attname AND ws.table_name= c.relname '
        . 'WHERE c.relname = \'%s\' AND a.attname= \'%s\' and c.relnamespace = (select oid from pg_catalog.pg_namespace where nspname= \'' . Config::getData('database', 'schema') . '\') ',
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
        cl.relname = '%s' and n.oid= (select oid from pg_catalog.pg_namespace where nspname= '" . Config::getData('database', 'schema') . "') group by a.attname, af.attname, clf.relname"
    )
);
$query = $querys[Config::getData('database', 'type')];
$con->executeQuery($query['listTables']);

$tabelas = [];
while ($dd = $con->next()) {
    $tabelas[] = $dd['tablename'];
}

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
    //'text' => 'string',
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

$defaults = array('CURRENT_TIMESTAMP' => '', '\'{}\'::jsonb' => "'{}'", '::text' => '', '::character varying' => '', '::bpchar' => '', 'now()' => "date('Y-m-d H:i:s')", 'nextval' => '');
$prefixos = array('mem_', 'sis_', 'anz_', 'aux_', 'app_');


$menu = [];
// rotas padrão
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

$libraryEntities = $hints = $libraryFields = []; // armazenara um arquivo com as etiquetas para os nomes dos campos
$aliases = $camposDouble = [];
if (count($tabelas) === 0) {
    echo '<div class="alert alert-danger text-center">'
    . 'ERROR!<br/>Nenhuma tabela na base de dados'
    . '</div>';
    die();
}
foreach ($tabelas as $tabela) {
    $estrutura = $entidade = $atributos = $table = $temp = $declaracao = $out = $relacionamentos = false;
    $camposDate = [];
    $camposDouble = [];
    $camposJson = [];

    // obter nome da entidade
    $myent = $entidade = Helper::name2CamelCase($tabela, $prefixos);
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
    $template = EntidadesCreate::get($dados);
    $file = Config::getData('path') . '/auto/entidades/' . $entidade . '.class.php';
    Helper::saveFileBuild($file, $template, 'SOBREPOR');

    ### Criação de controller
    // Não quero salvar esses controller, pq são padrão do framework
    if (array_search($entidade, [
                'Linktable',
                'Trash',
                'Uploadfile',
                'Usuario',
                'UsuarioPermissao',
                'UsuarioTipo',
                'Mensagem',
                'Status'
            ]) === false) {
        $template = ControllerCreate::get($dados);
        $file = Config::getData('path') . '/src/controller/' . $entidade . 'Controller.class.php';
        Helper::saveFileBuild($file, $template);
    }

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



    ### Criação dos components
    ##  saidas
    $outGeral[] = "Arquivos criados para entidade $entidade";
} // FECHA FOREACH TABELAS
//
//
//
//$m = '<?php $menu = [' . implode(",\n", $menu) . '];';
//Helper::saveFile(Config::getData('pathView') . '/template/nav.php', false, $m);
//ROTAS
$router = "<?php \n \$route = [\n" . implode(",\n", $rota) . "\n];";
Helper::saveFileBuild(Config::getData('path') . "/auto/config/router_default.php", $router, 'SOBREPOR');
$out[] = '<p class="col-sm-6 alert alert-success">Arquivo Config de rotas salvo com sucesso</p>';

//ALIASES
$alias = "<?php \n \$aliases_table = [\n" . implode(",\n", $aliases) . "\n];";
Helper::saveFileBuild(Config::getData('path') . "/auto/config/aliases_tables.php", $alias, 'SOBREPOR');
$out[] = '<p class="col-sm-6 alert alert-success">Arquivo Config de aliases salvo com sucesso</p>';

//ALIASES - CAMPOS
$alias = "<?php \n \$aliases_field = [\n" . implode(",\n", $libraryFields) . "\n];";
Helper::saveFileBuild(Config::getData('path') . "/auto/config/aliases_fields.php", $alias, 'SOBREPOR');
$out[] = '<p class="col-sm-6 alert alert-success">Arquivo Config de aliases-fields salvo com sucesso</p>';

//ALIASES - CAMPOS
$alias = "<?php \n \$libraryEntities = [\n" . implode(",\n", $libraryEntities) . "\n];";
Helper::saveFileBuild(Config::getData('path') . "/auto/config/library_entities.php", $alias, 'SOBREPOR');
$out[] = '<p class="col-sm-6 alert alert-success">Arquivo Config de aliases-fields salvo com sucesso</p>';

// HINTS
$alias = "<?php \n \$hints = [\n" . implode(",\n", $hints) . "\n];";
Helper::saveFileBuild(Config::getData('path') . "/auto/config/hints.php", $alias, 'SOBREPOR');
$out[] = '<p class="col-sm-6 alert alert-success">Arquivo Config de Hints salvo com sucesso</p>';

//  ENTIDAES DO SITE - MODELOS NÃO EXISTEM NO BANCO DE DADOS, APENAS NA APLCIAÇÃO
include __DIR__ . '/site_gera_entidades.php';


// MENU
$m[] = "['label' => 'Painel', 'link' => Config::getData('url') .'/home', 'icon' => 'dashboard', 'dropdown' => false]";
$m[] = "['label' => 'Todos', 'link' => '#', 'icon' => 'angle-right', 'dropdown' => [\n" . implode(",\n", $menu) . "\n]]";
$nav = "<?php \n \$nav = [\n" . implode(",\n", $m) . "\n];";
Helper::saveFileBuild(Config::getData('path') . "/src/config/nav/nav_default.php", $nav);
$out[] = '<p class="col-sm-6 alert alert-success">Arquivo Config de menu salvo com sucesso</p>';

$template = new Template(Config::getData('pathView') . '/template/000-template.html', [
    'TITLE' => 'NS Builder', 'CONTENT' => '<h1 class="text-center">NS Builder</h1>'
    . '<a class="btn btn-info" onclick="javascript:history.back()">Voltar</a>'
    . '<script>alert(\'Processo Concluído com Sucesso! \n' . count($outGeral) . ' entidades atendidas\');history.back();</script>'
        ]);



if ($_GET['pack']) {
    echo 'Build com sucesso';
} else {
    echo $template->render();
}
