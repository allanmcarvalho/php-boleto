<?php
namespace PhpBoleto\Cnab\Retorno\Cnab400\Banco;

use PhpBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use PhpBoleto\Contracts\Boleto\Boleto as BoletoContract;
use PhpBoleto\Contracts\Cnab\RetornoCnab400;
use PhpBoleto\Util;

class Banrisul extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BANRISUL;



    protected function processarHeader(array $header)
    {
        return true;
    }

    protected function processarDetalhe(array $detalhe)
    {
        return true;
    }

    protected function processarTrailer(array $trailer)
    {
        return true;
    }
}
