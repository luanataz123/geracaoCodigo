<?php
defined('_IS_VALIDATION_') or die('Acesso não permitido.');

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
     * Lê o diretório informado, criando um array com todos os arquivos que devem fazer parte do
     * ZIP final
     * 
     * @param string $diretorio: caminho completo do diretório, incluindo o $this->getConfiguration()->pathModule
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
                            /*Se for um diretório:*/
                            /*se começar com &, é um diretório de configuração, não deve ser adicionado ao ZIP final, somente ignorar*/
                            /*se começar com #, deverão ser criadas várias instâncias do diretório através de loop, por isso encaminha para a função setArrArquivosModeloLoop*/
                            /*se não começar com nenhuma das palavras reservadas acima, deve-se entrar no diretório e chamar a própria função setArrArquivosModelo de forma recursiva, até que se leia todos os arquivos dos diretórios e subdiretórios da pasta informada*/
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
     * Lê o diretório informado, criando um array com todos os arquivos que devem ser gerados no ZIP
     * final através de loop em um array
     *
     * @param string $diretorio: caminho completo do diretório, incluindo o $this->getConfiguration()->pathModule
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
                            /*Se for um diretório:*/
                            /*se começar com &, é um diretório de configuração, não deve ser adicionado ao ZIP final, somente ignorar*/
                            /*se não começar com &, deve-se entrar no diretório e chamar a própria função setArrArquivosModeloLoop de forma recursiva, até que se leia todos os arquivos dos diretórios e subdiretórios da pasta informada*/
                            if ($file[0] != '&'){
                                $this->setArrArquivosModeloLoop($diretorio . '/' . $file);
                            }
                        }
                    }
                }
            }
        }
    }
    
    /*Faz as transformações necessárias nos parâmetros informados pelo usuário e popula as 
     * variáveis que serão utilizadas nos modelos de aplicação.
     * 
     * Deve-se sempre verificar a existência do parâmetro com a função array_key_exists('nomeParametro', $parametros)
     * antes de utilizá-lo, pois como essa função pode ser compartilhada por todos os modelos de 
     * geração de código, pode ser que o parâmetro não exista em algumas situações
     * 
     * Na medida do possível, o nome da variável deve se assemelhar ao conteúdo
     * em termos de maiúsculas, minúsculas e hífen
     * 
     * O array &variaveis guarda as palavras-chave que serão sempre iguais para toda a aplicação,
     * por exemplo: @nmAplicacao@
     * 
     * O array $variaveisLoop guarda as palavras-chave que devem variar de acordo com um array,
     * por exemplo: %NM_COLUNA%, na hora de criar um grid, deverá haver um loop criando uma <td>
     * para cada coluna da tabela, por isso criamos um array dentro de $variaveisLoop com o nome 
     * 'COLUNA' que deverá conter um item para cada coluna da tabela, no formato 
     * $variaveisLoop['COLUNA'][$i]['%NM_COLUNA%']
     * 
     * Por convenção, para facilitar a compreensão dos modelos de aplicação:
     * Variáveis que ficam dentro do array $variaveis devem começar e terminar com o símbolo @
     * Variáveis que ficam dentro do array $variaveisLoop devem começar e terminar com o símbolo %
     * */
    protected function popularVariaveis($parametros){
        
        $this->setVariavel('@VALIDACAO_PERIODO@', '');
        
        /*Coluna que será usada para ordenação inicial da tabela*/
        if (array_key_exists('colunaSort', $parametros)){
            $COLUNA_SORT = $parametros['colunaSort'];
            
            $this->setVariavel('@COLUNA_SORT@', $COLUNA_SORT);
            $this->setVariavel('@coluna_sort@', strtolower($COLUNA_SORT));
        }
        
        /*Coluna que será usada para pesquisa textual no grid*/
        if (array_key_exists('colunaPesquisa', $parametros)){
            $this->setVariavel('@COLUNA_PESQUISA@', $parametros['colunaPesquisa']);
        }
        
        /*Coluna que é a chave primária da tabela*/
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
            
            /*o array 'TABELA' deverá conter todas as tabelas necessárias para a query principal,
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
        
        /*a aplicação sempre terá um menu principal, com os nomes dos perfis de usuários
         * e para cada perfil, terá um submenu com as telas que aquele
         * perfil pode acessar, portanto a aba com o módulo será uma tab secundária*/
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
                /*o array 'PERFIL' contém todos os perfis que podem acessar o módulo*/
                $this->setVariavelLoop('PERFIL', array('%perfilAtual%'=> 'visao'.$arrTitulo['DsTexto'], '%PerfilAtual%'=> 'Visao'.$arrTitulo['DsTexto'], '%tab-primaria%'=> 'visao-'.$arrTitulo['dsTextoHifen']));
            }
        }
        
        
        if (array_key_exists('ST_GERAR_ITEM_PERFIL', $parametros) && $parametros['ST_GERAR_ITEM_PERFIL']){
            $arrTituloPerfilCriar = explode(',', $parametros['GERAR_NM_ITEM_PERFIS']);
            
            foreach($arrTituloPerfilCriar as $tituloPerfil){
                $tituloPerfil = trim($tituloPerfil);
                $arrTitulo = $this->transformarTextoAcentuado($tituloPerfil);
                /*o array 'PERFIL_CRIAR' contém os itens de menu principal (perfis de usuário)
                 * que ainda não existem na aplicação, portanto deve ser gerado o código
                 * para sua criação*/
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
                
                /*o array 'SELECT_COLUNA' contém todas as colunas que devem estar na cláusula
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
                    
                    /*o array 'SEARCH_REMOTO' contém todas as colunas cujo input no form será um 
                     * search que busca os dados remotamente, de acordo com a string de busca
                     * fornecida pelo usuário
                     * 
                     * o array 'SEARCH_LOCAL' contém todas as colunas cujo input no form será um
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
                    
                    /*por padrão, inputs do tipo Checkbox serão apresentadas no grid com o 
                     * valor Sim, quando estiverem marcadas e Não quando estiverem desmarcadas,
                     * o usuário poderá alterar isso manualmente, após a geração do código, caso deseje*/
                    foreach($arrDados as $key=>$value){
                        if ($value == 'CHECKED'){
                            $ds = 'Sim';
                        } else if($value == 'UNCHECKED'){
                            $ds = 'Não';
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
                    /*o array 'COLUNA_INT' contém as colunas que são numéricas e que a
                     * sua apresentação no grid será esse número mesmo, não uma descrição associada,
                     * dessa forma, para fins de ordenação, deverá ser tratada como número*/
                    $this->setVariavelLoop('COLUNA_INT', array('%NM_COLUNA%'=>$coluna['NM_COLUNA']));
                }
                
                if($coluna['DS_TIPO_INPUT'] == 'number'){
                    /*o array 'INPUT_NUMBER' contém as colunas que terão um input do tipo number*/
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
                
                /*o array 'COLUNA' contém todas as colunas da tabela*/
                $this->setVariavelLoop('COLUNA', array('%NM_COLUNA%'=>$coluna['NM_COLUNA'], '%DS_TIPO%'=>$coluna['DS_TIPO'], '%VALOR_NM_COLUNA_INSERT%'=> $VALOR_NM_COLUNA_INSERT, '%VALOR_NM_COLUNA_UPDATE%' => $VALOR_NM_COLUNA_UPDATE, '%NmColuna%' => $arrNmColuna['DsTexto'], '%nmColuna%' => $arrNmColuna['dsTexto'], '%COLUNA%'=>$arrNmColuna['TEXTO'], '%DS_LABEL%' => $coluna['DS_LABEL'], '%DS_GRID%' => $dsGrid, '%DS_TIPO_INPUT%' => $coluna['DS_TIPO_INPUT'], '%ID_FK%' => $ID_FK, '%DS_FK%' => $DS_FK, '%nmTabelaFK%' => $nmTabelaFK, '%NmTabelaFK%' => $NmTabelaFK, '%required%' => $required, '%QT_TAMANHO%'=> $coluna['QT_TAMANHO'], '%htmlOpcoes%'=> $htmlOpcoes, '%NG_MIN_MAX_PERIODO%' => '', '%ERROR_MIN_MAX_PERIODO%' => '', '%orderBy%'=>$orderBy, '%valorInicial%'=>$valorInicial));
                
                /*o array 'COLUNA_FORM' contém todas as colunas que devem estar no form de cadastro/edição */
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
            
            /*configura validações no front end e no back end para quando houver períodos de data na tabela*/
            if ($COLUNA_DT_INI && $COLUNA_DT_FIM){
                $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NG_MIN_MAX_PERIODO%'] = 'ng-max="@nmTabela@.' . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . '"';
                $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NG_MIN_MAX_PERIODO%'] = 'ng-min="@nmTabela@.' . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . '"';

                $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%ERROR_MIN_MAX_PERIODO%'] = '<p class="help-block" role="alert" ng-if="cadastro@NmTabela@.' . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . '.$error.max">
                <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>'.$this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%DS_LABEL%'] .' deve ser menor que '.$this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%DS_LABEL%'] .'
                </p>';
                $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%ERROR_MIN_MAX_PERIODO%'] = '<p class="help-block" role="alert" ng-if="cadastro@NmTabela@.' . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . '.$error.min">
                <i class="fa fa-exclamation-triangle fa-lg" aria-hidden="true"></i>'.$this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%DS_LABEL%'] .' deve ser maior que '.$this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%DS_LABEL%'] .'
                </p>';
                
                $validacaoPeriodo = "        /* Validação de período*/


        \$bind['" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . "'] = substr(\$this->params['" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . "'], 0, 10);
        \$bind['" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . "'] = substr(\$this->params['" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . "'], 0, 10);
/*        
        if (!empty(\$this->params['{$COLUNA_ID}'])) {
            \$and .= ' AND {$arrAlias[0]}.{$COLUNA_ID} <> :{$COLUNA_ID} ';
            \$bind['{$COLUNA_ID}'] = (int) \$this->params['{$COLUNA_ID}'];
        }
        
        //ATENÇÃO: Adicionar cláusula AND com a coluna que identifica de quem é o período
        \$sql = \" SELECT distinct 1 as st_existe
                   FROM {$tabela['NM_ESQUEMA']}.{$tabela['NM_TABELA']} {$arrAlias[0]}
                  WHERE 1 = 1  {\$and}
                    AND {$arrAlias[0]}." . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . " <= TO_DATE(:" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . ", 'YYYY-MM-DD')
                    AND {$arrAlias[0]}." . $this->variaveisLoop['COLUNA'][$COLUNA_DT_FIM]['%NM_COLUNA%'] . " >= TO_DATE(:" . $this->variaveisLoop['COLUNA'][$COLUNA_DT_INI]['%NM_COLUNA%'] . ", 'YYYY-MM-DD')\";
        
        \$retorno = \$this->pageExecuteSelect(\$sql, \$bind);
        
        
        if (\$retorno['total'] > 0){
            \$msg .= 'O período informado se sobrepõe a outro período já cadastrado!<br>';
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
    
    /*recebe como parâmetro uma string com todas as letras maiúsculas, com as palavras 
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
    
    /*recebe como parâmetro uma string em texto normal, com as palavras acentuadas e
     * separadas por espaços, e retorna um array com essa string em diversos formatos
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
     * Verifica se já foi gerado subconteúdo para o arquivo em modelo anterior e, caso
     * o novo conteúdo esteja programado para receber esse subconteúdo através da expressão
     * FRAGMENTO:= , inclui o subconteúdo no conteúdo final do arquivo.
     * 
     * @param string $caminho : caminho completo para o arquivo a ser gerado no ZIP, desde o diretório pai até a sua extensão
     * @param string $conteudo : conteúdo que deverá ser inserido no arquivo, antes da inclusão do fragmento
     * @param ZipArchive $zip : arquivo ZIP que está sendo gerado com o código da aplicação
     * @return string : conteúdo que deverá ser inserido no arquivo, com o fragmento já incluído
     */
    private function concatenarConteudoArquivo($caminho, $conteudo, $zip){
        /* a expressão FRAGMENTO:=, que indica onde deve ser inserido o subconteúdo,
         * deverá estar dentro de um comentário, como em arquivos com extensão .html
         * a forma de indicar comentário é diferente das demais extensões, o código
         * abaixo faz a configuração de qual tipo de comentário deve ser utilizado */
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
        
        /*verifica se existe previsão no conteúdo do arquivo, de lugar para inclusão
         * de subconteúdo proveniente de modelo anterior*/
        if (!(strpos($conteudo, $iniComentario.'FRAGMENTO:=') === false)){
            $zip->close();
            $res = $zip->open($this->getNomeArquivoZip());
            $conteudoAnterior = $zip->getFromName($caminho);
            $zip->close();
            $zip->open($this->getNomeArquivoZip(),  ZipArchive::CREATE);
    
            if ($conteudoAnterior){
                /*busca no conteúdo do arquivo de modelo anterior, qual(is) fragmento(s) deverá(ão) ser 
                 * copiado(s) para o conteúdo final, os fragmentos são identificados pela palavra
                 * FRAGMENTO, dentro de um comentário, prosseguida pelo código BEGIN(identificadorFragmento)
                 * dentro de outro comentário. O trecho a ser copiado vai do término dessa expressão
                 * até a próxima ocorrência da palavra FRAGMENTO dentro de um comentário*/
                $arrFragmentos = explode($iniComentario.'FRAGMENTO'.$fimComentario, $conteudoAnterior);
                foreach($arrFragmentos as $fragmento){
                    if (substr($fragmento, 0, $tamanhoIniFragmento) == $iniFragmento){
                        $posicaoFimNomeFragmento = strpos($fragmento, $fimFragmento);
                        $nomeFragmento = substr($fragmento, $tamanhoIniFragmento, $posicaoFimNomeFragmento-$tamanhoIniFragmento);
                        /*substituir o conteúdo do fragmento no local do conteúdo do novo arquivo onde houver
                         * a expressão FRAGMENTO:=identificadorFragmento, dentro de um comentário*/
                        $conteudo = str_replace($iniComentario."FRAGMENTO:={$nomeFragmento}".$fimComentario, substr($fragmento, $posicaoFimNomeFragmento+$tamanhoFimFragmento), $conteudo);
                    }
                }
            }
        }            
        return $conteudo;
    }
    
    
    /**
     * Faz a leitura de todos os arquivos da pasta raiz informada, fazendo a substituição das
     * palavras-chave e gerando um arquivo ZIP com o código da aplicação.
     * 
     * @param string $pastaRaiz :pasta raiz com os modelos de arquivos a serem gerados, o caminho da pasta
     * deve começar após o endereço do módulo da aplicação geracaoCodigo que está chamando a função,
     * 
     * Exemplo: para a pasta:
     *  geracaoCodigo/modules/geradorCrud/gerarModulo
     * o parâmetro seria:
     *  $pastaRaiz = gerarModulo
     * 
     * @param ZipArchive $zip : arquivo ZIP onde deverão ser incluídos os arquivos com o código da aplicação
     */
    protected function gerarArquivosModelo($pastaRaiz, $zip){
        
        $this->resetArrArquivos();
        $diretorio = $this->getConfiguration()->pathModule . $pastaRaiz;
        /*cria um array com todos os arquivos que deverão ser gerados no ZIP, com base na
         * leitura dos arquivos da pasta modelo*/
        $this->setArrArquivosModelo($diretorio);
        
        /*Geração dos arquivos que deverão ser criados diretamente pela substituição das
         * palavras-chave nos modelos*/
        foreach($this->arrArquivosModelo as $arrArquivo){
            /*faz a substituição das palavras-chave dentro do conteúdo dos arquivos 
             * pelos dados corretos, conforme parâmetros informados pelo usuário*/
            $arquivo = $this->substituirVariaveis($arrArquivo['pasta'].'/', $arrArquivo['nmArquivo'], $arrArquivo['extensao']);
            $nmArquivoDestino = $arrArquivo['nmArquivo'];
            $pastaDestino = str_replace($pastaRaiz.'/', '', $arrArquivo['pasta']);
            
            /*faz a substituição das palavras-chave no nomes dos arquivos e pastas pelos dados
             * corretos, conforme parâmetros informados pelo usuário*/
            foreach($this->getVariaveis() as $variavel=>$valor){
                $nmArquivoDestino = str_replace($variavel, $valor, $nmArquivoDestino);
                $pastaDestino = str_replace($variavel, $valor, $pastaDestino);
            }
            
            $caminhoCompleto = "/{$pastaDestino}/$nmArquivoDestino".$arrArquivo['extensao'];
            /*caso haja mais de um modelo para o mesmo arquivo, faz a concatenação conforme
             * configuração nos modelos*/
            $arquivo = $this->concatenarConteudoArquivo($caminhoCompleto, $arquivo, $zip);
            
            //echo "<pre>"; print_r($arquivo); echo "</pre>"; die;
            
            /*adiciona o arquivo já processado ao ZIP final*/
            $zip->addFromString($caminhoCompleto, $arquivo);
        }
        
        /*Geração dos arquivos que deverão ser criados através de loop em um array*/
        foreach($this->arrArquivosModeloLoop as $arrArquivo){
            /*dentro do caminho completo de cada arquivo, busca a pasta que começa com o
             * símbolo #, indicando que ela e todo o seu subconteúdo deverá ser gerado
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
                        /*o array a ser usado para o loop será o que estiver escrito após o símbolo -
                         * no nome da pasta que começa com # */
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
                    /*faz a substituição das palavras-chave do array $variaveisLoop no conteúdo 
                     * do arquivo, no nome  do arquivo e nos nomes das pastas pelos dados 
                     * corretos, conforme parâmetros informados pelo usuário. */
                    foreach($item as $variavel=>$valor){
                        $subConteudo = str_replace($variavel, $valor, $subConteudo);
                        $pastaDestino = str_replace($variavel, $valor, $pastaDestino);
                        $nmArquivoDestino = str_replace($variavel, $valor, $nmArquivoDestino);
                    }
                    $pastaDestino = str_replace($pastaRaiz.'/', '', $pastaDestino);
                    
                    /*faz a substituição das palavras-chave do array $variaveis no conteúdo
                     * do arquivo, no nome  do arquivo e nos nomes das pastas pelos dados
                     * corretos, conforme parâmetros informados pelo usuário. */
                    foreach($this->getVariaveis() as $variavel=>$valor){
                        $subConteudo = str_replace($variavel, $valor, $subConteudo);
                        $nmArquivoDestino = str_replace($variavel, $valor, $nmArquivoDestino);
                        $pastaDestino = str_replace($variavel, $valor, $pastaDestino);
                    }
                    
                    $caminhoCompleto = "/{$pastaDestino}/$nmArquivoDestino".$arrArquivo['extensao'];
                    
                    /*caso haja mais de um modelo para o mesmo arquivo, faz a concatenação conforme
                     * configuração nos modelos*/
                    $subConteudo = $this->concatenarConteudoArquivo($caminhoCompleto, $subConteudo, $zip);

                    //echo "<pre>"; print_r($subConteudo); echo "</pre>"; die;
                    
                    /*adiciona o arquivo já processado ao ZIP final*/
                    $zip->addFromString($caminhoCompleto, $subConteudo);
                }
            }
        }
        
    }
    
    
    /**
     * Faz a leitura do conteúdo do arquivo modelo e substitui pelos dados corretos, conforme
     * parâmetros informados pelo usuário
     * 
     * @param string $pasta : caminho da pasta com o modelo do arquivo a ser processado, o caminho da pasta deve começar logo após
     *      o caminho do módulo da aplicação geracaoCodigo onde ela está 
     * @param string $nmArquivo : nome do arquivo modelo a ser processado
     * @param string $extensao :  extensao do arquivo modelo a ser processado
     * 
     * Exemplo: para o arquivo:
     *  geracaoCodigo/modules/geradorCrud/gerarModulo/horusnet/application/@nmAplicacao@/modules/index/Controller.php
     * os parâmetros seriam:
     *  $pasta = gerarModulo/horusnet/application/@nmAplicacao@/modules/index/
     *  $nmArquivo = Controller
     *  $extensao = .php
     *  
     * @return mixed|string : conteúdo do arquivo, após o processamento para substituição das variáveis
     */
    private function substituirVariaveis($pasta, $nmArquivo, $extensao){
        $conteudo = $this->getConteudoArquivo($pasta . $nmArquivo . $extensao);
        
        /*verifica se na mesma pasta existe uma subpasta com o mesmo nome do arquivo, começando 
         * com o símbolo &, o que significa que é uma pasta com configurações para o arquivo*/
        $diretorio = $this->getConfiguration()->pathModule . $pasta . '&'. $nmArquivo . $extensao;
        
        if(file_exists($diretorio)){
            
            /*percorre a pasta de configurações do arquivo*/
            if ($handle = opendir($diretorio)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && !is_dir($diretorio . '/' . $file)) {
                        /*os arquivos desta pasta, que não sejam subpastas, terão o formato
                         * nomeTrecho-separador-NOME_ARRAY.txt, sendo que NOME_ARRAY representa o array
                         * $variaveisLoop['NOME_ARRAY'] que deverá ser percorrido para geração
                         * do trecho de código*/
                        $conteudoParcial = '';
                        $arrParametros = explode('-', $file);
                        $arrParametros[2] = explode('.', $arrParametros[2]);
                        $arrParametros[2] = $arrParametros[2][0];
                        /*o símbolo || não é permitido no nome do arquivo, por isso deverá ser usada a palavra OR em seu lugar*/
                        $arrParametros[1] = str_replace('OR', '||', $arrParametros[1]);
                        $subConteudoFixo = $this->getConteudoArquivo($pasta . '&'. $nmArquivo. $extensao . '/' .$file);
                        $variavelLoop = $this->getVariaveisLoop();
                        if (array_key_exists($arrParametros[2], $variavelLoop)){
                            /*percorre o array $variaveisLoop['NOME_ARRAY'] para geração do trecho de código*/
                            foreach($variavelLoop[$arrParametros[2]] as $item){
                                /*se existir uma subpasta dentro da pasta de configuração do arquivo, com o nome nomeTrecho,
                                 * utiliza o conteúdo do arquivo &pastaConfiguracao/nomeTrecho-separador-NOME_ARRAY.txt como
                                 * chave no array $variaveisLoop['NOME_ARRAY'][$i][chave] para saber qual modelo de arquivo 
                                 * dentro da subpasta nomeTrecho deverá ser utilizado, 
                                 * por exemplo: se $variaveisLoop['NOME_ARRAY'][$i][chave] == CHAR, utilizar o 
                                 * trecho de código do modelo &pastaConfiguracao/nomeTrecho/CHAR.txt */
                                $subDiretorio = $this->getConfiguration()->pathModule . $pasta .'&' .$nmArquivo. $extensao . '/' . $arrParametros[0];
                                
                                if(file_exists($subDiretorio)){
                                    $subConteudo = $this->getConteudoArquivo($pasta . '&' . $nmArquivo. $extensao . '/' . $arrParametros[0] . '/'. $item[$subConteudoFixo] . '.txt');
                                } else {
                                    /*se não existir a subpasta &pastaConfiguracao/nomeTrecho,
                                     * utilizar o trecho de código do modelo &pastaConfiguracao/nomeTrecho-separador-NOME_ARRAY.txt */
                                    $subConteudo = $subConteudoFixo;
                                }
                                
                                /*substitui as variáveis do array $variaveisLoop['NOME_ARRAY'][$i]
                                 * no trecho de código obtido acima*/
                                foreach($item as $variavel=>$valor){
                                    $subConteudo = str_replace($variavel, $valor, $subConteudo);
                                }
                                
                                /*concatenar os trechos de código de cada item $i do array 
                                 * $variaveisLoop['NOME_ARRAY'], utilizando entre eles o separador 
                                 * definido no nome do arquivo &pastaConfiguracao/nomeTrecho-separador-NOME_ARRAY.txt */
                                if ($conteudoParcial){
                                    $conteudoParcial .= ' '.$arrParametros[1] . ' ';
                                }
                                $conteudoParcial .= $subConteudo;
                            }
                        }
                        
                        /*o resultado com a concatenação de todos os trechos de código acima deverá
                         * ser substituído no modelo de arquivo principal, no local configurado
                         * como #nomeTrecho# */
                        $conteudo = str_replace('#'.$arrParametros[0].'#', $conteudoParcial, $conteudo);
                    }
                }
                closedir($handle);
            }
        }
        
        /*substituir as palavras-chave do array $variaveis no conteúdo do arquivo modelo*/
        foreach($this->getVariaveis() as $variavel=>$valor){
            $conteudo = str_replace($variavel, $valor, $conteudo);
        }
        
        return $conteudo;
    }
    
    
    /**
     * Retorna uma string com o conteúdo do arquivo solicitado
     * 
     * @param string $caminhoArquivo : caminho do arquivo, começando a partir do endereço do módulo
     * da aplicação geracaoCodigo que está chamando a função
     * 
     * Exemplo: para o arquivo:
     *  geracaoCodigo/modules/geradorCrud/gerarModulo/horusnet/application/@nmAplicacao@/modules/index/Controller.php
     * o parâmetro seria:
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