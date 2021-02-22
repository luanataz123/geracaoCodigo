É possível criar outros modelos de aplicação, usando a estrutura existente do gerador de código.

Para isso, se for outro modelo com interface em AngularJS, crie outra aba dentro do menu "Interface em AngularJS",
caso contrário, crie um novo menu com uma aba para o seu modelo dentro de um submenu.

A população das variáveis que podem ser usadas no modelo está no arquivo classes/ControllerApplication.php, 
dessa forma, não é necessário criar outra função para a população de variáveis comuns em qualquer modelo.

Caso necessário, crie novas variáveis com outros nomes e faça a população delas no ControllerApplication.php,
para que outros modelos tb possam se beneficiar posteriormente.

Para a geração do modelo, pode-se seguir o exemplo do módulo geradorCrud, de acordo com as seguintes regras:

1. Criar uma pasta para cada escopo que se permite gerar. 
	No exemplo do geradorCrud é possível gerar apenas um módulo dentro de uma aplicação com menu já existentes,
	para este escopo, o modelo está na pasta gerarModulo.
	
	Também é possível gerar todo o menu e mais o módulo para uma aplicação já existente, para este escopo,
	o modelo do menu está em gerarItemPerfil e o modelo do módulo está em gerarModulo.
	
	A última possibilidade é gerar a aplicação inteira com um menu e um módulo, para este escopo,
	o modelo da aplicação está em gerarAplicacao, o modelo do menu está em gerarItemPerfil e o modelo do
	módulo está em gerarModulo.
	
	Dentro do arquivo geradorCrud/Controller.php, a function gerarCrudZip coordena a geração do código
	a partir dos modelos, chamando a função gerarArquivosModelo, primeiro para a pasta gerarModulo e 
	depois, se o usuário tiver marcado essas opções, para a pasta gerarItemPerfil e gerarAplicacao.
	
	Desta forma, se houver arquivos com o mesmo nome dentro dos diferentes modelos 
	(gerarModulo, gerarItemPerfil e gerarAplicacao), o arquivo que for criado por último sobrescreverá
	os arquivos anteriores. 
	
	Caso o desenvolvedor deseje um comportamento diferente do descrito acima, com fragmentos do código 
	do escopo menor sendo incorporados ao código do escopo maior em um mesmo arquivo, deve-se programar
	da seguinte forma:
		- no arquivo com escopo menor (exemplo: gerarModulo\horusnet\application\@nmAplicacao@\modules\index\Controller.php):
			No início do trecho que deverá ser incorporado, digitar: 
				/*FRAGMENTO*//*BEGIN(meuIdentificador)*/
			onde a palavra meuIdentificador deverá ser substituída pelo nome desejado, o mesmo identificador 
			deverá ser usado nos marcadores abaixo.
			
			No término do trecho que deverá ser incorporado, digitar:
				/*FRAGMENTO*//*END(meuIdentificador)*/
			
			Somente o código que estiver entre estes dois marcadores será incorporado ao arquivo final.
			
		- no arquivo com escopo maior (exemplo: gerarAplicacao\horusnet\application\@nmAplicacao@\modules\index\Controller.php):
			Na posição que você desejar inserir o trecho a ser incorporado, digitar:
				/*FRAGMENTO:=meuIdentificador*/
				
2. Dentro das pastas criadas acima, devem ser criados os arquivos a serem gerados, 
com os nomes que eles devem ter, podendo-se usar palavras-chave, caso o nome do arquivo dependa 
dos parâmetros informados pelo usuário, seguem as regras para criação e utilização de palavras-chave:

	- @identificador@: palavras-chave que são obtidas diretamente, sem necessidade de loop 
						Exemplo: @nmAplicacao@, @nmTabela@
						Essas variáveis são as chaves do array $variaveis da classe ControllerApplication,
						na geração do código, o sistema irá substituí-las pelos valores associados no mesmo array.

	- #%identificador%-NOME_ARRAY: o símbolo # no nome de uma pasta, indica que haverá um loop criando
									uma pasta com todas os seus arquivos e subpastas para cada item de
									um array.
									Exemplo: #%perfilAtual%-PERFIL 
									Este array fica no atributo $variaveisLoop['NOME_ARRAY'] da classe 
									ControllerApplication. o nome de cada pasta será o que estiver entre
									os caracteres # e -, sendo que a palavra-chave %identificador% será substituída
									pelo atributo $variaveisLoop['NOME_ARRAY'][$i]['%identificador%'];
									
	- &NomeArquivo: o símbolo & no nome de uma pasta, significa que esta é uma pasta de configurações
					para geração do arquivo NomeArquivo, ou seja, ela não estará na estrutura de arquivos
					a ser gerada.
					Exemplo: &Controller.php (significa que esta é uma pasta com configurações para o 
								arquivo Controller.php)
								
3. Dentro dos arquivos criados acima, devem ser criados os modelos para geração de seus códigos.
	O sistema irá copiar o conteúdo do arquivo modelo, substituindo-se as palavras-chave de acordo
	com as seguintes regras:
	
	- @identificador@: palavras-chave que são obtidas diretamente, sem necessidade de loop 
						Exemplo: @nmAplicacao@, @nmTabela@
						Essas variáveis são as chaves do array $variaveis da classe ControllerApplication,
						na geração do código, o sistema irá substituí-las pelos valores associados no mesmo array.																 						
						
	- #identificador#: o símbolo # no início e término de um identificador, indica que haverá um loop criando 
						um trecho de código para cada item de um array.
						Exemplos: 
							#colunasForm# no arquivo gerarModulo\horusnet\application\@nmAplicacao@\modules\@nmTabela@\Controller.php,
								cuja pasta de configurações é gerarModulo\horusnet\application\@nmAplicacao@\modules\@nmTabela@\&Controller.php
							#inputColuna# no arquivo gerarModulo\horusnet\application\@nmAplicacao@\modules\@nmTabela@\views\edit@NmTabela@.html,
								cuja pasta de configurações é gerarModulo\horusnet\application\@nmAplicacao@\modules\@nmTabela@\views\&edit@NmTabela@.html
						O sistema irá buscar dentro da pasta de configurações do arquivo em questão,
						um arquivo com o nome identificador-separador-NOME_ARRAY, onde "separador" é a string que será colocada
						entre os trechos de código (caso o separador deva ser ||, utilize o separador OR) e NOME_ARRAY é o nome do array que será usado
						 como base para o loop.
						Este array fica no atributo $variaveisLoop['NOME_ARRAY'] da classe ControllerApplication. 
						Existem duas opções para geração do trecho de código:
							- caso exista uma pasta com o nome "identificador" dentro da pasta de configurações do arquivo,
							o sistema seguirá os seguintes passos:
								1- irá buscar o conteúdo do arquivo identificador-separador-NOME_ARRAY;
								2- palavras-chave dentro deste arquivo no formato %identificador%
									serão substituídas pelo atributo $variaveisLoop['NOME_ARRAY'][$i]['%identificador%'];
								3- irá buscar um arquivo dentro da pasta "identificador", cujo nome seja o resultado obtido
									no item 2, com a extensão ".txt".
								4- irá pegar o conteúdo do arquivo obtido no item 3, sendo que palavras-chave dentro deste arquivo 
									no formato %identificador% serão substituídas pelo atributo 
									$variaveisLoop['NOME_ARRAY'][$i]['%identificador%'], e este será o trecho de código;		 
							
							- caso não exista uma pasta com o nome "identificador", o trecho de código será o
						conteúdo do arquivo identificador-separador-NOME_ARRAY, sendo que palavras-chave dentro deste arquivo no formato %identificador%
						serão substituídas pelo atributo $variaveisLoop['NOME_ARRAY'][$i]['%identificador%'];

	- %identificador%: sempre representa um atributo obtido através de loop em um array, para saber
						de qual array, leia o item acima, #identificador#.
												