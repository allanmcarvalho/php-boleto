<?php

namespace PhpBoleto\Slip\Bank;

use Exception;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\Tools\CheckDigitCalculation;
use PhpBoleto\Tools\Util;

/**
 * Class Bb
 * @package PhpBoleto\SlipInterface\Banco
 */
class Bb extends SlipAbstract implements SlipInterface
{
    /**
     * Bb constructor.
     * @param array $params
     * @throws Exception
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setRequiredFields('number', 'covenant', 'wallet');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = self::BANK_CODE_BB;

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $wallets = array('11', '12', '15', '16', '17', '18', '31', '51');

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
        'LC' => '08',
        'W' => '09',
        'CH' => '10',
        'DS' => '12',
        'ND' => '13',
    ];


    /**
     * Define o numero da variação da carteira.
     *
     * @var string
     */
    protected $walletVariation;

    /**
     * Define o número da variação da carteira, para saber quando utilizar o nosso numero de 17 posições.
     *
     * @param string $walletVariation
     * @return Bb
     */
    public function setWalletVariation($walletVariation): Bb
    {
        $this->walletVariation = $walletVariation;
        return $this;
    }

    /**
     * Retorna o número da variação de carteira
     *
     * @return string
     */
    public function getWalletVariation()
    {
        return $this->walletVariation;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     * @throws Exception
     */
    protected function generateOurNumber()
    {
        $covenant = $this->getCovenant();
        $slipNumber = $this->getNumber();
        switch (strlen($covenant)) {
            case 4:
                $number = Util::numberFormatGeral($covenant, 4) . Util::numberFormatGeral($slipNumber, 7);
                break;
            case 6:
                if (in_array($this->getWallet(), ['16', '18']) && $this->getWalletVariation() == 17) {
                    $number = Util::numberFormatGeral($slipNumber, 17);
                } else {
                    $number = Util::numberFormatGeral($covenant, 6) . Util::numberFormatGeral($slipNumber, 5);
                }
                break;
            case 7:
                $number = Util::numberFormatGeral($covenant, 7) . Util::numberFormatGeral($slipNumber, 10);
                break;
            default:
                throw new Exception('O código do convênio precisa ter 4, 6 ou 7 dígitos!');
        }
        return $number;
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getOurNumberCustom()
    {
        $nn = $this->getOurNumber() . CheckDigitCalculation::bbOurNumber($this->getOurNumber());
        return strlen($nn) <= 17 ? substr_replace($nn, '-', -1, 0) : $nn;
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
        $length = strlen($this->getCovenant());
        $ourNumber = $this->generateOurNumber();
        if (strlen($this->getNumber()) > 10) {
            if ($length == 6 && in_array($this->getWallet(), ['16', '18']) && Util::numberFormatGeral($this->getWalletVariation(), 3) == '017') {
                return $this->fieldFree = Util::numberFormatGeral($this->getCovenant(), 6) . $ourNumber . '21';
            } else {
                throw new Exception('Só é possível criar um boleto com mais de 10 dígitos no nosso número quando a carteira é 21 e o convênio possuir 6 dígitos.');
            }
        }
        switch ($length) {
            case 4:
            case 6:
                return $this->fieldFree = $ourNumber . Util::numberFormatGeral($this->getAgency(), 4) . Util::numberFormatGeral($this->getAccount(), 8) . Util::numberFormatGeral($this->getWallet(), 2);
            case 7:
                return $this->fieldFree = '000000' . $ourNumber . Util::numberFormatGeral($this->getWallet(), 2);
        }
        throw new Exception('O código do convênio precisa ter 4, 6 ou 7 dígitos!');
    }
}
