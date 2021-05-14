<?php
require_once './library/SistemaLibrary.php';

$token = Config::getData('params')[1];

$ctr = new UsuarioController();
$dao = new EntityManager(new Usuario());


$condicao = [
    'tokenAlteraSenhaUsuario' => $token,
    'tokenValidadeUsuario' => ['>=', "'" . date('Y-m-d') . "'"]
];
$user = $dao->getAll($condicao, false, 0, 1)[0];
if (!($user instanceof Usuario)) {
    die('Token inválido');
}
$nome = $user->getNomeUsuario();

require_once Config::getData('pathView') . '/template/includes.php';

$form = new Form('form1', '', "recovery/0/$token");
$form->addElement(Html::msgDicaTxt('Sua nova senha deve conter pelo menos 8 caracateres, com letras e numeros<br/><br/>') . '', 'col-sm-12');
$form->addElement(Html::input(['ng-model' => 'args.novaSenhaA', 'type' => 'password', 'required' => 'required'], 'Nova Senha:'), 'col-sm-6');
$form->addElement(Html::input(['ng-model' => 'args.novaSenhaB', 'type' => 'password', 'required' => 'required'], 'Confirme Nova Senha:'), 'col-sm-6');
$form->addElement('', 'col-sm-4');
$form->addElement('<button ng-show="!working && args.novaSenhaA.length>=8 && args.novaSenhaA === args.novaSenhaB" class="btn btn-primary btn-lg mb-5" ng-click="alteraSenha()">Enviar</button>', 'col-sm-4');
$form->addElement('', 'col-sm-4');
?>

<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
        <title>Recovery</title>
        <?= $JS_INCLUDE . Config::getData('js_config') ?>

    </head>
    <body class="page-login" ng-app="starter">
        <div ng-controller="AppController"></div>
        <h2 class="alert alert-info text-center">Recuperação de Senha</h2>
        <div class="container cs-content" ng-controller="RecoveryController">
            <div class="cs-container text-center" style="max-width: 640px;margin: 0px auto;">
                <h3>Olá <?= explode(' ', $nome)[0] ?></h3>
                <div ng-show="!alteracaoEfetuada">
                    <?= $form->printForm() ?>
                    <p><small>Caso não tenha sucesso nesta alteração, <br/>encaminhe email para gdp@datacloudbrasil.com.br informando o ocorrido.</small></p>
                </div>                
            </div>
        </div>

        <!-- template alert modal -->
        <div id="alertModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h3></h3>
                        <h4 class="modal-title"></h4>
                    </div>
                    <div class="modal-body">
                        <p></p>
                    </div>
                    <div class="modal-footer text-center">
                        <button type="button" class="btn btn-info" data-dismiss="modal">Fechar</button>
                    </div>
                </div>

            </div>
        </div>
        <!-- template loading -->
        <div id="loading" class="modal fade" role="dialog">
            <div class="modal-dialog text-center">
                <img src="<?= Config::getData('urlView') ?>/images/loading.gif" style="width: 200px; height: 200px;" alt=""/>
            </div>
        </div>
        <div id="barraAviso" class="alert alert-success text-center d-print-none" style="padding: 5px;"></div>
        <?php
        Component::init('Recovery-script.js');
        ?>

    </body>
</html>