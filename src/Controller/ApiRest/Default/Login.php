<?php

namespace NsLibrary\Controller\ApiRest\Default;

use NsApp\NsLibrary\Entities\Usuario;
use NsLibrary\Config;
use NsLibrary\Controller\ApiRest\AbstractApiRestController;
use NsLibrary\Token;
use NsUtil\Api;
use NsUtil\Password;

if (!defined("SISTEMA_LIBRARY")) {
    die("Login: Direct access not allowed. Define the SISTEMA_LIBRARY contant to use this class.");
}

class Login extends AbstractApiRestController {

    private $entitieName = 'Usuario';

    public function __construct(Api $api) {
        $this->init($api);
        $this->controllerInit(
                $this->entitieName,
                new Usuario(),
                'Usuario',
                'Usuario',
                Config::getData('entitieConfig')[$this->entitieName]['camposDate'],
                Config::getData('entitieConfig')[$this->entitieName]['camposDouble'],
                Config::getData('entitieConfig')[$this->entitieName]['camposJson'],
        );
    }

    public function create(): void {
        (new \NsUtil\Validate())
                ->addCampoObrigatorio('login', 'Informe seu login', 'string')
                ->addCampoObrigatorio('password', 'Informe sua senha de acesso', 'string')
                ->runValidateData($this->dados, $this->api, 403);

        $user = (new Usuario())->list(['emailUsuario' => $this->dados['login']])[0];
        if (!($user instanceof Usuario)) {
            $this->errorResponse('Usuário não localizado', Api::HTTP_NOT_FOUND);
        }

        if (!Password::verify((string) $this->dados['password'], $user->getSenhaUsuario())) {
            $this->errorResponse('Acesso não autorizado', Api::HTTP_FORBIDDEN);
        }

        if ($user->getDataSenhaUsuario() < date('Y-m-d', time() - (60 * 60 * 24 * 365))) {
            $this->errorResponse('Senha vencida', Api::HTTP_FORBIDDEN);
        }

        $this->response([
            'token' => Token::createGuide($user->getId(), $user->getEmailUsuario(), $user->getNomeUsuario(), $user->getTipoUsuario(), $user->getSessionTimeUsuario()),
            'expires' => Token::$timeToExpire
        ]);
    }

    public function renew(): void {
        $token = Token::refresh($this->api->getTokenFromAuthorizationHeaders());
        $this->response([
            'token' => $token,
            'expires' => Token::$timeToExpire
        ]);
    }

}
