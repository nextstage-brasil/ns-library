<?php
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

//Helper::deleteFile(Config::getData('urlView') . '/js/js.ini.php', false, false);
require_once Config::getData('pathView') . '/template/includes.php';

// redirecionar caso ja exista login ativo
if (($_SESSION['user']['idUsuario'])) {
    header("Location:" . Config::getData('url') . "/home");
}

// para limpar o cache de acessos anteriores
unset($_SESSION['user']);
unset($_SESSION['login_vars']); // setada em temaplte1.php
unset($_SESSION['nav']);

// retornos registrados
if ($_SESSION['login_vars']) { // registrada em _template1.php
    $string = $_SESSION['login_vars'];
    $loginVars = json_decode($string, true);
    $login['error'] = str_replace('|', '<br/>', $loginVars['error']);
}
$locationUrl = (($loginVars['url'] ? $loginVars['url'] : Config::getData('url') . '/home')); //Config::getData('urlView') . "/index.php" ));
$locationUrl = Config::getData('url') . '/home';


Log::log('navegacao', $_SERVER['REQUEST_URI']);
$form = new Form('loginForm');
$form->addElement(Html::input(['autocomplete' => 'off', 'id' => 'user-name', 'ng-model' => 'Login.username', 'class'=>'cpf'], 'CPF'), 'col-sm-12');
$form->addElement(Html::input(['autocomplete' => 'off', 'ng-model' => 'Login.password', 'type' => 'password'], 'Senha'), 'col-sm-12');
$form->addElement(Html::input(['ng-click' => 'login()', 'name' => 'btnsubmit', 'class' => 'btn btn-lg btn-primary btn-block btn-signin btn-mp', 'type' => 'submit', 'value' => 'Entrar']), 'col-sm-12');
$arrayCSS = array();
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
        <title>::<?= Config::getData('title') ?>::</title>
        <?= $JS_INCLUDE . Config::getData('js_config') ?>


        <style>

            .form-group input[type="text"],
            .form-group input[type="password"]{
                background-color: #f5f5f5;
            }
            .btn-mp {
                background-color: #242448; /*#77C300;*/
            }
            .floating-label-range, .floating-label-select, p.form-control ~.floating-label, input:disabled ~ .floating-label, input:focus ~ .floating-label, input:not(:focus):valid ~ .floating-label {
                top: 5px;
            }
            .bg {
                background-image: url(view/images/bg-login.jpg);
                z-index: -1;
                background-repeat: repeat;
                width: 100%;
                height: 100%;
                margin: 0px auto;
                background-size: cover; /*Css padrão*/
                -webkit-background-size: cover; /*cover Css safari e chrome*/
                -moz-background-size: cover; /*Css firefox*/
                -ms-background-size: cover; /*Css IE */
                -o-background-size: cover; /*Css Opera*/
            }

            .login-box {
                width: 320px;
                margin: 50px auto;

            }
            .form-container {
                position: fixed; 
                z-index: -1;
                margin: 0 auto; 
                left: 50%; 
                top: 0px;
                margin-left:-200px;
                width: 400px;
                padding-top: 2px; 
                padding-bottom: 2px;
                box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
                background-color: #242448;
                opacity: 0.7;
                height: 100%;
            }
        </style>

        <!-- swal({}) -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

    </head>


    <body ng-app="starter" class="bg">
        <div ng-controller="AppController"></div>
        <div class="form-container"></div>
        <div id="content" class="login-box" ng-controller="LoginController">
            <div class="text-center mb-5">
                <img class="img img-fluid" src="<?= Config::getData('urlView') ?>/images/logo-2.jpg" alt="">
            </div>
            <?= $form->printForm() ?>

            <div class="row">
                <div class="col-sm-6">
                    <a class="btn btn-secondary btn-default btn-block text-center" ng-click="cadastroShow()">Registrar-me</a>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-secondary btn-default btn-block text-center" ng-click="forgotPassword()">Esqueci a senha</a>
                </div>
            </div>

            <alert-modal nsvar="cad" modal-id="cadastrar" title="Cadastre-se" confirm-action="cadastroSend()" btn-confirm-text="Enviar Cadastro">
                <?php
                $f = new Form();
                $f->addElement(Html::input(['ng-model' => 'cad.nome'], 'Qual seu nome?'), 'col-12');
                $f->addElement(Html::input(['ng-model' => 'cad.email', 'class' => 'email'], 'Qual seu email?'), 'col-12');
                $f->addElement(Html::input(['ng-model' => 'cad.celular', 'class' => 'fone'], 'Seu telefone celular?'), 'col-12');
                ?>
                <div class = "row">
                    <div class = "col-6">
                        <h3>Ambiente para cadastro de novos usuários autonomos</h3>
                        <p>Após preencher os dados ao lado, você irá receber em seu email um link para confirmação do cadastro</p>
                        <p>Basta seguir as instruções!</p>
                    </div>
                    <div class = "col-6">
                        <?= $f->printForm() ?>
                    </div>
                </div>


            </alert-modal>  

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
        <div id="loading" data-backdrop="static" class="modal fade" style="z-index:9999999" role="dialog">
            <div class="modal-dialog text-center p-5">
                <!--
                <img src="{url}/view/images/loading.gif" style="width: 200px; height: 200px;" alt=""/>
                -->
                <div class="spinner">
                    <div class="rect1"></div>
                    <div class="rect2"></div>
                    <div class="rect3"></div>
                    <div class="rect4"></div>
                    <div class="rect5"></div>
                </div>
                <h1>{{MSGINFO}}</h1>
            </div>
        </div>
        <div id="barraAviso" class="alert alert-success text-center d-print-none" style="padding: 5px;"></div>
        <script src="<?= Config::getData('url') . '/src/components/' . md5('LoginController.js') . '.js' ?>"></script>

        <?php
        Component::init('LoginController.js');
        Component::init('alertModal');
        ?>


    </body>


</html>

