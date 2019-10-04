<?php

namespace PhpBoleto\Cnab\Remittances\Cnab400\Bank;

use Exception;
use PhpBoleto\Cnab\Remittances\Cnab400\RemittanceAbstract;
use PhpBoleto\Interfaces\Cnab\RemittanceInterface;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Tools\CheckDigitCalculation;
use PhpBoleto\Tools\Util;

/**
 * Class Bradesco
 * @package PhpBoleto\CnabInterface\Remessa\Cnab400\Banks
 */
class Bradesco extends RemittanceAbstract implements RemittanceInterface
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_NOTA_SEGURO = '03';
    const ESPECIE_COBRANCA_SERIADA = '04';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_LETRAS_CAMBIO = '10';
    const ESPECIE_NOTA_DEBITO = '11';
    const ESPECIE_DUPLICATA_SERVICO = '12';
    const ESPECIE_OUTROS = '99';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO_CONCEDIDO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_CONTROLE_PARTICIPANTE = '07';
    const OCORRENCIA_ALT_SEU_NUMERO = '08';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_SUSTAR_PROTESTO_BAIXAR_TITULO = '18';
    const OCORRENCIA_SUSTAR_PROTESTO_MANTER_TITULO = '19';
    const OCORRENCIA_TRANS_CESSAO_CREDITO_ID10 = '22';
    const OCORRENCIA_TRANS_CARTEIRAS = '23';
    const OCORRENCIA_DEVOLUCAO_TRANS_CARTEIRAS = '24';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';
    const OCORRENCIA_DESAGENDAMENTO_DEBITO_AUT = '35';
    const OCORRENCIA_ACERTO_RATEIO_CREDITO = '68';
    const OCORRENCIA_CANC_RATEIO_CREDITO = '69';


    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_PROTESTAR_FAMILIAR_XX = '05';
    const INSTRUCAO_PROTESTAR_XX = '06';
    const INSTRUCAO_NAO_COBRAR_JUROS = '08';
    const INSTRUCAO_NAO_RECEBER_APOS_VENC = '09';
    const INSTRUCAO_MULTA_10_APOS_VENC_4 = '10';
    const INSTRUCAO_NAO_RECEBER_APOS_VENC_8 = '11';
    const INSTRUCAO_COBRAR_ENCAR_APOS_5 = '12';
    const INSTRUCAO_COBRAR_ENCAR_APOS_10 = '13';
    const INSTRUCAO_COBRAR_ENCAR_APOS_15 = '14';
    const INSTRUCAO_CENCEDER_DESC_APOS_VENC = '15';
    const INSTRUCAO_DEVOLVER_XX = '18';

    /**
     * Bradesco constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addRequiredField('clientCode', 'remittanceId');
    }


    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = SlipInterface::BANK_CODE_BRADESCO;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $wallets = ['09', '28'];

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
     * @return mixed
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
     * @return Bradesco
     */
    public function setClientCode($clientCode)
    {
        $this->clientCode = $clientCode;

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
        $this->add(12, 26, Util::formatCnab('X', 'COBRANCA', 15));
        $this->add(27, 46, Util::formatCnab('9', $this->getClientCode(), 20));
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiary()->getName(), 30));
        $this->add(77, 79, $this->getBankCode());
        $this->add(80, 94, Util::formatCnab('X', 'Bradesco', 15));
        $this->add(95, 100, date('dmy'));
        $this->add(101, 108, '');
        $this->add(109, 110, 'MX');
        $this->add(111, 117, Util::formatCnab('9', $this->getRemittanceId(), 7));
        $this->add(118, 394, '');
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

        $beneficiario_id = Util::formatCnab('9', $this->getWalletNumber(), 3) .
            Util::formatCnab('9', $this->getAgency(), 5) .
            Util::formatCnab('9', $this->getAccount(), 7) .
            Util::formatCnab('9', $this->getAccountCheckDigit() ?: CheckDigitCalculation::bradescoAccount($this->getAccount()), 1);

        $this->add(1, 1, '1');
        $this->add(2, 6, '');
        $this->add(7, 7, '');
        $this->add(8, 12, '');
        $this->add(13, 19, '');
        $this->add(20, 20, '');
        $this->add(21, 37, Util::formatCnab('9', $beneficiario_id, 17));
        $this->add(38, 62, Util::formatCnab('X', $slip->getControlNumber(), 25)); // numero de controle
        $this->add(63, 65, $this->getBankCode());
        $this->add(66, 66, $slip->getFine() > 0 ? '2' : '0');
        $this->add(67, 70, Util::formatCnab('9', $slip->getFine() > 0 ? $slip->getFine() : '0', 4, 2));
        $this->add(71, 82, Util::formatCnab('9', $slip->getOurNumber(), 12));
        $this->add(83, 92, Util::formatCnab('9', 0, 10, 2));
        $this->add(93, 93, '2'); // 1 = Banks emite e Processa o registro. 2 = Cliente emite e o Banks somente processa o registro
        $this->add(94, 94, ''); // N= Não registra na cobrança. Diferente de N registra e emite SlipInterface.
        $this->add(95, 104, '');
        $this->add(105, 105, '');
        $this->add(106, 106, '2'); // 1 = emite aviso, e assume o endereço do Pagador constante do Archive-Remessa; 2 = não emite aviso;
        $this->add(107, 108, '');
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
        $this->add(140, 142, '000');
        $this->add(143, 147, '00000');
        $this->add(148, 149, $slip->getDocumentTypeCode());
        $this->add(150, 150, 'N');
        $this->add(151, 156, $slip->getDocumentDate()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        if ($slip->getProtestAfter() > 0) {
            $this->add(157, 158, self::INSTRUCAO_PROTESTAR_XX);
            $this->add(159, 160, Util::formatCnab('9', $slip->getProtestAfter(), 2));
        } elseif ($slip->getAutomaticDropAfter() > 0) {
            $this->add(157, 158, self::INSTRUCAO_DEVOLVER_XX);
            $this->add(159, 160, Util::formatCnab('9', $slip->getAutomaticDropAfter(), 2));
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
        $this->add(335, 394, Util::formatCnab('X', $slip->getGuarantor() ? $slip->getGuarantor()->getName() : '', 60));
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
