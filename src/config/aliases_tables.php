<?php

/**
 * Aliases para nome das tabelas.
 * 
 * Basicamente no builder será gerado o nome de todas as tabelas do sistema e seu respectivo significado, porém pode ser necessário reconfigura-lo aqui.
 * Para isso basta editar a chave. 
 * Utilizar sempre lowercase
 */
$aliases_table = [];
$filename = $SistemaConfig['path'] . '/auto/config/aliases_tables.php';
if (file_exists($filename)) {
    include_once $filename;
}

$aliases_table = array_merge($aliases_table, [
    'curso_disponivel' => 'Cursos disponíveis',
    'usuariotipo' => 'Tipo de usuário',
    'perfilpermissao' => 'Perfil de permissões',
    'professores' => 'Professores',
    'alunos' => 'Alunos',
    'gradealuno' => 'Grade Alunos',
    'gradepolo' => 'Grade Curricular',
    'appusuario' => 'Professor',
    'app' => 'Apps',
    'apilog' => 'Api Logs',
    'auxiliar' => 'Auxiliars',
    'cep' => 'Ceps',
    'empresa' => 'Empresas',
    'endereco' => 'Enderecos',
    'linktable' => 'Vinculos',
    'loginattempts' => 'Tentivas de acesso',
    'ltrel' => 'Lt Rels',
    'mensagem' => 'Mensagems',
    'mensagemgrupo' => 'Mensagem Grupos',
    'mensagemgrupousers' => 'Mensagem Grupo Userss',
    'municipio' => 'Municipios',
    'pais' => 'País',
    'post' => 'Posts',
    'postread' => 'Leitura de postagens', 'shared' => 'Shareds',
    'shareduser' => 'Shared Users',
    'sistemafuncao' => 'Funções do sistema',
    'sistemalog' => 'Auditoria',
    'status' => 'Status',
    'trash' => 'Trashs',
    'uf' => 'Ufs',
    'uploadfile' => 'Arquivos',
    'usuario' => 'Usuários',
    'usuariopermissao' => 'Permissões de usuários',
    'avaliacao' => 'Avaliações',
    'bolsa' => 'Bolsas',
    'curso' => 'Cursos',
    'financeiro' => 'Financeiro',
    'forum' => 'Forums',
    'grade' => 'Grades',
    'gradealuno' => 'Grade de aluno',
    'gradepolo' => 'Grade de polo',
    'leciona' => 'Professor leciona',
    'matricula' => 'Matriculas',
    'modulo' => 'Módulos',
    'polo' => 'Polos',
    'reembolso' => 'Reembolsos',
    'reembolsomotivo' => 'Motivos de reembolso',
    'solicitacaocompra' => 'Solicitação de compras',
    'indisp' => 'Indisponibilidade de usuários',
    'colaboradores' => 'Colaboradores',
    'statusfinanceiro' => 'Status',
    'tipomatricula' => 'Tipo'
        ]);

