<?php
defined('_IS_VALIDATION_') or die('Acesso n�o permitido.');

abstract class ViewApplication extends ViewGenerics
{
    public $extVersion = 0; //N�o incluir arquivos do Extjs
    public $angularJS = true;
    public $bootstrapCSS = true;
    public $angularMaterial = true;
}