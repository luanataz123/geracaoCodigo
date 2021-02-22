<?php /*
defined('_IS_VALIDATION_') or die('Acesso não permitido.');

abstract class ControllerApplication extends ControllerGenerics
{*/
    /************************demais funcoes**********************************/
    
/*FRAGMENTO*//*BEGIN(ControllerApplicationModulo)*/
    /**
     * Retorna uma string no formato JSON
     *
     * @access protected
     * @return string/JSON
     */
    protected function consultar@NmTabela@() {
        $json = $this->model->consultar@NmTabela@();
        
        print json_encode($json);
        die;
    }
    
    #consultaSearchRemoto#
/*FRAGMENTO*//*END(ControllerApplicationModulo)*/
    /************************demais funcoes**********************************/
 /*   
}*/