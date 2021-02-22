<?php

defined('_IS_VALIDATION_') or die('Acesso não permitido.');

final class Model extends ModelApplication {
    
    private function validarSalvar@NmTabela@($debug = false){
        $and = '';
        $msg = '';
        $bind = array();
        
        /*Exemplo de validação

        $bind['COLUNA'] = $this->params['COLUNA'];
        
        if (!empty($this->params['@COLUNA_ID@'])) {
            $and .= " AND @COLUNA_ID@ <> :@COLUNA_ID@ ";
            $bind['@COLUNA_ID@'] = (int) $this->params['@COLUNA_ID@'];
        }
        
        $sql = " SELECT distinct 1 as st_existe
                   FROM @NM_ESQUEMA@.tabela
                  WHERE 1 = 1 {$and}
                  and coluna = :COLUNA";
        
        $retorno = $this->pageExecuteSelect($sql, $bind);
        
        
        if ($retorno['total'] > 0){
            $msg .= 'Mensagem de erro!<br>';
        }
        
        if (!$retorno) {
            print json_encode($this->error);
            die;
        }        
        */
        
        @VALIDACAO_PERIODO@
        
        
        if ($debug) {
            print_r($this->query);
            die;
        }
        
        return $msg;
    }
    
    public function salvar@NmTabela@($debug = false) {
        $msgErro = $this->validarSalvar@NmTabela@($debug);
        
        if($msgErro){
            print json_encode(array('success' => false, 'msg' => utf8_encode($msgErro)));
            die;
        }
        
        #bindColuna#
        
        if(empty($this->params['@COLUNA_ID@'])) {
        
            $bind['@COLUNA_ID@'] = (int)$this->getNextSequence('@NM_ESQUEMA@.SEQ_@NM_TABELA@');

            $sql = "
                INSERT INTO @NM_ESQUEMA@.@NM_TABELA@ (
                    #insertColuna#) VALUES (
                    #insertValuesColuna#)
            ";
        } else {
            $bind['@COLUNA_ID@'] = (int)$this->params['@COLUNA_ID@'];
            
            $sql = "
                UPDATE @NM_ESQUEMA@.@NM_TABELA@
                SET #updateColuna#
                WHERE @COLUNA_ID@ = :@COLUNA_ID@
            ";
        }

        $retorno = $this->execute($sql, $bind);

        if($debug){
            print_r($bind);
            print_r($this->query); die;
        }
        
        return $retorno ? $bind['@COLUNA_ID@'] : false;
    }
    
    private function validarExcluir@NmTabela@($debug = false){
        $and = '';
        $msg = '';
        $bind = array();
        
        /*Exemplo de validação
        
        $bind['@COLUNA_ID@'] = (int) $this->params['@COLUNA_ID@'];
        
        $sql = " SELECT distinct 1 as st_existe
        FROM @NM_ESQUEMA@.tabela_filha
        WHERE @COLUNA_ID@ = :@COLUNA_ID@";
        
        $retorno = $this->pageExecuteSelect($sql, $bind);
        
        
        if ($retorno['total'] > 0){
            $msg .= 'Não foi possível excluir o registro, pois existe(m) ... cadastrado(s) para ele!<br>';
        }
        
        if (!$retorno) {
            print json_encode($this->error);
            die;
        }
        
        */

        if ($debug) {
            print_r($this->query);
            die;
        }
        
        return $msg;
    }
    
    public function excluir@NmTabela@($debug = false) {
        $msgErro = $this->validarExcluir@NmTabela@($debug);
        
        if($msgErro){
            print json_encode(array('success' => false, 'msg' => utf8_encode($msgErro)));
            die;
        }
        
        $bind['@COLUNA_ID@'] = (int)$this->params['@COLUNA_ID@'];
        
        $sql = "
            DELETE FROM @NM_ESQUEMA@.@NM_TABELA@
            WHERE @COLUNA_ID@ = :@COLUNA_ID@";

        $this->execute($sql, $bind);
        
        if($debug){
            print_r($this->query); die;
        }
        
        return $this->completeTrans();
    }
}
