angular.module("@NmAplicacao@").controller("%PerfilAtual%Controller", function ($scope, $http, $routeParams, $timeout, Constants) {

    $(".nav-tabs-main li").removeClass("active");
    $('#nav-tab-%tab-primaria%').addClass("active");

    $(document).on('click', '.navbar-nav li', function() {
        $(".navbar-nav li").removeClass("active");
        $(this).addClass("active");
    });
});

