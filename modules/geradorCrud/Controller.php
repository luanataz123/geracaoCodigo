<?php

defined('_IS_VALIDATION_') or die('Acesso não permitido.');

final class Controller extends ControllerApplication {
    
    public function __construct($config) {
        parent::__construct($config);
        parent::verificarSessao();
        
        parent::init();
    }
    
    protected function index() {}
    
    /*Função utilizada para pegar a string com as informações da tabela informada pelo usuário
     * e transformar em um grid*/
    protected function carregarTabela() {
        $arrayParams = $this->verificarParamsObrigatorios(
            array(
                'DS_TABELA'
            )
            );
        
        if (!empty($arrayParams)) {
            $json['success'] = false;
            $json['msg'] = utf8_encode("Faltam parâmetros para realização desta operação.");
            print json_encode($json); die;
        }
        
        /*Transforma a string em um array unidimensional, usando as quebras de linha
         * para separar os elementos*/
        $arrColunas = preg_split('/\r\n|\r|\n/',  $this->params['DS_TABELA']);
        
        /*Para cada elemento do array, cria um novo array, usando o símbolo ;
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
    
    /*Função acessada via POST, salva os parâmetros na sessão e retorna o link codificado para
     * a função gerarCrudZip, para que ela possa ser acessada via GET, sem que todos os parâmetros
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
            $json['msg'] = utf8_encode("Faltam parâmetros para realização desta operação.");
            print json_encode($json); die;
        }
        
        
        $json['success'] = true;
        $_SESSION['ParametrosCrud']= $this->params;
        $json['url'] = utf8_encode('?p=' . $this->view->encodeUrl(PATH_URL_APPLICATION . 'module=geradorCrud&action=gerarCrudZip&task=init'));
        print json_encode($json);
        die;
    }
    
    /*Função acessada via GET, utiliza os parâmetros que foram colocados na sessão pela função
     * gerarCrud*/
    protected function gerarCrudZip() {
        $parametros = $_SESSION['ParametrosCrud'];
        
        /*faz as transformações necessárias nos parâmetros informados pelo usuário, para 
         * que possam ser usados como palavras-chave pelo modelo da aplicação*/
        $this->popularVariaveis($parametros);
        unset($_SESSION['ParametrosCrud']);
        
        $zip = new ZipArchive;
        $date = new DateTime();
        $this->setNomeArquivoZip('crud'.$date->format('YmdHis').'.zip');
        if ($zip->open($this->getNomeArquivoZip(),  ZipArchive::CREATE)) {
            
            /*gera o módulo do CRUD*/
            $this->gerarArquivosModelo('gerarModulo', $zip);
            
            /*caso o usuário tenha marcado essa opção, gera o menu para o novo perfil*/
            if ($parametros['ST_GERAR_ITEM_PERFIL']){
                $this->gerarArquivosModelo('gerarItemPerfil', $zip);
            }

            /*caso o usuário tenha marcado essa opção, gera o código completo da aplicação*/
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