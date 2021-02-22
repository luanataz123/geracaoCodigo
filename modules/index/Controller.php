<?php

defined('_IS_VALIDATION_') or die('Acesso não permitido.');

final class Controller extends ControllerApplication
{

    public function __construct($config) {
        parent::__construct($config);
        parent::verificarSessao();
        //echo '<pre>'; print_r($this->dados['autoriza']['modules']); echo '</pre>'; die;
        parent::init();
    }

    protected function index() {
        $matricula = (array_key_exists('VSU_MATRICULA', $_SESSION) && !empty($_SESSION['VSU_MATRICULA']) ? $_SESSION['VSU_MATRICULA'] : $_SESSION['MATRICULA']);
        
        $this->getViewsPermitidas();
        
        if (in_array('interfaceAngularJS', $this->dados['viewsPermitidas'])) {
            $this->dados['template']['interfaceAngularJS'] = '?p=' . $this->view->encodeUrl(PATH_URL_MODULE . 'module=interfaceAngularJS&task=init');
            
        }
        
        
        
        //geradorCrud
        if(in_array('interfaceAngularJS', $this->dados['viewsPermitidas']) ){
            
            $this->dados['template']['geradorCrud'] = '?p=' . $this->view->encodeUrl(PATH_URL_MODULE . 'module=geradorCrud&task=init');
            $this->dados['url']['carregarTabela'] = '?p=' . $this->view->encodeUrl(PATH_URL_APPLICATION . 'module=geradorCrud&action=carregarTabela&task=dados');
            $this->dados['url']['gerarCrud'] = '?p=' . $this->view->encodeUrl(PATH_URL_APPLICATION . 'module=geradorCrud&action=gerarCrud&task=dados');
            
        }
        
    }
    
    private function getViewsPermitidas() {

        if (array_key_exists('VSU_EMAIL', $_SESSION) && !empty($_SESSION['VSU_EMAIL'])){
            $vsuAutoriza = new AutorizaApplication($this);
            $vsuAutoriza->setApplication('GERACAOCODIGO');
            $vsuAutoriza->setEmail($_SESSION['VSU_EMAIL']);
            $vsuModules = $vsuAutoriza->getModules();
            
            $this->dados['autoriza']['modules'] = $vsuModules;
        }
        
        $this->dados['viewsPermitidas'] = array();
       /* $tipoTabela = (array_key_exists('VSU_TIPO_TABELA', $_SESSION) && !empty($_SESSION['VSU_TIPO_TABELA']) ? $_SESSION['VSU_TIPO_TABELA'] : $_SESSION['TIPO_TABELA']);*/
        
        if (true /*in_array('INTERFACEANGULARJS', $this->dados['autoriza']['modules'])*/) {
            $this->dados['viewsPermitidas'][] = 'interfaceAngularJS';
        }
        
        
    }
}
