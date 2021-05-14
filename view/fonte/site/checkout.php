<?php

use CWG\PagSeguro\PagSeguroCompras;

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}
UsuarioController::loginBryan(); // sem login
// Variaveis
$crypto = new NsUtil\Crypto(Config::getData('token'));
$id = (int) $crypto->decrypt(base64_decode(Config::getData('params')[1]));
$fpId = (int) $crypto->decrypt(base64_decode(Config::getData('params')[2]));
$dao = new EntityManager(new Financeiro());

// Obter o financeiro a receber
$financeiro = $dao->getAll(['idFinanceiro' => $id])[0];
if (!($financeiro instanceof Financeiro)) {
    die('Não localizado.' . $id);
}



// Obter o curso
$curso = $dao->setObject(new Curso())->getAll(['idCurso' => $financeiro->getMatricula()->getIdCurso()])[0];
if (!($curso instanceof Curso)) {
    die('Curso não localizado. ' . $financeiro->getMatricula()->getIdCurso());
}
//Obter o aluno
$usuario = $dao->setObject(new Usuario())->getById($financeiro->getMatricula()->getIdUsuario());
$usuario instanceof Usuario;

$out = [
    'Aluno' => $usuario->getNomeUsuario(),
    'Matricula' => $financeiro->getIdMatricula(),
    'Valor' => 'R$' . ($financeiro->getValorFinanceiro() / 100),
    'Vencimento' => Helper::formatDate($financeiro->getVencimentoFinanceiro(), 'mostrar'),
    'Tipo' => $financeiro->getAuxiliar()->getNomeAuxiliar(),
    'Status' => $financeiro->getStatus()->getNomeStatus()
];

$html = '';
foreach ($out as $key => $val) {
    $html .= "$key: $val<br/>";
}

// Pagameto já concluindo, nada a fazer
if ($financeiro->getStatus()->getOrderStatus() > 90) {
    $out['Data de pagamento'] = Helper::formatDate($financeiro->getPagamentoFinanceiro(), 'mostrar');
    $html = '';
    foreach ($out as $key => $val) {
        $html .= "$key: $val<br/>";
    }
    echo (new Template(false, ['TITLE' => 'Checkout de pagamentos', 'CONTENT' => $html]))->render();
    die();
}

$fp = $dao->setObject(new FormaPgto())->getAll(['idFormaPgto' => $fpId])[0];
if (!($fp instanceof FormaPgto)) {
    // formas de pagamento
    $list = $dao->setObject(new FormaPgto())->getAll(['idFormaPgto' => ['>1', '']]);
    $html .= "<p>Escolha a forma de pagamento</p>";
    foreach ($list as $item) {
        $link = Config::getData('url') . '/checkout/fYnD/' . Config::getData('params')[1] . '/' . base64_encode($crypto->encrypt($item->getId()));
        $html .= '<a class="btn btn-primary mr-1" href="' . $link . '">' . $item->getNomeFormaPgto() . '</a>';
    }
} else {
    $fp = $dao->setObject(new FormaPgto())->getAll(['idFormaPgto' => $fpId])[0];
    $html .= '<p>Forma de pagamento selecionada: ' . $fp->getNomeFormaPgto();
    if (Config::getData('params')[3] !== 'confirm') {

        // Salvar a forma de pagamento
        $financeiro->setIdFormaPgto($fp->getId());
        $dao->setObject($financeiro)->save();

        $link = $_SERVER['REQUEST_URI'] . '/confirm';
        $text = (($fp->getId() < 200) ? 'Confirmar' : 'Concluir pagamento');
        $html .= '<div><a class="btn btn-primary" href="' . $link . '">' . $text . '</a></idv>';
    } else {
        if ($fp->getId() < 200) {
            // pagamento local, apenas registrar e baixar
            $html .= '<p>Efetue o pagamento diretamente na secretaria ou informe a execução da transferência</p>';
        } else {
            // Abrir o gateway
            $pagseguro = new PagSeguroCompras(Config::getData('pagseguro', 'email'), Config::getData('pagseguro', 'token'), Config::getData('pagseguro', 'sandbox'));
            $pagseguro->setNomeCliente($usuario->getNomeUsuario());
            $pagseguro->setEmailCliente('frederico@sandbox.pagseguro.com.br'); //$usuario->getEmailUsuario());
            $pagseguro->setReferencia(mb_strtoupper(substr($financeiro->getAuxiliar()->getNomeAuxiliar(), 0, 4)) . '_' . $financeiro->getId());
            $pagseguro->adicionarItem(1, $financeiro->getAuxiliar()->getNomeAuxiliar() . ' no curso ' . $curso->getNomeCurso(), ($financeiro->getValorFinanceiro() / 100), 1);
            $pagseguro->setNotificationURL(Config::getData('pagseguro', 'urlRetorno')); //URL para onde será enviado as notificações de alteração da compra (OPCIONAL)
            try {
                $success = "window.location.ref='" . Config::getData('url') . '/pagamentoRecebido' . "';"; //URL para onde o comprador será redicionado após a compra (OPCIONAL)
                $error = "setTimeOut(function(){window.location.ref='" . Config::getData('url') . '/checkout/fYnD/' . Config::getData('params')[1] . "';}, 1000);";
                $html .= $pagseguro->gerarLightbox($success, $error);
            } catch (Exception $e) {
                $html = $e->getMessage();
            }
        }
    }
}


echo (new Template(false, ['TITLE' => 'Checkout de pagamentos', 'CONTENT' => $html]))->render();
