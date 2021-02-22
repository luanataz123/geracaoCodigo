<?php
defined('_IS_VALIDATION_') or die('Acesso não permitido.');

final class AutorizaApplication extends Autoriza
{
    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    public function validarAcesso($action = '', $debug = false)
    {
        if(!parent::validarAcesso($action, $debug)){
            switch($this->task){
                case 'autoload':
                    print "<script>alert('Acesso não permitido a: {$this->module}_{$action}')</script>"; die;
                    break;
                case 'init':
                    $this->view->openHTML('');
                    print "<script>alert('Acesso não permitido a: {$this->module}_{$action}')</script>"; die;
                    $this->view->closeHTML();
                    break;
                case 'dados':
                    $json['total'] = 1;
                    $json['dados'] = array();
                    $json['autoriza'] = false;
                    $json['msg'] = "Acesso não permitido a: {$this->module}_{$action}";
                    print json_encode($json); die;
                    break;
                default:
                    print "{success: false, msg: 'Acesso não permitido a: {$this->module}_{$action}'}"; die;
            }
        }
    }
}