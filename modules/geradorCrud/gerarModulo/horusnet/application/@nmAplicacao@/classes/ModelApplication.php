<?php
/*defined('_IS_VALIDATION_') or die('Acesso não permitido.');

abstract class ModelApplication extends ModelGenerics
{*/
    /************************demais funcoes**********************************/
    
    
/*FRAGMENTO*//*BEGIN(ModelApplicationModulo)*/
    public function consultar@NmTabela@($debug = false) {

        $and = '';
        $bind = array();
        $orderBy = '@AliasNmColunaSort@ ASC';
        
        if (!empty($this->params['order_by']) && is_array($this->params['order_by'])) {
            $orderBy = '';
            for($i=0;$i<count($this->params['order_by']);$i++){
                $sentido = ' ASC';
                if ($this->params['order_by'][$i]['descending'] == true){
                    $sentido = ' DESC';
                }
                
                if (!empty($orderBy)){
                    $orderBy .= ', ';
                }
                
                $orderBy .= $this->params['order_by'][$i]['columnBanco'] . $sentido;
                
            }
        }
        
        if (!empty($this->params['@DS_GRID_COLUNA_PESQUISA@'])) {
            $camposPesquisa[] = " UPPER(CONVERT(@AliasNmColunaPesquisa@, 'ZHT16MSWIN950')) LIKE CONVERT(UPPER(:@DS_GRID_COLUNA_PESQUISA@), 'ZHT16MSWIN950') ";
            /*$camposPesquisa[] = " UPPER(CONVERT(MinhaColunaPesquisa, 'ZHT16MSWIN950')) LIKE CONVERT(UPPER(:@DS_GRID_COLUNA_PESQUISA@), 'ZHT16MSWIN950') ";*/

            $and .= " AND (" . implode(' OR ', $camposPesquisa) . ")";

            $bind['@DS_GRID_COLUNA_PESQUISA@'] = "%{$this->params['@DS_GRID_COLUNA_PESQUISA@']}%";
        }
        
        if(!empty($this->params['SomenteAtivos'])){
            $and .= " AND @aliasTabela@.st_ativo = 1 ";
        }
        

        $sql = "
            SELECT 
                #selectColuna#
            FROM
                #fromTabela# 
            WHERE 1 = 1 {$and}
            ORDER BY ". $orderBy;
        
        if (!empty($this->params['pagina_atual'])){
            $this->setPagination((($this->params['pagina_atual'] - 1) * $this->params['itens_por_pagina']) + 1, $this->params['itens_por_pagina']);
        }
        
        $retorno = $this->pageExecuteSelect($sql, $bind);

        if ($debug) {
            print_r($this->query);
            die;
        }

        if (!$retorno) {
            print json_encode($this->error);
            die;
        }

        return $retorno;
    }
    
    #consultaSearchLocal#
    
    #consultaSearchRemoto#
/*FRAGMENTO*//*END(ModelApplicationModulo)*/
    /************************demais funcoes**********************************/
/*}*/
    