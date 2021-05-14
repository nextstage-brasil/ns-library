<?php

require '../../cron/_fn.php';
UsuarioController::loginBryan();

// Metodos a ignorar pois a criação será feita no builder
$methodosIgnore = ['ws_getNew', 'ws_getById', 'ws_getAll', 'ws_save', 'ws_remove'];
// Variaveis necessárias para não gerar erros sem sentidos nos testes
$dados = [
    'relacaoLinktable' => 'CURSO|MODULO',
    'email' => 'teste@teste.com.br',
    'nomeUsuario' => 'Usuario em teste',
    'emailUsuario' => 'teste@teste.com.br',
    'idEmpresa' => 1,
    'idUsuario' => 3,
    "idCurso" => 7,
    "idPolo" => 10,
];

function init($dados) {
    $dao = new EntityManager();
    clear();
    $dao->execQueryAndReturn("insert into app_usuario (id_usuario, nome_usuario, email_usuario, id_empresa) values (3, '$dados[nomeUsuario]', '$dados[emailUsuario]', 1)");
}

function clear() {
    $dao = new EntityManager();
    $dao->execQueryAndReturn("DELETE FROM public.financeiro WHERE id_matricula=(select id_matricula from matricula where id_usuario=3)");
    $dao->execQueryAndReturn("DELETE FROM public.matricula  WHERE id_matricula=(select id_matricula from matricula where id_usuario=3);");
    $dao->execQueryAndReturn('delete from app_usuario where id_usuario= 3');
}

init($dados);
$dao = new EntityManager();

$groups = [];

if ($handle = opendir('../../src/controller')) {
    while (false !== ($file = readdir($handle))) {
        if (stripos($file, '.class') > -1) {
            $class = str_replace('.class.php', '', $file);
            $grupo = mb_strtoupper(str_ireplace('CONTROLLER', '', $class));

            $groups[] = "'$grupo' => '$grupo'";

            $ctr = new $class();
            $classe = new $class();
            $api = new ReflectionClass($class);
            $i = 0;
            $fire_args = [];
            $loader = new NsUtil\StatusLoader(count($api->getMethods()), $class);
            $done = 0;
            $dao->execQueryAndReturn("INSERT INTO public.app_sistema_funcao (grupo_funcao, subgrupo_funcao, acao_funcao) VALUES"
                    . "('$grupo', '$grupo', 'INSERIR'),"
                    . "('$grupo', '$grupo', 'EDITAR'),"
                    . "('$grupo', '$grupo', 'REMOVER'),"
                    . "('$grupo', '$grupo', 'LER')"
                    . " ON CONFLICT DO NOTHING");
            foreach ($api->getMethods() as $method) {
                if (stripos($method->name, 'ws_') !== false && array_search($method->name, $methodosIgnore) === false) {
                    $fn = (string) $method->name;
                    $loader->setLabel($fn);
                    $classe->$fn($dados);
                }
                sleep(0.8);
                $done++;
                $loader->setLabel($class);
                $loader->done($done);
            }
        }
    }
    closedir($handle);
}

// AGRUPAMENTO DE ENTIDADES
$alias = "<?php \n \$permissao_grupos_auto = [\n" . implode(",\n", $groups) . "\n];";
Helper::saveFileBuild(Config::getData('path') . "/auto/config/permissao_grupos.php", $alias, 'SOBREPOR');
if (!file_exists(Config::getData('path') . "/src/config/permissao_grupos.php")) {
    $alias = "<?php\n
            /**
 * Agrupamento de chave de permissao por tipo.
 * Utilizar sempre o nome da entidade em caixa alta apontando para o grupo que irá aprecer nas permissões. 
 * Ex.: CEP => SISTEMA. As permissões para CEP fazem parte do grupo sistema
 */
 \$permissao_grupos_auto = [];
include_once \$SistemaConfig['path'] . '/auto/config/permissao_grupos.php';
\$permissao_grupos = array_merge(\$permissao_grupos_auto, [\n" . implode(",\n", $groups) . "\n]);
";
    Helper::saveFileBuild(Config::getData('path') . "/src/config/permissao_grupos.php", $alias);
}
echo "\nArquivo de grupo de permissões criado\n";

clear();
