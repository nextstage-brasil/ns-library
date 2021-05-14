<?php

$dao = new EntityManager();
$query = "(select * from (select -1 as id, '--Escolha uma opção--' as label) t) UNION (%s) order by label";

// Listas: se estiverem setadas a chave do cormulário será do tipo select
$listas = [
    'idPolo' => $dao->execQueryAndReturn(sprintf($query, "SELECT id_polo as id, nome_polo as label from polo where id_empresa=1 and is_alive_polo='true' order by nome_polo ASC")),
    'sexo' => [
        ['id' => -1, 'label' => '--Escolha--'],
        ['id' => 'Masculino', 'label' => 'Masculino'],
        ['id' => 'Feminino', 'label' => 'Feminino'],
    ],
    'igreja' => [
        ['id' => -1, 'label' => '--Escolha uma opção--'],
        // Palavra Viva
        ['id' => 'Palavra Viva Antônio Carlos|12', 'label' => 'Antônio Carlos', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Aririú|13', 'label' => 'Aririú', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Barreiros|16', 'label' => 'Barreiros', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Belford Roxo|50', 'label' => 'Belford Roxo', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Biguaçú|18', 'label' => 'Biguaçú', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Bocaiuva do Sul|95', 'label' => 'Bocaiuva do Sul', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Canasvieiras|41', 'label' => 'Canasvieiras', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Caxias do Sul|54', 'label' => 'Caxias do Sul', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Centro|20', 'label' => 'Centro', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Colombo|21', 'label' => 'Colombo', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Garopaba|23', 'label' => 'Garopaba', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Ingleses|29', 'label' => 'Ingleses', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Lajeado|86', 'label' => 'Lajeado', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Lisboa|27', 'label' => 'Lisboa', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Morro das Pedras|37', 'label' => 'Morro das Pedras', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Nova Iguaçú|30', 'label' => 'Nova Iguaçú', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Palhoça|32', 'label' => 'Palhoça', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Paulo Lopes|31', 'label' => 'Paulo Lopes', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Pelotas|55', 'label' => 'Pelotas', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Presidente Prudente|52', 'label' => 'Presidente Prudente', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Rio Vermelho|35', 'label' => 'Rio Vermelho', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva São Leopoldo|58', 'label' => 'São Leopoldo', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Sede|10', 'label' => 'Sede', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Tapera|38', 'label' => 'Tapera', 'group' => 'Palavra Viva'],
        ['id' => 'Palavra Viva Videira|101', 'label' => 'Videira', 'group' => 'Palavra Viva'],
        // Outras
        ['id' => 'Assembléia de Deus', 'label' => 'Assembléia de Deus', 'group' => 'Outras'],
        ['id' => 'Igreja Universal do Reino de Deus', 'label' => 'Igreja Universal do Reino de Deus', 'group' => 'Outras'],
        ['id' => 'Congregação Cristã no Brasil', 'label' => 'Congregação Cristã no Brasil', 'group' => 'Outras'],
        ['id' => 'Igreja do Evangelho Quadrangular', 'label' => 'Igreja do Evangelho Quadrangular', 'group' => 'Outras'],
        ['id' => 'Igreja Adventista do Sétimo Dia', 'label' => 'Igreja Adventista do Sétimo Dia', 'group' => 'Outras'],
        ['id' => 'Igreja Batista', 'label' => 'Igreja Batista', 'group' => 'Outras'],
        ['id' => 'Igreja Luterana', 'label' => 'Igreja Luterana', 'group' => 'Outras'],
        ['id' => 'Igreja Presbiteriana', 'label' => 'Igreja Presbiteriana', 'group' => 'Outras'],
        ['id' => 'Outras', 'label' => 'Outras (Informe nas observações)', 'group' => 'Outras'],
    ],
    //'escolaridade' => $dao->execQueryAndReturn(sprintf($query, "SELECT nome_escolaridade||'|'||id_escolaridade::text as id, nome_escolaridade as label from escolaridade order by order_escolaridade ASC")),
    'escolaridade' => $dao->execQueryAndReturn(sprintf($query, "SELECT id_escolaridade as id, nome_escolaridade as label from escolaridade order by order_escolaridade ASC")),
];

// Geração do config
$itens = [];
$config = [
    //'idCurso' => 'Curso|hidden|none|col-md-6|None|7',
    'idPolo' => 'Polo|select|none|col-md-6',
    'igreja' => 'Igreja|select|none|col-md-6',
    'nome' => 'Nome|text|none|col-lg-6',
    'email' => 'Email|text|none|col-lg-6|Informe um e-mail válido pois este será nosso canal de comunicação',
    'celular' => 'Celular|text|fone|col-6 col-md-4 col-lg-4',
    'sexo' => 'Sexo|select|none|col-6 col-md-4 col-lg-4',
    'dataNascimento' => 'Data de nascimento|text|data|col-12 col-md-4 col-lg-4',
    'cpf' => 'CPF|text|cpf|col-12 col-md-6',
    'escolaridade' => 'Escolaridade|text|none|col-md-6',
    'profissao' => 'Profissão|text|none|col-lg-6|Profissão que exerce e conhecimentos em areas especificas',
    'empresa' => 'Empresa onde trabalha|text|none|col-lg-6|Empresa onde trabalha atualmente',
    'obs' => 'Observações|textarea|none|col-md-12',
];


foreach ($config as $key => $val) {
    $input = explode("|", $val);
    $itens[$key] = [
        'grid' => $input[3],
        'type' => ((isset($listas[$key])) ? 'select' : $input[1]),
        'class' => $input[2],
        'tip' => (($input[4]) ? $input[4] : ''),
        'list' => ((isset($listas[$key])) ? $listas[$key] : ''),
        'label' => $input[0],
        'default' => ((isset($listas[$key])) ? -1 : ''),
    ];
}

//
$form = new Form();
$form->addElement('<config-json model="Form" grid="col-6"></config-json>', 'col-md-12');
$form->addElement('<div class="text-center"><button class="btn btn-primary pl-5 pr-5 m-5" '
        . 'style="color: #ffffff;background-color: #009dd8;border: solid #008abf;" '
        . 'ng-click="send()">Enviar inscrição</button></div>', 'col-12');


$html = '<div ng-controller="UnigraceController">'
        . '<div class="alert alert-dark text-center ns-content" style="background-color:#009dd8; color:white;">'
        . '<h3>Inscrição on-line UNIGRACE</h3>'
        . '<h6>Escolha o polo que você deseja estudar e faça sua inscrição hoje mesmo!</h6>'
        . '</div>'
        . '<div class="ns-content ns-form">'
        . $form->printForm()
        . '</div>'
        . '</div>';

$html .= '<style>'
        . 'body{background-color: #f1f1f1;}'
        . '.alert-dark {background-color:#000; color:#fff;}'
        . '.floating-label, .floating-label-select {font-weight:bolder;}'
        . '</style>';

// JS
$js = "var _data =" . json_encode(Helper::extrasJson($itens, [])) . " ;";

$controller = ''
        . NsUtil\Packer::jsPack($js)
        . NsUtil\Packer::jsPack(file_get_contents(__DIR__ . '/ng-app.js'))
        . NsUtil\Packer::jsPack(file_get_contents(Config::getData('path') . '/public/_x0023_html/components/configJson/configJson.js'))
//. '<script type="text/javascript" src="' . Config::getData('url') . '/public/_x0023_html/components/configJson/configJson.js"></script>'
;


$FormConfig = [
    'TITLE' => 'Inscricao UNIGRACE',
    'CONTENT' => $html,
    'URL' => Config::getData('url'),
    'URL_API' => Config::getData('url') . '/api',
    'JS' => $controller,
    'TEMA' => '',
    'FLUID' => '', //-FLUID'
        ]
;
