<?php

namespace PhpBoleto\Cnab\Remittances\Cnab400\Bank;

use PhpBoleto\Cnab\Remittances\Cnab400\AbstractRemessa;
use PhpBoleto\Interfaces\Cnab\Remessa as RemessaContract;
use PhpBoleto\Interfaces\Slip\SlipInterface as BoletoContract;
use PhpBoleto\Util;

/**
 * Class Caixa
 * @package PhpBoleto\CnabInterface\Remessa\Cnab400\Banco
 */
class Caixa  extends AbstractRemessa implements RemessaContract
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_DUPLICATA_SERVICO = '03';
    const SPECIE_NOTA_SEGURO = '05';
    const ESPECIE_LETRAS_CAMBIO = '06';
    const ESPECIE_OUTROS = '09';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '03';
    const OCORRENCIA_CANC_ABATIMENTO = '04';
    const OCORRENCIA_ALT_VENCIMENTO = '05';
    const OCORRENCIA_ALT_USO_EMPRESA = '06';
    const OCORRENCIA_ALT_PRAZO_PROTESTO = '07';
    const OCORRENCIA_ALT_PRAZO_DEVOLUCAO = '08';
    const OCORRENCIA_ALT_OUTROS_DADOS = '09';
    const OCORRENCIA_ALT_OUTROS_DADOS_EMISSAO_BOLETO = '10';
    const OCORRENCIA_ALT_PROTESTO_DEVOLUCAO = '11';
    const OCORRENCIA_ALT_DEVOLUCAO_PROTESTO = '12';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_PROTESTAR_VENC_XX = '01';
    const INSTRUCAO_DEVOLVER_VENC_XX = '02';

    /**
     * Caixa constructor.
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
    protected $codigoBanco = BoletoContract::BANK_CODE_CEF;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = ['RG'];

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
     * @return string
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * Retorna o numero da carteira, deve ser override em casos de carteira de letras
     *
     * @return string
     */
    public function getCarteiraNumero()
    {
        if ($this->getCarteira() == 'SR') {
            return '02';
        }
        return '01';
    }

    /**
     * Seta o codigo do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return Caixa
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
        $this->add(27, 30, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(31, 36, Util::formatCnab('9', $this->getCodigoCliente(), 6));
        $this->add(37, 46, '');
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getName(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 94, Util::formatCnab('X', 'C ECON FEDERAL', 15));
        $this->add(95, 100, date('dmy'));
        $this->add(101, 389, '');
        $this->add(390, 394, Util::formatCnab('9', $this->getIdremessa(), 5));
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

        $this->add(1, 1, '1');
        $this->add(2, 3, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocument())) == 14 ? '02' : '01');
        $this->add(4, 17, Util::formatCnab('9L', $this->getBeneficiario()->getDocument(), 14));
        $this->add(18, 21, Util::formatCnab('9', $this->getAgencia(), 4));
        $this->add(22, 27, Util::formatCnab('9', $this->getCodigoCliente(), 6));
        $this->add(28, 28, '2'); // ‘1’ = Banco Emite ‘2’ = Cliente Emite
        $this->add(29, 29, '0'); // ‘0’ = Postagem pelo Beneficiário ‘1’ = Pagador via Correio ‘2’ = Beneficiário via Agência CAIXA ‘3’ = Pagador via e-mail
        $this->add(30, 31, '00');
        $this->add(32, 56, Util::formatCnab('X', $boleto->getControlNumber(), 25)); // numero de controle
        $this->add(57, 73, Util::formatCnab('9', $boleto->getOurNumber(), 17));
        $this->add(74, 76, '');
        $this->add(77, 106, '');
        $this->add(107, 108, Util::formatCnab('9', $this->getCarteiraNumero(), 2));
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
        $this->add(143, 147, '00000');
        $this->add(148, 149, $boleto->getDocumentTypeCode());
        $this->add(150, 150, $boleto->getAcceptance());
        $this->add(151, 156, $boleto->getDocumentDate()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        if ($boleto->getProtestAfter() > 0) {
            $this->add(157, 158, self::INSTRUCAO_PROTESTAR_VENC_XX);
        } elseif ($boleto->getAutomaticDropAfter() > 0) {
            $this->add(157, 158, self::INSTRUCAO_DEVOLVER_VENC_XX);
        }
        $juros = 0;
        if ($boleto->getInterest() > 0) {
            $juros = Util::percent($boleto->getValue(), $boleto->getInterest())/30;
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
        $this->add(335, 349, Util::formatCnab('X', $boleto->getPayer()->getCity(), 15));
        $this->add(350, 351, Util::formatCnab('X', $boleto->getPayer()->getStateUf(), 2));
        $this->add(352, 357, $boleto->getChargeInterestAfter() === false ? '000000' : $boleto->getDueDate()->copy()->addDays($boleto->getChargeInterestAfter())->format('dmy'));
        $this->add(358, 367, Util::formatCnab('9', Util::percent($boleto->getValue(), $boleto->getFine()), 10, 2));
        $this->add(368, 389, Util::formatCnab('X', $boleto->getGuarantor() ? $boleto->getGuarantor()->getName() : '', 22));
        $this->add(390, 391, '00');
        $this->add(392, 393, Util::formatCnab('9', $boleto->getProtestAfter($boleto->getAutomaticDropAfter()), 2));
        // Código da Moeda - Código adotado para identificar a moeda referenciada no Título. Informar fixo: ‘1’ = REAL
        $this->add(394, 394, Util::formatCnab('9', 1, 1));
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
