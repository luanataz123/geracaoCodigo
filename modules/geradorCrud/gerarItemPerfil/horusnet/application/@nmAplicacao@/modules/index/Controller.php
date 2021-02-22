<?php
/*
defined('_IS_VALIDATION_') or die('Acesso não permitido.');

final class Controller extends ControllerApplication
{
    /************************demais funcoes**********************************/
    /*
    protected function index() {*/

        /************************demais ifs**********************************/

/*FRAGMENTO*//*BEGIN(indexModulo)*/
        #templatePerfil#
        
        /*FRAGMENTO:=indexModulo*/
/*FRAGMENTO*//*END(indexModulo)*/
        /************************demais ifs**********************************/
        
 /*   }
    
    private function getViewsPermitidas() {
        
        $autorizaModulesOriginal = $this->dados['autoriza']['modules'];
        if (array_key_exists('VSU_EMAIL', $_SESSION) && !empty($_SESSION['VSU_EMAIL'])){
            $vsuAutoriza = new AutorizaApplication($this);
            $vsuAutoriza->setApplication('@NMAPLICACAO@');
            $vsuAutoriza->setEmail($_SESSION['VSU_EMAIL']);
            $vsuModules = $vsuAutoriza->getModules();
            
            $this->dados['autoriza']['modules'] = $vsuModules;
        }
        
        $this->dados['viewsPermitidas'] = array();
        $tipoTabela = (array_key_exists('VSU_TIPO_TABELA', $_SESSION) && !empty($_SESSION['VSU_TIPO_TABELA']) ? $_SESSION['VSU_TIPO_TABELA'] : $_SESSION['TIPO_TABELA']);
        */

        /************************demais perfis**********************************/
/*FRAGMENTO*//*BEGIN(indexItemPerfil)*/
        #ifPerfilPermitido#
/*FRAGMENTO*//*END(indexItemPerfil)*/
        /************************demais perfis**********************************/
        
/*    }
    
    
    /************************demais funcoes**********************************/
    /*
}*/
