<?php defined('_IS_VALIDATION_') or die('Acesso não permitido.'); ?>

<script type="text/javascript">
    var horus = {
        dados: <?php print json_encode($this->dados); ?>,
        params: <?php print json_encode($this->params); ?>
    };

<?php

/************************demais requires**********************************/
/*FRAGMENTO*//*BEGIN(indexPhpModulo)*/
    #requirePerfil#

    /*FRAGMENTO:=indexPhpModulo*/
/*FRAGMENTO*//*END(indexPhpModulo)*/
    
?>
</script>