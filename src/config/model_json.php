<?php

/**
 * Cadastro / modelagem dos campos extras em json para cada entidade.
 * 
 * Utilizar a chave no modelo camelCase, exemplo: nomeUser em vez de nome_user.
 * No Helper, temos um metodo para apoio no preenchimento do padrão: Helper::getExtrasConfig()
  ao gerar o controller, o builder lanca um comentário no controller com os campos a serem editados conforme modelagem
 */
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

// EXTRAS JSON
$MODEL_JSON = [
// Exemplo
    'extrasModulo' => [
        'enc' => ['default' => 3, 'grid' => 'col-sm-3', 'type' => 'number', 'class' => '', 'ro' => 'false', 'tip' => 'Quantidade de encontros necessários para este módulo', 'label' => 'Quantidade de encontros'],
        'reoc' => ['default' => 'S', 'grid' => 'col-sm-3', 'type' => 'select', 'list' => [
                ['id' => 'S', 'label' => 'Semanal'],
                ['id' => 'M', 'label' => 'Mensal'],
            ], 'class' => '', 'ro' => 'false', 'tip' => 'Reocorrência prevista para as aulas', 'label' => 'Reocorrência'],
        'dur' => ['default' => '3:00', 'grid' => 'col-sm-4', 'type' => 'text', 'class' => 'duracao', 'ro' => 'false', 'tip' => 'Duração em minutos de cada encontro, horas : minutos', 'label' => 'Duração do encontro'],
        'ava' => ['default' => 2, 'grid' => 'col-sm-2', 'type' => 'number', 'class' => '', 'ro' => 'false', 'tip' => 'Quantidade de avaliaçãoes necessárias', 'label' => 'Avaliações'],
    ],
    'extrasCurso' => [
        'vlrInscricao' => Helper::getExtrasConfig('Valor da inscrição', 'col-2', 'text', 'Na sua maioria, as aulas são ministradas de qual forma?', false, false, 'decimal')
    ],
    'extrasPolo' => [
        'tp' => Helper::getExtrasConfig('Tipo de aulas', 'col-lg-4', 'select', 'Na sua maioria, as aulas são ministradas de qual forma?', [
            ['id' => 'M', 'label' => 'Misto'],
            ['id' => 'P', 'label' => 'Presencial somente'],
            ['id' => 'V', 'label' => 'Virtual somente']
                ], $ro, $class, 'P')
    ],
    'extrasFinanceiro' => [
        'tp' => Helper::getExtrasConfig('Tipo', 'col-3', 'select', 'Tipo do lançamento', [
            ['id' => 'C', 'label' => 'Contas a receber'],
            ['id' => 'D', 'label' => 'Contas a pagar'],
                ], $ro, $class, 'C'),
        'idMatricula' => Helper::getExtrasConfig('Matricula relacionada', 'col-6', 'int')
    ],
    'extrasAvaliacao' => [
        'avaliacoes' => Helper::getExtrasConfig('Relação de avaliações registradas, conforme exigido pelo módulo', 'col-6', 'int'),
        'recuperacao' => Helper::getExtrasConfig('Nota da recurperação', 'col-6', 'int'),
    ],
    'extrasMatricula' => [
        'confirmDate' => Helper::getExtrasConfig('Data de confirmação', 'col-4', 'text'),
        'taxaInscricao' => Helper::getExtrasConfig('Valor da taxa de inscrição', 'col-4', 'int'),
        'confirmIdUsuario' => Helper::getExtrasConfig('Usuario que confirmou a inscrição', 'col-4', 'int')
    ],
    'extrasEmpresa' => [
        'dirNome' => Helper::getExtrasConfig('Nome do diretor', 'col-md-4', 'text'),
        'dirEmail' => Helper::getExtrasConfig('E-mail do diretor', 'col-md-5', 'text'),
        'dirNotifica' => Helper::getExtrasConfig('Notificar inscrições?', 'col-md-3', 'boolean', 'Caso sim, será enviado por e-mail uma mensagem a cada nova inscrição registrada no site'),
        'admNome' => Helper::getExtrasConfig('Nome do administrador', 'col-md-4', 'text'),
        'admEmail' => Helper::getExtrasConfig('E-mail do administrador', 'col-md-5', 'text'),
        'admNotifica' => Helper::getExtrasConfig('Notificar inscrições?', 'col-md-3', 'boolean', 'Caso sim, será enviado por e-mail uma mensagem a cada nova inscrição registrada no site'),
        'textoInscricao' => Helper::getExtrasConfig('Texto do e-mail para confirmação de inscrição', 'col-12', 'summernote', 'Este texto será utilizado no envio da confirmação de novas inscrições')
    ],
];
