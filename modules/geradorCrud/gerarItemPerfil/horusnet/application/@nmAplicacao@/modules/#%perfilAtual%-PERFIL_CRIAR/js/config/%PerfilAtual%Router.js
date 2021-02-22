angular.module("@NmAplicacao@").config(function ($stateProvider, Constants, $urlRouterProvider) {

    $stateProvider
        .state('%perfilAtual%', {
            url: '/%perfilAtual%',
            controller: '%PerfilAtual%Controller',
            templateUrl: Constants.horus.dados.template.%perfilAtual%,
            title: "Menu %ItemMenuAcentuado%"
        })
        ;
});