<?php /*defined('_IS_VALIDATION_') or die('Acesso não permitido.'); ?>

<script type="text/javascript">
    var horus = {
        dados: <?php print json_encode($this->dados); ?>,
        params: <?php print json_encode($this->params); ?>
    };

<?php
*/
/************************demais requires**********************************/

/*FRAGMENTO*//*BEGIN(indexPhpModulo)*/
//@nmTabela@
if(#inArrayPerfil#){
    require_once PATH_APPLICATION_MODULES . '/@nmTabela@/js/config/@NmTabela@Router.js';
    require_once PATH_APPLICATION_MODULES . '/@nmTabela@/js/controllers/@NmTabela@Controller.js';
    require_once PATH_APPLICATION_MODULES . '/@nmTabela@/js/controllers/Editar@NmTabela@Controller.js';
    require_once PATH_APPLICATION_MODULES . '/@nmTabela@/js/services/@nmTabela@Service.js';
}
/*FRAGMENTO*//*END(indexPhpModulo)*/
/*
?>
</script>*/