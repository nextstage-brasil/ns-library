<?php

require ('../library/SistemaLibrary.php');

$dao = new EntityManager(new Usuario());

$user = $dao->getAll(['idUsuario' => 1], false, 0, 1)[0];
if ($user instanceof Usuario) {
    $id = $user->getId();
} else {
    $id = 1;
}

$senha = 'dev'; //.mb_substr(md5(time()), 0,4);

$dados = array(
    'nomeUsuario' => 'Mestre',
    'emailUsuario' => 'dev',
    'loginUsuario' => 'dev@dev.com',
    'senhaUsuario' => Password::codificaSenha($senha),
    'tokenAlteraSenhaUsuario' => '',
    'tokenValidadeUsuario' => NULL,
    'ultAcessoUsuario' => NULL,
    'statusUsuario' => 1,
    'dataSenhaUsuario' => date('Y-m-d'),
    'tipoUsuario' => 2,
    'perfilUsuario' => 1,
    'avatarUsuario' => 0,
    'sessionTimeUsuario' => 60,
);

$user = new Usuario($dados);
if (method_exists($user, 'setIdEmpresa')) {
    // criar a empresa antes
    $emp = new Empresa();
    $empresa = $dao->setObject($emp)->getAll(['idEmpresa' => 1], false, 0, 1)[0];
    if (!($empresa instanceof Empresa)) {
        $emp->setNomeEmpresa('Developers INIT');
        $emp->setIntegracoesEmpresa('');
        $ret = $dao->setObject($emp)->save();
        if ($ret->getError() !== false) {
            die($ret->getError());
        }
        $empresa = $dao->getObject();
    }
    $user->setIdEmpresa($empresa->getId());
}
$ret = $dao->setObject($user)->save();
$dao->getObject()->setId($id);
$dao->save();

if ($dao->getObject()->getError() === false) {
    $mensagem = 'Usuario criado com sucesso:\\n Login: dev e senha:' . $senha;
} else {
    $mensagem = 'Erro ao criar usuario: ' . $dao->getObject()->getErrorToString();
}

$template = new Template(Config::getData('pathView') . '/template/000-template.html', [
    'TITLE' => 'NS Builder', 'CONTENT' => '<h1 class="text-center">NS Builder</h1>'
    . '<a class="btn btn-info" onclick="javascript:history.back()">Voltar</a>'
    . '<script>alert(\'' . $mensagem . '\');history.back();</script>'
        ]);

echo $template->render();


