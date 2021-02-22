<?php

defined('_IS_VALIDATION_') or die('Acesso não permitido.');

final class Controller extends ControllerApplication {

    public function __construct($config) {
        parent::__construct($config);
        parent::verificarSessao();
        parent::init();
    }

    protected function index() {}
    
    protected function abrirEditar@NmTabela@() {}


    /**
     * Permite incluir ou editar um registro.
     *
     * @access protected
     * @return string/JSON
     */
    
    protected function salvar@NmTabela@() {
    //Verifica se esses campos foram enviados, mas não precisam conter informação
        $arrayParams = $this->verificarParamsObrigatorios(
                array(
                    #colunasForm#)
        );

        if (!empty($arrayParams)) {
            $json['success'] = false;
            $json['msg'] = utf8_encode("Faltam parâmetros para realização desta operação: ".implode(', ', array_keys($arrayParams)));
            print json_encode($json); die;
        }

        $id = $this->model->salvar@NmTabela@();

        if (!$id) {
            $json['success'] = false;
            $json['msg'] = utf8_encode("Erro ao salvar registro.");
            print json_encode($json);
            die;
        }

        if (!$this->model->completeTrans()) {
            print json_encode($this->model->error);
            die;
        } else {
            $json['success'] = true;
            $json['msg'] = utf8_encode('Operação realizada com sucesso.');
            print json_encode($json);
            die;
        }
    }

    protected function excluir@NmTabela@() {
        
        //Verifica se esses campos foram enviados, mas não precisam conter informação
        $arrayParams = $this->verificarParamsObrigatorios(array('@COLUNA_ID@'));

        if (!empty($arrayParams)) {
            $json['success'] = false;
            $json['msg'] = utf8_encode("Faltam parâmetros para realização desta operação.");
            print json_encode($json); die;
        }

        $retorno = $this->model->excluir@NmTabela@();

        if ($retorno) {
            $json['success'] = true;
            $json['msg'] = utf8_encode("Exclusão realizada com sucesso.");
            print json_encode($json);
            die;
        } else {
            print json_encode($this->model->error);
            die;
        }
    }

}
