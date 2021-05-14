<?php

require '../../../cron/_fn.php';
UsuarioController::loginBryan();
/* Feito. Gerou a tabela de dados a inserir */
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

/*
$file = __DIR__ . '/data';
$con = new \NsUtil\ConnectionPostgreSQL(Config::getData('database', 'host'), Config::getData('database', 'user'), Config::getData('database', 'pwd'), Config::getData('database', 'port'), Config::getData('database', 'database'));
$pgl = new \NsUtil\PgLoadCSV($con, 'importacao', true, true);
$pgl->run($file);

// Atualização de dados
$con->execQueryAndReturn("update importacao.turmas_18_02_2020 set cpf = replace(cpf, '.', '')");
$con->execQueryAndReturn("update importacao.turmas_18_02_2020 set cpf = replace(cpf, '-', '')");
$list = $con->execQueryAndReturn('select * from importacao.turmas_18_02_2020');
foreach ($list as $item) {
    $dt = $item['nascimento'];
    $ns = explode('/', $item['nascimento']);
    if ((int) $ns[1] > (int) 12) { // mes invertido
        $dt = str_pad($ns[1], 2, '0', STR_PAD_LEFT) . '/' . str_pad($ns[0], 2, '0', STR_PAD_LEFT) . '/' . $ns[2];
    }
    $dt = Helper::formatDate($dt, 'mostrar');
    $fone = Helper::formatFone($item['telefone']);
    $con->execQueryAndReturn("update importacao.turmas_18_02_2020 set nascimento= '$dt' where nascimento= '" . $item['nascimento'] . "'");
    $con->execQueryAndReturn("update importacao.turmas_18_02_2020 set telefone= '$fone' where telefone= '" . $item['telefone'] . "'");
}

$querys = [
    "ALTER TABLE importacao.turmas_18_02_2020 ADD inicio text NULL;",
    "update importacao.turmas_18_02_2020 a set email = t.email from (
            select b.*
            from importacao.turmas_18_02_2020 a 
            inner join importacao.conversoes_lahar_2021 b using (cpf)
            ) t
            WHERE a.cpf= t.cpf;",
    "update importacao.conversoes_lahar_2021 set polo = '11' where polo= 'polo centro';",
    "update importacao.conversoes_lahar_2021 set polo = '10' where polo= 'polo barreiros';",
    "update importacao.conversoes_lahar_2021 set polo = '14' where polo= 'polo norte da ilha';",
    "INSERT INTO importacao.turmas_18_02_2020 (idpolo, nome, cpf, nascimento, email, telefone, inicio)  (
	select
		polo, nome|| ' '|| sobrenome, cpf, data_nascimento, email, telefone_celular, '2021-01-01'
	FROM importacao.conversoes_lahar_2021 a
	where cpf not in (
		select cpf from importacao.turmas_18_02_2020
	)
);",
    "update importacao.turmas_18_02_2020 a set inicio= '2021-01-01' WHERE cpf IN (
	select b.cpf
	from importacao.turmas_18_02_2020 a 
	inner join importacao.conversoes_lahar_2021 b using (cpf)
);",
    "update importacao.turmas_18_02_2020 a set inicio= '2020-01-01' WHERE cpf not IN (
	select b.cpf
	from importacao.turmas_18_02_2020 a 
	inner join importacao.conversoes_lahar_2021 b using (cpf)
);",
    "UPDATE importacao.turmas_18_02_2020 SET nascimento='não informado' where nascimento= ''",
    "UPDATE importacao.turmas_18_02_2020 SET email=MD5(nome||cpf)||'@naoinformado.none' where email= ''"
];
$done = 0;
$loader = new \NsUtil\StatusLoader(count($list), 'Querys');
foreach ($querys as $query) {
    $con->execQueryAndReturn($query);
    $done++;
    $loader->done($done);
}
*/

$con = new \NsUtil\ConnectionPostgreSQL(Config::getData('database', 'host'), Config::getData('database', 'user'), Config::getData('database', 'pwd'), Config::getData('database', 'port'), Config::getData('database', 'database'));
$list = $con->execQueryAndReturn('select * from importacao.turmas_18_02_2020');
$console = new \NsUtil\ConsoleTable();
$consoleError = new \NsUtil\ConsoleTable();
$console->addHeader(['CPF', 'Nome', 'Cód Aluno', 'Cód matricula']);
$consoleError->addHeader(['CPF', 'Nome', 'Error']);
$done = 0;
$loader = new \NsUtil\StatusLoader(count($list), 'Carga de dados');
$error = [];
$error[] = ['idCurso', 'idPolo', 'nome', 'email', 'celular', 'dataNascimento', 'cpf', 'escolaridade', 'igreja', 'origem' => 'Carga de dados manual', 'error'];
foreach ($list as $item) {
    $inscricao = [
        'idCurso' => 7,
        'idPolo' => (int) $item['idpolo'],
        'nome' => $item['nome'],
        'email' => $item['email'],
        'celular' => $item['telefone'],
        'dataNascimento' => $item['nascimento'],
        'cpf' => $item['cpf'],
        'escolaridade' => 35,
        'igreja' => 'Palavra Viva Sede|10',
        'origem' => 'Carga de dados manual'
    ];
    $ret = Helper::curlCall(Config::getData('url') . '/api/site/inscricao', $inscricao, 'POST');
    $json = json_decode($ret->content);
    if ($ret->status !== 200 || $json->error !== false) {
        $inscricao['error'] = ((is_array($json->error)) ? $json->error[0] : $json->error);
        $error[] = $inscricao;
        $consoleError->addRow([Helper::formatCpfCnpj($item['cpf']), $item['nome'], $inscricao['error']]);
    } else {
        $console->addRow([Helper::formatCpfCnpj($item['cpf']), $item['nome'], $json->content->codAluno, $json->content->codInscricao]);
    }
    $done++;
    $loader->done($done);
}
file_put_contents(__DIR__ . '/report/load-result-ok.txt', $console->getTable());
// CSV
$csv = NsUtil\Helper::array2csv($error);
file_put_contents(__DIR__ . '/report/load-result-error.csv', $csv);

$consoleError->display();
