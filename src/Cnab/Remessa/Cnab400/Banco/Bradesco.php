<?php

namespace PhpBoleto\Cnab\Remessa\Cnab400\Banco;

use PhpBoleto\CalculoDV;
use PhpBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use PhpBoleto\Interfaces\Cnab\Remessa as RemessaContract;
use PhpBoleto\Interfaces\Slip\SlipInterface as BoletoContract;
use PhpBoleto\Util;

/**
 * Class Bradesco
 * @package PhpBoleto\CnabInterface\Remessa\Cnab400\Banco
 */
class Bradesco extends AbstractRemessa implements RemessaContract
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
        $this->addCampoObrigatorio('codigoCliente', 'idremessa');
    }


    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::BANK_CODE_BRADESCO;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = ['09', '28'];

    /**
     * Caracter de fim de linha
     *
     * @var string
     */
    protected $fimLinha = "\r\n";

    /**
     * Caracter de fim de arquivo
     *
     * @var null
     */
    protected $fimArquivo = "\r\n";

    /**
     * Codigo do cliente junto ao banco.
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Retorna o codigo do cliente.
     *
     * @return mixed
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * Seta o codigo do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return Bradesco
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * @return $this
     */
    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('X', 'COBRANCA', 15));
        $this->add(27, 46, Util::formatCnab('9', $this->getCodigoCliente(), 20));
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getName(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'Bradesco', 15));
        $this->add(95, 100, date('dmy'));
        $this->add(101, 108, '');
        $this->add(109, 110, 'MX');
        $this->add(111, 117, Util::formatCnab('9', $this->getIdremessa(), 7));
        $this->add(118, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param BoletoContract $boleto
     * @return $this
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();

        $beneficiario_id = Util::formatCnab('9', $this->getCarteiraNumero(), 3) .
            Util::formatCnab('9', $this->getAgencia(), 5) .
            Util::formatCnab('9', $this->getConta(), 7) .
            Util::formatCnab('9', $this->getContaDv() ?: CalculoDV::bradescoContaCorrente($this->getConta()), 1);

        $this->add(1, 1, '1');
        $this->add(2, 6, '');
        $this->add(7, 7, '');
        $this->add(8, 12, '');
        $this->add(13, 19, '');
        $this->add(20, 20, '');
        $this->add(21, 37, Util::formatCnab('9', $beneficiario_id, 17));
        $this->add(38, 62, Util::formatCnab('X', $boleto->getControlNumber(), 25)); // numero de controle
        $this->add(63, 65, $this->getCodigoBanco());
        $this->add(66, 66, $boleto->getFine() > 0 ? '2' : '0');
        $this->add(67, 70, Util::formatCnab('9', $boleto->getFine() > 0 ? $boleto->getFine() : '0', 4, 2));
        $this->add(71, 82, Util::formatCnab('9', $boleto->getOurNumber(), 12));
        $this->add(83, 92, Util::formatCnab('9', 0, 10, 2));
        $this->add(93, 93, '2'); // 1 = Banco emite e Processa o registro. 2 = Cliente emite e o Banco somente processa o registro
        $this->add(94, 94, ''); // N= Não registra na cobrança. Diferente de N registra e emite SlipInterface.
        $this->add(95, 104, '');
        $this->add(105, 105, '');
        $this->add(106, 106, '2'); // 1 = emite aviso, e assume o endereço do Pagador constante do Arquivo-Remessa; 2 = não emite aviso;
        $this->add(107, 108, '');
        $this->add(109, 110, self::OCORRENCIA_REMESSA); // REGISTRO
        if ($boleto->getStatus() == $boleto::STATUS_DROP) {
            $this->add(109, 110, self::OCORRENCIA_PEDIDO_BAIXA); // BAIXA
        }
        if ($boleto->getStatus() == $boleto::STATUS_ALTER) {
            $this->add(109, 110, self::OCORRENCIA_ALT_VENCIMENTO); // ALTERAR VENCIMENTO
        }
        $this->add(111, 120, Util::formatCnab('X', $boleto->getDocumentNumber(), 10));
        $this->add(121, 126, $boleto->getDueDate()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $boleto->getValue(), 13, 2));
        $this->add(140, 142, '000');
        $this->add(143, 147, '00000');
        $this->add(148, 149, $boleto->getDocumentTypeCode());
        $this->add(150, 150, 'N');
        $this->add(151, 156, $boleto->getDocumentDate()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        if ($boleto->getProtestAfter() > 0) {
            $this->add(157, 158, self::INSTRUCAO_PROTESTAR_XX);
            $this->add(159, 160, Util::formatCnab('9', $boleto->getProtestAfter(), 2));
        } elseif ($boleto->getAutomaticDropAfter() > 0) {
            $this->add(157, 158, self::INSTRUCAO_DEVOLVER_XX);
            $this->add(159, 160, Util::formatCnab('9', $boleto->getAutomaticDropAfter(), 2));
        }
        $juros = 0;
        if ($boleto->getInterest() > 0) {
            $juros = Util::percent($boleto->getValue(), $boleto->getInterest()) / 30;
        }
        $this->add(161, 173, Util::formatCnab('9', $juros, 13, 2));
        $this->add(174, 179, $boleto->getDiscount() > 0 ? $boleto->getDiscountDate()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDiscount(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPayer()->getDocument())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $boleto->getPayer()->getDocument(), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPayer()->getName(), 40));
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPayer()->getAddress(), 40));
        $this->add(315, 326, Util::formatCnab('X', $boleto->getPayer()->getAddressDistrict(), 12));
        $this->add(327, 334, Util::formatCnab('9L', $boleto->getPayer()->getPostalCode(), 8));
        $this->add(335, 394, Util::formatCnab('X', $boleto->getGuarantor() ? $boleto->getGuarantor()->getName() : '', 60));
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));

        return $this;
    }

    /**
     * @return $this
     */
    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }
}
