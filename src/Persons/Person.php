<?php

namespace PhpBoleto\Persons;

use Exception;
use PhpBoleto\Interfaces\Person\PersonInterface;
use PhpBoleto\Tools\Util;

class Person implements PersonInterface
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $address;
    /**
     * @var string
     */
    protected $addressDistrict;
    /**
     * @var string
     */
    protected $postalCode;
    /**
     * @var string
     */
    protected $stateUf;
    /**
     * @var string
     */
    protected $city;
    /**
     * @var string
     */
    protected $document;

    /**
     * Cria a pessoa passando os parâmetros.
     *
     * @param string $name
     * @param string $document
     * @param string $address
     * @param string $addressDistrict
     * @param string $postalCode
     * @param string $city
     * @param string $stateUf
     *
     * @return Person
     */
    public static function create(string $name, string $document, string $address = null, string $addressDistrict = null, string $postalCode = null, string $city = null, string $stateUf = null): Person
    {
        return new static([
            'name' => $name,
            'address' => $address,
            'addressDistrict' => $addressDistrict,
            'postalCode' => $postalCode,
            'stateUf' => $stateUf,
            'city' => $city,
            'document' => $document,
        ]);
    }

    /**
     * Construtor
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        Util::fillClass($this, $params);
    }

    /**
     * Define o CEP
     *
     * @param string $postalCode
     */
    public function setPostalCode(string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Retorna o CEP
     *
     * @return string
     */
    public function getPostalCode(): string
    {
        return Util::maskString(Util::onlyNumbers($this->postalCode), '#####-###');
    }

    /**
     * Define a cidade
     *
     * @param string $city
     */
    public function setCity(string $city)
    {
        $this->city = $city;
    }

    /**
     * Retorna a cidade
     *
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Define o documento (CPF, CNPJ ou CEI)
     *
     * @param string $document
     *
     * @throws Exception
     */
    public function setDocument(string $document)
    {
        $document = substr(Util::onlyNumbers($document), -14);
        if (!in_array(strlen($document), [9, 10, 11, 14, 0])) {
            throw new Exception('Documento inválido');
        }
        $this->document = $document;
    }

    /**
     * Retorna o documento (CPF ou CNPJ)
     *
     * @return string
     */
    public function getDocument(): string
    {
        if ($this->getDocumentLabel() == 'CPF') {
            return Util::maskString(Util::onlyNumbers($this->document), '###.###.###-##');
        } elseif ($this->getDocumentLabel() == 'CEI') {
            return Util::maskString(Util::onlyNumbers($this->document), '##.#####.#-##');
        }
        return Util::maskString(Util::onlyNumbers($this->document), '##.###.###/####-##');
    }

    /**
     * Define o endereço
     *
     * @param string $address
     */
    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    /**
     * Retorna o endereço
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Define o bairro
     *
     * @param string $addressDistrict
     */
    public function setAddressDistrict(string $addressDistrict)
    {
        $this->addressDistrict = $addressDistrict;
    }

    /**
     * Retorna o bairro
     *
     * @return string
     */
    public function getAddressDistrict(): string
    {
        return $this->addressDistrict;
    }

    /**
     * Define o nome
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Retorna o nome
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Define a UF
     *
     * @param string $stateUf
     */
    public function setStateUf(string $stateUf)
    {
        $this->stateUf = $stateUf;
    }

    /**
     * Retorna a UF
     *
     * @return string
     */
    public function getStateUf(): string
    {
        return $this->stateUf;
    }

    /**
     * Retorna o nome e o documento formatados
     *
     * @return string
     */
    public function getNameAndDocument(): string
    {
        if (!$this->getDocument()) {
            return $this->getName();
        } else {
            return $this->getName() . ' / ' . $this->getDocumentLabel() . ': ' . $this->getDocument();
        }
    }

    /**
     * Retorna se o tipo do documento é CPF ou CNPJ ou Documento
     *
     * @return string
     */
    public function getDocumentLabel()
    {
        $cpf_cnpj_cei = Util::onlyNumbers($this->document);

        if (strlen($cpf_cnpj_cei) == 11) {
            return 'CPF';
        } elseif (strlen($cpf_cnpj_cei) == 10) {
            return 'CEI';
        }

        return 'CNPJ';
    }

    /**
     * Retorna o endereço formatado para a linha 2 de endereço
     *
     * Ex: 71000-000 - Brasília - DF
     *
     * @return string
     */
    public function getPostalCodeCityAndStateUf(): string
    {
        $dados = array_filter(array($this->getPostalCode(), $this->getCity(), $this->getStateUf()));
        return implode(' - ', $dados);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'address' => $this->getAddress(),
            'addressDistrict' => $this->getAddressDistrict(),
            'postalCode' => $this->getPostalCode(),
            'stateUf' => $this->getStateUf(),
            'city' => $this->getCity(),
            'document' => $this->getDocument(),
            'nameAndDocument' => $this->getNameAndDocument(),
            'postalCodeCityAndStateUf' => $this->getPostalCodeCityAndStateUf(),
        ];
    }
}
