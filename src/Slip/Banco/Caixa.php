<?php

namespace PhpBoleto\Slip\Banco;

use Exception;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\Tools\CalculoDV;
use PhpBoleto\Tools\Util;

/**
 * Class Caixa
 * @package PhpBoleto\SlipInterface\Banco
 */
class Caixa extends SlipAbstract implements SlipInterface
{
    /**
     * Caixa constructor.
     * @param array $params
     * @throws Exception
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setRequiredFields('number', 'agency', 'wallet', 'clientCode');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = self::BANK_CODE_CEF;

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $wallets = ['RG', 'SR'];

    /**
     * Espécie do documento, código para remessa
     *
     * @var string
     */
    protected $documentTypes = [
        'DM' => '01',
        'NP' => '02',
        'DS' => '03',
        'NS' => '05',
        'LC' => '06',
    ];

    /**
     * Código do cliente junto ao banco.
     *
     * @var string
     */
    protected $clientCode;

    /**
     * Seta o código do cliente.
     *
     * @param mixed $clientCode
     *
     * @return $this
     */
    public function setClientCode($clientCode)
    {
        $this->clientCode = $clientCode;

        return $this;
    }

    /**
     * Retorna o código do cliente.
     *
     * @return string
     */
    public function getClientCode()
    {
        return $this->clientCode;
    }

    /**
     * Retorna o código do cliente como se fosse a conta
     * ja que a caixa não faz uso da conta para nada.
     *
     * @return string
     */
    public function getAccount()
    {
        return $this->getClientCode();
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     *@throws Exception
     */
    protected function generateOurNumber()
    {
        $numero_boleto = $this->getNumber();
        $composition = '1';
        if ($this->getWallet() == 'SR') {
            $composition = '2';
        }

        $carteira = $composition . '4';
        // As 15 próximas posições no nosso número são a critério do beneficiário, utilizando o sequencial
        // Depois, calcula-se o código verificador por módulo 11
        $numero = $carteira . Util::numberFormatGeral($numero_boleto, 15);
        return $numero;
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getOurNumberCustom()
    {
        return $this->getOurNumber() . '-' . CalculoDV::cefOurNumber($this->getOurNumber());
    }

    /**
     * Seta dias para baixa automática
     *
     * @param int $automaticDrop
     *
     * @return $this
     * @throws Exception
     */
    public function setAutomaticDropAfter(int $automaticDrop)
    {
        if ($this->getProtestAfter() > 0) {
            throw new Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        $automaticDrop = (int)$automaticDrop;
        $this->automaticDropAfter = $automaticDrop > 0 ? $automaticDrop : 0;
        return $this;
    }

    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     * @throws Exception
     */
    protected function getFieldFree()
    {
        if ($this->fieldFree) {
            return $this->fieldFree;
        }
        $ourNumber = Util::numberFormatGeral($this->generateOurNumber(), 17);
        $beneficiary = Util::numberFormatGeral($this->getClientCode(), 6);
        // Código do beneficiário + DV]
        $fieldFree = $beneficiary . Util::modulo11($beneficiary);
        // Sequencia 1 (posições 3-5 NN) + Constante 1 (1 => registrada, 2 => sem registro)
        $carteira = $this->getWallet();
        if ($carteira == 'SR') {
            $constant = '2';
        } else {
            $constant = '1';
        }
        $fieldFree .= substr($ourNumber, 2, 3) . $constant;
        // Sequencia 2 (posições 6-8 NN) + Constante 2 (4-Beneficiário)
        $fieldFree .= substr($ourNumber, 5, 3) . '4';
        // Sequencia 3 (posições 9-17 NN)
        $fieldFree .= substr($ourNumber, 8, 9);
        // DV do Campo Livre
        $fieldFree .= Util::modulo11($fieldFree);
        return $this->fieldFree = $fieldFree;
    }
}
