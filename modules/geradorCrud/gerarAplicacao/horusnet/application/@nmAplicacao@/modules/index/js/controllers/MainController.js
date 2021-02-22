angular.module("@NmAplicacao@").controller("MainController", function ($scope, $http, $routeParams, $timeout, Constants) {
    /*FRAGMENTO:=MainControllerItemPerfil*/
    $scope.mostrarTab = function (tab) {
        if (Constants.horus.dados.viewsPermitidas && Constants.horus.dados.viewsPermitidas.indexOf(tab) >= 0) {
            return true;
        }
        return false;
    };
    
    $(document).on('click', '.nav-tabs li', function() {
        $(".nav-tabs-main li").removeClass("active");
        $(this).addClass("active");
    });

});