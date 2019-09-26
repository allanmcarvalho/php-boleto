<?php

namespace PhpBoleto\Tests;

use PhpBoleto\Person;
use PhpBoleto\Util;

class PessoaTest extends TestCase
{

    public function testPessoaCriandoConstrutor(){

        $nome = 'Cliente';
        $endereco = 'Rua um, 123';
        $bairro = 'Bairro';
        $cep = '99999999';
        $uf = 'UF';
        $cidade = 'CIDADE';
        $documento = '99999999999';

        $pessoa = new Person(
            [
                'nome' => $nome,
                'endereco' => $endereco,
                'bairro' => $bairro,
                'cep' => $cep,
                'uf' => $uf,
                'cidade' => $cidade,
                'documento' => $documento,
            ]
        );

        $this->assertEquals($nome, $pessoa->getName());
        $this->assertEquals($endereco, $pessoa->getAddress());
        $this->assertEquals($bairro, $pessoa->getAddressDistrict());
        $this->assertEquals(Util::maskString($cep, '#####-###'), $pessoa->getPostalCode());
        $this->assertEquals($uf, $pessoa->getStateUf());
        $this->assertEquals($cidade, $pessoa->getCity());
        $this->assertEquals(Util::maskString($documento, '###.###.###-##'), $pessoa->getDocument());
        $this->assertEquals('CPF', $pessoa->getDocumentLabel());

        $this->assertContains(Util::maskString($cep, '#####-###'), $pessoa->getPostalCodeCityAndStateUf());
        $this->assertContains($cidade, $pessoa->getPostalCodeCityAndStateUf());
        $this->assertContains($uf, $pessoa->getPostalCodeCityAndStateUf());

        $this->assertContains($nome, $pessoa->getNameAndDocument());
        $this->assertContains('CPF', $pessoa->getNameAndDocument());
        $this->assertContains(Util::maskString($documento, '###.###.###-##'), $pessoa->getNameAndDocument());

        $pessoa->setDocument('');
        $this->assertEquals($nome, $pessoa->getNameAndDocument());

        $documento = '99999999999999';
        $pessoa->setDocument($documento);
        $this->assertEquals(Util::maskString($documento, '##.###.###/####-##'), $pessoa->getDocument());
        $this->assertEquals('CNPJ', $pessoa->getDocumentLabel());

        $documento = '9999999999';
        $pessoa->setDocument($documento);
        $this->assertEquals(Util::maskString($documento, '##.#####.#-##'), $pessoa->getDocument());
        $this->assertEquals('CEI', $pessoa->getDocumentLabel());

    }

    /**
     * @expectedException     \Exception
     */
    public function testPessoaDocumentoErrado(){

        $pessoa = new Person(
            [
                'documento' => '99999',
            ]
        );
    }


    public function testPessoaCriandoMetodoCreate(){

        $nome = 'Cliente';
        $endereco = 'Rua um, 123';
        $bairro = 'Bairro';
        $cep = '99999999';
        $uf = 'UF';
        $cidade = 'CIDADE';
        $documento = '99999999999';

        $pessoa = new Person(
            [
                'nome' => $nome,
                'endereco' => $endereco,
                'bairro' => $bairro,
                'cep' => $cep,
                'uf' => $uf,
                'cidade' => $cidade,
                'documento' => $documento,
            ]
        );

        $pessoa2 = Person::create($nome, $documento, $endereco, $bairro, $cep, $cidade, $uf);

        $pessoa_contrutor = new \ReflectionClass($pessoa);
        $pessoa_create = new \ReflectionClass($pessoa2);

        $propriedades = $pessoa_contrutor->getProperties();

        foreach ($propriedades as $propriedade) {

            $propriedade->setAccessible(true);
            $valor_1 = $propriedade->getValue($pessoa);

            $propriedade_create = $pessoa_create->getProperty($propriedade->getName());

            $propriedade_create->setAccessible(true);
            $valor_2 = $propriedade_create->getValue($pessoa2);

            $this->assertEquals($valor_1, $valor_2);
        }

    }

    public function testPessoaMascara(){

        $pessoa = new Person;

        $pessoa->setDocument('99.999.999/9999-99');
        $this->assertEquals('CNPJ', $pessoa->getDocumentLabel());
        $this->assertEquals('99.999.999/9999-99', $pessoa->getDocument());
        $pessoa->setDocument('99999999999999');
        $this->assertEquals('CNPJ', $pessoa->getDocumentLabel());
        $this->assertEquals('99.999.999/9999-99', $pessoa->getDocument());

        $pessoa->setDocument('999.999.999-99');
        $this->assertEquals('CPF', $pessoa->getDocumentLabel());
        $this->assertEquals('999.999.999-99', $pessoa->getDocument());
        $pessoa->setDocument('99999999999');
        $this->assertEquals('CPF', $pessoa->getDocumentLabel());
        $this->assertEquals('999.999.999-99', $pessoa->getDocument());

        $pessoa->setDocument('99.99999.9-99');
        $this->assertEquals('CEI', $pessoa->getDocumentLabel());
        $this->assertEquals('99.99999.9-99', $pessoa->getDocument());
        $pessoa->setDocument('9999999999');
        $this->assertEquals('CEI', $pessoa->getDocumentLabel());
        $this->assertEquals('99.99999.9-99', $pessoa->getDocument());

    }
}
