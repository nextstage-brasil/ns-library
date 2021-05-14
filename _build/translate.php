<?php

require_once '../library/SistemaLibrary.php';
$langs = [
    'pt_BR' => 'Português Brasileiro',
    'en_US' => 'Inglês',
    'es_ES' => 'Espanhol',
];
$lang = (($_GET['lang']) ? $_GET['lang'] : 'pt_BR');
$fileDefault = Config::getData('path') . '/src/config/lang/pt_BR.php';
$jsonDefault = file_get_contents($fileDefault);
$arrayDefault = json_decode($jsonDefault, true);
ksort($arrayDefault);

$file = Config::getData('path') . '/src/config/lang/' . $lang . '.php';
if ($_POST['Enviar'] === 'Atualizar Dicionário') {
    unset($_POST['Enviar']);
    $json = json_encode($_POST);
    Helper::saveFile($file, '', $json, 'SOBREPOR');
}
$json = file_get_contents($file);
$array = json_decode($json, true);

$form = new Form('', '', '', "post", "multipart/form-data", '');
foreach ($arrayDefault as $key => $value) {
    $value = (($array[$key]) ? $array[$key] : $key);
    $element = '<div class="form-group"><label>' . $key . '</label><input type="text" class="form-control" name="' . $key . '" value="' . $value . '"></div>';
    $form->addElement($element, 'col-sm-6');
}
$form->addElement('<a class="btn btn-link" href="index.php">Voltar</a>'.Html::inputSubmit('Atualizar Dicionário'), 'col-sm-12 text-center');
$formDicionario = new Form('switchDicionario', '', '', 'GET');
$formDicionario->addElement(Html::inputSelectFromArray('lang', 'Language', $langs, $lang, false, 'document.getElementById(\'switchDicionario\').submit()'), 'col-6 text-center');

$html = '<h1 class="text-center">NS Framework - Dicionário de Traduções</h1>';
$html .= '<h5 class="text-center alert alert-success">' . $formDicionario->printForm() . '</h5>';
$html .= $form->printForm();
$html .= '';

$fileTemplate = Config::getData('path') . '/view/template/000-template.html';
$template = new Template($fileTemplate, ['TITLE' => 'Dicionário de Tradução ' . $lang, 'CONTENT' => $html]);
echo $template->render();
