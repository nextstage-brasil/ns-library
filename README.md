**Cloudeduc - sistema de Gestão de Ensino**

---

## Configurações

Os arquivos de configuração estão contidos na pasta /src/config sendo:

1. cfg.php: Arquivo básico de configuração de acessos
2. library_entities.php: Apelidos para as entidades do sistema. Por padrão, no diretório /auto são gerados os aliases conforme a configuração padrão. Editar apenas o necessário.
3. model_json.php: Modelos de entidades para os campos "json" nas tabelas. 
4. permissao_grupos.php: Array contendo o nome da entidade e a qual grupo de permissão de usuario ela pertence
5. router.php: Além das rotas padrão de sistema geradas automaticamente pelo /_build/@GeraBuild.bat, estas são rotas adicionais e manuais
6. aliases_tables e aliases_fields: Apelidos para as tabelas e os campos contidos nelas. Os campos possuem este apelido gerado autmaticamente pelo comentário na modelagem com o padrão: Apelido|Texto para hint

---

## Build e versionamento

O sistema irá ser construído com base no banco de dados acoplado a ele. Para isso, em /_build/@GeraBuild.bat existe uma rotina que gera as entidades, cria os controllers inexistentes e cria os arquivos de configuração com base no sistema.
Algumas considerações sobre builds:

1. /_build/compile.php ira "compilar" os arquivos JS contido nas pastas em /build/js[app e framework], /build/components e ira gerar o arquivo minificado em htmle js na pasta /auto/components
2. O consumo destes arquivos deve ser feito pela classe Component::init('nomedoarquivo.js')
3. /_build/builder.php ira construir os controllers e views ainda não existentes. Os controllers irao para /src/controller e as views para "/view/fonte/Entidade.php" e o js "/_build/js_app/$entidade-script.js"
4. em /build/install tem o arquivo builder que orquestra a construção do builder. Também tem o diretorio /sql que contem as modelagens aplicadas
5. /build/site_gera_entidades.php Sera chamado pelo builder para criação de entidades não persistidas, apenas utilizadas na aplicação para padronização de modelos de retorno (view/site)

Não há necessidade evidente de edição dos demais arquivos. Se preciso, faça com cautela pois são utilizados em varios momentos.

---

## Backend

As chamadas do frontend para backend se darão exclusivamente pela rota /api/RECURSO/ACAO. 
A aplicação esta escrita para php7.2 ou maior, utilizando algumas bibliotecas de terceiro em /vendor. 
Alguns detalhes importantes:

1. /api/index.php Ira receber todas as requisições para tratamento via API. Espera que exista a rota acima, podendo ter laém de /RECURSO/ACAO/{idEditavel}. 
    Ex.: {url}/api/curso/read/12. Isto irá retorna um objeto do tipo Curso, com os dados da tupla com id 12.
2. As rotas em /api/index.php são por padrão obtidas dos controller. curso seria um controller chamado CursoController.class.php e neste deve haver um metodo chamado
    ws_read ou criar um aliases para ele na /api. Ex.: para /read temos o metodo padrão ws_getById em todos os controllers para atender a requsição. Métodos sem o prefixo ws_ não estarão disponíveis para API de forma automatizada.
3. O diretorio /ns-app deve ter permissão de escrita no servidor com acesso 0777 pois será utilizado para descarga de arquivos temporarios e outros

## Frontend

O sistema esta utilizando arquivos PHP para gerar os HTMLs e como controller JS o framework AngularJS.
Na estrutura de arquivos JS, existe um Controller chamado AppController.js que esta em build/js_framework.
Este arquivo é carregado em todas as navegações, e contem varias unificações de chamadas. Dentre todos os métodos, o $rootScope.trataEditOnLoad é chamado em todos os controllers de entidads
 e possuem uma generalização de funções de CRUD para todos.

O controller basico, sem nenhum edição ficara assim:

app.controller("EmpresaController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
        $scope.entidadeName = 'Empresa'; // nome da entidade deste controller
        $scope.listGetAux = []; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado
        
        /** Menu de contexto **/
        $scope.EmpresaContextItens = [
            {'link':'EmpresaOnEdit(Empresa)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
            {'link':'EmpresaRemove(Empresa)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
        ];
        
        /** Para tratar os linktables, caso exista **/
        $scope.setVinculosOnEdit = function (id) {};

        // vai injetar as funções padrão
        $rootScope.trataEditOnLoad($scope);

        // Demais funções exclusivas deste componente
});

Quase tudo que se precisará de front estará construido no appcontroller. As particularidades de cada pagina devem ser tratadas individualmente.

Particularidades do frontend:
1. /view/fonte: contem os arquivos .php que o router ira procurar para entregar ao browser. Nenhum deles deve ter acesso direto permitido. Somente pelo router.

---