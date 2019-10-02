<?php

use Carbon\Carbon;
use PhpBoleto\Cnab\Remittances\Cnab400\Bank\Bb;
use PhpBoleto\Person;

require 'autoload.php';
$beneficiario = new Person(
    [
        'nome'      => 'ACME',
        'endereco'  => 'Rua um, 123',
        'cep'       => '99999-999',
        'uf'        => 'UF',
        'cidade'    => 'CIDADE',
        'documento' => '99.999.999/9999-99',
    ]
);

$pagador = new Person(
    [
        'nome'      => 'Cliente',
        'endereco'  => 'Rua um, 123',
        'bairro'    => 'Bairro',
        'cep'       => '99999-999',
        'uf'        => 'UF',
        'cidade'    => 'CIDADE',
        'documento' => '999.999.999-99',
    ]
);

$boleto = new PhpBoleto\Slip\Bank\Bb(
    [
        'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '001.png',
        'dataVencimento' => new Carbon(),
        'valor'                  => 100,
        'multa'                  => false,
        'juros'                  => false,
        'numero'                 => 1,
        'numeroDocumento'        => 1,
        'pagador'                => $pagador,
        'beneficiario'           => $beneficiario,
        'carteira'               => 11,
        'convenio'               => 1234567,
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
        'aceite'                 => 'S',
        'especieDoc'             => 'DM',
    ]
);

$remessa = new Bb(
    [
        'agencia'      => 1111,
        'carteira'     => 11,
        'conta'        => 999999999,
        'convenio'     => 1234567,
        'beneficiario' => $beneficiario,
    ]
);
$remessa->addSlip($boleto);

echo $remessa->save(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'bb.txt');
