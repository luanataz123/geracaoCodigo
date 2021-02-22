<?php

defined('_IS_VALIDATION_') or die('Acesso não permitido.');

final class Controller extends ControllerApplication
{

    public function __construct($config) {
        parent::__construct($config);
        parent::verificarSessao();
        parent::init();
    }

    protected function index() {
        
    }
    
}
