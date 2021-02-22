angular.module("@NmAplicacao@").directive('modalConfirmClick', function ($uibModal, $parse) {
    return {
        restrict: 'EA',
        link: function (scope, element, attrs) {
            if (!attrs.do) {
                return;
            }

            var confirmButtonText = attrs.confirmButtonText ? attrs.confirmButtonText : 'Sim';
            var cancelButtonText = attrs.cancelButtonText ? attrs.cancelButtonText : 'N&atilde;o';
            element.click(function () {
                var doThis = $parse(attrs.do);
                
                if (attrs.confirmIf) {
                    var confirmationCondition = $parse(attrs.confirmIf);
                    if (!confirmationCondition(scope)) {
                        doThis(scope);
                        scope.$apply();
                        return;
                    }
                }
                $uibModal.open(
                    {
                        template: '<div class="modal-header" aria-modal="true">'
                                + '<h4 class="modal-title" id="dialog_label">' + attrs.title + '</h4>'
                                + '</div>'
                                + '<div class="modal-body" id="dialog_desc"><p>' + attrs.message + '</p></div>'
                                + '<div class="modal-footer">'
                                + '<button type="button" class="btn btn-primary" ng-click="$close(\'ok\')">' + confirmButtonText + '</button>'
                                + '<button type="button" class="btn btn-warning" ng-click="$dismiss(\'cancel\')">' + cancelButtonText + '</button>'
                                + '</div>',
                         ariaDescribedBy: 'dialog_desc',
                         ariaLabelledBy: 'dialog_label'
                    })
                    .result.then(function () {
                        doThis(scope);
                    }
                );
            });
        }
    };
});