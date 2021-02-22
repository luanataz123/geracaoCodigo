angular.module("@NmAplicacao@").service("modalService", ["$uibModal",
    function ($uibModal) {

        var modalDefaults = {
            backdrop: true,
            keyboard: true,
            modalFade: true,
            template: '<div class="modal-header">'
                      + '<h4 class="modal-title" id="dialog_label"><span ng-bind-html="modalOptions.headerText"></span></h4>'
                      + '</div>'
                      + '<div class="modal-body" id="dialog_desc">'
                      + '<p><span ng-bind-html="modalOptions.bodyText"></span></p>'
                      + '</div>'
                      + '<div class="modal-footer">'
                      + '<button type="button" class="btn btn-primary" '
                        + 'ng-click="close()">Fechar</button>'
                      + '</div>',
          ariaDescribedBy: 'dialog_desc',
          ariaLabelledBy: 'dialog_label'
        };

        var modalOptions = {
            headerText: 'Confirma?',
            bodyText: 'Confirma esta a&ccedil;&atilde;o?'
        };

        this.showModal = function (customModalDefaults, customModalOptions) {
            if (!customModalDefaults)
                customModalDefaults = {};
            customModalDefaults.backdrop = 'static';
            return this.show(customModalDefaults, customModalOptions);
        };

        this.show = function (customModalDefaults, customModalOptions) {
            /*Create temp objects to work with since we're in a singleton service*/
            var tempModalDefaults = {};
            var tempModalOptions = {};

            /*Map angular-ui modal custom defaults to modal defaults defined in service*/
            angular.extend(tempModalDefaults, modalDefaults, customModalDefaults);

            /*Map modal.html $scope custom properties to defaults defined in service*/
            angular.extend(tempModalOptions, modalOptions, customModalOptions);

            if (!tempModalDefaults.controller) {
                tempModalDefaults.controller = function ($scope, $uibModalInstance) {
                    $scope.modalOptions = tempModalOptions;
                    $scope.close = function () {
                        $uibModalInstance.close();
                    };
                };
            }

            return $uibModal.open(tempModalDefaults).result;
        };

    }]);