# Boleto PHP
Ferramenta para gerar boletos, remessas e fazer leitura de retornos. Projeto iniciado em cima do fork de [newerton/yii2-boleto-remessa](https://github.com/newerton/yii2-boleto-remessa).

Agradecimentos a [Newerton](https://github.com/newerton) por todo o trabalho feito até o momento do fork.

## Requerimentos
- [PHP Extensão Intl](http://php.net/manual/pt_BR/book.intl.php)
- [PHP Extensão Multibyte String  (MbString)](https://www.php.net/manual/pt_BR/book.mbstring.php)
- [PHP >=7.1.0](https://www.php.net/releases/7_1_0.php)


## Links
- [Documentação da API](http://newerton.github.io/yii2-boleto-remessa/)
- [Documentos Oficiais para gerar Boleto e Remessa](https://github.com/newerton/docs-boleto-remessa)

## Bancos suportados

Banco | Boleto | Remessa 400 | Remessa 240 | Retorno 400 | Retorno 240
----- | :---: | :---: | :---: | :---: | :---: |
 Banco do Brasil | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Bancoob (Sicoob) | :white_check_mark: | :white_check_mark: | :white_check_mark: | :eight_pointed_black_star: | :eight_pointed_black_star: |
 Banrisul | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Bradesco | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Caixa | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Hsbc | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Itau | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Santander | :white_check_mark: | :white_check_mark: | :white_check_mark: | :white_check_mark: | :white_check_mark: |
 Sicredi | :white_check_mark: | :white_check_mark: | | :white_check_mark: | |
 Banco do Nordeste | :eight_pointed_black_star: | :eight_pointed_black_star: | | | |

**\:eight_pointed_black_star: necessita de homologação**

## Instalação
Via composer:

```
composer require allanmcarvalho/php-boleto
```

Ou adicione manualmente ao seu composer.json:

```
"allanmcarvalho/php-boleto": "dev-master"
```

## Gerar boleto


### Criando o beneficiário ou pagador

```php
$beneficiario = new \PhpBoleto\Pessoa([
    'nome' => 'ACME',
    'endereco' => 'Rua um, 123',
    'cep' => '99999-999',
    'uf' => 'UF',
    'cidade' => 'CIDADE',
    'documento' => '99.999.999/9999-99',
]);

$pagador = new \PhpBoleto\Pessoa([
    'nome' => 'Cliente',
    'endereco' => 'Rua um, 123',
    'bairro' => 'Bairro',
    'cep' => '99999-999',
    'uf' => 'UF',
    'cidade' => 'CIDADE',
    'documento' => '999.999.999-99',
]);
```

### Criando o objeto boleto

#### Campos númericos e suas funções
- **numero**: campo numérico utilizado para a criação do nosso numero. (identificação do título no banco)*
- **numeroControle**: campo de livre utilização. até 25 caracteres. *(identificação do título na empresa)*
- **numeroDocumento**: campo utilizado para informar ao que o documento se referente *(duplicata, nf, np, ns, etc...)*


```php
$boletoArray = [
	'logo' => 'path/para/o/logo', // Logo da empresa
	'dataVencimento' => new \Carbon\Carbon('1790-01-01'),
	'valor' => 100.00,
	'multa' => 10.00, // porcento
	'juros' => 2.00, // porcento ao mes
	'juros_apos' =>  1, // juros e multa após
	'diasProtesto' => false, // protestar após, se for necessário
	'numero' => 1,
	'numeroDocumento' => 1,
	'pagador' => $pagador, // Objeto PessoaContract
	'beneficiario' => $beneficiario, // Objeto PessoaContract
	'agencia' => 9999, // BB, Bradesco, CEF, HSBC, Itáu
	'agenciaDv' => 9, // se possuir
	'conta' => 99999, // BB, Bradesco, CEF, HSBC, Itáu, Santander
	'contaDv' => 9, // Bradesco, HSBC, Itáu
	'carteira' => 99, // BB, Bradesco, CEF, HSBC, Itáu, Santander
	'convenio' => 9999999, // BB
	'variacaoCarteira' => 99, // BB
	'range' => 99999, // HSBC
	'codigoCliente' => 99999, // Bradesco, CEF, Santander
	'ios' => 0, // Santander
	'descricaoDemonstrativo' => ['msg1', 'msg2', 'msg3'], // máximo de 5
	'instrucoes' =>  ['inst1', 'inst2'], // máximo de 5
	'aceite' => 1,
	'especieDoc' => 'DM',
];

$boleto = new \PhpBoleto\Boleto\Banco\Bb($boletoArray);
```

### Gerando o boleto

**Gerando o boleto a partir da instância do objeto (somente um boleto)**

```php
$boleto->renderPDF();
// ou
$boleto->renderHTML();

// Os dois métodos aceitam como parâmetro dois booleanos.
// 1º Se True, após renderizado, irá mostrar a janela de impressão. O Valor default é false.
// 2º Se False, irá esconder as instruções de impressão. O valor default é true.
$boleto->renderPDF(true, false); // mostra a janela de impressão e esconde as instruções de impressão
```
```php
/*
 * O comportamento padrão para os métodos renderPDF() e renderHTML() é retornar uma string pura.
 * Para gerar um retorno no controller do yii2, utilize da seguinte forma:
 */

// PDF
Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
$headers = Yii::$app->response->headers;
$headers->add('Content-Type', 'application/pdf');
$headers->add('Content-Disposition', 'nline; boleto.pdf');
return $boleto->renderPDF();

// HTML
return $boleto->renderHTML();

```

**Gerando boleto a partir da instância do render**


```php
// Gerar em PDF
$pdf = new \PhpBoleto\Boleto\Render\Pdf();

$pdf->addBoleto($boleto);
// Ou, para adicionar um array de boletos
$pdf->addBoletos($boletos);

// Quando não informado parâmetros ele se comportará como Pdf::OUTPUT_STANDARD, enviando o buffer do pdf com os headers apropriados.
$pdf->gerarBoleto();

// Para mostrar a janela de impressão no load do PDF
$pdf->showPrint();

// Para remover as intruções de impressão
$pdf->hideInstrucoes();

// Para incluir Comprovante de entrega do boleto
$pdf->showComprovante();

// O método gerarBoleto() da classe PDF aceita como parâmetro:
//	1º destino: constante com os destinos disponíveis. Ex: Pdf::OUTPUT_SAVE.
//	2º path: caminho absoluto para salvar o pdf quando o destino for Pdf::OUTPUT_SAVE.
//Ex:
$pdf->gerarBoleto(Pdf::OUTPUT_SAVE, Yii::getAlias('@webroot/boletos/meu_boleto.pdf')); // salva o boleto na pasta.
$pdf_inline = $pdf->gerarBoleto(Pdf::OUTPUT_STRING); // retorna o boleto em formato string.
$pdf->gerarBoleto(Pdf::OUTPUT_DOWNLOAD); // força o download pelo navegador.

// Gerar em HTML
$html = new \PhpBoleto\Boleto\Render\Html();
$html->addBoleto($boleto);
// Ou para adicionar um array de boletos
$html->addBoletos($boletos);

// Para mostrar a janela de impressão no load da página
$html->showPrint();

// Para remover as intruções de impressão
$html->hideInstrucoes();

$html->gerarBoleto();

```

## Gerar remessa

```php
$remessaArray = [
	'agencia' => 9999,
	'agenciaDv' => 9, // se possuir
	'conta' => 99999,
	'contaDv' => 9, // se possuir
	'carteira' => 99,
	'convenio' => 9999999, // se possuir
	'range' => 99999, // se possuir
	'codigoCliente' => 99999, // se possuir
	'variacaoCarteira' => 99, // se possuir
	'beneficiario' => $beneficiario,
];

$remessa = new \PhpBoleto\Cnab\Remessa\Cnab400\Banco\Bb($remessaArray);

// Adicionar um boleto
$remessa->addBoleto($boleto);

// Ou para adicionar um array de boletos
$boletos = [];
$boletos[] = $boleto1;
$boletos[] = $boleto2;
$boletos[] = $boleto3;
$remessa->addBoletos($boletos);

echo $remessa->gerar();
```

## Tratar retorno

```php
$retorno = \PhpBoleto\Cnab\Retorno\Factory::make('full_path_arquivo_retorno');
$retorno->processar();
echo $retorno->getBancoNome();

// Retorno implementa \SeekableIterator, sendo assim, podemos utilizar o foreach da seguinte forma:
foreach($retorno as $registro) {
	var_dump($registro->toArray());
}

// Ou também podemos:
$detalheCollection = $retorno->getDetalhes();
foreach($detalheCollection as $detalhe) {
	var_dump($detalhe->toArray());
}

// Ou até mesmo do jeito laravel
$detalheCollection->each(function ($detalhe, $index) {
    var_dump($detalhe->toArray())
});
```

**Métodos disponíveis:**

```php
$retorno->getDetalhes();

$retorno->getHeader();

$retorno->getTrailer();
```
