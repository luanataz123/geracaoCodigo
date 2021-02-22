angular.module("GeracaoCodigo").directive('saveButton', function ($uibModal, $http, Constants, $timeout, $location, modalService) {
    var templateString =
            '<button type="submit" class="btn btn-primary" title="Salvar">' +
                '<span>{{buttonText}}</span>' +
                "<span class=\"d-inline\" aria-live=\"polite\" aria-label=\"{{buttonSpinnerStatus}}\">" +
                '<span class="sr-only">Salvar</span>' +
            '</button>';
    
    return {
        restrict: 'EA',
        scope: {
            salvar: '=',
            form: '=',
            destino: '@?',
            acao: '@',
            atualizar: '&?',
            validar: '&?'
        },
        template: templateString,
        link: function (scope, element) {
        	var ariaLoading = "Salvando, aguarde";
        	var ariaNotLoading = "Salvo";
            var buttonEl = element.find('button');
            scope.buttonText = 'Salvar';
			scope.buttonSpinnerStatus = ariaNotLoading;	
            
            element.click(function () {
                scope.buttonText = 'Salvando... Por favor, aguarde';
    			scope.buttonSpinnerStatus = ariaLoading;	
                buttonEl.addClass('disabled');
                var link = Constants.horus.dados.url[scope.acao];
                var objetoSalvo = scope.salvar;
                scope.$parent.$broadcast('show-errors-check-validity');
                scope.form.$setSubmitted();
                if (scope.form.$invalid) {
                    var modalOptions = {
                        headerText: 'Erros no formul&aacute;rio',
                        bodyText: 'Existem campos preenchidos incorretamente.'
                    };
                    modalService.showModal({}, modalOptions).then(function () {
                        scope.buttonText = 'Salvar';
            			scope.buttonSpinnerStatus = ariaNotLoading;	
                        buttonEl.removeClass('disabled');
                    });
                } else {
                	var valido = true;
                	if (scope.validar){
                		var retornoValidacao = scope.validar(scope.form);
                		valido = retornoValidacao.sucess;
                		if (!valido){
                			 var modalOptions = {
                                     headerText: 'Erros no formul&aacute;rio',
                                     bodyText: retornoValidacao.msg
                                 };
                                 modalService.showModal({}, modalOptions).then(function () {
                                     scope.buttonText = 'Salvar';
                                     scope.buttonSpinnerStatus = ariaNotLoading;	
                                     buttonEl.removeClass('disabled');
                                 });
                		}
                		
                	}
                	
                	if (valido){
	                    $timeout(function () {
	                        $http.post(link, objetoSalvo).then(function (response) {
	                            var modalOptions = {
	                                headerText: response.data.success ? 'Registro salvo com sucesso.' : 'Falha ao salvar registro.',
	                                bodyText: response.data.msg
	                            };
	                            modalService.showModal({}, modalOptions).then(function () {
	                                scope.form.$setPristine();
	                                if (scope.atualizar) {
	                                    scope.atualizar(response);
	                                } else {
	                                    if (response.data.success && scope.destino) {
	                                        $location.path("/" + scope.destino);
	                                    }
	                                }
	                                scope.buttonText = 'Salvar';
	                    			scope.buttonSpinnerStatus = ariaNotLoading;	
	                                buttonEl.removeClass('disabled');
	                            });
	                        });
	                    }, 600);
                	}
                }
            });
        }
    };
});