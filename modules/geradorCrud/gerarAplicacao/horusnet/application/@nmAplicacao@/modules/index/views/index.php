<?php defined('_IS_VALIDATION_') or die('Acesso n�o permitido.'); ?>

<script type="text/javascript">
    var horus = {
        dados: <?php print json_encode($this->dados); ?>,
        params: <?php print json_encode($this->params); ?>
    };

<?php
require_once PATH_MODULE . '/js/app.js';
require_once PATH_MODULE . '/js/config/MainRouter.js';
require_once PATH_MODULE . '/js/value/Constants.js';
require_once PATH_MODULE . '/js/controllers/MainController.js';
require_once(PATH_MODULE . '/js/directives/directiveButtonSpinner.js');
require_once(PATH_MODULE . '/js/directives/directiveModalConfirmation.js');
require_once(PATH_MODULE . '/js/directives/directiveModalDeleteConfirmation.js');
require_once(PATH_MODULE . '/js/directives/directiveNgConfirmClick.js');
require_once(PATH_MODULE . '/js/directives/dirPagination.js');
require_once(PATH_MODULE . '/js/directives/saveButtonDirective.js');
require_once PATH_MODULE . '/js/directives/showErrorsDirective.js';
require_once(PATH_MODULE . '/js/services/modalService.js');

/*FRAGMENTO:=indexPhpModulo*/

?>
</script>