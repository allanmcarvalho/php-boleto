<?php

namespace PhpBoleto\Cnab\Remittances\Cnab400\Bank;

use DateTimeInterface;
use Exception;
use PhpBoleto\Cnab\Remittances\Cnab400\RemittanceAbstract;
use PhpBoleto\Interfaces\Cnab\RemittanceInterface;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Tools\Util;

/**
 * Class Santander
 * @package PhpBoleto\CnabInterface\Remessa\Cnab400\Banco
 */
class Santander extends RemittanceAbstract implements RemittanceInterface
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_NOTA_SEGURO = '03';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_DUPLICATA_SERVICO = '06';
    const ESPECIE_LETRA_CAMBIO = '07';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_CONTROLE_PARTICIPANTE = '07';
    const OCORRENCIA_ALT_SEUNUMERO = '08';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '18';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_BAIXAR_APOS_VENC_15 = '02';
    const INSTRUCAO_BAIXAR_APOS_VENC_30 = '03';
    const INSTRUCAO_NAO_BAIXAR = '04';
    const INSTRUCAO_PROTESTAR = '06';
    const INSTRUCAO_NAO_PROTESTAR = '07';
    const INSTRUCAO_NAO_COBRAR_MORA = '08';

    /**
     * Santander constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addRequiredField('codigoCliente');
    }


    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = SlipInterface::BANK_CODE_SANTANDER;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $wallets = [101, 201];

    /**
     * Caracter de fim de linha
     *
     * @var string
     */
    protected $eolChar = "\r\n";

    /**
     * Caracter de fim de arquivo
     *
     * @var null
     */
    protected $endOfFileChar = "\r\n";

    /**
     * Código do cliente junto ao banco.
     *
     * @var string
     */
    protected $clientCode;

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
     * Seta o código do cliente.
     *
     * @param mixed $clientCode
     *
     * @return Santander
     */
    public function setClientCode($clientCode)
    {
        $this->clientCode = $clientCode;

        return $this;
    }

    /**
     * Retorna o código de transmissão.
     *
     * @return string
     * @throws Exception
     */
    public function getStreamingCode()
    {
        return Util::formatCnab('9', $this->getAgency(), 4)
            . Util::formatCnab('9', $this->getClientCode(), 8)
            . Util::formatCnab('9', Util::numberFormatGeral($this->getAccount(), 7), 8);
    }

    /**
     * Valor total dos titulos.
     *
     * @var float
     */
    private $total = 0;

    /**
     * @return $this|mixed
     * @throws Exception
     */
    protected function header()
    {
        $this->initiateHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('X', 'COBRANCA', 15));
        $this->add(27, 46, Util::formatCnab('9', $this->getStreamingCode(), 20));
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiary()->getName(), 30));
        $this->add(77, 79, $this->getBankCode());
        $this->add(80, 94, Util::formatCnab('X', 'SANTANDER', 15));
        $this->add(95, 100, date('dmy'));
        $this->add(101, 116, Util::formatCnab('9', '0', 16));
        $this->add(117, 391, '');
        $this->add(392, 394, '000');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param SlipInterface $slip
     * @return $this|mixed
     * @throws Exception
     */
    public function addSlip(SlipInterface $slip)
    {
        $this->initiateDetail();

        $this->total += $slip->getValue();

        $this->add(1, 1, '1');
        $this->add(2, 3, strlen(Util::onlyNumbers($this->getBeneficiary()->getDocument())) == 14 ? '02' : '01');
        $this->add(4, 17, Util::formatCnab('9L', $this->getBeneficiary()->getDocument(), 14));
        $this->add(18, 37, Util::formatCnab('9', $this->getStreamingCode(), 20));
        $this->add(38, 62, Util::formatCnab('X', $slip->getControlNumber(), 25)); // numero de controle
        $this->add(63, 70, Util::numberFormatGeral($slip->getOurNumber(), 8));
        $this->add(71, 76, '000000');
        $this->add(77, 77, '');
        $this->add(78, 78, ($slip->getFine() > 0 ? '4' : '0'));
        $this->add(79, 82, Util::formatCnab('9', $slip->getFine(), 4, 2));
        $this->add(83, 84, '00');
        $this->add(85, 97, Util::formatCnab('9', 0, 13, 2));
        $this->add(98, 101, '');

        if ($slip->getChargeInterestAfter() !== false) {
            /** @var DateTimeInterface $chargeInterestDate */
            $diffDays = $slip->getDueDate()->diff($slip->getChargeInterestAfter())->d;
            $chargeInterestDate = $slip->getDueDate()->copy();
            $chargeInterestDate->modify("+$diffDays days");
            $this->add(102, 107, $chargeInterestDate->format('dmy'));
        } else {
            $this->add(102, 107, '000000');
        }
        $this->add(108, 108, $this->getWalletNumber() > 200 ? '1' : '5');
        $this->add(109, 110, self::OCORRENCIA_REMESSA); // REGISTRO
        if ($slip->getStatus() == $slip::STATUS_DROP) {
            $this->add(109, 110, self::OCORRENCIA_PEDIDO_BAIXA); // BAIXA
        }
        if ($slip->getStatus() == $slip::STATUS_ALTER) {
            $this->add(109, 110, self::OCORRENCIA_ALT_VENCIMENTO); // ALTERAR VENCIMENTO
        }
        $this->add(111, 120, Util::formatCnab('X', $slip->getDocumentNumber(), 10));
        $this->add(121, 126, $slip->getDueDate()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $slip->getValue(), 13, 2));
        $this->add(140, 142, $this->getBankCode());
        $this->add(143, 147, '00000');
        $this->add(148, 149, $slip->getDocumentTypeCode());
        $this->add(150, 150, $slip->getAcceptance());
        $this->add(151, 156, $slip->getDocumentDate()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        if ($slip->getProtestAfter() > 0) {
            $this->add(157, 158, self::INSTRUCAO_PROTESTAR);
        } elseif ($slip->getAutomaticDropAfter() == 15) {
            $this->add(157, 158, self::INSTRUCAO_BAIXAR_APOS_VENC_15);
        } elseif ($slip->getAutomaticDropAfter() == 30) {
            $this->add(157, 158, self::INSTRUCAO_BAIXAR_APOS_VENC_30);
        }
        $juros = 0;
        if ($slip->getInterest() > 0) {
            $juros = Util::percent($slip->getValue(), $slip->getInterest()) / 30;
        }
        $this->add(161, 173, Util::formatCnab('9', $juros, 13, 2));
        $this->add(174, 179, $slip->getDiscount() > 0 ? $slip->getDiscountDate()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $slip->getDiscount(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($slip->getPayer()->getDocument())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $slip->getPayer()->getDocument(), 14));
        $this->add(235, 274, Util::formatCnab('X', $slip->getPayer()->getName(), 40));
        $this->add(275, 314, Util::formatCnab('X', $slip->getPayer()->getAddress(), 40));
        $this->add(315, 326, Util::formatCnab('X', $slip->getPayer()->getAddressDistrict(), 12));
        $this->add(327, 334, Util::formatCnab('9L', $slip->getPayer()->getPostalCode(), 8));
        $this->add(335, 349, Util::formatCnab('X', $slip->getPayer()->getCity(), 15));
        $this->add(350, 351, Util::formatCnab('X', $slip->getPayer()->getStateUf(), 2));
        $this->add(352, 381, Util::formatCnab('X', $slip->getGuarantor() ? $slip->getGuarantor()->getName() : '', 30));
        $this->add(382, 382, '');
        $this->add(383, 383, 'I');
        $this->add(384, 385, substr($this->getAccount(), -2));
        $this->add(386, 391, '');
        $this->add(392, 393, Util::formatCnab('9', $slip->getProtestAfter('0'), 2));
        $this->add(394, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->registryCount + 1, 6));

        return $this;
    }

    /**
     * @return $this|mixed
     * @throws Exception
     */
    protected function trailer()
    {
        $this->InitiateTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 7, Util::formatCnab('9', $this->getCount(), 6));
        $this->add(8, 20, Util::formatCnab('9', $this->total, 13, 2));
        $this->add(21, 394, Util::formatCnab('9', 0, 374));
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }
}
