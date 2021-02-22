angular.module("GeracaoCodigo").controller("GeradorCrudController", function ($scope, $http, $routeParams, $timeout, $location, Constants, $window, geradorCrudService, perfil, modalService, $filter) {
    
    $(".nav-tabs-main li").removeClass("active");
    
    if (perfil.valor =="interfaceAngularJS"){
        $('#nav-tab-interface-angularjs').addClass("active");
        $(".navbar-interface-angularjs li").removeClass("active");
        $('#navbar-interface-angularjs-gerador-de-crud').addClass("active");
	}  
    
    var arrNaoExibir = {'KEY':'nenhum', 'VALUE': 'Não incluir no form'};
    $scope.opcoesInput = {'VARCHAR2' : [{'KEY':'text', 'VALUE': 'text'}, {'KEY':'textarea', 'VALUE': 'textarea'}, {'KEY':'checkbox', 'VALUE': 'checkbox'}, {'KEY':'radio', 'VALUE': 'radio'}, {'KEY':'searchArray', 'VALUE': 'search (Array)'}, {'KEY':'hidden', 'VALUE': 'hidden'}, arrNaoExibir], 
    						'CHAR' : [{'KEY':'text', 'VALUE': 'text'}, {'KEY':'textarea', 'VALUE': 'textarea'}, {'KEY':'checkbox', 'VALUE': 'checkbox'}, {'KEY':'radio', 'VALUE': 'radio'}, {'KEY':'searchArray', 'VALUE': 'search (Array)'}, {'KEY':'hidden', 'VALUE': 'hidden'}, arrNaoExibir],
    						'DATE' : [{'KEY':'date', 'VALUE': 'date'}, {'KEY':'hidden', 'VALUE': 'hidden'}, arrNaoExibir],
    						'NUMBER': [{'KEY':'number', 'VALUE': 'number'}, {'KEY':'hidden', 'VALUE': 'hidden'}, {'KEY':'searchArray', 'VALUE': 'search (Array)'}, {'KEY':'searchTabelaLocal', 'VALUE': 'search (Tabela busca local)'}, {'KEY':'searchTabelaRemoto', 'VALUE': 'search (Tabela busca remota)'}, {'KEY':'checkbox', 'VALUE': 'checkbox'}, {'KEY':'radio', 'VALUE': 'radio'}, arrNaoExibir]};
    
    $scope.arrInputTabelaFK = ['searchTabelaLocal', 'searchTabelaRemoto'];
    
    $scope.arrInputArrayOpcoes = ['checkbox', 'searchArray', 'radio'];
    
    $scope.geradorCrud = {
    		ST_GERAR_ITEM_PERFIL: "0",
    		ST_GERAR_APLICACAO: "0"
    };
    $scope.carregarTabela = function (form) {
    	$scope.carregarTabelaLoading = true;
        return $http.post(Constants.horus.dados.url.carregarTabela, {'DS_TABELA': $scope.geradorCrud.DS_TABELA}).then(function (response) {
            $scope.geradorCrud.colunas = response.data.dados;
            $scope.carregarTabelaLoading = false;
            return response.data.dados;
        });
    };
    

    $scope.gerarCrud = function(form){
    	var msg = '';
    	var success = true;
        $scope.$broadcast('show-errors-check-validity');
        form.$setSubmitted();
		var regexTabelaFK = new RegExp("..*[.]..*;..*;..*");
		var regexArrayOpcoes = new RegExp("{\"..*\":\"..*\"(,\"..*\":\"..*\")*}");
		var regexArrayOpcoesCheckbox = new RegExp("{\"..*\":\"CHECKED\",\"..*\":\"UNCHECKED\"}");
        
        if (!$scope.geradorCrud.colunas){
        	success = false;
        	msg += 'Preencha a descrição da tabela, clique em "Carregar Tabela" e então preencha o detalhamento das colunas.<br>';
        } else {
        	var colunasSort = $scope.geradorCrud.colunas.filter(function (coluna) {
                return coluna.selected === true;
            });
        	
        	if(colunasSort.length != 1){
        		success = false;
        		msg += 'Selecione uma e apenas uma coluna para Ordenação inicial.<br>';
        	}
        	
        	var colunasPK = $scope.geradorCrud.colunas.filter(function (coluna) {
                return coluna.PK === true;
            });
        	
        	if(colunasPK.length != 1){
        		success = false;
        		msg += 'Selecione uma e apenas uma coluna PK (chave primária).<br>';
        	}
        	
        	var colunasPesquisa = $scope.geradorCrud.colunas.filter(function (coluna) {
                return coluna.PESQUISA === true;
            });
        	
        	if(colunasPesquisa.length != 1){
        		success = false;
        		msg += 'Selecione uma e apenas uma coluna para Pesquisa.<br>';
        	}
        	
        	
        	for(var i=0;i<$scope.geradorCrud.colunas.length;i++){
        		var c = $scope.geradorCrud.colunas[i];
        		
        		if(!c.DS_LABEL){
        			sucess = false;
        			msg += 'Preencha o Label para a coluna ' + c.NM_COLUNA + '.<br>';
        		}
        		
        		if(!c.DS_TIPO_INPUT){
        			sucess = false;
        			msg += 'Preencha o Input para a coluna ' + c.NM_COLUNA + '.<br>';
        		} else {
            		if($scope.arrInputArrayOpcoes.includes(c.DS_TIPO_INPUT)){
            			if(!c.ARRAY_OPCOES){
                			sucess = false;
                			msg += 'Preencha o Array de Opções para a coluna ' + c.NM_COLUNA + '.<br>';
            			} else {
            		         if (!regexArrayOpcoes.test(c.ARRAY_OPCOES)){
            		        	 sucess = false;
            		        	 msg += 'O Array de Opções da coluna ' + c.NM_COLUNA + ' deve estar no formato {"key1":"value1","key2":"value2"} , podendo ter a quantidade de opções necessárias .<br>';
            		         } else {
                		         if(c.DS_TIPO_INPUT == 'checkbox' && !regexArrayOpcoesCheckbox.test(c.ARRAY_OPCOES)){
                		        	 sucess = false;
                		        	 msg += 'A coluna ' + c.NM_COLUNA + ' possui Input do tipo "checkbox", portanto seu Array de Opções deve estar no formato {"key1":"CHECKED","key2":"UNCHECKED"} , não podendo alterar as palavras-chave "CHECKED" e "UNCHECKED".<br>';
                		         }
            		         }
            			}
            		}
        			
            		if($scope.arrInputTabelaFK.includes(c.DS_TIPO_INPUT)){
            			if(!c.TABELA_FK){
                			sucess = false;
                			msg += 'Preencha a Tabela FK para a coluna ' + c.NM_COLUNA + '.<br>';
            			} else {
            		         if (!regexTabelaFK.test(c.TABELA_FK)){
            		        	 sucess = false;
            		        	 msg += 'A tabela FK da coluna ' + c.NM_COLUNA + ' deve estar no formato MEU_SCHEMA.MINHA_TABELA;ID_TABELA;DS_TABELA  .<br>';
            		         }
            			}
            		}
        		}
        		
        	}
        }


    	if(success && form.$valid){
    		$scope.geradorCrud.colunaSort = colunasSort[0].NM_COLUNA;
            $scope.geradorCrud.colunaPK = colunasPK[0].NM_COLUNA;
            $scope.geradorCrud.colunaPesquisa = colunasPesquisa[0].NM_COLUNA;
            
            $http.post(Constants.horus.dados.url.gerarCrud, $scope.geradorCrud).then(function (response) {
            	if (response.data.success) {
                    $window.open(response.data.url, "_blank");
                } else {
                    var modalOptions = {
                        headerText: 'Falha ao gerar CRUD',
                        bodyText: response.data.msg
                    };
                    modalService.showModal({}, modalOptions).then(function () {});
                }
            });
    		
    	} else {
    		if (!msg){
    			msg = 'Existem campos preenchidos incorretamente.';
    		}
            var modalOptions = {
                    headerText: 'Erros no formul&aacute;rio',
                    bodyText: msg
                };
                modalService.showModal({}, modalOptions).then(function () {});

    	}

    }
    
    $scope.onSelectInput = function(c){
    	if(c.DS_TIPO_INPUT == 'checkbox'){
    		if (c.DS_TIPO == 'NUMBER'){
    			c.ARRAY_OPCOES = '{"1":"CHECKED","0":"UNCHECKED"}';
            } else {
    			c.ARRAY_OPCOES = '{"S":"CHECKED","N":"UNCHECKED"}';
            } 
    	}
    	
    	if (!$scope.arrInputTabelaFK.includes(c.DS_TIPO_INPUT)){
    		c.TABELA_FK = '';
    	}
    	
    	if (!$scope.arrInputArrayOpcoes.includes(c.DS_TIPO_INPUT)){
    		c.ARRAY_OPCOES = '';
    	}
    	
    };        
});