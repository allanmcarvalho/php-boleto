<?php

namespace PhpBoleto\Slip\Bank;

use Exception;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\Tools\CheckDigitCalculation;
use PhpBoleto\Tools\Util;

/**
 * Class Sicredi
 * @package PhpBoleto\SlipInterface\Banco
 */
class Sicredi extends SlipAbstract implements SlipInterface
{
    /**
     * Sicredi constructor.
     * @param array $params
     * @throws Exception
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addRequiredFiled('byte', 'posto');
    }

    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $paymentPlace = 'Pagável preferencialmente nas cooperativas de crédito do sicredi';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = self::BANK_CODE_SICREDI;

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $wallets = ['1', '2', '3'];

    /**
     * Espécie do documento, código para remessa
     *
     * @var string
     */
    protected $documentTypes = [
        'DMI' => 'A', // Duplicata Mercantil por Indicação
        'DM' => 'A', // Duplicata Mercantil por Indicação
        'DR' => 'B', // Duplicata Rural
        'NP' => 'C', // Nota Promissória
        'NR' => 'D', // Nota Promissória Rural
        'NS' => 'E', // Nota de Seguros
        'RC' => 'G', // Recibo
        'LC' => 'H', // Letra de Câmbio
        'ND' => 'I', // Nota de Débito
        'DSI' => 'J', // Duplicata de Serviço por Indicação
        'OS' => 'K', // Outros
    ];

    /**
     * Se possui registro o boleto (tipo = 1 com registro e 3 sem registro)
     *
     * @var bool
     */
    protected $registry = true;

    /**
     * Código do posto do cliente no banco.
     *
     * @var int
     */
    protected $post;

    /**
     * Byte que compõe o nosso número.
     *
     * @var int
     */
    protected $byte = 2;

    /**
     * Define se possui ou não registro
     *
     * @param bool $registry
     * @return $this
     */
    public function setRegistry(bool $registry)
    {
        $this->registry = $registry;
        return $this;
    }

    /**
     * Retorna se é com registro.
     *
     * @return bool
     */
    public function isRegistry()
    {
        return $this->registry;
    }

    /**
     * Define o posto do cliente
     *
     * @param int $post
     * @return $this
     */
    public function setPost($post)
    {
        $this->post = $post;
        return $this;
    }

    /**
     * Retorna o posto do cliente
     *
     * @return int
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Define o byte
     *
     * @param int $byte
     *
     * @return $this
     * @throws Exception
     */
    public function setByte($byte)
    {
        if ($byte > 9) {
            throw new Exception('O byte deve ser compreendido entre 1 e 9');
        }
        $this->byte = $byte;
        return $this;
    }

    /**
     * Retorna o byte
     *
     * @return int
     */
    public function getByte()
    {
        return $this->byte;
    }

    /**
     * Retorna o campo Agência/Beneficiário do boleto
     *
     * @return string
     */
    public function getAgencyAndAccount(): string
    {
        return sprintf('%04s.%02s.%05s', $this->getAgency(), $this->getPost(), $this->getAccount());
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function generateOurNumber()
    {
        $year = $this->getDocumentDate()->format('y');
        $byte = $this->getByte();
        $slipNumber = Util::numberFormatGeral($this->getNumber(), 5);
        $ourNumber = $year . $byte . $slipNumber
            . CheckDigitCalculation::sicrediOurNumber($this->getAgency(), $this->getPost(), $this->getAccount(), $year, $byte, $slipNumber);
        return $ourNumber;
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getOurNumberCustom()
    {
        return Util::maskString($this->getOurNumber(), '##/######-#');
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

        $chargeType = $this->isRegistry() ? '1' : '3';
        $wallet = Util::numberFormatGeral($this->getWallet(), 1);
        $ourNumber = $this->getOurNumber();
        $agency = Util::numberFormatGeral($this->getAgency(), 4);
        $post = Util::numberFormatGeral($this->getPost(), 2);
        $account = Util::numberFormatGeral($this->getAccount(), 5);

        $this->fieldFree = $chargeType . $wallet . $ourNumber . $agency . $post . $account . '10';
        return $this->fieldFree .= Util::modulo11($this->fieldFree);
    }
}
