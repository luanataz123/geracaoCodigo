<?php
defined('_IS_VALIDATION_') or die('Acesso não permitido.');

abstract class ViewApplication extends ViewGenerics
{
    public $extVersion = 0; //Não incluir arquivos do Extjs
    public $angularJS = true;
    public $bootstrapCSS = true;
    public $angularMaterial = true;
}