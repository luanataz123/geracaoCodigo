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
        //@nmTabela@
        if(#inArrayPerfil#){
        
            $this->dados['template']['@nmTabela@'] = '?p=' . $this->view->encodeUrl(PATH_URL_MODULE . 'module=@nmTabela@&task=init');
            $this->dados['template']['editar@NmTabela@'] = '?p=' . $this->view->encodeUrl(PATH_URL_MODULE . 'module=@nmTabela@&action=abrirEditar@NmTabela@&task=init');
            
            $this->dados['url']['consultar@NmTabela@'] = '?p=' . $this->view->encodeUrl(PATH_URL_APPLICATION . 'module=@nmTabela@&action=consultar@NmTabela@&task=dados');
            $this->dados['url']['salvar@NmTabela@'] = '?p=' . $this->view->encodeUrl(PATH_URL_APPLICATION . 'module=@nmTabela@&action=salvar@NmTabela@&task=dados');
            $this->dados['url']['excluir@NmTabela@'] = '?p=' . $this->view->encodeUrl(PATH_URL_APPLICATION . 'module=@nmTabela@&action=excluir@NmTabela@&task=dados');
            
            #dadosSearchLocal#
            #dadosSearchRemoto#
        }
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

/*    }
    
    
    /************************demais funcoes**********************************/
    /*
}*/
