<?php

namespace PhpBoleto\Cnab\Remittances\Cnab240\Bank;

use Exception;
use PhpBoleto\Cnab\Remittances\Cnab240\RemittanceAbstract;
use PhpBoleto\Interfaces\Cnab\RemittanceInterface;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Tools\CheckDigitCalculation;
use PhpBoleto\Tools\Util;

/**
 * Class Bancoob
 * @package PhpBoleto\CnabInterface\Remessa\Cnab240\Banks
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
     * Valor Total dos Títulos
     *
     * @var float
     */
    protected $titleTotalAmount = 0;

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
     * Convenio com o banco
     *
     * @var string
     */
    protected $covenant;

    /**
     * Sequência Segmento
     *
     * @var string
     */
    protected $followingSequence = 0;

    /**
     * Quantidade de registros do lote.
     */
    private $RegistryLotAmount;

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
     * Código do cliente junto ao banco.
     *
     * @var string
     */
    protected $clientCode;

    /**
     * Retorna o codigo do cliente.
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
     * @return Bancoob
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
        return Util::formatCnab('9', $this->getAgency(), 5)
            . CheckDigitCalculation::banrisulAgency($this->getAgency())
            . Util::formatCnab('9', $this->getAccount(), 12)
            . Util::formatCnab('9', $this->getAccountCheckDigit(), 1);
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function header()
    {
        $this->initiateHeader();

        $this->add(1, 3, $this->getBankCode());
        $this->add(4, 7, '0000');
        $this->add(8, 8, '0');
        $this->add(9, 17, '');
        $this->add(18, 18, strlen(Util::onlyNumbers($this->getBeneficiary()->getDocument())) == 14 ? '2' : '1'); // Tipo de inscrição da empresa
        $this->add(19, 32, Util::formatCnab('9L', $this->getBeneficiary()->getNameAndDocument(), 14));
        $this->add(33, 52, '');
        $this->add(53, 57, Util::formatCnab('9', $this->getAgency(), 5));
        $this->add(58, 58, CheckDigitCalculation::banrisulAgency($this->getAgency()));
        $this->add(59, 70, Util::formatCnab('9', $this->getAccount(), 12));
        $this->add(71, 71, Util::formatCnab('9', $this->getAccountCheckDigit(), 1));
        $this->add(72, 72, '0');
        $this->add(73, 102, Util::formatCnab('X', $this->getBeneficiary()->getName(), 30));
        $this->add(103, 132, 'SICOOB');
        $this->add(133, 142, '');
        $this->add(143, 143, '1');
        $this->add(144, 151, date('dmY'));
        $this->add(152, 157, date('His'));
        $this->add(158, 163, Util::formatCnab('9', $this->getRemittanceId(), 6));
        $this->add(164, 166, '081');
        $this->add(167, 171, '00000');
        $this->add(172, 191, '');
        $this->add(192, 211, '');
        $this->add(212, 240, '');

        return $this;
    }

    public function addSlip(SlipInterface $slip, $nSequentialLote = null)
    {

        $this->followingSequence++;
        $this->segmentP($nSequentialLote + $nSequentialLote + $this->followingSequence, $slip);
        $this->followingSequence++;
        $this->segmentoQ($nSequentialLote + $nSequentialLote + $this->followingSequence, $slip);
        $this->followingSequence++;
        $this->segmentoR($nSequentialLote + $nSequentialLote + $this->followingSequence, $slip);
        $this->followingSequence++;
        $this->segmentS($nSequentialLote + $nSequentialLote + $this->followingSequence, $slip);

        return $this;
    }

    /**
     * @param integer $nSequentialLot
     * @param SlipInterface $slip
     *
     * @return $this
     * @throws Exception
     */
    protected function segmentP($nSequentialLot, SlipInterface $slip)
    {
        $this->initiateDetail();
        $this->add(1, 3, Util::onlyNumbers($this->getBankCode())); //Código do Banks
        $this->add(4, 7, '0001'); // Numero do lote remessa
        $this->add(8, 8, '3'); // Numero do lote remessa
        $this->add(9, 13, Util::formatCnab(9, $nSequentialLot, 5)); // Nº sequencial do registro de lote
        $this->add(14, 14, Util::formatCnab('9', 'P', 1)); // Nº sequencial do registro de lote
        $this->add(15, 15, ''); // Reservado (Uso Banks)
        $this->add(16, 17, '01'); // Código de movimento remessa
        $this->add(18, 22, Util::formatCnab(9, $this->getAgency(), 5)); // Agência do cedente
        $this->add(23, 23, Util::formatCnab(9, '', 1)); // Digito verificador da Agência do cedente
        $this->add(24, 35, Util::formatCnab(9, $this->getAccount(), 12)); // Numero da conta corrente
        $this->add(36, 36, Util::formatCnab('9', $this->getAccountCheckDigit(), 1));
        $this->add(37, 37, ''); // Reservado (Uso Banks)

        $this->add(38, 47, Util::formatCnab(9, $slip->getOurNumber(), 10));
        $this->add(48, 49, Util::formatCnab(9, $slip->getQuota(), 2));
        $this->add(50, 51, '01');
        $this->add(52, 52, '1');
        $this->add(53, 57, '');

        $this->add(58, 58, $this->getWallet()); // Tipo de Cobrança

        $this->add(59, 59, '0'); // Forma de Cadastramento
        $this->add(60, 60, ''); // Tipo de documento
        $this->add(61, 61, '2'); // Reservado (Uso Banks)
        $this->add(62, 62, '2'); // Reservado (Uso Banks)
        //
        $this->add(63, 77, Util::formatCnab('X', $slip->getDocumentNumber(), 15)); // Seu Número
        $this->add(78, 85, $slip->getDueDate()->format('dmY')); // Data de vencimento do título
        $this->add(86, 100, Util::formatCnab(9, $slip->getValue(), 15, 2)); // Valor nominal do título
        $this->add(101, 105, Util::formatCnab(9, 0, 5)); //Agência encarregada da cobrança
        $this->add(106, 106, '');
        $this->add(107, 108, '02'); //Espécie do título
        $this->add(109, 109, Util::formatCnab('9', 'N', 1)); //Identif. de título Aceito/Não Aceito
        $this->add(110, 117, date('dmY')); //Data da emissão do título

        $juros = 0;
        if ($slip->getInterest() > 0) {
            $juros = Util::percent($slip->getValue(), $slip->getInterest()) / 30;
        }
        $this->add(118, 118, 1); //Código do juros de mora - 1 = Valor fixo ate a data informada – Informar o valor no campo “valor de desconto a ser concedido”.
        $this->add(119, 126, Util::formatCnab(9, $slip->getDueDate()->format('dmY'), 8)); //Data do juros de mora / data de vencimento do titulo
        $this->add(127, 141, Util::formatCnab(9, $juros, 15, 2)); //Valor da mora/dia ou Taxa mensal
        $this->add(142, 142, '0');
        $this->add(143, 150, '00000000');
        $this->add(151, 165, Util::formatCnab(9, $slip->getDiscount(), 15, 2)); //Valor ou Percentual do desconto concedido //TODO
        $this->add(166, 180, Util::formatCnab(9, 0, 15, 2)); //Valor do IOF a ser recolhido
        $this->add(181, 195, Util::formatCnab(9, 0, 15, 2)); //Valor do abatimento
        $this->add(196, 220, Util::formatCnab('X', $slip->getDocumentNumber(), 25)); //Identificação do título na empresa
        $this->add(221, 221, Util::formatCnab(9, 1, 1)); //Código para protesto
        $this->add(222, 223, Util::formatCnab(9, 0, 2)); //Número de dias para protesto
        $this->add(224, 224, Util::formatCnab(9, 0, 1)); //Código para Baixa/Devolução
        $this->add(225, 227, '');
        $this->add(228, 229, '09'); // Código da moeda
        $this->add(230, 239, '0000000000');
        $this->add(240, 240, ''); // Reservado (Uso Banks)

        $this->titleTotalAmount += $slip->getValue();

        return $this;
    }

    /**
     * @param integer $nSequentialLot
     * @param SlipInterface $slip
     *
     * @return Bancoob
     * @throws Exception
     */
    public function segmentoQ($nSequentialLot, SlipInterface $slip)
    {
        $this->RegistryLotAmount = $nSequentialLot;
        $this->initiateDetail();

        $this->add(1, 3, Util::onlyNumbers($this->getBankCode())); //Código do Banks
        $this->add(4, 7, '0001'); // Numero do lote remessa
        $this->add(8, 8, '3'); // Numero do lote remessa
        $this->add(9, 13, Util::formatCnab(9, $nSequentialLot, 5)); // Nº sequencial do registro de lote
        $this->add(14, 14, Util::formatCnab('9', 'Q', 1)); // Nº sequencial do registro de lote
        $this->add(15, 15, ''); // Reservado (Uso Banks)
        $this->add(16, 17, '01'); // Código de movimento remessa
        $this->add(18, 18, strlen(Util::onlyNumbers($slip->getPayer()->getDocument())) == 14 ? '2' : '1'); // Tipo de inscrição do sacado
        $this->add(19, 33, Util::formatCnab(9, Util::onlyNumbers($slip->getPayer()->getDocument()), 15)); // Número de inscrição do sacado
        $this->add(34, 73, Util::formatCnab('X', $slip->getPayer()->getName(), 40)); // Nome do pagador/Sacado
        $this->add(74, 113, Util::formatCnab('X', $slip->getPayer()->getAddress(), 40)); // Endereço do pagador/Sacado
        $this->add(114, 128, Util::formatCnab('X', $slip->getPayer()->getAddressDistrict(), 15)); // Bairro do pagador/Sacado
        $this->add(129, 133, Util::formatCnab(9, Util::onlyNumbers($slip->getPayer()->getPostalCode()), 5)); // CEP do pagador/Sacado
        $this->add(134, 136, Util::formatCnab(9, Util::onlyNumbers(substr($slip->getPayer()->getPostalCode(), 6, 9)), 3)); //SUFIXO do cep do pagador/Sacado
        $this->add(137, 151, Util::formatCnab('X', $slip->getPayer()->getCity(), 15)); // cidade do sacado
        $this->add(152, 153, Util::formatCnab('X', $slip->getPayer()->getStateUf(), 2)); // Uf do sacado
        $this->add(154, 154, strlen(Util::onlyNumbers($slip->getPayer()->getDocument())) == 14 ? '2' : '1'); // Tipo de inscrição do sacado
        $this->add(155, 169, Util::formatCnab(9, Util::onlyNumbers($slip->getPayer()->getDocument()), 15)); // Tipo de inscrição do sacado
        $this->add(170, 209, Util::formatCnab('X', $slip->getPayer()->getName(), 40)); // Nome do Sacador
        $this->add(210, 212, '000'); // Identificador de carne 000 - Não possui, 001 - Possui Carné
        $this->add(213, 232, '');
        $this->add(233, 240, '');

        return $this;
    }

    /**
     * @param integer $nSequencialLote
     * @param SlipInterface $slip
     *
     * @return Bancoob
     * @throws Exception
     */
    public function segmentoR($nSequencialLote, SlipInterface $slip)
    {
        $this->RegistryLotAmount = $nSequencialLote;
        $this->initiateDetail();

        $this->add(1, 3, Util::onlyNumbers($this->getBankCode())); //Código do Banks
        $this->add(4, 7, '0001'); // Numero do lote remessa
        $this->add(8, 8, '3'); // Numero do lote remessa
        $this->add(9, 13, Util::formatCnab(9, $nSequencialLote, 5)); // Nº sequencial do registro de lote
        $this->add(14, 14, Util::formatCnab('9', 'R', 1)); // Nº sequencial do registro de lote
        $this->add(15, 15, ''); // Reservado (Uso Banks)
        $this->add(16, 17, '01'); // Código de movimento remessa
        $this->add(18, 89, Util::formatCnab(9, 0, 72));
        $this->add(90, 99, '');
        $this->add(100, 139, '');
        $this->add(140, 179, '');
        $this->add(180, 199, '');
        $this->add(200, 215, Util::formatCnab(9, 0, 16));
        $this->add(216, 216, '');
        $this->add(217, 228, Util::formatCnab(9, 0, 12));
        $this->add(229, 230, '');
        $this->add(231, 231, '0');
        $this->add(232, 240, '');

        return $this;
    }

    /**
     * @param integer $nSequentialLot
     * @param SlipInterface $boleto
     *
     * @return Bancoob
     * @throws Exception
     */
    public function segmentS($nSequentialLot, SlipInterface $boleto)
    {
        $this->RegistryLotAmount = $nSequentialLot;
        $this->initiateDetail();

        $this->add(1, 3, Util::onlyNumbers($this->getBankCode())); //Código do Banks
        $this->add(4, 7, '0001'); // Numero do lote remessa
        $this->add(8, 8, '3'); // Numero do lote remessa
        $this->add(9, 13, Util::formatCnab(9, $nSequentialLot, 5)); // Nº sequencial do registro de lote
        $this->add(14, 14, Util::formatCnab('9', 'S', 1)); // Nº sequencial do registro de lote
        $this->add(15, 15, ''); // Reservado (Uso Banks)
        $this->add(16, 17, '01'); // Código de movimento remessa
        $this->add(18, 18, '3');
        $this->add(19, 240, '');

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function trailer()
    {
        $this->initiateTrailer();

        $this->add(1, 3, Util::onlyNumbers($this->getBankCode())); //Código do banco
        $this->add(4, 7, '9999'); // Numero do lote remessa
        $this->add(8, 8, '9'); //Tipo de registro
        $this->add(9, 17, ''); // Reservado (Uso Banks)
        $this->add(18, 23, Util::formatCnab(9, 1, 6)); // Qtd de lotes do arquivo
        $this->add(24, 29, Util::formatCnab(9, ($this->RegistryLotAmount + 4), 6)); // Qtd de lotes do arquivo
        $this->add(30, 35, Util::formatCnab(9, 0, 6));
        $this->add(36, 240, ''); // Reservado (Uso Banks)

        return $this;
    }

    /**
     * @return $this|mixed
     * @throws Exception
     */
    protected function headerLote()
    {
        $this->initiateHeaderLot();

        $this->add(1, 3, Util::onlyNumbers($this->getBankCode())); //Codigo do banco
        $this->add(4, 7, '0001'); // Lote de Serviço
        $this->add(8, 8, '1'); // Tipo de Registro
        $this->add(9, 9, 'R'); // Tipo de operação
        $this->add(10, 11, '01'); // Tipo de serviço
        $this->add(12, 13, ''); // Reservados (Uso Banks)
        $this->add(14, 16, '040'); // Versão do layout
        $this->add(17, 17, ''); // Reservados (Uso Banks)
        $this->add(18, 18, strlen(Util::onlyNumbers($this->getBeneficiary()->getDocument())) == 14 ? '2' : '1'); // Tipo de inscrição da empresa
        $this->add(19, 33, Util::formatCnab('9L', $this->getBeneficiary()->getDocument(), 14)); // Numero de inscrição da empresa
        $this->add(34, 53, ''); // Reservados (Uso Banks)
        $this->add(54, 58, Util::formatCnab(9, $this->getAgency(), 5)); // Agência do cedente
        $this->add(59, 59, Util::formatCnab(9, '', 1)); // Digito verificador da Agência do cedente
        $this->add(60, 71, Util::formatCnab(9, $this->getAccount(), 12)); // Numero da conta corrente
        $this->add(72, 72, Util::formatCnab('9', $this->getAccountCheckDigit(), 1));
        $this->add(73, 73, ''); // Reservados (Uso Banks)
        $this->add(74, 103, Util::formatCnab('X', $this->getBeneficiary()->getName(), 30)); // Nome do cedente
        $this->add(104, 143, ''); // Mensagem 1
        $this->add(144, 183, ''); // Mensagem 2
        $this->add(184, 191, Util::formatCnab(9, $this->getRemittanceId(), 8)); // Número Remessa/retorno
        $this->add(192, 199, date('dmY')); // Data de Gravação do arquivo
        $this->add(200, 207, '00000000');
        $this->add(208, 240, ''); // Reservado (Uso Banks)

        return $this;
    }

    /**
     * @return $this|mixed
     * @throws Exception
     */
    protected function trailerLote() {
        $this->initiateTrailerLot();

        $this->add(1, 3, Util::onlyNumbers($this->getBankCode())); //Codigo do banco
        $this->add(4, 7, '0001'); // Numero do lote remessa
        $this->add(8, 8, '5'); //Tipo de registro
        $this->add(9, 17, ''); // Reservado (Uso Banks)
        $this->add(18, 23, Util::formatCnab(9, 1, 6)); // Qtd de lotes do arquivo
        $this->add(24, 29, Util::formatCnab(9, ($this->RegistryLotAmount + 4), 6)); // Qtd de lotes do arquivo
        $this->add(30, 46, Util::formatCnab(9, ($this->titleTotalAmount * 100), 17, 2));
        $this->add(47, 115, Util::formatCnab(9, 0, 69)); // Qtd de lotes do arquivo
        $this->add(116, 240, ''); // Reservado (Uso Banks)

        return $this;
    }

}
