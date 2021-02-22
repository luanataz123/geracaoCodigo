<?php

defined('_IS_VALIDATION_') or die('Acesso não permitido.');

final class Controller extends ControllerApplication
{

    public function __construct($config) {
        parent::__construct($config);
        parent::verificarSessao();
/*
        $this->autoriza = new AutorizaApplication($this);
        $this->autoriza->setApplication('@NMAPLICACAO@');
        $this->dados['autoriza']['modules'] = $this->autoriza->getModules();
        foreach ($this->dados['autoriza']['modules'] as $module) {
            $this->autoriza->setModule($module);
            $this->dados['autoriza']['papeis'][$module] = $this->autoriza->getPapeis();
        }
        */
        //echo '<pre>'; print_r($this->dados['autoriza']['modules']); echo '</pre>'; die;
        parent::init();
    }

    protected function index() {
        $matricula = (array_key_exists('VSU_MATRICULA', $_SESSION) && !empty($_SESSION['VSU_MATRICULA']) ? $_SESSION['VSU_MATRICULA'] : $_SESSION['MATRICULA']);
        
        $this->getViewsPermitidas();
        
        /*FRAGMENTO:=indexModulo*/
    }
    
    private function getViewsPermitidas() {
/*
        $autorizaModulesOriginal = $this->dados['autoriza']['modules'];
        if (array_key_exists('VSU_EMAIL', $_SESSION) && !empty($_SESSION['VSU_EMAIL'])){
            $vsuAutoriza = new AutorizaApplication($this);
            $vsuAutoriza->setApplication('@NMAPLICACAO@');
            $vsuAutoriza->setEmail($_SESSION['VSU_EMAIL']);
            $vsuModules = $vsuAutoriza->getModules();
            
            $this->dados['autoriza']['modules'] = $vsuModules;
        }
        */  
        $this->dados['viewsPermitidas'] = array();
       /* $tipoTabela = (array_key_exists('VSU_TIPO_TABELA', $_SESSION) && !empty($_SESSION['VSU_TIPO_TABELA']) ? $_SESSION['VSU_TIPO_TABELA'] : $_SESSION['TIPO_TABELA']);*/
        
        /*FRAGMENTO:=indexItemPerfil*/
    }
}
