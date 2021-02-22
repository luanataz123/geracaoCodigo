angular.module("@NmAplicacao@").directive('modalDeleteConfirmClick', function ($uibModal, $http, Constants, $timeout, modalService) {
    var templateString =
            '<button type="button" class="btn btn-danger btn-xs" title="Excluir">' +
                '<span class="glyphicon glyphicon-remove-sign"></span>' +
                '<span class="sr-only">Excluir</span>' +
            '</button>';
    
    return {
        restrict: 'EA',
        scope: {
            deletar: '=',
            acao: '@',
            atualizar: '&'
        },
        template: templateString,
        link: function (scope, element) {
            element.click(function () {

                var link = Constants.horus.dados.url[scope.acao];
                var objetoDeletado = scope.deletar;
                $uibModal.open(
                    {
                        template: '<div class="modal-header">'
                                + '<h4 class="modal-title" id="dialog_label">Confirma&ccedil;&atilde;o de exclus&atilde;o de registro</h4>'
                                + '</div>'
                                + '<div class="modal-body" id="dialog_desc">O registro ser&aacute; apagado. Confirma exclus&atilde;o de registro?</div>'
                                + '<div class="modal-footer">'
                                + '<button type="button" class="btn btn-primary" ng-click="$close(\'ok\')">Sim</button>'
                                + '<button type="button" class="btn btn-warning" ng-click="$dismiss(\'cancel\')">N&atilde;o</button>'
                                + '</div>',
                        ariaDescribedBy: 'dialog_desc',
                        ariaLabelledBy: 'dialog_label'
                                
                    })
                    .result.then(function () {
                        $timeout(function () {
                            $http.post(link, objetoDeletado).then(function (response) {
                                var modalOptions = {
                                    headerText: response.data.success ? 'Registro exclu&iacute;do com sucesso.' : 'Falha ao excluir registro.',
                                    bodyText: response.data.msg
                                };
                                modalService.showModal({}, modalOptions).then(function () {
                                    if (response.data.success & scope.atualizar) {
                                        scope.atualizar();
                                    }
                                });
                            });
                        }, 600);
                    }
                );
            });
        }
    };
});