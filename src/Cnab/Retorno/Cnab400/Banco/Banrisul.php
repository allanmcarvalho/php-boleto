<?php
namespace PhpBoleto\Cnab\Retorno\Cnab400\Banco;

use PhpBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use PhpBoleto\Interfaces\Slip\SlipInterface as BoletoContract;
use PhpBoleto\Interfaces\Cnab\RetornoCnabInterface400;
use PhpBoleto\Util;

class Banrisul extends AbstractRetorno implements RetornoCnabInterface400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::BANK_CODE_BANRISUL;



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
