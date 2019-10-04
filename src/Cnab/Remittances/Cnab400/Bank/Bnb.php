<?php

namespace PhpBoleto\Cnab\Remittances\Cnab400\Bank;

use Exception;
use PhpBoleto\Cnab\Remittances\Cnab400\RemittanceAbstract;
use PhpBoleto\Interfaces\Cnab\RemittanceInterface;
use PhpBoleto\Interfaces\Slip\SlipInterface;
use PhpBoleto\Tools\CheckDigitCalculation;
use PhpBoleto\Tools\Util;

/**
 * Class Bnb
 * @package PhpBoleto\CnabInterface\Remessa\Cnab400\Banks
 */
class Bnb extends RemittanceAbstract implements RemittanceInterface
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_CHEQUE = '03';
    const ESPECIE_CARNE = '04';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_OUTROS = '19';

    const OCORRENCIA_REMESSA = '01';
    const OCORRENCIA_PEDIDO_BAIXA = '02';
    const OCORRENCIA_CONCESSAO_ABATIMENTO = '04';
    const OCORRENCIA_ALT_VENCIMENTO = '06';
    const OCORRENCIA_ALT_SEUNUMERO = '08';
    const OCORRENCIA_PROTESTAR = '09';
    const OCORRENCIA_NAO_PROTESTAR = '10';
    const OCORRENCIA_INCLUSAO_OCORRENCIA = '12';
    const OCORRENCIA_EXCLUSAO_OCORRENCIA = '13';
    const OCORRENCIA_ALT_OUTROS_DADOS = '31';
    const OCORRENCIA_PEDIDO_DEVOLUCAO = '32';
    const OCORRENCIA_PEDIDO_DEVOLUCAO_ENTREGUE_SACADO = '33';
    const OCORRENCIA_PEDIDO_DOS_TITULOS_EM_ABERTO = '99';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_ACATAR_INSTRUCOES_TITULO = '05';
    const INSTRUCAO_NAO_COBRAR_ENCARGOS = '08';
    const INSTRUCAO_NAO_RECEBER_APOS_VENCIMENTO = '12';
    const INSTRUCAO_APOS_VENCIMENTO_COBRAR_COMISSAO_PERMANENCIA = '15';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $bankCode = SlipInterface::BANK_CODE_BNB;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $wallets = ['21'];

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
     * Retorna o numero da carteira, deve ser override em casos de carteira de letras
     *
     * @return string
     */
    public function getWalletNumber()
    {
        if ($this->getWallet() == '21') {
            return '4';
        }
        return '1';
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
        $this->add(27, 30, Util::formatCnab('9', $this->getAgency(), 4));
        $this->add(31, 32, '00');
        $this->add(33, 39, Util::formatCnab('9', $this->getAccount(), 7));
        $this->add(40, 40, $this->getAccountCheckDigit() ?: CheckDigitCalculation::bnbAccount($this->getAgency(), $this->getAccount()));
        $this->add(41, 46, '');
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiary()->getName(), 30));
        $this->add(77, 79, $this->getBankCode());
        $this->add(80, 94, Util::formatCnab('X', 'B.DO NORDESTE', 15));
        $this->add(95, 100, date('dmy'));
        $this->add(101, 394, '');
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
        $this->add(1, 1, '1');
        $this->add(2, 17, '');
        $this->add(18, 21, Util::formatCnab('9', $this->getAgency(), 4));
        $this->add(22, 23, '00');
        $this->add(24, 30, Util::formatCnab('9', $this->getAccount(), 7));
        $this->add(31, 31, $this->getAccountCheckDigit() ?: CheckDigitCalculation::bnbAccount($this->getAgency(), $this->getAccount()));
        $this->add(32, 33, Util::formatCnab('9', round($slip->getFine()), 2)); // Só aceita números inteiros
        $this->add(34, 37, '');
        $this->add(38, 62, Util::formatCnab('X', $slip->getControlNumber(), 25)); // Numero de controle
        $this->add(63, 70, Util::formatCnab('9', $slip->getOurNumber(), 8));
        $this->add(71, 80, '0000000000');
        $this->add(81, 86, '000000'); // Data segundo desconto
        $this->add(87, 99, Util::formatCnab('9', '0', 13)); // Segundo desconto
        $this->add(100, 107, '');
        $this->add(108, 108, Util::formatCnab('9', $this->getWalletNumber(), 1));
        $this->add(109, 110, self::OCORRENCIA_REMESSA); // REGISTRO
        if ($slip->getStatus() == $slip::STATUS_DROP) {
            $this->add(109, 110, self::OCORRENCIA_PEDIDO_BAIXA); // BAIXA
        }
        if ($slip->getStatus() == $slip::STATUS_ALTER) {
            $this->add(109, 110, self::OCORRENCIA_ALT_VENCIMENTO); // ALTERAR VENCIMENTO
        }
        $this->add(111, 120, Util::formatCnab('X', $slip->getDocumentNumber(), 10));
        $this->add(121, 126, $slip->getDueDate()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $slip->getValue(), 13));
        $this->add(140, 142, $this->getBankCode());
        $this->add(143, 146, '0000');
        $this->add(147, 147, '');
        $this->add(148, 149, $slip->getDocumentTypeCode());
        $this->add(150, 150, $slip->getAcceptance());
        $this->add(151, 156, $slip->getDocumentDate()->format('dmy'));
        $this->add(157, 160, Util::formatCnab('9', self::INSTRUCAO_SEM, 4));
        $juros = 0;
        if ($slip->getInterest() > 0) {
            $juros = Util::percent($slip->getValue(), $slip->getInterest()) / 30; // Valor por dia
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
        $this->add(327, 331, Util::formatCnab('9', substr(Util::numbersOnly($slip->getPayer()->getPostalCode()), 0, 5), 5));
        $this->add(332, 334, Util::formatCnab('9', substr(Util::numbersOnly($slip->getPayer()->getPostalCode()), 5, 3), 3));
        $this->add(335, 349, Util::formatCnab('X', $slip->getPayer()->getCity(), 15));
        $this->add(350, 351, Util::formatCnab('X', $slip->getPayer()->getStateUf(), 2));
        $this->add(352, 391, Util::formatCnab('X', $slip->getGuarantor() ? $slip->getGuarantor()->getName() : '', 40));
        $this->add(392, 393, Util::formatCnab('9', '99', 2));
        if ($slip->getProtestAfter() > 0) {
            $this->add(392, 393, Util::formatCnab('9', $slip->getProtestAfter(), 2));
        }
        $this->add(394, 394, '0');
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
