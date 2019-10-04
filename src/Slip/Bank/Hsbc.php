<?php

namespace PhpBoleto\Slip\Bank;

use Exception;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\Tools\Util;

/**
 * Class Hsbc
 * @package PhpBoleto\SlipInterface\Banks
 */
class Hsbc extends SlipAbstract implements SlipInterface
{
    /**
     * Hsbc constructor.
     * @param array $params
     * @throws Exception
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addRequiredFiled('range', 'accountCheckDigit');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = self::BANK_CODE_HSBC;

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $wallets = ['CSB'];

    /**
     * Espécie do documento, código para remessa
     *
     * @var string
     */
    protected $documentTypes = [
        'DM' => '01',
        'NP' => '02',
        'NS' => '03',
        'REC' => '05',
        'CE' => '09',
        'DS' => '10',
        'PD' => '98',
    ];

    /**
     * Código de range de composição do nosso numero.
     *
     * @var string
     */
    protected $range;

    /**
     * Espécie do documento, geralmente DM (Duplicata Mercantil)
     *
     * @var string
     */
    protected $documentType = 'PD';

    /**
     * @return string
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @param string $range
     *
     * @return Hsbc
     */
    public function setRange($range)
    {
        $this->range = $range;

        return $this;
    }

    /**
     * Define o campo Espécie Doc, HSBC sempre PD
     *
     * @param string $documentType
     * @return SlipAbstract
     */
    public function setDocumentType(string $documentType): SlipAbstract
    {
        $this->documentType = 'PD';
        return $this;
    }

    /**
     * Retorna o campo Agência/Beneficiário do boleto
     *
     * @return string
     */
    public function getAgencyAndAccount(): string
    {
        $agency = $this->getAgencyCheckDigit() !== null ? $this->getAgency() . '-' . $this->getAgencyCheckDigit() : $this->getAgency();

        if ($this->getAccountCheckDigit() !== null && strlen($this->getAccountCheckDigit()) == 1) {
            $account = substr($this->getAccount(), 0, -1) . '-' . substr($this->getAccount(), -1) . $this->getAccountCheckDigit();
        } elseif ($this->getAccountCheckDigit() !== null && strlen($this->getAccountCheckDigit()) == 2) {
            $account = substr($this->getAccount(), 0, -1) . '-' . substr($this->getAccount(), -1) . $this->getAccountCheckDigit();
        } else {
            $account = $this->getAccount();
        }

        return $agency . ' / ' . $account;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function generateOurNumber()
    {
        $range = Util::numberFormatGeral($this->getRange(), 5);
        $slipNumber = Util::numberFormatGeral($this->getNumber(), 5);
        $dv = Util::modulo11($range . $slipNumber, 2, 7);
        return $range . $slipNumber . $dv;
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
     */
    protected function getFieldFree()
    {
        if ($this->fieldFree) {
            return $this->fieldFree;
        }

        $agency = Util::numberFormatGeral($this->getAgency(), 4);
        $account = Util::numberFormatGeral($this->getAccount(), 6);
        $agencyAndAccount = $agency . $account . ($this->getAccountCheckDigit() ? $this->getAccountCheckDigit() : Util::modulo11($agency . $account));

        return $this->fieldFree = $this->getOurNumber() .
            $agencyAndAccount .
            '00' . // Codigo da carteira
            '1'; // Codigo do aplicativo
    }
}
