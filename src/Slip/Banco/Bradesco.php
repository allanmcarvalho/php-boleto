<?php

namespace PhpBoleto\Slip\Banco;

use Exception;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\Tools\CalculoDV;
use PhpBoleto\Tools\Util;

/**
 * Class Bradesco
 * @package PhpBoleto\SlipInterface\Banco
 */
class Bradesco extends SlipAbstract implements SlipInterface
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = SlipInterface::BANK_CODE_BRADESCO;

    /**
     * Define as carteiras disponíveis para este banco
     * '09' => Com registro | '06' => Sem Registro | '21' => Com Registro - Pagável somente no Bradesco | '22' => Sem Registro - Pagável somente no Bradesco | '25' => Sem Registro - Emissão na Internet | '26' => Com Registro - Emissão na Internet
     *
     * @var array
     */
    protected $wallets = ['09', '06', '21', '22', '25', '26'];

    /**
     * Trata-se de código utilizado para identificar mensagens especificas ao cedente, sendo
     * que o mesmo consta no cadastro do Banco, quando não houver código cadastrado preencher
     * com zeros "000".
     *
     * @var int
     */
    protected $cip = '000';

    /**
     * Variaveis adicionais.
     *
     * @var array
     */
    public $additionalVariables = [
        'cip' => '000',
        'mostra_cip' => true,
    ];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $documentTypes = [
        'DM' => '01',
        'NP' => '02',
        'NS' => '03',
        'CS' => '04',
        'REC' => '05',
        'LC' => '10',
        'ND' => '11',
        'DS' => '12',
    ];

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function generateOurNumber()
    {
        return Util::numberFormatGeral($this->getNumber(), 11)
            . CalculoDV::bradescoOurNumber($this->getWallet(), $this->getNumber());
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
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getOurNumberCustom()
    {
        return Util::numberFormatGeral($this->getWallet(), 2) . ' / ' . substr_replace($this->getOurNumber(), '-', -1, 0);
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
        return $this->fieldFree = Util::numberFormatGeral($this->getAgency(), 4) .
            Util::numberFormatGeral($this->getWallet(), 2) .
            Util::numberFormatGeral($this->getNumber(), 11) .
            Util::numberFormatGeral($this->getAccount(), 7) .
            '0';
    }

    /**
     * Define o campo CIP do boleto
     *
     * @param  int $cip
     * @return Bradesco
     */
    public function setCip($cip)
    {
        $this->cip = $cip;
        $this->additionalVariables['cip'] = $this->getCip();
        return $this;
    }

    /**
     * Retorna o campo CIP do boleto
     *
     * @return int
     */
    public function getCip()
    {
        return Util::numberFormatGeral($this->cip, 3);
    }
}
