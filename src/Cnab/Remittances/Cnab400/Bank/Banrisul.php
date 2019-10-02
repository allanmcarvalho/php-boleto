<?php


namespace PhpBoleto\Cnab\Remittances\Cnab400\Bank;

use Exception;
use PhpBoleto\Cnab\Remittances\Cnab400\RemittanceAbstract;
use PhpBoleto\Interfaces\Cnab\RemittanceInterface;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Tools\Util;

/**
 * Class Banrisul
 * @package PhpBoleto\CnabInterface\Remessa\Cnab400\Banco
 */
class Banrisul extends RemittanceAbstract implements RemittanceInterface
{
    const TIPO_COBRANCA_DIRETA = '04';
    const TIPO_COBRANCA_ESCRITURAL = '06';
    const TIPO_COBRANCA_CREDENCIADA = '08';
    const TIPO_TITULO_TERCEIROS = '09';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_PROTESTAR_XX = '09';
    const INSTRUCAO_DEVOLVER_XX = '15';
    const INSTRUCAO_MULTA_XX = '18';
    const INSTRUCAO_MULTA_FRACAO_XX = '20';
    const INSTRUCAO_NAO_PROTESTAR = '23';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_CANC_ABATIMENTO_CONCEDIDO = '05';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_CONTROLE_PARTICIPANTE = '07';
    const OCORRENCIA_ALT_SEU_NUMERO = '08';
    const OCORRENCIA_PEDIDO_PROTESTO = '09';
    const OCORRENCIA_SUSTAR_PROTESTO = '10';
    const OCORRENCIA_DISPENSAR_JUROS = '11';
    const OCORRENCIA_REEMBOLSO_TRANS = '12';
    const OCORRENCIA_REEMBOLSO_DEV = '13';
    const OCORRENCIA_ALT_NOME_END_SACADO = '14';
    const OCORRENCIA_ALT_PRAZO_PROTESTO = '16';
    const OCORRENCIA_PROTESTO_FALENCIA = '17';
    const OCORRENCIA_ALT_PAGADOR_NOME = '18';
    const OCORRENCIA_ALT_PAGADOR_END = '19';
    const OCORRENCIA_ALT_PAGADOR_CIDADE = '20';
    const OCORRENCIA_ALT_PAGADOR_CEP = '21';
    const OCORRENCIA_ACERTO_RATEIO_CREDITO = '68';
    const OCORRENCIA_CANC_RATEIO_CREDITO = '69';

    /**
     * Banrisul constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addRequiredField('clientCode');
    }

    /**
     * Valor total dos títulos
     *
     * @var int
     */
    private $titleTotal = 0;

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = SlipInterface::BANK_CODE_BANRISUL;

    /**
     * Define as carteiras disponíveis para este banco
     * 1 -> Cobrança Simples
     * 3 -> Cobrança Caucionada
     * 4 -> Cobrança em IGPM
     * 5 -> Cobrança Caucionada CGB Especial
     * 6 -> Cobrança Simples Seguradora
     * 7 -> Cobrança em UFIR
     * 8 -> Cobrança em IDTR
     * C -> Cobrança Vinculada
     * D -> Cobrança CSB
     * E -> Cobrança Caucionada Câmbio
     * F -> Cobrança Vendor
     * H -> Cobrança Caucionada Dólar
     * I -> Cobrança Caucionada Compror
     * K -> Cobrança Simples INCC-M
     * M -> Cobrança Partilhada
     * N -> Capital de Giro CGB ICM
     * R -> Desconto de Duplicata
     * S -> Vendor Eletrônico – Valor Final (Corrigido)
     * X -> Vendor BDL – Valor Inicial (Valor da NF)
     *
     * @var array
     */
    protected $wallets = ['1', '2', '3', '4', '5', '6', '7', '8', 'C', 'D', 'E', 'F', 'H', 'I', 'K', 'M', '9', 'R', 'S', 'X'];

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
     * Codigo do cliente junto ao banco.
     *
     * @var string
     */
    protected $clientCode;
    /**
     * Codigo do cliente office banking junto ao banco.
     *
     * @var string
     */
    protected $ClientCodeOfficeBanking;

    /**
     * Remessa em teste
     *
     * @var bool
     */
    protected $test = false;

    /**
     * Define se é teste
     *
     * @param bool $test
     * @return $this
     */

    public function setTest(bool $test)
    {
        $this->test = $test;
        return $this;
    }
    /**
     * Retorna se é com registro.
     *
     * @return bool
     */
    public function isTest()
    {
        return $this->test;
    }
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
     * @return Banrisul
     */
    public function setClientCode($clientCode)
    {
        $this->clientCode = $clientCode;

        return $this;
    }
    /**
     * Retorna o código do cliente office banking.
     *
     * @return mixed
     */
    public function getClientCodeOfficeBanking()
    {
        return $this->ClientCodeOfficeBanking;
    }

    /**
     * Seta o código do cliente office banking.
     *
     * @param mixed $officeBanking
     *
     * @return Banrisul
     */
    public function setClientCodeOfficeBanking($officeBanking)
    {
        $this->ClientCodeOfficeBanking = $officeBanking;

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function header()
    {
        $this->initiateHeader();

        $cod_servico = '';
        $tipo_processamento = '';
        $cod_cliente = '';
        if ($this->isCarteiraRSX()) {
            $cod_servico = $this->isTest() ? '8808' : '0808';
            $tipo_processamento = $this->isTest() ? 'X' : 'P';
            $cod_cliente = $this->getClientCodeOfficeBanking();
        }

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 26, '');
        $this->add(27, 39, Util::formatCnab('9', $this->getClientCode(), 13));
        $this->add(40, 46, '');
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiary()->getName(), 30));
        $this->add(77, 79, $this->getBankCode());
        $this->add(80, 87, Util::formatCnab('X', 'BANRISUL', 8));
        $this->add(88, 94, '');
        $this->add(95, 100, date('dmy'));
        $this->add(101, 109, '');
        $this->add(110, 113, Util::formatCnab('9', $cod_servico, 4));
        $this->add(114, 114, '');
        $this->add(115, 115, $tipo_processamento);
        $this->add(116, 116, '');
        $this->add(117, 126, Util::formatCnab('9', $cod_cliente, 10));
        $this->add(127, 394, '');
        $this->add(395, 400, Util::formatCnab('9', 1, 6));

        return $this;
    }

    /**
     * @param SlipInterface $slip
     *
     * @return bool
     * @throws Exception
     */
    public function addSlip(SlipInterface $slip)
    {
        $this->initiateDetail();

        $this->add(1, 1, 1);
        $this->add(2, 17, '');
        $this->add(18, 30, Util::formatCnab('9', $this->getClientCode(), 13, '0'));
        $this->add(31, 37, '');
        $this->add(38, 62, Util::formatCnab('X', $slip->getControlNumber(), 25));
        $this->add(63, 72, Util::formatCnab('9L', $slip->getOurNumber(), 10));
        $this->add(73, 104, '');
        $this->add(105, 107, '');
        $this->add(108, 108, Util::formatCnab('X', $this->getWalletNumber(), 1));
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
        $this->add(143, 147, '');
        $this->add(148, 149, $this->isCarteiraRSX() ? '' : self::TIPO_COBRANCA_CREDENCIADA);
        $this->add(150, 150, $slip->getAcceptance());
        $this->add(151, 156, $slip->getDocumentDate()->format('dmy'));
        $this->add(157, 158, self::INSTRUCAO_SEM);
        $this->add(159, 160, self::INSTRUCAO_SEM);
        if ($slip->getProtestAfter() > 0) {
            $this->add(157, 158, self::INSTRUCAO_PROTESTAR_XX);
        } elseif ($slip->getAutomaticDropAfter() > 0) {
            $this->add(157, 158, self::INSTRUCAO_DEVOLVER_XX);
        }
        if ($slip->getFine() > 0) {
            $this->add(159, 160, self::INSTRUCAO_MULTA_XX);
        }
        $juros = 0;
        if ($slip->getInterest() > 0) {
            $juros = Util::percent($slip->getValue(), $slip->getInterest()) / 30;
        }
        $this->add(161, 161, '0');
        $this->add(162, 173, Util::formatCnab('9', $juros, 12, 2));
        $this->add(174, 179, $slip->getDiscount() > 0 ? $slip->getDiscountDate()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $slip->getDiscount(), 13, 2));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
        $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
        $this->add(219, 220, strlen(Util::onlyNumbers($slip->getPayer()->getDocument())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $slip->getPayer()->getDocument(), 14));
        $this->add(235, 269, Util::formatCnab('X', $slip->getPayer()->getName(), 35));
        $this->add(270, 274, '');
        $this->add(275, 314, Util::formatCnab('X', $slip->getPayer()->getAddress(), 40));
        $this->add(315, 321, '');
        $this->add(322, 324, Util::formatCnab('9', $slip->getFine(), 3, 1));
        $this->add(325, 326, '00');
        $this->add(327, 334, Util::formatCnab('9L', $slip->getPayer()->getPostalCode(), 8));
        $this->add(335, 349, Util::formatCnab('X', $slip->getPayer()->getCity(), 15));
        $this->add(350, 351, Util::formatCnab('X', $slip->getPayer()->getStateUf(), 2));
        $this->add(352, 355, Util::formatCnab('9', 0, 3));
        $this->add(356, 357, '');
        $this->add(358, 369, '00');
        $this->add(370, 371, Util::formatCnab('9', $slip->getProtestAfter($slip->getAutomaticDropAfter()), 2));
        $this->add(372, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->registryCount + 1, 6));

        $this->titleTotal += $slip->getValue();

        return true;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function trailer()
    {
        $this->InitiateTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 27, '');
        $this->add(28, 40, Util::formatCnab('9', $this->titleTotal, 13, 2));
        $this->add(41, 394, '');
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }

    /**
     * Verifica se a carteira é uma das seguintes : R, S, X ou alguma a mais passada por parametro
     *
     * @param array $adicional
     *
     * @return bool
     */
    private function isCarteiraRSX(array $adicional = [])
    {
        return in_array(Util::upper($this->getWallet()), array_merge(['R', 'S', 'X'], $adicional));
    }
}
