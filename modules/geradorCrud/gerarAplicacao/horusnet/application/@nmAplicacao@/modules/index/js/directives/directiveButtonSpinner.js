angular.module('@NmAplicacao@').directive('buttonSpinner', function ($compile) {
        "use strict";
        
        return {
            restrict: 'A',
            scope: {
                spinning: '=buttonSpinner',
                spinningIcon: '@?',
                buttonPrepend: '@?',
                buttonAppend: '@?',
                ariaLoading: '@',
                ariaNotLoading: '@'
            },
            transclude: true,
            template: 
            "<span ng-if=\"!!buttonPrepend\" ng-hide=\"spinning\"><i class=\"{{ buttonPrepend }}\"></i>&nbsp;</span>" +
            "<span ng-if=\"!!buttonPrepend\" ng-show=\"spinning\"><i class=\"{{ !!spinningIcon ? spinningIcon : 'fa fa-spinner fa-spin' }}\"></i>&nbsp;</span>" +
            "<ng-transclude></ng-transclude>" +
            "<span ng-if=\"!!buttonAppend\" ng-hide=\"spinning\">&nbsp;<i class=\"{{ buttonAppend }}\"></i></span>" +
            "<span class=\"d-inline\" aria-live=\"assertive\" aria-label=\"{{buttonSpinnerStatus}}\">" +
            "<span ng-if=\"!buttonPrepend\" ng-show=\"spinning\">&nbsp;<i class=\"{{ !!spinningIcon ? spinningIcon : 'fa fa-spinner fa-spin' }}\"></i></span>" +
            "</span>",
            link: function(scope){
            	if (angular.isUndefined(scope.ariaLoading)){
                    scope.ariaLoading = 'Carregando';
            	}

            	if (angular.isUndefined(scope.ariaNotLoading)){
                    scope.ariaNotLoading = 'Carregado';
            	}
            	
            	scope.$watch('spinning', function(value){
            		if (value){
            			scope.buttonSpinnerStatus = scope.ariaLoading;	
            		} else {
            			scope.buttonSpinnerStatus = scope.ariaNotLoading;	
            		}
            	});
            }
        }
    });