<?php

namespace PhpBoleto\Slip\Banco;

use PhpBoleto\Slip\SlipAbstract;
use PhpBoleto\CalculoDV;
use PhpBoleto\Interfaces\Slip\SlipInterface as BoletoContract;
use PhpBoleto\Util;

/**
 * Class Sicredi
 * @package PhpBoleto\SlipInterface\Banco
 */
class Sicredi extends SlipAbstract implements BoletoContract
{
    /**
     * Sicredi constructor.
     * @param array $params
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
     * Espécie do documento, coódigo para remessa
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
    protected $registro = true;

    /**
     * Código do posto do cliente no banco.
     *
     * @var int
     */
    protected $posto;

    /**
     * Byte que compoe o nosso número.
     *
     * @var int
     */
    protected $byte = 2;

    /**
     * Define se possui ou não registro
     *
     * @param  bool $registro
     * @return $this
     */
    public function setComRegistro(bool $registro)
    {
        $this->registro = $registro;
        return $this;
    }

    /**
     * Retorna se é com registro.
     *
     * @return bool
     */
    public function isComRegistro()
    {
        return $this->registro;
    }

    /**
     * Define o posto do cliente
     *
     * @param  int $posto
     * @return $this
     */
    public function setPosto($posto)
    {
        $this->posto = $posto;
        return $this;
    }

    /**
     * Retorna o posto do cliente
     *
     * @return int
     */
    public function getPosto()
    {
        return $this->posto;
    }

    /**
     * Define o byte
     *
     * @param  int $byte
     *
     * @return $this
     * @throws \Exception
     */
    public function setByte($byte)
    {
        if ($byte > 9) {
            throw new \Exception('O byte deve ser compreendido entre 1 e 9');
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
    public function getAgencyAndAccount()
    {
        return sprintf('%04s.%02s.%05s', $this->getAgency(), $this->getPosto(), $this->getAccount());
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function generateOurNumber()
    {
        $ano = $this->getDocumentDate()->format('y');
        $byte = $this->getByte();
        $numero_boleto = Util::numberFormatGeral($this->getNumber(), 5);
        $nossoNumero = $ano . $byte . $numero_boleto
            . CalculoDV::sicrediNossoNumero($this->getAgency(), $this->getPosto(), $this->getAccount(), $ano, $byte, $numero_boleto);
        return $nossoNumero;
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
     * @throws \Exception
     */
    protected function getFieldFree()
    {
        if ($this->fieldFree) {
            return $this->fieldFree;
        }

        $tipo_cobranca = $this->isComRegistro() ? '1' : '3';
        $carteira = Util::numberFormatGeral($this->getWallet(), 1);
        $nosso_numero = $this->getOurNumber();
        $agencia = Util::numberFormatGeral($this->getAgency(), 4);
        $posto = Util::numberFormatGeral($this->getPosto(), 2);
        $conta = Util::numberFormatGeral($this->getAccount(), 5);

        $this->fieldFree = $tipo_cobranca . $carteira . $nosso_numero . $agencia . $posto . $conta . '10';
        return $this->fieldFree .= Util::modulo11($this->fieldFree);
    }
}
