<?php

namespace PhpBoleto\Slip\Bank;

use Exception;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\Tools\CheckDigitCalculation;
use PhpBoleto\Tools\Util;

/**
 * Class Santander
 * @package PhpBoleto\SlipInterface\Banco
 */
class Santander extends SlipAbstract implements SlipInterface
{
    /**
     * Santander constructor.
     * @param array $params
     * @throws Exception
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setRequiredFields('number', 'account', 'wallet');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = self::BANK_CODE_SANTANDER;

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $wallets = ['101', '201'];

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
        'DS' => '06',
        'LC' => '07',
    ];

    /**
     * Define os nomes das carteiras para exibição no boleto
     *
     * @var array
     */
    protected $walletNames = ['101' => 'Cobrança Simples ECR', '102' => 'Cobrança Simples CSR'];

    /**
     * Define o valor do IOS - Seguradoras (Se 7% informar 7. Limitado a 9%) - Demais clientes usar 0 (zero)
     *
     * @var int
     */
    protected $ios = 0;

    /**
     * Variáveis adicionais.
     *
     * @var array
     */
    public $additionalVariables = [
        'esconde_uso_banco' => true,
    ];

    /**
     * Define o código da carteira (Com ou sem registro)
     *
     * @param  string $carteira
     * @return SlipAbstract
     * @throws Exception
     */
    public function setWallet($carteira): SlipAbstract
    {
        switch ($carteira) {
            case '1':
            case '5':
                $carteira = '101';
                break;
            case '4':
                $carteira = '102';
                break;
        }
        return parent::setWallet($carteira);
    }

    /**
     * Define o valor do IOS
     *
     * @param int $ios
     */
    public function setIos($ios)
    {
        $this->ios = $ios;
    }

    /**
     * Retorna o atual valor do IOS
     *
     * @return int
     */
    public function getIos()
    {
        return $this->ios;
    }

    /**
     * Seta dias para baixa automática
     *
     * @param int $automaticDrop
     * @return $this
     * @throws Exception
     */
    public function setAutomaticDropAfter(int $automaticDrop)
    {
        if ($this->getProtestAfter() > 0) {
            throw new Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        if (!in_array($automaticDrop, [15, 30])) {
            throw new Exception('O Banco Santander so aceita 15 ou 30 dias após o vencimento para baixa automática');
        }
        $automaticDrop = (int)$automaticDrop;
        $this->automaticDropAfter = $automaticDrop > 0 ? $automaticDrop : 0;
        return $this;
    }

    /**
     * Gera o Nosso Número.
     * Nota 3: Forma de cálculo do dígito de controle
     * Composição: NNNNNNN-D
     *
     * @return string
     */
    protected function generateOurNumber()
    {
        $ourNumber = $this->getNumber();
        return Util::numberFormatGeral($ourNumber, 7)
            . CheckDigitCalculation::santanderOurNumber($ourNumber);
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
        return $this->fieldFree = '9' . Util::numberFormatGeral($this->getAccount(), 7)
            . Util::numberFormatGeral($this->getOurNumber(), 13)
            . Util::numberFormatGeral($this->getIos(), 1)
            . Util::numberFormatGeral($this->getWallet(), 3);
    }
}
