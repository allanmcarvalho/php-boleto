<?php

use Carbon\Carbon;
use PhpBoleto\Cnab\Remittances\Cnab400\Bank\Hsbc;
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

$boleto = new PhpBoleto\Slip\Bank\Hsbc(
    [
        'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '399.png',
        'dataVencimento' => new Carbon(),
        'valor'                  => 100,
        'multa'                  => false,
        'juros'                  => false,
        'numero'                 => 1,
        'numeroDocumento'        => 1,
        'pagador'                => $pagador,
        'beneficiario'           => $beneficiario,
        'carteira'               => 'CSB',
        'range'                  => 12345,
        'agencia'                => 1111,
        'conta'                  => 999999,
        'contaDv'                => 9,
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
        'aceite'                 => 'S',
        'especieDoc'             => 'DM',
    ]
);

$remessa = new Hsbc(
    [
        'agencia'      => 1111,
        'carteira'     => 'CSB',
        'conta'        => 999999,
        'contaDv'      => 9,
        'beneficiario' => $beneficiario,
    ]
);
$remessa->addSlip($boleto);

echo $remessa->save(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'hsbc.txt');
