<?php

defined('_IS_VALIDATION_') or die('Acesso n�o permitido.');

final class Controller extends ControllerApplication {
    
    public function __construct($config) {
        parent::__construct($config);
        parent::verificarSessao();
        
        parent::init();
    }
    
    protected function index() {}
    
    /*Fun��o utilizada para pegar a string com as informa��es da tabela informada pelo usu�rio
     * e transformar em um grid*/
    protected function carregarTabela() {
        $arrayParams = $this->verificarParamsObrigatorios(
            array(
                'DS_TABELA'
            )
            );
        
        if (!empty($arrayParams)) {
            $json['success'] = false;
            $json['msg'] = utf8_encode("Faltam par�metros para realiza��o desta opera��o.");
            print json_encode($json); die;
        }
        
        /*Transforma a string em um array unidimensional, usando as quebras de linha
         * para separar os elementos*/
        $arrColunas = preg_split('/\r\n|\r|\n/',  $this->params['DS_TABELA']);
        
        /*Para cada elemento do array, cria um novo array, usando o s�mbolo ;
         * para separar os elementos*/
        foreach ($arrColunas as &$line) {
            $line = explode(';', $line);
            foreach($line as &$item){
                $item = trim($item);
            }
        }
        
        /*Transforma o array em array associativo, usando os nomes do primeiro array abaixo como chaves*/
        foreach ($arrColunas as &$row){
            $row = array_combine(array('NM_ESQUEMA', 'NM_TABELA', 'NM_COLUNA', 'DS_TIPO', 'QT_TAMANHO', 'ST_NULLABLE'), $row);
            $row['TABELA_FK_DISABLED']=false;
        }
        print json_encode(array('dados' => $arrColunas, 'total'=>count($arrColunas)));
        die;
        
    }
    
    /*Fun��o acessada via POST, salva os par�metros na sess�o e retorna o link codificado para
     * a fun��o gerarCrudZip, para que ela possa ser acessada via GET, sem que todos os par�metros
     * precisem estar na URL*/
    protected function gerarCrud() {
        $arrayParams = $this->verificarParamsObrigatorios(
            array(
                'colunas',
                'NM_APLICACAO',
                'NM_PERFIS',
                'colunaSort',
                'colunaPK',
                'NM_ITEM_MENU',
                'colunaPesquisa',
                'ST_GERAR_ITEM_PERFIL',
                'ST_GERAR_APLICACAO'
            )
            );
        
        if (!empty($arrayParams)) {
            $json['success'] = false;
            $json['msg'] = utf8_encode("Faltam par�metros para realiza��o desta opera��o.");
            print json_encode($json); die;
        }
        
        
        $json['success'] = true;
        $_SESSION['ParametrosCrud']= $this->params;
        $json['url'] = utf8_encode('?p=' . $this->view->encodeUrl(PATH_URL_APPLICATION . 'module=geradorCrud&action=gerarCrudZip&task=init'));
        print json_encode($json);
        die;
    }
    
    /*Fun��o acessada via GET, utiliza os par�metros que foram colocados na sess�o pela fun��o
     * gerarCrud*/
    protected function gerarCrudZip() {
        $parametros = $_SESSION['ParametrosCrud'];
        
        /*faz as transforma��es necess�rias nos par�metros informados pelo usu�rio, para 
         * que possam ser usados como palavras-chave pelo modelo da aplica��o*/
        $this->popularVariaveis($parametros);
        unset($_SESSION['ParametrosCrud']);
        
        $zip = new ZipArchive;
        $date = new DateTime();
        $this->setNomeArquivoZip('crud'.$date->format('YmdHis').'.zip');
        if ($zip->open($this->getNomeArquivoZip(),  ZipArchive::CREATE)) {
            
            /*gera o m�dulo do CRUD*/
            $this->gerarArquivosModelo('gerarModulo', $zip);
            
            /*caso o usu�rio tenha marcado essa op��o, gera o menu para o novo perfil*/
            if ($parametros['ST_GERAR_ITEM_PERFIL']){
                $this->gerarArquivosModelo('gerarItemPerfil', $zip);
            }

            /*caso o usu�rio tenha marcado essa op��o, gera o c�digo completo da aplica��o*/
            if ($parametros['ST_GERAR_APLICACAO']){
                $this->gerarArquivosModelo('gerarAplicacao', $zip);
            }

            //die;
            $zip->close();
            header("Content-disposition: attachment; filename={$this->getNomeArquivoZip()}");
            header('Content-type: application/zip');
            readfile($this->getNomeArquivoZip());
        } else {
            echo 'Erro ao gerar arquivo .zip!!';
        }
        
        die;
        
    }
    
    

    
}