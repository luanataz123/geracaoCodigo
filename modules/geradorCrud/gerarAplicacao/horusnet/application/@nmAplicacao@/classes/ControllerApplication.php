<?php
defined('_IS_VALIDATION_') or die('Acesso n�o permitido.');

abstract class ControllerApplication extends ControllerGenerics
{
    protected $autoriza;
    
    public function __construct($config)
    {
        parent::__construct($config);
        parent::verificarSessao();
    }
    
    /*FRAGMENTO:=ControllerApplicationModulo*/
}