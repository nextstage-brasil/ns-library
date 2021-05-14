<?php
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}
include Config::getData('pathView') . '/template/template1.php';
//Log::ver($_POST);
if ($_SESSION['od1Attemps'] > 3) {
    echo '<h1 class="text-center">'.$_SESSION['od1Attemps'].'<i class="fa fa-lock fa-5x"></i><br/>Acesso bloqueado. Responsáveis notificados</h1>';
    die();
}
$hash = hash('sha256', $_POST['devL'] . $_POST['devP']);

if ($hash === 'f11a3328749380afa20167a5e0133a279700423b6e917a55abe52147933c17e6') {
    $_SESSION['od1Attemps'] = 0;
    $_SESSION['od1'] = true;
    $location = Config::getData('url') . $_SESSION['oldRoute'];
    header("Location:$location");
} else {
    $_SESSION['od1Attemps'] ++;
}
$form = new Form('', '', '', "POST", "multipart/form-data", "");
$form->addElement(Html::input(['ng-model' => 'devL', 'name' => 'devL'], 'Login'), 'col-12');
$form->addElement(Html::input(['ng-model' => 'devP', 'name' => 'devP', 'type' => 'password'], 'Senha'), 'col-12');
$form->addElement(Html::inputSubmit(), 'col-12');
echo '<h1 class="text-center">Acesso Restrito</h1>';
?>
<div style="width: 400px; margin: 0 auto;" class="text-center">
    <img ng-src="view/images/logo-alone.png" class="img-rounded mb-3" />
    <?= $form->printForm() ?>    
</div>


<?php
include Config::getData('pathView') . '/template/template2.php';
