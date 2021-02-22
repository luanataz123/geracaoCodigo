angular.module("@NmAplicacao@").controller("@NmTabela@Controller", function ($scope, $http, $routeParams, $timeout, $location, Constants, $window, @nmTabela@Service, perfil, modalService, $filter) {
    
    $(".nav-tabs-main li").removeClass("active");
    
    #ifTabAtiva# 
    
    $scope.arr@NmTabela@ = [];
    $scope.itemsPerPage = 20;
    $scope.totalItems = 0;
    
    var iniPage = 1;
    $scope.pagination = {
    		current: iniPage
    };
    
    var iniSort = {'column': '@DS_GRID_COLUNA_SORT@', 'descending' : false, 'columnBanco':'@AliasNmColunaSort@'};
    $scope.sort = iniSort;
    
    $scope.ds_pesquisa = {
    		@DS_GRID_COLUNA_PESQUISA@: null,
            perfil: perfil.valor,
            pagina_atual: null,
            itens_por_pagina: $scope.itemsPerPage,
            order_by: [iniSort]

        };    
    
	$scope.changeSorting = function(column, columnBanco) {
    var sort = $scope.sort;
	    if (sort.column == column) {
	        sort.descending = !sort.descending;
	    } else {
	        sort.column = column;
	        sort.descending = false;
	    }
	    sort.columnBanco = columnBanco;
	    
	    /*se tiver mais de uma pagina, faz o sorting no servidor, senao faz no cliente*/
	    if ($scope.totalItems > $scope.itemsPerPage){
	        $scope.ds_pesquisa.order_by[0] = sort;
	        $scope.consultarArr@NmTabela@();
	    } else {
	    	$scope.arr@NmTabela@ = $filter('orderBy')($scope.arr@NmTabela@, 
	    			function(value){
	    				if (false #clausulaColunaData#){
	    					var arrDataHora = value[sort.column].split(' '); 
	    					var arrData = arrDataHora[0].split('/');
	    					var hora = '';
	    					if (arrDataHora.length == 2){
	    						hora = arrDataHora[1];
	    					}
	    					return arrData[2] + arrData[1] + arrData[0] + hora;
	    				}
	    				
	    				if(false #clausulaColunaInt#){
	    					return parseInt(value[sort.column]);
	    				}
	    				
	    				return value[sort.column];
	    			}, 
	    			sort.descending);
	    }
	    
	};    
    
    
	$scope.isPerfil = function (pPerfil) {
        return (perfil.valor === pPerfil);
    };
    
    $scope.@nmTabela@ = {
    	#colunasNull#	};
    
    var formCadastro@NmTabela@ = function () {
    	$location.path("/"+perfil.valor+"/editar@NmTabela@");
    };
    
    $scope.limparPesquisa@NmTabela@ = function () {
        $scope.ds_pesquisa = {
        		@DS_GRID_COLUNA_PESQUISA@: null,
                perfil: perfil.valor,
                pagina_atual: iniPage,
                itens_por_pagina: $scope.itemsPerPage,
                order_by: [iniSort]	
        };
        
        if($scope.pagination.current == iniPage){
        	$scope.consultarArr@NmTabela@();
        } else {
            $scope.pagination = {
            		current: iniPage
            };
        }
    };    
    /***
     * faz chamada ao servidor para preencher os dados do grid
     * @returns 
     */
    $scope.consultarArr@NmTabela@ = function() {;
    	$scope.@nmTabela@Loading = true;
            $http.post(Constants.horus.dados.url.consultar@NmTabela@, $scope.ds_pesquisa).then(function (response) {
            	$scope.totalItems = response.data.total;
            	
            	if(response.data.total > 0) {
                    $scope.arr@NmTabela@ = response.data.dados;
                } else {
                    $scope.arr@NmTabela@ = [];
                    var msg = '';
                    if (response.data.total === 0){
                    	msg = 'N&atilde;o foram encontrados registros para a pesquisa realizada.';
                    } else {
                    	msg = response.data.msg;
                    }
                    
                    var modalOptions = {
                            headerText: 'AVISO',
                            bodyText: msg
                        };
                        modalService.showModal({}, modalOptions).then(function (result) {
                    });
                }
            	$scope.@nmTabela@Loading = false;
            });
    };
    
    $scope.cadastrar@NmTabela@ = function () {
        @nmTabela@Service.dados = $scope.@nmTabela@;
        formCadastro@NmTabela@();
    };
    
    $scope.editar@NmTabela@ = function (@nmTabela@) {
        /* ajuste dos dados para edicao 
         * para campo de data � preciso converter para um objeto de data do javascript
         */
    	@nmTabela@Service.dados = null;
#edicaoCamposData#
#edicaoCamposNumber#
        @nmTabela@Service.dados = @nmTabela@;
        formCadastro@NmTabela@();
    };

    
    $scope.$watch('pagination.current', function(newValue, oldValue){
    	$scope.ds_pesquisa.pagina_atual = newValue;
    	$scope.consultarArr@NmTabela@();
    });    
});