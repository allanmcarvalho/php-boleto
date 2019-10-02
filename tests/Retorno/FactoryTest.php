<?php

namespace PhpBoleto\Tests\Retorno;

use Exception;
use Illuminate\Support\Collection;
use PhpBoleto\Cnab\Returns\Factory;
use PhpBoleto\Tests\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @expectedException     Exception
     */
    public function testCriarEmBranco(){
        $retorno = Factory::make('');
        $retorno->processar();
    }

    /**
     * @expectedException     Exception
     */
    public function testCriarComRemessa(){
        $retorno = Factory::make(__DIR__ . '/files/cnab400/remessa.txt');
        $retorno->processar();
    }

    /**
     * @expectedException     Exception
     */
    public function testCriarComPathQueNaoExiste(){
        $retorno = Factory::make(__DIR__ . '/files/cnab400/naoexiste.txt');
        $retorno->processar();
    }

    /**
     * @expectedException     Exception
     */
    public function testCriarComRetornoBancoNaoExiste(){
        $retorno = Factory::make(__DIR__ . '/files/cnab400/retorno_banco_fake.ret');
        $retorno->processar();
    }

    public function testCriarComFile()
    {
        $retorno = Factory::make(__DIR__ . '/files/cnab400/bradesco.ret');
        $retorno->processar();
    }

    public function testCriarComString()
    {
        $retorno = Factory::make(file_get_contents(__DIR__ . '/files/cnab400/bradesco.ret'));
        $retorno->processar();
    }
}
