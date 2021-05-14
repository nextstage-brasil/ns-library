<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

/**
 * 
 * @date 14/03/19 10:34:52
 */
class MensagemController extends AbstractController {

    /**
     * @create 14/03/2019
     */
    public function __construct() {
        $this->ent = 'Mensagem';
        $this->camposDate = ['dataMensagem', 'readDateMensagem'];
        $this->camposDouble = [];
        $this->camposJson = ['extrasMensagem'];

        $this->condicao = [];
        $this->object = new $this->ent();

        if (method_exists($this->object, 'setIdUsuario')) {
            $this->object->setIdUsuario($_SESSION['user']['idUsuario']);
            $this->condicao['idUsuario'] = $_SESSION['user']['idUsuario'];
        }
        if (method_exists($this->object, 'setIdEmpresa')) {
            $this->object->setIdEmpresa($_SESSION['user']['idEmpresa']);
            $this->condicao['idEmpresa'] = $_SESSION['user']['idEmpresa'];
        }

        // Campos default para o JSON. Chave => Config. 
        $this->jsonDefault['extrasMensagem'] = ['Campo a configurar' => ['grid' => 'col-sm-6', 'type' => 'text', 'class' => '', 'ro' => 'false', 'tip' => '']];
    }

    /**
     * @create 14/03/2019
     * Chama o método em parent e retorna. Caso seja necessário alguma intervenção nesta classe
     */
    public function toView($obj) {
        return parent::toView($obj);
    }

    ## Metodos padrão para WebService (ws)
    /**
     * @create 14/03/2019
     * Método responsavel por devolver uma entidade nova e vazia
     */

    public function ws_getNew() {
        return $this->toView($this->object);
    }

    /**
     * @create 14/03/2019
     * Método responsavel por devolver uma entidade nova e vazia
     */
    public function ws_getById($dados) {
        Poderes::verify('Mensagem', 'Mensagem', 'ler');
        $ent = parent::getById($dados['id'], true);
        return $this->toView($ent);
    }

    /**
     * @create 14/03/2019
     * Metodo responsavel por gerar relação de dados da entidade. Acesso via JSON.
     */
    public function ws_getAll($dados) {
        Poderes::verify('Mensagem', 'Mensagem', 'listar');

        // IDs esperados
        foreach (['idMensagem', 'idUsuario', 'idUploadfile'] as $v) {
            if ((int) $dados[$v] > 0) {
                $this->condicao[$v] = (int) $dados[$v];
            }
        }
        $inicio = (int) $dados['pagina'];
        $fim = 100; // paginação obrigatória
        $getRelacao = ((isset($dados['getRelacao'])) ? $dados['getRelacao'] : true);

        // set search padrão - ira procura por nomeENTIDADE
        $this->setSearch($dados);

        $entities = parent::getAll($dados, $getRelacao, $inicio, $fim, $order);
        $out = Helper::parseDateToDatePTBR($entities, $this->camposDate, $this->camposDouble, $this->camposJson);
        $this->setDadosComboSearch($dados, $out, $this->ent);

        return $out;
    }

    /**
     * @create 14/03/2019
     * @update 02/05/2019 - 09:36 - testado
     * Metodo responsavel por salvar uma entidade
     */
    public function ws_save($dados) {
        $dados['idUsuario'] = $_SESSION['user']['idUsuario'];
        $dados['destinoIdMensagem'] = $dados['destId'];
        $dados['textoMensagem'] = $dados['texto'];
        $dados['idMensagem'] = 0; // sempre uma nova, não tem update
        $id = parent::save($dados);
        // Retornar o objeto persisitido
        $item = $this->trataList([$this->lastObjectSave])[0];
        return $item;
    }

    /**
     * @create 14/03/2019
     * Metodo responsavel por remover uma entidade
     */
    public function ws_remove($dados) {
        Poderes::verify('Mensagem', 'Mensagem', 'excluir');
        return parent::remove($dados['id']);
    }

    /**
     * Método que monta a mensagem a ser enviada para o resposta
     * @update 02/05/2019 - 09:36 testado
     * @param array $list
     * @return boolean
     */
    private function trataList(array $list) {
        $item = new Mensagem();
        $title = false;
        $out = [];
        foreach ($list as $item) {
            $new = [
                'destId' => $item->getDestinoIdMensagem(),
                'texto' => $item->getTextoMensagem(),
                'index' => $item->getId(),
                'data' => Helper::formatDate($item->getDataMensagem(), 'mostrar', true, false)
            ];
            if ($item->getTypeUserMensagem() === false) { // usuario que escreveu para mensagens em grupos
                $new['remetente'] = (($item->getIdUsuario() === $_SESSION['user']['idUsuario']) ? 'Você' : $item->getUsuario()->getNomeUsuario());
            }
            if ($item->getIdUsuario() !== $_SESSION['user']['idUsuario']) { // avatra de quem remeteu eh o mesmo da conversa
                $new['avatar'] = true;
            }

            // mime type emoji
            // mime type audio
            // mime type video
            // mime type file / others
            // limpar o array, deixar somente o que precisa ser enviado
            $out[] = $new;
        }

        return $out;
    }

    /**
     * @create 23/11/2018
     * @update 02/05/2019 - 09:20 - Testado e funcionando
     * Metodo responsavel por gerar relação de dados da entidade. Acesso via JSON.
     */
    public function ws_getConversas($dados) {
        $idUser = $_SESSION['user']['idUsuario'];
        $dao = new EntityManager(new Mensagem());
        $out = [];

        if (!$dados['idUser']) {
            // Todas as conversas relacionadas a este usuario
            $query = 'select * from (
                    select distinct on (id_usuario, destino_id_mensagem) id_usuario, destino_id_mensagem, data_mensagem
                    from ' . Helper::setTable('app_mensagem') . '
                    where ((id_usuario=%1$s or destino_id_mensagem=%1$s) or (id_usuario=%1$s or destino_id_mensagem=%1$s))
                    limit 100
                ) p order by data_mensagem desc limit 100';
            $nuvemConversas = $dao->execQueryAndReturn(sprintf($query, $idUser));
            if (count($nuvemConversas) === 0) {
                return [
                    'messages' => [],
                    'totalNotRead' => 0
                ];
            }
            //Log::ver($nuvemConversas);
            // varrer as conversas, e isolar somente pessoas diferente de idUser
            $users = [];
            foreach ($nuvemConversas as $talk) {
                $id = (int) $talk['idUsuario'];
                if ((int) $talk['idUsuario'] === (int) $idUser) {
                    $id = $talk['destinoIdMensagem'];
                }
                $users[$id] = (int) $id; // todas as pessoas envolvidas em conversas, exceto o próprio usuário
            }
        } else {
            $users[] = (int) $dados['idUser'];
        }
        //Log::ver($users);
        // agora, com oso usuarios envolvidos, obter os dados de cada conversa
        $query = 'select nome_usuario, userout.id_usuario, app_uploadfile.filename_uploadfile as avatar,
            -- quantidade de mensagens total
            (select count(id_mensagem) from ' . Helper::setTable('app_mensagem') . ' where ( (id_usuario= %1$s and destino_id_mensagem=userout.id_usuario) or (id_usuario=userout.id_usuario and destino_id_mensagem=%1$s) )) total_messages,
            -- quantidade mensagens recebidas não lidas
            (select count(id_mensagem) from ' . Helper::setTable('app_mensagem') . ' where id_usuario=userout.id_usuario and destino_id_mensagem=%1$s and read_date_mensagem is null) not_read,
            -- última mensagem trocada
            (select json_agg(last) from (select data_mensagem as data, app_usuario.nome_usuario as remetente, texto_mensagem as message from ' . Helper::setTable('app_mensagem') . ' inner join ' . Helper::setTable('app_usuario') . ' using (id_usuario) where ( (app_mensagem.id_usuario= %1$s and destino_id_mensagem=userout.id_usuario) or (app_mensagem.id_usuario=userout.id_usuario and destino_id_mensagem=%1$s) ) order by id_mensagem desc limit 1) last) last_message,
            -- status do usuário
            (select max(createtime_log) from ' . Helper::setTable('app_sistema_log') . ' where usuario_id= userout.id_usuario) last_navigation
            from ' . Helper::setTable('app_usuario') . ' userout
            left join ' . Helper::setTable('app_uploadfile') . ' on userout.avatar_usuario= app_uploadfile.id_uploadfile
            where userout.id_usuario in (' . implode(', ', $users) . ')';
        $talks = $dao->execQueryAndReturn(sprintf($query, $idUser));
        $up = new UploadfileController();
        $limite = 29;
        $totalNotRead = 0;

        foreach ($talks as $talk) {
            $talk['lastNavigation'] = (($talk['lastNavigation']) ? $talk['lastNavigation'] : '1970-12-31');
            $talk['lastMessage'] = json_decode($talk['lastMessage'], true)[0];
            $talk['lastMessage']['message'] = substr($talk['lastMessage']['message'], 0, $limite) . ((strlen($talk['lastMessage']['message'])) > $limite ? '...' : '');
            $talk['avatar'] = $up->getThumbs($talk['avatar']);
            //unset($talk['lastNavigation']);
            $totalNotRead += $talk['notRead'];
            $talk['active'] = Helper::dateToMktime($talk['lastNavigation']) + (60 * 10) > Helper::dateToMktime();
            $out[] = $talk;
        }



        //Log::ver($out);
        return [
            'messages' => $out,
            'totalNotRead' => $totalNotRead
        ];
    }

    /**
     * @update 02/05/2019 - 09:39 - alteração das tabelas para prefixo app_
     * Ira montar a primeira chamada de mensagens, definir a data e hora da chamada..
     * @param type $dados
     */
    public function ws_getMessages($dados) {
        //sleep(3);// simular lentidão
        $idUser = $_SESSION['user']['idUsuario'];
        $id = (int) $dados['id'];
        $dao = new EntityManager(new Usuario());

        if (strlen($dados['lastUpdate']) > 0) {
            $lastUpdate = date('Y-m-d H:i:s', $dados['lastUpdate']);
            $queryLastUpdate = "app_mensagem.data_mensagem >= '$lastUpdate' and ";
        }

        // nome da conversa. Caso seja grupo, alterar a pesaui para mensagem_grupo
        /*
          $dao->setObject(new Usuario());
          $user = $dao->getAll(['idUsuario' => $id], true, 0, 1)[0];
          if (!($user instanceof Usuario)) {
          return ['error' => 'Usuário não localizado'];
          }
         */

        $dao->setObject(new Mensagem());
        $condicao = " ((app_mensagem.id_usuario= $idUser and destino_id_mensagem= $id) or (app_mensagem.id_usuario= $id and destino_id_mensagem= $idUser) ) and ativa_mensagem= true";
        $dao->setOrder('id_mensagem desc'); // ultimas 30 mensagens.
        // zerar lastupdate, pois o controle será feito por paginação
        if ((int) $dados['pagina'] > 0) {
            $queryLastUpdate = '';
        }

        // execute
        $list = $dao->getAll($queryLastUpdate . $condicao, true, (int) $dados['pagina'], 10);

        if (count($list) === 0 && !$lastUpdate) {
            // new talk
            $mensagem = new Mensagem();
            $mensagem->setIdUsuario($_SESSION['user']['idUsuario']);
            $mensagem->setAtivaMensagem('false');
            $mensagem->setDestinoIdMensagem($dados['id']);
            $mensagem->setTextoMensagem('Inicio de conversa');
            $mensagem->setUsuario(new Usuario(['nomeUsuario' => 'Nova conversa']));
            $list[0] = $mensagem;
        }
        $now = date('Y-m-d H:i:s');
        $query = "update " . Helper::setTable('app_mensagem') . " set read_date_mensagem= '$now' where data_mensagem <= '$now' and id_usuario=$id and destino_id_mensagem= $idUser";
        $dao->execQueryAndReturn($query);

        return [
            //'title' => $user->getNomeUsuario(),
            //'icone' => (($queryLastUpdate) ? '' : UploadfileController::toEntitie(parent::getAll(new Uploadfile(), ['idUploadfile' => $user->getAvatarUsuario()], false, 0, 1))['thumbs']),
            //'status' => $user->getStatusUsuario(),
            'messages' => $this->trataList($list),
            'lastUpdate' => Helper::dateToMktime(),
            'idUser' => $dados['id']
        ];
    }
    
    /**
     * Relação de usuários do sistema para iniciar uma conversa
     * @param type $dados
     * @return type
     */
    public function ws_getUsersToTalk($dados) {
        $user = new UsuarioController();
        $list = $user->searchByName($dados['Search']);
        $out = [];
        foreach ($list as $item)   {
            $out['comboSearchList'][] = [
                'id' => $item['idUsuario'],
                'value' => $item['nomeUsuario']
            ];
        }
        return $out;
    }
    
    
    /**
     * Retorna quantidade de mensagens não lida
     * @return type
     */
    public static function countMessageNotReadToUser() {
        $qtde = 0;
        if ($_SESSION['user']['idUsuario'] > 0) {
            $dao = new EntityManager(new Mensagem());
            $qtde = $dao->count([
                        'destinoIdMensagem' => $_SESSION['user']['idUsuario'],
                        'readDateMensagem' => ['is', 'null']
            ]);
        }
        return $qtde;
    }


}
