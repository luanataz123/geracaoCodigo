angular.module("GeracaoCodigo").controller("InterfaceAngularJSController", function ($scope, $http, $routeParams, $timeout, Constants) {

    $(".nav-tabs-main li").removeClass("active");
    $('#nav-tab-interface-angularjs').addClass("active");

    $(document).on('click', '.navbar-nav li', function() {
        $(".navbar-nav li").removeClass("active");
        $(this).addClass("active");
    });
});

