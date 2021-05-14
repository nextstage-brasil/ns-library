<?php

class ForcaBrutaControl {

    private $dao, $user, $pass, $geo;

    public function __construct($user, $pass) {
        $this->user = $user;
        $this->pass = Helper::codifica($pass);
        $this->geo = GeoLocalizacao::get(getenv("REMOTE_ADDR"));
        unset($this->geo['credit']);

        $this->dao = new EntityManager(new LoginAttempts());
    }

    public function add($motivo) {
        Log::logTxt('geo', $this->geo);
        $obj = $this->dao->getObject();
        $obj->setId(0);
        $obj->setCreatetimeLoginAttempts(gmdate('Y-m-d H:i:s'));
        $obj->setIpLoginAttempts($this->geo['request']);
        $obj->setUserLoginAttempts($this->user);
        $obj->setPassLoginAttempts($this->pass);
        $obj->setMotivoLoginAttempts($motivo);
        $obj->setGeoLoginAttempts($this->geo);
        $this->dao->setObject($obj)->save();
        if ($this->dao->getObject()->getError())  {
            Api::result(500, ['error' => $this->dao->getObject()->getError()]);
        }
    }

    /**
     * 
     * @param type $maxAttempts Maximo de tentaivas dentro do periodo estipulado
     * @param type $timeToWait tempo em minutos de bloqueio apos atingir maximo de tentaivas
     * @return boolean
     */
    public function checkForcaBruta($maxAttempts = 3) {
        $time = date('Y-m-d H:i:s', time() - (60*60*2)); /// 2 horas de bloqueio para o IP de tentativa
        $condicao = [
            'createtimeLoginAttempts' => ['>', "'$time'"],
            'ipLoginAttempts' => $this->geo['request']
        ];
        $attempts = $this->dao->getAll($condicao, false);
        if (count($attempts) > $maxAttempts) {
            // registrar em logs o alerta de tentativa de intrusão por força bruta
            Log::log('ALERTA', 'Possível tentativa de acesso por força bruta', '', '', [
                'username' => $this->user,
                'password' => $this->pass,
                'IP' => $this->geo,
            ]);
            // Notificar interessados
            
            // encerrar transação
            Api::result(401, ['error' => 'Seu IP esta bloqueado para acesso']);
            return true;
        } else {
            return false;
        }
    }

}
