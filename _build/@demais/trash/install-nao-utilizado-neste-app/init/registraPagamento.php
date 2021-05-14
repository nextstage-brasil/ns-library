<?php

require '../../../cron/_fn.php';

// Remoto
$database = [
    'host' => 'db11.usenextstep.com.br',
    'user' => 'nextstage',
    'pwd' => 'bl0nuIFvHj00ET49vH76nziDTGuKMSZrQl1eo7dus1p1bM5DFz',
    'database' => 'logos',
    'port' => 6432,
    'schema' => 'public', 
    'type' => 'postgres'
];
Config::setData('database', $database);
Config::setData('url', 'https://logos.usenextstep.com.br');


UsuarioController::loginBryan();
$con = new \NsUtil\ConnectionPostgreSQL(Config::getData('database', 'host'), Config::getData('database', 'user'), Config::getData('database', 'pwd'), Config::getData('database', 'port'), Config::getData('database', 'database'));
$list = $con->execQueryAndReturn("select * from importacao.turmas_18_02_2020 where inicio='2020-01-01'");
$us = new UsuarioController();
$fin = new FinanceiroController();
$dao = new EntityManager();

$console = new \NsUtil\ConsoleTable();
$console->addHeader(['CPF', 'Nome', 'Cód Aluno', 'Cód matricula', 'Status']);

$done = 0;
$loader = new \NsUtil\StatusLoader(count($list), 'Carga de dados');
$error = [
    ['CPF', 'Nome', 'Cód Aluno', 'Cód matricula', 'Status']
];
foreach ($list as $item) {
    $usuario = $us->getByCPF(['cpf' => $item['cpf']]);
    if ($usuario['idUsuario'] > 0) {
        $matricula = $con->execQueryAndReturn("select * from matricula where id_usuario= " . (int) $usuario['idUsuario'])[0];
        $financeiro = $con->execQueryAndReturn("select * from financeiro"
                        . " where id_matricula= " . (int) $matricula['idMatricula']
                        . " and id_status_financeiro=1"
                        . " and id_auxiliar= 2")[0];
        if ($financeiro['idFinanceiro'] > 0) {
            $conta = $con->execQueryAndReturn("select * from conta where is_contacaixa_conta = 'true' and id_polo = " . $matricula['idPolo'])[0];
            // Recebimento
            $retornoGateway = [
                'formaPgto' => 102,
                'isRetornoGateway' => false, // caso seja, será definido abaixo
                'origem' => 'Usuário', // Usuário que fez a informação
                'status' => 4, // esse status é do gateway. precisa ser 4 fixo
                'confirmDate' => '2020-01-01',
                'ignoreGeraGrade' => true // vou controlar apos a criação de todos
            ];
            $ret = $fin->registraRecebimento((int) $financeiro['idFinanceiro'], (int) $conta['idConta'], 102, $retornoGateway, $dao);
            if ($ret['error'] === false) {
                $console->addRow([$usuario['cpfUsuario'], $usuario['nomeUsuario'], $usuario['idUsuario'], $matricula['idMatricula'], $ret['result']]);
            } else {
                $error[] = [$usuario['cpfUsuario'], $usuario['nomeUsuario'], $usuario['idUsuario'], $matricula['idMatricula'], $ret['error']];
            }
        }

    }
    $done++;
    $loader->done($done);
}

// Gerar grade manualmente
$dao = new EntityManager();
(new GradeAlunoController())->geraGradeAluno($dao);

file_put_contents(__DIR__ . '/report/fin-ok.txt', $console->getTable());
// CSV
$csv = NsUtil\Helper::array2csv($error);
file_put_contents(__DIR__ . '/report/fin-error.csv', $csv);

$console->display();