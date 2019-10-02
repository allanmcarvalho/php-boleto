<?php

namespace PhpBoleto\Slip\Bank;

use Exception;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\Tools\CheckDigitCalculation;
use PhpBoleto\Tools\Util;

/**
 * Class Itau
 * @package PhpBoleto\SlipInterface\Banco
 */
class Itau extends SlipAbstract implements SlipInterface
{

    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $paymentPlace = 'Até o vencimento, preferencialmente no Itaú';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = self::BANK_CODE_ITAU;

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
    protected $wallets = ['112', '115', '188', '109', '121', '180', '175'];

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
        'CT' => '06',
        'CS' => '07',
        'DS' => '08',
        'LC' => '09',
        'ND' => '13',
        'CDA' => '15',
        'EC' => '16',
        'CPS' => '17',
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
        $automaticDrop = (int)$automaticDrop;
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
        $numero_boleto = Util::numberFormatGeral($this->getNumber(), 8);
        $carteira = Util::numberFormatGeral($this->getWallet(), 3);
        $agencia = Util::numberFormatGeral($this->getAgency(), 4);
        $conta = Util::numberFormatGeral($this->getAccount(), 5);
        $dv = CheckDigitCalculation::itauOurNumber($agencia, $conta, $carteira, $numero_boleto);
        return $numero_boleto . $dv;
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getOurNumberCustom()
    {
        return $this->getWallet() . '/' . substr_replace($this->getOurNumber(), '-', -1, 0);
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
        $ourNumber = Util::numberFormatGeral($this->getOurNumber(), 9);
        $wallet = Util::numberFormatGeral($this->getWallet(), 3);
        $agency = Util::numberFormatGeral($this->getAgency(), 4);
        $account = Util::numberFormatGeral($this->getAccount(), 5);
        $dvAgConta = CheckDigitCalculation::itauAccount($agency, $account);
        return $this->fieldFree = $wallet . $ourNumber . $agency . $account . $dvAgConta . '000';
    }
}
