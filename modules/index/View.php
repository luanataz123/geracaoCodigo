<?php

defined('_IS_VALIDATION_') or die('Acesso n�o permitido.');

final class View extends ViewApplication {

    public $extVersion = 0; //N�o incluir arquivos do Extjs
    public $angularJS = true;
    public $bootstrapCSS = true;
    public $angularMaterial = false;

    public function index($params, $dados) {
        $this->openHTML('Gera��o de C�digo');
        require_once('views/index.php');
        require_once('views/index.html');
        $this->closeHTML();
    }
}
