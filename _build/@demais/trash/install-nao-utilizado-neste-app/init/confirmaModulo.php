<?php

require '../../../cron/_fn.php';
UsuarioController::loginBryan();

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



$con = new \NsUtil\ConnectionPostgreSQL(Config::getData('database', 'host'), Config::getData('database', 'user'), Config::getData('database', 'pwd'), Config::getData('database', 'port'), Config::getData('database', 'database'));

$query = "select id_matricula, hash_grade_polo as cod_turma, 'Inserção automática 2020' as obs 
FROM grade_aluno a 
INNER JOIN grade_polo b USING (id_grade_polo)
where dt_hr_grade_polo = '2021-02-18'";
$list = $con->execQueryAndReturn($query);
$dao = new EntityManager();
$ctr = new GradeAlunoController();
$done = 0;
$loader = new \NsUtil\StatusLoader(count($list), 'Carga de dados');
foreach ($list as $item) {
    $ctr->ws_confirmarGradeAluno($item, $dao);
    $done++;
    $loader->done($done);
}
