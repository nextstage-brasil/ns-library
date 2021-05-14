<?php
// Automação de Criação de Sistema - 13/01/2020 07:34:44
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}
include Config::getData('path') . '/view/template/template1.php';
$title = '';
?>

<div ng-controller="HomeController">

    <div class="row">
        <div class="col-sm-6 col-lg-3 mb-2"><ns-card-home card-class="warning" title="Inscritos" valor="{{totais.inscritos}}" descricao="Total de inscrições" click="nav('matricula')"></ns-card-home></div>
        <div class="col-sm-6 col-lg-3 mb-2"><ns-card-home card-class="warning" title="Inscritos na semana" valor="{{totais.inscritosSemana}}" descricao="Total de inscrições na semana atual" click="nav('matricula')"></ns-card-home></div>
        <div class="col-sm-6 col-lg-3 mb-2"><ns-card-home card-class="warning" title="Inscritos no mês" valor="{{totais.inscritosMes}}" descricao="Total de inscrições no mês atual" click="nav('matricula')"></ns-card-home></div>
        <div class="col-sm-6 col-lg-3 mb-2"><ns-card-home card-class="success" title="Matriculados" valor="{{totais.matriculas}}" descricao="Total de alunos matriculados" click="nav('matricula')"></ns-card-home></div>
        <div class="col-sm-6 col-lg-3 mb-2"><ns-card-home card-class="danger" title="Evasão" valor="{{totais.evasao}}" descricao="Alunos que não confirmaram o próximo módulo" click="nav('matricula')"></ns-card-home></div>
        <div class="col-sm-6 col-lg-3 mb-2"><ns-card-home card-class="danger" title="Inadimplentes" valor="{{totais.inadimplentes}}" descricao="Alunos com pendências financeiras" click="nav('matricula')"></ns-card-home></div>
        <div class="col-sm-6 col-lg-3 mb-2"><ns-card-home card-class="info" title="Presentes" valor="{{totais.presentes}}" descricao="Alunos presentes no último encontro" click="nav('turmas')"></ns-card-home></div>
    </div>
</div>





<?php
Component::init(['Home-script.js']);

include Config::getData('path') . '/view/template/template2.php';
