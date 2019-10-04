<?php

namespace PhpBoleto\Slip\Bank;

use Exception;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\Tools\CheckDigitCalculation;
use PhpBoleto\Tools\Util;

/**
 * Class Bancoob
 * @package PhpBoleto\SlipInterface\Banks
 */
class Bancoob extends SlipAbstract implements SlipInterface
{
    /**
     * Bancoob constructor.
     * @param array $params
     * @throws Exception
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addRequiredFiled('covenant');
    }

    /**
     * Código do banco
     * @var string
     */
    protected $bankCode = self::BANK_CODE_BANCOOB;

    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $wallets = ['1', '3'];

    /**
     * Espécie do documento, código para remessa
     * @var string
     */
    protected $documentTypes = [
        'DM' => '01',
        'NP' => '02',
        'DS' => '12',
    ];

    /**
     * Código do cliente junto ao banco.
     *
     * @var string
     */
    protected $clientCode;

    /**
     * Parcela do boleto.
     *
     * @var string
     */
    protected $quota;

    /**
     * Gera o Nosso Número.
     *
     * @return string
     * @throws Exception
     */
    protected function generateOurNumber()
    {
        return $this->getNumber() . CheckDigitCalculation::bancoobOurNumber($this->getAgency(), $this->getCovenant(), $this->getNumber());
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getOurNumberCustom()
    {
        return substr_replace($this->getOurNumber(), '-', -1, 0);
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

        $nossoNumero = $this->getOurNumber();

        $campoLivre = Util::numberFormatGeral($this->getWallet(), 1);
        $campoLivre .= Util::numberFormatGeral($this->getAgency(), 4);
        $campoLivre .= Util::numberFormatGeral($this->getWallet(), 2);
        $campoLivre .= Util::numberFormatGeral($this->getCovenant(), 7);
        $campoLivre .= Util::numberFormatGeral($nossoNumero, 8);
        $campoLivre .= Util::numberFormatGeral($this->getQuota(), 3); //Numero da parcela - Não implementado

        return $this->fieldFree = $campoLivre;
    }

    /**
     * Método para gerar a Agencia e o Código do Beneficiário
     *
     * @return string
     * @throws Exception
     */
    public function getAgencyAndAccount(): string
    {
        $agencia = $this->getAgency();
        $conta = $this->getClientCode();

        return $agencia . ' / ' . $conta;
    }

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
     * Seta a Parcela.
     *
     * @param mixed $quota
     *
     * @return $this
     */
    public function setQuota($quota)
    {
        $this->quota = $quota;

        return $this;
    }

    /**
     * Retorna a Parcela.
     *
     * @return string
     */
    public function getQuota()
    {
        return $this->quota;
    }

}
