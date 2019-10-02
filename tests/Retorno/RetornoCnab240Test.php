<?php

namespace PhpBoleto\Tests\Retorno;

use Illuminate\Support\Collection;
use PhpBoleto\Cnab\Returns\Cnab240\Detalhe;
use PhpBoleto\Cnab\Returns\Cnab240\DetalheSegmentoT;
use PhpBoleto\Cnab\Returns\Cnab240\DetalheSegmentoU;
use PhpBoleto\Cnab\Returns\Cnab240\DetalheSegmentoY;
use PhpBoleto\Cnab\Returns\Factory;
use PhpBoleto\Tests\TestCase;

class RetornoCnab240Test extends TestCase
{
    public function testRetornoSantanderCnab240()
    {
        $retorno = Factory::make(__DIR__ . '/files/cnab240/santander.ret');
        $retorno->processar();

        $this->assertNotNull($retorno->getHeader());
        $this->assertNotNull($retorno->getHeaderLote());
        $this->assertNotNull($retorno->getDetalhes());
        $this->assertNotNull($retorno->getTrailerLote());
        $this->assertNotNull($retorno->getTrailer());

        $this->assertEquals('Banco Santander (Brasil) S.A.', $retorno->getBancoNome());
        $this->assertEquals('033', $retorno->getCodigoBanco());

        $this->assertInstanceOf(Collection::class, $retorno->getDetalhes());

        $this->assertInstanceOf(Detalhe::class, $retorno->getDetalhe(1));

        foreach ($retorno->getDetalhes() as $detalhe) {
            $this->assertInstanceOf(Detalhe::class, $detalhe);
            $this->assertArrayHasKey('numeroDocumento', $detalhe->toArray());
        }
    }

}
