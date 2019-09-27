<?php

use Carbon\Carbon;
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

$boleto = new PhpBoleto\Slip\Banco\Bnb(
    [
        'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '004.png',
        'dataVencimento' => Carbon::createFromDate(2017, 04, 03),
        'valor'                  => 2338.28,
        'multa'                  => false,
        'juros'                  => false,
        'numero'                 => '0990887',
        'numeroDocumento'        => '3456/1',
        'pagador'                => $pagador,
        'beneficiario'           => $beneficiario,
        'carteira'               => '21',
        'agencia'                => '0123',
        'conta'                  => '0000123',
        'contaDv'                => '1',
        'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
        'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
        'aceite'                 => 'S',
        'especieDoc'             => 'DM',
    ]
);

$pdf = new PhpBoleto\Slip\Render\Pdf();
$pdf->addBoleto($boleto);

$pdf->generateSlip($pdf::OUTPUT_SAVE, __DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'bnb.pdf');
