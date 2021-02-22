angular.module("GeracaoCodigo").config(function ($stateProvider, Constants, $urlRouterProvider) {
    $urlRouterProvider.when("", Constants.horus.dados.viewsPermitidas[0]);
});

angular.module("GeracaoCodigo").run(['$location', '$rootScope', '$trace', '$transitions', '$state', function($location, $rootScope, $trace, $transitions, $state) {
	$trace.enable('TRANSITION');
	
	$transitions.onSuccess({}, function(transition){
		if (angular.isDefined(transition.to().title)){
			$('#announcer').text(transition.to().title);
		}
		
	});
}]);
