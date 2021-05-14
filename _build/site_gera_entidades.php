<?php

function m($descricao = '', $tipo = 'string', $default = null) {
    return [$descricao, $tipo, $default];
}

/* Arquivo para gerar as entidades para uso no site
 * Padrão: 
 * 'Fotografo' => [
  'id_fotografo' => m('ID do usuário', 'int'),
  'nome' => m('Nome do usuário'),
  'icone' => m('Logomarca do usuário, caso tenha')
  ],
 */
$models = [
    'Turma' => [
        'cod_turma' => m('Código da turma'),
        'prim_encontro' => m('Data do primeiro encontro'),
        'ult_encontro' => m('Data do ultimo encontro'),
        'id_polo' => m('ID do polo relacionado', 'int'),
        'nome_polo' => m('Nome do polo'),
        'id_modulo' => m('ID do modulo', 'int'),
        'nome_modulo' => m('Nome do módulo'),
        'id_usuario' => m('ID do professor', 'int'),
        'nome_usuario' => m('Nome do professor'),
        'qtde_alunos_total' => m('Quantidade de alunos desta turma', 'int'),
        'qtde_alunos_confirmado' => m('Quantidade de alunos desta turma', 'int'),
        'qtde_alunos_recusado' => m('Quantidade de alunos desta turma', 'int'),
        'qtde_alunos_pendente' => m('Quantidade de alunos desta turma', 'int'),
        'encontros' => m('Encontros', 'jsonb')
    ],
    'Encontro' => [
        'cod_turma' => m('Código da turma'),
        'data_encontro' => m('Data do encontro'),
        'nome_modulo' => m('Nome do módulo')
    ],
    'Aluno' => [
        'id_usuario' => m('ID do aluno', 'int'),
        'nome_usuario' => m('Nome do aluno'),
        'email_usuario' => m('E-mail do aluno'),
        'celular_usuario' => m('Celular'), 
        'id_matricula' => m('Matricula em questão deste usuário', 'int')
    ],
    'AlunoEncontro' => [
        'id_usuario' => m('ID do aluno', 'int'),
        'nome_usuario' => m('Nome do aluno'),
        'email_usuario' => m('E-mail do aluno'),
        'id_grade_aluno' => m('Código do encontro', 'int'),
        'is_presente' => m('Aluno esta presente?', 'boolean'),
    ],
    'Nota' => [
        'order' => m('Ordem da avaliação', 'int'),
        'nota' => m('Nota atribuida', 'double'),
        'peso' => m('Peso para cálculo de média', 'int', 1),
        'date' => m('Data da atribuição', 'date'),
        'ref' => m('Código ou ID de referência que originou esta nota. Não obrigatório.', 'string'),
    ]
];

// iteração sobre os models para gerar os arquivos de entidades
$example = $doc = [];
foreach ($models as $key => $item) {
    $file = 'SiteModel' . $key;
    $dados = [
        'entidade' => $file,
        'tabela' => $key,
        'cpoID' => 'id' . $key,
    ];
    $dados['set'][] = '$obj = new ' . $file . '();
        ';
    // geração dos atributos
    foreach ($item as $k => $v) {
        $dados ['atributos'][] = [
            'nome' => Helper::name2CamelCase($k),
            'coments' => $v[0],
            'tipo' => $v[1],
            'valorPadrao' => ( ($v[3] !== null) ? $v[3] : "''"),
            'maxsize' => 250,
        ];
        $dados['doc'][Helper::name2CamelCase($k)] = "$v[1]: $v[0]";
        $dados['example'][Helper::name2CamelCase($k)] = "";
        $dados['set'][] = '$obj->set' . ucwords(Helper::name2CamelCase($k)) . '($item->get' . ucwords(Helper::name2CamelCase($k)) . '());
        ';
    }
    // Salvar entidade
    $template = EntidadesCreate::get($dados);
    echo $file . PHP_EOL;
    Helper::saveFile(Config::getData('path') . '/auto/entidades/' . $file . '.class.php', false, $template, 'SOBREPOR');

    // Gerar documentação
    $doc[$key] = $dados['doc'];
    Helper::saveFile(__DIR__ . '/doc/api/model_' . $key . '.json', '', json_encode($dados['example']), 'SOBREPOR');

    // setter facilitado
    $dados['set'][] = '$out[ ] = parent::objectToArray($obj);
        ';
    Helper::saveFile(__DIR__ . '/doc/api/setter/model_' . $key . '.txt', '', implode("\n", $dados['set']), 'SOBREPOR');
}

Helper::saveFile(__DIR__ . '/doc/api/site_model.json', '', json_encode($doc), 'SOBREPOR ');
