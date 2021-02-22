<?php
defined('_IS_VALIDATION_') or die('Acesso n�o permitido.');

abstract class ControllerApplication extends ControllerGenerics
{
    protected $autoriza;
 
    private $variaveis;
    private $variaveisLoop;
    private $arrArquivosModelo;
    private $arrArquivosModeloLoop;
    private $nomeArquivoZip;
    
    /**
     * @return mixed
     */
    protected function getNomeArquivoZip()
    {
        return $this->nomeArquivoZip;
    }

    /**
     * @param mixed $nomeArquivoZip
     */
    protected function setNomeArquivoZip($nomeArquivoZip)
    {
        $this->nomeArquivoZip = $nomeArquivoZip;
    }

    /**
     * @return mixed
     */
    protected function getVariaveisLoop()
    {
        return $this->variaveisLoop;
    }
    
    /**
     * @param mixed $variaveisLoop
     */
    protected function setVariavelLoop($chave, $arrValor)
    {
        if (!array_key_exists($chave, $this->variaveisLoop)){
            $this->variaveisLoop[$chave] = array();
        }
        $this->variaveisLoop[$chave][] = $arrValor;
    }
    
    /**
     * @return mixed
     */
    protected function getVariaveis()
    {
        return $this->variaveis;
    }
    
    /**
     * @param mixed $variaveis
     */
    protected function setVariavel($chave, $valor)
    {
        $this->variaveis[$chave] = $valor;
    }
    
    
    public function __construct($config)
    {
        parent::__construct($config);
        parent::verificarSessao();
        
        $this->variaveis = array();
        $this->variaveisLoop = array();
        $this->resetArrArquivos();
    }
    
    protected function resetArrArquivos(){
        $this->arrArquivosModelo = array();
        $this->arrArquivosModeloLoop = array();
    }
    
    /**
     * L� o diret�rio informado, criando um array com todos os arquivos que devem fazer parte do
     * ZIP final
     * 
     * @param string $diretorio: caminho completo do diret�rio, incluindo o $this->getConfiguration()->pathModule
     */
    protected function setArrArquivosModelo($diretorio){
        
        if(file_exists($diretorio)){
            
            if ($handle = opendir($diretorio)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        /*Se for um arquivo, adiciona ele no array*/
                        if (!is_dir($diretorio . '/' . $file)){
                            $pasta = substr($diretorio, strlen($this->getConfiguration()->pathModule));
                            $arrNmArquivo = explode('.', $file);
                            $this->arrArquivosModelo[] = array('pasta'=>$pasta, 'nmArquivo'=>$arrNmArquivo[0], 'extensao'=>'.'.$arrNmArquivo[1]);
                        } else {
                            /*Se for um diret�rio:*/
                            /*se come�ar com &, � um diret�rio de configura��o, n�o deve ser adicionado ao ZIP final, somente ignorar*/
                            /*se come�ar com #, dever�o ser criadas v�rias inst�ncias do diret�rio atrav�s de loop, por isso encaminha para a fun��o setArrArquivosModeloLoop*/
                            /*se n�o come�ar com nenhuma das palavras reservadas acima, deve-se entrar no diret�rio e chamar a pr�pria fun��o setArrArquivosModelo de forma recursiva, at� que se leia todos os arquivos dos diret�rios e subdiret�rios da pasta informada*/
                            if ($file[0] != '&' && $file[0] != '#' ){
                                $this->setArrArquivosModelo($diretorio . '/' . $file);
                            } else if ($file[0] == '#'){
                                $this->setArrArquivosModeloLoop($diretorio . '/' . $file);
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * L� o diret�rio informado, criando um array com todos os arquivos que devem ser gerados no ZIP
     * final atrav�s de loop em um array
     *
     * @param string $diretorio: caminho completo do diret�rio, incluindo o $this->getConfiguration()->pathModule
     */
     protected function setArrArquivosModeloLoop($diretorio){
        
        if(file_exists($diretorio)){
            
            if ($handle = opendir($diretorio)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        /*Se for um arquivo, adiciona ele no array*/
                        if (!is_dir($diretorio . '/' . $file)){
                            $pasta = substr($diretorio, strlen($this->getConfiguration()->pathModule));
                            $arrNmArquivo = explode('.', $file);
                            $this->arrArquivosModeloLoop[] = array('pasta'=>$pasta, 'nmArquivo'=>$arrNmArquivo[0], 'extensao'=>'.'.$arrNmArquivo[1]);
                        } else {
                            /*Se for um diret�rio:*/
                            /*se come�ar com &, � um diret�rio de configura��o, n�o deve ser adicionado ao ZIP final, somente ignorar*/
                            /*se n�o come�ar com &, deve-se entrar no diret�rio e chamar a pr�pria fun��o setArrArquivosModeloLoop de forma recursiva, at� que se leia todos os arquivos dos diret�rios e subdiret�rios da pasta informada*/
                            if ($file[0] != '&'){
                                $this->setArrArquivosModeloLoop($diretorio . '/' . $file);
                            }
                        }
                    }
                }
            }
        }
    }
    
    /*Faz as transforma��es necess�rias nos par�metros informados pelo usu�rio e popula as 
     * vari�veis que ser�o utilizadas nos modelos de aplica��o.
     * 
     * Deve-se sempre verificar a exist�ncia do par�metro com a fun��o array_key_exists('nomeParametro', $parametros)
     * antes de utiliz�-lo, pois como essa fun��o pode ser compartilhada por todos os modelos de 
     * gera��o de c�digo, pode ser que o par�metro n�o exista em algumas situa��es
     * 
     * Na medida do poss�vel, o nome da vari�vel deve se assemelhar ao conte�do
     * em termos de mai�sculas, min�sculas e h�fen
     * 
     * O array &variaveis guarda as palavras-chave que ser�o sempre iguais para toda a aplica��o,
     * por exemplo: @nmAplicacao@
     * 
     * O array $variaveisLoop guarda as palavras-chave que devem variar de acordo com um array,
     * por exemplo: %NM_COLUNA%, na hora de criar um grid, dever� haver um loop criando uma <td>
     * para cada coluna da tabela, por isso criamos um array dentro de $variaveisLoop com o nome 
     * 'COLUNA' que dever� conter um item para cada coluna da tabela, no formato 
     * $variaveisLoop['COLUNA'][$i]['%NM_COLUNA%']
     * 
     * Por conven��o, para facilitar a compreens�o dos modelos de aplica��o:
     * Vari�veis que ficam dentro do array $variaveis devem come�ar e terminar com o s�mbolo @
     * Vari�veis que ficam dentro do array $variaveisLoop devem come�ar e terminar com o s�mbolo %
     * */
    protected function popularVariaveis($parametros){
        
        $this->setVariavel('@VALIDACAO_PERIODO@', '');
        
        /*Coluna que ser� usada para ordena��o inicial da tabela*/
        if (array_key_exists('colunaSort', $parametros)){
            $COLUNA_SORT = $parametros['colunaSort'];
            
            $this->setVariavel('@COLUNA_SORT@', $COLUNA_SORT);
            $this->setVariavel('@coluna_sort@', strtolower($COLUNA_SORT));
        }
        
        /*Coluna que ser� usada para pesquisa textual no grid*/
        if (array_key_exists('colunaPesquisa', $parametros)){
            $this->setVariavel('@COLUNA_PESQUISA@', $parametros['colunaPesquisa']);
        }
        
        /*Coluna que � a chave prim�ria da tabela*/
        if (array_key_exists('colunaPK', $parametros)){
            $COLUNA_ID = $parametros['colunaPK'];
            $this->setVariavel('@COLUNA_ID@', $COLUNA_ID);
        }
        
        $arrAlias = array();
        
        if (array_key_exists('colunas', $parametros)){
            $tabela = $parametros['colunas'];
            
            if(is_array($tabela)){
                $tabela = $tabela[0];
            }
            
            $arrNmTabela = $this->transformar_($tabela['NM_TABELA']);
            
            $this->setVariavel('@NM_TABELA@', $tabela['NM_TABELA']);
            $this->setVariavel('@NmTabela@', $arrNmTabela['DsTexto']);
            $this->setVariavel('@nmTabela@', $arrNmTabela['dsTexto']);
            $this->setVariavel('@NM_ESQUEMA@', $tabela['NM_ESQUEMA']);
            
            /*alias da tabela principal para as queries*/
            $this->setVariavel('@aliasTabela@', $arrNmTabela['alias']);
            
            $arrAlias[] = $arrNmTabela['alias'];
            
            /*o array 'TABELA' dever� conter todas as tabelas necess�rias para a query principal,
             * ou seja, tabela principal mais as tabelas que tem FK com a principal*/
            $this->setVariavelLoop('TABELA', array('%NM_ESQUEMA%'=>$tabela['NM_ESQUEMA'],'%NM_TABELA%'=>$tabela['NM_TABELA'], '%alias%' => $arrNmTabela['alias'], '%JOIN%'=>'NO', '%ID_PRINCIPAL%'=>'', '%ID_SECUNDARIA%'=>''));
            
        }
        
        if (array_key_exists('NM_APLICACAO', $parametros)){
            $nmAplicacao = $parametros['NM_APLICACAO'];
            $NmAplicacao = $nmAplicacao;
            $NmAplicacao[0] = strtoupper($NmAplicacao[0]);
            $NMAPLICACAO = strtoupper($nmAplicacao);
            
            $this->setVariavel('@nmAplicacao@', $nmAplicacao);
            $this->setVariavel('@NmAplicacao@', $NmAplicacao);
            $this->setVariavel('@NMAPLICACAO@', $NMAPLICACAO);
        }
            
        if (array_key_exists('DS_TITULO_APLICACAO', $parametros)){
            $this->setVariavel('@DsTituloAplicacaoAcentuado@', $parametros['DS_TITULO_APLICACAO']);
        }
        
        /*a aplica��o sempre ter� um menu principal, com os nomes dos perfis de usu�rios
         * e para cada perfil, ter� um submenu com as telas que aquele
         * perfil pode acessar, portanto a aba com o m�dulo ser� uma tab secund�ria*/
        if (array_key_exists('NM_ITEM_MENU', $parametros)){
            $arrItemMenu = $this->transformarTextoAcentuado($parametros['NM_ITEM_MENU']);
            $this->setVariavel('@tab-secundaria@', $arrItemMenu['dsTextoHifen']);
            $this->setVariavel('@ItemMenuAcentuado@', $parametros['NM_ITEM_MENU']);
        }
        
        if (array_key_exists('NM_PERFIS', $parametros)){
            $arrTituloPerfil = explode(',', $parametros['NM_PERFIS']);
        
            foreach($arrTituloPerfil as $tituloPerfil){
                $tituloPerfil = trim($tituloPerfil);
                $arrTitulo = $this->transformarTextoAcentuado($tituloPerfil);
                /*o array 'PERFIL' cont�m todos os perfis que podem acessar o m�dulo*/
                $this->setVariavelLoop('PERFIL', array('%perfilAtual%'=> 'visao'.$arrTitulo['DsTexto'], '%PerfilAtual%'=> 'Visao'.$arrTitulo['DsTexto'], '%tab-primaria%'=> 'visao-'.$arrTitulo['dsTextoHifen']));
            }
        }
        
        
        if (array_key_exists('ST_GERAR_ITEM_PERFIL', $parametros) && $parametros['ST_GERAR_ITEM_PERFIL']){
            $arrTituloPerfilCriar = explode(',', $parametros['GERAR_NM_ITEM_PERFIS']);
            
            foreach($arrTituloPerfilCriar as $tituloPerfil){
                $tituloPerfil = trim($tituloPerfil);
                $arrTitulo = $this->transformarTextoAcentuado($tituloPerfil);
                /*o array 'PERFIL_CRIAR' cont�m os itens de menu principal (perfis de usu�rio)
                 * que ainda n�o existem na aplica��o, portanto deve ser gerado o c�digo
                 * para sua cria��o*/
                $this->setVariavelLoop('PERFIL_CRIAR', array('%perfilAtual%'=> 'visao'.$arrTitulo['DsTexto'], '%PerfilAtual%'=> 'Visao'.$arrTitulo['DsTexto'], '%tab-primaria%'=> 'visao-'.$arrTitulo['dsTextoHifen'], '%ItemMenuAcentuado%'=>$tituloPerfil, '%PERFILATUAL%'=>'VISAO'.$arrTitulo['DSTEXTO']));
            }
        }
        
        
        if (array_key_exists('colunas', $parametros)){
            $COLUNA_DT_INI = '';
            $COLUNA_DT_FIM = '';
            
            foreach($parametros['colunas'] as $i=>$coluna){
                $dsGrid = $coluna['NM_COLUNA'];
                $ID_FK = '';
                $DS_FK = '';
                $nmTabelaFK = '';
                $NmTabelaFK = '';
                $aliasColuna = $arrAlias[0];
                $htmlOpcoes = '';
                $ArrKeyValue = '';
                $orderBy = '';
                $wherePesquisa = '';
                $valorInicial = 'null';
                
                /*o array 'SELECT_COLUNA' cont�m todas as colunas que devem estar na cl�usula
                 * select da query principal*/
                $this->setVariavelLoop('SELECT_COLUNA', array('%ALIAS_NM_COLUNA%'=> $aliasColuna . "." . $coluna['NM_COLUNA'], '%TIPO_SELECT%' => $coluna['DS_TIPO'], '%AS_DS_COLUNA%'=>$coluna['NM_COLUNA']));
                
                $arrNmColuna = $this->transformar_($coluna['NM_COLUNA']);
                
                if (array_key_exists('TABELA_FK', $coluna) && $coluna['TABELA_FK']){
                    $arrDados = explode(';', $coluna['TABELA_FK']);
                    $arrFK = explode('.', $arrDados[0]);
                    $arrFK = array_combine(array('%NM_ESQUEMA%', '%NM_TABELA%'), $arrFK);
                    $arrNmTabelaFK = $this->transformar_($arrFK['%NM_TABELA%']);
                    $arrFK['%NmTabela%'] = $arrNmTabelaFK['DsTexto'];
                    $NmTabelaFK = $arrFK['%NmTabela%'];
                    $arrFK['%nmTabela%'] = $arrNmTabelaFK['dsTexto'];
                    $nmTabelaFK = $arrFK['%nmTabela%'];
                    $arrFK['%nmTabelaHifen%'] = $arrNmTabelaFK['dsTextoHifen'];
                    $arrFK['%COLUNA_ID%'] = $arrDados[1];
                    $ID_FK = $arrFK['%COLUNA_ID%'];
                    $arrFK['%COLUNA_DS%'] = $arrDados[2];
                    $DS_FK = $arrFK['%COLUNA_DS%'];
                    $arrFK['%NM_COLUNA%'] = $coluna['NM_COLUNA'];
                    $arrFK['%COLUNA%'] = $arrNmColuna['TEXTO'];
                    
                    if ($coluna['DS_TIPO_INPUT'] == 'searchTabelaRemoto'){
                        $nmVariavel = 'SEARCH_REMOTO';
                    } else {
                        $nmVariavel = 'SEARCH_LOCAL';
                    }
                    
                    /*o array 'SEARCH_REMOTO' cont�m todas as colunas cujo input no form ser� um 
                     * search que busca os dados remotamente, de acordo com a string de busca
                     * fornecida pelo usu�rio
                     * 
                     * o array 'SEARCH_LOCAL' cont�m todas as colunas cujo input no form ser� um
                     * search (select) que busca os dados em um array previamente carregado*/
                    $this->setVariavelLoop($nmVariavel, $arrFK);
                    $dsGrid = 'DS_'. $arrNmColuna['TEXTO'];
                    
                    $alias = $arrNmTabelaFK['alias'];
                    $qtAliasIguais = 0;
                    
                    foreach($arrAlias as $itemAlias){
                        if ($itemAlias == $alias){
                            $qtAliasIguais++;
                        }
                    }
                    
                    $arrAlias[] = $alias;
                    
                    if ($qtAliasIguais > 0){
                        $alias = $alias . $qtAliasIguais;
                    }
                    
                    $aliasColuna = $alias;
                    
                    if ($coluna['ST_NULLABLE'] == 'N'){
                        $join = 'INNER';
                    } else {
                        $join = 'LEFT';
                    }
                    
                    $this->setVariavelLoop('TABELA', array('%NM_ESQUEMA%'=>$arrFK['%NM_ESQUEMA%'],'%NM_TABELA%'=>$arrFK['%NM_TABELA%'], '%alias%' => $alias, '%JOIN%'=>$join, '%ID_PRINCIPAL%'=>$coluna['NM_COLUNA'], '%ID_SECUNDARIA%'=>$ID_FK));
                    $this->setVariavelLoop('SELECT_COLUNA', array('%ALIAS_NM_COLUNA%'=> $aliasColuna . "." . $DS_FK, '%TIPO_SELECT%' => 'VARCHAR2', '%AS_DS_COLUNA%'=>$dsGrid));
                    
                }
                
                
                if (array_key_exists('ARRAY_OPCOES', $coluna) && $coluna['ARRAY_OPCOES']){
                    $arrDados = json_decode(utf8_encode($coluna['ARRAY_OPCOES']));
                    $alias_nm_coluna = "CASE " . $aliasColuna . "." . $coluna['NM_COLUNA'] . " ";
                    
                    /*por padr�o, inputs do tipo Checkbox ser�o apresentadas no grid com o 
                     * valor Sim, quando estiverem marcadas e N�o quando estiverem desmarcadas,
                     * o usu�rio poder� alterar isso manualmente, ap�s a gera��o do c�digo, caso deseje*/
                    foreach($arrDados as $key=>$value){
                        if ($value == 'CHECKED'){
                            $ds = 'Sim';
                        } else if($value == 'UNCHECKED'){
                            $ds = 'N�o';
                        } else {
                            $ds = utf8_decode($value);
                        }
                        
                        if ($coluna['DS_TIPO'] == 'NUMBER'){
                            $aspa = "";
                        } else {
                            $aspa = "'";    
                        }
                        
                        $alias_nm_coluna .= "WHEN " . $aspa.$key.$aspa . " THEN " . "'{$ds}' ";
                    }
                    
                    $alias_nm_coluna .= "ELSE null END";
                    $dsGrid = 'DS_' . $coluna['NM_COLUNA'];
                    $orderBy = $dsGrid;
                    $wherePesquisa = $alias_nm_coluna;
                    $this->setVariavelLoop('SELECT_COLUNA', array('%ALIAS_NM_COLUNA%'=> $alias_nm_coluna, '%TIPO_SELECT%' => 'CASE', '%AS_DS_COLUNA%'=>$dsGrid));
                    
                    if($coluna['DS_TIPO_INPUT'] == 'radio' ){
                        
                        foreach($arrDados as $key=>$value){
                            $htmlOpcoes .= '<div class="form-group col-xs-6 col-md-2">
                            <label>
                                <input type="radio" ng-model="@nmTabela@.'.$coluna['NM_COLUNA'].'" value="'.$key.'">
                                '.utf8_decode($value).'
                            </label>
                        </div>
                        ';
                            if ($valorInicial == 'null'){
                                $valorInicial = "'{$key}'";
                            }
                        }
                    } else if ($coluna['DS_TIPO_INPUT'] == 'searchArray'){
                        foreach($arrDados as $key => $value){
                            if ($ArrKeyValue){
                                $ArrKeyValue .= ',';
                            }
                            
                            $ArrKeyValue .= "{'KEY': '{$key}', 'VALUE': '".utf8_decode($value)."'}";
                        }
                        $this->setVariavelLoop('SEARCH_ARRAY', array('%NmColuna%' => $arrNmColuna['DsTexto'], '%ArrKeyValue%'=> $ArrKeyValue));
                        
                    } else if($coluna['DS_TIPO_INPUT'] == 'checkbox'){
                        foreach($arrDados as $key => $value){
                            if ($value == 'CHECKED'){
                                $trueFalse = 'true';
                            } else {
-                               $trueFalse = 'false';
                                $valorInicial = "'{$key}'";
                            }
                            $htmlOpcoes .= 'ng-'.$trueFalse.'-value="'."'{$key}'" . '" ';
                        }
                    }
                }
                
                if($coluna['DS_TIPO'] == 'DATE'){
                    $this->setVariavelLoop('COLUNA_DATA', array('%NM_COLUNA%'=>$coluna['NM_COLUNA']));
                    $arrDT = explode('_', $coluna['NM_COLUNA']);
                    if ($arrDT[1] == 'INI' || $arrDT[1] == 'INICIO'){
                        $COLUNA_DT_INI = $i;
                    } else if($arrDT[1] == 'FIM' || $arrDT[1] == 'FINAL' || $arrDT[1] == 'TERMINO'){
                        $COLUNA_DT_FIM = $i;
                    }
                    $wherePesquisa = "TO_CHAR({$aliasColuna}.{$coluna['NM_COLUNA']}, 'dd/mm/yyyy')";
                }
                
                if($coluna['DS_TIPO'] == 'NUMBER' && $dsGrid == $coluna['NM_COLUNA']){
                    /*o array 'COLUNA_INT' cont�m as colunas que s�o num�ricas e que a
                     * sua apresenta��o no grid ser� esse n�mero mesmo, n�o uma descri��o associada,
                     * dessa forma, para fins de ordena��o, dever� ser tratada como n�mero*/
                    $this->setVariavelLoop('COLUNA_INT', array('%NM_COLUNA%'=>$coluna['NM_COLUNA']));
                }
                
                if($coluna['DS_TIPO_INPUT'] == 'number'){
                    /*o array 'INPUT_NUMBER' cont�m as colunas que ter�o um input do tipo number*/
                    $this->setVariavelLoop('INPUT_NUMBER', array('%NM_COLUNA%'=>$coluna['NM_COLUNA']));
                }
                
                if($coluna['ST_NULLABLE'] == 'N'){
                    $required = 'required';
                }else{
                    $required = '';
                }
                
                
                
                
                if ($coluna['NM_COLUNA'] == $COLUNA_ID){
                    $VALOR_NM_COLUNA_INSERT = ':' . $coluna['NM_COLUNA'];
                    $VALOR_NM_COLUNA_UPDATE = '';
                } else if ($coluna['DS_TIPO_INPUT'] == 'nenhum'){
                    $VALOR_NM_COLUNA_INSERT = 'NULL';
                    $VALOR_NM_COLUNA_UPDATE = '';
                } else if ($coluna['DS_TIPO'] == 'DATE'){
                    $VALOR_NM_COLUNA_INSERT = "TO_DATE(:{$coluna['NM_COLUNA']}, 'YYYY-MM-DD')";
                    $VALOR_NM_COLUNA_UPDATE = $coluna['NM_COLUNA'] . " = {$VALOR_NM_COLUNA_INSERT}
                        ";
                } else {
                    $VALOR_NM_COLUNA_INSERT = ':' . $coluna['NM_COLUNA'];
                    $VALOR_NM_COLUNA_UPDATE = $coluna['NM_COLUNA'] . " = :{$coluna['NM_COLUNA']}
                        ";
                }
                
                if (!$orderBy){
                    $orderBy = $aliasColuna . '.';
                    if ($DS_FK){
                        $orderBy .= $DS_FK;
                    } else {
                        $orderBy .= $coluna['NM_COLUNA'];
                    }
                }
                
                if (!$wherePesquisa){
                    $wherePesquisa = $aliasColuna . '.';
                    if ($DS_FK){
                        $wherePesquisa .= $DS_FK;
                    } else {
                        $wherePesquisa .= $coluna['NM_COLUNA'];
                    }
                }
                
                /*o array 'COLUNA' cont�m todas as colunas da tabela*/
                $this->setVariavelLoop('COLUNA', array('%NM_COLUNA%'=>$coluna['NM_COLUNA'], '%DS_TIPO%'=>$coluna['DS_TIPO'], '%VALOR_NM_COLUNA_INSERT%'=> $VALOR_NM_COLUNA_INSERT, '%VALOR_NM_COLUNA_UPDATE%' => $VALOR_NM_COLUNA_UPDATE, '%NmColuna%' => $arrNmColuna['DsTexto'], '%nmColuna%' => $arrNmColuna['dsTexto'], '%COLUNA%'=>$arrNmColuna['TEXTO'], '%DS_LABEL%' => $coluna['DS_LABEL'], '%DS_GRID%' => $dsGrid, '%DS_TIPO_INPUT%' => $coluna['DS_TIPO_INPUT'], '%ID_FK%' => $ID_FK, '%DS_FK%' => $DS_FK, '%nmTabelaFK%' => $nmTabelaFK, '%NmTabelaFK%' => $NmTabelaFK, '%required%' => $required, '%QT_TAMANHO%'=> $coluna['QT_TAMANHO'], '%htmlOpcoes%'=> $htmlOpcoes, '%NG_MIN_MAX_PERIODO%' => '', '%ERROR_MIN_MAX_PERIODO%' => '', '%orderBy%'=>$orderBy, '%valorInicial%'=>$valorInicial));
                
                /*o array 'COLUNA_FORM' cont�m todas as colunas que devem estar no form de cadastro/edi��o */
                if ($coluna['DS_TIPO_INPUT'] != 'nenhum'){
                    $this->setVariavelLoop('COLUNA_FORM', array('%NM_COLUNA%' => $coluna['NM_COLUNA'], '%DS_TIPO%'=>$coluna['DS_TIPO']));
                }
                
                if (array_key_exists('colunaSort', $parametros) && $coluna['NM_COLUNA'] == $parametros['colunaSort']){
                    $this->setVariavel('@LabelColunaSort@', $coluna['DS_LABEL']);
                    $this->setVariavel('@DS_GRID_COLUNA_SORT@', $dsGrid);
                    $this->setVariavel('@AliasNmColunaSort@', $orderBy);
                }
                
                if (array_key_exists('colunaPesquisa', $parametros) && $coluna['NM_COLUNA'] == $parametros['colunaPesquisa']){
                    $this->setVariavel('@LabelColunaPesquisa@', $coluna['DS_LABEL']);
                    $this->setVariavel('@DS_GRID_COLUNA_PESQUISA@', $dsGrid);
                    $this->setVariavel('@AliasNmColunaPesquisa@', $wherePesquisa);
                }
                
            }
            
            /*configura valida��es no front end e no back end para quando houver per�odos de data na tabela*/
            if ($COLUNA_DT_INI && $COLUNA_DT_FIM){
                $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NG_MIN_MAX_PERIODO%'] = 'ng-max="@nmTabela@.' . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . '"';
                $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NG_MIN_MAX_PERIODO%'] = 'ng-min="@nmTabela@.' . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . '"';

                $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%ERROR_MIN_MAX_PERIODO%'] = '<p class="help-block" role="alert" ng-if="cadastro@NmTabela@.' . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . '.$error.max">
                <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>'.$this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%DS_LABEL%'] .' deve ser menor que '.$this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%DS_LABEL%'] .'
                </p>';
                $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%ERROR_MIN_MAX_PERIODO%'] = '<p class="help-block" role="alert" ng-if="cadastro@NmTabela@.' . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . '.$error.min">
                <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>'.$this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%DS_LABEL%'] .' deve ser maior que '.$this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%DS_LABEL%'] .'
                </p>';
                
                $validacaoPeriodo = "        /* Valida��o de per�odo*/


        \$bind['" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . "'] = substr(\$this->params['" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . "'], 0, 10);
        \$bind['" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . "'] = substr(\$this->params['" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . "'], 0, 10);
/*        
        if (!empty(\$this->params['{$COLUNA_ID}'])) {
            \$and .= ' AND {$arrAlias[0]}.{$COLUNA_ID} <> :{$COLUNA_ID} ';
            \$bind['{$COLUNA_ID}'] = (int) \$this->params['{$COLUNA_ID}'];
        }
        
        //ATEN��O: Adicionar cl�usula AND com a coluna que identifica de quem � o per�odo
        \$sql = \" SELECT distinct 1 as st_existe
                   FROM {$tabela['NM_ESQUEMA']}.{$tabela['NM_TABELA']} {$arrAlias[0]}
                  WHERE 1 = 1  {\$and}
                    AND {$arrAlias[0]}." . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . " <= TO_DATE(:" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . ", 'YYYY-MM-DD')
                    AND {$arrAlias[0]}." . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . " >= TO_DATE(:" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . ", 'YYYY-MM-DD')\";
        
        \$retorno = \$this->pageExecuteSelect(\$sql, \$bind);
        
        
        if (\$retorno['total'] > 0){
            \$msg .= 'O per�odo informado se sobrep�e a outro per�odo j� cadastrado!<br>';
        }

        if (!\$retorno) {
            print json_encode(\$this->error);
            die;
        }
        */

        \$inicioPeriodo = new DateTime(\$bind['" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . "']);
        \$fimPeriodo = new DateTime(\$bind['" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . "']);

        \$inicioPeriodo->setTime(6, 0);
        \$fimPeriodo->setTime(6, 0);


        if (\$inicioPeriodo > \$fimPeriodo){
            \$msg .= '".$this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%DS_LABEL%'] .' deve ser menor que '.$this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%DS_LABEL%'].".<br>';
        }
";
                
                $this->setVariavel('@VALIDACAO_PERIODO@', $validacaoPeriodo);
            }
        }
    }
    
    /*recebe como par�metro uma string com todas as letras mai�sculas, com as palavras 
     * separadas por underline, e retorna um array com essa string em diversos formatos
     * alternativos*/
    private function transformar_($DS_TEXTO){
        $arrTexto = explode('_', $DS_TEXTO);
        $alias = '';
        foreach($arrTexto as $palavra){
            $alias .= strtolower($palavra[0]);
        }
        unset($arrTexto[0]);
        $TEXTO = implode('_', $arrTexto);
        $DsTexto = str_replace('_', ' ', strtolower($DS_TEXTO));
        $dsTextoHifen = str_replace('_', '-', strtolower($DS_TEXTO));
        $DsTexto = ucwords($DsTexto);
        $DsTexto = str_replace(' ', '', $DsTexto);
        $dsTexto = $DsTexto;
        $dsTexto[0] = strtolower($DsTexto[0]);
        
        return array('DsTexto'=>$DsTexto, 'dsTexto' => $dsTexto, 'dsTextoHifen'=>$dsTextoHifen, 'TEXTO' => $TEXTO, 'alias' => $alias);
    }
    
    /*recebe como par�metro uma string em texto normal, com as palavras acentuadas e
     * separadas por espa�os, e retorna um array com essa string em diversos formatos
     * alternativos*/
    private function transformarTextoAcentuado ($dsTextoAcentuado){
        $textoSemAcento = preg_replace( '/[`^~\'"]/', null, iconv( 'UTF-8', 'ASCII//TRANSLIT', utf8_encode($dsTextoAcentuado) ) );
        $texto = strtolower($textoSemAcento);
        $textoHifen = str_replace(' ', '-', $texto);
        $textoInitcap = ucwords($texto);
        $DsTexto = str_replace(' ', '', $textoInitcap);
        $DSTEXTO = strtoupper($DsTexto);
        $dsTexto = $DsTexto;
        $dsTexto[0] = strtolower($dsTexto[0]);
        
        return array('DsTexto'=>$DsTexto, 'dsTexto' => $dsTexto, 'dsTextoHifen'=>$textoHifen, 'DSTEXTO'=>$DSTEXTO);
    }
    
    
    /**
     * Verifica se j� foi gerado subconte�do para o arquivo em modelo anterior e, caso
     * o novo conte�do esteja programado para receber esse subconte�do atrav�s da express�o
     * FRAGMENTO:= , inclui o subconte�do no conte�do final do arquivo.
     * 
     * @param string $caminho : caminho completo para o arquivo a ser gerado no ZIP, desde o diret�rio pai at� a sua extens�o
     * @param string $conteudo : conte�do que dever� ser inserido no arquivo, antes da inclus�o do fragmento
     * @param ZipArchive $zip : arquivo ZIP que est� sendo gerado com o c�digo da aplica��o
     * @return string : conte�do que dever� ser inserido no arquivo, com o fragmento j� inclu�do
     */
    private function concatenarConteudoArquivo($caminho, $conteudo, $zip){
        /* a express�o FRAGMENTO:=, que indica onde deve ser inserido o subconte�do,
         * dever� estar dentro de um coment�rio, como em arquivos com extens�o .html
         * a forma de indicar coment�rio � diferente das demais extens�es, o c�digo
         * abaixo faz a configura��o de qual tipo de coment�rio deve ser utilizado */
        $iniComentarioPadrao = '/*';
        $iniComentarioHtml = '<!--';
        $fimComentarioPadrao = '*/';
        $fimComentarioHtml = '-->';
        
        if (substr($caminho, -5) == '.html'){
            $iniComentario = $iniComentarioHtml;
            $fimComentario = $fimComentarioHtml;
        } else {
            $iniComentario = $iniComentarioPadrao;
            $fimComentario = $fimComentarioPadrao;
        }
        
        $iniFragmento = $iniComentario.'BEGIN(';
        $tamanhoIniFragmento = strlen($iniFragmento);
        $fimFragmento = ')'.$fimComentario;
        $tamanhoFimFragmento = strlen($fimFragmento);
        
        /*verifica se existe previs�o no conte�do do arquivo, de lugar para inclus�o
         * de subconte�do proveniente de modelo anterior*/
        if (!(strpos($conteudo, $iniComentario.'FRAGMENTO:=') === false)){
            $zip->close();
            $res = $zip->open($this->getNomeArquivoZip());
            $conteudoAnterior = $zip->getFromName($caminho);
            $zip->close();
            $zip->open($this->getNomeArquivoZip(),  ZipArchive::CREATE);
    
            if ($conteudoAnterior){
                /*busca no conte�do do arquivo de modelo anterior, qual(is) fragmento(s) dever�(�o) ser 
                 * copiado(s) para o conte�do final, os fragmentos s�o identificados pela palavra
                 * FRAGMENTO, dentro de um coment�rio, prosseguida pelo c�digo BEGIN(identificadorFragmento)
                 * dentro de outro coment�rio. O trecho a ser copiado vai do t�rmino dessa express�o
                 * at� a pr�xima ocorr�ncia da palavra FRAGMENTO dentro de um coment�rio*/
                $arrFragmentos = explode($iniComentario.'FRAGMENTO'.$fimComentario, $conteudoAnterior);
                foreach($arrFragmentos as $fragmento){
                    if (substr($fragmento, 0, $tamanhoIniFragmento) == $iniFragmento){
                        $posicaoFimNomeFragmento = strpos($fragmento, $fimFragmento);
                        $nomeFragmento = substr($fragmento, $tamanhoIniFragmento, $posicaoFimNomeFragmento-$tamanhoIniFragmento);
                        /*substituir o conte�do do fragmento no local do conte�do do novo arquivo onde houver
                         * a express�o FRAGMENTO:=identificadorFragmento, dentro de um coment�rio*/
                        $conteudo = str_replace($iniComentario."FRAGMENTO:={$nomeFragmento}".$fimComentario, substr($fragmento, $posicaoFimNomeFragmento+$tamanhoFimFragmento), $conteudo);
                    }
                }
            }
        }            
        return $conteudo;
    }
    
    
    /**
     * Faz a leitura de todos os arquivos da pasta raiz informada, fazendo a substitui��o das
     * palavras-chave e gerando um arquivo ZIP com o c�digo da aplica��o.
     * 
     * @param string $pastaRaiz :pasta raiz com os modelos de arquivos a serem gerados, o caminho da pasta
     * deve come�ar ap�s o endere�o do m�dulo da aplica��o geracaoCodigo que est� chamando a fun��o,
     * 
     * Exemplo: para a pasta:
     *  geracaoCodigo/modules/geradorCrud/gerarModulo
     * o par�metro seria:
     *  $pastaRaiz = gerarModulo
     * 
     * @param ZipArchive $zip : arquivo ZIP onde dever�o ser inclu�dos os arquivos com o c�digo da aplica��o
     */
    protected function gerarArquivosModelo($pastaRaiz, $zip){
        
        $this->resetArrArquivos();
        $diretorio = $this->getConfiguration()->pathModule . $pastaRaiz;
        /*cria um array com todos os arquivos que dever�o ser gerados no ZIP, com base na
         * leitura dos arquivos da pasta modelo*/
        $this->setArrArquivosModelo($diretorio);
        
        /*Gera��o dos arquivos que dever�o ser criados diretamente pela substitui��o das
         * palavras-chave nos modelos*/
        foreach($this->arrArquivosModelo as $arrArquivo){
            /*faz a substitui��o das palavras-chave dentro do conte�do dos arquivos 
             * pelos dados corretos, conforme par�metros informados pelo usu�rio*/
            $arquivo = $this->substituirVariaveis($arrArquivo['pasta'].'/', $arrArquivo['nmArquivo'], $arrArquivo['extensao']);
            $nmArquivoDestino = $arrArquivo['nmArquivo'];
            $pastaDestino = str_replace($pastaRaiz.'/', '', $arrArquivo['pasta']);
            
            /*faz a substitui��o das palavras-chave no nomes dos arquivos e pastas pelos dados
             * corretos, conforme par�metros informados pelo usu�rio*/
            foreach($this->getVariaveis() as $variavel=>$valor){
                $nmArquivoDestino = str_replace($variavel, $valor, $nmArquivoDestino);
                $pastaDestino = str_replace($variavel, $valor, $pastaDestino);
            }
            
            $caminhoCompleto = "/{$pastaDestino}/$nmArquivoDestino".$arrArquivo['extensao'];
            /*caso haja mais de um modelo para o mesmo arquivo, faz a concatena��o conforme
             * configura��o nos modelos*/
            $arquivo = $this->concatenarConteudoArquivo($caminhoCompleto, $arquivo, $zip);
            
            //echo "<pre>"; print_r($arquivo); echo "</pre>"; die;
            
            /*adiciona o arquivo j� processado ao ZIP final*/
            $zip->addFromString($caminhoCompleto, $arquivo);
        }
        
        /*Gera��o dos arquivos que dever�o ser criados atrav�s de loop em um array*/
        foreach($this->arrArquivosModeloLoop as $arrArquivo){
            /*dentro do caminho completo de cada arquivo, busca a pasta que come�a com o
             * s�mbolo #, indicando que ela e todo o seu subconte�do dever� ser gerado
             * em loop*/
            $chaveLoop = '';
            $arrArvoreDiretorios = explode('/', $arrArquivo['pasta']);
            $sairLoop = false;
            foreach($arrArvoreDiretorios as &$subPasta){
                if (!$sairLoop){
                    if($subPasta[0] == '#'){
                        $subPasta = substr($subPasta, 1);
                        $arrsubPasta = explode('-', $subPasta);
                        $subPasta = $arrsubPasta[0];
                        /*o array a ser usado para o loop ser� o que estiver escrito ap�s o s�mbolo -
                         * no nome da pasta que come�a com # */
                        $chaveLoop = $arrsubPasta[1];
                        $sairLoop = true;
                    }
                }
            }
            
            $modeloPastaDestino = implode('/', $arrArvoreDiretorios);
            
            
            $subConteudoFixo = $this->getConteudoArquivo($arrArquivo['pasta'] . '/'. $arrArquivo['nmArquivo']. $arrArquivo['extensao']);
            $variavelLoop = $this->getVariaveisLoop();
            if (array_key_exists($chaveLoop, $variavelLoop)){
                foreach($variavelLoop[$chaveLoop] as $item){
                    $subConteudo = $subConteudoFixo;
                    $pastaDestino = $modeloPastaDestino;
                    $nmArquivoDestino = $arrArquivo['nmArquivo'];
                    /*faz a substitui��o das palavras-chave do array $variaveisLoop no conte�do 
                     * do arquivo, no nome  do arquivo e nos nomes das pastas pelos dados 
                     * corretos, conforme par�metros informados pelo usu�rio. */
                    foreach($item as $variavel=>$valor){
                        $subConteudo = str_replace($variavel, $valor, $subConteudo);
                        $pastaDestino = str_replace($variavel, $valor, $pastaDestino);
                        $nmArquivoDestino = str_replace($variavel, $valor, $nmArquivoDestino);
                    }
                    $pastaDestino = str_replace($pastaRaiz.'/', '', $pastaDestino);
                    
                    /*faz a substitui��o das palavras-chave do array $variaveis no conte�do
                     * do arquivo, no nome  do arquivo e nos nomes das pastas pelos dados
                     * corretos, conforme par�metros informados pelo usu�rio. */
                    foreach($this->getVariaveis() as $variavel=>$valor){
                        $subConteudo = str_replace($variavel, $valor, $subConteudo);
                        $nmArquivoDestino = str_replace($variavel, $valor, $nmArquivoDestino);
                        $pastaDestino = str_replace($variavel, $valor, $pastaDestino);
                    }
                    
                    $caminhoCompleto = "/{$pastaDestino}/$nmArquivoDestino".$arrArquivo['extensao'];
                    
                    /*caso haja mais de um modelo para o mesmo arquivo, faz a concatena��o conforme
                     * configura��o nos modelos*/
                    $subConteudo = $this->concatenarConteudoArquivo($caminhoCompleto, $subConteudo, $zip);

                    //echo "<pre>"; print_r($subConteudo); echo "</pre>"; die;
                    
                    /*adiciona o arquivo j� processado ao ZIP final*/
                    $zip->addFromString($caminhoCompleto, $subConteudo);
                }
            }
        }
        
    }
    
    
    /**
     * Faz a leitura do conte�do do arquivo modelo e substitui pelos dados corretos, conforme
     * par�metros informados pelo usu�rio
     * 
     * @param string $pasta : caminho da pasta com o modelo do arquivo a ser processado, o caminho da pasta deve come�ar logo ap�s
     *      o caminho do m�dulo da aplica��o geracaoCodigo onde ela est� 
     * @param string $nmArquivo : nome do arquivo modelo a ser processado
     * @param string $extensao :  extensao do arquivo modelo a ser processado
     * 
     * Exemplo: para o arquivo:
     *  geracaoCodigo/modules/geradorCrud/gerarModulo/horusnet/application/@nmAplicacao@/modules/index/Controller.php
     * os par�metros seriam:
     *  $pasta = gerarModulo/horusnet/application/@nmAplicacao@/modules/index/
     *  $nmArquivo = Controller
     *  $extensao = .php
     *  
     * @return mixed|string : conte�do do arquivo, ap�s o processamento para substitui��o das vari�veis
     */
    private function substituirVariaveis($pasta, $nmArquivo, $extensao){
        $conteudo = $this->getConteudoArquivo($pasta . $nmArquivo . $extensao);
        
        /*verifica se na mesma pasta existe uma subpasta com o mesmo nome do arquivo, come�ando 
         * com o s�mbolo &, o que significa que � uma pasta com configura��es para o arquivo*/
        $diretorio = $this->getConfiguration()->pathModule . $pasta . '&'. $nmArquivo . $extensao;
        
        if(file_exists($diretorio)){
            
            /*percorre a pasta de configura��es do arquivo*/
            if ($handle = opendir($diretorio)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && !is_dir($diretorio . '/' . $file)) {
                        /*os arquivos desta pasta, que n�o sejam subpastas, ter�o o formato
                         * nomeTrecho-separador-NOME_ARRAY.txt, sendo que NOME_ARRAY representa o array
                         * $variaveisLoop['NOME_ARRAY'] que dever� ser percorrido para gera��o
                         * do trecho de c�digo*/
                        $conteudoParcial = '';
                        $arrParametros = explode('-', $file);
                        $arrParametros[2] = explode('.', $arrParametros[2]);
                        $arrParametros[2] = $arrParametros[2][0];
                        /*o s�mbolo || n�o � permitido no nome do arquivo, por isso dever� ser usada a palavra OR em seu lugar*/
                        $arrParametros[1] = str_replace('OR', '||', $arrParametros[1]);
                        $subConteudoFixo = $this->getConteudoArquivo($pasta . '&'. $nmArquivo. $extensao . '/' .$file);
                        $variavelLoop = $this->getVariaveisLoop();
                        if (array_key_exists($arrParametros[2], $variavelLoop)){
                            /*percorre o array $variaveisLoop['NOME_ARRAY'] para gera��o do trecho de c�digo*/
                            foreach($variavelLoop[$arrParametros[2]] as $item){
                                /*se existir uma subpasta dentro da pasta de configura��o do arquivo, com o nome nomeTrecho,
                                 * utiliza o conte�do do arquivo &pastaConfiguracao/nomeTrecho-separador-NOME_ARRAY.txt como
                                 * chave no array $variaveisLoop['NOME_ARRAY'][$i][chave] para saber qual modelo de arquivo 
                                 * dentro da subpasta nomeTrecho dever� ser utilizado, 
                                 * por exemplo: se $variaveisLoop['NOME_ARRAY'][$i][chave] == CHAR, utilizar o 
                                 * trecho de c�digo do modelo &pastaConfiguracao/nomeTrecho/CHAR.txt */
                                $subDiretorio = $this->getConfiguration()->pathModule . $pasta .'&' .$nmArquivo. $extensao . '/' . $arrParametros[0];
                                
                                if(file_exists($subDiretorio)){
                                    $subConteudo = $this->getConteudoArquivo($pasta . '&' . $nmArquivo. $extensao . '/' . $arrParametros[0] . '/'. $item[$subConteudoFixo] . '.txt');
                                } else {
                                    /*se n�o existir a subpasta &pastaConfiguracao/nomeTrecho,
                                     * utilizar o trecho de c�digo do modelo &pastaConfiguracao/nomeTrecho-separador-NOME_ARRAY.txt */
                                    $subConteudo = $subConteudoFixo;
                                }
                                
                                /*substitui as vari�veis do array $variaveisLoop['NOME_ARRAY'][$i]
                                 * no trecho de c�digo obtido acima*/
                                foreach($item as $variavel=>$valor){
                                    $subConteudo = str_replace($variavel, $valor, $subConteudo);
                                }
                                
                                /*concatenar os trechos de c�digo de cada item $i do array 
                                 * $variaveisLoop['NOME_ARRAY'], utilizando entre eles o separador 
                                 * definido no nome do arquivo &pastaConfiguracao/nomeTrecho-separador-NOME_ARRAY.txt */
                                if ($conteudoParcial){
                                    $conteudoParcial .= ' '.$arrParametros[1] . ' ';
                                }
                                $conteudoParcial .= $subConteudo;
                            }
                        }
                        
                        /*o resultado com a concatena��o de todos os trechos de c�digo acima dever�
                         * ser substitu�do no modelo de arquivo principal, no local configurado
                         * como #nomeTrecho# */
                        $conteudo = str_replace('#'.$arrParametros[0].'#', $conteudoParcial, $conteudo);
                    }
                }
                closedir($handle);
            }
        }
        
        /*substituir as palavras-chave do array $variaveis no conte�do do arquivo modelo*/
        foreach($this->getVariaveis() as $variavel=>$valor){
            $conteudo = str_replace($variavel, $valor, $conteudo);
        }
        
        return $conteudo;
    }
    
    
    /**
     * Retorna uma string com o conte�do do arquivo solicitado
     * 
     * @param string $caminhoArquivo : caminho do arquivo, come�ando a partir do endere�o do m�dulo
     * da aplica��o geracaoCodigo que est� chamando a fun��o
     * 
     * Exemplo: para o arquivo:
     *  geracaoCodigo/modules/geradorCrud/gerarModulo/horusnet/application/@nmAplicacao@/modules/index/Controller.php
     * o par�metro seria:
     *  $caminhoArquivo = gerarModulo/horusnet/application/@nmAplicacao@/modules/index/Controller.php
     *  
     * @return string
     */
    private function getConteudoArquivo($caminhoArquivo){
        $conteudo = '';
        $filename = $this->getConfiguration()->pathModule . $caminhoArquivo;
        if(file_exists($filename)){
            $handle = fopen($filename, "r");
            if (filesize($filename)>0){
                $conteudo = fread($handle, filesize($filename));
            }
            fclose($handle);
        }
        return $conteudo;
    }
    
}