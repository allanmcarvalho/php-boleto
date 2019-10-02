<?php

namespace PhpBoleto\Cnab\Remittances\Cnab400\Bank;

use PhpBoleto\CalculoDV;
use PhpBoleto\Cnab\Remittances\Cnab400\AbstractRemessa;
use PhpBoleto\Interfaces\Cnab\Remessa as RemessaContract;
use PhpBoleto\Interfaces\Slip\SlipInterface as BoletoContract;
use PhpBoleto\Util;

/**
 * Class Bb
 * @package PhpBoleto\CnabInterface\Remessa\Cnab400\Banco
 */
class Bb extends AbstractRemessa implements RemessaContract
{
    const TIPO_COBRANCA_DESCONTADA = '04DSC';
    const TIPO_COBRANCA_VENDOR = '08VDR';
    const TIPO_COBRANCA_VINCULADA = '02VIN';
    const TIPO_COBRANCA_SIMPLES = '';

    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_NOTA_SEGURO = '03';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_LETRAS_CAMBIO = '08';
    const ESPECIE_WARRANT = '09';
    const ESPECIE_CHEQUE = '10';
    const ESPECIE_NOTA_DEBITO = '13';
    const ESPECIE_DUPLICATA_SERVICO = '12';
    const ESPECIE_APOLICE_SEGURO = '15';
    const ESPECIE_DIV_ATV_UNIAO = '25';
    const ESPECIE_DIV_ATV_ESTADO = '26';
    const ESPECIE_DIV_ATV_MUNICIPIO = '27';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_PEDIDO_DEBITO = '03';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO_CONCEDIDO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_CONTROLE_PARTICIPANTE = '07';
    const OCORRENCIA_ALT_SEU_NUMERO = '08';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '10';
    const OCORRENCIA_DISPENSAR_JUROS = '11';
    const OCORRENCIA_ALT_NOME_END_SACADO = '12';
    const OCORRENCIA_CONCEDER_DESC = '31';
    const OCORRENCIA_NAO_CONCEDER_DESC = '32';
    const OCORRENCIA_RETIFICAR_DESC = '33';
    const OCORRENCIA_ALT_DATA_DESC = '34';
    const OCORRENCIA_COBRAR_MULTA = '35';
    const OCORRENCIA_DISPENSAR_MULTA = '36';
    const OCORRENCIA_DISPOENSAR_INDEXADOR = '37';
    const OCORRENCIA_DISPENSAR_LIMITE_REC = '38';
    const OCORRENCIA_ALT_LIMITE_REC = '39';
    const OCORRENCIA_ALT_MODALIDADE = '40';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_COBRAR_JUROS = '01';
    const INSTRUCAO_NAO_PROTESTAR = '07';
    const INSTRUCAO_PROTESTAR = '09';
    const INSTRUCAO_PROTESTAR_VENC_03 = '03';
    const INSTRUCAO_PROTESTAR_VENC_04 = '04';
    const INSTRUCAO_PROTESTAR_VENC_05 = '05';
    const INSTRUCAO_PROTESTAR_VENC_XX = '06';
    const INSTRUCAO_PROTESTAR_VENC_15 = '15';
    const INSTRUCAO_PROTESTAR_VENC_20 = '20';
    const INSTRUCAO_PROTESTAR_VENC_25 = '25';
    const INSTRUCAO_PROTESTAR_VENC_30 = '30';
    const INSTRUCAO_PROTESTAR_VENC_45 = '45';
    const INSTRUCAO_CONCEDER_DESC_ATE = '22';
    const INSTRUCAO_DEVOLVER = '42';
    const INSTRUCAO_BAIXAR = '44';
    const INSTRUCAO_ENTREGAR_SACADO_PAGAMENTO = '46';

    /**
     * Bb constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('convenio', 'convenioLider');
    }

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::BANK_CODE_BB;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = [11, 12, 17, 31, 51];

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
     * Convenio com o banco
     *
     * @var string
     */
    protected $convenio;

    /**
     * Convenio lider com o banco
     *
     * @var string
     */
    protected $convenioLider;

    /**
     * Variação da carteira
     *
     * @var string
     */
    protected $variacaoCarteira;

    /**
     * @return mixed
     */
    public function getConvenio()
    {
        return $this->convenio;
    }

    /**
     * @param mixed $convenio
     *
     * @return Bb
     */
    public function setConvenio($convenio)
    {
        $this->convenio = ltrim($convenio, 0);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConvenioLider()
    {
        return $this->convenioLider ? $this->convenioLider : $this->getConvenio();
    }

    /**
     * @param mixed $convenioLider
     *
     * @return Bb
     */
    public function setConvenioLider($convenioLider)
    {
        $this->convenioLider = $convenioLider;

        return $this;
    }

    /**
     * Retorna variação da carteira
     *
     * @return string
     */
    public function getVariacaoCarteira()
    {
        return $this->variacaoCarteira;
    }

    /**
     * Seta a variação da carteira
     *
     * @param string $variacaoCarteira
     *
     * @return Bb
     */
    public function setVariacaoCarteira($variacaoCarteira)
    {
        $this->variacaoCarteira = $variacaoCarteira;

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
        $this->add(12, 19, Util::formatCnab('X', 'COBRANCA', 8));
        $this->add(20, 26, '');
        $this->add(27, 30, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(31, 31, CalculoDV::bbAgencia($this->getAgencia()));
        $this->add(32, 39, Util::formatCnab('9', $this->getConta(), 8));
        $this->add(40, 40, $this->getContaDv() ?: CalculoDV::bbContaCorrente($this->getConta()));
        $this->add(41, 46, '000000');
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'BANCODOBRASIL', 15));
        $this->add(95, 100, date('dmy'));
        $this->add(101, 107, Util::formatCnab('9', $this->getIdremessa(), 7));
        $this->add(108, 129, '');
        $this->add(130, 136, Util::formatCnab('9', $this->getConvenioLider(), 7));
        $this->add(137, 394, '');
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

        $this->add(1, 1, 7);
        $this->add(2, 3, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocument())) == 14 ? '02' : '01');
        $this->add(4, 17, Util::formatCnab('9L', $this->getBeneficiario()->getDocument(), 14));
        $this->add(18, 21, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(22, 22, CalculoDV::bbAgencia($this->getAgencia()));
        $this->add(23, 30, Util::formatCnab('9', $this->getConta(), 8));
        $this->add(31, 31, $this->getContaDv() ?: CalculoDV::bbContaCorrente($this->getConta()));
        $this->add(32, 38, Util::formatCnab('9', $this->getConvenio(), 7));
        $this->add(39, 63, Util::formatCnab('X', $boleto->getControlNumber(), 25)); // numero de controle
        $this->add(64, 80, $boleto->getOurNumber());
        $this->add(81, 82, '00');
        $this->add(83, 84, '00');
        $this->add(85, 87, '');
        $this->add(88, 88, ($boleto->getGuarantor() ? 'A' : ''));
        $this->add(89, 91, '');
        $this->add(92, 94, Util::formatCnab('9', $this->getVariacaoCarteira(), 3));
        $this->add(95, 95, '0');
        $this->add(96, 101, '000000');
        $this->add(102, 106, '');
        $this->add(107, 108, $this->getCarteiraNumero());
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
        $this->add(140, 142, $this->getCodigoBanco());
        $this->add(143, 146, '0000');
        $this->add(147, 147, '');
        $this->add(148, 149, $boleto->getDocumentTypeCode());
        $this->add(150, 150, $boleto->getAcceptance());
        $this->add(151, 156, $boleto->getDocumentDate()->format('dmy'));
        $this->add(157, 158, $boleto->getStatus() == $boleto::STATUS_DROP ? self::INSTRUCAO_BAIXAR : self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        $diasProtesto = '00';
        $const = sprintf('self::INSTRUCAO_PROTESTAR_VENC_%02s', $boleto->getProtestAfter());

        $juros = 0;

        if ($boleto->getStatus() != $boleto::STATUS_DROP) {
            if (defined($const)) {
                $this->add(157, 158, constant($const));
            } else {
                $this->add(157, 158, self::INSTRUCAO_PROTESTAR_VENC_XX);
                $diasProtesto = Util::formatCnab('9', $boleto->getProtestAfter(), 2, 0);
            }

            if ($boleto->getInterest() > 0) {
                $juros = Util::percent($boleto->getValue(), $boleto->getInterest()) / 30;
            }
        }

        $this->add(161, 173, Util::formatCnab('9', $juros, 13, 2));
        $this->add(174, 179, $boleto->getDiscount() > 0 ? $boleto->getDiscountDate()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDiscount(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPayer()->getDocument())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $boleto->getPayer()->getDocument(), 14));
        $this->add(235, 271, Util::formatCnab('X', $boleto->getPayer()->getName(), 37));
        $this->add(272, 274, '');
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPayer()->getAddress(), 40));
        $this->add(315, 326, Util::formatCnab('X', $boleto->getPayer()->getAddressDistrict(), 12));
        $this->add(327, 334, Util::formatCnab('9L', $boleto->getPayer()->getPostalCode(), 8));
        $this->add(335, 349, Util::formatCnab('X', $boleto->getPayer()->getCity(), 15));
        $this->add(350, 351, Util::formatCnab('X', $boleto->getPayer()->getStateUf(), 2));
        $this->add(352, 391, Util::formatCnab('X', $boleto->getGuarantor() ? $boleto->getGuarantor()->getName() : '', 40));
        $this->add(392, 393, $diasProtesto);
        $this->add(394, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));

        if ($boleto->getFine() > 0) {
            $this->iniciaDetalhe();

            $this->add(1, 1, 5);
            $this->add(2, 3, 99);
            $this->add(4, 4, 2);
            $this->add(5, 10, $boleto->getChargeInterestAfter() === false ? '000000' : $boleto->getDueDate()->copy()->addDays($boleto->getChargeInterestAfter())->format('dmy'));
            $this->add(11, 22, Util::formatCnab('9', $boleto->getFine(), 12, 2));
            $this->add(23, 394, '');
            $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));
        }
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
