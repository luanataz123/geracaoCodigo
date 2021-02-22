� poss�vel criar outros modelos de aplica��o, usando a estrutura existente do gerador de c�digo.

Para isso, se for outro modelo com interface em AngularJS, crie outra aba dentro do menu "Interface em AngularJS",
caso contr�rio, crie um novo menu com uma aba para o seu modelo dentro de um submenu.

A popula��o das vari�veis que podem ser usadas no modelo est� no arquivo classes/ControllerApplication.php, 
dessa forma, n�o � necess�rio criar outra fun��o para a popula��o de vari�veis comuns em qualquer modelo.

Caso necess�rio, crie novas vari�veis com outros nomes e fa�a a popula��o delas no ControllerApplication.php,
para que outros modelos tb possam se beneficiar posteriormente.

Para a gera��o do modelo, pode-se seguir o exemplo do m�dulo geradorCrud, de acordo com as seguintes regras:

1. Criar uma pasta para cada escopo que se permite gerar. 
	No exemplo do geradorCrud � poss�vel gerar apenas um m�dulo dentro de uma aplica��o com menu j� existentes,
	para este escopo, o modelo est� na pasta gerarModulo.
	
	Tamb�m � poss�vel gerar todo o menu e mais o m�dulo para uma aplica��o j� existente, para este escopo,
	o modelo do menu est� em gerarItemPerfil e o modelo do m�dulo est� em gerarModulo.
	
	A �ltima possibilidade � gerar a aplica��o inteira com um menu e um m�dulo, para este escopo,
	o modelo da aplica��o est� em gerarAplicacao, o modelo do menu est� em gerarItemPerfil e o modelo do
	m�dulo est� em gerarModulo.
	
	Dentro do arquivo geradorCrud/Controller.php, a function gerarCrudZip coordena a gera��o do c�digo
	a partir dos modelos, chamando a fun��o gerarArquivosModelo, primeiro para a pasta gerarModulo e 
	depois, se o usu�rio tiver marcado essas op��es, para a pasta gerarItemPerfil e gerarAplicacao.
	
	Desta forma, se houver arquivos com o mesmo nome dentro dos diferentes modelos 
	(gerarModulo, gerarItemPerfil e gerarAplicacao), o arquivo que for criado por �ltimo sobrescrever�
	os arquivos anteriores. 
	
	Caso o desenvolvedor deseje um comportamento diferente do descrito acima, com fragmentos do c�digo 
	do escopo menor sendo incorporados ao c�digo do escopo maior em um mesmo arquivo, deve-se programar
	da seguinte forma:
		- no arquivo com escopo menor (exemplo: gerarModulo\horusnet\application\@nmAplicacao@\modules\index\Controller.php):
			No in�cio do trecho que dever� ser incorporado, digitar: 
				/*FRAGMENTO*//*BEGIN(meuIdentificador)*/
			onde a palavra meuIdentificador dever� ser substitu�da pelo nome desejado, o mesmo identificador 
			dever� ser usado nos marcadores abaixo.
			
			No t�rmino do trecho que dever� ser incorporado, digitar:
				/*FRAGMENTO*//*END(meuIdentificador)*/
			
			Somente o c�digo que estiver entre estes dois marcadores ser� incorporado ao arquivo final.
			
		- no arquivo com escopo maior (exemplo: gerarAplicacao\horusnet\application\@nmAplicacao@\modules\index\Controller.php):
			Na posi��o que voc� desejar inserir o trecho a ser incorporado, digitar:
				/*FRAGMENTO:=meuIdentificador*/
				
2. Dentro das pastas criadas acima, devem ser criados os arquivos a serem gerados, 
com os nomes que eles devem ter, podendo-se usar palavras-chave, caso o nome do arquivo dependa 
dos par�metros informados pelo usu�rio, seguem as regras para cria��o e utiliza��o de palavras-chave:

	- @identificador@: palavras-chave que s�o obtidas diretamente, sem necessidade de loop 
						Exemplo: @nmAplicacao@, @nmTabela@
						Essas vari�veis s�o as chaves do array $variaveis da classe ControllerApplication,
						na gera��o do c�digo, o sistema ir� substitu�-las pelos valores associados no mesmo array.

	- #%identificador%-NOME_ARRAY: o s�mbolo # no nome de uma pasta, indica que haver� um loop criando
									uma pasta com todas os seus arquivos e subpastas para cada item de
									um array.
									Exemplo: #%perfilAtual%-PERFIL 
									Este array fica no atributo $variaveisLoop['NOME_ARRAY'] da classe 
									ControllerApplication. o nome de cada pasta ser� o que estiver entre
									os caracteres # e -, sendo que a palavra-chave %identificador% ser� substitu�da
									pelo atributo $variaveisLoop['NOME_ARRAY'][$i]['%identificador%'];
									
	- &NomeArquivo: o s�mbolo & no nome de uma pasta, significa que esta � uma pasta de configura��es
					para gera��o do arquivo NomeArquivo, ou seja, ela n�o estar� na estrutura de arquivos
					a ser gerada.
					Exemplo: &Controller.php (significa que esta � uma pasta com configura��es para o 
								arquivo Controller.php)
								
3. Dentro dos arquivos criados acima, devem ser criados os modelos para gera��o de seus c�digos.
	O sistema ir� copiar o conte�do do arquivo modelo, substituindo-se as palavras-chave de acordo
	com as seguintes regras:
	
	- @identificador@: palavras-chave que s�o obtidas diretamente, sem necessidade de loop 
						Exemplo: @nmAplicacao@, @nmTabela@
						Essas vari�veis s�o as chaves do array $variaveis da classe ControllerApplication,
						na gera��o do c�digo, o sistema ir� substitu�-las pelos valores associados no mesmo array.																 						
						
	- #identificador#: o s�mbolo # no in�cio e t�rmino de um identificador, indica que haver� um loop criando 
						um trecho de c�digo para cada item de um array.
						Exemplos: 
							#colunasForm# no arquivo gerarModulo\horusnet\application\@nmAplicacao@\modules\@nmTabela@\Controller.php,
								cuja pasta de configura��es � gerarModulo\horusnet\application\@nmAplicacao@\modules\@nmTabela@\&Controller.php
							#inputColuna# no arquivo gerarModulo\horusnet\application\@nmAplicacao@\modules\@nmTabela@\views\edit@NmTabela@.html,
								cuja pasta de configura��es � gerarModulo\horusnet\application\@nmAplicacao@\modules\@nmTabela@\views\&edit@NmTabela@.html
						O sistema ir� buscar dentro da pasta de configura��es do arquivo em quest�o,
						um arquivo com o nome identificador-separador-NOME_ARRAY, onde "separador" � a string que ser� colocada
						entre os trechos de c�digo (caso o separador deva ser ||, utilize o separador OR) e NOME_ARRAY � o nome do array que ser� usado
						 como base para o loop.
						Este array fica no atributo $variaveisLoop['NOME_ARRAY'] da classe ControllerApplication. 
						Existem duas op��es para gera��o do trecho de c�digo:
							- caso exista uma pasta com o nome "identificador" dentro da pasta de configura��es do arquivo,
							o sistema seguir� os seguintes passos:
								1- ir� buscar o conte�do do arquivo identificador-separador-NOME_ARRAY;
								2- palavras-chave dentro deste arquivo no formato %identificador%
									ser�o substitu�das pelo atributo $variaveisLoop['NOME_ARRAY'][$i]['%identificador%'];
								3- ir� buscar um arquivo dentro da pasta "identificador", cujo nome seja o resultado obtido
									no item 2, com a extens�o ".txt".
								4- ir� pegar o conte�do do arquivo obtido no item 3, sendo que palavras-chave dentro deste arquivo 
									no formato %identificador% ser�o substitu�das pelo atributo 
									$variaveisLoop['NOME_ARRAY'][$i]['%identificador%'], e este ser� o trecho de c�digo;		 
							
							- caso n�o exista uma pasta com o nome "identificador", o trecho de c�digo ser� o
						conte�do do arquivo identificador-separador-NOME_ARRAY, sendo que palavras-chave dentro deste arquivo no formato %identificador%
						ser�o substitu�das pelo atributo $variaveisLoop['NOME_ARRAY'][$i]['%identificador%'];

	- %identificador%: sempre representa um atributo obtido atrav�s de loop em um array, para saber
						de qual array, leia o item acima, #identificador#.
												