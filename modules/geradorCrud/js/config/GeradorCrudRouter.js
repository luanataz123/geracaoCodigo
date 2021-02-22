angular.module("GeracaoCodigo").config(function ($stateProvider, Constants, $urlRouterProvider) {

    $stateProvider
    	.state('interfaceAngularJS.geradorCrud', {
            url: '/geradorCrud',
            controller: 'GeradorCrudController',
            templateUrl: Constants.horus.dados.template.geradorCrud,
            resolve: {
                perfil: function () {
                    return {valor: 'interfaceAngularJS'};
                }
            },
            title: "Geração de CRUD"
        })

        ;
});