<?php

defined('_IS_VALIDATION_') or die('Acesso n�o permitido.');

final class View extends ViewApplication {


    public $extVersion = 0; /*N�o incluir arquivos do Extjs*/
    public $angularJS = true;
    public $bootstrapCSS = true;
    public $angularMaterial = true;

    public function index($params, $dados) {
        require_once('views/index.html');
    }
}