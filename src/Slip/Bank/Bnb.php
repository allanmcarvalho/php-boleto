<?php

namespace PhpBoleto\Slip\Bank;

use Exception;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\Tools\CheckDigitCalculation;
use PhpBoleto\Tools\Util;

/**
 * Class Bnb
 * @package PhpBoleto\SlipInterface\Banks
 */
class Bnb extends SlipAbstract implements SlipInterface
{
    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $paymentPlace = 'PAGÁVEL EM QUALQUER AGÊNCIA BANCÁRIA ATÉ O VENCIMENTO';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = self::BANK_CODE_BNB;

    /**
     * Variáveis adicionais.
     *
     * @var array
     */
    public $additionalVariables = [
        'carteira_nome' => '',
    ];

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $wallets = ['21'];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $documentTypes = [
        'DM' => '01',
        'NP' => '02',
        'CH' => '03',
        'CN' => '04',
        'RC' => '05'
    ];

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
        $automaticDrop = (int) $automaticDrop;
        $this->automaticDropAfter = $automaticDrop > 0 ? $automaticDrop : 0;
        return $this;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     * @throws Exception
     */
    protected function generateOurNumber()
    {
        $numero_boleto = $this->getNumber();
        return Util::numberFormatGeral($numero_boleto, 7) . CheckDigitCalculation::bnbOurNumber($this->getNumber());
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
        $ourNumber = $this->getOurNumber();
        $wallet = Util::numberFormatGeral($this->getWallet(), 2);
        $agency = Util::numberFormatGeral($this->getAgency(), 4);
        $account = Util::numberFormatGeral($this->getAccount(), 7);
        $assignorCheckDigit = $this->getAccountCheckDigit() ?: CheckDigitCalculation::bnbAccount($this->getAgency(), $this->getAccount());

        return $this->fieldFree = $agency . $account . $assignorCheckDigit . $ourNumber . $wallet . '000';
    }
}
