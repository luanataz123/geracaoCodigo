angular.module("@NmAplicacao@").controller("Editar@NmTabela@Controller", function ($scope, $http, $routeParams, $timeout, Constants, @nmTabela@Service, $location, $window, modalService, perfil) {
	if (Object.keys(@nmTabela@Service.dados).length > 0){
		$scope.@nmTabela@ = @nmTabela@Service.dados;	
	} else {
	    $scope.@nmTabela@ = {
	        	#colunasNull#	};
	}
    

    $(".nav-tabs-main li").removeClass("active");
    
    #ifTabAtiva#
    
    $scope.perfil = perfil.valor;
    
    if ($scope.@nmTabela@.@COLUNA_ID@ != null) {
        $scope.titleForm = "Edi&ccedil;&atilde;o de";
    } else {
        $scope.titleForm = "Inclus&atilde;o de";
    }

    #funcoesSearchArray#
    
    #funcoesSearchLocal#
    
    #funcoesSearchRemoto#
    
    $scope.cancelarEdicao@NmTabela@ = function () {
    	@nmTabela@Service.dados = [];
        $location.path("/"+perfil.valor+"/@nmTabela@");
    };
    
    $scope.validar@NmTabela@ = function(form){
    	var success = true;
    	var msg = '';
    	if (!success){
    		msg = 'Mensagem de erro!';
    	}
    	return {'success': success, 'msg': msg};
    };

});