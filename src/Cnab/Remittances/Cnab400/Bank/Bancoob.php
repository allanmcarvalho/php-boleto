<?php

namespace PhpBoleto\Cnab\Remittances\Cnab400\Bank;

use Exception;
use PhpBoleto\Cnab\Remittances\Cnab400\RemittanceAbstract;
use PhpBoleto\Interfaces\Cnab\RemittanceInterface;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Tools\CheckDigitCalculation;
use PhpBoleto\Tools\Util;

/**
 * Class Bancoob
 * @package PhpBoleto\CnabInterface\Remessa\Cnab400\Banks
 */
class Bancoob extends RemittanceAbstract implements RemittanceInterface
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_DUPLICATA_SERVICO = '12';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO_CONCEDIDO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_SEU_NUMERO = '08';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '10';
    const OCORRENCIA_DISPENSAR_JUROS = '11';
    const OCORRENCIA_ALT_PAGADOR = '12';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';
    const OCORRENCIA_BAIXAR = '34';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_COBRAR_JUROS = '01';
    const INSTRUCAO_NAO_PROTESTAR = '07';
    const INSTRUCAO_PROTESTAR = '09';
    const INSTRUCAO_PROTESTAR_VENC_03 = '03';
    const INSTRUCAO_PROTESTAR_VENC_04 = '04';
    const INSTRUCAO_PROTESTAR_VENC_05 = '05';
    const INSTRUCAO_PROTESTAR_VENC_15 = '15';
    const INSTRUCAO_PROTESTAR_VENC_20 = '20';
    const INSTRUCAO_CONCEDER_DESC_ATE = '22';
    const INSTRUCAO_DEVOLVER_APOS_15 = '42';
    const INSTRUCAO_DEVOLVER_APOS_30 = '43';

    /**
     * Bancoob constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addRequiredField('covenant');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = SlipInterface::BANK_CODE_BANCOOB;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $wallets = [1];

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
    protected $endOfFileChar = "";

    /**
     * Convênio com o banco
     *
     * @var string
     */
    protected $covenant;

    /**
     * @return mixed
     */
    public function getCovenant()
    {
        return $this->covenant;
    }

    /**
     * @param mixed $covenant
     *
     * @return Bancoob
     */
    public function setCovenant($covenant)
    {
        $this->covenant = ltrim($covenant, 0);

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function header()
    {
        $this->initiateHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, 'COBRANÇA      ');
        $this->add(27, 30, Util::formatCnab('9', $this->getAgency(), 4));
        $this->add(31, 31, CheckDigitCalculation::bancoobAgency($this->getAgency()));
        $this->add(32, 40, Util::formatCnab('9', $this->getCovenant(), 9));
        $this->add(41, 46, '');
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiary()->getName(), 30));
        $this->add(77, 79, $this->getBankCode());
        $this->add(80, 94, Util::formatCnab('X', 'BANCOOBCED', 15));
        $this->add(95, 100, date('dmy'));
        $this->add(101, 107, Util::formatCnab('9', $this->getRemittanceId(), 7));
        $this->add(108, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param SlipInterface $slip
     * @return $this
     * @throws Exception
     */
    public function addSlip(SlipInterface $slip)
    {
        $this->initiateDetail();

        $this->add(1, 1, 1);
        $this->add(2, 3, strlen(Util::onlyNumbers($this->getBeneficiary()->getDocument())) == 14 ? '02' : '01');
        $this->add(4, 17, Util::formatCnab('9L', $this->getBeneficiary()->getDocument(), 14));
        $this->add(18, 21, Util::formatCnab('9', $this->getAgency(), 4));
        $this->add(22, 22, CheckDigitCalculation::bancoobAgency($this->getAgency()));
        $this->add(23, 30, Util::formatCnab('9', $this->getAccount(), 8));
        $this->add(31, 31, Util::formatCnab('9', $this->getAccountCheckDigit(), 1));
        $this->add(32, 37, '000000');
        $this->add(38, 62, Util::formatCnab('X', $slip->getControlNumber(), 25)); // numero de controle
        $this->add(63, 74, Util::formatCnab('9', $slip->getOurNumber(), 12));
        $this->add(75, 76, '01'); //Numero da parcela - Não implementado
        $this->add(77, 78, '00'); //Grupo de valor
        $this->add(82, 82, '');
        $this->add(83, 85, '');
        $this->add(86, 88, '000');
        $this->add(89, 89, '0');
        $this->add(90, 94, '00000'); //Número do Contrato Garantia: Para Carteira 1 preencher "00000"
        $this->add(95, 95, '0'); //DV do contrato: Para Carteira 1 preencher "0"
        $this->add(96, 101, '000000');
        $this->add(102, 105, '');
        $this->add(106, 106, '2'); //Tipo de Emissão: 1 - Cooperativa 2 - Cliente
        $this->add(107, 108, Util::formatCnab('9', $this->getWallet(), 2));

        $this->add(109, 110, self::OCORRENCIA_REMESSA); // REGISTRO
        if ($slip->getStatus() == $slip::STATUS_DROP) {
            $this->add(109, 110, self::OCORRENCIA_PEDIDO_BAIXA); // BAIXA
        }

        $this->add(111, 120, Util::formatCnab('X', $slip->getDocumentNumber(), 10));
        $this->add(121, 126, $slip->getDueDate()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $slip->getValue(), 13, 2));
        $this->add(140, 142, $this->getBankCode());
        $this->add(143, 146, Util::formatCnab('9', $this->getAgency(), 4));
        $this->add(147, 147, CheckDigitCalculation::bancoobAgency($this->getAgency()));
        $this->add(148, 149, $slip->getDocumentTypeCode());

        $this->add(150, 150, ($slip->getAcceptance() == 'N' ? '0' : '1'));
        $this->add(151, 156, $slip->getDocumentDate()->format('dmy'));
        $this->add(157, 158, $slip->getStatus() == $slip::STATUS_DROP ? self::OCORRENCIA_BAIXAR : self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        $diasProtesto = '00';

        $juros = 0;

        if (($slip->getStatus() != $slip::STATUS_DROP) && ($slip->getProtestAfter() > 0)) {
            $const = sprintf('self::INSTRUCAO_PROTESTAR_VENC_%02s', $slip->getProtestAfter());

            if (defined($const)) {
                $this->add(157, 158, constant($const));
            } else {
                throw new Exception("A instrução para protesto em " . $slip->getProtestAfter() . " dias não existe no banco.");
            }

            if ($slip->getInterest() > 0) {
                $juros = Util::percent($slip->getValue(), $slip->getInterest()) / 30;
            }
        }

        $this->add(161, 166, Util::formatCnab('9', 0, 6, 4));
        $this->add(167, 172, Util::formatCnab('9', $juros, 6, 4));
        $this->add(173, 173, '2'); //Tipo de distribuição: 1 - Cooperativa 2 - Cliente
        $this->add(174, 179, $slip->getDiscount() > 0 ? $slip->getDiscountDate()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $slip->getDiscount(), 13, 2));
        $this->add(193, 193, '9');
        $this->add(194, 205, Util::formatCnab('9', 0, 12, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($slip->getPayer()->getDocument())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $slip->getPayer()->getDocument(), 14));
        $this->add(235, 271, Util::formatCnab('X', $slip->getPayer()->getName(), 37));
        $this->add(272, 274, '');
        $this->add(275, 314, Util::formatCnab('X', $slip->getPayer()->getAddress(), 40));
        $this->add(315, 326, Util::formatCnab('X', $slip->getPayer()->getAddressDistrict(), 12));
        $this->add(327, 334, Util::formatCnab('9L', $slip->getPayer()->getPostalCode(), 8));
        $this->add(335, 349, Util::formatCnab('X', $slip->getPayer()->getCity(), 15));
        $this->add(350, 351, Util::formatCnab('X', $slip->getPayer()->getStateUf(), 2));
        $this->add(352, 391, Util::formatCnab('X', $slip->getGuarantor() ? $slip->getGuarantor()->getName() : '', 40));
        $this->add(392, 393, $diasProtesto);
        $this->add(394, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->registryCount + 1, 6));

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function trailer()
    {
        $this->InitiateTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }
}
