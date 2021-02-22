angular.module("GeracaoCodigo").config(function ($stateProvider, Constants, $urlRouterProvider) {

    $stateProvider
        .state('interfaceAngularJS', {
            url: '/interfaceAngularJS',
            controller: 'InterfaceAngularJSController',
            templateUrl: Constants.horus.dados.template.interfaceAngularJS,
            title: "Menu Interface em AngularJS"
        })
        ;
});